/**
 * Vehicle Form Enhancement
 * Real-world application of event listeners and Constraint Validation API
 * 
 * Event handlers used:
 * - load: Initialize map and form
 * - resize: Adjust map on window resize
 * - focus/blur: Field validation
 * - change: Real-time updates
 * - error: Map tile loading errors
 * - beforeunload: Warn about unsaved changes
 */

class VehicleFormHandler {
    constructor() {
        this.form = null;
        this.map = null;
        this.formModified = false;
        this.validationRules = {};
        
        // Event: load - Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.init());
        } else {
            this.init();
        }
    }

    /**
     * Initialize form handlers
     */
    init() {
        this.form = document.querySelector('form[action*="/vehicle/store"], form[action*="/vehicle/update"]');
        
        if (!this.form) return;

        // Set up validation rules
        this.setupValidationRules();
        
        // Attach event handlers
        this.attachEventHandlers();
        
        // Event: resize - Invalidate map size on window resize
        window.addEventListener('resize', () => this.onResize());
        
        // Event: beforeunload - Warn about unsaved changes
        window.addEventListener('beforeunload', (e) => this.onBeforeUnload(e));
        
        // Event: error - Handle map tile loading errors
        window.addEventListener('error', (e) => this.onError(e), true);
        
        console.log('✅ Vehicle form handler initialized');
    }

    /**
     * Setup validation rules using Constraint Validation API
     */
    setupValidationRules() {
        this.validationRules = {
            vin: {
                pattern: /^[A-HJ-NPR-Z0-9]{17}$/i,
                message: 'VIN must be exactly 17 alphanumeric characters (excluding I, O, Q)',
                validator: (value) => {
                    if (!value) return 'VIN is required';
                    if (value.length !== 17) return 'VIN must be exactly 17 characters';
                    if (!/^[A-HJ-NPR-Z0-9]{17}$/i.test(value)) {
                        return 'VIN contains invalid characters (I, O, Q not allowed)';
                    }
                    return null;
                }
            },
            model: {
                minLength: 2,
                maxLength: 100,
                message: 'Model name must be between 2 and 100 characters'
            },
            battery_capacity: {
                min: 0.1,
                max: 200,
                message: 'Battery capacity must be between 0.1 and 200 kWh',
                validator: (value) => {
                    const num = parseFloat(value);
                    if (isNaN(num)) return 'Battery capacity must be a number';
                    if (num < 0.1) return 'Battery capacity must be at least 0.1 kWh';
                    if (num > 200) return 'Battery capacity cannot exceed 200 kWh';
                    return null;
                }
            },
            location: {
                pattern: /^POINT\s*\(\s*[-+]?\d+(\.\d+)?\s+[-+]?\d+(\.\d+)?\s*\)$/i,
                message: 'Location must be in POINT(latitude longitude) format',
                validator: (value) => {
                    if (!value) return 'Location is required';
                    const match = value.match(/^POINT\s*\(\s*([-+]?\d+(?:\.\d+)?)\s+([-+]?\d+(?:\.\d+)?)\s*\)$/i);
                    if (!match) return 'Invalid POINT format. Use: POINT(lat lon)';
                    
                    const lat = parseFloat(match[1]);
                    const lon = parseFloat(match[2]);
                    
                    if (lat < -90 || lat > 90) return 'Latitude must be between -90 and 90';
                    if (lon < -180 || lon > 180) return 'Longitude must be between -180 and 180';
                    
                    return null;
                }
            },
            sensor_data: {
                validator: (value) => {
                    if (!value || value.trim() === '') return null; // Optional field
                    
                    try {
                        const json = JSON.parse(value);
                        if (typeof json !== 'object' || json === null) {
                            return 'Sensor data must be a valid JSON object';
                        }
                        return null;
                    } catch (e) {
                        return 'Invalid JSON format: ' + e.message;
                    }
                }
            }
        };
    }

    /**
     * Attach event handlers to form inputs
     */
    attachEventHandlers() {
        const inputs = this.form.querySelectorAll('input, textarea, select');
        
        inputs.forEach(input => {
            // Event: focus - Clear error when user focuses field
            input.addEventListener('focus', () => this.onFocus(input));
            
            // Event: blur - Validate when user leaves field
            input.addEventListener('blur', () => this.onBlur(input));
            
            // Event: input/change - Track modifications and real-time validation
            const eventType = input.type === 'checkbox' || input.tagName === 'SELECT' ? 'change' : 'input';
            input.addEventListener(eventType, () => this.onChange(input));
        });

        // Form submit validation
        this.form.addEventListener('submit', (e) => this.onSubmit(e));
        
        // Special handling for VIN field - uppercase conversion
        const vinInput = this.form.querySelector('input[name="vin"]');
        if (vinInput) {
            vinInput.addEventListener('input', (e) => {
                e.target.value = e.target.value.toUpperCase().replace(/[^A-HJ-NPR-Z0-9]/g, '');
            });
        }

        // Special handling for sensor data field - JSON formatting
        const sensorInput = this.form.querySelector('textarea[name="sensor_data"]');
        if (sensorInput) {
            // Add a format button
            const formatBtn = document.createElement('button');
            formatBtn.type = 'button';
            formatBtn.className = 'mt-1 text-sm text-blue-600 hover:text-blue-800';
            formatBtn.textContent = '⚡ Format JSON';
            formatBtn.addEventListener('click', () => this.formatJSON(sensorInput));
            sensorInput.parentNode.appendChild(formatBtn);
        }
    }

    /**
     * Event: focus - Clear validation errors
     */
    onFocus(input) {
        input.classList.remove('is-invalid');
        this.clearError(input);
    }

    /**
     * Event: blur - Validate field
     */
    onBlur(input) {
        this.validateField(input);
    }

    /**
     * Event: change/input - Track modifications and real-time validation
     */
    onChange(input) {
        this.formModified = true;
        
        // Real-time validation after first blur (if field was touched)
        if (input.dataset.touched) {
            this.validateField(input);
        }
    }

    /**
     * Event: resize - Invalidate Leaflet map size
     */
    onResize() {
        // If there's a Leaflet map instance, invalidate its size
        if (window.L && window.createMap) {
            setTimeout(() => {
                if (window.createMap && window.createMap.invalidateSize) {
                    window.createMap.invalidateSize();
                }
            }, 100);
        }
    }

    /**
     * Event: beforeunload - Warn about unsaved changes
     */
    onBeforeUnload(event) {
        if (this.formModified) {
            event.preventDefault();
            event.returnValue = ''; // Required for Chrome
            return 'You have unsaved changes. Are you sure you want to leave?';
        }
    }

    /**
     * Event: error - Handle image/script loading errors
     */
    onError(event) {
        if (event.target && event.target.tagName === 'IMG') {
            console.warn('Image loading error:', event.target.src);
            // Optionally replace with placeholder
            event.target.src = '/images/logo.jpg';
        }
    }

    /**
     * Validate individual field using custom rules and Constraint Validation API
     */
    validateField(input) {
        input.dataset.touched = 'true';
        
        const name = input.name;
        const value = input.value;
        
        // First, check HTML5 constraint validation
        if (!input.checkValidity()) {
            this.showError(input, input.validationMessage);
            input.classList.add('is-invalid');
            input.classList.remove('is-valid');
            return false;
        }
        
        // Then check custom validation rules
        const rule = this.validationRules[name];
        if (rule && rule.validator) {
            const error = rule.validator(value);
            if (error) {
                this.showError(input, error);
                input.classList.add('is-invalid');
                input.classList.remove('is-valid');
                input.setCustomValidity(error);
                return false;
            }
        }
        
        // Field is valid
        input.setCustomValidity('');
        this.clearError(input);
        input.classList.add('is-valid');
        input.classList.remove('is-invalid');
        return true;
    }

    /**
     * Show validation error
     */
    showError(input, message) {
        const errorId = `${input.name}-error`;
        let errorElement = document.getElementById(errorId);
        
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.id = errorId;
            errorElement.className = 'text-red-600 text-sm mt-1';
            input.parentNode.appendChild(errorElement);
        }
        
        errorElement.textContent = message;
        errorElement.style.display = 'block';
        input.setAttribute('aria-describedby', errorId);
        input.setAttribute('aria-invalid', 'true');
    }

    /**
     * Clear validation error
     */
    clearError(input) {
        const errorId = `${input.name}-error`;
        const errorElement = document.getElementById(errorId);
        
        if (errorElement) {
            errorElement.style.display = 'none';
        }
        
        input.removeAttribute('aria-describedby');
        input.setAttribute('aria-invalid', 'false');
    }

    /**
     * Form submit validation
     */
    onSubmit(event) {
        let isValid = true;
        const inputs = this.form.querySelectorAll('input, textarea, select');
        
        inputs.forEach(input => {
            if (input.name && !this.validateField(input)) {
                isValid = false;
            }
        });

        if (!isValid) {
            event.preventDefault();
            event.stopPropagation();
            
            // Focus first invalid field
            const firstInvalid = this.form.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.focus();
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            
            // Show error message
            this.showFormError('Please fix the validation errors before submitting.');
        } else {
            // Mark form as saved (prevent beforeunload warning)
            this.formModified = false;
        }

        this.form.classList.add('was-validated');
    }

    /**
     * Show form-level error
     */
    showFormError(message) {
        let errorDiv = document.querySelector('.form-error-message');
        
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'form-error-message bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4';
            this.form.insertBefore(errorDiv, this.form.firstChild);
        }
        
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            errorDiv.style.display = 'none';
        }, 5000);
    }

    /**
     * Format JSON in sensor data field
     */
    formatJSON(textarea) {
        try {
            const json = JSON.parse(textarea.value);
            textarea.value = JSON.stringify(json, null, 2);
            this.clearError(textarea);
            textarea.classList.remove('is-invalid');
            textarea.classList.add('is-valid');
        } catch (e) {
            this.showError(textarea, 'Cannot format: ' + e.message);
            textarea.classList.add('is-invalid');
        }
    }
}

// Initialize vehicle form handler
const vehicleFormHandler = new VehicleFormHandler();
