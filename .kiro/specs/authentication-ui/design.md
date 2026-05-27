# Design Document: Authentication UI

## Overview

The Authentication UI provides a modern, responsive login interface for the Multi-Company HRMS system. The design emphasizes corporate professionalism, security, and accessibility while integrating seamlessly with the existing PHP backend API. The interface uses a clean, minimal approach with carefully chosen colors and typography to convey trust and reliability.

The system implements a single unified login page that automatically detects user roles (Admin, HR, Employee) after authentication, eliminating the need for role selection dropdowns. The design adapts fluidly between desktop and mobile layouts while maintaining consistent branding and user experience.

## Architecture

### Component Structure

The authentication interface follows a modular component architecture:

```
Authentication UI
├── Login Container
│   ├── Logo Component
│   ├── Login Form
│   │   ├── Email Input Field
│   │   ├── Password Input Field
│   │   ├── Validation Messages
│   │   └── Submit Button
│   └── Helper Links (Future: Forgot Password)
├── Loading States
├── Error Handling
└── Responsive Layout Manager
```

### Technology Stack

- **Frontend Framework**: Vanilla HTML5, CSS3, and JavaScript (framework-agnostic design)
- **CSS Methodology**: BEM (Block Element Modifier) for maintainable styling
- **Responsive Design**: CSS Grid and Flexbox with mobile-first approach
- **Form Validation**: Native HTML5 validation enhanced with custom JavaScript
- **HTTP Client**: Fetch API for backend communication
- **Security**: Content Security Policy compliant, HTTPS enforcement

### Integration Points

- **Backend API**: Integrates with existing PHP authentication endpoints
- **Session Management**: Handles JWT tokens from backend API
- **Role Detection**: Processes role information from authentication response
- **Company Branding**: Dynamic logo loading based on company context

## Components and Interfaces

### Login Container Component

**Purpose**: Main wrapper component that centers the login form and manages responsive layout

**Properties**:
- `maxWidth`: 400px on desktop, 100% on mobile
- `padding`: 2rem on desktop, 1rem on mobile
- `background`: White (#FFFFFF) with subtle shadow
- `borderRadius`: 12px for modern appearance

**Responsive Behavior**:
```css
.login-container {
  max-width: 400px;
  margin: 0 auto;
  padding: 2rem;
  background: #FFFFFF;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

@media (max-width: 768px) {
  .login-container {
    max-width: 100%;
    margin: 0;
    padding: 1rem;
    border-radius: 0;
  }
}
```

### Logo Component

**Purpose**: Displays company logo with proper sizing and fallback handling

**Specifications**:
- **Default Size**: 120px width, auto height
- **Mobile Size**: 100px width, auto height
- **Fallback**: Generic HRMS text logo if image fails to load
- **Loading**: Skeleton placeholder during logo fetch

**Implementation**:
```html
<div class="logo-container">
  <img src="" alt="Company Logo" class="company-logo" id="companyLogo">
  <div class="logo-fallback">HRMS</div>
</div>
```

### Input Field Components

**Email Input Field**:
- **Type**: `email` with HTML5 validation
- **Autocomplete**: `username` for password manager compatibility
- **Validation**: Real-time email format validation
- **Accessibility**: Proper ARIA labels and error associations

**Password Input Field**:
- **Type**: `password` with show/hide toggle
- **Autocomplete**: `current-password`
- **Security**: No password strength requirements (handled by backend policy)
- **Accessibility**: Screen reader compatible with proper labeling

**Shared Input Styling**:
```css
.input-field {
  width: 100%;
  padding: 12px 16px;
  border: 2px solid #E1E6EF;
  border-radius: 8px;
  font-size: 16px;
  transition: all 0.2s ease;
}

.input-field:focus {
  outline: none;
  border-color: #2FB7B2;
  box-shadow: 0 0 0 3px rgba(47, 183, 178, 0.1);
}

.input-field.error {
  border-color: #EF4444;
}
```

### Submit Button Component

**Purpose**: Primary action button for form submission with loading states

**Specifications**:
- **Background**: Deep Blue (#1F3A5F)
- **Hover State**: Slightly darker blue with smooth transition
- **Loading State**: Disabled with spinner animation
- **Accessibility**: Proper focus indicators and keyboard navigation

**States**:
```css
.submit-button {
  width: 100%;
  padding: 12px 24px;
  background: #1F3A5F;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.submit-button:hover {
  background: #1a2f4f;
  transform: translateY(-1px);
}

.submit-button:disabled {
  background: #6B7280;
  cursor: not-allowed;
  transform: none;
}
```

### Validation Message Component

**Purpose**: Displays inline error and success messages with proper styling

**Error Message Styling**:
```css
.validation-message {
  font-size: 14px;
  margin-top: 4px;
  display: flex;
  align-items: center;
  gap: 6px;
}

.validation-message.error {
  color: #EF4444;
}

.validation-message.success {
  color: #10B981;
}
```

## Data Models

### Authentication Request Model

```javascript
interface LoginRequest {
  email: string;           // User email address
  password: string;        // User password
  remember?: boolean;      // Optional remember me flag
}
```

### Authentication Response Model

```javascript
interface LoginResponse {
  success: boolean;
  data?: {
    token: string;         // JWT authentication token
    user: {
      id: number;
      email: string;
      role: 'Admin' | 'HR' | 'Employee';
      company_id: number;
      company_name: string;
      company_logo?: string;
    };
    expires_at: string;    // Token expiration timestamp
  };
  error?: {
    code: string;
    message: string;
    field?: string;        // Specific field that caused error
  };
}
```

### Form State Model

```javascript
interface FormState {
  email: {
    value: string;
    isValid: boolean;
    error?: string;
  };
  password: {
    value: string;
    isValid: boolean;
    error?: string;
  };
  isSubmitting: boolean;
  showPassword: boolean;
  generalError?: string;
}
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Responsive Layout Integrity
*For any* viewport size between 320px and 1920px width, the login interface should maintain proper centering on desktop, full-width layout on mobile, and functional interactive elements across all screen sizes
**Validates: Requirements 1.1, 1.2, 5.1, 5.2, 5.3, 5.4**

### Property 2: Color Scheme Consistency
*For any* UI element in the authentication interface, the applied colors should match the defined color scheme (Deep Blue #1F3A5F for primary elements, Teal #2FB7B2 for accents, specified grays for backgrounds and text)
**Validates: Requirements 2.2, 2.3, 2.4, 2.5**

### Property 3: Form Validation Consistency
*For any* form input (email format, required fields, validation states), the validation behavior should provide immediate feedback, use consistent error styling (#EF4444), and clear errors when corrected
**Validates: Requirements 4.1, 4.3, 4.4, 4.5**

### Property 4: Interactive State Synchronization
*For any* user interaction (focus, hover, input), the visual feedback should be immediate, use correct colors (teal focus glow, smooth transitions), and maintain consistent styling across all interactive elements
**Validates: Requirements 3.1, 3.2, 3.3, 3.4**

### Property 5: Authentication Flow Management
*For any* authentication attempt, the UI should properly manage loading states, handle API responses correctly, process role detection automatically, and maintain form state during error conditions
**Validates: Requirements 6.1, 6.3, 6.5, 8.2, 8.3**

### Property 6: Accessibility Compliance
*For any* accessibility requirement, the interface should provide proper keyboard navigation with logical tab order, include appropriate ARIA labels, meet WCAG 2.1 AA color contrast standards, and announce errors to assistive technologies
**Validates: Requirements 9.1, 9.2, 9.3, 9.4, 9.5**

### Property 7: Security Token Handling
*For any* authentication session, credentials should never be stored in browser storage, session tokens should be handled securely, and sensitive data should not appear in logs or network requests
**Validates: Requirements 7.1, 7.3, 7.4**

### Property 8: Form Structure Consistency
*For any* page render, the login form should always contain properly labeled email and password fields, a prominent submit button, company logo display, and maintain 8px border radius on interactive elements
**Validates: Requirements 1.3, 1.4, 1.5, 2.1**

## Error Handling

### Client-Side Error Handling

**Form Validation Errors**:
- **Empty Fields**: Display "This field is required" message
- **Invalid Email**: Display "Please enter a valid email address"
- **Network Errors**: Display "Connection error. Please check your internet connection."

**Error Display Strategy**:
- Inline validation messages appear below each field
- General errors appear at the top of the form
- Error messages are announced to screen readers
- Errors are cleared when user corrects the input

### Server-Side Error Handling

**Authentication Errors**:
- **Invalid Credentials**: "Invalid email or password. Please try again."
- **Account Locked**: "Account temporarily locked. Please contact support."
- **Server Error**: "Login temporarily unavailable. Please try again later."

**Error Response Processing**:
```javascript
function handleAuthError(error) {
  const errorMap = {
    'INVALID_CREDENTIALS': 'Invalid email or password. Please try again.',
    'ACCOUNT_LOCKED': 'Account temporarily locked. Please contact support.',
    'SERVER_ERROR': 'Login temporarily unavailable. Please try again later.',
    'NETWORK_ERROR': 'Connection error. Please check your internet connection.'
  };
  
  return errorMap[error.code] || 'An unexpected error occurred. Please try again.';
}
```

### Loading State Management

**Form Submission States**:
1. **Idle**: Form ready for input
2. **Validating**: Client-side validation in progress
3. **Submitting**: Request sent to server
4. **Success**: Authentication successful, redirecting
5. **Error**: Display error message, return to idle

## Testing Strategy

### Unit Testing Approach

**Component Testing**:
- Test individual form components in isolation
- Verify proper event handling and state management
- Test responsive behavior at different breakpoints
- Validate accessibility features and keyboard navigation

**Validation Testing**:
- Test email format validation with various input formats
- Test form submission with valid and invalid data
- Test error message display and clearing behavior
- Test loading state transitions

**Example Unit Tests**:
```javascript
// Test email validation
test('should validate email format correctly', () => {
  expect(validateEmail('user@company.com')).toBe(true);
  expect(validateEmail('invalid-email')).toBe(false);
  expect(validateEmail('')).toBe(false);
});

// Test responsive layout
test('should adapt layout for mobile screens', () => {
  setViewportWidth(375);
  expect(getComputedStyle(loginContainer).maxWidth).toBe('100%');
});
```

### Property-Based Testing Configuration

**Testing Framework**: Use Jest with fast-check for property-based testing
**Test Configuration**: Minimum 100 iterations per property test
**Test Environment**: JSDOM for DOM manipulation testing

**Property Test Examples**:
```javascript
// Property 1: Responsive Layout Integrity
test('Responsive layout integrity', () => {
  fc.assert(fc.property(
    fc.integer(320, 1920),
    (width) => {
      setViewportWidth(width);
      const container = document.querySelector('.login-container');
      const isVisible = container.offsetWidth > 0 && container.offsetHeight > 0;
      const isCentered = width >= 768 ? container.offsetLeft > 0 : container.offsetLeft === 0;
      const hasInteractiveElements = container.querySelector('input[type="email"]').offsetWidth > 0;
      return isVisible && isCentered && hasInteractiveElements;
    }
  ), { numRuns: 100 });
});

// Property 2: Color Scheme Consistency
test('Color scheme consistency', () => {
  fc.assert(fc.property(
    fc.constantFrom('primary', 'accent', 'background', 'text'),
    (elementType) => {
      const elements = getElementsByType(elementType);
      const expectedColors = getExpectedColors(elementType);
      return elements.every(el => expectedColors.includes(getComputedColor(el)));
    }
  ), { numRuns: 100 });
});

// Property 3: Form Validation Consistency  
test('Form validation consistency', () => {
  fc.assert(fc.property(
    fc.oneof(fc.emailAddress(), fc.string()),
    (input) => {
      const emailField = document.querySelector('input[type="email"]');
      emailField.value = input;
      emailField.dispatchEvent(new Event('blur'));
      
      const isValidEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input);
      const hasErrorMessage = document.querySelector('.validation-message.error') !== null;
      const errorColor = hasErrorMessage ? getComputedStyle(document.querySelector('.validation-message.error')).color : null;
      
      return isValidEmail ? !hasErrorMessage : (hasErrorMessage && errorColor === 'rgb(239, 68, 68)');
    }
  ), { numRuns: 100 });
});

// Property 5: Authentication Flow Management
test('Authentication flow management', () => {
  fc.assert(fc.property(
    fc.record({
      email: fc.emailAddress(),
      password: fc.string(8, 20),
      shouldSucceed: fc.boolean()
    }),
    async (credentials) => {
      mockApiResponse(credentials.shouldSucceed);
      
      const form = document.querySelector('form');
      const submitButton = document.querySelector('.submit-button');
      
      fillForm(credentials.email, credentials.password);
      form.dispatchEvent(new Event('submit'));
      
      // Check loading state
      const isDisabledDuringSubmit = submitButton.disabled;
      
      await waitForApiResponse();
      
      // Check final state
      const finalState = credentials.shouldSucceed ? 
        window.location.href.includes('dashboard') : 
        document.querySelector('.validation-message.error') !== null;
        
      return isDisabledDuringSubmit && finalState;
    }
  ), { numRuns: 100 });
});
```

**Integration Testing**:
- Test complete authentication flow with mock backend
- Test error handling with various server responses
- Test session management and token storage
- Test cross-browser compatibility

**Performance Testing**:
- Measure initial page load time (target: < 2 seconds)
- Test form responsiveness during submission
- Validate smooth animations and transitions
- Test with slow network conditions