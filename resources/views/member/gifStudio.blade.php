@extends('layouts.profileBase')

@section('title', 'Family Brand GIF Studio — FamilyPlatform')
@section('meta_description', 'Sovereign animated brand graphics and dynamic Dynasty GIF synthesiser by the Advanced Technology Group (ATG).')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700&family=Montserrat:wght@400;500;600;700&display=swap">
<style>
.gif-studio-wrap {
    --gs-primary: #a31621;
    --gs-primary-dark: #7a0e16;
    --gs-gold: #c9a24b;
    --gs-gold-bright: #ffd700;
    --gs-gold-light: #fef0c7;
    --gs-canvas: #faf8f5;
    --gs-card: #ffffff;
    --gs-ink: #0f172a;
    --gs-muted: #64748b;
    --gs-line: #e2e8f0;
    max-width: 1240px;
    margin: 0 auto;
    padding: 12px 16px 80px;
    color: var(--gs-ink);
}
.dark-mode .gif-studio-wrap {
    --gs-canvas: #0b0f19;
    --gs-card: #141a29;
    --gs-ink: #f8fafc;
    --gs-muted: #94a3b8;
    --gs-line: #1e293b;
}

/* Hero */
.gs-hero {
    position: relative;
    overflow: hidden;
    border-radius: 24px;
    padding: 32px 24px;
    margin-bottom: 28px;
    color: #ffffff;
    background: radial-gradient(85% 120% at 85% 15%, rgba(201,162,75,.35), transparent 65%),
                radial-gradient(70% 90% at 5% 95%, rgba(163,22,33,.8), transparent 70%),
                linear-gradient(135deg, #120306 0%, #29060d 50%, #4a0914 100%);
    box-shadow: 0 20px 45px -15px rgba(163,22,33,.45);
    border: 1px solid rgba(255,215,0,.25);
}
.gs-hero__eyebrow {
    font-family: 'Cinzel', Georgia, serif;
    font-size: 0.75rem;
    letter-spacing: 0.25em;
    text-transform: uppercase;
    color: var(--gs-gold-bright);
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.gs-hero h1 {
    font-family: 'Cinzel', Georgia, serif;
    font-weight: 700;
    font-size: clamp(1.8rem, 3.5vw, 2.6rem);
    line-height: 1.15;
    margin: 0 0 10px;
    color: #ffffff;
}
.gs-hero p {
    color: rgba(255,255,255,.85);
    max-width: 680px;
    font-size: 0.98rem;
    line-height: 1.55;
    margin: 0 0 18px;
}

/* Tabs */
.gs-nav-pills {
    display: inline-flex;
    background: rgba(0,0,0,.35);
    padding: 4px;
    border-radius: 999px;
    border: 1px solid rgba(255,215,0,.2);
    gap: 4px;
}
.gs-nav-btn {
    min-height: 44px;
    padding: 8px 20px;
    border-radius: 999px;
    font-size: 0.88rem;
    font-weight: 600;
    white-space: nowrap;
    cursor: pointer;
    border: none;
    background: transparent;
    color: rgba(255,255,255,.75);
    transition: all 0.2s ease;
}
.gs-nav-btn.active {
    background: linear-gradient(135deg, var(--gs-primary) 0%, var(--gs-primary-dark) 100%);
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(163,22,33,.4);
}

/* Grid & Cards */
.gs-cards-grid {
    display: grid;
    grid-template-columns: repeat(1, minmax(0, 1fr));
    gap: 24px;
}
@media (min-width: 640px) {
    .gs-cards-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (min-width: 1024px) {
    .gs-cards-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
}

.gs-gif-card {
    background: var(--gs-card);
    border: 1px solid var(--gs-line);
    border-radius: 20px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 4px 16px -4px rgba(15,23,42,.06);
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}
.gs-gif-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 16px 32px -8px rgba(163,22,33,.16);
    border-color: rgba(201,162,75,.4);
}
.gs-gif-preview {
    position: relative;
    width: 100%;
    aspect-ratio: 1 / 1;
    background: #0f172a;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.gs-gif-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.gs-gif-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    background: rgba(15,23,42,.8);
    backdrop-filter: blur(6px);
    border: 1px solid rgba(255,215,0,.3);
    color: var(--gs-gold-bright);
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    padding: 4px 10px;
    border-radius: 999px;
    text-transform: uppercase;
}
.gs-gif-content {
    padding: 16px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}
.gs-gif-title {
    font-family: 'Cinzel', Georgia, serif;
    font-weight: 700;
    font-size: 1.02rem;
    line-height: 1.3;
    margin: 0 0 4px;
    color: var(--gs-ink);
}
.gs-gif-sub {
    font-size: 0.82rem;
    color: var(--gs-muted);
    line-height: 1.4;
    margin: 0 0 16px;
    flex-grow: 1;
}
.gs-actions-row {
    display: flex;
    gap: 8px;
    align-items: center;
}
.gs-btn-icon {
    min-height: 44px;
    min-width: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    border: 1px solid var(--gs-line);
    background: var(--gs-canvas);
    color: var(--gs-ink);
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
}
.gs-btn-icon:hover {
    background: rgba(163,22,33,.08);
    color: var(--gs-primary);
    border-color: var(--gs-primary);
}
.gs-btn-primary {
    min-height: 44px;
    flex-grow: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 12px;
    border: none;
    background: linear-gradient(135deg, var(--gs-primary) 0%, var(--gs-primary-dark) 100%);
    color: #ffffff;
    font-size: 0.84rem;
    font-weight: 600;
    white-space: nowrap;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s ease;
}
.gs-btn-primary:hover {
    background: linear-gradient(135deg, #b8202c 0%, var(--gs-primary) 100%);
    color: #ffffff;
    transform: translateY(-1px);
}

/* Synthesiser Studio */
.gs-synth-panel {
    background: var(--gs-card);
    border: 1px solid var(--gs-line);
    border-radius: 24px;
    padding: 24px;
    box-shadow: 0 12px 32px -8px rgba(15,23,42,.06);
}
.gs-synth-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 32px;
}
@media (min-width: 900px) {
    .gs-synth-grid { grid-template-columns: minmax(0, 5fr) minmax(0, 4fr); }
}
.gs-form-label {
    display: block;
    font-size: 0.86rem;
    font-weight: 600;
    color: var(--gs-ink);
    margin-bottom: 6px;
}
.gs-form-input {
    width: 100%;
    min-height: 44px;
    padding: 10px 14px;
    border-radius: 12px;
    border: 1px solid var(--gs-line);
    background: var(--gs-canvas);
    color: var(--gs-ink);
    font-size: 0.94rem;
    margin-bottom: 16px;
    transition: border-color 0.2s ease;
}
.gs-form-input:focus {
    outline: none;
    border-color: var(--gs-primary);
    box-shadow: 0 0 0 3px rgba(163,22,33,.15);
}
.gs-themes-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-bottom: 24px;
}
.gs-theme-chip {
    min-height: 48px;
    padding: 8px 10px;
    border-radius: 12px;
    border: 1.5px solid var(--gs-line);
    background: var(--gs-canvas);
    color: var(--gs-ink);
    font-size: 0.8rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: center;
}
.gs-theme-chip.active {
    border-color: var(--gs-primary);
    background: rgba(163,22,33,.08);
    color: var(--gs-primary);
}
.gs-preview-stage {
    background: radial-gradient(circle at center, #1e293b, #0f172a);
    border-radius: 20px;
    border: 1px solid rgba(255,215,0,.2);
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 380px;
    position: relative;
    overflow: hidden;
}
.gs-preview-img {
    width: 100%;
    max-width: 320px;
    aspect-ratio: 1 / 1;
    border-radius: 16px;
    box-shadow: 0 20px 40px -10px rgba(0,0,0,.6);
    border: 1px solid rgba(255,215,0,.3);
}
</style>
@endpush

@section('content')
<div class="gif-studio-wrap" x-data="atgGifStudio({
    defaultName: '{{ addslashes($defaultName) }}',
    initialCatalog: @json(array_values($catalog))
})">
    <!-- Hero Header -->
    <div class="gs-hero">
        <div class="gs-hero__eyebrow">
            <span>✦</span> ATG ADVANCED TECHNOLOGY GROUP • SOVEREIGN ASSETS
        </div>
        <h1>Family Brand GIF Studio</h1>
        <p>
            Celebrate kinship, roots, and generational milestones with our sovereign animated GIF collection.
            Crafted 100% in-house with zero third-party tracking, optimised for instant sharing in comments, family feed, and messaging apps.
        </p>

        <!-- Navigation Tabs -->
        <div class="gs-nav-pills">
            <button type="button" class="gs-nav-btn" :class="{ 'active': activeTab === 'collection' }" @click="activeTab = 'collection'">
                💎 Brand Collection ({{ count($catalog) }})
            </button>
            <button type="button" class="gs-nav-btn" :class="{ 'active': activeTab === 'synthesiser' }" @click="activeTab = 'synthesiser'">
                ✨ Dynasty GIF Synthesiser
            </button>
        </div>
    </div>

    <!-- TAB 1: Official Brand GIF Collection -->
    <div x-show="activeTab === 'collection'" x-cloak>
        <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h2 class="h5 mb-1 font-semibold" style="font-family: 'Cinzel', Georgia, serif;">Official Curated Brand Animations</h2>
                <p class="text-sm text-muted mb-0">High-fidelity 400×400 looping GIFs reflecting our core heritage, celebration, and love invariants.</p>
            </div>
            <a href="/public/playbook#atg-gifs" class="text-sm font-semibold text-primary d-inline-flex align-items-center gap-1">
                <span>📖 Product Guide & Architecture</span> &rarr;
            </a>
        </div>

        <div class="gs-cards-grid">
            <template x-for="item in catalog" :key="item.key">
                <div class="gs-gif-card">
                    <div class="gs-gif-preview">
                        <img :src="item.url" :alt="item.alt" loading="lazy">
                        <span class="gs-gif-badge" x-text="item.category_label"></span>
                    </div>
                    <div class="gs-gif-content">
                        <h3 class="gs-gif-title" x-text="item.title"></h3>
                        <p class="gs-gif-sub" x-text="item.subtitle"></p>

                        <div class="gs-actions-row">
                            <!-- Copy Link -->
                            <button type="button" class="gs-btn-icon" title="Copy GIF URL" @click="copyLink(item.url)">
                                <i class="bi bi-link-45deg fs-5"></i>
                            </button>
                            <!-- Download GIF -->
                            <a :href="item.url" :download="item.filename" class="gs-btn-icon" title="Download GIF">
                                <i class="bi bi-download"></i>
                            </a>
                            <!-- Share to Feed -->
                            <a :href="'/member/profilePage?attach_gif=' + encodeURIComponent(item.url)" class="gs-btn-primary">
                                <i class="bi bi-chat-heart-fill"></i>
                                <span>Share to Feed</span>
                            </a>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- TAB 2: Dynamic Dynasty GIF Synthesiser -->
    <div x-show="activeTab === 'synthesiser'" x-cloak>
        <div class="gs-synth-panel">
            <div class="gs-synth-grid">
                <!-- Form Configuration -->
                <div>
                    <h2 class="h4 mb-2 font-semibold" style="font-family: 'Cinzel', Georgia, serif;">Personalised Dynasty Synthesiser</h2>
                    <p class="text-sm text-muted mb-4">
                        Generate a bespoke, animated brand GIF bearing your family's surname and motto. Rendered dynamically via the ATG graphics engine.
                    </p>

                    <form @submit.prevent="synthesiseDynastyGif">
                        @csrf
                        <label class="gs-form-label" for="dynastyName">Family Name or Dynasty Title</label>
                        <input id="dynastyName" type="text" class="gs-form-input" maxlength="40" x-model="form.familyName" placeholder="e.g. The Olaogun Family" required>

                        <label class="gs-form-label" for="dynastyCaption">Motto or Custom Caption</label>
                        <input id="dynastyCaption" type="text" class="gs-form-input" maxlength="50" x-model="form.caption" placeholder="e.g. Generations of Love & Unity">

                        <label class="gs-form-label">Colour Theme</label>
                        <div class="gs-themes-row">
                            <button type="button" class="gs-theme-chip" :class="{ 'active': form.theme === 'crimson_gold' }" @click="form.theme = 'crimson_gold'">
                                <span>🍷</span> Crimson Gold
                            </button>
                            <button type="button" class="gs-theme-chip" :class="{ 'active': form.theme === 'midnight_sapphire' }" @click="form.theme = 'midnight_sapphire'">
                                <span>🌌</span> Midnight
                            </button>
                            <button type="button" class="gs-theme-chip" :class="{ 'active': form.theme === 'emerald_heirloom' }" @click="form.theme = 'emerald_heirloom'">
                                <span>🌿</span> Emerald
                            </button>
                        </div>

                        <button type="submit" id="btnSynthesiseDynasty" class="btn btn-brand w-100 py-3 font-semibold d-inline-flex align-items-center justify-content-center gap-2" :disabled="isSynthesising">
                            <span x-show="!isSynthesising">✦ Synthesise Dynasty GIF</span>
                            <span x-show="isSynthesising" class="d-inline-flex align-items-center gap-2">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                <span>Synthesising Animation...</span>
                            </span>
                        </button>
                    </form>
                </div>

                <!-- Live Preview Stage -->
                <div class="d-flex flex-column align-items-center justify-content-center">
                    <div class="gs-preview-stage w-100">
                        <img :src="customGifUrl" class="gs-preview-img mb-3" alt="Custom Dynasty GIF Preview">
                        <div class="text-center text-white-50 text-xs mb-3">
                            <span class="text-warning font-semibold">400×400 GIF89a</span> • 16 Looping Frames • Infinite Repeat
                        </div>

                        <div class="d-flex gap-2 w-100 justify-content-center">
                            <a :href="customGifUrl" download="dynasty_family_brand.gif" class="btn btn-outline-light btn-sm d-inline-flex align-items-center gap-2 min-h-[44px]">
                                <i class="bi bi-download"></i>
                                <span>Download GIF</span>
                            </a>
                            <button type="button" class="btn btn-light btn-sm d-inline-flex align-items-center gap-2 min-h-[44px]" @click="copyLink(customGifUrl)">
                                <i class="bi bi-link-45deg"></i>
                                <span>Copy Link</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('component.submitSpinner')

@push('scripts')
<script>
function atgGifStudio(config) {
    return {
        activeTab: 'collection',
        catalog: config.initialCatalog || [],
        form: {
            familyName: config.defaultName || 'Our Family Circle',
            caption: 'Connected Forever & Always',
            theme: 'crimson_gold'
        },
        customGifUrl: '/api/atg/gif/custom?' + new URLSearchParams({
            name: config.defaultName || 'Our Family Circle',
            caption: 'Connected Forever & Always',
            theme: 'crimson_gold'
        }).toString(),
        isSynthesising: false,

        copyLink(url) {
            const fullUrl = url.startsWith('http') ? url : window.location.origin + url;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(fullUrl).then(() => {
                    if (window.Swal) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'GIF link copied to clipboard!',
                            showConfirmButton: false,
                            timer: 2500
                        });
                    } else {
                        alert('GIF link copied to clipboard!');
                    }
                });
            }
        },

        async synthesiseDynastyGif() {
            const btn = document.getElementById('btnSynthesiseDynasty');
            if (window.ScoreTenantSpinner) {
                ScoreTenantSpinner.start(btn);
            }
            this.isSynthesising = true;

            try {
                const response = await fetch('/api/atg/gif/synthesize', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        family_name: this.form.familyName,
                        caption: this.form.caption,
                        theme: this.form.theme
                    })
                });

                const data = await response.json();
                if (data.status === 'success') {
                    // Update preview with cache-busting timestamp
                    this.customGifUrl = data.url + '&_t=' + Date.now();
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Dynasty GIF Synthesised!',
                            text: 'Your custom animated brand GIF is ready for downloading and sharing.',
                            timer: 2500,
                            showConfirmButton: false
                        });
                    }
                } else {
                    throw new Error(data.message || 'Synthesis failed');
                }
            } catch (err) {
                console.error('[Dynasty GIF Synthesis Error]', err);
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Synthesis Error',
                        text: err.message || 'Could not synthesize custom GIF.'
                    });
                }
            } finally {
                this.isSynthesising = false;
                if (window.ScoreTenantSpinner) {
                    ScoreTenantSpinner.stop(btn);
                }
            }
        }
    };
}
</script>
@endpush
@endsection
