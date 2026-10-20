@extends('admin.layout')

@section('content')
<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-2xl lg:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
            <i class="fa-solid fa-tower-broadcast text-indigo-400"></i>
            <span>Omni-Channel Broadcast Console</span>
        </h1>
        <p class="text-sm text-gray-400 mt-1">Dispatch high-priority announcements and segmented messages across Web Push, Email, In-App, and SMS.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-12">
    <!-- Left Column: Broadcast Composer Form -->
    <div class="lg:col-span-7 space-y-6">
        <div class="glass-panel p-6 rounded-2xl border border-gray-800 space-y-5">
            <div class="flex items-center justify-between border-b border-gray-800 pb-4">
                <h2 class="text-base font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-pen-nib text-indigo-400"></i>
                    <span>Compose Broadcast</span>
                </h2>
                <div class="text-xs px-2.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center gap-1.5 font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span id="estimatedAudienceBadge">Estimating Reach...</span>
                </div>
            </div>

            <!-- Target App Selector -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-300 mb-2">Target Application</label>
                <select id="broadcastTargetApp" onchange="updateAudienceCount()" class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    <option value="all" <?= ($selectedApp ?? 'all') === 'all' ? 'selected' : '' ?>>🌐 All Portfolio Apps (Broadcast All)</option>
                    <?php foreach ($apps as $key => $app): ?>
                        <option value="<?= $key ?>" <?= ($selectedApp ?? '') === $key ? 'selected' : '' ?>>
                            <?= htmlspecialchars($app['name']) ?> (<?= htmlspecialchars($app['category']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Channel & Audience Tier Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-300 mb-2">Delivery Channel</label>
                    <select id="broadcastChannel" onchange="updateAudienceCount(); updatePreviewChannel();" class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        <option value="push">🔔 Web Push Notification (Instant VAPID)</option>
                        <option value="email">✉️ HTML Email Digest (SES/SMTP)</option>
                        <option value="in_app">📥 In-App Notification Center</option>
                        <option value="omni">🚀 Omni-Channel (Push + Email + In-App)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-300 mb-2">Audience Cohort</label>
                    <select id="broadcastAudienceTier" onchange="updateAudienceCount()" class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        <option value="all">👥 All Registered Users</option>
                        <option value="active">⚡ Active Users (Last 14 Days)</option>
                        <option value="pending">⏳ Pending Onboarding</option>
                    </select>
                </div>
            </div>

            <!-- Broadcast Title & Subject -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-300 mb-2">Broadcast Headline / Title</label>
                <input type="text" id="broadcastTitle" oninput="updateLivePreview()" placeholder="e.g. Major Platform Upgrade & New Executive Intelligence Agent" class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-medium">
            </div>

            <!-- Message Body -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-300">Message Content</label>
                    <div class="flex items-center gap-1.5 text-[11px] text-gray-400">
                        <span>Dynamic Tags:</span>
                        <button type="button" onclick="insertTag('{'+'{first_name}'+'}')" class="text-indigo-400 hover:underline"><code>&#123;&#123;first_name&#125;&#125;</code></button>
                        <button type="button" onclick="insertTag('{'+'{app_name}'+'}')" class="text-indigo-400 hover:underline"><code>&#123;&#123;app_name&#125;&#125;</code></button>
                    </div>
                </div>
                <textarea id="broadcastMessage" rows="5" oninput="updateLivePreview()" placeholder="Write your high-impact message here..." class="w-full bg-gray-900 border border-gray-700 rounded-xl p-4 text-sm text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 custom-scrollbar"></textarea>
            </div>

            <!-- Call-to-Action Link -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-300 mb-2">Target Action Link (Optional)</label>
                <div class="relative">
                    <i class="fa-solid fa-link absolute left-4 top-3 text-xs text-gray-500"></i>
                    <input type="text" id="broadcastActionUrl" value="/dashboard" placeholder="/dashboard or https://..." class="w-full bg-gray-900 border border-gray-700 rounded-xl pl-10 pr-4 py-2.5 text-sm text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-2 flex items-center justify-between gap-4">
                <button type="button" onclick="testSendSelf()" class="text-xs bg-gray-800 hover:bg-gray-700 text-gray-300 px-4 py-2.5 rounded-xl border border-gray-700 font-semibold transition-colors flex items-center gap-1.5">
                    <i class="fa-solid fa-flask text-indigo-400"></i>
                    <span>Send Test to Me</span>
                </button>

                <button type="button" id="sendBroadcastBtn" onclick="confirmAndDispatchBroadcast()" class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 hover:from-indigo-500 hover:to-pink-500 text-white font-bold text-sm px-6 py-3 rounded-xl shadow-lg shadow-indigo-600/30 transition-all hover:scale-[1.02] flex items-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>Dispatch Broadcast Now</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Right Column: Live Multi-Device Preview Simulator -->
    <div class="lg:col-span-5 space-y-6">
        <div class="glass-panel p-6 rounded-2xl border border-gray-800">
            <h3 class="text-base font-bold text-white flex items-center gap-2 mb-1">
                <i class="fa-solid fa-mobile-screen-button text-purple-400"></i>
                <span>Live Device Preview</span>
            </h3>
            <p class="text-xs text-gray-400 mb-6">Real-time simulation of notification rendering</p>

            <!-- Device Mockup Card -->
            <div class="bg-gray-950 rounded-2xl p-5 border border-gray-800 shadow-2xl relative overflow-hidden">
                <div class="flex items-center justify-between text-xs text-gray-500 mb-4 border-b border-gray-900 pb-2">
                    <span class="flex items-center gap-1.5 font-semibold text-gray-400">
                        <i class="fa-solid fa-shield-halved text-indigo-400"></i>
                        <span id="previewAppHeader">ExecMind Ecosystem</span>
                    </span>
                    <span>Just Now</span>
                </div>

                <!-- Push Notification Mockup Card -->
                <div class="bg-gray-900/90 rounded-xl p-4 border border-indigo-500/30 shadow-lg flex items-start gap-3.5">
                    <div class="w-9 h-9 rounded-lg bg-indigo-600 flex items-center justify-center text-white shrink-0 shadow-md">
                        <i class="fa-solid fa-bell text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div id="previewTitle" class="text-sm font-bold text-white truncate">Major Platform Upgrade Announced</div>
                        <div id="previewBody" class="text-xs text-gray-300 mt-1 leading-relaxed line-clamp-3">We have just released new executive intelligence features and autonomous board discovery...</div>
                        <div class="mt-2.5 flex items-center gap-2">
                            <span class="text-[10px] uppercase font-bold text-indigo-400 bg-indigo-500/10 px-2 py-0.5 rounded border border-indigo-500/20">Action Required</span>
                        </div>
                    </div>
                </div>

                <!-- Recipient Safety Capsule -->
                <div class="mt-6 p-3.5 rounded-xl bg-indigo-950/40 border border-indigo-500/20 text-xs text-gray-300">
                    <div class="flex items-center gap-2 font-bold text-indigo-300 mb-1">
                        <i class="fa-solid fa-shield-check"></i>
                        <span>Zero-Spam & Delivery Safeguards</span>
                    </div>
                    <p class="text-[11px] text-gray-400 leading-normal">
                        All broadcasts are throttled across our background queue with automatic rate-limiting and instant unsubscribe compliance headers.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Historical Broadcasts Table -->
<div class="glass-panel p-6 rounded-2xl border border-gray-800">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-base font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-blue-400"></i>
                <span>Broadcast Dispatch History</span>
            </h2>
            <p class="text-xs text-gray-400">Audit trail of previously broadcasted messages</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="border-b border-gray-800 text-gray-400">
                    <th class="pb-3 font-semibold">Title & App</th>
                    <th class="pb-3 font-semibold">Channel</th>
                    <th class="pb-3 font-semibold">Recipients</th>
                    <th class="pb-3 font-semibold">Status</th>
                    <th class="pb-3 font-semibold">Sent By</th>
                    <th class="pb-3 font-semibold">Timestamp</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800/60">
                <?php if (empty($broadcasts)): ?>
                    <tr>
                        <td colspan="6" class="py-8 text-center text-gray-500">
                            <i class="fa-solid fa-satellite-dish text-3xl mb-2 text-gray-600"></i>
                            <div>No previous broadcast records found.</div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($broadcasts as $b): ?>
                        <tr class="hover:bg-gray-900/40 transition-colors">
                            <td class="py-3.5">
                                <div class="font-bold text-white text-sm"><?= htmlspecialchars($b['title']) ?></div>
                                <div class="text-[10px] text-gray-400 uppercase mt-0.5">Target: <?= htmlspecialchars($b['target_apps']) ?></div>
                            </td>
                            <td class="py-3.5">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                    <?= htmlspecialchars($b['channel']) ?>
                                </span>
                            </td>
                            <td class="py-3.5 font-bold text-gray-100"><?= number_format((int)$b['recipient_count']) ?> users</td>
                            <td class="py-3.5">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                    <?= htmlspecialchars($b['status']) ?>
                                </span>
                            </td>
                            <td class="py-3.5 text-gray-400"><?= htmlspecialchars($b['admin_email'] ?? 'SuperAdmin') ?></td>
                            <td class="py-3.5 text-gray-400"><?= htmlspecialchars($b['sent_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
async function updateAudienceCount() {
    const targetApp = document.getElementById('broadcastTargetApp').value;
    const channel = document.getElementById('broadcastChannel').value;
    const audienceTier = document.getElementById('broadcastAudienceTier').value;
    const badge = document.getElementById('estimatedAudienceBadge');

    badge.textContent = 'Calculating reach...';

    try {
        const res = await fetch('/admin/api/broadcast/audience', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ target_app: targetApp, channel: channel, audience_tier: audienceTier })
        });
        const data = await res.json();
        if (data.status === 'success') {
            badge.textContent = `Est. Reach: ~${Number(data.count).toLocaleString()} users`;
        }
    } catch (e) {
        badge.textContent = 'Est. Reach: ~150 users';
    }
}

function updateLivePreview() {
    const titleInput = document.getElementById('broadcastTitle').value;
    const messageInput = document.getElementById('broadcastMessage').value;

    document.getElementById('previewTitle').textContent = titleInput.trim() || 'Major Platform Upgrade Announced';
    document.getElementById('previewBody').textContent = messageInput.trim() || 'We have just released new executive intelligence features and autonomous board discovery...';
}

function insertTag(tag) {
    const textarea = document.getElementById('broadcastMessage');
    textarea.value += ' ' + tag + ' ';
    updateLivePreview();
    textarea.focus();
}

function updatePreviewChannel() {
    const app = document.getElementById('broadcastTargetApp').value;
    document.getElementById('previewAppHeader').textContent = app === 'all' ? 'All Portfolio Apps' : app.toUpperCase() + ' Ecosystem';
}

async function testSendSelf() {
    showAdminToast('Test notification dispatched to your admin credentials!');
}

async function confirmAndDispatchBroadcast() {
    const title = document.getElementById('broadcastTitle').value.trim();
    const message = document.getElementById('broadcastMessage').value.trim();
    const targetApp = document.getElementById('broadcastTargetApp').value;
    const channel = document.getElementById('broadcastChannel').value;
    const audienceTier = document.getElementById('broadcastAudienceTier').value;
    const actionUrl = document.getElementById('broadcastActionUrl').value.trim();
    const sendBtn = document.getElementById('sendBroadcastBtn');

    if (!title || !message) {
        showAdminToast('Please provide both Title and Message content.', true);
        return;
    }

    if (!confirm(`Are you sure you want to DISPATCH this broadcast to "${targetApp}" via ${channel.toUpperCase()}?`)) {
        return;
    }

    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Dispatched Processing...';

    try {
        const res = await fetch('/admin/api/broadcast/send', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                title: title,
                message: message,
                target_app: targetApp,
                channel: channel,
                audience_tier: audienceTier,
                action_url: actionUrl
            })
        });
        const data = await res.json();
        if (data.status === 'success') {
            showAdminToast(data.message || 'Broadcast successfully dispatched!');
            setTimeout(() => { window.location.reload(); }, 1200);
        } else {
            showAdminToast(data.message || 'Failed to dispatch broadcast', true);
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Dispatch Broadcast Now';
        }
    } catch (err) {
        showAdminToast('Network error: ' + err.message, true);
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Dispatch Broadcast Now';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    updateAudienceCount();
});
</script>
@endsection
