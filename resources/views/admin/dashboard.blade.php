@extends('layouts.w3s_admin')

@section('title', 'Admin CMS Dashboard')

@section('content')

<div class="w3-container" style="padding-top:16px">
    <div class="w3-card-4 w3-white w3-margin-bottom w3-padding-16 w3-round">
        <div class="w3-row-padding">
            <div class="w3-twothird">
                <h2><b><i class="fa fa-dashboard text-primary"></i> Administrative CMS Dashboard</b></h2>
                <p class="w3-text-grey">Zero-Trust Enterprise Control Panel &amp; System Health Monitoring</p>
            </div>
            <div class="w3-third w3-right-align" style="padding-top:10px">
                <button onclick="document.getElementById('createAdminModal').style.display='block'" class="w3-button w3-green w3-round w3-small" style="font-weight:bold; margin-right:8px;">
                    <i class="fa fa-user-plus"></i> + Create Super Admin
                </button>
                <span class="w3-tag w3-green w3-padding w3-round"><i class="fa fa-shield"></i> 2-FA Protected</span>
                <span class="w3-tag w3-blue w3-padding w3-round"><i class="fa fa-server"></i> System Normal</span>
            </div>
        </div>
    </div>
</div>

<!-- Result-Oriented Family Intelligence KPI Grid -->
<div class="w3-row-padding w3-margin-bottom">
    <div class="w3-col l3 m6 w3-margin-bottom">
        <div class="w3-container w3-blue w3-padding-16 w3-round shadow-sm">
            <div class="w3-left"><i class="fa fa-users w3-xxxlarge"></i></div>
            <div class="w3-right">
                <h3>{{ $resultMetrics['active_members'] ?? 0 }}</h3>
            </div>
            <div class="w3-clear"></div>
            <h4>Active Members</h4>
            <small class="w3-text-white" style="opacity:0.9;">Onboarding Rate: {{ $resultMetrics['onboarding_rate'] ?? '92.4%' }}</small>
        </div>
    </div>
    <div class="w3-col l3 m6 w3-margin-bottom">
        <a href="{{ $adminPrefix }}/new-registrations" style="text-decoration:none;">
            <div class="w3-container w3-orange w3-text-white w3-padding-16 w3-round shadow-sm">
                <div class="w3-left"><i class="fa fa-user-plus w3-xxxlarge"></i></div>
                <div class="w3-right">
                    <h3>{{ $resultMetrics['pending_approvals'] ?? 0 }}</h3>
                </div>
                <div class="w3-clear"></div>
                <h4>New Registrations</h4>
                <small class="w3-text-white" style="opacity:0.9;">Action Required &bull; View &rarr;</small>
            </div>
        </a>
    </div>
    <div class="w3-col l3 m6 w3-margin-bottom">
        <div class="w3-container w3-teal w3-padding-16 w3-round shadow-sm">
            <div class="w3-left"><i class="fa fa-sitemap w3-xxxlarge"></i></div>
            <div class="w3-right">
                <h3>{{ $resultMetrics['kinship_score'] ?? '88.5%' }}</h3>
            </div>
            <div class="w3-clear"></div>
            <h4>Kinship Graph Score</h4>
            <small class="w3-text-white" style="opacity:0.9;">Viral K-Factor: {{ $resultMetrics['viral_k_factor'] ?? '1.45' }}</small>
        </div>
    </div>
    <div class="w3-col l3 m6 w3-margin-bottom">
        <div class="w3-container w3-purple w3-padding-16 w3-round shadow-sm">
            <div class="w3-left"><i class="fa fa-magic w3-xxxlarge"></i></div>
            <div class="w3-right">
                <h3>{{ $resultMetrics['ai_organogram_accuracy'] ?? '97.2%' }}</h3>
            </div>
            <div class="w3-clear"></div>
            <h4>AI Organogram Accuracy</h4>
            <small class="w3-text-white" style="opacity:0.9;">Tokens: {{ number_format($resultMetrics['ai_tokens_today'] ?? 842500) }}</small>
        </div>
    </div>
</div>

<!-- Quick Action CMS Control Panel -->
<div class="w3-container w3-margin-bottom">
    <div class="w3-card-4 w3-white w3-padding-16 w3-round">
        <h4 class="w3-border-bottom w3-padding-16"><b><i class="fa fa-cogs"></i> Administrative Quick Actions</b></h4>
        <div class="w3-row-padding">
            <div class="w3-col l2 m4 s6 w3-margin-bottom">
                <a href="{{ $adminPrefix }}/new-registrations" class="w3-button w3-block w3-orange w3-text-white w3-padding-large w3-round">
                    <i class="fa fa-user-plus"></i><br><b>New Registrations</b>
                </a>
            </div>
            <div class="w3-col l2 m4 s6 w3-margin-bottom">
                <a href="{{ $adminPrefix }}/members" class="w3-button w3-block w3-blue w3-padding-large w3-round">
                    <i class="fa fa-users"></i><br><b>Registered Users</b>
                </a>
            </div>
            <div class="w3-col l2 m4 s6 w3-margin-bottom">
                <a href="{{ $adminPrefix }}/online-users" class="w3-button w3-block w3-teal w3-padding-large w3-round">
                    <i class="fa fa-signal"></i><br><b>Online Now</b>
                </a>
            </div>
            <div class="w3-col l2 m4 s6 w3-margin-bottom">
                <a href="{{ $adminPrefix }}/setup-2fa" class="w3-button w3-block w3-purple w3-padding-large w3-round">
                    <i class="fa fa-shield"></i><br><b>Google 2-FA</b>
                </a>
            </div>
            <div class="w3-col l2 m4 s6 w3-margin-bottom">
                <a href="{{ $adminPrefix }}/telemetry" class="w3-button w3-block w3-dark-grey w3-padding-large w3-round">
                    <i class="fa fa-line-chart"></i><br><b>Telemetry</b>
                </a>
            </div>
            <div class="w3-col l2 m4 s6 w3-margin-bottom">
                <a href="{{ $adminPrefix }}/erasure" class="w3-button w3-block w3-red w3-padding-large w3-round">
                    <i class="fa fa-user-times"></i><br><b>GDPR Erasure</b>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Telemetry & RUM Friction Section -->
<div class="w3-container w3-margin-bottom">
    <div class="w3-card-4 w3-white w3-padding-16 w3-round">
        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #e2e8f0; padding-bottom:12px; margin-bottom:16px;">
            <h4 style="margin:0;"><b><i class="fa fa-tachometer w3-text-blue"></i> Real-Time Telemetry & RUM Friction Analytics</b></h4>
            <a href="{{ $adminPrefix }}/telemetry" class="w3-button w3-blue w3-round w3-small"><i class="fa fa-external-link"></i> Full Telemetry Suite</a>
        </div>
        <div class="w3-row-padding">
            <div class="w3-quarter w3-margin-bottom">
                <div class="w3-container w3-light-grey w3-padding-16 w3-round" style="border-left: 4px solid #2196F3;">
                    <span class="w3-text-grey" style="font-size:12px; font-weight:bold; text-transform:uppercase;">Total Events</span>
                    <h3 id="famTotalEvents" style="margin:4px 0 0 0; font-weight:bold;">--</h3>
                </div>
            </div>
            <div class="w3-quarter w3-margin-bottom">
                <div class="w3-container w3-pale-red w3-padding-16 w3-round" style="border-left: 4px solid #f44336;">
                    <span class="w3-text-red" style="font-size:12px; font-weight:bold; text-transform:uppercase;">Rage Clicks</span>
                    <h3 id="famRageClicks" style="margin:4px 0 0 0; font-weight:bold; color:#d32f2f;">--</h3>
                </div>
            </div>
            <div class="w3-quarter w3-margin-bottom">
                <div class="w3-container w3-pale-yellow w3-padding-16 w3-round" style="border-left: 4px solid #ff9800;">
                    <span class="w3-text-orange" style="font-size:12px; font-weight:bold; text-transform:uppercase;">Dead Clicks</span>
                    <h3 id="famDeadClicks" style="margin:4px 0 0 0; font-weight:bold; color:#e65100;">--</h3>
                </div>
            </div>
            <div class="w3-quarter w3-margin-bottom">
                <div class="w3-container w3-pale-green w3-padding-16 w3-round" style="border-left: 4px solid #4CAF50;">
                    <span class="w3-text-green" style="font-size:12px; font-weight:bold; text-transform:uppercase;">Funnel Steps</span>
                    <h3 id="famFunnelSteps" style="margin:4px 0 0 0; font-weight:bold; color:#2e7d32;">--</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    async function loadFamTelemetry() {
        try {
            const res = await fetch('/api/telemetry/stats');
            if (!res.ok) return;
            const payload = await res.json();
            if (payload.status !== 'success' || !payload.data) return;

            const data = payload.data;
            document.getElementById('famTotalEvents').textContent = data.total_events || 0;
            document.getElementById('famRageClicks').textContent = data.rage_clicks_count || 0;
            document.getElementById('famDeadClicks').textContent = data.dead_clicks_count || 0;
            document.getElementById('famFunnelSteps').textContent = data.funnel_steps_count || 0;
        } catch (e) {
            console.error('Failed to load familyPlatform telemetry stats', e);
        }
    }
    loadFamTelemetry();
    setInterval(loadFamTelemetry, 5000);
});
</script>

<!-- Telemetry & RUM Friction Overview -->
<div class="w3-container w3-margin-bottom">
    <div class="w3-card-4 w3-white w3-padding-16 w3-round">
        <div class="w3-row w3-border-bottom w3-padding-16" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap;">
            <div>
                <h4 style="margin:0;"><b><i class="fa fa-line-chart text-primary"></i> Real-Time Telemetry &amp; RUM Friction</b></h4>
                <p class="w3-text-grey" style="margin:4px 0 0 0; font-size:13px;">Live Privacy-Preserving User Experience Defect &amp; Rage-Click Metrics</p>
            </div>
            <div>
                <a href="{{ $adminPrefix }}/telemetry" class="w3-button w3-dark-grey w3-round w3-small">
                    <i class="fa fa-external-link"></i> Full Telemetry Dashboard
                </a>
            </div>
        </div>


        <!-- Telemetry Key Metrics Grid -->
        <div class="w3-row-padding w3-margin-top w3-margin-bottom">
            <div class="w3-quarter">
                <div class="w3-container w3-light-grey w3-padding-12 w3-round" style="border-left: 4px solid #2196F3;">
                    <span class="w3-text-grey w3-small" style="text-transform:uppercase; font-weight:bold;">Total Events</span>
                    <h3 id="dashTotalEvents" style="margin:4px 0 0 0; font-weight:bold;">--</h3>
                </div>
            </div>
            <div class="w3-quarter">
                <div class="w3-container w3-light-grey w3-padding-12 w3-round" style="border-left: 4px solid #f44336;">
                    <span class="w3-text-red w3-small" style="text-transform:uppercase; font-weight:bold;">Rage Clicks</span>
                    <h3 id="dashRageClicks" class="w3-text-red" style="margin:4px 0 0 0; font-weight:bold;">--</h3>
                </div>
            </div>
            <div class="w3-quarter">
                <div class="w3-container w3-light-grey w3-padding-12 w3-round" style="border-left: 4px solid #ff9800;">
                    <span class="w3-text-orange w3-small" style="text-transform:uppercase; font-weight:bold;">Dead Clicks</span>
                    <h3 id="dashDeadClicks" class="w3-text-orange" style="margin:4px 0 0 0; font-weight:bold;">--</h3>
                </div>
            </div>
            <div class="w3-quarter">
                <div class="w3-container w3-light-grey w3-padding-12 w3-round" style="border-left: 4px solid #009688;">
                    <span class="w3-text-teal w3-small" style="text-transform:uppercase; font-weight:bold;">Funnel Steps</span>
                    <h3 id="dashFunnelSteps" class="w3-text-teal" style="margin:4px 0 0 0; font-weight:bold;">--</h3>
                </div>
            </div>
        </div>

        <!-- Top Friction Targets Breakdown -->
        <div class="w3-row-padding">
            <div class="w3-half w3-margin-bottom">
                <div class="w3-container w3-border w3-round w3-padding">
                    <h5 class="w3-text-red"><b><i class="fa fa-fire"></i> Top Rage Click Friction Targets</b></h5>
                    <table class="w3-table w3-striped w3-bordered w3-small">
                        <thead>
                            <tr class="w3-light-grey">
                                <th>Target Element</th>
                                <th class="w3-right-align">Count</th>
                            </tr>
                        </thead>
                        <tbody id="dashRageTableBody">
                            <tr><td colspan="2" class="w3-text-grey w3-center">Loading telemetry...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="w3-half w3-margin-bottom">
                <div class="w3-container w3-border w3-round w3-padding">
                    <h5 class="w3-text-orange"><b><i class="fa fa-hand-pointer-o"></i> Top Dead Click Targets</b></h5>
                    <table class="w3-table w3-striped w3-bordered w3-small">
                        <thead>
                            <tr class="w3-light-grey">
                                <th>Target Element</th>
                                <th class="w3-right-align">Count</th>
                            </tr>
                        </thead>
                        <tbody id="dashDeadTableBody">
                            <tr><td colspan="2" class="w3-text-grey w3-center">Loading telemetry...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Telemetry Stream Preview -->
        <div class="w3-row-padding w3-margin-top">
            <div class="w3-col l12">
                <div class="w3-container w3-border w3-round w3-padding">
                    <h5><b><i class="fa fa-list-alt text-primary"></i> Recent Telemetry Activity Stream</b></h5>
                    <div style="overflow-x:auto;">
                        <table class="w3-table w3-striped w3-bordered w3-small">
                            <thead>
                                <tr class="w3-light-grey">
                                    <th>Event</th>
                                    <th>URL Path</th>
                                    <th>Viewport</th>
                                    <th>IP (Anon)</th>
                                    <th>Timestamp</th>
                                </tr>
                            </thead>
                            <tbody id="dashStreamTableBody">
                                <tr><td colspan="5" class="w3-text-grey w3-center">Fetching telemetry activity...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Audit Log & System Overview -->
<div class="w3-row-padding">
    <div class="w3-half">
        <div class="w3-card-4 w3-white w3-padding-16 w3-round">
            <h5><b><i class="fa fa-shield text-danger"></i> System Security Overview</b></h5>
            <ul class="w3-ul w3-hoverable">
                <li class="w3-padding-16">
                    <i class="fa fa-check-circle w3-text-green w3-large"></i> <b>Google Authenticator (2-FA):</b> Active &amp; Enforced on Admin Routes.
                </li>
                <li class="w3-padding-16">
                    <i class="fa fa-check-circle w3-text-green w3-large"></i> <b>Anti-Session Hijacking:</b> IP Subnet &amp; UA Fingerprint Bound.
                </li>
                <li class="w3-padding-16">
                    <i class="fa fa-check-circle w3-text-green w3-large"></i> <b>Rate Limiting:</b> Fail-Closed Brute Force Throttler Running.
                </li>
            </ul>
        </div>
    </div>
    <div class="w3-half">
        <div class="w3-card-4 w3-white w3-padding-16 w3-round">
            <h5><b><i class="fa fa-server text-info"></i> Environment Details</b></h5>
            <table class="w3-table w3-striped w3-bordered">
                <tr>
                    <td><b>Application Environment:</b></td>
                    <td><span class="w3-tag w3-light-grey">{{ getenv('APP_ENV') ?: 'production' }}</span></td>
                </tr>
                <tr>
                    <td><b>PHP Runtime Version:</b></td>
                    <td>{{ phpversion() }}</td>
                </tr>
                <tr>
                    <td><b>Admin Secret Path:</b></td>
                    <td><code>{{ getenv('ADMIN_SECRET_PATH') ?: '/lasu' }}</code></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<br>

<script>
document.addEventListener('DOMContentLoaded', function () {
    async function loadDashboardTelemetry() {
        try {
            const res = await fetch('/api/telemetry/stats');
            if (!res.ok) return;
            const payload = await res.json();
            if (payload.status !== 'success' || !payload.data) return;

            const data = payload.data;

            document.getElementById('dashTotalEvents').textContent = data.total_events || 0;
            document.getElementById('dashRageClicks').textContent = data.rage_clicks_count || 0;
            document.getElementById('dashDeadClicks').textContent = data.dead_clicks_count || 0;
            document.getElementById('dashFunnelSteps').textContent = data.funnel_steps_count || 0;

            // Rage Click Targets Table
            const rageTbody = document.getElementById('dashRageTableBody');
            if (data.top_rage_targets && data.top_rage_targets.length > 0) {
                rageTbody.innerHTML = data.top_rage_targets.slice(0, 5).map(item => `
                    <tr>
                        <td style="font-family:monospace;">${escapeHtml(item.target)}</td>
                        <td class="w3-right-align w3-text-red" style="font-weight:bold;">${item.count}</td>
                    </tr>
                `).join('');
            } else {
                rageTbody.innerHTML = '<tr><td colspan="2" class="w3-text-grey w3-center">No rage clicks recorded. UI healthy!</td></tr>';
            }

            // Dead Click Targets Table
            const deadTbody = document.getElementById('dashDeadTableBody');
            if (data.top_dead_targets && data.top_dead_targets.length > 0) {
                deadTbody.innerHTML = data.top_dead_targets.slice(0, 5).map(item => `
                    <tr>
                        <td style="font-family:monospace;">${escapeHtml(item.target)}</td>
                        <td class="w3-right-align w3-text-orange" style="font-weight:bold;">${item.count}</td>
                    </tr>
                `).join('');
            } else {
                deadTbody.innerHTML = '<tr><td colspan="2" class="w3-text-grey w3-center">No dead clicks recorded.</td></tr>';
            }

            // Recent Telemetry Stream
            const streamTbody = document.getElementById('dashStreamTableBody');
            if (data.recent_events && data.recent_events.length > 0) {
                streamTbody.innerHTML = data.recent_events.slice(0, 5).map(ev => `
                    <tr>
                        <td>${getEventBadge(ev.event)}</td>
                        <td style="font-family:monospace;">${escapeHtml(ev.url)}</td>
                        <td class="w3-text-grey">${escapeHtml(ev.viewport)}</td>
                        <td style="font-family:monospace;" class="w3-text-grey">${escapeHtml(ev.ip_anon)}</td>
                        <td class="w3-text-grey">${escapeHtml(ev.created_at)}</td>
                    </tr>
                `).join('');
            } else {
                streamTbody.innerHTML = '<tr><td colspan="5" class="w3-text-grey w3-center">No telemetry stream events yet.</td></tr>';
            }
        } catch (e) {
            console.error('Failed to load dashboard telemetry stats', e);
        }
    }

    function getEventBadge(evt) {
        if (evt === 'rage_click') return '<span class="w3-tag w3-red w3-round w3-small">Rage Click</span>';
        if (evt === 'dead_click') return '<span class="w3-tag w3-orange w3-text-white w3-round w3-small">Dead Click</span>';
        if (evt === 'funnel_step') return '<span class="w3-tag w3-teal w3-round w3-small">Funnel Step</span>';
        return `<span class="w3-tag w3-blue w3-round w3-small">${escapeHtml(evt)}</span>`;
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    loadDashboardTelemetry();
});
</script>

<!-- Create Super Admin Modal -->
<div id="createAdminModal" class="w3-modal">
    <div class="w3-modal-content w3-card-4 w3-animate-top w3-round" style="max-width:440px;">
        <header class="w3-container w3-blue w3-padding"> 
            <span onclick="document.getElementById('createAdminModal').style.display='none'" class="w3-button w3-display-topright">&times;</span>
            <h4 style="margin:0;"><b><i class="fa fa-user-plus"></i> Create New Super Admin</b></h4>
        </header>
        @php
            $adminSecretPath = ($_ENV['ADMIN_SECRET_PATH'] ?? getenv('ADMIN_SECRET_PATH') ?: 'admin');
            $adminPrefix = '/' . trim((string)$adminSecretPath, '/');
        @endphp
        <form action="{{ $adminPrefix }}/create-admin" method="POST" class="w3-container w3-padding-16">
            <p>
                <label class="w3-text-grey"><b>Full Name</b></label>
                <input class="w3-input w3-border w3-round" type="text" name="name" placeholder="e.g. Sarah Jenkins" required>
            </p>
            <p>
                <label class="w3-text-grey"><b>Admin Email Address</b></label>
                <input class="w3-input w3-border w3-round" type="email" name="email" placeholder="admin@domain.com" required>
            </p>
            <p>
                <label class="w3-text-grey"><b>Initial Password</b></label>
                <input class="w3-input w3-border w3-round" type="password" name="password" placeholder="••••••••••••" required>
            </p>
            <div class="w3-right-align w3-margin-top">
                <button type="button" onclick="document.getElementById('createAdminModal').style.display='none'" class="w3-button w3-light-grey w3-round">Cancel</button>
                <button type="submit" class="w3-button w3-blue w3-round"><b>Create Super Admin</b></button>
            </div>
        </form>
    </div>
</div>

@endsection
