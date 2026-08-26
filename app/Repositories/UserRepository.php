<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Repository;
use PDO;

class UserRepository extends Repository
{
    /*
    |--------------------------------------------------------------------------
    | Administration
    |--------------------------------------------------------------------------
    */

    public function countUsers(): int
    {
        $stmt = $this->db->query("
            SELECT COUNT(*)
            FROM users
        ");

        return (int) $stmt->fetchColumn();
    }

    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT
                u.id,
                u.username,
                u.email,
                u.email_verified,
                r.name AS role
            FROM users u
            LEFT JOIN roles r
                ON u.role_id = r.id
            ORDER BY u.id DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteUser(int $id): bool
    {
        try {
            $this->db->beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | Remove comments belonging to user
            |--------------------------------------------------------------------------
            */

            $stmt = $this->db->prepare("
                DELETE FROM comments
                WHERE user_id = :id
            ");

            $stmt->execute([
                'id' => $id,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Delete user
            |--------------------------------------------------------------------------
            */

            $stmt = $this->db->prepare("
                DELETE FROM users
                WHERE id = :id
            ");

            $stmt->execute([
                'id' => $id,
            ]);

            $deleted = $stmt->rowCount() > 0;

            if (!$deleted) {
                $this->db->rollBack();

                return false;
            }

            $this->db->commit();

            return true;

        } catch (\Throwable $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    public function findByUsername(
        string $username
    ): ?array {
        $stmt = $this->db->prepare("
            SELECT
                u.id,
                u.username,
                u.password,
                u.email_verified,
                r.name AS role
            FROM users u
            LEFT JOIN roles r
                ON u.role_id = r.id
            WHERE u.username = :username
            LIMIT 1
        ");

        $stmt->execute([
            'username' => $username,
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function findByEmail(
        string $email
    ): ?array {
        $stmt = $this->db->prepare("
            SELECT
                id,
                username,
                email,
                password,
                role_id,
                email_verified,
                verification_token
            FROM users
            WHERE email = :email
            LIMIT 1
        ");

        $stmt->execute([
            'email' => $email,
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /*
    |--------------------------------------------------------------------------
    | Registration
    |--------------------------------------------------------------------------
    */

    public function usernameExists(
        string $username
    ): bool {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM users
            WHERE username = :username
        ");

        $stmt->execute([
            'username' => $username,
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function emailExists(
        string $email
    ): bool {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM users
            WHERE email = :email
        ");

        $stmt->execute([
            'email' => $email,
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function usernameOrEmailExists(
        string $username,
        string $email
    ): bool {
        $stmt = $this->db->prepare("
            SELECT id
            FROM users
            WHERE username = :username
               OR email = :email
            LIMIT 1
        ");

        $stmt->execute([
            'username' => $username,
            'email' => $email,
        ]);

        return (bool) $stmt->fetch();
    }

    public function createUser(
        string $username,
        string $email,
        string $password,
        int $roleId,
        string $token
    ): bool {
        $stmt = $this->db->prepare("
            INSERT INTO users
            (
                username,
                email,
                password,
                role_id,
                verification_token
            )
            VALUES
            (
                :username,
                :email,
                :password,
                :role_id,
                :token
            )
        ");

        return $stmt->execute([
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'role_id' => $roleId,
            'token' => $token,
        ]);
    }

    public function verifyEmail(
        string $token
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE users
            SET
                email_verified = 1,
                verification_token = NULL
            WHERE verification_token = :token
        ");

        return $stmt->execute([
            'token' => $token,
        ]);
    }

    public function findRoleIdByName(
        string $role
    ): ?int {
        $stmt = $this->db->prepare("
            SELECT id
            FROM roles
            WHERE name = :role
            LIMIT 1
        ");

        $stmt->execute([
            'role' => $role,
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return isset($result['id'])
            ? (int) $result['id']
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Password Reset
    |--------------------------------------------------------------------------
    */

    public function findPasswordHashById(
        int $userId
    ): ?string {
        $stmt = $this->db->prepare("
            SELECT password
            FROM users
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute([
            'id' => $userId,
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['password'] ?? null;
    }

    public function findByResetToken(
        string $tokenHash
    ): ?array {
        $stmt = $this->db->prepare("
            SELECT
                id,
                username,
                email
            FROM users
            WHERE reset_token = :token
              AND reset_expires > NOW()
            LIMIT 1
        ");

        $stmt->execute([
            'token' => $tokenHash,
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function savePasswordResetToken(
        int $userId,
        string $tokenHash,
        string $expires
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE users
            SET
                reset_token = :token,
                reset_expires = :expires
            WHERE id = :id
        ");

        return $stmt->execute([
            'token' => $tokenHash,
            'expires' => $expires,
            'id' => $userId,
        ]);
    }

    public function updatePassword(
        int $userId,
        string $password
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE users
            SET
                password = :password,
                reset_token = NULL,
                reset_expires = NULL
            WHERE id = :id
        ");

        return $stmt->execute([
            'password' => $password,
            'id' => $userId,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Dashboard / Profile
    |--------------------------------------------------------------------------
    */

    public function findById(
        int $id
    ): ?array {
        $stmt = $this->db->prepare("
            SELECT
                u.id,
                u.username,
                u.email,
                r.name AS role,
                u.theme_color,
                u.background_color,
                u.text_color,
                i.filepath
            FROM users u
            LEFT JOIN roles r
                ON u.role_id = r.id
            LEFT JOIN images i
                ON u.image_id = i.id
            WHERE u.id = :id
            LIMIT 1
        ");

        $stmt->execute([
            'id' => $id,
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return null;
        }

        $user['theme_color'] =
            $user['theme_color'] ?? '#007bff';

        $user['background_color'] =
            $user['background_color'] ?? '#ffffff';

        $user['text_color'] =
            $user['text_color'] ?? '#000000';

        return $user;
    }

    public function updateTheme(
        int $id,
        string $theme,
        string $background,
        string $text
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE users
            SET
                theme_color = :theme,
                background_color = :background,
                text_color = :text
            WHERE id = :id
        ");

        return $stmt->execute([
            'theme' => $theme,
            'background' => $background,
            'text' => $text,
            'id' => $id,
        ]);
    }

    public function updateImage(
        int $userId,
        int $imageId
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE users
            SET image_id = :image
            WHERE id = :id
        ");

        return $stmt->execute([
            'image' => $imageId,
            'id' => $userId,
        ]);
    }

    public function updateUser(
        int $id,
        string $username,
        string $email,
        int $roleId
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE users
            SET
                username = :username,
                email = :email,
                role_id = :role_id
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
            'username' => $username,
            'email' => $email,
            'role_id' => $roleId,
        ]);
    }
}
