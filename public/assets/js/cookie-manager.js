class CookieManager {
    constructor() {
        this.consentCookieName = 'cookie_consent';
        this.langCookieName = 'lang';
        this.init();
    }

    /**
     * Initialize cookie manager - runs on page load
     */
    init() {
        // Event: load - Initialize on page load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.onLoad());
        } else {
            this.onLoad();
        }

        window.addEventListener('beforeunload', (e) => this.onBeforeUnload(e));

        window.addEventListener('scroll', () => this.onScroll());

        window.addEventListener('resize', () => this.onResize());

        window.addEventListener('error', (e) => this.onError(e), true);
    }

    /**
     * On page load handler
     */
    onLoad() {
        console.log('CookieManager: Page loaded');
        
        if (!this.hasConsent()) {
            this.showConsentBanner();
        } else {
            this.loadPreferences();
        }

        // Set up language switcher with JS cookie management
        this.setupLanguageSwitcher();

        this.setupFormValidation();

        this.trackScrollDepth();
    }

    /**
     * Before unload handler - warn about unsaved changes
     */
    onBeforeUnload(event) {
        const forms = document.querySelectorAll('form');
        let hasUnsavedChanges = false;

        forms.forEach(form => {
            if (form.dataset.modified === 'true') {
                hasUnsavedChanges = true;
            }
        });

        if (hasUnsavedChanges) {
            event.preventDefault();
            event.returnValue = ''; // Chrome requires returnValue to be set
            return 'You have unsaved changes. Are you sure you want to leave?';
        }
    }

    /**
     * Scroll event handler
     */
    onScroll() {
        const scrolled = window.scrollY;
        const navbar = document.querySelector('header, nav');
        
        if (navbar) {
            if (scrolled > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        }

        sessionStorage.setItem('scrollPosition', scrolled.toString());
    }

    /**
     * Resize event handler
     */
    onResize() {
        const width = window.innerWidth;
        console.log(`Window resized to: ${width}px`);
        
        // Adjust UI elements based on viewport
        if (width < 768) {
            document.body.classList.add('mobile-view');
            document.body.classList.remove('desktop-view');
        } else {
            document.body.classList.add('desktop-view');
            document.body.classList.remove('mobile-view');
        }
    }

    /**
     * Global error handler
     */
    onError(event) {
        console.error('Global error caught:', event.error || event.message);
        
        // Don't show error banner for resource loading errors
        if (event.target && (event.target.tagName === 'IMG' || event.target.tagName === 'SCRIPT')) {
            console.warn('Resource loading error:', event.target.src || event.target.href);
        }
    }

    /**
     * Track scroll depth for analytics
     */
    trackScrollDepth() {
        let maxScroll = 0;
        
        window.addEventListener('scroll', () => {
            const scrollPercent = (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100;
            if (scrollPercent > maxScroll) {
                maxScroll = Math.floor(scrollPercent);
                
                // Track milestones
                if (maxScroll >= 25 && !this.scrollMilestones?.['25']) {
                    this.scrollMilestones = this.scrollMilestones || {};
                    this.scrollMilestones['25'] = true;
                    console.log('User scrolled 25% of page');
                }
                if (maxScroll >= 50 && !this.scrollMilestones?.['50']) {
                    this.scrollMilestones['50'] = true;
                    console.log('User scrolled 50% of page');
                }
                if (maxScroll >= 75 && !this.scrollMilestones?.['75']) {
                    this.scrollMilestones['75'] = true;
                    console.log('User scrolled 75% of page');
                }
            }
        });
    }

    /**
     * Cookie consent methods
     */
    hasConsent() {
        return this.getCookie(this.consentCookieName) === 'accepted';
    }

    showConsentBanner() {
        const banner = document.createElement('div');
        banner.id = 'cookie-consent-banner';
        banner.className = 'cookie-consent-banner';
        banner.innerHTML = `
            <div class="cookie-consent-content">
                <div class="cookie-consent-text">
                    <h3>${this.getTranslation('cookies.banner.title')}</h3>
                    <p>${this.getTranslation('cookies.banner.message')}</p>
                </div>
                <div class="cookie-consent-actions">
                    <button id="cookie-accept" class="btn-accept">${this.getTranslation('cookies.banner.accept')}</button>
                    <button id="cookie-reject" class="btn-reject">${this.getTranslation('cookies.banner.reject')}</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(banner);

        document.getElementById('cookie-accept').addEventListener('click', () => {
            this.acceptCookies();
            banner.remove();
        });

        document.getElementById('cookie-reject').addEventListener('click', () => {
            this.rejectCookies();
            banner.remove();
        });

        // Animate banner entrance
        setTimeout(() => banner.classList.add('show'), 100);
    }

    acceptCookies() {
        this.setCookie(this.consentCookieName, 'accepted', 365);
        console.log('Cookies accepted');
        this.loadPreferences();
    }

    rejectCookies() {
        this.setCookie(this.consentCookieName, 'rejected', 365);
        console.log('Cookies rejected');
        this.clearNonEssentialCookies();
    }

    clearNonEssentialCookies() {
        // Keep only essential cookies (CSRF, session)
        const cookies = document.cookie.split(';');
        cookies.forEach(cookie => {
            const name = cookie.split('=')[0].trim();
            if (name !== this.consentCookieName && name !== 'PHPSESSID' && name !== 'csrf_token') {
                this.deleteCookie(name);
            }
        });
    }

    /**
     * Language switcher with JS cookie management
     */
    setupLanguageSwitcher() {
        const langLinks = document.querySelectorAll('[data-lang]');
        
        langLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const lang = link.dataset.lang;
                
                if (this.hasConsent()) {
                    this.setCookie(this.langCookieName, lang, 30);
                }
                
                const url = new URL(window.location.href);
                url.searchParams.set('lang', lang);
                window.location.href = url.toString();
            });
        });
    }

    /**
     * Form validation setup with Constraint Validation API
     */
    setupFormValidation() {
        const forms = document.querySelectorAll('form[data-validate="true"], form.needs-validation');
        
        forms.forEach(form => {
            this.attachFormValidation(form);
        });
    }

    attachFormValidation(form) {
        const inputs = form.querySelectorAll('input, textarea, select');
        
        inputs.forEach(input => {
            input.addEventListener('blur', () => {
                this.validateField(input);
            });

            // Event: input/change - Real-time validation
            input.addEventListener('input', () => {
                if (input.dataset.touched) {
                    this.validateField(input);
                }
                form.dataset.modified = 'true';
            });

            input.addEventListener('focus', () => {
                input.dataset.touched = 'true';
                this.clearFieldError(input);
            });
        });

        // Form submit validation
        form.addEventListener('submit', (e) => {
            let isValid = true;

            inputs.forEach(input => {
                if (!this.validateField(input)) {
                    isValid = false;
                }
            });

            if (!isValid) {
                e.preventDefault();
                e.stopPropagation();
                
                const firstInvalid = form.querySelector('input:invalid, textarea:invalid, select:invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                }
            } else {
                form.dataset.modified = 'false';
            }

            form.classList.add('was-validated');
        });
    }

    /**
     * Validate individual field using Constraint Validation API
     */
    validateField(input) {
        const isValid = input.checkValidity();
        
        if (!isValid) {
            this.showFieldError(input, input.validationMessage);
            input.classList.add('is-invalid');
            input.classList.remove('is-valid');
            
            // Use :invalid pseudo-class (CSS handles styling)
            input.setAttribute('aria-invalid', 'true');
        } else {
            this.clearFieldError(input);
            input.classList.add('is-valid');
            input.classList.remove('is-invalid');
            input.setAttribute('aria-invalid', 'false');
        }

        return isValid;
    }

    showFieldError(input, message) {
        const errorId = `${input.id || input.name}-error`;
        let errorElement = document.getElementById(errorId);
        
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.id = errorId;
            errorElement.className = 'invalid-feedback';
            input.parentNode.appendChild(errorElement);
        }
        
        errorElement.textContent = message;
        errorElement.style.display = 'block';
        input.setAttribute('aria-describedby', errorId);
    }

    clearFieldError(input) {
        const errorId = `${input.id || input.name}-error`;
        const errorElement = document.getElementById(errorId);
        
        if (errorElement) {
            errorElement.style.display = 'none';
        }
        
        input.removeAttribute('aria-describedby');
    }

    /**
     * Cookie utility methods
     */
    setCookie(name, value, days) {
        const expires = new Date();
        expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000);
        document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/;SameSite=Lax`;
    }

    getCookie(name) {
        const nameEQ = name + '=';
        const cookies = document.cookie.split(';');
        
        for (let i = 0; i < cookies.length; i++) {
            let cookie = cookies[i].trim();
            if (cookie.indexOf(nameEQ) === 0) {
                return cookie.substring(nameEQ.length);
            }
        }
        return null;
    }

    deleteCookie(name) {
        document.cookie = `${name}=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;`;
    }

    /**
     * Load user preferences from cookies
     */
    loadPreferences() {
        const lang = this.getCookie(this.langCookieName);
        if (lang) {
            console.log(`Language preference loaded: ${lang}`);
        }
    }

    /**
     * Simple translation helper for cookie banner
     */
    getTranslation(key) {
        const lang = this.getCookie(this.langCookieName) || 'en';
        
        const translations = {
            en: {
                'cookies.banner.title': 'We use cookies',
                'cookies.banner.message': 'This website uses cookies to enhance your experience and remember your preferences. By clicking "Accept", you consent to our use of cookies.',
                'cookies.banner.accept': 'Accept',
                'cookies.banner.reject': 'Reject'
            },
            ca: {
                'cookies.banner.title': 'Utilitzem cookies',
                'cookies.banner.message': 'Aquest lloc web utilitza cookies per millorar la teva experiència i recordar les teves preferències. En fer clic a "Acceptar", consents el nostre ús de cookies.',
                'cookies.banner.accept': 'Acceptar',
                'cookies.banner.reject': 'Rebutjar'
            }
        };

        return translations[lang]?.[key] || translations.en[key] || key;
    }
}

// Initialize cookie manager on load
const cookieManager = new CookieManager();
