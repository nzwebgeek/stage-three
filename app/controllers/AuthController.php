<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| AuthController
|--------------------------------------------------------------------------
| Handles HTTP requests related to authentication.
|--------------------------------------------------------------------------
*/

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

        $username = trim(
            (string) ($_POST['username'] ?? '')
        );

        $password = (string) (
            $_POST['password'] ?? ''
        );

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

        $username = trim(
            (string) ($_POST['username'] ?? '')
        );

        $email = trim(
            (string) ($_POST['email'] ?? '')
        );

        $password = (string) (
            $_POST['password'] ?? ''
        );

        $confirmPassword = (string) (
            $_POST['confirm_password'] ?? ''
        );

        $result = $this->auth->register(
            $username,
            $email,
            $password,
            $confirmPassword
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
