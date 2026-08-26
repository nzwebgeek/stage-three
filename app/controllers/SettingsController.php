<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AuthService;
use App\Services\CsrfService;
use App\Repositories\SettingsRepository;
use App\Repositories\BlogSettingsRepository;

class SettingsController extends Controller
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly SettingsRepository $settingsRepository,
        private readonly BlogSettingsRepository $blogSettingsRepository,
        private readonly CsrfService $csrf
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Settings Page
    |--------------------------------------------------------------------------
    */

    public function index(): void
    {
        $this->auth->requireAdmin();

        $settings = $this->settingsRepository->get();

        $blogSettings = $this->blogSettingsRepository->get();

        $this->view(
            'admin/dashboard/settings/index',
            [
                'title' => 'Settings',
                'settings' => $settings,
                'blogSettings' => $blogSettings,
                'csrfToken' => $this->csrf->token(),
            ],
            'admin'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update Settings
    |--------------------------------------------------------------------------
    */

    public function update(): void
    {
        $this->auth->requireAdmin();

        $this->csrf->requireValidToken();

        $siteName = trim(
            (string) ($_POST['site_name'] ?? '')
        );

        $contactEmail = trim(
            (string) ($_POST['contact_email'] ?? '')
        );

        $contactPhone = trim(
            (string) ($_POST['contact_phone'] ?? '')
        );

        $copyrightText = trim(
            (string) ($_POST['copyright_text'] ?? '')
        );

        $theme = trim(
            (string) ($_POST['theme'] ?? 'Light')
        );

        $maintenanceMode = isset(
            $_POST['maintenance_mode']
        ) ? 1 : 0;

        $adminEmail = trim(
            (string) ($_POST['admin_email'] ?? '')
        );

        $seoTitle = trim(
            (string) ($_POST['seo_title'] ?? '')
        );

        $seoDescription = trim(
            (string) ($_POST['seo_description'] ?? '')
        );

        try {

            $this->settingsRepository->update(
                $siteName,
                $contactEmail,
                $contactPhone,
                $copyrightText,
                $theme,
                $maintenanceMode,
                $adminEmail,
                $seoTitle,
                $seoDescription
            );

            $_SESSION['success'] =
                'Settings saved successfully.';

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Do not expose internal exception details to users
            |--------------------------------------------------------------------------
            */

            $_SESSION['error'] =
                'Unable to save settings.';
        }

        header('Location: /admin/settings');
        exit;
    }
}
