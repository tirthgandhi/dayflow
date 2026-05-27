# Design Document: HR Dashboard UI

## Overview

This document defines the frontend architecture for the HR Dashboard UI in the Multi-Company HRMS. The dashboard is built using vanilla HTML, CSS, and JavaScript with a modular component-based approach. It connects to the PHP backend API for all data operations and follows enterprise-grade design principles with responsive layouts.

## Architecture

### System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         Browser                                  │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────────┐  ┌─────────────────────────────────────────┐  │
│  │   Sidebar    │  │              Main Content               │  │
│  │              │  │  ┌─────────────────────────────────────┐│  │
│  │  Dashboard   │  │  │            Header                   ││  │
│  │  Employees   │  │  │  Logo | User Name | Logout          ││  │
│  │  Attendance  │  │  └─────────────────────────────────────┘│  │
│  │  Leave       │  │  ┌─────────────────────────────────────┐│  │
│  │  Payroll     │  │  │         Page Content                ││  │
│  │  Reports     │  │  │   (Cards, Tables, Forms)            ││  │
│  │              │  │  └─────────────────────────────────────┘│  │
│  └──────────────┘  └─────────────────────────────────────────┘  │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                      API Service Layer                           │
│                    (JavaScript Fetch API)                        │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                     PHP Backend API                              │
│                   /api/* endpoints                               │
└─────────────────────────────────────────────────────────────────┘
```

### Directory Structure

```
frontend/
├── index.html                 # Main entry point / Dashboard
├── employees.html             # Employee management page
├── attendance.html            # Attendance management page
├── leave.html                 # Leave management page
├── payroll.html               # Payroll management page
├── reports.html               # Reports page
├── login.html                 # Login page
├── css/
│   ├── variables.css          # CSS custom properties (colors, spacing)
│   ├── base.css               # Reset and base styles
│   ├── layout.css             # Sidebar, header, main layout
│   ├── components.css         # Cards, buttons, forms, tables
│   └── utilities.css          # Helper classes
├── js/
│   ├── api.js                 # API service layer
│   ├── auth.js                # Authentication handling
│   ├── router.js              # Client-side navigation
│   ├── components/
│   │   ├── sidebar.js         # Sidebar component
│   │   ├── header.js          # Header component
│   │   ├── card.js            # KPI card component
│   │   ├── table.js           # Data table component
│   │   ├── modal.js           # Modal dialog component
│   │   ├── toast.js           # Toast notification component
│   │   └── loader.js          # Loading states component
│   └── pages/
│       ├── dashboard.js       # Dashboard page logic
│       ├── employees.js       # Employees page logic
│       ├── attendance.js      # Attendance page logic
│       ├── leave.js           # Leave page logic
│       └── payroll.js         # Payroll page logic
└── assets/
    └── icons/                 # Lucide icon SVGs
```

## Components and Interfaces

### Color System (CSS Variables)

```css
:root {
  /* Primary Colors */
  --color-primary: #1F3A5F;
  --color-secondary: #2FB7B2;
  
  /* Backgrounds */
  --color-bg-page: #F5F7FA;
  --color-bg-card: #FFFFFF;
  --color-border: #E1E6EF;
  
  /* Text */
  --color-text-heading: #1C1E21;
  --color-text-body: #6B7280;
  
  /* Status */
  --color-success: #22C55E;
  --color-warning: #F59E0B;
  --color-error: #EF4444;
  
  /* Spacing */
  --spacing-xs: 4px;
  --spacing-sm: 8px;
  --spacing-md: 16px;
  --spacing-lg: 24px;
  --spacing-xl: 32px;
  
  /* Sidebar */
  --sidebar-width: 260px;
  --sidebar-collapsed-width: 64px;
  
  /* Header */
  --header-height: 64px;
}
```

### Layout Components

#### Sidebar Structure
```html
<aside class="sidebar">
  <div class="sidebar-logo">
    <img src="logo.png" alt="Company Logo">
  </div>
  <nav class="sidebar-nav">
    <a href="/" class="nav-item active" data-page="dashboard">
      <svg class="nav-icon"><!-- Lucide icon --></svg>
      <span class="nav-label">Dashboard</span>
    </a>
    <a href="/employees" class="nav-item" data-page="employees">
      <svg class="nav-icon"><!-- Lucide icon --></svg>
      <span class="nav-label">Employees</span>
    </a>
    <!-- Additional nav items -->
  </nav>
</aside>
```

#### Header Structure
```html
<header class="header">
  <div class="header-left">
    <button class="menu-toggle" aria-label="Toggle menu">
      <svg><!-- Menu icon --></svg>
    </button>
    <h1 class="page-title">Dashboard</h1>
  </div>
  <div class="header-right">
    <span class="user-name">John Doe</span>
    <button class="btn-logout">Logout</button>
  </div>
</header>
```

### KPI Card Component

```html
<div class="kpi-card">
  <div class="kpi-header">
    <span class="kpi-title">Total Employees</span>
    <svg class="kpi-icon"><!-- Icon --></svg>
  </div>
  <div class="kpi-value">2,547</div>
  <div class="kpi-breakdown">
    <span class="kpi-stat success">Active: 2,412</span>
    <span class="kpi-stat error">Inactive: 135</span>
  </div>
</div>
```

### Data Table Component

```html
<div class="table-container">
  <div class="table-header">
    <input type="search" class="table-search" placeholder="Search...">
    <div class="table-filters">
      <select class="filter-select"><!-- Filter options --></select>
    </div>
  </div>
  <table class="data-table">
    <thead>
      <tr>
        <th>Employee Code</th>
        <th>Name</th>
        <th>Department</th>
        <th>Position</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <!-- Dynamic rows -->
    </tbody>
  </table>
  <div class="table-pagination">
    <span class="pagination-info">Showing 1-20 of 100</span>
    <div class="pagination-controls">
      <button class="btn-page" disabled>Previous</button>
      <button class="btn-page">Next</button>
    </div>
  </div>
</div>
```

## Data Models

### API Response Handling

```javascript
// api.js
const API_BASE = '/api';

const api = {
  async request(endpoint, options = {}) {
    const response = await fetch(`${API_BASE}${endpoint}`, {
      headers: {
        'Content-Type': 'application/json',
        ...options.headers
      },
      credentials: 'include',
      ...options
    });
    
    const data = await response.json();
    
    if (!data.success) {
      throw new ApiError(data.error.code, data.error.message, data.error.details);
    }
    
    return data;
  },
  
  // Dashboard
  getDashboardStats: () => api.request('/dashboard/stats'),
  
  // Employees
  getEmployees: (params) => api.request(`/employees?${new URLSearchParams(params)}`),
  getEmployee: (id) => api.request(`/employees/${id}`),
  createEmployee: (data) => api.request('/employees', { method: 'POST', body: JSON.stringify(data) }),
  updateEmployee: (id, data) => api.request(`/employees/${id}`, { method: 'PUT', body: JSON.stringify(data) }),
  deleteEmployee: (id) => api.request(`/employees/${id}`, { method: 'DELETE' }),
  
  // Attendance
  getAttendance: (params) => api.request(`/attendance?${new URLSearchParams(params)}`),
  clockIn: () => api.request('/attendance/clock-in', { method: 'POST' }),
  clockOut: () => api.request('/attendance/clock-out', { method: 'POST' }),
  
  // Leave
  getLeaveTypes: () => api.request('/leave/types'),
  getLeaveRequests: (params) => api.request(`/leave/requests?${new URLSearchParams(params)}`),
  createLeaveRequest: (data) => api.request('/leave/requests', { method: 'POST', body: JSON.stringify(data) }),
  approveLeave: (id) => api.request(`/leave/requests/${id}/approve`, { method: 'PUT' }),
  rejectLeave: (id, reason) => api.request(`/leave/requests/${id}/reject`, { method: 'PUT', body: JSON.stringify({ reason }) }),
  getLeaveBalance: () => api.request('/leave/balance'),
  
  // Payroll
  getPayroll: (params) => api.request(`/payroll?${new URLSearchParams(params)}`),
  processPayroll: (month) => api.request('/payroll/process', { method: 'POST', body: JSON.stringify({ month }) })
};
```

### State Management

```javascript
// Simple state management for UI
const state = {
  user: null,
  currentPage: 'dashboard',
  employees: { data: [], total: 0, page: 1, loading: false },
  attendance: { data: [], total: 0, page: 1, loading: false },
  leave: { requests: [], types: [], balance: [], loading: false },
  payroll: { data: [], total: 0, page: 1, loading: false }
};
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Status Color Consistency

*For any* status value (success/approved/present, pending/warning, rejected/error), the rendered element SHALL display the corresponding color (#22C55E, #F59E0B, #EF4444 respectively).

**Validates: Requirements 4.5, 6.3, 8.5**

### Property 2: Pagination Row Count

*For any* selected page size (10, 20, or 50), the table SHALL display at most that number of rows, and exactly that number when sufficient data exists.

**Validates: Requirements 5.2**

### Property 3: Attendance Record Display

*For any* attendance record displayed in the table, the row SHALL contain clock-in time, clock-out time, and total hours fields.

**Validates: Requirements 6.2**

### Property 4: Leave Request Fields

*For any* leave request displayed, the component SHALL show leave type, start date, end date, duration, and employee name.

**Validates: Requirements 7.4**

### Property 5: Payroll Record Fields

*For any* payroll record displayed, the row SHALL contain gross salary, deductions, and net salary values.

**Validates: Requirements 8.2**

### Property 6: Toast Notification on Success

*For any* successful API operation (create, update, delete, approve, reject), the system SHALL display a toast notification confirming the action.

**Validates: Requirements 10.4**

### Property 7: Validation Error Display

*For any* form field that fails validation, the field SHALL be highlighted with error styling and display an error message.

**Validates: Requirements 10.5**

## Error Handling

### Error States

| State | Display | User Action |
|-------|---------|-------------|
| Loading | Skeleton/spinner | Wait |
| Empty | Empty state message | Add data |
| Error | Error message + retry | Click retry |
| Offline | Offline indicator | Check connection |

### Toast Notifications

```javascript
// toast.js
const toast = {
  show(message, type = 'info', duration = 3000) {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
      <span class="toast-message">${message}</span>
      <button class="toast-close">&times;</button>
    `;
    container.appendChild(toast);
    
    setTimeout(() => toast.remove(), duration);
  },
  
  success: (msg) => toast.show(msg, 'success'),
  error: (msg) => toast.show(msg, 'error'),
  warning: (msg) => toast.show(msg, 'warning'),
  info: (msg) => toast.show(msg, 'info')
};
```

## Testing Strategy

### Property-Based Testing Library

The frontend will use **fast-check** for property-based testing with Jest as the test runner.

### Test Categories

1. **Component Tests**: Verify UI components render correctly with various data
2. **State Tests**: Verify state management behaves correctly
3. **API Integration Tests**: Verify API calls and response handling
4. **Responsive Tests**: Verify layout at different breakpoints
5. **Accessibility Tests**: Verify ARIA labels and keyboard navigation

### Test Configuration

Each property-based test will:
- Run a minimum of 100 iterations
- Be tagged with format: `**Feature: hr-dashboard-ui, Property {number}: {property_text}**`
- Use mock API responses for isolation

### Example Property Test

```javascript
// **Feature: hr-dashboard-ui, Property 1: Status Color Consistency**
import fc from 'fast-check';
import { getStatusColor } from '../js/utils';

describe('Status Color Consistency', () => {
  test('status values map to correct colors', () => {
    const statusColorMap = {
      'success': '#22C55E',
      'approved': '#22C55E',
      'present': '#22C55E',
      'pending': '#F59E0B',
      'warning': '#F59E0B',
      'rejected': '#EF4444',
      'error': '#EF4444',
      'absent': '#EF4444'
    };
    
    fc.assert(
      fc.property(
        fc.constantFrom(...Object.keys(statusColorMap)),
        (status) => {
          const color = getStatusColor(status);
          return color === statusColorMap[status];
        }
      ),
      { numRuns: 100 }
    );
  });
});
```
