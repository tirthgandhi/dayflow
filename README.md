# Dayflow - Human Resource Management System

A multi-tenant HRMS built with PHP backend and vanilla JavaScript frontend. Supports multiple companies with complete employee management, attendance tracking, leave management, payroll processing, and report generation.

## 🎬 Video Demo

[![Dayflow HRMS Demo](https://img.youtube.com/vi/_IyCzM0UJqE/maxresdefault.jpg)](https://youtu.be/_IyCzM0UJqE)

Watch the full demo: [https://youtu.be/_IyCzM0UJqE](https://youtu.be/_IyCzM0UJqE)

## 🚀 Live Demo

Access the landing page: `frontend/landing.html`

## ✨ Features

- **Multi-Company Support**: Each company has isolated data with tenant-based access control
- **Role-Based Access Control**: Admin, HR, and Employee roles with granular permissions
- **Employee Management**: Full CRUD operations for employee records
- **Attendance Tracking**: Clock in/out with daily attendance records
- **Leave Management**: Leave requests, approvals, and balance tracking
- **Payroll Processing**: Salary structures and monthly payroll generation (₹ INR currency)
- **Report Generation**: CSV exports for Employee, Attendance, Leave, Payroll; PDF for Department report
- **Dynamic UI**: Smooth animations and liquid transitions for modern user experience
- **Landing Page**: Professional marketing page with features, pricing, and CTA sections

## 🛠 Tech Stack

- **Backend**: PHP 8.0+ (No framework, custom MVC architecture)
- **Frontend**: Vanilla JavaScript, HTML5, CSS3
- **Database**: MySQL 5.7+ / MariaDB 10.2+
- **Server**: Apache with mod_rewrite (XAMPP recommended)
- **Currency**: Indian Rupee (₹ INR)

## 📦 Quick Start

### Prerequisites

- XAMPP (or similar with PHP 8.0+ and MySQL)
- Composer (PHP package manager)

### Installation

1. Clone the repository to your XAMPP htdocs folder:
   ```bash
   cd C:\xampp\htdocs
   git clone https://github.com/your-repo/Dayflow---Human-Resource-Management-System.git
   ```

2. Install PHP dependencies:
   ```bash
   cd Dayflow---Human-Resource-Management-System
   composer install
   ```

3. Set up the database (see [Database Setup](database/README.md))

4. Configure database connection in `config/database.php`

5. Start Apache and MySQL in XAMPP

6. Access the application:
   - Landing Page: `http://localhost/Dayflow---Human-Resource-Management-System/frontend/landing.html`
   - Dashboard: `http://localhost/Dayflow---Human-Resource-Management-System/frontend/`
   - API: `http://localhost/Dayflow---Human-Resource-Management-System/public/api/`

## 📁 Project Structure

```
├── config/                 # Configuration files
│   ├── database.php       # Database connection settings
│   ├── routes.php         # API route definitions
│   └── permissions.php    # Permission configurations
├── database/              # Database files
│   ├── schema.sql         # Database schema
│   ├── seed.sql           # Sample data
│   └── README.md          # Database setup guide
├── frontend/              # Frontend application
│   ├── css/               # Stylesheets
│   │   ├── variables.css  # CSS custom properties
│   │   ├── base.css       # Base styles
│   │   ├── layout.css     # Layout styles
│   │   ├── components.css # Component styles
│   │   ├── utilities.css  # Utility classes
│   │   ├── animations.css # Animation effects
│   │   └── landing.css    # Landing page styles
│   ├── js/                # JavaScript files
│   │   ├── api.js         # API service layer
│   │   ├── auth.js        # Authentication handling
│   │   ├── components/    # Reusable UI components
│   │   ├── pages/         # Page-specific logic
│   │   └── utils/         # Utility functions
│   ├── landing.html       # Marketing landing page
│   └── *.html             # Application pages
├── public/                # Web root
│   ├── index.php          # API entry point
│   └── .htaccess          # URL rewriting
├── src/                   # Backend source code
│   ├── Controllers/       # API controllers
│   ├── Services/          # Business logic
│   ├── Repositories/      # Data access layer
│   ├── Middleware/        # Request middleware
│   ├── Core/              # Framework core classes
│   └── Exceptions/        # Custom exceptions
└── tests/                 # Test files
```

## 📄 Frontend Pages

| Page | File | Description |
|------|------|-------------|
| Landing | `landing.html` | Marketing page with features, pricing, CTA |
| Login | `login.html` | User authentication with email/password |
| Signup | `signup.html` | Company registration with admin account creation |
| Dashboard | `index.html` | Overview with stats, quick actions, recent activity |
| Employees | `employees.html` | Employee list with search, filter, add/edit/delete |
| Attendance | `attendance.html` | Clock in/out, attendance records, calendar view |
| Leave | `leave.html` | Leave requests, approvals, balance display |
| Payroll | `payroll.html` | Salary records, payroll processing |
| Profile | `profile.html` | User's own profile management |
| Company Profile | `company-profile.html` | Company settings (Admin only) |
| Reports | `reports.html` | Generate and download HR reports |

## 📊 Reports

The system supports generating various reports:

| Report | Format | Description |
|--------|--------|-------------|
| Employee Report | CSV | Complete list of all employees with details |
| Attendance Report | CSV | Monthly attendance summary with clock times |
| Leave Report | CSV | All leave requests with status and reasons |
| Payroll Report | CSV | Monthly payroll breakdown with salary details |
| Department Report | PDF | Employee distribution by department with visual charts |

### Department Report (PDF)
The department report opens in a new window with:
- Company branding and report header
- Summary cards (Total Employees, Departments, Average per Dept)
- Department distribution table with visual bar chart
- Detailed employee list grouped by department
- Print-friendly layout - use browser's Print > Save as PDF

## 🔌 API Endpoints

### Authentication
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/auth/register` | Register new company | No |
| POST | `/api/auth/login` | User login | No |
| POST | `/api/auth/logout` | User logout | Yes |
| GET | `/api/auth/me` | Get current user | Yes |

### Employees
| Method | Endpoint | Description | Permission |
|--------|----------|-------------|------------|
| GET | `/api/employees` | List all employees | employee.view |
| GET | `/api/employees/{id}` | Get employee details | employee.view |
| GET | `/api/employees/me` | Get own profile | employee.view_own |
| POST | `/api/employees` | Create employee | employee.create |
| PUT | `/api/employees/{id}` | Update employee | employee.update |
| PUT | `/api/employees/me` | Update own profile | employee.update_own |
| DELETE | `/api/employees/{id}` | Delete employee | employee.delete |

### Attendance
| Method | Endpoint | Description | Permission |
|--------|----------|-------------|------------|
| GET | `/api/attendance` | List attendance records | attendance.view |
| GET | `/api/attendance/me` | Get own attendance | attendance.view_own |
| POST | `/api/attendance/clock-in` | Clock in | attendance.clock |
| POST | `/api/attendance/clock-out` | Clock out | attendance.clock |
| POST | `/api/attendance` | Create attendance record | attendance.create |
| PUT | `/api/attendance/{id}` | Update attendance | attendance.update |

### Leave
| Method | Endpoint | Description | Permission |
|--------|----------|-------------|------------|
| GET | `/api/leave/types` | Get leave types | leave.view_own |
| GET | `/api/leave/balance` | Get leave balance | leave.view_own |
| GET | `/api/leave/requests` | List all requests | leave.view |
| GET | `/api/leave/requests/me` | Get own requests | leave.view_own |
| POST | `/api/leave/requests` | Submit leave request | leave.request |
| PUT | `/api/leave/requests/{id}/approve` | Approve request | leave.approve |
| PUT | `/api/leave/requests/{id}/reject` | Reject request | leave.approve |

### Payroll
| Method | Endpoint | Description | Permission |
|--------|----------|-------------|------------|
| GET | `/api/payroll` | List payroll records | payroll.view |
| GET | `/api/payroll/{id}` | Get payroll details | payroll.view |
| GET | `/api/payroll/me` | Get own payroll | payroll.view_own |
| POST | `/api/payroll/process` | Process monthly payroll | payroll.create |

## 👥 User Roles

| Role | ID | Description |
|------|-----|-------------|
| Admin | 1 | Full access to all features and settings |
| HR | 2 | Manage employees, attendance, leave, payroll |
| Employee | 3 | Self-service: view own data, clock in/out, request leave |

## 📋 Default Data

### Default Leave Types (Created on Company Registration)
- Annual Leave: 20 days/year (Paid)
- Sick Leave: 10 days/year (Paid)
- Personal Leave: 5 days/year (Paid)
- Unpaid Leave: Unlimited (Unpaid)

### Default Password
When creating a new employee with an email, the default password is: `password123`

## ⚙️ Configuration

### Database Configuration

Edit `config/database.php` or set environment variables:

```php
return [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'port' => getenv('DB_PORT') ?: '3306',
    'database' => getenv('DB_DATABASE') ?: 'hrms_db',
    'username' => getenv('DB_USERNAME') ?: 'root',
    'password' => getenv('DB_PASSWORD') ?: '',
];
```

### API Base URL

If running on a different port or path, update `frontend/js/api.js`:

```javascript
const API_BASE = '/Dayflow---Human-Resource-Management-System/public/api';
```

## 🎨 UI Features

- **Responsive Design**: Works on desktop, tablet, and mobile devices
- **Modern Landing Page**: Professional marketing page with animations
- **Smooth Animations**: Page transitions, card hover effects, loading states
- **Toast Notifications**: Success, error, warning, and info messages
- **Modal Dialogs**: Confirmation dialogs and form modals
- **Data Tables**: Sortable, filterable tables with pagination
- **Print-Friendly Reports**: PDF reports optimized for printing

## 🧪 Development

### Running Tests

```bash
composer test
```

### Debug Scripts

Located in `public/` folder:
- `test-db.php` - Test database connection
- `debug-login.php` - Test login functionality
- `debug-register.php` - Test registration
- `debug-employees.php` - Test employee endpoints
- `setup-leave-types.php` - Add default leave types to existing companies

## ❓ Troubleshooting

### Common Issues

1. **422 Error on Leave Request**: Ensure the company has leave types set up. Run `setup-leave-types.php` for existing companies.

2. **500 Error on API calls**: Check database connection and ensure all tables are created.

3. **Session Issues**: Make sure cookies are enabled and the API allows credentials.

4. **Payroll Processing Error**: Employees need salary structures set up before payroll can be processed.

5. **PDF Report Not Downloading**: The department report opens in a new window. Use your browser's Print function and select "Save as PDF".

## 📜 License

MIT License - see [LICENSE](LICENSE) file for details.

---

Made with ❤️ by Dayflow Team
