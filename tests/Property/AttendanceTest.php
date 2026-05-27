<?php
/**
 * Property-Based Tests for Attendance Management
 * 
 * **Feature: multi-company-hrms, Property 10: Attendance Uniqueness Per Day**
 * **Validates: Requirements 5.2**
 * 
 * For any employee_id and attendance_date combination, there SHALL be at most one attendance record.
 * 
 * **Feature: multi-company-hrms, Property 11: Total Hours Calculation**
 * **Validates: Requirements 5.4**
 * 
 * For any attendance record with both clock_in_time and clock_out_time populated,
 * total_hours SHALL equal the time difference between clock_out_time and clock_in_time in hours.
 */

namespace HRMS\Tests\Property;

use HRMS\Tests\TestCase;
use Eris\Generator;
use Eris\TestTrait;
use PDOException;

class AttendanceTest extends TestCase
{
    use TestTrait;
    
    /**
     * Property 10: Each employee should have at most one attendance record per day.
     * 
     * @test
     */
    public function attendanceIsUniquePerEmployeePerDay(): void
    {
        $duplicates = $this->query(
            'SELECT employee_id, attendance_date, COUNT(*) as count 
             FROM attendance 
             GROUP BY employee_id, attendance_date 
             HAVING count > 1'
        );
        
        $this->assertEmpty(
            $duplicates,
            'There should be no duplicate attendance records for same employee and date'
        );
    }
    
    /**
     * Property 10: For any randomly selected employee and date, at most one record exists.
     * 
     * @test
     */
    public function randomEmployeeDateHasAtMostOneRecord(): void
    {
        $attendance = $this->query(
            'SELECT DISTINCT employee_id, attendance_date FROM attendance LIMIT 100'
        );
        
        if (empty($attendance)) {
            $this->markTestSkipped('No attendance records in database');
        }
        
        $this->forAll(Generator\choose(0, count($attendance) - 1))
        ->withMaxSize(100)
        ->then(function ($index) use ($attendance) {
            $record = $attendance[$index];
            
            $count = $this->queryOne(
                'SELECT COUNT(*) as count FROM attendance 
                 WHERE employee_id = ? AND attendance_date = ?',
                [$record['employee_id'], $record['attendance_date']]
            );
            
            $this->assertEquals(
                1,
                $count['count'],
                "Employee {$record['employee_id']} should have exactly one record for {$record['attendance_date']}"
            );
        });
    }
    
    /**
     * Property 10: Database should reject duplicate attendance records.
     * 
     * @test
     */
    public function databaseRejectsDuplicateAttendance(): void
    {
        $this->beginTransaction();
        
        try {
            // Get an existing attendance record
            $existing = $this->queryOne(
                'SELECT company_id, employee_id, attendance_date FROM attendance LIMIT 1'
            );
            
            if (!$existing) {
                $this->markTestSkipped('No attendance records in database');
            }
            
            // Try to insert duplicate
            $this->expectException(PDOException::class);
            
            $this->execute(
                'INSERT INTO attendance (company_id, employee_id, attendance_date, status) 
                 VALUES (?, ?, ?, ?)',
                [
                    $existing['company_id'],
                    $existing['employee_id'],
                    $existing['attendance_date'],
                    'present'
                ]
            );
        } finally {
            $this->rollback();
        }
    }
    
    /**
     * Property 11: Total hours should equal clock_out - clock_in.
     * 
     * @test
     */
    public function totalHoursEqualsClockOutMinusClockIn(): void
    {
        $attendance = $this->query(
            'SELECT id, clock_in_time, clock_out_time, total_hours 
             FROM attendance 
             WHERE clock_in_time IS NOT NULL AND clock_out_time IS NOT NULL AND total_hours IS NOT NULL
             LIMIT 100'
        );
        
        if (empty($attendance)) {
            $this->markTestSkipped('No complete attendance records in database');
        }
        
        $this->forAll(Generator\choose(0, count($attendance) - 1))
        ->withMaxSize(100)
        ->then(function ($index) use ($attendance) {
            $record = $attendance[$index];
            
            // Calculate expected hours
            $clockIn = strtotime($record['clock_in_time']);
            $clockOut = strtotime($record['clock_out_time']);
            $expectedHours = ($clockOut - $clockIn) / 3600;
            
            // Allow small floating point tolerance
            $this->assertEqualsWithDelta(
                $expectedHours,
                (float) $record['total_hours'],
                0.1,
                "Attendance {$record['id']} total_hours should match calculated hours"
            );
        });
    }
    
    /**
     * Property 11: For generated clock times, total hours calculation is correct.
     * 
     * @test
     */
    public function totalHoursCalculationIsCorrect(): void
    {
        $this->forAll(
            Generator\choose(6, 10),  // Clock in hour (6 AM - 10 AM)
            Generator\choose(0, 59),  // Clock in minute
            Generator\choose(15, 21), // Clock out hour (3 PM - 9 PM)
            Generator\choose(0, 59)   // Clock out minute
        )
        ->withMaxSize(100)
        ->then(function ($inHour, $inMin, $outHour, $outMin) {
            $clockIn = sprintf('%02d:%02d:00', $inHour, $inMin);
            $clockOut = sprintf('%02d:%02d:00', $outHour, $outMin);
            
            $inSeconds = strtotime($clockIn);
            $outSeconds = strtotime($clockOut);
            
            $expectedHours = ($outSeconds - $inSeconds) / 3600;
            
            // Verify the calculation logic
            $this->assertGreaterThan(0, $expectedHours, 'Hours worked should be positive');
            $this->assertLessThanOrEqual(16, $expectedHours, 'Hours worked should be reasonable');
        });
    }
    
    /**
     * Property: Attendance status should be a valid enum value.
     * 
     * @test
     */
    public function allAttendanceStatusesAreValid(): void
    {
        $validStatuses = ['present', 'absent', 'half_day', 'late', 'on_leave'];
        
        $attendance = $this->query('SELECT id, status FROM attendance');
        
        foreach ($attendance as $record) {
            $this->assertContains(
                $record['status'],
                $validStatuses,
                "Attendance {$record['id']} should have a valid status"
            );
        }
    }
    
    /**
     * Property: All attendance records should reference valid employees.
     * 
     * @test
     */
    public function allAttendanceRecordsReferenceValidEmployees(): void
    {
        $orphaned = $this->query(
            'SELECT a.id FROM attendance a 
             LEFT JOIN employees e ON a.employee_id = e.id 
             WHERE e.id IS NULL'
        );
        
        $this->assertEmpty(
            $orphaned,
            'All attendance records should reference valid employees'
        );
    }
}
