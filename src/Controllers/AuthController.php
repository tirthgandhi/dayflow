<?php
/**
 * Authentication Controller
 * 
 * Handles login, logout, registration, and current user endpoints.
 */

namespace HRMS\Controllers;

use HRMS\Core\Request;
use HRMS\Core\Response;
use HRMS\Services\AuthService;
use HRMS\Exceptions\AuthException;
use HRMS\Exceptions\ValidationException;

class AuthController
{
    private AuthService $authService;
    
    public function __construct()
    {
        $this->authService = new AuthService();
    }
    
    /**
     * POST /api/auth/register
     * 
     * Register a new company with admin user
     */
    public function register(Request $request): Response
    {
        // Get input data
        $companyName = trim($request->input('company_name', ''));
        $registrationNumber = trim($request->input('registration_number', ''));
        $industry = trim($request->input('industry', ''));
        $companySize = $request->input('company_size', '1-10');
        $adminFirstName = trim($request->input('first_name', ''));
        $adminLastName = trim($request->input('last_name', ''));
        $adminEmail = trim($request->input('email', ''));
        $adminPassword = $request->input('password', '');
        $confirmPassword = $request->input('confirm_password', '');
        
        // Validate input
        $errors = [];
        
        // Company validation
        if (empty($companyName)) {
            $errors['company_name'] = 'Company name is required';
        } elseif (strlen($companyName) < 2) {
            $errors['company_name'] = 'Company name must be at least 2 characters';
        }
        
        if (empty($registrationNumber)) {
            $errors['registration_number'] = 'Registration number is required';
        }
        
        // Admin user validation
        if (empty($adminFirstName)) {
            $errors['first_name'] = 'First name is required';
        }
        
        if (empty($adminLastName)) {
            $errors['last_name'] = 'Last name is required';
        }
        
        if (empty($adminEmail)) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }
        
        if (empty($adminPassword)) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($adminPassword) < 6) {
            $errors['password'] = 'Password must be at least 6 characters';
        }
        
        if ($adminPassword !== $confirmPassword) {
            $errors['confirm_password'] = 'Passwords do not match';
        }
        
        if (!empty($errors)) {
            return Response::validationError($errors);
        }
        
        try {
            $result = $this->authService->register([
                'company_name' => $companyName,
                'registration_number' => $registrationNumber,
                'industry' => $industry,
                'company_size' => $companySize,
                'first_name' => $adminFirstName,
                'last_name' => $adminLastName,
                'email' => $adminEmail,
                'password' => $adminPassword
            ]);
            
            return Response::created($result, 'Company registered successfully');
            
        } catch (AuthException $e) {
            return Response::badRequest($e->getMessage());
        } catch (\Exception $e) {
            return Response::serverError('Registration failed. Please try again.');
        }
    }
    
    /**
     * POST /api/auth/login
     * 
     * Authenticate user and create session
     */
    public function login(Request $request): Response
    {
        $email = $request->input('email');
        $password = $request->input('password');
        
        // Validate input
        $errors = [];
        if (empty($email)) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }
        
        if (empty($password)) {
            $errors['password'] = 'Password is required';
        }
        
        if (!empty($errors)) {
            return Response::validationError($errors);
        }
        
        try {
            $result = $this->authService->login($email, $password);
            
            return Response::success($result, 'Login successful');
            
        } catch (AuthException $e) {
            return Response::unauthorized($e->getMessage());
        }
    }
    
    /**
     * POST /api/auth/logout
     * 
     * Logout current user and destroy session
     */
    public function logout(Request $request): Response
    {
        $this->authService->logout();
        
        return Response::success(null, 'Logout successful');
    }
    
    /**
     * GET /api/auth/me
     * 
     * Get current authenticated user details
     */
    public function me(Request $request): Response
    {
        $userData = $this->authService->getCurrentUser();
        
        if (!$userData) {
            return Response::unauthorized('Not authenticated');
        }
        
        return Response::success($userData);
    }
}
