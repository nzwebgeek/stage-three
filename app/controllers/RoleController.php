<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AuthService;
use App\Repositories\RoleRepository;
use App\Services\CsrfService;

class RoleController extends Controller
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly RoleRepository $roleRepository,
        private readonly CsrfService $csrf
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Role Management
    |--------------------------------------------------------------------------
    */

    public function index(): void
    {
        $this->auth->requireSuperAdmin();

        $roles = $this->roleRepository->all();

        $this->view(
            'admin/dashboard/roles/index',
            [
                'title' => 'Manage Roles',
                'roles' => $roles,
                'csrfToken' => $this->csrf->token(),
            ],
            'admin'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(): void
    {
        $this->auth->requireSuperAdmin();

        $this->view(
            'admin/dashboard/roles/create',
            [
                'title' => 'Create Role',
                'csrfToken' => $this->csrf->token(),
            ],
            'admin'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(): void
    {
        $this->auth->requireSuperAdmin();

        $this->csrf->requireValidToken();

        $name = trim(
            (string)($_POST['name'] ?? '')
        );

        $description = trim(
            (string)($_POST['description'] ?? '')
        );

        /*
        |--------------------------------------------------------------------------
        | Validate Name
        |--------------------------------------------------------------------------
        */

        if ($name === '') {
            $_SESSION['error'] = 'Role name is required.';

            header('Location: /admin/roles/create');
            exit;
        }

        if (mb_strlen($name) > 100) {
            $_SESSION['error'] =
                'Role name cannot exceed 100 characters.';

            header('Location: /admin/roles/create');
            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Check Duplicate
        |--------------------------------------------------------------------------
        */

        if ($this->roleRepository->exists($name)) {
            $_SESSION['error'] =
                'Role already exists.';

            header('Location: /admin/roles/create');
            exit;
        }

        try {
            $created = $this->roleRepository->create(
                $name,
                $description
            );

            if (!$created) {
                $_SESSION['error'] =
                    'Unable to create role.';
            } else {
                $_SESSION['success'] =
                    'Role created successfully.';
            }
        } catch (\Throwable $e) {
            $_SESSION['error'] =
                'Unable to create role.';
        }

        header('Location: /admin/roles');
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(): void
    {
        $this->auth->requireSuperAdmin();

        $id = (int)($_GET['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['error'] =
                'Invalid role.';

            header('Location: /admin/roles');
            exit;
        }

        $role = $this->roleRepository->findById($id);

        if (!$role) {
            $_SESSION['error'] =
                'Role not found.';

            header('Location: /admin/roles');
            exit;
        }

        $this->view(
            'admin/dashboard/roles/edit',
            [
                'title' => 'Edit Role',
                'role' => $role,
                'csrfToken' => $this->csrf->token(),
            ],
            'admin'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(): void
    {
        $this->auth->requireSuperAdmin();

        $this->csrf->requireValidToken();

        $id = (int)($_POST['id'] ?? 0);

        $name = trim(
            (string)($_POST['name'] ?? '')
        );

        $description = trim(
            (string)($_POST['description'] ?? '')
        );

        if ($id <= 0) {
            $_SESSION['error'] =
                'Invalid role.';

            header('Location: /admin/roles');
            exit;
        }

        if ($name === '') {
            $_SESSION['error'] =
                'Role name is required.';

            header(
                'Location: /admin/roles/edit?id=' . $id
            );

            exit;
        }

        if (mb_strlen($name) > 100) {
            $_SESSION['error'] =
                'Role name cannot exceed 100 characters.';

            header(
                'Location: /admin/roles/edit?id=' . $id
            );

            exit;
        }

        $role = $this->roleRepository->findById($id);

        if (!$role) {
            $_SESSION['error'] =
                'Role not found.';

            header('Location: /admin/roles');
            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Protect System Roles
        |--------------------------------------------------------------------------
        */

        if ($this->roleRepository->isSystemRole($id)) {
            $_SESSION['error'] =
                'This is a system role and cannot be modified.';

            header('Location: /admin/roles');
            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Duplicate Name
        |--------------------------------------------------------------------------
        */

        if (
            strcasecmp(
                trim((string)$role['name']),
                $name
            ) !== 0
            && $this->roleRepository->exists($name)
        ) {
            $_SESSION['error'] =
                'Another role already uses that name.';

            header(
                'Location: /admin/roles/edit?id=' . $id
            );

            exit;
        }

        try {
            $updated = $this->roleRepository->update(
                $id,
                $name,
                $description
            );

            if (!$updated) {
                $_SESSION['error'] =
                    'Unable to update role.';
            } else {
                $_SESSION['success'] =
                    'Role updated successfully.';
            }
        } catch (\Throwable $e) {
            $_SESSION['error'] =
                'Unable to update role.';
        }

        header('Location: /admin/roles');
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function delete(): void
    {
        $this->auth->requireSuperAdmin();

        $this->csrf->requireValidToken();

        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['error'] =
                'Invalid role.';

            header('Location: /admin/roles');
            exit;
        }

        $role = $this->roleRepository->findById($id);

        if (!$role) {
            $_SESSION['error'] =
                'Role not found.';

            header('Location: /admin/roles');
            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | System Role Protection
        |--------------------------------------------------------------------------
        */

        if ($this->roleRepository->isSystemRole($id)) {
            $_SESSION['error'] =
                'This is a system role and cannot be deleted.';

            header('Location: /admin/roles');
            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Users Assigned To Role
        |--------------------------------------------------------------------------
        */

        $users = $this->roleRepository->usersUsingRole($id);

        if ($users > 0) {
            $_SESSION['error'] =
                'Cannot delete role. Users are assigned to this role.';

            header('Location: /admin/roles');
            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Delete
        |--------------------------------------------------------------------------
        */

        try {
            $deleted = $this->roleRepository->delete($id);

            if (!$deleted) {
                $_SESSION['error'] =
                    'Unable to delete role.';
            } else {
                $_SESSION['success'] =
                    'Role deleted successfully.';
            }
        } catch (\Throwable $e) {
            $_SESSION['error'] =
                'Unable to delete role.';
        }

        header('Location: /admin/roles');
        exit;
    }
}
