/**
 * Leave Management Page Logic
 */

let currentPage = 1;
let totalPages = 1;
let pendingAction = null;
let isAdminView = false;

(async function() {
  const isAuth = await auth.requireAuth();
  if (!isAuth) return;

  // Check if user is admin/HR
  const role = auth.user?.role_name;
  isAdminView = role === 'Admin' || role === 'HR';

  sidebar.render('sidebar-container', isAdminView ? 'leave' : 'my-leave');
  header.render('header-container', isAdminView ? 'Leave Management' : 'My Leave');

  // Render appropriate view
  if (isAdminView) {
    renderAdminView();
    loadLeaveTypes();
    loadPendingRequests();
    loadAllRequests();
  } else {
    renderEmployeeView();
    loadLeaveTypes(); // Load after rendering so the select element exists
    loadMyRequests();
    loadMyBalance();
  }

  initEventListeners();
})();

function renderAdminView() {
  // Admin view is already in the HTML, no changes needed
}

function renderEmployeeView() {
  const mainContent = document.querySelector('.main-content');
  mainContent.innerHTML = `
    <!-- Leave Balance -->
    <div class="card mb-lg">
      <div class="card-header">
        <h3 class="card-title">My Leave Balance</h3>
        <button class="btn btn-primary" id="request-leave-btn">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-sm">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
          </svg>
          Request Leave
        </button>
      </div>
      <div class="card-body" id="leave-balance-container">
        <div class="text-center p-lg"><div class="spinner"></div></div>
      </div>
    </div>

    <!-- My Leave Requests -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">My Leave Requests</h3>
        <div class="flex gap-md">
          <select class="form-select" id="status-filter" style="width: auto;">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
          </select>
        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-container">
          <table class="table">
            <thead>
              <tr>
                <th>Leave Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Days</th>
                <th>Reason</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody id="requests-tbody">
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

    <!-- Request Leave Modal -->
    <div class="modal" id="leave-modal">
      <div class="modal-content">
        <div class="modal-header">
          <h3 class="modal-title">Request Leave</h3>
          <button class="btn btn-ghost btn-icon" onclick="closeLeaveModal()">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>
        <form id="leave-form">
          <div class="modal-body">
            <div class="form-group">
              <label class="form-label">Leave Type</label>
              <select class="form-select" id="leave_type_id" required>
                <option value="">Select Type</option>
              </select>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Start Date</label>
                <input type="date" class="form-input" id="start_date" required>
              </div>
              <div class="form-group">
                <label class="form-label">End Date</label>
                <input type="date" class="form-input" id="end_date" required>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Reason</label>
              <textarea class="form-input" id="reason" rows="3" placeholder="Enter reason for leave..."></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeLeaveModal()">Cancel</button>
            <button type="submit" class="btn btn-primary">Submit Request</button>
          </div>
        </form>
      </div>
    </div>
  `;
}

function initEventListeners() {
  // These elements may not exist depending on the view
  const statusFilter = document.getElementById('status-filter');
  const typeFilter = document.getElementById('type-filter');
  const prevBtn = document.getElementById('prev-btn');
  const nextBtn = document.getElementById('next-btn');
  const confirmModal = document.getElementById('confirm-modal');
  const requestLeaveBtn = document.getElementById('request-leave-btn');
  const leaveModal = document.getElementById('leave-modal');
  const leaveForm = document.getElementById('leave-form');

  if (statusFilter) {
    statusFilter.addEventListener('change', () => {
      currentPage = 1;
      if (isAdminView) {
        loadAllRequests();
      } else {
        loadMyRequests();
      }
    });
  }

  if (typeFilter) {
    typeFilter.addEventListener('change', () => {
      currentPage = 1;
      loadAllRequests();
    });
  }

  if (prevBtn) {
    prevBtn.addEventListener('click', () => {
      if (currentPage > 1) {
        currentPage--;
        if (isAdminView) {
          loadAllRequests();
        } else {
          loadMyRequests();
        }
      }
    });
  }

  if (nextBtn) {
    nextBtn.addEventListener('click', () => {
      if (currentPage < totalPages) {
        currentPage++;
        if (isAdminView) {
          loadAllRequests();
        } else {
          loadMyRequests();
        }
      }
    });
  }

  if (confirmModal) {
    confirmModal.addEventListener('click', (e) => {
      if (e.target.id === 'confirm-modal') closeConfirmModal();
    });
  }

  if (requestLeaveBtn) {
    requestLeaveBtn.addEventListener('click', openLeaveModal);
  }

  if (leaveModal) {
    leaveModal.addEventListener('click', (e) => {
      if (e.target.id === 'leave-modal') closeLeaveModal();
    });
  }

  if (leaveForm) {
    leaveForm.addEventListener('submit', handleLeaveSubmit);
  }
}

async function loadLeaveTypes() {
  try {
    const response = await api.leave.types();
    const types = response.data || [];
    
    // Populate admin view type filter if it exists
    const typeFilter = document.getElementById('type-filter');
    if (typeFilter) {
      typeFilter.innerHTML = '<option value="">All Types</option>' + 
        types.map(t => `<option value="${t.id}">${t.name}</option>`).join('');
    }
    
    // Also populate the employee modal leave type select if it exists
    const leaveTypeSelect = document.getElementById('leave_type_id');
    if (leaveTypeSelect) {
      leaveTypeSelect.innerHTML = '<option value="">Select Type</option>' + 
        types.map(t => `<option value="${t.id}">${t.name}</option>`).join('');
    }
  } catch (error) {
    console.error('Failed to load leave types:', error);
  }
}

async function loadPendingRequests() {
  const container = document.getElementById('pending-requests');

  try {
    const response = await api.leave.requests({ status: 'pending', per_page: 10 });
    const requests = response.data || [];
    const total = response.pagination?.total || 0;

    document.getElementById('pending-count').textContent = total;

    if (requests.length === 0) {
      container.innerHTML = `
        <div class="empty-state">
          <svg class="empty-state-icon" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
          </svg>
          <p class="empty-state-title">All Caught Up!</p>
          <p class="empty-state-text">No pending leave requests to review.</p>
        </div>
      `;
      return;
    }

    container.innerHTML = requests.map(req => `
      <div class="flex items-center justify-between py-md border-b" style="border-color: var(--color-border-light);">
        <div class="flex items-center gap-md">
          <div class="user-avatar" style="width: 40px; height: 40px;">
            ${getInitials(req.first_name, req.last_name)}
          </div>
          <div>
            <div class="font-medium text-heading">${escapeHtml(req.first_name)} ${escapeHtml(req.last_name)}</div>
            <div class="text-sm text-muted">
              ${escapeHtml(req.leave_type_name)} • ${formatDate(req.start_date)} - ${formatDate(req.end_date)} (${req.total_days} day${req.total_days > 1 ? 's' : ''})
            </div>
            ${req.reason ? `<div class="text-xs text-muted mt-xs">"${escapeHtml(req.reason)}"</div>` : ''}
          </div>
        </div>
        <div class="flex gap-sm">
          <button class="btn btn-success btn-sm" onclick="approveRequest(${req.id})">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            Approve
          </button>
          <button class="btn btn-danger btn-sm" onclick="rejectRequest(${req.id})">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
            Reject
          </button>
        </div>
      </div>
    `).join('');
  } catch (error) {
    console.error('Failed to load pending requests:', error);
    container.innerHTML = `
      <div class="text-center p-lg">
        <p class="text-error">Failed to load pending requests</p>
        <button class="btn btn-outline btn-sm mt-sm" onclick="loadPendingRequests()">Retry</button>
      </div>
    `;
  }
}

async function loadAllRequests() {
  const tbody = document.getElementById('requests-tbody');
  tbody.innerHTML = `<tr><td colspan="7" class="text-center p-lg"><div class="spinner"></div></td></tr>`;

  try {
    const params = {
      page: currentPage,
      per_page: 20,
      status: document.getElementById('status-filter').value,
      leave_type_id: document.getElementById('type-filter').value
    };

    Object.keys(params).forEach(key => {
      if (!params[key]) delete params[key];
    });

    const response = await api.leave.requests(params);
    const requests = response.data || [];
    const pagination = response.pagination || {};

    totalPages = pagination.total_pages || 1;
    updatePaginationInfo(pagination);
    renderRequests(requests);
  } catch (error) {
    console.error('Failed to load requests:', error);
    tbody.innerHTML = `
      <tr>
        <td colspan="7" class="text-center p-lg">
          <p class="text-error">Failed to load leave requests</p>
          <button class="btn btn-outline btn-sm mt-sm" onclick="loadAllRequests()">Retry</button>
        </td>
      </tr>
    `;
  }
}

function renderRequests(requests) {
  const tbody = document.getElementById('requests-tbody');

  if (requests.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="7" class="text-center p-lg">
          <div class="empty-state">
            <p class="empty-state-title">No Leave Requests</p>
            <p class="empty-state-text">No requests match your filters.</p>
          </div>
        </td>
      </tr>
    `;
    return;
  }

  tbody.innerHTML = requests.map(req => `
    <tr>
      <td>
        <div class="flex items-center gap-md">
          <div class="user-avatar" style="width: 32px; height: 32px; font-size: 12px;">
            ${getInitials(req.first_name, req.last_name)}
          </div>
          <div>
            <div class="font-medium text-heading text-sm">${escapeHtml(req.first_name)} ${escapeHtml(req.last_name)}</div>
            <div class="text-xs text-muted">${escapeHtml(req.employee_code || '')}</div>
          </div>
        </div>
      </td>
      <td>${escapeHtml(req.leave_type_name)}</td>
      <td>${formatDate(req.start_date)}</td>
      <td>${formatDate(req.end_date)}</td>
      <td>${req.total_days}</td>
      <td>
        <span class="badge ${getStatusBadgeClass(req.status)}">
          ${req.status}
        </span>
      </td>
      <td>
        ${req.status === 'pending' ? `
          <div class="flex gap-xs">
            <button class="btn btn-ghost btn-sm btn-icon text-success" onclick="approveRequest(${req.id})" title="Approve">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"></polyline>
              </svg>
            </button>
            <button class="btn btn-ghost btn-sm btn-icon text-error" onclick="rejectRequest(${req.id})" title="Reject">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>
          </div>
        ` : '-'}
      </td>
    </tr>
  `).join('');
}

function getStatusBadgeClass(status) {
  const classes = {
    pending: 'badge-warning',
    approved: 'badge-success',
    rejected: 'badge-error'
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

function approveRequest(id) {
  pendingAction = { type: 'approve', id };
  document.getElementById('confirm-title').textContent = 'Approve Leave Request';
  document.getElementById('confirm-message').textContent = 'Are you sure you want to approve this leave request?';
  document.getElementById('reject-reason-group').style.display = 'none';
  document.getElementById('confirm-btn').className = 'btn btn-success';
  document.getElementById('confirm-btn').textContent = 'Approve';
  document.getElementById('confirm-btn').onclick = confirmAction;
  document.getElementById('confirm-modal').classList.add('open');
}

function rejectRequest(id) {
  pendingAction = { type: 'reject', id };
  document.getElementById('confirm-title').textContent = 'Reject Leave Request';
  document.getElementById('confirm-message').textContent = 'Are you sure you want to reject this leave request?';
  document.getElementById('reject-reason-group').style.display = 'block';
  document.getElementById('reject-reason').value = '';
  document.getElementById('confirm-btn').className = 'btn btn-danger';
  document.getElementById('confirm-btn').textContent = 'Reject';
  document.getElementById('confirm-btn').onclick = confirmAction;
  document.getElementById('confirm-modal').classList.add('open');
}

async function confirmAction() {
  if (!pendingAction) return;

  const btn = document.getElementById('confirm-btn');
  btn.disabled = true;
  btn.textContent = 'Processing...';

  try {
    if (pendingAction.type === 'approve') {
      await api.leave.approve(pendingAction.id);
      toast.success('Leave request approved');
    } else {
      const reason = document.getElementById('reject-reason').value;
      await api.leave.reject(pendingAction.id, reason);
      toast.success('Leave request rejected');
    }
    closeConfirmModal();
    loadPendingRequests();
    loadAllRequests();
  } catch (error) {
    toast.error(error.message || 'Failed to process request');
  } finally {
    btn.disabled = false;
    btn.textContent = pendingAction.type === 'approve' ? 'Approve' : 'Reject';
  }
}

function closeConfirmModal() {
  document.getElementById('confirm-modal').classList.remove('open');
  pendingAction = null;
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


// ============================================
// EMPLOYEE VIEW FUNCTIONS
// ============================================

async function loadMyBalance() {
  const container = document.getElementById('leave-balance-container');
  if (!container) return;

  try {
    const response = await api.leave.balance();
    const balances = response.data || [];

    if (balances.length === 0) {
      container.innerHTML = `<p class="text-muted text-center">No leave balance information available.</p>`;
      return;
    }

    container.innerHTML = `
      <div class="content-grid-3">
        ${balances.map(b => `
          <div class="kpi-card" style="padding: 1rem; border: 1px solid var(--color-border-light); border-radius: 8px;">
            <div class="kpi-content">
              <div class="kpi-value">${b.remaining || 0}</div>
              <div class="kpi-label">${escapeHtml(b.leave_type_name)}</div>
              <div class="kpi-meta">of ${b.total_days || 0} days</div>
            </div>
          </div>
        `).join('')}
      </div>
    `;
  } catch (error) {
    console.error('Failed to load leave balance:', error);
    container.innerHTML = `<p class="text-error text-center">Failed to load leave balance</p>`;
  }
}

async function loadMyRequests() {
  const tbody = document.getElementById('requests-tbody');
  if (!tbody) return;
  
  tbody.innerHTML = `<tr><td colspan="6" class="text-center p-lg"><div class="spinner"></div></td></tr>`;

  try {
    const params = {
      page: currentPage,
      per_page: 20,
      status: document.getElementById('status-filter')?.value || ''
    };

    Object.keys(params).forEach(key => {
      if (!params[key]) delete params[key];
    });

    const response = await api.leave.myRequests(params);
    const requests = response.data || [];
    const pagination = response.pagination || {};

    totalPages = pagination.total_pages || 1;
    updatePaginationInfo(pagination);
    renderMyRequests(requests);
  } catch (error) {
    console.error('Failed to load my requests:', error);
    tbody.innerHTML = `
      <tr>
        <td colspan="6" class="text-center p-lg">
          <p class="text-error">Failed to load leave requests</p>
          <button class="btn btn-outline btn-sm mt-sm" onclick="loadMyRequests()">Retry</button>
        </td>
      </tr>
    `;
  }
}

function renderMyRequests(requests) {
  const tbody = document.getElementById('requests-tbody');

  if (requests.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="6" class="text-center p-lg">
          <div class="empty-state">
            <p class="empty-state-title">No Leave Requests</p>
            <p class="empty-state-text">You haven't submitted any leave requests yet.</p>
          </div>
        </td>
      </tr>
    `;
    return;
  }

  tbody.innerHTML = requests.map(req => `
    <tr>
      <td>${escapeHtml(req.leave_type_name)}</td>
      <td>${formatDate(req.start_date)}</td>
      <td>${formatDate(req.end_date)}</td>
      <td>${req.total_days}</td>
      <td>${escapeHtml(req.reason) || '-'}</td>
      <td>
        <span class="badge ${getStatusBadgeClass(req.status)}">
          ${req.status}
        </span>
      </td>
    </tr>
  `).join('');
}

function openLeaveModal() {
  const form = document.getElementById('leave-form');
  if (form) form.reset();
  
  // Set default dates
  const today = new Date().toISOString().split('T')[0];
  const startDate = document.getElementById('start_date');
  const endDate = document.getElementById('end_date');
  if (startDate) startDate.value = today;
  if (endDate) endDate.value = today;
  
  // Populate leave types
  populateLeaveTypes();
  
  const modal = document.getElementById('leave-modal');
  if (modal) modal.classList.add('open');
}

function closeLeaveModal() {
  const modal = document.getElementById('leave-modal');
  if (modal) modal.classList.remove('open');
}

async function populateLeaveTypes() {
  const select = document.getElementById('leave_type_id');
  if (!select) return;

  try {
    const response = await api.leave.types();
    const types = response.data || [];
    
    select.innerHTML = '<option value="">Select Type</option>' + 
      types.map(t => `<option value="${t.id}">${t.name}</option>`).join('');
  } catch (error) {
    console.error('Failed to load leave types:', error);
  }
}

async function handleLeaveSubmit(e) {
  e.preventDefault();

  const data = {
    leave_type_id: document.getElementById('leave_type_id').value,
    start_date: document.getElementById('start_date').value,
    end_date: document.getElementById('end_date').value,
    reason: document.getElementById('reason').value || undefined
  };

  Object.keys(data).forEach(key => {
    if (data[key] === undefined) delete data[key];
  });

  try {
    await api.leave.create(data);
    toast.success('Leave request submitted successfully');
    closeLeaveModal();
    loadMyRequests();
    loadMyBalance();
  } catch (error) {
    toast.error(error.message || 'Failed to submit leave request');
  }
}
