/**
 * API Service Layer
 * Handles all HTTP requests to the backend
 */

/**
 * API Base URL Configuration
 * Your project: Dayflow---Human-Resource-Management-System on port 8081
 */
const API_BASE = '/Dayflow---Human-Resource-Management-System/public/api';

class ApiError extends Error {
  constructor(code, message, details = null) {
    super(message);
    this.code = code;
    this.details = details;
  }
}

const api = {
  /**
   * Make an API request
   */
  async request(endpoint, options = {}) {
    const url = `${API_BASE}${endpoint}`;
    
    const config = {
      headers: {
        'Content-Type': 'application/json',
        ...options.headers
      },
      credentials: 'include',
      ...options
    };

    try {
      const response = await fetch(url, config);
      const data = await response.json();

      if (!data.success) {
        throw new ApiError(
          data.error?.code || 'UNKNOWN_ERROR',
          data.error?.message || 'An error occurred',
          data.error?.details
        );
      }

      return data;
    } catch (error) {
      if (error instanceof ApiError) {
        throw error;
      }
      throw new ApiError('NETWORK_ERROR', 'Network error. Please check your connection.');
    }
  },

  /**
   * GET request
   */
  get(endpoint, params = {}) {
    const queryString = new URLSearchParams(params).toString();
    const url = queryString ? `${endpoint}?${queryString}` : endpoint;
    return this.request(url, { method: 'GET' });
  },

  /**
   * POST request
   */
  post(endpoint, data = {}) {
    return this.request(endpoint, {
      method: 'POST',
      body: JSON.stringify(data)
    });
  },

  /**
   * PUT request
   */
  put(endpoint, data = {}) {
    return this.request(endpoint, {
      method: 'PUT',
      body: JSON.stringify(data)
    });
  },

  /**
   * DELETE request
   */
  delete(endpoint) {
    return this.request(endpoint, { method: 'DELETE' });
  },

  // ============================================
  // AUTH ENDPOINTS
  // ============================================
  auth: {
    login: (email, password) => api.post('/auth/login', { email, password }),
    logout: () => api.post('/auth/logout'),
    me: () => api.get('/auth/me'),
    register: (data) => api.post('/auth/register', data)
  },

  // ============================================
  // DASHBOARD ENDPOINTS
  // ============================================
  dashboard: {
    getStats: () => api.get('/dashboard/stats')
  },

  // ============================================
  // EMPLOYEE ENDPOINTS
  // ============================================
  employees: {
    list: (params = {}) => api.get('/employees', params),
    get: (id) => api.get(`/employees/${id}`),
    create: (data) => api.post('/employees', data),
    update: (id, data) => api.put(`/employees/${id}`, data),
    delete: (id) => api.delete(`/employees/${id}`),
    me: () => api.get('/employees/me'),
    updateMe: (data) => api.put('/employees/me', data)
  },

  // ============================================
  // ATTENDANCE ENDPOINTS
  // ============================================
  attendance: {
    list: (params = {}) => api.get('/attendance', params),
    me: (params = {}) => api.get('/attendance/me', params),
    clockIn: () => api.post('/attendance/clock-in'),
    clockOut: () => api.post('/attendance/clock-out'),
    create: (data) => api.post('/attendance', data),
    update: (id, data) => api.put(`/attendance/${id}`, data)
  },

  // ============================================
  // LEAVE ENDPOINTS
  // ============================================
  leave: {
    types: () => api.get('/leave/types'),
    requests: (params = {}) => api.get('/leave/requests', params),
    myRequests: (params = {}) => api.get('/leave/requests/me', params),
    balance: () => api.get('/leave/balance'),
    create: (data) => api.post('/leave/requests', data),
    approve: (id) => api.put(`/leave/requests/${id}/approve`),
    reject: (id, reason) => api.put(`/leave/requests/${id}/reject`, { reason })
  },

  // ============================================
  // PAYROLL ENDPOINTS
  // ============================================
  payroll: {
    list: (params = {}) => api.get('/payroll', params),
    get: (id) => api.get(`/payroll/${id}`),
    me: (params = {}) => api.get('/payroll/me', params),
    process: (month) => api.post('/payroll/process', { month })
  }
};

// Export for use
window.api = api;
window.ApiError = ApiError;
