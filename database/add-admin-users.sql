-- ============================================================
-- Add Admin Users with Known Credentials
-- Run this after seed.sql to add predictable admin accounts
-- ============================================================

USE hrms_db;

-- Add admin user for Company 1 (TechCorp Solutions)
INSERT INTO users (company_id, role_id, email, password_hash, status)
VALUES (1, 1, 'admin1@company1.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active')
ON DUPLICATE KEY UPDATE password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

SET @admin_user_id = LAST_INSERT_ID();

-- Add employee record for admin
INSERT INTO employees (company_id, user_id, employee_code, first_name, last_name, email, phone, date_of_birth, gender, address, hire_date, department, designation, employment_type, status)
VALUES (1, @admin_user_id, 'TC-ADMIN-001', 'System', 'Administrator', 'admin1@company1.com', '+1-555-0001', '1985-01-15', 'male', '123 Admin Street', '2020-01-01', 'Administration', 'System Administrator', 'full_time', 'active')
ON DUPLICATE KEY UPDATE first_name = 'System';

-- Add HR user for Company 1
INSERT INTO users (company_id, role_id, email, password_hash, status)
VALUES (1, 2, 'hr1@company1.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active')
ON DUPLICATE KEY UPDATE password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

SET @hr_user_id = LAST_INSERT_ID();

INSERT INTO employees (company_id, user_id, employee_code, first_name, last_name, email, phone, date_of_birth, gender, address, hire_date, department, designation, employment_type, status)
VALUES (1, @hr_user_id, 'TC-HR-001', 'HR', 'Manager', 'hr1@company1.com', '+1-555-0002', '1988-05-20', 'female', '456 HR Avenue', '2020-02-01', 'Human Resources', 'HR Manager', 'full_time', 'active')
ON DUPLICATE KEY UPDATE first_name = 'HR';

-- Add regular employee for Company 1
INSERT INTO users (company_id, role_id, email, password_hash, status)
VALUES (1, 3, 'employee1@company1.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active')
ON DUPLICATE KEY UPDATE password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

SET @emp_user_id = LAST_INSERT_ID();

INSERT INTO employees (company_id, user_id, employee_code, first_name, last_name, email, phone, date_of_birth, gender, address, hire_date, department, designation, employment_type, status)
VALUES (1, @emp_user_id, 'TC-EMP-001', 'John', 'Employee', 'employee1@company1.com', '+1-555-0003', '1990-08-10', 'male', '789 Employee Road', '2021-03-15', 'Engineering', 'Software Developer', 'full_time', 'active')
ON DUPLICATE KEY UPDATE first_name = 'John';

SELECT 'Admin users created successfully!' as message;
SELECT 'Login credentials:' as info;
SELECT 'admin1@company1.com / password' as admin_login;
SELECT 'hr1@company1.com / password' as hr_login;
SELECT 'employee1@company1.com / password' as employee_login;
