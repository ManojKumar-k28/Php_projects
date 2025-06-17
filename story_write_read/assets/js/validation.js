/**
 * Form Validation JavaScript
 * Provides client-side validation for all forms in the blog application
 */

// Global validation functions
window.ValidationUtils = {
    // Show error message
    showError(elementId, message) {
        const errorElement = document.getElementById(elementId);
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
    },

    // Clear error message
    clearError(elementId) {
        const errorElement = document.getElementById(elementId);
        if (errorElement) {
            errorElement.textContent = '';
            errorElement.style.display = 'none';
        }
    },

    // Clear all errors
    clearErrors() {
        const errorElements = document.querySelectorAll('.form-error');
        errorElements.forEach(element => {
            element.textContent = '';
            element.style.display = 'none';
        });
    },

    // Validate email format
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    },

    // Validate username format
    isValidUsername(username) {
        const usernameRegex = /^[a-zA-Z0-9_]{3,50}$/;
        return usernameRegex.test(username);
    },

    // Validate password strength
    isValidPassword(password) {
        return password.length >= 6;
    },

    // Add loading state to button
    addLoadingState(button) {
        button.classList.add('loading');
        button.disabled = true;
        button.dataset.originalText = button.textContent;
        button.textContent = 'Loading...';
    },

    // Remove loading state from button
    removeLoadingState(button) {
        button.classList.remove('loading');
        button.disabled = false;
        if (button.dataset.originalText) {
            button.textContent = button.dataset.originalText;
            delete button.dataset.originalText;
        }
    },

    // Show success message
    showSuccess(message) {
        const successDiv = document.createElement('div');
        successDiv.className = 'alert alert-success';
        successDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
            animation: slideInRight 0.3s ease-out;
        `;
        successDiv.textContent = message;
        
        document.body.appendChild(successDiv);
        
        setTimeout(() => {
            successDiv.style.animation = 'slideOutRight 0.3s ease-in';
            setTimeout(() => {
                if (successDiv.parentNode) {
                    successDiv.parentNode.removeChild(successDiv);
                }
            }, 300);
        }, 3000);
    },

    // Show error message
    showErrorMessage(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-error';
        errorDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
            animation: slideInRight 0.3s ease-out;
        `;
        errorDiv.textContent = message;
        
        document.body.appendChild(errorDiv);
        
        setTimeout(() => {
            errorDiv.style.animation = 'slideOutRight 0.3s ease-in';
            setTimeout(() => {
                if (errorDiv.parentNode) {
                    errorDiv.parentNode.removeChild(errorDiv);
                }
            }, 300);
        }, 5000);
    }
};

// Make functions available globally
window.showError = window.ValidationUtils.showError;
window.clearError = window.ValidationUtils.clearError;
window.clearErrors = window.ValidationUtils.clearErrors;

// Login form validation
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Clear previous errors
            clearErrors();
            
            // Validate username/email
            const username = document.getElementById('login_username');
            if (!username.value.trim()) {
                showError('login-username-error', 'Username or email is required');
                isValid = false;
            }
            
            // Validate password
            const password = document.getElementById('login_password');
            if (!password.value.trim()) {
                showError('login-password-error', 'Password is required');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                return false;
            }
            
            // Add loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                ValidationUtils.addLoadingState(submitBtn);
                
                // Remove loading state after timeout
                setTimeout(() => {
                    ValidationUtils.removeLoadingState(submitBtn);
                }, 5000);
            }
        });
    }

    // Register form validation
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Clear previous errors
            clearErrors();
            
            // Validate username
            const username = document.getElementById('reg_username');
            if (!username.value.trim()) {
                showError('reg-username-error', 'Username is required');
                isValid = false;
            } else if (!ValidationUtils.isValidUsername(username.value)) {
                showError('reg-username-error', 'Username must be 3-50 characters and contain only letters, numbers, and underscores');
                isValid = false;
            }
            
            // Validate email
            const email = document.getElementById('reg_email');
            if (!email.value.trim()) {
                showError('reg-email-error', 'Email is required');
                isValid = false;
            } else if (!ValidationUtils.isValidEmail(email.value)) {
                showError('reg-email-error', 'Please enter a valid email address');
                isValid = false;
            }
            
            // Validate password
            const password = document.getElementById('reg_password');
            if (!password.value.trim()) {
                showError('reg-password-error', 'Password is required');
                isValid = false;
            } else if (!ValidationUtils.isValidPassword(password.value)) {
                showError('reg-password-error', 'Password must be at least 6 characters long');
                isValid = false;
            }
            
            // Validate confirm password
            const confirmPassword = document.getElementById('reg_confirm_password');
            if (!confirmPassword.value.trim()) {
                showError('reg-confirm-error', 'Please confirm your password');
                isValid = false;
            } else if (password.value !== confirmPassword.value) {
                showError('reg-confirm-error', 'Passwords do not match');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                return false;
            }
            
            // Add loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                ValidationUtils.addLoadingState(submitBtn);
                
                // Remove loading state after timeout
                setTimeout(() => {
                    ValidationUtils.removeLoadingState(submitBtn);
                }, 5000);
            }
        });
        
        // Real-time password matching validation
        const password = document.getElementById('reg_password');
        const confirmPassword = document.getElementById('reg_confirm_password');
        
        if (password && confirmPassword) {
            function checkPasswordMatch() {
                if (confirmPassword.value && password.value !== confirmPassword.value) {
                    showError('reg-confirm-error', 'Passwords do not match');
                } else {
                    clearError('reg-confirm-error');
                }
            }
            
            password.addEventListener('input', checkPasswordMatch);
            confirmPassword.addEventListener('input', checkPasswordMatch);
        }
    }

    // Post creation form validation
    const createPostForm = document.getElementById('createPostForm');
    if (createPostForm) {
        createPostForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Clear previous errors
            clearErrors();
            
            // Validate title
            const title = document.getElementById('title');
            if (!title.value.trim()) {
                showError('title-error', 'Title is required');
                isValid = false;
            } else if (title.value.trim().length < 5) {
                showError('title-error', 'Title must be at least 5 characters long');
                isValid = false;
            } else if (title.value.trim().length > 255) {
                showError('title-error', 'Title must be less than 255 characters');
                isValid = false;
            }
            
            // Validate content
            const content = document.getElementById('content');
            if (!content.value.trim()) {
                showError('content-error', 'Content is required');
                isValid = false;
            } else if (content.value.trim().length < 50) {
                showError('content-error', 'Content must be at least 50 characters long');
                isValid = false;
            }
            
            // Validate file uploads
            const featuredImage = document.getElementById('featured_image');
            if (featuredImage && featuredImage.files.length > 0) {
                const file = featuredImage.files[0];
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                const maxSize = 5 * 1024 * 1024; // 5MB
                
                if (!allowedTypes.includes(file.type)) {
                    showError('featured-image-error', 'Featured image must be a JPG, PNG, or GIF file');
                    isValid = false;
                } else if (file.size > maxSize) {
                    showError('featured-image-error', 'Featured image must be less than 5MB');
                    isValid = false;
                }
            }
            
            if (!isValid) {
                e.preventDefault();
                return false;
            }
            
            // Add loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                ValidationUtils.addLoadingState(submitBtn);
            }
        });
        
        // Character counter for content
        const contentTextarea = document.getElementById('content');
        if (contentTextarea) {
            const counter = document.createElement('div');
            counter.className = 'character-counter';
            counter.style.textAlign = 'right';
            counter.style.color = '#666';
            counter.style.fontSize = '0.85rem';
            counter.style.marginTop = '0.5rem';
            contentTextarea.parentNode.insertBefore(counter, contentTextarea.nextSibling);
            
            function updateCounter() {
                const length = contentTextarea.value.length;
                counter.textContent = `${length} characters`;
                counter.style.color = length < 50 ? '#ef4444' : length > 5000 ? '#f59e0b' : '#666';
                
                if (length < 50) {
                    counter.textContent += ' (minimum 50 required)';
                }
            }
            
            contentTextarea.addEventListener('input', updateCounter);
            updateCounter(); // Initial count
        }
        
        // Title character counter
        const titleInput = document.getElementById('title');
        if (titleInput) {
            const titleCounter = document.createElement('div');
            titleCounter.className = 'character-counter';
            titleCounter.style.textAlign = 'right';
            titleCounter.style.color = '#666';
            titleCounter.style.fontSize = '0.85rem';
            titleCounter.style.marginTop = '0.5rem';
            titleInput.parentNode.insertBefore(titleCounter, titleInput.nextSibling);
            
            function updateTitleCounter() {
                const length = titleInput.value.length;
                titleCounter.textContent = `${length}/255 characters`;
                titleCounter.style.color = length > 255 ? '#ef4444' : length > 200 ? '#f59e0b' : '#666';
            }
            
            titleInput.addEventListener('input', updateTitleCounter);
            updateTitleCounter(); // Initial count
        }
    }

    // Edit post form validation
    const editPostForm = document.getElementById('editPostForm');
    if (editPostForm) {
        editPostForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Clear previous errors
            clearErrors();
            
            // Validate title
            const title = document.getElementById('title');
            if (!title.value.trim()) {
                showError('title-error', 'Title is required');
                isValid = false;
            } else if (title.value.trim().length < 5) {
                showError('title-error', 'Title must be at least 5 characters long');
                isValid = false;
            } else if (title.value.trim().length > 255) {
                showError('title-error', 'Title must be less than 255 characters');
                isValid = false;
            }
            
            // Validate content
            const content = document.getElementById('content');
            if (!content.value.trim()) {
                showError('content-error', 'Content is required');
                isValid = false;
            } else if (content.value.trim().length < 50) {
                showError('content-error', 'Content must be at least 50 characters long');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                return false;
            }
            
            // Add loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                ValidationUtils.addLoadingState(submitBtn);
            }
        });
    }

    // File upload validation
    function validateFileInput(input) {
        const files = input.files;
        const allowedTypes = {
            'image': ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'],
            'document': ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain']
        };
        const maxSize = 5 * 1024 * 1024; // 5MB
        
        let isValid = true;
        const errors = [];
        
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            
            // Check file size
            if (file.size > maxSize) {
                errors.push(`${file.name} is too large (max 5MB)`);
                isValid = false;
                continue;
            }
            
            // Check file type
            const isImage = allowedTypes.image.includes(file.type);
            const isDocument = allowedTypes.document.includes(file.type);
            
            if (!isImage && !isDocument) {
                errors.push(`${file.name} is not a supported file type`);
                isValid = false;
            }
        }
        
        // Show errors
        if (!isValid) {
            const errorId = input.id + '-error';
            showError(errorId, errors.join(', '));
        } else {
            const errorId = input.id + '-error';
            clearError(errorId);
        }
        
        return isValid;
    }
    
    // Add file validation to file inputs
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            validateFileInput(this);
        });
    });

    // Form auto-save functionality
    function setupAutoSave(formId, storageKey) {
        const form = document.getElementById(formId);
        if (!form) return;
        
        // Load saved data
        const savedData = localStorage.getItem(storageKey);
        if (savedData) {
            try {
                const data = JSON.parse(savedData);
                for (const [key, value] of Object.entries(data)) {
                    const input = form.querySelector(`[name="${key}"]`);
                    if (input && input.type !== 'file') {
                        input.value = value;
                    }
                }
            } catch (e) {
                console.error('Error loading saved form data:', e);
            }
        }
        
        // Save data on input
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            if (input.type !== 'file' && input.type !== 'submit') {
                input.addEventListener('input', function() {
                    const formData = new FormData(form);
                    const data = {};
                    for (const [key, value] of formData.entries()) {
                        if (key !== 'action') { // Don't save action field
                            data[key] = value;
                        }
                    }
                    localStorage.setItem(storageKey, JSON.stringify(data));
                });
            }
        });
        
        // Clear saved data on successful submit
        form.addEventListener('submit', function() {
            setTimeout(() => {
                localStorage.removeItem(storageKey);
            }, 1000);
        });
    }
    
    // Setup auto-save for post forms
    setupAutoSave('createPostForm', 'blog_create_post_draft');
    setupAutoSave('editPostForm', 'blog_edit_post_draft');

    // Enhanced form UX
    const allForms = document.querySelectorAll('form');
    allForms.forEach(form => {
        // Add loading animation to forms
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.classList.contains('loading')) {
                ValidationUtils.addLoadingState(submitBtn);
                
                // Fallback to remove loading state
                setTimeout(() => {
                    ValidationUtils.removeLoadingState(submitBtn);
                }, 10000);
            }
        });
        
        // Enhanced input focus states
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
            });
        });
    });
});

// Add CSS animations for notifications
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100%);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes slideOutRight {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(100%);
        }
    }
    
    .form-group.focused label {
        color: var(--primary-600);
        font-weight: 600;
    }
    
    .form-group.focused input,
    .form-group.focused textarea,
    .form-group.focused select {
        border-color: var(--primary-500);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
`;
document.head.appendChild(style);