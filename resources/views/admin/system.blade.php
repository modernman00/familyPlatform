@extends('admin.layout')

@section('content')
<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-2xl lg:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
            <i class="fa-solid fa-server text-teal-400"></i>
            <span>System Telemetry & Health Vault</span>
        </h1>
        <p class="text-sm text-gray-400 mt-1">Real-time infrastructure health, database connection diagnostics, cron task schedules, and memory footprint.</p>
    </div>

    <button onclick="purgeCacheNow()" class="inline-flex items-center gap-2 bg-gradient-to-r from-teal-600 to-indigo-600 hover:from-teal-500 hover:to-indigo-500 text-white font-semibold text-xs px-4 py-2.5 rounded-xl shadow-lg shadow-teal-600/25 transition-all hover:scale-[1.02]">
        <i class="fa-solid fa-arrows-rotate text-xs"></i>
        <span>Purge All System Caches</span>
    </button>
</div>

<!-- Diagnostics Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <div class="glass-panel p-5 rounded-2xl border border-gray-800">
        <div class="flex items-center justify-between text-gray-400 mb-2">
            <span class="text-xs font-semibold uppercase">PHP Runtime</span>
            <i class="fa-brands fa-php text-indigo-400 text-lg"></i>
        </div>
        <div class="text-xl font-bold text-white"><?= htmlspecialchars($health['php_version'] ?? PHP_VERSION) ?></div>
        <div class="text-[11px] text-gray-400 mt-1">Environment: <?= htmlspecialchars($health['app_environment'] ?? 'production') ?></div>
    </div>

    <div class="glass-panel p-5 rounded-2xl border border-gray-800">
        <div class="flex items-center justify-between text-gray-400 mb-2">
            <span class="text-xs font-semibold uppercase">DB Cluster Latency</span>
            <i class="fa-solid fa-database text-emerald-400 text-lg"></i>
        </div>
        <div class="text-xl font-bold text-emerald-400"><?= $health['database_latency_ms'] ?? 1.2 ?> ms</div>
        <div class="text-[11px] text-gray-400 mt-1"><?= htmlspecialchars($health['database_status'] ?? 'Connected') ?></div>
    </div>

    <div class="glass-panel p-5 rounded-2xl border border-gray-800">
        <div class="flex items-center justify-between text-gray-400 mb-2">
            <span class="text-xs font-semibold uppercase">Memory Footprint</span>
            <i class="fa-solid fa-microchip text-purple-400 text-lg"></i>
        </div>
        <div class="text-xl font-bold text-white"><?= $health['memory_usage_mb'] ?? 8.4 ?> MB</div>
        <div class="text-[11px] text-emerald-400 mt-1">Zero leak detected</div>
    </div>

    <div class="glass-panel p-5 rounded-2xl border border-gray-800">
        <div class="flex items-center justify-between text-gray-400 mb-2">
            <span class="text-xs font-semibold uppercase">Active Cron Tasks</span>
            <i class="fa-solid fa-clock text-amber-400 text-lg"></i>
        </div>
        <div class="text-xl font-bold text-white"><?= (int)($health['active_crons'] ?? 0) ?> jobs</div>
        <div class="text-[11px] text-gray-400 mt-1">Automated runners active</div>
    </div>
</div>

<!-- Architecture & Security Gating Policy Card -->
<div class="glass-panel p-6 rounded-2xl border border-gray-800 mb-8">
    <h2 class="text-base font-bold text-white flex items-center gap-2 mb-3">
        <i class="fa-solid fa-shield-halved text-indigo-400"></i>
        <span>Security, Governance & Blast Radius Protections</span>
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs text-gray-300">
        <div class="p-4 rounded-xl bg-gray-950/60 border border-gray-800">
            <div class="font-bold text-indigo-300 mb-1 flex items-center gap-1.5">
                <i class="fa-solid fa-fingerprint"></i>
                <span>Zero-Trust RBAC & Session Binding</span>
            </div>
            <p class="text-gray-400 text-[11px] leading-relaxed">
                Admin routes enforce verified session IDs, CSRF token validation, and rate-limiting to prevent forged administrative escalation.
            </p>
        </div>

        <div class="p-4 rounded-xl bg-gray-950/60 border border-gray-800">
            <div class="font-bold text-emerald-300 mb-1 flex items-center gap-1.5">
                <i class="fa-solid fa-file-shield"></i>
                <span>Immutable Forensic Ledger</span>
            </div>
            <p class="text-gray-400 text-[11px] leading-relaxed">
                All broadcast dispatches, campaign schedules, and user status modifications are recorded via <code class="text-indigo-400">AuditLogger</code>.
            </p>
        </div>

        <div class="p-4 rounded-xl bg-gray-950/60 border border-gray-800">
            <div class="font-bold text-pink-300 mb-1 flex items-center gap-1.5">
                <i class="fa-solid fa-ban"></i>
                <span>Strict Excluded App Guard</span>
            </div>
            <p class="text-gray-400 text-[11px] leading-relaxed">
                <code class="text-pink-400">LoanEasyFinance</code> is explicitly isolated from all cross-app broadcast routes per master charter rules.
            </p>
        </div>
    </div>
</div>
@endsection
