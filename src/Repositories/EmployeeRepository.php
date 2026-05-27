<?php
/**
 * Employee Repository
 * 
 * Handles database operations for employees.
 */

namespace HRMS\Repositories;

use HRMS\Core\Database;

class EmployeeRepository extends BaseRepository
{
    protected string $table = 'employees';
    protected bool $tenantScoped = true;
    
    /**
     * Find employee with user details
     */
    public function findWithUser(int $id, int $companyId): ?array
    {
        return Database::fetchOne(
            'SELECT e.*, u.email, u.status as user_status, r.name as role_name
             FROM employees e
             LEFT JOIN users u ON e.user_id = u.id
             LEFT JOIN roles r ON u.role_id = r.id
             WHERE e.id = ? AND e.company_id = ?',
            [$id, $companyId]
        );
    }
    
    /**
     * Find employee by user ID
     */
    public function findByUserId(int $userId, int $companyId): ?array
    {
        return Database::fetchOne(
            'SELECT e.*, u.email, r.name as role_name
             FROM employees e
             LEFT JOIN users u ON e.user_id = u.id
             LEFT JOIN roles r ON u.role_id = r.id
             WHERE e.user_id = ? AND e.company_id = ?',
            [$userId, $companyId]
        );
    }
    
    /**
     * Find employee by employee code
     */
    public function findByCode(string $code, int $companyId): ?array
    {
        return Database::fetchOne(
            'SELECT * FROM employees WHERE employee_code = ? AND company_id = ?',
            [$code, $companyId]
        );
    }
    
    /**
     * Get paginated employees with filters
     */
    public function getPaginated(int $companyId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where = ['e.company_id = ?'];
        $params = [$companyId];
        
        // Apply filters
        if (!empty($filters['department'])) {
            $where[] = 'e.department = ?';
            $params[] = $filters['department'];
        }
        
        if (!empty($filters['status'])) {
            $where[] = 'e.status = ?';
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['search'])) {
            $where[] = '(e.first_name LIKE ? OR e.last_name LIKE ? OR e.employee_code LIKE ? OR u.email LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$search, $search, $search, $search]);
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Get total count
        $countSql = "SELECT COUNT(*) as count FROM employees e 
                     LEFT JOIN users u ON e.user_id = u.id 
                     WHERE {$whereClause}";
        $total = (int) Database::fetchOne($countSql, $params)['count'];
        
        // Get paginated data
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT e.*, u.email, u.status as user_status, r.name as role_name
                FROM employees e
                LEFT JOIN users u ON e.user_id = u.id
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE {$whereClause}
                ORDER BY e.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";
        
        $data = Database::fetchAll($sql, $params);
        
        return [
            'data' => $data,
            'total' => $total
        ];
    }

    
    /**
     * Create employee with optional user account
     */
    public function createWithUser(array $employeeData, ?array $userData = null): int
    {
        Database::beginTransaction();
        
        try {
            $userId = null;
            
            // Create user account if provided
            if ($userData !== null) {
                $userSql = 'INSERT INTO users (company_id, role_id, email, password_hash, status, created_at) 
                            VALUES (?, ?, ?, ?, ?, NOW())';
                Database::execute($userSql, [
                    $employeeData['company_id'],
                    $userData['role_id'],
                    $userData['email'],
                    $userData['password_hash'],
                    $userData['status'] ?? 'active'
                ]);
                $userId = (int) Database::lastInsertId();
            }
            
            // Create employee - filter out null values
            $employeeData['user_id'] = $userId;
            $filteredData = array_filter($employeeData, fn($v) => $v !== null);
            $employeeId = $this->create($filteredData);
            
            Database::commit();
            
            return $employeeId;
            
        } catch (\Exception $e) {
            Database::rollback();
            throw $e;
        }
    }
    
    /**
     * Update employee with user data
     */
    public function updateWithUser(int $id, int $companyId, array $employeeData, ?array $userData = null): bool
    {
        Database::beginTransaction();
        
        try {
            // Get current employee
            $employee = $this->find($id, $companyId);
            if (!$employee) {
                Database::rollback();
                return false;
            }
            
            // Update user if exists and data provided
            if ($employee['user_id'] && $userData !== null) {
                $userSets = [];
                $userParams = [];
                
                foreach ($userData as $key => $value) {
                    $userSets[] = "{$key} = ?";
                    $userParams[] = $value;
                }
                
                if (!empty($userSets)) {
                    $userParams[] = $employee['user_id'];
                    $userSql = 'UPDATE users SET ' . implode(', ', $userSets) . ' WHERE id = ?';
                    Database::execute($userSql, $userParams);
                }
            }
            
            // Update employee
            $result = $this->update($id, $employeeData, $companyId);
            
            Database::commit();
            
            return $result;
            
        } catch (\Exception $e) {
            Database::rollback();
            throw $e;
        }
    }
    
    /**
     * Soft delete employee (set status to inactive)
     */
    public function softDelete(int $id, int $companyId): bool
    {
        Database::beginTransaction();
        
        try {
            $employee = $this->find($id, $companyId);
            if (!$employee) {
                Database::rollback();
                return false;
            }
            
            // Deactivate user account if exists
            if ($employee['user_id']) {
                Database::execute(
                    'UPDATE users SET status = ? WHERE id = ?',
                    ['inactive', $employee['user_id']]
                );
            }
            
            // Set employee status to inactive
            $result = $this->update($id, ['status' => 'inactive'], $companyId);
            
            Database::commit();
            
            return $result;
            
        } catch (\Exception $e) {
            Database::rollback();
            throw $e;
        }
    }
    
    /**
     * Get departments for a company
     */
    public function getDepartments(int $companyId): array
    {
        return Database::fetchAll(
            'SELECT DISTINCT department FROM employees WHERE company_id = ? AND department IS NOT NULL ORDER BY department',
            [$companyId]
        );
    }
    
    /**
     * Generate next employee code
     */
    public function generateEmployeeCode(int $companyId): string
    {
        $result = Database::fetchOne(
            'SELECT MAX(CAST(SUBSTRING(employee_code, 4) AS UNSIGNED)) as max_num 
             FROM employees WHERE company_id = ? AND employee_code LIKE ?',
            [$companyId, 'EMP%']
        );
        
        $nextNum = ($result['max_num'] ?? 0) + 1;
        return 'EMP' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
    }
}
