
        // RSVP Buttons
        const rsvpButtons = document.querySelectorAll('.rsvp-buttons .btn');
        
        rsvpButtons.forEach(button => {
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
        