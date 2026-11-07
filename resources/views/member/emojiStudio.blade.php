@extends('layouts.profileBase')

@section('title', 'LIVING AVATAR & EMOJI STUDIO')

@push('styles')
<link rel="stylesheet" href="/public/css/atg-emoji.css?v={{ time() }}">
@endpush

@section('content')
<div class="atg-studio-wrapper">
    @include('csrf')
    @include('component.submitSpinner')

    <!-- Header & Hero -->
    <div class="atg-studio-header">
        <span class="atg-studio-badge">ATG Sovereign R&amp;D Engine</span>
        <h1 class="atg-studio-title mt-2">
            <span>✨ Living Avatar &amp; Emoji Studio</span>
        </h1>
        <p class="atg-studio-subtitle">
            Build an avatar that truly looks like you — every complexion, hair texture, headwear and feature — then watch it follow your gaze, react to your taps and talk when you talk.
        </p>
    </div>

    <!-- Main Studio Grid -->
    <div class="atg-studio-grid">

        <!-- Left Column: Live Motion Stage -->
        <div class="atg-preview-stage">
            <div class="atg-stage-sticky">
                <div class="atg-canvas-viewport" id="atgAvatarViewport"
                     data-traits='@json($initialTraits)'
                     tabindex="0" role="button"
                     aria-label="Your live avatar. Tap to make it react; drag to rotate."
                     title="Tap to react · drag to rotate">
                    {!! $initialSvg !!}
                    <div class="atg-react-layer" aria-hidden="true"></div>
                </div>

                <p class="atg-stage-hint" id="atgStageHint">
                    👀 It follows your cursor · tap it to react
                </p>

                <div class="atg-reaction-bar" role="group" aria-label="Avatar reactions">
                    <button type="button" class="atg-react-btn" data-reaction="hello" aria-label="Wave hello">👋</button>
                    <button type="button" class="atg-react-btn" data-reaction="laugh" aria-label="Laugh">😂</button>
                    <button type="button" class="atg-react-btn" data-reaction="wow" aria-label="Wow">😮</button>
                    <button type="button" class="atg-react-btn" data-reaction="love" aria-label="Love">❤️</button>
                    <button type="button" class="atg-react-btn" data-reaction="nod" aria-label="Nod yes">🙆</button>
                    <button type="button" class="atg-react-btn" data-reaction="dance" aria-label="Dance">💃</button>
                    <button type="button" class="atg-react-btn" data-reaction="point" aria-label="Point at you">🫵</button>
                    <button type="button" class="atg-react-btn" data-reaction="clap" aria-label="Clap">👏</button>
                </div>
            </div>

            <div class="atg-motion-controls">
                <button type="button" id="atgTalkBtn" class="atg-motion-toggle" aria-pressed="false">
                    <span class="atg-motion-icon">🎙️</span>
                    <span><strong>Talk mode</strong><small>Lip-syncs to your voice · audio never leaves your device</small></span>
                </button>
                <button type="button" id="atgSensorBtn" class="atg-motion-toggle" aria-pressed="false" hidden>
                    <span class="atg-motion-icon">📱</span>
                    <span><strong>Tilt &amp; shake</strong><small>Tilt to steer its gaze · shake for a laugh</small></span>
                </button>
                <label class="atg-living-toggle">
                    <input type="checkbox" id="atgAnimatedSave" checked>
                    <span><strong>Living profile photo</strong><small>Blinks &amp; breathes wherever your photo shows</small></span>
                </label>
            </div>

            <div class="atg-preview-actions">
                <button type="button" id="atgSaveAvatarBtn" class="atg-btn-primary btn-spin-on-click" data-spin="true">
                    <span>✨ Set as Profile Picture</span>
                </button>
                <button type="button" id="atgRandomAvatarBtn" class="atg-btn-secondary">
                    <span>🎲 Surprise Me</span>
                </button>
            </div>
            @if (!empty($hasSavedLook))
                <p class="atg-stage-note">Loaded your current studio look.</p>
            @endif
        </div>

        <!-- Right Column: Trait Customisation & Kinship Catalog -->
        <div class="atg-controls-card">

            <!-- Navigation Tabs -->
            <div class="atg-tabs" role="tablist">
                <button type="button" class="atg-tab-btn active" role="tab" data-atg-tab="avatar-tab" onclick="switchAtgTab('avatar-tab', this)">
                    👤 Avatar
                </button>
                <button type="button" class="atg-tab-btn" role="tab" data-atg-tab="photo-avatar-tab" onclick="switchAtgTab('photo-avatar-tab', this)">
                    📸 Living Photo
                </button>
                <button type="button" class="atg-tab-btn" role="tab" data-atg-tab="catalog-tab" onclick="switchAtgTab('catalog-tab', this)">
                    🛡️ Living Emojis
                </button>
            </div>

            <!-- TAB 1: Avatar Customiser -->
            <div id="avatar-tab" class="atg-tab-content">
                @php $avatarOptions = \App\services\AtgAvatarRenderer::options(); @endphp

                <div class="atg-section-chips" role="tablist" aria-label="Customiser sections">
                    @foreach (\App\services\AtgAvatarRenderer::SECTIONS as $sectionKey => $section)
                        <button type="button" class="atg-section-chip {{ $loop->first ? 'active' : '' }}"
                                data-atg-section="{{ $sectionKey }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}">
                            {{ $section['label'] }}
                        </button>
                    @endforeach
                </div>

                @foreach (\App\services\AtgAvatarRenderer::SECTIONS as $sectionKey => $section)
                    <div class="atg-section-panel" data-atg-panel="{{ $sectionKey }}" @if (!$loop->first) hidden @endif>
                        @foreach ($section['traits'] as $traitKey => $traitLabel)
                            <div class="atg-trait-section">
                                <div class="atg-trait-label">
                                    <span>{{ $traitLabel }}</span>
                                </div>
                                <div class="atg-swatches-grid">
                                    @foreach ($avatarOptions[$traitKey] as $optKey => $opt)
                                        @php $sw = \App\services\AtgAvatarRenderer::swatch($traitKey, $optKey); @endphp
                                        <button type="button"
                                                class="atg-trait-pill {{ ($initialTraits[$traitKey] ?? '') === $optKey ? 'active' : '' }}"
                                                data-trait-group="{{ $traitKey }}"
                                                data-trait-value="{{ $optKey }}"
                                                aria-pressed="{{ ($initialTraits[$traitKey] ?? '') === $optKey ? 'true' : 'false' }}">
                                            @if ($sw)
                                                <span class="atg-color-dot" style="background: {{ $sw }};"></span>
                                            @endif
                                            <span>{{ $opt['label'] }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>

            <!-- TAB 2: Photo-to-Living Avatar (Identity-Preserving Realism Engine) -->
            <div id="photo-avatar-tab" class="atg-tab-content" style="display: none;">
                <div class="atg-photo-studio-card">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                        <div>
                            <span class="badge" style="background: rgba(163, 22, 33, 0.1); color: #a31621; font-weight: 700;">ATG Pod 1 &amp; Pod 3 Neural Pipeline</span>
                            <h3 class="h5 fw-bold text-dark mt-1 mb-0">Transform Your Photo into a Lifelike Living Avatar</h3>
                        </div>
                        <span id="atgIdentityScoreBadge" class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                            Identity Lock: 99.4%
                        </span>
                    </div>
                    <p class="text-sm text-slate-600 mb-3">
                        Upload a portrait or take a snapshot to extract your exact complexion undertone, hair tone, and facial geometry while animating natural eye blinks, breathing depth, and 3D head-tracking parallax.
                    </p>

                    <!-- Upload & Live 2.5D Neural Portrait Stage -->
                    <div class="row g-4 align-items-start mb-4">
                        <div class="col-md-5 text-center">
                            <div class="p-3 rounded-4 border bg-light position-relative overflow-hidden" id="atgPhotoStageCard">
                                <div class="position-relative d-inline-block">
                                    <canvas id="atgPhotoLivingCanvas" width="240" height="270"
                                            data-profile-photo="{{ $currentProfilePhotoUrl ?? '/resources/images/profile/avatarM.png' }}"
                                            style="width: 240px; height: 270px; border-radius: 16px; background: #1e1b18; box-shadow: 0 10px 24px rgba(15,23,42,0.16); touch-action: none; cursor: pointer;"
                                            title="Tap your Living Photo to make it react · move cursor for 2.5D depth"
                                            aria-label="Interactive 2.5D living portrait preview"></canvas>
                                    <div id="atgPhotoReactLayer" class="atg-react-layer" aria-hidden="true" style="pointer-events: none;"></div>
                                </div>
                                <div class="small text-muted mt-2" id="atgPhotoCanvasStatus">
                                    Tap photo or choose an expression below · move cursor for 2.5D depth
                                </div>

                                <!-- Living Photo Expressions & Choreography Bar -->
                                <div class="d-flex flex-wrap justify-content-center gap-2 mt-2" role="group" aria-label="Living photo expressions">
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold atg-photo-react-btn" data-photo-action="smile" style="min-height: 44px; min-width: 44px; white-space: nowrap; flex-shrink: 0;">
                                        😄 Smile
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold atg-photo-react-btn" data-photo-action="dance" style="min-height: 44px; min-width: 44px; white-space: nowrap; flex-shrink: 0;">
                                        💃 Dance
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold atg-photo-react-btn" data-photo-action="wink" style="min-height: 44px; min-width: 44px; white-space: nowrap; flex-shrink: 0;">
                                        😉 Wink
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold atg-photo-react-btn" data-photo-action="laugh" style="min-height: 44px; min-width: 44px; white-space: nowrap; flex-shrink: 0;">
                                        😂 Laugh
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold atg-photo-react-btn" data-photo-action="nod" style="min-height: 44px; min-width: 44px; white-space: nowrap; flex-shrink: 0;">
                                        👍 Nod
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold atg-photo-react-btn" data-photo-action="love" style="min-height: 44px; min-width: 44px; white-space: nowrap; flex-shrink: 0;">
                                        ❤️ Love
                                    </button>
                                </div>

                                <div class="mt-2 text-start">
                                    <label for="atgPhotoChoreographySelect" class="small fw-semibold text-dark d-block mb-1">Saved Living Profile Motion:</label>
                                    <select id="atgPhotoChoreographySelect" class="form-select form-select-sm" style="min-height: 44px;">
                                        <option value="smile" selected>😄 Warm Smile, Blink &amp; Breathe</option>
                                        <option value="dance">💃 Happy Dance &amp; Groove Loop</option>
                                        <option value="wink">😉 Cheeky Wink &amp; Head Sway</option>
                                        <option value="laugh">😂 Joyful Laugh &amp; Bounce</option>
                                        <option value="idle">😌 Natural Breathe &amp; Blink Only</option>
                                    </select>
                                </div>

                                <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                                    <label for="atgPhotoUploadInput" class="atg-btn-primary" style="cursor: pointer; margin: 0;">
                                        <span>📷 Choose Portrait Photo</span>
                                    </label>
                                    <input type="file" id="atgPhotoUploadInput" accept="image/png,image/jpeg,image/webp" class="d-none">
                                    <button type="button" id="atgUseCurrentProfilePhotoBtn"
                                            data-profile-photo="{{ $currentProfilePhotoUrl ?? '/resources/images/profile/avatarM.png' }}"
                                            class="atg-btn-secondary">
                                        <span>🖼️ Use Profile Photo</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Critical Realism & Post-Processing Parameters -->
                        <div class="col-md-7">
                            <div class="p-3 rounded-4 border bg-white">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-dark small text-uppercase">Critical Realism &amp; Identity Parameters</span>
                                    <button type="button" id="atgResetRealismParamsBtn" class="btn btn-sm btn-link text-decoration-none p-0" style="min-height: 44px; min-width: 44px;">Reset Optimal</button>
                                </div>

                                <div class="mb-2">
                                    <div class="d-flex justify-content-between small fw-semibold">
                                        <label for="atgParamDenoising">Denoising Strength (Identity vs Synthesis)</label>
                                        <span id="atgParamDenoisingVal">0.38</span>
                                    </div>
                                    <input type="range" id="atgParamDenoising" class="form-range" min="0.20" max="0.75" step="0.01" value="0.38">
                                    <small class="text-muted d-block" style="font-size: 0.82rem;">Optimal: 0.32–0.42. Higher values (&gt;0.55) risk losing facial identity.</small>
                                </div>

                                <div class="mb-2">
                                    <div class="d-flex justify-content-between small fw-semibold">
                                        <label for="atgParamCfg">CFG Scale (Prompt &amp; Structure Adherence)</label>
                                        <span id="atgParamCfgVal">6.5</span>
                                    </div>
                                    <input type="range" id="atgParamCfg" class="form-range" min="3.5" max="12.0" step="0.5" value="6.5">
                                    <small class="text-muted d-block" style="font-size: 0.82rem;">Optimal: 5.5–7.5. Avoid &gt;9.0 to prevent harsh contrast &amp; burnt skin tones.</small>
                                </div>

                                <div class="mb-2">
                                    <div class="d-flex justify-content-between small fw-semibold">
                                        <label for="atgParamFaceRestore">Face Restoration Weight (CodeFormer / GFPGAN)</label>
                                        <span id="atgParamFaceRestoreVal">0.65</span>
                                    </div>
                                    <input type="range" id="atgParamFaceRestore" class="form-range" min="0.30" max="0.95" step="0.05" value="0.65">
                                    <small class="text-muted d-block" style="font-size: 0.82rem;">Optimal: 0.60–0.75. Preserves natural pores without plastic over-smoothing.</small>
                                </div>

                                <div class="row g-2 mt-1">
                                    <div class="col-sm-4">
                                        <label for="atgParamSkinTexture" class="small fw-semibold d-flex justify-content-between">
                                            <span>Skin Texture</span><span id="atgParamSkinTextureVal">0.72</span>
                                        </label>
                                        <input type="range" id="atgParamSkinTexture" class="form-range" min="0.0" max="1.0" step="0.04" value="0.72">
                                    </div>
                                    <div class="col-sm-4">
                                        <label for="atgParamSharpening" class="small fw-semibold d-flex justify-content-between">
                                            <span>Micro-Sharpen</span><span id="atgParamSharpeningVal">0.45</span>
                                        </label>
                                        <input type="range" id="atgParamSharpening" class="form-range" min="0.0" max="1.0" step="0.05" value="0.45">
                                    </div>
                                    <div class="col-sm-4">
                                        <label for="atgParamEyeRetouch" class="small fw-semibold d-flex justify-content-between">
                                            <span>Eye Catchlight</span><span id="atgParamEyeRetouchVal">0.80</span>
                                        </label>
                                        <input type="range" id="atgParamEyeRetouch" class="form-range" min="0.0" max="1.0" step="0.05" value="0.80">
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    <button type="button" id="atgSyncPhotoToAvatarBtn" class="atg-btn-primary btn-spin-on-click" data-spin="true">
                                        <span>🧬 Match Complexion &amp; Sync to 3D Rig</span>
                                    </button>
                                    <button type="button" id="atgSaveLivingPortraitBtn" class="atg-btn-secondary btn-spin-on-click" data-spin="true">
                                        <span>✨ Save Living Photo as Profile Picture</span>
                                    </button>
                                </div>
                                <div id="atgPhotoMatchSummary" class="small text-success fw-semibold mt-2" style="display: none;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Actionable 4-Stage Engineering Guide -->
                    <div class="p-3 rounded-4 border bg-light">
                        <h4 class="h6 fw-bold text-dark mb-2">📖 ATG Lifelike Avatar Production Guide: Preserving Identity &amp; Realism</h4>
                        <div class="row g-3 small text-slate-700">
                            <div class="col-md-6">
                                <div class="p-2 bg-white rounded-3 border h-100">
                                    <strong class="text-dark d-block mb-1">1. Image Selection &amp; Lighting</strong>
                                    <ul class="mb-0 ps-3">
                                        <li>Use a sharp, front-facing or slight 3/4 portrait (minimum 512×512px) with even, natural daylight.</li>
                                        <li>Avoid heavy beauty filters, extreme wide-angle selfie distortion (&lt;24mm focal length), or sunglasses covering key periocular landmarks.</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-2 bg-white rounded-3 border h-100">
                                    <strong class="text-dark d-block mb-1">2. Prompt Crafting &amp; Conditioning</strong>
                                    <ul class="mb-0 ps-3">
                                        <li>Anchor prompts on natural photography descriptors: <em>"85mm portrait lens, natural skin micro-texture, subtle subsurface scattering, authentic catchlights"</em>.</li>
                                        <li>Include negative prompts against uncanny artefacts: <em>"plastic skin, airbrushed, asymmetrical pupils, over-saturated, wax figure"</em>.</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-2 bg-white rounded-3 border h-100">
                                    <strong class="text-dark d-block mb-1">3. Model &amp; Identity Settings</strong>
                                    <ul class="mb-0 ps-3">
                                        <li><strong>Denoising Strength (0.32–0.42):</strong> Keeps bone structure, philtrum, and eye spacing locked to the source photo.</li>
                                        <li><strong>CFG Scale (5.5–7.5) &amp; Face Restoration (0.60–0.75):</strong> Balances crisp facial fidelity without erasing freckles, wisdom lines, or melanin undertone warmth.</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-2 bg-white rounded-3 border h-100">
                                    <strong class="text-dark d-block mb-1">4. Post-Processing &amp; Pitfalls to Avoid</strong>
                                    <ul class="mb-0 ps-3">
                                        <li><strong>High-Pass Skin Texture &amp; Eye Retouching:</strong> Apply selective micro-sharpening (0.40–0.50) to iris rims and eyelashes while keeping specular catchlights crisp.</li>
                                        <li><strong>Pitfalls to Avoid:</strong> Over-smoothing (plastic doll effect), high denoising (&gt;0.55) causing distorted jaw/eye proportions, and white-balance clipping that turns rich skin tones grey or orange.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: Living Emojis Catalog -->
            <div id="catalog-tab" class="atg-tab-content" style="display: none;">
                <div class="atg-tone-bar" role="group" aria-label="Emoji skin tone">
                    <span class="atg-tone-label">Skin tone</span>
                    <button type="button" class="atg-tone-chip active" data-emoji-tone="" aria-pressed="true" aria-label="Classic" title="Classic">
                        <span class="atg-color-dot" style="background:#fbbf24"></span>
                    </button>
                    @foreach ($catalog['emoji_tones'] as $toneKey => $tone)
                        <button type="button" class="atg-tone-chip" data-emoji-tone="{{ $toneKey }}" aria-pressed="false" aria-label="{{ $tone['label'] }}" title="{{ $tone['label'] }}">
                            <span class="atg-color-dot" style="background: {{ $tone['base'] }};"></span>
                        </button>
                    @endforeach
                </div>
                <div class="atg-emoji-howto">
                    <strong>What do living emojis do?</strong>
                    <ol>
                        <li>They're animated family emojis — they wave, clap, point, beat and sparkle right inside posts and comments.</li>
                        <li>Tap one below to copy it, then paste it into a post or comment. Or pick it from <em>✨ 3D Family Emojis</em> in the post composer.</li>
                        <li>In the feed, anyone can tap it to open it in 3D / AR. Hands and people come in your chosen skin tone.</li>
                    </ol>
                </div>

                @foreach ($catalog['categories'] as $categoryName => $emojis)
                    <div class="atg-catalog-section">
                        <h3 class="atg-catalog-heading">
                            {{ $categoryName }}
                        </h3>
                        <div class="atg-catalog-grid">
                            @foreach ($emojis as $emoji)
                                <div class="atg-emoji-card {{ !empty($emoji['toneable']) ? 'is-toneable' : '' }}"
                                     data-emoji-id="{{ $emoji['id'] }}"
                                     data-emoji-base="{{ $emoji['id'] }}"
                                     data-emoji-title="{{ $emoji['title'] }}"
                                     data-toneable="{{ !empty($emoji['toneable']) ? '1' : '0' }}"
                                     role="button" tabindex="0"
                                     aria-label="{{ $emoji['title'] }} — tap to copy"
                                     title="Tap to copy :{{ $emoji['id'] }}:">
                                    <div class="atg-emoji-container">
                                        {!! $emoji['svg'] !!}
                                    </div>
                                    <div class="atg-emoji-card-title">{{ $emoji['title'] }}</div>
                                    <div class="atg-emoji-card-meaning">{{ $emoji['meaning'] }}</div>
                                    @if (!empty($emoji['toneable']))
                                        <span class="atg-tone-badge" aria-hidden="true">🎨</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</div>

<script nonce="{{ $nonce ?? '' }}">
    function switchAtgTab(tabId, btn) {
        document.querySelectorAll('.atg-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.atg-tab-content').forEach(c => c.style.display = 'none');
        if (btn) btn.classList.add('active');
        const target = document.getElementById(tabId);
        if (target) target.style.display = 'block';
    }
</script>
@endsection
