# Database Setup Guide

This guide explains how to set up the MySQL database for the Dayflow HRMS application.

## Prerequisites

- MySQL 5.7+ or MariaDB 10.2+
- phpMyAdmin (included with XAMPP) or MySQL command line client

## Quick Setup

### Option 1: Using phpMyAdmin (Recommended for XAMPP)

1. Start MySQL in XAMPP Control Panel
2. Open phpMyAdmin: `http://localhost/phpmyadmin`
3. Click "Import" tab
4. Select `database/schema.sql` file
5. Click "Go" to execute

### Option 2: Using MySQL Command Line

```bash
mysql -u root -p < database/schema.sql
```

### Option 3: Using MySQL Workbench

1. Open MySQL Workbench
2. Connect to your local MySQL server
3. File → Open SQL Script → Select `schema.sql`
4. Execute the script (lightning bolt icon)

## Database Schema

### Tables Overview

| Table | Description |
|-------|-------------|
| `roles` | User roles (Admin, HR, Employee) |
| `permissions` | System permissions |
| `role_permissions` | Role-permission mappings |
| `companies` | Company/tenant information |
| `users` | User accounts with authentication |
| `employees` | Employee profiles |
| `attendance` | Daily attendance records |
| `leave_types` | Leave type definitions per company |
| `leave_requests` | Employee leave requests |
| `salary_structures` | Employee salary configurations |
| `payroll_records` | Monthly payroll records |

### Entity Relationship

```
companies (1) ──────< (N) users
    │                      │
    │                      │
    └──────< (N) employees >──────┘
                  │
                  ├──< attendance
                  ├──< leave_requests
                  ├──< salary_structures
                  └──< payroll_records
```

## Table Details

### roles
Defines user roles in the system.

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| name | VARCHAR(50) | Role name (Admin, HR, Employee) |
| description | VARCHAR(255) | Role description |

Default roles:
- **Admin (ID: 1)**: Full system access
- **HR (ID: 2)**: Employee management access
- **Employee (ID: 3)**: Self-service access only

### permissions
System permissions for access control.

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| name | VARCHAR(100) | Permission identifier (e.g., employee.view) |
| module | VARCHAR(50) | Module name (employee, attendance, leave, payroll) |
| description | VARCHAR(255) | Permission description |

### companies
Multi-tenant company information.

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| name | VARCHAR(255) | Company name |
| registration_number | VARCHAR(100) | Unique registration number |
| email | VARCHAR(255) | Company email |
| phone | VARCHAR(20) | Contact phone |
| address | TEXT | Company address |
| industry | VARCHAR(100) | Industry type |
| company_size | ENUM | Size category (1-10, 11-50, etc.) |
| status | ENUM | active, inactive, suspended |

### users
User authentication accounts.

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| company_id | INT | Foreign key to companies |
| role_id | INT | Foreign key to roles |
| email | VARCHAR(255) | Login email (unique) |
| password_hash | VARCHAR(255) | Bcrypt hashed password |
| status | ENUM | active, inactive, locked |
| last_login | TIMESTAMP | Last login time |

### employees
Employee profile information.

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| company_id | INT | Foreign key to companies |
| user_id | INT | Foreign key to users (nullable) |
| employee_code | VARCHAR(50) | Unique employee code |
| first_name | VARCHAR(100) | First name |
| last_name | VARCHAR(100) | Last name |
| email | VARCHAR(255) | Work email |
| phone | VARCHAR(20) | Contact phone |
| date_of_birth | DATE | Birth date |
| gender | ENUM | male, female, other |
| hire_date | DATE | Employment start date |
| department | VARCHAR(100) | Department name |
| designation | VARCHAR(100) | Job title |
| employment_type | ENUM | full_time, part_time, contract, intern |
| status | ENUM | active, inactive, terminated |

### attendance
Daily attendance records.

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| company_id | INT | Foreign key to companies |
| employee_id | INT | Foreign key to employees |
| attendance_date | DATE | Date of attendance |
| clock_in_time | TIME | Clock in time |
| clock_out_time | TIME | Clock out time |
| total_hours | DECIMAL(4,2) | Hours worked |
| status | ENUM | present, absent, half_day, late, on_leave |

### leave_types
Company-specific leave type definitions.

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| company_id | INT | Foreign key to companies |
| name | VARCHAR(100) | Leave type name |
| annual_allocation | INT | Days allocated per year |
| is_paid | TINYINT(1) | Whether leave is paid |
| is_active | TINYINT(1) | Whether type is active |

Default leave types (created per company):
- Annual Leave (20 days, paid)
- Sick Leave (10 days, paid)
- Personal Leave (5 days, paid)
- Unpaid Leave (0 days, unpaid)

### leave_requests
Employee leave requests.

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| company_id | INT | Foreign key to companies |
| employee_id | INT | Foreign key to employees |
| leave_type_id | INT | Foreign key to leave_types |
| start_date | DATE | Leave start date |
| end_date | DATE | Leave end date |
| total_days | INT | Number of days |
| reason | TEXT | Leave reason |
| status | ENUM | pending, approved, rejected, cancelled |
| approver_id | INT | User who approved/rejected |

### salary_structures
Employee salary configurations.

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| company_id | INT | Foreign key to companies |
| employee_id | INT | Foreign key to employees |
| basic_salary | DECIMAL(12,2) | Base salary |
| housing_allowance | DECIMAL(12,2) | Housing allowance |
| transport_allowance | DECIMAL(12,2) | Transport allowance |
| other_allowances | DECIMAL(12,2) | Other allowances |
| tax_deduction | DECIMAL(12,2) | Tax deduction |
| insurance_deduction | DECIMAL(12,2) | Insurance deduction |
| other_deductions | DECIMAL(12,2) | Other deductions |
| effective_date | DATE | When structure takes effect |
| is_current | TINYINT(1) | Current active structure |

### payroll_records
Monthly payroll records.

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| company_id | INT | Foreign key to companies |
| employee_id | INT | Foreign key to employees |
| salary_structure_id | INT | Foreign key to salary_structures |
| year | INT | Payroll year |
| month | INT | Payroll month (1-12) |
| gross_salary | DECIMAL(12,2) | Total earnings |
| total_deductions | DECIMAL(12,2) | Total deductions |
| net_salary | DECIMAL(12,2) | Take-home pay |
| payment_status | ENUM | pending, processed, paid, failed |

## Sample Data

To load sample data for testing:

```bash
mysql -u root -p hrms_db < database/seed.sql
```

Or import `seed.sql` through phpMyAdmin after importing `schema.sql`.

## Troubleshooting

### Connection Issues

1. Verify MySQL is running in XAMPP
2. Check credentials in `config/database.php`
3. Test connection: `http://localhost/Dayflow---Human-Resource-Management-System/public/test-db.php`

### Permission Denied

```sql
GRANT ALL PRIVILEGES ON hrms_db.* TO 'root'@'localhost';
FLUSH PRIVILEGES;
```

### Database Already Exists

The schema.sql drops and recreates the database. To preserve data, comment out these lines:

```sql
-- DROP DATABASE IF EXISTS hrms_db;
-- CREATE DATABASE hrms_db ...
```

### Foreign Key Errors

Ensure tables are created in order. The schema.sql handles this automatically.

## Backup & Restore

### Backup

```bash
mysqldump -u root -p hrms_db > backup.sql
```

### Restore

```bash
mysql -u root -p hrms_db < backup.sql
```

## Multi-Tenant Architecture

The system uses a shared database with tenant isolation:

- Every data table has a `company_id` column
- All queries are filtered by the logged-in user's company
- Users can only access data from their own company
- The `TenantMiddleware` enforces this at the API level

This allows multiple companies to use the same application instance while keeping their data completely separate.
