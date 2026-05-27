<?php
/**
 * Payroll Controller
 * 
 * Handles payroll management API endpoints.
 */

namespace HRMS\Controllers;

use HRMS\Core\Request;
use HRMS\Core\Response;
use HRMS\Services\PayrollService;
use HRMS\Exceptions\NotFoundException;
use HRMS\Exceptions\ValidationException;

class PayrollController
{
    private PayrollService $payrollService;
    
    public function __construct()
    {
        $this->payrollService = new PayrollService();
    }
    
    /**
     * GET /api/payroll
     * 
     * List payroll records with pagination and filters
     */
    public function index(Request $request): Response
    {
        $pagination = $request->pagination();
        
        $filters = [
            'employee_id' => $request->query('employee_id'),
            'month' => $request->query('month'),
            'status' => $request->query('status')
        ];
        
        $result = $this->payrollService->getPayrollRecords(
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
     * GET /api/payroll/{id}
     * 
     * Get single payroll record
     */
    public function show(Request $request): Response
    {
        try {
            $id = (int) $request->param('id');
            $record = $this->payrollService->getPayrollRecord($id, $request->companyId);
            
            return Response::success($record);
            
        } catch (NotFoundException $e) {
            return Response::notFound($e->getMessage());
        }
    }
    
    /**
     * GET /api/payroll/me
     * 
     * Get current user's payroll records
     */
    public function me(Request $request): Response
    {
        $employeeId = $request->employeeId();
        
        if (!$employeeId) {
            return Response::notFound('Employee profile not found');
        }
        
        $pagination = $request->pagination();
        
        $filters = [
            'month' => $request->query('month'),
            'status' => $request->query('status')
        ];
        
        $result = $this->payrollService->getMyPayroll(
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
     * POST /api/payroll/process
     * 
     * Process payroll for a month
     */
    public function process(Request $request): Response
    {
        try {
            $result = $this->payrollService->processPayroll(
                $request->companyId,
                $request->body
            );
            
            return Response::success($result, 'Payroll processed successfully');
            
        } catch (ValidationException $e) {
            return Response::validationError($e->getErrors());
        }
    }
}
