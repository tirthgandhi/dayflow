/**
 * Reports Page Logic
 * Generates CSV reports for most data and PDF for department report
 */

(async function() {
  // Require authentication
  const isAuth = await auth.requireAuth();
  if (!isAuth) return;

  // Render layout
  sidebar.render('sidebar-container', 'reports');
  header.render('header-container', 'Reports');

  // Initialize report handlers
  initReportHandlers();
})();

/**
 * Initialize click handlers for report buttons
 */
function initReportHandlers() {
  const reportCards = document.querySelectorAll('.report-card');
  
  reportCards.forEach(card => {
    const btn = card.querySelector('.btn');
    const reportType = card.dataset.report;
    
    btn.addEventListener('click', async () => {
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner" style="width:16px;height:16px;"></span> Generating...';
      
      try {
        switch(reportType) {
          case 'employee':
            await generateEmployeeReport();
            break;
          case 'attendance':
            await generateAttendanceReport();
            break;
          case 'leave':
            await generateLeaveReport();
            break;
          case 'payroll':
            await generatePayrollReport();
            break;
          case 'department':
            await generateDepartmentReport();
            break;
          case 'custom':
            toast.info('Custom report builder coming soon');
            break;
        }
      } catch (error) {
        console.error('Report generation failed:', error);
        toast.error(error.message || 'Failed to generate report');
      } finally {
        btn.disabled = false;
        btn.innerHTML = reportType === 'department' ? 'Download PDF' : 
                        reportType === 'custom' ? 'Create Report' : 'Download CSV';
      }
    });
  });
}

// ============================================
// CSV GENERATION UTILITIES
// ============================================

/**
 * Convert array of objects to CSV string
 */
function arrayToCSV(data, columns) {
  if (!data || data.length === 0) {
    return columns.map(c => c.label).join(',') + '\n';
  }
  
  const header = columns.map(c => `"${c.label}"`).join(',');
  const rows = data.map(row => {
    return columns.map(c => {
      let value = row[c.key];
      if (value === null || value === undefined) value = '';
      if (typeof value === 'string') {
        value = value.replace(/"/g, '""');
        return `"${value}"`;
      }
      return value;
    }).join(',');
  });
  
  return header + '\n' + rows.join('\n');
}

/**
 * Download CSV file
 */
function downloadCSV(csvContent, filename) {
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  const url = URL.createObjectURL(blob);
  
  link.setAttribute('href', url);
  link.setAttribute('download', filename);
  link.style.visibility = 'hidden';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
}

/**
 * Format date for reports
 */
function formatReportDate(dateStr) {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-IN', { 
    year: 'numeric', 
    month: '2-digit', 
    day: '2-digit' 
  });
}

/**
 * Format currency for reports
 */
function formatReportCurrency(amount) {
  return new Intl.NumberFormat('en-IN', {
    style: 'currency',
    currency: 'INR',
    minimumFractionDigits: 0
  }).format(amount || 0);
}

// ============================================
// REPORT GENERATORS
// ============================================

/**
 * Generate Employee Report (CSV)
 */
async function generateEmployeeReport() {
  toast.info('Fetching employee data...');
  
  const response = await api.employees.list({ per_page: 10000 });
  const employees = response.data || [];
  
  if (employees.length === 0) {
    toast.warning('No employee data found');
    return;
  }
  
  const columns = [
    { key: 'employee_code', label: 'Employee Code' },
    { key: 'first_name', label: 'First Name' },
    { key: 'last_name', label: 'Last Name' },
    { key: 'email', label: 'Email' },
    { key: 'phone', label: 'Phone' },
    { key: 'department', label: 'Department' },
    { key: 'designation', label: 'Designation' },
    { key: 'hire_date', label: 'Hire Date' },
    { key: 'status', label: 'Status' }
  ];
  
  // Format dates
  const formattedData = employees.map(emp => ({
    ...emp,
    hire_date: formatReportDate(emp.hire_date)
  }));
  
  const csv = arrayToCSV(formattedData, columns);
  const filename = `employee_report_${new Date().toISOString().split('T')[0]}.csv`;
  
  downloadCSV(csv, filename);
  toast.success(`Employee report downloaded (${employees.length} records)`);
}

/**
 * Generate Attendance Report (CSV)
 */
async function generateAttendanceReport() {
  toast.info('Fetching attendance data...');
  
  // Get current month's attendance
  const today = new Date();
  const firstDay = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
  const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0).toISOString().split('T')[0];
  
  const response = await api.attendance.list({ 
    date_from: firstDay, 
    date_to: lastDay,
    per_page: 10000 
  });
  const records = response.data || [];
  
  if (records.length === 0) {
    toast.warning('No attendance data found for this month');
    return;
  }
  
  const columns = [
    { key: 'employee_code', label: 'Employee Code' },
    { key: 'first_name', label: 'First Name' },
    { key: 'last_name', label: 'Last Name' },
    { key: 'date', label: 'Date' },
    { key: 'clock_in_time', label: 'Clock In' },
    { key: 'clock_out_time', label: 'Clock Out' },
    { key: 'status', label: 'Status' },
    { key: 'work_hours', label: 'Work Hours' }
  ];
  
  const formattedData = records.map(rec => ({
    ...rec,
    date: formatReportDate(rec.date)
  }));
  
  const csv = arrayToCSV(formattedData, columns);
  const monthName = today.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
  const filename = `attendance_report_${monthName.replace(' ', '_')}.csv`;
  
  downloadCSV(csv, filename);
  toast.success(`Attendance report downloaded (${records.length} records)`);
}

/**
 * Generate Leave Report (CSV)
 */
async function generateLeaveReport() {
  toast.info('Fetching leave data...');
  
  const response = await api.leave.requests({ per_page: 10000 });
  const requests = response.data || [];
  
  if (requests.length === 0) {
    toast.warning('No leave data found');
    return;
  }
  
  const columns = [
    { key: 'employee_code', label: 'Employee Code' },
    { key: 'first_name', label: 'First Name' },
    { key: 'last_name', label: 'Last Name' },
    { key: 'leave_type_name', label: 'Leave Type' },
    { key: 'start_date', label: 'Start Date' },
    { key: 'end_date', label: 'End Date' },
    { key: 'total_days', label: 'Total Days' },
    { key: 'status', label: 'Status' },
    { key: 'reason', label: 'Reason' }
  ];
  
  const formattedData = requests.map(req => ({
    ...req,
    start_date: formatReportDate(req.start_date),
    end_date: formatReportDate(req.end_date)
  }));
  
  const csv = arrayToCSV(formattedData, columns);
  const filename = `leave_report_${new Date().toISOString().split('T')[0]}.csv`;
  
  downloadCSV(csv, filename);
  toast.success(`Leave report downloaded (${requests.length} records)`);
}

/**
 * Generate Payroll Report (CSV)
 */
async function generatePayrollReport() {
  toast.info('Fetching payroll data...');
  
  const currentMonth = new Date().toISOString().slice(0, 7);
  const response = await api.payroll.list({ month: currentMonth, per_page: 10000 });
  const records = response.data || [];
  
  if (records.length === 0) {
    toast.warning('No payroll data found for this month');
    return;
  }
  
  const columns = [
    { key: 'employee_code', label: 'Employee Code' },
    { key: 'first_name', label: 'First Name' },
    { key: 'last_name', label: 'Last Name' },
    { key: 'pay_period', label: 'Pay Period' },
    { key: 'basic_salary', label: 'Basic Salary' },
    { key: 'allowances', label: 'Allowances' },
    { key: 'gross_salary', label: 'Gross Salary' },
    { key: 'deductions', label: 'Deductions' },
    { key: 'net_salary', label: 'Net Salary' },
    { key: 'status', label: 'Status' }
  ];
  
  const formattedData = records.map(rec => ({
    ...rec,
    pay_period: `${rec.year}-${String(rec.month).padStart(2, '0')}`,
    basic_salary: rec.basic_salary || 0,
    allowances: rec.allowances || 0,
    deductions: rec.deductions || rec.total_deductions || 0
  }));
  
  const csv = arrayToCSV(formattedData, columns);
  const filename = `payroll_report_${currentMonth}.csv`;
  
  downloadCSV(csv, filename);
  toast.success(`Payroll report downloaded (${records.length} records)`);
}

/**
 * Generate Department Report (PDF)
 * Opens a print-friendly page that can be saved as PDF
 */
async function generateDepartmentReport() {
  toast.info('Generating department report...');
  
  const response = await api.employees.list({ per_page: 10000 });
  const employees = response.data || [];
  
  if (employees.length === 0) {
    toast.warning('No employee data found');
    return;
  }
  
  // Group employees by department
  const departments = {};
  employees.forEach(emp => {
    const dept = emp.department || 'Unassigned';
    if (!departments[dept]) {
      departments[dept] = [];
    }
    departments[dept].push(emp);
  });
  
  // Generate and open PDF report
  openPDFReport(departments, employees.length);
  toast.success('Department report opened - Use Print > Save as PDF');
}

/**
 * Open a print-friendly PDF report in new window
 */
function openPDFReport(departments, totalEmployees) {
  const companyName = auth.user?.company_name || 'Company';
  const reportDate = new Date().toLocaleDateString('en-IN', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
  
  // Sort departments by employee count
  const sortedDepts = Object.entries(departments)
    .sort((a, b) => b[1].length - a[1].length);
  
  // Calculate max for chart
  const maxCount = Math.max(...sortedDepts.map(([, emps]) => emps.length));
  
  // Generate table rows
  const tableRows = sortedDepts.map(([dept, emps]) => {
    const percentage = ((emps.length / totalEmployees) * 100).toFixed(1);
    const barWidth = (emps.length / maxCount) * 100;
    return `
      <tr>
        <td style="padding: 12px; border-bottom: 1px solid #e5e7eb;">${escapeHtml(dept)}</td>
        <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; text-align: center; font-weight: 600;">${emps.length}</td>
        <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; text-align: center;">${percentage}%</td>
        <td style="padding: 12px; border-bottom: 1px solid #e5e7eb;">
          <div style="background: #e5e7eb; border-radius: 4px; height: 20px; width: 100%;">
            <div style="background: linear-gradient(90deg, #2FB7B2, #1F3A5F); height: 100%; width: ${barWidth}%; border-radius: 4px;"></div>
          </div>
        </td>
      </tr>
    `;
  }).join('');
  
  // Generate employee list by department
  const employeesByDept = sortedDepts.map(([dept, emps]) => `
    <div style="margin-bottom: 24px; page-break-inside: avoid;">
      <h3 style="color: #1F3A5F; font-size: 16px; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid #2FB7B2;">
        ${escapeHtml(dept)} (${emps.length} employees)
      </h3>
      <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
        <thead>
          <tr style="background: #f3f4f6;">
            <th style="padding: 8px; text-align: left; border: 1px solid #e5e7eb;">Code</th>
            <th style="padding: 8px; text-align: left; border: 1px solid #e5e7eb;">Name</th>
            <th style="padding: 8px; text-align: left; border: 1px solid #e5e7eb;">Designation</th>
            <th style="padding: 8px; text-align: left; border: 1px solid #e5e7eb;">Email</th>
            <th style="padding: 8px; text-align: left; border: 1px solid #e5e7eb;">Status</th>
          </tr>
        </thead>
        <tbody>
          ${emps.map(emp => `
            <tr>
              <td style="padding: 8px; border: 1px solid #e5e7eb;">${escapeHtml(emp.employee_code || '-')}</td>
              <td style="padding: 8px; border: 1px solid #e5e7eb;">${escapeHtml(emp.first_name)} ${escapeHtml(emp.last_name)}</td>
              <td style="padding: 8px; border: 1px solid #e5e7eb;">${escapeHtml(emp.designation || '-')}</td>
              <td style="padding: 8px; border: 1px solid #e5e7eb;">${escapeHtml(emp.email || '-')}</td>
              <td style="padding: 8px; border: 1px solid #e5e7eb;">
                <span style="padding: 2px 8px; border-radius: 12px; font-size: 11px; background: ${emp.status === 'active' ? '#dcfce7' : '#fee2e2'}; color: ${emp.status === 'active' ? '#166534' : '#991b1b'};">
                  ${emp.status || 'active'}
                </span>
              </td>
            </tr>
          `).join('')}
        </tbody>
      </table>
    </div>
  `).join('');
  
  const htmlContent = `
    <!DOCTYPE html>
    <html>
    <head>
      <title>Department Report - ${companyName}</title>
      <style>
        @media print {
          body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
          .no-print { display: none !important; }
          .page-break { page-break-before: always; }
        }
        body {
          font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
          margin: 0;
          padding: 40px;
          color: #1f2937;
          line-height: 1.5;
        }
        .header {
          display: flex;
          justify-content: space-between;
          align-items: flex-start;
          margin-bottom: 32px;
          padding-bottom: 24px;
          border-bottom: 3px solid #2FB7B2;
        }
        .logo {
          display: flex;
          align-items: center;
          gap: 12px;
        }
        .logo-icon {
          width: 48px;
          height: 48px;
          background: #2FB7B2;
          border-radius: 8px;
          display: flex;
          align-items: center;
          justify-content: center;
          color: white;
          font-weight: bold;
          font-size: 18px;
        }
        .company-name {
          font-size: 24px;
          font-weight: 700;
          color: #1F3A5F;
        }
        .report-title {
          font-size: 14px;
          color: #6b7280;
        }
        .meta {
          text-align: right;
          font-size: 12px;
          color: #6b7280;
        }
        .summary-cards {
          display: grid;
          grid-template-columns: repeat(3, 1fr);
          gap: 20px;
          margin-bottom: 32px;
        }
        .summary-card {
          background: linear-gradient(135deg, #f8fafc, #f1f5f9);
          border-radius: 12px;
          padding: 20px;
          text-align: center;
          border: 1px solid #e2e8f0;
        }
        .summary-value {
          font-size: 36px;
          font-weight: 700;
          color: #1F3A5F;
        }
        .summary-label {
          font-size: 14px;
          color: #64748b;
          margin-top: 4px;
        }
        .section-title {
          font-size: 18px;
          font-weight: 600;
          color: #1F3A5F;
          margin: 32px 0 16px;
        }
        .print-btn {
          position: fixed;
          top: 20px;
          right: 20px;
          background: #2FB7B2;
          color: white;
          border: none;
          padding: 12px 24px;
          border-radius: 8px;
          cursor: pointer;
          font-size: 14px;
          font-weight: 600;
          box-shadow: 0 4px 12px rgba(47, 183, 178, 0.3);
        }
        .print-btn:hover {
          background: #259e9a;
        }
      </style>
    </head>
    <body>
      <button class="print-btn no-print" onclick="window.print()">
        🖨️ Print / Save as PDF
      </button>
      
      <div class="header">
        <div class="logo">
          <div class="logo-icon">HR</div>
          <div>
            <div class="company-name">${escapeHtml(companyName)}</div>
            <div class="report-title">Department Report</div>
          </div>
        </div>
        <div class="meta">
          <div>Generated: ${reportDate}</div>
          <div>Dayflow HRMS</div>
        </div>
      </div>
      
      <div class="summary-cards">
        <div class="summary-card">
          <div class="summary-value">${totalEmployees}</div>
          <div class="summary-label">Total Employees</div>
        </div>
        <div class="summary-card">
          <div class="summary-value">${Object.keys(departments).length}</div>
          <div class="summary-label">Departments</div>
        </div>
        <div class="summary-card">
          <div class="summary-value">${Math.round(totalEmployees / Object.keys(departments).length)}</div>
          <div class="summary-label">Avg per Department</div>
        </div>
      </div>
      
      <h2 class="section-title">Department Distribution</h2>
      <table style="width: 100%; border-collapse: collapse; margin-bottom: 32px;">
        <thead>
          <tr style="background: #1F3A5F; color: white;">
            <th style="padding: 12px; text-align: left;">Department</th>
            <th style="padding: 12px; text-align: center; width: 100px;">Employees</th>
            <th style="padding: 12px; text-align: center; width: 100px;">Percentage</th>
            <th style="padding: 12px; text-align: left; width: 200px;">Distribution</th>
          </tr>
        </thead>
        <tbody>
          ${tableRows}
        </tbody>
      </table>
      
      <div class="page-break"></div>
      
      <h2 class="section-title">Employees by Department</h2>
      ${employeesByDept}
      
      <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #9ca3af; font-size: 12px;">
        Generated by Dayflow HRMS • ${reportDate}
      </div>
    </body>
    </html>
  `;
  
  // Open in new window
  const printWindow = window.open('', '_blank');
  printWindow.document.write(htmlContent);
  printWindow.document.close();
}

/**
 * Escape HTML for safe rendering
 */
function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text || '';
  return div.innerHTML;
}

// Export functions for global access
window.generateEmployeeReport = generateEmployeeReport;
window.generateAttendanceReport = generateAttendanceReport;
window.generateLeaveReport = generateLeaveReport;
window.generatePayrollReport = generatePayrollReport;
window.generateDepartmentReport = generateDepartmentReport;
