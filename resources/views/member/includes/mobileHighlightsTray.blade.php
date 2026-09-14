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
        <a href="/profilePage#eventHeader" class="text-decoration-none small text-primary fw-bold" style="font-size: 0.78rem;">
            See All <i class="bi bi-chevron-right"></i>
        </a>
    </div>

    <!-- Horizontal Swipe Carousel -->
    <div class="highlights-scroll-wrapper d-flex gap-3 overflow-x-auto pb-2 pt-1 scrollbar-none"
         style="scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch; scroll-behavior: smooth;">

        <!-- 1. Nostalgia Memory Flashback Card -->
        <template x-if="memories.length > 0">
            <div class="highlight-card flex-shrink-0 p-3 rounded-4 shadow-sm position-relative overflow-hidden"
                 style="width: 280px; scroll-snap-align: start; background: linear-gradient(135deg, rgba(99, 102, 241, 0.12) 0%, rgba(236, 72, 153, 0.12) 100%); border: 1px solid rgba(99, 102, 241, 0.25);">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge rounded-pill px-2.5 py-1 fw-bold text-white d-inline-flex align-items-center gap-1 shadow-sm"
                          style="background: linear-gradient(135deg, #4f46e5, #ec4899); font-size: 0.72rem;">
                        <i class="bi bi-clock-history"></i> <span x-text="memories[0].years_ago_label || 'Memory Flashback'"></span>
                    </span>
                    <span class="text-muted small" style="font-size: 0.72rem;" x-text="memories[0].formatted_date"></span>
                </div>

                <template x-if="memories[0].primary_image">
                    <div class="mb-2 rounded-3 overflow-hidden shadow-sm" style="height: 100px;">
                        <img :src="'/resources/images/post/' + memories[0].primary_image" alt="Memory photo" class="w-100 h-100 object-fit-cover">
                    </div>
                </template>

                <p class="mb-2 fw-medium text-truncate-2" style="color: var(--text-color, #1f2937); font-size: 0.84rem; line-height: 1.35; height: 2.7em;" x-text="memories[0].postMessage"></p>

                <div class="d-flex justify-content-between align-items-center pt-2 border-top border-light-subtle">
                    <span class="text-muted small fw-semibold text-truncate" style="font-size: 0.75rem; max-width: 130px;" x-text="memories[0].fullName"></span>
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-bold" style="font-size: 0.75rem; min-height: 36px;" @click="shareMemory(memories[0])">
                        <i class="bi bi-share-fill me-1"></i> Share
                    </button>
                </div>
            </div>
        </template>

        <!-- 2. Upcoming Milestones Cards (Birthdays & Anniversaries) -->
        <template x-for="(milestone, idx) in milestones" :key="'ms-' + idx">
            <div class="highlight-card flex-shrink-0 p-3 rounded-4 shadow-sm position-relative"
                 style="width: 250px; scroll-snap-align: start; background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(239, 68, 68, 0.1) 100%); border: 1px solid rgba(245, 158, 11, 0.25);">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge rounded-pill px-2.5 py-1 fw-bold text-dark bg-warning bg-gradient shadow-sm" style="font-size: 0.72rem;">
                        <i class="bi bi-cake2-fill me-1"></i> Milestone
                    </span>
                    <span class="badge rounded-pill bg-light text-dark fw-semibold" style="font-size: 0.7rem;" x-text="milestone.days_until_label || 'Upcoming'"></span>
                </div>

                <div class="d-flex align-items-center gap-2.5 mb-2">
                    <template x-if="milestone.profileImg">
                        <img :src="'/resources/images/profile/' + milestone.profileImg" class="rounded-circle shadow-sm" style="width: 38px; height: 38px; object-fit: cover;" alt="Avatar">
                    </template>
                    <div class="overflow-hidden">
                        <h6 class="fw-bold mb-0 text-truncate" style="font-size: 0.88rem; color: var(--text-color, #1f2937);" x-text="milestone.title"></h6>
                        <p class="text-muted small mb-0 text-truncate" style="font-size: 0.75rem;" x-text="milestone.subtitle"></p>
                    </div>
                </div>

                <div class="pt-2 border-top border-light-subtle d-flex justify-content-end">
                    <button type="button" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-3 py-1 fw-bold" style="font-size: 0.75rem; min-height: 36px;" data-bs-toggle="modal" data-bs-target="#postModal">
                        🎉 Wish Birthday
                    </button>
                </div>
            </div>
        </template>

        <!-- 3. Upcoming Family Events Cards -->
        <template x-for="event in events" :key="'evt-' + event.no">
            <div class="highlight-card flex-shrink-0 p-3 rounded-4 shadow-sm position-relative"
                 style="width: 250px; scroll-snap-align: start; background: linear-gradient(135deg, rgba(59, 130, 246, 0.08) 0%, rgba(16, 185, 129, 0.08) 100%); border: 1px solid rgba(59, 130, 246, 0.2);">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge rounded-pill px-2.5 py-1 fw-bold text-white bg-primary bg-gradient shadow-sm" style="font-size: 0.72rem;">
                        <i class="bi bi-calendar-event me-1"></i> Event
                    </span>
                    <span class="badge rounded-pill px-2 py-1 fw-bold" style="font-size: 0.72rem; background: #e0e7ff; color: #3730a3;" x-text="event.dateDifference"></span>
                </div>

                <h6 class="fw-bold mb-1 text-truncate" style="font-size: 0.88rem; color: var(--text-color, #1f2937);" x-text="event.eventName"></h6>

                <div class="d-flex align-items-center mb-1 text-muted small" style="font-size: 0.78rem;">
                    <i class="bi bi-clock me-1 text-primary"></i>
                    <span x-text="event.eventDate"></span>
                </div>
                <div class="d-flex align-items-center mb-2 text-muted small" style="font-size: 0.78rem;">
                    <i class="bi bi-geo-alt me-1 text-danger"></i>
                    <span class="text-truncate" x-text="event.eventType"></span>
                </div>

                <div class="pt-2 border-top border-light-subtle d-flex justify-content-end">
                    <a href="/profilePage#eventHeader" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-bold" style="font-size: 0.75rem; min-height: 36px;">
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
                fetch('/api/memories-milestones')
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
            },
            shareMemory(memory) {
                if (typeof window.openShareModal === 'function') {
                    window.openShareModal(memory);
                } else {
                    const postModal = document.getElementById('postModal');
                    if (postModal) {
                        const modal = new bootstrap.Modal(postModal);
                        modal.show();
                    }
                }
            }
        };
    };
}
</script>
