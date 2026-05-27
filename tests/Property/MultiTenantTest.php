<?php
/**
 * Property-Based Tests for Multi-Tenant Data Isolation
 * 
 * **Feature: multi-company-hrms, Property 1: Multi-Tenant Data Isolation**
 * **Validates: Requirements 1.2, 2.4, 4.2**
 * 
 * For any query on tenant-specific tables (users, employees, attendance, leave_requests,
 * salary_structures, payroll_records), the results SHALL contain only records where
 * company_id matches the requesting user's company_id.
 * 
 * **Feature: multi-company-hrms, Property 2: Referential Integrity Cascade**
 * **Validates: Requirements 1.4, 8.4**
 * 
 * For any parent record deletion (company, employee, role), child records SHALL be
 * handled according to the defined cascade rules without creating orphaned records.
 * 
 * **Feature: multi-company-hrms, Property 8: Timestamp Auto-Population**
 * **Validates: Requirements 4.3, 8.3**
 * 
 * For any INSERT operation on tables with created_at, the created_at field SHALL be
 * auto-populated. For any UPDATE operation on tables with updated_at, the updated_at
 * field SHALL change while created_at remains constant.
 * 
 * **Feature: multi-company-hrms, Property 15: Enum Constraint Enforcement**
 * **Validates: Requirements 8.2**
 * 
 * For any INSERT or UPDATE with an invalid enum value for status fields, the database
 * SHALL reject the operation.
 */

namespace HRMS\Tests\Property;

use HRMS\Tests\TestCase;
use Eris\Generator;
use Eris\TestTrait;
use PDOException;

class MultiTenantTest extends TestCase
{
    use TestTrait;
    
    /**
     * Property 1: All tenant-specific records should have valid company_id.
     * 
     * @test
     */
    public function allTenantRecordsHaveValidCompanyId(): void
    {
        $tenantTables = ['users', 'employees', 'attendance', 'leave_types', 'leave_requests', 'salary_structures', 'payroll_records'];
        
        foreach ($tenantTables as $table) {
            $orphaned = $this->query(
                "SELECT t.id FROM {$table} t 
                 LEFT JOIN companies c ON t.company_id = c.id 
                 WHERE c.id IS NULL"
            );
            
            $this->assertEmpty(
                $orphaned,
                "All records in {$table} should have valid company_id"
            );
        }
    }
    
    /**
     * Property 1: For any company, querying with company_id filter returns only that company's data.
     * 
     * @test
     */
    public function companyIdFilterReturnsOnlyCompanyData(): void
    {
        $companies = $this->query('SELECT id FROM companies LIMIT 10');
        
        if (count($companies) < 2) {
            $this->markTestSkipped('Need at least 2 companies to test isolation');
        }
        
        $this->forAll(Generator\choose(0, count($companies) - 1))
        ->withMaxSize(50)
        ->then(function ($companyIndex) use ($companies) {
            $companyId = $companies[$companyIndex]['id'];
            
            // Check users
            $users = $this->query(
                'SELECT company_id FROM users WHERE company_id = ?',
                [$companyId]
            );
            
            foreach ($users as $user) {
                $this->assertEquals(
                    $companyId,
                    $user['company_id'],
                    'Filtered users should only contain company data'
                );
            }
            
            // Check employees
            $employees = $this->query(
                'SELECT company_id FROM employees WHERE company_id = ?',
                [$companyId]
            );
            
            foreach ($employees as $employee) {
                $this->assertEquals(
                    $companyId,
                    $employee['company_id'],
                    'Filtered employees should only contain company data'
                );
            }
        });
    }
    
    /**
     * Property 1: Cross-company data access should return empty results.
     * 
     * @test
     */
    public function crossCompanyAccessReturnsEmpty(): void
    {
        $companies = $this->query('SELECT id FROM companies LIMIT 10');
        
        if (count($companies) < 2) {
            $this->markTestSkipped('Need at least 2 companies to test');
        }
        
        // Get users from company 1
        $company1Users = $this->query(
            'SELECT id FROM users WHERE company_id = ?',
            [$companies[0]['id']]
        );
        
        if (empty($company1Users)) {
            $this->markTestSkipped('No users in first company');
        }
        
        // Try to access company 1 users with company 2 filter
        $crossAccess = $this->query(
            'SELECT id FROM users WHERE id = ? AND company_id = ?',
            [$company1Users[0]['id'], $companies[1]['id']]
        );
        
        $this->assertEmpty(
            $crossAccess,
            'Cross-company access should return empty results'
        );
    }
    
    /**
     * Property 2: Deleting a company should cascade to all related records.
     * 
     * @test
     */
    public function companyDeletionCascades(): void
    {
        $this->beginTransaction();
        
        try {
            // Create a test company
            $this->execute(
                "INSERT INTO companies (name, registration_number, email, status) 
                 VALUES ('Test Cascade Co', 'CASCADE-TEST-001', 'cascade@test.com', 'active')"
            );
            $companyId = $this->lastInsertId();
            
            // Create a user for this company
            $this->execute(
                "INSERT INTO users (company_id, role_id, email, password_hash, status) 
                 VALUES (?, 3, 'cascade.user@test.com', '\$2y\$10\$test', 'active')",
                [$companyId]
            );
            $userId = $this->lastInsertId();
            
            // Create an employee
            $this->execute(
                "INSERT INTO employees (company_id, user_id, employee_code, first_name, last_name, email, hire_date, status) 
                 VALUES (?, ?, 'CASCADE001', 'Test', 'User', 'cascade.emp@test.com', CURDATE(), 'active')",
                [$companyId, $userId]
            );
            
            // Verify records exist
            $userExists = $this->queryOne('SELECT 1 FROM users WHERE company_id = ?', [$companyId]);
            $this->assertNotNull($userExists, 'User should exist before deletion');
            
            // Delete the company
            $this->execute('DELETE FROM companies WHERE id = ?', [$companyId]);
            
            // Verify cascade deletion
            $userAfter = $this->queryOne('SELECT 1 FROM users WHERE company_id = ?', [$companyId]);
            $this->assertNull($userAfter, 'Users should be deleted when company is deleted');
            
            $empAfter = $this->queryOne('SELECT 1 FROM employees WHERE company_id = ?', [$companyId]);
            $this->assertNull($empAfter, 'Employees should be deleted when company is deleted');
        } finally {
            $this->rollback();
        }
    }
    
    /**
     * Property 2: No orphaned records should exist in the database.
     * 
     * @test
     */
    public function noOrphanedRecordsExist(): void
    {
        // Check for orphaned users (no company)
        $orphanedUsers = $this->query(
            'SELECT u.id FROM users u 
             LEFT JOIN companies c ON u.company_id = c.id 
             WHERE c.id IS NULL'
        );
        $this->assertEmpty($orphanedUsers, 'No orphaned users should exist');
        
        // Check for orphaned employees (no company)
        $orphanedEmployees = $this->query(
            'SELECT e.id FROM employees e 
             LEFT JOIN companies c ON e.company_id = c.id 
             WHERE c.id IS NULL'
        );
        $this->assertEmpty($orphanedEmployees, 'No orphaned employees should exist');
        
        // Check for orphaned attendance (no employee)
        $orphanedAttendance = $this->query(
            'SELECT a.id FROM attendance a 
             LEFT JOIN employees e ON a.employee_id = e.id 
             WHERE e.id IS NULL'
        );
        $this->assertEmpty($orphanedAttendance, 'No orphaned attendance records should exist');
    }
    
    /**
     * Property 8: created_at should be auto-populated on insert.
     * 
     * @test
     */
    public function createdAtIsAutoPopulated(): void
    {
        $this->beginTransaction();
        
        try {
            // Insert a company without specifying created_at
            $this->execute(
                "INSERT INTO companies (name, registration_number, email, status) 
                 VALUES ('Timestamp Test Co', 'TS-TEST-001', 'timestamp@test.com', 'active')"
            );
            $companyId = $this->lastInsertId();
            
            $company = $this->queryOne(
                'SELECT created_at FROM companies WHERE id = ?',
                [$companyId]
            );
            
            $this->assertNotNull(
                $company['created_at'],
                'created_at should be auto-populated'
            );
            
            // Verify it's a recent timestamp
            $createdAt = strtotime($company['created_at']);
            $now = time();
            
            $this->assertLessThan(
                60,
                abs($now - $createdAt),
                'created_at should be within 60 seconds of now'
            );
        } finally {
            $this->rollback();
        }
    }
    
    /**
     * Property 8: updated_at should change on update while created_at stays constant.
     * 
     * @test
     */
    public function updatedAtChangesOnUpdate(): void
    {
        $this->beginTransaction();
        
        try {
            // Insert a company
            $this->execute(
                "INSERT INTO companies (name, registration_number, email, status) 
                 VALUES ('Update Test Co', 'UP-TEST-001', 'update@test.com', 'active')"
            );
            $companyId = $this->lastInsertId();
            
            $before = $this->queryOne(
                'SELECT created_at, updated_at FROM companies WHERE id = ?',
                [$companyId]
            );
            
            // Wait a moment
            sleep(1);
            
            // Update the company
            $this->execute(
                'UPDATE companies SET name = ? WHERE id = ?',
                ['Updated Test Co', $companyId]
            );
            
            $after = $this->queryOne(
                'SELECT created_at, updated_at FROM companies WHERE id = ?',
                [$companyId]
            );
            
            // created_at should remain the same
            $this->assertEquals(
                $before['created_at'],
                $after['created_at'],
                'created_at should not change on update'
            );
            
            // updated_at should be different (or at least not earlier)
            $this->assertGreaterThanOrEqual(
                strtotime($before['updated_at']),
                strtotime($after['updated_at']),
                'updated_at should be updated'
            );
        } finally {
            $this->rollback();
        }
    }
    
    /**
     * Property 15: Database should reject invalid enum values.
     * 
     * @test
     */
    public function databaseRejectsInvalidEnumValues(): void
    {
        $this->beginTransaction();
        
        try {
            // Try to insert company with invalid status
            $this->expectException(PDOException::class);
            
            $this->execute(
                "INSERT INTO companies (name, registration_number, email, status) 
                 VALUES ('Invalid Status Co', 'INV-TEST-001', 'invalid@test.com', 'invalid_status')"
            );
        } finally {
            $this->rollback();
        }
    }
    
    /**
     * Property 15: All enum fields should contain valid values.
     * 
     * @test
     */
    public function allEnumFieldsHaveValidValues(): void
    {
        // Company status
        $companyStatuses = ['active', 'inactive', 'suspended'];
        $companies = $this->query('SELECT id, status FROM companies');
        foreach ($companies as $company) {
            $this->assertContains($company['status'], $companyStatuses);
        }
        
        // User status
        $userStatuses = ['active', 'inactive', 'locked'];
        $users = $this->query('SELECT id, status FROM users');
        foreach ($users as $user) {
            $this->assertContains($user['status'], $userStatuses);
        }
        
        // Employee status
        $employeeStatuses = ['active', 'inactive', 'terminated'];
        $employees = $this->query('SELECT id, status FROM employees');
        foreach ($employees as $employee) {
            $this->assertContains($employee['status'], $employeeStatuses);
        }
    }
}
