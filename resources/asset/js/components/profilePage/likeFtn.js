 // Like buttons functionality
        const likeButtons = document.querySelectorAll('.reaction-buttons button');
        
        likeButtons.forEach(button => {
            button.addEventListener('click', function() {
                if (this.textContent.includes('Like')) {
                    this.innerHTML = '<i class="bi bi-hand-thumbs-up-fill me-1"></i> Liked';
                    this.classList.add('text-primary');
                }
            });
        });
