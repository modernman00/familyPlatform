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
                    <span class="badge rounded-pill small border px-2 py-0.5" style="font-size: 0.72rem; background-color: var(--card-bg, #ffffff); color: var(--text-color, #0f172a);" x-text="memories[0].formatted_date"></span>
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

                <p class="mb-2 fw-medium text-truncate-2" style="color: var(--text-color, #1f2937); font-size: 0.84rem; line-height: 1.35; height: 2.7em;" x-html="formatMemoryMessage(memories[0].postMessage)"></p>

                <!-- Meaningful Prompt -->
                <div class="p-1.5 rounded-2 mb-2" style="background: rgba(248, 250, 252, 0.8); border-left: 2px solid #f59e0b; font-size: 0.72rem; color: #475569;">
                    <i class="bi bi-heart-pulse text-warning me-1"></i> <span x-text="memories[0].meaningful_prompt || 'A cherished generational moment.'"></span>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-2 border-top border-light-subtle">
                    <span class="text-muted small fw-semibold" style="font-size: 0.75rem; overflow-wrap: anywhere;" x-text="memories[0].fullName"></span>
                    <span class="badge rounded-pill bg-light text-muted border px-2 py-0.5 flex-shrink-0" style="font-size: 0.68rem;">
                        <i class="bi bi-shield-lock-fill me-1 text-secondary"></i> Private Chronicle
                    </span>
                </div>
            </div>
        </template>

        <!-- 2. Upcoming Milestones Cards (3D Tactile Celebration Stage & Surprise Contribution Bridge) -->
        <template x-for="(milestone, idx) in milestones" :key="'ms-' + idx">
            <div class="highlight-card mobile-ms-3d-card flex-shrink-0 p-3 rounded-4 position-relative d-flex flex-column justify-content-between"
                 id="mobileMilestonesCard"
                 :class="milestone.type === 'event' ? 'mobile-ms-3d-card--event' : ''"
                 style="width: 304px; scroll-snap-align: start;">
                
                <div>
                    <!-- Top Row: Landmark Badge -->
                    <div class="d-flex justify-content-between align-items-center gap-1 flex-wrap mb-2">
                        <span class="badge rounded-pill px-2.5 py-1 fw-bold"
                              style="background: rgba(163, 22, 33, 0.1); color: #8a1019; border: 1px solid rgba(163, 22, 33, 0.18); font-size: 0.72rem; white-space: nowrap;"
                              x-text="milestone.landmark_tag || milestone.anniversary_tag || (milestone.type === 'birthday' ? '🎂 Birthday' : (milestone.type === 'anniversary' ? '💍 Anniversary' : '📅 Event'))">
                        </span>
                        <template x-if="milestone.can_contribute_surprise && milestone.surprise_claimed">
                            <span class="badge rounded-pill px-2 py-0.5 fw-bold"
                                  style="background: rgba(16, 185, 129, 0.14); color: #065f46; border: 1px solid rgba(16, 185, 129, 0.3); font-size: 0.68rem; white-space: nowrap;">
                                Shared Card Active
                            </span>
                        </template>
                    </div>

                    <!-- 3D Graphics & Celebratory Avatar + Full Name Row -->
                    <div class="d-flex align-items-center gap-2.5 mb-2">
                        <!-- 3D SVG Graphics Icon -->
                        <div class="flex-shrink-0" style="filter: drop-shadow(0 5px 8px rgba(163, 22, 33, 0.16));">
                            <template x-if="milestone.type === 'birthday'">
                                <svg width="44" height="44" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <circle cx="32" cy="32" r="28" fill="#FDE68A" fill-opacity="0.35"/>
                                    <ellipse cx="32" cy="50" rx="20" ry="4" fill="#78350F" fill-opacity="0.2"/>
                                    <rect x="14" y="34" width="36" height="14" rx="4" fill="#B8202C"/>
                                    <rect x="18" y="23" width="28" height="12" rx="3.5" fill="#F59E0B"/>
                                    <path d="M15 36C18 39 21 39 24 36C27 39 30 39 33 36C36 39 39 39 42 36C45 39 47 39 49 36" stroke="#FFFBEB" stroke-width="2" stroke-linecap="round"/>
                                    <rect x="26" y="13" width="3" height="10" rx="1.5" fill="#EC4899"/>
                                    <rect x="35" y="13" width="3" height="10" rx="1.5" fill="#3B82F6"/>
                                    <circle cx="27.5" cy="10" r="2.2" fill="#FBBF24"/>
                                    <circle cx="36.5" cy="10" r="2.2" fill="#FBBF24"/>
                                </svg>
                            </template>
                            <template x-if="milestone.type === 'anniversary'">
                                <svg width="42" height="42" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <circle cx="28" cy="28" r="26" fill="#F43F5E" fill-opacity="0.14"/>
                                    <circle cx="23" cy="29" r="9" stroke="#F59E0B" stroke-width="2.8" fill="none"/>
                                    <circle cx="33" cy="29" r="9" stroke="#B8202C" stroke-width="2.8" fill="none"/>
                                    <path d="M28 20C28 17.5 30 16 32 16C34.5 16 36 18 36 20C36 23 32 25 28 27C24 25 20 23 20 20C20 18 21.5 16 24 16C26 16 28 17.5 28 20Z" fill="#EC4899"/>
                                </svg>
                            </template>
                            <template x-if="milestone.type === 'event'">
                                <svg width="42" height="42" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <circle cx="28" cy="28" r="26" fill="#0D9488" fill-opacity="0.14"/>
                                    <rect x="17" y="18" width="22" height="23" rx="4" fill="#0D9488"/>
                                    <path d="M28 28L29.5 31.5L33.5 32L30.5 34.5L31.5 38.5L28 36.5L24.5 38.5L25.5 34.5L22.5 32L26.5 31.5L28 28Z" fill="#FEF08A"/>
                                </svg>
                            </template>
                        </div>

                        <!-- Celebratory Avatar(s) with Halo -->
                        <div class="position-relative flex-shrink-0">
                            <template x-if="milestone.type === 'anniversary'">
                                <div class="d-flex align-items-center position-relative" style="width: 50px; height: 36px;">
                                    <img :src="milestone.p1_img ? (milestone.p1_img.startsWith('http') || milestone.p1_img.startsWith('/') ? milestone.p1_img : '/resources/images/profile/' + milestone.p1_img) : '/resources/images/profile/avatarM.png'" 
                                         class="rounded-circle shadow-xs position-absolute start-0" 
                                         style="width: 30px; height: 30px; object-fit: cover; border: 2px solid #f59e0b; z-index: 1;" 
                                         alt="Partner 1"
                                         onerror="this.src='/resources/images/profile/avatarM.png'">
                                    <img :src="milestone.p2_img ? (milestone.p2_img.startsWith('http') || milestone.p2_img.startsWith('/') ? milestone.p2_img : '/resources/images/profile/' + milestone.p2_img) : '/resources/images/profile/avatarF.png'" 
                                         class="rounded-circle shadow-xs position-absolute end-0" 
                                         style="width: 30px; height: 30px; object-fit: cover; border: 2px solid #b8202c; z-index: 2;" 
                                         alt="Partner 2"
                                         onerror="this.src='/resources/images/profile/avatarF.png'">
                                </div>
                            </template>
                            <template x-if="milestone.type === 'birthday'">
                                <div class="position-relative" style="width: 40px; height: 40px; padding: 2px; border-radius: 50%; background: linear-gradient(135deg, #f59e0b, #b8202c);">
                                    <img :src="(milestone.profile_img || milestone.profileImg) ? ((milestone.profile_img || milestone.profileImg).startsWith('http') || (milestone.profile_img || milestone.profileImg).startsWith('/') ? (milestone.profile_img || milestone.profileImg) : '/resources/images/profile/' + (milestone.profile_img || milestone.profileImg)) : '/resources/images/profile/avatarM.png'" 
                                         class="rounded-circle w-100 h-100" 
                                         style="object-fit: cover; border: 1.5px solid #ffffff;" 
                                         alt="Celebrant"
                                         onerror="this.src='/resources/images/profile/avatarM.png'">
                                    <span class="position-absolute" style="top: -8px; right: -4px; font-size: 0.78rem;">👑</span>
                                </div>
                            </template>
                            <template x-if="milestone.type === 'event'">
                                <div class="rounded-circle d-flex align-items-center justify-content-center shadow-xs"
                                     style="width: 36px; height: 36px; background: rgba(13, 148, 136, 0.15); color: #0d9488; font-size: 0.9rem;">
                                    <i class="bi bi-calendar-check-fill"></i>
                                </div>
                            </template>
                        </div>

                        <!-- Full Name Shown Without Truncation -->
                        <div class="min-w-0 flex-grow-1">
                            <h3 class="h6 fw-bold mb-0 mobile-ms-fullname" x-text="milestone.full_name || milestone.title"></h3>
                            <p class="mb-0" style="font-size: 0.76rem; color: var(--text-muted-aa, #475569); line-height: 1.3;" x-text="milestone.subtitle"></p>
                        </div>
                    </div>

                    <!-- Landmark Emotional Tribute Message (only when landmark message exists) -->
                    <template x-if="milestone.meaningful_message">
                        <div class="p-2 rounded-3 mb-2" style="background: rgba(255, 255, 255, 0.76); border-left: 3px solid #b8202c;">
                            <p class="mb-0 fw-normal" style="font-size: 0.76rem; color: #334155; line-height: 1.4;" x-text="milestone.meaningful_message"></p>
                        </div>
                    </template>

                    <!-- Secret Family Surprise Actions (Strictly omitted for celebrant / couple) -->
                    <template x-if="milestone.can_contribute_surprise">
                        <div class="p-2 rounded-3 mt-1.5 mobile-ms-surprise-panel">
                            <div class="d-flex align-items-center gap-1 fw-semibold mb-1.5 mobile-ms-surprise-whisper">
                                <i class="bi bi-eye-slash-fill flex-shrink-0" aria-hidden="true"></i>
                                <span x-text="milestone.type === 'anniversary' ? 'Hidden from the couple' : ('Hidden from ' + (milestone.first_name || 'them'))"></span>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                <a :href="milestone.sign_card_url"
                                   data-spin="true"
                                   class="btn w-100 fw-bold d-inline-flex align-items-center justify-content-center gap-1.5 btn-spin-on-click"
                                   style="min-height: 44px; min-width: 44px; border-radius: 12px; font-size: 0.82rem; white-space: nowrap; flex-shrink: 0; background: linear-gradient(135deg, #a31621 0%, #8a1019 100%); color: #ffffff !important; border: 1px solid #7f1d1d; border-bottom: 3px solid #5c0b11;">
                                    <i class="bi bi-envelope-heart-fill" aria-hidden="true"></i>
                                    <span>Sign / Send a Card</span>
                                </a>
                                <a :href="milestone.record_video_url"
                                   data-spin="true"
                                   class="btn w-100 fw-bold d-inline-flex align-items-center justify-content-center gap-1.5 btn-spin-on-click mobile-ms-btn-secondary"
                                   style="min-height: 44px; min-width: 44px; border-radius: 12px; font-size: 0.82rem; white-space: nowrap; flex-shrink: 0;">
                                    <i class="bi bi-camera-video-fill" aria-hidden="true"></i>
                                    <span>Record Goodwill Video</span>
                                </a>
                            </div>
                            <template x-if="milestone.is_surprise_organiser">
                                <div class="d-flex flex-column gap-2 pt-2 mt-2 border-top" style="border-color: rgba(148, 163, 184, 0.22) !important;">
                                    <a :href="milestone.manage_cards_url"
                                       data-spin="true"
                                       class="btn w-100 fw-bold d-inline-flex align-items-center justify-content-center gap-1.5 btn-spin-on-click mobile-ms-organiser-btn"
                                       style="min-height: 44px; min-width: 44px; border-radius: 10px; font-size: 0.78rem; white-space: nowrap; flex-shrink: 0;">
                                        <i class="bi bi-send-check-fill" aria-hidden="true"></i>
                                        <span>Manage &amp; Reveal Card</span>
                                    </a>
                                    <a :href="milestone.manage_videos_url"
                                       data-spin="true"
                                       class="btn w-100 fw-bold d-inline-flex align-items-center justify-content-center gap-1.5 btn-spin-on-click mobile-ms-organiser-btn"
                                       style="min-height: 44px; min-width: 44px; border-radius: 10px; font-size: 0.78rem; white-space: nowrap; flex-shrink: 0;">
                                        <i class="bi bi-film" aria-hidden="true"></i>
                                        <span>Approve Videos &amp; Cinema</span>
                                    </a>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- 'in xx days' placed UNDER as requested by user -->
                <div class="d-flex align-items-center justify-content-between mt-2.5 pt-2 border-top" style="border-color: rgba(148, 163, 184, 0.22) !important;">
                    <span class="small fw-semibold" style="font-size: 0.74rem; color: var(--text-muted-aa, #475569);" x-text="milestone.target_date"></span>
                    <span class="badge rounded-pill px-2.5 py-0.5 fw-bold shadow-2xs"
                          :class="milestone.days_remaining === 0 ? 'bg-danger text-white' : (milestone.days_remaining === 1 ? 'bg-warning text-dark' : 'bg-white text-dark border')"
                          style="font-size: 0.72rem; white-space: nowrap;"
                          x-text="milestone.date_badge || milestone.days_until_label || 'Upcoming'"></span>
                </div>
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

                <h6 class="fw-bold mb-1" style="font-size: 0.95rem; color: var(--text-color, #1f2937); overflow-wrap: anywhere;" x-text="event.eventName"></h6>

                <div class="d-flex align-items-center mb-1" style="font-size: 0.875rem; color: var(--text-muted-aa, #475569);">
                    <i class="bi bi-clock me-1 text-primary"></i>
                    <span x-text="event.eventDate"></span>
                </div>
                <div class="d-flex align-items-center mb-2" style="font-size: 0.875rem; color: var(--text-muted-aa, #475569);">
                    <i class="bi bi-bookmark-heart-fill me-1.5 text-danger"></i>
                    <span class="text-truncate" x-text="event.eventType"></span>
                </div>

                <div class="pt-2 border-top border-light-subtle d-flex justify-content-end">
                    <a href="/profilePage#eventHeader" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-bold shadow-xs d-inline-flex align-items-center justify-content-center" style="font-size: 0.85rem; min-height: 44px; white-space: nowrap;">
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
.mobile-ms-3d-card {
    background: linear-gradient(150deg, #ffffff 0%, #fffbf2 55%, #fef3c7 100%);
    border: 1px solid rgba(217, 119, 6, 0.28);
    border-bottom: 4px solid rgba(180, 83, 9, 0.36);
    box-shadow: 0 12px 24px -10px rgba(163, 22, 33, 0.14), inset 0 1px 0 rgba(255, 255, 255, 0.92);
}
.mobile-ms-3d-card--event {
    background: linear-gradient(150deg, #ffffff 0%, #f0fdfa 65%, #ccfbf1 100%);
    border-color: rgba(13, 148, 136, 0.28);
    border-bottom-color: rgba(15, 118, 110, 0.34);
}
body.dark-mode .mobile-ms-3d-card,
[data-bs-theme="dark"] .mobile-ms-3d-card {
    background: linear-gradient(150deg, #1e293b 0%, #271a24 58%, #1f172a 100%) !important;
    border: 1px solid rgba(251, 191, 36, 0.28) !important;
    border-bottom: 4px solid rgba(217, 119, 6, 0.45) !important;
}
.mobile-ms-fullname {
    font-size: 0.95rem;
    color: var(--text-color, #0f172a);
    line-height: 1.3;
    overflow-wrap: anywhere;
    word-break: normal;
}
body.dark-mode .mobile-ms-fullname,
[data-bs-theme="dark"] .mobile-ms-fullname {
    color: #f8fafc !important;
}
.mobile-ms-btn-secondary {
    background: #ffffff;
    color: #7f1d1d !important;
    border: 1.5px solid rgba(163, 22, 33, 0.32);
    border-bottom: 3px solid rgba(163, 22, 33, 0.26);
}
body.dark-mode .mobile-ms-btn-secondary,
[data-bs-theme="dark"] .mobile-ms-btn-secondary {
    background: #1e293b !important;
    color: #fef3c7 !important;
    border-color: rgba(251, 191, 36, 0.38) !important;
}
.mobile-ms-surprise-panel {
    background: rgba(255, 255, 255, 0.86);
    border: 1px solid rgba(163, 22, 33, 0.16);
}
body.dark-mode .mobile-ms-surprise-panel,
[data-bs-theme="dark"] .mobile-ms-surprise-panel {
    background: rgba(15, 23, 42, 0.78) !important;
    border-color: rgba(251, 191, 36, 0.28) !important;
}
.mobile-ms-surprise-whisper {
    color: #7f1d1d;
    font-size: 0.72rem;
}
body.dark-mode .mobile-ms-surprise-whisper,
[data-bs-theme="dark"] .mobile-ms-surprise-whisper {
    color: #fde68a !important;
}
.mobile-ms-organiser-btn {
    background: rgba(245, 158, 11, 0.16);
    color: #92400e !important;
    border: 1px solid rgba(217, 119, 6, 0.35);
}
body.dark-mode .mobile-ms-organiser-btn,
[data-bs-theme="dark"] .mobile-ms-organiser-btn {
    background: rgba(251, 191, 36, 0.18) !important;
    color: #fde68a !important;
    border-color: rgba(251, 191, 36, 0.38) !important;
}
</style>

<script nonce="{{ csp_nonce($nonce ?? null) }}">
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
            hasHighlights() {
                return (this.memories && this.memories.length > 0) ||
                       (this.milestones && this.milestones.length > 0) ||
                       (this.events && this.events.length > 0);
            }
        };
    };
}
</script>
