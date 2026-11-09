{{-- ══════════════════════════════════════════════════════════════════════
     PORTFOLIO RICH SHARE PORTAL MODAL (FamilyPlatform Invariant)
     Strict v2.1 Design: Exact Parity with Article & Blog Share Portal
     Author-Only Guarded: Native System Sheet, WhatsApp, X, LinkedIn, Facebook, Telegram, Clipboard
     ══════════════════════════════════════════════════════════════════════ --}}
<div x-show="sharePortalOpen" 
     x-cloak
     class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center p-3"
     style="z-index: 10550; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);"
     @keydown.escape.window="closeSharePortal()"
     role="dialog"
     aria-modal="true"
     aria-labelledby="sharePortalModalTitle"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

    <div class="card border-0 shadow-2xl rounded-4 w-100 overflow-hidden"
         style="max-width: 520px; background: var(--card-bg, #ffffff); color: var(--text-main, #0f172a); border: 1px solid rgba(226, 232, 240, 0.8) !important;"
         @click.outside="closeSharePortal()"
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0 scale-95 translate-y-3"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-3">

        <!-- Modal Header -->
        <div class="d-flex align-items-center justify-content-between p-3.5 pb-2 border-0">
            <div class="d-flex align-items-center gap-2.5">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" 
                     style="width: 40px; height: 40px; background: rgba(29, 114, 254, 0.12); color: #1d72fe;">
                    <i class="bi bi-send-fill" style="font-size: 1.15rem; transform: rotate(-15deg); display: inline-block;"></i>
                </div>
                <h2 class="h5 mb-0 fw-bold" id="sharePortalModalTitle" style="color: var(--text-main, #0f172a); font-size: 1.15rem;">
                    {{ __('feed.share_memory_title', 'Share Memory') }}
                </h2>
            </div>
            <button type="button" 
                    class="btn-close shadow-none p-2" 
                    aria-label="Close"
                    style="min-width: 44px; min-height: 44px;"
                    @click="closeSharePortal()"></button>
        </div>

        <!-- Modal Body -->
        <div class="p-4 pt-2">
            <!-- 1. Rich Inset Preview Card -->
            <div class="p-3 mb-3.5 rounded-3 d-flex gap-3 align-items-center border"
                 style="background: var(--surface-secondary, #f8fafc); border-color: rgba(226, 232, 240, 0.9) !important;">
                <img :src="sharePortalImg" 
                     alt="Memory Preview" 
                     class="rounded-3 flex-shrink-0" 
                     style="width: 68px; height: 68px; object-fit: cover; background: #e2e8f0; border: 1px solid rgba(0,0,0,0.06);"
                     onerror="this.onerror=null; this.src='/resources/images/profile/avatarM.png';">
                <div class="overflow-hidden flex-grow-1">
                    <h3 class="h6 fw-bold mb-1 text-truncate" 
                        style="font-size: 0.95rem; color: var(--text-main, #0f172a);"
                        x-text="sharePortalTitle"></h3>
                    <p class="text-muted small mb-0" 
                       style="font-size: 0.82rem; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"
                       x-text="sharePortalText"></p>
                </div>
            </div>

            <!-- 2. Primary Web Share API Button (Mobile & Desktop System Sheet) -->
            <button type="button" 
                    class="btn w-100 py-2.5 rounded-3 fw-bold d-flex align-items-center justify-content-center gap-2 text-white shadow-sm"
                    style="background-color: #1d72fe; border: none; min-height: 48px; font-size: 0.98rem; transition: background-color 0.2s ease, transform 0.1s ease;"
                    @click="sharePortalViaSystem()">
                <i class="bi bi-box-arrow-up-right" style="font-size: 1.05rem;"></i>
                <span>{{ __('feed.share_via_system', 'Share via System') }}</span>
            </button>

            <!-- 3. Sub-Heading Divider -->
            <div class="text-center text-muted small my-3" style="font-size: 0.82rem;">
                {{ __('feed.or_share_directly_to', 'or share directly to:') }}
            </div>

            <!-- 4. Direct Social Share Channels (6 Grid Buttons) -->
            <div class="row g-2 text-center mb-3">
                <!-- WhatsApp -->
                <div class="col-4 col-sm-2">
                    <a :href="getSharePortalWhatsappUrl()" 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="btn w-100 py-2 rounded-3 d-flex flex-column align-items-center justify-content-center text-white text-decoration-none shadow-xs" 
                       style="background: #25d366; min-height: 62px; min-width: 44px; transition: transform 0.15s ease;"
                       aria-label="Share on WhatsApp">
                        <i class="bi bi-whatsapp mb-1" style="font-size: 1.25rem;"></i>
                        <span style="font-size: 0.7rem; font-weight: 700; white-space: nowrap;">WhatsApp</span>
                    </a>
                </div>

                <!-- X / Twitter -->
                <div class="col-4 col-sm-2">
                    <a :href="getSharePortalXUrl()" 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="btn w-100 py-2 rounded-3 d-flex flex-column align-items-center justify-content-center text-white text-decoration-none shadow-xs" 
                       style="background: #000000; min-height: 62px; min-width: 44px; transition: transform 0.15s ease;"
                       aria-label="Share on X / Twitter">
                        <i class="bi bi-twitter-x mb-1" style="font-size: 1.15rem;"></i>
                        <span style="font-size: 0.7rem; font-weight: 700; white-space: nowrap;">X / Twitter</span>
                    </a>
                </div>

                <!-- LinkedIn -->
                <div class="col-4 col-sm-2">
                    <a :href="getSharePortalLinkedinUrl()" 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="btn w-100 py-2 rounded-3 d-flex flex-column align-items-center justify-content-center text-white text-decoration-none shadow-xs" 
                       style="background: #0a66c2; min-height: 62px; min-width: 44px; transition: transform 0.15s ease;"
                       aria-label="Share on LinkedIn">
                        <i class="bi bi-linkedin mb-1" style="font-size: 1.25rem;"></i>
                        <span style="font-size: 0.7rem; font-weight: 700; white-space: nowrap;">LinkedIn</span>
                    </a>
                </div>

                <!-- Facebook -->
                <div class="col-4 col-sm-2">
                    <a :href="getSharePortalFacebookUrl()" 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="btn w-100 py-2 rounded-3 d-flex flex-column align-items-center justify-content-center text-white text-decoration-none shadow-xs" 
                       style="background: #1877f2; min-height: 62px; min-width: 44px; transition: transform 0.15s ease;"
                       aria-label="Share on Facebook">
                        <i class="bi bi-facebook mb-1" style="font-size: 1.25rem;"></i>
                        <span style="font-size: 0.7rem; font-weight: 700; white-space: nowrap;">Facebook</span>
                    </a>
                </div>

                <!-- Telegram -->
                <div class="col-4 col-sm-2">
                    <a :href="getSharePortalTelegramUrl()" 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="btn w-100 py-2 rounded-3 d-flex flex-column align-items-center justify-content-center text-white text-decoration-none shadow-xs" 
                       style="background: #229ed9; min-height: 62px; min-width: 44px; transition: transform 0.15s ease;"
                       aria-label="Share on Telegram">
                        <i class="bi bi-telegram mb-1" style="font-size: 1.25rem;"></i>
                        <span style="font-size: 0.7rem; font-weight: 700; white-space: nowrap;">Telegram</span>
                    </a>
                </div>

                <!-- Copy Link Button -->
                <div class="col-4 col-sm-2">
                    <button type="button" 
                            class="btn w-100 py-2 rounded-3 d-flex flex-column align-items-center justify-content-center text-white shadow-xs" 
                            style="background: #475569; min-height: 62px; min-width: 44px; border: none; transition: transform 0.15s ease;"
                            @click="copySharePortalLink()"
                            aria-label="Copy memory link">
                        <i class="bi mb-1" :class="sharePortalCopied ? 'bi-check-lg text-success' : 'bi-clipboard'" style="font-size: 1.25rem;"></i>
                        <span style="font-size: 0.7rem; font-weight: 700; white-space: nowrap;" x-text="sharePortalCopied ? 'Copied!' : 'Copy Link'"></span>
                    </button>
                </div>
            </div>

            <!-- 5. Bottom Read-Only URL Field with Attached Copy Button -->
            <div class="input-group">
                <input type="text" 
                       class="form-control form-control-sm bg-light" 
                       readonly 
                       :value="sharePortalUrl" 
                       aria-label="Memory share URL"
                       style="font-size: 0.88rem; color: #334155;">
                <button class="btn btn-outline-secondary btn-sm fw-bold d-inline-flex align-items-center gap-1 px-3" 
                        type="button" 
                        style="min-height: 44px; min-width: 72px;"
                        @click="copySharePortalLink()">
                    <i class="bi" :class="sharePortalCopied ? 'bi-check-circle-fill text-success' : 'bi-copy'"></i>
                    <span x-text="sharePortalCopied ? 'Copied' : 'Copy'"></span>
                </button>
            </div>
        </div>
    </div>
</div>
