# Database Schema Analysis

## Overview

The Dayflow HRMS uses a well-designed MySQL database schema optimized for multi-tenant operations with strong referential integrity and performance considerations.

## Database Tables

### Core Reference Tables

#### 1. Roles Table
```sql
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_roles_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Purpose**: Defines user roles (Admin, HR, Employee)
**Key Features**:
- Unique constraint on role names
- UTF8MB4 charset for international support
- InnoDB engine for ACID compliance

#### 2. Permissions Table
```sql
CREATE TABLE permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    module VARCHAR(50) NOT NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_permissions_name (name),
    INDEX idx_permissions_module (module)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Purpose**: Granular permission system for RBAC
**Key Features**:
- Module-based organization
- Indexed by module for fast lookups
- 25+ predefined permissions

#### 3. Role-Permissions Junction Table
```sql
CREATE TABLE role_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    UNIQUE KEY uk_role_permission (role_id, permission_id),
    CONSTRAINT fk_rp_role FOREIGN KEY (role_id) 
        REFERENCES roles(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_rp_permission FOREIGN KEY (permission_id) 
        REFERENCES permissions(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Purpose**: Many-to-many relationship between roles and permissions
**Key Features**:
- Composite unique constraint prevents duplicates
- Cascade deletion maintains integrity

### Tenant Tables

#### 4. Companies Table (Tenant Root)
```sql
CREATE TABLE companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    registration_number VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    address TEXT NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(100) NULL,
    country VARCHAR(100) DEFAULT 'USA',
    postal_code VARCHAR(20) NULL,
    website VARCHAR(255) NULL,
    industry VARCHAR(100) NULL,
    company_size ENUM('1-10', '11-50', '51-200', '201-500', '501-1000', '1000+') DEFAULT '51-200',
    logo_path VARCHAR(255) NULL,
    logo_url VARCHAR(500) NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    subscription_plan ENUM('free', 'basic', 'professional', 'enterprise') DEFAULT 'professional',
    subscription_expires DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_companies_registration (registration_number),
    INDEX idx_companies_status (status),
    INDEX idx_companies_industry (industry)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Purpose**: Root tenant entity for multi-company support
**Key Features**:
- Comprehensive company profile data
- Subscription management fields
- Status tracking with ENUM constraints
- Automatic timestamp management

#### 5. Users Table
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    role_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive', 'locked') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_users_email (email),
    INDEX idx_users_company (company_id),
    INDEX idx_users_status (status),
    CONSTRAINT fk_users_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) 
        REFERENCES roles(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Purpose**: User authentication and company association
**Key Features**:
- Tenant isolation via company_id
- Role-based access control
- Secure password storage
- Login tracking

#### 6. Employees Table
```sql
CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    user_id INT NULL,
    employee_code VARCHAR(50) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    date_of_birth DATE NULL,
    gender ENUM('male', 'female', 'other') NULL,
    address TEXT NULL,
    hire_date DATE NOT NULL,
    termination_date DATE NULL,
    department VARCHAR(100) NULL,
    designation VARCHAR(100) NULL,
    employment_type ENUM('full_time', 'part_time', 'contract', 'intern') DEFAULT 'full_time',
    status ENUM('active', 'inactive', 'terminated') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_employees_user (user_id),
    UNIQUE KEY uk_employees_code (company_id, employee_code),
    INDEX idx_employees_company (company_id),
    INDEX idx_employees_status (status),
    INDEX idx_employees_department (department),
    CONSTRAINT fk_employees_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_employees_user FOREIGN KEY (user_id) 
        REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Purpose**: Employee profile and HR data management
**Key Features**:
- Comprehensive employee information
- Flexible user association (employees can exist without user accounts)
- Department and designation tracking
- Employment lifecycle management

#### 7. Attendance Table
```sql
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    employee_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    clock_in_time TIME NULL,
    clock_out_time TIME NULL,
    total_hours DECIMAL(4,2) NULL,
    status ENUM('present', 'absent', 'half_day', 'late', 'on_leave') DEFAULT 'present',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_attendance_employee_date (employee_id, attendance_date),
    INDEX idx_attendance_company (company_id),
    INDEX idx_attendance_date (attendance_date),
    INDEX idx_attendance_status (status),
    CONSTRAINT fk_attendance_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_attendance_employee FOREIGN KEY (employee_id) 
        REFERENCES employees(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Purpose**: Daily attendance tracking and time management
**Key Features**:
- One record per employee per day (unique constraint)
- Flexible time tracking (clock in/out or manual entry)
- Automatic total hours calculation
- Multiple attendance statuses

#### 8. Leave Types Table
```sql
CREATE TABLE leave_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    annual_allocation INT NOT NULL DEFAULT 0,
    is_paid TINYINT(1) DEFAULT 1,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_leave_types_name (company_id, name),
    INDEX idx_leave_types_company (company_id),
    CONSTRAINT fk_leave_types_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Purpose**: Company-specific leave type configuration
**Key Features**:
- Tenant-specific leave policies
- Annual allocation tracking
- Paid/unpaid leave distinction
- Active/inactive status management

#### 9. Leave Requests Table
```sql
CREATE TABLE leave_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    employee_id INT NOT NULL,
    leave_type_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_days INT NOT NULL,
    reason TEXT NULL,
    status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
    approver_id INT NULL,
    approval_date TIMESTAMP NULL,
    rejection_reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_leave_requests_company (company_id),
    INDEX idx_leave_requests_employee (employee_id),
    INDEX idx_leave_requests_type (leave_type_id),
    INDEX idx_leave_requests_status (status),
    INDEX idx_leave_requests_dates (start_date, end_date),
    CONSTRAINT fk_leave_requests_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_leave_requests_employee FOREIGN KEY (employee_id) 
        REFERENCES employees(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_leave_requests_type FOREIGN KEY (leave_type_id) 
        REFERENCES leave_types(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_leave_requests_approver FOREIGN KEY (approver_id) 
        REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Purpose**: Leave request workflow management
**Key Features**:
- Complete approval workflow
- Date range validation
- Approver tracking
- Comprehensive status management

#### 10. Salary Structures Table
```sql
CREATE TABLE salary_structures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    employee_id INT NOT NULL,
    basic_salary DECIMAL(12,2) NOT NULL,
    housing_allowance DECIMAL(12,2) DEFAULT 0.00,
    transport_allowance DECIMAL(12,2) DEFAULT 0.00,
    other_allowances DECIMAL(12,2) DEFAULT 0.00,
    tax_deduction DECIMAL(12,2) DEFAULT 0.00,
    insurance_deduction DECIMAL(12,2) DEFAULT 0.00,
    other_deductions DECIMAL(12,2) DEFAULT 0.00,
    effective_date DATE NOT NULL,
    is_current TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_salary_company (company_id),
    INDEX idx_salary_employee (employee_id),
    INDEX idx_salary_effective (effective_date),
    INDEX idx_salary_current (is_current),
    CONSTRAINT fk_salary_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_salary_employee FOREIGN KEY (employee_id) 
        REFERENCES employees(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Purpose**: Employee salary structure and compensation management
**Key Features**:
- Detailed salary breakdown
- Historical salary tracking
- Effective date management
- Current salary identification

#### 11. Payroll Records Table
```sql
CREATE TABLE payroll_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    employee_id INT NOT NULL,
    salary_structure_id INT NOT NULL,
    year INT NOT NULL,
    month INT NOT NULL,
    gross_salary DECIMAL(12,2) NOT NULL,
    total_deductions DECIMAL(12,2) NOT NULL,
    net_salary DECIMAL(12,2) NOT NULL,
    payment_date DATE NULL,
    payment_status ENUM('pending', 'processed', 'paid', 'failed') DEFAULT 'pending',
    payment_reference VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_payroll_employee_month (employee_id, year, month),
    INDEX idx_payroll_company (company_id),
    INDEX idx_payroll_employee (employee_id),
    INDEX idx_payroll_period (year, month),
    INDEX idx_payroll_status (payment_status),
    CONSTRAINT fk_payroll_company FOREIGN KEY (company_id) 
        REFERENCES companies(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_payroll_employee FOREIGN KEY (employee_id) 
        REFERENCES employees(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_payroll_salary FOREIGN KEY (salary_structure_id) 
        REFERENCES salary_structures(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_payroll_month CHECK (month >= 1 AND month <= 12)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Purpose**: Monthly payroll processing and payment tracking
**Key Features**:
- One payroll record per employee per month
- Complete salary calculation tracking
- Payment status workflow
- Check constraints for data validation

## Schema Statistics

| Table | Estimated Rows | Storage Engine | Charset |
|-------|---------------|----------------|---------|
| companies | 1,000+ | InnoDB | utf8mb4 |
| users | 50,000+ | InnoDB | utf8mb4 |
| employees | 50,000+ | InnoDB | utf8mb4 |
| attendance | 1,000,000+ | InnoDB | utf8mb4 |
| leave_requests | 100,000+ | InnoDB | utf8mb4 |
| payroll_records | 500,000+ | InnoDB | utf8mb4 |

## Key Design Principles

### 1. Multi-Tenancy
- Every tenant table includes `company_id` for data isolation
- Cascade deletion ensures clean tenant removal
- Indexes on `company_id` for performance

### 2. Referential Integrity
- 15+ foreign key constraints
- Appropriate cascade rules (CASCADE, RESTRICT, SET NULL)
- Check constraints for data validation

### 3. Performance Optimization
- Strategic indexing on frequently queried columns
- Composite indexes for complex queries
- Unique constraints prevent data duplication

### 4. Data Types & Storage
- DECIMAL for financial calculations (precision)
- ENUM for controlled vocabularies
- TEXT for variable-length content
- Appropriate VARCHAR lengths

### 5. Audit Trail
- `created_at` and `updated_at` timestamps on all tables
- Automatic timestamp management
- Historical data preservation

## Relationships Overview

```
companies (1) ──→ (∞) users
companies (1) ──→ (∞) employees  
companies (1) ──→ (∞) attendance
companies (1) ──→ (∞) leave_types
companies (1) ──→ (∞) leave_requests
companies (1) ──→ (∞) salary_structures
companies (1) ──→ (∞) payroll_records

users (1) ──→ (1) employees [optional]
users (∞) ──→ (∞) roles [via role_permissions]

employees (1) ──→ (∞) attendance
employees (1) ──→ (∞) leave_requests
employees (1) ──→ (∞) salary_structures
employees (1) ──→ (∞) payroll_records

leave_types (1) ──→ (∞) leave_requests
salary_structures (1) ──→ (∞) payroll_records
```

This schema design provides a solid foundation for a scalable, multi-tenant HRMS system with strong data integrity and performance characteristics.