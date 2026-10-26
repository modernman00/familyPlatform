{{--
  Profile Photo Viewer (Facebook-Style Lightbox Modal)
  Standards v2.1 Enforced (en-GB)
--}}
<div id="pfViewerOverlay" 
     class="pf-viewer-overlay" 
     role="dialog" 
     aria-modal="true" 
     aria-label="Profile photo viewer" 
     aria-hidden="true" 
     tabindex="-1">
     
    <!-- Header Bar -->
    <header class="pf-viewer-header">
        <div class="pf-viewer-user">
            <img id="pfViewerThumb" 
                 src="/resources/images/profile/avatarM.png" 
                 alt="Thumbnail" 
                 class="pf-viewer-thumb" 
                 loading="lazy">
            <div class="pf-viewer-meta">
                <span id="pfViewerName" class="pf-viewer-name">Family Member</span>
                <span id="pfViewerSubtitle" class="pf-viewer-subtitle">Profile Photo</span>
            </div>
        </div>

        <div class="pf-viewer-actions">
            <!-- HD Zoom Toggle Button -->
            <button type="button" 
                    id="pfViewerZoomBtn" 
                    class="pf-action-btn" 
                    title="Toggle HD zoom (Z)" 
                    aria-label="Toggle HD zoom">
                <i class="bi bi-zoom-in"></i>
            </button>

            <!-- Download Button -->
            <button type="button" 
                    id="pfViewerDownloadBtn" 
                    class="pf-action-btn" 
                    title="Download photo" 
                    aria-label="Download photo">
                <i class="bi bi-download"></i>
            </button>

            <!-- Close Button (Esc) -->
            <button type="button" 
                    id="pfViewerCloseBtn" 
                    class="pf-action-btn pf-close-btn" 
                    title="Close (Esc)" 
                    aria-label="Close profile photo viewer">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </header>

    <!-- Main Stage Canvas -->
    <main class="pf-viewer-stage" id="pfViewerStage">
        <div class="pf-image-wrapper">
            <!-- Loading Ring -->
            <div id="pfViewerLoader" class="pf-loader" aria-live="polite">
                <div class="pf-spinner-ring"></div>
                <span>Loading photo...</span>
            </div>

            <!-- Enlarged Profile Image -->
            <img id="pfViewerMainImg" 
                 src="/resources/images/profile/avatarM.png" 
                 alt="Profile Photo" 
                 class="pf-main-image" 
                 tabindex="0"
                 draggable="false">
        </div>
    </main>

    <!-- Bottom Controls & Action Bar -->
    <footer class="pf-viewer-footer">
        <div class="pf-footer-pill">
            <!-- Update Profile Picture Button (shown for owner) -->
            <button type="button" 
                    id="pfViewerEditBtn" 
                    class="pf-btn-edit-photo" 
                    style="display: none;" 
                    aria-label="Update profile picture">
                <i class="bi bi-camera-fill"></i>
                <span>Update Profile Picture</span>
            </button>

            <!-- Share / Copy Link Button -->
            <button type="button" 
                    id="pfViewerShareBtn" 
                    class="pf-btn-share" 
                    title="Copy photo link" 
                    aria-label="Copy photo link">
                <i class="bi bi-link-45deg"></i>
                <span>Copy Link</span>
            </button>
        </div>
    </footer>
</div>
