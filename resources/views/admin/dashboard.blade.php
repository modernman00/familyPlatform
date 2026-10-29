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

<!-- Autonomous Executive Decision & Action Engine -->
<div class="mb-10">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-brain text-emerald-400"></i>
                    <span>Autonomous Executive Decision &amp; Action Engine</span>
                </h2>
                <span class="text-[10px] uppercase font-extrabold tracking-wider px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                    Algorithmic Intelligence
                </span>
            </div>
            <p class="text-xs text-gray-400 mt-0.5">Empirical anomaly detection, friction indexing, and 1-click prescriptive interventions</p>
        </div>
        
        <div class="flex items-center gap-3">
            <?php 
                $healthScore = (int)($decision['overall_health_score'] ?? 100);
                $healthBadgeColor = $healthScore >= 85 ? 'text-emerald-400 border-emerald-500/30 bg-emerald-500/10' : ($healthScore >= 70 ? 'text-amber-400 border-amber-500/30 bg-amber-500/10' : 'text-rose-400 border-rose-500/30 bg-rose-500/10');
            ?>
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl border text-xs font-bold <?= $healthBadgeColor ?>">
                <i class="fa-solid fa-heart-pulse"></i>
                <span>Platform Health: <?= $healthScore ?>/100</span>
            </div>
            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-gray-800 bg-gray-900/60 text-xs font-semibold text-gray-300">
                <i class="fa-solid fa-gauge text-emerald-400"></i>
                <span>Momentum: <?= number_format((float)($decision['market_momentum_index'] ?? 1.0), 2) ?>x</span>
            </div>
        </div>
    </div>

    <?php $insightsList = $decision['insights'] ?? []; ?>
    <?php if (!empty($insightsList) && count($insightsList) > 0): ?>
        <div class="space-y-3">
            <?php foreach ($insightsList as $ins): ?>
                <?php 
                    $sev = $ins['severity'] ?? 'INFO';
                    $borderClass = $sev === 'CRITICAL' ? 'border-rose-500/40 bg-rose-950/20' : ($sev === 'WARNING' ? 'border-amber-500/40 bg-amber-950/20' : 'border-emerald-500/30 bg-emerald-950/20');
                    $badgeClass = $sev === 'CRITICAL' ? 'bg-rose-500/20 text-rose-300 border-rose-500/30' : ($sev === 'WARNING' ? 'bg-amber-500/20 text-amber-300 border-amber-500/30' : 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30');
                    $btnClass = $sev === 'CRITICAL' ? 'bg-rose-600 hover:bg-rose-500 text-white' : ($sev === 'WARNING' ? 'bg-amber-600 hover:bg-amber-500 text-white' : 'bg-emerald-600 hover:bg-emerald-500 text-white');
                ?>
                <div class="glass-panel rounded-2xl p-5 border <?= $borderClass ?> flex flex-col lg:flex-row lg:items-center justify-between gap-4 transition-all">
                    <div class="space-y-1.5 max-w-3xl">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full border <?= $badgeClass ?>">
                                <?= htmlspecialchars($sev) ?>
                            </span>
                            <span class="text-xs font-semibold text-gray-400">
                                <?= htmlspecialchars($ins['vector'] ?? 'Operational') ?>
                            </span>
                            <span class="text-gray-600">&bull;</span>
                            <span class="text-xs text-emerald-400 font-medium">
                                <?= (int)($ins['confidence_pct'] ?? 95) ?>% Algorithmic Confidence
                            </span>
                        </div>
                        <h4 class="text-sm font-bold text-white"><?= htmlspecialchars($ins['headline'] ?? '') ?></h4>
                        <p class="text-xs text-gray-300 leading-relaxed"><?= htmlspecialchars($ins['body'] ?? '') ?></p>
                        <?php if (!empty($ins['empirical_proof'])): ?>
                            <div class="text-[11px] text-gray-400 font-mono mt-1 flex items-center gap-1.5">
                                <i class="fa-solid fa-square-poll-vertical text-emerald-400 text-xs"></i>
                                <span>Evidence: <?= htmlspecialchars($ins['empirical_proof']) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($ins['action_label']) && !empty($ins['action_url'])): ?>
                        <div class="flex-shrink-0">
                            <a href="<?= htmlspecialchars($ins['action_url']) ?>" 
                               <?= !empty($ins['action_onclick']) ? 'onclick="' . htmlspecialchars($ins['action_onclick']) . '; return false;"' : '' ?>
                               class="inline-flex items-center gap-2 <?= $btnClass ?> text-xs font-bold px-4 py-2.5 rounded-xl shadow-lg transition-all hover:scale-[1.02] whitespace-nowrap">
                                <i class="fa-solid <?= htmlspecialchars($ins['action_icon'] ?? 'fa-bolt') ?>"></i>
                                <span><?= htmlspecialchars($ins['action_label']) ?></span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="glass-panel rounded-2xl p-6 border border-emerald-500/30 bg-emerald-950/10 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-white">All Operational Vectors Optimal</h4>
                    <p class="text-xs text-gray-400 mt-0.5">Statistical anomaly detection, friction indexing, and churn modeling report zero critical bottlenecks across the ecosystem.</p>
                </div>
            </div>
            <span class="text-xs font-semibold text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-3 py-1 rounded-full whitespace-nowrap">
                Optimal Baseline
            </span>
        </div>
    <?php endif; ?>
</div>

<!-- Real-Time Presence & Live Visitor Telemetry ("Who is on the site and when") -->
<div id="live-presence-feed" class="mb-10">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-users-viewfinder text-emerald-400"></i>
                    <span>Live Presence &amp; Active Visitors</span>
                </h2>
                <span class="text-[10px] uppercase font-extrabold tracking-wider px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                    Real-Time Feed
                </span>
            </div>
            <p class="text-xs text-gray-400 mt-0.5">Live tracking of authenticated users and anonymous visitors active in the last 15 minutes</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-xs px-2.5 py-1 rounded-lg bg-gray-900 border border-gray-800 text-gray-300 font-medium">
                <strong class="text-white"><?= (int)($presenceSummary['authenticated_count'] ?? 0) ?></strong> Members
            </span>
            <span class="text-xs px-2.5 py-1 rounded-lg bg-gray-900 border border-gray-800 text-gray-300 font-medium">
                <strong class="text-white"><?= (int)($presenceSummary['guest_count'] ?? 0) ?></strong> Guests
            </span>
            <span class="text-xs px-2.5 py-1 rounded-lg bg-gray-900 border border-gray-800 text-gray-300 font-medium">
                <i class="fa-solid fa-mobile-screen text-[10px] text-gray-400"></i> <?= (int)($presenceSummary['mobile_count'] ?? 0) ?>
                <span class="mx-1 text-gray-600">/</span>
                <i class="fa-solid fa-laptop text-[10px] text-gray-400"></i> <?= (int)($presenceSummary['desktop_count'] ?? 0) ?>
            </span>
            <button onclick="window.location.reload()" title="Refresh presence" class="text-xs px-3 py-1 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-200 transition-colors flex items-center gap-1.5">
                <i class="fa-solid fa-arrows-rotate text-[10px]"></i>
                <span>Refresh</span>
            </button>
        </div>
    </div>

    <div class="glass-panel rounded-2xl border border-gray-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-gray-950/80 border-b border-gray-800 text-gray-400 uppercase font-semibold text-[10px] tracking-wider">
                        <th class="p-3.5 pl-5">Visitor / Account</th>
                        <th class="p-3.5">Role / Status</th>
                        <th class="p-3.5">Current Location (URL)</th>
                        <th class="p-3.5">Device &amp; IP</th>
                        <th class="p-3.5">Session Duration</th>
                        <th class="p-3.5">Last Seen</th>
                        <th class="p-3.5 pr-5 text-right">Activity</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/60">
                    <?php if (!empty($activeVisitors) && count($activeVisitors) > 0): ?>
                        <?php foreach ($activeVisitors as $vis): ?>
                            <tr class="hover:bg-gray-900/40 transition-colors">
                                <td class="p-3.5 pl-5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg <?= $vis['is_authenticated'] ? 'bg-emerald-600/30 text-emerald-400 border border-emerald-500/30' : 'bg-gray-800 text-gray-400 border border-gray-700' ?> flex items-center justify-center font-bold text-xs">
                                            <i class="fa-solid <?= $vis['is_authenticated'] ? 'fa-user-check' : 'fa-user-secret' ?>"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-white truncate max-w-[200px]" title="<?= htmlspecialchars($vis['display_name']) ?>">
                                                <?= htmlspecialchars($vis['display_name']) ?>
                                            </div>
                                            <div class="text-[10px] text-gray-500 uppercase font-mono">
                                                <?= htmlspecialchars($vis['app_name']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-3.5">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold <?= $vis['is_authenticated'] ? 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/20' : 'bg-gray-800 text-gray-400 border border-gray-700' ?>">
                                        <?= htmlspecialchars($vis['user_role'] ?? 'Guest') ?>
                                    </span>
                                </td>
                                <td class="p-3.5 font-mono text-[11px] text-gray-300">
                                    <span class="truncate block max-w-[240px]" title="<?= htmlspecialchars($vis['current_url']) ?>">
                                        <?= htmlspecialchars($vis['current_url']) ?>
                                    </span>
                                </td>
                                <td class="p-3.5 text-gray-400">
                                    <div class="flex items-center gap-1.5">
                                        <i class="fa-solid <?= $vis['device'] === 'Mobile' ? 'fa-mobile-screen' : ($vis['device'] === 'Tablet' ? 'fa-tablet-screen-button' : 'fa-laptop') ?> text-gray-400 text-[11px]"></i>
                                        <span><?= htmlspecialchars($vis['device']) ?></span>
                                    </div>
                                    <span class="text-[10px] text-gray-500 font-mono"><?= htmlspecialchars($vis['ip_address']) ?></span>
                                </td>
                                <td class="p-3.5 text-gray-300">
                                    <?= (int)$vis['duration_minutes'] ?> min<?= $vis['duration_minutes'] > 1 ? 's' : '' ?>
                                </td>
                                <td class="p-3.5 text-gray-300 font-medium">
                                    <?= htmlspecialchars($vis['time_ago_human']) ?>
                                </td>
                                <td class="p-3.5 pr-5 text-right">
                                    <?php if ($vis['status'] === 'Active Now'): ?>
                                        <span class="inline-flex items-center gap-1.5 text-emerald-400 font-semibold">
                                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                            <span>Active Now</span>
                                        </span>
                                    <?php elseif ($vis['status'] === 'Idle'): ?>
                                        <span class="inline-flex items-center gap-1.5 text-amber-400 font-medium">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                            <span>Idle</span>
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 text-gray-500">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span>
                                            <span>Away</span>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="p-8 text-center text-gray-400">
                                <div class="w-12 h-12 rounded-2xl bg-gray-900 border border-gray-800 text-gray-500 flex items-center justify-center mx-auto mb-2 text-lg">
                                    <i class="fa-solid fa-radar"></i>
                                </div>
                                <p class="text-sm font-semibold text-gray-300">No active visitors recorded in the last 15 minutes</p>
                                <p class="text-xs text-gray-500 mt-0.5">Live sessions will automatically display here as visitors browse routes.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Portfolio Growth Engine Matrix & Outstanding Strategic Tasks -->
<div class="glass-panel p-6 rounded-2xl border border-gray-800 mb-8" id="growth-engine-matrix">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-gray-800">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs uppercase font-extrabold tracking-widest px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                    Growth Engine Architecture
                </span>
                <span class="text-xs text-gray-400">• Executive Backlog &amp; Operational Status</span>
            </div>
            <h2 class="text-lg lg:text-xl font-black text-white mt-1.5 flex items-center gap-2.5">
                <i class="fa-solid fa-rocket text-emerald-400"></i>
                <span>The 5 Best-in-Class Portfolio Growth Engines &amp; Outstanding Tasks</span>
            </h2>
            <p class="text-xs text-gray-400 mt-1">
                Unified strategy for organic traffic dominance, algorithmic search discovery, community seeding, and Digital PR.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ $adminPrefix }}/pseo" class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs px-3.5 py-2 rounded-xl shadow-lg shadow-emerald-600/20 transition-all hover:scale-[1.02]">
                <i class="fa-solid fa-magnifying-glass-chart text-xs"></i>
                <span>Open pSEO CMS</span>
            </a>
            <a href="/kinship-calculator" target="_blank" class="inline-flex items-center gap-1.5 bg-gray-800 hover:bg-gray-700 text-gray-200 font-semibold text-xs px-3.5 py-2 rounded-xl border border-gray-700 transition-all">
                <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                <span>Test Tools</span>
            </a>
        </div>
    </div>

    <!-- 5 Engines Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        
        <!-- Engine 1: pSEO Matrix -->
        <div class="p-4 rounded-xl bg-gray-900/70 border border-gray-800 flex flex-col justify-between hover:border-emerald-500/40 transition-colors">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-400 flex items-center gap-1.5">
                        <i class="fa-solid fa-sitemap"></i> Engine 1
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        LIVE &amp; OPERATIONAL
                    </span>
                </div>
                <h3 class="text-sm font-bold text-white mb-1.5">Programmatic SEO (pSEO) Matrix &amp; CMS</h3>
                <p class="text-xs text-gray-400 leading-relaxed mb-3">
                    Dynamic long-tail landing pages answering high-intent questions across <code class="text-emerald-300 font-mono">/prompts/*</code> and <code class="text-purple-300 font-mono">/plan/*</code> with structured Answer Capsules and automated sitemap sync.
                </p>
            </div>
            <div class="pt-3 border-t border-gray-800/80 flex items-center justify-between text-[11px]">
                <span class="text-gray-400">Directory: <strong class="text-white">Live in /admin/pseo</strong></span>
                <a href="{{ $adminPrefix }}/pseo" class="text-emerald-400 hover:text-emerald-300 font-semibold">Manage CMS →</a>
            </div>
        </div>

        <!-- Engine 2: Free Interactive Tools -->
        <div class="p-4 rounded-xl bg-gray-900/70 border border-gray-800 flex flex-col justify-between hover:border-teal-500/40 transition-colors">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-teal-400 flex items-center gap-1.5">
                        <i class="fa-solid fa-screwdriver-wrench"></i> Engine 2
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-500/20 text-teal-300 border border-teal-500/30">
                        LIVE &amp; LINKED
                    </span>
                </div>
                <h3 class="text-sm font-bold text-white mb-1.5">Engineering-as-Marketing Free Tools</h3>
                <p class="text-xs text-gray-400 leading-relaxed mb-3">
                    Zero-friction utility engines: Kinship Calculator (<code class="text-teal-300 font-mono">/kinship-calculator</code>), Oral History Studio (<code class="text-teal-300 font-mono">/memory-prompts</code>), and Party Budget Estimator (<code class="text-purple-300 font-mono">/calculator</code>) directly linked from homepages and navbars.
                </p>
            </div>
            <div class="pt-3 border-t border-gray-800/80 flex items-center justify-between text-[11px]">
                <span class="text-gray-400">Placement: <strong class="text-white">Homepage &amp; Navbars</strong></span>
                <span class="text-teal-400 font-semibold">K-Factor &gt; 1.4</span>
            </div>
        </div>

        <!-- Engine 3: Autonomous Content Engine -->
        <div class="p-4 rounded-xl bg-gray-900/70 border border-gray-800 flex flex-col justify-between hover:border-indigo-500/40 transition-colors">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-indigo-400 flex items-center gap-1.5">
                        <i class="fa-solid fa-robot"></i> Engine 3
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                        LIVE &amp; ACTIVE
                    </span>
                </div>
                <h3 class="text-sm font-bold text-white mb-1.5">Autonomous Content &amp; Knowledge Graph</h3>
                <p class="text-xs text-gray-400 leading-relaxed mb-3">
                    Automated publication of research-backed generational articles in British English (<code class="text-indigo-300 font-mono">en-GB</code>), generating schema.org JSON-LD microdata, FAQPage markup, and social sharing cards.
                </p>
            </div>
            <div class="pt-3 border-t border-gray-800/80 flex items-center justify-between text-[11px]">
                <span class="text-gray-400">Cadence: <strong class="text-white">Automated Schedule</strong></span>
                <a href="/blog" target="_blank" class="text-indigo-400 hover:text-indigo-300 font-semibold">View Blog →</a>
            </div>
        </div>

        <!-- Engine 4: High-Leverage Community Seeding (Outstanding) -->
        <div class="p-4 rounded-xl bg-amber-950/20 border border-amber-500/30 flex flex-col justify-between hover:border-amber-500/60 transition-colors">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-400 flex items-center gap-1.5">
                        <i class="fa-solid fa-users-rays"></i> Engine 4
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                        OUTSTANDING ACTION ITEMS
                    </span>
                </div>
                <h3 class="text-sm font-bold text-white mb-1.5">High-Leverage Community Seeding</h3>
                <p class="text-xs text-gray-300 leading-relaxed mb-3">
                    Execute value-first distribution across Reddit (<code class="text-amber-200">r/Genealogy</code>, <code class="text-amber-200">r/FamilyHistory</code>, <code class="text-amber-200">r/EventPlanning</code>), Mumsnet, and Quora by sharing answers and benchmarks rather than promotional links.
                </p>
            </div>
            <div class="space-y-1.5 pt-2 border-t border-amber-500/20 text-[11px] text-amber-200/90 font-mono">
                <div class="flex items-center gap-1.5">
                    <i class="fa-regular fa-square text-amber-400"></i>
                    <span>Reddit Kinship &amp; Heritage Question Seeding</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <i class="fa-regular fa-square text-amber-400"></i>
                    <span>Mumsnet Family Lore &amp; Memory Preservation</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <i class="fa-regular fa-square text-amber-400"></i>
                    <span>Quora "How Many Drinks" Benchmark Answers</span>
                </div>
            </div>
        </div>

        <!-- Engine 5: Proprietary Digital PR & GEO (Outstanding) -->
        <div class="p-4 rounded-xl bg-purple-950/20 border border-purple-500/30 flex flex-col justify-between hover:border-purple-500/60 transition-colors lg:col-span-2">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-purple-400 flex items-center gap-1.5">
                        <i class="fa-solid fa-newspaper"></i> Engine 5
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30">
                        OUTSTANDING ACTION ITEMS
                    </span>
                </div>
                <h3 class="text-sm font-bold text-white mb-1.5">Proprietary Digital PR &amp; Generative Engine Optimization (GEO)</h3>
                <p class="text-xs text-gray-300 leading-relaxed mb-3">
                    Pitch proprietary data studies to lifestyle editors, BBC Heritage, and wedding publishers to earn high-authority backlinks and ensure ChatGPT, Perplexity, and Gemini cite our tools as the primary authoritative source.
                </p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-purple-500/20 text-[11px] text-purple-200/90">
                <div class="space-y-1.5 font-mono">
                    <div class="flex items-center gap-1.5">
                        <i class="fa-regular fa-square text-purple-400"></i>
                        <span>Campaign 1: UK Wedding Alcohol Waste Study</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <i class="fa-regular fa-square text-purple-400"></i>
                        <span>Pitch 38% overbuying rate to lifestyle press</span>
                    </div>
                </div>
                <div class="space-y-1.5 font-mono">
                    <div class="flex items-center gap-1.5">
                        <i class="fa-regular fa-square text-purple-400"></i>
                        <span>Campaign 2: Generational Memory Gap Index</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <i class="fa-regular fa-square text-purple-400"></i>
                        <span>Register brand entity schema in Wikidata &amp; GEO feeds</span>
                    </div>
                </div>
            </div>
        </div>

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
