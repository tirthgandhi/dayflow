/**
 * Dashboard Page Logic
 * Shows role-appropriate content based on user permissions
 */

(async function() {
  // Require authentication
  const isAuth = await auth.requireAuth();
  if (!isAuth) return;

  // Render layout
  sidebar.render('sidebar-container', 'dashboard');
  header.render('header-container', 'Dashboard');

  // Render dashboard based on role
  renderDashboard();
  
  // Load dashboard data
  loadDashboardData();
})();

/**
 * Render dashboard layout based on user role
 */
function renderDashboard() {
  const mainContent = document.querySelector('.main-content');
  const role = auth.user?.role_name;
  const isAdmin = role === 'Admin' || role === 'HR';

  if (isAdmin) {
    // Admin/HR Dashboard - Full stats with clock in/out for HR
    const isHR = role === 'HR';
    
    mainContent.innerHTML = `
      ${isHR ? `
      <!-- HR Personal Clock In/Out Section -->
      <div class="card mb-lg">
        <div class="card-header">
          <h3 class="card-title">My Attendance</h3>
        </div>
        <div class="card-body">
          <div class="flex items-center gap-lg">
            <div class="flex-1">
              <div class="kpi-value" id="hr-attendance-status">--</div>
              <div class="kpi-meta" id="hr-clock-time">--</div>
            </div>
            <div class="flex gap-md">
              <button class="btn btn-primary" id="hr-clock-in-btn" onclick="clockIn()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-sm">
                  <polyline points="9 11 12 14 22 4"></polyline>
                  <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                </svg>
                Clock In
              </button>
              <button class="btn btn-outline" id="hr-clock-out-btn" onclick="clockOut()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-sm">
                  <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                  <polyline points="16 17 21 12 16 7"></polyline>
                  <line x1="21" x2="9" y1="12" y2="12"></line>
                </svg>
                Clock Out
              </button>
            </div>
          </div>
        </div>
      </div>
      ` : ''}
      
      <div class="content-grid mb-lg">
        <div class="card kpi-card">
          <div class="kpi-icon" style="background-color: rgba(47, 183, 178, 0.1); color: var(--color-secondary);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
              <circle cx="9" cy="7" r="4"></circle>
              <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
          </div>
          <div class="kpi-content">
            <div class="kpi-value" id="total-employees">--</div>
            <div class="kpi-label">Total Employees</div>
            <div class="kpi-meta">
              <span id="active-employees">Active: --</span>
              <span id="inactive-employees">Inactive: --</span>
            </div>
          </div>
        </div>

        <div class="card kpi-card">
          <div class="kpi-icon" style="background-color: rgba(34, 197, 94, 0.1); color: var(--color-success);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"></circle>
              <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
          </div>
          <div class="kpi-content">
            <div class="kpi-value" id="attendance-rate">--%</div>
            <div class="kpi-label">Today's Attendance</div>
            <div class="kpi-meta">
              <span id="present-count">Present: --</span>
              <span id="absent-count">Absent: --</span>
              <span id="leave-count">Late: --</span>
            </div>
          </div>
        </div>

        <div class="card kpi-card">
          <div class="kpi-icon" style="background-color: rgba(245, 158, 11, 0.1); color: var(--color-warning);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect>
              <line x1="16" x2="16" y1="2" y2="6"></line>
              <line x1="8" x2="8" y1="2" y2="6"></line>
              <line x1="3" x2="21" y1="10" y2="10"></line>
            </svg>
          </div>
          <div class="kpi-content">
            <div class="kpi-value" id="pending-leaves">--</div>
            <div class="kpi-label">Pending Leave Requests</div>
            <div class="kpi-meta">Awaiting approval</div>
          </div>
        </div>

        <div class="card kpi-card">
          <div class="kpi-icon" style="background-color: rgba(31, 58, 95, 0.1); color: var(--color-primary);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <text x="6" y="18" font-size="16" font-weight="bold" fill="currentColor" stroke="none">₹</text>
            </svg>
          </div>
          <div class="kpi-content">
            <div class="kpi-value" id="payroll-total">₹--</div>
            <div class="kpi-label">This Month's Payroll</div>
            <div class="kpi-meta" id="payroll-status">-- processed</div>
          </div>
        </div>
      </div>

      <div class="content-grid-2">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Pending Leave Requests</h3>
            <a href="leave.html" class="btn btn-ghost btn-sm">View All</a>
          </div>
          <div class="card-body" id="recent-leaves">
            <div class="text-center p-lg"><div class="spinner"></div></div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Quick Actions</h3>
          </div>
          <div class="card-body">
            <div class="flex flex-col gap-sm">
              <a href="employees.html" class="btn btn-outline">Manage Employees</a>
              <a href="attendance.html" class="btn btn-outline">View Attendance</a>
              <a href="leave.html" class="btn btn-outline">Approve Leave Requests</a>
              <a href="payroll.html" class="btn btn-outline">Process Payroll</a>
            </div>
          </div>
        </div>
      </div>
    `;
  } else {
    // Employee Dashboard - Self-service
    mainContent.innerHTML = `
      <div class="card mb-lg">
        <div class="card-body">
          <h2 class="text-xl font-semibold text-heading mb-sm">Welcome, ${auth.user?.first_name || auth.user?.email || 'Employee'}!</h2>
          <p class="text-muted">Here's your personal dashboard. You can view your attendance, leave balance, and payslips.</p>
        </div>
      </div>

      <div class="content-grid-3 mb-lg">
        <div class="card kpi-card">
          <div class="kpi-icon" style="background-color: rgba(34, 197, 94, 0.1); color: var(--color-success);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"></circle>
              <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
          </div>
          <div class="kpi-content">
            <div class="kpi-value" id="my-attendance-status">--</div>
            <div class="kpi-label">Today's Status</div>
            <div class="kpi-meta" id="my-clock-time">--</div>
          </div>
        </div>

        <div class="card kpi-card">
          <div class="kpi-icon" style="background-color: rgba(245, 158, 11, 0.1); color: var(--color-warning);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect>
              <line x1="16" x2="16" y1="2" y2="6"></line>
              <line x1="8" x2="8" y1="2" y2="6"></line>
              <line x1="3" x2="21" y1="10" y2="10"></line>
            </svg>
          </div>
          <div class="kpi-content">
            <div class="kpi-value" id="my-leave-balance">--</div>
            <div class="kpi-label">Leave Balance</div>
            <div class="kpi-meta">Days remaining</div>
          </div>
        </div>

        <div class="card kpi-card">
          <div class="kpi-icon" style="background-color: rgba(31, 58, 95, 0.1); color: var(--color-primary);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <text x="6" y="18" font-size="16" font-weight="bold" fill="currentColor" stroke="none">₹</text>
            </svg>
          </div>
          <div class="kpi-content">
            <div class="kpi-value" id="my-last-salary">₹--</div>
            <div class="kpi-label">Last Salary</div>
            <div class="kpi-meta" id="my-salary-month">--</div>
          </div>
        </div>
      </div>

      <div class="content-grid-2">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Clock In/Out</h3>
          </div>
          <div class="card-body">
            <div class="flex gap-md">
              <button class="btn btn-primary flex-1" id="clock-in-btn" onclick="clockIn()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-sm">
                  <polyline points="9 11 12 14 22 4"></polyline>
                  <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                </svg>
                Clock In
              </button>
              <button class="btn btn-outline flex-1" id="clock-out-btn" onclick="clockOut()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-sm">
                  <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                  <polyline points="16 17 21 12 16 7"></polyline>
                  <line x1="21" x2="9" y1="12" y2="12"></line>
                </svg>
                Clock Out
              </button>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Quick Actions</h3>
          </div>
          <div class="card-body">
            <div class="flex flex-col gap-sm">
              <a href="leave.html" class="btn btn-outline">Request Leave</a>
              <a href="attendance.html" class="btn btn-outline">View My Attendance</a>
              <a href="payroll.html" class="btn btn-outline">View My Payslips</a>
            </div>
          </div>
        </div>
      </div>

      <div class="card mt-lg">
        <div class="card-header">
          <h3 class="card-title">My Recent Leave Requests</h3>
          <a href="leave.html" class="btn btn-ghost btn-sm">View All</a>
        </div>
        <div class="card-body" id="my-leave-requests">
          <div class="text-center p-lg"><div class="spinner"></div></div>
        </div>
      </div>
    `;
  }
}

/**
 * Load all dashboard data based on role
 */
async function loadDashboardData() {
  const role = auth.user?.role_name;
  const isAdmin = role === 'Admin' || role === 'HR';
  const isHR = role === 'HR';

  if (isAdmin) {
    const promises = [
      loadEmployeeStats(),
      loadAttendanceStats(),
      loadLeaveStats(),
      loadPayrollStats(),
      loadRecentLeaves()
    ];
    
    // HR also needs to load their own attendance status
    if (isHR) {
      promises.push(loadHRAttendanceStatus());
    }
    
    await Promise.all(promises);
  } else {
    await Promise.all([
      loadMyAttendanceStatus(),
      loadMyLeaveBalance(),
      loadMyLastSalary(),
      loadMyLeaveRequests()
    ]);
  }
}

// ============================================
// ADMIN/HR DASHBOARD FUNCTIONS
// ============================================

async function loadHRAttendanceStatus() {
  try {
    const today = new Date().toISOString().split('T')[0];
    const response = await api.attendance.me({ date_from: today, date_to: today });
    const records = response.data || [];
    
    const statusEl = document.getElementById('hr-attendance-status');
    const timeEl = document.getElementById('hr-clock-time');
    const clockInBtn = document.getElementById('hr-clock-in-btn');
    const clockOutBtn = document.getElementById('hr-clock-out-btn');
    
    if (!statusEl) return; // Element doesn't exist (not HR)
    
    if (records.length > 0) {
      const record = records[0];
      statusEl.textContent = record.status === 'present' ? 'Present' : record.status;
      statusEl.style.color = record.status === 'present' ? 'var(--color-success)' : 'var(--color-warning)';
      
      if (record.clock_in_time) {
        timeEl.textContent = `In: ${record.clock_in_time}${record.clock_out_time ? ' | Out: ' + record.clock_out_time : ''}`;
      }
      
      // Update button states
      if (clockInBtn) clockInBtn.disabled = true;
      if (clockOutBtn) clockOutBtn.disabled = !!record.clock_out_time;
    } else {
      statusEl.textContent = 'Not Clocked In';
      statusEl.style.color = 'var(--color-text-muted)';
      timeEl.textContent = 'Clock in to start your day';
      if (clockInBtn) clockInBtn.disabled = false;
      if (clockOutBtn) clockOutBtn.disabled = true;
    }
  } catch (error) {
    console.error('Failed to load HR attendance status:', error);
  }
}

async function loadEmployeeStats() {
  try {
    const response = await api.employees.list({ per_page: 1 });
    const total = response.pagination?.total || 0;
    
    const activeResponse = await api.employees.list({ status: 'active', per_page: 1 });
    const activeCount = activeResponse.pagination?.total || 0;
    const inactiveCount = total - activeCount;

    document.getElementById('total-employees').textContent = formatNumber(total);
    document.getElementById('active-employees').textContent = `Active: ${formatNumber(activeCount)}`;
    document.getElementById('inactive-employees').textContent = `Inactive: ${formatNumber(inactiveCount)}`;
  } catch (error) {
    console.error('Failed to load employee stats:', error);
    document.getElementById('total-employees').textContent = '--';
  }
}

async function loadAttendanceStats() {
  try {
    const today = new Date().toISOString().split('T')[0];
    const response = await api.attendance.list({ date_from: today, date_to: today, per_page: 1000 });
    
    const records = response.data || [];
    const presentCount = records.filter(r => r.status === 'present').length;
    const absentCount = records.filter(r => r.status === 'absent').length;
    const lateCount = records.filter(r => r.status === 'late').length;
    
    const empResponse = await api.employees.list({ status: 'active', per_page: 1 });
    const totalEmployees = empResponse.pagination?.total || 1;
    
    const rate = totalEmployees > 0 ? Math.round((presentCount / totalEmployees) * 100) : 0;

    document.getElementById('attendance-rate').textContent = `${rate}%`;
    document.getElementById('present-count').textContent = `Present: ${presentCount}`;
    document.getElementById('absent-count').textContent = `Absent: ${absentCount}`;
    document.getElementById('leave-count').textContent = `Late: ${lateCount}`;
  } catch (error) {
    console.error('Failed to load attendance stats:', error);
    document.getElementById('attendance-rate').textContent = '--%';
  }
}

async function loadLeaveStats() {
  try {
    const response = await api.leave.requests({ status: 'pending', per_page: 1 });
    const pendingCount = response.pagination?.total || 0;
    document.getElementById('pending-leaves').textContent = formatNumber(pendingCount);
  } catch (error) {
    console.error('Failed to load leave stats:', error);
    document.getElementById('pending-leaves').textContent = '--';
  }
}

async function loadPayrollStats() {
  try {
    const currentMonth = new Date().toISOString().slice(0, 7);
    const response = await api.payroll.list({ month: currentMonth, per_page: 1000 });
    
    const records = response.data || [];
    const totalNet = records.reduce((sum, r) => sum + parseFloat(r.net_salary || 0), 0);
    const processedCount = records.length;

    document.getElementById('payroll-total').textContent = formatCurrency(totalNet);
    document.getElementById('payroll-status').textContent = `${processedCount} processed`;
  } catch (error) {
    console.error('Failed to load payroll stats:', error);
    document.getElementById('payroll-total').textContent = '₹--';
  }
}

async function loadRecentLeaves() {
  const container = document.getElementById('recent-leaves');
  
  try {
    const response = await api.leave.requests({ status: 'pending', per_page: 5 });
    const requests = response.data || [];

    if (requests.length === 0) {
      container.innerHTML = `
        <div class="empty-state">
          <p class="empty-state-title">No Pending Requests</p>
          <p class="empty-state-text">All leave requests have been processed.</p>
        </div>
      `;
      return;
    }

    container.innerHTML = requests.map(req => `
      <div class="flex items-center justify-between py-sm border-b" style="border-color: var(--color-border-light);">
        <div class="flex items-center gap-md">
          <div class="user-avatar" style="width: 32px; height: 32px; font-size: 12px;">
            ${getInitials(req.first_name, req.last_name)}
          </div>
          <div>
            <div class="font-medium text-heading text-sm">${escapeHtml(req.first_name)} ${escapeHtml(req.last_name)}</div>
            <div class="text-xs text-muted">${escapeHtml(req.leave_type_name)} • ${formatDate(req.start_date)} - ${formatDate(req.end_date)}</div>
          </div>
        </div>
        <span class="badge badge-warning">${req.total_days} day${req.total_days > 1 ? 's' : ''}</span>
      </div>
    `).join('');
  } catch (error) {
    console.error('Failed to load recent leaves:', error);
    container.innerHTML = `<p class="text-error text-center">Failed to load</p>`;
  }
}

// ============================================
// EMPLOYEE DASHBOARD FUNCTIONS
// ============================================

async function loadMyAttendanceStatus() {
  try {
    const today = new Date().toISOString().split('T')[0];
    const response = await api.attendance.me({ date_from: today, date_to: today });
    const records = response.data || [];
    
    const statusEl = document.getElementById('my-attendance-status');
    const timeEl = document.getElementById('my-clock-time');
    
    if (records.length > 0) {
      const record = records[0];
      statusEl.textContent = record.status === 'present' ? 'Present' : record.status;
      statusEl.style.color = record.status === 'present' ? 'var(--color-success)' : 'var(--color-warning)';
      
      if (record.clock_in_time) {
        timeEl.textContent = `In: ${record.clock_in_time}${record.clock_out_time ? ' | Out: ' + record.clock_out_time : ''}`;
      }
      
      // Update button states
      document.getElementById('clock-in-btn').disabled = true;
      document.getElementById('clock-out-btn').disabled = !!record.clock_out_time;
    } else {
      statusEl.textContent = 'Not Clocked In';
      statusEl.style.color = 'var(--color-text-muted)';
      timeEl.textContent = 'Clock in to start your day';
    }
  } catch (error) {
    console.error('Failed to load attendance status:', error);
  }
}

async function loadMyLeaveBalance() {
  try {
    const response = await api.leave.balance();
    const balances = response.data || [];
    
    const totalBalance = balances.reduce((sum, b) => sum + (b.remaining || 0), 0);
    document.getElementById('my-leave-balance').textContent = totalBalance;
  } catch (error) {
    console.error('Failed to load leave balance:', error);
    document.getElementById('my-leave-balance').textContent = '--';
  }
}

async function loadMyLastSalary() {
  try {
    const response = await api.payroll.me({ per_page: 1 });
    const records = response.data || [];
    
    if (records.length > 0) {
      const record = records[0];
      document.getElementById('my-last-salary').textContent = formatCurrency(record.net_salary);
      document.getElementById('my-salary-month').textContent = formatPeriod(record.pay_period_start);
    } else {
      document.getElementById('my-last-salary').textContent = '₹--';
      document.getElementById('my-salary-month').textContent = 'No records';
    }
  } catch (error) {
    console.error('Failed to load salary:', error);
  }
}

async function loadMyLeaveRequests() {
  const container = document.getElementById('my-leave-requests');
  
  try {
    const response = await api.leave.myRequests({ per_page: 5 });
    const requests = response.data || [];

    if (requests.length === 0) {
      container.innerHTML = `
        <div class="empty-state">
          <p class="empty-state-title">No Leave Requests</p>
          <p class="empty-state-text">You haven't submitted any leave requests yet.</p>
        </div>
      `;
      return;
    }

    container.innerHTML = requests.map(req => `
      <div class="flex items-center justify-between py-sm border-b" style="border-color: var(--color-border-light);">
        <div>
          <div class="font-medium text-heading text-sm">${escapeHtml(req.leave_type_name)}</div>
          <div class="text-xs text-muted">${formatDate(req.start_date)} - ${formatDate(req.end_date)} (${req.total_days} days)</div>
        </div>
        <span class="badge ${getStatusBadgeClass(req.status)}">${req.status}</span>
      </div>
    `).join('');
  } catch (error) {
    console.error('Failed to load leave requests:', error);
    container.innerHTML = `<p class="text-error text-center">Failed to load</p>`;
  }
}

// Clock In/Out functions
async function clockIn() {
  try {
    await api.attendance.clockIn();
    toast.success('Clocked in successfully!');
    // Refresh appropriate status based on role
    const role = auth.user?.role_name;
    if (role === 'HR') {
      loadHRAttendanceStatus();
    } else {
      loadMyAttendanceStatus();
    }
  } catch (error) {
    toast.error(error.message || 'Failed to clock in');
  }
}

async function clockOut() {
  try {
    await api.attendance.clockOut();
    toast.success('Clocked out successfully!');
    // Refresh appropriate status based on role
    const role = auth.user?.role_name;
    if (role === 'HR') {
      loadHRAttendanceStatus();
    } else {
      loadMyAttendanceStatus();
    }
  } catch (error) {
    toast.error(error.message || 'Failed to clock out');
  }
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

function formatNumber(num) {
  return new Intl.NumberFormat().format(num);
}

function formatCurrency(amount) {
  return new Intl.NumberFormat('en-IN', {
    style: 'currency',
    currency: 'INR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount || 0);
}

function formatDate(dateStr) {
  if (!dateStr) return '--';
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

function formatPeriod(dateStr) {
  if (!dateStr) return '--';
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
}

function getInitials(firstName, lastName) {
  const first = firstName?.[0] || '';
  const last = lastName?.[0] || '';
  return (first + last).toUpperCase() || '?';
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text || '';
  return div.innerHTML;
}

function getStatusBadgeClass(status) {
  const classes = {
    pending: 'badge-warning',
    approved: 'badge-success',
    rejected: 'badge-error'
  };
  return classes[status] || 'badge-neutral';
}
