<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\PageRepository;
use App\Repositories\SettingsRepository;
use App\Services\CsrfService;

class PageController extends Controller
{
    public function __construct(
        private readonly PageRepository $pages,
        private readonly SettingsRepository $settings,
        private readonly CsrfService $csrf
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Home
    |--------------------------------------------------------------------------
    */

    public function home(): void
    {
        $settings = $this->settings->get();

        $pages = $this->pages->getAll();

        $this->view(
            'pages/home',
            [
                'pages' => $pages,
                'settings' => $settings,
                'csrfToken' => $this->csrf->token(),
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Dynamic Page
    |--------------------------------------------------------------------------
    */

    public function show(string $slug): void
    {
        $page = $this->pages->findBySlug($slug);

        if (!$page) {
            $this->view('errors/404');

            return;
        }

        $settings = $this->settings->get();

        $pages = $this->pages->getAll();

        $this->view(
            'pages/page',
            [
                'page' => $page,
                'pages' => $pages,
                'settings' => $settings,
                'csrfToken' => $this->csrf->token(),
            ]
        );
    }
}
