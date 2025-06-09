// Sidebar functionality
const sidebarLinks = document.querySelectorAll('.sidebar .nav-link');
const submenuToggles = document.querySelectorAll('.sidebar .nav-link[data-bs-toggle="collapse"]');

function setActiveLink(link) {
    sidebarLinks.forEach(l => l.classList.remove('active'));
    link.classList.add('active');
}

let sidebarTransitioning = false;

submenuToggles.forEach(toggle => {
    toggle.addEventListener('click', function (e) {
        const sidebar = document.querySelector('.sidebar');
        const targetId = this.getAttribute('data-bs-target');
        const targetSubmenu = document.querySelector(targetId);

        if (sidebar.classList.contains('collapsed')) {
            if (sidebarTransitioning) {
                e.preventDefault();
                return;
            }
            sidebarTransitioning = true;
            sidebar.classList.remove('collapsed');
            const icon = document.getElementById('userSidebarToggleBtn').querySelector('i');
            icon.classList.add('fa-angle-left');
            icon.classList.remove('fa-angle-right');
            const handler = function (e2) {
                if (e2.propertyName === 'width') {
                    if (targetSubmenu) {
                        const collapseInstance = bootstrap.Collapse.getInstance(targetSubmenu) || new bootstrap.Collapse(targetSubmenu, { toggle: false });
                        collapseInstance.show();
                    }
                    sidebar.removeEventListener('transitionend', handler);
                    sidebarTransitioning = false;
                }
            };
            sidebar.addEventListener('transitionend', handler);
            e.preventDefault();
            return;
        }

        if (targetSubmenu) {
            const collapseInstance = bootstrap.Collapse.getInstance(targetSubmenu) || new bootstrap.Collapse(targetSubmenu, { toggle: false });
            // Close all other submenus except the one being toggled
            document.querySelectorAll('.sidebar .collapse.show').forEach(submenu => {
                if (submenu !== targetSubmenu) {
                    const otherCollapse = bootstrap.Collapse.getInstance(submenu) || new bootstrap.Collapse(submenu, { toggle: false });
                    otherCollapse.hide();
                }
            });
            // Toggle the clicked submenu (open if closed, close if open)
            collapseInstance.toggle();
        } else {
            console.error('Submenu target not found:', targetId);
        }
    });
});

// Sidebar toggle event listener setup for user dashboard
let userSidebarToggleHandler = null;
function setupUserSidebarToggle() {
    const sidebarToggleBtn = document.getElementById('userSidebarToggleBtn');
    if (sidebarToggleBtn) {
        if (userSidebarToggleHandler) {
            sidebarToggleBtn.removeEventListener('click', userSidebarToggleHandler);
        }
        userSidebarToggleHandler = function () {
            const sidebar = document.querySelector('.sidebar');
            const icon = this.querySelector('i');
            const header = document.querySelector('header');
            const mainContent = document.querySelector('.main-content');
            const willCollapse = !sidebar.classList.contains('collapsed');
            sidebar.classList.toggle('collapsed');
            icon.classList.toggle('fa-angle-left');
            icon.classList.toggle('fa-angle-right');
            if (sidebar.classList.contains('collapsed')) {
                if (header) header.classList.add('sidebar-collapsed');
                if (mainContent) mainContent.classList.add('sidebar-collapsed');
            } else {
                if (header) header.classList.remove('sidebar-collapsed');
                if (mainContent) mainContent.classList.remove('sidebar-collapsed');
            }
            if (willCollapse) {
                document.querySelectorAll('.sidebar .collapse.show').forEach(submenu => {
                    const collapseInstance = bootstrap.Collapse.getInstance(submenu) || new bootstrap.Collapse(submenu, { toggle: false });
                    submenu.style.display = 'none';
                    collapseInstance.hide();
                });
                setTimeout(() => {
                    document.querySelectorAll('.sidebar .collapse').forEach(submenu => {
                        submenu.style.display = '';
                    });
                }, 350);
            } else {
                document.querySelectorAll('.sidebar .collapse.show').forEach(submenu => {
                    const collapseInstance = bootstrap.Collapse.getInstance(submenu) || new bootstrap.Collapse(submenu, { toggle: false });
                    collapseInstance.hide();
                });
            }
        };
        sidebarToggleBtn.addEventListener('click', userSidebarToggleHandler);
    }
}

// Logout loading animation
const logoutBtn = document.getElementById('modal-logout-btn');
if (logoutBtn) {
    logoutBtn.addEventListener('click', function () {
        const button = this;
        const cancelBtn = document.querySelector('.btn-no');
        button.innerHTML = '<span class="spinner"></span>';
        button.classList.add('btn-loading');
        button.disabled = true;
        if (cancelBtn) cancelBtn.disabled = true;
        setTimeout(() => {
            try {
                window.location.href = 'Logout.php';
            } catch (e) {
                button.innerHTML = 'Logout';
                button.classList.remove('btn-loading');
                button.disabled = false;
                if (cancelBtn) cancelBtn.disabled = false;
            }
        }, 1000);
    });
}

// Add spinner CSS if not present
if (!document.getElementById('logout-spinner-style')) {
    const style = document.createElement('style');
    style.id = 'logout-spinner-style';
    style.innerHTML = `.btn-loading .spinner { display: inline-block; width: 16px; height: 16px; border: 2px solid #fff; border-top: 2px solid transparent; border-radius: 50%; animation: spin 1s linear infinite; margin-right: 5px; } @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } } .btn-loading .btn-text { display: none; }`;
    document.head.appendChild(style);
}

// Load page function for dynamic content loading
function loadPage(url, activeSubmenuId) {
    const mainContent = document.getElementById('main-content');
    if (!mainContent) {
        console.error('Main content element not found');
        return;
    }

    mainContent.innerHTML = `<div class="d-flex justify-content-center align-items-center" style="height: 200px;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
    </div>`;

    fetch(url, { credentials: 'same-origin' })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.text();
        })
        .then(html => {
            if (html.includes('name="username"') && html.includes('name="password"')) {
                window.location.href = 'Login.php';
                return;
            }
            mainContent.innerHTML = html;
            // Re-initialize event listeners for dynamically loaded content
            attachEventListeners();
            setupUserSidebarToggle();
            setupSidebarActiveLinks();
            // Reattach attendance form listener if this is UserAttendance.php
            if (url.includes('UserAttendance.php')) {
                setupAttendanceFormAjax();
            }
            // Reattach period dropdown listener if this is UserAttendanceReport.php
            if (url.includes('UserAttendanceReport.php')) {
                setupPeriodDropdownListener();
            }
            // Reattach feedback form listener if this is UserFeedback.php
            if (url.includes('UserFeedback.php')) {
                setupFeedbackFormAjax();
            }
            // Ensure sidebar-collapsed class is in sync after load
            const sidebar = document.querySelector('.sidebar');
            const header = document.querySelector('header');
            const mainContentDiv = document.querySelector('.main-content');
            if (sidebar && sidebar.classList.contains('collapsed')) {
                if (header) header.classList.add('sidebar-collapsed');
                if (mainContentDiv) mainContentDiv.classList.add('sidebar-collapsed');
            } else {
                if (header) header.classList.remove('sidebar-collapsed');
                if (mainContentDiv) mainContentDiv.classList.remove('sidebar-collapsed');
            }
        })
        .catch(error => {
            mainContent.innerHTML = `<div class="alert alert-danger">Error loading content: ${error.message}</div>`;
            console.error('Error loading page:', error);
        });
}

// Function to attach event listeners to dynamically loaded content
let logoutHandler = null;
function attachEventListeners() {
    const form = document.getElementById('user-settings-form');
    const alertDiv = document.getElementById('settings-alert');
    const profileImg = document.querySelector('.card-body img');
    const headerProfileImg = document.querySelector('#profileDropdown');
    const cameraBtn = document.getElementById('camera-btn');
    const fileInput = document.getElementById('profile_image');

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(form);
            fetch('UserUpdateProfile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alertDiv.innerHTML = data.message;
                alertDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
                if (data.success && data.profile_image) {
                    const newImageUrl = data.profile_image + '?t=' + new Date().getTime();
                    if (profileImg) profileImg.src = newImageUrl;
                    if (headerProfileImg) headerProfileImg.src = newImageUrl;
                }
                setTimeout(() => { alertDiv.innerHTML = ''; }, 3000);
            })
            .catch(error => {
                alertDiv.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
                setTimeout(() => { alertDiv.innerHTML = ''; }, 3000);
            });
        });
    }

    if (cameraBtn && fileInput) {
        cameraBtn.addEventListener('click', function() {
            fileInput.click();
        });

        fileInput.addEventListener('change', function(e) {
            if (fileInput.files && fileInput.files[0]) {
                const reader = new FileReader();
                reader.onload = function(ev) {
                    if (profileImg) profileImg.src = ev.target.result;
                };
                reader.readAsDataURL(fileInput.files[0]);
            }
        });
    }

    if (logoutBtn) {
        if (logoutHandler) {
            logoutBtn.removeEventListener('click', logoutHandler);
        }
        logoutHandler = function () {
            const button = this;
            const cancelBtn = document.querySelector('.btn-no');
            button.innerHTML = '<span class="spinner"></span>';
            button.classList.add('btn-loading');
            button.disabled = true;
            if (cancelBtn) cancelBtn.disabled = true;
            setTimeout(() => {
                try {
                    window.location.href = 'Logout.php';
                } catch (e) {
                    button.innerHTML = 'Logout';
                    button.classList.remove('btn-loading');
                    button.disabled = false;
                    if (cancelBtn) cancelBtn.disabled = false;
                }
            }, 1000);
        };
        logoutBtn.addEventListener('click', logoutHandler);
    }

    setupUserSidebarToggle();
    setupSidebarActiveLinks();
}

// Ensure only the current sidebar link is active
function setupSidebarActiveLinks() {
    const allLinks = document.querySelectorAll('.sidebar .nav-link');
    allLinks.forEach(link => {
        link.addEventListener('click', function () {
            allLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });
    document.querySelectorAll('.sidebar .collapse .nav-link').forEach(link => {
        link.addEventListener('click', function () {
            allLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    if (window.location.pathname.includes('UserDashboard.php')) {
        attachEventListeners();
        setupUserSidebarToggle();
        setupSidebarActiveLinks();
    }
    var notificationDropdown = document.getElementById('notificationDropdown');
    var badge = document.getElementById('notificationCount');
    if (notificationDropdown && badge) {
        notificationDropdown.addEventListener('click', function() {
            badge.style.display = 'none';
            localStorage.setItem('notificationsSeen', 'true');
        });
    }
    // Use event delegation for notification-item clicks
    document.body.addEventListener('click', function(e) {
        var item = e.target.closest('.notification-item');
        if (item && item.getAttribute('data-link')) {
            var link = item.getAttribute('data-link');
            if (link && typeof loadPage === 'function') {
                loadPage(link);
            }
        }
    });
});

window.loadPage = loadPage;

function setupAttendanceFormAjax() {
    const form = document.getElementById('create-attendance-form');
    const alertDiv = document.getElementById('attendance-alert');
    if (form && !form.dataset.listenerAttached) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(form);
            fetch('AttendanceCreate.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`Network error: ${response.status} - ${text}`);
                    });
                }
                return response.text().then(text => {
                    if (!text) {
                        throw new Error('Empty response received');
                    }
                    try {
                        return JSON.parse(text);
                    } catch (error) {
                        throw new Error(`Invalid JSON: ${text}`);
                    }
                });
            })
            .then(data => {
                if (data.success) {
                    alertDiv.innerHTML = '<div class="alert alert-success" style="border-left: 5px solid #000080; background-color: #e6f4ea;">' + data.message + '</div>';
                    form.reset();
                } else {
                    alertDiv.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
                }
                alertDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
                setTimeout(() => { alertDiv.innerHTML = ''; }, 2500);
            })
            .catch(error => {
                alertDiv.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
                setTimeout(() => { alertDiv.innerHTML = ''; }, 2500);
                console.error('Fetch error:', error);
            });
        });
        form.dataset.listenerAttached = 'true'; // Prevent duplicate listeners
    }
}

function setupPeriodDropdownListener() {
    const periodSelect = document.getElementById('period');
    if (periodSelect && !periodSelect.dataset.listenerAttached) {
        periodSelect.addEventListener('change', function() {
            const selectedPeriod = this.value;
            loadPage(`UserAttendanceReport.php?period=${selectedPeriod}`, 'attendance-submenu');
        });
        periodSelect.dataset.listenerAttached = 'true'; // Prevent duplicate listeners
    }
}

function setupFeedbackFormAjax() {
    const form = document.getElementById('feedback-form');
    const alertContainer = document.getElementById('feedback-alert-container');
    const ratingValidation = document.getElementById('rating-validation-message');
    if (form && !form.dataset.listenerAttached) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const message = form.querySelector('textarea[name="message"]').value.trim();
            let hasError = false;
            if (!message) {
                alertContainer.innerHTML = '<div class="alert alert-danger">Please fill in all required fields.</div>';
                setTimeout(() => { alertContainer.innerHTML = ''; }, 2500);
                hasError = true;
            }
            if (ratingValidation) {
                ratingValidation.style.display = 'none';
            }
            if (hasError) return;
            const formData = new FormData(form);
            fetch('UserFeedbackSubmit.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`Network error: ${response.status} - ${text}`);
                    });
                }
                return response.text().then(text => {
                    if (!text) {
                        throw new Error('Empty response received');
                    }
                    try {
                        return JSON.parse(text);
                    } catch (error) {
                        throw new Error(`Invalid JSON: ${text}`);
                    }
                });
            })
            .then(data => {
                if (data.success) {
                    alertContainer.innerHTML = '<div class="alert alert-success" style="border-left: 5px solid #000080; background-color: #e6f4ea;">' + data.message + '</div>';
                    form.reset();
                } else {
                    alertContainer.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
                }
                alertContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
                setTimeout(() => { alertContainer.innerHTML = ''; }, 2500);
            })
            .catch(error => {
                alertContainer.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
                setTimeout(() => { alertContainer.innerHTML = ''; }, 2500);
                console.error('Fetch error:', error);
            });
        });
        form.dataset.listenerAttached = 'true'; // Prevent duplicate listeners
    }
}

// Mobile menu toggle functionality
function setupMobileMenu() {
    const mobileMenuToggle = document.createElement('button');
    mobileMenuToggle.className = 'mobile-menu-toggle';
    mobileMenuToggle.innerHTML = '<i class="fas fa-bars"></i>';
    document.body.appendChild(mobileMenuToggle);

    const sidebar = document.querySelector('.sidebar');
    const overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1040;
        display: none;
    `;
    document.body.appendChild(overlay);

    mobileMenuToggle.addEventListener('click', () => {
        sidebar.classList.toggle('show');
        overlay.style.display = sidebar.classList.contains('show') ? 'block' : 'none';
    });

    overlay.addEventListener('click', () => {
        sidebar.classList.remove('show');
        overlay.style.display = 'none';
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 768 && 
            !sidebar.contains(e.target) && 
            !mobileMenuToggle.contains(e.target) && 
            sidebar.classList.contains('show')) {
            sidebar.classList.remove('show');
            overlay.style.display = 'none';
        }
    });
}

// Initialize mobile menu
document.addEventListener('DOMContentLoaded', () => {
    setupMobileMenu();
    // ... existing initialization code ...
});