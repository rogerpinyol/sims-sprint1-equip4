# JavaScript Cookie Management & Validation

This document explains the JavaScript-based cookie management system, consent banner, event handling, and form validation implemented in the EcoMotion project.

## Overview

The cookie management system provides:
- **Cookie consent banner** (GDPR-compliant)
- **JavaScript-based cookie management** for user preferences (language, etc.)
- **Comprehensive event handling** (load, beforeunload, scroll, resize, focus, blur, change, error)
- **Form validation** using the Constraint Validation API with `:valid` and `:invalid` pseudo-classes

## Files Structure

```
public/
├── assets/
│   ├── css/
│   │   └── cookie-manager.css       # Styles for consent banner and validation
│   └── js/
│       └── cookie-manager.js        # Main cookie manager class
```

## Features

### 1. Cookie Consent Banner

When users first visit the site, they see a consent banner asking permission to use cookies.

**Languages supported:**
- English (en)
- Catalan (ca)

**User actions:**
- **Accept**: Allows all cookies (language preference, analytics, etc.)
- **Reject**: Only essential cookies (CSRF, session)

### 2. JavaScript Cookie Management

The `CookieManager` class provides methods to:
- `setCookie(name, value, days)` - Set a cookie with expiration
- `getCookie(name)` - Retrieve cookie value
- `deleteCookie(name)` - Remove a cookie
- `hasConsent()` - Check if user has given consent

**Example usage:**
```javascript
// Automatically handled by CookieManager
// Language preference is saved when user clicks language switcher
```

### 3. Event Handlers Implemented

#### **load Event**
- Initializes cookie manager on page load
- Shows consent banner if needed
- Sets up form validation
- Loads user preferences from cookies

#### **beforeunload Event**
- Warns users about unsaved changes in forms
- Prevents accidental navigation away from modified forms

```javascript
// Forms are tracked automatically
// Any change to a form input marks it as modified
```

#### **scroll Event**
- Adds shadow to navbar when scrolled
- Tracks scroll depth for analytics (25%, 50%, 75%, 100%)
- Saves scroll position to sessionStorage

#### **resize Event**
- Adjusts UI for mobile/desktop views
- Adds responsive classes to body element

#### **focus & blur Events**
- Clears validation errors on focus
- Validates fields on blur
- Marks fields as "touched" for validation

#### **change Event**
- Real-time validation on form inputs
- Marks forms as modified

#### **error Event**
- Global error handler
- Logs errors to console
- Handles image/script loading failures gracefully

### 4. Form Validation with Constraint Validation API

All forms with `data-validate="true"` attribute are automatically validated.

**Supported validation rules:**
- `required` - Field must not be empty
- `type="email"` - Must be valid email format
- `minlength` - Minimum character length
- `maxlength` - Maximum character length
- `pattern` - Custom regex pattern
- Custom validation messages

**CSS Pseudo-classes:**
- `:valid` - Applied to valid inputs (green border + checkmark)
- `:invalid` - Applied to invalid inputs (red border + error icon)

**Example HTML:**
```html
<form data-validate="true" novalidate>
    <input 
        type="email" 
        name="email" 
        required 
        placeholder="you@example.com"
    />
    <p id="email-error" class="invalid-feedback"></p>
    
    <input 
        type="password" 
        name="password" 
        required 
        minlength="6"
        placeholder="Enter password"
    />
    <p id="password-error" class="invalid-feedback"></p>
    
    <button type="submit">Submit</button>
</form>
```

**Validation behavior:**
1. Fields are validated on **blur** (when user leaves field)
2. Once touched, validation happens in **real-time** (on input)
3. On **submit**, all fields are validated
4. First invalid field receives **focus**
5. Native browser validation messages are used

### 5. Language Preference with JS Cookies

When users change language via the switcher:
1. JavaScript intercepts the click
2. If consent is given, saves `lang` cookie
3. Updates URL parameter `?lang=en` or `?lang=ca`
4. Reloads page with new language

**Cookie details:**
- Name: `lang`
- Values: `en`, `ca`
- Expiration: 30 days
- Path: `/` (site-wide)
- SameSite: `Lax`

## Integration

### Add to Layouts

The cookie manager is automatically included in:
- `app/views/layouts/app.php`
- `public/landingpage.php`

**Required includes:**
```html
<link rel="stylesheet" href="/assets/css/cookie-manager.css" />
<script src="/assets/js/cookie-manager.js"></script>
```

### Add Validation to Forms

1. Add `data-validate="true"` to form tag
2. Add `novalidate` to disable browser defaults
3. Add validation attributes to inputs (`required`, `minlength`, etc.)
4. Add error message containers with class `invalid-feedback`

```html
<form data-validate="true" novalidate>
    <input type="text" name="field" required />
    <p id="field-error" class="invalid-feedback"></p>
</form>
```

## Browser Compatibility

- **Modern browsers**: Full support (Chrome 90+, Firefox 88+, Safari 14+, Edge 90+)
- **Older browsers**: Graceful degradation (native HTML5 validation)

## Testing

### Test Cookie Consent
1. Visit site in incognito/private mode
2. Consent banner should appear at bottom
3. Click "Accept" or "Reject"
4. Reload page - banner should not appear again

### Test Language Switching
1. Click language switcher (EN/CA)
2. Check browser DevTools > Application > Cookies
3. Verify `lang` cookie is set with correct value
4. Page reloads with selected language

### Test Form Validation
1. Focus on email field and leave empty - should show error on blur
2. Enter invalid email - should show red border
3. Enter valid email - should show green border with checkmark
4. Try to submit invalid form - should prevent submission
5. First invalid field should receive focus

### Test Event Handlers
- **Scroll**: Scroll down page - navbar should get shadow
- **Resize**: Resize browser window - console logs window width
- **Beforeunload**: Modify form input, try to close tab - should warn about unsaved changes
- **Error**: Check console for any JavaScript errors

## Performance

- **Cookie banner**: Lazy-loaded, only shown once
- **Event listeners**: Debounced/throttled for scroll and resize
- **Validation**: Runs only when needed (blur, submit)
- **CSS**: Uses native `:valid` and `:invalid` pseudo-classes (hardware accelerated)

## Security

- **HttpOnly cookies**: Session cookies are HttpOnly (set by PHP)
- **SameSite**: All cookies use `SameSite=Lax` to prevent CSRF
- **Secure**: In production, add `Secure` flag for HTTPS
- **XSS Protection**: All user input is sanitized before display
- **CSRF Protection**: Forms include CSRF tokens

## Future Enhancements

- [ ] Add "Manage Preferences" button for granular cookie control
- [ ] Track user analytics with consent
- [ ] Add more validation rules (phone numbers, credit cards, etc.)
- [ ] Implement custom validation messages per field
- [ ] Add accessibility improvements (ARIA labels, screen reader support)
- [ ] Cookie settings panel to manage individual cookies

## Troubleshooting

**Banner not showing:**
- Check browser console for errors
- Verify `/assets/js/cookie-manager.js` is loaded
- Check if `cookie_consent` cookie already exists

**Validation not working:**
- Verify form has `data-validate="true"` attribute
- Check that input has proper validation attributes
- Look for JavaScript errors in console

**Language not persisting:**
- Check if cookies are enabled in browser
- Verify `lang` cookie is set in DevTools
- Check if consent was given (reject blocks non-essential cookies)

## References

- [MDN: Constraint Validation API](https://developer.mozilla.org/en-US/docs/Web/API/Constraint_validation)
- [MDN: CSS :valid and :invalid](https://developer.mozilla.org/en-US/docs/Web/CSS/:valid)
- [GDPR Cookie Consent Guidelines](https://gdpr.eu/cookies/)
- [MDN: Document.cookie](https://developer.mozilla.org/en-US/docs/Web/API/Document/cookie)
