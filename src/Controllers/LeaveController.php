<?php
/**
 * Leave Controller
 * 
 * Handles leave management API endpoints.
 */

namespace HRMS\Controllers;

use HRMS\Core\Request;
use HRMS\Core\Response;
use HRMS\Services\LeaveService;
use HRMS\Exceptions\NotFoundException;
use HRMS\Exceptions\ValidationException;

class LeaveController
{
    private LeaveService $leaveService;
    
    public function __construct()
    {
        $this->leaveService = new LeaveService();
    }
    
    /**
     * GET /api/leave/types
     * 
     * List leave types
     */
    public function types(Request $request): Response
    {
        $types = $this->leaveService->getLeaveTypes($request->companyId);
        return Response::success($types);
    }
    
    /**
     * GET /api/leave/requests
     * 
     * List leave requests with pagination and filters
     */
    public function index(Request $request): Response
    {
        $pagination = $request->pagination();
        
        $filters = [
            'employee_id' => $request->query('employee_id'),
            'status' => $request->query('status'),
            'leave_type_id' => $request->query('leave_type_id')
        ];
        
        $result = $this->leaveService->getLeaveRequests(
            $request->companyId,
            array_filter($filters),
            $pagination['page'],
            $pagination['per_page']
        );
        
        return Response::paginated(
            $result['data'],
            $result['total'],
            $pagination['page'],
            $pagination['per_page']
        );
    }
    
    /**
     * GET /api/leave/requests/me
     * 
     * Get current user's leave requests
     */
    public function myRequests(Request $request): Response
    {
        $employeeId = $request->employeeId();
        
        if (!$employeeId) {
            return Response::notFound('Employee profile not found');
        }
        
        $pagination = $request->pagination();
        
        $filters = [
            'status' => $request->query('status'),
            'leave_type_id' => $request->query('leave_type_id')
        ];
        
        $result = $this->leaveService->getMyLeaveRequests(
            $employeeId,
            $request->companyId,
            array_filter($filters),
            $pagination['page'],
            $pagination['per_page']
        );
        
        return Response::paginated(
            $result['data'],
            $result['total'],
            $pagination['page'],
            $pagination['per_page']
        );
    }
    
    /**
     * GET /api/leave/balance
     * 
     * Get current user's leave balance
     */
    public function balance(Request $request): Response
    {
        $employeeId = $request->employeeId();
        
        if (!$employeeId) {
            return Response::notFound('Employee profile not found');
        }
        
        $balance = $this->leaveService->getBalance($employeeId, $request->companyId);
        
        return Response::success($balance);
    }
    
    /**
     * POST /api/leave/requests
     * 
     * Create leave request
     */
    public function store(Request $request): Response
    {
        try {
            $employeeId = $request->employeeId();
            
            if (!$employeeId) {
                return Response::notFound('Employee profile not found');
            }
            
            $leaveRequest = $this->leaveService->createLeaveRequest(
                $employeeId,
                $request->companyId,
                $request->body
            );
            
            return Response::created($leaveRequest, 'Leave request submitted successfully');
            
        } catch (ValidationException $e) {
            return Response::validationError($e->getErrors());
        } catch (NotFoundException $e) {
            return Response::notFound($e->getMessage());
        }
    }
    
    /**
     * PUT /api/leave/requests/{id}/approve
     * 
     * Approve leave request
     */
    public function approve(Request $request): Response
    {
        try {
            $id = (int) $request->param('id');
            
            $leaveRequest = $this->leaveService->approveLeaveRequest(
                $id,
                $request->companyId,
                $request->userId()
            );
            
            return Response::success($leaveRequest, 'Leave request approved');
            
        } catch (NotFoundException $e) {
            return Response::notFound($e->getMessage());
        } catch (ValidationException $e) {
            return Response::validationError($e->getErrors());
        }
    }
    
    /**
     * PUT /api/leave/requests/{id}/reject
     * 
     * Reject leave request
     */
    public function reject(Request $request): Response
    {
        try {
            $id = (int) $request->param('id');
            $reason = $request->input('reason');
            
            $leaveRequest = $this->leaveService->rejectLeaveRequest(
                $id,
                $request->companyId,
                $request->userId(),
                $reason
            );
            
            return Response::success($leaveRequest, 'Leave request rejected');
            
        } catch (NotFoundException $e) {
            return Response::notFound($e->getMessage());
        } catch (ValidationException $e) {
            return Response::validationError($e->getErrors());
        }
    }
}
