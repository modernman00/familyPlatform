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
            $dateDifference = "in {$dateDiff} days";
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
        'dateDifference' => $dateDifference,
        'party_event_id' => $event['party_event_id'] ?? null,
        'party_event_slug' => $event['party_event_slug'] ?? null,
        'party_rsvp_url' => $event['party_rsvp_url'] ?? null,
        'party_card_url' => $event['party_card_url'] ?? null,
        'party_sync_status' => $event['party_sync_status'] ?? 'none',
    ];
}
@endphp

<!-- Right Column - Sidebar -->
<div class="sidebar-column" x-data="upcomingEvents({{ json_encode($enrichedEvents) }})">
    <!-- Suggested Kin & In-Laws (Kinship Radar) — Desktop only (mobile version is in feed column) -->
    @if(!empty($suggestedKin))
    <div class="d-none d-lg-block">
        {{-- BladeOne's default includeScope=false means include-passed variables leak
             globally into every include that runs afterward - middleColumn.blade.php's
             kinshipWidgetId => 'kinshipRadarWidgetMobile' would otherwise still be set
             here, giving this desktop copy the mobile id too. Explicitly reset it so the
             `?? 'kinshipRadarWidget'` default in the partial actually applies. --}}
        @includeIf('member.includes.kinshipSuggestions', ['kinshipWidgetId' => null])
    </div>
    @endif

    <!-- Family Heritage & Weekly Rituals Hub Widget -->
    @includeIf('member.includes.familyHeritageWidget')

    <!-- Family Memories & Milestones Widget -->
    @includeIf('member.includes.memoriesMilestonesWidget')

    <!-- Then & Now timeline (own) -->
    @includeIf('member.includes.thenNowTimeline', ['memberId' => (string)($_SESSION['id'] ?? ''), 'memberName' => '', 'tnSuffix' => 'self'])

    <!-- Events Card -->
    <div class="card border-0 shadow-sm mb-4" id="upcomingEventsSection" style="border-radius: 20px; background-color: var(--card-bg); scroll-margin-top: 80px;">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-start pt-4 pb-2 px-4" id="eventHeader">
            <div>
                <h5 class="mb-0 fw-bold" style="color: var(--text-color); font-size: 1.15rem;">{{ __('events.upcoming', 'Upcoming Events') }}</h5>
                <p class="mb-0" style="font-size: 0.875rem; color: var(--text-muted-aa, #475569); line-height: 1.5;">{{ __('events.gatherings_subtitle', 'Family gatherings, birthdays & milestones.') }}</p>
                <div class="mt-1" style="font-size: 0.8125rem; color: var(--text-muted-aa, #475569);">
                    <i class="bi bi-globe me-1 text-primary"></i> <span class="user-tz-name">{{ __('global.local_time', 'Local Time') }}</span>
                </div>
            </div>
            <button type="button" 
                    class="btn btn-link text-decoration-none mt-1 p-0 d-inline-flex align-items-center gap-1 border-0 bg-transparent flex-shrink-0" 
                    style="color: var(--primary-color); font-weight: 600; font-size: 0.875rem; min-height: 44px; min-width: 44px; justify-content: flex-end; white-space: nowrap;" 
                    data-bs-toggle="modal" 
                    data-bs-target="#allUpcomingEventsModal"
                    aria-label="{{ __('profile.see_all', 'See All') }}">
                <span>{{ __('profile.see_all', 'See All') }}</span> <i class="bi bi-chevron-right" style="font-size: 0.75rem;"></i>
            </button>
        </div>

        <div class="card-body eventList px-4" id="eventList">
            <!-- Empty state fallback -->
            <template x-if="events.length === 0">
                <div class="text-center py-4 px-2">
                    <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded-circle p-3" style="width: 52px; height: 52px; background: var(--secondary-color); color: var(--primary-color);">
                        <i class="bi bi-calendar-event fs-3"></i>
                    </div>
                    <h6 class="fw-bold mb-1" style="color: var(--text-color); font-size: 0.95rem;">{{ __('events.no_events', 'No Upcoming Events') }}</h6>
                    <p class="mb-3" style="font-size: 0.875rem; color: var(--text-muted-aa, #475569); line-height: 1.45;">
                        {{ __('events.clear_calendar_desc', 'Looks like the calendar is clear. Why not plan the next family gathering or reunion?') }}
                    </p>
                    <button type="button" class="btn btn-sm text-white rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#createEventModal" style="background-color: var(--primary-color, #a31621); color: #ffffff !important; border: none; font-size: 0.875rem; min-height: 44px;">
                        <i class="bi bi-plus-circle-fill me-1 text-warning"></i> {{ __('events.create_event', 'Create Event') }}
                    </button>
                </div>
            </template>

            <!-- Dynamic Events List -->
            <template x-if="events.length > 0">
                <div class="d-flex flex-column gap-3">
                    <template x-for="event in events" :key="event.no">
                        <div class="event-card" :id="'linkNotification' + event.no">
                            <div class="p-3 rounded-3" style="background-color: var(--hover-color); border: 1px solid var(--border-color); border-radius: 14px;">
                                <div class="d-flex flex-column">
                                    <h6 class="fw-bold mb-1" style="color: var(--text-color); font-size: 0.95rem; line-height: 1.35; word-break: break-word;" x-text="event.eventName" :title="event.eventName"></h6>
                                    <div class="mb-2">
                                        <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background: var(--secondary-color); color: #8a1019; font-size: 0.75rem;" x-text="event.dateDifference"></span>
                                    </div>
                                    
                                    <div class="d-flex align-items-center mb-1" style="font-size: 0.875rem; color: var(--text-muted-aa, #475569);">
                                        <i class="bi bi-calendar3 me-2 text-primary"></i>
                                        <span x-text="event.eventDate"></span>
                                    </div>
                                    
                                    <div class="d-flex align-items-center justify-content-between mb-2" style="font-size: 0.875rem; color: var(--text-muted-aa, #475569);">
                                        <div>
                                            <i class="bi bi-geo-alt me-2 text-danger"></i>
                                            <span x-text="event.eventType"></span>
                                        </div>
                                        <template x-if="event.party_rsvp_url">
                                            <span class="badge rounded-pill px-2 py-1" style="background: rgba(163, 22, 33, 0.1); color: #a31621; font-size: 0.75rem; font-weight: 700;">
                                                ✨ Party Studio
                                            </span>
                                        </template>
                                    </div>

                                    <!-- Gear 2 & Gear 3 PartyPlatform Action Bar -->
                                    <template x-if="event.party_rsvp_url">
                                        <div class="d-flex flex-wrap gap-2 mt-1 mb-2 pt-2 border-top">
                                            <a :href="event.party_rsvp_url" target="_blank" rel="noopener noreferrer"
                                               class="btn btn-sm rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1"
                                               style="background-color: var(--primary-color, #a31621); color: #ffffff !important; font-size: 0.85rem; min-height: 44px; white-space: nowrap; flex-shrink: 0;"
                                               title="Open Interactive RSVP &amp; Event Hub">
                                                <i class="bi bi-ticket-perforated-fill text-warning"></i> RSVP &amp; Hub
                                            </a>

                                            <template x-if="event.party_card_url && ['Birthday', 'Anniversary', 'Wedding', 'Graduation', 'Party', 'Reunion'].includes(event.eventType)">
                                                <a :href="event.party_card_url" target="_blank" rel="noopener noreferrer"
                                                   class="btn btn-sm rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1 shadow-sm"
                                                   style="background-color: #ffffff; color: #8a1019 !important; border: 1px solid rgba(163, 22, 33, 0.25); font-size: 0.85rem; min-height: 44px; white-space: nowrap; flex-shrink: 0;"
                                                   title="Sign the Family Celebration Card">
                                                    <i class="bi bi-envelope-paper-heart-fill"></i> Sign Card
                                                </a>
                                            </template>

                                            <template x-if="isOwnEvent(event)">
                                                <a :href="'/member/profilePage/event/' + event.no + '/party-sso'" target="_blank" rel="noopener noreferrer"
                                                   class="btn btn-sm rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1"
                                                   style="background-color: #0f172a; color: #ffffff !important; font-size: 0.85rem; min-height: 44px; white-space: nowrap; flex-shrink: 0;"
                                                   title="Launch Full PartyPlatform Event Studio (Single Sign-On)">
                                                    <i class="bi bi-stars text-warning"></i> Manage Studio ↗
                                                </a>
                                            </template>
                                        </div>
                                    </template>

                                    <template x-if="!event.party_rsvp_url && isOwnEvent(event)">
                                        <div class="mt-1 mb-2 pt-2 border-top">
                                            <button type="button"
                                                    class="btn btn-sm rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1 w-100 justify-content-center"
                                                    style="background: linear-gradient(135deg, #a31621 0%, #8a1019 100%); color: #ffffff !important; border: 1px solid #7a0e16; font-size: 0.875rem; min-height: 44px; white-space: nowrap; flex-shrink: 0;"
                                                    :disabled="event.isUpgradingParty"
                                                    @click="upgradeToParty(event, $event)">
                                                <template x-if="!event.isUpgradingParty">
                                                    <span><i class="bi bi-stars me-1"></i> Enable RSVPs &amp; Party Studio</span>
                                                </template>
                                                <template x-if="event.isUpgradingParty">
                                                    <span><span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Syncing Family Tree...</span>
                                                </template>
                                            </button>
                                        </div>
                                    </template>

                                    <template x-if="isOwnEvent(event)">
                                        <div class="d-flex gap-2 mt-1 border-top pt-2">
                                            <button type="button" class="btn btn-sm btn-link text-muted p-0" style="font-size: 0.78rem; text-decoration: none;" title="Edit event" @click="editEvent(event)">
                                                <i class="bi bi-pencil me-1"></i> Edit
                                            </button>
                                            <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-2" style="font-size: 0.78rem; text-decoration: none;" title="Delete event" @click="deleteEvent(event.no)">
                                                <i class="bi bi-trash me-1"></i> Delete
                                            </button>
                                        </div>
                                    </template>

                                    <input type="hidden" :name="'event_no'" :id="'event' + event.no" :value="event.no">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>

    <!-- Family Members Metric Card -->
    <div class="card border-0 text-white text-center shadow-lg position-relative overflow-hidden" style="background: linear-gradient(135deg, #2a0a0d 0%, #2a0a0d 40%, #8a1019 80%, #b8202c 100%); border-radius: 20px; padding: 28px 20px; box-shadow: 0 16px 32px -4px rgba(42,10,13, 0.35);">
        <div class="d-inline-flex align-items-center gap-1 mb-2 px-3 py-1 rounded-pill mx-auto" style="background: rgba(255, 255, 255, 0.15); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
            <i class="bi bi-people-fill text-warning"></i> Family Network
        </div>
        <h1 class="display-4 fw-bold mb-0 text-white" style="letter-spacing: -0.02em;">{{ $totalFamilyMembers ?? 0 }}</h1>
        <p class="mb-3 text-white-50" style="font-size: 0.95rem;">Connected Members</p>
        <button class="btn rounded-pill fw-bold mx-auto px-4 py-2 shadow-sm d-inline-flex align-items-center justify-content-center" style="font-size: 0.85rem; min-height: 44px; background-color: #ffffff !important; color: #8a1019 !important; border: 1px solid rgba(255,255,255,0.9) !important;" onclick="window.location.href='/allMembers'">
            <i class="bi bi-arrow-right-circle-fill me-1.5" style="color: #a31621;"></i> View Directory
        </button>
    </div>
</div>
