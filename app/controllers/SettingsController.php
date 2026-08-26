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
    | Settings
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

    public function update(): void
    {
        $this->auth->requireAdmin();

        $this->csrf->requireValidToken();

        $siteName = trim($_POST['site_name'] ?? '');
        $contactEmail = trim($_POST['contact_email'] ?? '');
        $contactPhone = trim($_POST['contact_phone'] ?? '');
        $copyrightText = trim($_POST['copyright_text'] ?? '');
        $theme = trim($_POST['theme'] ?? 'Light');
        $maintenanceMode = isset($_POST['maintenance_mode']) ? 1 : 0;
        $adminEmail = trim($_POST['admin_email'] ?? '');
        $seoTitle = trim($_POST['seo_title'] ?? '');
        $seoDescription = trim($_POST['seo_description'] ?? '');

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
            error_log(
                'SettingsController::update() failed: '
                . $e->getMessage()
            );

            $_SESSION['error'] =
                'Unable to save settings.';
        }

        header('Location: /admin/settings');
        exit;
    }
}
