@extends('admin.layout')

@section('content')
@include('component.submitSpinner')

<!-- Header Banner -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-2xl lg:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
            <i class="fa-solid fa-film text-emerald-400"></i>
            <span>Curated Video Content Engine</span>
        </h1>
        <p class="text-sm text-gray-300 mt-1">
            Automatically discover family-safe funny, wholesome, and YouTube videos to publish onto everyone's feed and spark community engagement.
        </p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <form action="{{ $adminPrefix }}/content-engine/fetch" method="POST" class="inline-flex">
            @csrf
            <button type="submit" data-spin="true" class="min-h-[44px] px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 active:scale-[0.98] text-white text-xs font-bold shadow-lg shadow-emerald-950/50 transition-all flex items-center gap-2 whitespace-nowrap flex-shrink-0">
                <i class="fa-solid fa-rotate"></i>
                <span>Fetch New Videos Now</span>
            </button>
        </form>
        <a href="{{ $adminPrefix }}/official-posts" class="min-h-[44px] text-xs bg-gray-800/90 hover:bg-gray-700 text-gray-200 px-3.5 py-2.5 rounded-xl border border-gray-700 transition-colors flex items-center gap-1.5 font-medium whitespace-nowrap flex-shrink-0">
            <i class="fa-solid fa-bullhorn text-emerald-400"></i>
            <span>Official Feed</span>
        </a>
        <a href="/member/profilePage" target="_blank" class="min-h-[44px] text-xs bg-gray-800/90 hover:bg-gray-700 text-gray-200 px-3.5 py-2.5 rounded-xl border border-gray-700 transition-colors flex items-center gap-1.5 font-medium whitespace-nowrap flex-shrink-0">
            <i class="fa-solid fa-arrow-up-right-from-square text-emerald-400"></i>
            <span>View Live Feed</span>
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
        <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 text-xl">
            <i class="fa-solid fa-layer-group"></i>
        </div>
        <div>
            <div class="text-2xl font-extrabold text-white">{{ number_format((int)($stats['queued_count'] ?? 0)) }}</div>
            <div class="text-xs text-gray-300 font-medium">Videos Ready in Queue</div>
        </div>
    </div>

    <div class="glass-panel p-5 rounded-2xl border border-gray-800 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 text-xl">
            <i class="fa-solid fa-paper-plane"></i>
        </div>
        <div>
            <div class="text-2xl font-extrabold text-white">{{ number_format((int)($stats['published_count'] ?? 0)) }}</div>
            <div class="text-xs text-gray-300 font-medium">Published to All Feeds</div>
        </div>
    </div>

    <div class="glass-panel p-5 rounded-2xl border border-gray-800 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 text-xl">
            <i class="fa-solid fa-rss"></i>
        </div>
        <div>
            <div class="text-2xl font-extrabold text-white">{{ number_format((int)($stats['active_sources_count'] ?? 0)) }}</div>
            <div class="text-xs text-gray-300 font-medium">Active Curated Sources</div>
        </div>
    </div>

    <div class="glass-panel p-5 rounded-2xl border border-gray-800 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-teal-500/10 border border-teal-500/20 flex items-center justify-center text-teal-400 text-xl">
            <i class="fa-solid fa-clock"></i>
        </div>
        <div>
            <div class="text-lg font-extrabold text-white">
                {{ !empty($settings['auto_publish_enabled']) ? 'Auto (' . (int)($settings['publish_interval_hours'] ?? 12) . 'h)' : 'Manual Review' }}
            </div>
            <div class="text-xs text-gray-300 font-medium">Publishing Cadence</div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-12">
    <!-- Left Column: Queued Videos & Quick Import -->
    <div class="lg:col-span-8 space-y-6">
        <!-- Quick Import Single Video URL -->
        <div class="glass-panel p-6 rounded-2xl border border-gray-800 space-y-4">
            <div class="flex items-center justify-between border-b border-gray-800 pb-3">
                <h2 class="text-base font-bold text-white flex items-center gap-2">
                    <i class="fa-brands fa-youtube text-rose-400"></i>
                    <span>Quick-Add Any YouTube or Vimeo Link</span>
                </h2>
                <span class="text-xs text-gray-400">Instant family-safety check &amp; oEmbed preview</span>
            </div>

            <form action="{{ $adminPrefix }}/content-engine/import" method="POST" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                @csrf
                <div class="sm:col-span-6">
                    <label for="importVideoUrl" class="block text-xs font-semibold text-gray-300 mb-1.5">Video URL</label>
                    <input id="importVideoUrl" type="url" name="video_url" required
                           placeholder="https://www.youtube.com/watch?v=..."
                           class="w-full min-h-[44px] px-3.5 py-2 rounded-xl bg-gray-900 border border-gray-700 text-white text-sm placeholder-gray-400 focus:outline-none focus:border-emerald-500">
                </div>
                <div class="sm:col-span-3">
                    <label for="importCategory" class="block text-xs font-semibold text-gray-300 mb-1.5">Category</label>
                    <select id="importCategory" name="category" class="w-full min-h-[44px] px-3 py-2 rounded-xl bg-gray-900 border border-gray-700 text-white text-sm focus:outline-none focus:border-emerald-500">
                        <option value="funny">Funny</option>
                        <option value="family">Family</option>
                        <option value="pets">Pets &amp; Animals</option>
                        <option value="wholesome">Wholesome</option>
                        <option value="kids">Kids &amp; Learning</option>
                    </select>
                </div>
                <div class="sm:col-span-3">
                    <button type="submit" data-spin="true" class="w-full min-h-[44px] px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:scale-[0.98] text-white text-xs font-bold transition-all flex items-center justify-center gap-2 whitespace-nowrap flex-shrink-0">
                        <i class="fa-solid fa-plus"></i>
                        <span>Add to Queue</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Curation Queue -->
        <div class="glass-panel p-6 rounded-2xl border border-gray-800 space-y-5">
            <div class="flex items-center justify-between border-b border-gray-800 pb-3">
                <h2 class="text-base font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-sparkles text-amber-400"></i>
                    <span>Curation Queue ({{ count($queued ?? []) }})</span>
                </h2>
                <span class="text-xs text-gray-300">Review, personalise the British English caption, and publish to everyone's feed</span>
            </div>

            @if(empty($queued))
                <div class="p-8 rounded-2xl bg-gray-900/60 border border-dashed border-gray-700 text-center space-y-3">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 text-2xl">
                        <i class="fa-solid fa-video"></i>
                    </div>
                    <h3 class="text-base font-bold text-white">Your video queue is clear</h3>
                    <p class="text-xs text-gray-300 max-w-md mx-auto">
                        Click <strong>Fetch New Videos Now</strong> to pull fresh funny and family-friendly clips from your active sources, or paste a YouTube link above.
                    </p>
                    <form action="{{ $adminPrefix }}/content-engine/fetch" method="POST" class="inline-flex pt-1">
                        @csrf
                        <button type="submit" data-spin="true" class="min-h-[44px] px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition-all flex items-center gap-2 whitespace-nowrap flex-shrink-0">
                            <i class="fa-solid fa-rotate"></i>
                            <span>Fetch New Videos Now</span>
                        </button>
                    </form>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($queued as $item)
                        <div class="p-4 rounded-2xl bg-gray-900/80 border border-gray-800 space-y-3" x-data="{ previewOpen: false }" x-cloak>
                            <div class="flex flex-col sm:flex-row gap-4">
                                <div class="sm:w-44 flex-shrink-0">
                                    <div class="relative rounded-xl overflow-hidden bg-black aspect-video border border-gray-800">
                                        @if(!empty($item['thumbnail_url']))
                                            <img src="{{ htmlspecialchars((string)$item['thumbnail_url']) }}" alt="{{ htmlspecialchars((string)$item['title']) }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-500">
                                                <i class="fa-solid fa-play-circle text-3xl"></i>
                                            </div>
                                        @endif
                                        <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md bg-gray-950/85 text-[11px] font-bold uppercase tracking-wide text-emerald-300 border border-emerald-500/30">
                                            {{ htmlspecialchars((string)($item['category'] ?? 'family')) }}
                                        </span>
                                    </div>
                                    <button type="button" @click="previewOpen = !previewOpen" class="mt-2 w-full min-h-[44px] px-3 py-1.5 rounded-xl bg-gray-800 hover:bg-gray-700 text-gray-200 text-xs font-semibold transition-colors flex items-center justify-center gap-1.5 whitespace-nowrap">
                                        <i class="fa-solid" :class="previewOpen ? 'fa-eye-slash' : 'fa-play'"></i>
                                        <span x-text="previewOpen ? 'Hide Preview' : 'Preview Video'"></span>
                                    </button>
                                </div>

                                <div class="flex-1 min-w-0 space-y-3">
                                    <div>
                                        <h3 class="text-sm font-bold text-white line-clamp-2" title="{{ htmlspecialchars((string)$item['title']) }}">
                                            {{ htmlspecialchars((string)$item['title']) }}
                                        </h3>
                                        <p class="text-xs text-gray-400 mt-0.5">
                                            {{ htmlspecialchars((string)($item['author_name'] ?: ($item['source_name'] ?: 'Curated Source'))) }}
                                            · <a href="{{ htmlspecialchars((string)$item['video_url']) }}" target="_blank" rel="noopener noreferrer" class="text-emerald-400 hover:underline">Open on {{ ucfirst((string)($item['provider'] ?? 'youtube')) }}</a>
                                        </p>
                                    </div>

                                    @php
                                        $isQueuedShort = \App\services\CuratedVideoEngineService::isLikelyShortVideo(
                                            (string)($item['video_url'] ?? ''),
                                            (string)($item['title'] ?? ''),
                                            (string)($item['suggested_caption'] ?? ''),
                                            (string)($item['author_name'] ?? '')
                                        );
                                    @endphp
                                    <div x-show="previewOpen" x-collapse class="rounded-xl overflow-hidden border border-gray-800 bg-black {{ $isQueuedShort ? 'aspect-[9/16] max-w-[320px] mx-auto' : 'aspect-video' }}">
                                        @if(($item['provider'] ?? '') === 'youtube')
                                            <iframe class="w-full h-full" src="https://www.youtube.com/embed/{{ htmlspecialchars((string)$item['external_video_id']) }}?rel=0" title="Video preview" allowfullscreen loading="lazy"></iframe>
                                        @elseif(($item['provider'] ?? '') === 'vimeo')
                                            <iframe class="w-full h-full" src="https://player.vimeo.com/video/{{ htmlspecialchars((string)$item['external_video_id']) }}" title="Video preview" allowfullscreen loading="lazy"></iframe>
                                        @endif
                                    </div>

                                    <form action="{{ $adminPrefix }}/content-engine/publish" method="POST" class="space-y-3">
                                        @csrf
                                        <input type="hidden" name="video_id" value="{{ (int)$item['id'] }}">
                                        <textarea name="caption" rows="3" class="w-full px-3 py-2 rounded-xl bg-gray-950 border border-gray-700 text-gray-100 text-xs leading-relaxed focus:outline-none focus:border-emerald-500">{{ (string)$item['suggested_caption'] }}</textarea>

                                        <div class="flex flex-wrap items-center gap-2">
                                            <button type="submit" data-spin="true" class="min-h-[44px] px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 active:scale-[0.98] text-white text-xs font-bold transition-all flex items-center gap-2 whitespace-nowrap flex-shrink-0">
                                                <i class="fa-solid fa-paper-plane"></i>
                                                <span>Publish to All Feeds</span>
                                            </button>
                                    </form>
                                            <form action="{{ $adminPrefix }}/content-engine/dismiss" method="POST" class="inline-flex">
                                                @csrf
                                                <input type="hidden" name="video_id" value="{{ (int)$item['id'] }}">
                                                <button type="submit" data-spin="true" class="min-h-[44px] px-3.5 py-2 rounded-xl bg-gray-800 hover:bg-gray-700 active:scale-[0.98] text-gray-200 border border-gray-700 text-xs font-semibold transition-all flex items-center gap-1.5 whitespace-nowrap flex-shrink-0">
                                                    <i class="fa-solid fa-xmark"></i>
                                                    <span>Dismiss</span>
                                                </button>
                                            </form>
                                        </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Right Column: Cadence Settings, Sources, & Published History -->
    <div class="lg:col-span-4 space-y-6">
        <!-- Auto-Publish Cadence Settings -->
        <div class="glass-panel p-6 rounded-2xl border border-gray-800 space-y-4">
            <h2 class="text-base font-bold text-white flex items-center gap-2 border-b border-gray-800 pb-3">
                <i class="fa-solid fa-sliders text-emerald-400"></i>
                <span>Automation &amp; Cadence</span>
            </h2>

            <form action="{{ $adminPrefix }}/content-engine/settings" method="POST" class="space-y-4">
                @csrf
                <label class="flex items-start gap-3 p-3 rounded-xl bg-gray-900/70 border border-gray-800 cursor-pointer">
                    <input type="checkbox" name="auto_publish_enabled" value="1" {{ !empty($settings['auto_publish_enabled']) ? 'checked' : '' }} class="mt-1 w-5 h-5 rounded border-gray-600 text-emerald-500 focus:ring-emerald-500">
                    <div>
                        <div class="text-xs font-bold text-white">Enable Scheduled Auto-Publish</div>
                        <div class="text-[11px] text-gray-300 mt-0.5">Automatically fetch and post 1 family-safe video onto everyone's feed on your chosen interval.</div>
                    </div>
                </label>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="publishInterval" class="block text-xs font-semibold text-gray-300 mb-1">Post Every (Hours)</label>
                        <input id="publishInterval" type="number" name="publish_interval_hours" min="1" max="168" value="{{ (int)($settings['publish_interval_hours'] ?? 12) }}" class="w-full min-h-[44px] px-3 py-2 rounded-xl bg-gray-900 border border-gray-700 text-white text-sm">
                    </div>
                    <div>
                        <label for="maxPerSource" class="block text-xs font-semibold text-gray-300 mb-1">Max / Source</label>
                        <input id="maxPerSource" type="number" name="max_queue_per_source" min="1" max="15" value="{{ (int)($settings['max_queue_per_source'] ?? 8) }}" class="w-full min-h-[44px] px-3 py-2 rounded-xl bg-gray-900 border border-gray-700 text-white text-sm">
                    </div>
                </div>

                <button type="submit" data-spin="true" class="w-full min-h-[44px] px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 active:scale-[0.98] text-white text-xs font-bold transition-all flex items-center justify-center gap-2 whitespace-nowrap">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Save Cadence Settings</span>
                </button>
            </form>
        </div>

        <!-- Video Sources Manager -->
        <div class="glass-panel p-6 rounded-2xl border border-gray-800 space-y-4">
            <h2 class="text-base font-bold text-white flex items-center gap-2 border-b border-gray-800 pb-3">
                <i class="fa-solid fa-rss text-indigo-400"></i>
                <span>Curated Video Sources</span>
            </h2>

            <form action="{{ $adminPrefix }}/content-engine/sources/add" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label for="sourceName" class="block text-xs font-semibold text-gray-300 mb-1">Source Name</label>
                    <input id="sourceName" type="text" name="name" required placeholder="e.g. Wholesome Family Laughs" class="w-full min-h-[44px] px-3 py-2 rounded-xl bg-gray-900 border border-gray-700 text-white text-xs">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label for="sourceType" class="block text-xs font-semibold text-gray-300 mb-1">Type</label>
                        <select id="sourceType" name="source_type" class="w-full min-h-[44px] px-2.5 py-2 rounded-xl bg-gray-900 border border-gray-700 text-white text-xs">
                            <option value="youtube_channel">YouTube Channel</option>
                            <option value="youtube_playlist">YouTube Playlist</option>
                            <option value="rss_feed">RSS Video Feed</option>
                        </select>
                    </div>
                    <div>
                        <label for="sourceCategory" class="block text-xs font-semibold text-gray-300 mb-1">Category</label>
                        <select id="sourceCategory" name="category" class="w-full min-h-[44px] px-2.5 py-2 rounded-xl bg-gray-900 border border-gray-700 text-white text-xs">
                            <option value="funny">Funny</option>
                            <option value="family">Family</option>
                            <option value="pets">Pets</option>
                            <option value="wholesome">Wholesome</option>
                            <option value="kids">Kids</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label for="sourceIdent" class="block text-xs font-semibold text-gray-300 mb-1">Channel ID, Playlist ID, or Feed URL</label>
                    <input id="sourceIdent" type="text" name="source_identifier" required placeholder="UC... or PL... or https://..." class="w-full min-h-[44px] px-3 py-2 rounded-xl bg-gray-900 border border-gray-700 text-white text-xs">
                </div>
                <button type="submit" data-spin="true" class="w-full min-h-[44px] px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:scale-[0.98] text-white text-xs font-bold transition-all flex items-center justify-center gap-2 whitespace-nowrap">
                    <i class="fa-solid fa-plus"></i>
                    <span>Add Video Source</span>
                </button>
            </form>

            <div class="space-y-2.5 pt-2">
                @foreach($sources ?? [] as $src)
                    <div class="p-3 rounded-xl bg-gray-900/70 border border-gray-800 flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-white truncate" title="{{ htmlspecialchars((string)$src['name']) }}">
                                {{ htmlspecialchars((string)$src['name']) }}
                            </div>
                            <div class="text-[11px] text-gray-400 truncate">
                                {{ htmlspecialchars((string)$src['category']) }} · {{ htmlspecialchars((string)$src['source_identifier']) }}
                            </div>
                            @if(!empty($src['last_error']))
                                <div class="text-[11px] text-amber-300 mt-0.5 truncate" title="{{ htmlspecialchars((string)$src['last_error']) }}">
                                    Note: {{ htmlspecialchars((string)$src['last_error']) }}
                                </div>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <form action="{{ $adminPrefix }}/content-engine/sources/toggle" method="POST" class="inline-flex">
                                @csrf
                                <input type="hidden" name="source_id" value="{{ (int)$src['id'] }}">
                                <input type="hidden" name="is_active" value="{{ !empty($src['is_active']) ? '0' : '1' }}">
                                <button type="submit" data-spin="true" class="min-h-[44px] min-w-[44px] px-2.5 py-1.5 rounded-xl text-xs font-semibold border transition-colors whitespace-nowrap {{ !empty($src['is_active']) ? 'bg-emerald-500/15 border-emerald-500/30 text-emerald-300' : 'bg-gray-800 border-gray-700 text-gray-300' }}">
                                    {{ !empty($src['is_active']) ? 'Active' : 'Paused' }}
                                </button>
                            </form>
                            <form action="{{ $adminPrefix }}/content-engine/sources/delete" method="POST" class="inline-flex">
                                @csrf
                                <input type="hidden" name="source_id" value="{{ (int)$src['id'] }}">
                                <button type="submit" data-spin="true" title="Remove source" class="min-h-[44px] min-w-[44px] rounded-xl bg-gray-800 hover:bg-rose-900/60 text-gray-300 hover:text-rose-200 border border-gray-700 transition-colors flex items-center justify-center">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Recently Published -->
        <div class="glass-panel p-6 rounded-2xl border border-gray-800 space-y-3">
            <h2 class="text-base font-bold text-white flex items-center gap-2 border-b border-gray-800 pb-3">
                <i class="fa-solid fa-clock-rotate-left text-teal-400"></i>
                <span>Recently Published</span>
            </h2>
            @if(empty($published))
                <p class="text-xs text-gray-400">No curated videos have been published yet.</p>
            @else
                <div class="space-y-2.5">
                    @foreach(array_slice($published, 0, 5) as $pub)
                        <div class="p-3 rounded-xl bg-gray-900/60 border border-gray-800">
                            <div class="text-xs font-semibold text-white line-clamp-2" title="{{ htmlspecialchars((string)$pub['title']) }}">
                                {{ htmlspecialchars((string)$pub['title']) }}
                            </div>
                            <div class="text-[11px] text-gray-400 mt-1">
                                Post #{{ (int)($pub['published_post_no'] ?? 0) }} · {{ htmlspecialchars((string)($pub['published_at'] ?? '')) }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
