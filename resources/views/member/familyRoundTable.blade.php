@extends('layouts.profileBase')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800;900&family=Montserrat:wght@500;600;700;800;900&display=swap" rel="stylesheet">
<style>
.roundtable-hero {
    background: linear-gradient(135deg, rgba(15, 23, 42, 0.03) 0%, rgba(99, 102, 241, 0.06) 100%);
    border-radius: 24px;
    padding: 32px 20px;
    border: 1px solid rgba(212, 175, 55, 0.25);
}
.gold-seal-badge {
    background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%);
    border: 1.5px solid #d4af37;
    color: #ffd700;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
.hover-lift {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.08) !important;
}
</style>
@endpush

@section('content')
<div class="container-fluid py-4" style="max-width: 1200px;">
    {{-- RoundTable Header --}}
    <div class="roundtable-hero text-center mb-4">
        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-white shadow-sm small fw-bold mb-3" style="color: #4338ca;">
            <i class="bi bi-shield-shaded text-warning"></i> ✨ Kinship Council &bull; Reconciliatory Justice
        </div>
        <h1 class="h2 fw-bold text-dark mb-2" style="font-family: 'Cinzel', serif; letter-spacing: -0.5px;">Family RoundTable</h1>
        <p class="text-muted mx-auto mb-0" style="max-width: 680px;">
            A dignified, ancestral council for resolving complex family issues, hearing all perspectives with empathy, and sealing reconciliatory accords that preserve family unity.
        </p>
    </div>

    {{-- Council Sub-Nav: Grand Council vs Fast 2-Minute Peacemaker --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 bg-white p-2 rounded-4 shadow-sm border">
        <div class="nav nav-pills gap-2" id="roundTableModes" role="tablist">
            <button class="nav-link active rounded-pill fw-semibold py-2 px-3 small" id="mode-tribunal-btn" data-bs-toggle="pill" data-bs-target="#court-mode-tribunal" type="button" role="tab" style="min-height: 44px;">
                <i class="bi bi-diagram-3-fill me-1 text-primary"></i> The Grand Council (Complex Issues)
            </button>
            <button class="nav-link rounded-pill fw-semibold py-2 px-3 small" id="mode-instant-btn" data-bs-toggle="pill" data-bs-target="#court-mode-instant" type="button" role="tab" style="min-height: 44px;">
                <i class="bi bi-lightning-charge-fill me-1 text-warning"></i> Quick 2-Minute Peacemaker
            </button>
        </div>
        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 bg-light rounded-pill small text-muted">
            <i class="bi bi-peace text-success"></i> Reconciliatory &bull; Firm &bull; Non-Punitive
        </div>
    </div>

    <div class="tab-content" id="courtModeContent">
        {{-- MODE A: THE GRAND COUNCIL --}}
        <div class="tab-pane fade show active" id="court-mode-tribunal" role="tabpanel">
            <div class="row g-4">
                {{-- Left: File Dispute Docket --}}
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-shield-shaded text-primary me-2"></i> Convene Council</h4>
                            <span class="badge gold-seal-badge rounded-pill px-3 py-1">RoundTable</span>
                        </div>
                        <p class="text-muted small mb-3">
                            Initiate a reconciliatory hearing. Both parties and named witnesses will be invited to share their perspective before the Council seals a binding family accord.
                        </p>

                        <form id="complex-dispute-form" onsubmit="handleComplexDisputeSubmit(event)">
                            @csrf

                            {{-- 🕊️ 3rd Party Peacemaker Switch --}}
                            <div class="p-3 mb-3 rounded-3 border" style="background: linear-gradient(135deg, rgba(67, 56, 202, 0.04) 0%, rgba(99, 102, 241, 0.08) 100%);">
                                <div class="form-check form-switch mb-1">
                                    <input class="form-check-input" type="checkbox" role="switch" id="is-third-party-switch" onchange="togglePeacemakerMode(this.checked)" style="cursor: pointer;">
                                    <label class="form-check-label fw-bold text-dark small" for="is-third-party-switch" style="cursor: pointer;">
                                        🕊️ Concerned Peacemaker Initiation
                                    </label>
                                </div>
                                <small class="text-muted d-block" style="font-size: 0.8rem;">
                                    Enable if you are a third party (e.g. parent, sibling, or elder) observing two family members feuding, and asking the RoundTable to mediate.
                                </small>
                            </div>

                            {{-- Dispute Title & Category --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Dispute Matter / Headline</label>
                                <input type="text" id="complex-title" class="form-control rounded-3" placeholder="e.g. Kitchen Rota &amp; Late Night Cleanliness" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Category</label>
                                <select id="complex-category" class="form-select rounded-3">
                                    <option value="household_chores">🧹 Household Chores &amp; Dishwasher Rota</option>
                                    <option value="boundaries_space">🚪 Personal Space, Noise &amp; Boundaries</option>
                                    <option value="borrowed_property">📱 Borrowing Clothes, Electronics &amp; Belongings</option>
                                    <option value="family_events">📅 Family Event Attendance &amp; Planning</option>
                                    <option value="finances_split">💳 Shared Expenses &amp; Takeaway Splits</option>
                                    <option value="respect_communication">🗣️ Tone of Voice &amp; Communication Frustrations</option>
                                    <option value="general_harmony">🌿 General Kinship Reconciliation</option>
                                </select>
                            </div>

                            {{-- Party A --}}
                            <div class="mb-3" id="party-a-wrap">
                                <label class="form-label fw-semibold small">
                                    <span id="party-a-label">First Person (Party A)</span>
                                </label>
                                <select id="complex-party-a" class="form-select rounded-3" required>
                                    <option value="{{ $userData['id'] ?? '' }}" selected>{{ $userData['firstName'] ?? 'You' }} {{ $userData['lastName'] ?? '(Myself)' }}</option>
                                    @foreach($familyMembers as $m)
                                        @if(($m['id'] ?? '') !== ($userData['id'] ?? ''))
                                            <option value="{{ $m['id'] }}">{{ $m['firstName'] }} {{ $m['lastName'] }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            {{-- Party B --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Second Person (Party B)</label>
                                <select id="complex-party-b" class="form-select rounded-3" required>
                                    <option value="" disabled selected>-- Select family member --</option>
                                    @foreach($familyMembers as $m)
                                        @if(($m['id'] ?? '') !== ($userData['id'] ?? ''))
                                            <option value="{{ $m['id'] }}">{{ $m['firstName'] }} {{ $m['lastName'] }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            {{-- Named Witnesses (Dropdown) --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold small" for="complex-witness-select">
                                    Named Eyewitnesses / Confidants <span class="text-muted fw-normal">(Optional)</span>
                                </label>
                                <select id="complex-witness-select" class="form-select rounded-3" onchange="handleWitnessSelectChange(this)">
                                    <option value="" selected>-- Select eyewitness / confidant --</option>
                                    @foreach($familyMembers as $m)
                                        @if(($m['id'] ?? '') !== ($userData['id'] ?? ''))
                                            <option value="{{ $m['id'] }}" data-name="{{ $m['firstName'] }} {{ $m['lastName'] }}">
                                                {{ $m['firstName'] }} {{ $m['lastName'] }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                <div id="selected-witness-pills" class="d-flex flex-wrap gap-2 mt-2"></div>
                                <small class="text-muted d-block mt-1" style="font-size: 0.78rem;">
                                    Select from the dropdown to invite one or more family confidants to share their objective observation.
                                </small>
                            </div>

                            {{-- Grievance Statement --}}
                            <div class="mb-4">
                                <label class="form-label fw-semibold small" id="grievance-label">Your Statement / Grievance</label>
                                <textarea id="complex-grievance" class="form-control rounded-3" rows="3" placeholder="Describe the situation fairly and honestly. What happened and how did it affect you?" required></textarea>
                            </div>

                            <button type="submit" id="btn-submit-complex-dispute" class="btn btn-primary rounded-pill w-100 fw-bold py-2 shadow-sm" style="background: linear-gradient(135deg, #1e1b4b, #312e81); border: 1.5px solid #d4af37; min-height: 44px;">
                                <i class="bi bi-shield-shaded text-warning me-1"></i> Convene Family RoundTable
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Right: Active Hearings & Sealed Accords Docket --}}
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-journal-bookmark-fill text-primary me-2"></i> Council Docket</h4>
                                <p class="text-muted small mb-0">Active hearings and sealed RoundTable accords</p>
                            </div>
                            <span class="badge rounded-pill px-3 py-1" style="background: #1e1b4b; color: #ffd700; border: 1px solid #d4af37;">
                                {{ count($disputes ?? []) }} Docket(s)
                            </span>
                        </div>

                        {{-- Dispute List / Cards --}}
                        <div class="d-flex flex-column gap-3" id="dispute-docket-list" style="max-height: 600px; overflow-y: auto;">
                            @forelse($disputes ?? [] as $dsp)
                                <div class="card border border-light-subtle rounded-3 p-3 shadow-xs hover-lift" style="cursor: pointer;" onclick="openDisputeDocket('{{ $dsp['id'] }}')">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="fw-bold text-dark mb-1">{{ $dsp['title'] }}</h6>
                                            <div class="small text-muted">
                                                <span><i class="bi bi-people me-1"></i> {{ $dsp['partyA_firstName'] ?? 'Party A' }} &amp; {{ $dsp['partyB_firstName'] ?? 'Party B' }}</span>
                                                @if(!empty($dsp['is_third_party']))
                                                    <span class="badge bg-light text-primary border ms-2">🕊️ Mediated by {{ $dsp['initiator_firstName'] ?? 'Peacemaker' }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div>
                                            @if(($dsp['status'] ?? '') === 'resolved')
                                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-bold">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Sealed Accord
                                                </span>
                                            @elseif(($dsp['status'] ?? '') === 'ready_for_judgement')
                                                <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-3 py-1 fw-bold">
                                                    <i class="bi bi-hourglass-split me-1"></i> Ready for Accord
                                                </span>
                                            @else
                                                <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3 py-1 fw-bold">
                                                    <i class="bi bi-chat-dots-fill me-1"></i> Awaiting Statements
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-2 text-truncate" style="max-width: 90%;">
                                        {{ $dsp['grievance'] }}
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                        <small class="text-muted" style="font-size: 0.75rem;">
                                            <i class="bi bi-clock me-1"></i> Convened {{ date('j M Y, H:i', strtotime($dsp['created_at'])) }}
                                        </small>
                                        <span class="text-primary small fw-semibold">
                                            View Docket &amp; Accord <i class="bi bi-arrow-right ms-1"></i>
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5 text-muted bg-light rounded-4">
                                    <i class="bi bi-peace display-4 d-block mb-3 text-secondary opacity-50"></i>
                                    <h6 class="fw-bold text-dark">No Active Council Dockets</h6>
                                    <p class="small text-muted mb-0">Peace reigns in your household! Use the form on the left if any friction or domestic disagreement arises.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODE B: QUICK 2-MINUTE PEACEMAKER --}}
        <div class="tab-pane fade" id="court-mode-instant" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        <h4 class="fw-bold mb-3 text-dark"><i class="bi bi-lightning-charge text-warning me-2"></i> Quick 2-Minute Peacemaker</h4>
                        <p class="text-muted small mb-4">A fast-turn instant mediator for everyday sibling banter, chore debates, or TV remote disputes. The RoundTable delivers an immediate reconciliatory resolution.</p>

                        <form id="dispute-form" onsubmit="handleArbitrate(event)">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">First Person (Party A)</label>
                                <input type="text" id="party-a-name" class="form-control rounded-3" value="{{ $userData['firstName'] ?? 'Liam' }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Party A's Grievance</label>
                                <textarea id="party-a-claim" class="form-control rounded-3" rows="2" placeholder="e.g. I unloaded the dishwasher yesterday, it is Maya's turn today!" required>I unloaded the dishwasher yesterday, it is Maya's turn today!</textarea>
                            </div>

                            <hr class="my-3">

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Second Person (Party B)</label>
                                <input type="text" id="party-b-name" class="form-control rounded-3" value="Maya" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Party B's Counter-Claim</label>
                                <textarea id="party-b-claim" class="form-control rounded-3" rows="2" placeholder="e.g. I had football practice and homework, Liam was playing video games all evening!" required>I had football practice and homework, Liam was playing video games all evening!</textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold small">Dispute Category</label>
                                <select id="dispute-context" class="form-select rounded-3">
                                    <option value="household_chores">🧹 Household Chores &amp; Dishes</option>
                                    <option value="tv_remote">📺 TV Remote &amp; Screen Time</option>
                                    <option value="bathroom_turn">🚿 Morning Bathroom Schedule</option>
                                    <option value="last_snack">🍕 Who Gets the Last Slice/Snack</option>
                                </select>
                            </div>

                            <button type="submit" id="btn-arbitrate" class="btn btn-primary rounded-pill w-100 fw-bold py-2 shadow-sm" style="background: linear-gradient(135deg, #1e1b4b, #312e81); border: 1.5px solid #d4af37; min-height: 44px;">
                                <i class="bi bi-peace me-1"></i> Resolve with Family RoundTable
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="fw-bold mb-0 text-dark">Council Accord</h4>
                            <span class="badge gold-seal-badge rounded-pill px-3 py-1">Official Resolution</span>
                        </div>

                        <div id="verdict-placeholder" class="text-center py-5 text-muted">
                            <i class="bi bi-shield-check display-3 d-block mb-3 text-secondary opacity-50"></i>
                            <span>Submit a dispute to receive the Family RoundTable's reconciliatory and firm accord.</span>
                        </div>

                        <div id="verdict-card" style="display: none;" class="animate__animated animate__fadeIn">
                            <div class="alert alert-purple bg-light border-purple p-4 rounded-4 mb-4">
                                <h5 class="fw-bold text-dark mb-2"><i class="bi bi-search text-warning me-2"></i> The Finding of Fact</h5>
                                <p id="verdict-box" class="lead text-dark mb-0 fs-6"></p>
                            </div>

                            <div class="card border border-light-subtle rounded-4 p-4 mb-4 bg-white shadow-xs">
                                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-list-check text-success me-2"></i> Restorative Action Plan</h6>
                                <div id="action-plan-list" class="d-flex flex-column gap-2"></div>
                            </div>

                            <div class="card bg-warning bg-opacity-10 border-warning border-opacity-25 rounded-4 p-3 mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-peace-fill text-primary fs-5"></i>
                                    <div>
                                        <div class="fw-bold text-dark small">Reconciliation Standard</div>
                                        <div id="penalty-box" class="small text-muted"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end fst-italic text-muted small" id="closing-quip"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 🏛️ FAMILY ROUNDTABLE DOCKET MODAL --}}
<div class="modal fade" id="disputeDocketModal" tabindex="-1" aria-labelledby="disputeDocketModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom pb-3">
                <div class="w-100 pe-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="badge rounded-pill px-3 py-1" id="modal-docket-status">Status</span>
                        <small class="text-muted" id="modal-docket-category">Category</small>
                    </div>
                    <h5 class="modal-title fw-bold text-dark" id="modal-docket-title">Dispute Title</h5>
                    <div class="small text-muted" id="modal-docket-participants">Parties involved</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="min-height: 44px; min-width: 44px;"></button>
            </div>
            <div class="modal-body py-3">
                {{-- Sealed Accord Section (Shown if resolved) --}}
                <div id="modal-verdict-section" style="display: none;" class="mb-4">
                    <div class="p-4 rounded-4" style="background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%); border: 2px solid #d4af37; box-shadow: 0 10px 30px rgba(0,0,0,0.25);">
                        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2 border-warning border-opacity-25">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fs-4">🛡️</span>
                                <h5 class="fw-bold text-warning mb-0" id="verdict-modal-heading">The RoundTable Accord</h5>
                            </div>
                            <span class="badge bg-warning text-dark fw-bold rounded-pill px-3 py-1">Sealed Accord</span>
                        </div>

                        {{-- Finding of Fact --}}
                        <div class="mb-3">
                            <h6 class="fw-bold text-light small text-uppercase tracking-wider mb-1"><i class="bi bi-search text-warning me-1"></i> Finding of Fact</h6>
                            <p class="text-white-50 small mb-0 lh-base" id="verdict-modal-finding"></p>
                        </div>

                        {{-- Reconciliatory Bridge --}}
                        <div class="mb-3 p-3 rounded-3" style="background: rgba(255, 255, 255, 0.08); border-left: 3px solid #10b981;">
                            <h6 class="fw-bold text-emerald small text-uppercase tracking-wider mb-1 text-success"><i class="bi bi-peace text-success me-1"></i> The Reconciliatory Bridge</h6>
                            <p class="text-light small mb-0 lh-base" id="verdict-modal-bridge"></p>
                        </div>

                        {{-- Restorative Action Plan --}}
                        <div class="mb-3">
                            <h6 class="fw-bold text-light small text-uppercase tracking-wider mb-2"><i class="bi bi-list-check text-warning me-1"></i> Restorative Action Plan</h6>
                            <div class="d-flex flex-column gap-2" id="verdict-modal-plan"></div>
                        </div>

                        {{-- Council Wisdom --}}
                        <div class="text-end fst-italic text-warning small pt-2 border-top border-warning border-opacity-25" id="verdict-modal-wisdom"></div>
                    </div>
                </div>

                {{-- Statements Timeline --}}
                <div class="mb-4">
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-chat-quote-fill text-primary me-2"></i> Hearing Statements &amp; Testimonies</h6>
                    <div class="d-flex flex-column gap-3" id="modal-statements-list"></div>
                </div>

                {{-- Submit Statement Box (If hearing active) --}}
                <div id="modal-statement-form-wrap" class="p-3 bg-light rounded-4 border">
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-pencil-fill text-primary me-2"></i> Add Your Perspective</h6>
                    <p class="text-muted small mb-2">Speak from the heart with dignity. Your account will help the RoundTable construct a reconciliatory accord.</p>
                    <form onsubmit="submitStatementToDocket(event)">
                        @csrf
                        <textarea id="modal-user-statement" class="form-control rounded-3 mb-3" rows="3" placeholder="Share your account, observations, or response..." required></textarea>
                        <div class="d-flex justify-content-between align-items-center">
                            <button type="submit" id="btn-submit-statement" class="btn btn-primary rounded-pill px-4 fw-semibold" style="min-height: 44px;">
                                <i class="bi bi-send-fill me-1"></i> Submit Statement
                            </button>
                            <button type="button" id="btn-convene-judgement" class="btn btn-outline-primary rounded-pill px-4 fw-bold" onclick="conveneCourtJudgement()" style="min-height: 44px; display: none;">
                                <i class="bi bi-shield-shaded me-1"></i> Convene Council &amp; Seal Accord
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal" style="min-height: 44px;">Close Docket</button>
            </div>
        </div>
    </div>
</div>

<script>
    let activeDisputeId = null;
    const selectedWitnesses = new Map();

    function handleWitnessSelectChange(selectEl) {
        const val = selectEl.value;
        if (!val) return;
        const opt = selectEl.options[selectEl.selectedIndex];
        const name = (opt && opt.getAttribute('data-name')) ? opt.getAttribute('data-name') : (opt ? opt.textContent.trim() : 'Eyewitness');
        selectedWitnesses.set(String(val), name);
        renderSelectedWitnessPills();
    }

    function removeSelectedWitness(id) {
        selectedWitnesses.delete(String(id));
        const selectEl = document.getElementById('complex-witness-select');
        if (selectEl && String(selectEl.value) === String(id)) {
            selectEl.value = '';
        }
        renderSelectedWitnessPills();
    }

    function renderSelectedWitnessPills() {
        const container = document.getElementById('selected-witness-pills');
        if (!container) return;
        container.innerHTML = '';
        selectedWitnesses.forEach((name, id) => {
            const badge = document.createElement('span');
            badge.className = 'badge rounded-pill d-inline-flex align-items-center gap-2 px-3 py-2 fw-semibold';
            badge.style.background = '#f1f5f9';
            badge.style.color = '#1e1b4b';
            badge.style.border = '1px solid #cbd5e1';
            badge.innerHTML = `<i class="bi bi-eye text-primary"></i> <span>${name}</span> <button type="button" class="btn-close" style="font-size: 0.65rem;" aria-label="Remove" onclick="removeSelectedWitness('${id}')"></button>`;
            container.appendChild(badge);
        });
    }

    function togglePeacemakerMode(isThirdParty) {
        const partyALabel = document.getElementById('party-a-label');
        const grievanceLabel = document.getElementById('grievance-label');
        const partyASelect = document.getElementById('complex-party-a');

        if (isThirdParty) {
            partyALabel.innerHTML = 'First Feuding Member (Party A)';
            grievanceLabel.innerHTML = 'Your Observation as Peacemaker (What issue are they facing?)';
            if (partyASelect.options.length > 1) {
                partyASelect.selectedIndex = 1;
            }
        } else {
            partyALabel.innerHTML = 'First Person (Party A)';
            grievanceLabel.innerHTML = 'Your Statement / Grievance';
            partyASelect.selectedIndex = 0;
        }
    }

    async function handleComplexDisputeSubmit(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-submit-complex-dispute');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Convening Family RoundTable...';

        const isThirdParty = document.getElementById('is-third-party-switch').checked;
        const partyA = document.getElementById('complex-party-a').value;
        const partyB = document.getElementById('complex-party-b').value;
        const title = document.getElementById('complex-title').value;
        const category = document.getElementById('complex-category').value;
        const grievance = document.getElementById('complex-grievance').value;

        const witnessSelectEl = document.getElementById('complex-witness-select');
        if (witnessSelectEl && witnessSelectEl.value) {
            const opt = witnessSelectEl.options[witnessSelectEl.selectedIndex];
            const name = opt ? opt.textContent.trim() : 'Eyewitness';
            selectedWitnesses.set(String(witnessSelectEl.value), name);
        }
        const witnessIds = Array.from(selectedWitnesses.keys());

        if (!partyB) {
            alert('Please select the second party (Party B).');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-shield-shaded text-warning me-1"></i> Convene Family RoundTable';
            return;
        }

        if (partyA === partyB) {
            alert('Party A and Party B must be different family members.');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-shield-shaded text-warning me-1"></i> Convene Family RoundTable';
            return;
        }

        try {
            const res = await fetch('/api/roundtable/file-dispute', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    is_third_party: isThirdParty,
                    party_a_id: partyA,
                    party_b_id: partyB,
                    witness_ids: witnessIds,
                    title: title,
                    category: category,
                    grievance: grievance
                })
            });

            const data = await res.json();
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-shield-shaded text-warning me-1"></i> Convene Family RoundTable';

            if (data.success) {
                alert('Council hearing successfully convened! All parties and witnesses have been notified.');
                if (data.dispute_id) {
                    window.location.href = '/family/roundtable?dispute_id=' + encodeURIComponent(data.dispute_id);
                } else {
                    window.location.reload();
                }
            } else {
                alert(data.error || 'Failed to convene council hearing.');
            }
        } catch (err) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-shield-shaded text-warning me-1"></i> Convene Family RoundTable';
            alert('Request error: ' + err.message);
        }
    }

    async function openDisputeDocket(disputeId) {
        activeDisputeId = disputeId;
        const modalEl = document.getElementById('disputeDocketModal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

        document.getElementById('modal-docket-title').textContent = 'Loading Council Docket...';
        document.getElementById('modal-statements-list').innerHTML = '<div class="text-center py-4"><span class="spinner-border spinner-border-sm me-2"></span> Convening RoundTable records...</div>';
        document.getElementById('modal-verdict-section').style.display = 'none';
        modal.show();

        try {
            const res = await fetch('/api/roundtable/dispute/' + encodeURIComponent(disputeId));
            const data = await res.json();

            if (!data.success || !data.dispute) {
                alert(data.error || 'Failed to load dispute details.');
                modal.hide();
                return;
            }

            const d = data.dispute;
            document.getElementById('modal-docket-title').textContent = d.title;
            document.getElementById('modal-docket-category').textContent = (d.category || 'General').replace('_', ' ').toUpperCase();

            const statusBadge = document.getElementById('modal-docket-status');
            if (d.status === 'resolved') {
                statusBadge.className = 'badge bg-success rounded-pill px-3 py-1';
                statusBadge.textContent = 'Sealed Accord';
            } else if (d.status === 'ready_for_judgement') {
                statusBadge.className = 'badge bg-info text-dark rounded-pill px-3 py-1';
                statusBadge.textContent = 'Ready for Accord';
            } else {
                statusBadge.className = 'badge bg-warning text-dark rounded-pill px-3 py-1';
                statusBadge.textContent = 'Awaiting Statements';
            }

            const partyAName = d.party_a ? `${d.party_a.firstName} ${d.party_a.lastName}` : 'Party A';
            const partyBName = d.party_b ? `${d.party_b.firstName} ${d.party_b.lastName}` : 'Party B';
            const initName = d.initiator ? `${d.initiator.firstName} ${d.initiator.lastName}` : 'Initiator';

            let partsText = `${partyAName} & ${partyBName}`;
            if (d.is_third_party) {
                partsText += ` (🕊️ Peacemaker: ${initName})`;
            }
            document.getElementById('modal-docket-participants').textContent = partsText;

            // Render Statements
            const sList = document.getElementById('modal-statements-list');
            sList.innerHTML = '';
            (d.statements || []).forEach(st => {
                const authorName = st.author ? `${st.author.firstName} ${st.author.lastName}` : 'Participant';
                const roleBadge = st.role === 'party_a' 
                    ? '<span class="badge bg-primary bg-opacity-10 text-primary small">Party A</span>'
                    : (st.role === 'party_b' 
                        ? '<span class="badge bg-info bg-opacity-10 text-info small">Party B</span>'
                        : (st.role === 'initiator'
                            ? '<span class="badge bg-purple bg-opacity-10 text-purple small">🕊️ Peacemaker</span>'
                            : '<span class="badge bg-secondary bg-opacity-10 text-secondary small">Eyewitness</span>'));

                const row = document.createElement('div');
                row.className = 'p-3 rounded-3 border bg-white shadow-xs';
                row.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold small text-dark">${authorName}</span>
                            ${roleBadge}
                        </div>
                        <small class="text-muted" style="font-size: 0.72rem;">${st.created_at || ''}</small>
                    </div>
                    <p class="text-muted small mb-0 lh-base">${st.statement || ''}</p>
                `;
                sList.appendChild(row);
            });

            if (d.status === 'resolved') {
                document.getElementById('modal-verdict-section').style.display = 'block';
                document.getElementById('modal-statement-form-wrap').style.display = 'none';

                document.getElementById('verdict-modal-heading').textContent = d.verdict_title || 'The RoundTable Accord';
                document.getElementById('verdict-modal-finding').textContent = d.finding_of_fact || '';
                document.getElementById('verdict-modal-bridge').textContent = d.reconciliatory_bridge || '';

                const planBox = document.getElementById('verdict-modal-plan');
                planBox.innerHTML = '';
                const plan = d.action_plan_decoded || {};
                for (const [key, val] of Object.entries(plan)) {
                    const planRow = document.createElement('div');
                    planRow.className = 'p-2 rounded-2 bg-white bg-opacity-10 text-light small d-flex align-items-start gap-2';
                    planRow.innerHTML = `<span class="badge bg-warning text-dark fw-bold">${key}:</span> <span>${val}</span>`;
                    planBox.appendChild(planRow);
                }

                document.getElementById('verdict-modal-wisdom').textContent = d.yommy_wisdom ? `"${d.yommy_wisdom}" — The Family RoundTable` : '';
            } else {
                document.getElementById('modal-verdict-section').style.display = 'none';
                document.getElementById('modal-statement-form-wrap').style.display = 'block';

                const conveneBtn = document.getElementById('btn-convene-judgement');
                conveneBtn.style.display = (d.status === 'ready_for_judgement' || (d.statements && d.statements.length >= 2)) ? 'inline-block' : 'none';
            }
        } catch (err) {
            alert('Failed to load docket: ' + err.message);
        }
    }

    async function submitStatementToDocket(e) {
        e.preventDefault();
        if (!activeDisputeId) return;

        const btn = document.getElementById('btn-submit-statement');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Submitting...';

        const text = document.getElementById('modal-user-statement').value;

        try {
            const res = await fetch('/api/roundtable/submit-statement', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    dispute_id: activeDisputeId,
                    statement: text
                })
            });

            const data = await res.json();
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send-fill me-1"></i> Submit Statement';

            if (data.success) {
                document.getElementById('modal-user-statement').value = '';
                openDisputeDocket(activeDisputeId);
            } else {
                alert(data.error || 'Failed to submit statement.');
            }
        } catch (err) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send-fill me-1"></i> Submit Statement';
            alert('Error submitting statement: ' + err.message);
        }
    }

    async function conveneCourtJudgement() {
        if (!activeDisputeId) return;
        if (!confirm('Convene the Family RoundTable to seal the reconciliatory accord now?')) return;

        const btn = document.getElementById('btn-convene-judgement');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Sealing Accord...';

        try {
            const res = await fetch('/api/roundtable/adjudicate', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ dispute_id: activeDisputeId })
            });

            const data = await res.json();
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-shield-shaded me-1"></i> Convene Council &amp; Seal Accord';

            if (data.success) {
                openDisputeDocket(activeDisputeId);
            } else {
                alert(data.error || 'Accord generation failed.');
            }
        } catch (err) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-shield-shaded me-1"></i> Convene Council &amp; Seal Accord';
            alert('Request error: ' + err.message);
        }
    }

    @if(!empty($selectedDispute))
    document.addEventListener('DOMContentLoaded', function() {
        openDisputeDocket('{{ $selectedDispute['id'] }}');
    });
    @endif

    // ⚡ QUICK 2-MINUTE PEACEMAKER ARBITRATION
    async function handleArbitrate(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-arbitrate');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Deliberating Accord...';

        try {
            const res = await fetch('/api/roundtable/arbitrate-instant', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    party_a_name: document.getElementById('party-a-name').value,
                    party_a_claim: document.getElementById('party-a-claim').value,
                    party_b_name: document.getElementById('party-b-name').value,
                    party_b_claim: document.getElementById('party-b-claim').value,
                    context: document.getElementById('dispute-context').value
                })
            });
            const data = await res.json();
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-peace me-1"></i> Resolve with Family RoundTable';

            if (data.success) {
                document.getElementById('verdict-placeholder').style.display = 'none';
                document.getElementById('verdict-box').textContent = data.verdict;

                const planList = document.getElementById('action-plan-list');
                planList.innerHTML = '';
                for (const [person, task] of Object.entries(data.compromise_action_plan || {})) {
                    const row = document.createElement('div');
                    row.className = 'd-flex align-items-center justify-content-between p-2 bg-light rounded-3';
                    row.innerHTML = `<span class="fw-bold small text-dark">${person}:</span><span class="small text-muted">${task}</span>`;
                    planList.appendChild(row);
                }

                document.getElementById('penalty-box').textContent = data.penalty_or_chore;
                document.getElementById('closing-quip').textContent = `"${data.closing_quip}" — The Family RoundTable`;
                document.getElementById('verdict-card').style.display = 'block';
            } else {
                alert(data.error || 'Peacemaker arbitration failed. Please check server logs.');
            }
        } catch (err) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-peace me-1"></i> Resolve with Family RoundTable';
            alert('Request error: ' + err.message);
        }
    }
</script>
@endsection
