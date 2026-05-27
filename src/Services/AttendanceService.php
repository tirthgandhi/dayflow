<?php
/**
 * Attendance Service
 * 
 * Business logic for attendance management.
 */

namespace HRMS\Services;

use HRMS\Repositories\AttendanceRepository;
use HRMS\Repositories\EmployeeRepository;
use HRMS\Exceptions\NotFoundException;
use HRMS\Exceptions\ValidationException;

class AttendanceService
{
    private AttendanceRepository $attendanceRepository;
    private EmployeeRepository $employeeRepository;
    
    public function __construct()
    {
        $this->attendanceRepository = new AttendanceRepository();
        $this->employeeRepository = new EmployeeRepository();
    }
    
    /**
     * Get paginated attendance records
     */
    public function getAttendance(int $companyId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        return $this->attendanceRepository->getPaginated($companyId, $filters, $page, $perPage);
    }
    
    /**
     * Get employee's own attendance
     */
    public function getMyAttendance(int $employeeId, int $companyId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        return $this->attendanceRepository->getByEmployee($employeeId, $companyId, $filters, $page, $perPage);
    }
    
    /**
     * Clock in
     */
    public function clockIn(int $employeeId, int $companyId): array
    {
        $today = date('Y-m-d');
        $now = date('H:i:s');
        
        // Check if already clocked in today
        $existing = $this->attendanceRepository->findByEmployeeAndDate($employeeId, $today, $companyId);
        if ($existing) {
            throw new ValidationException(['clock_in' => 'Already clocked in today']);
        }
        
        // Verify employee exists
        $employee = $this->employeeRepository->find($employeeId, $companyId);
        if (!$employee) {
            throw new NotFoundException('Employee not found');
        }
        
        $id = $this->attendanceRepository->clockIn($employeeId, $companyId, $today, $now);
        
        return $this->attendanceRepository->find($id, $companyId);
    }
    
    /**
     * Clock out
     */
    public function clockOut(int $employeeId, int $companyId): array
    {
        $today = date('Y-m-d');
        $now = date('H:i:s');
        
        // Get today's attendance record
        $attendance = $this->attendanceRepository->findByEmployeeAndDate($employeeId, $today, $companyId);
        
        if (!$attendance) {
            throw new ValidationException(['clock_out' => 'No clock-in record found for today']);
        }
        
        if ($attendance['clock_out_time'] !== null) {
            throw new ValidationException(['clock_out' => 'Already clocked out today']);
        }
        
        // Calculate total hours
        $clockIn = new \DateTime($attendance['clock_in_time']);
        $clockOut = new \DateTime($now);
        $diff = $clockIn->diff($clockOut);
        $totalHours = round($diff->h + ($diff->i / 60), 2);
        
        $this->attendanceRepository->clockOut($attendance['id'], $companyId, $now, $totalHours);
        
        return $this->attendanceRepository->find($attendance['id'], $companyId);
    }
    
    /**
     * Create attendance record (admin)
     */
    public function createAttendance(int $companyId, array $data): array
    {
        $this->validateAttendanceData($data);
        
        // Verify employee exists
        $employee = $this->employeeRepository->find($data['employee_id'], $companyId);
        if (!$employee) {
            throw new NotFoundException('Employee not found');
        }
        
        // Check for duplicate
        $existing = $this->attendanceRepository->findByEmployeeAndDate(
            $data['employee_id'], 
            $data['date'], 
            $companyId
        );
        if ($existing) {
            throw new ValidationException(['date' => 'Attendance record already exists for this date']);
        }
        
        // Calculate total hours if both times provided
        $totalHours = null;
        if (!empty($data['clock_in_time']) && !empty($data['clock_out_time'])) {
            $clockIn = new \DateTime($data['clock_in_time']);
            $clockOut = new \DateTime($data['clock_out_time']);
            $diff = $clockIn->diff($clockOut);
            $totalHours = round($diff->h + ($diff->i / 60), 2);
        }
        
        $attendanceData = [
            'company_id' => $companyId,
            'employee_id' => $data['employee_id'],
            'attendance_date' => $data['date'],
            'clock_in_time' => $data['clock_in_time'] ?? null,
            'clock_out_time' => $data['clock_out_time'] ?? null,
            'total_hours' => $totalHours,
            'status' => $data['status'] ?? 'present',
            'notes' => $data['notes'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $id = $this->attendanceRepository->create($attendanceData);
        
        return $this->attendanceRepository->find($id, $companyId);
    }
    
    /**
     * Update attendance record
     */
    public function updateAttendance(int $id, int $companyId, array $data): array
    {
        $attendance = $this->attendanceRepository->find($id, $companyId);
        if (!$attendance) {
            throw new NotFoundException('Attendance record not found');
        }
        
        // Calculate total hours if times changed
        $clockIn = $data['clock_in_time'] ?? $attendance['clock_in_time'];
        $clockOut = $data['clock_out_time'] ?? $attendance['clock_out_time'];
        
        $totalHours = null;
        if ($clockIn && $clockOut) {
            $clockInDt = new \DateTime($clockIn);
            $clockOutDt = new \DateTime($clockOut);
            $diff = $clockInDt->diff($clockOutDt);
            $totalHours = round($diff->h + ($diff->i / 60), 2);
        }
        
        $updateData = array_filter([
            'clock_in_time' => $data['clock_in_time'] ?? null,
            'clock_out_time' => $data['clock_out_time'] ?? null,
            'total_hours' => $totalHours,
            'status' => $data['status'] ?? null,
            'notes' => $data['notes'] ?? null,
            'updated_at' => date('Y-m-d H:i:s')
        ], fn($v) => $v !== null);
        
        $this->attendanceRepository->update($id, $updateData, $companyId);
        
        return $this->attendanceRepository->find($id, $companyId);
    }
    
    /**
     * Get attendance summary
     */
    public function getSummary(int $employeeId, int $companyId, ?string $month = null): array
    {
        $month = $month ?? date('Y-m');
        return $this->attendanceRepository->getSummary($employeeId, $companyId, $month);
    }
    
    /**
     * Validate attendance data
     */
    private function validateAttendanceData(array $data): void
    {
        $errors = [];
        
        if (empty($data['employee_id'])) {
            $errors['employee_id'] = 'Employee ID is required';
        }
        
        if (empty($data['date'])) {
            $errors['date'] = 'Date is required';
        } elseif (!\DateTime::createFromFormat('Y-m-d', $data['date'])) {
            $errors['date'] = 'Invalid date format (use YYYY-MM-DD)';
        }
        
        if (!empty($data['status']) && !in_array($data['status'], ['present', 'absent', 'late', 'half_day'])) {
            $errors['status'] = 'Invalid status';
        }
        
        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }
}
