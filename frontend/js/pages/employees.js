/**
 * Employees Page Logic
 */

let currentPage = 1;
let pageSize = 20;
let totalPages = 1;
let searchTimeout = null;

(async function() {
  const isAuth = await auth.requireAuth();
  if (!isAuth) return;

  sidebar.render('sidebar-container', 'employees');
  header.render('header-container', 'Employees');

  initEventListeners();
  loadEmployees();
})();

function initEventListeners() {
  // Search
  document.getElementById('search-input').addEventListener('input', (e) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
      currentPage = 1;
      loadEmployees();
    }, 300);
  });

  // Filters
  document.getElementById('status-filter').addEventListener('change', () => {
    currentPage = 1;
    loadEmployees();
  });

  document.getElementById('department-filter').addEventListener('change', () => {
    currentPage = 1;
    loadEmployees();
  });

  // Page size
  document.getElementById('page-size').addEventListener('change', (e) => {
    pageSize = parseInt(e.target.value);
    currentPage = 1;
    loadEmployees();
  });

  // Pagination
  document.getElementById('prev-btn').addEventListener('click', () => {
    if (currentPage > 1) {
      currentPage--;
      loadEmployees();
    }
  });

  document.getElementById('next-btn').addEventListener('click', () => {
    if (currentPage < totalPages) {
      currentPage++;
      loadEmployees();
    }
  });

  // Add employee button
  document.getElementById('add-employee-btn').addEventListener('click', () => openModal());

  // Modal
  document.getElementById('modal-close').addEventListener('click', closeModal);
  document.getElementById('modal-cancel').addEventListener('click', closeModal);
  document.getElementById('employee-modal').addEventListener('click', (e) => {
    if (e.target.id === 'employee-modal') closeModal();
  });

  // Form submit
  document.getElementById('employee-form').addEventListener('submit', handleFormSubmit);
}

async function loadEmployees() {
  const tbody = document.getElementById('employees-tbody');
  tbody.innerHTML = `<tr><td colspan="6" class="text-center p-lg"><div class="spinner"></div></td></tr>`;

  try {
    const params = {
      page: currentPage,
      per_page: pageSize,
      search: document.getElementById('search-input').value,
      status: document.getElementById('status-filter').value,
      department: document.getElementById('department-filter').value
    };

    // Remove empty params
    Object.keys(params).forEach(key => {
      if (!params[key]) delete params[key];
    });

    const response = await api.employees.list(params);
    const employees = response.data || [];
    const pagination = response.pagination || {};

    totalPages = pagination.total_pages || 1;
    
    updatePaginationInfo(pagination);
    renderEmployees(employees);
  } catch (error) {
    console.error('Failed to load employees:', error);
    tbody.innerHTML = `
      <tr>
        <td colspan="6" class="text-center p-lg">
          <p class="text-error">Failed to load employees</p>
          <button class="btn btn-outline btn-sm mt-sm" onclick="loadEmployees()">Retry</button>
        </td>
      </tr>
    `;
  }
}

function renderEmployees(employees) {
  const tbody = document.getElementById('employees-tbody');

  if (employees.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="6" class="text-center p-lg">
          <div class="empty-state">
            <svg class="empty-state-icon" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
              <circle cx="9" cy="7" r="4"></circle>
              <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            <p class="empty-state-title">No Employees Found</p>
            <p class="empty-state-text">Try adjusting your search or filters.</p>
          </div>
        </td>
      </tr>
    `;
    return;
  }

  tbody.innerHTML = employees.map(emp => `
    <tr data-id="${emp.id}">
      <td>
        <div class="flex items-center gap-md">
          <div class="user-avatar" style="width: 36px; height: 36px; font-size: 14px;">
            ${getInitials(emp.first_name, emp.last_name)}
          </div>
          <div>
            <div class="font-medium text-heading">${escapeHtml(emp.first_name)} ${escapeHtml(emp.last_name)}</div>
            <div class="text-xs text-muted">${escapeHtml(emp.employee_code || '')}</div>
          </div>
        </div>
      </td>
      <td>${escapeHtml(emp.department || '-')}</td>
      <td>${escapeHtml(emp.position || '-')}</td>
      <td>${escapeHtml(emp.email || '-')}</td>
      <td>
        <span class="badge ${emp.status === 'active' ? 'badge-success' : 'badge-error'}">
          ${emp.status || 'unknown'}
        </span>
      </td>
      <td>
        <div class="flex gap-xs">
          <button class="btn btn-ghost btn-sm btn-icon" onclick="editEmployee(${emp.id})" title="Edit">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
              <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
          </button>
          <button class="btn btn-ghost btn-sm btn-icon text-error" onclick="deleteEmployee(${emp.id})" title="Delete">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="3 6 5 6 21 6"></polyline>
              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
            </svg>
          </button>
        </div>
      </td>
    </tr>
  `).join('');
}

function updatePaginationInfo(pagination) {
  const total = pagination.total || 0;
  const page = pagination.page || 1;
  const perPage = pagination.per_page || pageSize;
  const start = total > 0 ? (page - 1) * perPage + 1 : 0;
  const end = Math.min(page * perPage, total);

  document.getElementById('pagination-info').textContent = `Showing ${start}-${end} of ${total}`;
  document.getElementById('prev-btn').disabled = page <= 1;
  document.getElementById('next-btn').disabled = page >= totalPages;
}

function openModal(employee = null) {
  const modal = document.getElementById('employee-modal');
  const form = document.getElementById('employee-form');
  const title = document.getElementById('modal-title');

  form.reset();
  document.getElementById('employee-id').value = '';

  if (employee) {
    title.textContent = 'Edit Employee';
    document.getElementById('employee-id').value = employee.id;
    document.getElementById('first_name').value = employee.first_name || '';
    document.getElementById('last_name').value = employee.last_name || '';
    document.getElementById('email').value = employee.email || '';
    document.getElementById('department').value = employee.department || '';
    document.getElementById('position').value = employee.position || '';
    document.getElementById('phone').value = employee.phone || '';
    document.getElementById('hire_date').value = employee.hire_date || '';
    document.getElementById('address').value = employee.address || '';
  } else {
    title.textContent = 'Add Employee';
  }

  modal.classList.add('open');
}

function closeModal() {
  document.getElementById('employee-modal').classList.remove('open');
}

async function editEmployee(id) {
  try {
    const response = await api.employees.get(id);
    openModal(response.data);
  } catch (error) {
    toast.error('Failed to load employee details');
  }
}

async function deleteEmployee(id) {
  if (!confirm('Are you sure you want to deactivate this employee?')) return;

  try {
    await api.employees.delete(id);
    toast.success('Employee deactivated successfully');
    loadEmployees();
  } catch (error) {
    toast.error(error.message || 'Failed to delete employee');
  }
}

async function handleFormSubmit(e) {
  e.preventDefault();

  const id = document.getElementById('employee-id').value;
  const data = {
    first_name: document.getElementById('first_name').value,
    last_name: document.getElementById('last_name').value,
    email: document.getElementById('email').value || undefined,
    department: document.getElementById('department').value || undefined,
    position: document.getElementById('position').value || undefined,
    phone: document.getElementById('phone').value || undefined,
    hire_date: document.getElementById('hire_date').value || undefined,
    address: document.getElementById('address').value || undefined
  };

  // Remove undefined values
  Object.keys(data).forEach(key => {
    if (data[key] === undefined) delete data[key];
  });

  const submitBtn = document.getElementById('modal-submit');
  submitBtn.disabled = true;
  submitBtn.textContent = 'Saving...';

  try {
    if (id) {
      await api.employees.update(id, data);
      toast.success('Employee updated successfully');
    } else {
      await api.employees.create(data);
      toast.success('Employee created successfully');
    }
    closeModal();
    loadEmployees();
  } catch (error) {
    if (error.details) {
      // Show validation errors
      Object.entries(error.details).forEach(([field, message]) => {
        const input = document.getElementById(field);
        if (input) {
          input.classList.add('error');
        }
      });
    }
    toast.error(error.message || 'Failed to save employee');
  } finally {
    submitBtn.disabled = false;
    submitBtn.textContent = 'Save Employee';
  }
}

// Utility functions
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
