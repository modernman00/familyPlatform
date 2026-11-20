@extends('layouts.profileBase')

@push('styles')
<style>
.commitments-hero {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    border-radius: var(--radius-lg, 20px);
    padding: var(--space-8, 32px) var(--space-6, 24px);
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: #ffffff;
}
.commitment-card {
    position: relative;
    background-color: var(--card-bg, #ffffff);
    border: 1px solid var(--border-color, #cbd5e1);
    border-radius: var(--radius-lg, 20px);
    padding: var(--space-6, 24px);
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);
    transition: transform var(--motion-fast, 150ms) ease, box-shadow var(--motion-fast, 150ms) ease;
    overflow: hidden;
}
.commitment-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: #a31621;
}
.commitment-card.is-period-met::before {
    background: #059669;
}
@media (hover: hover) {
    .commitment-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.1);
    }
}
.commitment-icon-medallion {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1.4rem;
}
.commitment-card-title {
    font-size: 1.375rem;
    font-weight: 800;
    line-height: 1.3;
    letter-spacing: -0.015em;
    color: var(--text-color, #0f172a);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    word-break: break-word;
}
.commitment-card-statement {
    font-size: 1.0625rem;
    line-height: 1.55;
    font-weight: 500;
    color: var(--text-muted-aa, #334155);
}
.commitment-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: var(--text-sm, 0.875rem);
    font-weight: 700;
    padding: 6px 14px;
    border-radius: 999px;
    line-height: 1.2;
    white-space: nowrap;
}
.commitment-progress-panel {
    background: rgba(15, 23, 42, 0.035);
    border: 1px solid var(--border-color, #cbd5e1);
    border-radius: 16px;
    padding: 18px 20px;
}
body.dark-mode .commitment-progress-panel,
body.dark-theme .commitment-progress-panel,
[data-bs-theme="dark"] .commitment-progress-panel {
    background: rgba(255, 255, 255, 0.04);
}
.commitment-quota-number {
    font-size: var(--text-lg, 1.25rem);
    font-weight: 800;
    font-variant-numeric: tabular-nums;
}
.commitment-meta-row {
    font-size: var(--text-base, 1rem);
    font-weight: 600;
    color: var(--text-color, #1e293b);
}
.commitment-day-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 4px 10px;
    border-radius: 8px;
    font-size: var(--text-sm, 0.875rem);
    font-weight: 700;
    background: rgba(163, 22, 33, 0.1);
    color: #a31621;
}
body.dark-mode .commitment-day-chip,
body.dark-theme .commitment-day-chip,
[data-bs-theme="dark"] .commitment-day-chip {
    background: rgba(255, 77, 97, 0.18);
    color: #fecdd3;
}
.commitment-checkin-box {
    font-size: var(--text-base, 1rem);
    line-height: 1.55;
    padding: 16px 18px;
    border-radius: 14px;
    background: rgba(5, 150, 105, 0.07);
    border: 1px solid rgba(5, 150, 105, 0.24);
}
.commitment-btn {
    min-height: 48px;
    min-width: 48px;
    font-size: var(--text-base, 1rem);
    white-space: nowrap;
    flex-shrink: 0;
}
.commitment-btn:active {
    transform: scale(0.98);
}
.day-pill-btn {
    min-height: 48px;
    min-width: 54px;
    white-space: nowrap;
    flex-shrink: 0;
    border-radius: 12px;
    font-weight: 700;
    font-size: var(--text-base, 1rem);
}
.rating-star-btn {
    min-height: 48px;
    min-width: 48px;
    border-radius: 12px;
    font-size: 1.15rem;
}
[x-cloak] {
    display: none !important;
}
</style>
@endpush

@section('content')
<div class="container py-4 px-3 px-md-4"
     style="max-width: 1140px;"
     x-data="familyCommitmentsApp()"
     x-init="init()"
     x-cloak>

    <!-- Hero Header -->
    <div class="commitments-hero mb-4 shadow-sm">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                    <span class="commitment-badge" style="background: rgba(251, 191, 36, 0.22); color: #fef08a;">
                        <i class="bi bi-calendar2-heart-fill"></i> Family Commitments
                    </span>
                    <span class="commitment-badge" style="background: rgba(56, 189, 248, 0.2); color: #e0f2fe;">
                        <i class="bi bi-arrow-repeat"></i> Synced with Family Calendar
                    </span>
                </div>
                <h1 class="fw-bold mb-2" style="color: #ffffff; font-size: clamp(1.65rem, 2.5vw, 2.15rem); letter-spacing: -0.02em;">Family Commitments &amp; Targets</h1>
                <p class="mb-0" style="color: #e2e8f0; font-size: 1.0625rem; line-height: 1.6; max-width: 680px;">
                    Set intentional promises for the people who matter most — from calling Dad twice a week to family walks and date nights.
                </p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button"
                        class="btn btn-light fw-bold commitment-btn px-4 d-inline-flex align-items-center gap-2 shadow-sm"
                        style="border-radius: 14px; color: #0f172a; font-size: 1.05rem;"
                        @click="openCreateModal()">
                    <i class="bi bi-plus-circle-fill" style="color: #a31621; font-size: 1.2rem;"></i>
                    <span>New Commitment</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Exact-Time In-App Reminders Banner -->
    <template x-if="reminders.length > 0">
        <div class="mb-4 d-flex flex-column gap-3" aria-live="polite">
            <template x-for="rem in reminders" :key="rem.commitment_id + '_' + rem.slot_key">
                <div class="commitment-card d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3"
                     style="border-left: 5px solid #a31621;">
                    <div class="d-flex align-items-start gap-3">
                        <div class="commitment-icon-medallion"
                             style="background: rgba(163, 22, 33, 0.12); color: #a31621;">
                            <i class="bi bi-bell-fill"></i>
                        </div>
                        <div>
                            <div class="fw-bold" style="color: var(--text-color, #0f172a); font-size: 1.15rem;" x-text="'Reminder: ' + rem.title"></div>
                            <div style="color: var(--text-muted-aa, #334155); font-size: 1rem;" x-text="rem.target_statement || ('Scheduled for ' + (rem.preferred_time || 'now'))"></div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                        <button type="button"
                                class="btn fw-bold commitment-btn px-4 text-white"
                                style="background-color: #a31621; border-radius: 12px;"
                                @click="openCheckinById(rem.commitment_id, rem.slot_key)">
                            <i class="bi bi-check2-circle me-1"></i> Done
                        </button>
                        <button type="button"
                                class="btn btn-outline-secondary fw-semibold commitment-btn px-3"
                                style="border-radius: 12px;"
                                :disabled="submittingId === ('dismiss_' + rem.commitment_id)"
                                @click="dismissReminder(rem.commitment_id, rem.slot_key)">
                            <span x-show="submittingId !== ('dismiss_' + rem.commitment_id)">Dismiss</span>
                            <span x-show="submittingId === ('dismiss_' + rem.commitment_id)" class="spinner-border spinner-border-sm"></span>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </template>

    <!-- Error / Offline State -->
    <template x-if="errorMessage !== ''">
        <div class="alert alert-danger d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-4 p-3" role="alert" style="border-radius: 14px; font-size: 1rem;">
            <div class="fw-semibold">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <span x-text="errorMessage"></span>
            </div>
            <button type="button" class="btn btn-outline-light fw-bold commitment-btn px-3" style="color: #7f1d1d; border-color: #7f1d1d;" @click="refreshList()">
                Retry
            </button>
        </div>
    </template>

    <!-- Filter Pills -->
    <div class="d-flex align-items-center gap-2 overflow-x-auto pb-2 mb-4">
        <button type="button"
                class="btn commitment-btn px-4 fw-bold"
                :style="filterTab === 'all' ? 'background-color: #0f172a; color: #ffffff; border-radius: 999px;' : 'background-color: var(--card-bg, #ffffff); color: var(--text-color, #1e293b); border: 1px solid var(--border-color, #cbd5e1); border-radius: 999px;'"
                @click="filterTab = 'all'">
            All Commitments (<span x-text="commitments.length"></span>)
        </button>
        <button type="button"
                class="btn commitment-btn px-4 fw-semibold"
                :style="filterTab === 'fixed' ? 'background-color: #0f172a; color: #ffffff; border-radius: 999px;' : 'background-color: var(--card-bg, #ffffff); color: var(--text-color, #1e293b); border: 1px solid var(--border-color, #cbd5e1); border-radius: 999px;'"
                @click="filterTab = 'fixed'">
            Fixed Schedule
        </button>
        <button type="button"
                class="btn commitment-btn px-4 fw-semibold"
                :style="filterTab === 'flexible' ? 'background-color: #0f172a; color: #ffffff; border-radius: 999px;' : 'background-color: var(--card-bg, #ffffff); color: var(--text-color, #1e293b); border: 1px solid var(--border-color, #cbd5e1); border-radius: 999px;'"
                @click="filterTab = 'flexible'">
            Flexible Quotas
        </button>
        <button type="button"
                class="btn commitment-btn px-4 fw-semibold"
                :style="filterTab === 'shared' ? 'background-color: #0f172a; color: #ffffff; border-radius: 999px;' : 'background-color: var(--card-bg, #ffffff); color: var(--text-color, #1e293b); border: 1px solid var(--border-color, #cbd5e1); border-radius: 999px;'"
                @click="filterTab = 'shared'">
            Shared with Family
        </button>
    </div>

    <!-- Loading Skeleton -->
    <template x-if="isLoading">
        <div class="row g-4">
            <template x-for="n in [1, 2, 3]" :key="n">
                <div class="col-12 col-md-6">
                    <div class="commitment-card placeholder-glow">
                        <div class="placeholder col-7 rounded mb-3" style="height: 28px;"></div>
                        <div class="placeholder col-10 rounded mb-2" style="height: 18px;"></div>
                        <div class="placeholder col-5 rounded mb-4" style="height: 18px;"></div>
                        <div class="placeholder col-12 rounded" style="height: 48px;"></div>
                    </div>
                </div>
            </template>
        </div>
    </template>

    <!-- Empty State -->
    <template x-if="!isLoading && filteredCommitments.length === 0">
        <div class="commitment-card p-5 text-center">
            <div class="mx-auto mb-3 rounded-circle d-inline-flex align-items-center justify-content-center"
                 style="width: 80px; height: 80px; background: rgba(163, 22, 33, 0.1); color: #a31621;">
                <i class="bi bi-calendar2-heart fs-1"></i>
            </div>
            <h2 class="fw-bold mb-2" style="color: var(--text-color, #0f172a); font-size: 1.5rem;">No Family Commitments Yet</h2>
            <p class="mx-auto mb-4" style="max-width: 560px; color: var(--text-muted-aa, #334155); font-size: 1.0625rem; line-height: 1.6;">
                Create your first commitment or start from a quick template below. Commitments appear on your Family Calendar and trigger exact-time in-app reminders.
            </p>
            <div class="d-flex flex-wrap justify-content-center gap-2 mb-4">
                <button type="button" class="btn btn-outline-secondary fw-semibold commitment-btn px-4" style="border-radius: 12px;"
                        @click="openTemplate('Call Dad twice a week', 'Catch up with Dad on health and family news', 'fixed', 'week', 2, ['wed','sun'], '19:00', 'private')">
                    + Call Dad (Wed &amp; Sun 19:00)
                </button>
                <button type="button" class="btn btn-outline-secondary fw-semibold commitment-btn px-4" style="border-radius: 12px;"
                        @click="openTemplate('Go for a walk with the kids', 'Active outdoor time together without screens', 'flexible', 'week', 3, [], '', 'family')">
                    + Walk with the kids (3×/week)
                </button>
                <button type="button" class="btn btn-outline-secondary fw-semibold commitment-btn px-4" style="border-radius: 12px;"
                        @click="openTemplate('Dinner with wife', 'Dedicated date night together', 'fixed', 'week', 1, ['fri'], '20:00', 'specific')">
                    + Dinner with wife (Fri 20:00)
                </button>
            </div>
            <button type="button" class="btn fw-bold text-white commitment-btn px-4" style="background-color: #a31621; border-radius: 14px; font-size: 1.05rem;" @click="openCreateModal()">
                <i class="bi bi-plus-lg me-1"></i> Create Custom Commitment
            </button>
        </div>
    </template>

    <!-- Commitments Grid -->
    <div class="row g-4" x-show="!isLoading && filteredCommitments.length > 0">
        <template x-for="item in filteredCommitments" :key="item.id">
            <div class="col-12 col-md-6">
                <div class="commitment-card h-100 d-flex flex-column justify-content-between"
                     :class="{ 'is-period-met': item.period_met }">
                    <div>
                        <!-- Top Badges Row -->
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-3 flex-wrap">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="commitment-badge"
                                      style="background: rgba(37, 99, 235, 0.12); color: #1d4ed8;">
                                    <i class="bi" :class="item.schedule_mode === 'fixed' ? 'bi-calendar-event-fill' : 'bi-sliders'"></i>
                                    <span x-text="item.schedule_mode === 'fixed' ? 'Fixed Schedule' : 'Flexible Quota'"></span>
                                </span>
                                <span class="commitment-badge"
                                      style="background: rgba(15, 23, 42, 0.08); color: var(--text-color, #1e293b);">
                                    <i class="bi" :class="item.visibility === 'family' ? 'bi-people-fill' : (item.visibility === 'specific' ? 'bi-person-check-fill' : 'bi-lock-fill')"></i>
                                    <span x-text="visibilityLabel(item.visibility)"></span>
                                </span>
                            </div>
                            <template x-if="item.streak_periods > 0">
                                <span class="commitment-badge"
                                      style="background: rgba(245, 158, 11, 0.18); color: #92400e;">
                                    <i class="bi bi-fire"></i>
                                    <span x-text="item.streak_periods + ' ' + item.period_unit + (item.streak_periods > 1 ? 's' : '') + ' streak'"></span>
                                </span>
                            </template>
                        </div>

                        <!-- Medallion + Title & Target Statement -->
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div class="commitment-icon-medallion"
                                 :style="item.period_met ? 'background: rgba(5, 150, 105, 0.14); color: #047857;' : 'background: rgba(163, 22, 33, 0.12); color: #a31621;'">
                                <i class="bi" :class="item.period_met ? 'bi-check2-circle' : 'bi-calendar2-heart-fill'"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <h2 class="commitment-card-title mb-2"
                                    :title="item.title"
                                    x-text="item.title"></h2>
                                <p class="commitment-card-statement mb-0"
                                   x-text="item.target_statement || ('Goal: ' + item.target_count + '× per ' + item.period_unit)"></p>
                            </div>
                        </div>

                        <!-- Active Days Strip (Fixed Weekly) -->
                        <template x-if="item.schedule_mode === 'fixed' && Array.isArray(item.days_of_week) && item.days_of_week.length > 0">
                            <div class="d-flex align-items-center flex-wrap gap-2 mb-3">
                                <span class="fw-bold" style="font-size: 0.95rem; color: var(--text-muted-aa, #475569);">Schedule:</span>
                                <template x-for="dayKey in item.days_of_week" :key="dayKey">
                                    <span class="commitment-day-chip" x-text="formatDayLabel(dayKey)"></span>
                                </template>
                                <template x-if="item.preferred_time">
                                    <span class="fw-bold ms-1" style="font-size: 0.95rem; color: var(--text-color, #0f172a);"
                                          x-text="'at ' + item.preferred_time"></span>
                                </template>
                            </div>
                        </template>

                        <!-- Gentle Weekly/Monthly Progress Panel -->
                        <div class="commitment-progress-panel mb-3">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2 flex-wrap">
                                <span class="fw-bold" style="font-size: 1.02rem; color: var(--text-color, #0f172a);"
                                      x-text="'This ' + (item.period_unit === 'month' ? 'Month' : 'Week') + ' (' + item.current_period_key + ')'"></span>
                                <span class="commitment-quota-number"
                                      :style="item.period_met ? 'color: #047857;' : 'color: var(--text-color, #0f172a);'"
                                      x-text="item.completed_this_period + ' / ' + item.target_count + (item.period_met ? ' ✓ Target Met' : ' completed')"></span>
                            </div>
                            <div class="progress my-2" style="height: 12px; border-radius: 999px; background-color: #cbd5e1;">
                                <div class="progress-bar"
                                     role="progressbar"
                                     :style="'width: ' + Math.max(item.progress_pct, item.completed_this_period > 0 ? 12 : 4) + '%; background-color: ' + (item.period_met ? '#059669' : '#a31621') + '; border-radius: 999px; transition: width 0.3s ease;'"
                                     :aria-valuenow="item.progress_pct"
                                     aria-valuemin="0"
                                     aria-valuemax="100"></div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mt-2 flex-wrap gap-2 commitment-meta-row">
                                <span>
                                    <i class="bi bi-clock-fill me-1" style="color: #a31621;"></i>
                                    <span x-text="item.next_occurrence ? ('Next: ' + item.next_occurrence.label) : 'Flexible schedule'"></span>
                                </span>
                                <span x-show="item.in_app_reminder" style="color: var(--text-muted-aa, #334155);">
                                    <i class="bi bi-bell-fill me-1"></i>Reminder on
                                </span>
                            </div>
                        </div>

                        <!-- Latest Check-in Reflection Preview -->
                        <template x-if="item.recent_checkins && item.recent_checkins.length > 0">
                            <div class="commitment-checkin-box mb-3">
                                <div class="fw-bold d-flex align-items-center justify-content-between flex-wrap gap-1" style="color: #065f46; font-size: 1rem;">
                                    <span>
                                        <i class="bi bi-chat-quote-fill me-1"></i>
                                        Latest check-in by <span x-text="item.recent_checkins[0].completed_by_name"></span>
                                    </span>
                                    <template x-if="item.recent_checkins[0].rating">
                                        <span class="badge rounded-pill" style="background: rgba(245, 158, 11, 0.2); color: #92400e; font-size: 0.875rem;">
                                            <i class="bi bi-star-fill me-1"></i><span x-text="item.recent_checkins[0].rating + '/5'"></span>
                                        </span>
                                    </template>
                                </div>
                                <div class="mt-2 fw-medium" style="color: var(--text-color, #0f172a); font-size: 1.02rem;" x-text="item.recent_checkins[0].reflection_note"></div>
                            </div>
                        </template>
                    </div>

                    <!-- Card Actions -->
                    <div class="d-flex align-items-center justify-content-between gap-2 pt-3 border-top" style="border-color: var(--border-color, #cbd5e1) !important;">
                        <button type="button"
                                class="btn fw-bold text-white commitment-btn flex-grow-1"
                                style="background-color: #a31621; border-radius: 14px; font-size: 1.05rem;"
                                @click="openCheckinModal(item)">
                            <i class="bi bi-check2-circle me-1"></i> Log Check-in
                        </button>
                        <template x-if="item.is_owner">
                            <div class="d-flex align-items-center gap-2">
                                <button type="button"
                                        class="btn btn-outline-secondary commitment-btn px-3 fw-semibold"
                                        style="border-radius: 14px;"
                                        title="Edit commitment"
                                        aria-label="Edit commitment"
                                        @click="openEditModal(item)">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button type="button"
                                        class="btn btn-outline-danger commitment-btn px-3 fw-semibold"
                                        style="border-radius: 14px;"
                                        title="Delete commitment"
                                        aria-label="Delete commitment"
                                        :disabled="submittingId === ('del_' + item.id)"
                                        @click="deleteCommitment(item.id)">
                                    <i class="bi bi-trash3" x-show="submittingId !== ('del_' + item.id)"></i>
                                    <span x-show="submittingId === ('del_' + item.id)" class="spinner-border spinner-border-sm"></span>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Create / Edit Commitment Modal -->
    <div class="modal fade" id="commitmentFormModal" tabindex="-1" aria-labelledby="commitmentFormModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" x-trap.noscroll="showFormModal">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; background-color: var(--card-bg, #ffffff);">
                <form @submit.prevent="saveCommitment()">
                    @include('csrf')
                    <div class="modal-header border-0 pb-2 pt-4 px-4">
                        <h2 class="modal-title fw-bold" id="commitmentFormModalLabel" style="color: var(--text-color, #0f172a); font-size: 1.375rem;"
                            x-text="form.id ? 'Edit Family Commitment' : 'New Family Commitment'"></h2>
                        <button type="button" class="btn-close commitment-btn" data-bs-dismiss="modal" aria-label="Close" @click="showFormModal = false"></button>
                    </div>
                    <div class="modal-body px-4 py-3">
                        <template x-if="errorMessage !== ''">
                            <div class="alert alert-danger fw-semibold mb-3" role="alert" style="border-radius: 12px; font-size: 1rem;">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <span x-text="errorMessage"></span>
                            </div>
                        </template>

                        <div class="mb-3">
                            <label class="form-label fw-bold" style="color: var(--text-color, #0f172a); font-size: 1.02rem;">Commitment Title</label>
                            <input type="text" class="form-control" maxlength="120" required
                                   placeholder="e.g. Call Dad twice a week"
                                   x-model="form.title"
                                   style="min-height: 48px; border-radius: 12px; font-size: 1.02rem;">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold" style="color: var(--text-color, #0f172a); font-size: 1.02rem;">Stated Target / Intention (Optional)</label>
                            <textarea class="form-control" rows="2" maxlength="500"
                                      placeholder="e.g. Catch up on health, share family updates, and check if he needs anything"
                                      x-model="form.target_statement"
                                      style="border-radius: 12px; font-size: 1.02rem;"></textarea>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold" style="color: var(--text-color, #0f172a); font-size: 1.02rem;">Schedule Mode</label>
                                <select class="form-select" x-model="form.schedule_mode" style="min-height: 48px; border-radius: 12px; font-size: 1.02rem;">
                                    <option value="fixed">Fixed Day &amp; Time</option>
                                    <option value="flexible">Flexible Quota</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label fw-bold" style="color: var(--text-color, #0f172a); font-size: 1.02rem;">Period</label>
                                <select class="form-select" x-model="form.period_unit" style="min-height: 48px; border-radius: 12px; font-size: 1.02rem;">
                                    <option value="week">Per Week</option>
                                    <option value="month">Per Month</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label fw-bold" style="color: var(--text-color, #0f172a); font-size: 1.02rem;">Target Count</label>
                                <input type="number" class="form-control" min="1" max="31" required
                                       x-model.number="form.target_count"
                                       style="min-height: 48px; border-radius: 12px; font-size: 1.02rem;">
                            </div>
                        </div>

                        <!-- Fixed Weekly Days -->
                        <div class="mb-3" x-show="form.schedule_mode === 'fixed' && form.period_unit === 'week'" x-collapse>
                            <label class="form-label fw-bold d-block" style="color: var(--text-color, #0f172a); font-size: 1.02rem;">Select Days of Week</label>
                            <div class="d-flex flex-wrap gap-2">
                                <template x-for="d in weekDays" :key="d.key">
                                    <button type="button"
                                            class="btn day-pill-btn"
                                            :style="form.days_of_week.includes(d.key) ? 'background-color: #a31621; color: #ffffff; border: 1px solid #a31621;' : 'background-color: var(--card-bg, #ffffff); color: var(--text-color, #1e293b); border: 1px solid #cbd5e1;'"
                                            @click="toggleDay(d.key)"
                                            x-text="d.label"></button>
                                </template>
                            </div>
                        </div>

                        <!-- Fixed Monthly Day -->
                        <div class="mb-3" x-show="form.schedule_mode === 'fixed' && form.period_unit === 'month'" x-collapse>
                            <label class="form-label fw-bold" style="color: var(--text-color, #0f172a); font-size: 1.02rem;">Day of Month (1–31)</label>
                            <input type="number" class="form-control" min="1" max="31" x-model.number="form.month_day" style="min-height: 48px; border-radius: 12px; font-size: 1.02rem;">
                        </div>

                        <!-- Time & Reminder -->
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold" style="color: var(--text-color, #0f172a); font-size: 1.02rem;">
                                    <span x-text="form.schedule_mode === 'fixed' ? 'Reminder & Calendar Time' : 'Preferred Time (Optional)'"></span>
                                </label>
                                <input type="time" class="form-control" x-model="form.preferred_time"
                                       :required="form.schedule_mode === 'fixed'"
                                       style="min-height: 48px; border-radius: 12px; font-size: 1.02rem;">
                            </div>
                            <div class="col-12 col-md-6 d-flex align-items-end">
                                <div class="form-check py-2">
                                    <input class="form-check-input" type="checkbox" id="inAppReminderToggle" x-model="form.in_app_reminder" style="width: 22px; height: 22px;">
                                    <label class="form-check-label fw-bold ms-2" for="inAppReminderToggle" style="color: var(--text-color, #0f172a); font-size: 1rem;">
                                        Trigger exact-time in-app reminder
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Tiered Visibility -->
                        <div class="mb-3">
                            <label class="form-label fw-bold" style="color: var(--text-color, #0f172a); font-size: 1.02rem;">Calendar &amp; Sharing Visibility</label>
                            <select class="form-select" x-model="form.visibility" style="min-height: 48px; border-radius: 12px; font-size: 1.02rem;">
                                <option value="private">Private to me (only on my calendar)</option>
                                <option value="specific">Specific People (shared with selected family members)</option>
                                <option value="family">Whole Family (visible on shared Family Calendar)</option>
                            </select>
                        </div>

                        <!-- Participant Picker -->
                        <div class="mb-2" x-show="form.visibility === 'specific' && familyMembers.length > 0" x-collapse>
                            <label class="form-label fw-bold" style="color: var(--text-color, #0f172a); font-size: 1.02rem;">Select Participants</label>
                            <div class="d-flex flex-wrap gap-2">
                                <template x-for="m in familyMembers" :key="m.id">
                                    <button type="button"
                                            class="btn commitment-btn px-3 fw-semibold"
                                            :style="form.participant_ids.includes(m.id) ? 'background-color: #0f172a; color: #ffffff; border-radius: 10px;' : 'background-color: var(--card-bg, #ffffff); color: var(--text-color, #1e293b); border: 1px solid #cbd5e1; border-radius: 10px;'"
                                            @click="toggleParticipant(m.id)"
                                            x-text="m.name"></button>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4 pt-2 gap-2">
                        <button type="button" class="btn btn-outline-secondary fw-semibold commitment-btn px-4" data-bs-dismiss="modal" style="border-radius: 12px;" @click="showFormModal = false">Cancel</button>
                        <button type="submit" class="btn fw-bold text-white commitment-btn px-4" style="background-color: #a31621; border-radius: 12px;" :disabled="isSubmitting">
                            <span x-show="!isSubmitting" x-text="form.id ? 'Save Changes' : 'Create Commitment'"></span>
                            <span x-show="isSubmitting"><span class="spinner-border spinner-border-sm me-1"></span> Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Rich Check-In Modal -->
    <div class="modal fade" id="commitmentCheckinModal" tabindex="-1" aria-labelledby="commitmentCheckinModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" x-trap.noscroll="showCheckinModal">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; background-color: var(--card-bg, #ffffff);">
                <form @submit.prevent="submitCheckin()">
                    @include('csrf')
                    <div class="modal-header border-0 pb-2 pt-4 px-4">
                        <div>
                            <h2 class="modal-title fw-bold" id="commitmentCheckinModalLabel" style="color: var(--text-color, #0f172a); font-size: 1.375rem;">Log Commitment Check-in</h2>
                            <p class="mb-0 fw-semibold mt-1" style="color: var(--text-muted-aa, #334155); font-size: 1.02rem;" x-text="checkinForm.title"></p>
                        </div>
                        <button type="button" class="btn-close commitment-btn" data-bs-dismiss="modal" aria-label="Close" @click="showCheckinModal = false"></button>
                    </div>
                    <div class="modal-body px-4 py-3">
                        <template x-if="errorMessage !== ''">
                            <div class="alert alert-danger fw-semibold mb-3" role="alert" style="border-radius: 12px; font-size: 1rem;">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <span x-text="errorMessage"></span>
                            </div>
                        </template>

                        <div class="mb-3">
                            <label class="form-label fw-bold d-block" style="color: var(--text-color, #0f172a); font-size: 1.02rem;">How did it go? (Optional rating)</label>
                            <div class="d-flex align-items-center gap-2">
                                <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                                    <button type="button"
                                            class="btn rating-star-btn"
                                            :style="checkinForm.rating >= star ? 'background-color: #f59e0b; color: #0f172a; border: 1px solid #f59e0b;' : 'background-color: var(--card-bg, #ffffff); color: var(--text-muted, #64748b); border: 1px solid #cbd5e1;'"
                                            @click="checkinForm.rating = star">
                                        <i class="bi bi-star-fill"></i>
                                    </button>
                                </template>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-bold" style="color: var(--text-color, #0f172a); font-size: 1.02rem;">Short Reflection Note (Required)</label>
                            <textarea class="form-control" rows="3" minlength="2" maxlength="1000" required
                                      placeholder="e.g. Had a great 20-minute chat with Dad — he's looking forward to Sunday!"
                                      x-model="checkinForm.reflection_note"
                                      style="border-radius: 12px; font-size: 1.02rem;"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4 pt-2 gap-2">
                        <button type="button" class="btn btn-outline-secondary fw-semibold commitment-btn px-4" data-bs-dismiss="modal" style="border-radius: 12px;" @click="showCheckinModal = false">Cancel</button>
                        <button type="submit" class="btn fw-bold text-white commitment-btn px-4" style="background-color: #059669; border-radius: 12px;" :disabled="isSubmitting">
                            <span x-show="!isSubmitting"><i class="bi bi-check2-circle me-1"></i> Complete Check-in</span>
                            <span x-show="isSubmitting"><span class="spinner-border spinner-border-sm me-1"></span> Logging...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script nonce="{{ csp_nonce($nonce ?? null) }}">
window.familyCommitmentsApp = function familyCommitmentsApp() {
    return {
        commitments: @json($commitments ?? []),
        reminders: @json($dueReminders ?? []),
        familyMembers: @json($familyMembers ?? []),
        csrfToken: @json($csrfToken ?? ''),
        filterTab: 'all',
        isLoading: false,
        isSubmitting: false,
        submittingId: '',
        errorMessage: '',
        showFormModal: false,
        showCheckinModal: false,
        weekDays: [
            { key: 'mon', label: 'Mon' },
            { key: 'tue', label: 'Tue' },
            { key: 'wed', label: 'Wed' },
            { key: 'thu', label: 'Thu' },
            { key: 'fri', label: 'Fri' },
            { key: 'sat', label: 'Sat' },
            { key: 'sun', label: 'Sun' }
        ],
        form: {
            id: '',
            title: '',
            target_statement: '',
            schedule_mode: 'fixed',
            period_unit: 'week',
            target_count: 1,
            days_of_week: ['wed', 'sun'],
            month_day: 1,
            preferred_time: '19:00',
            visibility: 'private',
            participant_ids: [],
            in_app_reminder: true
        },
        checkinForm: {
            commitment_id: '',
            title: '',
            slot_key: '',
            rating: 5,
            reflection_note: ''
        },
        init() {
            const params = new URLSearchParams(window.location.search);
            const checkinId = params.get('checkin');
            const slot = params.get('slot') || '';
            if (checkinId) {
                this.openCheckinById(checkinId, slot);
            }
        },
        get filteredCommitments() {
            if (this.filterTab === 'fixed') {
                return this.commitments.filter(c => c.schedule_mode === 'fixed');
            }
            if (this.filterTab === 'flexible') {
                return this.commitments.filter(c => c.schedule_mode === 'flexible');
            }
            if (this.filterTab === 'shared') {
                return this.commitments.filter(c => c.visibility !== 'private');
            }
            return this.commitments;
        },
        visibilityLabel(vis) {
            if (vis === 'family') return 'Whole Family';
            if (vis === 'specific') return 'Shared';
            return 'Private';
        },
        formatDayLabel(dayKey) {
            const found = this.weekDays.find(d => d.key === String(dayKey).toLowerCase());
            return found ? found.label : String(dayKey).toUpperCase();
        },
        openCreateModal() {
            this.errorMessage = '';
            this.form = {
                id: '',
                title: '',
                target_statement: '',
                schedule_mode: 'fixed',
                period_unit: 'week',
                target_count: 1,
                days_of_week: ['wed'],
                month_day: 1,
                preferred_time: '19:00',
                visibility: 'private',
                participant_ids: [],
                in_app_reminder: true
            };
            this.showFormModal = true;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('commitmentFormModal')).show();
        },
        openTemplate(title, statement, mode, unit, count, days, time, vis) {
            this.errorMessage = '';
            this.form = {
                id: '',
                title: title,
                target_statement: statement,
                schedule_mode: mode,
                period_unit: unit,
                target_count: count,
                days_of_week: days,
                month_day: 1,
                preferred_time: time,
                visibility: vis,
                participant_ids: [],
                in_app_reminder: true
            };
            this.showFormModal = true;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('commitmentFormModal')).show();
        },
        openEditModal(item) {
            this.errorMessage = '';
            this.form = {
                id: item.id,
                title: item.title,
                target_statement: item.target_statement || '',
                schedule_mode: item.schedule_mode,
                period_unit: item.period_unit,
                target_count: item.target_count,
                days_of_week: Array.isArray(item.days_of_week) ? [...item.days_of_week] : [],
                month_day: item.month_day || 1,
                preferred_time: item.preferred_time || '19:00',
                visibility: item.visibility || 'private',
                participant_ids: Array.isArray(item.participants) ? item.participants.filter(p => p.role !== 'owner').map(p => p.user_id) : [],
                in_app_reminder: !!item.in_app_reminder
            };
            this.showFormModal = true;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('commitmentFormModal')).show();
        },
        toggleDay(dayKey) {
            if (this.form.days_of_week.includes(dayKey)) {
                this.form.days_of_week = this.form.days_of_week.filter(d => d !== dayKey);
            } else {
                this.form.days_of_week.push(dayKey);
            }
            if (this.form.schedule_mode === 'fixed' && this.form.period_unit === 'week') {
                this.form.target_count = Math.max(1, this.form.days_of_week.length);
            }
        },
        toggleParticipant(uid) {
            if (this.form.participant_ids.includes(uid)) {
                this.form.participant_ids = this.form.participant_ids.filter(id => id !== uid);
            } else {
                this.form.participant_ids.push(uid);
            }
        },
        openCheckinModal(item, slotKey = '') {
            this.errorMessage = '';
            this.checkinForm = {
                commitment_id: item.id,
                title: item.title,
                slot_key: slotKey || (item.next_occurrence ? item.next_occurrence.slot_key : ''),
                rating: 5,
                reflection_note: ''
            };
            this.showCheckinModal = true;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('commitmentCheckinModal')).show();
        },
        openCheckinById(commitmentId, slotKey = '') {
            const found = this.commitments.find(c => c.id === commitmentId);
            if (found) {
                this.openCheckinModal(found, slotKey);
            }
        },
        async refreshList() {
            this.isLoading = true;
            this.errorMessage = '';
            try {
                const res = await fetch('/api/commitments', { headers: { 'Accept': 'application/json' } });
                const body = await res.json();
                const payload = body.data || body;
                if (!res.ok) throw new Error(body.error || body.message || 'Unable to load commitments.');
                this.commitments = payload.commitments || [];
                this.reminders = payload.reminders || [];
            } catch (e) {
                this.errorMessage = e.message || 'Unable to load commitments.';
            } finally {
                this.isLoading = false;
            }
        },
        async saveCommitment() {
            this.isSubmitting = true;
            this.errorMessage = '';
            const isEdit = !!this.form.id;
            const url = isEdit ? `/api/commitments/${encodeURIComponent(this.form.id)}/update` : '/api/commitments';
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                    body: JSON.stringify({ ...this.form, csrf_token: this.csrfToken })
                });
                const body = await res.json();
                if (!res.ok) throw new Error(body.error || body.message || 'Unable to save commitment.');
                bootstrap.Modal.getOrCreateInstance(document.getElementById('commitmentFormModal')).hide();
                this.showFormModal = false;
                await this.refreshList();
                if (typeof this.$toast === 'function') this.$toast(isEdit ? 'Commitment updated' : 'Commitment created', 'success');
            } catch (e) {
                this.errorMessage = e.message || 'Unable to save commitment.';
            } finally {
                this.isSubmitting = false;
            }
        },
        async deleteCommitment(id) {
            this.submittingId = 'del_' + id;
            this.errorMessage = '';
            try {
                const res = await fetch(`/api/commitments/${encodeURIComponent(id)}/delete`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                    body: JSON.stringify({ csrf_token: this.csrfToken })
                });
                const body = await res.json();
                if (!res.ok) throw new Error(body.error || body.message || 'Unable to remove commitment.');
                await this.refreshList();
                if (typeof this.$toast === 'function') this.$toast('Commitment removed', 'info');
            } catch (e) {
                this.errorMessage = e.message || 'Unable to remove commitment.';
            } finally {
                this.submittingId = '';
            }
        },
        async submitCheckin() {
            this.isSubmitting = true;
            this.errorMessage = '';
            try {
                const res = await fetch(`/api/commitments/${encodeURIComponent(this.checkinForm.commitment_id)}/checkin`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                    body: JSON.stringify({
                        slot_key: this.checkinForm.slot_key,
                        rating: this.checkinForm.rating,
                        reflection_note: this.checkinForm.reflection_note,
                        csrf_token: this.csrfToken
                    })
                });
                const body = await res.json();
                if (!res.ok) throw new Error(body.error || body.message || 'Unable to log check-in.');
                bootstrap.Modal.getOrCreateInstance(document.getElementById('commitmentCheckinModal')).hide();
                this.showCheckinModal = false;
                await this.refreshList();
                if (typeof this.$toast === 'function') this.$toast('Check-in logged. Well done!', 'success');
            } catch (e) {
                this.errorMessage = e.message || 'Unable to log check-in.';
            } finally {
                this.isSubmitting = false;
            }
        },
        async dismissReminder(commitmentId, slotKey) {
            this.submittingId = 'dismiss_' + commitmentId;
            try {
                const res = await fetch(`/api/commitments/${encodeURIComponent(commitmentId)}/dismiss-reminder`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                    body: JSON.stringify({ slot_key: slotKey, csrf_token: this.csrfToken })
                });
                if (res.ok) {
                    this.reminders = this.reminders.filter(r => !(r.commitment_id === commitmentId && r.slot_key === slotKey));
                }
            } finally {
                this.submittingId = '';
            }
        }
    };
};
</script>
@endpush
