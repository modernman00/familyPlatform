{{-- resources/views/member/includes/treeAccessModals.blade.php --}}
{{-- Modals and scripts for: 1. Who Viewed My Tree, 2. Strict Same-Family-Code Notice, 3. Granular Limit Access Controls --}}

@php
    $effectiveFamCode = $familyCode ?? ($primaryFamCode ?? ($data['famCode'] ?? ''));
    $viewersList = $treeViewersData['viewers'] ?? [];
    $totalViews = (int)($treeViewersData['total_views'] ?? 0);
    $uniqueViewers = (int)($treeViewersData['unique_viewers'] ?? 0);
    $currentAccessMode = $treeAccessData['access_mode'] ?? ($treeViewersData['access_mode'] ?? 'family_all');
    $familyMembersList = $treeAccessData['members'] ?? [];
@endphp

<!-- Modal 1: Who Viewed My Family Tree -->
<div class="modal fade" id="whoViewedTreeModal" tabindex="-1" aria-labelledby="whoViewedTreeModalLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 580px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; background: #0f172a; color: #ffffff; border: 1px solid rgba(56, 189, 248, 0.35);">
            <div class="modal-header border-bottom border-secondary py-3 px-4" style="background: rgba(15, 23, 42, 0.98);">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: rgba(56, 189, 248, 0.18); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.45); font-size: 1.25rem;">
                        <i class="bi bi-eye-fill"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0 fs-6" id="whoViewedTreeModalLabel">Who Viewed My Family Tree</h5>
                        <small class="text-white-50" style="font-size: 0.78rem;">
                            <span id="whoViewedSummaryText"><strong>{{ $uniqueViewers }}</strong> {{ $uniqueViewers === 1 ? 'relative' : 'relatives' }} &middot; <strong>{{ $totalViews }}</strong> total {{ $totalViews === 1 ? 'view' : 'views' }}</span>
                        </small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-3 p-md-4" style="overflow-y: auto; max-height: calc(80vh - 120px);">
                <!-- Strict Same-Family-Code Isolation Banner (Requirement 2 & 4) -->
                <div class="alert p-3 mb-3 rounded-3 d-flex align-items-start gap-2.5 border-0" style="background: rgba(16, 185, 129, 0.12); border-left: 4px solid #10b981 !important;">
                    <i class="bi bi-shield-lock-fill text-success fs-5 flex-shrink-0 mt-0.5"></i>
                    <div style="font-size: 0.82rem; line-height: 1.45; color: #e2e8f0;">
                        <strong class="text-white d-block mb-0.5">Same Family Code Only Invariant</strong>
                        Your family tree can strictly <strong>only</strong> be viewed by relatives sharing your family code (<strong>#{{ $effectiveFamCode }}</strong>). External friends or directory connections cannot view your tree.
                    </div>
                </div>

                <!-- Viewer Search Box -->
                <div class="mb-3 position-relative">
                    <i class="bi bi-search position-absolute top-50 translate-middle-y text-white-50 ms-3" style="font-size: 0.85rem;"></i>
                    <input type="text" id="whoViewedSearchInput" class="form-control rounded-pill ps-5 py-2 text-white" placeholder="Search relatives who viewed..." oninput="filterWhoViewedList()" style="background: rgba(255, 255, 255, 0.08); border-color: rgba(255, 255, 255, 0.2); font-size: 0.85rem;">
                </div>

                <!-- Viewers List -->
                <div id="whoViewedListContainer" class="d-flex flex-column gap-2">
                    @forelse($viewersList as $viewer)
                        @php
                            $vId = (string)($viewer['viewer_id'] ?? '');
                            $vName = (string)($viewer['full_name'] ?? 'Family Member');
                            $vAvatar = (string)($viewer['avatar_url'] ?? '/resources/images/profile/avatarM.png');
                            $vCount = (int)($viewer['view_count'] ?? 1);
                            $vHumanTime = (string)($viewer['last_viewed_human'] ?? 'Recently');
                            $vCode = (string)($viewer['family_code'] ?? $effectiveFamCode);
                            $vIsAllowed = !empty($viewer['is_allowed']);
                            $vRuleType = (string)($viewer['rule_type'] ?? 'default');
                        @endphp
                        <div class="viewer-entry-card p-3 rounded-3 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2.5" data-name="{{ strtolower($vName) }}" data-user-id="{{ $vId }}" style="background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08); transition: background 0.2s ease;">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="position-relative flex-shrink-0">
                                    <img src="{{ $vAvatar }}" alt="{{ $vName }}" class="rounded-circle object-fit-cover" style="width: 44px; height: 44px; border: 2px solid #38bdf8;" onerror="this.src='/resources/images/profile/avatarM.png'">
                                    <span class="position-absolute bottom-0 end-0 bg-success border border-dark rounded-circle" style="width: 10px; height: 10px;" title="Same Family Code Active"></span>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                        <h6 class="mb-0 fw-bold text-white fs-6 text-truncate" style="max-width: 220px;" title="{{ $vName }}">{{ $vName }}</h6>
                                        <span class="badge rounded-pill" style="font-size: 0.68rem; background: rgba(56, 189, 248, 0.2); color: #7dd3fc; border: 1px solid rgba(56, 189, 248, 0.4);">#{{ $vCode }}</span>
                                    </div>
                                    <div class="text-white-50 small mt-0.5" style="font-size: 0.76rem;">
                                        <span><i class="bi bi-clock-history me-1"></i>{{ $vHumanTime }}</span> &middot; 
                                        <span class="text-info fw-semibold">{{ $vCount }} {{ $vCount === 1 ? 'view' : 'views' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-1.5 flex-shrink-0 align-self-end align-self-sm-center">
                                @if($vIsAllowed)
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1 btn-toggle-access" onclick="setPersonAccessDirectly('{{ $vId }}', 'restrict', this)" style="min-height: 44px; font-size: 0.78rem; white-space: nowrap; flex-shrink: 0;" title="Limit access: This person will not be able to view your family tree">
                                        <i class="bi bi-person-x-fill"></i> Limit Access
                                    </button>
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1 btn-toggle-access" onclick="setPersonAccessDirectly('{{ $vId }}', 'allow', this)" style="min-height: 44px; font-size: 0.78rem; white-space: nowrap; flex-shrink: 0;" title="Restore access: Allow this family member to view your tree">
                                        <i class="bi bi-person-check-fill"></i> Restore Access
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 px-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 56px; height: 56px; background: rgba(56, 189, 248, 0.1); color: #38bdf8;">
                                <i class="bi bi-eye-slash-fill fs-3"></i>
                            </div>
                            <h6 class="fw-bold text-white mb-1">No Tree Views Yet</h6>
                            <p class="text-white-50 small mb-0" style="max-width: 380px; margin: 0 auto; line-height: 1.5;">
                                Relatives sharing your family code (<strong>#{{ $effectiveFamCode }}</strong>) will appear here the moment they explore your family tree or lineage branch.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="modal-footer border-top border-secondary py-2.5 px-4 d-flex justify-content-between align-items-center" style="background: rgba(15, 23, 42, 0.98);">
                <button type="button" class="btn btn-link text-info p-0 text-decoration-none small d-inline-flex align-items-center gap-1" onclick="switchModalToAccessControl()">
                    <i class="bi bi-sliders"></i> Configure Full Access Policy &rarr;
                </button>
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal" style="min-height: 44px;">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Limit Access to Specific Persons (Requirement 3 & 4) -->
<div class="modal fade" id="treeAccessControlModal" tabindex="-1" aria-labelledby="treeAccessControlModalLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 620px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; background: #0f172a; color: #ffffff; border: 1px solid rgba(245, 158, 11, 0.35);">
            <div class="modal-header border-bottom border-secondary py-3 px-4" style="background: rgba(15, 23, 42, 0.98);">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: rgba(245, 158, 11, 0.18); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.45); font-size: 1.25rem;">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0 fs-6" id="treeAccessControlModalLabel">Limit Family Tree Access</h5>
                        <small class="text-white-50" style="font-size: 0.78rem;">Choose who has permission to view your genealogical tree</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-3 p-md-4" style="overflow-y: auto; max-height: calc(80vh - 120px);">
                <!-- Strict Family Code Isolation Reminder -->
                <div class="alert p-3 mb-3 rounded-3 d-flex align-items-start gap-2.5 border-0" style="background: rgba(56, 189, 248, 0.12); border-left: 4px solid #38bdf8 !important;">
                    <i class="bi bi-info-circle-fill text-info fs-5 flex-shrink-0 mt-0.5"></i>
                    <div style="font-size: 0.82rem; line-height: 1.45; color: #e2e8f0;">
                        <strong class="text-white d-block mb-0.5">Access Boundary: Family Code #{{ $effectiveFamCode }}</strong>
                        Only members of this family code can be granted access. You can limit access to everyone except specific individuals, restrict selected relatives, or lock your tree completely.
                    </div>
                </div>

                <!-- Access Mode Selector -->
                <label class="form-label text-white fw-bold small mb-2 text-uppercase" style="letter-spacing: 0.05em; font-size: 0.72rem;">
                    1. Select Overall Access Policy
                </label>
                <div class="d-flex flex-column gap-2 mb-4" id="accessModeCardsContainer">
                    <!-- Mode 1: All Family (Default) -->
                    <label class="access-mode-card p-3 rounded-3 d-flex align-items-start gap-3 cursor-pointer" style="background: rgba(255, 255, 255, 0.04); border: 2px solid {{ $currentAccessMode === 'family_all' ? '#fbbf24' : 'rgba(255, 255, 255, 0.1)' }}; cursor: pointer; transition: all 0.2s ease;">
                        <input type="radio" name="tree_access_mode" value="family_all" {{ $currentAccessMode === 'family_all' ? 'checked' : '' }} onchange="onAccessModeChanged('family_all')" class="form-check-input mt-1 flex-shrink-0">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold text-white fs-6">All Family Members (Default)</span>
                                <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25 rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">RECOMMENDED</span>
                            </div>
                            <p class="text-white-50 small mb-0 mt-1" style="font-size: 0.8rem; line-height: 1.4;">
                                Everyone sharing your family code (#{{ $effectiveFamCode }}) can view your tree. You can manually limit specific persons in the list below.
                            </p>
                        </div>
                    </label>

                    <!-- Mode 2: Specific Persons Only -->
                    <label class="access-mode-card p-3 rounded-3 d-flex align-items-start gap-3 cursor-pointer" style="background: rgba(255, 255, 255, 0.04); border: 2px solid {{ $currentAccessMode === 'specific_persons' ? '#fbbf24' : 'rgba(255, 255, 255, 0.1)' }}; cursor: pointer; transition: all 0.2s ease;">
                        <input type="radio" name="tree_access_mode" value="specific_persons" {{ $currentAccessMode === 'specific_persons' ? 'checked' : '' }} onchange="onAccessModeChanged('specific_persons')" class="form-check-input mt-1 flex-shrink-0">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold text-white fs-6">Only Specific Selected Persons</span>
                                <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-25 rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">ALLOWLIST</span>
                            </div>
                            <p class="text-white-50 small mb-0 mt-1" style="font-size: 0.8rem; line-height: 1.4;">
                                Only relatives you explicitly check as allowed below will be able to see your tree. Everyone else will be blocked.
                            </p>
                        </div>
                    </label>

                    <!-- Mode 3: Only Me -->
                    <label class="access-mode-card p-3 rounded-3 d-flex align-items-start gap-3 cursor-pointer" style="background: rgba(255, 255, 255, 0.04); border: 2px solid {{ $currentAccessMode === 'only_me' ? '#fbbf24' : 'rgba(255, 255, 255, 0.1)' }}; cursor: pointer; transition: all 0.2s ease;">
                        <input type="radio" name="tree_access_mode" value="only_me" {{ $currentAccessMode === 'only_me' ? 'checked' : '' }} onchange="onAccessModeChanged('only_me')" class="form-check-input mt-1 flex-shrink-0">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold text-white fs-6">Only Me (Private Lock)</span>
                                <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-25 rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">LOCKED</span>
                            </div>
                            <p class="text-white-50 small mb-0 mt-1" style="font-size: 0.8rem; line-height: 1.4;">
                                Only you can see your family tree. No other family members will have access.
                            </p>
                        </div>
                    </label>
                </div>

                <!-- Per-Person Access List Container -->
                <div id="perPersonSection" class="{{ $currentAccessMode === 'only_me' ? 'd-none' : '' }}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label text-white fw-bold small mb-0 text-uppercase" style="letter-spacing: 0.05em; font-size: 0.72rem;">
                            2. Granular Person-by-Person Permissions
                        </label>
                        <span class="text-white-50 small" id="accessMemberCountLabel" style="font-size: 0.75rem;">
                            {{ count($familyMembersList) }} {{ count($familyMembersList) === 1 ? 'member' : 'members' }} in family
                        </span>
                    </div>

                    <!-- Search Filter for Members -->
                    <div class="mb-3 position-relative">
                        <i class="bi bi-search position-absolute top-50 translate-middle-y text-white-50 ms-3" style="font-size: 0.85rem;"></i>
                        <input type="text" id="accessControlSearchInput" class="form-control rounded-pill ps-5 py-2 text-white" placeholder="Filter family relatives by name..." oninput="filterAccessControlList()" style="background: rgba(255, 255, 255, 0.08); border-color: rgba(255, 255, 255, 0.2); font-size: 0.85rem;">
                    </div>

                    <!-- Member Access Roster -->
                    <div id="accessMembersList" class="d-flex flex-column gap-2" style="max-height: 280px; overflow-y: auto;">
                        @forelse($familyMembersList as $mem)
                            @php
                                $mId = (string)($mem['user_id'] ?? '');
                                $mName = (string)($mem['full_name'] ?? 'Family Member');
                                $mAvatar = (string)($mem['avatar_url'] ?? '/resources/images/profile/avatarM.png');
                                $mIsAllowed = !empty($mem['is_allowed']);
                                $mRuleType = (string)($mem['rule_type'] ?? 'default');
                                $mViewCount = (int)($mem['view_count'] ?? 0);
                            @endphp
                            <div class="access-member-row p-2.5 rounded-3 d-flex align-items-center justify-content-between gap-2" data-name="{{ strtolower($mName) }}" data-user-id="{{ $mId }}" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08);">
                                <div class="d-flex align-items-center gap-2.5 overflow-hidden">
                                    <img src="{{ $mAvatar }}" alt="{{ $mName }}" class="rounded-circle object-fit-cover flex-shrink-0" style="width: 38px; height: 38px; border: 2px solid {{ $mIsAllowed ? '#10b981' : '#ef4444' }};" onerror="this.src='/resources/images/profile/avatarM.png'">
                                    <div class="overflow-hidden">
                                        <div class="fw-bold text-white text-truncate" style="font-size: 0.86rem;" title="{{ $mName }}">{{ $mName }}</div>
                                        <div class="text-white-50 small text-truncate" style="font-size: 0.72rem;">
                                            @if($mViewCount > 0)
                                                <span class="text-info"><i class="bi bi-eye"></i> Viewed {{ $mViewCount }}x</span> &middot;
                                            @endif
                                            <span class="badge-status-text {{ $mIsAllowed ? 'text-success' : 'text-danger' }} fw-semibold">
                                                {{ $mIsAllowed ? 'Has Access' : 'Access Limited' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    <button type="button" class="btn btn-sm {{ $mIsAllowed ? 'btn-outline-danger' : 'btn-outline-success' }} rounded-pill px-3 py-1 fw-bold btn-member-access-toggle" onclick="toggleMemberPermissionInModal('{{ $mId }}', this)" style="min-height: 44px; font-size: 0.76rem; white-space: nowrap; flex-shrink: 0;">
                                        @if($mIsAllowed)
                                            <i class="bi bi-slash-circle me-1"></i> Limit Access
                                        @else
                                            <i class="bi bi-check-circle me-1"></i> Grant Access
                                        @endif
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-white-50 small">
                                No other members currently found under family code #{{ $effectiveFamCode }}.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="modal-footer border-top border-secondary py-3 px-4 d-flex justify-content-between align-items-center" style="background: rgba(15, 23, 42, 0.98);">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal" style="min-height: 44px;">Cancel</button>
                <button type="button" id="btnSaveTreeAccessPolicy" class="btn btn-warning text-dark btn-sm fw-bold rounded-pill px-4 d-inline-flex align-items-center gap-1.5 shadow-sm" onclick="saveTreeAccessPolicy(this)" style="min-height: 44px;">
                    <i class="bi bi-shield-check"></i> Save Access Policy
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    // Current in-memory state for access policy modal
    window.__treeAccessState = {
        mode: '{{ $currentAccessMode }}',
        allowedUserIds: new Set({!! json_encode(array_values(array_filter(array_map(function($m) { return !empty($m['is_allowed']) ? (string)$m['user_id'] : null; }, $familyMembersList)))) !!}),
        deniedUserIds: new Set({!! json_encode(array_values(array_filter(array_map(function($m) { return empty($m['is_allowed']) ? (string)$m['user_id'] : null; }, $familyMembersList)))) !!}),
        csrfToken: '{{ $_SESSION['token'] ?? '' }}'
    };

    window.openWhoViewedTreeModal = function() {
        const modalEl = document.getElementById('whoViewedTreeModal');
        if (!modalEl) return;
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
        // Refresh live from server
        refreshWhoViewedData();
    };

    window.openTreeAccessControlModal = function() {
        const modalEl = document.getElementById('treeAccessControlModal');
        if (!modalEl) return;
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
        // Refresh live from server
        refreshTreeAccessSettingsData();
    };

    window.switchModalToAccessControl = function() {
        const modal1 = bootstrap.Modal.getInstance(document.getElementById('whoViewedTreeModal'));
        if (modal1) modal1.hide();
        setTimeout(window.openTreeAccessControlModal, 350);
    };

    window.onAccessModeChanged = function(newMode) {
        window.__treeAccessState.mode = newMode;
        // Update borders
        document.querySelectorAll('#accessModeCardsContainer .access-mode-card').forEach(card => {
            const radio = card.querySelector('input[type="radio"]');
            if (radio && radio.value === newMode) {
                card.style.borderColor = '#fbbf24';
            } else {
                card.style.borderColor = 'rgba(255, 255, 255, 0.1)';
            }
        });

        const perPersonSection = document.getElementById('perPersonSection');
        if (perPersonSection) {
            if (newMode === 'only_me') {
                perPersonSection.classList.add('d-none');
            } else {
                perPersonSection.classList.remove('d-none');
            }
        }
    };

    window.filterWhoViewedList = function() {
        const q = (document.getElementById('whoViewedSearchInput')?.value || '').trim().toLowerCase();
        document.querySelectorAll('#whoViewedListContainer .viewer-entry-card').forEach(card => {
            const name = card.getAttribute('data-name') || '';
            card.style.display = (name.includes(q) || q === '') ? 'flex' : 'none';
        });
    };

    window.filterAccessControlList = function() {
        const q = (document.getElementById('accessControlSearchInput')?.value || '').trim().toLowerCase();
        document.querySelectorAll('#accessMembersList .access-member-row').forEach(row => {
            const name = row.getAttribute('data-name') || '';
            row.style.display = (name.includes(q) || q === '') ? 'flex' : 'none';
        });
    };

    window.toggleMemberPermissionInModal = function(userId, btn) {
        if (!userId) return;
        const isCurrentlyAllowed = window.__treeAccessState.allowedUserIds.has(userId);
        const row = btn.closest('.access-member-row');
        const statusText = row?.querySelector('.badge-status-text');
        const img = row?.querySelector('img');

        if (isCurrentlyAllowed) {
            // Switch to limited
            window.__treeAccessState.allowedUserIds.delete(userId);
            window.__treeAccessState.deniedUserIds.add(userId);
            btn.className = 'btn btn-sm btn-outline-success rounded-pill px-3 py-1 fw-bold btn-member-access-toggle';
            btn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Grant Access';
            if (statusText) {
                statusText.className = 'badge-status-text text-danger fw-semibold';
                statusText.textContent = 'Access Limited';
            }
            if (img) img.style.borderColor = '#ef4444';
        } else {
            // Switch to allowed
            window.__treeAccessState.deniedUserIds.delete(userId);
            window.__treeAccessState.allowedUserIds.add(userId);
            btn.className = 'btn btn-sm btn-outline-danger rounded-pill px-3 py-1 fw-bold btn-member-access-toggle';
            btn.innerHTML = '<i class="bi bi-slash-circle me-1"></i> Limit Access';
            if (statusText) {
                statusText.className = 'badge-status-text text-success fw-semibold';
                statusText.textContent = 'Has Access';
            }
            if (img) img.style.borderColor = '#10b981';
        }
    };

    window.setPersonAccessDirectly = async function(targetUserId, action, btn) {
        if (!targetUserId) return;
        if (window.ScoreTenantSpinner) window.ScoreTenantSpinner.start(btn, 'Updating...');
        btn.disabled = true;

        try {
            const fd = new FormData();
            fd.append('target_user_id', targetUserId);
            fd.append('action', action);
            fd.append('token', window.__treeAccessState.csrfToken);
            fd.append('_token', window.__treeAccessState.csrfToken);

            const res = await fetch('/member/organogram/access-rule', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': window.__treeAccessState.csrfToken
                },
                body: fd
            });

            const data = await res.json();
            if (window.ScoreTenantSpinner) window.ScoreTenantSpinner.stop(btn);
            btn.disabled = false;

            if (data.success) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Access Updated',
                        text: data.message || 'Permission updated successfully.',
                        timer: 1800,
                        showConfirmButton: false
                    });
                }
                refreshWhoViewedData();
                refreshTreeAccessSettingsData();
            } else {
                if (window.Swal) {
                    Swal.fire('Error', data.error || 'Failed to update access.', 'error');
                } else {
                    alert(data.error || 'Failed to update access.');
                }
            }
        } catch (err) {
            if (window.ScoreTenantSpinner) window.ScoreTenantSpinner.stop(btn);
            btn.disabled = false;
            console.error('setPersonAccessDirectly error:', err);
        }
    };

    window.saveTreeAccessPolicy = async function(btn) {
        if (window.ScoreTenantSpinner) window.ScoreTenantSpinner.start(btn, 'Saving...');
        btn.disabled = true;

        try {
            const fd = new FormData();
            fd.append('access_mode', window.__treeAccessState.mode);
            fd.append('allowed_user_ids', Array.from(window.__treeAccessState.allowedUserIds).join(','));
            fd.append('denied_user_ids', Array.from(window.__treeAccessState.deniedUserIds).join(','));
            fd.append('token', window.__treeAccessState.csrfToken);
            fd.append('_token', window.__treeAccessState.csrfToken);

            const res = await fetch('/member/organogram/access-settings', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': window.__treeAccessState.csrfToken
                },
                body: fd
            });

            const data = await res.json();
            if (window.ScoreTenantSpinner) window.ScoreTenantSpinner.stop(btn);
            btn.disabled = false;

            if (data.success) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Privacy Policy Saved',
                        text: data.message || 'Your family tree access settings have been saved.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
                const modal = bootstrap.Modal.getInstance(document.getElementById('treeAccessControlModal'));
                if (modal) modal.hide();
                refreshWhoViewedData();
            } else {
                if (window.Swal) {
                    Swal.fire('Error', data.error || 'Failed to save access policy.', 'error');
                } else {
                    alert(data.error || 'Failed to save access policy.');
                }
            }
        } catch (err) {
            if (window.ScoreTenantSpinner) window.ScoreTenantSpinner.stop(btn);
            btn.disabled = false;
            console.error('saveTreeAccessPolicy error:', err);
        }
    };

    async function refreshWhoViewedData() {
        try {
            const res = await fetch('/member/organogram/tree-viewers', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (!data.success) return;

            const count = data.unique_viewers || 0;
            const total = data.total_views || 0;

            // Update badge counters across the page
            ['badgeHeaderViewersCount', 'bannerViewersCount', 'headerViewersBadge', 'treeUniqueViewersCount'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.textContent = count;
            });

            const sumText = document.getElementById('whoViewedSummaryText');
            if (sumText) {
                sumText.innerHTML = `<strong>${count}</strong> ${count === 1 ? 'relative' : 'relatives'} &middot; <strong>${total}</strong> total ${total === 1 ? 'view' : 'views'}`;
            }

            // Render items if viewers exist
            if (Array.isArray(data.viewers) && data.viewers.length > 0) {
                const cont = document.getElementById('whoViewedListContainer');
                if (cont) {
                    cont.innerHTML = data.viewers.map(v => {
                        const name = escHtml(v.full_name || 'Family Member');
                        const vId = escHtml(v.viewer_id);
                        const avatar = escHtml(v.avatar_url || '/resources/images/profile/avatarM.png');
                        const count = v.view_count || 1;
                        const time = escHtml(v.last_viewed_human || 'Recently');
                        const code = escHtml(v.family_code || '{{ $effectiveFamCode }}');
                        const isAllowed = Boolean(v.is_allowed);

                        const actionBtn = isAllowed
                            ? `<button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1 btn-toggle-access" onclick="setPersonAccessDirectly('${vId}', 'restrict', this)" style="min-height: 44px; font-size: 0.78rem; white-space: nowrap; flex-shrink: 0;" title="Limit access"><i class="bi bi-person-x-fill"></i> Limit Access</button>`
                            : `<button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1 btn-toggle-access" onclick="setPersonAccessDirectly('${vId}', 'allow', this)" style="min-height: 44px; font-size: 0.78rem; white-space: nowrap; flex-shrink: 0;" title="Restore access"><i class="bi bi-person-check-fill"></i> Restore Access</button>`;

                        return `
                            <div class="viewer-entry-card p-3 rounded-3 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2.5" data-name="${name.toLowerCase()}" data-user-id="${vId}" style="background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08); transition: background 0.2s ease;">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="position-relative flex-shrink-0">
                                        <img src="${avatar}" alt="${name}" class="rounded-circle object-fit-cover" style="width: 44px; height: 44px; border: 2px solid #38bdf8;" onerror="this.src='/resources/images/profile/avatarM.png'">
                                        <span class="position-absolute bottom-0 end-0 bg-success border border-dark rounded-circle" style="width: 10px; height: 10px;" title="Same Family Code Active"></span>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                            <h6 class="mb-0 fw-bold text-white fs-6 text-truncate" style="max-width: 220px;" title="${name}">${name}</h6>
                                            <span class="badge rounded-pill" style="font-size: 0.68rem; background: rgba(56, 189, 248, 0.2); color: #7dd3fc; border: 1px solid rgba(56, 189, 248, 0.4);">#${code}</span>
                                        </div>
                                        <div class="text-white-50 small mt-0.5" style="font-size: 0.76rem;">
                                            <span><i class="bi bi-clock-history me-1"></i>${time}</span> &middot; 
                                            <span class="text-info fw-semibold">${count} ${count === 1 ? 'view' : 'views'}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-1.5 flex-shrink-0 align-self-end align-self-sm-center">
                                    ${actionBtn}
                                </div>
                            </div>
                        `;
                    }).join('');
                }
            }
        } catch (e) {
            console.error('refreshWhoViewedData error:', e);
        }
    }

    async function refreshTreeAccessSettingsData() {
        try {
            const res = await fetch('/member/organogram/access-settings', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (!data.success) return;

            window.__treeAccessState.mode = data.access_mode || 'family_all';
            window.onAccessModeChanged(window.__treeAccessState.mode);

            if (Array.isArray(data.members)) {
                window.__treeAccessState.allowedUserIds = new Set(
                    data.members.filter(m => m.is_allowed).map(m => String(m.user_id))
                );
                window.__treeAccessState.deniedUserIds = new Set(
                    data.members.filter(m => !m.is_allowed).map(m => String(m.user_id))
                );
            }
        } catch (e) {
            console.error('refreshTreeAccessSettingsData error:', e);
        }
    }

    function escHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
})();
</script>
