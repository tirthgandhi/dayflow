<?php
/**
 * Employee Service
 * 
 * Business logic for employee management.
 */

namespace HRMS\Services;

use HRMS\Repositories\EmployeeRepository;
use HRMS\Exceptions\NotFoundException;
use HRMS\Exceptions\ValidationException;

class EmployeeService
{
    private EmployeeRepository $employeeRepository;
    
    public function __construct()
    {
        $this->employeeRepository = new EmployeeRepository();
    }
    
    /**
     * Get paginated list of employees
     */
    public function getEmployees(int $companyId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        return $this->employeeRepository->getPaginated($companyId, $filters, $page, $perPage);
    }
    
    /**
     * Get single employee by ID
     */
    public function getEmployee(int $id, int $companyId): array
    {
        $employee = $this->employeeRepository->findWithUser($id, $companyId);
        
        if (!$employee) {
            throw new NotFoundException('Employee not found');
        }
        
        return $employee;
    }
    
    /**
     * Get employee by user ID (for self-service)
     */
    public function getEmployeeByUserId(int $userId, int $companyId): array
    {
        $employee = $this->employeeRepository->findByUserId($userId, $companyId);
        
        if (!$employee) {
            throw new NotFoundException('Employee profile not found');
        }
        
        return $employee;
    }
    
    /**
     * Create new employee
     */
    public function createEmployee(int $companyId, array $data): array
    {
        // Validate required fields
        $this->validateEmployeeData($data, true);
        
        // Check for duplicate employee code
        if (!empty($data['employee_code'])) {
            $existing = $this->employeeRepository->findByCode($data['employee_code'], $companyId);
            if ($existing) {
                throw new ValidationException(['employee_code' => 'Employee code already exists']);
            }
        } else {
            $data['employee_code'] = $this->employeeRepository->generateEmployeeCode($companyId);
        }
        
        // Prepare employee data
        $employeeData = [
            'company_id' => $companyId,
            'employee_code' => $data['employee_code'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'] ?? '',
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'gender' => $data['gender'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'department' => $data['department'] ?? null,
            'designation' => $data['designation'] ?? $data['position'] ?? null,
            'hire_date' => $data['hire_date'] ?? date('Y-m-d'),
            'status' => $data['status'] ?? 'active'
        ];

        
        // Prepare user data if email provided
        $userData = null;
        if (!empty($data['email'])) {
            $userData = [
                'role_id' => $data['role_id'] ?? 3, // Default to Employee role (1=Admin, 2=HR, 3=Employee)
                'email' => $data['email'],
                'password_hash' => password_hash($data['password'] ?? 'password123', PASSWORD_BCRYPT),
                'status' => 'active'
            ];
        }
        
        $employeeId = $this->employeeRepository->createWithUser($employeeData, $userData);
        
        return $this->getEmployee($employeeId, $companyId);
    }
    
    /**
     * Update employee
     */
    public function updateEmployee(int $id, int $companyId, array $data): array
    {
        // Check employee exists
        $employee = $this->employeeRepository->find($id, $companyId);
        if (!$employee) {
            throw new NotFoundException('Employee not found');
        }
        
        // Validate data
        $this->validateEmployeeData($data, false);
        
        // Check for duplicate employee code if changing
        if (!empty($data['employee_code']) && $data['employee_code'] !== $employee['employee_code']) {
            $existing = $this->employeeRepository->findByCode($data['employee_code'], $companyId);
            if ($existing) {
                throw new ValidationException(['employee_code' => 'Employee code already exists']);
            }
        }
        
        // Prepare employee update data
        $employeeData = array_filter([
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'gender' => $data['gender'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'department' => $data['department'] ?? null,
            'designation' => $data['designation'] ?? $data['position'] ?? null,
            'status' => $data['status'] ?? null
        ], fn($v) => $v !== null);
        
        // Prepare user update data
        $userData = null;
        if (!empty($data['email']) || !empty($data['password']) || isset($data['role_id'])) {
            $userData = array_filter([
                'email' => $data['email'] ?? null,
                'role_id' => $data['role_id'] ?? null,
                'password_hash' => !empty($data['password']) 
                    ? password_hash($data['password'], PASSWORD_BCRYPT) 
                    : null
            ], fn($v) => $v !== null);
        }
        
        $this->employeeRepository->updateWithUser($id, $companyId, $employeeData, $userData);
        
        return $this->getEmployee($id, $companyId);
    }
    
    /**
     * Update own profile (limited fields)
     */
    public function updateOwnProfile(int $userId, int $companyId, array $data): array
    {
        $employee = $this->employeeRepository->findByUserId($userId, $companyId);
        if (!$employee) {
            throw new NotFoundException('Employee profile not found');
        }
        
        // Only allow updating certain fields for self-service
        $allowedFields = ['phone', 'address'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));
        $updateData['updated_at'] = date('Y-m-d H:i:s');
        
        $this->employeeRepository->update($employee['id'], $updateData, $companyId);
        
        return $this->getEmployeeByUserId($userId, $companyId);
    }
    
    /**
     * Delete (soft) employee
     */
    public function deleteEmployee(int $id, int $companyId): bool
    {
        $employee = $this->employeeRepository->find($id, $companyId);
        if (!$employee) {
            throw new NotFoundException('Employee not found');
        }
        
        return $this->employeeRepository->softDelete($id, $companyId);
    }
    
    /**
     * Validate employee data
     */
    private function validateEmployeeData(array $data, bool $isCreate): void
    {
        $errors = [];
        
        if ($isCreate) {
            if (empty($data['first_name'])) {
                $errors['first_name'] = 'First name is required';
            }
            if (empty($data['last_name'])) {
                $errors['last_name'] = 'Last name is required';
            }
        }
        
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }
        
        if (!empty($data['date_of_birth'])) {
            $dob = \DateTime::createFromFormat('Y-m-d', $data['date_of_birth']);
            if (!$dob) {
                $errors['date_of_birth'] = 'Invalid date format (use YYYY-MM-DD)';
            }
        }
        
        if (!empty($data['hire_date'])) {
            $hireDate = \DateTime::createFromFormat('Y-m-d', $data['hire_date']);
            if (!$hireDate) {
                $errors['hire_date'] = 'Invalid date format (use YYYY-MM-DD)';
            }
        }
        
        if (!empty($data['gender']) && !in_array($data['gender'], ['male', 'female', 'other'])) {
            $errors['gender'] = 'Gender must be male, female, or other';
        }
        
        if (!empty($data['status']) && !in_array($data['status'], ['active', 'inactive', 'terminated'])) {
            $errors['status'] = 'Invalid status';
        }
        
        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }
}
