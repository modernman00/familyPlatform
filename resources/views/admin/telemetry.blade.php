@extends ('layouts.w3s_admin')

@section('title', 'TELEMETRY_RUM_DASHBOARD')

@section('content')
<div class="w3-container" style="padding-top:22px; background:#0f172a; color:#f8fafc; min-height:100vh; font-family: system-ui, -apple-system, sans-serif;">
    <!-- Dashboard Header -->
    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #334155; padding-bottom:16px; margin-bottom:24px;">
        <div>
            <h2 style="margin:0; font-weight:800; color:#38bdf8; display:flex; align-items:center; gap:10px;">
                <i class="fa fa-tachometer" aria-hidden="true"></i> Executive Telemetry & RUM Friction Dashboard
            </h2>
            <p style="margin:4px 0 0 0; color:#94a3b8; font-size:14px;">Real-Time Privacy-Preserving User Experience Defect & Rage-Click Telemetry</p>
        </div>
        <div>
            <span style="background:rgba(56, 189, 248, 0.1); border:1px solid #38bdf8; color:#38bdf8; padding:6px 14px; border-radius:20px; font-size:12px; font-weight:600;">
                <i class="fa fa-shield"></i> UK GDPR Zero-PII Enforced
            </span>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="w3-row-padding w3-margin-bottom" style="margin:0 -8px;">
        <div class="w3-quarter" style="padding:0 8px;">
            <div style="background:rgba(30, 41, 59, 0.8); border:1px solid #334155; border-radius:12px; padding:20px; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="color:#94a3b8; font-size:13px; font-weight:600; text-transform:uppercase;">Total Events</span>
                    <i class="fa fa-bar-chart" style="color:#38bdf8; font-size:20px;"></i>
                </div>
                <h2 id="totalEvents" style="margin:12px 0 0 0; font-weight:800; color:#f8fafc; font-size:32px;">--</h2>
            </div>
        </div>
        <div class="w3-quarter" style="padding:0 8px;">
            <div style="background:rgba(239, 68, 68, 0.1); border:1px solid #ef4444; border-radius:12px; padding:20px; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="color:#fca5a5; font-size:13px; font-weight:600; text-transform:uppercase;">Rage Clicks</span>
                    <i class="fa fa-bolt" style="color:#ef4444; font-size:20px;"></i>
                </div>
                <h2 id="rageClicks" style="margin:12px 0 0 0; font-weight:800; color:#ef4444; font-size:32px;">--</h2>
            </div>
        </div>
        <div class="w3-quarter" style="padding:0 8px;">
            <div style="background:rgba(245, 158, 11, 0.1); border:1px solid #f59e0b; border-radius:12px; padding:20px; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="color:#fde68a; font-size:13px; font-weight:600; text-transform:uppercase;">Dead Clicks</span>
                    <i class="fa fa-exclamation-triangle" style="color:#f59e0b; font-size:20px;"></i>
                </div>
                <h2 id="deadClicks" style="margin:12px 0 0 0; font-weight:800; color:#f59e0b; font-size:32px;">--</h2>
            </div>
        </div>
        <div class="w3-quarter" style="padding:0 8px;">
            <div style="background:rgba(16, 185, 129, 0.1); border:1px solid #10b981; border-radius:12px; padding:20px; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="color:#a7f3d0; font-size:13px; font-weight:600; text-transform:uppercase;">Funnel Steps</span>
                    <i class="fa fa-filter" style="color:#10b981; font-size:20px;"></i>
                </div>
                <h2 id="funnelSteps" style="margin:12px 0 0 0; font-weight:800; color:#10b981; font-size:32px;">--</h2>
            </div>
        </div>
    </div>

    <!-- Hotspot Tables -->
    <div style="display:flex; gap:16px; margin-bottom:24px; flex-wrap:wrap;">
        <!-- Top Rage Click Targets -->
        <div style="flex:1; min-width:300px; background:rgba(30, 41, 59, 0.8); border:1px solid #334155; border-radius:12px; padding:20px;">
            <h4 style="margin:0 0 16px 0; color:#fca5a5; font-size:16px; font-weight:700; display:flex; align-items:center; gap:8px;">
                <i class="fa fa-fire"></i> Top Rage Click Friction Targets
            </h4>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; text-align:left; font-size:13px;">
                    <thead>
                        <tr style="border-bottom:1px solid #475569; color:#94a3b8;">
                            <th style="padding:8px 0;">Target Element</th>
                            <th style="padding:8px 0; text-align:right;">Rage Count</th>
                        </tr>
                    </thead>
                    <tbody id="rageTableBody">
                        <tr><td colspan="2" style="padding:12px 0; color:#64748b; text-align:center;">Loading friction data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Dead Click Targets -->
        <div style="flex:1; min-width:300px; background:rgba(30, 41, 59, 0.8); border:1px solid #334155; border-radius:12px; padding:20px;">
            <h4 style="margin:0 0 16px 0; color:#fde68a; font-size:16px; font-weight:700; display:flex; align-items:center; gap:8px;">
                <i class="fa fa-hand-pointer-o"></i> Top Dead Click Targets
            </h4>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; text-align:left; font-size:13px;">
                    <thead>
                        <tr style="border-bottom:1px solid #475569; color:#94a3b8;">
                            <th style="padding:8px 0;">Target Element</th>
                            <th style="padding:8px 0; text-align:right;">Dead Clicks</th>
                        </tr>
                    </thead>
                    <tbody id="deadTableBody">
                        <tr><td colspan="2" style="padding:12px 0; color:#64748b; text-align:center;">Loading friction data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Live Telemetry Stream -->
    <div style="background:rgba(30, 41, 59, 0.8); border:1px solid #334155; border-radius:12px; padding:20px;">
        <h4 style="margin:0 0 16px 0; color:#38bdf8; font-size:16px; font-weight:700; display:flex; align-items:center; gap:8px;">
            <i class="fa fa-list-alt"></i> Recent Telemetry Activity Stream (Live Auto-Refresh)
        </h4>
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; text-align:left; font-size:13px;">
                <thead>
                    <tr style="border-bottom:1px solid #475569; color:#94a3b8;">
                        <th style="padding:10px 8px;">Event</th>
                        <th style="padding:10px 8px;">URL Path</th>
                        <th style="padding:10px 8px;">Viewport</th>
                        <th style="padding:10px 8px;">Anonymized IP</th>
                        <th style="padding:10px 8px;">Timestamp</th>
                    </tr>
                </thead>
                <tbody id="streamTableBody">
                    <tr><td colspan="5" style="padding:16px 8px; color:#64748b; text-align:center;">Fetching telemetry stream...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    async function loadTelemetryStats() {
        try {
            const res = await fetch('/api/telemetry/stats');
            if (!res.ok) return;
            const payload = await res.json();
            if (payload.status !== 'success' || !payload.data) return;

            const data = payload.data;

            document.getElementById('totalEvents').textContent = data.total_events || 0;
            document.getElementById('rageClicks').textContent = data.rage_clicks_count || 0;
            document.getElementById('deadClicks').textContent = data.dead_clicks_count || 0;
            document.getElementById('funnelSteps').textContent = data.funnel_steps_count || 0;

            // Render Rage Table
            const rageTbody = document.getElementById('rageTableBody');
            if (data.top_rage_targets && data.top_rage_targets.length > 0) {
                rageTbody.innerHTML = data.top_rage_targets.map(item => `
                    <tr style="border-bottom:1px solid #334155;">
                        <td style="padding:10px 0; color:#cbd5e1; font-family:monospace;">${escapeHtml(item.target)}</td>
                        <td style="padding:10px 0; text-align:right; font-weight:bold; color:#ef4444;">${item.count}</td>
                    </tr>
                `).join('');
            } else {
                rageTbody.innerHTML = '<tr><td colspan="2" style="padding:12px 0; color:#64748b; text-align:center;">No rage clicks recorded yet. UI is operating cleanly! 🎉</td></tr>';
            }

            // Render Dead Table
            const deadTbody = document.getElementById('deadTableBody');
            if (data.top_dead_targets && data.top_dead_targets.length > 0) {
                deadTbody.innerHTML = data.top_dead_targets.map(item => `
                    <tr style="border-bottom:1px solid #334155;">
                        <td style="padding:10px 0; color:#cbd5e1; font-family:monospace;">${escapeHtml(item.target)}</td>
                        <td style="padding:10px 0; text-align:right; font-weight:bold; color:#f59e0b;">${item.count}</td>
                    </tr>
                `).join('');
            } else {
                deadTbody.innerHTML = '<tr><td colspan="2" style="padding:12px 0; color:#64748b; text-align:center;">No dead clicks recorded yet. UI design is clear!</td></tr>';
            }

            // Render Activity Stream
            const streamTbody = document.getElementById('streamTableBody');
            if (data.recent_events && data.recent_events.length > 0) {
                streamTbody.innerHTML = data.recent_events.map(ev => `
                    <tr style="border-bottom:1px solid #334155;">
                        <td style="padding:10px 8px;">${getEventBadge(ev.event)}</td>
                        <td style="padding:10px 8px; color:#e2e8f0; font-family:monospace;">${escapeHtml(ev.url)}</td>
                        <td style="padding:10px 8px; color:#94a3b8;">${escapeHtml(ev.viewport)}</td>
                        <td style="padding:10px 8px; color:#94a3b8; font-family:monospace;">${escapeHtml(ev.ip_anon)}</td>
                        <td style="padding:10px 8px; color:#64748b; font-size:12px;">${escapeHtml(ev.created_at)}</td>
                    </tr>
                `).join('');
            } else {
                streamTbody.innerHTML = '<tr><td colspan="5" style="padding:16px 8px; color:#64748b; text-align:center;">No telemetry events in stream yet.</td></tr>';
            }
        } catch (e) {
            console.error('Failed to load telemetry stats', e);
        }
    }

    function getEventBadge(evt) {
        if (evt === 'rage_click') return '<span style="background:rgba(239, 68, 68, 0.2); color:#ef4444; padding:3px 8px; border-radius:12px; font-weight:600; font-size:11px;">Rage Click</span>';
        if (evt === 'dead_click') return '<span style="background:rgba(245, 158, 11, 0.2); color:#f59e0b; padding:3px 8px; border-radius:12px; font-weight:600; font-size:11px;">Dead Click</span>';
        if (evt === 'funnel_step') return '<span style="background:rgba(16, 185, 129, 0.2); color:#10b981; padding:3px 8px; border-radius:12px; font-weight:600; font-size:11px;">Funnel Step</span>';
        return `<span style="background:rgba(56, 189, 248, 0.2); color:#38bdf8; padding:3px 8px; border-radius:12px; font-weight:600; font-size:11px;">${escapeHtml(evt)}</span>`;
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    loadTelemetryStats();
    setInterval(loadTelemetryStats, 5000);
});
</script>
@endsection
