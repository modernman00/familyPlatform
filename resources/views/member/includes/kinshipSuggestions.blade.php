<div class="card border-0 shadow-sm mb-4 kinship-radar-widget" id="{{ $kinshipWidgetId ?? 'kinshipRadarWidget' }}" style="border-radius: 20px; background-color: var(--card-bg, #ffffff); border: 1px solid #e2e8f0 !important;">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center text-primary" style="width: 36px; height: 36px; background: #e0e7ff; font-size: 1.1rem; flex-shrink: 0;">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem; letter-spacing: -0.01em;">Suggested Kin & In-Laws</h6>
                    <span class="text-muted" style="font-size: 0.75rem;">People you may know</span>
                </div>
            </div>
            <span class="badge rounded-pill bg-light text-primary border" style="font-size: 0.7rem; font-weight: 700;">Kinship Radar</span>
        </div>

        <div class="d-flex flex-column gap-3" id="kinshipSuggestionsList">
            @forelse(($suggestedKin ?? []) as $kin)
                @php
                    $kinAvatar = !empty($kin['profilePics']) ? (str_starts_with($kin['profilePics'], '/') ? $kin['profilePics'] : '/resources/images/profile/' . $kin['profilePics']) : '/resources/images/profile/avatarM.png';
                    $kinName = ucwords(strtolower(($kin['firstName'] ?? '') . ' ' . ($kin['lastName'] ?? '')));
                    $kinId = $kin['user_id'] ?? '';
                    $kinType = $kin['kinship_type'] ?? 'Potential Kin';
                    $primaryReason = $kin['primary_reason'] ?? 'Shared family heritage network';
                    $score = $kin['confidence_score'] ?? 75;
                @endphp
                <div class="p-3 rounded-4 bg-light kinship-item-card position-relative" id="kinCard_{{ $kinId }}" style="border: 1px solid #f1f5f9; transition: all 0.2s ease;">
                    <!-- Top row: Avatar + Names + Dismiss -->
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                        <div class="d-flex align-items-center gap-2" style="min-width: 0; flex: 1;">
                            <img src="{{ $kinAvatar }}" alt="{{ $kinName }}" class="rounded-circle border flex-shrink-0" width="44" height="44" style="object-fit: cover;">
                            <div class="text-truncate" style="min-width: 0; flex: 1;">
                                <a href="/organogram/{{ $kinId }}" class="fw-bold text-dark text-decoration-none d-block text-truncate" title="{{ $kinName }}" style="font-size: 0.9rem;">{{ $kinName }}</a>
                                <div class="d-flex align-items-center gap-1 mt-1 flex-wrap">
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-0" style="font-size: 0.65rem; font-weight: 700;">{{ $score }}% Match</span>
                                    <span class="text-secondary small fw-bold" style="font-size: 0.72rem;">• {{ $kinType }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Dismiss Button (Top Right) -->
                        <button type="button" class="btn btn-sm btn-link text-muted p-0 text-decoration-none btn-dismiss-kin flex-shrink-0" data-user-id="{{ $kinId }}" title="Dismiss suggestion" style="line-height: 1; margin-top: 2px;">
                            <i class="bi bi-x-lg" style="font-size: 0.8rem;"></i>
                        </button>
                    </div>

                    <!-- Primary Reason Badge / Info -->
                    <div class="text-muted small mb-3 ps-1" style="font-size: 0.72rem; line-height: 1.3;">
                        <i class="bi bi-diagram-2 text-warning me-1"></i> {{ $primaryReason }}
                    </div>

                    <!-- Connect Action Button (Full Width, never overlaps) -->
                    <button type="button" class="btn btn-primary btn-sm rounded-pill w-100 fw-bold btn-connect-kin d-flex align-items-center justify-content-center gap-1 shadow-sm" data-user-id="{{ $kinId }}" style="font-size: 0.78rem; padding: 6px 12px; transition: all 0.2s ease;">
                        <i class="bi bi-person-plus-fill"></i> Connect
                    </button>
                </div>
            @empty
                <div class="text-center py-4 text-muted small">
                    <i class="bi bi-shield-check text-success fs-3 d-block mb-2"></i>
                    You're connected to all nearby kin! We'll suggest new relatives as more family members join.
                </div>
            @endforelse
        </div>
    </div>
</div>
