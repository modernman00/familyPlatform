@extends ('admin.base')

@section('title', 'GDPR Data Erasure & Deletion Requests')

@section('content-title', 'GDPR Article 17 — Account Erasure & Deletion Requests')

@section('content')
<div class="box">
    <div class="level">
        <div class="level-left">
            <div>
                <h4 class="title is-4 mb-2">GDPR Article 17 Erasure Register</h4>
                <p class="subtitle is-6 text-muted">
                    Monitor accounts scheduled for 30-day deletion (Option B) and permanently erased profiles (Option A).
                </p>
            </div>
        </div>
        <div class="level-right">
            <span class="tag is-info is-medium">Total Records: {{ count($erasedAccounts) }}</span>
        </div>
    </div>

    <div class="table-container mt-4">
        <table class="table is-bordered is-striped is-narrow is-hoverable is-fullwidth">
            <thead>
                <tr class="has-background-primary-dark">
                    <th class="has-text-white">Account ID</th>
                    <th class="has-text-white">Email Address</th>
                    <th class="has-text-white">Current Status</th>
                    <th class="has-text-white">Registered At</th>
                    <th class="has-text-white">Actioned / Scheduled At</th>
                    <th class="has-text-white has-text-centered">Operational Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($erasedAccounts as $account)
                    <tr>
                        <td class="is-family-monospace font-weight-bold">{{ $account['id'] }}</td>
                        <td>{{ $account['email'] }}</td>
                        <td>
                            @if (($account['status'] ?? '') === 'pending_deletion')
                                <span class="tag is-warning is-light font-weight-bold">
                                    <i class="fas fa-clock mr-1"></i> Pending Deletion (30-Day Grace)
                                </span>
                            @elseif (($account['status'] ?? '') === 'erased')
                                <span class="tag is-danger is-light font-weight-bold">
                                    <i class="fas fa-trash-alt mr-1"></i> Erased & Anonymised
                                </span>
                            @else
                                <span class="tag is-light">{{ $account['status'] ?? '—' }}</span>
                            @endif
                        </td>
                        <td>{{ !empty($account['created_at']) ? date('d M Y H:i', strtotime($account['created_at'])) : '—' }}</td>
                        <td>{{ !empty($account['deleted_at']) ? date('d M Y H:i', strtotime($account['deleted_at'])) : '—' }}</td>
                        <td class="has-text-centered">
                            @if (($account['status'] ?? '') === 'pending_deletion')
                                <form method="POST" action="/admin/erasure/process" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently erase this account immediately? This cannot be undone.');">
                                    @include('csrf')
                                    <input type="hidden" name="target_user_id" value="{{ $account['id'] }}">
                                    <button type="submit" class="button is-danger is-small is-rounded btn-spin-on-click" data-spin="true">
                                        <i class="fas fa-bolt mr-1"></i> Erase Permanently Now
                                    </button>
                                </form>
                            @else
                                <span class="text-muted is-size-7"><i class="fas fa-check-circle text-success mr-1"></i> Pipeline Complete</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="has-text-centered py-5 text-muted">
                            <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                            <p>No pending deletion requests or active erasure records found.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
