-- ============================================================
-- Multi-Company HRMS Database Schema
-- Compatible with XAMPP MySQL 5.7+ / MariaDB 10.2+
-- ============================================================

-- Create Database
DROP DATABASE IF EXISTS hrms_db;
CREATE DATABASE hrms_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE hrms_db;

-- ============================================================
-- CORE REFERENCE TABLES
-- ============================================================

-- Roles Table
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_roles_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Permissions Table
CREATE TABLE permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    module VARCHAR(50) NOT NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_permissions_name (name),
    INDEX idx_permissions_module (module)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Role-Permissions Junction Table
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

-- ============================================================
-- TENANT TABLES
-- ============================================================

-- Companies Table (Tenant Root)
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

-- Users Table
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


-- Employees Table
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

-- Attendance Table
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

-- Leave Types Table
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

-- Leave Requests Table
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


-- Salary Structures Table
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

-- Payroll Records Table
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

-- ============================================================
-- DEFAULT DATA INSERTS
-- ============================================================

-- Insert Default Roles
INSERT INTO roles (name, description) VALUES
('Admin', 'Company administrator with full access'),
('HR', 'Human Resources manager with employee management access'),
('Employee', 'Regular employee with self-service access');

-- Insert Default Permissions
INSERT INTO permissions (name, module, description) VALUES
-- Employee Module
('employee.view', 'employee', 'View employee profiles'),
('employee.create', 'employee', 'Create new employees'),
('employee.update', 'employee', 'Update employee information'),
('employee.delete', 'employee', 'Delete employees'),
('employee.view_own', 'employee', 'View own profile'),
('employee.update_own', 'employee', 'Update own profile'),
-- Attendance Module
('attendance.view', 'attendance', 'View all attendance records'),
('attendance.create', 'attendance', 'Create attendance records'),
('attendance.update', 'attendance', 'Update attendance records'),
('attendance.delete', 'attendance', 'Delete attendance records'),
('attendance.view_own', 'attendance', 'View own attendance'),
('attendance.clock', 'attendance', 'Clock in/out'),
-- Leave Module
('leave.view', 'leave', 'View all leave requests'),
('leave.create', 'leave', 'Create leave requests for others'),
('leave.approve', 'leave', 'Approve/reject leave requests'),
('leave.delete', 'leave', 'Delete leave requests'),
('leave.view_own', 'leave', 'View own leave requests'),
('leave.request', 'leave', 'Submit own leave requests'),
-- Payroll Module
('payroll.view', 'payroll', 'View all payroll records'),
('payroll.create', 'payroll', 'Process payroll'),
('payroll.update', 'payroll', 'Update payroll records'),
('payroll.delete', 'payroll', 'Delete payroll records'),
('payroll.view_own', 'payroll', 'View own salary/payroll'),
-- Company Module
('company.view', 'company', 'View company settings'),
('company.update', 'company', 'Update company settings'),
-- User Module
('user.view', 'user', 'View all users'),
('user.create', 'user', 'Create new users'),
('user.update', 'user', 'Update user accounts'),
('user.delete', 'user', 'Delete users');

-- Insert Role-Permission Mappings
-- Admin gets all permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions;

-- HR gets most permissions except company settings
INSERT INTO role_permissions (role_id, permission_id)
SELECT 2, id FROM permissions 
WHERE module IN ('employee', 'attendance', 'leave', 'payroll', 'user')
AND name NOT LIKE '%delete%';

-- Employee gets self-service permissions only
INSERT INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM permissions 
WHERE name LIKE '%_own' OR name IN ('attendance.clock', 'leave.request');
