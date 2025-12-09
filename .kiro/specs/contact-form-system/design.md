# Design Document

## Overview

The Contact Form System is a comprehensive solution that enables website visitors to submit inquiries through validated forms on the M H K Trading & Ship Chandlers LLC website. The system consists of client-side JavaScript for form handling and validation, server-side PHP for processing and email delivery, and security mechanisms to prevent spam and abuse.

The design follows a progressive enhancement approach where basic HTML forms work without JavaScript, but enhanced features (AJAX submission, real-time validation) are available when JavaScript is enabled. The system integrates seamlessly with the existing Bootstrap-based website design.

## Architecture

The system follows a three-tier architecture:

### Presentation Layer (Client-Side)
- HTML forms with Bootstrap styling
- JavaScript for client-side validation and AJAX submission
- Real-time feedback and error display
- Loading states and user interaction management

### Application Layer (Server-Side)
- PHP script for request handling
- Input validation and sanitization
- Business logic for email composition
- Security checks (CSRF, rate limiting, honeypot)

### Integration Layer
- PHP mail() function for email delivery
- Session management for CSRF tokens
- File-based rate limiting (can be upgraded to database)

### Data Flow

```
User Input → Client Validation → AJAX Request → Server Validation → 
Email Sending → Response → User Feedback
```

## Components and Interfaces

### 1. Contact Form Component (HTML/JavaScript)

**Location:** `index.html` and `contact.html`

**Responsibilities:**
- Render form fields with proper attributes
- Capture user input
- Perform client-side validation
- Submit data via AJAX
- Display feedback messages

**Interface:**
```javascript
// Form submission handler
function submitContactForm(event)
  Input: FormEvent
  Output: void
  Side Effects: AJAX request, UI updates

// Validation functions
function validateEmail(email)
  Input: string
  Output: boolean

function validatePhone(phone)
  Input: string
  Output: boolean

function validateRequired(value)
  Input: string
  Output: boolean

// UI feedback
function showSuccess(message)
  Input: string
  Output: void
  Side Effects: Display success modal/message

function showError(message)
  Input: string
  Output: void
  Side Effects: Display error message

function setLoading(isLoading)
  Input: boolean
  Output: void
  Side Effects: Disable/enable form, show/hide spinner
```

### 2. Form Processor (PHP)

**Location:** `process_form.php`

**Responsibilities:**
- Receive and validate POST requests
- Sanitize input data
- Validate CSRF tokens
- Check rate limits
- Send email notifications
- Return JSON responses

**Interface:**
```php
// Main processing function
function processFormSubmission()
  Input: $_POST data
  Output: JSON response
  Side Effects: Email sent, session updated

// Validation functions
function validateFormData($data)
  Input: array
  Output: array (errors or empty)

function sanitizeInput($input)
  Input: string
  Output: string

// Security functions
function validateCSRFToken($token)
  Input: string
  Output: boolean

function checkRateLimit($ip)
  Input: string
  Output: boolean

function detectHoneypot($data)
  Input: array
  Output: boolean

// Email functions
function sendNotificationEmail($data)
  Input: array
  Output: boolean

function formatEmailContent($data)
  Input: array
  Output: string (HTML)
```

### 3. Security Module

**Responsibilities:**
- Generate and validate CSRF tokens
- Implement rate limiting
- Detect and block spam attempts
- Sanitize all inputs

**Interface:**
```php
function generateCSRFToken()
  Output: string

function getRateLimitKey($ip)
  Input: string
  Output: string

function recordSubmission($ip)
  Input: string
  Side Effects: Update rate limit storage

function isSpamSubmission($data)
  Input: array
  Output: boolean
```

## Data Models

### Form Submission Data

```javascript
{
  name: string,          // Required, 2-100 characters
  email: string,         // Required, valid email format
  phone: string,         // Required, 10-15 digits with optional formatting
  subject: string,       // Required, one of predefined categories
  message: string,       // Required, 10-1000 characters
  csrf_token: string,    // Required for security
  honeypot: string       // Should be empty (spam detection)
}
```

### Validation Rules

```javascript
{
  name: {
    required: true,
    minLength: 2,
    maxLength: 100,
    pattern: /^[a-zA-Z\s'-]+$/
  },
  email: {
    required: true,
    pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  },
  phone: {
    required: true,
    pattern: /^[\d\s\-\+\(\)]+$/,
    minLength: 10,
    maxLength: 15
  },
  subject: {
    required: true,
    enum: ['Foodstuff', 'Ship Chandlers', 'General Products']
  },
  message: {
    required: true,
    minLength: 10,
    maxLength: 1000
  }
}
```

### Email Notification Format

```html
<html>
<body>
  <h2>New Contact Form Submission</h2>
  <p><strong>Submitted:</strong> [timestamp]</p>
  <p><strong>Name:</strong> [name]</p>
  <p><strong>Email:</strong> [email]</p>
  <p><strong>Phone:</strong> [phone]</p>
  <p><strong>Category:</strong> [subject]</p>
  <p><strong>Message:</strong><br/>[message]</p>
</body>
</html>
```

### API Response Format

```javascript
// Success response
{
  status: 'success',
  message: 'Thank you for your message. We will contact you shortly!'
}

// Error response
{
  status: 'error',
  message: 'Error description',
  errors: {
    fieldName: 'Field-specific error message'
  }
}
```


## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Valid form data submission

*For any* form submission with valid data in all required fields, the system should successfully send the data to the server for processing.
**Validates: Requirements 1.1**

### Property 2: Success message display

*For any* successful form submission, the system should display a success message to the user.
**Validates: Requirements 1.2**

### Property 3: Form field clearing

*For any* successful form submission, all form fields should be cleared and reset to their initial empty state.
**Validates: Requirements 1.3**

### Property 4: Email notification sending

*For any* successful form submission, the system should send an email notification containing the form data to the configured business email address.
**Validates: Requirements 1.4, 3.1**

### Property 5: Asynchronous submission

*For any* form submission, the page should not reload and the submission should be handled asynchronously via AJAX.
**Validates: Requirements 1.5**

### Property 6: Invalid email detection

*For any* email input that does not match valid email format, the system should display an error message indicating the format is incorrect.
**Validates: Requirements 2.1**

### Property 7: Required field validation

*For any* form submission attempt with one or more empty required fields, the system should prevent submission and highlight all missing fields.
**Validates: Requirements 2.2**

### Property 8: Invalid phone detection

*For any* phone input that does not match valid phone format, the system should display an error message with the expected format.
**Validates: Requirements 2.3**

### Property 9: Error message removal

*For any* form field that transitions from invalid to valid state, the associated error message should be removed.
**Validates: Requirements 2.4**

### Property 10: Length limit enforcement

*For any* text field with a maximum length limit, the system should prevent input beyond that limit and display a character count.
**Validates: Requirements 2.5**

### Property 11: Complete email content

*For any* email notification sent, the email body should include all submitted form fields (name, email, phone, subject, message) in a readable format.
**Validates: Requirements 3.2**

### Property 12: Email timestamp inclusion

*For any* email notification sent, the email should include a valid timestamp indicating when the submission occurred.
**Validates: Requirements 3.3**

### Property 13: Email header correctness

*For any* email notification sent, the email headers should include properly formatted From, Reply-To, and Content-Type headers.
**Validates: Requirements 3.4**

### Property 14: Server-side required field validation

*For any* form data received by the server, the server should validate that all required fields are present and non-empty before processing.
**Validates: Requirements 4.1**

### Property 15: Input sanitization

*For any* form data received by the server, all input fields should be sanitized to remove potentially harmful content before processing or storage.
**Validates: Requirements 4.2**

### Property 16: Server-side email validation

*For any* email field received by the server, the server should validate that it matches a valid email format.
**Validates: Requirements 4.3**

### Property 17: Server-side phone validation

*For any* phone field received by the server, the server should validate that it contains only allowed characters.
**Validates: Requirements 4.4**

### Property 18: Validation error responses

*For any* server-side validation failure, the server should return a detailed JSON response indicating which specific fields are invalid.
**Validates: Requirements 4.5**

### Property 19: POST method verification

*For any* request received by the form processor, the server should verify that the request method is POST before processing.
**Validates: Requirements 5.1**

### Property 20: Rate limiting enforcement

*For any* IP address that submits multiple forms within a short time period, subsequent submissions should be rejected with a rate limit error.
**Validates: Requirements 5.2**

### Property 21: CSRF token validation

*For any* form submission received by the server, the server should validate the CSRF token before processing the request.
**Validates: Requirements 5.3**

### Property 22: Spam pattern detection

*For any* form submission containing suspicious patterns (honeypot filled, unusual content), the system should log the attempt and reject the submission.
**Validates: Requirements 5.4**

### Property 23: Submit button disabling

*For any* form submission initiated by clicking the submit button, the button should be disabled and a loading indicator should be displayed.
**Validates: Requirements 6.1**

### Property 24: Double submission prevention

*For any* form that is currently being submitted, additional submission attempts should be prevented until the current submission completes.
**Validates: Requirements 6.2**

### Property 25: Success feedback display

*For any* successful server response, the system should display a success message with confirmation details to the user.
**Validates: Requirements 6.3**

### Property 26: Error feedback display

*For any* error server response, the system should display a user-friendly error message to the user.
**Validates: Requirements 6.4**

### Property 27: Visual feedback styling

*For any* feedback message displayed (success or error), the system should use appropriate visual styling to distinguish between success and error states.
**Validates: Requirements 6.5**

### Property 28: Form consistency across pages

*For any* validation rule or submission behavior, both the homepage form and contact page form should behave identically.
**Validates: Requirements 7.1, 7.2, 7.3, 7.4, 7.5**

### Property 29: Category inclusion in email

*For any* form submission with a selected category, the email notification should include the selected category value.
**Validates: Requirements 8.1**

### Property 30: Category validation

*For any* form submission, the server should validate that the category field contains one of the valid predefined category options.
**Validates: Requirements 8.2**

### Property 31: Category display format

*For any* email notification sent, the category should be displayed using its human-readable name rather than an internal ID or code.
**Validates: Requirements 8.3**

### Property 32: Missing category validation

*For any* form submission without a category selection, the system should treat this as a validation error and prevent submission.
**Validates: Requirements 8.4**

### Property 33: Category in email subject

*For any* email notification sent, the email subject line should include the selected category in a prominent format.
**Validates: Requirements 8.5**

## Error Handling

### Client-Side Error Handling

1. **Network Errors**: If the AJAX request fails due to network issues, display a user-friendly message asking the user to check their connection and try again.

2. **Timeout Errors**: If the server doesn't respond within 30 seconds, display a timeout message and allow the user to retry.

3. **Validation Errors**: Display inline error messages next to the relevant form fields with clear instructions on how to fix the issue.

4. **Unexpected Errors**: Catch all JavaScript errors and display a generic error message while logging details to the console for debugging.

### Server-Side Error Handling

1. **Missing POST Data**: Return a 400 Bad Request with a JSON error response indicating missing fields.

2. **Validation Failures**: Return a 422 Unprocessable Entity with detailed field-level error messages.

3. **Email Sending Failures**: Log the error, return a 500 Internal Server Error, and display a message asking the user to try again or contact via phone.

4. **Rate Limit Exceeded**: Return a 429 Too Many Requests with a message indicating when the user can try again.

5. **CSRF Token Mismatch**: Return a 403 Forbidden with a message asking the user to refresh the page and try again.

6. **Honeypot Triggered**: Silently reject the submission with a fake success response to avoid alerting spam bots.

### Error Response Format

```javascript
{
  status: 'error',
  message: 'General error description',
  errors: {
    fieldName: 'Specific field error',
    anotherField: 'Another field error'
  },
  code: 'ERROR_CODE' // Optional error code for programmatic handling
}
```

## Testing Strategy

### Unit Testing

Unit tests will verify individual functions and components in isolation:

1. **Validation Functions**: Test each validation function (email, phone, required fields) with various valid and invalid inputs
2. **Sanitization Functions**: Test that sanitization properly removes harmful content while preserving valid data
3. **Email Formatting**: Test that email content is properly formatted with all required fields
4. **CSRF Token Generation**: Test that tokens are properly generated and stored
5. **Rate Limiting Logic**: Test that rate limit checks work correctly with various time intervals

### Property-Based Testing

Property-based tests will verify that correctness properties hold across many randomly generated inputs. We will use a JavaScript property-based testing library (such as fast-check) for client-side code and a PHP property-based testing approach for server-side code.

**Configuration**: Each property-based test should run a minimum of 100 iterations to ensure thorough coverage of the input space.

**Test Tagging**: Each property-based test must include a comment explicitly referencing the correctness property from this design document using the format: `**Feature: contact-form-system, Property {number}: {property_text}**`

**Property Implementation**: Each correctness property listed in the Correctness Properties section must be implemented as a single property-based test.

**Key Property Tests**:

1. **Form Submission Properties**: Generate random valid form data and verify successful submission, email sending, and field clearing
2. **Validation Properties**: Generate random invalid inputs (emails, phones, empty fields) and verify proper error handling
3. **Sanitization Properties**: Generate random inputs with potentially harmful content and verify proper sanitization
4. **Rate Limiting Properties**: Generate multiple submissions from the same IP and verify rate limiting enforcement
5. **CSRF Properties**: Generate requests with valid and invalid tokens and verify proper validation
6. **Email Content Properties**: Generate random form data and verify all fields appear correctly in emails
7. **Consistency Properties**: Verify both forms behave identically for the same inputs

### Integration Testing

Integration tests will verify that components work together correctly:

1. **End-to-End Form Submission**: Submit a complete form and verify the entire flow from client to server to email
2. **Error Flow Testing**: Trigger various error conditions and verify proper error handling throughout the stack
3. **Security Integration**: Test that all security measures (CSRF, rate limiting, honeypot) work together
4. **Cross-Browser Testing**: Verify forms work correctly in major browsers (Chrome, Firefox, Safari, Edge)
5. **Mobile Testing**: Verify forms work correctly on mobile devices with touch interactions

### Manual Testing Checklist

- [ ] Submit valid form data and verify email receipt
- [ ] Test all validation rules with invalid data
- [ ] Verify success and error messages display correctly
- [ ] Test form on both homepage and contact page
- [ ] Verify AJAX submission works without page reload
- [ ] Test rate limiting by submitting multiple times quickly
- [ ] Verify CSRF protection by manipulating tokens
- [ ] Test honeypot by filling the hidden field
- [ ] Verify forms work with JavaScript disabled (graceful degradation)
- [ ] Test on mobile devices and various screen sizes

## Implementation Notes

### Technology Stack

- **Frontend**: Vanilla JavaScript (ES6+), Bootstrap 5 for styling
- **Backend**: PHP 7.4+ with built-in mail() function
- **Security**: Session-based CSRF tokens, file-based rate limiting
- **Email**: PHP mail() function (can be upgraded to PHPMailer or SMTP)

### Configuration

The following values should be configurable:

```php
// config.php
define('BUSINESS_EMAIL', 'info@mhkts.ae');
define('RATE_LIMIT_WINDOW', 300); // 5 minutes in seconds
define('RATE_LIMIT_MAX', 3); // Maximum submissions per window
define('CSRF_TOKEN_LIFETIME', 3600); // 1 hour in seconds
define('HONEYPOT_FIELD_NAME', 'website'); // Hidden field name
```

### Security Considerations

1. **Input Sanitization**: Use `htmlspecialchars()` and `filter_var()` for all inputs
2. **SQL Injection**: Not applicable (no database), but prepared statements should be used if database is added
3. **XSS Prevention**: Sanitize all output, especially in email content
4. **CSRF Protection**: Generate unique tokens per session and validate on submission
5. **Rate Limiting**: Track submissions by IP address with time-based expiration
6. **Honeypot**: Include hidden field that should remain empty
7. **Email Header Injection**: Validate and sanitize email headers to prevent injection attacks

### Performance Considerations

1. **AJAX Requests**: Use async/await for cleaner code and better error handling
2. **Form Validation**: Perform client-side validation first to reduce server load
3. **Rate Limiting**: Use efficient file-based storage with automatic cleanup
4. **Email Sending**: Consider queuing emails for high-traffic scenarios
5. **Caching**: Cache CSRF tokens in session to avoid regeneration

### Accessibility

1. **Form Labels**: All form fields must have associated labels
2. **Error Messages**: Use ARIA attributes to announce errors to screen readers
3. **Keyboard Navigation**: Ensure all form elements are keyboard accessible
4. **Focus Management**: Manage focus appropriately during submission and error display
5. **Color Contrast**: Ensure error and success messages meet WCAG contrast requirements

### Browser Compatibility

- Modern browsers (Chrome, Firefox, Safari, Edge) - last 2 versions
- Mobile browsers (iOS Safari, Chrome Mobile) - last 2 versions
- Graceful degradation for older browsers (form still submits, but without AJAX)

### Future Enhancements

1. **Database Storage**: Store submissions in a database for record-keeping
2. **Admin Dashboard**: Create an interface to view and manage submissions
3. **Email Templates**: Use a templating system for more sophisticated emails
4. **File Uploads**: Allow users to attach files to their inquiries
5. **Auto-Response**: Send confirmation emails to users
6. **Analytics**: Track form submission rates and conversion metrics
7. **A/B Testing**: Test different form layouts and copy
8. **Internationalization**: Support multiple languages
