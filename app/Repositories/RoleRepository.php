<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Repository;
use PDO;

class RoleRepository extends Repository
{
    /*
    |--------------------------------------------------------------------------
    | All Roles
    |--------------------------------------------------------------------------
    */

    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT
                id,
                name,
                description
            FROM roles
            ORDER BY id ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /*
    |--------------------------------------------------------------------------
    | Find
    |--------------------------------------------------------------------------
    */

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                id,
                name,
                description
            FROM roles
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute([
            'id' => $id
        ]);

        $role = $stmt->fetch(PDO::FETCH_ASSOC);

        return $role ?: null;
    }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(
        string $name,
        string $description
    ): bool {
        $stmt = $this->db->prepare("
            INSERT INTO roles
            (
                name,
                description
            )
            VALUES
            (
                :name,
                :description
            )
        ");

        return $stmt->execute([
            'name' => $name,
            'description' => $description
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        int $id,
        string $name,
        string $description
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE roles
            SET
                name = :name,
                description = :description
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
            'name' => $name,
            'description' => $description
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM roles
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Users Using Role
    |--------------------------------------------------------------------------
    */

    public function usersUsingRole(int $id): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM users
            WHERE role_id = :id
        ");

        $stmt->execute([
            'id' => $id
        ]);

        return (int)$stmt->fetchColumn();
    }

    /*
    |--------------------------------------------------------------------------
    | System Roles
    |--------------------------------------------------------------------------
    */

    public function isSystemRole(int $id): bool
    {
        return in_array(
            $id,
            [1, 2, 5],
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Role Exists
    |--------------------------------------------------------------------------
    */

    public function exists(string $name): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM roles
            WHERE LOWER(name) = LOWER(:name)
        ");

        $stmt->execute([
            'name' => $name
        ]);

        return (int)$stmt->fetchColumn() > 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Role Exists Except Current Role
    |--------------------------------------------------------------------------
    */

    public function existsExceptId(
        string $name,
        int $id
    ): bool {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM roles
            WHERE LOWER(name) = LOWER(:name)
            AND id != :id
        ");

        $stmt->execute([
            'name' => $name,
            'id' => $id
        ]);

        return (int)$stmt->fetchColumn() > 0;
    }
}
