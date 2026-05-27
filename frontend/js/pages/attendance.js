/**
 * Attendance Page Logic
 */

let currentPage = 1;
let totalPages = 1;
let isAdminView = false;
let isHR = false;
let activeTab = 'all'; // 'all' or 'my'

(async function() {
  const isAuth = await auth.requireAuth();
  if (!isAuth) return;

  // Check if user is admin/HR
  const role = auth.user?.role_name;
  isAdminView = role === 'Admin' || role === 'HR';
  isHR = role === 'HR';

  sidebar.render('sidebar-container', isAdminView ? 'attendance' : 'my-attendance');
  header.render('header-container', isAdminView ? 'Attendance' : 'My Attendance');

  // Render HR tabs if HR user
  if (isHR) {
    renderHRTabs();
  }

  // Set default dates (current month)
  const today = new Date();
  const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
  document.getElementById('date-from').value = firstDay.toISOString().split('T')[0];
  document.getElementById('date-to').value = today.toISOString().split('T')[0];

  // Hide admin-only elements for employees
  if (!isAdminView) {
    const addBtn = document.getElementById('add-attendance-btn');
    if (addBtn) addBtn.style.display = 'none';
    const statusFilter = document.getElementById('status-filter');
    if (statusFilter) statusFilter.parentElement.style.display = 'none';
  }

  initEventListeners();
  loadAttendance();
  if (isAdminView) loadEmployeesForSelect();
})();

function renderHRTabs() {
  const cardHeader = document.querySelector('.card-header');
  if (!cardHeader) return;

  // Insert tabs before the filters
  const tabsHtml = `
    <div class="tabs mb-md" id="attendance-tabs" style="margin-bottom: 1rem;">
      <button class="tab-btn active" data-tab="all">All Employees</button>
      <button class="tab-btn" data-tab="my">My Attendance</button>
    </div>
  `;
  
  cardHeader.insertAdjacentHTML('afterbegin', tabsHtml);

  // Add tab click handlers
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      activeTab = btn.dataset.tab;
      currentPage = 1;
      
      // Show/hide admin controls based on tab
      const addBtn = document.getElementById('add-attendance-btn');
      const statusFilter = document.getElementById('status-filter');
      if (activeTab === 'my') {
        if (addBtn) addBtn.style.display = 'none';
        if (statusFilter) statusFilter.parentElement.style.display = 'none';
      } else {
        if (addBtn) addBtn.style.display = '';
        if (statusFilter) statusFilter.parentElement.style.display = '';
      }
      
      loadAttendance();
    });
  });
}

function initEventListeners() {
  document.getElementById('date-from').addEventListener('change', () => {
    currentPage = 1;
    loadAttendance();
  });

  document.getElementById('date-to').addEventListener('change', () => {
    currentPage = 1;
    loadAttendance();
  });

  document.getElementById('status-filter').addEventListener('change', () => {
    currentPage = 1;
    loadAttendance();
  });

  document.getElementById('prev-btn').addEventListener('click', () => {
    if (currentPage > 1) {
      currentPage--;
      loadAttendance();
    }
  });

  document.getElementById('next-btn').addEventListener('click', () => {
    if (currentPage < totalPages) {
      currentPage++;
      loadAttendance();
    }
  });

  document.getElementById('add-attendance-btn').addEventListener('click', openModal);

  document.getElementById('attendance-modal').addEventListener('click', (e) => {
    if (e.target.id === 'attendance-modal') closeModal();
  });

  document.getElementById('attendance-form').addEventListener('submit', handleFormSubmit);
}

async function loadAttendance() {
  const tbody = document.getElementById('attendance-tbody');
  tbody.innerHTML = `<tr><td colspan="7" class="text-center p-lg"><div class="spinner"></div></td></tr>`;

  try {
    const params = {
      page: currentPage,
      per_page: 20,
      date_from: document.getElementById('date-from').value,
      date_to: document.getElementById('date-to').value
    };

    // Only add status filter for admin view when viewing all employees
    if (isAdminView && activeTab === 'all') {
      params.status = document.getElementById('status-filter').value;
    }

    Object.keys(params).forEach(key => {
      if (!params[key]) delete params[key];
    });

    // Use appropriate endpoint based on role and active tab
    let response;
    if (isAdminView && activeTab === 'all') {
      response = await api.attendance.list(params);
    } else {
      // Employee view OR HR viewing "My Attendance" tab
      response = await api.attendance.me(params);
    }
    
    const records = response.data || [];
    const pagination = response.pagination || {};

    totalPages = pagination.total_pages || 1;
    updatePaginationInfo(pagination);
    renderAttendance(records);
  } catch (error) {
    console.error('Failed to load attendance:', error);
    tbody.innerHTML = `
      <tr>
        <td colspan="7" class="text-center p-lg">
          <p class="text-error">Failed to load attendance records</p>
          <button class="btn btn-outline btn-sm mt-sm" onclick="loadAttendance()">Retry</button>
        </td>
      </tr>
    `;
  }
}

function renderAttendance(records) {
  const tbody = document.getElementById('attendance-tbody');
  const showingMyAttendance = !isAdminView || activeTab === 'my';

  if (records.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="7" class="text-center p-lg">
          <div class="empty-state">
            <svg class="empty-state-icon" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <circle cx="12" cy="12" r="10"></circle>
              <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            <p class="empty-state-title">No Attendance Records</p>
            <p class="empty-state-text">No records found for the selected date range.</p>
          </div>
        </td>
      </tr>
    `;
    return;
  }

  tbody.innerHTML = records.map(rec => `
    <tr>
      <td>
        <div class="flex items-center gap-md">
          <div class="user-avatar" style="width: 32px; height: 32px; font-size: 12px;">
            ${getInitials(rec.first_name, rec.last_name)}
          </div>
          <div>
            <div class="font-medium text-heading text-sm">${escapeHtml(rec.first_name)} ${escapeHtml(rec.last_name)}</div>
            <div class="text-xs text-muted">${escapeHtml(rec.employee_code || '')}</div>
          </div>
        </div>
      </td>
      <td>${formatDate(rec.attendance_date)}</td>
      <td>${rec.clock_in_time || '-'}</td>
      <td>${rec.clock_out_time || '-'}</td>
      <td>${rec.total_hours ? rec.total_hours + ' hrs' : '-'}</td>
      <td>
        <span class="badge ${getStatusBadgeClass(rec.status)}">
          ${rec.status || 'unknown'}
        </span>
      </td>
      <td>
        ${(isAdminView && activeTab === 'all') ? `
          <button class="btn btn-ghost btn-sm btn-icon" onclick="editAttendance(${rec.id})" title="Edit">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
              <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
          </button>
        ` : '-'}
      </td>
    </tr>
  `).join('');
}

function getStatusBadgeClass(status) {
  const classes = {
    present: 'badge-success',
    absent: 'badge-error',
    late: 'badge-warning',
    half_day: 'badge-info'
  };
  return classes[status] || 'badge-neutral';
}

function updatePaginationInfo(pagination) {
  const total = pagination.total || 0;
  const page = pagination.page || 1;
  const perPage = pagination.per_page || 20;
  const start = total > 0 ? (page - 1) * perPage + 1 : 0;
  const end = Math.min(page * perPage, total);

  document.getElementById('pagination-info').textContent = `Showing ${start}-${end} of ${total}`;
  document.getElementById('prev-btn').disabled = page <= 1;
  document.getElementById('next-btn').disabled = page >= totalPages;
}

async function loadEmployeesForSelect() {
  try {
    const response = await api.employees.list({ status: 'active', per_page: 1000 });
    const employees = response.data || [];
    
    const select = document.getElementById('employee_id');
    select.innerHTML = '<option value="">Select Employee</option>' + 
      employees.map(emp => `<option value="${emp.id}">${emp.first_name} ${emp.last_name} (${emp.employee_code})</option>`).join('');
  } catch (error) {
    console.error('Failed to load employees:', error);
  }
}

function openModal() {
  document.getElementById('attendance-form').reset();
  document.getElementById('date').value = new Date().toISOString().split('T')[0];
  document.getElementById('attendance-modal').classList.add('open');
}

function closeModal() {
  document.getElementById('attendance-modal').classList.remove('open');
}

async function handleFormSubmit(e) {
  e.preventDefault();

  const data = {
    employee_id: document.getElementById('employee_id').value,
    date: document.getElementById('date').value,
    clock_in_time: document.getElementById('clock_in_time').value || undefined,
    clock_out_time: document.getElementById('clock_out_time').value || undefined,
    status: document.getElementById('status').value,
    notes: document.getElementById('notes').value || undefined
  };

  Object.keys(data).forEach(key => {
    if (data[key] === undefined) delete data[key];
  });

  try {
    await api.attendance.create(data);
    toast.success('Attendance record created successfully');
    closeModal();
    loadAttendance();
  } catch (error) {
    toast.error(error.message || 'Failed to create attendance record');
  }
}

function formatDate(dateStr) {
  if (!dateStr) return '-';
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function getInitials(firstName, lastName) {
  return ((firstName?.[0] || '') + (lastName?.[0] || '')).toUpperCase() || '?';
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text || '';
  return div.innerHTML;
}
