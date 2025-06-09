document.addEventListener('DOMContentLoaded', function () {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    // Sidebar functionality
    const sidebarLinks = document.querySelectorAll('.sidebar .nav-link');
    const submenuToggles = document.querySelectorAll('.sidebar .nav-link[data-bs-toggle="collapse"]');

    // Function to set active state
    function setActiveLink(link) {
        sidebarLinks.forEach(l => l.classList.remove('active'));
        link.classList.add('active');
    }

    let sidebarTransitioning = false;
    let sidebarToggleHandler = null;

    // Handle submenu toggles
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
                const icon = document.getElementById('sidebarToggleBtn')?.querySelector('i');
                if (icon) {
                icon.classList.add('fa-angle-left');
                icon.classList.remove('fa-angle-right');
                }
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
                const collapseInstance = bootstrap.Collapse.getInstance(targetSubmenu) || new bootstrap.Collapse(targetSubmenu, { toggle: true });
                collapseInstance.toggle();

                document.querySelectorAll('.sidebar .collapse.show').forEach(submenu => {
                    if (submenu.id !== targetId.substring(1)) {
                        const otherCollapse = bootstrap.Collapse.getInstance(submenu) || new bootstrap.Collapse(submenu, { toggle: false });
                        otherCollapse.hide();
                    }
                });
            } else {
                console.error('Submenu target not found:', targetId);
            }
        });
    });

    // Sidebar toggle
    function setupSidebarToggle() {
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
    if (sidebarToggleBtn) {
            // Remove previous event listener if any
            if (sidebarToggleHandler) {
                sidebarToggleBtn.removeEventListener('click', sidebarToggleHandler);
            }
            sidebarToggleHandler = function () {
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
            sidebarToggleBtn.addEventListener('click', sidebarToggleHandler);
        }
    }

    // Handle regular navigation links
    sidebarLinks.forEach(link => {
        if (
            !link.hasAttribute('data-bs-toggle') &&
            !link.getAttribute('onclick')?.includes('loadPage') &&
            !link.getAttribute('onclick')?.includes('refreshMainContent')
        ) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const mainContent = document.getElementById('main-content');
                if (mainContent) {
                    mainContent.innerHTML = `
                        <div class="alert alert-danger m-3">
                            <h4 class="alert-heading">Unauthorized Access</h4>
                            <p>You are not logged in or do not have permission to access this page.</p>
                            <hr>
                            <p class="mb-0">Please <a href="Login.php">log in</a> or contact support.</p>
                        </div>
                    `;
                    showAlert('You are not authorized to access this page.', 'danger');
                } else {
                    console.error('Main content element not found');
                }
            });
        }
    });

    // Function to show alerts
    function showAlert(message, type, containerId = 'alert-container') {
        const alertContainer = document.getElementById(containerId) || document.getElementById('main-content');
        if (!alertContainer) return;
        alertContainer.innerHTML = ''; // Clear previous alerts
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.role = 'alert';
        alert.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`;
        alertContainer.appendChild(alert);
        setTimeout(() => alert.remove(), 3000);
    }

    // Function to clean up modal backdrops
    function cleanupModalBackdrop() {
        document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    }

    // Function to format date for datetime-local input
    function formatDateForInput(dateStr) {
        if (!dateStr) return '';
        try {
            const date = new Date(dateStr);
            return date.toISOString().slice(0, 16);
        } catch (e) {
            console.error('Invalid date format:', dateStr);
            return '';
        }
    }

    // Function to set sidebar state
    function setSidebarState(activeSubmenuId) {
        document.querySelectorAll('.sidebar .nav-link').forEach(link => link.classList.remove('active'));
        const submenuLink = document.querySelector(`[data-bs-target="#${activeSubmenuId}"]`);
        if (submenuLink) {
            submenuLink.classList.add('active');
            const submenu = document.getElementById(activeSubmenuId);
            if (submenu) {
                const collapseInstance = bootstrap.Collapse.getInstance(submenu) || new bootstrap.Collapse(submenu, { toggle: false });
                collapseInstance.show();
            }
        }
        const submenuItem = document.querySelector(`#${activeSubmenuId} .nav-link[onclick*="${activeSubmenuId}"]`);
        if (submenuItem) {
            document.querySelectorAll(`#${activeSubmenuId} .nav-link`).forEach(link => link.classList.remove('active'));
            submenuItem.classList.add('active');
        }
    }

    // Load page function
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

        fetch(url, {
            method: 'GET',
            credentials: 'same-origin', // Ensure session cookies are sent
            headers: {
                'Accept': 'text/html',
                'X-Requested-With': 'XMLHttpRequest' // Indicate this is an AJAX request
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(html => {
                // Check if the response contains a login page (indicating session expired)
                if (html.includes('name="username"') && html.includes('name="password"')) {
                    mainContent.innerHTML = `
                        <div class="alert alert-danger m-3">
                            <h4 class="alert-heading">Session Expired</h4>
                            <p>Your session has expired. Please log in again.</p>
                            <hr>
                            <p class="mb-0">Redirecting to login page...</p>
                        </div>
                    `;
                    setTimeout(() => {
                    window.location.href = 'Login.php';
                    }, 2000);
                    return;
                }
                mainContent.innerHTML = html;
                console.log('Content loaded for:', url);

                const tooltips = mainContent.querySelectorAll('[data-bs-toggle="tooltip"]');
                tooltips.forEach(tooltip => new bootstrap.Tooltip(tooltip));

                attachEventListeners();
                setupSidebarToggle();
                setupSidebarActiveLinks();
                if (activeSubmenuId) setSidebarState(activeSubmenuId);
            })
            .catch(error => {
                mainContent.innerHTML = `<div class="alert alert-danger">Error loading content: ${error.message}</div>`;
                console.error('Error loading page:', error);
            });
    }

    // Handle query parameters
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('load')) loadPage(urlParams.get('load'), null);

    window.loadPage = loadPage;

    // Function to attach event listeners
    function attachEventListeners() {
        const mainContent = document.getElementById('main-content');
        if (!mainContent) return;

        // Handle various button clicks
        mainContent.addEventListener('click', function (e) {
            // Update Attendance
            const updateAttendanceButton = e.target.closest('.edit-attendance');
            if (updateAttendanceButton) {
                const attendanceId = updateAttendanceButton.getAttribute('data-id');
                if (!attendanceId || isNaN(parseInt(attendanceId, 10))) {
                    showAlert('Invalid attendance ID.', 'danger');
                    return;
                }

                const checkInTime = updateAttendanceButton.getAttribute('data-check-in');
                const checkOutTime = updateAttendanceButton.getAttribute('data-check-out');
                const status = updateAttendanceButton.getAttribute('data-status');
                const userId = updateAttendanceButton.getAttribute('data-user-id');
                const eventId = updateAttendanceButton.getAttribute('data-event-id');

                const attendanceIdInput = document.getElementById('attendanceId');
                const userIdInput = document.getElementById('update_user_id');
                const eventIdInput = document.getElementById('update_event_id');
                const checkInInput = document.getElementById('update_check_in_time');
                const checkOutInput = document.getElementById('update_check_out_time');
                const statusSelect = document.getElementById('update_status');

                if (attendanceIdInput && userIdInput && eventIdInput && checkInInput && checkOutInput && statusSelect) {
                    attendanceIdInput.value = attendanceId;
                    userIdInput.value = userId;
                    eventIdInput.value = eventId;
                    checkInInput.value = checkInTime ? checkInTime.replace(' ', 'T') : '';
                    checkOutInput.value = checkOutTime ? checkOutTime.replace(' ', 'T') : '';
                    statusSelect.value = status || 'Present';
                    cleanupModalBackdrop();
                    new bootstrap.Modal(document.getElementById('updateAttendanceModal')).show();
                } else {
                    showAlert('Error initializing update form.', 'danger');
                }
                return;
            }

            // Delete Attendance
            const deleteAttendanceButton = e.target.closest('.delete-attendance');
            if (deleteAttendanceButton) {
                const attendanceId = deleteAttendanceButton.getAttribute('data-id');
                if (!attendanceId || isNaN(parseInt(attendanceId, 10))) {
                    showAlert('Invalid or missing attendance ID.', 'danger');
                    return;
                }
                const parsedAttendanceId = parseInt(attendanceId, 10);

                const modalTitle = document.getElementById('deleteAttendanceModalLabel');
                const confirmDeleteBtn = document.getElementById('confirmDeleteAttendanceBtn');
                const deleteModal = document.getElementById('deleteAttendanceModal');

                if (!modalTitle || !confirmDeleteBtn || !deleteModal) {
                    showAlert('Error initializing delete modal.', 'danger');
                    return;
                }

                modalTitle.textContent = `Are you sure you want to delete attendance record ID: ${parsedAttendanceId}?`;
                confirmDeleteBtn.dataset.attendanceId = parsedAttendanceId.toString();
                cleanupModalBackdrop();
                new bootstrap.Modal(deleteModal).show();
                    return;
            }

            // Edit Promotion
            const editPromotionBtn = e.target.closest('.edit-promotion');
            if (editPromotionBtn) {
                e.preventDefault();
                const promotionData = JSON.parse(editPromotionBtn.getAttribute('data-promotion'));
                document.getElementById('editPromotionId').value = promotionData.promotion_id;
                document.getElementById('editPromotionTitle').value = promotionData.title;
                document.getElementById('editPromotionDescription').value = promotionData.description;
                document.getElementById('editPromotionPrice').value = promotionData.price;
                cleanupModalBackdrop();
                new bootstrap.Modal(document.getElementById('editPromotionModal')).show();
                return;
            }

            // Delete Promotion
            const deletePromotionBtn = e.target.closest('.delete-promotion');
            if (deletePromotionBtn) {
                e.preventDefault();
                const promotionId = deletePromotionBtn.getAttribute('data-promotion-id');
                document.getElementById('deletePromotionId').value = promotionId;
                cleanupModalBackdrop();
                new bootstrap.Modal(document.getElementById('deletePromotionModal')).show();
                return;
            }

            // Edit Event
            const editEventBtn = e.target.closest('.edit-event');
            if (editEventBtn) {
                e.preventDefault();
                const eventData = JSON.parse(editEventBtn.getAttribute('data-event'));
                document.getElementById('editEventId').value = eventData.event_id;
                document.getElementById('editEventTitle').value = eventData.title;
                document.getElementById('editEventDescription').value = eventData.description || '';
                document.getElementById('editEventStartDate').value = formatDateForInput(eventData.start_date);
                document.getElementById('editEventEndDate').value = formatDateForInput(eventData.end_date);
                document.getElementById('editEventLocation').value = eventData.location || '';
                cleanupModalBackdrop();
                new bootstrap.Modal(document.getElementById('editEventModal')).show();
                return;
            }

            // Delete Event
            const deleteEventBtn = e.target.closest('.delete-event');
            if (deleteEventBtn) {
                e.preventDefault();
                const eventId = deleteEventBtn.getAttribute('data-event-id');
                const eventTitle = deleteEventBtn.getAttribute('data-event-title');
                document.getElementById('deleteEventId').value = eventId;
                document.getElementById('deleteEventTitle').textContent = eventTitle;
                cleanupModalBackdrop();
                new bootstrap.Modal(document.getElementById('deleteEventModal')).show();
                return;
            }

            // Edit Announcement
            const editAnnouncementBtn = e.target.closest('.btn-edit-announcement');
            if (editAnnouncementBtn) {
                e.preventDefault();
                const id = editAnnouncementBtn.getAttribute('data-id');
                const message = editAnnouncementBtn.getAttribute('data-message');
                const createdBy = editAnnouncementBtn.getAttribute('data-created_by');

                const editModal = document.getElementById('editAnnouncementModal');
                if (!editModal) {
                    showAlert('Edit modal not found.', 'danger', 'announcement-alert-container');
                    return;
                }

                document.getElementById('editAnnouncementId').value = id;
                document.getElementById('editMessage').value = message;
                document.getElementById('editCreatedBy').value = createdBy;
                cleanupModalBackdrop();
                new bootstrap.Modal(editModal).show();
                return;
            }

            // Delete Announcement
            const deleteAnnouncementBtn = e.target.closest('.btn-delete-announcement');
            if (deleteAnnouncementBtn) {
                e.preventDefault();
                window.announcementToDeleteId = deleteAnnouncementBtn.getAttribute('data-id');
                const deleteModal = document.getElementById('deleteAnnouncementModal');
                if (!deleteModal) {
                    showAlert('Delete modal not found.', 'danger', 'announcement-alert-container');
                    return;
                }
                cleanupModalBackdrop();
                new bootstrap.Modal(deleteModal).show();
                return;
            }
        });

        // Announcement creation form submission
        const announcementForm = document.getElementById('announcement-create-form');
        if (announcementForm) {
            announcementForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(this);
                const message = formData.get('message').trim();

                if (message.length < 10) {
                    showAlert('Announcement message must be at least 10 characters long.', 'danger', 'announcement-alert-container');
                    return;
                }

                fetch('CreateAnnouncement.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showAlert('Announcement Created!', 'success', 'announcement-alert-container');
                            announcementForm.reset();
                        } else {
                            showAlert(data.message, 'danger', 'announcement-alert-container');
                        }
                    })
                    .catch(error => {
                        showAlert('Error creating announcement: ' + error.message, 'danger', 'announcement-alert-container');
                    });
            });
        }

        // Event creation form submission
        const createForm = document.getElementById('event-create-form');
        if (createForm) {
            createForm.addEventListener('submit', function (e) {
                e.preventDefault();
                var form = e.target;
                var formData = new FormData(form);

                var startDate = new Date(form.start_date.value);
                var endDate = new Date(form.end_date.value);
                if (endDate <= startDate) {
                    document.getElementById('alert-container').innerHTML = `
                        <div class="alert alert-danger text-center" role="alert">
                            End date must be after start date.
                        </div>
                    `;
                    return;
                }

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    var alertContainer = document.getElementById('alert-container');
                    if (data.success) {
                        alertContainer.innerHTML = `
                            <div class="alert alert-success text-center" role="alert">
                                ${data.message}
                            </div>
                        `;
                        form.reset();
                        // Reload the events list after successful creation
                        setTimeout(() => {
                            loadPage('AdminViewEvent.php', 'events-submenu');
                        }, 1500);
                    } else {
                        alertContainer.innerHTML = `
                            <div class="alert alert-danger text-center" role="alert">
                                ${data.message}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    document.getElementById('alert-container').innerHTML = `
                        <div class="alert alert-danger text-center" role="alert">
                            Error: ${error.message}
                        </div>
                    `;
                });
            });
        }

        // Event update form submission
        const updateForm = document.getElementById('event-update-form');
        if (updateForm) {
            updateForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(this);
                const startDate = new Date(formData.get('eventStartDate'));
                const endDate = new Date(formData.get('eventEndDate'));

                if (endDate <= startDate) {
                    showAlert('End date must be after start date.', 'danger');
                    return;
                }

                fetch('AdminUpdateEvent.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        const modalEl = document.getElementById('updateEventModal');
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();

                        if (data.success) {
                            modalEl.addEventListener('hidden.bs.modal', function handler() {
                                loadPage('AdminViewEvent.php', 'events-submenu');
                                cleanupModalBackdrop();
                                modalEl.removeEventListener('hidden.bs.modal', handler);
                            });
                            showAlert(data.message, 'success');
                        } else {
                            showAlert(data.message, 'danger');
                            cleanupModalBackdrop();
                        }
                    })
                    .catch(error => {
                        showAlert('Error updating event: ' + error.message, 'danger');
                        cleanupModalBackdrop();
                    });
            });
        }

        // Attendance update form submission
        const updateAttendanceForm = document.getElementById('attendance-update-form');
        if (updateAttendanceForm) {
            updateAttendanceForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(this);
                const checkInTime = formData.get('check_in_time');
                const checkOutTime = formData.get('check_out_time');

                if (checkInTime && checkOutTime) {
                    const checkInDate = new Date(checkInTime);
                    const checkOutDate = new Date(checkOutTime);
                    if (checkOutDate <= checkInDate) {
                        showAlert('Check-out time must be after check-in time.', 'danger');
                        return;
                    }
                }

                fetch('AttendanceUpdate.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('updateAttendanceModal'));
                        modal.hide();
                        cleanupModalBackdrop();

                        // Remove all existing alerts inside #main-content before showing a new one
                        const mainContent = document.getElementById('main-content');
                        if (mainContent) {
                            mainContent.querySelectorAll('.alert').forEach(alert => alert.remove());
                        }
                        document.querySelectorAll('.alert-success, .alert-danger').forEach(alert => alert.remove());
                        if (data.success) {
                            // Only show one success alert for 'Attendance Record Deleted!!!'
                            let alreadyShown = false;
                            document.querySelectorAll('.alert-success').forEach(alert => {
                                if (alert.textContent.includes('Attendance Record Deleted!!!')) {
                                    alreadyShown = true;
                                }
                            });
                            if (!alreadyShown) {
                                showAlert(data.message, 'success');
                            }
                            // Reload the attendance records page after successful deletion
                            loadPage('AttendanceRecords.php', 'attendance-submenu');
                        } else {
                            showAlert(data.message, 'danger');
                        }
                    })
                    .catch(error => {
                        showAlert('Error updating attendance record: ' + error.message, 'danger');
                        cleanupModalBackdrop();
                    });
            });
        }

        // Populate user and event selects
        const userSelect = document.getElementById('update_user_id');
        const eventSelect = document.getElementById('update_event_id');
        const usersDataElement = document.getElementById('users-data');
        const eventsDataElement = document.getElementById('events-data');

        if (userSelect && usersDataElement) {
            const users = JSON.parse(usersDataElement.textContent);
            userSelect.innerHTML = users.length > 0
                ? users.map(user => `<option value="${user.user_id}">${user.student_id}</option>`).join('')
                : '<option value="">No users available</option>';
        }

        if (eventSelect && eventsDataElement) {
            const events = JSON.parse(eventsDataElement.textContent);
            eventSelect.innerHTML = events.length > 0
                ? events.map(event => `<option value="${event.event_id}">${event.title}</option>`).join('')
                : '<option value="">No events available</option>';
        }

        // Modal cleanup
        const deleteAttendanceModal = document.getElementById('deleteAttendanceModal');
        if (deleteAttendanceModal) {
            deleteAttendanceModal.addEventListener('hidden.bs.modal', cleanupModalBackdrop);
        }

        const updateAttendanceModal = document.getElementById('updateAttendanceModal');
        if (updateAttendanceModal) {
            updateAttendanceModal.addEventListener('hidden.bs.modal', cleanupModalBackdrop);
            updateAttendanceModal.addEventListener('show.bs.modal', cleanupModalBackdrop);
        }

        const deleteEventModal = document.getElementById('deleteEventModal');
        if (deleteEventModal) {
            deleteEventModal.addEventListener('hidden.bs.modal', cleanupModalBackdrop);
            deleteEventModal.addEventListener('show.bs.modal', cleanupModalBackdrop);
        }

        const updateEventModal = document.getElementById('updateEventModal');
        if (updateEventModal) {
            updateEventModal.addEventListener('hidden.bs.modal', cleanupModalBackdrop);
            updateEventModal.addEventListener('show.bs.modal', cleanupModalBackdrop);
        }

        // Event card delete button
        mainContent.querySelectorAll('.delete-event').forEach(button => {
            button.addEventListener('click', function () {
                const eventId = this.getAttribute('data-event-id');
                const eventTitle = this.getAttribute('data-event-title');
                document.getElementById('deleteEventId').value = eventId;
                document.getElementById('deleteEventTitle').textContent = eventTitle;
            });
        });

        // Confirm delete event
        const confirmDeleteEventBtn = document.getElementById('confirmDeleteEventBtn');
        if (confirmDeleteEventBtn) {
            confirmDeleteEventBtn.onclick = function () {
                const eventId = document.getElementById('deleteEventId').value;
                const eventCard = document.querySelector(`.event-card[data-event-id="${eventId}"]`);
                fetch('AdminDeleteEvent.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'event_id=' + encodeURIComponent(eventId)
                })
                    .then(response => response.json())
                    .then(data => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteEventModal'));
                        modal.hide();
                        if (data.success) {
                            showAlert('Event deleted successfully!', 'success');
                            if (eventCard) eventCard.remove();
                            if (document.querySelectorAll('.event-card').length === 0) {
                                document.getElementById('event-list').innerHTML = `
                                <div class="text-center p-4">
                                    <i class="fas fa-calendar-times fa-3x mb-3 text-muted"></i>
                                    <h3>No Events Found</h3>
                                    <p>There are no events scheduled at the moment.</p>
                                </div>
                            `;
                            }
                        } else {
                            showAlert(data.message || 'Error deleting event', 'danger');
                        }
                    })
                    .catch(error => {
                        showAlert('Error deleting event: ' + error.message, 'danger');
                    });
            };
        }

        // Edit event form submission
        const editEventForm = document.getElementById('editEventForm');
        if (editEventForm) {
            editEventForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(this);
                const eventId = document.getElementById('editEventId').value;

                fetch('AdminUpdateEvent.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('editEventModal'));
                        if (modal) modal.hide();
                        cleanupModalBackdrop();
                        if (data.success) {
                            const card = document.querySelector(`.event-card[data-event-id="${eventId}"]`);
                            if (card) {
                                card.querySelector('.card-title').textContent = document.getElementById('editEventTitle').value;
                                card.querySelector('.fa-calendar').parentNode.innerHTML = `<i class="fa fa-calendar me-2"></i> ${new Date(document.getElementById('editEventStartDate').value).toLocaleDateString()} - ${new Date(document.getElementById('editEventEndDate').value).toLocaleDateString()}`;
                                card.querySelector('.fa-map-marker-alt').parentNode.innerHTML = `<i class="fa fa-map-marker-alt me-2"></i> ${document.getElementById('editEventLocation').value}`;
                                card.querySelector('.fa-info-circle').parentNode.innerHTML = `<i class="fa fa-info-circle me-2"></i> ${document.getElementById('editEventDescription').value}`;
                            }
                        } else {
                            showAlert(data.message || 'Error updating event', 'danger');
                        }
                    })
                    .catch(error => {
                        showAlert('Error updating event: ' + error.message, 'danger');
                        cleanupModalBackdrop();
                    });
            });
        }

        // Promotion creation form submission
        const promotionForm = document.getElementById('promotion-create-form');
        if (promotionForm) {
            promotionForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(this);

                const startDate = new Date(formData.get('start_date'));
                const endDate = new Date(formData.get('end_date'));
                if (endDate <= startDate) {
                    showAlert('End date must be after start date.', 'danger', 'promotion-alert-container');
                    return;
                }

                fetch('AdminCreatePromotion.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showAlert('Promotion created!', 'success', 'promotion-alert-container');
                            promotionForm.reset();
                        } else {
                            showAlert(data.message, 'danger', 'promotion-alert-container');
                        }
                    })
                    .catch(error => {
                        showAlert('Error creating promotion: ' + error.message, 'danger', 'promotion-alert-container');
                    });
            });
        }

        // Edit promotion form submission
        const editPromotionForm = document.getElementById('editPromotionForm');
        if (editPromotionForm) {
            editPromotionForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const submitBtn = editPromotionForm.querySelector('button[type="submit"]');
                submitBtn.disabled = true;

                const formData = new FormData(this);
                const promotionId = document.getElementById('editPromotionId').value;
                if (!promotionId) {
                    showAlert('Invalid promotion ID.', 'danger', 'promotion-alert-container');
                    submitBtn.disabled = false;
                    return;
                }
                fetch('PromotionUpdate.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        const modalEl = document.getElementById('editPromotionModal');
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();

                        if (data.success) {
                            modalEl.addEventListener('hidden.bs.modal', function handler() {
                                loadPage('AdminViewPromotions.php', 'promotions-submenu');
                                cleanupModalBackdrop();
                                modalEl.removeEventListener('hidden.bs.modal', handler);
                            });
                        } else {
                            const alertContainer = document.getElementById('promotion-alert-container');
                            alertContainer.innerHTML = `
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    ${data.message}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            `;
                            cleanupModalBackdrop();
                        }
                    })
                    .catch(error => {
                        const alertContainer = document.getElementById('promotion-alert-container');
                        alertContainer.innerHTML = `
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                Error updating promotion: ${error.message}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        `;
                        cleanupModalBackdrop();
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                    });
            });
        }

        // Confirm delete promotion
        const confirmDeletePromotionBtn = document.getElementById('confirmDeletePromotionBtn');
        if (confirmDeletePromotionBtn) {
            confirmDeletePromotionBtn.addEventListener('click', function () {
                const promotionId = document.getElementById('deletePromotionId').value;
                if (!promotionId) {
                    showAlert('Invalid promotion ID.', 'danger', 'promotion-alert-container');
                    return;
                }
                fetch('PromotionDelete.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'promotion_id=' + encodeURIComponent(promotionId)
                })
                    .then(response => response.json())
                    .then(data => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('deletePromotionModal'));
                        if (modal) modal.hide();
                        cleanupModalBackdrop();
                        const alertContainer = document.getElementById('promotion-alert-container');
                        if (alertContainer) {
                            alertContainer.innerHTML = `
                                <div class="alert alert-${data.success ? 'success' : 'danger'} alert-dismissible fade show" role="alert">
                                    ${data.message}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            `;
                        }
                        if (data.success) {
                            loadPage('AdminViewPromotions.php', 'promotions-submenu');
                        }
                    })
                    .catch(error => {
                        const alertContainer = document.getElementById('promotion-alert-container');
                        if (alertContainer) {
                            alertContainer.innerHTML = `
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    ${data.message || 'Error deleting promotion'}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            `;
                        }
                        cleanupModalBackdrop();
                    });
            });
        }

        // Promotion image zoom
        document.querySelectorAll('.promotion-zoom-img').forEach(img => {
            img.addEventListener('click', function() {
                document.getElementById('promotionImageZoom').src = this.src;
                document.getElementById('promotionImageTitle').textContent = this.getAttribute('data-title');
                document.getElementById('promotionImageDetails').innerHTML = `
                    <strong>Description:</strong> ${this.getAttribute('data-description')}<br>
                    <strong>Price:</strong> ₱${this.getAttribute('data-price')}<br>
                    <strong>Location:</strong> ${this.getAttribute('data-location')}
                `;
                new bootstrap.Modal(document.getElementById('promotionImageModal')).show();
            });
        });

        // Only attach the event listener once
        const confirmDeleteAttendanceBtn = document.getElementById('confirmDeleteAttendanceBtn');
        if (confirmDeleteAttendanceBtn && !confirmDeleteAttendanceBtn.dataset.listenerAttached) {
            confirmDeleteAttendanceBtn.addEventListener('click', function () {
                const attendanceId = this.dataset.attendanceId;
                if (!attendanceId || isNaN(parseInt(attendanceId, 10))) {
                    showAlert('Invalid or missing attendance ID.', 'danger');
                    return;
                }

                const formData = new FormData();
                formData.append('attendance_id', attendanceId);
                fetch('AttendanceDelete.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteAttendanceModal'));
                        modal.hide();
                        cleanupModalBackdrop();

                        if (data.success) {
                            // Only show one success alert for 'Attendance Record Deleted!!!'
                            let alreadyShown = false;
                            document.querySelectorAll('.alert-success').forEach(alert => {
                                if (alert.textContent.includes('Attendance Record Deleted!!!')) {
                                    alreadyShown = true;
                                }
                            });
                            if (!alreadyShown) {
                                showAlert(data.message, 'success');
                            }
                            // Reload the attendance records page after successful deletion
                            loadPage('AttendanceRecords.php', 'attendance-submenu');
                        } else {
                            showAlert(data.message, 'danger');
                        }
                    })
                    .catch(error => {
                        showAlert('Error deleting attendance record: ' + error.message, 'danger');
                        cleanupModalBackdrop();
                    });
            });
            confirmDeleteAttendanceBtn.dataset.listenerAttached = 'true'; // Prevent duplicate listeners
        }

        // Edit announcement form submission
        const editAnnouncementForm = document.getElementById('editAnnouncementForm');
        if (editAnnouncementForm) {
            editAnnouncementForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const submitBtn = editAnnouncementForm.querySelector('button[type="submit"]');
                submitBtn.disabled = true;

                const formData = new FormData(this);
                const message = formData.get('message').trim();

                if (message.length < 10) {
                    showAlert('Announcement message must be at least 10 characters long.', 'danger', 'announcement-alert-container');
                    submitBtn.disabled = false;
                    return;
                }

                fetch('AdminUpdateAnnouncement.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('editAnnouncementModal'));
                        if (modal) {
                            modal.hide();
                            cleanupModalBackdrop();
                        }

                        showAlert(data.message, data.success ? 'success' : 'danger', 'announcement-alert-container');
                        if (data.success) {
                            loadPage('AdminViewAnnouncements.php', 'announcements-submenu');
                            sessionStorage.setItem('announcementUpdateSuccess', data.message);
                        }
                    })
                    .catch(error => {
                        showAlert('Error updating announcement: ' + error.message, 'danger', 'announcement-alert-container');
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                    });
            });
        }

        // Confirm delete announcement
        const confirmDeleteAnnouncementBtn = document.getElementById('confirmDeleteAnnouncementBtn');
        if (confirmDeleteAnnouncementBtn) {
            confirmDeleteAnnouncementBtn.addEventListener('click', function () {
                if (!window.announcementToDeleteId) {
                    showAlert('No announcement selected for deletion.', 'danger', 'announcement-alert-container');
                    return;
                }

                fetch('AdminDeleteAnnouncement.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'id=' + encodeURIComponent(window.announcementToDeleteId)
                })
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteAnnouncementModal'));
                        if (deleteModal) {
                            deleteModal.hide();
                            cleanupModalBackdrop();
                        }
                        showAlert(data.message, data.success ? 'success' : 'danger', 'announcement-alert-container');
                        if (data.success) {
                            loadPage('AdminViewAnnouncements.php', 'announcements-submenu');
                        }
                    })
                    .catch(error => {
                        showAlert('Error deleting announcement: ' + error.message, 'danger', 'announcement-alert-container');
                    });
            });
        }

        // Modal cleanup
        const modals = [
            'deleteAttendanceModal',
            'updateAttendanceModal',
            'deleteEventModal',
            'updateEventModal',
            'editAnnouncementModal',
            'deleteAnnouncementModal',
            'editPromotionModal',
            'deletePromotionModal'
        ];
        modals.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.addEventListener('hidden.bs.modal', cleanupModalBackdrop);
                modal.addEventListener('hide.bs.modal', cleanupModalBackdrop);
            }
        });
    }

    // Logout button
    const logoutBtn = document.getElementById('modal-logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function () {
            const button = this;
            const cancelBtn = document.querySelector('.btn-no');
            button.innerHTML = '<span class="spinner"></span>';
            button.classList.add('btn-loading');
            button.disabled = true;
            cancelBtn.disabled = true;

            setTimeout(() => {
                try {
                    window.location.href = 'Logout.php';
                } catch (e) {
                    button.innerHTML = 'Logout';
                    button.classList.remove('btn-loading');
                    button.disabled = false;
                    cancelBtn.disabled = false;
                    showAlert('Logout failed. Please try again.', 'danger');
                }
            }, 1000);
        });
    }

    document.querySelectorAll('.sidebar .nav-link').forEach(link => {
        link.addEventListener('click', function () {
            document.querySelectorAll('.sidebar .nav-link').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });

    document.querySelectorAll('.sidebar .collapse .nav-link').forEach(link => {
        link.addEventListener('click', function () {
            document.querySelectorAll('.sidebar .collapse .nav-link').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });

    window.loadPage = loadPage;
    window.refreshMainContent = function () {
        window.location.href = 'Admin.php';
    };

    const attendanceForm = document.getElementById('attendance-create-form');
    if (attendanceForm) {
        attendanceForm.addEventListener('submit', function (e) {
            e.preventDefault();
            fetch('Attendanceuser.php', {
                method: 'POST',
                body: new FormData(attendanceForm)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Attendance Created!', 'success', 'alert-container');
                        attendanceForm.reset();
                    } else {
                        showAlert(data.message, 'danger', 'alert-container');
                    }
                })
                .catch(error => {
                    showAlert('Error creating attendance: ' + error.message, 'danger', 'alert-container');
                });
        });
    }

    // Initialize calendar
    if (typeof dashboardEvents !== 'undefined') {
        new Calendar('#dashboard-calendar', {
            style: 'background',
            dataSource: dashboardEvents.map(event => ({
                startDate: new Date(event.start_date),
                endDate: new Date(event.end_date),
                name: event.title
            })),
            mouseOnDay: function(e) {
                if (e.events.length > 0) {
                    const content = e.events.map(event => `
                        <div class="event-tooltip-content">
                            <div class="event-name">${event.name}</div>
                            <div class="event-date">${e.date.toLocaleDateString()}</div>
                        </div>
                    `).join('');
                    const tooltip = document.createElement('div');
                    tooltip.className = 'calendar-tooltip';
                    tooltip.innerHTML = content;
                    document.body.appendChild(tooltip);

                    tooltip.style.position = 'absolute';
                    tooltip.style.top = (e.element.getBoundingClientRect().top + window.scrollY - tooltip.offsetHeight - 5) + 'px';
                    tooltip.style.left = (e.element.getBoundingClientRect().left + (e.element.offsetWidth - tooltip.offsetWidth) / 2) + 'px';
                    tooltip.style.zIndex = '1000';
                    tooltip.style.background = '#fff';
                    tooltip.style.border = '1px solid #ddd';
                    tooltip.style.padding = '5px';
                    tooltip.style.borderRadius = '5px';
                    tooltip.style.boxShadow = '0 2px 5px rgba(0,0,0,0.2)';

                    e.element.addEventListener('mouseleave', function() {
                        tooltip.remove();
                    }, { once: true });
                }
            },
            clickDay: function(e) {
                if (e.events.length > 0) {
                    const eventDetails = e.events.map(event => `
                        <div class="mb-2">
                            <strong>${event.name}</strong><br>
                            Date: ${e.date.toLocaleDateString()}<br>
                            Time: ${event.startDate.toLocaleTimeString()} - ${event.endDate.toLocaleTimeString()}
                        </div>
                    `).join('');
                    document.getElementById('eventDetailsContent').innerHTML = eventDetails;
                    new bootstrap.Modal(document.getElementById('eventDetailsModal')).show();
                }
            }
        });
    }

    // Setup sidebar active links
    function setupSidebarActiveLinks() {
        const currentPath = window.location.pathname.split('/').pop();
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            const href = link.getAttribute('href') || '';
            if (href === currentPath || (link.onclick && link.onclick.toString().includes(currentPath))) {
                link.classList.add('active');
            }
        });
    }

    // Initial setup
    setupSidebarToggle();
    setupSidebarActiveLinks();
    attachEventListeners();

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
});