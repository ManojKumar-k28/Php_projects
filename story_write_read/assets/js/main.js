/**
 * Main JavaScript file for StoryCraft Blog
 * Handles modal interactions, animations, and general UI functionality
 */

(function() {
    'use strict';
    
    // Modal management
    const ModalManager = {
        // Show login modal
        showLogin() {
            const modal = document.getElementById('loginModal');
            if (modal) {
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';
                
                // Focus on first input
                const firstInput = modal.querySelector('input');
                if (firstInput) {
                    setTimeout(() => firstInput.focus(), 100);
                }
            }
        },
        
        // Hide login modal
        hideLogin() {
            const modal = document.getElementById('loginModal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        },
        
        // Show register modal
        showRegister() {
            const modal = document.getElementById('registerModal');
            if (modal) {
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';
                
                // Focus on first input
                const firstInput = modal.querySelector('input');
                if (firstInput) {
                    setTimeout(() => firstInput.focus(), 100);
                }
            }
        },
        
        // Hide register modal
        hideRegister() {
            const modal = document.getElementById('registerModal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        },
        
        // Initialize modal event listeners
        init() {
            // Close modal when clicking outside
            window.addEventListener('click', (event) => {
                const loginModal = document.getElementById('loginModal');
                const registerModal = document.getElementById('registerModal');
                
                if (event.target === loginModal) {
                    this.hideLogin();
                }
                if (event.target === registerModal) {
                    this.hideRegister();
                }
            });
            
            // Close modals with Escape key
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    this.hideLogin();
                    this.hideRegister();
                }
            });
        }
    };
    
    // Make modal functions available globally
    window.showLoginModal = () => ModalManager.showLogin();
    window.hideLoginModal = () => ModalManager.hideLogin();
    window.showRegisterModal = () => ModalManager.showRegister();
    window.hideRegisterModal = () => ModalManager.hideRegister();
    
    // Animation utilities
    const AnimationUtils = {
        // Fade in element
        fadeIn(element, duration = 300) {
            element.style.opacity = '0';
            element.style.display = 'block';
            
            let start = null;
            function animate(timestamp) {
                if (!start) start = timestamp;
                const progress = timestamp - start;
                const opacity = Math.min(progress / duration, 1);
                
                element.style.opacity = opacity;
                
                if (progress < duration) {
                    requestAnimationFrame(animate);
                }
            }
            
            requestAnimationFrame(animate);
        },
        
        // Fade out element
        fadeOut(element, duration = 300) {
            let start = null;
            const initialOpacity = parseFloat(getComputedStyle(element).opacity);
            
            function animate(timestamp) {
                if (!start) start = timestamp;
                const progress = timestamp - start;
                const opacity = initialOpacity * (1 - Math.min(progress / duration, 1));
                
                element.style.opacity = opacity;
                
                if (progress < duration) {
                    requestAnimationFrame(animate);
                } else {
                    element.style.display = 'none';
                }
            }
            
            requestAnimationFrame(animate);
        },
        
        // Slide in from top
        slideInFromTop(element, duration = 300) {
            element.style.transform = 'translateY(-100%)';
            element.style.opacity = '0';
            element.style.display = 'block';
            
            let start = null;
            function animate(timestamp) {
                if (!start) start = timestamp;
                const progress = timestamp - start;
                const percent = Math.min(progress / duration, 1);
                
                const translateY = -100 + (100 * percent);
                element.style.transform = `translateY(${translateY}%)`;
                element.style.opacity = percent;
                
                if (progress < duration) {
                    requestAnimationFrame(animate);
                } else {
                    element.style.transform = 'translateY(0)';
                    element.style.opacity = '1';
                }
            }
            
            requestAnimationFrame(animate);
        }
    };
    
    // Scroll animations
    const ScrollAnimations = {
        // Observe elements for scroll animations
        observe() {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-in');
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);
            
            // Observe elements that should animate on scroll
            const animatedElements = document.querySelectorAll('.post-card, .stat-card, .action-card');
            animatedElements.forEach(el => {
                el.classList.add('animate-on-scroll');
                observer.observe(el);
            });
        }
    };
    
    // Search functionality
    const SearchManager = {
        init() {
            const searchInput = document.querySelector('.search-input');
            const searchForm = document.querySelector('.search-form');
            
            if (searchInput && searchForm) {
                // Add search suggestions (if you want to implement this later)
                searchInput.addEventListener('input', this.handleSearchInput.bind(this));
                
                // Handle form submission
                searchForm.addEventListener('submit', this.handleSearchSubmit.bind(this));
                
                // Add search keyboard shortcuts
                document.addEventListener('keydown', (e) => {
                    // Ctrl+K or Cmd+K to focus search
                    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                        e.preventDefault();
                        searchInput.focus();
                        searchInput.select();
                    }
                });
            }
        },
        
        handleSearchInput(e) {
            const query = e.target.value.trim();
            
            // Add some visual feedback
            if (query.length > 0) {
                e.target.parentElement.classList.add('has-content');
            } else {
                e.target.parentElement.classList.remove('has-content');
            }
        },
        
        handleSearchSubmit(e) {
            const searchInput = e.target.querySelector('.search-input');
            const query = searchInput.value.trim();
            
            if (!query) {
                e.preventDefault();
                searchInput.focus();
                return false;
            }
            
            // Add loading state to search button
            const searchBtn = e.target.querySelector('.search-btn');
            if (searchBtn) {
                searchBtn.textContent = 'Searching...';
                searchBtn.disabled = true;
            }
        }
    };
    
    // Post card interactions
    const PostCardManager = {
        init() {
            const postCards = document.querySelectorAll('.post-card');
            
            postCards.forEach(card => {
                // Add keyboard navigation
                card.setAttribute('tabindex', '0');
                
                // Handle keyboard navigation
                card.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        card.click();
                    }
                });
                
                // Add loading state when clicked
                card.addEventListener('click', () => {
                    card.style.pointerEvents = 'none';
                    card.style.opacity = '0.7';
                    
                    // Reset after timeout (in case navigation fails)
                    setTimeout(() => {
                        card.style.pointerEvents = 'auto';
                        card.style.opacity = '1';
                    }, 3000);
                });
                
                // Add hover sound effect (optional)
                card.addEventListener('mouseenter', () => {
                    // You can add subtle audio feedback here if desired
                });
            });
        }
    };
    
    // Notification system
    const NotificationManager = {
        show(message, type = 'info', duration = 5000) {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 1rem 1.5rem;
                border-radius: 0.5rem;
                color: white;
                font-weight: 500;
                z-index: 9999;
                max-width: 400px;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
                transform: translateX(100%);
                transition: transform 0.3s ease-out;
            `;
            
            // Set background color based on type
            const colors = {
                success: '#10b981',
                error: '#ef4444',
                warning: '#f59e0b',
                info: '#3b82f6'
            };
            notification.style.backgroundColor = colors[type] || colors.info;
            
            notification.textContent = message;
            
            // Add close button
            const closeBtn = document.createElement('button');
            closeBtn.innerHTML = '×';
            closeBtn.style.cssText = `
                background: none;
                border: none;
                color: white;
                font-size: 1.25rem;
                margin-left: 1rem;
                cursor: pointer;
                padding: 0;
                line-height: 1;
            `;
            closeBtn.addEventListener('click', () => this.remove(notification));
            notification.appendChild(closeBtn);
            
            document.body.appendChild(notification);
            
            // Animate in
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 100);
            
            // Auto remove
            setTimeout(() => {
                this.remove(notification);
            }, duration);
            
            return notification;
        },
        
        remove(notification) {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }
    };
    
    // Make notification manager available globally
    window.showNotification = (message, type, duration) => 
        NotificationManager.show(message, type, duration);
    
    // Smooth scrolling for anchor links
    const SmoothScroll = {
        init() {
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    const targetId = this.getAttribute('href');
                    const targetElement = document.querySelector(targetId);
                    
                    if (targetElement) {
                        e.preventDefault();
                        targetElement.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        }
    };
    
    // Theme management (for future dark mode support)
    const ThemeManager = {
        init() {
            // Check for saved theme preference or default to light mode
            const savedTheme = localStorage.getItem('theme') || 'light';
            this.setTheme(savedTheme);
            
            // Listen for system theme changes
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            mediaQuery.addEventListener('change', (e) => {
                if (!localStorage.getItem('theme')) {
                    this.setTheme(e.matches ? 'dark' : 'light');
                }
            });
        },
        
        setTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
        },
        
        toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            this.setTheme(newTheme);
        }
    };
    
    // Performance optimization
    const PerformanceOptimizer = {
        init() {
            // Lazy load images
            this.lazyLoadImages();
            
            // Debounce scroll events
            this.optimizeScrollEvents();
        },
        
        lazyLoadImages() {
            const images = document.querySelectorAll('img[loading="lazy"]');
            
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src || img.src;
                            img.classList.remove('lazy');
                            imageObserver.unobserve(img);
                        }
                    });
                });
                
                images.forEach(img => imageObserver.observe(img));
            }
        },
        
        optimizeScrollEvents() {
            let ticking = false;
            
            function updateOnScroll() {
                // Add scroll-based animations or effects here
                ticking = false;
            }
            
            function requestTick() {
                if (!ticking) {
                    requestAnimationFrame(updateOnScroll);
                    ticking = true;
                }
            }
            
            window.addEventListener('scroll', requestTick);
        }
    };
    
    // Initialize everything when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize all managers
        ModalManager.init();
        SearchManager.init();
        PostCardManager.init();
        SmoothScroll.init();
        ThemeManager.init();
        PerformanceOptimizer.init();
        
        // Initialize scroll animations after a short delay
        setTimeout(() => {
            ScrollAnimations.observe();
        }, 100);
        
        // Show modals based on server-side conditions
        // Check if we should show register modal
        if (window.location.search.includes('show=register')) {
            ModalManager.showRegister();
        }
        
        // Check if we should show login modal
        if (window.location.search.includes('show=login')) {
            ModalManager.showLogin();
        }
        
        // Handle form submission feedback
        const urlParams = new URLSearchParams(window.location.search);
        const success = urlParams.get('success');
        const error = urlParams.get('error');
        
        if (success) {
            NotificationManager.show(success, 'success');
        }
        if (error) {
            NotificationManager.show(error, 'error');
        }
        
        // Add loading states to all external links
        document.querySelectorAll('a[href^="http"]').forEach(link => {
            link.addEventListener('click', function() {
                this.style.opacity = '0.7';
                this.style.pointerEvents = 'none';
            });
        });
        
        // Add form enhancement
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn && !submitBtn.disabled) {
                    submitBtn.style.minWidth = submitBtn.offsetWidth + 'px';
                }
            });
        });
    });
    
    // Add CSS for scroll animations
    const style = document.createElement('style');
    style.textContent = `
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }
        
        .animate-on-scroll.animate-in {
            opacity: 1;
            transform: translateY(0);
        }
        
        .search-form.has-content .search-btn {
            background: linear-gradient(135deg, #10b981, #059669);
        }
        
        .notification {
            animation: slideInRight 0.3s ease-out;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        /* Loading spinner for buttons */
        .btn.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 16px;
            height: 16px;
            border: 2px solid transparent;
            border-top-color: currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: translate(-50%, -50%) rotate(360deg); }
        }
        
        /* Focus styles for accessibility */
        .post-card:focus {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }
        
        /* Smooth transitions for all interactive elements */
        .btn, .post-card, .nav-link, .pagination-btn {
            transition: all 0.15s ease-in-out;
        }
        
        /* Hover effects for better UX */
        .btn:hover {
            transform: translateY(-1px);
        }
        
        .post-card:hover {
            transform: translateY(-8px);
        }
        
        /* Loading states */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #e5e7eb;
            border-top-color: #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
    `;
    document.head.appendChild(style);
    
})();