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
    | Roles
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

        if ($name === '') {
            $_SESSION['error'] = 'Role name is required.';

            header('Location: /admin/roles/create');
            exit;
        }

        if ($this->roleRepository->exists($name)) {
            $_SESSION['error'] =
                'Role already exists.';

            header('Location: /admin/roles/create');
            exit;
        }

        $this->roleRepository->create(
            $name,
            $description
        );

        $_SESSION['success'] =
            'Role created successfully.';

        header('Location: /admin/roles');
        exit;
    }

    public function edit(): void
    {
        $this->auth->requireSuperAdmin();

        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            header('Location: /admin/roles');
            exit;
        }

        $role = $this->roleRepository->findById($id);

        if (!$role) {
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

        if ($id <= 0) {
            $_SESSION['error'] = 'Invalid role.';

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

        $role = $this->roleRepository->findById($id);

        if (!$role) {
            $_SESSION['error'] = 'Role not found.';

            header('Location: /admin/roles');
            exit;
        }

        $this->roleRepository->update(
            $id,
            $name,
            $description
        );

        $_SESSION['success'] =
            'Role updated successfully.';

        header('Location: /admin/roles');
        exit;
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

        if ($this->roleRepository->isSystemRole($id)) {
            $_SESSION['error'] =
                'This is a system role and cannot be deleted.';

            header('Location: /admin/roles');
            exit;
        }

        $users = $this->roleRepository->usersUsingRole($id);

        if ($users > 0) {
            $_SESSION['error'] =
                'Cannot delete role. Users are assigned to this role.';

            header('Location: /admin/roles');
            exit;
        }

        $this->roleRepository->delete($id);

        $_SESSION['success'] =
            'Role deleted successfully.';

        header('Location: /admin/roles');
        exit;
    }
}
