@extends('admin.layout')

@section('content')
<!-- Header Welcome & Actions -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-2xl lg:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
            <i class="fa-solid fa-magnifying-glass-chart text-emerald-400"></i>
            <span>Programmatic SEO (pSEO) Matrix &amp; CMS</span>
        </h1>
        <p class="text-sm text-gray-400 mt-1">
            Dynamic content management engine for high-intent oral history guides, celebration checklists, and machine-readable Answer Engine Optimization.
        </p>
    </div>
    
    <div class="flex items-center gap-3">
        <a href="#create-guide-panel" class="inline-flex items-center gap-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-semibold text-xs px-4 py-2.5 rounded-xl shadow-lg shadow-emerald-600/25 transition-all hover:scale-[1.02]">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Create New Programmatic Page</span>
        </a>
        <a href="/prompts" target="_blank" class="inline-flex items-center gap-2 bg-gray-800 hover:bg-gray-700 text-gray-200 font-semibold text-xs px-4 py-2.5 rounded-xl border border-gray-700 transition-all">
            <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
            <span>Public Prompts Hub</span>
        </a>
    </div>
</div>

@if(!empty($_SESSION['success']))
    <div class="mb-6 p-4 rounded-xl bg-emerald-950/40 border border-emerald-500/40 text-emerald-300 text-xs flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-400 text-base"></i>
            <span><?= htmlspecialchars($_SESSION['success']) ?></span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-200"><i class="fa-solid fa-xmark"></i></button>
    </div>
    @php unset($_SESSION['success']); @endphp
@endif

@if(!empty($_SESSION['error']))
    <div class="mb-6 p-4 rounded-xl bg-rose-950/40 border border-rose-500/40 text-rose-300 text-xs flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation text-rose-400 text-base"></i>
            <span><?= htmlspecialchars($_SESSION['error']) ?></span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-rose-400 hover:text-rose-200"><i class="fa-solid fa-xmark"></i></button>
    </div>
    @php unset($_SESSION['error']); @endphp
@endif

@php
    $totalFamily = count($familyGuides ?? []);
    $totalParty = count($partyGuides ?? []);
    $totalQuestions = 0;
    foreach ($familyGuides as $fg) {
        foreach ($fg['phases'] ?? [] as $phase) {
            $totalQuestions += count($phase['questions'] ?? []);
        }
    }
@endphp

<!-- KPI Overview Ribbons -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <div class="glass-panel p-4 rounded-2xl">
        <div class="flex items-center justify-between text-gray-400 mb-2">
            <span class="text-xs font-semibold uppercase tracking-wider">FamilyPlatform pSEO</span>
            <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                <i class="fa-solid fa-sitemap text-sm"></i>
            </div>
        </div>
        <div class="text-2xl font-black text-white"><?= $totalFamily ?> Pages</div>
        <div class="text-[11px] text-emerald-400 font-medium mt-1">Live in /prompts/*</div>
    </div>

    <div class="glass-panel p-4 rounded-2xl">
        <div class="flex items-center justify-between text-gray-400 mb-2">
            <span class="text-xs font-semibold uppercase tracking-wider">PartyPlatform pSEO</span>
            <div class="w-8 h-8 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center">
                <i class="fa-solid fa-champagne-glasses text-sm"></i>
            </div>
        </div>
        <div class="text-2xl font-black text-white"><?= $totalParty ?> Pages</div>
        <div class="text-[11px] text-purple-400 font-medium mt-1">Live in /plan/*</div>
    </div>

    <div class="glass-panel p-4 rounded-2xl">
        <div class="flex items-center justify-between text-gray-400 mb-2">
            <span class="text-xs font-semibold uppercase tracking-wider">Indexed Prompts</span>
            <div class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center">
                <i class="fa-solid fa-microphone-lines text-sm"></i>
            </div>
        </div>
        <div class="text-2xl font-black text-white"><?= $totalQuestions ?> Questions</div>
        <div class="text-[11px] text-indigo-400 font-medium mt-1">Structured for LLM citations</div>
    </div>

    <div class="glass-panel p-4 rounded-2xl">
        <div class="flex items-center justify-between text-gray-400 mb-2">
            <span class="text-xs font-semibold uppercase tracking-wider">GEO Sitemap Status</span>
            <div class="w-8 h-8 rounded-lg bg-teal-500/10 text-teal-400 flex items-center justify-center">
                <i class="fa-solid fa-bolt text-sm"></i>
            </div>
        </div>
        <div class="text-2xl font-black text-emerald-400">100% Synced</div>
        <div class="text-[11px] text-teal-400 font-medium mt-1">llms.txt + sitemap.xml</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-10">
    <!-- Active URL Registry Table (Left 7 Cols) -->
    <div class="lg:col-span-7 space-y-6">
        <div class="glass-panel rounded-2xl border border-gray-800 p-6">
            <div class="flex items-center justify-between gap-4 mb-4 flex-wrap">
                <div>
                    <h2 class="text-base font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-table-list text-emerald-400"></i>
                        <span>Programmatic URL Directory</span>
                    </h2>
                    <p class="text-xs text-gray-400 mt-0.5">Authoritative registry of search-targeted guides and machine manifests</p>
                </div>
                <div class="text-xs text-gray-400 font-mono">
                    Total: <?= $totalFamily + $totalParty ?> URLs
                </div>
            </div>

            <!-- Table of URLs -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-gray-800 text-gray-400 uppercase font-semibold text-[10px] tracking-wider bg-gray-950/40">
                            <th class="py-3 px-3">Live Path / Slug</th>
                            <th class="py-3 px-3">Category</th>
                            <th class="py-3 px-3">Payload</th>
                            <th class="py-3 px-3">Source</th>
                            <th class="py-3 px-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800/60">
                        @foreach ($familyGuides as $slug => $guide)
                            @php
                                $qCount = 0;
                                foreach ($guide['phases'] ?? [] as $p) {
                                    $qCount += count($p['questions'] ?? []);
                                }
                                $isCustom = !empty($guide['custom']);
                            @endphp
                            <tr class="hover:bg-gray-900/40 transition-colors group">
                                <td class="py-3 px-3">
                                    <div class="font-bold text-white flex items-center gap-1.5">
                                        <span class="text-emerald-400">/prompts/</span><?= htmlspecialchars($slug) ?>
                                    </div>
                                    <div class="text-[11px] text-gray-400 truncate max-w-xs mt-0.5" title="<?= htmlspecialchars($guide['title']) ?>">
                                        <?= htmlspecialchars($guide['title']) ?>
                                    </div>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/10 text-emerald-300 border border-emerald-500/20 whitespace-nowrap">
                                        <?= htmlspecialchars($guide['category'] ?? 'Heritage') ?>
                                    </span>
                                </td>
                                <td class="py-3 px-3 font-mono text-[11px] text-gray-300">
                                    <?= $qCount ?> prompts
                                </td>
                                <td class="py-3 px-3">
                                    @if ($isCustom)
                                        <span class="px-2 py-0.5 rounded text-[10px] bg-purple-500/20 text-purple-300 border border-purple-500/30">CMS Dynamic</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] bg-gray-800 text-gray-400">Built-in</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <a href="/prompts/<?= htmlspecialchars($slug) ?>" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-400 hover:text-emerald-300 px-2.5 py-1 rounded-lg bg-emerald-950/40 border border-emerald-500/30 transition-all hover:bg-emerald-900/50">
                                        <span>View</span>
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach

                        @foreach ($partyGuides as $slug => $pGuide)
                            @php
                                $mCount = count($pGuide['timeline'] ?? []);
                                $isCustom = !empty($pGuide['custom']);
                            @endphp
                            <tr class="hover:bg-gray-900/40 transition-colors group">
                                <td class="py-3 px-3">
                                    <div class="font-bold text-white flex items-center gap-1.5">
                                        <span class="text-purple-400">/plan/</span><?= htmlspecialchars($slug) ?>
                                    </div>
                                    <div class="text-[11px] text-gray-400 truncate max-w-xs mt-0.5" title="<?= htmlspecialchars($pGuide['title']) ?>">
                                        <?= htmlspecialchars($pGuide['title']) ?>
                                    </div>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-purple-500/10 text-purple-300 border border-purple-500/20 whitespace-nowrap">
                                        <?= htmlspecialchars($pGuide['category'] ?? 'Planning') ?>
                                    </span>
                                </td>
                                <td class="py-3 px-3 font-mono text-[11px] text-gray-300">
                                    <?= $mCount ?> milestones
                                </td>
                                <td class="py-3 px-3">
                                    @if ($isCustom)
                                        <span class="px-2 py-0.5 rounded text-[10px] bg-purple-500/20 text-purple-300 border border-purple-500/30">CMS Dynamic</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] bg-gray-800 text-gray-400">Built-in</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <a href="/plan/<?= htmlspecialchars($slug) ?>" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-semibold text-purple-400 hover:text-purple-300 px-2.5 py-1 rounded-lg bg-purple-950/40 border border-purple-500/30 transition-all hover:bg-purple-900/50">
                                        <span>View</span>
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Content Management System (Right 5 Cols) -->
    <div class="lg:col-span-5" id="create-guide-panel">
        <div class="glass-panel rounded-2xl border border-gray-800 p-6 sticky top-24">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-600 flex items-center justify-center text-white shadow-lg shadow-emerald-500/20">
                    <i class="fa-solid fa-pen-nib"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-white">Create New Programmatic Page</h2>
                    <p class="text-xs text-gray-400">Instant publish to live routing, sitemap, and LLM manifests</p>
                </div>
            </div>

            <form action="{{ $adminPrefix }}/pseo/create" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="platform" value="familyplatform">

                <!-- Title -->
                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-1">Page Title <span class="text-rose-400">*</span></label>
                    <input type="text" name="title" required placeholder="e.g. 40 Questions to Ask Your Grandfather About His Early Career" class="w-full bg-gray-900 border border-gray-700 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-emerald-500">
                </div>

                <!-- Slug -->
                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-1">URL Slug <span class="text-rose-400">*</span></label>
                    <div class="flex items-center">
                        <span class="inline-flex items-center px-3 py-2.5 rounded-l-xl border border-r-0 border-gray-700 bg-gray-950 text-gray-500 text-xs font-mono">/prompts/</span>
                        <input type="text" name="slug" required placeholder="grandfather-career-interview-questions" class="w-full bg-gray-900 border border-gray-700 rounded-r-xl px-3 py-2.5 text-xs text-white font-mono focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <!-- Category -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-300 mb-1">Thematic Category</label>
                        <select name="category" class="w-full bg-gray-900 border border-gray-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-emerald-500">
                            <option value="Grandparents &amp; Elders">Grandparents &amp; Elders</option>
                            <option value="Honour &amp; Service">Honour &amp; Service</option>
                            <option value="Heirlooms &amp; Culture">Heirlooms &amp; Culture</option>
                            <option value="Parents &amp; Origins">Parents &amp; Origins</option>
                            <option value="Heritage &amp; Migration">Heritage &amp; Migration</option>
                            <option value="Origins &amp; Community">Origins &amp; Community</option>
                            <option value="Family Customs">Family Customs</option>
                        </select>
                    </div>

                    <!-- Read Time -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-300 mb-1">Reading Time</label>
                        <input type="text" name="read_time" value="5 min read" class="w-full bg-gray-900 border border-gray-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <!-- Summary -->
                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-1">Summary (Answer Capsule / Lead Paragraph) <span class="text-rose-400">*</span></label>
                    <textarea name="summary" rows="2" required placeholder="A brief, high-empathy overview answering why this interview matters..." class="w-full bg-gray-900 border border-gray-700 rounded-xl p-3 text-xs text-white focus:outline-none focus:border-emerald-500"></textarea>
                </div>

                <!-- Meta Description -->
                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-1">Meta Description (SEO Snippet)</label>
                    <input type="text" name="meta_description" placeholder="150-160 characters designed to capture Google and Perplexity snippets" class="w-full bg-gray-900 border border-gray-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-emerald-500">
                </div>

                <!-- Curator Audio Tip -->
                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-1">Curator's Recording Tip (Amber Card)</label>
                    <input type="text" name="audio_tip" value="Position recording device 12 inches away on a soft cushion to avoid fabric rustle." class="w-full bg-gray-900 border border-gray-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-emerald-500">
                </div>

                <!-- Prompts List -->
                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-1">
                        Interview Prompts (1 question per line) <span class="text-rose-400">*</span>
                    </label>
                    <textarea name="questions_raw" rows="5" required placeholder="What was your first job and what was your first wage?&#10;Who taught you the trade or skill you were most proud of?&#10;What was the most challenging workday you ever experienced?" class="w-full bg-gray-900 border border-gray-700 rounded-xl p-3 text-xs text-white font-mono leading-relaxed focus:outline-none focus:border-emerald-500"></textarea>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-600 via-teal-600 to-indigo-600 hover:from-emerald-500 hover:to-indigo-500 text-white font-bold text-xs px-4 py-3 rounded-xl shadow-lg shadow-emerald-600/30 transition-all hover:scale-[1.01]" style="min-height: 44px;">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span>Publish New Programmatic Page &amp; Sync Sitemap</span>
                    </button>
                    <p class="text-[11px] text-gray-500 text-center mt-2">
                        Instantly updates sitemap.xml and generates sovereign oral history layout.
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
