@extends('layouts.profileBase')

@section('title', 'Flyer & Poster Studio — FamilyPlatform')
@section('meta_description', 'Design family flyers and posters for anniversaries, reunions, naming ceremonies, birthdays, weddings and celebrations of life — share on WhatsApp or post to your family feed.')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,700;0,900;1,400&family=Great+Vibes&family=Montserrat:wght@400;600;700;800;900&family=Pacifico&display=swap">
<style>
.ps-wrap { --g1: #f3dc9a; --g2: #c9a24b; --ink: #17120c; --line: #e8e1d4; --surface: #fff; --muted: #6b6257; --soft: #faf7f1;
    max-width: 1320px; margin: 0 auto; padding: 8px 16px 80px; color: var(--ink); }
.dark-mode .ps-wrap { --ink: #f4efe6; --line: #2d2a26; --surface: #18171a; --muted: #a79f94; --soft: #201e22; }
.ps-hero { position: relative; overflow: hidden; border-radius: 28px; padding: 26px 24px; margin-bottom: 22px; color: #fbf6ea;
    background: radial-gradient(70% 120% at 90% 0%, rgba(201,162,75,.35), transparent 60%), radial-gradient(60% 90% at 0% 100%, rgba(10,70,60,.55), transparent 70%), linear-gradient(135deg, #0b0c0e, #141a18 55%, #1d1a10); }
.ps-hero::after { content: ''; position: absolute; inset: 10px; border: 1px solid rgba(243,220,154,.25); border-radius: 20px; pointer-events: none; }
.ps-eyebrow { font-family: 'Cinzel', Georgia, serif; font-size: .72rem; letter-spacing: .32em; text-transform: uppercase; color: var(--g1); margin-bottom: 8px; }
.ps-hero h1 { font-family: 'Playfair Display', Georgia, serif; font-weight: 500; font-size: clamp(1.6rem, 3.4vw, 2.4rem); margin: 0 0 8px; color: #fff; line-height: 1.15; }
.ps-hero h1 em { font-family: 'Great Vibes', cursive; font-style: normal; font-size: 1.25em; background: linear-gradient(110deg,#f3dc9a,#c9a24b 40%,#fff1c4 60%,#c9a24b); -webkit-background-clip: text; background-clip: text; color: transparent; }
.ps-hero p { color: rgba(251,246,234,.78); margin: 0; max-width: 640px; }
@media (max-width: 575.98px) { .ps-hero { padding: 20px 18px; margin-bottom: 14px; } .ps-hero p { display: none; } }

.ps-grid { display: grid; grid-template-columns: minmax(0,1fr); gap: 22px; }
@media (min-width: 992px) { .ps-grid { grid-template-columns: minmax(0, 6fr) minmax(0, 5fr); gap: 28px; } }
.ps-preview-col { order: -1; }
@media (min-width: 992px) { .ps-preview-col { order: 2; } }
.ps-stage { position: sticky; top: 84px; border-radius: 28px; padding: 22px; background: radial-gradient(70% 55% at 50% 30%, rgba(201,162,75,.18), transparent 70%), #0e0c10; box-shadow: inset 0 0 0 1px rgba(243,220,154,.14), 0 30px 60px -36px rgba(0,0,0,.7); }
@media (max-width: 991.98px) { .ps-stage { position: relative; top: 0; padding: 16px; } }
.ps-canvas-wrap { position: relative; margin: 0 auto; max-width: 440px; }
@media (max-width: 575.98px) { .ps-canvas-wrap { max-width: 320px; } }
.ps-canvas-wrap canvas { display: block; width: 100%; height: auto; border-radius: 10px; box-shadow: 0 24px 50px -20px rgba(0,0,0,.85), 0 0 0 1px rgba(243,220,154,.18); background: #1b191d; }
.ps-ratios { display: flex; justify-content: center; flex-wrap: wrap; gap: 8px; margin-top: 14px; }
.ps-ratio { min-height: 44px; padding: 8px 14px; border-radius: 999px; border: 1px solid rgba(243,220,154,.25); background: rgba(255,255,255,.05); color: #efe6d4; font-size: .8rem; font-weight: 600; cursor: pointer; }
.ps-ratio[aria-pressed="true"] { background: linear-gradient(110deg,#f3dc9a,#c9a24b); color: #1b1406; border-color: transparent; }
.ps-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 16px; }
.ps-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 50px; padding: 10px 14px; border-radius: 16px; border: 1px solid rgba(243,220,154,.22); background: rgba(255,255,255,.06); color: #f4efe6; font-weight: 600; font-size: .9rem; cursor: pointer; text-decoration: none; }
.ps-btn:hover { background: rgba(255,255,255,.12); color: #fff; }
.ps-btn[disabled] { opacity: .6; cursor: progress; }
.ps-btn--wa { grid-column: 1 / -1; background: linear-gradient(135deg, #25d366, #128c4a); border: 0; color: #fff; font-size: 1rem; min-height: 54px; }
.ps-btn--gold { background: linear-gradient(110deg, #f3dc9a, #c9a24b 50%, #f3dc9a); color: #1b1406; border: 0; }
.ps-btn--gold:hover { color: #1b1406; }
.ps-note { text-align: center; color: rgba(239,230,212,.55); font-size: .76rem; margin: 10px 0 0; }

.ps-panel { background: var(--surface); border: 1px solid var(--line); border-radius: 24px; padding: 20px 18px; box-shadow: 0 20px 40px -32px rgba(23,18,12,.35); }
@media (min-width: 768px) { .ps-panel { padding: 26px; } }
.ps-step { padding-bottom: 22px; margin-bottom: 22px; border-bottom: 1px solid var(--line); }
.ps-step:last-child { border-bottom: 0; margin-bottom: 0; padding-bottom: 0; }
.ps-step__head { display: flex; align-items: baseline; gap: 12px; margin-bottom: 12px; }
.ps-step__num { font-family: 'Cinzel', Georgia, serif; font-size: .78rem; color: var(--g2); letter-spacing: .12em; }
.ps-step__title { font-family: 'Playfair Display', Georgia, serif; font-size: 1.2rem; font-weight: 500; margin: 0; color: var(--ink); }
.ps-sub { color: var(--muted); font-size: .84rem; margin: -4px 0 12px; }

.ps-occasions { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 10px; }
@media (min-width: 576px) { .ps-occasions { grid-template-columns: repeat(3, minmax(0,1fr)); } }
.ps-tile { display: flex; align-items: center; gap: 10px; min-height: 56px; padding: 10px 12px; border-radius: 16px; border: 1.5px solid var(--line); background: var(--soft); color: var(--ink); cursor: pointer; text-align: left; font: inherit; }
.ps-tile[aria-pressed="true"] { border-color: var(--g2); background: linear-gradient(135deg, rgba(243,220,154,.3), rgba(201,162,75,.08)); box-shadow: 0 8px 20px -12px rgba(201,162,75,.8); }
.ps-tile__emoji { font-size: 1.3rem; flex: none; }
.ps-tile__name { font-size: .84rem; font-weight: 600; line-height: 1.2; }

.ps-event { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-top: 12px; padding: 12px 14px; border-radius: 16px; border: 1px dashed #cdb47a; background: linear-gradient(160deg, rgba(243,220,154,.15), transparent); }
.ps-event label { font-size: .82rem; font-weight: 700; color: var(--muted); }
.ps-select, .ps-input, .ps-textarea { width: 100%; min-height: 48px; padding: 11px 14px; border-radius: 14px; border: 1.5px solid var(--line); background: var(--surface); color: var(--ink); font-size: 1rem; font-family: inherit; }
.ps-select:focus, .ps-input:focus, .ps-textarea:focus { outline: none; border-color: var(--g2); box-shadow: 0 0 0 4px rgba(201,162,75,.18); }
.ps-event .ps-select { flex: 1; min-width: 200px; }

.ps-layouts { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 10px; }
@media (min-width: 576px) { .ps-layouts { grid-template-columns: repeat(5, minmax(0,1fr)); } }
.ps-layout { padding: 8px; border-radius: 16px; border: 1.5px solid var(--line); background: var(--soft); cursor: pointer; text-align: left; font: inherit; color: var(--ink); }
.ps-layout[aria-pressed="true"] { border-color: var(--g2); box-shadow: 0 10px 22px -14px rgba(201,162,75,.95); }
.ps-layout canvas { display: block; width: 100%; aspect-ratio: 4/5; border-radius: 8px; background: #121014; object-fit: cover; }
.ps-layout span { display: block; font-size: .74rem; font-weight: 700; margin-top: 6px; line-height: 1.2; }

.ps-swatches { display: flex; flex-wrap: wrap; gap: 8px; }
.ps-swatch { display: inline-flex; align-items: center; gap: 8px; min-height: 46px; padding: 6px 14px 6px 6px; border-radius: 999px; border: 1.5px solid var(--line); background: var(--surface); cursor: pointer; font: inherit; font-size: .8rem; font-weight: 600; color: var(--ink); }
.ps-swatch[aria-pressed="true"] { border-color: var(--g2); box-shadow: 0 6px 16px -10px rgba(201,162,75,.9); }
.ps-swatch i { width: 32px; height: 32px; border-radius: 50%; display: block; box-shadow: inset 0 0 0 2px rgba(255,255,255,.6); }

.ps-drop { position: relative; display: flex; align-items: center; gap: 14px; padding: 14px; min-height: 84px; border-radius: 18px; border: 1.5px dashed #cdb47a; background: var(--soft); cursor: pointer; }
.ps-drop input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
.ps-drop__thumb { width: 60px; height: 60px; border-radius: 12px; flex: none; background: #e9e1d2; display: grid; place-items: center; overflow: hidden; color: #9b8a66; font-size: 1.4rem; }
.ps-drop__thumb img { width: 100%; height: 100%; object-fit: cover; }
.ps-drop b { display: block; font-size: .9rem; }
.ps-drop small { color: var(--muted); font-size: .76rem; }
.ps-link { position: relative; z-index: 2; background: none; border: 0; color: #9b2c2c; font-weight: 600; font-size: .82rem; min-height: 44px; padding: 0 6px; cursor: pointer; }
.ps-toggles { display: flex; flex-wrap: wrap; gap: 14px; margin-top: 12px; }
.ps-check { display: inline-flex; align-items: center; gap: 8px; min-height: 44px; font-size: .86rem; font-weight: 600; cursor: pointer; }
.ps-check input { width: 20px; height: 20px; accent-color: var(--g2); }
.ps-range { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-top: 8px; }
.ps-range label { font-size: .76rem; font-weight: 700; color: var(--muted); display: block; margin-bottom: 4px; }
.ps-range input { width: 100%; accent-color: var(--g2); min-height: 32px; }

.ps-fields { display: grid; grid-template-columns: 1fr; gap: 12px; }
@media (min-width: 576px) { .ps-fields { grid-template-columns: 1fr 1fr; } .ps-fields .ps-span { grid-column: 1 / -1; } }
.ps-label { display: block; font-size: .76rem; font-weight: 700; color: var(--muted); margin-bottom: 5px; }
.ps-group { margin-top: 18px; }
.ps-group > summary { cursor: pointer; list-style: none; display: flex; align-items: center; justify-content: space-between; min-height: 44px; font-weight: 700; font-size: .9rem; color: var(--ink); }
.ps-group > summary::-webkit-details-marker { display: none; }
.ps-group > summary::after { content: '+'; font-size: 1.2rem; color: var(--g2); }
.ps-group[open] > summary::after { content: '–'; }
.ps-draft { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 16px; padding: 10px 14px; border-radius: 14px; background: rgba(201,162,75,.12); border: 1px solid rgba(201,162,75,.35); font-size: .84rem; }
.fp-toast { position: fixed; left: 50%; bottom: 28px; z-index: 3000; transform: translate(-50%, 20px); opacity: 0; background: #17120c; color: #f4efe6; border: 1px solid rgba(243,220,154,.35); padding: 12px 18px; border-radius: 999px; font-size: .9rem; font-weight: 600; box-shadow: 0 16px 30px rgba(0,0,0,.35); transition: opacity .3s, transform .3s; }
.fp-toast.is-in { opacity: 1; transform: translate(-50%, 0); }
</style>
@endpush

@section('content')
@include('member.creativeStudioTabs', ['creativeTab' => 'flyers'])
<script nonce="{{ $nonce ?? '' }}">{!! $engineJs !!}</script>
<script nonce="{{ $nonce ?? '' }}" src="/public/js/festive-cards.js?v={{ assetVersion('js/festive-cards.js') }}"></script>

<div class="ps-wrap" x-data="posterStudio()">
    @include('csrf')

    <section class="ps-hero">
        <div class="ps-eyebrow">Flyer &amp; Poster Studio</div>
        <h1>Posters for <em>every</em> family moment</h1>
        <p>Anniversaries, reunions, naming ceremonies, birthdays, weddings and celebrations of life. Pick an occasion, add a photo and the details — then share it on WhatsApp or post it to your family feed.</p>
    </section>

    <div class="ps-grid">
        <aside class="ps-preview-col" aria-label="Poster preview">
            <div class="ps-stage">
                <div class="ps-canvas-wrap"><canvas id="psCanvas" aria-label="Poster preview"></canvas></div>
                <div class="ps-ratios" role="group" aria-label="Size">
                    <template x-for="r in ratios" :key="r.id">
                        <button type="button" class="ps-ratio" :aria-pressed="ratio === r.id" @click="setRatio(r.id)" x-text="r.label"></button>
                    </template>
                </div>
                <div class="ps-actions">
                    <button type="button" class="ps-btn ps-btn--wa" @click="shareWhatsApp()" :disabled="busy"><i class="bi bi-whatsapp" aria-hidden="true"></i><span>Share on WhatsApp</span></button>
                    <button type="button" class="ps-btn" @click="download()"><i class="bi bi-download" aria-hidden="true"></i><span>Download</span></button>
                    <button type="button" class="ps-btn ps-btn--gold" @click="postToFeed()" :disabled="busy || posted"><i class="bi" :class="posted ? 'bi-check2-circle' : 'bi-broadcast'" aria-hidden="true"></i><span x-text="posted ? 'Posted' : 'Post to feed'"></span></button>
                </div>
                <p class="ps-note">High resolution — ready for WhatsApp status, social media or printing.</p>
            </div>
        </aside>

        <div>
            <div class="ps-panel">
                <div class="ps-draft" x-show="restoredDraft" x-cloak>
                    <span><i class="bi bi-clock-history" aria-hidden="true"></i> We restored your last poster.</span>
                    <button type="button" class="ps-link" @click="startOver()">Start over</button>
                </div>

                <section class="ps-step">
                    <div class="ps-step__head"><span class="ps-step__num">I</span><h2 class="ps-step__title">What’s the occasion?</h2></div>
                    <div class="ps-occasions" role="group" aria-label="Occasion">
                        <template x-for="p in presets" :key="p.key">
                            <button type="button" class="ps-tile" :aria-pressed="presetKey === p.key" @click="applyPreset(p.key)">
                                <span class="ps-tile__emoji" x-text="p.emoji" aria-hidden="true"></span>
                                <span class="ps-tile__name" x-text="p.label"></span>
                            </button>
                        </template>
                    </div>
                    @if(!empty($events))
                    <div class="ps-event">
                        <label for="psEvent"><i class="bi bi-calendar-heart" aria-hidden="true"></i> Or start from a family event</label>
                        <select id="psEvent" class="ps-select" @change="fromEvent($event.target.value); $event.target.value = ''">
                            <option value="">Choose an upcoming event…</option>
                            @foreach($events as $i => $e)
                                <option value="{{ $i }}">{{ $e['eventName'] }} — {{ date('j M Y', strtotime((string)$e['eventDate'])) }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </section>

                <section class="ps-step">
                    <div class="ps-step__head"><span class="ps-step__num">II</span><h2 class="ps-step__title">Choose a layout</h2></div>
                    <p class="ps-sub">Each preview uses your words and photo.</p>
                    <div class="ps-layouts" role="group" aria-label="Layout">
                        <template x-for="l in layouts" :key="l.id">
                            <button type="button" class="ps-layout" :aria-pressed="archetype === l.id" @click="setLayout(l.id)">
                                <canvas :data-thumb="l.id" width="216" height="270"></canvas>
                                <span x-text="l.label"></span>
                            </button>
                        </template>
                    </div>
                    <div class="ps-group" style="margin-top:16px">
                        <span class="ps-label">Colours</span>
                        <div class="ps-swatches" role="group" aria-label="Colours">
                            <template x-for="t in themes" :key="t.id">
                                <button type="button" class="ps-swatch" :aria-pressed="theme === t.id" @click="setTheme(t.id)">
                                    <i :style="'background:' + t.swatch"></i><span x-text="t.label"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </section>

                <section class="ps-step">
                    <div class="ps-step__head"><span class="ps-step__num">III</span><h2 class="ps-step__title">Add a photo <span style="font-size:.8rem;color:var(--muted)">(optional)</span></h2></div>
                    <label class="ps-drop">
                        <input type="file" accept="image/*" @change="onPhoto($event.target.files[0]); $event.target.value = ''" aria-label="Upload a photo">
                        <span class="ps-drop__thumb">
                            <template x-if="photoUrl"><img :src="photoUrl" alt=""></template>
                            <template x-if="!photoUrl"><i class="bi bi-person-bounding-box" aria-hidden="true"></i></template>
                        </span>
                        <span style="flex:1;min-width:0">
                            <b x-text="photoUrl ? 'Photo added' : 'Add the celebrant, the couple or the family'"></b>
                            <small>A portrait with a plain background works best. Your photo never leaves your device until you share.</small>
                        </span>
                        <button type="button" class="ps-link" x-show="photoUrl" x-cloak @click.prevent.stop="removePhoto()">Remove</button>
                    </label>
                    <div x-show="photoUrl" x-cloak>
                        <div class="ps-toggles">
                            <label class="ps-check"><input type="checkbox" x-model="cutout" @change="reprocessPhoto()"> Remove photo background <span x-show="cutting" x-cloak style="font-weight:400;color:var(--muted)">— finding the person…</span></label>
                            <label class="ps-check"><input type="checkbox" x-model="ghost" @change="render()"> Soft echo behind photo</label>
                        </div>
                        <div class="ps-range">
                            <div><label>Photo size <span x-text="Math.round(scale * 100) + '%'"></span></label><input type="range" min="0.6" max="1.6" step="0.02" x-model.number="scale" @input="render()"></div>
                            <div><label>Move up / down</label><input type="range" min="-400" max="400" step="5" x-model.number="offsetY" @input="render()" aria-label="Move photo up or down"></div>
                            <div><label>Move left / right</label><input type="range" min="-500" max="500" step="5" x-model.number="offsetX" @input="render()" aria-label="Move photo left or right"></div>
                            <div style="display:flex;align-items:flex-end"><button type="button" class="ps-link" style="color:var(--g2)" @click="scale = 1; offsetX = 0; offsetY = 0; render()">Reset position</button></div>
                        </div>
                    </div>
                    <details class="ps-group">
                        <summary>Use your own background picture</summary>
                        <label class="ps-drop" style="min-height:64px">
                            <input type="file" accept="image/*" @change="onBackdrop($event.target.files[0]); $event.target.value = ''" aria-label="Upload a background">
                            <span style="flex:1"><b x-text="hasBackdrop ? 'Custom background in use' : 'Choose a background picture'"></b><small>Venues, gardens or family homes look lovely.</small></span>
                            <button type="button" class="ps-link" x-show="hasBackdrop" x-cloak @click.prevent.stop="removeBackdrop()">Remove</button>
                        </label>
                    </details>
                </section>

                <section class="ps-step">
                    <div class="ps-step__head"><span class="ps-step__num">IV</span><h2 class="ps-step__title">The details</h2></div>
                    <div class="ps-fields">
                        <div class="ps-span"><label class="ps-label" for="psBadge">Top line</label><input id="psBadge" class="ps-input" x-model="d.badge" @input="changed()" placeholder="e.g. The Olaogun Family invites you to"></div>
                        <div><label class="ps-label" for="psKicker">Script line</label><input id="psKicker" class="ps-input" x-model="d.kicker" @input="changed()" placeholder="e.g. Golden 50th"></div>
                        <div><label class="ps-label" for="psTitle">Main headline</label><input id="psTitle" class="ps-input" x-model="d.title" @input="changed()" placeholder="e.g. ANNIVERSARY"></div>
                        <div><label class="ps-label" for="psTitle2">Second headline</label><input id="psTitle2" class="ps-input" x-model="d.titleSecondary" @input="changed()" placeholder="e.g. Thanksgiving & Dinner"></div>
                        <div><label class="ps-label" for="psAccent">Honour badge</label><input id="psAccent" class="ps-input" x-model="d.accentMetric" @input="changed()" placeholder="e.g. 50 YEARS"></div>
                        <div><label class="ps-label" for="psDate">Date</label><input id="psDate" class="ps-input" x-model="d.date" @input="changed()" placeholder="e.g. SAT 22 AUG"></div>
                        <div><label class="ps-label" for="psTime">Time</label><input id="psTime" class="ps-input" x-model="d.time" @input="changed()" placeholder="e.g. 4:00 PM"></div>
                        <div><label class="ps-label" for="psVenue">Venue</label><input id="psVenue" class="ps-input" x-model="d.venue" @input="changed()" placeholder="e.g. St Mary’s Hall"></div>
                        <div><label class="ps-label" for="psAddress">Address</label><input id="psAddress" class="ps-input" x-model="d.address" @input="changed()" placeholder="Street, town, postcode"></div>
                    </div>
                    <details class="ps-group">
                        <summary>More details (hosts, dress code, programme)</summary>
                        <div class="ps-fields">
                            <div><label class="ps-label" for="psHosts">Hosted by</label><input id="psHosts" class="ps-input" x-model="d.hosts" @input="changed()" placeholder="e.g. The children & grandchildren"></div>
                            <div><label class="ps-label" for="psDress">Dress code</label><input id="psDress" class="ps-input" x-model="d.dressCode" @input="changed()" placeholder="e.g. Gold & ivory"></div>
                            <div><label class="ps-label" for="psMusic">Music &amp; entertainment</label><input id="psMusic" class="ps-input" x-model="d.musicBy" @input="changed()" placeholder="e.g. Live highlife band"></div>
                            <div><label class="ps-label" for="psPerks">Highlights (comma separated)</label><input id="psPerks" class="ps-input" x-model="perks" @input="changed()" placeholder="e.g. Dinner, Speeches, Dancing"></div>
                            <div class="ps-span"><label class="ps-label" for="psFooter">Bottom strip</label><input id="psFooter" class="ps-input" x-model="d.footerStrip" @input="changed()" placeholder="e.g. All generations welcome • Free parking"></div>
                            <div><label class="ps-label" for="psWater">Large background word</label><input id="psWater" class="ps-input" x-model="d.watermarkText" @input="changed()" placeholder="e.g. FIFTY"></div>
                        </div>
                    </details>
                    <details class="ps-group" open>
                        <summary>RSVP &amp; QR code</summary>
                        <div class="ps-fields">
                            <div><label class="ps-label" for="psRsvp">RSVP line</label><input id="psRsvp" class="ps-input" x-model="d.rsvpContact" @input="changed()" placeholder="e.g. RSVP to Funmi • 07700 900200"></div>
                            <div><label class="ps-label" for="psQr">QR code opens</label><input id="psQr" class="ps-input" type="url" x-model="d.qrUrl" @input="changed()"></div>
                        </div>
                        <p class="ps-sub" style="margin:8px 0 0">By default the QR code invites guests to join your family on FamilyPlatform.</p>
                    </details>
                </section>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ $nonce ?? '' }}">
/*
 * On-device person cut-out (MediaPipe selfie multiclass segmenter). The photo never leaves the device.
 * Returns a blob URL of a transparent PNG cropped to the person.
 */
let __fpSegmenter = null;
async function segmentPerson(src) {
    const MP = 'https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@0.10.14';
    const MODEL = 'https://storage.googleapis.com/mediapipe-models/image_segmenter/selfie_multiclass_256x256/float32/latest/selfie_multiclass_256x256.tflite';
    const img = new Image();
    img.src = src;
    await img.decode();
    if (!__fpSegmenter) {
        const vision = await import(MP + '/vision_bundle.mjs');
        const fileset = await vision.FilesetResolver.forVisionTasks(MP + '/wasm');
        __fpSegmenter = await vision.ImageSegmenter.createFromOptions(fileset, {
            baseOptions: { modelAssetPath: MODEL, delegate: 'CPU' },
            runningMode: 'IMAGE', outputCategoryMask: false, outputConfidenceMasks: true,
        });
    }
    const W = img.naturalWidth, H = img.naturalHeight;
    const res = __fpSegmenter.segment(img);
    const bg = res.confidenceMasks[0];
    const mw = bg.width, mh = bg.height;
    const conf = bg.getAsFloat32Array();
    const mask = document.createElement('canvas');
    mask.width = mw; mask.height = mh;
    const mctx = mask.getContext('2d');
    const md = mctx.createImageData(mw, mh);
    let minX = mw, minY = mh, maxX = -1, maxY = -1;
    for (let i = 0; i < conf.length; i++) {
        const person = 1 - conf[i];
        const a = Math.max(0, Math.min(1, (person - 0.3) / 0.4));
        md.data[i * 4 + 3] = Math.round(a * 255);
        if (a > 0.5) { const x = i % mw, y = (i / mw) | 0; if (x < minX) minX = x; if (x > maxX) maxX = x; if (y < minY) minY = y; if (y > maxY) maxY = y; }
    }
    res.close && res.close();
    if (maxX < 0) throw new Error('No person found in the photo');
    mctx.putImageData(md, 0, 0);
    const full = document.createElement('canvas');
    full.width = W; full.height = H;
    const fctx = full.getContext('2d');
    fctx.drawImage(img, 0, 0);
    fctx.globalCompositeOperation = 'destination-in';
    fctx.imageSmoothingEnabled = true; fctx.imageSmoothingQuality = 'high';
    if ('filter' in fctx) fctx.filter = 'blur(' + Math.max(1, Math.round(W / 700)) + 'px)';
    fctx.drawImage(mask, 0, 0, W, H);
    // Crop to the person with a small margin so the poster layout can size them properly
    const sx = W / mw, sy = H / mh, pad = 0.03;
    const cx0 = Math.max(0, Math.floor((minX / mw - pad) * W)), cy0 = Math.max(0, Math.floor((minY / mh - pad) * H));
    const cx1 = Math.min(W, Math.ceil(((maxX + 1) / mw + pad) * W)), cy1 = Math.min(H, Math.ceil(((maxY + 1) / mh + pad) * H));
    const out = document.createElement('canvas');
    out.width = Math.max(1, cx1 - cx0); out.height = Math.max(1, cy1 - cy0);
    out.getContext('2d').drawImage(full, cx0, cy0, out.width, out.height, 0, 0, out.width, out.height);
    const blob = await new Promise((r) => out.toBlob(r, 'image/png'));
    return URL.createObjectURL(blob);
}

function posterStudio() {
    const SURNAME = @json($lastName, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    const FAMILY = SURNAME ? ('The ' + SURNAME + ' Family') : 'Our Family';
    const FAMILY_UP = FAMILY.toUpperCase();
    const JOIN_URL = @json($joinUrl, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    const EVENTS = @json($events, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    const DRAFT_KEY = 'fp_poster_draft_v1';
    const ASSET = '/poster-studio-asset/';
    const blank = { badge: '', kicker: '', title: '', titleSecondary: '', subtitle: '', accentMetric: '', date: '', time: '', venue: '', address: '', hosts: '', dressCode: '', musicBy: '', footerStrip: '', watermarkText: '', rsvpContact: '', regulatoryNotice: '' };

    const PRESETS = [
        { key: 'anniversary', emoji: '💍', label: 'Wedding Anniversary', theme: 'family_heirloom_gold', archetype: 'vip_black_gold', ratio: 'portrait', perks: 'Thanksgiving, Dinner, Dancing',
          d: { badge: FAMILY_UP + ' INVITES YOU', kicker: 'Golden 50th', title: 'ANNIVERSARY', titleSecondary: 'CELEBRATION', accentMetric: '50 YEARS', date: 'SAT 22 AUG', time: '4:00 PM', venue: 'THE GRAND HALL', address: 'Add the address', dressCode: 'GOLD & IVORY', rsvpContact: 'RSVP BY 1 AUG', watermarkText: 'FIFTY' } },
        { key: 'reunion', emoji: '🏡', label: 'Family Reunion', theme: 'family_botanical', archetype: 'floral_chrome_overlap', ratio: 'portrait', perks: 'Family tree unveiling, Feast, Games',
          d: { badge: 'ANNUAL FAMILY GATHERING', kicker: 'Grand Family\nReunion', title: FAMILY_UP.replace(/^THE /, '').replace(/ FAMILY$/, '') || 'FAMILY', titleSecondary: 'Reunion', date: '15 AUG', time: '1 PM', venue: 'Add the venue', musicBy: 'LIVE MUSIC & STORYTELLING', footerStrip: 'ALL GENERATIONS WELCOME • BRING A DISH' } },
        { key: 'naming', emoji: '👶', label: 'Naming Ceremony', theme: 'family_pastel_baby', archetype: 'acrylic_keepsake_block', ratio: 'portrait', perks: '',
          d: { badge: 'JOYFUL BLESSINGS', kicker: 'Welcome', title: 'NAMING\nCEREMONY', date: 'SUNDAY 14 JUNE', time: '12 NOON', venue: 'Add the venue', hosts: 'PROUD PARENTS & GRANDPARENTS', rsvpContact: 'RSVP • add a number' } },
        { key: 'birthday', emoji: '🎂', label: 'Milestone Birthday', theme: 'party_floral_chrome', archetype: 'floral_chrome_overlap', ratio: 'portrait', perks: 'Dinner, Music, Cake',
          d: { badge: 'YOU’RE INVITED TO', kicker: 'Mum’s', title: '70TH', titleSecondary: 'BIRTHDAY', accentMetric: '70', date: 'SAT 10 OCT', time: '6:00 PM', venue: 'Add the venue', dressCode: 'ELEGANT', rsvpContact: 'RSVP • add a number' } },
        { key: 'wedding', emoji: '💐', label: 'Wedding', theme: 'party_floral_chrome', archetype: 'floral_chrome_overlap', ratio: 'portrait', perks: 'Ceremony, Reception, Dancing',
          d: { badge: 'TOGETHER WITH THEIR FAMILIES', kicker: 'Ada & Tunde', title: 'WEDDING', titleSecondary: 'Celebration', date: '12 SEP', time: '11 AM', venue: 'Add the venue', dressCode: 'TRADITIONAL ATTIRE', rsvpContact: 'RSVP BY 1 AUG' } },
        { key: 'graduation', emoji: '🎓', label: 'Graduation', theme: 'party_black_gold', archetype: 'vip_black_gold', ratio: 'portrait', perks: 'Thanksgiving, Lunch',
          d: { badge: 'CELEBRATE WITH US', kicker: 'Congratulations', title: 'GRADUATE', titleSecondary: 'CLASS OF 2026', date: 'SAT 18 JUL', time: '2:00 PM', venue: 'Add the venue', rsvpContact: 'RSVP • add a number' } },
        { key: 'life', emoji: '🕊️', label: 'Celebration of Life', theme: 'family_heirloom_gold', archetype: 'vip_black_gold', ratio: 'portrait', perks: '',
          d: { badge: 'IN LOVING MEMORY', kicker: 'Celebrating the life of', title: 'FULL NAME', titleSecondary: '1945 – 2026', date: 'FRI 4 DEC', time: '10:00 AM', venue: 'Add the venue', address: 'Reception to follow', dressCode: 'WHITE & BLACK', footerStrip: 'FOREVER IN OUR HEARTS' } },
        { key: 'thanksgiving', emoji: '🙏', label: 'Thanksgiving Service', theme: 'family_botanical', archetype: 'floral_chrome_overlap', ratio: 'portrait', perks: 'Service, Lunch',
          d: { badge: FAMILY_UP + ' INVITES YOU', kicker: 'Thanksgiving', title: 'SERVICE', titleSecondary: 'with Gratitude', date: 'SUN 6 SEP', time: '10:00 AM', venue: 'Add the church / venue', rsvpContact: 'ALL ARE WELCOME' } },
        { key: 'festive', emoji: '🎄', label: 'Festive Gathering', theme: 'party_floral_chrome', archetype: 'floral_chrome_overlap', ratio: 'portrait', perks: 'Dinner, Carols, Games',
          d: { badge: FAMILY_UP, kicker: 'Christmas', title: 'FAMILY', titleSecondary: 'Get-together', date: 'SAT 26 DEC', time: '3:00 PM', venue: 'Add the venue', dressCode: 'FESTIVE RED & GOLD' } },
    ];
    // Wording for the engine's fixed captions (shared-lib >= v2.12.5; older engines ignore it)
    const LABELS_DEFAULT = { timeLead: 'FROM', venueLine1: 'VENUE', venueLine2: '', dressLine1: 'DRESS', dressLine2: 'CODE', rsvp: 'rsvp', sealTop: '', sealBottom: '' };
    const LABEL_SETS = {
        anniversary: { ...LABELS_DEFAULT, timeLead: 'CELEBRATION FROM', sealTop: 'WITH LOVE', sealBottom: 'ALWAYS' },
        life: { timeLead: 'SERVICE AT', venueLine1: 'VENUE', venueLine2: '', dressLine1: 'DRESS', dressLine2: 'CODE', rsvp: '', sealTop: 'FOREVER', sealBottom: 'LOVED' },
        graduation: { ...LABELS_DEFAULT, timeLead: 'CELEBRATION FROM' },
        thanksgiving: { ...LABELS_DEFAULT, timeLead: 'SERVICE AT' },
    };
    PRESETS.forEach((p) => { p.labels = LABEL_SETS[p.key] || LABELS_DEFAULT; });
    const LAYOUTS = [
        { id: 'vip_black_gold', label: 'Editorial Gold' },
        { id: 'floral_chrome_overlap', label: 'Botanical Bloom' },
        { id: 'acrylic_keepsake_block', label: 'Crystal Keepsake' },
        { id: 'pool_tropical_split', label: 'Bright Split' },
        { id: 'neon_script_clash', label: 'Neon Script' },
    ];
    const THEMES = [
        { id: 'family_heirloom_gold', label: 'Heirloom Gold', swatch: 'linear-gradient(135deg,#0d3b2e 0 50%,#d4af37 50%)' },
        { id: 'party_black_gold', label: 'Black & Gold', swatch: 'linear-gradient(135deg,#0b0b0c 0 50%,#d4af37 50%)' },
        { id: 'family_botanical', label: 'Botanical', swatch: 'linear-gradient(135deg,#f6efe6 0 50%,#b5476b 50%)' },
        { id: 'party_floral_chrome', label: 'Crimson Velvet', swatch: 'linear-gradient(135deg,#6b0f1a 0 50%,#e8e8e8 50%)' },
        { id: 'family_pastel_baby', label: 'Soft Pastel', swatch: 'linear-gradient(135deg,#bfe3f5 0 50%,#f7c6d9 50%)' },
        { id: 'party_acrylic_crystal', label: 'Crystal', swatch: 'linear-gradient(135deg,#e9eef7 0 50%,#9fb3d9 50%)' },
        { id: 'party_neon', label: 'Neon Night', swatch: 'linear-gradient(135deg,#2b0a4a 0 50%,#ff3fb4 50%)' },
    ];
    const EVENT_PRESET = [[/birth/i, 'birthday'], [/wedding|marri/i, 'wedding'], [/anniv|jubilee/i, 'anniversary'], [/nam|christen|baby|dedicat/i, 'naming'], [/funeral|memor|burial|life|remembr/i, 'life'], [/graduat/i, 'graduation'], [/thank|church|service/i, 'thanksgiving'], [/christmas|festiv|new year|eid|diwali/i, 'festive']];

    let engine = null;
    let thumbTimer = null;
    let saveTimer = null;

    return {
        presets: PRESETS, themes: THEMES,
        // Bright Split suits pastel; Crystal Keepsake art is a baby teddy block, so only offer it for naming/baby posters
        get layouts() { return LAYOUTS.filter((l) => (l.id !== 'pool_tropical_split' || this.theme === 'family_pastel_baby') && (l.id !== 'acrylic_keepsake_block' || this.presetKey === 'naming')); },
        ratios: [{ id: 'portrait', label: 'Portrait 4:5' }, { id: 'story', label: 'WhatsApp Status 9:16' }, { id: 'feed', label: 'Square 1:1' }, { id: 'poster_a4', label: 'A4 Print' }],
        presetKey: 'anniversary', theme: 'family_heirloom_gold', archetype: 'vip_black_gold', ratio: 'portrait',
        d: { ...blank }, perks: '',
        photoSrc: null, photoUrl: '', cutoutUrl: null, cutting: false, cutout: true, ghost: true, scale: 1, offsetX: 0, offsetY: 0, hasBackdrop: false,
        busy: false, posted: false, restoredDraft: false,

        async init() {
            const canvas = document.getElementById('psCanvas');
            engine = new AtgUniversalPosterEngine({ canvas, aspectRatio: 'portrait', theme: this.theme, archetype: this.archetype, strictContent: true, data: { ...blank, showDefaultSubject: false } });
            window.fpPosterEngine = engine;
            const draft = this.loadDraft();
            if (draft) { Object.assign(this, draft); this.restoredDraft = true; }
            else { this.applyPreset('anniversary', true); }
            this.d.qrUrl = this.d.qrUrl || JOIN_URL;
            this.render();
            engine.preloadStudioAssets(ASSET).then(() => this.render());
            if (document.fonts && document.fonts.ready) document.fonts.ready.then(() => this.render());
        },

        applyPreset(key, silent) {
            const p = PRESETS.find((x) => x.key === key) || PRESETS[0];
            this.presetKey = p.key;
            this.theme = p.theme;
            this.archetype = p.archetype;
            this.ratio = p.ratio;
            this.d = { ...blank, ...p.d, qrUrl: (this.d && this.d.qrUrl) || JOIN_URL };
            this.perks = p.perks;
            this.posted = false;
            this.render();
            if (!silent) window.FPCards && FPCards.toast(p.label + ' poster ready to edit');
        },

        fromEvent(idx) {
            const e = EVENTS[parseInt(idx, 10)];
            if (!e) return;
            const text = (e.eventType || '') + ' ' + (e.eventName || '');
            const match = EVENT_PRESET.find(([re]) => re.test(text));
            this.applyPreset(match ? match[1] : 'reunion', true);
            const dt = e.eventDate ? new Date(e.eventDate + 'T12:00:00') : null;
            Object.assign(this.d, {
                title: String(e.eventName || this.d.title).toUpperCase().slice(0, 40),
                titleSecondary: '',
                date: dt ? dt.toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short' }).toUpperCase() : this.d.date,
                time: e.eventTime || this.d.time,
                venue: e.eventLocation || this.d.venue,
                address: '',
                qrUrl: e.party_rsvp_url || JOIN_URL,
                rsvpContact: e.party_rsvp_url ? 'SCAN TO RSVP' : this.d.rsvpContact,
            });
            this.render();
            window.FPCards && FPCards.toast('Filled in from “' + e.eventName + '”');
        },

        setLayout(id) { this.archetype = id; this.render(); },
        setTheme(id) { this.theme = id; this.render(); },
        setRatio(id) { this.ratio = id; this.render(); },
        changed() { this.posted = false; this.render(); },

        payload() {
            return {
                ...this.d,
                perks: this.perks.split(',').map((s) => s.trim()).filter(Boolean),
                tenant: 'familyplatform',
                showDefaultSubject: false,
                labels: (PRESETS.find((p) => p.key === this.presetKey) || {}).labels || LABELS_DEFAULT,
                photoMode: this.ghost ? 'ghost_double' : 'cutout_auto',
                subjectScale: this.scale,
                subjectOffsetX: this.offsetX,
                subjectOffsetY: this.offsetY,
            };
        },

        render() {
            if (!engine) return;
            engine.setAspectRatio(this.ratio);
            engine.setTheme(this.theme, this.archetype);
            engine.updateData(this.payload());
            try { engine.render(); } catch (e) { console.warn('[PosterStudio] render', e); }
            clearTimeout(thumbTimer);
            thumbTimer = setTimeout(() => this.renderThumbs(), 450);
            clearTimeout(saveTimer);
            saveTimer = setTimeout(() => this.saveDraft(), 800);
        },

        renderThumbs() {
            const off = document.createElement('canvas');
            const t = new AtgUniversalPosterEngine({ canvas: off, aspectRatio: 'portrait', theme: this.theme, archetype: this.archetype, strictContent: true, data: { ...blank, showDefaultSubject: false } });
            t.studioAssetCache = engine.studioAssetCache;
            t.backgroundImage = engine.backgroundImage;
            t.subjectImage = engine.subjectImage;
            t.processedSubjectCanvas = engine.processedSubjectCanvas;
            t.qrImage = engine.qrImage;
            t.updateData(this.payload());
            this.layouts.forEach((l) => {
                const target = document.querySelector('canvas[data-thumb="' + l.id + '"]');
                if (!target) return;
                try {
                    t.setTheme(this.theme, l.id);
                    t.render();
                    const ctx = target.getContext('2d');
                    ctx.clearRect(0, 0, target.width, target.height);
                    ctx.drawImage(off, 0, 0, target.width, target.height);
                } catch (e) { /* layout unavailable */ }
            });
        },

        async onPhoto(file) {
            if (!file) return;
            try {
                const out = await FPCards.preparePhoto(file, 1400);
                this.photoUrl = out.url;
                this.photoSrc = out.url;
                if (this.cutoutUrl) URL.revokeObjectURL(this.cutoutUrl);
                this.cutoutUrl = null;
                await this.reprocessPhoto();
            } catch (e) {
                FPCards.toast(e.message || 'We could not read that photo');
            }
        },
        async reprocessPhoto() {
            if (!this.photoSrc) return;
            if (this.cutout) {
                this.cutting = true;
                try {
                    if (!this.cutoutUrl) this.cutoutUrl = await segmentPerson(this.photoSrc);
                    await engine.setSubjectImage(this.cutoutUrl, { autoRemoveBackground: false });
                } catch (e) {
                    console.warn('[PosterStudio] AI cutout unavailable, using basic cutout', e);
                    await engine.setSubjectImage(this.photoSrc, { autoRemoveBackground: true, tolerance: 44 });
                } finally { this.cutting = false; }
            } else {
                await engine.setSubjectImage(this.photoSrc, { autoRemoveBackground: false });
            }
            this.render();
        },
        removePhoto() {
            if (this.photoUrl) URL.revokeObjectURL(this.photoUrl);
            if (this.cutoutUrl) URL.revokeObjectURL(this.cutoutUrl);
            this.photoUrl = ''; this.photoSrc = null; this.cutoutUrl = null;
            engine.subjectImage = null; engine.processedSubjectCanvas = null;
            this.render();
        },
        async onBackdrop(file) {
            if (!file) return;
            try {
                const out = await FPCards.preparePhoto(file, 1800);
                await engine.setBackgroundImage(out.url);
                this.hasBackdrop = true;
                this.render();
            } catch (e) { FPCards.toast(e.message || 'We could not read that picture'); }
        },
        removeBackdrop() { engine.backgroundImage = null; this.hasBackdrop = false; this.render(); },

        fileName(ext) {
            const slug = (this.d.title + ' ' + (this.d.titleSecondary || '')).toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '') || 'family-poster';
            return 'familyplatform-' + slug.slice(0, 40) + '.' + ext;
        },
        blob(type) { return new Promise((res) => engine.canvas.toBlob(res, type || 'image/png', 0.92)); },
        shareText() {
            const when = [this.d.date, this.d.time].filter((s) => s && s.trim()).join(' · ');
            const title = [this.d.kicker, this.d.title, this.d.titleSecondary].filter((s) => s && s.trim()).join(' ').replace(/\s+/g, ' ');
            return ['📣 *' + title + '*', when, this.d.venue, '', 'RSVP / join us: ' + (this.d.qrUrl || JOIN_URL)].filter((l, i) => l !== undefined && (l !== '' || i === 3)).join('\n');
        },

        async download() {
            const b = await this.blob('image/png');
            const a = document.createElement('a');
            a.href = URL.createObjectURL(b);
            a.download = this.fileName('png');
            document.body.appendChild(a); a.click(); a.remove();
            setTimeout(() => URL.revokeObjectURL(a.href), 2000);
            FPCards.toast('Poster downloaded');
        },

        async shareWhatsApp() {
            this.busy = true;
            try {
                const b = await this.blob('image/jpeg');
                const file = new File([b], this.fileName('jpg'), { type: 'image/jpeg' });
                if (FPCards.isTouch && navigator.canShare && navigator.canShare({ files: [file] })) {
                    try { await navigator.share({ files: [file], text: this.shareText() }); }
                    catch (e) { if (e && e.name !== 'AbortError') window.open('https://wa.me/?text=' + encodeURIComponent(this.shareText()), '_blank', 'noopener'); }
                } else {
                    await this.download();
                    window.open('https://wa.me/?text=' + encodeURIComponent(this.shareText()), '_blank', 'noopener');
                    FPCards.toast('Poster downloaded — attach it in WhatsApp');
                }
            } finally { this.busy = false; }
        },

        async postToFeed() {
            this.busy = true;
            try {
                const fd = new FormData();
                fd.append('token', document.querySelector('input[name="token"]')?.value || '');
                fd.append('caption', this.shareText().replace(/\*/g, ''));
                fd.append('poster', await this.blob('image/jpeg'), 'poster.jpg');
                const r = await fetch('/api/poster-studio/post-to-feed', { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } });
                const j = await r.json().catch(() => ({}));
                if (r.ok && j.status === 'success') { this.posted = true; FPCards.toast('Posted to your family feed'); }
                else { FPCards.toast(j.message || 'Could not post to the feed'); }
            } catch (e) {
                FPCards.toast('Network error — please try again');
            } finally { this.busy = false; }
        },

        saveDraft() {
            try { localStorage.setItem(DRAFT_KEY, JSON.stringify({ presetKey: this.presetKey, theme: this.theme, archetype: this.archetype, ratio: this.ratio, d: this.d, perks: this.perks, ghost: this.ghost, scale: this.scale, offsetX: this.offsetX, offsetY: this.offsetY })); } catch (e) {}
        },
        loadDraft() {
            try { const raw = localStorage.getItem(DRAFT_KEY); return raw ? JSON.parse(raw) : null; } catch (e) { return null; }
        },
        startOver() {
            try { localStorage.removeItem(DRAFT_KEY); } catch (e) {}
            this.restoredDraft = false;
            this.removePhoto();
            this.removeBackdrop();
            this.applyPreset('anniversary', true);
        },
    };
}
</script>
@endsection
