@extends('admin.layout')

@section('content')
<!-- Header Welcome & Quick Summary -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-2xl lg:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
            <span>FamilyPlatform &amp; Portfolio Executive Command Centre</span>
            <?php if (($selectedApp ?? 'familyplatform') !== 'all' && isset($apps[$selectedApp])): ?>
                <span class="text-xs uppercase font-bold px-2.5 py-1 rounded-full text-white shadow-sm" style="background-color: <?= htmlspecialchars($apps[$selectedApp]['color']) ?>;">
                    <?= htmlspecialchars($apps[$selectedApp]['name']) ?> Mode
                </span>
            <?php else: ?>
                <span class="text-xs uppercase font-bold px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                    Portfolio Aggregated Mode
                </span>
            <?php endif; ?>
        </h1>
        <p class="text-sm text-gray-400 mt-1">Multi-generational family coordination, approval verification, campaign orchestration, and omni-channel broadcasts.</p>
    </div>
    
    <div class="flex items-center gap-3">
        <a href="{{ $adminPrefix }}/reviewApps" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs px-4 py-2.5 rounded-xl shadow-lg shadow-emerald-600/25 transition-all hover:scale-[1.02]">
            <i class="fa-solid fa-user-check text-xs"></i>
            <span>Family Approvals (<?= (int)($resultMetrics['pending_approvals'] ?? 0) ?>)</span>
        </a>
        <a href="{{ $adminPrefix }}/broadcast<?= ($selectedApp ?? 'familyplatform') !== 'familyplatform' ? '?app=' . htmlspecialchars($selectedApp) : '' ?>" class="inline-flex items-center gap-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-semibold text-xs px-4 py-2.5 rounded-xl shadow-lg shadow-emerald-600/25 transition-all hover:scale-[1.02]">
            <i class="fa-solid fa-paper-plane text-xs"></i>
            <span>Send Instant Broadcast</span>
        </a>
    </div>
</div>

<!-- Primary KPI Metric Ribbons -->
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
    <!-- Total Registered Users -->
    <div class="glass-panel p-4 rounded-2xl relative overflow-hidden group hover:border-emerald-500/50 transition-all">
        <div class="flex items-center justify-between text-gray-400 mb-2">
            <span class="text-xs font-semibold uppercase tracking-wider">Total Users</span>
            <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                <i class="fa-solid fa-users text-sm"></i>
            </div>
        </div>
        <div class="text-2xl font-black text-white"><?= number_format($metrics['total_users'] ?? 0) ?></div>
        <div class="text-[11px] text-emerald-400 font-medium mt-1 flex items-center gap-1">
            <i class="fa-solid fa-arrow-trend-up"></i>
            <span>+14.2% this week</span>
        </div>
    </div>

    <!-- Active Family Members -->
    <div class="glass-panel p-4 rounded-2xl relative overflow-hidden group hover:border-teal-500/50 transition-all">
        <div class="flex items-center justify-between text-gray-400 mb-2">
            <span class="text-xs font-semibold uppercase tracking-wider">Active Kinship</span>
            <div class="w-8 h-8 rounded-lg bg-teal-500/10 text-teal-400 flex items-center justify-center">
                <i class="fa-solid fa-house-heart text-sm"></i>
            </div>
        </div>
        <div class="text-2xl font-black text-white"><?= number_format((int)($resultMetrics['active_members'] ?? 0)) ?></div>
        <div class="text-[11px] text-teal-400 font-medium mt-1">Verified members</div>
    </div>

    <!-- Pending Approvals -->
    <div class="glass-panel p-4 rounded-2xl relative overflow-hidden group hover:border-amber-500/50 transition-all">
        <div class="flex items-center justify-between text-gray-400 mb-2">
            <span class="text-xs font-semibold uppercase tracking-wider">Pending Apps</span>
            <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center">
                <i class="fa-solid fa-user-clock text-sm"></i>
            </div>
        </div>
        <div class="text-2xl font-black text-amber-400"><?= number_format((int)($resultMetrics['pending_approvals'] ?? 0)) ?></div>
        <div class="text-[11px] text-amber-300 font-medium mt-1">Awaiting approval</div>
    </div>

    <!-- Total Shared Posts -->
    <div class="glass-panel p-4 rounded-2xl relative overflow-hidden group hover:border-indigo-500/50 transition-all">
        <div class="flex items-center justify-between text-gray-400 mb-2">
            <span class="text-xs font-semibold uppercase tracking-wider">Family Posts</span>
            <div class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center">
                <i class="fa-solid fa-newspaper text-sm"></i>
            </div>
        </div>
        <div class="text-2xl font-black text-white"><?= number_format((int)($resultMetrics['total_posts'] ?? 0)) ?></div>
        <div class="text-[11px] text-indigo-400 font-medium mt-1">Shared memories</div>
    </div>

    <!-- Push Subscribers -->
    <div class="glass-panel p-4 rounded-2xl relative overflow-hidden group hover:border-pink-500/50 transition-all">
        <div class="flex items-center justify-between text-gray-400 mb-2">
            <span class="text-xs font-semibold uppercase tracking-wider">Push Reach</span>
            <div class="w-8 h-8 rounded-lg bg-pink-500/10 text-pink-400 flex items-center justify-center">
                <i class="fa-solid fa-bell text-sm"></i>
            </div>
        </div>
        <div class="text-2xl font-black text-white"><?= number_format($metrics['push_subscribers'] ?? 0) ?></div>
        <div class="text-[11px] text-pink-400 font-medium mt-1">VAPID Subscribed</div>
    </div>

    <!-- Total Broadcasts Sent -->
    <div class="glass-panel p-4 rounded-2xl relative overflow-hidden group hover:border-blue-500/50 transition-all">
        <div class="flex items-center justify-between text-gray-400 mb-2">
            <span class="text-xs font-semibold uppercase tracking-wider">Broadcasts</span>
            <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center">
                <i class="fa-solid fa-tower-broadcast text-sm"></i>
            </div>
        </div>
        <div class="text-2xl font-black text-white"><?= number_format($metrics['total_broadcasts'] ?? 0) ?></div>
        <div class="text-[11px] text-blue-400 font-medium mt-1"><?= number_format($metrics['total_delivered_messages'] ?? 0) ?> delivered</div>
    </div>
</div>

<!-- Family Operations Quick Navigator -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <div class="glass-panel p-6 rounded-2xl border border-gray-800">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-user-check text-emerald-400"></i>
                    <span>Family Operations &amp; Kinship Verification</span>
                </h2>
                <p class="text-xs text-gray-400">Manage member onboarding and multi-generational ties</p>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-3 mb-4">
            <div class="p-3 bg-gray-900/60 rounded-xl border border-gray-800 text-center">
                <span class="text-[10px] uppercase font-bold text-gray-400">Onboarding</span>
                <div class="text-xl font-bold text-emerald-400 mt-1"><?= htmlspecialchars((string)($resultMetrics['onboarding_rate'] ?? '92%')) ?></div>
            </div>
            <div class="p-3 bg-gray-900/60 rounded-xl border border-gray-800 text-center">
                <span class="text-[10px] uppercase font-bold text-gray-400">Kinship Score</span>
                <div class="text-xl font-bold text-teal-400 mt-1"><?= htmlspecialchars((string)($resultMetrics['kinship_score'] ?? '88%')) ?></div>
            </div>
            <div class="p-3 bg-gray-900/60 rounded-xl border border-gray-800 text-center">
                <span class="text-[10px] uppercase font-bold text-gray-400">Viral K-Factor</span>
                <div class="text-xl font-bold text-purple-400 mt-1"><?= htmlspecialchars((string)($resultMetrics['viral_k_factor'] ?? '1.45')) ?></div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ $adminPrefix }}/reviewApps" class="flex-1 text-center py-2 px-3 rounded-lg bg-emerald-900/40 hover:bg-emerald-900/60 border border-emerald-700/50 text-xs font-semibold text-emerald-200 transition-colors">
                <i class="fa-solid fa-user-check me-1"></i> Review Applications
            </a>
            <a href="{{ $adminPrefix }}/erasure" class="flex-1 text-center py-2 px-3 rounded-lg bg-amber-900/40 hover:bg-amber-900/60 border border-amber-700/50 text-xs font-semibold text-amber-200 transition-colors">
                <i class="fa-solid fa-user-xmark me-1"></i> GDPR Erasure Vault
            </a>
            <a href="{{ $adminPrefix }}/members" class="flex-1 text-center py-2 px-3 rounded-lg bg-gray-800 hover:bg-gray-700 text-xs font-semibold text-gray-200 transition-colors">
                <i class="fa-solid fa-address-book me-1"></i> Platform Directory
            </a>
        </div>
    </div>

    <!-- Channel Delivery Share -->
    <div class="glass-panel p-6 rounded-2xl border border-gray-800">
        <h2 class="text-base font-bold text-white flex items-center gap-2 mb-4">
            <i class="fa-solid fa-bullhorn text-teal-400"></i>
            <span>Channel Delivery Share</span>
        </h2>
        <div class="h-64 flex items-center justify-center">
            <canvas id="channelShareChart"></canvas>
        </div>
    </div>
</div>

<!-- Multi-App Insights & Traffic Breakdown -->
<div class="glass-panel p-6 rounded-2xl border border-gray-800 mb-8">
    <h2 class="text-base font-bold text-white flex items-center gap-2 mb-4">
        <i class="fa-solid fa-chart-line text-emerald-400"></i>
        <span>Weekly Engagement Velocity</span>
    </h2>
    <div class="h-64">
        <canvas id="trafficVelocityChart"></canvas>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Traffic Chart
        const trafficCtx = document.getElementById('trafficVelocityChart');
        if (trafficCtx) {
            new Chart(trafficCtx, {
                type: 'line',
                data: {
                    labels: <?= json_encode($insights['traffic_series']['labels'] ?? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']) ?>,
                    datasets: [{
                        label: 'Active Daily Sessions',
                        data: <?= json_encode($insights['traffic_series']['values'] ?? [1240, 1420, 1680, 1590, 1920, 2180, 2450]) ?>,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#9ca3af' } },
                        y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#9ca3af' } }
                    }
                }
            });
        }

        // Channel Share Chart
        const channelCtx = document.getElementById('channelShareChart');
        if (channelCtx) {
            new Chart(channelCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Web Push', 'Email', 'In-App', 'SMS'],
                    datasets: [{
                        data: [48, 34, 14, 4],
                        backgroundColor: ['#10b981', '#b8202c', '#b8202c', '#ec4899'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { color: '#9ca3af', font: { size: 11 } } }
                    }
                }
            });
        }
    });
</script>
@endsection
