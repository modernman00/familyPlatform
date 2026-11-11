@extends('admin.layout')

@section('content')
<div x-data="adminModerationConsole()" class="space-y-6">
    <!-- Header Summary & Badges -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-800 pb-6">
        <div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
                <i class="fa-solid fa-shield-halved text-rose-500"></i>
                <span>Content Safety &amp; Community Moderation Console</span>
                <span class="text-xs uppercase font-bold px-2.5 py-1 rounded-full bg-rose-500/20 text-rose-300 border border-rose-500/30">
                    Super Admin Sovereign Gate
                </span>
            </h1>
            <p class="text-sm text-gray-400 mt-1">Review flagged community reports, delete abusive posts/comments, and dispatch formal warnings to offenders.</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-2 bg-rose-600/20 border border-rose-500/30 text-rose-300 font-semibold text-xs px-3.5 py-2 rounded-xl">
                <i class="fa-solid fa-triangle-exclamation text-rose-400"></i>
                <span>Pending Action: <strong>{{ (int)($counts['pending_count'] ?? 0) }}</strong></span>
            </span>
            <span class="inline-flex items-center gap-2 bg-emerald-600/20 border border-emerald-500/30 text-emerald-300 font-semibold text-xs px-3.5 py-2 rounded-xl">
                <i class="fa-solid fa-check text-emerald-400"></i>
                <span>Actioned: <strong>{{ (int)($counts['actioned_count'] ?? 0) }}</strong></span>
            </span>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="flex items-center gap-2 border-b border-gray-800 pb-2 overflow-x-auto">
        <a href="{{ $adminPrefix }}/moderation?status=pending" class="px-4 py-2 rounded-lg text-xs font-semibold whitespace-nowrap transition-colors {{ $statusFilter === 'pending' ? 'bg-rose-600 text-white shadow' : 'text-gray-400 hover:text-white hover:bg-gray-800/60' }}">
            Pending Reports ({{ (int)($counts['pending_count'] ?? 0) }})
        </a>
        <a href="{{ $adminPrefix }}/moderation?status=actioned" class="px-4 py-2 rounded-lg text-xs font-semibold whitespace-nowrap transition-colors {{ $statusFilter === 'actioned' ? 'bg-emerald-600 text-white shadow' : 'text-gray-400 hover:text-white hover:bg-gray-800/60' }}">
            Actioned (Deleted / Warned) ({{ (int)($counts['actioned_count'] ?? 0) }})
        </a>
        <a href="{{ $adminPrefix }}/moderation?status=dismissed" class="px-4 py-2 rounded-lg text-xs font-semibold whitespace-nowrap transition-colors {{ $statusFilter === 'dismissed' ? 'bg-gray-700 text-white shadow' : 'text-gray-400 hover:text-white hover:bg-gray-800/60' }}">
            Dismissed ({{ (int)($counts['dismissed_count'] ?? 0) }})
        </a>
        <a href="{{ $adminPrefix }}/moderation?status=all" class="px-4 py-2 rounded-lg text-xs font-semibold whitespace-nowrap transition-colors {{ $statusFilter === 'all' ? 'bg-gray-600 text-white shadow' : 'text-gray-400 hover:text-white hover:bg-gray-800/60' }}">
            All Reports ({{ (int)($counts['total_count'] ?? 0) }})
        </a>
    </div>

    <!-- Reports Feed -->
    @if(empty($reports))
        <div class="bg-gray-900/60 border border-gray-800 rounded-2xl p-12 text-center">
            <div class="w-16 h-16 mx-auto bg-gray-800 rounded-full flex items-center justify-center text-gray-400 text-2xl mb-4">
                <i class="fa-solid fa-check-double text-emerald-400"></i>
            </div>
            <h3 class="text-lg font-bold text-white mb-1">No reports in this category</h3>
            <p class="text-xs text-gray-400 max-w-md mx-auto">All community safety queues are clear. FamilyPlatform feeds remain respectful, peaceful, and family-friendly.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($reports as $rep)
                @php
                    $isPending = ($rep['status'] ?? '') === 'pending';
                    $reasonLabels = [
                        'hate_speech' => 'Hate Speech / Slurs',
                        'harassment' => 'Harassment / Bullying',
                        'inappropriate' => 'Inappropriate Content',
                        'spam' => 'Spam / Misleading',
                        'other' => 'Other Concern',
                    ];
                    $reasonText = $reasonLabels[$rep['reason'] ?? ''] ?? ucfirst($rep['reason'] ?? 'Other');
                @endphp
                <div class="bg-gray-900/80 border {{ $isPending ? 'border-rose-900/60 shadow-lg shadow-rose-950/20' : 'border-gray-800' }} rounded-2xl p-5 sm:p-6 transition-all" id="report-card-{{ $rep['id'] }}">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-800/80 pb-4 mb-4">
                        <div class="flex items-center gap-3">
                            <span class="px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wide {{ $rep['target_type'] === 'post' ? 'bg-blue-600/20 text-blue-300 border border-blue-500/30' : 'bg-purple-600/20 text-purple-300 border border-purple-500/30' }}">
                                Flagged {{ ucfirst($rep['target_type']) }} #{{ $rep['target_id'] }}
                            </span>
                            <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-rose-600/20 text-rose-300 border border-rose-500/30">
                                {{ $reasonText }}
                            </span>
                            @if(!$isPending)
                                <span class="px-2.5 py-1 rounded-md text-xs font-bold {{ $rep['status'] === 'actioned' ? 'bg-emerald-600/20 text-emerald-300 border border-emerald-500/30' : 'bg-gray-700 text-gray-300' }}">
                                    Status: {{ strtoupper($rep['status']) }} ({{ $rep['action_taken'] ?? 'resolved' }})
                                </span>
                            @endif
                        </div>

                        <div class="text-xs text-gray-400">
                            <span>Family Code: <strong class="text-gray-200">{{ $rep['fam_code'] }}</strong></span>
                            <span class="mx-2">•</span>
                            <span>Reported {{ date('d M Y, H:i', strtotime($rep['created_at'])) }}</span>
                        </div>
                    </div>

                    <!-- Offending Content Box -->
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Flagged Message Content</label>
                        <div class="bg-black/60 border border-gray-800 rounded-xl p-4 text-sm text-gray-200 font-mono whitespace-pre-wrap leading-relaxed select-all">
                            {{ $rep['target_content'] }}
                        </div>
                    </div>

                    <!-- Reporter & Author Metadata -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-gray-950/50 border border-gray-800/60 rounded-xl p-3.5 mb-5 text-xs">
                        <div>
                            <span class="text-gray-400">Reported by:</span>
                            <strong class="text-gray-200 ml-1">
                                {{ trim(($rep['reporter_first_name'] ?? '') . ' ' . ($rep['reporter_last_name'] ?? '')) ?: 'Family Member' }}
                            </strong>
                            <span class="text-gray-500 text-[11px] block">User ID: {{ $rep['reporter_id'] }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400">Offending Author:</span>
                            <strong class="text-rose-400 ml-1">
                                {{ trim(($rep['author_first_name'] ?? '') . ' ' . ($rep['author_last_name'] ?? '')) ?: 'Author' }}
                            </strong>
                            <span class="text-gray-500 text-[11px] block">User ID: {{ $rep['target_author_id'] }}</span>
                        </div>
                        @if(!empty($rep['details']))
                            <div class="sm:col-span-2 pt-2 border-t border-gray-800/60 text-gray-300">
                                <span class="text-gray-400 font-semibold">Reporter's Note:</span> {{ $rep['details'] }}
                            </div>
                        @endif
                    </div>

                    <!-- Action Controls (Super Admin Actions) -->
                    @if($isPending)
                        <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <button type="button" 
                                        @click="executeAction({{ $rep['id'] }}, 'delete_and_warn')" 
                                        class="inline-flex items-center gap-2 bg-rose-600 hover:bg-rose-500 text-white font-semibold text-xs px-4 py-2.5 rounded-xl shadow-lg shadow-rose-900/30 transition-all hover:scale-[1.02]">
                                    <i class="fa-solid fa-trash-can"></i>
                                    <span>Delete Content &amp; Warn User</span>
                                </button>

                                <button type="button" 
                                        @click="executeAction({{ $rep['id'] }}, 'delete_only')" 
                                        class="inline-flex items-center gap-2 bg-gray-800 hover:bg-gray-700 text-rose-300 border border-rose-900/40 font-semibold text-xs px-3.5 py-2.5 rounded-xl transition-all">
                                    <i class="fa-solid fa-trash-can text-rose-400"></i>
                                    <span>Delete Only</span>
                                </button>

                                <button type="button" 
                                        @click="executeAction({{ $rep['id'] }}, 'warn_only')" 
                                        class="inline-flex items-center gap-2 bg-amber-600/20 hover:bg-amber-600/30 text-amber-300 border border-amber-600/30 font-semibold text-xs px-3.5 py-2.5 rounded-xl transition-all">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    <span>Warn User Only</span>
                                </button>
                            </div>

                            <button type="button" 
                                    @click="executeAction({{ $rep['id'] }}, 'dismiss')" 
                                    class="inline-flex items-center gap-2 text-gray-400 hover:text-gray-200 text-xs px-3 py-2 rounded-lg hover:bg-gray-800 transition-colors">
                                <i class="fa-solid fa-xmark"></i>
                                <span>Dismiss Report (False Alarm)</span>
                            </button>
                        </div>
                    @else
                        <div class="text-xs text-gray-500 flex items-center justify-between pt-1">
                            <span>Reviewed by Admin #{{ $rep['reviewed_by'] ?? '1' }} on {{ date('d M Y, H:i', strtotime($rep['reviewed_at'] ?? 'now')) }}</span>
                            @if(!empty($rep['admin_note']))
                                <span class="italic text-gray-400">"{{ $rep['admin_note'] }}"</span>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($totalPages > 1)
            <div class="flex items-center justify-center gap-2 pt-6">
                @for($p = 1; $p <= $totalPages; $p++)
                    <a href="{{ $adminPrefix }}/moderation?status={{ $statusFilter }}&page={{ $p }}" 
                       class="px-3.5 py-2 rounded-lg text-xs font-bold transition-colors {{ $p === $currentPage ? 'bg-rose-600 text-white' : 'bg-gray-800 text-gray-300 hover:bg-gray-700' }}">
                        {{ $p }}
                    </a>
                @endfor
            </div>
        @endif
    @endif
</div>

<script nonce="{{ csp_nonce($nonce ?? null) }}">
function adminModerationConsole() {
    return {
        async executeAction(reportId, action) {
            let actionTitles = {
                'delete_and_warn': 'Delete Content & Warn User?',
                'delete_only': 'Delete Content from Platform?',
                'warn_only': 'Send Safety Warning to Offending User?',
                'dismiss': 'Dismiss this Report?'
            };
            let actionTexts = {
                'delete_and_warn': 'This will immediately soft-delete the flagged content from all family feeds and dispatch an official community safety warning notice to the author.',
                'delete_only': 'This will remove the content from the family feed without issuing an official community safety warning.',
                'warn_only': 'This will issue a formal warning to the author explaining community safety standards without deleting the post.',
                'dismiss': 'This will mark the report as dismissed without altering any content.'
            };

            const confirmed = await Swal.fire({
                title: actionTitles[action] || 'Confirm Moderation Action',
                text: actionTexts[action] || 'Proceed with this moderation action?',
                icon: action === 'dismiss' ? 'info' : 'warning',
                showCancelButton: true,
                confirmButtonColor: action === 'dismiss' ? '#4b5563' : '#e11d48',
                cancelButtonColor: '#1f2937',
                confirmButtonText: 'Yes, Proceed',
                cancelButtonText: 'Cancel'
            });

            if (!confirmed.isConfirmed) {
                return;
            }

            try {
                const response = await fetch('{{ $adminPrefix }}/moderation/action', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        report_id: reportId,
                        action: action
                    })
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.message || 'Action completed successfully.',
                        showConfirmButton: false,
                        timer: 2500
                    });

                    // Remove or update the report card visually
                    const card = document.getElementById('report-card-' + reportId);
                    if (card) {
                        card.style.opacity = '0.4';
                        card.style.pointerEvents = 'none';
                        setTimeout(() => window.location.reload(), 1200);
                    }
                } else {
                    Swal.fire('Error', data.error || 'Failed to execute moderation action.', 'error');
                }
            } catch (err) {
                console.error('[adminModerationConsole]', err);
                Swal.fire('Error', 'Network or server error while executing moderation action.', 'error');
            }
        }
    };
}
</script>
@endsection
