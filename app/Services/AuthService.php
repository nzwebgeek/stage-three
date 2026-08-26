<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| AuthService
|--------------------------------------------------------------------------
| Handles authentication, registration and session logic.
|--------------------------------------------------------------------------
*/

namespace App\Services;

use App\Repositories\UserRepository;

class AuthService
{
    private const SESSION_IDLE_TIMEOUT = 1800; // 30 minutes

    public function __construct(
        private readonly UserRepository $users,
        private readonly Mailer $mailer,
        private readonly PasswordService $passwords
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    public function login(
        string $username,
        string $password
    ): ServiceResult {

        $user = $this->users->findByUsername($username);

        if (!$user) {
            return ServiceResult::error(
                'Invalid username or password.'
            );
        }

        if (!password_verify($password, $user['password'])) {
            return ServiceResult::error(
                'Invalid username or password.'
            );
        }

        if (!$user['email_verified']) {
            return ServiceResult::warning(
                'Please verify your email before logging in.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent session fixation
        |--------------------------------------------------------------------------
        */

        session_regenerate_id(true);

        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = strtolower(trim($user['role']));
        $_SESSION['last_activity'] = time();

        return ServiceResult::success(
            'Login successful.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {

            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    /*
    |--------------------------------------------------------------------------
    | Registration
    |--------------------------------------------------------------------------
    */

    public function register(
        string $username,
        string $email,
        string $password,
        string $confirmPassword
    ): ServiceResult {

        if ($password !== $confirmPassword) {
            return ServiceResult::error(
                'Passwords do not match.'
            );
        }

        $passwordError = $this->passwords->validate($password);

        if ($passwordError !== null) {
            return ServiceResult::error($passwordError);
        }

        if ($this->users->usernameOrEmailExists($username, $email)) {
            return ServiceResult::error(
                'That username or email is already registered.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | New registrations receive the User role
        |--------------------------------------------------------------------------
        */

        $roleId = $this->users->findRoleIdByName('User');

        if (!$roleId) {
            return ServiceResult::error(
                'Default role not found.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Email verification token
        |--------------------------------------------------------------------------
        */

        $token = bin2hex(random_bytes(32));

        $hashedPassword = $this->passwords->hash($password);

        $success = $this->users->createUser(
            $username,
            $email,
            $hashedPassword,
            $roleId,
            $token
        );

        if (!$success) {
            return ServiceResult::error(
                'Registration failed.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Send verification email
        |--------------------------------------------------------------------------
        */

        $this->mailer->sendVerificationEmail(
            $email,
            $username,
            $token
        );

        return ServiceResult::success(
            'Registration successful. Please check your email to verify your account.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Authentication State
    |--------------------------------------------------------------------------
    */

    public function isLoggedIn(): bool
    {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        $lastActivity = $_SESSION['last_activity'] ?? null;

        if (
            !is_int($lastActivity)
            && !ctype_digit((string) $lastActivity)
        ) {
            $this->logout();

            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Idle session timeout
        |--------------------------------------------------------------------------
        */

        if (
            time() - (int) $lastActivity
            >= self::SESSION_IDLE_TIMEOUT
        ) {
            $this->logout();

            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Refresh activity timestamp
        |--------------------------------------------------------------------------
        */

        $_SESSION['last_activity'] = time();

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Role Checks
    |--------------------------------------------------------------------------
    */

    public function isSuperAdmin(): bool
    {
        return strtolower(
            trim($_SESSION['role'] ?? '')
        ) === 'super admin';
    }

    public function isAdmin(): bool
    {
        $role = strtolower(
            trim($_SESSION['role'] ?? '')
        );

        return in_array(
            $role,
            [
                'admin',
                'super admin'
            ],
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Route Guards
    |--------------------------------------------------------------------------
    */

    public function requireLogin(): void
    {
        if (!$this->isLoggedIn()) {
            header('Location: /login');
            exit;
        }
    }

    public function requireAdmin(): void
    {
        $this->requireLogin();

        if (!$this->isAdmin()) {
            header('Location: /dashboard');
            exit;
        }
    }

    public function requireSuperAdmin(): void
    {
        $this->requireLogin();

        if (!$this->isSuperAdmin()) {
            header('Location: /admin');
            exit;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Current User
    |--------------------------------------------------------------------------
    */

    public function currentUserId(): ?int
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        return (int) $_SESSION['user_id'];
    }

    public function currentUsername(): ?string
    {
        return isset($_SESSION['username'])
            ? (string) $_SESSION['username']
            : null;
    }

    public function currentRole(): ?string
    {
        return isset($_SESSION['role'])
            ? (string) $_SESSION['role']
            : null;
    }
}
