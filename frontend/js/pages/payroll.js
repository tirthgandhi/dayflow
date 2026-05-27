/**
 * Payroll Page Logic
 */

let currentPage = 1;
let totalPages = 1;
let isAdminView = false;

(async function() {
  const isAuth = await auth.requireAuth();
  if (!isAuth) return;

  // Check if user is admin/HR
  const role = auth.user?.role_name;
  isAdminView = role === 'Admin' || role === 'HR';

  sidebar.render('sidebar-container', isAdminView ? 'payroll' : 'my-payroll');
  header.render('header-container', isAdminView ? 'Payroll' : 'My Payslips');

  // Render appropriate view
  if (!isAdminView) {
    renderEmployeeView();
  }

  // Set default month to current
  const currentMonth = new Date().toISOString().slice(0, 7);
  const monthFilter = document.getElementById('month-filter');
  if (monthFilter) monthFilter.value = currentMonth;
  
  const processMonth = document.getElementById('process-month');
  if (processMonth) processMonth.value = currentMonth;

  initEventListeners();
  loadPayroll();
})();

function renderEmployeeView() {
  const mainContent = document.querySelector('.main-content');
  mainContent.innerHTML = `
    <!-- My Payslips -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">My Payslips</h3>
        <div class="flex gap-md">
          <input type="month" class="form-input" id="month-filter" style="width: auto;">
        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-container">
          <table class="table">
            <thead>
              <tr>
                <th>Period</th>
                <th>Basic Salary</th>
                <th>Allowances</th>
                <th>Deductions</th>
                <th>Net Salary</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody id="payroll-tbody">
              <tr><td colspan="6" class="text-center p-lg"><div class="spinner"></div></td></tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-footer flex items-center justify-between">
        <span class="text-sm text-muted" id="pagination-info">Showing 0-0 of 0</span>
        <div class="flex gap-sm">
          <button class="btn btn-outline btn-sm" id="prev-btn" disabled>Previous</button>
          <button class="btn btn-outline btn-sm" id="next-btn" disabled>Next</button>
        </div>
      </div>
    </div>
  `;
}

function initEventListeners() {
  const monthFilter = document.getElementById('month-filter');
  const statusFilter = document.getElementById('status-filter');
  const prevBtn = document.getElementById('prev-btn');
  const nextBtn = document.getElementById('next-btn');
  const processBtn = document.getElementById('process-btn');
  const confirmProcessBtn = document.getElementById('confirm-process-btn');
  const exportBtn = document.getElementById('export-btn');
  const processModal = document.getElementById('process-modal');

  if (monthFilter) {
    monthFilter.addEventListener('change', () => {
      currentPage = 1;
      loadPayroll();
    });
  }

  if (statusFilter) {
    statusFilter.addEventListener('change', () => {
      currentPage = 1;
      loadPayroll();
    });
  }

  if (prevBtn) {
    prevBtn.addEventListener('click', () => {
      if (currentPage > 1) {
        currentPage--;
        loadPayroll();
      }
    });
  }

  if (nextBtn) {
    nextBtn.addEventListener('click', () => {
      if (currentPage < totalPages) {
        currentPage++;
        loadPayroll();
      }
    });
  }

  if (processBtn) processBtn.addEventListener('click', openProcessModal);
  if (confirmProcessBtn) confirmProcessBtn.addEventListener('click', processPayroll);
  if (exportBtn) exportBtn.addEventListener('click', exportPayroll);

  if (processModal) {
    processModal.addEventListener('click', (e) => {
      if (e.target.id === 'process-modal') closeProcessModal();
    });
  }
}

async function loadPayroll() {
  const tbody = document.getElementById('payroll-tbody');
  tbody.innerHTML = `<tr><td colspan="6" class="text-center p-lg"><div class="spinner"></div></td></tr>`;

  try {
    const params = {
      page: currentPage,
      per_page: 20,
      month: document.getElementById('month-filter')?.value || ''
    };

    // Only add status filter for admin view
    if (isAdminView) {
      const statusFilter = document.getElementById('status-filter');
      if (statusFilter) params.status = statusFilter.value;
    }

    Object.keys(params).forEach(key => {
      if (!params[key]) delete params[key];
    });

    // Use appropriate endpoint based on role
    const response = isAdminView 
      ? await api.payroll.list(params)
      : await api.payroll.me(params);
    
    const records = response.data || [];
    const pagination = response.pagination || {};

    totalPages = pagination.total_pages || 1;
    updatePaginationInfo(pagination);
    
    if (isAdminView) {
      updateSummary(records);
      renderPayroll(records);
    } else {
      renderMyPayslips(records);
    }
  } catch (error) {
    console.error('Failed to load payroll:', error);
    tbody.innerHTML = `
      <tr>
        <td colspan="6" class="text-center p-lg">
          <p class="text-error">Failed to load payroll records</p>
          <button class="btn btn-outline btn-sm mt-sm" onclick="loadPayroll()">Retry</button>
        </td>
      </tr>
    `;
  }
}

function renderMyPayslips(records) {
  const tbody = document.getElementById('payroll-tbody');

  if (records.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="6" class="text-center p-lg">
          <div class="empty-state">
            <svg class="empty-state-icon" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <text x="6" y="18" font-size="20" font-weight="bold" fill="currentColor" stroke="none">₹</text>
            </svg>
            <p class="empty-state-title">No Payslips</p>
            <p class="empty-state-text">No payslips available for the selected period.</p>
          </div>
        </td>
      </tr>
    `;
    return;
  }

  tbody.innerHTML = records.map(rec => `
    <tr>
      <td>${formatPeriod(rec.pay_period_start, rec.pay_period_end)}</td>
      <td>${formatCurrency(rec.basic_salary)}</td>
      <td class="text-success">${formatCurrency(rec.allowances)}</td>
      <td class="text-error">${formatCurrency(rec.deductions)}</td>
      <td class="font-semibold text-success">${formatCurrency(rec.net_salary)}</td>
      <td>
        <span class="badge ${getStatusBadgeClass(rec.status)}">
          ${rec.status}
        </span>
      </td>
    </tr>
  `).join('');
}

function updateSummary(records) {
  const totalGross = records.reduce((sum, r) => sum + parseFloat(r.gross_salary || 0), 0);
  const totalDeductions = records.reduce((sum, r) => sum + parseFloat(r.deductions || 0), 0);
  const totalNet = records.reduce((sum, r) => sum + parseFloat(r.net_salary || 0), 0);

  document.getElementById('total-gross').textContent = formatCurrency(totalGross);
  document.getElementById('total-deductions').textContent = formatCurrency(totalDeductions);
  document.getElementById('total-net').textContent = formatCurrency(totalNet);
  document.getElementById('total-records').textContent = records.length;
}

function renderPayroll(records) {
  const tbody = document.getElementById('payroll-tbody');

  if (records.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="6" class="text-center p-lg">
          <div class="empty-state">
            <svg class="empty-state-icon" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <text x="6" y="18" font-size="20" font-weight="bold" fill="currentColor" stroke="none">₹</text>
            </svg>
            <p class="empty-state-title">No Payroll Records</p>
            <p class="empty-state-text">No payroll has been processed for this period.</p>
            <button class="btn btn-primary mt-md" onclick="openProcessModal()">Process Payroll</button>
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
      <td>${formatPeriod(rec.pay_period_start, rec.pay_period_end)}</td>
      <td class="font-medium">${formatCurrency(rec.gross_salary)}</td>
      <td class="text-error">${formatCurrency(rec.deductions)}</td>
      <td class="font-semibold text-success">${formatCurrency(rec.net_salary)}</td>
      <td>
        <span class="badge ${getStatusBadgeClass(rec.status)}">
          ${rec.status}
        </span>
      </td>
    </tr>
  `).join('');
}

function getStatusBadgeClass(status) {
  const classes = {
    pending: 'badge-warning',
    processed: 'badge-info',
    paid: 'badge-success'
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

function openProcessModal() {
  document.getElementById('process-preview').style.display = 'none';
  document.getElementById('process-modal').classList.add('open');
}

function closeProcessModal() {
  document.getElementById('process-modal').classList.remove('open');
}

async function processPayroll() {
  const month = document.getElementById('process-month').value;
  if (!month) {
    toast.error('Please select a month');
    return;
  }

  const btn = document.getElementById('confirm-process-btn');
  btn.disabled = true;
  btn.textContent = 'Processing...';

  try {
    const response = await api.payroll.process(month);
    const result = response.data;

    toast.success(`Payroll processed: ${result.processed_count} records created`);
    
    if (result.skipped_count > 0) {
      toast.warning(`${result.skipped_count} employees skipped (already processed)`);
    }

    closeProcessModal();
    document.getElementById('month-filter').value = month;
    loadPayroll();
  } catch (error) {
    toast.error(error.message || 'Failed to process payroll');
  } finally {
    btn.disabled = false;
    btn.textContent = 'Process Payroll';
  }
}

function exportPayroll() {
  const month = document.getElementById('month-filter').value;
  toast.info('Export functionality coming soon');
  // In a real app, this would trigger a CSV/Excel download
}

function formatCurrency(amount) {
  return new Intl.NumberFormat('en-IN', {
    style: 'currency',
    currency: 'INR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount || 0);
}

function formatPeriod(start, end) {
  if (!start) return '-';
  const startDate = new Date(start);
  return startDate.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
}

function getInitials(firstName, lastName) {
  return ((firstName?.[0] || '') + (lastName?.[0] || '')).toUpperCase() || '?';
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text || '';
  return div.innerHTML;
}
