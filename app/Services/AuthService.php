<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;

/*
|--------------------------------------------------------------------------
| AuthService
|--------------------------------------------------------------------------
| Handles authentication, sessions and authorization.
|--------------------------------------------------------------------------
*/

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

        if (!(bool)$user['email_verified']) {
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

        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = strtolower(trim((string)$user['role']));
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
                [
                    'expires' => time() - 42000,
                    'path' => $params['path'],
                    'domain' => $params['domain'],
                    'secure' => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => $params['samesite'] ?? 'Lax',
                ]
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

        $username = trim($username);
        $email = trim($email);

        if ($username === '') {
            return ServiceResult::error(
                'Username is required.'
            );
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ServiceResult::error(
                'Please enter a valid email address.'
            );
        }

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
        | New registrations always receive the User role
        |--------------------------------------------------------------------------
        */

        $roleId = $this->users->findRoleIdByName('User');

        if (!$roleId) {
            return ServiceResult::error(
                'Default role not found.'
            );
        }

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
    | Session
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
            && !ctype_digit((string)$lastActivity)
        ) {
            $this->logout();

            return false;
        }

        if (
            time() - (int)$lastActivity
            >= self::SESSION_IDLE_TIMEOUT
        ) {
            $this->logout();

            return false;
        }

        $_SESSION['last_activity'] = time();

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    */

    public function isSuperAdmin(): bool
    {
        if (!$this->isLoggedIn()) {
            return false;
        }

        return $this->currentRole() === 'super admin';
    }

    public function isAdmin(): bool
    {
        if (!$this->isLoggedIn()) {
            return false;
        }

        return in_array(
            $this->currentRole(),
            [
                'admin',
                'super admin',
            ],
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Authorization Requirements
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

        return (int)$_SESSION['user_id'];
    }

    public function currentUsername(): ?string
    {
        return isset($_SESSION['username'])
            ? (string)$_SESSION['username']
            : null;
    }

    public function currentRole(): ?string
    {
        return isset($_SESSION['role'])
            ? strtolower(trim((string)$_SESSION['role']))
            : null;
    }
}
