        <!-- Middle Column - Feed -->
        <div class="feed-column">
            <!-- Post Composer -->
            <div class="card post-composer mb-4" data-bs-toggle="modal" data-bs-target="#postModal">
                <div class="card-body d-flex align-items-center">
                    @isset($data['img'])
                        <img src="/public/img/profile/{{ $data['img'] }}" alt="profile"
                            class="rounded-circle me-3 " width="40" height="40">
                    @else
                        <img src="/public/avatar/avatarF.png" alt="profile" width="40" height="40"
                            class="rounded-circle me-3">
                    @endisset

                    <div class="flex-grow-1">
                        <span class="text-muted">What's on your mind, {{ $data['firstName'] }} ?</span>
                    </div>
                </div>
            </div>

            <!-- Posts -->
            <div class="postIt" id="postIt">
               
            </div>

        <!-- The Modal Structure (Hidden by default) -->
    <div id="imageModal" class="image-modal">
        <span id="imageModalClose" class="image-modal-close">&times;</span>
        <div class="image-modal-content">
            <img id="modalImage" src="" alt="Enlarged view">
        </div>
    </div>


            <!-- Pagination -->
            <nav aria-label="Feed pagination">
                <ul class="pagination justify-content-center">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1">Previous</a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#">Next</a>
                    </li>
                </ul>
            </nav>
        </div>

<script>
            /**
         * ----------------------------------------------------------------
         * Reusable Image Modal Function
         * ----------------------------------------------------------------
         * This function finds all images with the specified selector
         * and attaches a click event to show them in a modal.
         *
         * @param {string} selector - The CSS selector for the images you want to be zoomable (e.g., '.zoomable-image').
         */
        function enableImageModal(selector) {
            // Get references to the modal elements
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            const closeModal = document.getElementById('imageModalClose');

            // Find all images that match the selector
            const images = document.querySelectorAll(selector);

            // Guard clause: if no modal or images, do nothing.
            if (!modal || !modalImage || !closeModal || images.length === 0) {
                console.warn('Image modal setup failed: Required elements not found.');
                return;
            }

            // Function to show the modal
            const showModal = (src, alt) => {
                modalImage.src = src;
                modalImage.alt = alt;
                modal.classList.add('show');
                document.body.style.overflow = 'hidden'; // Prevent background scrolling
            };

            // Function to hide the modal
            const hideModal = () => {
                modal.classList.remove('show');
                document.body.style.overflow = ''; // Restore scrolling
            };

            // Attach click event listeners to each zoomable image
            images.forEach(image => {
                image.addEventListener('click', () => {
                    showModal(image.src, image.alt);
                });
            });

            // Event listener for the close button
            closeModal.addEventListener('click', hideModal);

            // Event listener to close the modal by clicking the background
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    hideModal();
                }
            });

            // Event listener to close the modal by pressing the Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && modal.classList.contains('show')) {
                    hideModal();
                }
            });
        }

        // --- Initialize the function on page load ---
        document.addEventListener('DOMContentLoaded', () => {
            // Call the function and pass the class name of the images you want to be zoomable
            enableImageModal('.zoomable-image');
        });
</script>