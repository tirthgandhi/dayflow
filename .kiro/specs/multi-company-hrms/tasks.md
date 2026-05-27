# Implementation Plan

## Phase 1: Database Foundation

- [x] 1. Set up project structure and database configuration



  - [x] 1.1 Create project directory structure (database/, config/, tests/)

    - Create folders for SQL scripts, PHP configuration, and test files
    - Initialize composer.json for dependency management
    - _Requirements: 9.2, 9.3_

  - [x] 1.2 Create database configuration file

    - Write config/database.php with connection parameters
    - Include environment-based configuration support

    - _Requirements: 1.1_

  - [ ] 1.3 Create main SQL schema file with database creation
    - Write database/schema.sql with CREATE DATABASE statement
    - Set character encoding to utf8mb4 for full Unicode support

    - _Requirements: 1.1_


- [ ] 2. Implement core reference tables (roles, permissions)
  - [x] 2.1 Create roles table SQL

    - Define id, name, description, created_at columns
    - Add UNIQUE constraint on name
    - Insert default roles: Admin, HR, Employee
    - _Requirements: 3.2_


  - [ ] 2.2 Create permissions table SQL
    - Define id, name, module, description, created_at columns
    - Add UNIQUE constraint on name

    - Insert default permissions for each module (employee, attendance, leave, payroll)
    - _Requirements: 3.3, 3.4_
  - [ ] 2.3 Create role_permissions junction table SQL
    - Define id, role_id, permission_id columns
    - Add foreign keys to roles and permissions tables
    - Add UNIQUE constraint on (role_id, permission_id)


    - Insert default role-permission mappings


    - _Requirements: 3.3, 3.4_
  - [ ] 2.4 Write property test for role-permission mapping
    - **Property 7: Role-Based Permission Check**

    - **Validates: Requirements 3.3**

- [ ] 3. Implement companies table
  - [x] 3.1 Create companies table SQL


    - Define all columns: id, name, registration_number, email, phone, address, status, timestamps
    - Add UNIQUE constraint on registration_number

    - Add ENUM constraint for status (active, inactive, suspended)

    - Add indexes on status column
    - _Requirements: 2.1, 2.3, 8.2_
  - [x] 3.2 Write property test for company registration uniqueness

    - **Property 3: Company Registration Uniqueness**
    - **Validates: Requirements 2.3**



- [x] 4. Implement users table with authentication support

  - [ ] 4.1 Create users table SQL
    - Define all columns: id, company_id, role_id, email, password_hash, status, last_login, timestamps
    - Add foreign keys to companies and roles tables with appropriate cascade rules

    - Add UNIQUE constraint on email
    - Add ENUM constraint for status (active, inactive, locked)
    - Add index on company_id for tenant filtering
    - _Requirements: 3.1, 3.2, 3.5, 8.1_

  - [ ] 4.2 Write property test for user-company-role invariant
    - **Property 6: User-Company-Role Invariant**
    - **Validates: Requirements 3.2, 8.1**

  - [x] 4.3 Write property test for password hash round-trip


    - **Property 5: Password Hash Round-Trip**
    - **Validates: Requirements 3.1**


- [ ] 5. Implement employees table
  - [ ] 5.1 Create employees table SQL
    - Define all columns: id, company_id, user_id, employee_code, personal details, employment details, timestamps
    - Add foreign keys to companies and users tables
    - Add UNIQUE constraint on user_id (one-to-one with users)

    - Add UNIQUE constraint on (company_id, employee_code)
    - Add ENUM constraints for gender, employment_type, status


    - Add indexes on company_id, user_id


    - _Requirements: 4.1, 4.4, 8.1, 8.2_
  - [ ] 5.2 Write property test for employee-user one-to-one relationship
    - **Property 9: Employee-User One-to-One Relationship**

    - **Validates: Requirements 4.4**

- [ ] 6. Implement attendance table
  - [ ] 6.1 Create attendance table SQL
    - Define all columns: id, company_id, employee_id, attendance_date, clock_in_time, clock_out_time, total_hours, status, notes, timestamps
    - Add foreign keys to companies and employees tables with CASCADE on delete
    - Add UNIQUE constraint on (employee_id, attendance_date)
    - Add ENUM constraint for status (present, absent, half_day, late, on_leave)

    - Add indexes on company_id, employee_id, attendance_date
    - _Requirements: 5.1, 5.2, 5.3, 8.1, 8.2_
  - [ ] 6.2 Write property test for attendance uniqueness per day
    - **Property 10: Attendance Uniqueness Per Day**

    - **Validates: Requirements 5.2**




  - [ ] 6.3 Write property test for total hours calculation
    - **Property 11: Total Hours Calculation**
    - **Validates: Requirements 5.4**



- [ ] 7. Implement leave management tables
  - [ ] 7.1 Create leave_types table SQL
    - Define all columns: id, company_id, name, annual_allocation, is_paid, is_active, timestamps
    - Add foreign key to companies table with CASCADE on delete
    - Add UNIQUE constraint on (company_id, name)
    - Add index on company_id
    - _Requirements: 6.2, 8.1_
  - [ ] 7.2 Create leave_requests table SQL
    - Define all columns: id, company_id, employee_id, leave_type_id, dates, total_days, reason, status, approver_id, approval_date, rejection_reason, timestamps


    - Add foreign keys to companies, employees, leave_types, users (approver)
    - Add ENUM constraint for status (pending, approved, rejected, cancelled)


    - Add indexes on company_id, employee_id, leave_type_id, status
    - _Requirements: 6.1, 6.3, 8.1, 8.2_


  - [x] 7.3 Write property test for leave approval audit trail

    - **Property 13: Leave Approval Audit Trail**


    - **Validates: Requirements 6.3**


  - [ ] 7.4 Write property test for leave balance calculation
    - **Property 12: Leave Balance Calculation**

    - **Validates: Requirements 6.4**



- [ ] 8. Implement payroll tables
  - [x] 8.1 Create salary_structures table SQL

    - Define all columns: id, company_id, employee_id, basic_salary, allowances, deductions, effective_date, is_current, timestamps
    - Add foreign keys to companies and employees tables with CASCADE on delete
    - Add index on company_id, employee_id, effective_date
    - _Requirements: 7.1, 8.1_
  - [ ] 8.2 Create payroll_records table SQL
    - Define all columns: id, company_id, employee_id, salary_structure_id, year, month, gross_salary, total_deductions, net_salary, payment_date, payment_status, payment_reference, timestamps
    - Add foreign keys to companies, employees, salary_structures
    - Add UNIQUE constraint on (employee_id, year, month)
    - Add ENUM constraint for payment_status (pending, processed, paid, failed)


    - Add CHECK constraint for month (1-12)
    - Add indexes on company_id, employee_id, (year, month)
    - _Requirements: 7.2, 7.3, 8.1, 8.2_
  - [ ] 8.3 Write property test for payroll net salary calculation
    - **Property 14: Payroll Net Salary Calculation**
    - **Validates: Requirements 7.2**

- [ ] 9. Checkpoint - Verify database schema
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 10. Implement multi-tenant isolation and cascade tests
  - [ ] 10.1 Create test utilities for database operations
    - Write PHP helper functions for test data generation
    - Create database connection wrapper for tests
    - _Requirements: 1.2_
  - [ ] 10.2 Write property test for multi-tenant data isolation
    - **Property 1: Multi-Tenant Data Isolation**
    - **Validates: Requirements 1.2, 2.4, 4.2**
  - [x] 10.3 Write property test for referential integrity cascade

    - **Property 2: Referential Integrity Cascade**
    - **Validates: Requirements 1.4, 8.4**
  - [ ] 10.4 Write property test for timestamp auto-population
    - **Property 8: Timestamp Auto-Population**
    - **Validates: Requirements 4.3, 8.3**
  - [ ] 10.5 Write property test for enum constraint enforcement
    - **Property 15: Enum Constraint Enforcement**
    - **Validates: Requirements 8.2**

- [x] 11. Create seed data and documentation

  - [ ] 11.1 Create seed data SQL script
    - Write database/seed.sql with sample companies, users, employees
    - Include sample attendance, leave, and payroll records
    - Ensure data demonstrates multi-tenant isolation
    - _Requirements: 9.2_

  - [x] 11.2 Update README.md with complete database documentation

    - Document all tables with column descriptions
    - Include ER diagram
    - Document multi-tenant isolation strategy
    - Include setup instructions and SQL script execution order
    - _Requirements: 9.1, 9.2, 9.3, 9.4_

- [ ] 12. Final Checkpoint - Verify complete database foundation
  - Ensure all tests pass, ask the user if questions arise.
