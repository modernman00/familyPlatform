@php
$enrichedEvents = [];
foreach(($eventData ?? []) as $event) {
    $dateDiff = dateDifferenceInt(date('Y-m-d'), $event['eventDate']);
    $getDateDiff = number2word($dateDiff);

    if($getDateDiff == 'Zero') {
        $dateDifference = 'Today';
    } else if($getDateDiff == 'One') {
        $dateDifference = 'Tomorrow';
    } else {
        $dateDifference = "in $getDateDiff Days";
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

<!-- Right Column - Sidebar -->
<div class="sidebar-column" x-data="upcomingEvents({{ json_encode($enrichedEvents) }})">
    <!-- Suggested Kin & In-Laws (Kinship Radar) — Desktop only (mobile version is in feed column) -->
    <div class="d-none d-lg-block">
        {{-- BladeOne's default includeScope=false means include-passed variables leak
             globally into every include that runs afterward - middleColumn.blade.php's
             kinshipWidgetId => 'kinshipRadarWidgetMobile' would otherwise still be set
             here, giving this desktop copy the mobile id too. Explicitly reset it so the
             `?? 'kinshipRadarWidget'` default in the partial actually applies. --}}
        @includeIf('member.includes.kinshipSuggestions', ['kinshipWidgetId' => null])
    </div>

    <!-- Events Card -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; background-color: var(--card-bg);">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-start pt-4 pb-2 px-4" id="eventHeader">
            <div>
                <h5 class="mb-0 fw-bold" style="color: var(--text-color); font-size: 1.05rem;">Upcoming Events</h5>
                <p class="text-muted small mb-0" style="font-size: 0.8rem;">Family gatherings, birthdays & milestones.</p>
            </div>
            <a href="/profilePage#eventHeader" class="text-decoration-none mt-1" style="color: var(--primary-color); font-weight: 600; font-size: 0.85rem;">See All <i class="bi bi-chevron-right" style="font-size: 0.75rem;"></i></a>
        </div>

        <div class="card-body eventList px-4" id="eventList">
            <!-- Empty state fallback -->
            <template x-if="events.length === 0">
                <div class="text-center py-4 px-2">
                    <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded-circle p-3" style="width: 52px; height: 52px; background: var(--secondary-color); color: var(--primary-color);">
                        <i class="bi bi-calendar-event fs-3"></i>
                    </div>
                    <h6 class="fw-bold mb-1" style="color: var(--text-color); font-size: 0.95rem;">No Upcoming Events</h6>
                    <p class="text-muted small mb-3" style="font-size: 0.82rem; line-height: 1.45;">
                        Looks like the calendar is clear. Why not plan the next family gathering or reunion?
                    </p>
                    <button type="button" class="btn btn-sm text-white rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#createEventModal" style="background-color: var(--primary-color); border: none; font-size: 0.82rem;">
                        <i class="bi bi-plus-circle-fill me-1 text-warning"></i> Create Event
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
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <h6 class="fw-bold mb-0 text-truncate" style="color: var(--text-color); font-size: 0.9rem;" x-text="event.eventName"></h6>
                                        <span class="badge rounded-pill px-2 py-1 fw-bold" style="background: var(--secondary-color); color: #4338ca; font-size: 0.72rem;" x-text="event.dateDifference"></span>
                                    </div>
                                    
                                    <div class="d-flex align-items-center mb-1 text-muted" style="font-size: 0.82rem;">
                                        <i class="bi bi-calendar3 me-2 text-primary"></i>
                                        <span x-text="event.eventDate"></span>
                                    </div>
                                    
                                    <div class="d-flex align-items-center mb-2 text-muted" style="font-size: 0.82rem;">
                                        <i class="bi bi-geo-alt me-2 text-danger"></i>
                                        <span x-text="event.eventType"></span>
                                    </div>

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
    <div class="card border-0 text-white text-center shadow-lg position-relative overflow-hidden" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4338ca 80%, #6366f1 100%); border-radius: 20px; padding: 28px 20px; box-shadow: 0 16px 32px -4px rgba(49, 46, 129, 0.35);">
        <div class="d-inline-flex align-items-center gap-1 mb-2 px-3 py-1 rounded-pill mx-auto" style="background: rgba(255, 255, 255, 0.15); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
            <i class="bi bi-people-fill text-warning"></i> Family Network
        </div>
        <h1 class="display-4 fw-bold mb-0 text-white" style="letter-spacing: -0.02em;">{{ $totalFamilyMembers ?? 0 }}</h1>
        <p class="mb-3 text-white-50" style="font-size: 0.95rem;">Connected Members</p>
        <button class="btn btn-light rounded-pill fw-bold mx-auto px-4 py-2 text-primary shadow-sm" style="font-size: 0.85rem;" onclick="window.location.href='/allMembers'">
            <i class="bi bi-arrow-right-circle-fill me-1"></i> View Directory
        </button>
    </div>
</div>
