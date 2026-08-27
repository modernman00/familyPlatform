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
    <!-- Events -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px; background-color: var(--card-bg);">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-4 pb-2 px-4" id="eventHeader">
            <h5 class="mb-0 fw-bold" style="font-family: 'Playfair Display', serif; color: var(--text-color);">Upcoming Events</h5>
            <a href="#" class="text-decoration-none" style="color: var(--primary-color); font-weight: 600; font-size: 0.9rem;">See All <i class="bi bi-chevron-right" style="font-size: 0.8rem;"></i></a>
        </div>

        <div class="card-body eventList px-4" id="eventList">
            <!-- Empty state fallback -->
            <template x-if="events.length === 0">
                <div class="text-center py-4">
                    <i class="bi bi-calendar-x text-muted fs-3"></i>
                    <p class="text-muted small mt-2 mb-0">No upcoming events scheduled</p>
                </div>
            </template>

            <!-- Dynamic Events List -->
            <template x-if="events.length > 0">
                <div class="d-flex flex-column gap-3">
                    <template x-for="event in events" :key="event.no">
                        <div class="event-card" :id="'linkNotification' + event.no">
                            <div class="p-3" style="background-color: var(--hover-color); border-radius: 12px; border: 1px solid var(--border-color);">
                                <div class="d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h6 class="fw-bold mb-1" style="color: var(--text-color);" x-text="event.eventName"></h6>
                                        <span class="badge bg-primary-subtle text-primary-emphasis rounded-pill px-2 py-1" style="font-size: 0.75rem;" x-text="event.dateDifference"></span>
                                    </div>
                                    
                                    <div class="d-flex align-items-center mb-1 text-muted" style="font-size: 0.85rem;">
                                        <i class="bi bi-calendar3 me-2"></i>
                                        <span x-text="event.eventDate"></span>
                                    </div>
                                    
                                    <div class="d-flex align-items-center mb-2 text-muted" style="font-size: 0.85rem;">
                                        <i class="bi bi-geo-alt me-2"></i>
                                        <span x-text="event.eventType"></span>
                                    </div>

                                    <template x-if="isOwnEvent(event)">
                                        <div class="d-flex gap-1 mt-1">
                                            <button type="button" class="btn btn-sm btn-link text-muted p-0" style="font-size: 0.78rem;" title="Edit event" @click="editEvent(event)">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                            <button type="button" class="btn btn-sm btn-link text-muted p-0 ms-2" style="font-size: 0.78rem;" title="Delete event" @click="deleteEvent(event.no)">
                                                <i class="bi bi-trash"></i> Delete
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
    <div class="card border-0 text-white text-center shadow-sm" style="background-color: var(--primary-color); border-radius: 16px; padding: 30px 20px;">
        <h1 class="display-4 fw-bold mb-0 text-white">{{ $totalFamilyMembers ?? 0 }}</h1>
        <p class="mb-3" style="color: rgba(255, 255, 255, 0.8); font-size: 1.1rem;">Family Members</p>
        <button class="btn btn-light rounded-pill fw-semibold mx-auto" style="color: var(--primary-color); padding: 8px 24px; background-color: rgba(255,255,255,0.9); border: none;" onclick="window.location.href='/allMembers'">
            View Tree
        </button>
    </div>
</div>

    </div>
