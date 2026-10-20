@extends('admin.layout')

@section('content')
<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-2xl lg:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
            <i class="fa-solid fa-bullhorn text-purple-400"></i>
            <span>Campaign & Lifecycle Orchestrator</span>
        </h1>
        <p class="text-sm text-gray-400 mt-1">Schedule and automate user onboarding, retention drips, and re-engagement campaigns across apps.</p>
    </div>

    <button onclick="openNewCampaignModal()" class="inline-flex items-center gap-2 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-semibold text-xs px-4 py-2.5 rounded-xl shadow-lg shadow-purple-600/25 transition-all hover:scale-[1.02]">
        <i class="fa-solid fa-plus text-xs"></i>
        <span>Create New Campaign</span>
    </button>
</div>

<!-- Campaigns List Table -->
<div class="glass-panel p-6 rounded-2xl border border-gray-800">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-base font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-calendar-check text-indigo-400"></i>
                <span>Active & Scheduled Marketing Drips</span>
            </h2>
            <p class="text-xs text-gray-400">Automated lifecycle workflows</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="border-b border-gray-800 text-gray-400">
                    <th class="pb-3 font-semibold">Campaign Title</th>
                    <th class="pb-3 font-semibold">Apps</th>
                    <th class="pb-3 font-semibold">Channel</th>
                    <th class="pb-3 font-semibold">Audience</th>
                    <th class="pb-3 font-semibold">Scheduled At</th>
                    <th class="pb-3 font-semibold">Status</th>
                    <th class="pb-3 font-semibold text-right">Performance</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800/60">
                <?php if (empty($campaigns)): ?>
                    <tr>
                        <td colspan="7" class="py-10 text-center text-gray-500">
                            <i class="fa-solid fa-bullhorn text-3xl mb-2 text-gray-600"></i>
                            <div>No automated campaigns created yet. Click "Create New Campaign" to start!</div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($campaigns as $c): ?>
                        <tr class="hover:bg-gray-900/40 transition-colors">
                            <td class="py-3.5 font-bold text-white text-sm">
                                <?= htmlspecialchars($c['title']) ?>
                                <div class="text-[11px] text-gray-400 font-normal mt-0.5"><?= htmlspecialchars($c['subject'] ?? '') ?></div>
                            </td>
                            <td class="py-3.5">
                                <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold bg-gray-800 text-gray-300 border border-gray-700">
                                    <?= htmlspecialchars($c['target_apps']) ?>
                                </span>
                            </td>
                            <td class="py-3.5">
                                <span class="px-2 py-0.5 rounded-full text-[10px] uppercase font-semibold bg-purple-500/20 text-purple-300 border border-purple-500/30">
                                    <?= htmlspecialchars($c['channel']) ?>
                                </span>
                            </td>
                            <td class="py-3.5 text-gray-300 font-medium capitalize"><?= htmlspecialchars($c['audience_filter']) ?></td>
                            <td class="py-3.5 text-gray-400"><?= htmlspecialchars($c['scheduled_at'] ?? 'Immediate') ?></td>
                            <td class="py-3.5">
                                <span class="px-2 py-0.5 rounded-full text-[10px] uppercase font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                    <?= htmlspecialchars($c['status']) ?>
                                </span>
                            </td>
                            <td class="py-3.5 text-right font-medium text-gray-300">
                                <span class="text-emerald-400 font-bold"><?= (int)$c['delivered_count'] ?></span> sent &middot; <span class="text-indigo-400"><?= (int)$c['open_count'] ?></span> opens
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create Campaign Modal -->
<div id="newCampaignModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="glass-panel max-w-xl w-full rounded-2xl border border-gray-700 p-6 shadow-2xl relative">
        <div class="flex items-center justify-between border-b border-gray-800 pb-4 mb-4">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-calendar-plus text-purple-400"></i>
                <span>Create Marketing & Onboarding Campaign</span>
            </h3>
            <button onclick="closeNewCampaignModal()" class="text-gray-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <form id="createCampaignForm" onsubmit="submitNewCampaign(event)" class="space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-xs font-semibold uppercase text-gray-300 mb-1">Campaign Title</label>
                <input type="text" id="campaignTitle" required placeholder="e.g. 7-Day Executive Onboarding Drip" class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-2 text-sm text-white focus:outline-none focus:border-purple-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-300 mb-1">Target Application</label>
                    <select id="campaignTargetApp" class="w-full bg-gray-900 border border-gray-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-purple-500">
                        <option value="all">All Portfolio Apps</option>
                        <?php foreach ($apps as $k => $a): ?>
                            <option value="<?= $k ?>"><?= htmlspecialchars($a['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-300 mb-1">Channel</label>
                    <select id="campaignChannel" class="w-full bg-gray-900 border border-gray-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-purple-500">
                        <option value="email">Email Digest</option>
                        <option value="push">Web Push</option>
                        <option value="in_app">In-App Notification</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase text-gray-300 mb-1">Subject Line</label>
                <input type="text" id="campaignSubject" placeholder="Email subject or push alert headline" class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-2 text-sm text-white focus:outline-none focus:border-purple-500">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase text-gray-300 mb-1">Campaign Body Content</label>
                <textarea id="campaignBody" rows="4" required placeholder="Campaign message template..." class="w-full bg-gray-900 border border-gray-700 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-purple-500"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-300 mb-1">Audience Filter</label>
                    <select id="campaignAudience" class="w-full bg-gray-900 border border-gray-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-purple-500">
                        <option value="all">All Users</option>
                        <option value="active">Active Users Only</option>
                        <option value="trial">Trial Users</option>
                        <option value="dormant">Dormant (>30 Days)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-300 mb-1">Schedule Date & Time</label>
                    <input type="datetime-local" id="campaignScheduledAt" class="w-full bg-gray-900 border border-gray-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-purple-500">
                </div>
            </div>

            <div class="pt-3 flex justify-end gap-3">
                <button type="button" onclick="closeNewCampaignModal()" class="px-4 py-2 rounded-xl bg-gray-800 text-gray-300 text-xs font-semibold hover:bg-gray-700">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold shadow-lg shadow-purple-600/30">Schedule Campaign</button>
            </div>
        </form>
    </div>
</div>

<script>
function openNewCampaignModal() {
    document.getElementById('newCampaignModal').classList.remove('hidden');
}
function closeNewCampaignModal() {
    document.getElementById('newCampaignModal').classList.add('hidden');
}

async function submitNewCampaign(e) {
    e.preventDefault();
    const title = document.getElementById('campaignTitle').value.trim();
    const targetApps = document.getElementById('campaignTargetApp').value;
    const channel = document.getElementById('campaignChannel').value;
    const subject = document.getElementById('campaignSubject').value.trim();
    const body = document.getElementById('campaignBody').value.trim();
    const audienceFilter = document.getElementById('campaignAudience').value;
    const scheduledAt = document.getElementById('campaignScheduledAt').value;

    try {
        const res = await fetch('/admin/api/campaigns', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                title: title,
                target_apps: targetApps,
                channel: channel,
                subject: subject,
                body: body,
                audience_filter: audienceFilter,
                scheduled_at: scheduledAt
            })
        });
        const data = await res.json();
        if (data.status === 'success') {
            showAdminToast(data.message || 'Campaign scheduled successfully!');
            closeNewCampaignModal();
            setTimeout(() => { window.location.reload(); }, 1000);
        } else {
            showAdminToast(data.message || 'Failed to schedule campaign', true);
        }
    } catch (err) {
        showAdminToast('Error: ' + err.message, true);
    }
}
</script>
@endsection
