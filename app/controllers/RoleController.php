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
    | List Roles
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
    | Create Role
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
    | Store Role
    |--------------------------------------------------------------------------
    */

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

        if (mb_strlen($name) > 100) {
            $_SESSION['error'] = 'Role name is too long.';

            header('Location: /admin/roles/create');
            exit;
        }

        if ($this->roleRepository->exists($name)) {
            $_SESSION['error'] = 'Role already exists.';

            header('Location: /admin/roles/create');
            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Create
        |--------------------------------------------------------------------------
        */

        $created = $this->roleRepository->create(
            $name,
            $description
        );

        if (!$created) {
            $_SESSION['error'] = 'Unable to create role.';

            header('Location: /admin/roles/create');
            exit;
        }

        $_SESSION['success'] = 'Role created successfully.';

        header('Location: /admin/roles');
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Edit Role
    |--------------------------------------------------------------------------
    */

    public function edit(): void
    {
        $this->auth->requireSuperAdmin();

        $id = (int)($_GET['id'] ?? 0);

        if ($id <= 0) {
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

    /*
    |--------------------------------------------------------------------------
    | Update Role
    |--------------------------------------------------------------------------
    */

    public function update(): void
    {
        $this->auth->requireSuperAdmin();

        $this->csrf->requireValidToken();

        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

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

        if ($name === '') {
            $_SESSION['error'] = 'Role name is required.';

            header('Location: /admin/roles/edit?id=' . $id);
            exit;
        }

        if (mb_strlen($name) > 100) {
            $_SESSION['error'] = 'Role name is too long.';

            header('Location: /admin/roles/edit?id=' . $id);
            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Don't allow duplicate role names
        |--------------------------------------------------------------------------
        */

        if (
            $this->roleRepository->existsExceptId(
                $name,
                $id
            )
        ) {
            $_SESSION['error'] = 'Role already exists.';

            header('Location: /admin/roles/edit?id=' . $id);
            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Protect system roles from being renamed
        |--------------------------------------------------------------------------
        */

        if ($this->roleRepository->isSystemRole($id)) {

            /*
             * Allow description changes, but retain the
             * original system role name.
             */

            $name = $role['name'];
        }

        $updated = $this->roleRepository->update(
            $id,
            $name,
            $description
        );

        if (!$updated) {
            $_SESSION['error'] = 'Unable to update role.';

            header('Location: /admin/roles/edit?id=' . $id);
            exit;
        }

        $_SESSION['success'] = 'Role updated successfully.';

        header('Location: /admin/roles');
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Role
    |--------------------------------------------------------------------------
    */

    public function delete(): void
    {
        $this->auth->requireSuperAdmin();

        $this->csrf->requireValidToken();

        $id = (int)($_POST['id'] ?? 0);

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

        /*
        |--------------------------------------------------------------------------
        | System roles cannot be deleted
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
        | Don't delete roles currently assigned to users
        |--------------------------------------------------------------------------
        */

        $users = $this->roleRepository->usersUsingRole($id);

        if ($users > 0) {
            $_SESSION['error'] =
                'Cannot delete role. Users are assigned to this role.';

            header('Location: /admin/roles');
            exit;
        }

        $deleted = $this->roleRepository->delete($id);

        if (!$deleted) {
            $_SESSION['error'] = 'Unable to delete role.';

            header('Location: /admin/roles');
            exit;
        }

        $_SESSION['success'] = 'Role deleted successfully.';

        header('Location: /admin/roles');
        exit;
    }
}
