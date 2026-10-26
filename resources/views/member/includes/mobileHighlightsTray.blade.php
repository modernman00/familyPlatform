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
                 style="width: 280px; scroll-snap-align: start; background: linear-gradient(135deg, rgba(184,32,44, 0.12) 0%, rgba(236, 72, 153, 0.12) 100%); border: 1px solid rgba(184,32,44, 0.25);">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge rounded-pill px-2.5 py-1 fw-bold text-white d-inline-flex align-items-center gap-1 shadow-sm"
                          style="background: linear-gradient(135deg, #a31621, #ec4899); font-size: 0.72rem;">
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
                    <span class="text-muted small fw-semibold text-truncate" style="font-size: 0.75rem; max-width: 110px;" x-text="memories[0].fullName"></span>
                    <div class="d-flex align-items-center gap-1.5">
                        <a :href="whatsappMemoryUrl(memories[0])" target="_blank" rel="noopener noreferrer"
                           class="btn btn-sm btn-success rounded-pill px-2.5 py-1 fw-bold d-inline-flex align-items-center gap-1 text-white"
                           style="font-size: 0.75rem; min-height: 36px; background-color: #25D366; border: none; text-decoration: none;"
                           title="Share memory to WhatsApp" aria-label="Share memory to WhatsApp">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-bold" style="font-size: 0.75rem; min-height: 36px;" @click="shareMemory(memories[0])">
                            <i class="bi bi-share-fill me-1"></i> Share
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <!-- 2. Upcoming Milestones Cards (Birthdays & Anniversaries) -->
        <template x-for="(milestone, idx) in milestones" :key="'ms-' + idx">
            <div class="highlight-card flex-shrink-0 p-3 rounded-4 shadow-sm position-relative"
                 id="mobileMilestonesCard"
                 style="width: 250px; scroll-snap-align: start; background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(239, 68, 68, 0.1) 100%); border: 1px solid rgba(245, 158, 11, 0.25);">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge rounded-pill px-2.5 py-1 fw-bold text-dark bg-warning bg-gradient shadow-sm" style="font-size: 0.75rem;">
                        <i class="bi bi-cake2-fill me-1"></i> Milestone
                    </span>
                    <span class="badge rounded-pill bg-light text-dark fw-semibold" style="font-size: 0.75rem;" x-text="milestone.days_until_label || 'Upcoming'"></span>
                </div>

                <div class="d-flex align-items-center gap-2.5 mb-2">
                    <template x-if="milestone.profileImg">
                        <img :src="'/resources/images/profile/' + milestone.profileImg" class="rounded-circle shadow-sm" style="width: 38px; height: 38px; object-fit: cover;" alt="Avatar">
                    </template>
                    <div class="overflow-hidden">
                        <h6 class="fw-bold mb-0 text-truncate" style="font-size: 0.95rem; color: var(--text-color, #1f2937);" x-text="milestone.title"></h6>
                        <p class="mb-0 text-truncate" style="font-size: 0.875rem; color: var(--text-muted-aa, #475569); line-height: 1.4;" x-text="milestone.subtitle"></p>
                    </div>
                </div>

                <div class="pt-2 border-top border-light-subtle d-flex justify-content-end">
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-bold text-white shadow-sm" style="font-size: 0.875rem; min-height: 44px; background: #a31621; color: #ffffff !important; border: none;" data-bs-toggle="modal" data-bs-target="#postModal">
                        🎉 Wish Birthday
                    </button>
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
            },
            whatsappMemoryUrl(memory) {
                if (!memory) return '#';
                const yearsAgo = memory.years_ago_label || 'A family memory';
                const author = memory.fullName ? ` — ${memory.fullName}` : '';
                const quote = memory.postMessage ? `"${memory.postMessage.trim()}"` : '';
                const origin = window.location.origin || '';
                const link = memory.post_no ? `${origin}/profilePage?post_no=${memory.post_no}#post${memory.post_no}` : `${origin}/profilePage`;
                let text = `✨ [Family Memory Flashback — ${yearsAgo}]${author}\n\n${quote}\n\n🔗 ${link}`;
                if (text.length > 700) {
                    text = text.slice(0, 690) + '...\n\n🔗 ' + link;
                }
                return `https://api.whatsapp.com/send?text=${encodeURIComponent(text)}`;
            }
        };
    };
}
</script>
