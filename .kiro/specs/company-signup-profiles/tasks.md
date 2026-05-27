# Implementation Plan

- [-] 1. Fix Leave Type Selection Bug



  - [ ] 1.1 Update leave.js to populate leave types in employee view modal
    - Read leave types from API when opening modal
    - Populate the select dropdown with leave type options
    - _Requirements: 3.1, 3.2_
  - [ ] 1.2 Write property test for leave type population
    - **Property 5: Leave types populate correctly**



    - **Validates: Requirements 3.1, 3.2**

- [x] 2. Add HR Attendance Self-Service


  - [ ] 2.1 Update attendance.js to show tabs for HR users
    - Add "All Employees" and "My Attendance" tabs for HR role
    - Implement tab switching logic
    - Load appropriate data based on selected tab
    - _Requirements: 4.1, 4.2, 4.3_
  - [ ] 2.2 Update dashboard.js to show clock in/out for HR users
    - Add clock in/out buttons to HR dashboard
    - Reuse existing clock in/out functions
    - _Requirements: 4.4, 4.5, 4.6_
  - [ ] 2.3 Write property test for HR attendance filtering
    - **Property 6: HR attendance tab filtering**
    - **Validates: Requirements 4.3**

- [ ] 3. Checkpoint - Verify bug fixes work
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 4. Create File Upload Infrastructure
  - [ ] 4.1 Create uploads directory structure
    - Create uploads/logos and uploads/profiles directories
    - Add .htaccess for security
    - _Requirements: 1.4, 5.2_
  - [ ] 4.2 Create FileService for handling uploads
    - Implement file validation (type, size)
    - Implement secure file storage
    - Implement URL generation
    - _Requirements: 1.4, 5.2_
  - [ ] 4.3 Write property test for logo upload
    - **Property 2: Logo upload associates with company**
    - **Validates: Requirements 1.4, 5.2**

- [ ] 5. Create Company Registration Backend
  - [ ] 5.1 Create RegistrationService
    - Implement company creation logic
    - Implement admin user creation
    - Implement transaction handling
    - _Requirements: 1.2, 1.3_
  - [ ] 5.2 Create RegistrationController
    - Implement POST /api/auth/register endpoint
    - Handle logo upload during registration
    - _Requirements: 1.1, 1.2, 1.3, 1.4_



  - [ ] 5.3 Add registration route to routes.php
    - Add route without auth requirement
    - _Requirements: 1.1_


  - [ ] 5.4 Write property test for registration
    - **Property 1: Company registration creates linked records**
    - **Validates: Requirements 1.2, 1.3**



- [ ] 6. Create Company Signup Frontend
  - [ ] 6.1 Create signup.html page
    - Create registration form with company name, email, password
    - Add optional logo upload field
    - Add form validation
    - _Requirements: 1.1, 1.4_
  - [ ] 6.2 Create signup.js page logic
    - Implement form submission
    - Handle file upload
    - Handle success/error responses
    - Redirect to login on success
    - _Requirements: 1.5, 1.6_
  - [ ] 6.3 Update login.html with signup link
    - Add "Register your company" link
    - _Requirements: 1.1_

- [ ] 7. Checkpoint - Verify registration works
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 8. Create Company Profile Backend
  - [ ] 8.1 Create CompanyService
    - Implement get company profile
    - Implement update company profile
    - Implement logo update
    - _Requirements: 5.1, 5.2, 8.1, 8.2_
  - [ ] 8.2 Create CompanyController
    - Implement GET /api/company/profile
    - Implement PUT /api/company/profile
    - Implement POST /api/company/logo
    - _Requirements: 5.1, 5.2, 8.1, 8.2, 8.4_
  - [ ] 8.3 Add company routes to routes.php
    - Add routes with admin permission
    - _Requirements: 5.1, 8.1_




- [ ] 9. Create User Profile Backend
  - [x] 9.1 Update EmployeeService with profile methods


    - Implement get own profile
    - Implement update own profile with field restrictions
    - _Requirements: 6.1, 6.2, 6.4, 7.1, 7.2_


  - [ ] 9.2 Update EmployeeController with profile endpoints
    - Ensure GET /api/employees/me returns full profile
    - Ensure PUT /api/employees/me restricts sensitive fields


    - _Requirements: 6.2, 6.4, 7.2, 7.3_
  - [ ] 9.3 Write property test for profile field protection
    - **Property 12: Sensitive field protection**
    - **Validates: Requirements 6.4, 7.3**

- [ ] 10. Create Profile Frontend Pages
  - [ ] 10.1 Create profile.html page
    - Create profile display layout
    - Add edit form for allowed fields
    - Show different fields based on role
    - _Requirements: 6.1, 6.3, 7.1, 8.3_
  - [ ] 10.2 Create profile.js page logic
    - Load profile data from API
    - Handle profile updates
    - Handle field restrictions based on role
    - _Requirements: 6.2, 7.2_
  - [ ] 10.3 Create company-profile.html page (admin only)
    - Create company profile display
    - Add logo upload functionality
    - Add company info edit form
    - _Requirements: 5.1, 8.1, 8.2, 8.4_
  - [ ] 10.4 Create company-profile.js page logic
    - Load company data from API
    - Handle company updates
    - Handle logo upload
    - _Requirements: 5.2, 8.2, 8.4_

- [ ] 11. Display Company Logo on Dashboards
  - [ ] 11.1 Update auth.js to include company logo in user data
    - Fetch company logo URL with user data
    - Store in auth.user object
    - _Requirements: 5.3_
  - [ ] 11.2 Update sidebar.js to display company logo
    - Replace default logo with company logo if available
    - Fallback to default if no logo
    - _Requirements: 5.3, 5.4_
  - [ ] 11.3 Write property test for company logo visibility
    - **Property 9: Company logo visibility**
    - **Validates: Requirements 5.3**

- [ ] 12. Update Navigation with Profile Links
  - [ ] 12.1 Update sidebar.js with profile navigation
    - Add "My Profile" link for all users
    - Add "Company Settings" link for admins
    - _Requirements: 6.1, 7.1, 8.1_
  - [ ] 12.2 Update header.js with profile dropdown
    - Add user avatar/name in header
    - Add dropdown with profile and logout links
    - _Requirements: 6.1, 7.1, 8.3_

- [ ] 13. Enhance Employee Management
  - [ ] 13.1 Update employees.js add employee modal
    - Add checkbox for "Create user account"
    - Add role selection for admins
    - Add password field when creating user
    - _Requirements: 2.1, 2.2, 2.3, 2.5_
  - [ ] 13.2 Update EmployeeService to create user with employee
    - Implement combined employee + user creation
    - Assign default Employee role
    - _Requirements: 2.3, 2.4_
  - [ ] 13.3 Write property test for employee with user creation
    - **Property 3: Employee creation with user account**
    - **Property 4: Default role assignment**
    - **Validates: Requirements 2.3, 2.4**

- [ ] 14. Final Checkpoint - Verify all features work
  - Ensure all tests pass, ask the user if questions arise.
