/**
 * Company Profile Page Logic
 * Admin-only page for managing company settings
 */

let companyData = null;

(async function() {
  const isAuth = await auth.requireAuth();
  if (!isAuth) return;

  // Check if user is admin
  const role = auth.user?.role_name;
  if (role !== 'Admin') {
    toast.error('Access denied. Admin only.');
    window.location.href = 'index.html';
    return;
  }

  sidebar.render('sidebar-container', 'company-settings');
  header.render('header-container', 'Company Settings');

  await loadCompanyProfile();
})();

async function loadCompanyProfile() {
  const mainContent = document.querySelector('.main-content');
  
  try {
    const response = await api.get('/company/profile');
    companyData = response.data;
    
    renderCompanyProfile();
  } catch (error) {
    console.error('Failed to load company profile:', error);
    // Show form with empty data for new setup
    companyData = {
      name: '',
      address: '',
      phone: '',
      email: '',
      logo_url: null
    };
    renderCompanyProfile();
  }
}

function renderCompanyProfile() {
  const mainContent = document.querySelector('.main-content');
  
  mainContent.innerHTML = `
    <div class="content-grid-company">
      <!-- Company Logo Card -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Company Logo</h3>
        </div>
        <div class="card-body text-center">
          <div class="company-logo-preview" id="logo-preview">
            ${companyData?.logo_url 
              ? `<img src="${escapeHtml(companyData.logo_url)}" alt="Company Logo">`
              : `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                  <circle cx="8.5" cy="8.5" r="1.5"></circle>
                  <polyline points="21 15 16 10 5 21"></polyline>
                </svg>`
            }
          </div>
          <p class="text-muted text-sm mb-md">Upload your company logo</p>
          <input type="file" id="logo-input" accept="image/jpeg,image/png,image/gif" style="display: none;">
          <button class="btn btn-outline" onclick="document.getElementById('logo-input').click()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-sm">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
              <polyline points="17 8 12 3 7 8"></polyline>
              <line x1="12" y1="3" x2="12" y2="15"></line>
            </svg>
            Upload Logo
          </button>
          <p class="form-hint mt-sm">JPG, PNG or GIF. Max 2MB.</p>
        </div>
      </div>
      
      <!-- Company Details Card -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Company Information</h3>
        </div>
        <div class="card-body">
          <form id="company-form">
            <div class="form-group">
              <label class="form-label">Company Name *</label>
              <input type="text" class="form-input" id="company_name" value="${escapeHtml(companyData?.name || '')}" required>
            </div>
            
            <div class="form-group">
              <label class="form-label">Company Email</label>
              <input type="email" class="form-input" id="company_email" value="${escapeHtml(companyData?.email || '')}">
            </div>
            
            <div class="form-group">
              <label class="form-label">Company Phone</label>
              <input type="tel" class="form-input" id="company_phone" value="${escapeHtml(companyData?.phone || '')}">
            </div>
            
            <div class="form-group">
              <label class="form-label">Company Address</label>
              <textarea class="form-input" id="company_address" rows="3">${escapeHtml(companyData?.address || '')}</textarea>
            </div>
            
            <div class="form-actions">
              <button type="submit" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-sm">
                  <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                  <polyline points="17 21 17 13 7 13 7 21"></polyline>
                  <polyline points="7 3 7 8 15 8"></polyline>
                </svg>
                Save Changes
              </button>
            </div>
          </form>
        </div>
      </div>
      
      <!-- Company Stats Card -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Company Statistics</h3>
        </div>
        <div class="card-body">
          <div class="stats-grid">
            <div class="stat-item">
              <div class="stat-value" id="total-employees">--</div>
              <div class="stat-label">Total Employees</div>
            </div>
            <div class="stat-item">
              <div class="stat-value" id="active-employees">--</div>
              <div class="stat-label">Active Employees</div>
            </div>
            <div class="stat-item">
              <div class="stat-value" id="total-departments">--</div>
              <div class="stat-label">Departments</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <style>
      .content-grid-company {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: var(--spacing-lg);
      }
      
      .content-grid-company > .card:first-child {
        grid-row: span 2;
      }
      
      @media (max-width: 1024px) {
        .content-grid-company {
          grid-template-columns: 1fr;
        }
        .content-grid-company > .card:first-child {
          grid-row: auto;
        }
      }
      
      .company-logo-preview {
        width: 150px;
        height: 150px;
        border-radius: var(--radius-lg);
        background: var(--color-bg-page);
        border: 2px dashed var(--color-border);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto var(--spacing-md);
        overflow: hidden;
      }
      
      .company-logo-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }
      
      .company-logo-preview svg {
        width: 48px;
        height: 48px;
        color: var(--color-text-muted);
      }
      
      .form-actions {
        margin-top: var(--spacing-lg);
        padding-top: var(--spacing-lg);
        border-top: 1px solid var(--color-border);
      }
      
      .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: var(--spacing-md);
      }
      
      @media (max-width: 640px) {
        .stats-grid {
          grid-template-columns: 1fr;
        }
      }
      
      .stat-item {
        text-align: center;
        padding: var(--spacing-md);
        background: var(--color-bg-page);
        border-radius: var(--radius-md);
      }
      
      .stat-value {
        font-size: var(--font-size-2xl);
        font-weight: var(--font-weight-bold);
        color: var(--color-secondary);
      }
      
      .stat-label {
        font-size: var(--font-size-sm);
        color: var(--color-text-muted);
        margin-top: var(--spacing-xs);
      }
    </style>
  `;
  
  // Add event listeners
  document.getElementById('company-form').addEventListener('submit', handleCompanySubmit);
  document.getElementById('logo-input').addEventListener('change', handleLogoUpload);
  
  // Load stats
  loadCompanyStats();
}

async function handleCompanySubmit(e) {
  e.preventDefault();
  
  const data = {
    name: document.getElementById('company_name').value.trim(),
    email: document.getElementById('company_email').value.trim(),
    phone: document.getElementById('company_phone').value.trim(),
    address: document.getElementById('company_address').value.trim()
  };
  
  try {
    await api.put('/company/profile', data);
    toast.success('Company information updated successfully');
    await loadCompanyProfile();
  } catch (error) {
    toast.error(error.message || 'Failed to update company information');
  }
}

async function handleLogoUpload(e) {
  const file = e.target.files[0];
  if (!file) return;
  
  // Validate file type
  if (!['image/jpeg', 'image/png', 'image/gif'].includes(file.type)) {
    toast.error('Please select a valid image file (JPG, PNG, or GIF)');
    return;
  }
  
  // Validate file size (2MB max)
  if (file.size > 2 * 1024 * 1024) {
    toast.error('File size must be less than 2MB');
    return;
  }
  
  // Show preview immediately
  const reader = new FileReader();
  reader.onload = (e) => {
    document.getElementById('logo-preview').innerHTML = `<img src="${e.target.result}" alt="Logo preview">`;
  };
  reader.readAsDataURL(file);
  
  // Upload file
  try {
    const formData = new FormData();
    formData.append('logo', file);
    
    const response = await fetch(`${API_BASE}/company/logo`, {
      method: 'POST',
      body: formData,
      credentials: 'include'
    });
    
    const data = await response.json();
    
    if (!data.success) {
      throw new Error(data.error?.message || 'Upload failed');
    }
    
    toast.success('Logo uploaded successfully');
    companyData.logo_url = data.data.logo_url;
  } catch (error) {
    toast.error(error.message || 'Failed to upload logo');
    // Revert preview
    if (companyData?.logo_url) {
      document.getElementById('logo-preview').innerHTML = `<img src="${escapeHtml(companyData.logo_url)}" alt="Company Logo">`;
    } else {
      document.getElementById('logo-preview').innerHTML = `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
          <circle cx="8.5" cy="8.5" r="1.5"></circle>
          <polyline points="21 15 16 10 5 21"></polyline>
        </svg>
      `;
    }
  }
}

async function loadCompanyStats() {
  try {
    const response = await api.employees.list({ per_page: 1 });
    const total = response.pagination?.total || 0;
    
    const activeResponse = await api.employees.list({ status: 'active', per_page: 1 });
    const activeCount = activeResponse.pagination?.total || 0;
    
    document.getElementById('total-employees').textContent = total;
    document.getElementById('active-employees').textContent = activeCount;
    document.getElementById('total-departments').textContent = '--'; // Would need a departments endpoint
  } catch (error) {
    console.error('Failed to load company stats:', error);
  }
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text || '';
  return div.innerHTML;
}
