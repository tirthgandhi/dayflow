/**
 * Animation Utilities
 * Dynamic UI effects and liquid transitions
 */

const animations = {
  /**
   * Add ripple effect to element on click
   */
  addRipple(element) {
    element.classList.add('ripple');
    element.addEventListener('click', (e) => {
      const rect = element.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      
      const ripple = document.createElement('span');
      ripple.className = 'ripple-effect';
      ripple.style.left = x + 'px';
      ripple.style.top = y + 'px';
      
      element.appendChild(ripple);
      
      setTimeout(() => ripple.remove(), 600);
    });
  },

  /**
   * Animate number counter
   */
  animateCounter(element, targetValue, duration = 1000) {
    const startValue = parseInt(element.textContent.replace(/[^0-9.-]/g, '')) || 0;
    const startTime = performance.now();
    const prefix = element.textContent.match(/^[^0-9]*/)?.[0] || '';
    const suffix = element.textContent.match(/[^0-9]*$/)?.[0] || '';
    
    const animate = (currentTime) => {
      const elapsed = currentTime - startTime;
      const progress = Math.min(elapsed / duration, 1);
      
      // Ease out expo
      const easeProgress = 1 - Math.pow(1 - progress, 3);
      const currentValue = Math.round(startValue + (targetValue - startValue) * easeProgress);
      
      element.textContent = prefix + currentValue.toLocaleString('en-IN') + suffix;
      
      if (progress < 1) {
        requestAnimationFrame(animate);
      } else {
        element.classList.add('counting');
        setTimeout(() => element.classList.remove('counting'), 300);
      }
    };
    
    requestAnimationFrame(animate);
  },

  /**
   * Animate currency counter
   */
  animateCurrency(element, targetValue, duration = 1000) {
    const startValue = parseFloat(element.textContent.replace(/[^0-9.-]/g, '')) || 0;
    const startTime = performance.now();
    
    const animate = (currentTime) => {
      const elapsed = currentTime - startTime;
      const progress = Math.min(elapsed / duration, 1);
      
      // Ease out expo
      const easeProgress = 1 - Math.pow(1 - progress, 3);
      const currentValue = startValue + (targetValue - startValue) * easeProgress;
      
      element.textContent = new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
      }).format(currentValue);
      
      if (progress < 1) {
        requestAnimationFrame(animate);
      } else {
        element.classList.add('updating');
        setTimeout(() => element.classList.remove('updating'), 500);
      }
    };
    
    requestAnimationFrame(animate);
  },

  /**
   * Stagger animation for list items
   */
  staggerIn(elements, delay = 50) {
    elements.forEach((el, index) => {
      el.style.opacity = '0';
      el.style.transform = 'translateY(20px)';
      
      setTimeout(() => {
        el.style.transition = 'all 0.4s cubic-bezier(0.16, 1, 0.3, 1)';
        el.style.opacity = '1';
        el.style.transform = 'translateY(0)';
      }, index * delay);
    });
  },

  /**
   * Fade in element
   */
  fadeIn(element, duration = 300) {
    element.style.opacity = '0';
    element.style.display = 'block';
    
    requestAnimationFrame(() => {
      element.style.transition = `opacity ${duration}ms ease`;
      element.style.opacity = '1';
    });
  },

  /**
   * Fade out element
   */
  fadeOut(element, duration = 300) {
    element.style.transition = `opacity ${duration}ms ease`;
    element.style.opacity = '0';
    
    setTimeout(() => {
      element.style.display = 'none';
    }, duration);
  },

  /**
   * Slide down element
   */
  slideDown(element, duration = 300) {
    element.style.height = '0';
    element.style.overflow = 'hidden';
    element.style.display = 'block';
    
    const height = element.scrollHeight;
    element.style.transition = `height ${duration}ms cubic-bezier(0.16, 1, 0.3, 1)`;
    
    requestAnimationFrame(() => {
      element.style.height = height + 'px';
    });
    
    setTimeout(() => {
      element.style.height = '';
      element.style.overflow = '';
    }, duration);
  },

  /**
   * Slide up element
   */
  slideUp(element, duration = 300) {
    element.style.height = element.scrollHeight + 'px';
    element.style.overflow = 'hidden';
    element.style.transition = `height ${duration}ms cubic-bezier(0.16, 1, 0.3, 1)`;
    
    requestAnimationFrame(() => {
      element.style.height = '0';
    });
    
    setTimeout(() => {
      element.style.display = 'none';
      element.style.height = '';
      element.style.overflow = '';
    }, duration);
  },

  /**
   * Shake element (for errors)
   */
  shake(element) {
    element.style.animation = 'none';
    element.offsetHeight; // Trigger reflow
    element.style.animation = 'shake 0.5s ease';
  },

  /**
   * Pulse element
   */
  pulse(element) {
    element.classList.add('updating');
    setTimeout(() => element.classList.remove('updating'), 500);
  },

  /**
   * Initialize scroll reveal
   */
  initScrollReveal() {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
        }
      });
    }, { threshold: 0.1 });

    document.querySelectorAll('.scroll-reveal').forEach(el => {
      observer.observe(el);
    });
  },

  /**
   * Add hover glow effect
   */
  addGlow(element) {
    element.classList.add('glow-hover');
  },

  /**
   * Initialize all animations on page
   */
  init() {
    // Add ripple to all buttons
    document.querySelectorAll('.btn').forEach(btn => {
      this.addRipple(btn);
    });

    // Initialize scroll reveal
    this.initScrollReveal();

    // Add CSS for shake animation if not exists
    if (!document.getElementById('shake-keyframes')) {
      const style = document.createElement('style');
      style.id = 'shake-keyframes';
      style.textContent = `
        @keyframes shake {
          0%, 100% { transform: translateX(0); }
          10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
          20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
      `;
      document.head.appendChild(style);
    }
  }
};

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  animations.init();
});

// Export for use
window.animations = animations;
