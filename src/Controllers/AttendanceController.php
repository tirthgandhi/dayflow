<?php
/**
 * Attendance Controller
 * 
 * Handles attendance management API endpoints.
 */

namespace HRMS\Controllers;

use HRMS\Core\Request;
use HRMS\Core\Response;
use HRMS\Services\AttendanceService;
use HRMS\Exceptions\NotFoundException;
use HRMS\Exceptions\ValidationException;

class AttendanceController
{
    private AttendanceService $attendanceService;
    
    public function __construct()
    {
        $this->attendanceService = new AttendanceService();
    }
    
    /**
     * GET /api/attendance
     * 
     * List attendance records with pagination and filters
     */
    public function index(Request $request): Response
    {
        $pagination = $request->pagination();
        
        $filters = [
            'employee_id' => $request->query('employee_id'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'status' => $request->query('status')
        ];
        
        $result = $this->attendanceService->getAttendance(
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
     * GET /api/attendance/me
     * 
     * Get current user's attendance records
     */
    public function me(Request $request): Response
    {
        $employeeId = $request->employeeId();
        
        if (!$employeeId) {
            return Response::notFound('Employee profile not found');
        }
        
        $pagination = $request->pagination();
        
        $filters = [
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to')
        ];
        
        $result = $this->attendanceService->getMyAttendance(
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
     * POST /api/attendance/clock-in
     * 
     * Clock in for current user
     */
    public function clockIn(Request $request): Response
    {
        try {
            $employeeId = $request->employeeId();
            
            if (!$employeeId) {
                return Response::notFound('Employee profile not found');
            }
            
            $attendance = $this->attendanceService->clockIn($employeeId, $request->companyId);
            
            return Response::created($attendance, 'Clocked in successfully');
            
        } catch (ValidationException $e) {
            return Response::validationError($e->getErrors());
        } catch (NotFoundException $e) {
            return Response::notFound($e->getMessage());
        }
    }
    
    /**
     * POST /api/attendance/clock-out
     * 
     * Clock out for current user
     */
    public function clockOut(Request $request): Response
    {
        try {
            $employeeId = $request->employeeId();
            
            if (!$employeeId) {
                return Response::notFound('Employee profile not found');
            }
            
            $attendance = $this->attendanceService->clockOut($employeeId, $request->companyId);
            
            return Response::success($attendance, 'Clocked out successfully');
            
        } catch (ValidationException $e) {
            return Response::validationError($e->getErrors());
        } catch (NotFoundException $e) {
            return Response::notFound($e->getMessage());
        }
    }
    
    /**
     * POST /api/attendance
     * 
     * Create attendance record (admin)
     */
    public function store(Request $request): Response
    {
        try {
            $attendance = $this->attendanceService->createAttendance(
                $request->companyId,
                $request->body
            );
            
            return Response::created($attendance, 'Attendance record created successfully');
            
        } catch (ValidationException $e) {
            return Response::validationError($e->getErrors());
        } catch (NotFoundException $e) {
            return Response::notFound($e->getMessage());
        }
    }
    
    /**
     * PUT /api/attendance/{id}
     * 
     * Update attendance record
     */
    public function update(Request $request): Response
    {
        try {
            $id = (int) $request->param('id');
            
            $attendance = $this->attendanceService->updateAttendance(
                $id,
                $request->companyId,
                $request->body
            );
            
            return Response::success($attendance, 'Attendance record updated successfully');
            
        } catch (NotFoundException $e) {
            return Response::notFound($e->getMessage());
        } catch (ValidationException $e) {
            return Response::validationError($e->getErrors());
        }
    }
}
