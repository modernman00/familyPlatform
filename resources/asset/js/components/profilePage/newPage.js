
        // Create Event Functionality
        const createEventBtn = document.getElementById('createEventBtn');
        
        createEventBtn.addEventListener('click', () => {
            const eventTitle = document.getElementById('eventTitle').value;
            const eventDate = document.getElementById('eventDate').value;
            const eventTime = document.getElementById('eventTime').value;
            const eventLocation = document.getElementById('eventLocation').value;
            const eventDescription = document.getElementById('eventDescription').value;
            
            if (!eventTitle || !eventDate || !eventTime || !eventLocation) {
                alert('Please fill in all required fields: Title, Date, Time, and Location');
                return;
            }
            
            // Format the date for display
            const dateObj = new Date(eventDate);
            const day = dateObj.getDate();
            const month = dateObj.toLocaleString('default', { month: 'SHORT' }).toUpperCase();
            
            // Format the time for display
            const timeObj = new Date(`1970-01-01T${eventTime}`);
            const formattedTime = timeObj.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            
            // Create the new event card
            const eventsContainer = document.getElementById('eventsContainer');
            const newEventCard = document.createElement('div');
            newEventCard.className = 'event-card card';
            newEventCard.innerHTML = `
                <div class="card-body">
                    <div class="d-flex">
                        <div class="event-date me-3">
                            <div class="fw-bold">${day}</div>
                            <div>${month}</div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">${eventTitle}</h6>
                            <small class="text-muted">${formattedTime} • ${eventLocation}</small>
                            <div class="mt-2 rsvp-buttons d-flex">
                                <button class="btn btn-sm btn-outline-primary">Going</button>
                                <button class="btn btn-sm btn-outline-secondary">Maybe</button>
                                <button class="btn btn-sm btn-outline-danger">Decline</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Add the new event at the top of the events list
            eventsContainer.insertBefore(newEventCard, eventsContainer.firstChild);
            
            // Reset the form
            document.getElementById('eventForm').reset();
            
            // Close the modal
            bootstrap.Modal.getInstance(document.getElementById('createEventModal')).hide();
            
            // Show success message
            alert('Event created successfully!');
            
            // Add event listeners to the new RSVP buttons
            const newRsvpButtons = newEventCard.querySelectorAll('.rsvp-buttons .btn');
            newRsvpButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons in the same container
                    this.parentElement.querySelectorAll('.btn').forEach(btn => {
                        btn.classList.remove('active');
                        if (btn.classList.contains('btn-outline-primary')) {
                            btn.classList.replace('btn-primary', 'btn-outline-primary');
                        } else if (btn.classList.contains('btn-outline-secondary')) {
                            btn.classList.replace('btn-secondary', 'btn-outline-secondary');
                        } else if (btn.classList.contains('btn-outline-danger')) {
                            btn.classList.replace('btn-danger', 'btn-outline-danger');
                        }
                    });
                    
                    // Add active class to clicked button
                    this.classList.add('active');
                    if (this.classList.contains('btn-outline-primary')) {
                        this.classList.replace('btn-outline-primary', 'btn-primary');
                    } else if (this.classList.contains('btn-outline-secondary')) {
                        this.classList.replace('btn-outline-secondary', 'btn-secondary');
                    } else if (this.classList.contains('btn-outline-danger')) {
                        this.classList.replace('btn-outline-danger', 'btn-danger');
                    }
                });
            });
        });

        // Save Profile Changes
        const saveProfileBtn = document.getElementById('saveProfileBtn');
        
        saveProfileBtn.addEventListener('click', () => {
            const firstName = document.getElementById('firstName').value;
            const lastName = document.getElementById('lastName').value;
            const bio = document.getElementById('bio').value;
            
            // Update profile card
            document.querySelector('.profile-card .card-title').textContent = `${firstName} ${lastName}`;
            document.querySelector('.profile-card .card-text').textContent = bio;
            
            // Update navbar
            document.querySelector('#userMenu').innerHTML = `<i class="bi bi-person-circle me-1"></i> ${firstName} ${lastName}`;
            
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('editProfileModal')).hide();
            
            // Show success message
            alert('Profile updated successfully!');
        });

        // Notification Button
        const notificationBtn = document.getElementById('notificationBtn');
        
        notificationBtn.addEventListener('click', () => {
            alert('You have 3 new notifications');
            // Reset notification badge
            document.querySelector('.notification-badge').style.display = 'none';
        });

        // Comment Time Stamps
        document.querySelectorAll('.comment-btn').forEach(button => {
            button.addEventListener('click', function() {
                const commentSection = this.closest('.card-body').querySelector('.comment-section');
                const commentInput = commentSection.querySelector('input');
                commentInput.focus();
            });
        });

        // Add comment functionality
        document.querySelectorAll('.comment-section .btn').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.previousElementSibling;
                const commentText = input.value.trim();
                if (commentText) {
                    const commentSection = this.closest('.comment-section');
                    const newComment = document.createElement('div');
                    newComment.className = 'd-flex mb-3';
                    newComment.innerHTML = `
                        <img src="https://randomuser.me/api/portraits/men/1.jpg" alt="Profile" class="rounded-circle me-2" width="32" height="32">
                        <div class="flex-grow-1">
                            <div class="comment">
                                <strong>John Doe</strong> ${commentText}
                                <div class="comment-time">Just now</div>
                            </div>
                        </div>
                    `;
                    commentSection.insertBefore(newComment, commentSection.lastElementChild);
                    input.value = '';
                }
            });
        });

        // Pagination
        document.querySelectorAll('.pagination .page-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const pageNum = this.getAttribute('data-page');
                if (pageNum) {
                    document.querySelectorAll('.post-page').forEach(page => {
                        page.classList.remove('active');
                    });
                    document.getElementById(`page${pageNum}`).classList.add('active');
                    
                    document.querySelectorAll('.pagination .page-item').forEach(item => {
                        item.classList.remove('active');
                    });
                    this.parentElement.classList.add('active');
                }
            });
        });