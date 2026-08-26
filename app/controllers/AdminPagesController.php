<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AuthService;
use App\Repositories\PageRepository;
use App\Repositories\ImageRepository;
use App\Services\CsrfService;

class AdminPagesController extends Controller
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly PageRepository $pageRepository,
        private readonly ImageRepository $imageRepository,
        private readonly CsrfService $csrf
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(): void
    {
        $this->auth->requireAdmin();

        $pages = $this->pageRepository->adminAll();

        $this->view(
            'admin/dashboard/pages/index',
            [
                'title' => 'Manage Pages',
                'pages' => $pages,
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
        $this->auth->requireAdmin();

        $images = $this->imageRepository->all();

        $this->view(
            'admin/dashboard/pages/create',
            [
                'title' => 'Create Page',
                'images' => $images,
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
        $this->auth->requireAdmin();
        $this->csrf->requireValidToken();

        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),

            'hero_title' => trim(
                $_POST['hero_title'] ?? ''
            ),

            'hero_subtitle' => trim(
                $_POST['hero_subtitle'] ?? ''
            ),

            'hero_media_id' => !empty($_POST['hero_media_id'])
                ? (int) $_POST['hero_media_id']
                : null,

            'hero_image_alt' => trim(
                $_POST['hero_image_alt'] ?? ''
            ),

            'main_heading' => trim(
                $_POST['main_heading'] ?? ''
            ),

            'main_content' => trim(
                $_POST['main_content'] ?? ''
            ),

            'column1_title' => trim(
                $_POST['column1_title'] ?? ''
            ),

            'column1_content' => trim(
                $_POST['column1_content'] ?? ''
            ),

            'column2_title' => trim(
                $_POST['column2_title'] ?? ''
            ),

            'column2_content' => trim(
                $_POST['column2_content'] ?? ''
            ),

            'column3_title' => trim(
                $_POST['column3_title'] ?? ''
            ),

            'column3_content' => trim(
                $_POST['column3_content'] ?? ''
            ),

            'column4_title' => trim(
                $_POST['column4_title'] ?? ''
            ),

            'column4_content' => trim(
                $_POST['column4_content'] ?? ''
            ),

            'column5_title' => trim(
                $_POST['column5_title'] ?? ''
            ),

            'column5_content' => trim(
                $_POST['column5_content'] ?? ''
            ),

            'status' => $_POST['status'] ?? 'draft',

            'seo_title' => trim(
                $_POST['seo_title'] ?? ''
            ),

            'seo_description' => trim(
                $_POST['seo_description'] ?? ''
            ),
        ];

        if ($data['title'] === '') {
            $_SESSION['error'] =
                'Page title is required.';

            header('Location: /admin/pages/create');
            exit;
        }

        if ($data['slug'] === '') {
            $_SESSION['error'] =
                'Page slug is required.';

            header('Location: /admin/pages/create');
            exit;
        }

        try {
            $this->pageRepository->create($data);

            $_SESSION['success'] =
                'Page created successfully.';

        } catch (\Throwable $e) {
            $_SESSION['error'] =
                'Unable to create page.';
        }

        header('Location: /admin/pages');
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(): void
    {
        $this->auth->requireAdmin();

        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['error'] =
                'Invalid page.';

            header('Location: /admin/pages');
            exit;
        }

        $page = $this->pageRepository->findById($id);

        if (!$page) {
            $_SESSION['error'] =
                'Page not found.';

            header('Location: /admin/pages');
            exit;
        }

        $images = $this->imageRepository->all();

        $this->view(
            'admin/dashboard/pages/edit',
            [
                'title' => 'Edit Page',
                'page' => $page,
                'images' => $images,
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
        $this->auth->requireAdmin();
        $this->csrf->requireValidToken();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['error'] =
                'Invalid page.';

            header('Location: /admin/pages');
            exit;
        }

        $page = $this->pageRepository->findById($id);

        if (!$page) {
            $_SESSION['error'] =
                'Page not found.';

            header('Location: /admin/pages');
            exit;
        }

        $data = [
            'id' => $id,

            'title' => trim(
                $_POST['title'] ?? ''
            ),

            'slug' => trim(
                $_POST['slug'] ?? ''
            ),

            'hero_title' => trim(
                $_POST['hero_title'] ?? ''
            ),

            'hero_subtitle' => trim(
                $_POST['hero_subtitle'] ?? ''
            ),

            'hero_media_id' => !empty(
                $_POST['hero_media_id']
            )
                ? (int) $_POST['hero_media_id']
                : null,

            'hero_image_alt' => trim(
                $_POST['hero_image_alt'] ?? ''
            ),

            'main_heading' => trim(
                $_POST['main_heading'] ?? ''
            ),

            'main_content' => trim(
                $_POST['main_content'] ?? ''
            ),

            'column1_title' => trim(
                $_POST['column1_title'] ?? ''
            ),

            'column1_content' => trim(
                $_POST['column1_content'] ?? ''
            ),

            'column2_title' => trim(
                $_POST['column2_title'] ?? ''
            ),

            'column2_content' => trim(
                $_POST['column2_content'] ?? ''
            ),

            'column3_title' => trim(
                $_POST['column3_title'] ?? ''
            ),

            'column3_content' => trim(
                $_POST['column3_content'] ?? ''
            ),

            'column4_title' => trim(
                $_POST['column4_title'] ?? ''
            ),

            'column4_content' => trim(
                $_POST['column4_content'] ?? ''
            ),

            'column5_title' => trim(
                $_POST['column5_title'] ?? ''
            ),

            'column5_content' => trim(
                $_POST['column5_content'] ?? ''
            ),

            'status' => $_POST['status'] ?? 'draft',

            'seo_title' => trim(
                $_POST['seo_title'] ?? ''
            ),

            'seo_description' => trim(
                $_POST['seo_description'] ?? ''
            ),
        ];

        if ($data['title'] === '') {
            $_SESSION['error'] =
                'Page title is required.';

            header('Location: /admin/pages/edit?id=' . $id);
            exit;
        }

        if ($data['slug'] === '') {
            $_SESSION['error'] =
                'Page slug is required.';

            header('Location: /admin/pages/edit?id=' . $id);
            exit;
        }

        try {
            $this->pageRepository->update($data);

            $_SESSION['success'] =
                'Page updated successfully.';

        } catch (\Throwable $e) {
            $_SESSION['error'] =
                'Unable to update page.';
        }

        header('Location: /admin/pages');
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Protected Pages
    |--------------------------------------------------------------------------
    */

    private function protectedPage(string $slug): bool
    {
        return in_array(
            strtolower($slug),
            [
                'home',
                'blog',
                'contact',
            ],
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function delete(): void
    {
        $this->auth->requireAdmin();
        $this->csrf->requireValidToken();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['error'] =
                'Invalid page.';

            header('Location: /admin/pages');
            exit;
        }

        $page = $this->pageRepository->findById($id);

        if (!$page) {
            $_SESSION['error'] =
                'Page not found.';

            header('Location: /admin/pages');
            exit;
        }

        if (
            $this->protectedPage(
                (string) $page['slug']
            )
        ) {
            $_SESSION['error'] =
                'This page is protected and cannot be deleted.';

            header('Location: /admin/pages');
            exit;
        }

        try {
            $this->pageRepository->delete($id);

            $_SESSION['success'] =
                'Page deleted successfully.';

        } catch (\Throwable $e) {
            $_SESSION['error'] =
                'Unable to delete page.';
        }

        header('Location: /admin/pages');
        exit;
    }
}
