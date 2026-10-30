/**
 * validation.js — Client-Side Form Validation & Password Utilities
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Password Visibility Toggles
    document.querySelectorAll('.password-toggle-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = btn.previousElementSibling;
            if (input && (input.type === 'password' || input.type === 'text')) {
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                const icon = btn.querySelector('i');
                if (icon) {
                    icon.classList.toggle('fa-eye', !isPassword);
                    icon.classList.toggle('fa-eye-slash', isPassword);
                }
            }
        });
    });

    // 2. Password Strength Meter
    const passwordInput = document.getElementById('reg_password');
    const meterBar = document.getElementById('passwordMeterBar');

    if (passwordInput && meterBar) {
        passwordInput.addEventListener('input', () => {
            const val = passwordInput.value;
            let score = 0;

            if (val.length >= 8) score++;
            if (/[a-z]/.test(val) && /[A-Z]/.test(val)) score++;
            if (/\d/.test(val)) score++;
            if (/[^a-zA-Z\d]/.test(val)) score++;

            if (val.length === 0) {
                meterBar.style.width = '0%';
                meterBar.style.backgroundColor = 'transparent';
            } else if (score <= 1) {
                meterBar.style.width = '25%';
                meterBar.style.backgroundColor = '#ef4444'; // Red (Weak)
            } else if (score === 2) {
                meterBar.style.width = '50%';
                meterBar.style.backgroundColor = '#f59e0b'; // Amber (Medium)
            } else if (score === 3) {
                meterBar.style.width = '75%';
                meterBar.style.backgroundColor = '#3b82f6'; // Blue (Good)
            } else {
                meterBar.style.width = '100%';
                meterBar.style.backgroundColor = '#10b981'; // Green (Strong)
            }
        });
    }

    // 3. Helper to display / clear field error
    function setError(input, message) {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        let errorSpan = input.parentElement.querySelector('.form-error-msg');
        if (!errorSpan) {
            errorSpan = document.createElement('span');
            errorSpan.className = 'form-error-msg';
            input.parentElement.appendChild(errorSpan);
        }
        errorSpan.textContent = message;
    }

    function clearError(input) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        const errorSpan = input.parentElement.querySelector('.form-error-msg');
        if (errorSpan) {
            errorSpan.textContent = '';
        }
    }

    // 4. Registration Form Validation
    const regForm = document.getElementById('registerForm');
    if (regForm) {
        regForm.addEventListener('submit', (e) => {
            let isValid = true;

            const name = document.getElementById('reg_name');
            const email = document.getElementById('reg_email');
            const phone = document.getElementById('reg_phone');
            const password = document.getElementById('reg_password');
            const confirmPassword = document.getElementById('reg_confirm_password');
            const terms = document.getElementById('reg_terms');

            // Name
            if (!name.value.trim() || name.value.trim().length < 3) {
                setError(name, 'Full name must be at least 3 characters.');
                isValid = false;
            } else {
                clearError(name);
            }

            // Email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email.value.trim() || !emailRegex.test(email.value.trim())) {
                setError(email, 'Please enter a valid email address.');
                isValid = false;
            } else {
                clearError(email);
            }

            // Phone (optional or digits 10-15)
            if (phone && phone.value.trim()) {
                const phoneRegex = /^[\d\+\-\s]{10,18}$/;
                if (!phoneRegex.test(phone.value.trim())) {
                    setError(phone, 'Please enter a valid phone number (e.g. +88017XXXXXXXX).');
                    isValid = false;
                } else {
                    clearError(phone);
                }
            }

            // Password
            if (!password.value || password.value.length < 8) {
                setError(password, 'Password must be at least 8 characters long.');
                isValid = false;
            } else if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(password.value)) {
                setError(password, 'Must include uppercase, lowercase, and a number.');
                isValid = false;
            } else {
                clearError(password);
            }

            // Confirm Password
            if (confirmPassword.value !== password.value) {
                setError(confirmPassword, 'Passwords do not match.');
                isValid = false;
            } else {
                clearError(confirmPassword);
            }

            // Terms
            if (terms && !terms.checked) {
                alert('You must accept the Terms of Service to create an account.');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    }

    // 5. Login Form Validation
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            let isValid = true;
            const email = document.getElementById('login_email');
            const password = document.getElementById('login_password');

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email.value.trim() || !emailRegex.test(email.value.trim())) {
                setError(email, 'Please enter a valid email address.');
                isValid = false;
            } else {
                clearError(email);
            }

            if (!password.value.trim()) {
                setError(password, 'Password is required.');
                isValid = false;
            } else {
                clearError(password);
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    }

    // 6. Contact Form Validation
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', (e) => {
            let isValid = true;
            const name = document.getElementById('contact_name');
            const email = document.getElementById('contact_email');
            const subject = document.getElementById('contact_subject');
            const message = document.getElementById('contact_message');

            if (!name.value.trim() || name.value.trim().length < 2) {
                setError(name, 'Full name must be at least 2 characters.');
                isValid = false;
            } else {
                clearError(name);
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email.value.trim() || !emailRegex.test(email.value.trim())) {
                setError(email, 'Please provide a valid email address.');
                isValid = false;
            } else {
                clearError(email);
            }

            if (!subject.value.trim() || subject.value.trim().length < 3) {
                setError(subject, 'Subject must be at least 3 characters.');
                isValid = false;
            } else {
                clearError(subject);
            }

            if (!message.value.trim() || message.value.trim().length < 10) {
                setError(message, 'Message must be at least 10 characters.');
                isValid = false;
            } else {
                clearError(message);
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    }
});

