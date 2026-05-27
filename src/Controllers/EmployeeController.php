<?php
/**
 * Employee Controller
 * 
 * Handles employee management API endpoints.
 */

namespace HRMS\Controllers;

use HRMS\Core\Request;
use HRMS\Core\Response;
use HRMS\Services\EmployeeService;
use HRMS\Exceptions\NotFoundException;
use HRMS\Exceptions\ValidationException;

class EmployeeController
{
    private EmployeeService $employeeService;
    
    public function __construct()
    {
        $this->employeeService = new EmployeeService();
    }
    
    /**
     * GET /api/employees
     * 
     * List employees with pagination and filters
     */
    public function index(Request $request): Response
    {
        $pagination = $request->pagination();
        
        $filters = [
            'department' => $request->query('department'),
            'status' => $request->query('status'),
            'search' => $request->query('search')
        ];
        
        $result = $this->employeeService->getEmployees(
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
     * GET /api/employees/{id}
     * 
     * Get single employee
     */
    public function show(Request $request): Response
    {
        try {
            $id = (int) $request->param('id');
            $employee = $this->employeeService->getEmployee($id, $request->companyId);
            
            return Response::success($employee);
            
        } catch (NotFoundException $e) {
            return Response::notFound($e->getMessage());
        }
    }
    
    /**
     * POST /api/employees
     * 
     * Create new employee
     */
    public function store(Request $request): Response
    {
        try {
            $employee = $this->employeeService->createEmployee(
                $request->companyId,
                $request->body
            );
            
            return Response::created($employee, 'Employee created successfully');
            
        } catch (ValidationException $e) {
            return Response::validationError($e->getErrors());
        }
    }
    
    /**
     * PUT /api/employees/{id}
     * 
     * Update employee
     */
    public function update(Request $request): Response
    {
        try {
            $id = (int) $request->param('id');
            $employee = $this->employeeService->updateEmployee(
                $id,
                $request->companyId,
                $request->body
            );
            
            return Response::success($employee, 'Employee updated successfully');
            
        } catch (NotFoundException $e) {
            return Response::notFound($e->getMessage());
        } catch (ValidationException $e) {
            return Response::validationError($e->getErrors());
        }
    }
    
    /**
     * DELETE /api/employees/{id}
     * 
     * Soft delete employee
     */
    public function destroy(Request $request): Response
    {
        try {
            $id = (int) $request->param('id');
            $this->employeeService->deleteEmployee($id, $request->companyId);
            
            return Response::success(null, 'Employee deleted successfully');
            
        } catch (NotFoundException $e) {
            return Response::notFound($e->getMessage());
        }
    }
    
    /**
     * GET /api/employees/me
     * 
     * Get current user's employee profile
     */
    public function me(Request $request): Response
    {
        try {
            $employee = $this->employeeService->getEmployeeByUserId(
                $request->userId(),
                $request->companyId
            );
            
            return Response::success($employee);
            
        } catch (NotFoundException $e) {
            return Response::notFound($e->getMessage());
        }
    }
    
    /**
     * PUT /api/employees/me
     * 
     * Update current user's own profile
     */
    public function updateMe(Request $request): Response
    {
        try {
            $employee = $this->employeeService->updateOwnProfile(
                $request->userId(),
                $request->companyId,
                $request->body
            );
            
            return Response::success($employee, 'Profile updated successfully');
            
        } catch (NotFoundException $e) {
            return Response::notFound($e->getMessage());
        } catch (ValidationException $e) {
            return Response::validationError($e->getErrors());
        }
    }
}
