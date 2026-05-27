<?php
/**
 * Authentication Service
 * 
 * Handles user authentication, session management, registration, and password operations.
 */

namespace HRMS\Services;

use HRMS\Repositories\UserRepository;
use HRMS\Core\Database;
use HRMS\Exceptions\AuthException;

class AuthService
{
    private UserRepository $userRepository;
    
    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }
    
    /**
     * Register a new company with admin user
     */
    public function register(array $data): array
    {
        $pdo = Database::getConnection();
        
        // Check if email already exists
        $existingUser = $this->userRepository->findByEmail($data['email']);
        if ($existingUser) {
            throw new AuthException('Email address is already registered');
        }
        
        // Check if registration number already exists
        $stmt = $pdo->prepare("SELECT id FROM companies WHERE registration_number = ?");
        $stmt->execute([$data['registration_number']]);
        if ($stmt->fetch()) {
            throw new AuthException('Company registration number already exists');
        }
        
        try {
            $pdo->beginTransaction();
            
            // 1. Create company
            $stmt = $pdo->prepare("
                INSERT INTO companies (name, registration_number, email, industry, company_size, status)
                VALUES (?, ?, ?, ?, ?, 'active')
            ");
            $stmt->execute([
                $data['company_name'],
                $data['registration_number'],
                $data['email'],
                $data['industry'] ?? null,
                $data['company_size'] ?? '1-10'
            ]);
            $companyId = (int) $pdo->lastInsertId();
            
            // 2. Get Admin role ID
            $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = 'Admin'");
            $stmt->execute();
            $role = $stmt->fetch();
            $adminRoleId = $role ? (int) $role['id'] : 1;
            
            // 3. Create admin user
            $passwordHash = $this->hashPassword($data['password']);
            $stmt = $pdo->prepare("
                INSERT INTO users (company_id, role_id, email, password_hash, status)
                VALUES (?, ?, ?, ?, 'active')
            ");
            $stmt->execute([
                $companyId,
                $adminRoleId,
                $data['email'],
                $passwordHash
            ]);
            $userId = (int) $pdo->lastInsertId();
            
            // 4. Create employee record for admin
            $employeeCode = 'EMP' . str_pad($userId, 5, '0', STR_PAD_LEFT);
            $stmt = $pdo->prepare("
                INSERT INTO employees (company_id, user_id, employee_code, first_name, last_name, email, hire_date, status)
                VALUES (?, ?, ?, ?, ?, ?, CURDATE(), 'active')
            ");
            $stmt->execute([
                $companyId,
                $userId,
                $employeeCode,
                $data['first_name'],
                $data['last_name'],
                $data['email']
            ]);
            
            // 5. Create default leave types for the company
            $defaultLeaveTypes = [
                ['Annual Leave', 20, 1],
                ['Sick Leave', 10, 1],
                ['Personal Leave', 5, 1],
                ['Unpaid Leave', 0, 0]
            ];
            
            $stmt = $pdo->prepare("
                INSERT INTO leave_types (company_id, name, annual_allocation, is_paid)
                VALUES (?, ?, ?, ?)
            ");
            
            foreach ($defaultLeaveTypes as $leaveType) {
                $stmt->execute([
                    $companyId,
                    $leaveType[0],
                    $leaveType[1],
                    $leaveType[2]
                ]);
            }
            
            $pdo->commit();
            
            // Auto-login the new user
            $loginResult = $this->login($data['email'], $data['password']);
            
            return [
                'company_id' => $companyId,
                'user_id' => $userId,
                'user' => $loginResult['user'],
                'permissions' => $loginResult['permissions']
            ];
            
        } catch (\PDOException $e) {
            $pdo->rollBack();
            throw new AuthException('Registration failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Authenticate user with email and password
     */
    public function login(string $email, string $password): array
    {
        // Find user by email
        $user = $this->userRepository->findByEmail($email);
        
        if (!$user) {
            throw new AuthException('Invalid email or password');
        }
        
        // Check if user is active
        if ($user['status'] !== 'active') {
            throw new AuthException('Your account is not active. Please contact administrator.');
        }
        
        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            throw new AuthException('Invalid email or password');
        }
        
        // Get user permissions
        $permissions = $this->userRepository->getPermissions($user['role_id']);
        
        // Update last login
        $this->userRepository->updateLastLogin($user['id']);
        
        // Create session data
        $sessionData = [
            'id' => (int) $user['id'],
            'company_id' => (int) $user['company_id'],
            'role_id' => (int) $user['role_id'],
            'role_name' => $user['role_name'],
            'email' => $user['email'],
            'employee_id' => $user['employee_id'] ? (int) $user['employee_id'] : null
        ];
        
        // Store in session
        $_SESSION['user'] = $sessionData;
        $_SESSION['permissions'] = $permissions;
        
        // Return user data (without sensitive info)
        return [
            'user' => $sessionData,
            'permissions' => $permissions
        ];
    }
    
    /**
     * Logout current user
     */
    public function logout(): void
    {
        // Clear session data
        unset($_SESSION['user']);
        unset($_SESSION['permissions']);
        
        // Destroy session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
    
    /**
     * Get current authenticated user
     */
    public function getCurrentUser(): ?array
    {
        if (!isset($_SESSION['user'])) {
            return null;
        }
        
        $userId = $_SESSION['user']['id'];
        $user = $this->userRepository->findWithDetails($userId);
        
        if (!$user || $user['status'] !== 'active') {
            $this->logout();
            return null;
        }
        
        // Get fresh permissions
        $permissions = $this->userRepository->getPermissions($user['role_id']);
        
        return [
            'user' => [
                'id' => (int) $user['id'],
                'company_id' => (int) $user['company_id'],
                'role_id' => (int) $user['role_id'],
                'role_name' => $user['role_name'],
                'email' => $user['email'],
                'employee_id' => $user['employee_id'] ? (int) $user['employee_id'] : null,
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'employee_code' => $user['employee_code'],
                'last_login' => $user['last_login']
            ],
            'permissions' => $permissions
        ];
    }
    
    /**
     * Check if user is authenticated
     */
    public function isAuthenticated(): bool
    {
        return isset($_SESSION['user']) && !empty($_SESSION['user']['id']);
    }
    
    /**
     * Hash a password
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    }
    
    /**
     * Verify a password against a hash
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
