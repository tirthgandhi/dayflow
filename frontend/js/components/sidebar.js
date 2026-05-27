/**
 * Sidebar Component
 */

const sidebar = {
  element: null,
  overlay: null,

  /**
   * Navigation items configuration with required permissions
   */
  navItems: [
    { id: 'dashboard', label: 'Dashboard', href: 'index.html', icon: 'home', permission: null },
    { id: 'employees', label: 'Employees', href: 'employees.html', icon: 'users', permission: 'employee.view' },
    { id: 'attendance', label: 'Attendance', href: 'attendance.html', icon: 'clock', permission: 'attendance.view' },
    { id: 'leave', label: 'Leave Management', href: 'leave.html', icon: 'calendar', permission: 'leave.view' },
    { id: 'payroll', label: 'Payroll', href: 'payroll.html', icon: 'rupee', permission: 'payroll.view' },
    { id: 'reports', label: 'Reports', href: 'reports.html', icon: 'bar-chart-2', permission: 'employee.view' }
  ],

  /**
   * Employee-specific navigation (self-service)
   */
  employeeNavItems: [
    { id: 'dashboard', label: 'Dashboard', href: 'index.html', icon: 'home', permission: null },
    { id: 'my-attendance', label: 'My Attendance', href: 'attendance.html', icon: 'clock', permission: 'attendance.view_own' },
    { id: 'my-leave', label: 'My Leave', href: 'leave.html', icon: 'calendar', permission: 'leave.view_own' },
    { id: 'my-payroll', label: 'My Payslips', href: 'payroll.html', icon: 'rupee', permission: 'payroll.view_own' }
  ],

  /**
   * Lucide icons SVG
   */
  icons: {
    home: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>`,
    users: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>`,
    clock: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>`,
    calendar: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect><line x1="16" x2="16" y1="2" y2="6"></line><line x1="8" x2="8" y1="2" y2="6"></line><line x1="3" x2="21" y1="10" y2="10"></line></svg>`,
    'rupee': `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><text x="12" y="17" text-anchor="middle" font-size="16" font-weight="bold" fill="currentColor" stroke="none">₹</text></svg>`,
    'bar-chart-2': `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" x2="18" y1="20" y2="10"></line><line x1="12" x2="12" y1="20" y2="4"></line><line x1="6" x2="6" y1="20" y2="14"></line></svg>`,
    menu: `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"></line><line x1="4" x2="20" y1="6" y2="6"></line><line x1="4" x2="20" y1="18" y2="18"></line></svg>`
  },

  /**
   * Get navigation items based on user role
   */
  getNavItems() {
    const role = auth.user?.role_name;
    
    // Admin and HR see full menu
    if (role === 'Admin' || role === 'HR') {
      return this.navItems;
    }
    
    // Employees see self-service menu
    return this.employeeNavItems.filter(item => 
      !item.permission || auth.hasPermission(item.permission)
    );
  },

  /**
   * Render sidebar
   */
  render(containerId, activePage) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const items = this.getNavItems();
    const navHtml = items.map(item => `
      <a href="${item.href}" class="nav-item ${item.id === activePage ? 'active' : ''}" data-page="${item.id}">
        <span class="nav-icon">${this.icons[item.icon]}</span>
        <span class="nav-label">${item.label}</span>
      </a>
    `).join('');

    const roleLabel = auth.user?.role_name || 'User';

    container.innerHTML = `
      <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo">
          <svg width="32" height="32" viewBox="0 0 32 32" fill="currentColor">
            <rect width="32" height="32" rx="8" fill="#2FB7B2"/>
            <text x="16" y="22" text-anchor="middle" fill="white" font-size="16" font-weight="bold">HR</text>
          </svg>
          <span class="logo-text">HRMS</span>
        </div>
        <div class="sidebar-role">
          <span class="role-badge">${roleLabel}</span>
        </div>
        <nav class="sidebar-nav">
          ${navHtml}
        </nav>
      </aside>
      <div class="sidebar-overlay" id="sidebar-overlay"></div>
    `;

    this.element = document.getElementById('sidebar');
    this.overlay = document.getElementById('sidebar-overlay');

    // Overlay click closes sidebar on mobile
    this.overlay?.addEventListener('click', () => this.close());
  },

  /**
   * Toggle sidebar (mobile)
   */
  toggle() {
    this.element?.classList.toggle('open');
  },

  /**
   * Open sidebar (mobile)
   */
  open() {
    this.element?.classList.add('open');
  },

  /**
   * Close sidebar (mobile)
   */
  close() {
    this.element?.classList.remove('open');
  },

  /**
   * Collapse sidebar (tablet)
   */
  collapse() {
    this.element?.classList.add('collapsed');
  },

  /**
   * Expand sidebar
   */
  expand() {
    this.element?.classList.remove('collapsed');
  }
};

// Export for use
window.sidebar = sidebar;
