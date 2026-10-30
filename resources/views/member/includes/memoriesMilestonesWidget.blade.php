@php
$wId = $widgetId ?? 'memoriesWidgetDesktop';
@endphp
<style>
  body.dark-mode #memoriesCard,
  [data-bs-theme="dark"] #memoriesCard {
    background: var(--card-bg, #1e293b) !important;
    border: 1px solid var(--border-color, #334155) !important;
  }
</style>
<div class="card border-0 shadow-sm mb-4 overflow-hidden" 
     id="{{ $wId }}"
     data-id="memoriesMilestonesWidget"
     style="border-radius: 20px; background-color: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e5e7eb);"
     x-data="memoriesMilestonesWidget('{{ $wId }}')"
     x-init="initWidget()"
     x-show="loaded ? (memories.length > 0 || milestones.length > 0) : true">
    
    <!-- Widget Header -->
    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3 pb-2 px-3 px-sm-4">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" 
                 style="width: 36px; height: 36px; background: rgba(184,32,44, 0.12); color: var(--primary-color, #a31621);">
                <i class="bi bi-stars fs-6 text-primary"></i>
            </div>
            <div>
                <h5 class="mb-0 fw-bold" style="color: var(--text-color, #1f2937); font-size: 1rem;">{{ __('milestones.title', 'Memories & Milestones') }}</h5>
                <p class="text-muted small mb-0" style="font-size: 0.78rem;">{{ __('milestones.subtitle', 'On this day flashbacks & family celebrations') }}</p>
            </div>
        </div>
        <button type="button" 
                class="btn btn-sm btn-light border rounded-circle d-flex align-items-center justify-content-center text-muted shadow-xs" 
                @click="loadData()" 
                title="Refresh memories" 
                aria-label="Refresh memories"
                style="width: 34px; height: 34px; min-width: 34px; min-height: 34px; padding: 0;">
            <i class="bi bi-arrow-clockwise" :class="{'spin-anim': loading}"></i>
        </button>
    </div>

    <!-- Widget Body -->
    <div class="card-body px-3 px-sm-4 pt-1 pb-3 pb-sm-4">
        <!-- Loading State -->
        <template x-if="loading && !loaded">
            <div class="py-4 text-center">
                <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                <p class="text-muted small mb-0" style="font-size: 0.8rem;">{{ __('milestones.gathering', 'Gathering family memories...') }}</p>
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

                        <!-- Graphics-Rich Memory Media -->
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
                                &ldquo;<span x-text="memories[0].postMessage"></span>&rdquo;
                            </p>
                        </div>

                        <!-- Meaningful Generational Prompt -->
                        <div class="p-2 rounded-3 mb-2.5" style="background: rgba(248, 250, 252, 0.8); border-left: 3px solid #f59e0b; font-size: 0.78rem; color: #475569;">
                            <i class="bi bi-heart-pulse-fill text-warning me-1"></i> <span x-text="memories[0].meaningful_prompt || 'A cherished moment etched forever in our family story.'"></span>
                        </div>

                        <div class="pt-2 border-top border-light-subtle d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <!-- Author Attribution -->
                            <div class="d-flex align-items-center gap-2">
                                <template x-if="memories[0].profileImg">
                                    <img :src="'/resources/images/profile/' + memories[0].profileImg" class="rounded-circle flex-shrink-0 shadow-2xs" style="width: 26px; height: 26px; object-fit: cover; border: 1px solid #e2e8f0;" alt="Author">
                                </template>
                                <div class="text-truncate">
                                    <span class="text-dark small fw-bold text-truncate d-block" style="font-size: 0.8125rem;" x-text="memories[0].fullName"></span>
                                    <span class="text-muted small" style="font-size: 0.7rem;">Family Story Contributor</span>
                                </div>
                            </div>

                            <!-- Private Family Sanctuary Indicator (Zero external social buttons) -->
                            <span class="badge rounded-pill bg-light text-muted border px-2.5 py-1 d-inline-flex align-items-center gap-1 shadow-2xs" style="font-size: 0.72rem;">
                                <i class="bi bi-shield-lock-fill text-secondary"></i> Private Family Chronicle
                            </span>
                        </div>
                    </div>
                </template>

                <!-- 2. Upcoming Milestones Cards (Graphics Rich & Strictly Non-Shareable) -->
                <template x-if="milestones.length > 0">
                    <div id="milestonesCard" data-id="milestonesCard">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="fw-bold mb-0 text-uppercase tracking-wider" style="font-size: 0.8125rem; letter-spacing: 0.05em; color: var(--text-muted-aa, #475569);">
                                🎉 Upcoming Milestones
                            </h6>
                            <span class="badge rounded-pill bg-light text-muted border px-2 py-0.5" style="font-size: 0.7rem;" x-text="milestones.length + ' Upcoming'"></span>
                        </div>

                        <div class="d-flex flex-column gap-2.5">
                            <template x-for="(milestone, idx) in milestones.slice(0, 4)" :key="idx">
                                <div class="p-3 rounded-4 position-relative overflow-hidden transition-all shadow-xs" 
                                     style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 252, 0.95) 100%); border: 1px solid var(--border-color, #e2e8f0); box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                                    
                                    <!-- Header Row: Graphics & Avatar Badge + Title -->
                                    <div class="d-flex align-items-start gap-2.5 mb-2">
                                        
                                        <!-- Graphics-Rich Bespoke SVG Celebration Illustration -->
                                        <div class="flex-shrink-0">
                                            <!-- A. Birthday Celebration Cake & Sparkles SVG -->
                                            <template x-if="milestone.type === 'birthday'">
                                                <svg width="44" height="44" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0">
                                                    <circle cx="28" cy="28" r="26" fill="#F59E0B" fill-opacity="0.14"/>
                                                    <circle cx="28" cy="28" r="24" stroke="#F59E0B" stroke-width="1.4" stroke-dasharray="3 3"/>
                                                    <rect x="16" y="32" width="24" height="12" rx="3" fill="#B8202C"/>
                                                    <rect x="18" y="24" width="20" height="9" rx="2" fill="#F59E0B"/>
                                                    <path d="M16 33C18 35 20 35 22 33C24 35 26 35 28 33C30 35 32 35 34 33C36 35 38 35 40 33" stroke="#FFF7ED" stroke-width="1.5" stroke-linecap="round"/>
                                                    <rect x="23" y="17" width="2.5" height="7" rx="1" fill="#EC4899"/>
                                                    <rect x="30.5" y="17" width="2.5" height="7" rx="1" fill="#3B82F6"/>
                                                    <circle cx="24.25" cy="14" r="2" fill="#F59E0B"/>
                                                    <circle cx="31.75" cy="14" r="2" fill="#F59E0B"/>
                                                    <path d="M11 19L12.5 15L14 19L18 20.5L14 22L12.5 26L11 22L7 20.5L11 19Z" fill="#F59E0B" fill-opacity="0.8"/>
                                                    <path d="M43 14L44 11L45 14L48 15L45 16L44 19L43 16L40 15L43 14Z" fill="#B8202C" fill-opacity="0.7"/>
                                                </svg>
                                            </template>

                                            <!-- B. Anniversary Interlocking Rings & Laurel SVG -->
                                            <template x-if="milestone.type === 'anniversary'">
                                                <svg width="44" height="44" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0">
                                                    <circle cx="28" cy="28" r="26" fill="#F43F5E" fill-opacity="0.14"/>
                                                    <circle cx="28" cy="28" r="24" stroke="#F43F5E" stroke-width="1.4" stroke-dasharray="3 3"/>
                                                    <circle cx="23" cy="29" r="10" stroke="#F59E0B" stroke-width="2.8" fill="none"/>
                                                    <circle cx="33" cy="29" r="10" stroke="#B8202C" stroke-width="2.8" fill="none"/>
                                                    <polygon points="23,15 26,19 23,23 20,19" fill="#38BDF8"/>
                                                    <path d="M28 20C28 17.5 30 16 32 16C34.5 16 36 18 36 20C36 23 32 25 28 27C24 25 20 23 20 20C20 18 21.5 16 24 16C26 16 28 17.5 28 20Z" fill="#EC4899" fill-opacity="0.8"/>
                                                </svg>
                                            </template>

                                            <!-- C. Event Heritage Calendar Pad SVG -->
                                            <template x-if="milestone.type === 'event'">
                                                <svg width="44" height="44" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0">
                                                    <circle cx="28" cy="28" r="26" fill="#0D9488" fill-opacity="0.14"/>
                                                    <circle cx="28" cy="28" r="24" stroke="#0D9488" stroke-width="1.4" stroke-dasharray="3 3"/>
                                                    <rect x="17" y="18" width="22" height="23" rx="4" fill="#0D9488"/>
                                                    <rect x="17" y="18" width="22" height="8" rx="3" fill="#0F766E"/>
                                                    <rect x="21" y="15" width="2.5" height="5" rx="1" fill="#F59E0B"/>
                                                    <rect x="32.5" y="15" width="2.5" height="5" rx="1" fill="#F59E0B"/>
                                                    <path d="M28 28L29.5 31.5L33.5 32L30.5 34.5L31.5 38.5L28 36.5L24.5 38.5L25.5 34.5L22.5 32L26.5 31.5L28 28Z" fill="#FEF08A"/>
                                                </svg>
                                            </template>
                                        </div>

                                        <!-- Celebratory Avatar(s) with Halo Framing -->
                                        <div class="position-relative flex-shrink-0">
                                            <!-- Anniversary Dual Overlapping Avatars -->
                                            <template x-if="milestone.type === 'anniversary'">
                                                <div class="d-flex align-items-center position-relative" style="width: 50px; height: 36px;">
                                                    <img :src="milestone.p1_img ? (milestone.p1_img.startsWith('http') || milestone.p1_img.startsWith('/') ? milestone.p1_img : '/resources/images/profile/' + milestone.p1_img) : '/resources/images/profile/avatarM.png'" 
                                                         class="rounded-circle shadow-xs position-absolute start-0" 
                                                         style="width: 30px; height: 30px; object-fit: cover; border: 2px solid #ffffff; z-index: 1;" 
                                                         alt="Partner 1"
                                                         onerror="this.src='/resources/images/profile/avatarM.png'">
                                                    <img :src="milestone.p2_img ? (milestone.p2_img.startsWith('http') || milestone.p2_img.startsWith('/') ? milestone.p2_img : '/resources/images/profile/' + milestone.p2_img) : '/resources/images/profile/avatarF.png'" 
                                                         class="rounded-circle shadow-xs position-absolute end-0" 
                                                         style="width: 30px; height: 30px; object-fit: cover; border: 2px solid #ffffff; z-index: 2;" 
                                                         alt="Partner 2"
                                                         onerror="this.src='/resources/images/profile/avatarF.png'">
                                                </div>
                                            </template>

                                            <!-- Birthday Avatar with Celebratory Crown -->
                                            <template x-if="milestone.type === 'birthday'">
                                                <div class="position-relative">
                                                    <img :src="milestone.profile_img ? '/resources/images/profile/' + milestone.profile_img : '/resources/images/profile/avatarM.png'" 
                                                         class="rounded-circle shadow-xs" 
                                                         style="width: 36px; height: 36px; object-fit: cover; border: 2px solid #f59e0b;" 
                                                         alt="Celebrant"
                                                         onerror="this.src='/resources/images/profile/avatarM.png'">
                                                    <span class="position-absolute" style="top: -8px; right: -4px; font-size: 0.82rem;" title="Celebrant">👑</span>
                                                </div>
                                            </template>

                                            <!-- Event Calendar Badge -->
                                            <template x-if="milestone.type === 'event'">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center shadow-xs"
                                                     style="width: 36px; height: 36px; background: rgba(13, 148, 136, 0.15); color: #0d9488; font-size: 0.95rem;">
                                                    <i class="bi bi-calendar-check-fill"></i>
                                                </div>
                                            </template>
                                        </div>

                                        <!-- Title & Landmark Tag -->
                                        <div class="min-w-0 flex-grow-1">
                                            <div class="fw-bold text-truncate" style="color: var(--text-color, #1f2937); font-size: 0.9125rem; line-height: 1.3;" x-text="milestone.title"></div>
                                            <div class="d-flex align-items-center gap-1.5 flex-wrap mt-0.5">
                                                <template x-if="milestone.landmark_tag || milestone.anniversary_tag">
                                                    <span class="badge rounded-pill px-2 py-0.5 fw-bold shadow-2xs" 
                                                          style="background: linear-gradient(135deg, rgba(184,32,44,0.12), rgba(245,158,11,0.15)); color: #8a1019; font-size: 0.72rem;" 
                                                          x-text="milestone.landmark_tag || milestone.anniversary_tag"></span>
                                                </template>
                                                <template x-if="!milestone.landmark_tag && !milestone.anniversary_tag && milestone.subtitle">
                                                    <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5" 
                                                          style="font-size: 0.72rem;" 
                                                          x-text="milestone.subtitle"></span>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Meaningful Emotional Tribute Message (The content that MEANS something to users!) -->
                                    <div class="p-2 rounded-3 mt-1.5" style="background: rgba(241, 245, 249, 0.65); border-left: 3px solid #b8202c;">
                                        <p class="mb-0 fw-normal" style="font-size: 0.8rem; color: #334155; line-height: 1.45;" x-text="milestone.meaningful_message || milestone.subtitle"></p>
                                    </div>

                                    <!-- 'in xx days' placed UNDER as requested by user -->
                                    <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top border-light-subtle">
                                        <span class="text-muted small d-inline-flex align-items-center gap-1" style="font-size: 0.74rem;">
                                            <i class="bi bi-calendar3 text-secondary"></i>
                                            <span x-text="milestone.target_date"></span>
                                        </span>
                                        <span class="badge rounded-pill px-2.5 py-1 fw-bold shadow-2xs" 
                                              :class="milestone.days_remaining === 0 ? 'bg-danger text-white' : (milestone.days_remaining === 1 ? 'bg-warning text-dark' : 'bg-light text-primary border')"
                                              style="font-size: 0.74rem; white-space: nowrap;" 
                                              x-text="milestone.date_badge"></span>
                                    </div>

                                    <!-- NOTE: Upcoming milestones are strictly non-shareable. Zero share buttons by CEO & TAT mandate. -->
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
                        <h6 class="fw-bold mb-1" style="color: var(--text-color, #1f2937); font-size: 0.88rem;">No Flashbacks Today</h6>
                        <p class="text-muted small mb-2" style="font-size: 0.78rem; line-height: 1.4;">
                            Keep sharing photos and family moments! They will reappear here as cherished memories on this day in future years.
                        </p>
                        <div>
                            <a href="/organogram" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1" style="font-size: 0.78rem;">
                                <i class="bi bi-compass me-1"></i> Lineage Quests
                            </a>
                        </div>
                    </div>
                </template>

            </div>
        </template>
    </div>

</div>

<script>
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
