/**
 * Collaborative Progress Tracker JavaScript
 * Handles client-side interactions and enhancements for shared tasks
 */

document.addEventListener('DOMContentLoaded', function () {
    // Initialize the application
    initializeApp();
});

/**
 * Initialize application functionality
 */
function initializeApp() {
    // Add smooth animations to progress bars
    animateProgressBars();

    // Add form validation
    setupFormValidation();

    // Add keyboard shortcuts
    setupKeyboardShortcuts();

    // Add task interaction enhancements
    setupTaskInteractions();

    // Add auto-save functionality for forms
    setupAutoSave();

    // Add collaboration features
    setupCollaborationFeatures();
}

/**
 * Animate progress bars on page load
 */
function animateProgressBars() {
    const progressFills = document.querySelectorAll('.progress-fill');
    progressFills.forEach(fill => {
        const targetWidth = fill.style.width;
        fill.style.width = '0%';

        setTimeout(() => {
            fill.style.width = targetWidth;
        }, 500);
    });
}

/**
 * Setup form validation with enhanced features
 */
function setupFormValidation() {
    const taskForm = document.querySelector('.task-form');
    if (taskForm) {
        const titleInput = taskForm.querySelector('#title');
        const descriptionInput = taskForm.querySelector('#description');
        const submitButton = taskForm.querySelector('button[type="submit"]');

        // Real-time title validation
        titleInput.addEventListener('input', function () {
            const isValid = this.value.trim().length > 0 && this.value.length <= 255;

            if (isValid) {
                this.style.borderColor = '#48bb78';
                this.style.boxShadow = '0 0 0 3px rgba(72, 187, 120, 0.1)';
                submitButton.disabled = false;
            } else {
                this.style.borderColor = '#f56565';
                this.style.boxShadow = '0 0 0 3px rgba(245, 101, 101, 0.1)';
                submitButton.disabled = true;
            }
        });

        // Character counter for title
        const titleCounter = document.createElement('small');
        titleCounter.className = 'char-counter';
        titleCounter.style.cssText = 'display: block; margin-top: 5px; font-size: 0.8rem;';
        titleInput.parentNode.appendChild(titleCounter);

        titleInput.addEventListener('input', function () {
            const remaining = 255 - this.value.length;
            titleCounter.textContent = `${remaining} characters remaining`;
            titleCounter.style.color = remaining < 20 ? '#f56565' : '#718096';
        });

        // Description counter
        const descCounter = document.createElement('small');
        descCounter.className = 'desc-counter';
        descCounter.style.cssText = 'display: block; margin-top: 5px; font-size: 0.8rem; color: #718096;';
        descriptionInput.parentNode.appendChild(descCounter);

        descriptionInput.addEventListener('input', function () {
            const length = this.value.length;
            descCounter.textContent = `${length} characters`;
        });
    }
}

/**
 * Setup keyboard shortcuts
 */
function setupKeyboardShortcuts() {
    document.addEventListener('keydown', function (e) {
        // Ctrl/Cmd + Enter to submit task form
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            const taskForm = document.querySelector('.task-form');
            if (taskForm && document.activeElement.closest('.task-form')) {
                e.preventDefault();
                const submitButton = taskForm.querySelector('button[type="submit"]');
                if (!submitButton.disabled) {
                    submitButton.click();
                    showNotification('Task created successfully!', 'success');
                }
            }
        }

        // Escape to clear form
        if (e.key === 'Escape') {
            const activeForm = document.activeElement.closest('form');
            if (activeForm && activeForm.classList.contains('task-form')) {
                activeForm.reset();
                document.activeElement.blur();
                showNotification('Form cleared', 'info');
            }
        }

        // Space to toggle task completion (when focused on checkbox)
        if (e.key === ' ' && document.activeElement.type === 'checkbox') {
            e.preventDefault();
            document.activeElement.checked = !document.activeElement.checked;
            document.activeElement.closest('form').submit();
        }
    });
}

/**
 * Setup task interaction enhancements
 */
function setupTaskInteractions() {
    const taskItems = document.querySelectorAll('.task-item');

    taskItems.forEach(item => {
        // Add completion animation
        const checkbox = item.querySelector('input[type="checkbox"]');
        if (checkbox) {
            checkbox.addEventListener('change', function () {
                const taskItem = this.closest('.task-item');

                if (this.checked) {
                    // Completion animation
                    taskItem.style.transform = 'scale(0.98)';
                    taskItem.style.opacity = '0.8';

                    // Add completion effect
                    const completionEffect = document.createElement('div');
                    completionEffect.innerHTML = '✓';
                    completionEffect.style.cssText = `
                        position: absolute;
                        top: 50%;
                        left: 50%;
                        transform: translate(-50%, -50%) scale(0);
                        font-size: 3rem;
                        color: #48bb78;
                        font-weight: bold;
                        pointer-events: none;
                        z-index: 10;
                        animation: completionPop 0.6s ease-out forwards;
                    `;

                    taskItem.style.position = 'relative';
                    taskItem.appendChild(completionEffect);

                    setTimeout(() => {
                        taskItem.style.transform = 'scale(1)';
                        taskItem.style.opacity = '1';
                        completionEffect.remove();
                        showNotification('Task marked as completed!', 'success');
                    }, 600);
                } else {
                    showNotification('Task marked as pending', 'info');
                }
            });
        }

        // Add hover effects for collaboration indicators
        const collabIndicator = item.querySelector('.collaboration-indicator');
        if (collabIndicator) {
            collabIndicator.addEventListener('mouseenter', function () {
                this.style.transform = 'scale(1.05)';
                this.style.boxShadow = '0 4px 12px rgba(102, 126, 234, 0.2)';
            });

            collabIndicator.addEventListener('mouseleave', function () {
                this.style.transform = 'scale(1)';
                this.style.boxShadow = 'none';
            });
        }
    });

    // Add CSS animation for completion effect
    if (!document.querySelector('#completion-animation-style')) {
        const style = document.createElement('style');
        style.id = 'completion-animation-style';
        style.textContent = `
            @keyframes completionPop {
                0% { transform: translate(-50%, -50%) scale(0) rotate(0deg); opacity: 0; }
                50% { transform: translate(-50%, -50%) scale(1.2) rotate(180deg); opacity: 1; }
                100% { transform: translate(-50%, -50%) scale(1) rotate(360deg); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    }
}

/**
 * Setup auto-save functionality
 */
function setupAutoSave() {
    const titleInput = document.querySelector('#title');
    const descriptionInput = document.querySelector('#description');

    if (titleInput && descriptionInput) {
        let saveTimeout;

        function saveFormData() {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => {
                const formData = {
                    title: titleInput.value,
                    description: descriptionInput.value,
                    timestamp: Date.now()
                };
                localStorage.setItem('sharedTaskFormData', JSON.stringify(formData));

                // Show subtle save indicator
                showSaveIndicator();
            }, 1000); // Save after 1 second of inactivity
        }

        function restoreFormData() {
            const savedData = localStorage.getItem('sharedTaskFormData');
            if (savedData) {
                const formData = JSON.parse(savedData);
                // Only restore if data is less than 1 hour old
                if (Date.now() - formData.timestamp < 3600000) {
                    titleInput.value = formData.title || '';
                    descriptionInput.value = formData.description || '';

                    if (formData.title || formData.description) {
                        showNotification('Draft restored from auto-save', 'info');
                    }
                }
            }
        }

        function clearSavedData() {
            localStorage.removeItem('sharedTaskFormData');
        }

        // Setup event listeners
        titleInput.addEventListener('input', saveFormData);
        descriptionInput.addEventListener('input', saveFormData);

        // Restore data on page load
        restoreFormData();

        // Clear data on form submission
        const taskForm = document.querySelector('.task-form');
        if (taskForm) {
            taskForm.addEventListener('submit', clearSavedData);
        }
    }
}

/**
 * Setup collaboration features
 */
function setupCollaborationFeatures() {
    // Add progress comparison animations
    const progressCards = document.querySelectorAll('.progress-card');
    progressCards.forEach(card => {
        card.addEventListener('mouseenter', function () {
            this.style.transform = 'translateY(-5px) scale(1.02)';
            this.style.boxShadow = '0 15px 40px rgba(0, 0, 0, 0.15)';
        });

        card.addEventListener('mouseleave', function () {
            this.style.transform = 'translateY(0) scale(1)';
            this.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.1)';
        });
    });

    // Add team stats animations
    const teamStats = document.querySelectorAll('.team-stat');
    teamStats.forEach(stat => {
        stat.addEventListener('mouseenter', function () {
            const icon = this.querySelector('i');
            if (icon) {
                icon.style.transform = 'scale(1.2) rotate(10deg)';
                icon.style.color = '#667eea';
            }
        });

        stat.addEventListener('mouseleave', function () {
            const icon = this.querySelector('i');
            if (icon) {
                icon.style.transform = 'scale(1) rotate(0deg)';
                icon.style.color = '#9f7aea';
            }
        });
    });

    // Add VS circle animation enhancement
    const vsCircle = document.querySelector('.vs-circle');
    if (vsCircle) {
        vsCircle.addEventListener('click', function () {
            this.style.animation = 'none';
            setTimeout(() => {
                this.style.animation = 'pulse 2s infinite, spin 1s ease-in-out';
            }, 10);

            setTimeout(() => {
                this.style.animation = 'pulse 2s infinite';
            }, 1000);
        });

        // Add spin animation
        if (!document.querySelector('#vs-animation-style')) {
            const style = document.createElement('style');
            style.id = 'vs-animation-style';
            style.textContent = `
                @keyframes spin {
                    from { transform: rotate(0deg) scale(1); }
                    50% { transform: rotate(180deg) scale(1.1); }
                    to { transform: rotate(360deg) scale(1); }
                }
            `;
            document.head.appendChild(style);
        }
    }
}

/**
 * Show save indicator
 */
function showSaveIndicator() {
    let indicator = document.querySelector('.save-indicator');
    if (!indicator) {
        indicator = document.createElement('div');
        indicator.className = 'save-indicator';
        indicator.innerHTML = '<i class="fas fa-save"></i> Draft saved';
        indicator.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(72, 187, 120, 0.9);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            z-index: 1000;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.3s ease;
        `;
        document.body.appendChild(indicator);
    }

    // Show indicator
    indicator.style.opacity = '1';
    indicator.style.transform = 'translateY(0)';

    // Hide after 2 seconds
    setTimeout(() => {
        indicator.style.opacity = '0';
        indicator.style.transform = 'translateY(20px)';
    }, 2000);
}

/**
 * Enhanced notification system
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;

    const icons = {
        success: 'fas fa-check-circle',
        error: 'fas fa-exclamation-circle',
        warning: 'fas fa-exclamation-triangle',
        info: 'fas fa-info-circle'
    };

    notification.innerHTML = `
        <i class="${icons[type] || icons.info}"></i>
        <span>${message}</span>
    `;

    // Style the notification
    Object.assign(notification.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        padding: '15px 20px',
        borderRadius: '12px',
        color: 'white',
        fontWeight: '600',
        zIndex: '1000',
        transform: 'translateX(100%)',
        transition: 'transform 0.3s ease',
        display: 'flex',
        alignItems: 'center',
        gap: '10px',
        minWidth: '250px',
        backdropFilter: 'blur(10px)'
    });

    // Set background color based on type
    const colors = {
        success: 'linear-gradient(135deg, #48bb78 0%, #38a169 100%)',
        error: 'linear-gradient(135deg, #f56565 0%, #e53e3e 100%)',
        warning: 'linear-gradient(135deg, #ed8936 0%, #dd6b20 100%)',
        info: 'linear-gradient(135deg, #4299e1 0%, #3182ce 100%)'
    };
    notification.style.background = colors[type] || colors.info;

    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);

    // Auto remove after 4 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 4000);
}

/**
 * Add smooth scrolling for better UX
 */
function smoothScrollTo(element) {
    element.scrollIntoView({
        behavior: 'smooth',
        block: 'center'
    });
}

/**
 * Format dates in a user-friendly way
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffTime = Math.abs(now - date);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays === 1) {
        return 'Yesterday';
    } else if (diffDays < 7) {
        return `${diffDays} days ago`;
    } else {
        return date.toLocaleDateString();
    }
}

/**
 * Add loading states to forms
 */
function addLoadingState(form) {
    const submitButton = form.querySelector('button[type="submit"]');
    if (submitButton) {
        const originalText = submitButton.innerHTML;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
        submitButton.disabled = true;

        // Reset after 3 seconds (fallback)
        setTimeout(() => {
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
        }, 3000);
    }
}

/**
 * Initialize progress comparison animations
 */
function initProgressComparison() {
    const myProgress = document.querySelector('.my-progress .progress-percentage');
    const otherProgress = document.querySelector('.other-progress .progress-percentage');

    if (myProgress && otherProgress) {
        const myValue = parseInt(myProgress.textContent);
        const otherValue = parseInt(otherProgress.textContent);

        // Add winner indicator
        if (myValue > otherValue) {
            myProgress.parentElement.classList.add('leading');
        } else if (otherValue > myValue) {
            otherProgress.parentElement.classList.add('leading');
        }
    }
}

// Initialize progress comparison on load
document.addEventListener('DOMContentLoaded', initProgressComparison);

// Export functions for potential use in other scripts
window.CollaborativeTracker = {
    showNotification,
    smoothScrollTo,
    formatDate,
    addLoadingState,
    initProgressComparison
};