/**
 * Header Component
 */

const header = {
  element: null,

  /**
   * Render header
   */
  render(containerId, pageTitle) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const userName = auth.getDisplayName();
    const userInitials = auth.getInitials();
    const userRole = auth.user?.role_name || 'User';

    container.innerHTML = `
      <header class="header">
        <div class="header-left">
          <button class="menu-toggle" id="menu-toggle" aria-label="Toggle menu">
            ${sidebar.icons.menu}
          </button>
          <h1 class="page-title">${this.escapeHtml(pageTitle)}</h1>
        </div>
        <div class="header-right">
          <div class="user-info">
            <div class="user-details">
              <span class="user-name">${this.escapeHtml(userName)}</span>
              <span class="user-role">${this.escapeHtml(userRole)}</span>
            </div>
            <div class="user-avatar">${userInitials}</div>
          </div>
          <div class="dropdown" id="user-dropdown">
            <button class="btn btn-ghost btn-icon dropdown-toggle" aria-label="User menu">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 12 15 18 9"></polyline>
              </svg>
            </button>
            <div class="dropdown-menu">
              <a href="profile.html" class="dropdown-item">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                  <circle cx="12" cy="7" r="4"></circle>
                </svg>
                My Profile
              </a>
              <div class="dropdown-divider"></div>
              <button class="dropdown-item" id="logout-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                  <polyline points="16 17 21 12 16 7"></polyline>
                  <line x1="21" x2="9" y1="12" y2="12"></line>
                </svg>
                Logout
              </button>
            </div>
          </div>
        </div>
      </header>
    `;

    this.element = container.querySelector('.header');
    this.initEventListeners();
  },

  /**
   * Initialize event listeners
   */
  initEventListeners() {
    // Menu toggle (mobile)
    const menuToggle = document.getElementById('menu-toggle');
    menuToggle?.addEventListener('click', () => sidebar.toggle());

    // User dropdown
    const dropdown = document.getElementById('user-dropdown');
    const dropdownToggle = dropdown?.querySelector('.dropdown-toggle');
    
    dropdownToggle?.addEventListener('click', (e) => {
      e.stopPropagation();
      dropdown.classList.toggle('open');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', () => {
      dropdown?.classList.remove('open');
    });

    // Logout button
    const logoutBtn = document.getElementById('logout-btn');
    logoutBtn?.addEventListener('click', () => {
      auth.logout();
    });
  },

  /**
   * Update page title
   */
  setTitle(title) {
    const titleEl = this.element?.querySelector('.page-title');
    if (titleEl) {
      titleEl.textContent = title;
    }
    document.title = `${title} - HRMS`;
  },

  /**
   * Escape HTML
   */
  escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
};

// Add styles for user details
const headerStyle = document.createElement('style');
headerStyle.textContent = `
  .user-details {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    margin-right: var(--spacing-sm);
  }
  .user-name {
    font-weight: var(--font-weight-medium);
    color: var(--color-text-heading);
    font-size: var(--font-size-sm);
  }
  .user-role {
    font-size: var(--font-size-xs);
    color: var(--color-text-muted);
  }
  @media (max-width: 767px) {
    .user-details {
      display: none;
    }
  }
`;
document.head.appendChild(headerStyle);

// Export for use
window.header = header;
