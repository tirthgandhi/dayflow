# Requirements Document

## Introduction

This document specifies requirements for company self-registration, member management, profile sections, and UI improvements for the HRMS application. The features include company signup with logo upload, HR attendance self-service, leave type selection fix, and profile pages for employees, HR, and companies.

## Glossary

- **Company**: An organization that registers to use the HRMS system
- **Admin**: The first user created during company signup who has full administrative privileges
- **HR**: Human Resources role with employee management capabilities
- **Employee**: A staff member of a company with self-service access
- **Profile**: A page displaying and allowing editing of user/company information
- **Clock In/Out**: The action of recording work start and end times

## Requirements

### Requirement 1: Company Self-Registration

**User Story:** As a new company, I want to register my organization on the HRMS platform, so that I can start managing my employees.

#### Acceptance Criteria

1. WHEN a user visits the signup page THEN the System SHALL display a registration form with company name, admin email, admin password, and optional company logo upload fields
2. WHEN a user submits valid registration data THEN the System SHALL create a new company record with the provided information
3. WHEN a user submits valid registration data THEN the System SHALL create an admin user account linked to the new company
4. WHEN a user uploads a company logo THEN the System SHALL store the image and associate it with the company record
5. IF a user submits an email that already exists THEN the System SHALL display an error message and prevent registration
6. WHEN registration completes successfully THEN the System SHALL redirect the user to the login page with a success message

### Requirement 2: Member Management by Admin/HR

**User Story:** As an admin or HR user, I want to add new members to my company, so that they can access the HRMS system.

#### Acceptance Criteria

1. WHEN an admin or HR user accesses the employees page THEN the System SHALL display an "Add Employee" button
2. WHEN an admin or HR user clicks "Add Employee" THEN the System SHALL display a form with employee details and optional user account creation
3. WHEN an admin or HR user submits valid employee data with user account option THEN the System SHALL create both employee and user records
4. WHEN a new user account is created THEN the System SHALL assign the Employee role by default
5. IF an admin creates a user THEN the System SHALL allow selecting Admin, HR, or Employee role

### Requirement 3: Leave Type Selection Fix

**User Story:** As an employee, I want to select a leave type when requesting leave, so that my request is properly categorized.

#### Acceptance Criteria

1. WHEN an employee opens the leave request form THEN the System SHALL populate the leave type dropdown with available leave types
2. WHEN leave types are loaded THEN the System SHALL display the leave type name for each option
3. IF no leave types exist for the company THEN the System SHALL display a message indicating no leave types are available

### Requirement 4: HR Attendance Self-Service

**User Story:** As an HR user, I want to view my own attendance and clock in/out, so that I can track my own work hours while managing others.

#### Acceptance Criteria

1. WHEN an HR user accesses the attendance page THEN the System SHALL display two tabs: "All Employees" and "My Attendance"
2. WHEN an HR user views "All Employees" tab THEN the System SHALL display attendance records for all employees
3. WHEN an HR user views "My Attendance" tab THEN the System SHALL display only their own attendance records
4. WHEN an HR user is on the dashboard THEN the System SHALL display clock in/out buttons
5. WHEN an HR user clicks clock in THEN the System SHALL record their attendance start time
6. WHEN an HR user clicks clock out THEN the System SHALL record their attendance end time

### Requirement 5: Company Profile with Logo

**User Story:** As a company admin, I want to manage my company profile including logo, so that the company branding appears throughout the system.

#### Acceptance Criteria

1. WHEN an admin accesses the company profile page THEN the System SHALL display current company information and logo
2. WHEN an admin uploads a new logo THEN the System SHALL update the company logo and display it immediately
3. WHEN an employee views their dashboard THEN the System SHALL display the company logo in the header or sidebar
4. WHEN company logo is updated THEN the System SHALL reflect the change across all user dashboards

### Requirement 6: Employee Profile Page

**User Story:** As an employee, I want to view and edit my profile information, so that my records are accurate.

#### Acceptance Criteria

1. WHEN an employee accesses their profile page THEN the System SHALL display their personal information
2. WHEN an employee edits allowed fields THEN the System SHALL update their profile information
3. WHEN an employee views their profile THEN the System SHALL display their department, designation, and contact information
4. THE System SHALL restrict employees from editing sensitive fields like salary and employee code

### Requirement 7: HR Profile Page

**User Story:** As an HR user, I want to view and edit my profile information, so that my records are accurate.

#### Acceptance Criteria

1. WHEN an HR user accesses their profile page THEN the System SHALL display their personal and employment information
2. WHEN an HR user edits allowed fields THEN the System SHALL update their profile information
3. THE System SHALL allow HR users to edit the same fields as employees

### Requirement 8: Admin/Company Profile Page

**User Story:** As an admin, I want to manage company settings and my own profile, so that company information is current.

#### Acceptance Criteria

1. WHEN an admin accesses the company profile page THEN the System SHALL display company details including name, address, and logo
2. WHEN an admin edits company information THEN the System SHALL update the company record
3. WHEN an admin accesses their personal profile THEN the System SHALL display their user information
4. THE System SHALL allow admins to update company logo through the profile page
