<?php
/**
 * User Repository
 * 
 * Database operations for users table.
 */

namespace HRMS\Repositories;

use HRMS\Core\Database;

class UserRepository extends BaseRepository
{
    protected string $table = 'users';
    protected bool $tenantScoped = true;
    
    /**
     * Find user by email (global, not tenant-scoped)
     */
    public function findByEmail(string $email): ?array
    {
        return Database::fetchOne(
            'SELECT u.*, r.name as role_name, e.id as employee_id
             FROM users u
             JOIN roles r ON u.role_id = r.id
             LEFT JOIN employees e ON e.user_id = u.id
             WHERE u.email = ?',
            [$email]
        );
    }
    
    /**
     * Find user by ID with role and employee info
     */
    public function findWithDetails(int $id): ?array
    {
        return Database::fetchOne(
            'SELECT u.*, r.name as role_name, e.id as employee_id,
                    e.first_name, e.last_name, e.employee_code
             FROM users u
             JOIN roles r ON u.role_id = r.id
             LEFT JOIN employees e ON e.user_id = u.id
             WHERE u.id = ?',
            [$id]
        );
    }
    
    /**
     * Get user permissions
     */
    public function getPermissions(int $roleId): array
    {
        $permissions = Database::fetchAll(
            'SELECT p.name FROM permissions p
             JOIN role_permissions rp ON p.id = rp.permission_id
             WHERE rp.role_id = ?',
            [$roleId]
        );
        
        return array_column($permissions, 'name');
    }
    
    /**
     * Update last login timestamp
     */
    public function updateLastLogin(int $userId): bool
    {
        return Database::execute(
            'UPDATE users SET last_login = NOW() WHERE id = ?',
            [$userId]
        ) > 0;
    }
    
    /**
     * Find users by company with pagination
     */
    public function findByCompanyPaginated(int $companyId, int $offset, int $limit): array
    {
        return Database::fetchAll(
            'SELECT u.id, u.email, u.status, u.last_login, u.created_at,
                    r.name as role_name, e.first_name, e.last_name
             FROM users u
             JOIN roles r ON u.role_id = r.id
             LEFT JOIN employees e ON e.user_id = u.id
             WHERE u.company_id = ?
             ORDER BY u.created_at DESC
             LIMIT ? OFFSET ?',
            [$companyId, $limit, $offset]
        );
    }
    
    /**
     * Check if email exists
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = 'SELECT 1 FROM users WHERE email = ?';
        $params = [$email];
        
        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        
        return Database::fetchOne($sql, $params) !== null;
    }
}
