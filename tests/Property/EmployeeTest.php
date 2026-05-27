<?php
/**
 * Property-Based Tests for Employee Management
 * 
 * **Feature: multi-company-hrms, Property 9: Employee-User One-to-One Relationship**
 * **Validates: Requirements 4.4**
 * 
 * For any employee record with a non-null user_id, there SHALL be exactly one user record
 * with that id, and no other employee SHALL reference the same user_id.
 */

namespace HRMS\Tests\Property;

use HRMS\Tests\TestCase;
use Eris\Generator;
use Eris\TestTrait;
use PDOException;

class EmployeeTest extends TestCase
{
    use TestTrait;
    
    /**
     * Property 9: Each user_id should be referenced by at most one employee.
     * 
     * @test
     */
    public function userIdIsUniqueAcrossEmployees(): void
    {
        $employees = $this->query(
            'SELECT id, user_id FROM employees WHERE user_id IS NOT NULL'
        );
        
        if (count($employees) < 2) {
            $this->markTestSkipped('Need at least 2 employees with user_id to test');
        }
        
        $userIds = array_column($employees, 'user_id');
        $uniqueUserIds = array_unique($userIds);
        
        $this->assertCount(
            count($userIds),
            $uniqueUserIds,
            'Each user_id should be referenced by at most one employee'
        );
    }
    
    /**
     * Property 9: For any employee with user_id, that user should exist.
     * 
     * @test
     */
    public function employeeUserIdReferencesValidUser(): void
    {
        $employees = $this->query(
            'SELECT id, user_id FROM employees WHERE user_id IS NOT NULL'
        );
        
        if (empty($employees)) {
            $this->markTestSkipped('No employees with user_id in database');
        }
        
        $this->forAll(Generator\choose(0, count($employees) - 1))
        ->withMaxSize(100)
        ->then(function ($empIndex) use ($employees) {
            $employee = $employees[$empIndex];
            
            $user = $this->queryOne(
                'SELECT id FROM users WHERE id = ?',
                [$employee['user_id']]
            );
            
            $this->assertNotNull(
                $user,
                "Employee {$employee['id']} should reference a valid user"
            );
        });
    }
    
    /**
     * Property 9: Database should reject duplicate user_id assignments.
     * 
     * @test
     */
    public function databaseRejectsDuplicateUserIdAssignment(): void
    {
        $this->beginTransaction();
        
        try {
            // Get an employee with a user_id
            $existing = $this->queryOne(
                'SELECT user_id, company_id FROM employees WHERE user_id IS NOT NULL LIMIT 1'
            );
            
            if (!$existing) {
                $this->markTestSkipped('No employees with user_id in database');
            }
            
            // Try to insert another employee with the same user_id
            $this->expectException(PDOException::class);
            
            $this->execute(
                'INSERT INTO employees (company_id, user_id, employee_code, first_name, last_name, email, hire_date, status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $existing['company_id'],
                    $existing['user_id'],
                    'TEST-DUP-' . time(),
                    'Test',
                    'Duplicate',
                    'test.duplicate@test.com',
                    date('Y-m-d'),
                    'active'
                ]
            );
        } finally {
            $this->rollback();
        }
    }
    
    /**
     * Property: Employee code should be unique within a company.
     * 
     * @test
     */
    public function employeeCodeIsUniqueWithinCompany(): void
    {
        $companies = $this->query('SELECT id FROM companies');
        
        foreach ($companies as $company) {
            $employees = $this->query(
                'SELECT employee_code FROM employees WHERE company_id = ?',
                [$company['id']]
            );
            
            if (count($employees) < 2) {
                continue;
            }
            
            $codes = array_column($employees, 'employee_code');
            $uniqueCodes = array_unique($codes);
            
            $this->assertCount(
                count($codes),
                $uniqueCodes,
                "Employee codes should be unique within company {$company['id']}"
            );
        }
    }
    
    /**
     * Property: All employees should have valid company_id.
     * 
     * @test
     */
    public function allEmployeesHaveValidCompanyId(): void
    {
        $employees = $this->query('SELECT id, company_id FROM employees');
        
        $this->forAll(Generator\choose(0, max(0, count($employees) - 1)))
        ->withMaxSize(100)
        ->then(function ($empIndex) use ($employees) {
            if (empty($employees)) {
                return;
            }
            
            $employee = $employees[$empIndex];
            
            $this->assertNotNull(
                $employee['company_id'],
                "Employee {$employee['id']} should have a company_id"
            );
            
            $company = $this->queryOne(
                'SELECT id FROM companies WHERE id = ?',
                [$employee['company_id']]
            );
            
            $this->assertNotNull(
                $company,
                "Employee {$employee['id']} should reference a valid company"
            );
        });
    }
    
    /**
     * Property: Employee status should be a valid enum value.
     * 
     * @test
     */
    public function allEmployeeStatusesAreValid(): void
    {
        $validStatuses = ['active', 'inactive', 'terminated'];
        
        $employees = $this->query('SELECT id, status FROM employees');
        
        foreach ($employees as $employee) {
            $this->assertContains(
                $employee['status'],
                $validStatuses,
                "Employee {$employee['id']} should have a valid status"
            );
        }
    }
}
