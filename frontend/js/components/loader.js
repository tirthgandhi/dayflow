/**
 * Loader Component
 * Skeleton loading states and spinners
 */

const loader = {
  /**
   * Create skeleton element
   */
  skeleton(type = 'text', width = '100%') {
    const el = document.createElement('div');
    el.className = `skeleton skeleton-${type}`;
    if (width !== '100%') {
      el.style.width = width;
    }
    return el;
  },

  /**
   * Create spinner element
   */
  spinner(size = 'md') {
    const sizes = { sm: '16px', md: '24px', lg: '32px' };
    const el = document.createElement('div');
    el.className = 'spinner';
    el.style.width = sizes[size] || sizes.md;
    el.style.height = sizes[size] || sizes.md;
    return el;
  },

  /**
   * Create loading overlay
   */
  overlay(container) {
    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.appendChild(this.spinner('lg'));
    
    if (container) {
      container.style.position = 'relative';
      container.appendChild(overlay);
    }
    
    return overlay;
  },

  /**
   * Remove loading overlay
   */
  removeOverlay(container) {
    const overlay = container?.querySelector('.loading-overlay');
    overlay?.remove();
  },

  /**
   * Render KPI card skeleton
   */
  kpiCardSkeleton() {
    return `
      <div class="kpi-card">
        <div class="kpi-header">
          <div class="skeleton skeleton-text" style="width: 100px;"></div>
          <div class="skeleton" style="width: 40px; height: 40px; border-radius: var(--radius-md);"></div>
        </div>
        <div class="skeleton skeleton-value" style="width: 80px; height: 36px; margin-bottom: var(--spacing-sm);"></div>
        <div class="kpi-breakdown">
          <div class="skeleton skeleton-text" style="width: 80px;"></div>
          <div class="skeleton skeleton-text" style="width: 80px;"></div>
        </div>
      </div>
    `;
  },

  /**
   * Render table skeleton
   */
  tableSkeleton(rows = 5, cols = 5) {
    const headerCells = Array(cols).fill('<th><div class="skeleton skeleton-text" style="width: 80%;"></div></th>').join('');
    const bodyRows = Array(rows).fill(`
      <tr>
        ${Array(cols).fill('<td><div class="skeleton skeleton-text" style="width: 90%;"></div></td>').join('')}
      </tr>
    `).join('');

    return `
      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>${headerCells}</tr>
          </thead>
          <tbody>
            ${bodyRows}
          </tbody>
        </table>
      </div>
    `;
  },

  /**
   * Render card skeleton
   */
  cardSkeleton() {
    return `
      <div class="card">
        <div class="card-header">
          <div class="skeleton skeleton-title" style="width: 150px;"></div>
        </div>
        <div class="card-body">
          <div class="skeleton skeleton-text"></div>
          <div class="skeleton skeleton-text"></div>
          <div class="skeleton skeleton-text" style="width: 70%;"></div>
        </div>
      </div>
    `;
  },

  /**
   * Show loading state in element
   */
  showIn(element, type = 'spinner') {
    if (!element) return;
    
    element.dataset.originalContent = element.innerHTML;
    
    if (type === 'spinner') {
      element.innerHTML = '<div class="flex items-center justify-center p-lg"><div class="spinner"></div></div>';
    } else if (type === 'skeleton-table') {
      element.innerHTML = this.tableSkeleton();
    } else if (type === 'skeleton-cards') {
      element.innerHTML = Array(4).fill(this.kpiCardSkeleton()).join('');
    }
  },

  /**
   * Restore original content
   */
  restore(element) {
    if (!element || !element.dataset.originalContent) return;
    element.innerHTML = element.dataset.originalContent;
    delete element.dataset.originalContent;
  }
};

// Export for use
window.loader = loader;
