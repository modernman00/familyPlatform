@php
    $modalToken = $_SESSION['token'] ?? '';
@endphp

<!-- All Upcoming Family Events (Next 3 Months) Modal -->
<div class="modal fade" id="allUpcomingEventsModal" tabindex="-1" aria-labelledby="allUpcomingEventsModalLabel" aria-hidden="true" x-data="allUpcomingEventsModal()">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" style="max-width: 860px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; background-color: var(--card-bg, #ffffff); overflow: hidden;">
            
            <!-- Modal Header -->
            <div class="modal-header border-0 pb-3 pt-4 px-4 px-md-5 d-flex flex-column align-items-stretch" style="background: linear-gradient(135deg, rgba(163, 22, 33, 0.06) 0%, rgba(245, 158, 11, 0.08) 100%); border-bottom: 1px solid var(--border-color, rgba(0,0,0,0.08)) !important;">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle text-white shadow-sm flex-shrink-0" 
                             style="width: 48px; height: 48px; background: linear-gradient(135deg, var(--primary-color, #a31621) 0%, #d92d3e 100%);">
                            <i class="bi bi-calendar-check-fill fs-5 text-warning"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h4 class="modal-title fw-bold mb-0" id="allUpcomingEventsModalLabel" style="color: var(--text-color, #0f172a); font-size: 1.35rem; letter-spacing: -0.01em;">
                                    Upcoming Family Events
                                </h4>
                                <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background: rgba(163, 22, 33, 0.12); color: var(--primary-color, #a31621); font-size: 0.75rem;">
                                    Next 3 Months
                                </span>
                            </div>
                            <p class="text-muted small mb-0 mt-1" style="font-size: 0.84rem;">
                                Family gatherings, birthdays, anniversaries &amp; milestones scheduled across your family network.
                            </p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                        <button type="button" 
                                class="btn btn-sm rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm text-white" 
                                style="background-color: var(--primary-color, #a31621); border: none; font-size: 0.82rem; min-height: 44px; min-width: 44px;"
                                @click="openCreateEventModal()">
                            <i class="bi bi-plus-circle-fill text-warning"></i>
                            <span class="d-none d-sm-inline">Schedule Event</span>
                        </button>
                        <button type="button" 
                                class="btn-close ms-1" 
                                data-bs-dismiss="modal" 
                                aria-label="Close"
                                style="min-height: 44px; min-width: 44px; padding: 12px;"></button>
                    </div>
                </div>

                <!-- Controls: Search and Filters -->
                <div class="row g-2 mt-3 pt-2 align-items-center">
                    <!-- Search Input -->
                    <div class="col-12 col-md-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3 text-muted" style="border-color: var(--border-color, #cbd5e1);">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" 
                                   class="form-control border-start-0 rounded-end-pill py-2" 
                                   style="border-color: var(--border-color, #cbd5e1); font-size: 0.84rem;"
                                   placeholder="Search by event or relative..." 
                                   x-model="searchQuery" 
                                   aria-label="Search upcoming events">
                        </div>
                    </div>

                    <!-- Category Pills -->
                    <div class="col-12 col-md-7">
                        <div class="d-flex align-items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none flex-nowrap" style="-webkit-overflow-scrolling: touch;">
                            <template x-for="cat in categories" :key="cat.key">
                                <button type="button" 
                                        class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold text-nowrap flex-shrink-0 transition-all"
                                        :style="selectedCategory === cat.key 
                                            ? 'background-color: var(--primary-color, #a31621); color: #ffffff; border: 1px solid var(--primary-color, #a31621); font-size: 0.76rem; min-height: 38px;' 
                                            : 'background-color: var(--card-bg, #ffffff); color: var(--text-color, #334155); border: 1px solid var(--border-color, #cbd5e1); font-size: 0.76rem; min-height: 38px;'"
                                        @click="selectedCategory = cat.key">
                                    <span x-text="cat.label"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Month Pills (Chapter Navigation) -->
                <div class="d-flex align-items-center gap-2 mt-2 pt-1 overflow-x-auto pb-1 scrollbar-none" x-show="availableMonths.length > 1">
                    <span class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em; flex-shrink: 0;">
                        <i class="bi bi-funnel me-1"></i> Month:
                    </span>
                    <button type="button"
                            class="btn btn-xs rounded-pill px-2.5 py-1 fw-bold text-nowrap flex-shrink-0"
                            :style="selectedMonth === 'all'
                                ? 'background: #0f172a; color: #ffffff; border: 1px solid #0f172a; font-size: 0.72rem; min-height: 32px;'
                                : 'background: rgba(0,0,0,0.04); color: var(--text-color, #475569); border: 1px solid transparent; font-size: 0.72rem; min-height: 32px;'"
                            @click="selectedMonth = 'all'">
                        All Months
                    </button>
                    <template x-for="m in availableMonths" :key="m.key">
                        <button type="button"
                                class="btn btn-xs rounded-pill px-2.5 py-1 fw-bold text-nowrap flex-shrink-0"
                                :style="selectedMonth === m.key
                                    ? 'background: #0f172a; color: #ffffff; border: 1px solid #0f172a; font-size: 0.72rem; min-height: 32px;'
                                    : 'background: rgba(0,0,0,0.04); color: var(--text-color, #475569); border: 1px solid transparent; font-size: 0.72rem; min-height: 32px;'"
                                @click="selectedMonth = m.key">
                            <span x-text="m.label"></span>
                            <span class="badge rounded-pill bg-white text-dark ms-1" style="font-size: 0.65rem;" x-text="m.count"></span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Modal Body (Events List & States) -->
            <div class="modal-body p-4 px-md-5" style="min-height: 360px; max-height: 65vh; background-color: var(--card-bg, #ffffff);">
                
                <!-- 1. Loading State (Shimmer Skeletons) -->
                <template x-if="isLoading">
                    <div class="d-flex flex-column gap-3 py-2">
                        <template x-for="i in [1, 2, 3]" :key="i">
                            <div class="p-3.5 rounded-4 border placeholder-glow d-flex align-items-center gap-3" style="border-color: var(--border-color, #e2e8f0) !important;">
                                <div class="rounded-3 bg-secondary placeholder" style="width: 58px; height: 58px; opacity: 0.18;"></div>
                                <div class="flex-grow-1">
                                    <div class="placeholder col-6 rounded py-1 mb-2"></div>
                                    <div class="placeholder col-4 rounded py-1 mb-1" style="opacity: 0.14;"></div>
                                    <div class="placeholder col-3 rounded py-1" style="opacity: 0.12;"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- 2. Empty State (Zero Events Found) -->
                <template x-if="!isLoading && filteredEvents.length === 0">
                    <div class="text-center py-5 px-3">
                        <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded-circle p-3 shadow-sm" 
                             style="width: 72px; height: 72px; background: rgba(163, 22, 33, 0.08); color: var(--primary-color, #a31621);">
                            <i class="bi bi-calendar-heart fs-1"></i>
                        </div>
                        <h5 class="fw-bold mb-1" style="color: var(--text-color, #0f172a);">No Scheduled Events Found</h5>
                        <p class="text-muted small mx-auto mb-4" style="max-width: 440px; font-size: 0.86rem; line-height: 1.5;">
                            <span x-show="events.length === 0">
                                Looks like the family calendar is clear for the next 3 months. Why not organise the next family gathering, milestone dinner, or birthday reunion?
                            </span>
                            <span x-show="events.length > 0">
                                No events match your selected filters. Try choosing a different month or category.
                            </span>
                        </p>
                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                            <button type="button" 
                                    class="btn rounded-pill px-4 py-2.5 fw-semibold d-inline-flex align-items-center gap-2 shadow-sm text-white" 
                                    style="background-color: var(--primary-color, #a31621); font-size: 0.88rem; min-height: 44px;"
                                    @click="openCreateEventModal()">
                                <i class="bi bi-plus-circle-fill text-warning"></i>
                                <span>Schedule Family Event</span>
                            </button>
                            <button type="button" 
                                    class="btn btn-outline-secondary rounded-pill px-4 py-2.5 fw-semibold"
                                    style="font-size: 0.88rem; min-height: 44px;"
                                    x-show="events.length > 0 && (selectedCategory !== 'all' || selectedMonth !== 'all' || searchQuery.trim() !== '')"
                                    @click="resetFilters()">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filters
                            </button>
                        </div>
                    </div>
                </template>

                <!-- 3. Happy Path: Dynamic Events Feed -->
                <template x-if="!isLoading && filteredEvents.length > 0">
                    <div class="d-flex flex-column gap-3">
                        
                        <!-- Results Summary Header -->
                        <div class="d-flex align-items-center justify-content-between px-1 pb-1">
                            <span class="text-muted small fw-medium" style="font-size: 0.8rem;">
                                Showing <strong class="text-dark" x-text="filteredEvents.length"></strong> 
                                <span x-text="filteredEvents.length === 1 ? 'scheduled event' : 'scheduled events'"></span> in the next 3 months
                            </span>
                            <button type="button" 
                                    class="btn btn-sm btn-link text-decoration-none text-muted p-0 d-inline-flex align-items-center gap-1"
                                    style="font-size: 0.78rem;"
                                    @click="downloadIcsAll()">
                                <i class="bi bi-download text-primary"></i> Export All to Calendar (.ics)
                            </button>
                        </div>

                        <!-- Event Cards -->
                        <template x-for="event in filteredEvents" :key="event.no">
                            <div class="card border rounded-4 shadow-sm transition-all hover-lift overflow-hidden" 
                                 :id="'modalEventCard_' + event.no"
                                 style="border-color: var(--border-color, rgba(0,0,0,0.08)) !important; background-color: var(--card-bg, #ffffff);">
                                <div class="card-body p-3.5 p-md-4">
                                    <div class="d-flex align-items-start gap-3 flex-column flex-sm-row">
                                        
                                        <!-- Left: Date Ribbon Calendar Badge -->
                                        <div class="d-flex flex-column align-items-center justify-content-center text-center rounded-3 p-2 flex-shrink-0 shadow-sm"
                                             style="width: 68px; min-width: 68px; height: 68px; background: linear-gradient(135deg, rgba(163, 22, 33, 0.08) 0%, rgba(245, 158, 11, 0.12) 100%); border: 1px solid rgba(163, 22, 33, 0.2);">
                                            <span class="text-uppercase fw-bold" style="color: var(--primary-color, #a31621); font-size: 0.7rem; letter-spacing: 0.05em;" x-text="event.dayName"></span>
                                            <span class="fw-bolder lh-1" style="font-size: 1.55rem; color: #0f172a;" x-text="event.dayOfMonth"></span>
                                            <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.65rem;" x-text="(event.monthYear || '').split(' ')[0]"></span>
                                        </div>

                                        <!-- Center: Event Details -->
                                        <div class="flex-grow-1 min-w-0 w-100">
                                            <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-1">
                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    <!-- Event Name (Zero Truncation - Full Wrap) -->
                                                    <h5 class="fw-bold mb-0 text-break" style="color: var(--text-color, #0f172a); font-size: 1.05rem;" x-text="event.eventName"></h5>
                                                    
                                                    <!-- Event Type Badge -->
                                                    <span class="badge rounded-pill px-2.5 py-1 fw-bold" 
                                                          :style="getCategoryBadgeStyle(event.eventType)"
                                                          x-text="getCategoryLabel(event.eventType)"></span>
                                                </div>

                                                <!-- Relative Countdown Pill -->
                                                <span class="badge rounded-pill px-2.5 py-1 fw-bold flex-shrink-0" 
                                                      :style="event.daysUntil === 0 
                                                        ? 'background: #dc2626; color: #ffffff; font-size: 0.74rem;' 
                                                        : (event.daysUntil === 1 
                                                            ? 'background: #ea580c; color: #ffffff; font-size: 0.74rem;' 
                                                            : 'background: var(--secondary-color, #fef2f2); color: #8a1019; font-size: 0.74rem;')"
                                                      x-text="event.dateDifference"></span>
                                            </div>

                                            <!-- Metadata: Full Date & Creator -->
                                            <div class="d-flex align-items-center gap-3 text-muted small flex-wrap mb-2" style="font-size: 0.82rem;">
                                                <div class="d-flex align-items-center gap-1.5">
                                                    <i class="bi bi-calendar3 text-primary"></i>
                                                    <span x-text="event.eventDate"></span>
                                                </div>
                                                <div class="d-flex align-items-center gap-1.5" x-show="event.creatorName">
                                                    <img :src="event.creatorImg || '/public/img/default.jpg'" 
                                                         alt="" 
                                                         class="rounded-circle object-fit-cover flex-shrink-0" 
                                                         style="width: 18px; height: 18px; border: 1px solid rgba(0,0,0,0.1);"
                                                         onerror="this.src='/public/img/default.jpg'">
                                                    <span>Organised by <strong class="text-dark" x-text="event.creatorName"></strong></span>
                                                </div>
                                                <div class="d-flex align-items-center gap-1.5" x-show="event.eventFrequency">
                                                    <i class="bi bi-arrow-repeat text-muted"></i>
                                                    <span x-text="event.eventFrequency"></span>
                                                </div>
                                            </div>

                                            <!-- Event Description -->
                                            <template x-if="event.eventDescription">
                                                <p class="mb-3 text-muted small text-break" style="font-size: 0.84rem; line-height: 1.45; white-space: pre-line;" x-text="event.eventDescription"></p>
                                            </template>

                                            <!-- Action Buttons & Deep Links -->
                                            <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-2 border-top mt-2">
                                                <!-- Left: Calendar Sync Links -->
                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    <!-- Google Calendar Quick Link -->
                                                    <a :href="event.gCalUrl" 
                                                       target="_blank" 
                                                       rel="noopener noreferrer" 
                                                       class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-2xs" 
                                                       style="font-size: 0.76rem; min-height: 38px; color: #1e293b;"
                                                       title="Add to Google Calendar">
                                                        <i class="bi bi-google text-danger"></i>
                                                        <span>Google Calendar</span>
                                                    </a>

                                                    <!-- Apple / Outlook .ics Export -->
                                                    <button type="button" 
                                                            class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-2xs" 
                                                            style="font-size: 0.76rem; min-height: 38px; color: #1e293b;"
                                                            @click="downloadIcsSingle(event)"
                                                            title="Download .ics file for Apple Calendar or Outlook">
                                                        <i class="bi bi-calendar-plus text-primary"></i>
                                                        <span>Apple / Outlook (.ics)</span>
                                                    </button>
                                                </div>

                                                <!-- Right: Party Studio & Edit/Delete Actions -->
                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    <!-- PartyPlatform Studio Action Bar -->
                                                    <template x-if="event.party_rsvp_url">
                                                        <div class="d-inline-flex align-items-center gap-2">
                                                            <a :href="event.party_rsvp_url" 
                                                               target="_blank" 
                                                               rel="noopener noreferrer"
                                                               class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm text-white"
                                                               style="background-color: var(--primary-color, #a31621); font-size: 0.76rem; min-height: 38px;">
                                                                <i class="bi bi-ticket-perforated-fill text-warning"></i>
                                                                <span>RSVP &amp; Hub</span>
                                                            </a>

                                                            <template x-if="event.party_card_url && ['Birthday', 'Anniversary', 'Wedding', 'Graduation', 'Party', 'Reunion'].includes(event.eventType)">
                                                                <a :href="event.party_card_url" 
                                                                   target="_blank" 
                                                                   rel="noopener noreferrer"
                                                                   class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5"
                                                                   style="background-color: var(--secondary-color, #fef2f2); color: #8a1019; border: 1px solid rgba(163, 22, 33, 0.25); font-size: 0.76rem; min-height: 38px;">
                                                                    <i class="bi bi-envelope-paper-heart-fill"></i>
                                                                    <span>Sign Card</span>
                                                                </a>
                                                            </template>

                                                            <template x-if="event.isOwnEvent">
                                                                <a :href="'/member/profilePage/event/' + event.no + '/party-sso'" 
                                                                   target="_blank" 
                                                                   rel="noopener noreferrer"
                                                                   class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5"
                                                                   style="background-color: #0f172a; color: #f8fafc; font-size: 0.76rem; min-height: 38px;">
                                                                    <i class="bi bi-stars text-warning"></i>
                                                                    <span>Studio ↗</span>
                                                                </a>
                                                            </template>
                                                        </div>
                                                    </template>

                                                    <!-- Upgrade to PartyPlatform if Own Event & Not Synced -->
                                                    <template x-if="!event.party_rsvp_url && event.isOwnEvent">
                                                        <button type="button" 
                                                                class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5"
                                                                style="background: linear-gradient(135deg, rgba(163, 22, 33, 0.09) 0%, rgba(245, 158, 11, 0.14) 100%); color: #8a1019; border: 1px solid rgba(163, 22, 33, 0.25); font-size: 0.76rem; min-height: 38px;"
                                                                :disabled="event.isUpgradingParty"
                                                                @click="upgradeToParty(event, $event)">
                                                            <template x-if="!event.isUpgradingParty">
                                                                <span><i class="bi bi-stars text-warning me-1"></i> Enable RSVPs</span>
                                                            </template>
                                                            <template x-if="event.isUpgradingParty">
                                                                <span><span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Syncing...</span>
                                                            </template>
                                                        </button>
                                                    </template>

                                                    <!-- Edit & Delete (Author Only) -->
                                                    <template x-if="event.isOwnEvent">
                                                        <div class="d-inline-flex align-items-center gap-1">
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-link text-muted p-1.5 d-inline-flex align-items-center"
                                                                    style="font-size: 0.8rem; min-height: 38px; min-width: 38px; text-decoration: none;"
                                                                    title="Edit event"
                                                                    @click="editEvent(event)">
                                                                <i class="bi bi-pencil-square"></i>
                                                            </button>
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-link text-danger p-1.5 d-inline-flex align-items-center"
                                                                    style="font-size: 0.8rem; min-height: 38px; min-width: 38px; text-decoration: none;"
                                                                    title="Delete event"
                                                                    @click="deleteEvent(event.no)">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-0 px-4 px-md-5 py-3 d-flex align-items-center justify-content-between" style="background-color: var(--card-bg, #ffffff); border-top: 1px solid var(--border-color, rgba(0,0,0,0.06)) !important;">
                <div class="text-muted small" style="font-size: 0.78rem;">
                    <i class="bi bi-shield-check text-success me-1"></i> Visible only to verified members of your family network.
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" 
                            class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold" 
                            data-bs-dismiss="modal" 
                            style="font-size: 0.84rem; min-height: 44px;">
                        Close
                    </button>
                    <button type="button" 
                            class="btn rounded-pill px-4 py-2 fw-semibold text-white shadow-sm d-inline-flex align-items-center gap-1.5" 
                            style="background-color: var(--primary-color, #a31621); border: none; font-size: 0.84rem; min-height: 44px;"
                            @click="openCreateEventModal()">
                        <i class="bi bi-plus-circle-fill text-warning"></i>
                        <span>Create Event</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
/**
 * Alpine component managing the 3-Month Upcoming Events Interactive Modal Schedule
 */
function allUpcomingEventsModal() {
    return {
        events: [],
        isLoading: false,
        searchQuery: '',
        selectedCategory: 'all',
        selectedMonth: 'all',
        currentUserId: localStorage.getItem('requesterId') || '',
        
        categories: [
            { key: 'all', label: 'All Events' },
            { key: 'Birthday', label: 'Birthdays 🎂' },
            { key: 'Gathering', label: 'Gatherings 🏡' },
            { key: 'Anniversary', label: 'Anniversaries 💍' },
            { key: 'Reunion', label: 'Reunions 🤝' },
            { key: 'Party', label: 'Parties ✨' },
            { key: 'Milestone', label: 'Milestones 🏆' }
        ],

        init() {
            const modalEl = document.getElementById('allUpcomingEventsModal');
            if (modalEl) {
                modalEl.addEventListener('show.bs.modal', () => {
                    this.loadUpcomingEvents();
                });
            }

            // Sync with created/updated events in real-time
            window.addEventListener('event-created', (e) => {
                const ev = e?.detail;
                if (ev && !this.events.some(item => String(item.no) === String(ev.no))) {
                    this.loadUpcomingEvents();
                }
            });

            window.addEventListener('event-updated', () => {
                this.loadUpcomingEvents();
            });

            window.addEventListener('event-deleted', (e) => {
                const eventNo = e?.detail?.eventNo;
                if (eventNo) {
                    this.events = this.events.filter(item => String(item.no) !== String(eventNo));
                }
            });
        },

        async loadUpcomingEvents() {
            this.isLoading = true;
            try {
                const res = await axios.get('/member/events/upcoming3Months');
                const raw = res?.data?.message || res?.data?.data || res?.data?.msg || [];
                this.events = Array.isArray(raw) ? raw : [];
            } catch (err) {
                console.error('[UpcomingEventsModal] Failed to load 3-month schedule:', err);
                this.events = [];
            } finally {
                this.isLoading = false;
            }
        },

        get availableMonths() {
            const map = new Map();
            (this.events || []).forEach(ev => {
                if (ev.monthKey && ev.monthYear) {
                    if (!map.has(ev.monthKey)) {
                        map.set(ev.monthKey, { key: ev.monthKey, label: ev.monthYear, count: 0 });
                    }
                    map.get(ev.monthKey).count++;
                }
            });
            return Array.from(map.values()).sort((a, b) => a.key.localeCompare(b.key));
        },

        get filteredEvents() {
            return (this.events || []).filter(ev => {
                // Category Filter
                if (this.selectedCategory !== 'all') {
                    if (this.selectedCategory === 'Milestone') {
                        if (!['Milestone', 'Graduation', 'House_warming'].includes(ev.eventType)) return false;
                    } else if (ev.eventType !== this.selectedCategory) {
                        return false;
                    }
                }

                // Month Filter
                if (this.selectedMonth !== 'all' && ev.monthKey !== this.selectedMonth) {
                    return false;
                }

                // Search Filter
                if (this.searchQuery.trim() !== '') {
                    const q = this.searchQuery.toLowerCase().trim();
                    const name = (ev.eventName || '').toLowerCase();
                    const desc = (ev.eventDescription || '').toLowerCase();
                    const creator = (ev.creatorName || '').toLowerCase();
                    const type = (ev.eventType || '').toLowerCase();
                    if (!name.includes(q) && !desc.includes(q) && !creator.includes(q) && !type.includes(q)) {
                        return false;
                    }
                }

                return true;
            });
        },

        resetFilters() {
            this.searchQuery = '';
            this.selectedCategory = 'all';
            this.selectedMonth = 'all';
        },

        getCategoryLabel(type) {
            const map = {
                'Birthday': '🎂 Birthday',
                'Gathering': '🏡 Gathering',
                'Anniversary': '💍 Anniversary',
                'Reunion': '🤝 Reunion',
                'Party': '✨ Party',
                'Wedding': '👰 Wedding',
                'Graduation': '🎓 Graduation',
                'House_warming': '🔑 House Warming',
                'Milestone': '🏆 Milestone'
            };
            return map[type] || (type || 'Event');
        },

        getCategoryBadgeStyle(type) {
            switch(type) {
                case 'Birthday':
                    return 'background: rgba(236, 72, 153, 0.12); color: #be185d; font-size: 0.72rem;';
                case 'Anniversary':
                case 'Wedding':
                    return 'background: rgba(168, 85, 247, 0.12); color: #7e22ce; font-size: 0.72rem;';
                case 'Reunion':
                case 'Gathering':
                    return 'background: rgba(14, 165, 233, 0.12); color: #0369a1; font-size: 0.72rem;';
                case 'Party':
                    return 'background: rgba(245, 158, 11, 0.15); color: #b45309; font-size: 0.72rem;';
                default:
                    return 'background: rgba(100, 116, 139, 0.12); color: #334155; font-size: 0.72rem;';
            }
        },

        openCreateEventModal() {
            const currentModal = document.getElementById('allUpcomingEventsModal');
            if (currentModal && window.bootstrap?.Modal) {
                const currentInst = window.bootstrap.Modal.getInstance(currentModal);
                currentInst?.hide();
            }
            setTimeout(() => {
                const createModal = document.getElementById('createEventModal');
                if (createModal && window.bootstrap?.Modal) {
                    const inst = window.bootstrap.Modal.getOrCreateInstance(createModal);
                    inst?.show();
                }
            }, 300);
        },

        editEvent(event) {
            const currentModal = document.getElementById('allUpcomingEventsModal');
            if (currentModal && window.bootstrap?.Modal) {
                const currentInst = window.bootstrap.Modal.getInstance(currentModal);
                currentInst?.hide();
            }

            setTimeout(() => {
                const editEventNo = document.getElementById('editEventNo');
                const notice = document.getElementById('editEventNotice');
                const title = document.getElementById('createEventModalLabel');
                const submitBtn = document.getElementById('submitEventModal');
                if (!editEventNo) return;

                editEventNo.value = event.no;
                const setVal = (id, val) => { const el = document.getElementById(id); if (el) el.value = val ?? ''; };
                setVal('eventName', event.eventName);
                setVal('eventDate', event.eventDateRaw);
                setVal('eventType', event.eventType);
                setVal('eventDescription', event.eventDescription);
                setVal('eventFrequency', event.eventFrequency);

                if (notice) notice.classList.remove('d-none');
                if (title) title.textContent = 'Edit Event';
                if (submitBtn) submitBtn.textContent = 'Save Changes';

                const modalEl = document.getElementById('createEventModal');
                const instance = window.bootstrap?.Modal?.getOrCreateInstance(modalEl);
                instance?.show();
            }, 300);
        },

        async deleteEvent(eventNo) {
            const result = await Swal.fire({
                title: 'Delete this event?',
                text: 'This will remove the event for all family members.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#a31621',
                confirmButtonText: 'Yes, delete it!'
            });
            if (!result.isConfirmed) return;

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                await axios.delete(`/member/profilePage/event/${eventNo}`, {
                    headers: { 'X-CSRF-Token': token, 'X-XSRF-TOKEN': token }
                });
                
                this.events = this.events.filter(ev => String(ev.no) !== String(eventNo));
                window.dispatchEvent(new CustomEvent('event-deleted', { detail: { eventNo } }));
                
                Swal.fire({
                    icon: 'success',
                    title: 'Event Deleted',
                    text: 'The event has been successfully deleted.',
                    timer: 2000,
                    showConfirmButton: false
                });
            } catch (err) {
                console.error('[UpcomingEventsModal] Delete error:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Delete Failed',
                    text: err?.response?.data?.message || 'Could not delete the event. Please try again.',
                    confirmButtonColor: '#a31621'
                });
            }
        },

        async upgradeToParty(event, domEvent) {
            if (!event?.no || event.isUpgradingParty) return;
            event.isUpgradingParty = true;
            const btn = domEvent?.currentTarget;
            if (btn && window.ScoreTenantSpinner?.start) {
                window.ScoreTenantSpinner.start(btn);
            }

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const payload = new URLSearchParams();
                if (token) payload.append('token', token);

                const res = await axios.post(`/member/profilePage/event/${event.no}/upgrade-party`, payload, {
                    headers: { 'X-CSRF-Token': token, 'X-XSRF-TOKEN': token }
                });

                const bridgeData = res?.data?.token || {};
                Object.assign(event, {
                    party_event_id: bridgeData.party_event_id || event.party_event_id,
                    party_event_slug: bridgeData.party_event_slug || event.party_event_slug,
                    party_rsvp_url: bridgeData.party_rsvp_url || event.party_rsvp_url,
                    party_card_url: bridgeData.party_card_url || event.party_card_url,
                    party_sync_status: 'synced',
                    isUpgradingParty: false,
                });

                const seededCount = Number(bridgeData.seeded_prospects_count || 0);
                const result = await Swal.fire({
                    icon: 'success',
                    title: 'PartyPlatform Studio Ready!',
                    html: `Your event is now live on PartyPlatform Studio${seededCount > 0 ? ` with <strong>${seededCount} family relatives</strong> pre-loaded into Guest Builder by kinship tier` : ''}.`,
                    showCancelButton: true,
                    confirmButtonColor: '#a31621',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Open Party Studio ↗',
                    cancelButtonText: 'Stay Here'
                });

                if (result.isConfirmed) {
                    window.open(`/member/profilePage/event/${event.no}/party-sso`, '_blank', 'noopener');
                }
            } catch (err) {
                console.error('[UpcomingEventsModal] Upgrade to Party error:', err);
                event.isUpgradingParty = false;
                Swal.fire({
                    icon: 'error',
                    title: 'Studio Sync Failed',
                    text: err?.response?.data?.message || 'Could not connect to PartyPlatform Studio. Please try again.',
                    confirmButtonColor: '#a31621'
                });
            } finally {
                if (btn && window.ScoreTenantSpinner?.stop) {
                    window.ScoreTenantSpinner.stop(btn);
                }
            }
        },

        downloadIcsSingle(event) {
            const startRaw = (event.eventDateRaw || '').replace(/-/g, '');
            if (!startRaw) return;
            
            const icsContent = [
                'BEGIN:VCALENDAR',
                'VERSION:2.0',
                'PRODID:-//FamilyPlatform//UpcomingEvents//EN',
                'CALSCALE:GREGORIAN',
                'METHOD:PUBLISH',
                'BEGIN:VEVENT',
                `UID:family-event-${event.no}@familyplatform.com`,
                `DTSTAMP:${new Date().toISOString().replace(/[-:]/g, '').split('.')[0]}Z`,
                `DTSTART;VALUE=DATE:${startRaw}`,
                `SUMMARY:${(event.eventName || 'Family Event').replace(/,/g, '\\,')}`,
                `DESCRIPTION:${(event.eventDescription || '').replace(/\n/g, '\\n').replace(/,/g, '\\,')}`,
                `STATUS:CONFIRMED`,
                'END:VEVENT',
                'END:VCALENDAR'
            ].join('\r\n');

            const blob = new Blob([icsContent], { type: 'text/calendar;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `${(event.eventName || 'event').toLowerCase().replace(/[^a-z0-9]/g, '_')}.ics`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        },

        downloadIcsAll() {
            if (!this.filteredEvents.length) return;
            const nowStamp = new Date().toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z';
            
            const lines = [
                'BEGIN:VCALENDAR',
                'VERSION:2.0',
                'PRODID:-//FamilyPlatform//UpcomingEventsAll//EN',
                'CALSCALE:GREGORIAN',
                'METHOD:PUBLISH'
            ];

            this.filteredEvents.forEach(ev => {
                const startRaw = (ev.eventDateRaw || '').replace(/-/g, '');
                if (!startRaw) return;
                lines.push(
                    'BEGIN:VEVENT',
                    `UID:family-event-${ev.no}@familyplatform.com`,
                    `DTSTAMP:${nowStamp}`,
                    `DTSTART;VALUE=DATE:${startRaw}`,
                    `SUMMARY:${(ev.eventName || 'Family Event').replace(/,/g, '\\,')}`,
                    `DESCRIPTION:${(ev.eventDescription || '').replace(/\n/g, '\\n').replace(/,/g, '\\,')}`,
                    `STATUS:CONFIRMED`,
                    'END:VEVENT'
                );
            });

            lines.push('END:VCALENDAR');
            const blob = new Blob([lines.join('\r\n')], { type: 'text/calendar;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `family_events_next_3_months.ics`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    };
}
</script>
