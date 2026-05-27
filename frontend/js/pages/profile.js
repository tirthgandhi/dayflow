/**
 * Profile Page Logic
 * Displays and allows editing of user profile information
 */

let profileData = null;

(async function() {
  const isAuth = await auth.requireAuth();
  if (!isAuth) return;

  sidebar.render('sidebar-container', 'profile');
  header.render('header-container', 'My Profile');

  await loadProfile();
})();

async function loadProfile() {
  const mainContent = document.querySelector('.main-content');
  
  try {
    const response = await api.employees.me();
    profileData = response.data;
    
    renderProfile();
  } catch (error) {
    console.error('Failed to load profile:', error);
    mainContent.innerHTML = `
      <div class="card">
        <div class="card-body">
          <div class="empty-state">
            <p class="empty-state-title">Failed to Load Profile</p>
            <p class="empty-state-text">${error.message || 'Unable to load your profile information.'}</p>
            <button class="btn btn-primary mt-md" onclick="loadProfile()">Retry</button>
          </div>
        </div>
      </div>
    `;
  }
}

function renderProfile() {
  const mainContent = document.querySelector('.main-content');
  const role = auth.user?.role_name;
  
  mainContent.innerHTML = `
    <div class="content-grid-profile">
      <!-- Profile Card -->
      <div class="card">
        <div class="card-body text-center">
          <div class="profile-avatar">
            ${getInitials(profileData?.first_name, profileData?.last_name)}
          </div>
          <h2 class="profile-name">${escapeHtml(profileData?.first_name || '')} ${escapeHtml(profileData?.last_name || '')}</h2>
          <p class="profile-role">${escapeHtml(profileData?.designation || role || 'Employee')}</p>
          <p class="profile-department">${escapeHtml(profileData?.department || '')}</p>
          
          <div class="profile-meta">
            <div class="profile-meta-item">
              <span class="profile-meta-label">Employee Code</span>
              <span class="profile-meta-value">${escapeHtml(profileData?.employee_code || '--')}</span>
            </div>
            <div class="profile-meta-item">
              <span class="profile-meta-label">Join Date</span>
              <span class="profile-meta-value">${formatDate(profileData?.hire_date)}</span>
            </div>
            <div class="profile-meta-item">
              <span class="profile-meta-label">Status</span>
              <span class="badge ${profileData?.status === 'active' ? 'badge-success' : 'badge-warning'}">${profileData?.status || 'active'}</span>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Profile Details -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Personal Information</h3>
          <button class="btn btn-outline btn-sm" onclick="toggleEditMode()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-sm">
              <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
              <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
            Edit
          </button>
        </div>
        <div class="card-body">
          <form id="profile-form">
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">First Name</label>
                <input type="text" class="form-input" id="first_name" value="${escapeHtml(profileData?.first_name || '')}" disabled>
              </div>
              <div class="form-group">
                <label class="form-label">Last Name</label>
                <input type="text" class="form-input" id="last_name" value="${escapeHtml(profileData?.last_name || '')}" disabled>
              </div>
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" class="form-input" id="email" value="${escapeHtml(profileData?.email || auth.user?.email || '')}" disabled>
              </div>
              <div class="form-group">
                <label class="form-label">Phone</label>
                <input type="tel" class="form-input" id="phone" value="${escapeHtml(profileData?.phone || '')}" disabled>
              </div>
            </div>
            
            <div class="form-group">
              <label class="form-label">Address</label>
              <textarea class="form-input" id="address" rows="2" disabled>${escapeHtml(profileData?.address || '')}</textarea>
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Date of Birth</label>
                <input type="date" class="form-input" id="date_of_birth" value="${profileData?.date_of_birth || ''}" disabled>
              </div>
              <div class="form-group">
                <label class="form-label">Gender</label>
                <select class="form-select" id="gender" disabled>
                  <option value="">Select</option>
                  <option value="male" ${profileData?.gender === 'male' ? 'selected' : ''}>Male</option>
                  <option value="female" ${profileData?.gender === 'female' ? 'selected' : ''}>Female</option>
                  <option value="other" ${profileData?.gender === 'other' ? 'selected' : ''}>Other</option>
                </select>
              </div>
            </div>
            
            <div class="form-actions hidden" id="form-actions">
              <button type="button" class="btn btn-outline" onclick="cancelEdit()">Cancel</button>
              <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
          </form>
        </div>
      </div>
      
      <!-- Employment Details (Read-only) -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Employment Details</h3>
        </div>
        <div class="card-body">
          <div class="detail-grid">
            <div class="detail-item">
              <span class="detail-label">Department</span>
              <span class="detail-value">${escapeHtml(profileData?.department || '--')}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Designation</span>
              <span class="detail-value">${escapeHtml(profileData?.designation || '--')}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Employment Type</span>
              <span class="detail-value">${escapeHtml(profileData?.employment_type || 'Full-time')}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Hire Date</span>
              <span class="detail-value">${formatDate(profileData?.hire_date)}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <style>
      .content-grid-profile {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: var(--spacing-lg);
      }
      
      .content-grid-profile > .card:first-child {
        grid-row: span 2;
      }
      
      @media (max-width: 1024px) {
        .content-grid-profile {
          grid-template-columns: 1fr;
        }
        .content-grid-profile > .card:first-child {
          grid-row: auto;
        }
      }
      
      .profile-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--color-secondary), var(--color-primary));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 600;
        margin: 0 auto var(--spacing-md);
      }
      
      .profile-name {
        font-size: var(--font-size-xl);
        font-weight: var(--font-weight-semibold);
        color: var(--color-text-heading);
        margin-bottom: var(--spacing-xs);
      }
      
      .profile-role {
        color: var(--color-secondary);
        font-weight: var(--font-weight-medium);
        margin-bottom: var(--spacing-xs);
      }
      
      .profile-department {
        color: var(--color-text-muted);
        font-size: var(--font-size-sm);
        margin-bottom: var(--spacing-lg);
      }
      
      .profile-meta {
        border-top: 1px solid var(--color-border);
        padding-top: var(--spacing-md);
      }
      
      .profile-meta-item {
        display: flex;
        justify-content: space-between;
        padding: var(--spacing-sm) 0;
        border-bottom: 1px solid var(--color-border-light);
      }
      
      .profile-meta-item:last-child {
        border-bottom: none;
      }
      
      .profile-meta-label {
        color: var(--color-text-muted);
        font-size: var(--font-size-sm);
      }
      
      .profile-meta-value {
        font-weight: var(--font-weight-medium);
        color: var(--color-text-heading);
      }
      
      .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: var(--spacing-md);
      }
      
      @media (max-width: 640px) {
        .detail-grid {
          grid-template-columns: 1fr;
        }
      }
      
      .detail-item {
        padding: var(--spacing-sm);
        background: var(--color-bg-page);
        border-radius: var(--radius-md);
      }
      
      .detail-label {
        display: block;
        font-size: var(--font-size-xs);
        color: var(--color-text-muted);
        margin-bottom: var(--spacing-xs);
      }
      
      .detail-value {
        font-weight: var(--font-weight-medium);
        color: var(--color-text-heading);
      }
      
      .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: var(--spacing-sm);
        margin-top: var(--spacing-lg);
        padding-top: var(--spacing-lg);
        border-top: 1px solid var(--color-border);
      }
    </style>
  `;
  
  // Add form submit handler
  document.getElementById('profile-form').addEventListener('submit', handleProfileSubmit);
}

let isEditMode = false;

function toggleEditMode() {
  isEditMode = !isEditMode;
  
  const editableFields = ['first_name', 'last_name', 'phone', 'address', 'date_of_birth', 'gender'];
  const formActions = document.getElementById('form-actions');
  
  editableFields.forEach(fieldId => {
    const field = document.getElementById(fieldId);
    if (field) {
      field.disabled = !isEditMode;
    }
  });
  
  if (isEditMode) {
    formActions.classList.remove('hidden');
  } else {
    formActions.classList.add('hidden');
  }
}

function cancelEdit() {
  isEditMode = false;
  renderProfile();
}

async function handleProfileSubmit(e) {
  e.preventDefault();
  
  const data = {
    first_name: document.getElementById('first_name').value.trim(),
    last_name: document.getElementById('last_name').value.trim(),
    phone: document.getElementById('phone').value.trim(),
    address: document.getElementById('address').value.trim(),
    date_of_birth: document.getElementById('date_of_birth').value || null,
    gender: document.getElementById('gender').value || null
  };
  
  // Remove empty values
  Object.keys(data).forEach(key => {
    if (!data[key]) delete data[key];
  });
  
  try {
    await api.employees.updateMe(data);
    toast.success('Profile updated successfully');
    isEditMode = false;
    await loadProfile();
  } catch (error) {
    toast.error(error.message || 'Failed to update profile');
  }
}

function formatDate(dateStr) {
  if (!dateStr) return '--';
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
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
