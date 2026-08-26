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

    public function store(): void
    {
        $this->auth->requireSuperAdmin();

        $this->csrf->requireValidToken();

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        if ($name === '') {
            $_SESSION['error'] = 'Role name is required.';

            header('Location: /admin/roles/create');
            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Check Duplicate
        |--------------------------------------------------------------------------
        */

        if ($this->roleRepository->exists($name)) {
            $_SESSION['error'] = 'Role already exists.';

            header('Location: /admin/roles/create');
            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Create Role
        |--------------------------------------------------------------------------
        */

        try {
            $success = $this->roleRepository->create(
                $name,
                $description
            );

            if (!$success) {
                $_SESSION['error'] = 'Unable to create role.';

                header('Location: /admin/roles/create');
                exit;
            }

            $_SESSION['success'] = 'Role created successfully.';

            header('Location: /admin/roles');
            exit;
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Unable to create role.';

            header('Location: /admin/roles/create');
            exit;
        }
    }

    public function edit(): void
    {
        $this->auth->requireSuperAdmin();

        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['error'] = 'Invalid role.';

            header('Location: /admin/roles');
            exit;
        }

        $role = $this->roleRepository->findById($id);

        if (!$role) {
            $_SESSION['error'] = 'Role not found.';

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

    public function update(): void
    {
        $this->auth->requireSuperAdmin();

        $this->csrf->requireValidToken();

        $id = (int) ($_POST['id'] ?? 0);

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        if ($id <= 0) {
            $_SESSION['error'] = 'Invalid role.';

            header('Location: /admin/roles');
            exit;
        }

        if ($name === '') {
            $_SESSION['error'] = 'Role name is required.';

            header(
                'Location: /admin/roles/edit?id=' . $id
            );

            exit;
        }

        $role = $this->roleRepository->findById($id);

        if (!$role) {
            $_SESSION['error'] = 'Role not found.';

            header('Location: /admin/roles');
            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent Duplicate Role Names
        |--------------------------------------------------------------------------
        */

        if (
            strtolower(trim($role['name'])) !== strtolower($name)
            && $this->roleRepository->exists($name)
        ) {
            $_SESSION['error'] = 'Role already exists.';

            header(
                'Location: /admin/roles/edit?id=' . $id
            );

            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Update Role
        |--------------------------------------------------------------------------
        */

        try {
            $success = $this->roleRepository->update(
                $id,
                $name,
                $description
            );

            if (!$success) {
                $_SESSION['error'] = 'Unable to update role.';

                header(
                    'Location: /admin/roles/edit?id=' . $id
                );

                exit;
            }

            $_SESSION['success'] = 'Role updated successfully.';

            header('Location: /admin/roles');
            exit;
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Unable to update role.';

            header(
                'Location: /admin/roles/edit?id=' . $id
            );

            exit;
        }
    }

    public function delete(): void
    {
        $this->auth->requireSuperAdmin();

        $this->csrf->requireValidToken();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['error'] = 'Invalid role.';

            header('Location: /admin/roles');
            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Find Role
        |--------------------------------------------------------------------------
        */

        $role = $this->roleRepository->findById($id);

        if (!$role) {
            $_SESSION['error'] = 'Role not found.';

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
                'This is a system role and cannot be deleted.';

            header('Location: /admin/roles');
            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent Deleting Roles Assigned To Users
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
        | Delete Role
        |--------------------------------------------------------------------------
        */

        try {
            $success = $this->roleRepository->delete($id);

            if (!$success) {
                $_SESSION['error'] = 'Unable to delete role.';

                header('Location: /admin/roles');
                exit;
            }

            $_SESSION['success'] =
                'Role deleted successfully.';

            header('Location: /admin/roles');
            exit;
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Unable to delete role.';

            header('Location: /admin/roles');
            exit;
        }
    }
}
