# Design Document: HRMS Backend API

## Overview

This document defines the PHP Backend API architecture for the Multi-Company HRMS system. The API follows RESTful principles with session-based authentication, multi-tenant middleware, and role-based access control. All endpoints return JSON responses and enforce company-level data isolation.

## Architecture

### System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        Frontend (Browser)                        │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                      Apache (XAMPP)                              │
│                    public/index.php                              │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                         Router                                   │
│                   src/Core/Router.php                           │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                      Middleware Stack                            │
├─────────────────────────────────────────────────────────────────┤
│  1. CORS Middleware          - Handle cross-origin requests     │
│  2. Auth Middleware          - Validate session/token           │
│  3. Tenant Middleware        - Set company_id context           │
│  4. RBAC Middleware          - Check permissions                │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                        Controllers                               │
├─────────────────────────────────────────────────────────────────┤
│  AuthController      │  EmployeeController  │  AttendanceCtrl   │
│  LeaveController     │  PayrollController   │  CompanyController│
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                         Services                                 │
├─────────────────────────────────────────────────────────────────┤
│  AuthService         │  EmployeeService     │  AttendanceService│
│  LeaveService        │  PayrollService      │  ValidationService│
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                        Repositories                              │
├─────────────────────────────────────────────────────────────────┤
│  UserRepository      │  EmployeeRepository  │  AttendanceRepo   │
│  LeaveRepository     │  PayrollRepository   │  CompanyRepository│
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                     MySQL Database (XAMPP)                       │
│                         hrms_db                                  │
└─────────────────────────────────────────────────────────────────┘
```

### Directory Structure

```
project/
├── public/
│   └── index.php              # Entry point
├── src/
│   ├── Core/
│   │   ├── Router.php         # Request routing
│   │   ├── Request.php        # Request wrapper
│   │   ├── Response.php       # JSON response builder
│   │   ├── Database.php       # PDO connection singleton
│   │   └── Container.php      # Dependency injection
│   ├── Middleware/
│   │   ├── AuthMiddleware.php
│   │   ├── TenantMiddleware.php
│   │   ├── RBACMiddleware.php
│   │   └── CorsMiddleware.php
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── EmployeeController.php
│   │   ├── AttendanceController.php
│   │   ├── LeaveController.php
│   │   └── PayrollController.php
│   ├── Services/
│   │   ├── AuthService.php
│   │   ├── EmployeeService.php
│   │   ├── AttendanceService.php
│   │   ├── LeaveService.php
│   │   └── PayrollService.php
│   ├── Repositories/
│   │   ├── BaseRepository.php
│   │   ├── UserRepository.php
│   │   ├── EmployeeRepository.php
│   │   ├── AttendanceRepository.php
│   │   ├── LeaveRepository.php
│   │   └── PayrollRepository.php
│   └── Helpers/
│       ├── Validator.php
│       └── ResponseHelper.php
├── config/
│   ├── database.php
│   ├── routes.php
│   └── permissions.php
└── tests/
```

## Components and Interfaces

### API Endpoints

#### Authentication
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | /api/auth/login | User login | No |
| POST | /api/auth/logout | User logout | Yes |
| GET | /api/auth/me | Get current user | Yes |

#### Employees
| Method | Endpoint | Description | Permission |
|--------|----------|-------------|------------|
| GET | /api/employees | List employees | employee.view |
| GET | /api/employees/{id} | Get employee | employee.view |
| POST | /api/employees | Create employee | employee.create |
| PUT | /api/employees/{id} | Update employee | employee.update |
| DELETE | /api/employees/{id} | Delete employee | employee.delete |
| GET | /api/employees/me | Get own profile | employee.view_own |
| PUT | /api/employees/me | Update own profile | employee.update_own |

#### Attendance
| Method | Endpoint | Description | Permission |
|--------|----------|-------------|------------|
| GET | /api/attendance | List attendance | attendance.view |
| POST | /api/attendance/clock-in | Clock in | attendance.clock |
| POST | /api/attendance/clock-out | Clock out | attendance.clock |
| GET | /api/attendance/me | Get own attendance | attendance.view_own |
| POST | /api/attendance | Create record | attendance.create |
| PUT | /api/attendance/{id} | Update record | attendance.update |

#### Leave
| Method | Endpoint | Description | Permission |
|--------|----------|-------------|------------|
| GET | /api/leave/types | List leave types | leave.view |
| GET | /api/leave/requests | List requests | leave.view |
| POST | /api/leave/requests | Create request | leave.request |
| PUT | /api/leave/requests/{id}/approve | Approve | leave.approve |
| PUT | /api/leave/requests/{id}/reject | Reject | leave.approve |
| GET | /api/leave/balance | Get balance | leave.view_own |
| GET | /api/leave/requests/me | Own requests | leave.view_own |

#### Payroll
| Method | Endpoint | Description | Permission |
|--------|----------|-------------|------------|
| GET | /api/payroll | List payroll | payroll.view |
| GET | /api/payroll/{id} | Get record | payroll.view |
| POST | /api/payroll/process | Process payroll | payroll.create |
| GET | /api/payroll/me | Own payroll | payroll.view_own |

### Request/Response Format

#### Success Response
```json
{
    "success": true,
    "data": { ... },
    "message": "Operation successful"
}
```

#### List Response with Pagination
```json
{
    "success": true,
    "data": [ ... ],
    "pagination": {
        "total": 100,
        "page": 1,
        "per_page": 20,
        "total_pages": 5
    }
}
```

#### Error Response
```json
{
    "success": false,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "Invalid input data",
        "details": {
            "email": "Email is required",
            "password": "Password must be at least 8 characters"
        }
    }
}
```

## Data Models

### Session Data Structure
```php
$_SESSION['user'] = [
    'id' => int,
    'company_id' => int,
    'role_id' => int,
    'role_name' => string,
    'email' => string,
    'permissions' => array
];
```

### Request Context
```php
class Request {
    public int $companyId;      // Set by TenantMiddleware
    public ?array $user;        // Set by AuthMiddleware
    public array $permissions;  // Set by RBACMiddleware
    public array $body;         // Parsed JSON body
    public array $query;        // Query parameters
    public array $params;       // Route parameters
}
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Authentication Round-Trip

*For any* valid user credentials (email and password), authenticating and then accessing a protected endpoint with the returned session SHALL succeed.

**Validates: Requirements 1.1, 1.4**

### Property 2: Invalid Credentials Rejection

*For any* invalid credentials (wrong email or wrong password), the authentication response SHALL return an error without revealing which field was incorrect.

**Validates: Requirements 1.2**

### Property 3: Session Invalidation

*For any* authenticated session, after logout the session token SHALL no longer grant access to protected endpoints.

**Validates: Requirements 1.3, 1.5**

### Property 4: Multi-Tenant Data Isolation

*For any* API request to tenant-specific endpoints, the response SHALL contain only records where company_id matches the authenticated user's company_id.

**Validates: Requirements 2.1, 2.3, 4.1, 4.2**

### Property 5: Automatic Company Assignment

*For any* record creation through the API, the created record's company_id SHALL equal the authenticated user's company_id.

**Validates: Requirements 2.4, 4.3**

### Property 6: Role-Based Access Enforcement

*For any* user-endpoint combination, access SHALL be granted if and only if the user's role has the required permission in role_permissions.

**Validates: Requirements 3.1, 3.2, 3.4, 3.5**

### Property 7: Attendance Uniqueness

*For any* employee and date, attempting to clock in when a record already exists for that date SHALL return an error.

**Validates: Requirements 5.4**

### Property 8: Clock-Out Hours Calculation

*For any* clock-out operation, the total_hours field SHALL equal the difference between clock_out_time and clock_in_time in hours.

**Validates: Requirements 5.2**

### Property 9: Leave Approval Audit

*For any* leave request with status 'approved' or 'rejected', the approver_id and approval_date fields SHALL be non-null.

**Validates: Requirements 6.3, 6.4**

### Property 10: Leave Balance Calculation

*For any* employee and leave type, the balance returned by the API SHALL equal annual_allocation minus sum of approved leave days for the current year.

**Validates: Requirements 6.5**

### Property 11: Payroll Net Calculation

*For any* payroll record, net_salary SHALL equal gross_salary minus total_deductions.

**Validates: Requirements 7.5**

### Property 12: HTTP Status Code Consistency

*For any* API error, the HTTP status code SHALL match the error type: 400 for validation, 401 for unauthorized, 403 for forbidden, 404 for not found, 500 for server errors.

**Validates: Requirements 8.1, 8.2, 8.3, 8.4, 8.5**

### Property 13: Response Format Consistency

*For any* API response, the JSON structure SHALL include 'success' boolean and either 'data' (on success) or 'error' (on failure).

**Validates: Requirements 9.1, 9.2**

### Property 14: Pagination Metadata

*For any* list endpoint response, the response SHALL include pagination metadata with total, page, per_page, and total_pages.

**Validates: Requirements 9.3**

## Error Handling

### Error Codes
| Code | HTTP Status | Description |
|------|-------------|-------------|
| AUTH_REQUIRED | 401 | Authentication required |
| INVALID_CREDENTIALS | 401 | Wrong email or password |
| SESSION_EXPIRED | 401 | Session has expired |
| FORBIDDEN | 403 | Insufficient permissions |
| NOT_FOUND | 404 | Resource not found |
| VALIDATION_ERROR | 400 | Invalid input data |
| DUPLICATE_ENTRY | 409 | Record already exists |
| SERVER_ERROR | 500 | Internal server error |

### Exception Handling
```php
try {
    // Controller logic
} catch (ValidationException $e) {
    return Response::error(400, 'VALIDATION_ERROR', $e->getErrors());
} catch (AuthException $e) {
    return Response::error(401, 'AUTH_REQUIRED', $e->getMessage());
} catch (ForbiddenException $e) {
    return Response::error(403, 'FORBIDDEN', $e->getMessage());
} catch (NotFoundException $e) {
    return Response::error(404, 'NOT_FOUND', $e->getMessage());
} catch (Exception $e) {
    error_log($e->getMessage());
    return Response::error(500, 'SERVER_ERROR', 'An error occurred');
}
```

## Testing Strategy

### Property-Based Testing Library

The system will use **PHPUnit** with **Eris** for property-based tests.

### Test Categories

1. **Authentication Tests**: Login, logout, session validation
2. **Authorization Tests**: RBAC permission checks
3. **Multi-Tenant Tests**: Data isolation verification
4. **CRUD Tests**: Employee, attendance, leave, payroll operations
5. **Validation Tests**: Input validation and error responses
6. **Integration Tests**: End-to-end API workflows

### Test Configuration

Each property-based test will:
- Run a minimum of 100 iterations
- Be tagged with format: `**Feature: hrms-backend-api, Property {number}: {property_text}**`
- Use database transactions for isolation (rollback after each test)
