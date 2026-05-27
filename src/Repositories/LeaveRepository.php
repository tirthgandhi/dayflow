<?php
/**
 * Leave Repository
 * 
 * Handles database operations for leave types and requests.
 */

namespace HRMS\Repositories;

use HRMS\Core\Database;

class LeaveRepository extends BaseRepository
{
    protected string $table = 'leave_requests';
    protected bool $tenantScoped = true;
    
    /**
     * Get leave types for a company
     */
    public function getLeaveTypes(int $companyId): array
    {
        return Database::fetchAll(
            'SELECT * FROM leave_types WHERE company_id = ? ORDER BY name',
            [$companyId]
        );
    }
    
    /**
     * Get leave type by ID
     */
    public function getLeaveType(int $id, int $companyId): ?array
    {
        return Database::fetchOne(
            'SELECT * FROM leave_types WHERE id = ? AND company_id = ?',
            [$id, $companyId]
        );
    }
    
    /**
     * Get paginated leave requests with filters
     */
    public function getPaginated(int $companyId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where = ['lr.company_id = ?'];
        $params = [$companyId];
        
        if (!empty($filters['employee_id'])) {
            $where[] = 'lr.employee_id = ?';
            $params[] = $filters['employee_id'];
        }
        
        if (!empty($filters['status'])) {
            $where[] = 'lr.status = ?';
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['leave_type_id'])) {
            $where[] = 'lr.leave_type_id = ?';
            $params[] = $filters['leave_type_id'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Get total count
        $countSql = "SELECT COUNT(*) as count FROM leave_requests lr WHERE {$whereClause}";
        $total = (int) Database::fetchOne($countSql, $params)['count'];
        
        // Get paginated data
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT lr.*, 
                       e.first_name, e.last_name, e.employee_code,
                       lt.name as leave_type_name,
                       CONCAT(a.first_name, ' ', a.last_name) as approver_name
                FROM leave_requests lr
                JOIN employees e ON lr.employee_id = e.id
                JOIN leave_types lt ON lr.leave_type_id = lt.id
                LEFT JOIN employees a ON lr.approver_id = a.id
                WHERE {$whereClause}
                ORDER BY lr.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";
        
        $data = Database::fetchAll($sql, $params);
        
        return [
            'data' => $data,
            'total' => $total
        ];
    }
    
    /**
     * Get employee's leave requests
     */
    public function getByEmployee(int $employeeId, int $companyId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $filters['employee_id'] = $employeeId;
        return $this->getPaginated($companyId, $filters, $page, $perPage);
    }
    
    /**
     * Get leave request with details
     */
    public function findWithDetails(int $id, int $companyId): ?array
    {
        return Database::fetchOne(
            "SELECT lr.*, 
                    e.first_name, e.last_name, e.employee_code,
                    lt.name as leave_type_name, lt.annual_allocation,
                    CONCAT(a.first_name, ' ', a.last_name) as approver_name
             FROM leave_requests lr
             JOIN employees e ON lr.employee_id = e.id
             JOIN leave_types lt ON lr.leave_type_id = lt.id
             LEFT JOIN employees a ON lr.approver_id = a.id
             WHERE lr.id = ? AND lr.company_id = ?",
            [$id, $companyId]
        );
    }
    
    /**
     * Calculate leave balance for employee
     */
    public function getBalance(int $employeeId, int $companyId, int $year = null): array
    {
        $year = $year ?? (int) date('Y');
        $startDate = "{$year}-01-01";
        $endDate = "{$year}-12-31";
        
        $sql = "SELECT 
                    lt.id as leave_type_id,
                    lt.name as leave_type_name,
                    lt.annual_allocation,
                    COALESCE(SUM(CASE WHEN lr.status = 'approved' THEN lr.total_days ELSE 0 END), 0) as used_days
                FROM leave_types lt
                LEFT JOIN leave_requests lr ON lt.id = lr.leave_type_id 
                    AND lr.employee_id = ? 
                    AND lr.start_date >= ? 
                    AND lr.end_date <= ?
                WHERE lt.company_id = ?
                GROUP BY lt.id, lt.name, lt.annual_allocation
                ORDER BY lt.name";
        
        $results = Database::fetchAll($sql, [$employeeId, $startDate, $endDate, $companyId]);
        
        return array_map(function($row) {
            $row['remaining'] = $row['annual_allocation'] - $row['used_days'];
            $row['total_days'] = $row['annual_allocation'];
            return $row;
        }, $results);
    }
    
    /**
     * Check for overlapping leave requests
     */
    public function hasOverlap(int $employeeId, int $companyId, string $startDate, string $endDate, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM leave_requests 
                WHERE employee_id = ? AND company_id = ? 
                AND status IN ('pending', 'approved')
                AND ((start_date <= ? AND end_date >= ?) OR (start_date <= ? AND end_date >= ?))";
        $params = [$employeeId, $companyId, $endDate, $startDate, $startDate, $endDate];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = Database::fetchOne($sql, $params);
        return (int) $result['count'] > 0;
    }
    
    /**
     * Approve leave request
     */
    public function approve(int $id, int $companyId, int $approverId): bool
    {
        return Database::execute(
            'UPDATE leave_requests SET status = ?, approver_id = ?, approval_date = NOW(), updated_at = NOW() 
             WHERE id = ? AND company_id = ?',
            ['approved', $approverId, $id, $companyId]
        ) > 0;
    }
    
    /**
     * Reject leave request
     */
    public function reject(int $id, int $companyId, int $approverId, ?string $reason = null): bool
    {
        return Database::execute(
            'UPDATE leave_requests SET status = ?, approver_id = ?, approval_date = NOW(), rejection_reason = ?, updated_at = NOW() 
             WHERE id = ? AND company_id = ?',
            ['rejected', $approverId, $reason, $id, $companyId]
        ) > 0;
    }
}
