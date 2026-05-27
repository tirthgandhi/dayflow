<?php
/**
 * Payroll Repository
 * 
 * Handles database operations for payroll records and salary structures.
 */

namespace HRMS\Repositories;

use HRMS\Core\Database;

class PayrollRepository extends BaseRepository
{
    protected string $table = 'payroll_records';
    protected bool $tenantScoped = true;
    
    /**
     * Get salary structure for employee
     */
    public function getSalaryStructure(int $employeeId, int $companyId): ?array
    {
        return Database::fetchOne(
            'SELECT * FROM salary_structures WHERE employee_id = ? AND company_id = ?',
            [$employeeId, $companyId]
        );
    }
    
    /**
     * Get paginated payroll records with filters
     */
    public function getPaginated(int $companyId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where = ['pr.company_id = ?'];
        $params = [$companyId];
        
        if (!empty($filters['employee_id'])) {
            $where[] = 'pr.employee_id = ?';
            $params[] = $filters['employee_id'];
        }
        
        if (!empty($filters['month'])) {
            // month format: 2026-01
            $parts = explode('-', $filters['month']);
            if (count($parts) === 2) {
                $where[] = 'pr.year = ? AND pr.month = ?';
                $params[] = (int) $parts[0];
                $params[] = (int) $parts[1];
            }
        }
        
        if (!empty($filters['status'])) {
            $where[] = 'pr.payment_status = ?';
            $params[] = $filters['status'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Get total count
        $countSql = "SELECT COUNT(*) as count FROM payroll_records pr WHERE {$whereClause}";
        $total = (int) Database::fetchOne($countSql, $params)['count'];
        
        // Get paginated data
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT pr.*, e.first_name, e.last_name, e.employee_code,
                       pr.total_deductions as deductions, pr.payment_status as status,
                       ss.basic_salary, 
                       COALESCE(ss.housing_allowance, 0) + COALESCE(ss.transport_allowance, 0) + COALESCE(ss.other_allowances, 0) as allowances
                FROM payroll_records pr
                JOIN employees e ON pr.employee_id = e.id
                LEFT JOIN salary_structures ss ON pr.salary_structure_id = ss.id
                WHERE {$whereClause}
                ORDER BY pr.year DESC, pr.month DESC, e.last_name ASC
                LIMIT {$perPage} OFFSET {$offset}";
        
        $data = Database::fetchAll($sql, $params);
        
        // Add pay_period_start for frontend compatibility
        foreach ($data as &$row) {
            $row['pay_period_start'] = sprintf('%04d-%02d-01', $row['year'], $row['month']);
            $row['pay_period_end'] = date('Y-m-t', strtotime($row['pay_period_start']));
        }
        
        return [
            'data' => $data,
            'total' => $total
        ];
    }
    
    /**
     * Get employee's payroll records
     */
    public function getByEmployee(int $employeeId, int $companyId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $filters['employee_id'] = $employeeId;
        return $this->getPaginated($companyId, $filters, $page, $perPage);
    }
    
    /**
     * Get payroll record with details
     */
    public function findWithDetails(int $id, int $companyId): ?array
    {
        return Database::fetchOne(
            "SELECT pr.*, e.first_name, e.last_name, e.employee_code, e.department, e.designation,
                    pr.total_deductions as deductions, pr.payment_status as status
             FROM payroll_records pr
             JOIN employees e ON pr.employee_id = e.id
             WHERE pr.id = ? AND pr.company_id = ?",
            [$id, $companyId]
        );
    }
    
    /**
     * Check if payroll exists for employee and period
     */
    public function existsForPeriod(int $employeeId, int $companyId, int $year, int $month): bool
    {
        $result = Database::fetchOne(
            'SELECT COUNT(*) as count FROM payroll_records 
             WHERE employee_id = ? AND company_id = ? AND year = ? AND month = ?',
            [$employeeId, $companyId, $year, $month]
        );
        return (int) $result['count'] > 0;
    }
    
    /**
     * Get all employees with salary structures for processing
     */
    public function getEmployeesForPayroll(int $companyId): array
    {
        return Database::fetchAll(
            "SELECT e.id as employee_id, e.first_name, e.last_name, e.employee_code,
                    ss.id as salary_structure_id, ss.basic_salary, ss.housing_allowance, 
                    ss.transport_allowance, ss.other_allowances, ss.tax_deduction, 
                    ss.insurance_deduction, ss.other_deductions
             FROM employees e
             JOIN salary_structures ss ON e.id = ss.employee_id AND ss.is_current = 1
             WHERE e.company_id = ? AND e.status = 'active'",
            [$companyId]
        );
    }
    
    /**
     * Create payroll record
     */
    public function createPayrollRecord(array $data): int
    {
        $sql = 'INSERT INTO payroll_records 
                (company_id, employee_id, salary_structure_id, year, month, 
                 gross_salary, total_deductions, net_salary, payment_status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())';
        
        Database::execute($sql, [
            $data['company_id'],
            $data['employee_id'],
            $data['salary_structure_id'],
            $data['year'],
            $data['month'],
            $data['gross_salary'],
            $data['total_deductions'],
            $data['net_salary'],
            $data['status'] ?? 'pending'
        ]);
        
        return (int) Database::lastInsertId();
    }
    
    /**
     * Get payroll summary for a period
     */
    public function getSummary(int $companyId, int $year, int $month): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_records,
                    SUM(gross_salary) as total_gross,
                    SUM(total_deductions) as total_deductions,
                    SUM(net_salary) as total_net,
                    SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) as paid_count,
                    SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending_count
                FROM payroll_records 
                WHERE company_id = ? AND year = ? AND month = ?";
        
        return Database::fetchOne($sql, [$companyId, $year, $month]) ?? [
            'total_records' => 0,
            'total_gross' => 0,
            'total_deductions' => 0,
            'total_net' => 0,
            'paid_count' => 0,
            'pending_count' => 0
        ];
    }
}
