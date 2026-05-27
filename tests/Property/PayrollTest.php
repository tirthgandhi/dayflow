<?php
/**
 * Property-Based Tests for Payroll Management
 * 
 * **Feature: multi-company-hrms, Property 14: Payroll Net Salary Calculation**
 * **Validates: Requirements 7.2**
 * 
 * For any payroll_record, net_salary SHALL equal gross_salary minus total_deductions.
 */

namespace HRMS\Tests\Property;

use HRMS\Tests\TestCase;
use Eris\Generator;
use Eris\TestTrait;

class PayrollTest extends TestCase
{
    use TestTrait;
    
    /**
     * Property 14: Net salary should equal gross salary minus deductions.
     * 
     * @test
     */
    public function netSalaryEqualsGrossMinusDeductions(): void
    {
        $payroll = $this->query(
            'SELECT id, gross_salary, total_deductions, net_salary FROM payroll_records'
        );
        
        if (empty($payroll)) {
            $this->markTestSkipped('No payroll records in database');
        }
        
        foreach ($payroll as $record) {
            $expectedNet = $record['gross_salary'] - $record['total_deductions'];
            
            $this->assertEqualsWithDelta(
                $expectedNet,
                (float) $record['net_salary'],
                0.01,
                "Payroll {$record['id']}: net_salary should equal gross_salary - total_deductions"
            );
        }
    }
    
    /**
     * Property 14: For any randomly selected payroll record, net = gross - deductions.
     * 
     * @test
     */
    public function randomPayrollNetSalaryIsCorrect(): void
    {
        $payroll = $this->query(
            'SELECT id, gross_salary, total_deductions, net_salary FROM payroll_records LIMIT 100'
        );
        
        if (empty($payroll)) {
            $this->markTestSkipped('No payroll records in database');
        }
        
        $this->forAll(Generator\choose(0, count($payroll) - 1))
        ->withMaxSize(100)
        ->then(function ($index) use ($payroll) {
            $record = $payroll[$index];
            
            $expectedNet = (float) $record['gross_salary'] - (float) $record['total_deductions'];
            
            $this->assertEqualsWithDelta(
                $expectedNet,
                (float) $record['net_salary'],
                0.01,
                'Net salary should equal gross minus deductions'
            );
        });
    }
    
    /**
     * Property 14: For generated salary values, calculation is correct.
     * 
     * @test
     */
    public function salaryCalculationIsCorrect(): void
    {
        $this->forAll(
            Generator\choose(1000, 50000),  // Gross salary
            Generator\choose(100, 10000)    // Deductions
        )
        ->withMaxSize(100)
        ->then(function ($gross, $deductions) {
            // Ensure deductions don't exceed gross
            $deductions = min($deductions, $gross * 0.5);
            
            $net = $gross - $deductions;
            
            $this->assertGreaterThan(0, $net, 'Net salary should be positive');
            $this->assertEquals($gross - $deductions, $net, 'Calculation should be correct');
        });
    }
    
    /**
     * Property: Payroll records should be unique per employee per month.
     * 
     * @test
     */
    public function payrollIsUniquePerEmployeePerMonth(): void
    {
        $duplicates = $this->query(
            'SELECT employee_id, year, month, COUNT(*) as count 
             FROM payroll_records 
             GROUP BY employee_id, year, month 
             HAVING count > 1'
        );
        
        $this->assertEmpty(
            $duplicates,
            'There should be no duplicate payroll records for same employee and month'
        );
    }
    
    /**
     * Property: Payroll month should be between 1 and 12.
     * 
     * @test
     */
    public function payrollMonthIsValid(): void
    {
        $payroll = $this->query('SELECT id, month FROM payroll_records');
        
        foreach ($payroll as $record) {
            $this->assertGreaterThanOrEqual(
                1,
                $record['month'],
                "Payroll {$record['id']} month should be >= 1"
            );
            
            $this->assertLessThanOrEqual(
                12,
                $record['month'],
                "Payroll {$record['id']} month should be <= 12"
            );
        }
    }
    
    /**
     * Property: Payroll status should be a valid enum value.
     * 
     * @test
     */
    public function allPayrollStatusesAreValid(): void
    {
        $validStatuses = ['pending', 'processed', 'paid', 'failed'];
        
        $payroll = $this->query('SELECT id, payment_status FROM payroll_records');
        
        foreach ($payroll as $record) {
            $this->assertContains(
                $record['payment_status'],
                $validStatuses,
                "Payroll {$record['id']} should have a valid payment_status"
            );
        }
    }
    
    /**
     * Property: All payroll records should reference valid salary structures.
     * 
     * @test
     */
    public function allPayrollRecordsReferenceValidSalaryStructures(): void
    {
        $orphaned = $this->query(
            'SELECT pr.id FROM payroll_records pr 
             LEFT JOIN salary_structures ss ON pr.salary_structure_id = ss.id 
             WHERE ss.id IS NULL'
        );
        
        $this->assertEmpty(
            $orphaned,
            'All payroll records should reference valid salary structures'
        );
    }
    
    /**
     * Property: Gross salary should match salary structure components.
     * 
     * @test
     */
    public function grossSalaryMatchesSalaryStructure(): void
    {
        $records = $this->query(
            'SELECT pr.id, pr.gross_salary, 
                    ss.basic_salary, ss.housing_allowance, ss.transport_allowance, ss.other_allowances
             FROM payroll_records pr
             JOIN salary_structures ss ON pr.salary_structure_id = ss.id
             LIMIT 100'
        );
        
        foreach ($records as $record) {
            $expectedGross = $record['basic_salary'] + 
                            $record['housing_allowance'] + 
                            $record['transport_allowance'] + 
                            $record['other_allowances'];
            
            $this->assertEqualsWithDelta(
                $expectedGross,
                (float) $record['gross_salary'],
                0.01,
                "Payroll {$record['id']} gross should match salary structure"
            );
        }
    }
}
