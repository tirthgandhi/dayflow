/**
 * Authentication Module
 * Handles login, logout, and session management
 */

const auth = {
  user: null,
  permissions: [],

  /**
   * Initialize auth state
   */
  async init() {
    try {
      const response = await api.auth.me();
      this.user = response.data.user;
      this.permissions = response.data.permissions || [];
      return true;
    } catch (error) {
      this.user = null;
      this.permissions = [];
      return false;
    }
  },

  /**
   * Login user
   */
  async login(email, password) {
    const response = await api.auth.login(email, password);
    this.user = response.data.user;
    this.permissions = response.data.permissions || [];
    return response;
  },

  /**
   * Logout user
   */
  async logout() {
    try {
      await api.auth.logout();
    } catch (error) {
      // Ignore logout errors
    }
    this.user = null;
    this.permissions = [];
    window.location.href = 'login.html';
  },

  /**
   * Check if user is authenticated
   */
  isAuthenticated() {
    return this.user !== null;
  },

  /**
   * Check if user has permission
   */
  hasPermission(permission) {
    if (this.user?.role_name === 'Admin') return true;
    return this.permissions.includes(permission);
  },

  /**
   * Check if user has any of the permissions
   */
  hasAnyPermission(permissions) {
    if (this.user?.role_name === 'Admin') return true;
    return permissions.some(p => this.permissions.includes(p));
  },

  /**
   * Get user initials for avatar
   */
  getInitials() {
    if (!this.user) return '?';
    const first = this.user.first_name?.[0] || this.user.email[0];
    const last = this.user.last_name?.[0] || '';
    return (first + last).toUpperCase();
  },

  /**
   * Get user display name
   */
  getDisplayName() {
    if (!this.user) return 'Guest';
    if (this.user.first_name && this.user.last_name) {
      return `${this.user.first_name} ${this.user.last_name}`;
    }
    return this.user.email;
  },

  /**
   * Require authentication - redirect to login if not authenticated
   */
  async requireAuth() {
    const isAuth = await this.init();
    if (!isAuth) {
      window.location.href = 'login.html';
      return false;
    }
    return true;
  }
};

// Export for use
window.auth = auth;
