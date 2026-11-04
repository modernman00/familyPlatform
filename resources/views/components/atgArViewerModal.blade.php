{{-- ATG Augmented Reality (AR) & 3D Holographic Emoji Viewer Modal --}}
<div class="modal fade atg-ar-modal" id="atgArEmojiModal" tabindex="-1" aria-labelledby="atgArEmojiModalTitle" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden" style="background: #ffffff; color: #0f172a; border: 1.5px solid rgba(245, 158, 11, 0.25) !important;">
            
            <!-- Modal Header -->
            <div class="modal-header border-0 pb-0 px-4 pt-4 d-flex justify-content-between align-items-center bg-white">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-pill fw-bold text-uppercase px-2.5 py-1 text-white" style="background: linear-gradient(135deg, #f59e0b, #b45309); font-size: 0.72rem; letter-spacing: 0.05em;">
                        ✨ ATG Augmented Reality
                    </span>
                    <h5 class="modal-title fw-bold mb-0 text-dark" id="atgArEmojiModalTitle" style="font-size: 1.15rem;">
                        3D AR Emoji Viewer
                    </h5>
                </div>
                <button type="button" class="btn-close" id="closeAtgArModalBtn" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body (3D Stage & AR Camera Viewport) -->
            <div class="modal-body p-4 text-center bg-white">
                
                <!-- The Main AR / 3D Canvas Stage -->
                <div class="atg-ar-stage-container position-relative mx-auto rounded-4 overflow-hidden" id="atgArStageContainer" style="background: radial-gradient(circle at 50% 35%, #ffffff 0%, #fffbeb 55%, #fef3c7 100%) !important; border: 1.5px solid rgba(245, 158, 11, 0.3) !important;">
                    
                    <!-- Live Camera Feed (Hidden by default, shown in AR Camera Mode) -->
                    <video id="atgArCameraFeed" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover d-none" autoplay playsinline muted></video>
                    
                    <!-- Camera Overlay Grid & Reticle -->
                    <div id="atgArCameraReticle" class="position-absolute top-0 start-0 w-100 h-100 d-none pointer-events-none d-flex align-items-center justify-content-center">
                        <div class="ar-reticle-ring"></div>
                        <span class="ar-reticle-instruction">Drag to reposition in room • Pinch to scale</span>
                    </div>

                    <!-- Ambient 3D Particle Field Canvas -->
                    <canvas id="atgArParticleCanvas" class="position-absolute top-0 start-0 w-100 h-100 pointer-events-none"></canvas>

                    <!-- The Floating 3D Emoji Object -->
                    <div class="atg-ar-floating-object" id="atgArFloatingObject" title="Drag to orbit in 3D">
                        <img id="atgArEmojiImg" src="" alt="3D Emoji" class="atg-3d-emoji-render">
                    </div>

                    <!-- AR Mode Indicator Watermark -->
                    <div class="ar-mode-badge" id="atgArModeBadge">
                        <i class="bi bi-box"></i> 3D Orbit Mode
                    </div>

                </div>

                <!-- Info & Meaning Bar -->
                <div class="mt-3 text-center">
                    <h4 class="fw-bold mb-1 text-dark" id="atgArEmojiName">Family Emoji</h4>
                    <p class="text-secondary small mb-0 mx-auto" style="max-width: 520px; font-size: 0.85rem;" id="atgArEmojiMeaning">
                        Rooted together across generations.
                    </p>
                </div>

                <!-- AR Interactive Controls Dock -->
                <div class="d-flex flex-wrap align-items-center justify-content-center gap-2 mt-4 pt-2 border-top border-slate-200">
                    <button type="button" class="btn atg-ar-ctrl-btn active" id="atgToggleOrbitBtn">
                        <i class="bi bi-arrow-repeat me-1.5 text-warning"></i> 3D Orbit
                    </button>
                    <button type="button" class="btn atg-ar-ctrl-btn" id="atgToggleCameraBtn">
                        <i class="bi bi-camera-video-fill me-1.5 text-danger"></i> Project in Room (AR Camera)
                    </button>
                    <button type="button" class="btn atg-ar-ctrl-btn" id="atgBurstParticlesBtn">
                        <i class="bi bi-stars me-1.5 text-primary"></i> Particle Burst
                    </button>
                    <button type="button" class="btn atg-ar-ctrl-btn d-none" id="atgArSnapshotBtn">
                        <i class="bi bi-camera-fill me-1.5 text-danger"></i> Take Photo
                    </button>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-0 px-4 pb-4 pt-0 justify-content-between bg-white border-top border-slate-100">
                <span class="text-xs text-secondary">
                    Sovereign WebGL &amp; WebXR Micro-Engine • Zero App Install Required
                </span>
                <button type="button" class="btn btn-sm btn-secondary px-3 py-1.5 rounded-pill" data-bs-dismiss="modal">
                    Close
                </button>
            </div>

        </div>
    </div>
</div>
