@extends('layouts.profileBase')

@section('title', 'Creative Studio — FamilyPlatform')
@section('meta_description', 'Curate your family lineage in Tree Studio, design family flyers, posters and festive greeting cards, and craft 4K reels in Living Archives Studio.')

@push('styles')
<style>
.cs-hub {
    --cs-ink: #0f172a;
    --cs-muted: #475569;
    --cs-surface: #ffffff;
    --cs-border: #e2e8f0;
    --cs-crimson: #a31621;
    --cs-gold: #f59e0b;
    max-width: 1140px;
    margin: 0 auto;
    padding: 16px 16px 72px;
}
.dark-mode .cs-hub {
    --cs-ink: #f8fafc;
    --cs-muted: #cbd5e1;
    --cs-surface: #141a29;
    --cs-border: #2d3748;
}

/* Engaging Studio Hero */
.cs-hero {
    position: relative;
    overflow: hidden;
    border-radius: 26px;
    padding: 28px 26px;
    margin-bottom: 20px;
    color: #ffffff;
    background:
        radial-gradient(55% 110% at 92% 12%, rgba(251, 191, 36, 0.34), transparent 60%),
        radial-gradient(55% 100% at 8% 95%, rgba(225, 29, 72, 0.52), transparent 65%),
        radial-gradient(45% 80% at 52% 0%, rgba(56, 189, 248, 0.22), transparent 60%),
        linear-gradient(135deg, #0b0714 0%, #240919 52%, #0f172a 100%);
    border: 1px solid rgba(251, 191, 36, 0.35);
    box-shadow: 0 24px 54px -20px rgba(15, 7, 20, 0.65);
}
.cs-hero::after {
    content: '';
    position: absolute;
    inset: 10px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 18px;
    pointer-events: none;
}
.cs-hero__inner {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-columns: minmax(0, 1.35fr) minmax(0, 0.9fr);
    align-items: center;
    gap: 20px;
}
@media (max-width: 900px) {
    .cs-hero__inner {
        grid-template-columns: 1fr;
    }
}
.cs-hero__marquee {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
}
.cs-marquee-tile {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.14);
    backdrop-filter: blur(10px);
    text-decoration: none;
    color: #ffffff;
    min-height: 54px;
    transition: transform 0.2s ease, background-color 0.2s ease, border-color 0.2s ease;
}
.cs-marquee-tile:hover {
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(251, 191, 36, 0.6);
    color: #ffffff;
    transform: translateY(-2px);
}
.cs-marquee-tile__icon {
    width: 36px;
    height: 36px;
    border-radius: 11px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    flex-shrink: 0;
}
.cs-marquee-tile__title {
    font-size: 0.8rem;
    font-weight: 700;
    line-height: 1.2;
    color: #ffffff;
}
.cs-marquee-tile__sub {
    font-size: 0.7rem;
    color: rgba(248, 250, 252, 0.72);
    line-height: 1.2;
}
.cs-spark-strip {
    display: flex;
    align-items: center;
    gap: 8px;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scroll-snap-type: x proximity;
    padding: 4px 2px 14px;
    margin-bottom: 10px;
}
.cs-spark-label {
    font-size: 0.74rem;
    font-weight: 800;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--cs-muted);
    white-space: nowrap;
    flex-shrink: 0;
    margin-right: 4px;
}
.cs-spark-pill {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    min-height: 44px;
    padding: 8px 14px;
    border-radius: 999px;
    background: var(--cs-surface);
    border: 1px solid var(--cs-border);
    color: var(--cs-ink);
    font-size: 0.8rem;
    font-weight: 700;
    text-decoration: none;
    white-space: nowrap;
    flex-shrink: 0;
    scroll-snap-align: start;
    box-shadow: 0 6px 16px -12px rgba(15, 23, 42, 0.25);
    transition: transform 0.18s ease, border-color 0.18s ease;
}
.cs-spark-pill:hover {
    border-color: var(--cs-gold);
    color: var(--cs-ink);
    transform: translateY(-1px);
}
.cs-hero__badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 5px 12px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(251, 191, 36, 0.4);
    color: #fde68a;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin-bottom: 10px;
}
.cs-hero__title {
    font-size: clamp(1.6rem, 3.2vw, 2.35rem);
    font-weight: 800;
    line-height: 1.15;
    letter-spacing: -0.02em;
    color: #ffffff;
    margin: 0 0 8px;
}
.cs-hero__desc {
    color: rgba(248, 250, 252, 0.85);
    font-size: 0.95rem;
    line-height: 1.5;
    max-width: 600px;
    margin: 0;
}
.cs-hero__pills {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 14px;
}
.cs-hero__pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    border-radius: 999px;
    background: rgba(15, 23, 42, 0.55);
    border: 1px solid rgba(255, 255, 255, 0.18);
    color: #f8fafc;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
}

/* Studio Cards Grid */
.cs-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
}
@media (max-width: 767.98px) {
    .cs-hub {
        padding: 10px 12px 64px;
    }
    .cs-hero {
        padding: 18px 16px;
        border-radius: 20px;
        margin-bottom: 16px;
    }
    .cs-hero__desc {
        font-size: 0.86rem;
    }
    .cs-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }
}

.cs-card {
    display: flex;
    flex-direction: column;
    background: var(--cs-surface);
    border: 1px solid var(--cs-border);
    border-radius: 22px;
    overflow: hidden;
    text-decoration: none;
    color: var(--cs-ink);
    box-shadow: 0 12px 30px -18px rgba(15, 23, 42, 0.25);
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease, border-color 0.25s ease;
}
@media (hover: hover) {
    .cs-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 24px 48px -20px rgba(15, 23, 42, 0.35);
        border-color: rgba(201, 162, 75, 0.65);
    }
    .cs-card:hover .cs-stage__art {
        transform: scale(1.03);
    }
}
.cs-card:active {
    transform: scale(0.985);
}
.cs-card:focus-visible {
    outline: 2px solid var(--cs-gold);
    outline-offset: 2px;
}

/* Bespoke Visual Stage per Sub-Container */
.cs-stage {
    position: relative;
    height: 220px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 14px;
}
@media (max-width: 575.98px) {
    .cs-stage {
        height: 196px;
    }
    .cs-hero__marquee {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
    }
    .cs-marquee-tile {
        padding: 8px 10px;
        min-height: 48px;
    }
}
.cs-stage--tree {
    background:
        radial-gradient(65% 95% at 78% 18%, rgba(56, 189, 248, 0.28), transparent 65%),
        radial-gradient(65% 95% at 18% 88%, rgba(16, 185, 129, 0.34), transparent 65%),
        radial-gradient(50% 60% at 50% 50%, rgba(251, 191, 36, 0.16), transparent 70%),
        linear-gradient(135deg, #071324 0%, #0f2942 52%, #062c22 100%);
}
.cs-stage--flyers {
    background:
        radial-gradient(70% 95% at 85% 20%, rgba(251, 191, 36, 0.36), transparent 65%),
        radial-gradient(70% 90% at 12% 88%, rgba(244, 63, 94, 0.36), transparent 65%),
        linear-gradient(135deg, #1f100c 0%, #4a151b 52%, #29180c 100%);
}
.cs-stage--cards {
    background:
        radial-gradient(75% 95% at 82% 16%, rgba(244, 63, 94, 0.42), transparent 65%),
        radial-gradient(70% 90% at 14% 86%, rgba(245, 158, 11, 0.36), transparent 65%),
        linear-gradient(135deg, #1a0614 0%, #4a0e27 55%, #26102e 100%);
}
.cs-stage--archives {
    background:
        radial-gradient(75% 95% at 82% 15%, rgba(251, 191, 36, 0.3), transparent 60%),
        radial-gradient(70% 90% at 12% 88%, rgba(99, 102, 241, 0.38), transparent 65%),
        linear-gradient(135deg, #060a14 0%, #111827 52%, #1e1b4b 100%);
}

.cs-stage__tag {
    position: absolute;
    top: 12px;
    left: 12px;
    z-index: 2;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 999px;
    background: rgba(15, 23, 42, 0.72);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #f8fafc;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}
.cs-stage__dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #fbbf24;
    animation: csPulse 2s ease-in-out infinite;
}
.cs-stage__art {
    width: 100%;
    max-width: 340px;
    height: 184px;
    transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}

/* Card Body */
.cs-card__body {
    padding: 20px 20px 22px;
    display: flex;
    flex-direction: column;
    flex: 1;
}
.cs-card__title {
    font-size: 1.2rem;
    font-weight: 800;
    color: var(--cs-ink);
    margin: 0 0 6px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.cs-card__desc {
    font-size: 0.88rem;
    line-height: 1.5;
    color: var(--cs-muted);
    margin: 0 0 14px;
}
.cs-card__chips {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 16px;
}
.cs-chip {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 999px;
    background: rgba(148, 163, 184, 0.14);
    color: var(--cs-ink);
    font-size: 0.74rem;
    font-weight: 600;
    white-space: nowrap;
}
.cs-card__cta {
    margin-top: auto;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 44px;
    padding: 10px 18px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 0.88rem;
    white-space: nowrap;
    flex-shrink: 0;
    align-self: flex-start;
}

/* Subtle Delight Animations */
@keyframes csPulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.45; transform: scale(0.8); }
}
@keyframes csFloat {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-4px); }
}
@keyframes csWave {
    0%, 100% { transform: scaleY(0.45); }
    50% { transform: scaleY(1); }
}
@keyframes csTwinkle {
    0%, 100% { opacity: 0.35; transform: scale(0.82); }
    50% { opacity: 1; transform: scale(1.15); }
}
@keyframes csPlayhead {
    0% { transform: translateX(0px); }
    50% { transform: translateX(52px); }
    100% { transform: translateX(0px); }
}
.cs-anim-float {
    animation: csFloat 3.6s ease-in-out infinite;
}
.cs-anim-float-delay {
    animation: csFloat 4.2s ease-in-out 0.8s infinite;
}
.cs-anim-twinkle {
    transform-box: fill-box;
    transform-origin: center;
    animation: csTwinkle 2.8s ease-in-out infinite;
}
.cs-anim-twinkle--2 {
    animation-delay: 1.1s;
}
.cs-anim-playhead {
    animation: csPlayhead 4.5s ease-in-out infinite;
}
.cs-wave-bar {
    transform-origin: center bottom;
    animation: csWave 1.4s ease-in-out infinite;
}
.cs-wave-bar--2 { animation-delay: 0.2s; }
.cs-wave-bar--3 { animation-delay: 0.4s; }
.cs-wave-bar--4 { animation-delay: 0.6s; }
.cs-wave-bar--5 { animation-delay: 0.3s; }

@media (prefers-reduced-motion: reduce) {
    .cs-stage__dot,
    .cs-anim-float,
    .cs-anim-float-delay,
    .cs-anim-twinkle,
    .cs-anim-playhead,
    .cs-wave-bar {
        animation: none !important;
    }
    .cs-card,
    .cs-stage__art {
        transition: none !important;
    }
}
</style>
@endpush

@section('content')
<div class="cs-hub">
    @include('member.creativeStudioTabs', ['creativeTab' => 'hub'])

    <header class="cs-hero">
        <div class="cs-hero__inner">
            <div>
                <div class="cs-hero__badge">
                    <i class="bi bi-sparkles" aria-hidden="true"></i>
                    <span>4 Interactive Studios</span>
                </div>
                <div class="d-flex align-items-center gap-3 mb-2">
                    <span class="creative-3d-icon-wrap creative-3d-icon-wrap--lg" aria-hidden="true">
                        <span class="creative-3d-gem">
                            <span class="creative-3d-sheen"></span>
                            <svg class="creative-3d-svg" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2.5L14.4 7.8L20.1 8.5L15.8 12.4L17 18.1L12 15.2L7 18.1L8.2 12.4L3.9 8.5L9.6 7.8L12 2.5Z" fill="#fef08a" stroke="#ffffff" stroke-width="1.2" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="creative-3d-spark creative-3d-spark--1"></span>
                        <span class="creative-3d-spark creative-3d-spark--2"></span>
                    </span>
                    <h1 class="cs-hero__title mb-0">Creative Studio</h1>
                </div>
                <p class="cs-hero__desc">
                    Your family's playful design &amp; storytelling playground. Map your living lineage, craft celebration posters, send 3D wax-sealed festive cards, and cut 4K reels in minutes.
                </p>
                <div class="cs-hero__pills">
                    <span class="cs-hero__pill"><i class="bi bi-phone text-warning" aria-hidden="true"></i> CapCut-Style Mobile Studio</span>
                    <span class="cs-hero__pill"><i class="bi bi-whatsapp text-success" aria-hidden="true"></i> Instant WhatsApp Share</span>
                    <span class="cs-hero__pill"><i class="bi bi-film text-info" aria-hidden="true"></i> 4K Direct MP4</span>
                </div>
            </div>
            <div class="cs-hero__marquee" aria-label="Quick studio launchers">
                <a href="/familyStudio" class="cs-marquee-tile">
                    <span class="cs-marquee-tile__icon" style="background:rgba(56,189,248,0.22);color:#7dd3fc;"><i class="bi bi-diagram-3-fill"></i></span>
                    <span>
                        <span class="cs-marquee-tile__title d-block">Tree Studio</span>
                        <span class="cs-marquee-tile__sub d-block">Lineage &amp; Roots</span>
                    </span>
                </a>
                <a href="/poster-studio" class="cs-marquee-tile">
                    <span class="cs-marquee-tile__icon" style="background:rgba(251,191,36,0.22);color:#fde68a;"><i class="bi bi-easel2-fill"></i></span>
                    <span>
                        <span class="cs-marquee-tile__title d-block">Flyers &amp; Posters</span>
                        <span class="cs-marquee-tile__sub d-block">Reunions &amp; Events</span>
                    </span>
                </a>
                <a href="/world-studio" class="cs-marquee-tile">
                    <span class="cs-marquee-tile__icon" style="background:rgba(124,92,255,0.25);color:#ddd6fe;"><i class="bi bi-film"></i></span>
                    <span>
                        <span class="cs-marquee-tile__title d-block">Video &amp; Photo Lab</span>
                        <span class="cs-marquee-tile__sub d-block">Multi-Track Editor</span>
                    </span>
                </a>
                <a href="/member/festive-cards" class="cs-marquee-tile">
                    <span class="cs-marquee-tile__icon" style="background:rgba(244,63,94,0.25);color:#fda4af;"><i class="bi bi-envelope-heart-fill"></i></span>
                    <span>
                        <span class="cs-marquee-tile__title d-block">Festive Cards</span>
                        <span class="cs-marquee-tile__sub d-block">3D Wax Envelopes</span>
                    </span>
                </a>
                <a href="/family-studio" class="cs-marquee-tile">
                    <span class="cs-marquee-tile__icon" style="background:rgba(52,211,153,0.22);color:#6ee7b7;"><i class="bi bi-film"></i></span>
                    <span>
                        <span class="cs-marquee-tile__title d-block">Living Archives</span>
                        <span class="cs-marquee-tile__sub d-block">4K Reel Studio</span>
                    </span>
                </a>
            </div>
        </div>
    </header>

    <div class="cs-spark-strip" aria-label="Quick creative ideas">
        <span class="cs-spark-label"><i class="bi bi-stars text-warning me-1"></i>Try now:</span>
        <a href="/poster-studio" class="cs-spark-pill">🎉 Family Reunion Flyer</a>
        <a href="/world-studio" class="cs-spark-pill">🎞️ Multi-Track Video &amp; Photo Lab</a>
        <a href="/member/festive-cards" class="cs-spark-pill">💌 3D Birthday Wax Card</a>
        <a href="/family-studio" class="cs-spark-pill">🎬 60s Milestone 4K Reel</a>
        <a href="/familyStudio" class="cs-spark-pill">🌳 Add Grandparents &amp; Cousins</a>
        <a href="/member/festive-cards" class="cs-spark-pill">✨ Eid, Christmas &amp; Diwali</a>
    </div>

    <div class="cs-grid">
        <!-- 1. Tree Studio -->
        <a href="/familyStudio" class="cs-card" aria-label="Open Tree Studio">
            <div class="cs-stage cs-stage--tree">
                <span class="cs-stage__tag">
                    <span class="cs-stage__dot" style="background:#38bdf8;"></span>
                    Lineage Builder
                </span>
                <svg class="cs-stage__art" viewBox="0 0 340 184" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <!-- Glowing Constellation & Tree Canopy -->
                    <circle cx="170" cy="90" r="72" fill="url(#treeGlow)" opacity="0.5"/>
                    <path d="M90 142 Q170 112 250 142" stroke="rgba(52,211,153,0.28)" stroke-width="1.5" stroke-dasharray="3 4"/>
                    <!-- Golden Heritage Trunk & Branches -->
                    <path d="M108 50 C108 76, 170 66, 170 92" stroke="#fbbf24" stroke-width="2.6" stroke-linecap="round"/>
                    <path d="M232 50 C232 76, 170 66, 170 92" stroke="#fbbf24" stroke-width="2.6" stroke-linecap="round"/>
                    <path d="M170 92 L170 120" stroke="#fde68a" stroke-width="2.8" stroke-linecap="round"/>
                    <path d="M84 142 C84 116, 170 118, 170 118 C170 118, 256 116, 256 142" stroke="#38bdf8" stroke-width="2.4" stroke-linecap="round"/>
                    <path d="M170 118 L170 144" stroke="#34d399" stroke-width="2.4" stroke-linecap="round"/>

                    <!-- Grandparents Portrait Medallions -->
                    <g class="cs-anim-float">
                        <rect x="64" y="18" width="84" height="36" rx="18" fill="#0f172a" stroke="#fbbf24" stroke-width="1.8"/>
                        <circle cx="83" cy="36" r="12" fill="#f59e0b"/>
                        <circle cx="83" cy="33" r="4.2" fill="#fef3c7"/>
                        <path d="M76 43 C77.5 38.5, 88.5 38.5, 90 43" fill="#fef3c7"/>
                        <rect x="100" y="29" width="36" height="4.5" rx="2.25" fill="#f8fafc"/>
                        <rect x="100" y="37" width="24" height="3.5" rx="1.75" fill="#fbbf24"/>
                    </g>
                    <g class="cs-anim-float-delay">
                        <rect x="192" y="18" width="84" height="36" rx="18" fill="#0f172a" stroke="#38bdf8" stroke-width="1.8"/>
                        <circle cx="211" cy="36" r="12" fill="#0ea5e9"/>
                        <circle cx="211" cy="33" r="4.2" fill="#e0f2fe"/>
                        <path d="M204 43 C205.5 38.5, 216.5 38.5, 218 43" fill="#e0f2fe"/>
                        <rect x="228" y="29" width="36" height="4.5" rx="2.25" fill="#f8fafc"/>
                        <rect x="228" y="37" width="26" height="3.5" rx="1.75" fill="#7dd3fc"/>
                    </g>

                    <!-- Root Union Heart Node -->
                    <circle cx="170" cy="92" r="17" fill="#a31621" stroke="#fde68a" stroke-width="2.4"/>
                    <path d="M170 98.5l-5.2-4.8a3.4 3.4 0 0 1 4.8-4.8l.4.4.4-.4a3.4 3.4 0 0 1 4.8 4.8L170 98.5z" fill="#ffffff"/>

                    <!-- Next Generation Kids Row -->
                    <g>
                        <rect x="46" y="138" width="76" height="32" rx="16" fill="#0f172a" stroke="#34d399" stroke-width="1.6"/>
                        <circle cx="63" cy="154" r="9.5" fill="#10b981"/>
                        <circle cx="63" cy="151.5" r="3.2" fill="#ecfdf5"/>
                        <path d="M58 159.5 C59 156, 67 156, 68 159.5" fill="#ecfdf5"/>
                        <rect x="78" y="149" width="32" height="4" rx="2" fill="#e2e8f0"/>
                        <rect x="78" y="156" width="20" height="3" rx="1.5" fill="#6ee7b7"/>
                    </g>
                    <g>
                        <rect x="132" y="138" width="76" height="32" rx="16" fill="#0f172a" stroke="#fbbf24" stroke-width="1.6"/>
                        <circle cx="149" cy="154" r="9.5" fill="#f59e0b"/>
                        <circle cx="149" cy="151.5" r="3.2" fill="#fffbeb"/>
                        <path d="M144 159.5 C145 156, 153 156, 154 159.5" fill="#fffbeb"/>
                        <rect x="164" y="149" width="32" height="4" rx="2" fill="#e2e8f0"/>
                        <rect x="164" y="156" width="22" height="3" rx="1.5" fill="#fde68a"/>
                    </g>
                    <g>
                        <rect x="218" y="138" width="76" height="32" rx="16" fill="#0f172a" stroke="#f472b6" stroke-width="1.6"/>
                        <circle cx="235" cy="154" r="9.5" fill="#ec4899"/>
                        <circle cx="235" cy="151.5" r="3.2" fill="#fdf2f8"/>
                        <path d="M230 159.5 C231 156, 239 156, 240 159.5" fill="#fdf2f8"/>
                        <rect x="250" y="149" width="32" height="4" rx="2" fill="#e2e8f0"/>
                        <rect x="250" y="156" width="20" height="3" rx="1.5" fill="#f9a8d4"/>
                    </g>

                    <!-- Twinkling Heritage Fireflies -->
                    <circle cx="42" cy="74" r="3" fill="#fde68a" class="cs-anim-twinkle"/>
                    <circle cx="298" cy="78" r="3.5" fill="#7dd3fc" class="cs-anim-twinkle cs-anim-twinkle--2"/>
                    <path d="M294 34 L297 40 L303 43 L297 46 L294 52 L291 46 L285 43 L291 40 Z" fill="#6ee7b7" class="cs-anim-twinkle"/>
                    <defs>
                        <radialGradient id="treeGlow" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(170 90) scale(72)">
                            <stop stop-color="#fbbf24"/>
                            <stop offset="1" stop-color="#fbbf24" stop-opacity="0"/>
                        </radialGradient>
                    </defs>
                </svg>
            </div>
            <div class="cs-card__body">
                <h2 class="cs-card__title">
                    <i class="bi bi-diagram-3-fill text-primary" aria-hidden="true"></i>
                    <span>Tree Studio</span>
                </h2>
                <p class="cs-card__desc">Curate your living family lineage, connect parents, partners and children, and preserve generational stories.</p>
                <div class="cs-card__chips">
                    <span class="cs-chip">Generations</span>
                    <span class="cs-chip">Birth &amp; Marital Trees</span>
                    <span class="cs-chip">Access Control</span>
                </div>
                <span class="btn btn-primary text-white cs-card__cta">
                    <span>Open Tree Studio</span>
                    <i class="bi bi-arrow-right" aria-hidden="true"></i>
                </span>
            </div>
        </a>

        <!-- 2. Flyers & Posters -->
        <a href="/poster-studio" class="cs-card" aria-label="Open Flyer and Poster Studio">
            <div class="cs-stage cs-stage--flyers">
                <span class="cs-stage__tag">
                    <span class="cs-stage__dot" style="background:#fbbf24;"></span>
                    Print &amp; Social
                </span>
                <svg class="cs-stage__art" viewBox="0 0 340 184" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <!-- Festive Bunting Flags -->
                    <path d="M24 22 Q170 40 316 22" stroke="rgba(253,230,138,0.45)" stroke-width="1.5" fill="none"/>
                    <polygon points="44,24 56,26 48,38" fill="#fbbf24"/>
                    <polygon points="74,27 86,28 79,40" fill="#fb7185"/>
                    <polygon points="254,28 266,27 261,40" fill="#38bdf8"/>
                    <polygon points="284,26 296,24 292,38" fill="#fbbf24"/>

                    <!-- Back Tilted Poster (9:16 WhatsApp Story) -->
                    <g transform="rotate(-9 102 96)">
                        <rect x="52" y="28" width="92" height="132" rx="12" fill="#292524" stroke="#f59e0b" stroke-width="1.8"/>
                        <rect x="62" y="38" width="72" height="48" rx="8" fill="#7c2d12"/>
                        <circle cx="98" cy="62" r="15" fill="#fbbf24" opacity="0.92"/>
                        <path d="M88 64 A10 10 0 0 1 108 64" fill="#7c2d12"/>
                        <rect x="64" y="96" width="58" height="7" rx="3.5" fill="#fef3c7"/>
                        <rect x="72" y="109" width="42" height="4.5" rx="2.25" fill="#d6d3d1"/>
                        <rect x="76" y="122" width="34" height="14" rx="7" fill="#16a34a"/>
                        <rect x="83" y="127" width="20" height="4" rx="2" fill="#ffffff"/>
                    </g>

                    <!-- Front Hero Celebration Poster -->
                    <g class="cs-anim-float" transform="rotate(4 190 92)">
                        <rect x="134" y="20" width="112" height="146" rx="14" fill="#fffbeb" stroke="#fbbf24" stroke-width="2.2"/>
                        <rect x="144" y="30" width="92" height="16" rx="8" fill="#a31621"/>
                        <text x="190" y="41.5" text-anchor="middle" fill="#ffffff" font-size="8.5" font-weight="800" font-family="system-ui, sans-serif" letter-spacing="1.2">JUBILEE</text>
                        <!-- Arch Family Portrait Frame -->
                        <path d="M154 106 V70 A36 36 0 0 1 226 70 V106 Z" fill="#fef3c7" stroke="#c9a24b" stroke-width="1.8"/>
                        <circle cx="179" cy="70" r="10" fill="#a31621"/>
                        <path d="M166 98 C168 85, 190 85, 192 98" fill="#b45309"/>
                        <circle cx="203" cy="74" r="8.5" fill="#0f766e"/>
                        <path d="M192 98 C194 87, 212 87, 214 98" fill="#0d9488"/>
                        <!-- Poster Typography Lines -->
                        <rect x="150" y="116" width="80" height="7" rx="3.5" fill="#1c1412"/>
                        <rect x="162" y="129" width="56" height="4.5" rx="2.25" fill="#78716c"/>
                        <rect x="166" y="140" width="48" height="10" rx="5" fill="#a31621"/>
                    </g>

                    <!-- Floating Colour Swatches & Typography Badge -->
                    <g class="cs-anim-float-delay">
                        <rect x="264" y="42" width="38" height="96" rx="19" fill="rgba(15,23,42,0.84)" stroke="rgba(255,255,255,0.25)"/>
                        <circle cx="283" cy="62" r="9.5" fill="#a31621" stroke="#ffffff" stroke-width="1.6"/>
                        <circle cx="283" cy="88" r="9.5" fill="#fbbf24" stroke="#ffffff" stroke-width="1.6"/>
                        <circle cx="283" cy="114" r="9.5" fill="#0f766e" stroke="#ffffff" stroke-width="1.6"/>
                        <rect x="26" y="114" width="36" height="36" rx="11" fill="#1e293b" stroke="#fbbf24" stroke-width="1.4"/>
                        <text x="44" y="138" text-anchor="middle" fill="#fde68a" font-size="16" font-weight="800" font-family="Georgia, serif">Aa</text>
                    </g>

                    <!-- Sparkles -->
                    <path d="M36 52 L39 59 L46 62 L39 65 L36 72 L33 65 L26 62 L33 59 Z" fill="#fde68a" class="cs-anim-twinkle"/>
                    <path d="M310 132 L313 138 L319 141 L313 144 L310 150 L307 144 L301 141 L307 138 Z" fill="#fb7185" class="cs-anim-twinkle cs-anim-twinkle--2"/>
                </svg>
            </div>
            <div class="cs-card__body">
                <h2 class="cs-card__title">
                    <i class="bi bi-easel2-fill text-warning" aria-hidden="true"></i>
                    <span>Flyers &amp; Posters</span>
                </h2>
                <p class="cs-card__desc">Design eye-catching invitations and milestone posters for reunions, anniversaries, birthdays and naming ceremonies.</p>
                <div class="cs-card__chips">
                    <span class="cs-chip">5 Layouts</span>
                    <span class="cs-chip">4:5 &amp; 9:16 Formats</span>
                    <span class="cs-chip">WhatsApp Ready</span>
                </div>
                <span class="btn btn-warning text-dark cs-card__cta">
                    <span>Open Flyer Studio</span>
                    <i class="bi bi-arrow-right" aria-hidden="true"></i>
                </span>
            </div>
        </a>

        <!-- 3. Festive Cards -->
        <a href="/member/festive-cards" class="cs-card" aria-label="Open Festive Cards Studio">
            <div class="cs-stage cs-stage--cards">
                <span class="cs-stage__tag">
                    <span class="cs-stage__dot" style="background:#fb7185;"></span>
                    3D Envelopes
                </span>
                <svg class="cs-stage__art" viewBox="0 0 340 184" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <!-- Warm Envelope Glow -->
                    <circle cx="170" cy="94" r="68" fill="url(#cardGlow)" opacity="0.45"/>

                    <!-- Open Envelope Back & Upper Flap -->
                    <path d="M84 68 L170 28 L256 68 V148 A14 14 0 0 1 242 162 H98 A14 14 0 0 1 84 148 Z" fill="#6b1025" stroke="#fbbf24" stroke-width="1.8"/>

                    <!-- Sliding Couture Greeting Card -->
                    <g class="cs-anim-float">
                        <rect x="102" y="18" width="136" height="106" rx="12" fill="#fffdf7" stroke="#f59e0b" stroke-width="2"/>
                        <rect x="110" y="26" width="120" height="90" rx="8" fill="none" stroke="#e5c158" stroke-width="1.2" stroke-dasharray="3 2"/>
                        <!-- Card Crest / Star -->
                        <circle cx="170" cy="52" r="16" fill="#fef3c7" stroke="#d97706" stroke-width="1.4"/>
                        <path d="M170 41 L173.5 48.2 L181.5 49.3 L175.7 54.9 L177.1 62.8 L170 59.1 L162.9 62.8 L164.3 54.9 L158.5 49.3 L166.5 48.2 Z" fill="#b45309"/>
                        <!-- Greeting Script Lines -->
                        <rect x="132" y="76" width="76" height="6" rx="3" fill="#7f1d1d"/>
                        <rect x="144" y="88" width="52" height="4" rx="2" fill="#78716c"/>
                        <rect x="154" y="98" width="32" height="4" rx="2" fill="#d97706"/>
                    </g>

                    <!-- Envelope Front Pocket -->
                    <path d="M84 78 L170 126 L256 78 V148 A14 14 0 0 1 242 162 H98 A14 14 0 0 1 84 148 Z" fill="#a31621" stroke="#fbbf24" stroke-width="1.8"/>
                    <!-- Gold Wax Seal -->
                    <circle cx="170" cy="122" r="16" fill="#fbbf24" stroke="#fff7ed" stroke-width="2.2"/>
                    <circle cx="170" cy="122" r="10.5" fill="none" stroke="#92400e" stroke-width="1.3"/>
                    <path d="M170 116 L172 120 L176.5 120.6 L173.2 123.8 L174 128.2 L170 126.1 L166 128.2 L166.8 123.8 L163.5 120.6 L168 120 Z" fill="#78350f"/>

                    <!-- Cultural & Festive Motifs (Crescent, Hearts, Confetti) -->
                    <g class="cs-anim-float-delay">
                        <!-- Crescent Moon -->
                        <path d="M52 44 A11 11 0 1 0 64 58 A8.5 8.5 0 1 1 52 44 Z" fill="#fde68a"/>
                        <!-- Floating Heart -->
                        <path d="M286 58l-5.5-5a3.6 3.6 0 0 1 5-5l.5.5.5-.5a3.6 3.6 0 0 1 5 5L286 58z" fill="#fb7185"/>
                        <!-- Confetti Ribbons -->
                        <rect x="44" y="110" width="10" height="10" rx="2.5" transform="rotate(24 49 115)" fill="#38bdf8"/>
                        <circle cx="294" cy="116" r="5" fill="#fde68a"/>
                        <path d="M276 94 L279 101 L286 104 L279 107 L276 114 L273 107 L266 104 L273 101 Z" fill="#fde68a" class="cs-anim-twinkle"/>
                        <path d="M56 76 L58.5 81.5 L64 84 L58.5 86.5 L56 92 L53.5 86.5 L48 84 L53.5 81.5 Z" fill="#fda4af" class="cs-anim-twinkle cs-anim-twinkle--2"/>
                    </g>
                    <defs>
                        <radialGradient id="cardGlow" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(170 94) scale(68)">
                            <stop stop-color="#fbbf24"/>
                            <stop offset="1" stop-color="#fbbf24" stop-opacity="0"/>
                        </radialGradient>
                    </defs>
                </svg>
            </div>
            <div class="cs-card__body">
                <h2 class="cs-card__title">
                    <i class="bi bi-envelope-heart-fill text-danger" aria-hidden="true"></i>
                    <span>Festive Cards</span>
                </h2>
                <p class="cs-card__desc">Christmas, Easter, Eid, Diwali, birthdays and bespoke occasions — sent as living 3D wax-sealed envelopes.</p>
                <div class="cs-card__chips">
                    <span class="cs-chip">7 Design Styles</span>
                    <span class="cs-chip">Custom Occasions</span>
                    <span class="cs-chip">Living Envelopes</span>
                </div>
                <span class="btn btn-danger text-white cs-card__cta">
                    <span>Open Card Studio</span>
                    <i class="bi bi-arrow-right" aria-hidden="true"></i>
                </span>
            </div>
        </a>

        <!-- 4. Living Archives Studio -->
        <a href="/family-studio" class="cs-card" aria-label="Open Living Archives 4K Reel Studio">
            <div class="cs-stage cs-stage--archives">
                <span class="cs-stage__tag">
                    <span class="cs-stage__dot" style="background:#4ade80;"></span>
                    4K &bull; 60 FPS
                </span>
                <svg class="cs-stage__art" viewBox="0 0 340 184" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <!-- CapCut-style Mobile/Desktop Cinema Studio Frame -->
                    <rect x="30" y="14" width="280" height="156" rx="16" fill="#0b1120" stroke="#334155" stroke-width="1.8"/>
                    <!-- 16:9 Viewfinder Screen with Family Celebration Scene -->
                    <rect x="44" y="26" width="174" height="86" rx="10" fill="url(#cinemaSky)" stroke="#fbbf24" stroke-width="1.4"/>
                    <!-- Celebration Silhouette Inside Viewfinder -->
                    <circle cx="110" cy="64" r="9" fill="#fde68a" opacity="0.9"/>
                    <path d="M98 96 C100 80, 120 80, 122 96" fill="#f59e0b" opacity="0.85"/>
                    <circle cx="146" cy="68" r="8" fill="#fda4af" opacity="0.9"/>
                    <path d="M135 96 C137 82, 155 82, 157 96" fill="#fb7185" opacity="0.85"/>
                    <!-- Play Button Overlay -->
                    <circle cx="131" cy="69" r="18" fill="rgba(15, 23, 42, 0.72)" stroke="#fbbf24" stroke-width="1.8"/>
                    <path d="M126 61 L140 69 L126 77 Z" fill="#fbbf24"/>
                    <!-- REC 4K Badge -->
                    <rect x="52" y="34" width="44" height="14" rx="7" fill="rgba(163,22,33,0.94)"/>
                    <circle cx="60" cy="41" r="2.8" fill="#ffffff" class="cs-anim-twinkle"/>
                    <text x="77" y="44" text-anchor="middle" fill="#ffffff" font-size="7.5" font-weight="800" font-family="system-ui, sans-serif">4K REC</text>

                    <!-- Live Audio Spectrum Panel -->
                    <rect x="228" y="26" width="68" height="86" rx="10" fill="#111827" stroke="#334155"/>
                    <text x="262" y="40" text-anchor="middle" fill="#94a3b8" font-size="7" font-weight="700" font-family="system-ui, sans-serif" letter-spacing="0.8">BEAT SYNC</text>
                    <rect x="238" y="64" width="6" height="34" rx="3" fill="#38bdf8" class="cs-wave-bar"/>
                    <rect x="248" y="50" width="6" height="48" rx="3" fill="#fbbf24" class="cs-wave-bar cs-wave-bar--2"/>
                    <rect x="258" y="44" width="6" height="54" rx="3" fill="#f43f5e" class="cs-wave-bar cs-wave-bar--3"/>
                    <rect x="268" y="54" width="6" height="44" rx="3" fill="#fbbf24" class="cs-wave-bar cs-wave-bar--4"/>
                    <rect x="278" y="66" width="6" height="32" rx="3" fill="#34d399" class="cs-wave-bar cs-wave-bar--5"/>

                    <!-- Multi-Track Timeline (Video, Audio, Captions) -->
                    <rect x="44" y="122" width="110" height="14" rx="4" fill="#2563eb" stroke="#93c5fd" stroke-width="1"/>
                    <rect x="158" y="122" width="138" height="14" rx="4" fill="#1d4ed8"/>
                    <rect x="44" y="140" width="180" height="12" rx="4" fill="#b45309"/>
                    <rect x="228" y="140" width="68" height="12" rx="4" fill="#047857"/>
                    <!-- Sweeping Golden Playhead -->
                    <g class="cs-anim-playhead">
                        <line x1="138" y1="116" x2="138" y2="158" stroke="#fde047" stroke-width="2.4"/>
                        <circle cx="138" cy="116" r="4" fill="#fde047"/>
                    </g>
                    <defs>
                        <linearGradient id="cinemaSky" x1="44" y1="26" x2="218" y2="112" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#1e293b"/>
                            <stop offset="0.55" stop-color="#31102f"/>
                            <stop offset="1" stop-color="#0f172a"/>
                        </linearGradient>
                    </defs>
                </svg>
            </div>
            <div class="cs-card__body">
                <h2 class="cs-card__title">
                    <i class="bi bi-film text-primary" aria-hidden="true"></i>
                    <span>Living Archives Studio</span>
                </h2>
                <p class="cs-card__desc">Browser-native 4K Reel Studio with multi-angle Chrono-Sync matrix, heritage motion templates and direct MP4 downloads.</p>
                <div class="cs-card__chips">
                    <span class="cs-chip">16:9 &bull; 1:1 &bull; 9:16</span>
                    <span class="cs-chip">Chrono-Sync</span>
                    <span class="cs-chip">12-Module Suite</span>
                </div>
                <span class="btn btn-dark text-white cs-card__cta">
                    <span>Open Living Archives</span>
                    <i class="bi bi-arrow-right" aria-hidden="true"></i>
                </span>
            </div>
        </a>
    </div>
</div>
@endsection
