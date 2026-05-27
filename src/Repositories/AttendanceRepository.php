<?php
/**
 * Attendance Repository
 * 
 * Handles database operations for attendance records.
 */

namespace HRMS\Repositories;

use HRMS\Core\Database;

class AttendanceRepository extends BaseRepository
{
    protected string $table = 'attendance';
    protected bool $tenantScoped = true;
    
    /**
     * Find attendance record for employee on a specific date
     */
    public function findByEmployeeAndDate(int $employeeId, string $date, int $companyId): ?array
    {
        return Database::fetchOne(
            'SELECT * FROM attendance WHERE employee_id = ? AND attendance_date = ? AND company_id = ?',
            [$employeeId, $date, $companyId]
        );
    }
    
    /**
     * Get paginated attendance records with filters
     */
    public function getPaginated(int $companyId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where = ['a.company_id = ?'];
        $params = [$companyId];
        
        if (!empty($filters['employee_id'])) {
            $where[] = 'a.employee_id = ?';
            $params[] = $filters['employee_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = 'a.attendance_date >= ?';
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = 'a.attendance_date <= ?';
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['status'])) {
            $where[] = 'a.status = ?';
            $params[] = $filters['status'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Get total count
        $countSql = "SELECT COUNT(*) as count FROM attendance a WHERE {$whereClause}";
        $total = (int) Database::fetchOne($countSql, $params)['count'];
        
        // Get paginated data
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT a.*, e.first_name, e.last_name, e.employee_code
                FROM attendance a
                JOIN employees e ON a.employee_id = e.id
                WHERE {$whereClause}
                ORDER BY a.attendance_date DESC, a.clock_in_time DESC
                LIMIT {$perPage} OFFSET {$offset}";
        
        $data = Database::fetchAll($sql, $params);
        
        return [
            'data' => $data,
            'total' => $total
        ];
    }
    
    /**
     * Get employee's attendance records
     */
    public function getByEmployee(int $employeeId, int $companyId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $filters['employee_id'] = $employeeId;
        return $this->getPaginated($companyId, $filters, $page, $perPage);
    }
    
    /**
     * Clock in employee
     */
    public function clockIn(int $employeeId, int $companyId, string $date, string $time): int
    {
        $sql = 'INSERT INTO attendance (company_id, employee_id, attendance_date, clock_in_time, status, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())';
        
        Database::execute($sql, [$companyId, $employeeId, $date, $time, 'present']);
        
        return (int) Database::lastInsertId();
    }
    
    /**
     * Clock out employee
     */
    public function clockOut(int $id, int $companyId, string $time, float $totalHours): bool
    {
        $sql = 'UPDATE attendance SET clock_out_time = ?, total_hours = ?, updated_at = NOW() 
                WHERE id = ? AND company_id = ?';
        
        return Database::execute($sql, [$time, $totalHours, $id, $companyId]) > 0;
    }
    
    /**
     * Get today's attendance for employee
     */
    public function getTodayAttendance(int $employeeId, int $companyId): ?array
    {
        return $this->findByEmployeeAndDate($employeeId, date('Y-m-d'), $companyId);
    }
    
    /**
     * Get attendance summary for employee
     */
    public function getSummary(int $employeeId, int $companyId, string $month): array
    {
        $startDate = $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));
        
        $sql = "SELECT 
                    COUNT(*) as total_days,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                    SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days,
                    SUM(COALESCE(total_hours, 0)) as total_hours
                FROM attendance 
                WHERE employee_id = ? AND company_id = ? AND attendance_date BETWEEN ? AND ?";
        
        return Database::fetchOne($sql, [$employeeId, $companyId, $startDate, $endDate]) ?? [
            'total_days' => 0,
            'present_days' => 0,
            'absent_days' => 0,
            'late_days' => 0,
            'total_hours' => 0
        ];
    }
}
