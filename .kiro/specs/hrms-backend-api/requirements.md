# Requirements Document

## Introduction

This document defines the requirements for the PHP Backend API of the Multi-Company HRMS system. The API provides RESTful endpoints for authentication, employee management, attendance tracking, leave management, and payroll operations. All endpoints enforce multi-tenant data isolation through company_id scoping and role-based access control.

## Glossary

- **API**: Application Programming Interface - RESTful HTTP endpoints for data operations
- **JWT**: JSON Web Token - stateless authentication token
- **RBAC**: Role-Based Access Control - permission system based on user roles
- **Middleware**: Code that runs before/after request handlers for authentication and authorization
- **Endpoint**: A specific URL path that handles HTTP requests
- **Multi-tenant**: Architecture ensuring each company's data is isolated
- **Session**: Server-side storage of authenticated user context

## Requirements

### Requirement 1: Authentication System

**User Story:** As a user, I want to securely log in and out of the system, so that I can access my company's HR data.

#### Acceptance Criteria

1. WHEN a user submits valid email and password credentials THEN the Authentication_System SHALL return a session token and user details including company_id and role
2. WHEN a user submits invalid credentials THEN the Authentication_System SHALL return an authentication error without revealing which field was incorrect
3. WHEN a user logs out THEN the Authentication_System SHALL invalidate the current session and clear authentication cookies
4. WHEN an authenticated request is made THEN the Authentication_System SHALL validate the session token and attach user context to the request
5. WHEN a session token expires or is invalid THEN the Authentication_System SHALL return an unauthorized error with appropriate HTTP status code

### Requirement 2: Multi-Tenant Middleware

**User Story:** As a system architect, I want all API requests to be scoped to the authenticated user's company, so that data isolation is enforced at the API layer.

#### Acceptance Criteria

1. WHEN any API request is processed THEN the Tenant_Middleware SHALL extract company_id from the authenticated user's session
2. WHEN database queries are executed THEN the Tenant_Middleware SHALL automatically append company_id filter to all tenant-specific table queries
3. WHEN a user attempts to access data from another company THEN the Tenant_Middleware SHALL return a forbidden error
4. WHEN creating new records THEN the Tenant_Middleware SHALL automatically set company_id to the authenticated user's company

### Requirement 3: Role-Based Access Control

**User Story:** As an administrator, I want to restrict API access based on user roles, so that employees can only access features appropriate to their role.

#### Acceptance Criteria

1. WHEN a user accesses an endpoint THEN the RBAC_Middleware SHALL verify the user has the required permission for that action
2. WHEN a user lacks required permission THEN the RBAC_Middleware SHALL return a forbidden error with clear message
3. WHEN checking permissions THEN the RBAC_Middleware SHALL query the role_permissions table for the user's role_id
4. WHEN Admin role accesses any endpoint THEN the RBAC_Middleware SHALL grant access to all operations
5. WHEN Employee role accesses restricted endpoints THEN the RBAC_Middleware SHALL limit access to self-service operations only

### Requirement 4: Employee Management API

**User Story:** As an HR manager, I want to manage employee records through the API, so that I can onboard, update, and offboard employees.

#### Acceptance Criteria

1. WHEN GET /api/employees is called THEN the Employee_API SHALL return a paginated list of employees for the authenticated user's company
2. WHEN GET /api/employees/{id} is called THEN the Employee_API SHALL return the employee details if they belong to the same company
3. WHEN POST /api/employees is called with valid data THEN the Employee_API SHALL create a new employee record with the authenticated user's company_id
4. WHEN PUT /api/employees/{id} is called THEN the Employee_API SHALL update the employee record if authorized
5. WHEN DELETE /api/employees/{id} is called THEN the Employee_API SHALL soft-delete the employee by setting status to terminated

### Requirement 5: Attendance Management API

**User Story:** As an employee, I want to clock in/out and view my attendance history through the API, so that I can track my work hours.

#### Acceptance Criteria

1. WHEN POST /api/attendance/clock-in is called THEN the Attendance_API SHALL create an attendance record with current timestamp as clock_in_time
2. WHEN POST /api/attendance/clock-out is called THEN the Attendance_API SHALL update the attendance record with clock_out_time and calculate total_hours
3. WHEN GET /api/attendance is called THEN the Attendance_API SHALL return attendance records filtered by company_id and optional date range
4. WHEN an employee clocks in twice on the same day THEN the Attendance_API SHALL return an error indicating duplicate entry
5. WHEN attendance data is retrieved THEN the Attendance_API SHALL support filtering by employee_id, date range, and status

### Requirement 6: Leave Management API

**User Story:** As an employee, I want to submit leave requests and track their status through the API, so that I can manage my time off.

#### Acceptance Criteria

1. WHEN POST /api/leave/requests is called THEN the Leave_API SHALL create a leave request with status pending
2. WHEN GET /api/leave/requests is called THEN the Leave_API SHALL return leave requests filtered by company_id and user permissions
3. WHEN PUT /api/leave/requests/{id}/approve is called by HR/Admin THEN the Leave_API SHALL update status to approved and record approver_id and approval_date
4. WHEN PUT /api/leave/requests/{id}/reject is called THEN the Leave_API SHALL update status to rejected with rejection_reason
5. WHEN GET /api/leave/balance is called THEN the Leave_API SHALL calculate and return remaining leave days per leave type for the employee

### Requirement 7: Payroll API

**User Story:** As an HR manager, I want to view and manage payroll records through the API, so that I can process employee compensation.

#### Acceptance Criteria

1. WHEN GET /api/payroll is called THEN the Payroll_API SHALL return payroll records filtered by company_id and optional year/month
2. WHEN GET /api/payroll/employee/{id} is called THEN the Payroll_API SHALL return payroll history for the specified employee
3. WHEN POST /api/payroll/process is called THEN the Payroll_API SHALL generate payroll records for all active employees for the specified month
4. WHEN employees view their own payroll THEN the Payroll_API SHALL return only their own salary and payment records
5. WHEN payroll is processed THEN the Payroll_API SHALL calculate gross_salary, total_deductions, and net_salary from salary_structures

### Requirement 8: Input Validation and Error Handling

**User Story:** As a developer, I want consistent input validation and error responses, so that API consumers can handle errors gracefully.

#### Acceptance Criteria

1. WHEN invalid input is submitted THEN the Validation_System SHALL return a 400 error with field-specific error messages
2. WHEN a resource is not found THEN the Error_Handler SHALL return a 404 error with descriptive message
3. WHEN an unauthorized request is made THEN the Error_Handler SHALL return a 401 error
4. WHEN a forbidden action is attempted THEN the Error_Handler SHALL return a 403 error
5. WHEN a server error occurs THEN the Error_Handler SHALL log the error details and return a 500 error with generic message

### Requirement 9: API Response Format

**User Story:** As an API consumer, I want consistent JSON response formats, so that I can reliably parse API responses.

#### Acceptance Criteria

1. WHEN any API request succeeds THEN the Response_System SHALL return JSON with success status, data, and optional message
2. WHEN any API request fails THEN the Response_System SHALL return JSON with error status, error code, and error message
3. WHEN returning lists THEN the Response_System SHALL include pagination metadata (total, page, per_page, total_pages)
4. WHEN returning single resources THEN the Response_System SHALL include the resource data directly in the data field
5. WHEN timestamps are returned THEN the Response_System SHALL format them in ISO 8601 format
