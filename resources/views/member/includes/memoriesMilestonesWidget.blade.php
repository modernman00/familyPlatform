@php
$wId = $widgetId ?? 'memoriesWidgetDesktop';
@endphp
<style>
  body.dark-mode #memoriesCard,
  [data-bs-theme="dark"] #memoriesCard {
    background: var(--card-bg, #1e293b) !important;
    border: 1px solid var(--border-color, #334155) !important;
  }

  .ms-3d-viewport {
    perspective: 960px;
  }

  .ms-3d-stage {
    position: relative;
    border-radius: var(--radius-lg, 1rem);
    padding: var(--space-4, 1rem);
    transform-style: preserve-3d;
    transition: transform 0.22s cubic-bezier(0.2, 0.8, 0.2, 1), box-shadow 0.22s ease;
    background: linear-gradient(150deg, #ffffff 0%, #fffbf2 55%, #fef3c7 100%);
    border: 1px solid rgba(217, 119, 6, 0.28);
    border-bottom: 4px solid rgba(180, 83, 9, 0.34);
    box-shadow:
      0 14px 28px -10px rgba(163, 22, 33, 0.14),
      0 4px 10px -2px rgba(15, 23, 42, 0.06),
      inset 0 1px 0 rgba(255, 255, 255, 0.95);
    overflow: hidden;
  }

  .ms-3d-stage--event {
    background: linear-gradient(150deg, #ffffff 0%, #f0fdfa 65%, #ccfbf1 100%);
    border-color: rgba(13, 148, 136, 0.28);
    border-bottom-color: rgba(15, 118, 110, 0.34);
    box-shadow:
      0 12px 24px -10px rgba(13, 148, 136, 0.14),
      inset 0 1px 0 rgba(255, 255, 255, 0.95);
  }

  .ms-3d-stage::before {
    content: '';
    position: absolute;
    top: -40%;
    right: -25%;
    width: 170px;
    height: 170px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(251, 191, 36, 0.22) 0%, rgba(163, 22, 33, 0.06) 55%, transparent 72%);
    pointer-events: none;
  }

  body.dark-mode .ms-3d-stage,
  [data-bs-theme="dark"] .ms-3d-stage {
    background: linear-gradient(150deg, #1e293b 0%, #271a24 58%, #1f172a 100%) !important;
    border: 1px solid rgba(251, 191, 36, 0.28) !important;
    border-bottom: 4px solid rgba(217, 119, 6, 0.45) !important;
    box-shadow:
      0 16px 30px -10px rgba(0, 0, 0, 0.55),
      inset 0 1px 0 rgba(255, 255, 255, 0.08) !important;
  }

  .ms-3d-elevated-medallion {
    transform: translateZ(18px);
    filter: drop-shadow(0 6px 10px rgba(163, 22, 33, 0.18));
  }

  .ms-3d-avatar-ring {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    padding: 2px;
    background: linear-gradient(135deg, #f59e0b 0%, #fde68a 50%, #b8202c 100%);
    box-shadow: 0 6px 14px rgba(180, 83, 9, 0.28);
  }

  .ms-3d-fullname {
    color: var(--text-color, #0f172a);
    font-size: var(--text-base, 1rem);
    font-weight: 800;
    line-height: 1.32;
    letter-spacing: -0.01em;
    overflow-wrap: anywhere;
    word-break: normal;
  }

  body.dark-mode .ms-3d-fullname,
  [data-bs-theme="dark"] .ms-3d-fullname {
    color: #f8fafc !important;
  }

  .ms-3d-crest-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.22rem 0.62rem;
    border-radius: 999px;
    font-size: var(--text-xs, 0.75rem);
    font-weight: 700;
    background: rgba(163, 22, 33, 0.1);
    color: #8a1019;
    border: 1px solid rgba(163, 22, 33, 0.18);
    white-space: nowrap;
  }

  body.dark-mode .ms-3d-crest-pill,
  [data-bs-theme="dark"] .ms-3d-crest-pill {
    background: rgba(251, 191, 36, 0.15) !important;
    color: #fde68a !important;
    border-color: rgba(251, 191, 36, 0.3) !important;
  }

  .ms-surprise-panel {
    margin-top: var(--space-3, 0.75rem);
    padding: var(--space-2, 0.5rem) var(--space-3, 0.75rem);
    border-radius: var(--radius-md, 0.75rem);
    background: rgba(255, 255, 255, 0.86);
    border: 1px solid rgba(163, 22, 33, 0.16);
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
    transform: translateZ(14px);
  }

  body.dark-mode .ms-surprise-panel,
  [data-bs-theme="dark"] .ms-surprise-panel {
    background: rgba(15, 23, 42, 0.78) !important;
    border-color: rgba(251, 191, 36, 0.28) !important;
  }

  .ms-surprise-whisper {
    color: #7f1d1d;
    font-size: var(--text-xs, 0.75rem);
  }

  body.dark-mode .ms-surprise-whisper,
  [data-bs-theme="dark"] .ms-surprise-whisper {
    color: #fde68a !important;
  }

  .ms-organiser-btn-card {
    background: rgba(245, 158, 11, 0.16);
    color: #92400e !important;
    border: 1px solid rgba(217, 119, 6, 0.35);
  }
  .ms-organiser-btn-video {
    background: rgba(15, 118, 110, 0.14);
    color: #0f766e !important;
    border: 1px solid rgba(13, 148, 136, 0.32);
  }
  body.dark-mode .ms-organiser-btn-card,
  [data-bs-theme="dark"] .ms-organiser-btn-card {
    background: rgba(251, 191, 36, 0.18) !important;
    color: #fde68a !important;
    border-color: rgba(251, 191, 36, 0.38) !important;
  }
  body.dark-mode .ms-organiser-btn-video,
  [data-bs-theme="dark"] .ms-organiser-btn-video {
    background: rgba(20, 184, 166, 0.18) !important;
    color: #5eead4 !important;
    border-color: rgba(45, 212, 191, 0.38) !important;
  }

  .ms-surprise-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(175px, 1fr));
    gap: var(--space-2, 0.5rem);
    margin-top: var(--space-2, 0.5rem);
  }

  .ms-btn-3d-primary {
    min-height: 44px;
    min-width: 44px;
    padding: 0.5rem 0.85rem;
    border-radius: var(--radius-md, 0.75rem);
    font-size: var(--text-sm, 0.875rem);
    font-weight: 700;
    white-space: nowrap;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    background: linear-gradient(135deg, #a31621 0%, #8a1019 100%);
    color: #ffffff !important;
    border: 1px solid #7f1d1d;
    border-bottom: 3px solid #5c0b11;
    box-shadow: 0 6px 14px -4px rgba(163, 22, 33, 0.42);
    text-decoration: none;
    transition: transform 0.15s ease, filter 0.15s ease;
  }

  .ms-btn-3d-secondary {
    min-height: 44px;
    min-width: 44px;
    padding: 0.5rem 0.85rem;
    border-radius: var(--radius-md, 0.75rem);
    font-size: var(--text-sm, 0.875rem);
    font-weight: 700;
    white-space: nowrap;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    background: #ffffff;
    color: #7f1d1d !important;
    border: 1.5px solid rgba(163, 22, 33, 0.32);
    border-bottom: 3px solid rgba(163, 22, 33, 0.28);
    box-shadow: 0 4px 10px -3px rgba(15, 23, 42, 0.08);
    text-decoration: none;
    transition: transform 0.15s ease, background-color 0.15s ease;
  }

  body.dark-mode .ms-btn-3d-secondary,
  [data-bs-theme="dark"] .ms-btn-3d-secondary {
    background: #1e293b !important;
    color: #fef3c7 !important;
    border-color: rgba(251, 191, 36, 0.38) !important;
  }

  @media (hover: hover) {
    .ms-btn-3d-primary:hover {
      filter: brightness(1.07);
      color: #ffffff !important;
    }
    .ms-btn-3d-secondary:hover {
      background: #fff7ed;
    }
  }

  .ms-btn-3d-primary:active,
  .ms-btn-3d-secondary:active {
    transform: scale(0.98);
  }

  .ms-btn-3d-primary:focus-visible,
  .ms-btn-3d-secondary:focus-visible {
    outline: 2px solid #a31621;
    outline-offset: 2px;
  }

  @media (prefers-reduced-motion: reduce) {
    .ms-3d-stage,
    .ms-btn-3d-primary,
    .ms-btn-3d-secondary {
      transition: none !important;
      transform: none !important;
    }
  }
</style>
<div class="card border-0 shadow-sm mb-4 overflow-hidden" 
     id="{{ $wId }}"
     data-id="memoriesMilestonesWidget"
     style="border-radius: 20px; background-color: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e5e7eb);"
     x-data="memoriesMilestonesWidget('{{ $wId }}')"
     x-init="initWidget()"
     x-cloak
     x-show="loaded ? (memories.length > 0 || milestones.length > 0) : true">
    
    <!-- Widget Header -->
    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3 pb-2 px-3 px-sm-4">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" 
                 style="width: 36px; height: 36px; background: rgba(184,32,44, 0.12); color: var(--primary-color, #a31621);">
                <i class="bi bi-stars fs-6 text-primary"></i>
            </div>
            <div>
                <h3 class="h5 mb-0 fw-bold" style="color: var(--text-color, #1f2937); font-size: 1rem;">{{ __('milestones.title', 'Memories & Milestones') }}</h3>
                <p class="text-muted mb-0" style="font-size: 0.875rem;">{{ __('milestones.subtitle', 'On this day flashbacks & family celebrations') }}</p>
            </div>
        </div>
        <button type="button" 
                class="btn btn-sm btn-light border rounded-circle d-flex align-items-center justify-content-center text-muted shadow-xs" 
                @click="loadData()" 
                title="Refresh memories" 
                aria-label="Refresh memories"
                style="width: 44px; height: 44px; min-width: 44px; min-height: 44px; padding: 0; flex-shrink: 0;">
            <i class="bi bi-arrow-clockwise" :class="{'spin-anim': loading}"></i>
        </button>
    </div>

    <!-- Widget Body -->
    <div class="card-body px-3 px-sm-4 pt-1 pb-3 pb-sm-4">
        <!-- Loading State -->
        <template x-if="loading && !loaded">
            <div class="py-4 text-center">
                <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                <p class="text-muted mb-0" style="font-size: 0.875rem;">{{ __('milestones.gathering', 'Gathering family memories...') }}</p>
            </div>
        </template>

        <template x-if="loaded">
            <div class="d-flex flex-column gap-3">
                
                <!-- 1. Nostalgia "On This Day" Memory Card -->
                <template x-if="memories.length > 0">
                    <div class="p-3.5 rounded-4 position-relative overflow-hidden shadow-xs" 
                         id="memoriesCard"
                         data-id="memoriesCard"
                         style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 252, 0.95) 100%); border: 1px solid var(--border-color, #e2e8f0); box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                        
                        <!-- Commemorative Archival Header -->
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2.5">
                            <span class="badge rounded-pill px-3 py-1.5 fw-bold text-white d-inline-flex align-items-center gap-1.5 shadow-xs" 
                                  style="background: linear-gradient(135deg, #a31621, #d97706); font-size: 0.74rem; white-space: nowrap;">
                                <i class="bi bi-clock-history"></i> <span x-text="memories[0].years_ago_label"></span>
                            </span>
                            <span class="badge rounded-pill small fw-semibold text-nowrap border px-2.5 py-1" style="font-size: 0.76rem; background-color: var(--card-bg, #ffffff); color: var(--text-color, #0f172a);" x-text="memories[0].formatted_date"></span>
                        </div>

                        <!-- A. If Memory has a photo: Archival Polaroid Keepsake Frame -->
                        <template x-if="memories[0].primary_image">
                            <div class="mb-2.5 rounded-3 overflow-hidden position-relative" 
                                 style="border: 3px solid #ffffff; box-shadow: 0 4px 14px rgba(0,0,0,0.08); background: #f8fafc; max-height: 180px;">
                                <picture class="w-100 d-block h-100">
                                    <source :srcset="'/resources/images/post/' + memories[0].primary_image.replace(/\.[^/.]+$/, '') + '.avif'" type="image/avif">
                                    <source :srcset="'/resources/images/post/' + memories[0].primary_image.replace(/\.[^/.]+$/, '') + '.webp'" type="image/webp">
                                    <img :src="'/resources/images/post/' + memories[0].primary_image" 
                                         alt="Memory" 
                                         class="w-100 object-fit-cover" 
                                         style="height: 175px; max-height: 175px;"
                                         loading="lazy"
                                         decoding="async"
                                         onerror="this.style.display='none'">
                                </picture>
                                <span class="position-absolute bottom-0 start-0 m-2 badge bg-dark bg-opacity-75 text-white rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1 shadow-xs" 
                                      style="font-size: 0.7rem; backdrop-filter: blur(4px);">
                                    <i class="bi bi-camera-fill text-warning"></i> Family Chronicle
                                </span>
                            </div>
                        </template>

                        <!-- B. If Memory has NO photo (Text-Only): Bespoke Heirloom Chronicle SVG Illustration -->
                        <template x-if="!memories[0].primary_image">
                            <div class="mb-2.5 rounded-3 p-2.5 text-center position-relative overflow-hidden" 
                                 style="background: linear-gradient(135deg, rgba(255, 253, 248, 0.95) 0%, rgba(254, 243, 199, 0.6) 100%); border: 1px dashed rgba(245, 158, 11, 0.35);">
                                <svg width="68" height="52" viewBox="0 0 72 56" fill="none" xmlns="http://www.w3.org/2000/svg" class="mx-auto mb-1">
                                    <path d="M36 16C30 12 18 12 10 15V44C18 41 30 41 36 45C42 41 54 41 62 44V15C54 12 42 12 36 16Z" fill="#FFFDF8" stroke="#D97706" stroke-width="1.6"/>
                                    <path d="M36 16V45" stroke="#B45309" stroke-width="1.6"/>
                                    <line x1="16" y1="22" x2="30" y2="22" stroke="#CBD5E1" stroke-width="1.5" stroke-linecap="round"/>
                                    <line x1="16" y1="27" x2="28" y2="27" stroke="#CBD5E1" stroke-width="1.5" stroke-linecap="round"/>
                                    <line x1="16" y1="32" x2="30" y2="32" stroke="#CBD5E1" stroke-width="1.5" stroke-linecap="round"/>
                                    <line x1="42" y1="22" x2="56" y2="22" stroke="#CBD5E1" stroke-width="1.5" stroke-linecap="round"/>
                                    <line x1="42" y1="27" x2="54" y2="27" stroke="#CBD5E1" stroke-width="1.5" stroke-linecap="round"/>
                                    <line x1="42" y1="32" x2="56" y2="32" stroke="#CBD5E1" stroke-width="1.5" stroke-linecap="round"/>
                                    <path d="M50 8C48 10 42 22 41 26L43 27C45 23 52 12 53 9C54 6 52 6 50 8Z" fill="#F59E0B"/>
                                    <circle cx="20" cy="10" r="1.8" fill="#B8202C"/>
                                    <circle cx="56" cy="12" r="2.2" fill="#D97706"/>
                                    <circle cx="12" cy="38" r="1.5" fill="#F59E0B"/>
                                </svg>
                                <div class="small fw-bold text-uppercase tracking-wider" style="color: #8a1019; font-size: 0.72rem; letter-spacing: 0.05em;">Family Heirloom Chronicle</div>
                                <div class="text-muted small" style="font-size: 0.75rem;">A preserved story from our generational heritage</div>
                            </div>
                        </template>

                        <!-- Meaningful Memory Quote Text -->
                        <div class="mb-2">
                            <p class="mb-0 fw-medium fst-italic" style="color: var(--text-color, #1f2937); font-size: 0.88rem; line-height: 1.5;">
                                &ldquo;<span x-html="formatMemoryMessage(memories[0].postMessage)"></span>&rdquo;
                            </p>
                        </div>

                        <!-- Meaningful Generational Prompt -->
                        <div class="p-2 rounded-3 mb-2.5" style="background: rgba(248, 250, 252, 0.8); border-left: 3px solid #f59e0b; font-size: 0.78rem; color: #475569;">
                            <i class="bi bi-heart-pulse-fill text-warning me-1"></i> <span x-text="memories[0].meaningful_prompt || 'A cherished moment etched forever in our family story.'"></span>
                        </div>

                        <div class="pt-2 border-top border-light-subtle d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <template x-if="memories[0].profileImg">
                                    <img :src="'/resources/images/profile/' + memories[0].profileImg" class="rounded-circle flex-shrink-0 shadow-2xs" style="width: 26px; height: 26px; object-fit: cover; border: 1px solid #e2e8f0;" alt="Author">
                                </template>
                                <div>
                                    <span class="text-dark small fw-bold d-block" style="font-size: 0.8125rem; overflow-wrap: anywhere;" x-text="memories[0].fullName"></span>
                                    <span class="text-muted small" style="font-size: 0.7rem;">Family Story Contributor</span>
                                </div>
                            </div>

                            <span class="badge rounded-pill bg-light text-muted border px-2.5 py-1 d-inline-flex align-items-center gap-1 shadow-2xs" style="font-size: 0.72rem;">
                                <i class="bi bi-shield-lock-fill text-secondary"></i> Private Family Chronicle
                            </span>
                        </div>
                    </div>
                </template>

                <!-- 2. Upcoming Milestones Cards (3D Tactile Celebration Stage & Surprise Contribution Bridge) -->
                <template x-if="milestones.length > 0">
                    <div id="milestonesCard" data-id="milestonesCard">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h4 class="h6 fw-bold mb-0 text-uppercase tracking-wider" style="font-size: 0.8125rem; letter-spacing: 0.05em; color: var(--text-muted-aa, #475569);">
                                🎉 Upcoming Milestones
                            </h4>
                            <span class="badge rounded-pill bg-light border px-2 py-0.5" style="font-size: 0.7rem; color: var(--text-muted-aa, #475569);" x-text="milestones.length + ' Upcoming'"></span>
                        </div>

                        <div class="d-flex flex-column gap-3 ms-3d-viewport">
                            <template x-for="(milestone, idx) in milestones.slice(0, 4)" :key="idx">
                                <div class="ms-3d-stage"
                                     :class="milestone.type === 'event' ? 'ms-3d-stage--event' : ''"
                                     @mousemove="tiltCard($event)"
                                     @mouseleave="resetTilt($event)">

                                    <!-- Top Celebration Crest Row -->
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2.5">
                                        <span class="ms-3d-crest-pill">
                                            <span x-text="milestone.type === 'birthday' ? '🎂' : (milestone.type === 'anniversary' ? '💍' : '📅')"></span>
                                            <span x-text="milestone.landmark_tag || milestone.anniversary_tag || milestone.subtitle"></span>
                                        </span>
                                        <template x-if="milestone.can_contribute_surprise && milestone.surprise_claimed">
                                            <span class="badge rounded-pill px-2.5 py-1 fw-bold"
                                                  style="background: rgba(16, 185, 129, 0.14); color: #065f46; border: 1px solid rgba(16, 185, 129, 0.3); font-size: 0.7rem; white-space: nowrap;">
                                                <i class="bi bi-check-circle-fill me-1"></i> Group Card Active
                                            </span>
                                        </template>
                                    </div>

                                    <!-- 3D Hero Row: Dimensional Celebration Sculpture + Avatar Halo + FULL NAME -->
                                    <div class="d-flex align-items-center gap-3">
                                        <!-- Dimensional 3D SVG Sculpture -->
                                        <div class="flex-shrink-0 ms-3d-elevated-medallion">
                                            <!-- A. Birthday Celebration Cake & Sparkles SVG -->
                                            <template x-if="milestone.type === 'birthday'">
                                                <svg width="54" height="54" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                    <defs>
                                                        <linearGradient id="cakeBase3d" x1="14" y1="34" x2="50" y2="50" gradientUnits="userSpaceOnUse">
                                                            <stop offset="0%" stop-color="#D92638"/>
                                                            <stop offset="55%" stop-color="#A31621"/>
                                                            <stop offset="100%" stop-color="#6B0D15"/>
                                                        </linearGradient>
                                                        <linearGradient id="cakeTop3d" x1="18" y1="22" x2="46" y2="36" gradientUnits="userSpaceOnUse">
                                                            <stop offset="0%" stop-color="#FDE68A"/>
                                                            <stop offset="50%" stop-color="#F59E0B"/>
                                                            <stop offset="100%" stop-color="#B45309"/>
                                                        </linearGradient>
                                                        <radialGradient id="haloGlow3d" cx="50%" cy="50%" r="50%">
                                                            <stop offset="0%" stop-color="#FDE68A" stop-opacity="0.55"/>
                                                            <stop offset="100%" stop-color="#F59E0B" stop-opacity="0"/>
                                                        </radialGradient>
                                                    </defs>
                                                    <circle cx="32" cy="32" r="30" fill="url(#haloGlow3d)"/>
                                                    <ellipse cx="32" cy="51" rx="21" ry="4.5" fill="#78350F" fill-opacity="0.22"/>
                                                    <rect x="14" y="34" width="36" height="15" rx="4.5" fill="url(#cakeBase3d)"/>
                                                    <rect x="18" y="23" width="28" height="12" rx="3.5" fill="url(#cakeTop3d)"/>
                                                    <path d="M15 36C18 39 21 39 24 36C27 39 30 39 33 36C36 39 39 39 42 36C45 39 47 39 49 36" stroke="#FFFBEB" stroke-width="2.2" stroke-linecap="round"/>
                                                    <rect x="25.5" y="13" width="3" height="10" rx="1.5" fill="#EC4899"/>
                                                    <rect x="35.5" y="13" width="3" height="10" rx="1.5" fill="#3B82F6"/>
                                                    <path d="M27 7.5C28.8 9.8 28.8 12.2 27 13C25.2 12.2 25.2 9.8 27 7.5Z" fill="#FBBF24"/>
                                                    <path d="M37 7.5C38.8 9.8 38.8 12.2 37 13C35.2 12.2 35.2 9.8 37 7.5Z" fill="#FBBF24"/>
                                                    <path d="M11 18L12.6 14L14.2 18L18.2 19.6L14.2 21.2L12.6 25.2L11 21.2L7 19.6L11 18Z" fill="#F59E0B"/>
                                                    <path d="M51 13L52.2 10L53.4 13L56.4 14.2L53.4 15.4L52.2 18.4L51 15.4L48 14.2L51 13Z" fill="#B8202C"/>
                                                </svg>
                                            </template>

                                            <!-- B. Anniversary Interlocking Rings & Laurel SVG -->
                                            <template x-if="milestone.type === 'anniversary'">
                                                <svg width="52" height="52" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                    <circle cx="28" cy="28" r="26" fill="#F43F5E" fill-opacity="0.14"/>
                                                    <circle cx="23" cy="30" r="10" stroke="#F59E0B" stroke-width="3.2" fill="none"/>
                                                    <circle cx="33" cy="30" r="10" stroke="#B8202C" stroke-width="3.2" fill="none"/>
                                                    <polygon points="23,14 26.5,18.5 23,23 19.5,18.5" fill="#38BDF8"/>
                                                    <path d="M28 19C28 16.5 30 15 32 15C34.5 15 36 17 36 19C36 22 32 24 28 26C24 24 20 22 20 19C20 17 21.5 15 24 15C26 15 28 16.5 28 19Z" fill="#EC4899"/>
                                                </svg>
                                            </template>

                                            <!-- C. Event Heritage Calendar Pad SVG -->
                                            <template x-if="milestone.type === 'event'">
                                                <svg width="48" height="48" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                    <circle cx="28" cy="28" r="26" fill="#0D9488" fill-opacity="0.14"/>
                                                    <rect x="17" y="18" width="22" height="23" rx="4" fill="#0D9488"/>
                                                    <rect x="17" y="18" width="22" height="8" rx="3" fill="#0F766E"/>
                                                    <rect x="21" y="15" width="2.5" height="5" rx="1" fill="#F59E0B"/>
                                                    <rect x="32.5" y="15" width="2.5" height="5" rx="1" fill="#F59E0B"/>
                                                    <path d="M28 28L29.5 31.5L33.5 32L30.5 34.5L31.5 38.5L28 36.5L24.5 38.5L25.5 34.5L22.5 32L26.5 31.5L28 28Z" fill="#FEF08A"/>
                                                </svg>
                                            </template>
                                        </div>

                                        <!-- Celebratory Avatar(s) with 3D Gold Halo -->
                                        <div class="position-relative flex-shrink-0 ms-3d-elevated-medallion">
                                            <template x-if="milestone.type === 'anniversary'">
                                                <div class="d-flex align-items-center position-relative" style="width: 58px; height: 42px;">
                                                    <img :src="milestone.p1_img ? (milestone.p1_img.startsWith('http') || milestone.p1_img.startsWith('/') ? milestone.p1_img : '/resources/images/profile/' + milestone.p1_img) : '/resources/images/profile/avatarM.png'" 
                                                         class="rounded-circle shadow-xs position-absolute start-0" 
                                                         style="width: 36px; height: 36px; object-fit: cover; border: 2px solid #f59e0b; z-index: 1;" 
                                                         alt="Partner 1"
                                                         onerror="this.src='/resources/images/profile/avatarM.png'">
                                                    <img :src="milestone.p2_img ? (milestone.p2_img.startsWith('http') || milestone.p2_img.startsWith('/') ? milestone.p2_img : '/resources/images/profile/' + milestone.p2_img) : '/resources/images/profile/avatarF.png'" 
                                                         class="rounded-circle shadow-xs position-absolute end-0" 
                                                         style="width: 36px; height: 36px; object-fit: cover; border: 2px solid #b8202c; z-index: 2;" 
                                                         alt="Partner 2"
                                                         onerror="this.src='/resources/images/profile/avatarF.png'">
                                                </div>
                                            </template>

                                            <template x-if="milestone.type === 'birthday'">
                                                <div class="position-relative ms-3d-avatar-ring">
                                                    <img :src="milestone.profile_img ? (milestone.profile_img.startsWith('http') || milestone.profile_img.startsWith('/') ? milestone.profile_img : '/resources/images/profile/' + milestone.profile_img) : '/resources/images/profile/avatarM.png'" 
                                                         class="rounded-circle w-100 h-100" 
                                                         style="object-fit: cover; border: 2px solid #ffffff;" 
                                                         alt="Celebrant"
                                                         onerror="this.src='/resources/images/profile/avatarM.png'">
                                                    <span class="position-absolute" style="top: -9px; right: -5px; font-size: 0.9rem;" title="Celebrant">👑</span>
                                                </div>
                                            </template>

                                            <template x-if="milestone.type === 'event'">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center shadow-xs"
                                                     style="width: 42px; height: 42px; background: rgba(13, 148, 136, 0.15); color: #0d9488; font-size: 1rem;">
                                                    <i class="bi bi-calendar-check-fill"></i>
                                                </div>
                                            </template>
                                        </div>

                                        <!-- Full Name Display (Zero Truncation) -->
                                        <div class="min-w-0 flex-grow-1">
                                            <div class="ms-3d-fullname" x-text="milestone.full_name || milestone.title"></div>
                                            <div class="small fw-semibold mt-0.5" style="color: var(--text-muted-aa, #475569); font-size: 0.78rem;"
                                                 x-text="milestone.type === 'birthday' ? 'Family Birthday Celebration' : (milestone.type === 'anniversary' ? 'Wedding Anniversary' : milestone.subtitle)"></div>
                                        </div>
                                    </div>

                                    <!-- Landmark Tribute Message (Shown ONLY for special jubilee milestones; generic 'Another wonderful year...' removed) -->
                                    <template x-if="milestone.meaningful_message">
                                        <div class="p-2.5 rounded-3 mt-2.5" style="background: rgba(255, 255, 255, 0.75); border-left: 3px solid #b8202c;">
                                            <p class="mb-0 fw-normal" style="font-size: 0.85rem; color: #334155; line-height: 1.45;" x-text="milestone.meaningful_message"></p>
                                        </div>
                                    </template>

                                    <!-- Surprise Group Card & Goodwill Video Actions (Strictly hidden from celebrant / anniversary couple) -->
                                    <template x-if="milestone.can_contribute_surprise">
                                        <div class="ms-surprise-panel">
                                            <div class="d-flex align-items-center gap-1.5 small fw-semibold ms-surprise-whisper">
                                                <i class="bi bi-eye-slash-fill flex-shrink-0" aria-hidden="true"></i>
                                                <span x-text="milestone.type === 'anniversary' ? 'Secret surprise — hidden from the couple' : ('Secret surprise — hidden from ' + (milestone.first_name || 'them'))"></span>
                                            </div>
                                            <div class="ms-surprise-actions">
                                                <a :href="milestone.sign_card_url"
                                                   class="ms-btn-3d-primary btn-spin-on-click"
                                                   data-spin="true"
                                                   :aria-label="'Sign / Send a Card — ' + (milestone.full_name || milestone.title)">
                                                    <i class="bi bi-envelope-heart-fill" aria-hidden="true"></i>
                                                    <span>Sign / Send a Card</span>
                                                </a>
                                                <a :href="milestone.record_video_url"
                                                   class="ms-btn-3d-secondary btn-spin-on-click"
                                                   data-spin="true"
                                                   :aria-label="'Record Goodwill Video — ' + (milestone.full_name || milestone.title)">
                                                    <i class="bi bi-camera-video-fill" aria-hidden="true"></i>
                                                    <span>Record Goodwill Video</span>
                                                </a>
                                            </div>
                                            <template x-if="milestone.is_surprise_organiser">
                                                <div class="pt-2 mt-2 border-top d-flex flex-wrap gap-2" style="border-color: rgba(148, 163, 184, 0.22) !important;">
                                                    <a :href="milestone.manage_cards_url"
                                                       class="btn btn-sm rounded-3 fw-bold flex-fill d-inline-flex align-items-center justify-content-center gap-1.5 btn-spin-on-click ms-organiser-btn-card"
                                                       data-spin="true"
                                                       style="min-height: 44px; min-width: 44px; font-size: 0.78rem; white-space: nowrap; flex-shrink: 0;">
                                                        <i class="bi bi-send-check-fill" aria-hidden="true"></i>
                                                        <span>Manage &amp; Reveal Card</span>
                                                    </a>
                                                    <a :href="milestone.manage_videos_url"
                                                       class="btn btn-sm rounded-3 fw-bold flex-fill d-inline-flex align-items-center justify-content-center gap-1.5 btn-spin-on-click ms-organiser-btn-video"
                                                       data-spin="true"
                                                       style="min-height: 44px; min-width: 44px; font-size: 0.78rem; white-space: nowrap; flex-shrink: 0;">
                                                        <i class="bi bi-film" aria-hidden="true"></i>
                                                        <span>Approve Videos &amp; Cinema</span>
                                                    </a>
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                    <!-- 'in xx days' placed UNDER as requested by user -->
                                    <div class="d-flex align-items-center justify-content-between mt-2.5 pt-2 border-top" style="border-color: rgba(148, 163, 184, 0.22) !important;">
                                        <span class="small fw-semibold d-inline-flex align-items-center gap-1.5" style="font-size: 0.78rem; color: var(--text-muted-aa, #475569);">
                                            <i class="bi bi-calendar3"></i>
                                            <span x-text="milestone.target_date"></span>
                                        </span>
                                        <span class="badge rounded-pill px-2.5 py-1 fw-bold shadow-2xs" 
                                              :class="milestone.days_remaining === 0 ? 'bg-danger text-white' : (milestone.days_remaining === 1 ? 'bg-warning text-dark' : 'bg-white text-dark border')"
                                              style="font-size: 0.74rem; white-space: nowrap;" 
                                              x-text="milestone.date_badge"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Empty State (No memories or milestones today) -->
                <template x-if="memories.length === 0 && milestones.length === 0">
                    <div class="text-center py-3 px-2">
                        <div class="mb-2 d-inline-flex align-items-center justify-content-center rounded-circle p-2.5" 
                             style="width: 44px; height: 44px; background: rgba(184,32,44, 0.08); color: var(--primary-color, #a31621);">
                            <i class="bi bi-camera-reels fs-4"></i>
                        </div>
                        <h6 class="fw-bold mb-1" style="color: var(--text-color, #1f2937); font-size: 0.95rem;">No Flashbacks Today</h6>
                        <p class="text-muted mb-2" style="font-size: 0.875rem; line-height: 1.45;">
                            Keep sharing photos and family moments! They will reappear here as cherished memories on this day in future years.
                        </p>
                        <div>
                            <a href="/organogram" class="btn btn-sm btn-outline-primary rounded-pill px-3 d-inline-flex align-items-center justify-content-center" style="font-size: 0.8rem; min-height: 44px; white-space: nowrap;">
                                <i class="bi bi-compass me-1"></i> Lineage Quests
                            </a>
                        </div>
                    </div>
                </template>

            </div>
        </template>
    </div>

</div>

<script nonce="{{ csp_nonce($nonce ?? null) }}">
if (typeof window.memoriesMilestonesWidget !== 'function') {
    window.memoriesMilestonesWidget = function(widgetId) {
        return {
            widgetId: widgetId || 'memoriesWidgetDesktop',
            loading: true,
            loaded: false,
            memories: [],
            milestones: [],

            initWidget() {
                this.loadData();
            },

            tiltCard(event) {
                if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
                const el = event.currentTarget;
                if (!el) return;
                const rect = el.getBoundingClientRect();
                const relX = ((event.clientX - rect.left) / Math.max(rect.width, 1)) * 2 - 1;
                const relY = ((event.clientY - rect.top) / Math.max(rect.height, 1)) * 2 - 1;
                const rotX = (-relY * 4.2).toFixed(2);
                const rotY = (relX * 4.2).toFixed(2);
                el.style.transform = `rotateX(${rotX}deg) rotateY(${rotY}deg) translateZ(2px)`;
            },

            resetTilt(event) {
                const el = event.currentTarget;
                if (el) {
                    el.style.transform = '';
                }
            },

            formatMemoryMessage(raw) {
                const text = String(raw || '').replace(/\[style:[a-z0-9_]+\]\s*/gi, '').trim();
                if (!text) return '';
                const esc = (s) => String(s)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
                const cfg = window.ATG_LIVING_EMOJIS;
                let knownIds = null;
                if (cfg && Array.isArray(cfg.catalog) && cfg.catalog.length > 0) {
                    knownIds = new Set();
                    const toneable = new Set(cfg.toneable || []);
                    cfg.catalog.forEach((e) => {
                        knownIds.add(e.id);
                        if (toneable.has(e.id)) {
                            (cfg.tones || []).forEach((t) => knownIds.add(e.id.replace(/_3d$/, '_' + t + '_3d')));
                        }
                    });
                }
                const titleFor = (id) => {
                    const base = id.replace(/_(medium_light|medium_dark|light|medium|dark)_3d$/i, '_3d');
                    if (cfg && Array.isArray(cfg.catalog)) {
                        const hit = cfg.catalog.find((e) => e.id === base || e.id === id);
                        if (hit && hit.title) return esc(hit.title);
                    }
                    return esc(base.replace(/_3d$/i, '').split('_').filter(Boolean).map((w) => w.charAt(0).toUpperCase() + w.slice(1)).join(' ') || id);
                };
                return esc(text)
                    .replace(/:me_(hello|laugh|wow|love|nod|dance|point|clap)@([A-Za-z0-9_-]{1,64}):/gi, (_, reaction, uid) => {
                        const r = String(reaction).toLowerCase();
                        return '<img src="/api/emoji/avatar-sticker/' + encodeURIComponent(uid) + '/' + encodeURIComponent(r) + '?v=live2" alt="Avatar sticker: ' + esc(r) + '" width="24" height="24" class="atg-3d-emoji" style="vertical-align:middle;margin:0 2px;">';
                    })
                    .replace(/:([a-z0-9_]+_3d):/gi, (full, rawId) => {
                        const id = String(rawId).toLowerCase();
                        if (knownIds && !knownIds.has(id)) return full;
                        return '<img src="/public/img/emoji/' + id + '.svg?v=20261107a" onerror="if(!this.dataset.fb){this.dataset.fb=\'1\';this.src=\'/img/emoji/' + id + '.svg?v=20261107a\';}" alt="' + titleFor(id) + '" width="20" height="20" class="atg-3d-emoji" style="vertical-align:middle;margin:0 2px;">';
                    });
            },

            async loadData() {
                this.loading = true;
                try {
                    const response = await fetch('/api/memories/today', {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (!response.ok) throw new Error('Failed to fetch memories');
                    const result = await response.json();
                    if (result.status === 'success' && result.data) {
                        this.memories = result.data.memories || [];
                        this.milestones = result.data.milestones || [];
                    }
                } catch (err) {
                    console.warn('[MemoriesWidget] Load error:', err);
                } finally {
                    this.loading = false;
                    this.loaded = true;
                }
            }
        };
    };
}
</script>

<style>
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.spin-anim {
    display: inline-block;
    animation: spin 0.8s linear infinite;
}
</style>
