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
        'eventLocation' => $event['eventLocation'] ?? '',
        'proximity_mode' => $event['proximity_mode'] ?? 'all_family',
        'target_radius_miles' => (int)($event['target_radius_miles'] ?? 25),
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
<div class="sidebar-column">
    @if(empty($isPendingApproval))
    <!-- Suggested Kin & In-Laws: Primary home is in the main feed carousel -->
    @if(!empty($suggestedKin))
    <div class="d-none">
        @includeIf('member.includes.kinshipSuggestions', ['kinshipWidgetId' => null])
    </div>
    @endif
    <div id="upcoming-events-root"></div>
    <script type="application/json" id="upcoming-events-bootstrap">{!! json_encode(['events' => $enrichedEvents, 'userId' => (string)($_SESSION['id'] ?? ''), 'famCode' => (string)($data['famCode'] ?? '')]) !!}</script>

    <!-- Then & Now timeline (own) -->
    @includeIf('member.includes.thenNowTimeline', ['memberId' => (string)($_SESSION['id'] ?? ''), 'memberName' => '', 'tnSuffix' => 'self'])

    <!-- Family Memories & Milestones Widget -->
    @includeIf('member.includes.memoriesMilestonesWidget')

    <!-- Family Heritage & Weekly Rituals Hub Widget -->
    @includeIf('member.includes.familyHeritageWidget')

    <!-- Family Members Metric Card -->
    <div class="card border-0 text-white text-center shadow-lg position-relative overflow-hidden" id="familyNetworkCard" style="background: linear-gradient(135deg, #2a0a0d 0%, #2a0a0d 40%, #8a1019 80%, #b8202c 100%); border-radius: 20px; padding: 28px 20px; box-shadow: 0 16px 32px -4px rgba(42,10,13, 0.35);">
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
                           title="+{{ $overflowCount }} more connected family members"
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
    @else
    <!-- Holding State Info Card for Pending Users -->
    <div class="card border-0 shadow-sm p-4 mb-4 text-center" style="border-radius: 18px; background-color: var(--card-bg, #ffffff);">
        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3" 
             style="width: 52px; height: 52px; background: rgba(163, 22, 33, 0.08); color: #a31621; font-size: 1.5rem;">
            <i class="bi bi-calendar2-heart"></i>
        </div>
        <h6 class="fw-bold mb-1 text-dark" style="font-size: 1.05rem;">Family Hub Confidential</h6>
        <p class="text-muted small mb-0" style="font-size: 0.88rem; line-height: 1.5;">
            Upcoming family birthdays, wedding anniversaries, photo albums, and the connected member directory will automatically appear here once your family membership is approved.
        </p>
    </div>
    @endif
</div>
