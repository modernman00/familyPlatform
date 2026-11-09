@extends('layouts.profileBase')

@section('title', 'Festive Cards Studio — FamilyPlatform')
@section('meta_description', 'Create bespoke family greeting cards for Christmas, Easter, New Year, and all milestone celebrations with 3D living envelopes.')

@push('styles')
<style>
.festive-studio-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 24px 16px 60px 16px;
}

.festive-studio-card {
    background: #ffffff;
    border-radius: 24px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.dark-mode .festive-studio-card {
    background: #18181b;
    border-color: #27272a;
    color: #f4f4f5;
}

.archetype-pill {
    cursor: pointer;
    border: 2px solid #e2e8f0;
    border-radius: 16px;
    padding: 12px 14px;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    background: #f8fafc;
    min-height: 44px;
}

.dark-mode .archetype-pill {
    background: #27272a;
    border-color: #3f3f46;
}

.archetype-pill:hover {
    border-color: #f59e0b;
    transform: translateY(-2px);
}

.archetype-pill.active {
    border-color: #f59e0b;
    background: #fefce8;
    box-shadow: 0 4px 14px rgba(245, 158, 11, 0.25);
}

.dark-mode .archetype-pill.active {
    background: rgba(245, 158, 11, 0.15);
    border-color: #f59e0b;
}

.live-card-preview-wrap {
    position: sticky;
    top: 90px;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 20px 45px rgba(0, 0, 0, 0.25);
    border: 2px solid rgba(245, 158, 11, 0.35);
    transition: all 0.3s ease;
}

/* 7 Archetype Live Preview Classes */
.preview-card-classic {
    background: linear-gradient(135deg, #18050b 0%, #4c0519 100%);
    border: 2px solid #f59e0b;
    color: #ffffff;
}

.preview-card-spatial_3d {
    background: linear-gradient(145deg, #1f1b2e 0%, #110e1c 100%);
    border: 2px solid #a855f7;
    color: #ffffff;
    box-shadow: 0 15px 35px rgba(168, 85, 247, 0.35);
}

.preview-card-translucent {
    background: rgba(15, 23, 42, 0.85);
    backdrop-filter: blur(16px);
    border: 1.5px solid rgba(255, 255, 255, 0.35);
    color: #ffffff;
}

.preview-card-hollow {
    background: #0f172a;
    border: 2px dashed #f59e0b;
    color: #ffffff;
}

.preview-card-plaque {
    background: linear-gradient(180deg, #09090b 0%, #1c1917 100%);
    border: 3px solid #d4af37;
    color: #ffffff;
}

.preview-card-hyper_reality {
    background: linear-gradient(135deg, #4c0519 0%, #1e1b4b 50%, #064e3b 100%);
    border: 2px solid #38bdf8;
    color: #ffffff;
    box-shadow: 0 0 30px rgba(56, 189, 248, 0.3);
}

.preview-card-origami {
    background: #ffffff;
    color: #0f172a;
    border: 3px solid #e11d48;
}

.verse-option-pill {
    cursor: pointer;
    border-radius: 12px;
    padding: 10px 14px;
    font-size: 0.86rem;
    border: 1px solid #e2e8f0;
    transition: background 0.15s;
    background: #f8fafc;
}
.verse-option-pill:hover {
    background: #f1f5f9;
}
.dark-mode .verse-option-pill {
    background: #27272a;
    border-color: #3f3f46;
    color: #e4e4e7;
}
</style>
@endpush

@section('content')
@include('member.creativeStudioTabs', ['creativeTab' => 'cards'])
<div class="festive-studio-container" x-data="festiveStudioWizard()">
    @include('csrf')
    @include('component.submitSpinner')

    <!-- Studio Header Banner -->
    <div class="card border-0 mb-4 overflow-hidden shadow-sm" 
         style="border-radius: 24px; background: linear-gradient(135deg, #1e0910 0%, #4c0519 45%, #881337 100%); color: #ffffff; border: 1.5px solid rgba(245, 158, 11, 0.35);">
        <div class="card-body p-4 p-md-5 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge rounded-pill fw-bold px-3 py-1 text-white" 
                          style="background: rgba(245, 158, 11, 0.25); border: 1px solid rgba(245, 158, 11, 0.5); font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase;">
                        ATG Sovereign Studio &bull; RFC-0019
                    </span>
                    <span class="badge rounded-pill fw-semibold px-2.5 py-1 text-white" 
                          style="background: rgba(255, 255, 255, 0.15); font-size: 0.75rem;">
                        7 Creative Archetypes
                    </span>
                </div>
                <h1 class="fw-bold text-white mb-2" style="font-size: 1.85rem; letter-spacing: -0.02em;">
                    Festivity &amp; Celebration Greeting Cards Studio
                </h1>
                <p class="text-white-50 mb-0" style="font-size: 0.95rem; max-width: 620px; line-height: 1.55;">
                    Craft bespoke holiday cards with 3D wax seal envelopes for Christmas, Easter, New Year, and milestone family moments. Share internally on your feed or send viral links to friends on WhatsApp.
                </p>
            </div>

            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                <a href="#previousCardsSection" class="btn btn-outline-light rounded-pill px-4 py-2.5 fw-semibold small" style="min-height: 44px; white-space: nowrap;">
                    <i class="bi bi-clock-history me-1.5"></i>
                    <span>Family Cards ({{ count($cards) }})</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Studio Workspace (2-Column Wizard & Live Preview) -->
    <div class="row g-4">
        
        <!-- Left: Studio Wizard Form -->
        <div class="col-12 col-lg-7">
            <div class="festive-studio-card p-4 p-md-4">
                
                <form id="festiveCardForm" @submit.prevent="submitFestiveCard($event)">
                    @csrf
                    
                    <!-- Step 1: Occasion Selector -->
                    <div class="mb-4">
                        <label class="form-label fw-bold d-flex align-items-center justify-content-between mb-2">
                            <span>1. Choose the Celebration / Occasion</span>
                            <span class="badge bg-warning text-dark fw-bold" x-text="selectedOccasion.title"></span>
                        </label>
                        <div class="row g-2">
                            @foreach($occasions as $key => $occ)
                            <div class="col-6 col-sm-4">
                                <div class="archetype-pill d-flex align-items-center gap-2"
                                     :class="{ 'active': occasionKey === '{{ $key }}' }"
                                     @click="selectOccasion('{{ $key }}')">
                                    <i class="bi {{ $occ['icon'] }} fs-5 text-warning"></i>
                                    <div class="small fw-bold text-truncate" style="font-size: 0.8rem;">
                                        {{ $occ['title'] }}
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Step 2: Design Archetype (The 7 Archetypes) -->
                    <div class="mb-4">
                        <label class="form-label fw-bold d-flex align-items-center justify-content-between mb-2">
                            <span>2. Select Design Archetype (Royal Luxe &amp; ATG)</span>
                            <span class="badge bg-primary text-white fw-semibold" x-text="selectedStyle.name"></span>
                        </label>
                        <div class="row g-2">
                            @foreach($templateStyles as $sKey => $s)
                            <div class="col-6 col-sm-4">
                                <div class="archetype-pill d-flex flex-column justify-content-between"
                                     :class="{ 'active': templateStyle === '{{ $sKey }}' }"
                                     @click="templateStyle = '{{ $sKey }}'">
                                    <div class="d-flex align-items-center gap-1.5 mb-1">
                                        <i class="bi {{ $s['icon'] }} text-warning"></i>
                                        <span class="fw-bold small text-truncate" style="font-size: 0.82rem;">{{ $s['name'] }}</span>
                                    </div>
                                    <span class="text-muted" style="font-size: 0.72rem; line-height: 1.3;">{{ $s['tagline'] }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Step 3: Color Palette & Theme -->
                    <div class="mb-4">
                        <label class="form-label fw-bold mb-2">3. Palette &amp; Particle Dynamics</label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($themes as $tKey => $thm)
                            <button type="button" 
                                    class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5"
                                    :class="themeKey === '{{ $tKey }}' ? 'btn-dark' : 'btn-outline-secondary'"
                                    @click="themeKey = '{{ $tKey }}'"
                                    style="min-height: 40px; font-size: 0.8rem;">
                                <span class="rounded-circle d-inline-block" style="width: 12px; height: 12px; background: {{ $thm['primary_color'] }}; border: 1px solid #ffffff;"></span>
                                <span>{{ $thm['label'] }}</span>
                            </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Step 4: Visual Hero (Photo / Living Avatar) -->
                    <div class="mb-4">
                        <label class="form-label fw-bold mb-2">4. Family Hero Imagery</label>
                        <div class="d-flex gap-2 mb-3">
                            <button type="button" 
                                    class="btn btn-sm rounded-pill px-3.5 py-2 fw-semibold"
                                    :class="heroType === 'photo' ? 'btn-danger' : 'btn-outline-secondary'"
                                    @click="heroType = 'photo'"
                                    style="min-height: 44px;">
                                <i class="bi bi-camera me-1"></i>
                                <span>Upload Family Photo</span>
                            </button>
                            <button type="button" 
                                    class="btn btn-sm rounded-pill px-3.5 py-2 fw-semibold"
                                    :class="heroType === 'crest' ? 'btn-danger' : 'btn-outline-secondary'"
                                    @click="heroType = 'crest'"
                                    style="min-height: 44px;">
                                <i class="bi bi-shield me-1"></i>
                                <span>Clan Heraldic Crest</span>
                            </button>
                        </div>

                        <div x-show="heroType === 'photo'" class="p-3 rounded-3 border bg-light">
                            <span class="small text-muted d-block mb-2">
                                <i class="bi bi-shield-check text-success me-1"></i>
                                <strong>SecOps Protected:</strong> All camera EXIF GPS metadata is automatically stripped on upload.
                            </span>
                            <input type="file" 
                                   name="hero_photo" 
                                   id="heroPhotoInput" 
                                   accept="image/*" 
                                   class="form-control"
                                   @change="handlePhotoPreview($event)">
                        </div>
                    </div>

                    <!-- Step 5: Headline, Verse & Sign-Off -->
                    <div class="mb-4">
                        <label class="form-label fw-bold mb-2">5. Personalize Festive Verse &amp; Sign-Off</label>
                        
                        <div class="mb-3">
                            <label class="form-label small text-muted">Card Headline</label>
                            <input type="text" 
                                   name="headline" 
                                   class="form-control" 
                                   x-model="headline" 
                                   required 
                                   placeholder="Merry Christmas &amp; Joyous Festivities">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-muted">Recipient Name (Optional)</label>
                            <input type="text" 
                                   name="recipient_name" 
                                   class="form-control" 
                                   x-model="recipientName" 
                                   placeholder="e.g. Grandma Rose, The Sterling Family, Friends &amp; Colleagues">
                        </div>

                        <!-- Sample Verse Quick Selector -->
                        <div class="mb-2">
                            <span class="small fw-semibold text-muted d-block mb-1">Pick a curated verse or write your own:</span>
                            <div class="d-flex flex-column gap-1.5 mb-2">
                                <template x-for="(v, idx) in selectedOccasion.sample_verses" :key="idx">
                                    <div class="verse-option-pill" @click="verse = v">
                                        <i class="bi bi-chat-heart text-warning me-1"></i>
                                        <span x-text="v"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-muted">Card Verse / Message</label>
                            <textarea name="verse" 
                                      class="form-control" 
                                      rows="3" 
                                      x-model="verse" 
                                      required></textarea>
                        </div>

                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small text-muted">Sign-off Phrase</label>
                                <input type="text" name="sign_off" class="form-control" x-model="signOff" required placeholder="With all our love,">
                            </div>
                            <div class="col-6">
                                <label class="form-label small text-muted">Family / Sender Name</label>
                                <input type="text" name="sender_name" class="form-control" x-model="senderName" required placeholder="The Olaogun Family">
                            </div>
                        </div>
                    </div>

                    <!-- Submission Action -->
                    <div class="pt-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <button type="submit" 
                                id="btnCreateFestiveCard"
                                class="btn btn-danger btn-lg rounded-pill px-5 fw-bold shadow-sm d-inline-flex align-items-center gap-2"
                                style="min-height: 48px; background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%); border: none;">
                            <i class="bi bi-magic fs-5"></i>
                            <span>Generate Festive Card &amp; Living Envelope</span>
                        </button>
                    </div>

                </form>

            </div>
        </div>

        <!-- Right: Live Real-Time Interactive Card Preview -->
        <div class="col-12 col-lg-5">
            <div class="live-card-preview-wrap" :class="`preview-card-${templateStyle}`">
                
                <div class="p-4 p-md-4">
                    <!-- Preview Badge -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge rounded-pill fw-bold px-2.5 py-1 text-white"
                              style="background: rgba(245, 158, 11, 0.3); border: 1px solid rgba(245, 158, 11, 0.6); font-size: 0.72rem;">
                            <span x-text="selectedStyle.name"></span>
                        </span>
                        <span class="small text-white-50" style="font-size: 0.78rem;">
                            Live Interactive Preview
                        </span>
                    </div>

                    <!-- Hero Image Preview -->
                    <div class="mb-3 text-center" x-show="previewPhotoUrl">
                        <img :src="previewPhotoUrl" alt="Card Hero Preview" class="img-fluid rounded-3 shadow-sm" style="max-height: 220px; object-fit: cover; width: 100%;">
                    </div>

                    <!-- Headline -->
                    <h3 class="fw-bold mb-2 text-center" 
                        style="font-family: 'Georgia', serif; font-size: 1.45rem;"
                        x-text="headline || 'Merry Christmas & Joyous Festivities'"></h3>

                    <!-- Recipient -->
                    <div class="text-center text-warning fw-semibold mb-2" 
                         x-show="recipientName"
                         x-text="`Dearest ${recipientName},`"
                         style="font-size: 0.95rem;"></div>

                    <!-- Verse Body -->
                    <p class="text-center small mb-3" 
                       style="line-height: 1.6; font-size: 0.92rem; opacity: 0.9;"
                       x-text="verse || 'Wishing our dearest family and friends a season filled with warmth and joy.'"></p>

                    <!-- Sign-Off -->
                    <div class="text-center pt-2 border-top border-white-50">
                        <div class="small fst-italic text-white-50" x-text="signOff || 'With all our love,'"></div>
                        <h5 class="fw-bold mb-0 text-warning" 
                            style="font-family: 'Georgia', serif;"
                            x-text="senderName || 'The Family'"></h5>
                    </div>

                    <!-- Mock Wax Seal Stamp -->
                    <div class="d-flex justify-content-center mt-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                             style="width: 52px; height: 52px; background: #b91c1c; border: 2px solid #fef08a; color: #fef08a; font-weight: bold; font-family: 'Georgia', serif; font-size: 1.1rem;">
                            FP
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- Previously Generated Festive Cards Archive -->
    <div class="mt-5 pt-4 border-top" id="previousCardsSection">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h4 class="fw-bold mb-1">Your Family’s Festive Cards Archive</h4>
                <p class="text-muted small mb-0">Browse, share on WhatsApp, or cross-post holiday cards to your family dashboard.</p>
            </div>
            <span class="badge bg-secondary rounded-pill px-3 py-1.5">{{ count($cards) }} Created</span>
        </div>

        @if(empty($cards))
        <div class="card border-0 p-5 text-center bg-light rounded-4">
            <div class="display-6 mb-2">💌</div>
            <h5 class="fw-bold">No Festive Cards Created Yet</h5>
            <p class="text-muted small mb-0">Select an occasion and archetype above to author your very first holiday greeting card.</p>
        </div>
        @else
        <div class="row g-3">
            @foreach($cards as $c)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden" style="background: var(--card-bg, #ffffff); border: 1px solid #e2e8f0;">
                    @if(!empty($c['hero_photo_url']))
                    <img src="{{ $c['hero_photo_url'] }}" alt="{{ $c['headline'] }}" class="card-img-top" style="height: 160px; object-fit: cover;">
                    @else
                    <div class="p-4 text-center" style="background: {{ $c['theme_meta']['gradient'] ?? '#881337' }}; color: #ffffff;">
                        <span class="badge bg-warning text-dark fw-bold mb-1">{{ $c['occasion_meta']['title'] }}</span>
                        <h6 class="fw-bold mb-0 text-white">{{ $c['headline'] }}</h6>
                    </div>
                    @endif

                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-1.5">
                                <span class="badge bg-light text-dark border fw-bold" style="font-size: 0.72rem;">
                                    {{ $c['template_style_meta']['name'] ?? 'Classic Heritage' }}
                                </span>
                                <span class="small text-muted" style="font-size: 0.75rem;">
                                    <i class="bi bi-eye me-1"></i>{{ $c['view_count'] }} views
                                </span>
                            </div>
                            <h6 class="fw-bold text-dark mb-1 text-truncate">{{ $c['headline'] }}</h6>
                            <p class="text-muted small mb-3" style="font-size: 0.8rem; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                {{ $c['verse'] }}
                            </p>
                        </div>

                        <div class="pt-2 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <a href="{{ $c['public_url'] }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-semibold" style="min-height: 38px;">
                                <i class="bi bi-envelope-open me-1"></i> View Card
                            </a>
                            
                            <div class="d-flex gap-1.5">
                                <button type="button" 
                                        class="btn btn-sm btn-success rounded-pill px-2.5" 
                                        @click="shareWhatsApp('{{ addslashes($c['headline']) }}', '{{ $c['public_url'] }}')"
                                        title="Share on WhatsApp"
                                        style="min-height: 38px; min-width: 38px;">
                                    <i class="bi bi-whatsapp"></i>
                                </button>
                                <button type="button" 
                                        class="btn btn-sm btn-danger rounded-pill px-2.5" 
                                        @click="postToFeed('{{ $c['share_token'] }}', $event)"
                                        title="Post to Family Feed"
                                        style="min-height: 38px;">
                                    <i class="bi bi-broadcast me-1"></i> Feed
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>

<script>
function festiveStudioWizard() {
    const occasions = @json($occasions);
    const themes = @json($themes);
    const templateStyles = @json($templateStyles);

    return {
        occasions: occasions,
        themes: themes,
        templateStyles: templateStyles,
        occasionKey: 'christmas',
        templateStyle: 'classic',
        themeKey: 'royal_crimson',
        heroType: 'photo',
        headline: 'Merry Christmas & Joyous Festivities',
        recipientName: '',
        verse: 'Wishing our dearest family and friends a season filled with warmth, radiant laughter, and treasured moments together.',
        signOff: 'With all our love,',
        senderName: '{{ addslashes($user['name'] ?? 'The Family') }} {{ addslashes($user['lastName'] ?? '') }}',
        previewPhotoUrl: '',

        get selectedOccasion() {
            return this.occasions[this.occasionKey] || this.occasions['christmas'];
        },

        get selectedStyle() {
            return this.templateStyles[this.templateStyle] || this.templateStyles['classic'];
        },

        selectOccasion(key) {
            this.occasionKey = key;
            const occ = this.selectedOccasion;
            this.themeKey = occ.default_theme || 'royal_crimson';
            this.headline = occ.default_headline || '';
            this.verse = occ.default_verse || '';
            this.signOff = occ.default_sign_off || 'With warm regards,';
        },

        handlePhotoPreview(event) {
            const file = event.target.files && event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.previewPhotoUrl = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        async submitFestiveCard(event) {
            const btn = document.getElementById('btnCreateFestiveCard');
            if (window.ScoreTenantSpinner) {
                ScoreTenantSpinner.start(btn);
            }

            try {
                const form = document.getElementById('festiveCardForm');
                const formData = new FormData(form);
                
                const csrfToken = document.querySelector('input[name="token"]')?.value || '';
                formData.append('token', csrfToken);
                formData.append('occasion', this.occasionKey);
                formData.append('template_style', this.templateStyle);
                formData.append('theme', this.themeKey);
                formData.append('hero_type', this.heroType);

                const response = await fetch('/api/festive-cards/create', {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json' }
                });

                const res = await response.json();
                if (window.ScoreTenantSpinner) {
                    ScoreTenantSpinner.stop(btn);
                }

                if (res.status === 'success' && res.data) {
                    if (window.Swal) {
                        Swal.fire({
                            title: 'Festive Card Created! 🎄',
                            html: `<p>Your card has been generated. Open the Living Envelope to preview or copy your link to share on WhatsApp.</p>`,
                            icon: 'success',
                            showCancelButton: true,
                            confirmButtonText: 'Open Living Envelope',
                            cancelButtonText: 'Done',
                            confirmButtonColor: '#e11d48'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.open(res.data.public_url, '_blank');
                            }
                            window.location.reload();
                        });
                    } else {
                        alert('Card created! Redirecting to preview...');
                        window.location.href = res.data.public_url;
                    }
                } else {
                    alert(res.message || 'Could not generate card. Please try again.');
                }
            } catch (err) {
                if (window.ScoreTenantSpinner) {
                    ScoreTenantSpinner.stop(btn);
                }
                alert('Network error while generating festive card.');
            }
        },

        shareWhatsApp(headline, url) {
            const fullUrl = window.location.origin + url;
            const text = encodeURIComponent(`✨ ${headline} ✨\nOpen our family's festive holiday card:\n${fullUrl}`);
            window.open(`https://api.whatsapp.com/send?text=${text}`, '_blank');
        },

        async postToFeed(shareToken, event) {
            const btn = event.currentTarget;
            if (window.ScoreTenantSpinner) {
                ScoreTenantSpinner.start(btn);
            }

            try {
                const csrfToken = document.querySelector('input[name="token"]')?.value || '';
                const fd = new FormData();
                fd.append('token', csrfToken);
                fd.append('share_token', shareToken);

                const response = await fetch('/api/festive-cards/post-to-feed', {
                    method: 'POST',
                    body: fd,
                    headers: { 'Accept': 'application/json' }
                });

                const res = await response.json();
                if (window.ScoreTenantSpinner) {
                    ScoreTenantSpinner.stop(btn);
                }

                if (res.status === 'success') {
                    if (window.Swal) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Published to Family Feed!',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    } else {
                        alert('Published to family feed!');
                    }
                } else {
                    alert(res.message || 'Could not post to feed.');
                }
            } catch (e) {
                if (window.ScoreTenantSpinner) {
                    ScoreTenantSpinner.stop(btn);
                }
                alert('Network error while posting card to feed.');
            }
        }
    };
}
</script>
@endsection
