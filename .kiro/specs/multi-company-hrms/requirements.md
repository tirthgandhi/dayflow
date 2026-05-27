# Requirements Document

## Introduction

This document defines the requirements for a Multi-Company Human Resource Management System (HRMS) built with PHP and MySQL. The system enables multiple organizations to register on a single platform and independently manage their employees, attendance, leave, and payroll information. The architecture follows a strict database-first approach with multi-tenant data isolation, ensuring each company's data remains completely separate while sharing the same infrastructure.

## Glossary

- **HRMS**: Human Resource Management System - software for managing employee-related processes
- **Multi-tenant**: Architecture where a single instance serves multiple organizations with isolated data
- **Company**: An organization registered on the platform with its own isolated data space
- **Admin**: Company administrator with full access to manage all company resources
- **HR**: Human Resources role with permissions to manage employees, attendance, leave, and payroll
- **Employee**: Staff member who can view and manage their own profile, attendance, leave, and salary
- **Attendance**: Daily record of employee work hours including clock-in and clock-out times
- **Leave**: Time-off requests including vacation, sick leave, and other absence types
- **Payroll**: Salary structure and payment records for employees
- **RBAC**: Role-Based Access Control - permission system based on user roles
- **Data Isolation**: Ensuring each company can only access its own data via company_id scoping

## Requirements

### Requirement 1: Multi-Tenant Database Foundation

**User Story:** As a system architect, I want a properly normalized multi-tenant database schema, so that multiple companies can use the platform with complete data isolation.

#### Acceptance Criteria

1. WHEN the database is created THEN the HRMS_Database SHALL contain tables for companies, users, roles, permissions, employees, attendance, leave_types, leave_requests, salary_structures, and payroll_records with proper primary keys and foreign key relationships
2. WHEN any data table is queried THEN the HRMS_Database SHALL enforce company-level data isolation through company_id foreign key constraints on all tenant-specific tables
3. WHEN tables are created THEN the HRMS_Database SHALL implement proper normalization (3NF minimum) to eliminate data redundancy and ensure data integrity
4. WHEN foreign key relationships are defined THEN the HRMS_Database SHALL use appropriate ON DELETE and ON UPDATE cascade rules to maintain referential integrity
5. WHEN frequently queried columns are identified THEN the HRMS_Database SHALL include indexes on company_id, user_id, employee_id, and date-based columns for optimal query performance

### Requirement 2: Company Registration and Management

**User Story:** As a company administrator, I want to register my organization on the platform, so that I can start managing my HR operations.

#### Acceptance Criteria

1. WHEN a company registers THEN the HRMS_System SHALL create a company record with unique identifier, name, registration number, contact details, and subscription status
2. WHEN a company is created THEN the HRMS_System SHALL automatically create an Admin user account linked to that company
3. WHEN company data is stored THEN the HRMS_Database SHALL validate that company registration numbers are unique across the platform
4. WHEN a company record is accessed THEN the HRMS_System SHALL return only data belonging to that specific company_id

### Requirement 3: User Authentication and Role-Based Access Control

**User Story:** As a system user, I want secure authentication with role-based permissions, so that I can access only the features appropriate to my role.

#### Acceptance Criteria

1. WHEN a user attempts to login THEN the HRMS_System SHALL validate credentials against securely hashed passwords stored in the database
2. WHEN a user is created THEN the HRMS_Database SHALL associate the user with exactly one company and one role (Admin, HR, or Employee)
3. WHEN a user accesses a resource THEN the HRMS_System SHALL verify the user has the required permission through their assigned role
4. WHEN permissions are checked THEN the HRMS_Database SHALL retrieve role-permission mappings from the role_permissions junction table
5. WHEN a user session is created THEN the HRMS_System SHALL store session data with company_id to enforce tenant isolation in all subsequent requests

### Requirement 4: Employee Profile Management

**User Story:** As an HR manager, I want to onboard and manage employee profiles, so that I can maintain accurate employee records.

#### Acceptance Criteria

1. WHEN an employee is created THEN the HRMS_Database SHALL store personal details, employment details, and link the record to the company via company_id
2. WHEN employee data is retrieved THEN the HRMS_System SHALL return only employees belonging to the requesting user's company
3. WHEN an employee profile is updated THEN the HRMS_Database SHALL maintain audit timestamps (created_at, updated_at) for tracking changes
4. WHEN an employee is linked to a user account THEN the HRMS_Database SHALL establish a one-to-one relationship between the employee and user tables

### Requirement 5: Attendance Tracking

**User Story:** As an HR manager, I want to track employee attendance, so that I can monitor work hours and generate attendance reports.

#### Acceptance Criteria

1. WHEN an attendance record is created THEN the HRMS_Database SHALL store employee_id, date, clock_in_time, clock_out_time, and status with company_id for isolation
2. WHEN attendance is recorded THEN the HRMS_Database SHALL enforce a unique constraint on employee_id and date combination to prevent duplicate entries
3. WHEN attendance data is queried THEN the HRMS_System SHALL filter records by company_id and support date range filtering
4. WHEN clock_out_time is recorded THEN the HRMS_System SHALL calculate and store total_hours worked for that day

### Requirement 6: Leave Management

**User Story:** As an employee, I want to request time off and track my leave balance, so that I can manage my absences effectively.

#### Acceptance Criteria

1. WHEN a leave request is submitted THEN the HRMS_Database SHALL store employee_id, leave_type_id, start_date, end_date, reason, and status (pending/approved/rejected)
2. WHEN leave types are configured THEN the HRMS_Database SHALL allow each company to define custom leave types with annual allocation days
3. WHEN a leave request status changes THEN the HRMS_Database SHALL record the approver_id and approval_date for audit purposes
4. WHEN leave balance is calculated THEN the HRMS_System SHALL compute remaining days based on leave_type allocation minus approved leave days for the current year

### Requirement 7: Payroll and Salary Management

**User Story:** As an HR manager, I want to manage salary structures and payroll records, so that I can ensure accurate compensation for employees.

#### Acceptance Criteria

1. WHEN a salary structure is defined THEN the HRMS_Database SHALL store employee_id, basic_salary, allowances, deductions, and effective_date with company_id isolation
2. WHEN payroll is processed THEN the HRMS_Database SHALL create payroll_records with gross_salary, net_salary, payment_date, and payment_status
3. WHEN salary history is queried THEN the HRMS_System SHALL return records ordered by effective_date to show salary progression
4. WHEN an employee views their salary THEN the HRMS_System SHALL display only their own payroll records based on their employee_id

### Requirement 8: Data Integrity and Constraints

**User Story:** As a database administrator, I want proper constraints and validation rules, so that the database maintains data integrity at all times.

#### Acceptance Criteria

1. WHEN data is inserted THEN the HRMS_Database SHALL enforce NOT NULL constraints on required fields (company_id, user_id, employee_id, dates)
2. WHEN enumerated values are stored THEN the HRMS_Database SHALL use ENUM or CHECK constraints for status fields (active/inactive, pending/approved/rejected)
3. WHEN timestamps are needed THEN the HRMS_Database SHALL auto-populate created_at on INSERT and updated_at on UPDATE using database triggers or defaults
4. WHEN a parent record is deleted THEN the HRMS_Database SHALL handle child records according to defined cascade rules to prevent orphaned data

### Requirement 9: Database Schema Documentation

**User Story:** As a developer, I want comprehensive database documentation, so that I can understand the schema and implement the backend correctly.

#### Acceptance Criteria

1. WHEN the schema is finalized THEN the Documentation SHALL include an Entity-Relationship (ER) diagram showing all tables and relationships
2. WHEN SQL scripts are created THEN the Documentation SHALL include complete CREATE TABLE statements with all constraints, indexes, and relationships
3. WHEN the README is updated THEN the Documentation SHALL describe each table's purpose, columns, and relationships in detail
4. WHEN the database is designed THEN the Documentation SHALL explain the multi-tenant isolation strategy and indexing approach
