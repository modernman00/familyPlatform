@extends('layouts.profileBase')

@section('title', '3D EMOJI & AVATAR STUDIO')

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
            <span>✨ 3D Emoji &amp; Avatar Studio</span>
        </h1>
        <p class="atg-studio-subtitle">
            Craft your bespoke procedural 3D persona and explore specialised kinship emojis designed specifically for our multi-generational family network.
        </p>
    </div>

    <!-- Main Studio Grid -->
    <div class="atg-studio-grid">
        
        <!-- Left Column: 3D Live Interactive Stage -->
        <div class="atg-preview-stage">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 mb-3">Live 3D Viewport</h2>
            
            <div class="atg-canvas-viewport" id="atgAvatarViewport" title="Click and drag to rotate in 3D">
                {!! (new \App\services\AtgEmojiService())->renderProceduralAvatar([
                    'skin_tone'  => 'warm_chestnut',
                    'hairstyle'  => 'afro_crown',
                    'expression' => 'warm_smile',
                    'accessory'  => 'none',
                    'backdrop'   => 'warm_sunset'
                ], 256) !!}
            </div>

            <p class="text-xs text-slate-400 mb-4 flex items-center justify-center gap-1">
                <span>🔄 Click and drag to inspect in 3D</span>
            </p>

            <div class="atg-preview-actions">
                <button type="button" id="atgSaveAvatarBtn" class="atg-btn-primary btn-spin-on-click" data-spin="true">
                    <span>✨ Set as Profile Picture</span>
                </button>
                <button type="button" id="atgRandomAvatarBtn" class="atg-btn-secondary">
                    <span>🎲 Randomise Persona</span>
                </button>
            </div>
        </div>

        <!-- Right Column: Trait Customisation & Kinship Catalog -->
        <div class="atg-controls-card">
            
            <!-- Navigation Tabs -->
            <div class="atg-tabs">
                <button type="button" class="atg-tab-btn active" onclick="switchAtgTab('avatar-tab', this)">
                    👤 3D Persona Customiser
                </button>
                <button type="button" class="atg-tab-btn" onclick="switchAtgTab('catalog-tab', this)">
                    🛡️ Family 3D Emojis Catalog
                </button>
            </div>

            <!-- TAB 1: 3D Persona Customiser -->
            <div id="avatar-tab" class="atg-tab-content">
                
                <!-- 1. Complexion & Radiance -->
                <div class="atg-trait-section">
                    <div class="atg-trait-label">
                        <span>Skin Complexion &amp; Radiance</span>
                    </div>
                    <div class="atg-swatches-grid">
                        @foreach (\App\services\AtgEmojiService::SKIN_TONES as $key => $skin)
                            <div class="atg-trait-pill {{ $key === 'warm_chestnut' ? 'active' : '' }}" 
                                 data-trait-group="skin_tone" 
                                 data-trait-value="{{ $key }}">
                                <span class="atg-color-dot" style="background: {{ $skin['base'] }};"></span>
                                <span>{{ $skin['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- 2. Sculpted Hairstyles -->
                <div class="atg-trait-section">
                    <div class="atg-trait-label">
                        <span>Hairstyle &amp; Crown</span>
                    </div>
                    <div class="atg-swatches-grid">
                        @foreach (\App\services\AtgEmojiService::HAIRSTYLES as $key => $hair)
                            <div class="atg-trait-pill {{ $key === 'afro_crown' ? 'active' : '' }}" 
                                 data-trait-group="hairstyle" 
                                 data-trait-value="{{ $key }}">
                                <span>{{ $hair['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- 3. Facial Expression -->
                <div class="atg-trait-section">
                    <div class="atg-trait-label">
                        <span>Facial Expression</span>
                    </div>
                    <div class="atg-swatches-grid">
                        @foreach (\App\services\AtgEmojiService::EXPRESSIONS as $key => $expr)
                            <div class="atg-trait-pill {{ $key === 'warm_smile' ? 'active' : '' }}" 
                                 data-trait-group="expression" 
                                 data-trait-value="{{ $key }}">
                                <span>{{ $expr['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- 4. Royal & Kinship Accessories -->
                <div class="atg-trait-section">
                    <div class="atg-trait-label">
                        <span>Heritage Accessories</span>
                    </div>
                    <div class="atg-swatches-grid">
                        @foreach (\App\services\AtgEmojiService::ACCESSORIES as $key => $acc)
                            <div class="atg-trait-pill {{ $key === 'none' ? 'active' : '' }}" 
                                 data-trait-group="accessory" 
                                 data-trait-value="{{ $key }}">
                                <span>{{ $acc['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- 5. Lighting Backdrop -->
                <div class="atg-trait-section">
                    <div class="atg-trait-label">
                        <span>Lighting Aura &amp; Backdrop</span>
                    </div>
                    <div class="atg-swatches-grid">
                        @foreach (\App\services\AtgEmojiService::BACKDROPS as $key => $bd)
                            <div class="atg-trait-pill {{ $key === 'warm_sunset' ? 'active' : '' }}" 
                                 data-trait-group="backdrop" 
                                 data-trait-value="{{ $key }}">
                                <span class="atg-color-dot" style="background: linear-gradient(135deg, {{ $bd['start'] }}, {{ $bd['end'] }});"></span>
                                <span>{{ $bd['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

            <!-- TAB 2: Family 3D Emojis Catalog -->
            <div id="catalog-tab" class="atg-tab-content" style="display: none;">
                <p class="text-sm text-slate-500 mb-4">
                    Click any 3D emoji to copy its shortcode or inspect its generational meaning.
                </p>

                @foreach ($catalog['categories'] as $categoryName => $emojis)
                    <div class="mb-6">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 mb-3 border-b pb-1">
                            {{ $categoryName }}
                        </h3>
                        <div class="atg-catalog-grid">
                            @foreach ($emojis as $emoji)
                                <div class="atg-emoji-card" 
                                     data-emoji-id="{{ $emoji['id'] }}" 
                                     data-emoji-title="{{ $emoji['title'] }}"
                                     title="Click to copy :{{ $emoji['id'] }}:">
                                    <div class="atg-emoji-container">
                                        {!! $emoji['svg'] !!}
                                    </div>
                                    <div class="atg-emoji-card-title">{{ $emoji['title'] }}</div>
                                    <div class="atg-emoji-card-meaning">{{ $emoji['meaning'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</div>

<script src="/public/js/atg-emoji-engine.js?v={{ time() }}"></script>
<script>
    function switchAtgTab(tabId, btn) {
        document.querySelectorAll('.atg-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.atg-tab-content').forEach(c => c.style.display = 'none');
        btn.classList.add('active');
        const target = document.getElementById(tabId);
        if (target) target.style.display = 'block';
    }
</script>
@endsection
