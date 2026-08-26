<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AuthService;
use App\Repositories\PageRepository;
use App\Repositories\SettingsRepository;
use App\Services\CsrfService;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly PageRepository $pages,
        private readonly SettingsRepository $settings,
        private readonly CsrfService $csrf
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    public function login(): void
    {
        if ($this->auth->isLoggedIn()) {
            header('Location: /dashboard');
            exit;
        }

        $this->view(
            'auth/login',
            [
                'message' => '',
                'messageType' => '',
                'username' => '',
                'pages' => $this->pages->getAll(),
                'settings' => $this->settings->get(),
                'csrfToken' => $this->csrf->token(),
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Authenticate
    |--------------------------------------------------------------------------
    */

    public function authenticate(): void
    {
        $this->csrf->requireValidToken();

        if ($this->auth->isLoggedIn()) {
            header('Location: /dashboard');
            exit;
        }

        $username = trim(
            $_POST['username'] ?? ''
        );

        $password =
            $_POST['password'] ?? '';

        $result = $this->auth->login(
            $username,
            $password
        );

        if ($result->success) {
            header('Location: /dashboard');
            exit;
        }

        $this->view(
            'auth/login',
            [
                'message' => $result->message,
                'messageType' => $result->type,
                'username' => $username,
                'pages' => $this->pages->getAll(),
                'settings' => $this->settings->get(),
                'csrfToken' => $this->csrf->token(),
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    public function logout(): void
    {
        $this->auth->requireLogin();

        $this->csrf->requireValidToken();

        $this->auth->logout();

        header('Location: /login');
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Register Page
    |--------------------------------------------------------------------------
    */

    public function register(): void
    {
        if ($this->auth->isLoggedIn()) {
            header('Location: /dashboard');
            exit;
        }

        $this->view(
            'auth/register',
            [
                'message' => '',
                'messageType' => '',
                'username' => '',
                'email' => '',
                'pages' => $this->pages->getAll(),
                'settings' => $this->settings->get(),
                'csrfToken' => $this->csrf->token(),
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Store Registration
    |--------------------------------------------------------------------------
    */

    public function store(): void
    {
        $this->csrf->requireValidToken();

        if ($this->auth->isLoggedIn()) {
            header('Location: /dashboard');
            exit;
        }

        $username = trim(
            $_POST['username'] ?? ''
        );

        $email = trim(
            $_POST['email'] ?? ''
        );

        $result = $this->auth->register(
            $username,
            $email,
            $_POST['password'] ?? '',
            $_POST['confirm_password'] ?? ''
        );

        $this->view(
            'auth/register',
            [
                'message' => $result->message,
                'messageType' => $result->type,
                'username' => $username,
                'email' => $email,
                'pages' => $this->pages->getAll(),
                'settings' => $this->settings->get(),
                'csrfToken' => $this->csrf->token(),
            ]
        );
    }
}
