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
        <div class="card-header bg-transparent border-0 pt-4 pb-2 px-4" id="eventHeader">
            <div>
                <h5 class="mb-0 fw-bold" style="color: var(--text-color); font-size: 1.15rem;">{{ __('events.upcoming', 'Upcoming Events') }}</h5>
                <p class="mb-0" style="font-size: 0.875rem; color: var(--text-muted-aa, #475569); line-height: 1.5;">{{ __('events.gatherings_subtitle', 'Family gatherings, birthdays & milestones.') }}</p>
                <div class="mt-1" style="font-size: 0.8125rem; color: var(--text-muted-aa, #475569);">
                    <i class="bi bi-globe me-1 text-primary"></i> <span class="user-tz-name">{{ __('global.local_time', 'Local Time') }}</span>
                </div>
            </div>
            <div class="d-flex justify-content-end align-items-center mt-2">
                <button type="button" 
                        class="btn btn-link text-decoration-none p-0 d-inline-flex align-items-center gap-1 border-0 bg-transparent flex-shrink-0" 
                        style="color: var(--primary-color, #a31621); font-weight: 600; font-size: 0.875rem; min-height: 44px; min-width: 44px; justify-content: flex-end; white-space: nowrap;" 
                        data-bs-toggle="modal" 
                        data-bs-target="#allUpcomingEventsModal"
                        aria-label="{{ __('profile.see_all', 'See All') }}">
                    <span>{{ __('profile.see_all', 'See All') }}</span> <i class="bi bi-chevron-right" style="font-size: 0.75rem;"></i>
                </button>
            </div>
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
                            <div class="p-3 rounded-3" style="background-color: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0); border-radius: 14px; box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
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
                                                   style="background-color: #ffffff !important; color: #8a1019 !important; border: 1px solid rgba(163, 22, 33, 0.25); font-size: 0.85rem; min-height: 44px; white-space: nowrap; flex-shrink: 0;"
                                                   title="Sign the Family Celebration Card">
                                                    <i class="bi bi-envelope-paper-heart-fill"></i> Sign Card
                                                </a>
                                            </template>

                                            <template x-if="isOwnEvent(event)">
                                                <div class="w-100 mt-1">
                                                    <div class="small text-muted mb-1" style="font-size: 0.72rem; color: var(--text-muted-aa, #64748b); line-height: 1.2;">
                                                        powered by <a href="https://mypartyplatform.com" target="_blank" rel="noopener noreferrer" class="text-decoration-none fw-semibold" style="color: var(--primary-color, #a31621);">mypartyplatform.com</a>
                                                    </div>
                                                    <a :href="'/member/profilePage/event/' + event.no + '/party-sso'" target="_blank" rel="noopener noreferrer"
                                                       class="btn btn-sm rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1 shadow-sm"
                                                       style="background-color: #0f172a; color: #ffffff !important; font-size: 0.85rem; min-height: 44px; white-space: nowrap; flex-shrink: 0;"
                                                       title="Launch Full PartyPlatform Event Studio (Single Sign-On)">
                                                        <i class="bi bi-stars text-warning"></i> Manage Studio ↗
                                                    </a>
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                    <template x-if="!event.party_rsvp_url && isOwnEvent(event)">
                                        <div class="mt-1 mb-2 pt-2 border-top">
                                            <div class="small text-muted mb-1" style="font-size: 0.72rem; color: var(--text-muted-aa, #64748b); line-height: 1.2;">
                                                powered by <a href="https://mypartyplatform.com" target="_blank" rel="noopener noreferrer" class="text-decoration-none fw-semibold" style="color: var(--primary-color, #a31621);">mypartyplatform.com</a>
                                            </div>
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

        @php
            $roster = is_array($familyRoster ?? null) ? $familyRoster : [];
            // Defensive fallback if not passed directly in view context
            if (empty($roster) && !empty($_SESSION['id'])) {
                try {
                    $rawMembers = (new \App\model\DataAll())->getAllMembers((string)$_SESSION['id']);
                    $roster = array_values(array_map(static fn(array $m): array => [
                        'id' => (string)($m['id'] ?? ''),
                        'firstName' => (string)($m['firstName'] ?? ''),
                        'lastName' => (string)($m['lastName'] ?? ''),
                        'img' => (string)($m['img'] ?? ($m['profilePics'] ?? '')),
                    ], $rawMembers));
                } catch (\Throwable $e) {}
            }

            // Sort to prioritize members with real profile pictures over default avatars
            if (!empty($roster)) {
                usort($roster, static function(array $a, array $b): int {
                    $hasA = !empty($a['img']) && !in_array($a['img'], ['avatarM.png', 'avatarF.png', 'avatar.png', ''], true) ? 1 : 0;
                    $hasB = !empty($b['img']) && !in_array($b['img'], ['avatarM.png', 'avatarF.png', 'avatar.png', ''], true) ? 1 : 0;
                    return $hasB <=> $hasA;
                });
            }

            $memberCount = isset($totalFamilyMembers) ? (int)$totalFamilyMembers : count($roster);
            $previewCount = 5;
            $previewMembers = array_slice($roster, 0, $previewCount);
            $overflowCount = max(0, $memberCount - count($previewMembers));
        @endphp

        <h1 class="display-4 fw-bold mb-0 text-white" style="letter-spacing: -0.02em;">{{ $memberCount }}</h1>
        <p class="mb-3 text-white-50" style="font-size: 0.95rem;">Connected Members</p>

        @if(!empty($previewMembers))
            <div class="d-flex align-items-center justify-content-center mb-3 pt-1">
                <div class="d-inline-flex align-items-center justify-content-center" style="padding: 4px 0;">
                    @foreach($previewMembers as $index => $member)
                        @php
                            $rawImg = (string)($member['img'] ?? '');
                            $memberImg = !empty($rawImg)
                                ? (str_starts_with($rawImg, '/') ? $rawImg : '/resources/images/profile/' . $rawImg)
                                : '/resources/images/profile/avatarM.png';
                            $memberName = trim(($member['firstName'] ?? '') . ' ' . ($member['lastName'] ?? ''));
                        @endphp
                        <a href="/allMembers/seeProfile/{{ $member['id'] }}" 
                           class="d-inline-block rounded-circle position-relative text-decoration-none" 
                           title="{{ htmlspecialchars($memberName ?: 'Family Member', ENT_QUOTES, 'UTF-8') }}"
                           style="width: 44px; height: 44px; margin-left: {{ $index === 0 ? '0' : '-12px' }}; z-index: {{ 10 - $index }}; transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), z-index 0.2s ease;"
                           onmouseover="this.style.transform='translateY(-4px) scale(1.15)'; this.style.zIndex='25';"
                           onmouseout="this.style.transform='none'; this.style.zIndex='{{ 10 - $index }}';">
                            <img src="{{ htmlspecialchars($memberImg, ENT_QUOTES, 'UTF-8') }}" 
                                 alt="{{ htmlspecialchars($memberName ?: 'Member', ENT_QUOTES, 'UTF-8') }}" 
                                 class="rounded-circle"
                                 width="44" 
                                 height="44" 
                                 style="object-fit: cover; border: 2.5px solid #ffffff; box-shadow: 0 4px 10px rgba(0,0,0,0.35); background-color: #2a0a0d;"
                                 onerror="this.onerror=null;this.src='/resources/images/profile/avatarM.png';">
                        </a>
                    @endforeach
                    @if($overflowCount > 0)
                        <a href="/allMembers" 
                           class="rounded-circle text-white fw-bold d-inline-flex align-items-center justify-content-center text-decoration-none shadow-sm position-relative" 
                           title="+{{ $overflowCount }} more connected members in directory"
                           style="width: 44px; height: 44px; margin-left: -12px; z-index: 4; background: rgba(255, 255, 255, 0.25); border: 2.5px solid #ffffff; backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); font-size: 0.8rem; box-shadow: 0 4px 10px rgba(0,0,0,0.35); transition: transform 0.2s ease;"
                           onmouseover="this.style.transform='translateY(-4px) scale(1.12)';"
                           onmouseout="this.style.transform='none';">
                            +{{ $overflowCount }}
                        </a>
                    @endif
                </div>
            </div>
        @endif

        <a href="/allMembers" class="btn rounded-pill fw-bold mx-auto px-4 py-2 shadow-sm d-inline-flex align-items-center justify-content-center text-decoration-none" style="font-size: 0.85rem; min-height: 44px; white-space: nowrap; flex-shrink: 0; background-color: #ffffff !important; color: #8a1019 !important; border: 1px solid rgba(255,255,255,0.9) !important;">
            <i class="bi bi-arrow-right-circle-fill me-1.5" style="color: #a31621;"></i> View Directory
        </a>
    </div>
</div>
