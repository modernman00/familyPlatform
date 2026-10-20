@extends('admin.layout')

@section('content')
<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-2xl lg:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
            <i class="fa-solid fa-users text-indigo-400"></i>
            <span>Universal User Registry & Access Control</span>
        </h1>
        <p class="text-sm text-gray-400 mt-1">Audit, search, and manage registered user accounts, security roles, and platform statuses.</p>
    </div>

    <!-- Search Form -->
    <form method="GET" action="/admin/users" class="flex items-center gap-2">
        <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-xs text-gray-400"></i>
            <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Search by email..." class="bg-gray-900 border border-gray-700 text-xs text-white rounded-xl pl-9 pr-4 py-2 focus:outline-none focus:border-indigo-500 w-64">
        </div>
        <button type="submit" class="bg-gray-800 hover:bg-gray-700 text-gray-200 text-xs font-semibold px-4 py-2 rounded-xl border border-gray-700 transition-colors">
            Search
        </button>
    </form>
</div>

<!-- Users Table -->
<div class="glass-panel p-6 rounded-2xl border border-gray-800">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-base font-bold text-white">Registered Accounts (<?= number_format($totalUsers ?? 0) ?> total)</h2>
            <p class="text-xs text-gray-400">Manage account status and administrative privileges</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="border-b border-gray-800 text-gray-400">
                    <th class="pb-3 font-semibold">User ID</th>
                    <th class="pb-3 font-semibold">Email & Identity</th>
                    <th class="pb-3 font-semibold">Role</th>
                    <th class="pb-3 font-semibold">Status</th>
                    <th class="pb-3 font-semibold">Joined At</th>
                    <th class="pb-3 font-semibold text-right">Admin Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800/60">
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="py-8 text-center text-gray-500">
                            <i class="fa-solid fa-user-slash text-3xl mb-2 text-gray-600"></i>
                            <div>No users found matching query.</div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <?php 
                            $status = $u['status'] ?? 'active';
                            $isAdmin = !empty($u['is_admin']);
                        ?>
                        <tr class="hover:bg-gray-900/40 transition-colors">
                            <td class="py-3.5 font-mono text-gray-400">#<?= htmlspecialchars((string)$u['id']) ?></td>
                            <td class="py-3.5">
                                <div class="font-bold text-white text-sm"><?= htmlspecialchars((string)$u['email']) ?></div>
                                <div class="text-[10px] text-gray-400">Ecosystem Access Active</div>
                            </td>
                            <td class="py-3.5">
                                <?php if ($isAdmin): ?>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-purple-500/20 text-purple-300 border border-purple-500/30">
                                        SuperAdmin
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-gray-800 text-gray-300 border border-gray-700">
                                        Member
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3.5">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase <?= $status === 'active' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-300 border border-rose-500/30' ?>">
                                    <?= htmlspecialchars($status) ?>
                                </span>
                            </td>
                            <td class="py-3.5 text-gray-400"><?= htmlspecialchars((string)($u['created_at'] ?? 'N/A')) ?></td>
                            <td class="py-3.5 text-right space-x-2">
                                <?php if ($status === 'active'): ?>
                                    <button onclick="changeUserStatus(<?= (int)$u['id'] ?>, 'suspended')" class="px-2.5 py-1 rounded bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 text-rose-300 text-[11px] font-semibold transition-colors">
                                        Suspend
                                    </button>
                                <?php else: ?>
                                    <button onclick="changeUserStatus(<?= (int)$u['id'] ?>, 'active')" class="px-2.5 py-1 rounded bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 text-[11px] font-semibold transition-colors">
                                        Activate
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
async function changeUserStatus(userId, newStatus) {
    if (!confirm(`Are you sure you want to change User #${userId} status to "${newStatus}"?`)) {
        return;
    }

    try {
        const res = await fetch('/admin/api/users/status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId, status: newStatus })
        });
        const data = await res.json();
        if (data.status === 'success') {
            showAdminToast(data.message || 'Status updated');
            setTimeout(() => { window.location.reload(); }, 800);
        } else {
            showAdminToast(data.message || 'Failed to update status', true);
        }
    } catch (e) {
        showAdminToast('Error: ' + e.message, true);
    }
}
</script>
@endsection
