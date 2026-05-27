/**
 * Signup Page Logic
 * Handles company registration
 */

(async function() {
  // Check if already logged in (silently - don't show error)
  try {
    const isAuth = await auth.init();
    if (isAuth) {
      window.location.href = 'index.html';
      return;
    }
  } catch (e) {
    // Not logged in, continue with signup
  }

  initSignupForm();
  initLogoPreview();
})();

function initSignupForm() {
  const form = document.getElementById('signup-form');
  const signupBtn = document.getElementById('signup-btn');
  const signupError = document.getElementById('signup-error');
  const signupSuccess = document.getElementById('signup-success');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    // Clear errors
    signupError.classList.remove('show');
    signupSuccess.classList.remove('show');
    clearFieldErrors();

    // Get form data
    const companyName = document.getElementById('company_name').value.trim();
    const adminName = document.getElementById('admin_name').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    // Validate
    if (!validateForm(companyName, adminName, email, password, confirmPassword)) {
      return;
    }

    // Show loading
    signupBtn.disabled = true;
    signupBtn.querySelector('.btn-text').classList.add('hidden');
    signupBtn.querySelector('.btn-loading').classList.remove('hidden');

    try {
      // Parse admin name into first and last name
      const nameParts = adminName.trim().split(/\s+/);
      const firstName = nameParts[0] || 'Admin';
      const lastName = nameParts.length > 1 ? nameParts.slice(1).join(' ') : 'User';

      // Generate a registration number
      const registrationNumber = 'REG-' + Date.now().toString(36).toUpperCase();

      // Submit registration using JSON
      await api.auth.register({
        company_name: companyName,
        registration_number: registrationNumber,
        industry: '',
        company_size: '1-10',
        first_name: firstName,
        last_name: lastName,
        email: email,
        password: password,
        confirm_password: confirmPassword
      });

      // Show success message
      signupSuccess.textContent = 'Registration successful! Redirecting to dashboard...';
      signupSuccess.classList.add('show');
      form.reset();
      document.getElementById('logo-preview').innerHTML = `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
          <circle cx="8.5" cy="8.5" r="1.5"></circle>
          <polyline points="21 15 16 10 5 21"></polyline>
        </svg>
      `;

      // Auto-login was done by the API, redirect to dashboard
      setTimeout(() => {
        window.location.href = 'index.html';
      }, 1500);

    } catch (error) {
      signupError.textContent = error.message || 'Registration failed. Please try again.';
      signupError.classList.add('show');
      
      // Reset button
      signupBtn.disabled = false;
      signupBtn.querySelector('.btn-text').classList.remove('hidden');
      signupBtn.querySelector('.btn-loading').classList.add('hidden');
    }
  });
}

function validateForm(companyName, adminName, email, password, confirmPassword) {
  let isValid = true;

  if (!companyName) {
    showFieldError('company_name', 'Company name is required');
    isValid = false;
  }

  if (!adminName) {
    showFieldError('admin_name', 'Your name is required');
    isValid = false;
  }

  if (!email) {
    showFieldError('email', 'Email is required');
    isValid = false;
  } else if (!email.includes('@')) {
    showFieldError('email', 'Invalid email format');
    isValid = false;
  }

  if (!password) {
    showFieldError('password', 'Password is required');
    isValid = false;
  } else if (password.length < 6) {
    showFieldError('password', 'Password must be at least 6 characters');
    isValid = false;
  }

  if (!confirmPassword) {
    showFieldError('confirm_password', 'Please confirm your password');
    isValid = false;
  } else if (password !== confirmPassword) {
    showFieldError('confirm_password', 'Passwords do not match');
    isValid = false;
  }

  return isValid;
}

function showFieldError(fieldId, message) {
  const input = document.getElementById(fieldId);
  const errorEl = document.getElementById(`${fieldId}-error`);
  
  if (input) input.classList.add('error');
  if (errorEl) errorEl.textContent = message;
}

function clearFieldErrors() {
  const fields = ['company_name', 'admin_name', 'email', 'password', 'confirm_password'];
  
  fields.forEach(fieldId => {
    const input = document.getElementById(fieldId);
    const errorEl = document.getElementById(`${fieldId}-error`);
    
    if (input) input.classList.remove('error');
    if (errorEl) errorEl.textContent = '';
  });
}

function initLogoPreview() {
  const logoInput = document.getElementById('company_logo');
  const logoPreview = document.getElementById('logo-preview');

  logoInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    
    if (file) {
      // Validate file type
      if (!['image/jpeg', 'image/png', 'image/gif'].includes(file.type)) {
        toast.error('Please select a valid image file (JPG, PNG, or GIF)');
        logoInput.value = '';
        return;
      }

      // Validate file size (2MB max)
      if (file.size > 2 * 1024 * 1024) {
        toast.error('File size must be less than 2MB');
        logoInput.value = '';
        return;
      }

      // Show preview
      const reader = new FileReader();
      reader.onload = (e) => {
        logoPreview.innerHTML = `<img src="${e.target.result}" alt="Logo preview">`;
      };
      reader.readAsDataURL(file);
    } else {
      // Reset preview
      logoPreview.innerHTML = `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
          <circle cx="8.5" cy="8.5" r="1.5"></circle>
          <polyline points="21 15 16 10 5 21"></polyline>
        </svg>
      `;
    }
  });
}
