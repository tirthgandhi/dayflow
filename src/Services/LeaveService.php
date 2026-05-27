<?php
/**
 * Leave Service
 * 
 * Business logic for leave management.
 */

namespace HRMS\Services;

use HRMS\Repositories\LeaveRepository;
use HRMS\Repositories\EmployeeRepository;
use HRMS\Exceptions\NotFoundException;
use HRMS\Exceptions\ValidationException;
use HRMS\Exceptions\ForbiddenException;

class LeaveService
{
    private LeaveRepository $leaveRepository;
    private EmployeeRepository $employeeRepository;
    
    public function __construct()
    {
        $this->leaveRepository = new LeaveRepository();
        $this->employeeRepository = new EmployeeRepository();
    }
    
    /**
     * Get leave types
     */
    public function getLeaveTypes(int $companyId): array
    {
        return $this->leaveRepository->getLeaveTypes($companyId);
    }
    
    /**
     * Get paginated leave requests
     */
    public function getLeaveRequests(int $companyId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        return $this->leaveRepository->getPaginated($companyId, $filters, $page, $perPage);
    }
    
    /**
     * Get employee's own leave requests
     */
    public function getMyLeaveRequests(int $employeeId, int $companyId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        return $this->leaveRepository->getByEmployee($employeeId, $companyId, $filters, $page, $perPage);
    }
    
    /**
     * Get leave balance
     */
    public function getBalance(int $employeeId, int $companyId): array
    {
        return $this->leaveRepository->getBalance($employeeId, $companyId);
    }
    
    /**
     * Create leave request
     */
    public function createLeaveRequest(int $employeeId, int $companyId, array $data): array
    {
        $this->validateLeaveRequestData($data);
        
        // Verify leave type exists
        $leaveType = $this->leaveRepository->getLeaveType($data['leave_type_id'], $companyId);
        if (!$leaveType) {
            throw new NotFoundException('Leave type not found');
        }
        
        // Calculate total days
        $startDate = new \DateTime($data['start_date']);
        $endDate = new \DateTime($data['end_date']);
        $totalDays = $startDate->diff($endDate)->days + 1;
        
        // Check for overlapping requests
        if ($this->leaveRepository->hasOverlap($employeeId, $companyId, $data['start_date'], $data['end_date'])) {
            throw new ValidationException(['dates' => 'You already have a leave request for these dates']);
        }
        
        // Check balance
        $balance = $this->leaveRepository->getBalance($employeeId, $companyId);
        $typeBalance = array_filter($balance, fn($b) => $b['leave_type_id'] == $data['leave_type_id']);
        $typeBalance = reset($typeBalance);
        
        if ($typeBalance && isset($typeBalance['remaining']) && $typeBalance['remaining'] < $totalDays) {
            throw new ValidationException([
                'leave_type_id' => "Insufficient leave balance. Available: {$typeBalance['remaining']} days"
            ]);
        }
        
        $requestData = [
            'company_id' => $companyId,
            'employee_id' => $employeeId,
            'leave_type_id' => $data['leave_type_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'total_days' => $totalDays,
            'reason' => $data['reason'] ?? null,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $id = $this->leaveRepository->create($requestData);
        
        return $this->leaveRepository->findWithDetails($id, $companyId);
    }
    
    /**
     * Approve leave request
     */
    public function approveLeaveRequest(int $id, int $companyId, int $approverId): array
    {
        $request = $this->leaveRepository->find($id, $companyId);
        
        if (!$request) {
            throw new NotFoundException('Leave request not found');
        }
        
        if ($request['status'] !== 'pending') {
            throw new ValidationException(['status' => 'Only pending requests can be approved']);
        }
        
        // Get approver's employee ID
        $approver = $this->employeeRepository->findByUserId($approverId, $companyId);
        $approverEmployeeId = $approver ? $approver['id'] : null;
        
        $this->leaveRepository->approve($id, $companyId, $approverEmployeeId);
        
        return $this->leaveRepository->findWithDetails($id, $companyId);
    }
    
    /**
     * Reject leave request
     */
    public function rejectLeaveRequest(int $id, int $companyId, int $approverId, ?string $reason = null): array
    {
        $request = $this->leaveRepository->find($id, $companyId);
        
        if (!$request) {
            throw new NotFoundException('Leave request not found');
        }
        
        if ($request['status'] !== 'pending') {
            throw new ValidationException(['status' => 'Only pending requests can be rejected']);
        }
        
        // Get approver's employee ID
        $approver = $this->employeeRepository->findByUserId($approverId, $companyId);
        $approverEmployeeId = $approver ? $approver['id'] : null;
        
        $this->leaveRepository->reject($id, $companyId, $approverEmployeeId, $reason);
        
        return $this->leaveRepository->findWithDetails($id, $companyId);
    }
    
    /**
     * Validate leave request data
     */
    private function validateLeaveRequestData(array $data): void
    {
        $errors = [];
        
        if (empty($data['leave_type_id'])) {
            $errors['leave_type_id'] = 'Leave type is required';
        }
        
        if (empty($data['start_date'])) {
            $errors['start_date'] = 'Start date is required';
        } elseif (!\DateTime::createFromFormat('Y-m-d', $data['start_date'])) {
            $errors['start_date'] = 'Invalid date format (use YYYY-MM-DD)';
        }
        
        if (empty($data['end_date'])) {
            $errors['end_date'] = 'End date is required';
        } elseif (!\DateTime::createFromFormat('Y-m-d', $data['end_date'])) {
            $errors['end_date'] = 'Invalid date format (use YYYY-MM-DD)';
        }
        
        if (!empty($data['start_date']) && !empty($data['end_date'])) {
            $start = new \DateTime($data['start_date']);
            $end = new \DateTime($data['end_date']);
            
            if ($end < $start) {
                $errors['end_date'] = 'End date must be after start date';
            }
            
            if ($start < new \DateTime('today')) {
                $errors['start_date'] = 'Start date cannot be in the past';
            }
        }
        
        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }
}
