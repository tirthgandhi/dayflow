<?php
/**
 * Property-Based Tests for Company Registration
 * 
 * **Feature: multi-company-hrms, Property 3: Company Registration Uniqueness**
 * **Validates: Requirements 2.3**
 * 
 * For any two company records in the database, their registration_number values SHALL be different.
 */

namespace HRMS\Tests\Property;

use HRMS\Tests\TestCase;
use Eris\Generator;
use Eris\TestTrait;
use PDOException;

class CompanyTest extends TestCase
{
    use TestTrait;
    
    /**
     * Property: All company registration numbers should be unique.
     * 
     * @test
     */
    public function allCompanyRegistrationNumbersAreUnique(): void
    {
        $companies = $this->query('SELECT id, registration_number FROM companies');
        
        if (count($companies) < 2) {
            $this->markTestSkipped('Need at least 2 companies to test uniqueness');
        }
        
        $registrationNumbers = array_column($companies, 'registration_number');
        $uniqueNumbers = array_unique($registrationNumbers);
        
        $this->assertCount(
            count($registrationNumbers),
            $uniqueNumbers,
            'All company registration numbers should be unique'
        );
    }
    
    /**
     * Property: For any two randomly selected companies, their registration numbers differ.
     * 
     * @test
     */
    public function anyTwoCompaniesHaveDifferentRegistrationNumbers(): void
    {
        $companies = $this->query('SELECT id, registration_number FROM companies');
        
        if (count($companies) < 2) {
            $this->markTestSkipped('Need at least 2 companies to test');
        }
        
        $this->forAll(
            Generator\choose(0, count($companies) - 1),
            Generator\choose(0, count($companies) - 1)
        )
        ->withMaxSize(100)
        ->then(function ($index1, $index2) use ($companies) {
            if ($index1 === $index2) {
                return; // Same company, skip
            }
            
            $company1 = $companies[$index1];
            $company2 = $companies[$index2];
            
            $this->assertNotEquals(
                $company1['registration_number'],
                $company2['registration_number'],
                "Companies {$company1['id']} and {$company2['id']} should have different registration numbers"
            );
        });
    }
    
    /**
     * Property: Database should reject duplicate registration numbers.
     * 
     * @test
     */
    public function databaseRejectsDuplicateRegistrationNumbers(): void
    {
        $this->beginTransaction();
        
        try {
            // Get an existing registration number
            $existing = $this->queryOne('SELECT registration_number FROM companies LIMIT 1');
            
            if (!$existing) {
                $this->markTestSkipped('No companies in database');
            }
            
            // Try to insert a company with duplicate registration number
            $this->expectException(PDOException::class);
            
            $this->execute(
                'INSERT INTO companies (name, registration_number, email, status) VALUES (?, ?, ?, ?)',
                ['Test Company', $existing['registration_number'], 'test@test.com', 'active']
            );
        } finally {
            $this->rollback();
        }
    }
    
    /**
     * Property: For any randomly generated registration number, it should be insertable
     * only if it doesn't already exist.
     * 
     * @test
     */
    public function uniqueRegistrationNumbersCanBeInserted(): void
    {
        $this->forAll(Generator\string())
        ->withMaxSize(50)
        ->then(function ($randomSuffix) {
            $this->beginTransaction();
            
            try {
                $regNumber = 'TEST-' . substr(md5($randomSuffix . microtime()), 0, 20);
                
                // Check if it exists
                $exists = $this->queryOne(
                    'SELECT 1 FROM companies WHERE registration_number = ?',
                    [$regNumber]
                );
                
                if (!$exists) {
                    // Should be able to insert
                    $this->execute(
                        'INSERT INTO companies (name, registration_number, email, status) VALUES (?, ?, ?, ?)',
                        ['Test Company ' . $regNumber, $regNumber, 'test' . $regNumber . '@test.com', 'active']
                    );
                    
                    // Verify it was inserted
                    $inserted = $this->queryOne(
                        'SELECT 1 FROM companies WHERE registration_number = ?',
                        [$regNumber]
                    );
                    
                    $this->assertNotNull($inserted, 'Unique registration number should be insertable');
                }
            } finally {
                $this->rollback();
            }
        });
    }
}
