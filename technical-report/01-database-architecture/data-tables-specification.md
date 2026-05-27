# Data Tables Specification

## Complete Database Tables Documentation

### Table 1: companies (Tenant Root)

| Column | Data Type | Length | Null | Default | Key | Description |
|--------|-----------|--------|------|---------|-----|-------------|
| id | INT | - | NO | AUTO_INCREMENT | PK | Unique company identifier |
| name | VARCHAR | 255 | NO | - | - | Company legal name |
| registration_number | VARCHAR | 100 | NO | - | UK | Government registration number |
| email | VARCHAR | 255 | NO | - | - | Primary company email |
| phone | VARCHAR | 20 | YES | NULL | - | Company phone number |
| address | TEXT | - | YES | NULL | - | Company physical address |
| city | VARCHAR | 100 | YES | NULL | - | Company city |
| state | VARCHAR | 100 | YES | NULL | - | Company state/province |
| country | VARCHAR | 100 | YES | 'USA' | - | Company country |
| postal_code | VARCHAR | 20 | YES | NULL | - | Postal/ZIP code |
| website | VARCHAR | 255 | YES | NULL | - | Company website URL |
| industry | VARCHAR | 100 | YES | NULL | IDX | Industry classification |
| company_size | ENUM | - | YES | '51-200' | IDX | Employee count range |
| logo_path | VARCHAR | 255 | YES | NULL | - | Local logo file path |
| logo_url | VARCHAR | 500 | YES | NULL | - | External logo URL |
| status | ENUM | - | YES | 'active' | IDX | Company status |
| subscription_plan | ENUM | - | YES | 'professional' | - | Subscription tier |
| subscription_expires | DATE | - | YES | NULL | - | Subscription expiry date |
| created_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | - | Record creation time |
| updated_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | - | Last update time |

**Enums:**
- company_size: '1-10', '11-50', '51-200', '201-500', '501-1000', '1000+'
- status: 'active', 'inactive', 'suspended'
- subscription_plan: 'free', 'basic', 'professional', 'enterprise'

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY uk_companies_registration (registration_number)
- INDEX idx_companies_status (status)
- INDEX idx_companies_industry (industry)

---

### Table 2: roles (System Reference)

| Column | Data Type | Length | Null | Default | Key | Description |
|--------|-----------|--------|------|---------|-----|-------------|
| id | INT | - | NO | AUTO_INCREMENT | PK | Unique role identifier |
| name | VARCHAR | 50 | NO | - | UK | Role name |
| description | VARCHAR | 255 | YES | NULL | - | Role description |
| created_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | - | Record creation time |

**Default Data:**
1. Admin - Company administrator with full access
2. HR - Human Resources manager with employee management access  
3. Employee - Regular employee with self-service access

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY uk_roles_name (name)

---

### Table 3: permissions (System Reference)

| Column | Data Type | Length | Null | Default | Key | Description |
|--------|-----------|--------|------|---------|-----|-------------|
| id | INT | - | NO | AUTO_INCREMENT | PK | Unique permission identifier |
| name | VARCHAR | 100 | NO | - | UK | Permission name |
| module | VARCHAR | 50 | NO | - | IDX | Module/feature group |
| description | VARCHAR | 255 | YES | NULL | - | Permission description |
| created_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | - | Record creation time |

**Permission Modules:**
- employee (6 permissions)
- attendance (6 permissions)  
- leave (6 permissions)
- payroll (5 permissions)
- company (2 permissions)
- user (4 permissions)

**Total Permissions:** 29 granular permissions

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY uk_permissions_name (name)
- INDEX idx_permissions_module (module)

---

### Table 4: role_permissions (Junction Table)

| Column | Data Type | Length | Null | Default | Key | Description |
|--------|-----------|--------|------|---------|-----|-------------|
| id | INT | - | NO | AUTO_INCREMENT | PK | Unique mapping identifier |
| role_id | INT | - | NO | - | FK | Reference to roles table |
| permission_id | INT | - | NO | - | FK | Reference to permissions table |

**Relationships:**
- Admin Role: All 29 permissions
- HR Role: 20 permissions (no delete operations)
- Employee Role: 6 self-service permissions

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY uk_role_permission (role_id, permission_id)
- FOREIGN KEY fk_rp_role (role_id) REFERENCES roles(id)
- FOREIGN KEY fk_rp_permission (permission_id) REFERENCES permissions(id)

---

### Table 5: users (Tenant Entity)

| Column | Data Type | Length | Null | Default | Key | Description |
|--------|-----------|--------|------|---------|-----|-------------|
| id | INT | - | NO | AUTO_INCREMENT | PK | Unique user identifier |
| company_id | INT | - | NO | - | FK, IDX | Company association |
| role_id | INT | - | NO | - | FK | User role assignment |
| email | VARCHAR | 255 | NO | - | UK | User email address |
| password_hash | VARCHAR | 255 | NO | - | - | Encrypted password |
| status | ENUM | - | YES | 'active' | IDX | User account status |
| last_login | TIMESTAMP | - | YES | NULL | - | Last login timestamp |
| created_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | - | Record creation time |
| updated_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | - | Last update time |

**Enums:**
- status: 'active', 'inactive', 'locked'

**Security Features:**
- bcrypt password hashing (cost factor 12)
- Account lockout after 5 failed attempts
- Session-based authentication
- Email uniqueness across system

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY uk_users_email (email)
- INDEX idx_users_company (company_id)
- INDEX idx_users_status (status)
- FOREIGN KEY fk_users_company (company_id) REFERENCES companies(id)
- FOREIGN KEY fk_users_role (role_id) REFERENCES roles(id)

---

### Table 6: employees (Core Entity)

| Column | Data Type | Length | Null | Default | Key | Description |
|--------|-----------|--------|------|---------|-----|-------------|
| id | INT | - | NO | AUTO_INCREMENT | PK | Unique employee identifier |
| company_id | INT | - | NO | - | FK, IDX | Company association |
| user_id | INT | - | YES | NULL | FK, UK | Optional user account |
| employee_code | VARCHAR | 50 | NO | - | - | Company-specific employee ID |
| first_name | VARCHAR | 100 | NO | - | - | Employee first name |
| last_name | VARCHAR | 100 | NO | - | - | Employee last name |
| email | VARCHAR | 255 | NO | - | - | Employee email address |
| phone | VARCHAR | 20 | YES | NULL | - | Employee phone number |
| date_of_birth | DATE | - | YES | NULL | - | Employee birth date |
| gender | ENUM | - | YES | NULL | - | Employee gender |
| address | TEXT | - | YES | NULL | - | Employee address |
| hire_date | DATE | - | NO | - | - | Employment start date |
| termination_date | DATE | - | YES | NULL | - | Employment end date |
| department | VARCHAR | 100 | YES | NULL | IDX | Employee department |
| designation | VARCHAR | 100 | YES | NULL | - | Job title/position |
| employment_type | ENUM | - | YES | 'full_time' | - | Employment classification |
| status | ENUM | - | YES | 'active' | IDX | Employee status |
| created_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | - | Record creation time |
| updated_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | - | Last update time |

**Enums:**
- gender: 'male', 'female', 'other'
- employment_type: 'full_time', 'part_time', 'contract', 'intern'
- status: 'active', 'inactive', 'terminated'

**Business Rules:**
- Employee code must be unique within company
- User account is optional (employees can exist without login)
- One user account can have only one employee profile

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY uk_employees_user (user_id)
- UNIQUE KEY uk_employees_code (company_id, employee_code)
- INDEX idx_employees_company (company_id)
- INDEX idx_employees_status (status)
- INDEX idx_employees_department (department)
- FOREIGN KEY fk_employees_company (company_id) REFERENCES companies(id)
- FOREIGN KEY fk_employees_user (user_id) REFERENCES users(id)

---

### Table 7: attendance (Transaction Table)

| Column | Data Type | Length | Null | Default | Key | Description |
|--------|-----------|--------|------|---------|-----|-------------|
| id | INT | - | NO | AUTO_INCREMENT | PK | Unique attendance identifier |
| company_id | INT | - | NO | - | FK, IDX | Company association |
| employee_id | INT | - | NO | - | FK | Employee reference |
| attendance_date | DATE | - | NO | - | IDX | Attendance date |
| clock_in_time | TIME | - | YES | NULL | - | Clock in time |
| clock_out_time | TIME | - | YES | NULL | - | Clock out time |
| total_hours | DECIMAL | 4,2 | YES | NULL | - | Total hours worked |
| status | ENUM | - | YES | 'present' | IDX | Attendance status |
| notes | TEXT | - | YES | NULL | - | Additional notes |
| created_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | - | Record creation time |
| updated_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | - | Last update time |

**Enums:**
- status: 'present', 'absent', 'half_day', 'late', 'on_leave'

**Business Rules:**
- One attendance record per employee per day
- Total hours calculated automatically from clock times
- Status determines payroll calculations

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY uk_attendance_employee_date (employee_id, attendance_date)
- INDEX idx_attendance_company (company_id)
- INDEX idx_attendance_date (attendance_date)
- INDEX idx_attendance_status (status)
- FOREIGN KEY fk_attendance_company (company_id) REFERENCES companies(id)
- FOREIGN KEY fk_attendance_employee (employee_id) REFERENCES employees(id)

---

### Table 8: leave_types (Configuration Table)

| Column | Data Type | Length | Null | Default | Key | Description |
|--------|-----------|--------|------|---------|-----|-------------|
| id | INT | - | NO | AUTO_INCREMENT | PK | Unique leave type identifier |
| company_id | INT | - | NO | - | FK, IDX | Company association |
| name | VARCHAR | 100 | NO | - | - | Leave type name |
| annual_allocation | INT | - | NO | 0 | - | Days allocated per year |
| is_paid | TINYINT | 1 | YES | 1 | - | Paid leave flag |
| is_active | TINYINT | 1 | YES | 1 | - | Active status flag |
| created_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | - | Record creation time |
| updated_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | - | Last update time |

**Default Leave Types (Created on Company Registration):**
1. Annual Leave: 20 days/year (Paid)
2. Sick Leave: 10 days/year (Paid)
3. Personal Leave: 5 days/year (Paid)
4. Unpaid Leave: Unlimited (Unpaid)

**Business Rules:**
- Leave type names must be unique within company
- Annual allocation defines yearly entitlement
- Paid flag affects payroll calculations

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY uk_leave_types_name (company_id, name)
- INDEX idx_leave_types_company (company_id)
- FOREIGN KEY fk_leave_types_company (company_id) REFERENCES companies(id)

---

### Table 9: leave_requests (Transaction Table)

| Column | Data Type | Length | Null | Default | Key | Description |
|--------|-----------|--------|------|---------|-----|-------------|
| id | INT | - | NO | AUTO_INCREMENT | PK | Unique request identifier |
| company_id | INT | - | NO | - | FK, IDX | Company association |
| employee_id | INT | - | NO | - | FK, IDX | Employee making request |
| leave_type_id | INT | - | NO | - | FK, IDX | Type of leave requested |
| start_date | DATE | - | NO | - | IDX | Leave start date |
| end_date | DATE | - | NO | - | IDX | Leave end date |
| total_days | INT | - | NO | - | - | Total days requested |
| reason | TEXT | - | YES | NULL | - | Reason for leave |
| status | ENUM | - | YES | 'pending' | IDX | Request status |
| approver_id | INT | - | YES | NULL | FK | User who approved/rejected |
| approval_date | TIMESTAMP | - | YES | NULL | - | Approval/rejection date |
| rejection_reason | TEXT | - | YES | NULL | - | Reason for rejection |
| created_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | - | Record creation time |
| updated_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | - | Last update time |

**Enums:**
- status: 'pending', 'approved', 'rejected', 'cancelled'

**Workflow:**
1. Employee submits request (status: pending)
2. Manager/HR reviews request
3. Request approved/rejected with optional reason
4. Employee notified of decision

**Indexes:**
- PRIMARY KEY (id)
- INDEX idx_leave_requests_company (company_id)
- INDEX idx_leave_requests_employee (employee_id)
- INDEX idx_leave_requests_type (leave_type_id)
- INDEX idx_leave_requests_status (status)
- INDEX idx_leave_requests_dates (start_date, end_date)
- FOREIGN KEY fk_leave_requests_company (company_id) REFERENCES companies(id)
- FOREIGN KEY fk_leave_requests_employee (employee_id) REFERENCES employees(id)
- FOREIGN KEY fk_leave_requests_type (leave_type_id) REFERENCES leave_types(id)
- FOREIGN KEY fk_leave_requests_approver (approver_id) REFERENCES users(id)

---

### Table 10: salary_structures (Configuration Table)

| Column | Data Type | Length | Null | Default | Key | Description |
|--------|-----------|--------|------|---------|-----|-------------|
| id | INT | - | NO | AUTO_INCREMENT | PK | Unique structure identifier |
| company_id | INT | - | NO | - | FK, IDX | Company association |
| employee_id | INT | - | NO | - | FK, IDX | Employee reference |
| basic_salary | DECIMAL | 12,2 | NO | - | - | Base salary amount |
| housing_allowance | DECIMAL | 12,2 | YES | 0.00 | - | Housing allowance |
| transport_allowance | DECIMAL | 12,2 | YES | 0.00 | - | Transport allowance |
| other_allowances | DECIMAL | 12,2 | YES | 0.00 | - | Other allowances |
| tax_deduction | DECIMAL | 12,2 | YES | 0.00 | - | Tax deduction amount |
| insurance_deduction | DECIMAL | 12,2 | YES | 0.00 | - | Insurance deduction |
| other_deductions | DECIMAL | 12,2 | YES | 0.00 | - | Other deductions |
| effective_date | DATE | - | NO | - | IDX | Structure effective date |
| is_current | TINYINT | 1 | YES | 1 | IDX | Current structure flag |
| created_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | - | Record creation time |
| updated_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | - | Last update time |

**Currency:** Indian Rupee (₹ INR)

**Salary Components:**
- **Gross Salary** = basic_salary + housing_allowance + transport_allowance + other_allowances
- **Total Deductions** = tax_deduction + insurance_deduction + other_deductions  
- **Net Salary** = Gross Salary - Total Deductions

**Business Rules:**
- Only one current salary structure per employee
- Historical structures maintained for audit trail
- Effective date determines when structure becomes active

**Indexes:**
- PRIMARY KEY (id)
- INDEX idx_salary_company (company_id)
- INDEX idx_salary_employee (employee_id)
- INDEX idx_salary_effective (effective_date)
- INDEX idx_salary_current (is_current)
- FOREIGN KEY fk_salary_company (company_id) REFERENCES companies(id)
- FOREIGN KEY fk_salary_employee (employee_id) REFERENCES employees(id)

---

### Table 11: payroll_records (Transaction Table)

| Column | Data Type | Length | Null | Default | Key | Description |
|--------|-----------|--------|------|---------|-----|-------------|
| id | INT | - | NO | AUTO_INCREMENT | PK | Unique payroll identifier |
| company_id | INT | - | NO | - | FK, IDX | Company association |
| employee_id | INT | - | NO | - | FK, IDX | Employee reference |
| salary_structure_id | INT | - | NO | - | FK | Salary structure used |
| year | INT | - | NO | - | IDX | Payroll year |
| month | INT | - | NO | - | IDX | Payroll month (1-12) |
| gross_salary | DECIMAL | 12,2 | NO | - | - | Total gross amount |
| total_deductions | DECIMAL | 12,2 | NO | - | - | Total deduction amount |
| net_salary | DECIMAL | 12,2 | NO | - | - | Final payable amount |
| payment_date | DATE | - | YES | NULL | - | Actual payment date |
| payment_status | ENUM | - | YES | 'pending' | IDX | Payment status |
| payment_reference | VARCHAR | 100 | YES | NULL | - | Payment reference number |
| created_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | - | Record creation time |
| updated_at | TIMESTAMP | - | YES | CURRENT_TIMESTAMP | - | Last update time |

**Enums:**
- payment_status: 'pending', 'processed', 'paid', 'failed'

**Business Rules:**
- One payroll record per employee per month
- Month must be between 1 and 12 (check constraint)
- Immutable once payment is processed
- Links to salary structure for audit trail

**Payment Workflow:**
1. Payroll processing creates records (status: pending)
2. Calculations verified and approved (status: processed)
3. Payment executed (status: paid)
4. Failed payments marked (status: failed)

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY uk_payroll_employee_month (employee_id, year, month)
- INDEX idx_payroll_company (company_id)
- INDEX idx_payroll_employee (employee_id)
- INDEX idx_payroll_period (year, month)
- INDEX idx_payroll_status (payment_status)
- FOREIGN KEY fk_payroll_company (company_id) REFERENCES companies(id)
- FOREIGN KEY fk_payroll_employee (employee_id) REFERENCES employees(id)
- FOREIGN KEY fk_payroll_salary (salary_structure_id) REFERENCES salary_structures(id)
- CHECK CONSTRAINT chk_payroll_month (month >= 1 AND month <= 12)

## Database Summary Statistics

### Table Count and Types
- **Total Tables:** 11
- **Reference Tables:** 3 (roles, permissions, role_permissions)
- **Tenant Root:** 1 (companies)
- **Tenant Tables:** 7 (users, employees, attendance, leave_types, leave_requests, salary_structures, payroll_records)

### Constraint Summary
- **Primary Keys:** 11
- **Foreign Keys:** 15
- **Unique Constraints:** 8
- **Check Constraints:** 1
- **Indexes:** 35+

### Data Type Usage
- **INT:** 35 columns (identifiers, counts, years)
- **VARCHAR:** 25 columns (names, codes, references)
- **DECIMAL:** 10 columns (financial amounts)
- **DATE:** 8 columns (dates)
- **TIME:** 2 columns (clock times)
- **TIMESTAMP:** 22 columns (audit trail)
- **TEXT:** 5 columns (long descriptions)
- **ENUM:** 8 columns (controlled vocabularies)
- **TINYINT:** 4 columns (boolean flags)

This comprehensive data tables specification provides complete documentation of all database structures, constraints, and business rules for the multi-tenant HRMS system.