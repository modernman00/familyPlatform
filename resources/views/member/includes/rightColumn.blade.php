@php
$enrichedEvents = [];
foreach(($eventData ?? []) as $event) {
    $dateDiff = dateDifferenceInt(date('Y-m-d'), $event['eventDate']);
    if ($dateDiff < 0) {
        $dateDifference = abs($dateDiff) === 1 ? 'Yesterday' : abs($dateDiff) . ' days ago';
        $daysBadge = abs($dateDiff) . ' days ago';
    } else if ($dateDiff === 0) {
        $dateDifference = 'Today';
        $daysBadge = 'Today';
    } else if ($dateDiff === 1) {
        $dateDifference = 'Tomorrow';
        $daysBadge = 'Tomorrow';
    } else {
        $dateDifference = "in {$dateDiff} days";
        $daysBadge = "in {$dateDiff} days";
    }

    $eventTime = strtotime($event['eventDate']);
    $monthAbbr = strtoupper(date('M', $eventTime));
    $dayOfMonth = date('j', $eventTime);

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
        'daysUntil' => $dateDiff,
        'daysBadge' => $daysBadge,
        'monthAbbr' => $monthAbbr,
        'dayOfMonth' => $dayOfMonth,
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

    <!-- Family Rituals & Quests Hub Widget -->
    @includeIf('member.includes.familyHeritageWidget')

    <!-- Family Memories & Milestones Widget -->
    @includeIf('member.includes.memoriesMilestonesWidget')

    <!-- Then & Now timeline (own) -->
    @includeIf('member.includes.thenNowTimeline', ['memberId' => (string)($_SESSION['id'] ?? ''), 'memberName' => '', 'tnSuffix' => 'self'])

    <!-- Events Card -->
    <div class="card border-0 mb-4" id="upcomingEventsSection" style="font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; border-radius: 28px; background-color: #ffffff; box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05); border: 1.5px solid #f1f5f9; padding: 24px; scroll-margin-top: 80px;" x-data="{ expandedEventNo: null }">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-1">
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0 fw-bold" style="font-family: 'Plus Jakarta Sans', sans-serif; color: #0f172a; font-size: 1.25rem; font-weight: 800; letter-spacing: -0.02em;">{{ __('events.upcoming', 'Upcoming Events') }}</h5>
                <template x-if="events.length > 0">
                    <span class="badge rounded-pill px-2.5 py-1 fw-bold" 
                          style="background: #fff1f2; color: #be123c; border-radius: 9999px; font-size: 0.72rem; letter-spacing: 0.02em; font-weight: 700;" 
                          x-text="events.length + ' Active'">
                    </span>
                </template>
            </div>
            <button type="button" 
                    class="btn btn-link text-decoration-none p-0 d-inline-flex align-items-center gap-1 border-0 bg-transparent flex-shrink-0" 
                    style="color: #be123c; font-weight: 700; font-size: 0.85rem; min-height: 44px; min-width: 44px; justify-content: flex-end; white-space: nowrap;" 
                    data-bs-toggle="modal" 
                    data-bs-target="#allUpcomingEventsModal"
                    aria-label="{{ __('profile.see_all', 'See All') }}"
                    title="See All - View all upcoming family events and calendar records">
                <span>{{ __('profile.see_all', 'See All') }}</span> <i class="bi bi-chevron-right" style="font-size: 0.75rem;"></i>
            </button>
        </div>
        <p class="mb-1" style="font-size: 0.875rem; color: #64748b; line-height: 1.4;">{{ __('events.gatherings_subtitle', 'Family gatherings, birthdays & milestones.') }}</p>
        <div class="d-flex align-items-center gap-1.5" style="font-size: 0.8rem; color: #94a3b8;">
            <i class="bi bi-globe" style="color: #94a3b8; font-size: 0.82rem;"></i> <span>Timezone:</span> <span class="user-tz-name fw-medium" style="color: #94a3b8;">{{ __('global.local_time', 'Local Time') }}</span>
        </div>
        
        <!-- Faint Divider beneath Header -->
        <div style="height: 1px; background: #f1f5f9; margin: 16px 0 20px 0;"></div>

        <div class="eventList" id="eventList">
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
                    <button type="button" class="btn btn-sm text-white rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#createEventModal" style="background-color: var(--primary-color, #a31621); color: #ffffff !important; border: none; font-size: 0.875rem; min-height: 44px;" title="Create Event - Schedule a family celebration, birthday, or gathering">
                        <i class="bi bi-plus-circle-fill me-1 text-warning"></i> {{ __('events.create_event', 'Create Event') }}
                    </button>
                </div>
            </template>

            <!-- Dynamic Events List -->
            <template x-if="events.length > 0">
                <div class="d-flex flex-column gap-3">
                    <template x-for="(event, index) in events" :key="event.no">
                        <div class="event-card" :id="'linkNotification' + event.no">
                            <!-- Primary / Expanded Card (First event or expanded by user click) -->
                            <template x-if="index === 0 || expandedEventNo === event.no">
                                <div class="rounded-3 mb-1" style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02); padding: 20px;">
                                    <div class="d-flex flex-column">
                                        <!-- Title & Countdown Pill -->
                                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                            <h6 class="fw-bold mb-0 flex-grow-1" style="color: #0f172a; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1.125rem; font-weight: 800; letter-spacing: -0.015em; line-height: 1.35; word-break: break-word;" x-text="event.eventName" :title="event.eventName"></h6>
                                            <span class="badge rounded-pill px-2.5 py-1 fw-bold flex-shrink-0" 
                                                  style="background: #fff1f2; color: #be123c; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; white-space: nowrap;" 
                                                  x-text="event.daysBadge || (event.daysUntil !== undefined ? (event.daysUntil === 0 ? 'Today' : (event.daysUntil === 1 ? 'Tomorrow' : (event.daysUntil > 1 ? 'in ' + event.daysUntil + ' days' : Math.abs(event.daysUntil) + ' days ago'))) : event.dateDifference)">
                                            </span>
                                        </div>
                                        
                                        <!-- Sub-Row: Type & Party Studio Pill -->
                                        <div class="d-flex align-items-center flex-wrap gap-2 mb-3" style="font-size: 0.85rem; color: #64748b;">
                                            <div class="d-inline-flex align-items-center gap-1">
                                                <i class="bi bi-geo-alt text-muted" style="font-size: 0.85rem;"></i>
                                                <span class="fw-medium" x-text="event.eventType"></span>
                                            </div>
                                            <template x-if="event.party_rsvp_url">
                                                <div class="d-inline-flex align-items-center gap-2">
                                                    <span style="color: #cbd5e1;">•</span>
                                                    <span class="badge rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1" style="background: #faf5ff; color: #9333ea; border: 1px solid #f3e8ff; border-radius: 9999px; font-size: 0.75rem; font-weight: 700;">
                                                        <i class="bi bi-star-fill" style="font-size: 0.65rem; color: #9333ea;"></i> Party Studio
                                                    </span>
                                                </div>
                                            </template>
                                        </div>

                                        <!-- Inset Date Box -->
                                        <div class="d-flex align-items-center gap-2.5 px-3 py-2.5 mb-3 rounded-3" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px;">
                                            <i class="bi bi-calendar3 text-danger fs-6 flex-shrink-0"></i>
                                            <span class="fw-medium text-truncate" style="color: #475569; font-size: 0.875rem; font-family: 'Inter', sans-serif;" x-text="event.eventDate" :title="event.eventDate"></span>
                                        </div>

                                        <!-- The 3 Action Buttons in Equal Width Grid -->
                                        <template x-if="event.party_rsvp_url">
                                            <div class="d-grid gap-2 mb-3" style="grid-template-columns: repeat(3, minmax(0, 1fr));">
                                                <!-- Button 1: RSVP & Hub -->
                                                <a :href="event.party_rsvp_url" target="_blank" rel="noopener noreferrer"
                                                   class="btn btn-sm px-1.5 py-2 fw-semibold d-inline-flex align-items-center justify-content-center gap-1.5 shadow-sm text-decoration-none"
                                                   style="background-color: #881337; color: #ffffff !important; border: 1px solid #700f2b; font-size: 0.78rem; font-weight: 700; min-height: 48px; border-radius: 12px; white-space: nowrap; overflow: hidden;"
                                                   title="Open Interactive RSVP &amp; Event Hub">
                                                    <i class="bi bi-ticket-perforated-fill text-white" style="font-size: 0.85rem;"></i> <span>RSVP &amp; Hub</span>
                                                </a>

                                                <!-- Button 2: Sign Card -->
                                                <template x-if="event.party_card_url && ['Birthday', 'Anniversary', 'Wedding', 'Graduation', 'Party', 'Reunion'].includes(event.eventType)">
                                                    <a :href="event.party_card_url" target="_blank" rel="noopener noreferrer"
                                                       class="btn btn-sm px-1.5 py-2 fw-semibold d-inline-flex align-items-center justify-content-center gap-1.5 shadow-sm text-decoration-none"
                                                       style="background-color: #ffffff; color: #0f172a !important; border: 1px solid #e2e8f0; font-size: 0.78rem; font-weight: 700; min-height: 48px; border-radius: 12px; white-space: nowrap; overflow: hidden;"
                                                       title="Sign the Family Celebration Card">
                                                        <i class="bi bi-envelope text-danger" style="font-size: 0.85rem;"></i> <span>Sign Card</span>
                                                    </a>
                                                </template>

                                                <!-- Button 3: Manage Studio -->
                                                <template x-if="isOwnEvent(event)">
                                                    <a :href="'/member/profilePage/event/' + event.no + '/party-sso'" target="_blank" rel="noopener noreferrer"
                                                       class="btn btn-sm px-2 py-1 fw-semibold d-inline-flex align-items-center justify-content-between shadow-sm text-decoration-none"
                                                       style="background-color: #0f172a; color: #ffffff !important; border: 1px solid #0f172a; min-height: 48px; border-radius: 12px; overflow: hidden;"
                                                       title="Launch Full PartyPlatform Event Studio (Single Sign-On)">
                                                        <i class="bi bi-star-fill text-warning flex-shrink-0" style="font-size: 0.75rem;"></i>
                                                        <div class="d-flex flex-column text-center px-1" style="line-height: 1.15;">
                                                            <span style="font-size: 0.72rem; font-weight: 700; color: #ffffff;">Manage</span>
                                                            <span style="font-size: 0.72rem; font-weight: 700; color: #ffffff;">Studio</span>
                                                        </div>
                                                        <i class="bi bi-box-arrow-up-right flex-shrink-0" style="font-size: 0.68rem; color: #94a3b8;"></i>
                                                    </a>
                                                </template>
                                            </div>
                                        </template>

                                        <!-- If not upgraded yet: Full width upgrade button -->
                                        <template x-if="!event.party_rsvp_url && isOwnEvent(event)">
                                            <div class="mt-1 mb-3 pt-2 border-top">
                                                <button type="button"
                                                        class="btn btn-sm rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center justify-content-center gap-1 w-100 shadow-sm"
                                                        style="background: linear-gradient(135deg, #a31621 0%, #8a1019 100%); color: #ffffff !important; border: 1px solid #7a0e16; font-size: 0.85rem; min-height: 44px; white-space: nowrap;"
                                                        :disabled="event.isUpgradingParty"
                                                        @click="upgradeToParty(event, $event)">
                                                    <template x-if="!event.isUpgradingParty">
                                                        <span><i class="bi bi-stars me-1 text-warning"></i> Enable RSVPs &amp; Party Studio</span>
                                                    </template>
                                                    <template x-if="event.isUpgradingParty">
                                                        <span><span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Syncing Family Tree...</span>
                                                    </template>
                                                </button>
                                            </div>
                                        </template>

                                        <!-- Footer: Created via Event Hub and Edit | Delete -->
                                        <div class="d-flex justify-content-between align-items-center pt-2.5 border-top" style="border-color: #f1f5f9 !important;">
                                            <span class="text-muted" style="font-size: 0.78rem; color: #94a3b8 !important; font-weight: 500;">Created via Event Hub</span>
                                            <template x-if="isOwnEvent(event)">
                                                <div class="d-inline-flex align-items-center gap-2">
                                                    <button type="button" class="btn btn-sm btn-link p-0 d-inline-flex align-items-center gap-1 text-decoration-none" style="font-size: 0.78rem; font-weight: 600; color: #64748b !important; min-height: 44px;" title="Edit event" @click="editEvent(event)">
                                                        <i class="bi bi-pencil" style="font-size: 0.72rem;"></i> Edit
                                                    </button>
                                                    <span style="color: #e2e8f0;">|</span>
                                                    <button type="button" class="btn btn-sm btn-link p-0 d-inline-flex align-items-center gap-1 text-decoration-none" style="font-size: 0.78rem; font-weight: 600; color: #64748b !important; min-height: 44px;" title="Delete event" @click="deleteEvent(event.no)">
                                                        <i class="bi bi-trash" style="font-size: 0.72rem;"></i> Delete
                                                    </button>
                                                </div>
                                            </template>
                                        </div>

                                        <input type="hidden" :name="'event_no'" :id="'event' + event.no" :value="event.no">
                                    </div>
                                </div>
                            </template>

                            <!-- Secondary / Compact Card (For index > 0, click to expand) -->
                            <template x-if="index > 0 && expandedEventNo !== event.no">
                                <div class="rounded-3 d-flex align-items-center justify-content-between gap-2.5 mb-2"
                                     style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 18px; cursor: pointer; transition: all 0.18s ease; padding: 12px 16px;"
                                     @click="expandedEventNo = event.no"
                                     title="Click to view full event actions">
                                    <div class="d-flex align-items-center gap-3 overflow-hidden">
                                        <!-- Date block badge (e.g. DEC 12) -->
                                        <div class="d-flex flex-column align-items-center justify-content-center rounded-3 border flex-shrink-0"
                                             style="width: 48px; height: 48px; background: #f8fafc; border-color: #e2e8f0 !important; border-radius: 12px;">
                                            <span class="fw-bold text-danger text-uppercase" style="font-size: 0.65rem; line-height: 1; letter-spacing: 0.05em; font-weight: 800;" 
                                                  x-text="event.monthAbbr || (new Date(event.eventDateRaw || event.eventDate)).toLocaleDateString('en-GB', { month: 'short' }).toUpperCase()"></span>
                                            <span class="fw-bold" style="color: #0f172a; font-size: 1.15rem; line-height: 1.1; font-weight: 800;" 
                                                  x-text="event.dayOfMonth || (new Date(event.eventDateRaw || event.eventDate)).getDate()"></span>
                                        </div>
                                        <div class="d-flex flex-column overflow-hidden">
                                            <h6 class="fw-bold mb-0 text-truncate" style="color: #0f172a; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 0.95rem; font-weight: 700;" x-text="event.eventName" :title="event.eventName"></h6>
                                            <span class="text-muted text-truncate" style="font-size: 0.78rem; color: #64748b !important; font-family: 'Inter', sans-serif;" x-text="event.eventDate" :title="event.eventDate"></span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-1.5 flex-shrink-0">
                                        <span class="badge rounded-pill px-2.5 py-1" style="background: #f1f5f9; color: #475569; font-size: 0.75rem; font-weight: 600; border-radius: 9999px;" 
                                              x-text="event.daysBadge || (event.daysUntil !== undefined ? (event.daysUntil === 0 ? 'Today' : (event.daysUntil === 1 ? 'Tomorrow' : (event.daysUntil > 1 ? 'in ' + event.daysUntil + ' days' : Math.abs(event.daysUntil) + ' days ago'))) : event.dateDifference)"></span>
                                        <i class="bi bi-chevron-right text-muted" style="font-size: 0.75rem; color: #94a3b8 !important;"></i>
                                    </div>
                                </div>
                            </template>
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
            $roster = $familyRoster ?? [];
            if (empty($roster)) {
                $uid = $data['id'] ?? ($_SESSION['id'] ?? null);
                if (!empty($uid)) {
                    try {
                        $allM = (new \App\model\AllMembersData())->getAllMembers($uid);
                        $roster = array_values(array_map(static fn(array $m): array => [
                            'id' => (string)($m['id'] ?? ''),
                            'firstName' => (string)($m['firstName'] ?? ''),
                            'lastName' => (string)($m['lastName'] ?? ''),
                            'img' => (string)($m['img'] ?? ($m['profilePics'] ?? '')),
                        ], $allM));
                    } catch (\Throwable $e) {
                        $roster = [];
                    }
                }
            }

            if (!empty($roster)) {
                // Prioritise members with custom uploaded profile photos over default placeholders
                usort($roster, function($a, $b) {
                    $hasA = !empty($a['img']) && !in_array($a['img'], ['avatarM.png', 'avatarF.png', 'avatar.png'], true) ? 1 : 0;
                    $hasB = !empty($b['img']) && !in_array($b['img'], ['avatarM.png', 'avatarF.png', 'avatar.png'], true) ? 1 : 0;
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
                           style="width: 42px; height: 42px; margin-left: {{ $index === 0 ? '0' : '-12px' }}; z-index: {{ 10 - $index }}; transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), z-index 0.2s ease;"
                           onmouseover="this.style.transform='translateY(-4px) scale(1.15)'; this.style.zIndex='25';"
                           onmouseout="this.style.transform='none'; this.style.zIndex='{{ 10 - $index }}';">
                            <img src="{{ htmlspecialchars($memberImg, ENT_QUOTES, 'UTF-8') }}" 
                                 alt="{{ htmlspecialchars($memberName ?: 'Member', ENT_QUOTES, 'UTF-8') }}" 
                                 class="rounded-circle"
                                 width="42" 
                                 height="42" 
                                 style="object-fit: cover; border: 2.5px solid #ffffff; box-shadow: 0 4px 10px rgba(0,0,0,0.35); background-color: #2a0a0d;"
                                 onerror="this.onerror=null;this.src='/resources/images/profile/avatarM.png';">
                        </a>
                    @endforeach
                    @if($overflowCount > 0)
                        <a href="/allMembers" 
                           class="rounded-circle text-white fw-bold d-inline-flex align-items-center justify-content-center text-decoration-none shadow-sm position-relative" 
                           title="+{{ $overflowCount }} more connected members in directory"
                           style="width: 42px; height: 42px; margin-left: -12px; z-index: 4; background: rgba(255, 255, 255, 0.25); border: 2.5px solid #ffffff; backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); font-size: 0.8rem; box-shadow: 0 4px 10px rgba(0,0,0,0.35); transition: transform 0.2s ease;"
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
