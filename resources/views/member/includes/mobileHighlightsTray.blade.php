@php
$enrichedEvents = [];
foreach(($eventData ?? []) as $event) {
    $dateDiff = dateDifferenceInt(date('Y-m-d'), $event['eventDate']);
    if ($dateDiff < 0) {
        $dateDifference = abs($dateDiff) === 1 ? 'Yesterday' : abs($dateDiff) . ' days ago';
    } else {
        $getDateDiff = number2word($dateDiff);
        if ($dateDiff === 0 || strtolower($getDateDiff) === 'zero') {
            $dateDifference = 'Today';
        } else if ($dateDiff === 1 || strtolower($getDateDiff) === 'one') {
            $dateDifference = 'Tomorrow';
        } else {
            $dateDifference = "in $getDateDiff Days";
        }
    }

    $enrichedEvents[] = [
        'no' => $event['no'],
        'id' => $event['id'],
        'eventName' => $event['eventName'],
        'eventDate' => dateFormat($event['eventDate']),
        'eventDateRaw' => $event['eventDate'],
        'eventType' => $event['eventType'],
        'eventDescription' => $event['eventDescription'] ?? '',
        'eventFrequency' => $event['eventFrequency'] ?? '',
        'dateDifference' => $dateDifference
    ];
}
@endphp

<!-- Mobile Highlights & Upcoming Events Horizontal Tray (Facebook Mobile UX) -->
<div class="mobile-highlights-container d-lg-none mb-4" 
     id="mobileMemoriesMilestones"
     x-data="mobileHighlightsTray({{ json_encode($enrichedEvents) }})"
     x-init="initTray()"
     x-cloak
     x-show="loaded ? hasHighlights() : (events && events.length > 0)">
    
    <div class="d-flex align-items-center justify-content-between mb-2 px-1">
        <div class="d-flex align-items-center gap-2">
            <span class="badge rounded-pill bg-primary bg-gradient text-white px-2.5 py-1 fw-bold" style="font-size: 0.75rem;">
                <i class="bi bi-stars me-1"></i> Family Highlights
            </span>
            <span class="text-muted small fw-medium" style="font-size: 0.78rem;">Memories, Events &amp; Milestones</span>
        </div>
        <button type="button" 
                class="btn btn-link text-decoration-none small text-primary fw-bold p-0 d-inline-flex align-items-center gap-1 border-0 bg-transparent flex-shrink-0" 
                style="font-size: 0.78rem; min-height: 44px; min-width: 44px; justify-content: flex-end; white-space: nowrap;"
                data-bs-toggle="modal" 
                data-bs-target="#allUpcomingEventsModal"
                aria-label="See all scheduled family events for the next 3 months">
            <span>See All</span> <i class="bi bi-chevron-right"></i>
        </button>
    </div>

    <!-- Horizontal Swipe Carousel -->
    <div class="highlights-scroll-wrapper d-flex gap-3 overflow-x-auto pb-2 pt-1 scrollbar-none"
         style="scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch; scroll-behavior: smooth;">

        <!-- 1. Nostalgia Memory Flashback Card -->
        <template x-if="memories.length > 0">
            <div class="highlight-card flex-shrink-0 p-3 rounded-4 shadow-sm position-relative overflow-hidden"
                 id="mobileMemoriesCard"
                 style="width: 290px; scroll-snap-align: start; background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 252, 0.95) 100%); border: 1px solid var(--border-color, #e2e8f0); box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge rounded-pill px-2.5 py-1 fw-bold text-white d-inline-flex align-items-center gap-1 shadow-sm"
                          style="background: linear-gradient(135deg, #a31621, #d97706); font-size: 0.72rem;">
                        <i class="bi bi-clock-history"></i> <span x-text="memories[0].years_ago_label || 'Memory Flashback'"></span>
                    </span>
                    <span class="badge bg-white bg-opacity-75 text-dark border px-2 py-0.5 rounded-pill small" style="font-size: 0.72rem;" x-text="memories[0].formatted_date"></span>
                </div>

                <!-- Media Preview -->
                <template x-if="memories[0].primary_image">
                    <div class="mb-2 rounded-3 overflow-hidden shadow-sm position-relative" style="height: 120px; border: 2px solid #ffffff;">
                        <img :src="'/resources/images/post/' + memories[0].primary_image" alt="Memory photo" class="w-100 h-100 object-fit-cover">
                        <span class="position-absolute bottom-0 start-0 m-1.5 badge bg-dark bg-opacity-75 text-white rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">
                            <i class="bi bi-camera-fill text-warning"></i> Chronicle
                        </span>
                    </div>
                </template>

                <!-- Text-only Graphic Banner -->
                <template x-if="!memories[0].primary_image">
                    <div class="mb-2 rounded-3 p-2 text-center position-relative overflow-hidden" style="background: rgba(255, 253, 248, 0.9); border: 1px dashed rgba(245, 158, 11, 0.35);">
                        <svg width="48" height="34" viewBox="0 0 72 56" fill="none" xmlns="http://www.w3.org/2000/svg" class="mx-auto mb-1">
                            <path d="M36 16C30 12 18 12 10 15V44C18 41 30 41 36 45C42 41 54 41 62 44V15C54 12 42 12 36 16Z" fill="#FFFDF8" stroke="#D97706" stroke-width="1.6"/>
                            <path d="M36 16V45" stroke="#B45309" stroke-width="1.6"/>
                            <path d="M50 8C48 10 42 22 41 26L43 27C45 23 52 12 53 9C54 6 52 6 50 8Z" fill="#F59E0B"/>
                        </svg>
                        <div class="fw-bold small text-uppercase" style="color: #8a1019; font-size: 0.68rem; letter-spacing: 0.05em;">Family Chronicle</div>
                    </div>
                </template>

                <p class="mb-2 fw-medium text-truncate-2" style="color: var(--text-color, #1f2937); font-size: 0.84rem; line-height: 1.35; height: 2.7em;" x-text="memories[0].postMessage"></p>

                <!-- Meaningful Prompt -->
                <div class="p-1.5 rounded-2 mb-2" style="background: rgba(248, 250, 252, 0.8); border-left: 2px solid #f59e0b; font-size: 0.72rem; color: #475569;">
                    <i class="bi bi-heart-pulse text-warning me-1"></i> <span x-text="memories[0].meaningful_prompt || 'A cherished generational moment.'"></span>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-2 border-top border-light-subtle">
                    <span class="text-muted small fw-semibold text-truncate" style="font-size: 0.75rem; max-width: 140px;" x-text="memories[0].fullName"></span>
                    <span class="badge rounded-pill bg-light text-muted border px-2 py-0.5" style="font-size: 0.68rem;">
                        <i class="bi bi-shield-lock-fill me-1 text-secondary"></i> Private Chronicle
                    </span>
                </div>
            </div>
        </template>

        <!-- 2. Upcoming Milestones Cards (Graphics Rich & Strictly Non-Shareable) -->
        <template x-for="(milestone, idx) in milestones" :key="'ms-' + idx">
            <div class="highlight-card flex-shrink-0 p-3 rounded-4 shadow-sm position-relative"
                 id="mobileMilestonesCard"
                 style="width: 280px; scroll-snap-align: start; background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 252, 0.95) 100%); border: 1px solid var(--border-color, #e2e8f0); box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                
                <!-- Top Row: Landmark Badge -->
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge rounded-pill px-2.5 py-1 fw-bold"
                          style="background: linear-gradient(135deg, rgba(184,32,44,0.12), rgba(245,158,11,0.15)); color: #8a1019; font-size: 0.72rem;"
                          x-text="milestone.landmark_tag || milestone.anniversary_tag || (milestone.type === 'birthday' ? 'Birthday' : (milestone.type === 'anniversary' ? 'Anniversary' : 'Event'))">
                    </span>
                </div>

                <!-- Graphics & Celebratory Avatar Row -->
                <div class="d-flex align-items-center gap-2.5 mb-2">
                    <!-- SVG Graphics Icon -->
                    <div class="flex-shrink-0">
                        <template x-if="milestone.type === 'birthday'">
                            <svg width="40" height="40" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="28" cy="28" r="26" fill="#F59E0B" fill-opacity="0.14"/>
                                <rect x="16" y="32" width="24" height="12" rx="3" fill="#B8202C"/>
                                <rect x="18" y="24" width="20" height="9" rx="2" fill="#F59E0B"/>
                                <path d="M16 33C18 35 20 35 22 33C24 35 26 35 28 33C30 35 32 35 34 33C36 35 38 35 40 33" stroke="#FFF7ED" stroke-width="1.5" stroke-linecap="round"/>
                                <circle cx="24.25" cy="14" r="2" fill="#F59E0B"/>
                                <circle cx="31.75" cy="14" r="2" fill="#F59E0B"/>
                            </svg>
                        </template>
                        <template x-if="milestone.type === 'anniversary'">
                            <svg width="40" height="40" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="28" cy="28" r="26" fill="#F43F5E" fill-opacity="0.14"/>
                                <circle cx="23" cy="29" r="9" stroke="#F59E0B" stroke-width="2.5" fill="none"/>
                                <circle cx="33" cy="29" r="9" stroke="#B8202C" stroke-width="2.5" fill="none"/>
                                <path d="M28 20C28 17.5 30 16 32 16C34.5 16 36 18 36 20C36 23 32 25 28 27C24 25 20 23 20 20C20 18 21.5 16 24 16C26 16 28 17.5 28 20Z" fill="#EC4899" fill-opacity="0.8"/>
                            </svg>
                        </template>
                        <template x-if="milestone.type === 'event'">
                            <svg width="40" height="40" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="28" cy="28" r="26" fill="#0D9488" fill-opacity="0.14"/>
                                <rect x="17" y="18" width="22" height="23" rx="4" fill="#0D9488"/>
                                <path d="M28 28L29.5 31.5L33.5 32L30.5 34.5L31.5 38.5L28 36.5L24.5 38.5L25.5 34.5L22.5 32L26.5 31.5L28 28Z" fill="#FEF08A"/>
                            </svg>
                        </template>
                    </div>

                    <!-- Celebratory Avatar(s) with Halo -->
                    <div class="position-relative flex-shrink-0">
                        <template x-if="milestone.type === 'anniversary'">
                            <div class="d-flex align-items-center position-relative" style="width: 48px; height: 34px;">
                                <img :src="milestone.p1_img ? (milestone.p1_img.startsWith('http') || milestone.p1_img.startsWith('/') ? milestone.p1_img : '/resources/images/profile/' + milestone.p1_img) : '/resources/images/profile/avatarM.png'" 
                                     class="rounded-circle shadow-xs position-absolute start-0" 
                                     style="width: 28px; height: 28px; object-fit: cover; border: 2px solid #ffffff; z-index: 1;" 
                                     alt="Partner 1"
                                     onerror="this.src='/resources/images/profile/avatarM.png'">
                                <img :src="milestone.p2_img ? (milestone.p2_img.startsWith('http') || milestone.p2_img.startsWith('/') ? milestone.p2_img : '/resources/images/profile/' + milestone.p2_img) : '/resources/images/profile/avatarF.png'" 
                                     class="rounded-circle shadow-xs position-absolute end-0" 
                                     style="width: 28px; height: 28px; object-fit: cover; border: 2px solid #ffffff; z-index: 2;" 
                                     alt="Partner 2"
                                     onerror="this.src='/resources/images/profile/avatarF.png'">
                            </div>
                        </template>
                        <template x-if="milestone.type === 'birthday'">
                            <div class="position-relative">
                                <img :src="milestone.profile_img || milestone.profileImg ? '/resources/images/profile/' + (milestone.profile_img || milestone.profileImg) : '/resources/images/profile/avatarM.png'" 
                                     class="rounded-circle shadow-xs" 
                                     style="width: 34px; height: 34px; object-fit: cover; border: 2px solid #f59e0b;" 
                                     alt="Celebrant"
                                     onerror="this.src='/resources/images/profile/avatarM.png'">
                                <span class="position-absolute" style="top: -8px; right: -4px; font-size: 0.78rem;">👑</span>
                            </div>
                        </template>
                        <template x-if="milestone.type === 'event'">
                            <div class="rounded-circle d-flex align-items-center justify-content-center shadow-xs"
                                 style="width: 34px; height: 34px; background: rgba(13, 148, 136, 0.15); color: #0d9488; font-size: 0.9rem;">
                                <i class="bi bi-calendar-check-fill"></i>
                            </div>
                        </template>
                    </div>

                    <div class="overflow-hidden min-w-0">
                        <h6 class="fw-bold mb-0 text-truncate" style="font-size: 0.9125rem; color: var(--text-color, #1f2937);" x-text="milestone.title"></h6>
                        <p class="mb-0 text-truncate" style="font-size: 0.78rem; color: var(--text-muted-aa, #475569); line-height: 1.3;" x-text="milestone.subtitle"></p>
                    </div>
                </div>

                <!-- Meaningful Emotional Tribute Message -->
                <div class="p-2 rounded-3" style="background: rgba(241, 245, 249, 0.65); border-left: 3px solid #b8202c;">
                    <p class="mb-0 fw-normal" style="font-size: 0.76rem; color: #334155; line-height: 1.4;" x-text="milestone.meaningful_message || milestone.subtitle"></p>
                </div>

                <!-- 'in xx days' placed UNDER as requested by user -->
                <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top border-light-subtle">
                    <span class="text-muted small" style="font-size: 0.72rem;" x-text="milestone.target_date"></span>
                    <span class="badge rounded-pill px-2 py-0.5 fw-bold shadow-2xs"
                          :class="milestone.days_remaining === 0 ? 'bg-danger text-white' : (milestone.days_remaining === 1 ? 'bg-warning text-dark' : 'bg-light text-primary border')"
                          style="font-size: 0.72rem;"
                          x-text="milestone.date_badge || milestone.days_until_label || 'Upcoming'"></span>
                </div>

                <!-- NOTE: Strictly Non-Shareable upcoming milestone. Zero composer triggers or share buttons. -->
            </div>
        </template>

        <!-- 3. Upcoming Family Events Cards -->
        <template x-for="event in events" :key="'evt-' + event.no">
            <div class="highlight-card flex-shrink-0 p-3 rounded-4 shadow-sm position-relative"
                 style="width: 250px; scroll-snap-align: start; background: linear-gradient(135deg, rgba(163,22,33, 0.08) 0%, rgba(16, 185, 129, 0.08) 100%); border: 1px solid rgba(163,22,33, 0.2);">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge rounded-pill px-2.5 py-1 fw-bold text-white bg-primary bg-gradient shadow-sm" style="font-size: 0.75rem;">
                        <i class="bi bi-calendar-event me-1"></i> Event
                    </span>
                    <span class="badge rounded-pill px-2 py-1 fw-bold" style="font-size: 0.75rem; background: #f5dede; color: #8a1019;" x-text="event.dateDifference"></span>
                </div>

                <h6 class="fw-bold mb-1 text-truncate" style="font-size: 0.95rem; color: var(--text-color, #1f2937);" x-text="event.eventName"></h6>

                <div class="d-flex align-items-center mb-1" style="font-size: 0.875rem; color: var(--text-muted-aa, #475569);">
                    <i class="bi bi-clock me-1 text-primary"></i>
                    <span x-text="event.eventDate"></span>
                </div>
                <div class="d-flex align-items-center mb-2" style="font-size: 0.875rem; color: var(--text-muted-aa, #475569);">
                    <i class="bi bi-geo-alt me-1 text-danger"></i>
                    <span class="text-truncate" x-text="event.eventType"></span>
                </div>

                <div class="pt-2 border-top border-light-subtle d-flex justify-content-end">
                    <a href="/profilePage#eventHeader" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-bold shadow-xs d-inline-flex align-items-center justify-content-center" style="font-size: 0.85rem; min-height: 44px;">
                        View Event
                    </a>
                </div>
            </div>
        </template>

    </div>
</div>

<style>
.scrollbar-none::-webkit-scrollbar { display: none; }
.scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
.text-truncate-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<script>
if (typeof window.mobileHighlightsTray !== 'function') {
    window.mobileHighlightsTray = function(initialEvents) {
        return {
            events: initialEvents || [],
            memories: [],
            milestones: [],
            loaded: false,
            initTray() {
                fetch('/api/memories/today')
                    .then(r => r.json())
                    .then(res => {
                        if (res.status === 'success' && res.data) {
                            this.memories = res.data.memories || [];
                            this.milestones = res.data.milestones || [];
                        }
                        this.loaded = true;
                    })
                    .catch(err => {
                        console.error('Mobile highlights tray error:', err);
                        this.loaded = true;
                    });
            },
            hasHighlights() {
                return (this.memories && this.memories.length > 0) ||
                       (this.milestones && this.milestones.length > 0) ||
                       (this.events && this.events.length > 0);
            }
        };
    };
}
</script>
