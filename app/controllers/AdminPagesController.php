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

    /**
     * List pages.
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

    /**
     * Show create page.
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

    /**
     * Store page.
     */
    public function store(): void
    {
        $this->auth->requireAdmin();
        $this->csrf->requireValidToken();

        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),

            'hero_title' => trim($_POST['hero_title'] ?? ''),
            'hero_subtitle' => trim($_POST['hero_subtitle'] ?? ''),

            'hero_media_id' => !empty($_POST['hero_media_id'])
                ? (int)$_POST['hero_media_id']
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

        $this->pageRepository->create($data);

        header(
            'Location: /admin/pages?success=created'
        );
        exit;
    }

    /**
     * Show edit page.
     */
    public function edit(): void
    {
        $this->auth->requireAdmin();

        $id = (int)($_GET['id'] ?? 0);

        if ($id <= 0) {
            header('Location: /admin/pages');
            exit;
        }

        $page = $this->pageRepository->findById($id);

        if (!$page) {
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

    /**
     * Update page.
     */
    public function update(): void
    {
        $this->auth->requireAdmin();
        $this->csrf->requireValidToken();

        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
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

            'hero_media_id' => !empty($_POST['hero_media_id'])
                ? (int)$_POST['hero_media_id']
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

        $this->pageRepository->update($data);

        header(
            'Location: /admin/pages?success=updated'
        );
        exit;
    }

    /**
     * Determine whether a page is protected.
     */
    private function protectedPage(string $slug): bool
    {
        return in_array(
            $slug,
            [
                'home',
                'blog',
                'contact',
            ],
            true
        );
    }

    /**
     * Delete page.
     */
    public function delete(): void
    {
        $this->auth->requireAdmin();
        $this->csrf->requireValidToken();

        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            header('Location: /admin/pages');
            exit;
        }

        $page = $this->pageRepository->findById($id);

        if (!$page) {
            header('Location: /admin/pages');
            exit;
        }

        if ($this->protectedPage($page['slug'])) {
            header(
                'Location: /admin/pages?error=protected'
            );
            exit;
        }

        $this->pageRepository->delete($id);

        header(
            'Location: /admin/pages?success=deleted'
        );
        exit;
    }
}
