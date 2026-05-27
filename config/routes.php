<?php
/**
 * API Routes Configuration
 * 
 * Defines all API endpoints with their handlers and middleware.
 */

use HRMS\Core\Router;

/** @var Router $router */

// Add global middleware
$router->addMiddleware('HRMS\\Middleware\\CorsMiddleware');

// ============================================================
// Authentication Routes (No auth required for login/register)
// ============================================================

$router->post('/api/auth/login', ['HRMS\\Controllers\\AuthController', 'login'], [
    'auth' => false
]);

$router->post('/api/auth/register', ['HRMS\\Controllers\\AuthController', 'register'], [
    'auth' => false
]);

$router->post('/api/auth/logout', ['HRMS\\Controllers\\AuthController', 'logout'], [
    'auth' => true
]);

$router->get('/api/auth/me', ['HRMS\\Controllers\\AuthController', 'me'], [
    'auth' => true
]);

// ============================================================
// Employee Routes
// ============================================================

$router->get('/api/employees', ['HRMS\\Controllers\\EmployeeController', 'index'], [
    'auth' => true,
    'permission' => 'employee.view',
    'middleware' => ['HRMS\\Middleware\\TenantMiddleware', 'HRMS\\Middleware\\RBACMiddleware']
]);

$router->get('/api/employees/me', ['HRMS\\Controllers\\EmployeeController', 'me'], [
    'auth' => true,
    'permission' => 'employee.view_own',
    'middleware' => ['HRMS\\Middleware\\TenantMiddleware', 'HRMS\\Middleware\\RBACMiddleware']
]);

$router->get('/api/employees/{id}', ['HRMS\\Controllers\\EmployeeController', 'show'], [
    'auth' => true,
    'permission' => 'employee.view',
    'middleware' => ['HRMS\\Middleware\\TenantMiddleware', 'HRMS\\Middleware\\RBACMiddleware']
]);

$router->post('/api/employees', ['HRMS\\Controllers\\EmployeeController', 'store'], [
    'auth' => true,
    'permission' => 'employee.create',
    'middleware' => ['HRMS\\Middleware\\TenantMiddleware', 'HRMS\\Middleware\\RBACMiddleware']
]);

$router->put('/api/employees/me', ['HRMS\\Controllers\\EmployeeController', 'updateMe'], [
    'auth' => true,
    'permission' => 'employee.update_own',
    'middleware' => ['HRMS\\Middleware\\TenantMiddleware', 'HRMS\\Middleware\\RBACMiddleware']
]);

$router->put('/api/employees/{id}', ['HRMS\\Controllers\\EmployeeController', 'update'], [
    'auth' => true,
    'permission' => 'employee.update',
    'middleware' => ['HRMS\\Middleware\\TenantMiddleware', 'HRMS\\Middleware\\RBACMiddleware']
]);

$router->delete('/api/employees/{id}', ['HRMS\\Controllers\\EmployeeController', 'destroy'], [
    'auth' => true,
    'permission' => 'employee.delete',
    'middleware' => ['HRMS\\Middleware\\TenantMiddleware', 'HRMS\\Middleware\\RBACMiddleware']
]);

// ============================================================
// Attendance Routes
// ============================================================

$router->get('/api/attendance', ['HRMS\\Controllers\\AttendanceController', 'index'], [
    'auth' => true,
    'permission' => 'attendance.view',
    'middleware' => ['HRMS\\Middleware\\TenantMiddleware', 'HRMS\\Middleware\\RBACMiddleware']
]);

$router->get('/api/attendance/me', ['HRMS\\Controllers\\AttendanceController', 'me'], [
    'auth' => true,
    'permission' => 'attendance.view_own',
    'middleware' => ['HRMS\\Middleware\\TenantMiddleware', 'HRMS\\Middleware\\RBACMiddleware']
]);

$router->post('/api/attendance/clock-in', ['HRMS\\Controllers\\AttendanceController', 'clockIn'], [
    'auth' => true,
    'permission' => 'attendance.clock',
    'middleware' => ['HRMS\\Middleware\\TenantMiddleware', 'HRMS\\Middleware\\RBACMiddleware']
]);

$router->post('/api/attendance/clock-out', ['HRMS\\Controllers\\AttendanceController', 'clockOut'], [
    'auth' => true,
    'permission' => 'attendance.clock',
    'middleware' => ['HRMS\\Middleware\\TenantMiddleware', 'HRMS\\Middleware\\RBACMiddleware']
]);

$router->post('/api/attendance', ['HRMS\\Controllers\\AttendanceController', 'store'], [
    'auth' => true,
    'permission' => 'attendance.create',
    'middleware' => ['HRMS\\Middleware\\TenantMiddleware', 'HRMS\\Middleware\\RBACMiddleware']
]);

$router->put('/api/attendance/{id}', ['HRMS\\Controllers\\AttendanceController', 'update'], [
    'auth' => true,
    'permission' => 'attendance.update',
    'middleware' => ['HRMS\\Middleware\\TenantMiddleware', 'HRMS\\Middleware\\RBACMiddleware']
]);

// ============================================================
// Leave Routes
// ============================================================

$router->get('/api/leave/types', ['HRMS\\Controllers\\LeaveController', 'types'], [
    'auth' => true,
    'permission' => 'leave.view_own',
    'middleware' => ['HRMS\\Middleware\\TenantMiddleware', 'HRMS\\Middleware\\RBACMiddleware']
]);

$router->get('/api/leave/balance', ['HRMS\\Controllers\\LeaveController', 'balance'], [
    'auth' => true,
    'permission' => 'leave.view_own',
    'middleware' => ['HRMS\\Middleware\\TenantMiddleware', 'HRMS\\Middleware\\RBACMiddleware']
]);

$router->get('/api/leave/requests', ['HRMS\\Controllers\\LeaveController', 'index'], [
    'auth' => true,
    'permission' => 'leave.view',
    'middleware' => ['HRMS\\Middleware\\TenantMiddleware', 'HRMS\\Middleware\\RBACMiddleware']
]);

$router->get('/api/leave/requests/me', ['HRMS\\Controllers\\LeaveController', 'myRequests'], [
    'auth' => true,
    'permission' => 'leave.view_own',
    'middleware' => ['HRMS\\Middleware\\TenantMiddleware', 'HRMS\\Middleware\\RBACMiddleware']
]);

$router->post('/api/leave/requests', ['HRMS\\Controllers\\LeaveController', 'store'], [
    'auth' => true,
    'permission' => 'leave.request',
    'middleware' => ['HRMS\\Middleware\\TenantMiddleware', 'HRMS\\Middleware\\RBACMiddleware']
]);

$router->put('/api/leave/requests/{id}/approve', ['HRMS\\Controllers\\LeaveController', 'approve'], [
    'auth' => true,
    'permission' => 'leave.approve',
    'middleware' => ['HRMS\\Middleware\\TenantMiddleware', 'HRMS\\Middleware\\RBACMiddleware']
]);

$router->put('/api/leave/requests/{id}/reject', ['HRMS\\Controllers\\LeaveController', 'reject'], [
    'auth' => true,
    'permission' => 'leave.approve',
    'middleware' => ['HRMS\\Middleware\\TenantMiddleware', 'HRMS\\Middleware\\RBACMiddleware']
]);

// ============================================================
// Payroll Routes
// ============================================================

$router->get('/api/payroll', ['HRMS\\Controllers\\PayrollController', 'index'], [
    'auth' => true,
    'permission' => 'payroll.view',
    'middleware' => ['HRMS\\Middleware\\TenantMiddleware', 'HRMS\\Middleware\\RBACMiddleware']
]);

$router->get('/api/payroll/me', ['HRMS\\Controllers\\PayrollController', 'me'], [
    'auth' => true,
    'permission' => 'payroll.view_own',
    'middleware' => ['HRMS\\Middleware\\TenantMiddleware', 'HRMS\\Middleware\\RBACMiddleware']
]);

$router->post('/api/payroll/process', ['HRMS\\Controllers\\PayrollController', 'process'], [
    'auth' => true,
    'permission' => 'payroll.create',
    'middleware' => ['HRMS\\Middleware\\TenantMiddleware', 'HRMS\\Middleware\\RBACMiddleware']
]);

$router->get('/api/payroll/{id}', ['HRMS\\Controllers\\PayrollController', 'show'], [
    'auth' => true,
    'permission' => 'payroll.view',
    'middleware' => ['HRMS\\Middleware\\TenantMiddleware', 'HRMS\\Middleware\\RBACMiddleware']
]);
