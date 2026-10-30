<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'FamilyPlatform Master Admin Control Centre') ?> | UMAC</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    @include('partials.pwa-head')
    
    <!-- Modern Tailwind & FontAwesome CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/public/css/standards-v21.css?v={{ assetVersion('css/standards-v21.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: {
                            900: '#0b0f19',
                            800: '#111827',
                            700: '#1f2937',
                            600: '#374151'
                        },
                        brand: {
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #0b0f19;
            color: #f3f4f6;
            font-family: var(--font-sans, -apple-system, system-ui, sans-serif);
        }
        .glass-panel {
            background: rgba(17, 24, 39, 0.75);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #111827;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #374151;
            border-radius: 3px;
        }
        /* 🛡️ Inviolable Admin Typography Invariant: Immunity to hardcoded dark inline text */
        .w3-white, .w3-card, .w3-card-2, .w3-card-4, .w3-panel.w3-white {
            background-color: #111827 !important;
            color: #f3f4f6 !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
        }
        .w3-light-blue {
            background-color: rgba(30, 58, 138, 0.25) !important;
            color: #93c5fd !important;
        }
        .w3-light-grey {
            background-color: #1f2937 !important;
            color: #f3f4f6 !important;
        }
        .w3-table-all tr:nth-child(even) {
            background-color: #111827 !important;
        }
        .w3-table-all tr:nth-child(odd) {
            background-color: #0f172a !important;
        }
        .w3-table-all tr {
            color: #f3f4f6 !important;
            border-color: #374151 !important;
        }
        h1[style*="#1e293b"], h2[style*="#1e293b"], h3[style*="#1e293b"], h4[style*="#1e293b"], h5[style*="#1e293b"], h6[style*="#1e293b"],
        p[style*="#1e293b"], span[style*="#1e293b"], div[style*="#1e293b"], ol[style*="#1e293b"],
        h1[style*="#334155"], h2[style*="#334155"], h3[style*="#334155"], h4[style*="#334155"], h5[style*="#334155"], h6[style*="#334155"],
        span[style*="#334155"], div[style*="#334155"],
        h1[style*="#0f172a"], h2[style*="#0f172a"], h3[style*="#0f172a"], h4[style*="#0f172a"], h5[style*="#0f172a"], h6[style*="#0f172a"] {
            color: #f3f4f6 !important;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col bg-[#0b0f19] text-gray-100 antialiased selection:bg-emerald-500 selection:text-white">

    @php
        if (empty($adminPrefix)) {
            $adminSecretPath = ($_ENV['ADMIN_SECRET_PATH'] ?? getenv('ADMIN_SECRET_PATH'));
            $adminPrefix = !empty($adminSecretPath) ? '/' . trim((string)$adminSecretPath, '/') : '/admin';
        }
    @endphp

    <!-- Top Navigation Ribbon -->
    <header class="sticky top-0 z-50 glass-panel border-b border-gray-800/80 px-4 lg:px-8 py-3.5 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ $adminPrefix }}/dashboard" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 via-teal-600 to-indigo-600 flex items-center justify-center shadow-lg shadow-emerald-500/20 group-hover:scale-105 transition-transform duration-200">
                    <i class="fa-solid fa-house-heart text-white text-lg"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-lg text-white tracking-tight">UMAC</span>
                        <span class="text-[10px] uppercase font-bold tracking-widest px-2 py-0.5 rounded-full bg-emerald-500/20 border border-emerald-500/30 text-emerald-300">FamilyPlatform Control</span>
                    </div>
                    <p class="text-xs text-gray-400">Unified Multi-App Admin &amp; Growth Engine</p>
                </div>
            </a>

            <!-- App Selector Pill Selector -->
            <div class="hidden md:flex items-center ml-6 pl-6 border-l border-gray-800">
                <div class="relative">
                    <select id="globalAppSelector" onchange="switchAppFilter(this.value)" class="bg-gray-900/90 border border-gray-700/80 text-gray-200 text-xs rounded-lg px-3 py-1.5 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 appearance-none pr-8 cursor-pointer font-medium">
                        <option value="all" <?= ($selectedApp ?? 'familyplatform') === 'all' ? 'selected' : '' ?>>🌐 All Apps (Portfolio Aggregated)</option>
                        <option value="familyplatform" <?= ($selectedApp ?? 'familyplatform') === 'familyplatform' ? 'selected' : '' ?>>🏡 FamilyPlatform</option>
                        <option value="partyplatform" <?= ($selectedApp ?? '') === 'partyplatform' ? 'selected' : '' ?>>🎉 PartyPlatform</option>
                        <option value="idecide" <?= ($selectedApp ?? '') === 'idecide' ? 'selected' : '' ?>>⚖️ iDecide</option>
                        <option value="iaccount" <?= ($selectedApp ?? '') === 'iaccount' ? 'selected' : '' ?>>💳 iAccount</option>
                        <option value="scoretenant" <?= ($selectedApp ?? '') === 'scoretenant' ? 'selected' : '' ?>>🏢 TenantScore</option>
                        <option value="execmind" <?= ($selectedApp ?? '') === 'execmind' ? 'selected' : '' ?>>🧠 ExecMind</option>
                        <option value="bcp" <?= ($selectedApp ?? '') === 'bcp' ? 'selected' : '' ?>>🏥 Best Care Pro (BCP)</option>
                    </select>
                    <i class="fa-solid fa-chevron-down absolute right-2.5 top-2.5 text-[10px] text-gray-400 pointer-events-none"></i>
                </div>
            </div>
        </div>

        <!-- Right Side Nav Actions -->
        <div class="flex items-center gap-3">
            <button onclick="purgeCacheNow()" class="hidden sm:flex items-center gap-1.5 text-xs bg-gray-800/80 hover:bg-gray-700 border border-gray-700 text-gray-300 px-3 py-1.5 rounded-lg transition-colors" title="Purge Opcode &amp; Data Caches">
                <i class="fa-solid fa-arrows-rotate text-emerald-400 text-xs"></i>
                <span>Purge Cache</span>
            </button>

            <a href="{{ $adminPrefix }}/broadcast" class="flex items-center gap-1.5 text-xs bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-medium px-3.5 py-1.5 rounded-lg shadow-md shadow-emerald-600/20 transition-all hover:shadow-emerald-600/40">
                <i class="fa-solid fa-tower-broadcast text-xs"></i>
                <span>New Broadcast</span>
            </a>

            <div class="h-6 w-px bg-gray-800 hidden sm:block"></div>

            <a href="/dashboard" class="text-xs text-gray-400 hover:text-gray-200 transition-colors flex items-center gap-1">
                <span>Exit to App</span>
                <i class="fa-solid fa-arrow-right-from-bracket text-[10px]"></i>
            </a>
        </div>
    </header>

    <!-- Sub-Navbar Navigation Tabs -->
    <nav class="bg-gray-950/60 border-b border-gray-800/60 px-4 lg:px-8 py-2 overflow-x-auto custom-scrollbar">
        <div class="flex items-center gap-1 min-w-max">
            <a href="{{ $adminPrefix }}/dashboard<?= ($selectedApp ?? 'familyplatform') !== 'familyplatform' && ($selectedApp ?? '') !== 'all' ? '?app=' . htmlspecialchars($selectedApp) : '' ?>" class="flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-semibold <?= !isset($activeTab) || $activeTab === 'dashboard' ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-400 hover:text-gray-200 hover:bg-gray-900/60' ?> transition-colors">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Executive Dashboard</span>
            </a>

            <a href="{{ $adminPrefix }}/campaigns<?= ($selectedApp ?? 'familyplatform') !== 'familyplatform' ? '?app=' . htmlspecialchars($selectedApp) : '' ?>" class="flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-semibold <?= ($activeTab ?? '') === 'campaigns' ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-400 hover:text-gray-200 hover:bg-gray-900/60' ?> transition-colors">
                <i class="fa-solid fa-bullhorn"></i>
                <span>Campaigns</span>
            </a>

            <a href="{{ $adminPrefix }}/official-posts" class="flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-semibold <?= ($activeTab ?? '') === 'official_posts' ? 'bg-emerald-600 text-white shadow-sm' : 'text-emerald-400 hover:text-emerald-300 hover:bg-emerald-950/40' ?> transition-colors">
                <i class="fa-solid fa-bullhorn text-emerald-300"></i>
                <span>Official Feed</span>
            </a>

            <a href="{{ $adminPrefix }}/broadcast<?= ($selectedApp ?? 'familyplatform') !== 'familyplatform' ? '?app=' . htmlspecialchars($selectedApp) : '' ?>" class="flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-semibold <?= ($activeTab ?? '') === 'broadcast' ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-400 hover:text-gray-200 hover:bg-gray-900/60' ?> transition-colors">
                <i class="fa-solid fa-tower-broadcast"></i>
                <span>Broadcast Console</span>
            </a>

            <a href="{{ $adminPrefix }}/users" class="flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-semibold <?= ($activeTab ?? '') === 'users' ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-400 hover:text-gray-200 hover:bg-gray-900/60' ?> transition-colors">
                <i class="fa-solid fa-users"></i>
                <span>Universal Users</span>
            </a>

            <a href="{{ $adminPrefix }}/reviewApps" class="flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-semibold <?= ($activeTab ?? '') === 'reviewApps' ? 'bg-emerald-600 text-white shadow-sm' : 'text-emerald-400 hover:text-emerald-300 hover:bg-emerald-950/40' ?> transition-colors">
                <i class="fa-solid fa-user-check"></i>
                <span>Family Approvals</span>
            </a>

            <a href="{{ $adminPrefix }}/erasure" class="flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-semibold <?= ($activeTab ?? '') === 'erasure' ? 'bg-amber-600 text-white shadow-sm' : 'text-amber-400 hover:text-amber-300 hover:bg-amber-950/40' ?> transition-colors">
                <i class="fa-solid fa-user-xmark"></i>
                <span>GDPR Erasure</span>
            </a>

            <a href="{{ $adminPrefix }}/members" class="flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-semibold <?= ($activeTab ?? '') === 'members' ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-400 hover:text-gray-200 hover:bg-gray-900/60' ?> transition-colors">
                <i class="fa-solid fa-address-book"></i>
                <span>Platform Directory</span>
            </a>

            <a href="{{ $adminPrefix }}/telemetry" class="flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-semibold <?= ($activeTab ?? '') === 'telemetry' ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-400 hover:text-gray-200 hover:bg-gray-900/60' ?> transition-colors">
                <i class="fa-solid fa-chart-line"></i>
                <span>RUM Telemetry</span>
            </a>

            <a href="{{ $adminPrefix }}/pseo" class="flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-semibold <?= ($activeTab ?? '') === 'pseo' ? 'bg-emerald-600 text-white shadow-sm' : 'text-emerald-400 hover:text-emerald-300 hover:bg-emerald-950/40' ?> transition-colors">
                <i class="fa-solid fa-magnifying-glass-chart"></i>
                <span>pSEO Matrix &amp; CMS</span>
            </a>

            <a href="{{ $adminPrefix }}/system" class="flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-semibold <?= ($activeTab ?? '') === 'system' ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-400 hover:text-gray-200 hover:bg-gray-900/60' ?> transition-colors">
                <i class="fa-solid fa-server"></i>
                <span>System Health &amp; Vault</span>
            </a>

            <a href="{{ $adminPrefix }}/playbook" class="flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-semibold <?= ($activeTab ?? '') === 'playbook' ? 'bg-emerald-600 text-white shadow-sm' : 'text-indigo-400 hover:text-indigo-300 hover:bg-indigo-950/40' ?> transition-colors">
                <i class="fa-solid fa-book-bookmark"></i>
                <span>Living Playbook</span>
            </a>
        </div>
    </nav>

    <!-- Main Dynamic Content Container -->
    <main class="flex-1 px-4 lg:px-8 py-6 max-w-7xl w-full mx-auto">
        <!-- Toast Notification Banner -->
        <div id="adminToast" class="fixed bottom-5 right-5 z-50 transform translate-y-20 opacity-0 transition-all duration-300 pointer-events-none">
            <div id="adminToastBox" class="glass-panel px-4 py-3 rounded-xl border border-emerald-500/40 text-sm shadow-2xl flex items-center gap-3">
                <i id="adminToastIcon" class="fa-solid fa-circle-check text-emerald-400 text-lg"></i>
                <span id="adminToastMsg" class="font-medium text-gray-100">Operation Successful</span>
            </div>
        </div>

        @yield('content')
    </main>

    <!-- Master Footer -->
    <footer class="glass-panel border-t border-gray-800/80 py-4 px-4 lg:px-8 text-center text-xs text-gray-500 mt-auto">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-2 max-w-7xl mx-auto">
            <span>&copy; <?= date('Y') ?> FamilyPlatform Master Unified Admin Engine &middot; Active Multi-App Governance Charter v3.0</span>
            <div class="flex items-center gap-3 text-gray-400">
                <span class="flex items-center gap-1"><i class="fa-solid fa-circle text-[8px] text-emerald-400"></i> Cluster Healthy</span>
                <span>&middot;</span>
                <span class="text-gray-500">Node: LocalHost (PHP <?= PHP_VERSION ?>)</span>
            </div>
        </div>
    </footer>

    <!-- Global Scripts -->
    <script>
        function switchAppFilter(appName) {
            const currentUrl = new URL(window.location.href);
            if (appName === 'all') {
                currentUrl.searchParams.delete('app');
            } else {
                currentUrl.searchParams.set('app', appName);
            }
            window.location.href = currentUrl.toString();
        }

        function showAdminToast(message, isError = false) {
            const toast = document.getElementById('adminToast');
            const msg = document.getElementById('adminToastMsg');
            const icon = document.getElementById('adminToastIcon');
            const box = document.getElementById('adminToastBox');

            msg.textContent = message;
            if (isError) {
                icon.className = 'fa-solid fa-circle-exclamation text-rose-400 text-lg';
                box.className = 'glass-panel px-4 py-3 rounded-xl border border-rose-500/40 text-sm shadow-2xl flex items-center gap-3 bg-rose-950/80';
            } else {
                icon.className = 'fa-solid fa-circle-check text-emerald-400 text-lg';
                box.className = 'glass-panel px-4 py-3 rounded-xl border border-emerald-500/40 text-sm shadow-2xl flex items-center gap-3 bg-gray-900/90';
            }

            toast.classList.remove('translate-y-20', 'opacity-0', 'pointer-events-none');
            setTimeout(() => {
                toast.classList.add('translate-y-20', 'opacity-0', 'pointer-events-none');
            }, 3500);
        }

        async function purgeCacheNow() {
            try {
                const prefix = '<?= htmlspecialchars($adminPrefix) ?>';
                const res = await fetch(prefix + '/api/system/purge-cache', { method: 'POST' });
                const data = await res.json();
                if (data.status === 'success') {
                    showAdminToast(data.message || 'Cache purged successfully!');
                } else {
                    showAdminToast(data.message || 'Failed to purge cache', true);
                }
            } catch (err) {
                showAdminToast('Error purging cache: ' + err.message, true);
            }
        }
    </script>
    <script src="/public/js/family-tooltip.js?v={{ assetVersion('js/family-tooltip.js') }}" defer></script>
    @include('component.submitSpinner')
    @include('partials.pwa-register')
</body>
</html>
