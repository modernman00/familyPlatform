@extends('admin.layout')

@section('content')
@include('component.submitSpinner')

<!-- Header Banner -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-2xl lg:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
            <i class="fa-solid fa-bullhorn text-emerald-400"></i>
            <span>FamilyPlatform Official Feed &amp; Updates</span>
        </h1>
        <p class="text-sm text-gray-400 mt-1">
            Broadcast warm, exciting updates and milestone announcements directly to every family member's feed.
        </p>
    </div>
    <div class="flex items-center gap-2">
        <a href="/member/profilePage" target="_blank" class="text-xs bg-gray-800/90 hover:bg-gray-700 text-gray-200 px-3.5 py-2 rounded-xl border border-gray-700 transition-colors flex items-center gap-1.5 font-medium">
            <i class="fa-solid fa-arrow-up-right-from-square text-emerald-400"></i>
            <span>View Live Member Feed</span>
        </a>
    </div>
</div>

@if(!empty($_SESSION['success']))
    <div class="mb-6 p-4 rounded-xl bg-emerald-950/60 border border-emerald-500/40 text-emerald-200 text-sm flex items-center gap-3 shadow-sm">
        <i class="fa-solid fa-circle-check text-emerald-400 text-base"></i>
        <span>{{ $_SESSION['success'] }}</span>
        @php unset($_SESSION['success']); @endphp
    </div>
@endif

@if(!empty($_SESSION['error']))
    <div class="mb-6 p-4 rounded-xl bg-rose-950/60 border border-rose-500/40 text-rose-200 text-sm flex items-center gap-3 shadow-sm">
        <i class="fa-solid fa-triangle-exclamation text-rose-400 text-base"></i>
        <span>{{ $_SESSION['error'] }}</span>
        @php unset($_SESSION['error']); @endphp
    </div>
@endif

<!-- Aggregate Stats Row -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="glass-panel p-5 rounded-2xl border border-gray-800 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 text-xl">
            <i class="fa-solid fa-bullhorn"></i>
        </div>
        <div>
            <div class="text-2xl font-extrabold text-white">{{ number_format($stats['total_posts'] ?? 0) }}</div>
            <div class="text-xs text-gray-400 font-medium">Published Broadcasts</div>
        </div>
    </div>

    <div class="glass-panel p-5 rounded-2xl border border-gray-800 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 text-xl">
            <i class="fa-solid fa-users"></i>
        </div>
        <div>
            <div class="text-2xl font-extrabold text-white">100% Reach</div>
            <div class="text-xs text-gray-400 font-medium">Delivered to All Feeds</div>
        </div>
    </div>

    <div class="glass-panel p-5 rounded-2xl border border-gray-800 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 text-xl">
            <i class="fa-solid fa-heart"></i>
        </div>
        <div>
            <div class="text-2xl font-extrabold text-white">{{ number_format($stats['total_likes'] ?? 0) }}</div>
            <div class="text-xs text-gray-400 font-medium">Total Member Reactions</div>
        </div>
    </div>

    <div class="glass-panel p-5 rounded-2xl border border-gray-800 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-teal-500/10 border border-teal-500/20 flex items-center justify-center text-teal-400 text-xl">
            <i class="fa-solid fa-comments"></i>
        </div>
        <div>
            <div class="text-2xl font-extrabold text-white">{{ number_format($stats['total_comments'] ?? 0) }}</div>
            <div class="text-xs text-gray-400 font-medium">Community Comments</div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-12">
    <!-- Left Column: Friendly Update Composer -->
    <div class="lg:col-span-7 space-y-6">
        <div class="glass-panel p-6 rounded-2xl border border-gray-800 space-y-5">
            <div class="flex items-center justify-between border-b border-gray-800 pb-4">
                <div class="flex items-center gap-2.5">
                    <img src="/images/logo/logo.png" alt="FamilyPlatform Logo" class="w-8 h-8 rounded-full border border-emerald-500/40 object-contain bg-white/10 p-0.5">
                    <div>
                        <h2 class="text-base font-bold text-white flex items-center gap-2">
                            <span>Post as FamilyPlatform</span>
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 font-semibold">
                                <i class="fa-solid fa-badge-check text-[10px] mr-1"></i>Official Verified
                            </span>
                        </h2>
                        <p class="text-xs text-gray-400">This post will automatically appear on everyone's feed</p>
                    </div>
                </div>
            </div>

            <!-- Jargon Eliminator / Friendly Copy Summariser -->
            <div class="p-4 rounded-xl bg-gray-900/80 border border-gray-800 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-emerald-400 flex items-center gap-1.5">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                        <span>Friendly Update Summariser (No Technical Jargon)</span>
                    </span>
                    <span class="text-[11px] text-gray-400">Translates technical notes for families</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    <button type="button" onclick="selectPreset('feature')" class="preset-btn px-2.5 py-1.5 rounded-lg text-xs font-medium border border-gray-700 bg-gray-800 text-gray-200 hover:border-emerald-500 transition-colors" data-preset="feature">
                        ✨ New Feature
                    </button>
                    <button type="button" onclick="selectPreset('milestone')" class="preset-btn px-2.5 py-1.5 rounded-lg text-xs font-medium border border-gray-700 bg-gray-800 text-gray-200 hover:border-emerald-500 transition-colors" data-preset="milestone">
                        🎉 Milestone
                    </button>
                    <button type="button" onclick="selectPreset('tip')" class="preset-btn px-2.5 py-1.5 rounded-lg text-xs font-medium border border-gray-700 bg-gray-800 text-gray-200 hover:border-emerald-500 transition-colors" data-preset="tip">
                        💡 Helpful Tip
                    </button>
                    <button type="button" onclick="selectPreset('security')" class="preset-btn px-2.5 py-1.5 rounded-lg text-xs font-medium border border-gray-700 bg-gray-800 text-gray-200 hover:border-emerald-500 transition-colors" data-preset="security">
                        🛡️ Peace of Mind
                    </button>
                </div>

                <textarea id="rawTechNotes" rows="3" placeholder="Paste technical release notes, code changes, or raw bullet points here..." class="w-full bg-gray-950 border border-gray-800 rounded-lg p-3 text-xs text-gray-200 focus:outline-none focus:border-emerald-500 custom-scrollbar"></textarea>

                <div class="flex justify-end">
                    <button type="button" id="summariseBtn" onclick="runFriendlySummarise()" class="text-xs bg-emerald-600 hover:bg-emerald-500 text-white px-3.5 py-1.5 rounded-lg font-semibold transition-colors flex items-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-sparkles"></i>
                        <span>Summarise for Families</span>
                    </button>
                </div>
            </div>

            <!-- Composer Form -->
            <form id="officialPostForm" action="{{ $adminPrefix }}/official-posts/publish" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $_SESSION['token'] ?? '' }}">

                <!-- Post Message Content -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-300 mb-2">
                        Post Announcement Message
                    </label>
                    <textarea name="postMessage" id="officialPostMessage" rows="6" oninput="updateLivePreview()" placeholder="Write your warm, exciting message for families here..." required class="w-full bg-gray-900 border border-gray-700 rounded-xl p-4 text-sm text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 custom-scrollbar leading-relaxed"></textarea>
                </div>

                <!-- Image Attachment Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-300 mb-2">
                            Image URL / Banner Path (Optional)
                        </label>
                        <input type="text" name="img_url" id="officialImgUrl" oninput="updateLivePreview()" placeholder="e.g. /images/family-hub-hero.webp or https://..." class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-300 mb-2">
                            Or Upload Photo / Graphic
                        </label>
                        <input type="file" name="post_img" id="officialImgFile" accept="image/*" onchange="previewUploadedImage(this)" class="w-full bg-gray-900 border border-gray-700 rounded-xl px-3 py-2 text-xs text-gray-300 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-600 file:text-white hover:file:bg-emerald-500 cursor-pointer">
                    </div>
                </div>

                <!-- Interactive Poll Toggle -->
                <div class="pt-2">
                    <button type="button" onclick="togglePollSection()" class="text-xs text-emerald-400 hover:text-emerald-300 font-semibold flex items-center gap-1.5">
                        <i class="fa-solid fa-square-poll-vertical"></i>
                        <span id="pollToggleText">+ Attach a Family Opinion Poll (Optional)</span>
                    </button>
                    <div id="pollSection" class="hidden mt-3 p-4 rounded-xl bg-gray-900/90 border border-gray-800 space-y-3">
                        <div>
                            <label class="block text-xs text-gray-300 font-medium mb-1">Poll Question</label>
                            <input type="text" name="poll_question" placeholder="e.g. Which new feature is your family most excited to try?" class="w-full bg-gray-950 border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:border-emerald-500">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-xs text-gray-300 font-medium">Poll Choices</label>
                            <input type="text" name="poll_options[]" placeholder="Choice 1 (e.g. Living Family Tree)" class="w-full bg-gray-950 border border-gray-700 rounded-lg px-3 py-1.5 text-xs text-white">
                            <input type="text" name="poll_options[]" placeholder="Choice 2 (e.g. Voice Audio Capsule)" class="w-full bg-gray-950 border border-gray-700 rounded-lg px-3 py-1.5 text-xs text-white">
                            <input type="text" name="poll_options[]" placeholder="Choice 3 (Optional)" class="w-full bg-gray-950 border border-gray-700 rounded-lg px-3 py-1.5 text-xs text-white">
                        </div>
                    </div>
                </div>

                <!-- Publish Action -->
                <div class="pt-3 flex items-center justify-between border-t border-gray-800">
                    <span class="text-xs text-gray-400 flex items-center gap-1">
                        <i class="fa-solid fa-shield-check text-emerald-400"></i>
                        <span>Instantly visible on 100% of member feeds</span>
                    </span>

                    <button type="submit" id="publishBtn" onclick="ScoreTenantSpinner.start(this)" class="bg-gradient-to-r from-emerald-600 via-teal-600 to-indigo-600 hover:from-emerald-500 hover:to-indigo-500 text-white font-bold text-sm px-6 py-2.5 rounded-xl shadow-lg shadow-emerald-600/30 transition-all hover:scale-[1.02] flex items-center gap-2">
                        <i class="fa-solid fa-paper-plane text-xs"></i>
                        <span>Publish to Everyone's Feed</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Column: Live Member Feed Preview -->
    <div class="lg:col-span-5 space-y-6">
        <div class="glass-panel p-6 rounded-2xl border border-gray-800 sticky top-24">
            <div class="flex items-center justify-between border-b border-gray-800 pb-3 mb-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-300 flex items-center gap-2">
                    <i class="fa-solid fa-eye text-emerald-400"></i>
                    <span>Live Member Feed Preview</span>
                </h3>
                <span class="text-[11px] text-gray-400">How families will see this</span>
            </div>

            <!-- Simulated Feed Card -->
            <div class="bg-white text-gray-900 rounded-xl p-4 shadow-md border-l-4 border-emerald-600 space-y-3">
                <div class="flex items-center gap-3">
                    <img src="/images/logo/logo.png" alt="Platform" class="w-10 h-10 rounded-full border border-gray-200 object-contain p-0.5 bg-gray-50">
                    <div>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <h4 class="font-bold text-sm text-gray-900 leading-none">FamilyPlatform</h4>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold text-white shadow-2xs" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
                                <i class="fa-solid fa-circle-check text-[9px]"></i>
                                <span>Official Update</span>
                            </span>
                        </div>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-[11px] text-gray-500">Just now</span>
                            <span class="inline-block px-2 py-0.2 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-800 border border-emerald-200">
                                ✨ Platform Announcement
                            </span>
                        </div>
                    </div>
                </div>

                <div id="previewMessageBody" class="text-xs text-gray-800 leading-relaxed whitespace-pre-wrap">
                    ✨ Welcome to FamilyPlatform Updates! Friendly announcements and community moments will appear here.
                </div>

                <div id="previewImageWrapper" class="hidden rounded-lg overflow-hidden border border-gray-200">
                    <img id="previewImageTag" src="" alt="Post Attachment" class="w-full max-h-48 object-cover">
                </div>

                <!-- Simulated Action Buttons -->
                <div class="flex items-center justify-between pt-2 border-t border-gray-100 text-gray-500 text-xs">
                    <span class="flex items-center gap-1.5 font-medium"><i class="fa-regular fa-heart text-emerald-600"></i> React</span>
                    <span class="flex items-center gap-1.5 font-medium"><i class="fa-regular fa-comment"></i> Comment</span>
                    <span class="flex items-center gap-1.5 font-medium"><i class="fa-solid fa-share-nodes"></i> Share</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Past Official Announcements History Table -->
<div class="glass-panel p-6 rounded-2xl border border-gray-800 space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-800 pb-4">
        <div>
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-emerald-400"></i>
                <span>Past Official Announcements</span>
            </h3>
            <p class="text-xs text-gray-400">Manage, edit, or remove broadcast posts from everyone's feed</p>
        </div>
        <span class="text-xs px-3 py-1 rounded-full bg-gray-800 border border-gray-700 text-gray-300 font-medium">
            {{ count($posts) }} Announcements on Feed
        </span>
    </div>

    @if(empty($posts))
        <div class="text-center py-12">
            <div class="w-14 h-14 rounded-full bg-gray-800/80 border border-gray-700 flex items-center justify-center text-gray-400 text-2xl mx-auto mb-3">
                <i class="fa-solid fa-bullhorn"></i>
            </div>
            <h4 class="text-sm font-semibold text-gray-200">No official announcements published yet</h4>
            <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">
                Use the composer above to share exciting news, milestones, or helpful tips with all families on the platform.
            </p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-gray-800 text-gray-400 uppercase tracking-wider font-semibold text-[11px]">
                        <th class="py-3 px-4">Post #</th>
                        <th class="py-3 px-4">Announcement Content</th>
                        <th class="py-3 px-4">Media</th>
                        <th class="py-3 px-4">Views</th>
                        <th class="py-3 px-4">Reactions</th>
                        <th class="py-3 px-4">Comments</th>
                        <th class="py-3 px-4">Published Date</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/60">
                    @foreach($posts as $p)
                        <tr class="hover:bg-gray-900/40 transition-colors">
                            <td class="py-3 px-4 font-mono text-gray-400">#{{ $p['post_no'] }}</td>
                            <td class="py-3 px-4 max-w-md">
                                <div class="text-gray-200 font-medium line-clamp-2">
                                    {{ $p['postMessage'] }}
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                @if(!empty($p['post_img0']))
                                    <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px] font-medium">
                                        <i class="fa-solid fa-image mr-1"></i>Image
                                    </span>
                                @else
                                    <span class="text-gray-500 text-[11px]">None</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-semibold text-gray-300">
                                {{ number_format($p['views_count'] ?? 0) }}
                            </td>
                            <td class="py-3 px-4 font-semibold text-emerald-400">
                                <i class="fa-solid fa-heart text-[10px] mr-1"></i>{{ number_format($p['reactions_count'] ?? $p['post_likes'] ?? 0) }}
                            </td>
                            <td class="py-3 px-4 font-semibold text-teal-400">
                                <i class="fa-solid fa-comment text-[10px] mr-1"></i>{{ number_format($p['comments_count'] ?? 0) }}
                            </td>
                            <td class="py-3 px-4 text-gray-400 whitespace-nowrap">
                                {{ date('d M Y, H:i', strtotime($p['date_created'])) }}
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap space-x-1">
                                <button type="button" onclick="openEditModal({{ (int)$p['post_no'] }}, {{ json_encode($p['postMessage']) }})" class="px-2.5 py-1 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-200 border border-gray-700 text-xs font-medium transition-colors" title="Edit message">
                                    <i class="fa-solid fa-pen text-[10px] mr-1"></i>Edit
                                </button>
                                <button type="button" onclick="confirmDeletePost({{ (int)$p['post_no'] }})" class="px-2.5 py-1 rounded-lg bg-rose-950/60 hover:bg-rose-900 text-rose-300 border border-rose-800 text-xs font-medium transition-colors" title="Remove from all feeds">
                                    <i class="fa-solid fa-trash text-[10px] mr-1"></i>Delete
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<!-- Edit Post Modal -->
<div id="editPostModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-xs hidden p-4">
    <div class="glass-panel p-6 rounded-2xl border border-gray-700 max-w-lg w-full space-y-4">
        <div class="flex items-center justify-between border-b border-gray-800 pb-3">
            <h4 class="text-sm font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-emerald-400"></i>
                <span>Edit Official Announcement</span>
            </h4>
            <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-white">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>
        <form action="{{ $adminPrefix }}/official-posts/update" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $_SESSION['token'] ?? '' }}">
            <input type="hidden" name="post_no" id="editPostNo">
            <div>
                <label class="block text-xs font-semibold text-gray-300 mb-1">Message Content</label>
                <textarea name="postMessage" id="editPostMessage" rows="5" required class="w-full bg-gray-900 border border-gray-700 rounded-xl p-3 text-xs text-white focus:outline-none focus:border-emerald-500 custom-scrollbar"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-300 bg-gray-800 hover:bg-gray-700">Cancel</button>
                <button type="submit" onclick="ScoreTenantSpinner.start(this)" class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-500 flex items-center gap-1.5">
                    <i class="fa-solid fa-check"></i>
                    <span>Save Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deletePostModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-xs hidden p-4">
    <div class="glass-panel p-6 rounded-2xl border border-gray-700 max-w-md w-full space-y-4">
        <div class="flex items-center gap-3 text-rose-400">
            <div class="w-10 h-10 rounded-xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-lg">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <h4 class="text-sm font-bold text-white">Remove Announcement from Feed?</h4>
                <p class="text-xs text-gray-400">This post will be immediately removed from everyone's feed.</p>
            </div>
        </div>
        <form action="{{ $adminPrefix }}/official-posts/delete" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $_SESSION['token'] ?? '' }}">
            <input type="hidden" name="post_no" id="deletePostNo">
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-300 bg-gray-800 hover:bg-gray-700">Cancel</button>
                <button type="submit" onclick="ScoreTenantSpinner.start(this)" class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-rose-600 hover:bg-rose-500 flex items-center gap-1.5">
                    <i class="fa-solid fa-trash"></i>
                    <span>Remove from Feed</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentPreset = 'feature';

function selectPreset(preset) {
    currentPreset = preset;
    document.querySelectorAll('.preset-btn').forEach(btn => {
        if (btn.dataset.preset === preset) {
            btn.classList.add('border-emerald-500', 'bg-emerald-950/40', 'text-emerald-300');
        } else {
            btn.classList.remove('border-emerald-500', 'bg-emerald-950/40', 'text-emerald-300');
        }
    });
}
selectPreset('feature');

function updateLivePreview() {
    const msg = document.getElementById('officialPostMessage').value.trim();
    const previewBody = document.getElementById('previewMessageBody');
    previewBody.textContent = msg || '✨ Welcome to FamilyPlatform Updates! Friendly announcements and community moments will appear here.';

    const imgUrl = document.getElementById('officialImgUrl').value.trim();
    const imgWrapper = document.getElementById('previewImageWrapper');
    const imgTag = document.getElementById('previewImageTag');

    if (imgUrl) {
        imgTag.src = imgUrl;
        imgWrapper.classList.remove('hidden');
    } else {
        const fileInput = document.getElementById('officialImgFile');
        if (!fileInput.files || !fileInput.files[0]) {
            imgWrapper.classList.add('hidden');
        }
    }
}

function previewUploadedImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const imgWrapper = document.getElementById('previewImageWrapper');
            const imgTag = document.getElementById('previewImageTag');
            imgTag.src = e.target.result;
            imgWrapper.classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function togglePollSection() {
    const pollSec = document.getElementById('pollSection');
    const toggleTxt = document.getElementById('pollToggleText');
    if (pollSec.classList.contains('hidden')) {
        pollSec.classList.remove('hidden');
        toggleTxt.textContent = '− Remove Poll';
    } else {
        pollSec.classList.add('hidden');
        toggleTxt.textContent = '+ Attach a Family Opinion Poll (Optional)';
    }
}

async function runFriendlySummarise() {
    const rawNotes = document.getElementById('rawTechNotes').value.trim();
    if (!rawNotes) {
        alert('Please paste some update notes or bullet points to summarise.');
        return;
    }

    const btn = document.getElementById('summariseBtn');
    if (window.ScoreTenantSpinner) {
        ScoreTenantSpinner.start(btn);
    }

    try {
        const token = document.querySelector('input[name="token"]')?.value || '';
        const res = await fetch('{{ $adminPrefix }}/official-posts/summarise', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                token: token,
                raw_notes: rawNotes,
                preset: currentPreset
            })
        });

        const data = await res.json();
        if (data.status === 'success' && data.friendly_copy) {
            document.getElementById('officialPostMessage').value = data.friendly_copy;
            updateLivePreview();
        } else {
            alert(data.message || 'Could not generate summary.');
        }
    } catch (err) {
        console.error('Summarise error:', err);
        alert('Failed to connect to summariser service.');
    } finally {
        if (window.ScoreTenantSpinner) {
            ScoreTenantSpinner.stop(btn);
        }
    }
}

function openEditModal(postNo, message) {
    document.getElementById('editPostNo').value = postNo;
    document.getElementById('editPostMessage').value = message;
    document.getElementById('editPostModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editPostModal').classList.add('hidden');
}

function confirmDeletePost(postNo) {
    document.getElementById('deletePostNo').value = postNo;
    document.getElementById('deletePostModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deletePostModal').classList.add('hidden');
}
</script>
@endsection
