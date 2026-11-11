{{--
    FamilyPlatform — Couture Occasion Illustration & Ambient Motif Sprite Bank (RFC-0019 v2.2)
    Emitted once per document via @once and referenced by every <article class="fc-card"> via <use>.
    Inherits --fc-primary, --fc-accent, --fc-light, --fc-deep, --fc-paper, --fc-ink across the shadow boundary.
--}}
@if(empty($GLOBALS['__fc_occasion_art_rendered']))
@php $GLOBALS['__fc_occasion_art_rendered'] = true; @endphp
<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" style="position:absolute;width:0;height:0;overflow:hidden;pointer-events:none">
    <defs>
        <linearGradient id="fcg-gold" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#fff6c9"/>
            <stop offset="28%" stop-color="#e5c158"/>
            <stop offset="55%" stop-color="#b38728"/>
            <stop offset="78%" stop-color="#fbf0b9"/>
            <stop offset="100%" stop-color="#c99e32"/>
        </linearGradient>
        <linearGradient id="fcg-pine" x1="0%" y1="0%" x2="0%" y2="100%">
            <stop offset="0%" stop-color="#1f8a58"/>
            <stop offset="55%" stop-color="#0f5c38"/>
            <stop offset="100%" stop-color="#073620"/>
        </linearGradient>
        <linearGradient id="fcg-pine-dark" x1="0%" y1="0%" x2="0%" y2="100%">
            <stop offset="0%" stop-color="#146c43"/>
            <stop offset="100%" stop-color="#052917"/>
        </linearGradient>
        <linearGradient id="fcg-snow" x1="0%" y1="0%" x2="0%" y2="100%">
            <stop offset="0%" stop-color="#ffffff"/>
            <stop offset="65%" stop-color="#eef5ff"/>
            <stop offset="100%" stop-color="#d4e3f7"/>
        </linearGradient>
        <linearGradient id="fcg-crimson" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#fb7185"/>
            <stop offset="45%" stop-color="#e11d48"/>
            <stop offset="100%" stop-color="#881337"/>
        </linearGradient>
        <linearGradient id="fcg-flame" x1="50%" y1="0%" x2="50%" y2="100%">
            <stop offset="0%" stop-color="#ffffff"/>
            <stop offset="35%" stop-color="#fef08a"/>
            <stop offset="72%" stop-color="#f59e0b"/>
            <stop offset="100%" stop-color="#dc2626"/>
        </linearGradient>
        <radialGradient id="fcg-glow" cx="50%" cy="50%" r="50%">
            <stop offset="0%" stop-color="#fffbeb" stop-opacity="0.95"/>
            <stop offset="35%" stop-color="#fde047" stop-opacity="0.55"/>
            <stop offset="100%" stop-color="#fde047" stop-opacity="0"/>
        </radialGradient>
        <radialGradient id="fcg-aura" cx="50%" cy="44%" r="56%">
            <stop offset="0%" stop-color="#fde68a" stop-opacity="0.38"/>
            <stop offset="55%" stop-color="#d4af37" stop-opacity="0.14"/>
            <stop offset="100%" stop-color="#000000" stop-opacity="0"/>
        </radialGradient>

        {{-- Reusable 6-point crystalline snowflake --}}
        <g id="fcp-snowflake" stroke="#ffffff" stroke-linecap="round" fill="none">
            <path d="M0-10V10M-8.66-5L8.66 5M-8.66 5L8.66-5" stroke-width="1.5"/>
            <path d="M-2.5-6.5L0-4.2L2.5-6.5M-2.5 6.5L0 4.2L2.5 6.5M-6.8-1L-3.6-2.1L-4.3-5.3M6.8 1L3.6 2.1L4.3 5.3M-4.3 5.3L-3.6 2.1L-6.8 1M4.3-5.3L3.6-2.1L6.8-1" stroke-width="1.1"/>
            <circle cx="0" cy="0" r="1.4" fill="#ffffff" stroke="none"/>
        </g>

        {{-- Reusable 8-point starburst --}}
        <g id="fcp-starburst">
            <path d="M0-12 L2.2-2.2 L12 0 L2.2 2.2 L0 12 L-2.2 2.2 L-12 0 L-2.2-2.2 Z" fill="url(#fcg-gold)"/>
            <path d="M-7-7 L-1.2-1.2 L7-7 L1.2-1.2 L7 7 L-1.2 1.2 L-7 7 L-1.2-1.2 Z" fill="#fffbeb" opacity="0.85"/>
        </g>

        {{-- Reusable holly sprig --}}
        <g id="fcp-holly">
            <path d="M0 0 C-8-12 -22-14 -30-6 C-24 2 -14 8 0 0 Z" fill="url(#fcg-pine)"/>
            <path d="M0 0 C10-10 24-10 30 0 C22 8 10 10 0 0 Z" fill="url(#fcg-pine-dark)"/>
            <circle cx="-3" cy="2" r="4.2" fill="url(#fcg-crimson)"/>
            <circle cx="4" cy="3" r="3.8" fill="url(#fcg-crimson)"/>
            <circle cx="0" cy="-3" r="4" fill="#fb7185"/>
            <circle cx="-1" cy="-4" r="1.2" fill="#ffffff" opacity="0.8"/>
        </g>

        {{-- Reusable firework rosette --}}
        <g id="fcp-firework">
            <circle cx="0" cy="0" r="36" fill="url(#fcg-glow)" opacity="0.55"/>
            <g stroke="url(#fcg-gold)" stroke-width="1.8" stroke-linecap="round">
                <line x1="0" y1="-6" x2="0" y2="-32"/><line x1="0" y1="6" x2="0" y2="32"/>
                <line x1="-6" y1="0" x2="-32" y2="0"/><line x1="6" y1="0" x2="32" y2="0"/>
                <line x1="-4" y1="-4" x2="-23" y2="-23"/><line x1="4" y1="4" x2="23" y2="23"/>
                <line x1="4" y1="-4" x2="23" y2="-23"/><line x1="-4" y1="4" x2="-23" y2="23"/>
            </g>
            <g stroke="#fb7185" stroke-width="1.4" stroke-linecap="round" stroke-dasharray="2 4">
                <line x1="0" y1="-10" x2="-12" y2="-29"/><line x1="0" y1="-10" x2="12" y2="-29"/>
                <line x1="0" y1="10" x2="-12" y2="29"/><line x1="0" y1="10" x2="12" y2="29"/>
                <line x1="-10" y1="0" x2="-29" y2="-12"/><line x1="-10" y1="0" x2="-29" y2="12"/>
                <line x1="10" y1="0" x2="29" y2="-12"/><line x1="10" y1="0" x2="29" y2="12"/>
            </g>
            <circle cx="0" cy="-34" r="2.2" fill="#fffbeb"/><circle cx="0" cy="34" r="2.2" fill="#fffbeb"/>
            <circle cx="-34" cy="0" r="2.2" fill="#fffbeb"/><circle cx="34" cy="0" r="2.2" fill="#fffbeb"/>
            <circle cx="-25" cy="-25" r="2" fill="#fde047"/><circle cx="25" cy="25" r="2" fill="#fde047"/>
            <circle cx="25" cy="-25" r="2" fill="#fde047"/><circle cx="-25" cy="25" r="2" fill="#fde047"/>
            <circle cx="0" cy="0" r="4" fill="#ffffff"/>
        </g>
    </defs>

    {{-- =====================================================================
         1. CHRISTMAS — Enchanted Christmas Tree, Snowfall, Star, Baubles & Gifts
         ===================================================================== --}}
    <symbol id="fc-art-christmas" viewBox="0 0 320 260">
        <rect width="320" height="260" fill="var(--fc-deep, #1a0309)"/>
        <ellipse cx="160" cy="115" rx="145" ry="110" fill="url(#fcg-aura)"/>
        <path d="M0 198 Q80 176 160 194 T320 190 L320 260 L0 260 Z" fill="#dbeafe" opacity="0.22"/>
        {{-- Distant snow-covered evergreens --}}
        <g opacity="0.38" fill="url(#fcg-pine-dark)">
            <polygon points="38,142 18,198 58,198"/>
            <polygon points="38,124 22,166 54,166"/>
            <polygon points="282,146 262,200 302,200"/>
            <polygon points="282,128 266,170 298,170"/>
        </g>
        {{-- Snow hills --}}
        <path d="M0 212 Q85 190 165 208 T320 202 L320 260 L0 260 Z" fill="url(#fcg-snow)"/>
        <path d="M0 228 Q110 210 210 226 T320 220 L320 260 L0 260 Z" fill="#ffffff"/>
        <ellipse cx="160" cy="222" rx="76" ry="11" fill="#94a3b8" opacity="0.35"/>

        {{-- Tree trunk --}}
        <rect x="148" y="192" width="24" height="28" rx="3" fill="#5c3a21"/>

        {{-- 4-tier sculpted evergreen Christmas Tree --}}
        {{-- Tier 4 (Bottom) --}}
        <path d="M160 118 L78 202 Q106 212 134 202 Q160 214 186 202 Q214 212 242 202 Z" fill="url(#fcg-pine-dark)"/>
        <path d="M78 202 Q106 210 134 200 Q160 212 186 200 Q214 210 242 202" fill="none" stroke="#ffffff" stroke-width="5" stroke-linecap="round"/>
        {{-- Tier 3 --}}
        <path d="M160 90 L92 166 Q116 175 138 166 Q160 176 182 166 Q204 175 228 166 Z" fill="url(#fcg-pine)"/>
        <path d="M92 166 Q116 173 138 164 Q160 174 182 164 Q204 173 228 166" fill="none" stroke="#ffffff" stroke-width="4.5" stroke-linecap="round"/>
        {{-- Tier 2 --}}
        <path d="M160 64 L108 130 Q126 138 144 130 Q160 139 176 130 Q194 138 212 130 Z" fill="url(#fcg-pine)"/>
        <path d="M108 130 Q126 136 144 129 Q160 137 176 129 Q194 136 212 130" fill="none" stroke="#ffffff" stroke-width="4" stroke-linecap="round"/>
        {{-- Tier 1 (Top) --}}
        <path d="M160 40 L124 94 Q142 101 160 94 Q178 101 196 94 Z" fill="url(#fcg-pine)"/>
        <path d="M124 94 Q142 100 160 93 Q178 100 196 94" fill="none" stroke="#ffffff" stroke-width="3.5" stroke-linecap="round"/>

        {{-- Golden tinsel garlands --}}
        <path d="M130 88 Q160 104 192 86" fill="none" stroke="url(#fcg-gold)" stroke-width="2.2" stroke-dasharray="2 4" stroke-linecap="round"/>
        <path d="M114 122 Q160 144 206 120" fill="none" stroke="url(#fcg-gold)" stroke-width="2.4" stroke-dasharray="2.5 4.5" stroke-linecap="round"/>
        <path d="M98 158 Q160 184 222 156" fill="none" stroke="url(#fcg-gold)" stroke-width="2.6" stroke-dasharray="3 5" stroke-linecap="round"/>
        <path d="M86 192 Q160 218 234 190" fill="none" stroke="url(#fcg-gold)" stroke-width="2.6" stroke-dasharray="3 5" stroke-linecap="round"/>

        {{-- Baubles & Ornaments --}}
        <circle cx="144" cy="80" r="5.5" fill="url(#fcg-crimson)"/><circle cx="142" cy="78" r="1.6" fill="#fff"/>
        <circle cx="176" cy="82" r="5" fill="url(#fcg-gold)"/><circle cx="174" cy="80" r="1.5" fill="#fff"/>
        <circle cx="128" cy="116" r="6" fill="url(#fcg-gold)"/><circle cx="126" cy="114" r="1.8" fill="#fff"/>
        <circle cx="160" cy="114" r="6.5" fill="url(#fcg-crimson)"/><circle cx="158" cy="112" r="2" fill="#fff"/>
        <circle cx="192" cy="118" r="5.8" fill="#ffffff"/><circle cx="190" cy="116" r="1.8" fill="#fde047"/>
        <circle cx="112" cy="152" r="6.5" fill="url(#fcg-crimson)"/><circle cx="110" cy="150" r="2" fill="#fff"/>
        <circle cx="146" cy="150" r="6" fill="url(#fcg-gold)"/><circle cx="144" cy="148" r="1.8" fill="#fff"/>
        <circle cx="178" cy="154" r="6.5" fill="url(#fcg-crimson)"/><circle cx="176" cy="152" r="2" fill="#fff"/>
        <circle cx="208" cy="150" r="5.8" fill="url(#fcg-gold)"/><circle cx="206" cy="148" r="1.8" fill="#fff"/>
        <circle cx="98" cy="188" r="6.5" fill="url(#fcg-gold)"/><circle cx="96" cy="186" r="2" fill="#fff"/>
        <circle cx="132" cy="186" r="7" fill="url(#fcg-crimson)"/><circle cx="130" cy="184" r="2" fill="#fff"/>
        <circle cx="166" cy="190" r="6.5" fill="#ffffff"/><circle cx="164" cy="188" r="2" fill="#fde047"/>
        <circle cx="198" cy="184" r="7" fill="url(#fcg-gold)"/><circle cx="196" cy="182" r="2" fill="#fff"/>
        <circle cx="224" cy="188" r="6.2" fill="url(#fcg-crimson)"/><circle cx="222" cy="186" r="1.8" fill="#fff"/>

        {{-- Wrapped Festive Gifts at the Base --}}
        {{-- Left Gift --}}
        <g transform="translate(88, 198)">
            <rect x="0" y="6" width="34" height="26" rx="3" fill="url(#fcg-crimson)" stroke="url(#fcg-gold)" stroke-width="1"/>
            <rect x="-2" y="2" width="38" height="7" rx="2" fill="#be123c"/>
            <rect x="14" y="2" width="6" height="30" fill="url(#fcg-gold)"/>
            <rect x="0" y="15" width="34" height="5" fill="url(#fcg-gold)"/>
            <path d="M17 2 C10-6 4-2 12 2 C10-6 24-6 17 2 C24-6 30-2 22 2 Z" fill="url(#fcg-gold)"/>
        </g>
        {{-- Right Gift --}}
        <g transform="translate(196, 196)">
            <rect x="0" y="6" width="36" height="28" rx="3" fill="var(--fc-primary, #0b4d3b)" stroke="url(#fcg-gold)" stroke-width="1.2"/>
            <rect x="15" y="6" width="6" height="28" fill="url(#fcg-gold)"/>
            <rect x="0" y="17" width="36" height="5" fill="url(#fcg-gold)"/>
            <path d="M18 6 C11-3 5 1 13 6 M18 6 C25-3 31 1 23 6" stroke="url(#fcg-gold)" stroke-width="3" fill="none" stroke-linecap="round"/>
        </g>
        {{-- Centre-Front Parcel --}}
        <g transform="translate(142, 208)">
            <rect x="0" y="4" width="36" height="22" rx="3" fill="url(#fcg-gold)" stroke="#78350f" stroke-width="0.8"/>
            <rect x="15" y="4" width="6" height="22" fill="url(#fcg-crimson)"/>
            <rect x="0" y="12" width="36" height="5" fill="url(#fcg-crimson)"/>
        </g>

        {{-- Radiant Bethlehem Star Topper --}}
        <circle cx="160" cy="36" r="28" fill="url(#fcg-glow)"/>
        <g transform="translate(160, 36) scale(1.65)">
            <use href="#fcp-starburst"/>
        </g>

        {{-- Crystalline Snowflakes & Falling Snow --}}
        <use href="#fcp-snowflake" transform="translate(52, 54) scale(1.15)" opacity="0.9"/>
        <use href="#fcp-snowflake" transform="translate(268, 50) scale(1.05)" opacity="0.85"/>
        <use href="#fcp-snowflake" transform="translate(38, 122) scale(0.85)" opacity="0.75"/>
        <use href="#fcp-snowflake" transform="translate(280, 118) scale(0.95)" opacity="0.8"/>
        <use href="#fcp-snowflake" transform="translate(94, 42) scale(0.7)" opacity="0.7"/>
        <use href="#fcp-snowflake" transform="translate(226, 40) scale(0.75)" opacity="0.75"/>
        <g fill="#ffffff">
            <circle cx="26" cy="30" r="2.2" opacity="0.8"/><circle cx="76" cy="84" r="2.6" opacity="0.85"/>
            <circle cx="64" cy="158" r="2" opacity="0.75"/><circle cx="24" cy="176" r="2.8" opacity="0.85"/>
            <circle cx="116" cy="64" r="1.8" opacity="0.7"/><circle cx="204" cy="62" r="2" opacity="0.75"/>
            <circle cx="246" cy="88" r="2.5" opacity="0.85"/><circle cx="256" cy="162" r="2.2" opacity="0.8"/>
            <circle cx="294" cy="172" r="2.6" opacity="0.8"/><circle cx="296" cy="36" r="2" opacity="0.7"/>
        </g>
    </symbol>

    {{-- =====================================================================
         2. NEW YEAR — Midnight Firecrackers, Fireworks, Clock & Champagne Toast
         ===================================================================== --}}
    <symbol id="fc-art-new_year" viewBox="0 0 320 260">
        <rect width="320" height="260" fill="var(--fc-deep, #050506)"/>
        <ellipse cx="160" cy="115" rx="150" ry="118" fill="url(#fcg-aura)"/>
        {{-- Subtle Midnight Clock Ring --}}
        <circle cx="160" cy="112" r="88" fill="none" stroke="url(#fcg-gold)" stroke-width="0.9" stroke-dasharray="2 6" opacity="0.45"/>
        <circle cx="160" cy="112" r="78" fill="none" stroke="url(#fcg-gold)" stroke-width="0.6" opacity="0.3"/>

        {{-- 3 Dazzling Firework / Firecracker Bursts --}}
        <g transform="translate(160, 74) scale(1.38)">
            <use href="#fcp-firework"/>
        </g>
        <g transform="translate(68, 66) scale(0.95)">
            <use href="#fcp-firework"/>
        </g>
        <g transform="translate(254, 70) scale(1.0)">
            <use href="#fcp-firework"/>
        </g>

        {{-- Left & Right Shooting Firecracker Rockets --}}
        <g transform="translate(52, 184) rotate(-26)">
            <path d="M0 18 Q-6 42 -18 68" fill="none" stroke="url(#fcg-gold)" stroke-width="2" stroke-dasharray="3 3"/>
            <rect x="-8" y="-18" width="16" height="34" rx="2" fill="url(#fcg-crimson)" stroke="url(#fcg-gold)" stroke-width="1.2"/>
            <path d="M-8-8 L8-2 M-8 4 L8 10" stroke="url(#fcg-gold)" stroke-width="2.5"/>
            <polygon points="-10,-18 10,-18 0,-34" fill="url(#fcg-gold)"/>
            <circle cx="0" cy="20" r="5" fill="url(#fcg-flame)"/>
        </g>
        <g transform="translate(268, 184) rotate(26)">
            <path d="M0 18 Q6 42 18 68" fill="none" stroke="url(#fcg-gold)" stroke-width="2" stroke-dasharray="3 3"/>
            <rect x="-8" y="-18" width="16" height="34" rx="2" fill="url(#fcg-crimson)" stroke="url(#fcg-gold)" stroke-width="1.2"/>
            <path d="M-8-8 L8-2 M-8 4 L8 10" stroke="url(#fcg-gold)" stroke-width="2.5"/>
            <polygon points="-10,-18 10,-18 0,-34" fill="url(#fcg-gold)"/>
            <circle cx="0" cy="20" r="5" fill="url(#fcg-flame)"/>
        </g>

        {{-- Clinking Crystal Champagne Flutes --}}
        <g transform="translate(138, 178) rotate(12)">
            <path d="M-12-36 L12-36 L7 4 C5 12 -5 12 -7 4 Z" fill="rgba(255,255,255,0.16)" stroke="url(#fcg-gold)" stroke-width="1.6"/>
            <path d="M-9-22 L9-22 L6 2 C4 9 -4 9 -6 2 Z" fill="url(#fcg-gold)" opacity="0.88"/>
            <line x1="0" y1="10" x2="0" y2="42" stroke="url(#fcg-gold)" stroke-width="2"/>
            <ellipse cx="0" cy="42" rx="12" ry="3.2" fill="url(#fcg-gold)"/>
            <circle cx="-2" cy="-12" r="1.4" fill="#fff"/><circle cx="2" cy="-18" r="1.2" fill="#fff"/>
        </g>
        <g transform="translate(182, 178) rotate(-12)">
            <path d="M-12-36 L12-36 L7 4 C5 12 -5 12 -7 4 Z" fill="rgba(255,255,255,0.16)" stroke="url(#fcg-gold)" stroke-width="1.6"/>
            <path d="M-9-22 L9-22 L6 2 C4 9 -4 9 -6 2 Z" fill="url(#fcg-gold)" opacity="0.88"/>
            <line x1="0" y1="10" x2="0" y2="42" stroke="url(#fcg-gold)" stroke-width="2"/>
            <ellipse cx="0" cy="42" rx="12" ry="3.2" fill="url(#fcg-gold)"/>
            <circle cx="2" cy="-10" r="1.4" fill="#fff"/><circle cx="-2" cy="-17" r="1.2" fill="#fff"/>
        </g>
        {{-- Clink Starburst & Curling Confetti Streamers --}}
        <use href="#fcp-starburst" transform="translate(160, 140) scale(0.95)"/>
        <path d="M92 128 C104 142 90 156 106 170" fill="none" stroke="url(#fcg-gold)" stroke-width="2.2" stroke-linecap="round"/>
        <path d="M228 128 C216 142 230 156 214 170" fill="none" stroke="#fb7185" stroke-width="2.2" stroke-linecap="round"/>
        <path d="M114 218 Q160 232 206 218" fill="none" stroke="url(#fcg-gold)" stroke-width="1.4" opacity="0.7"/>
    </symbol>

    {{-- =====================================================================
         3. EASTER — Spring Sunrise, Blooming Tulips & Painted Fabergé Eggs
         ===================================================================== --}}
    <symbol id="fc-art-easter" viewBox="0 0 320 260">
        <rect width="320" height="260" fill="var(--fc-deep, #2a0d18)"/>
        <circle cx="160" cy="185" r="130" fill="url(#fcg-aura)"/>
        {{-- Golden Sunrise Rays --}}
        <g stroke="url(#fcg-gold)" stroke-width="1.2" opacity="0.35">
            <line x1="160" y1="190" x2="160" y2="18"/><line x1="160" y1="190" x2="90" y2="30"/>
            <line x1="160" y1="190" x2="230" y2="30"/><line x1="160" y1="190" x2="35" y2="75"/>
            <line x1="160" y1="190" x2="285" y2="75"/><line x1="160" y1="190" x2="10" y2="135"/>
            <line x1="160" y1="190" x2="310" y2="135"/>
        </g>
        {{-- Spring Meadow Hills --}}
        <path d="M0 198 Q90 176 160 194 T320 188 L320 260 L0 260 Z" fill="url(#fcg-pine)"/>
        <path d="M0 218 Q110 200 220 218 T320 210 L320 260 L0 260 Z" fill="url(#fcg-pine-dark)"/>

        {{-- Left & Right Painted Easter Eggs --}}
        <g transform="translate(100, 174) rotate(-12)">
            <ellipse cx="0" cy="0" rx="25" ry="33" fill="#f5d0fe" stroke="url(#fcg-gold)" stroke-width="2"/>
            <path d="M-24 -6 Q0 -16 24 -6 M-24 8 Q0 -2 24 8" fill="none" stroke="url(#fcg-gold)" stroke-width="2.2"/>
            <circle cx="0" cy="-18" r="2.5" fill="url(#fcg-crimson)"/>
            <circle cx="-10" cy="1" r="2.2" fill="url(#fcg-crimson)"/><circle cx="10" cy="1" r="2.2" fill="url(#fcg-crimson)"/>
        </g>
        <g transform="translate(220, 174) rotate(12)">
            <ellipse cx="0" cy="0" rx="25" ry="33" fill="#bbf7d0" stroke="url(#fcg-gold)" stroke-width="2"/>
            <path d="M-23 -8 L-12 2 L0 -8 L12 2 L23 -8" fill="none" stroke="url(#fcg-gold)" stroke-width="2.2"/>
            <path d="M-22 12 Q0 20 22 12" fill="none" stroke="url(#fcg-crimson)" stroke-width="2"/>
        </g>
        {{-- Central Royal Fabergé Easter Egg --}}
        <g transform="translate(160, 156)">
            <ellipse cx="0" cy="0" rx="34" ry="45" fill="url(#fcg-crimson)" stroke="url(#fcg-gold)" stroke-width="2.6"/>
            <path d="M-33 -10 Q-16 -22 0 -10 Q16 -22 33 -10" fill="none" stroke="url(#fcg-gold)" stroke-width="2.5"/>
            <path d="M-33 12 Q-16 24 0 12 Q16 24 33 12" fill="none" stroke="url(#fcg-gold)" stroke-width="2.5"/>
            <polygon points="0,-10 9,1 0,12 -9,1" fill="url(#fcg-gold)"/>
            <circle cx="-18" cy="1" r="3" fill="#fffbeb"/><circle cx="18" cy="1" r="3" fill="#fffbeb"/>
            <circle cx="0" cy="-28" r="3.2" fill="#fffbeb"/><circle cx="0" cy="28" r="3.2" fill="#fffbeb"/>
        </g>

        {{-- Blooming Spring Tulips --}}
        <g stroke="#22c55e" stroke-width="2.8" fill="none" stroke-linecap="round">
            <path d="M52 224 Q44 166 56 120"/>
            <path d="M268 224 Q276 166 264 120"/>
        </g>
        <g transform="translate(56, 114)">
            <path d="M0 10 C-14 8 -16 -12 -6 -18 C-2 -10 2 -10 6 -18 C16 -12 14 8 0 10 Z" fill="#fb7185" stroke="url(#fcg-gold)" stroke-width="1"/>
            <path d="M-6 -14 L0 -22 L6 -14" fill="#fde047"/>
        </g>
        <g transform="translate(264, 114)">
            <path d="M0 10 C-14 8 -16 -12 -6 -18 C-2 -10 2 -10 6 -18 C16 -12 14 8 0 10 Z" fill="#fde047" stroke="url(#fcg-gold)" stroke-width="1"/>
            <path d="M-6 -14 L0 -22 L6 -14" fill="#fb7185"/>
        </g>
        <use href="#fcp-starburst" transform="translate(160, 62) scale(1.15)"/>
    </symbol>

    {{-- =====================================================================
         4. EID — Celestial Crescent Moon, Hanging Moroccan Lanterns & Mosque Dome
         ===================================================================== --}}
    <symbol id="fc-art-eid" viewBox="0 0 320 260">
        <rect width="320" height="260" fill="var(--fc-deep, #040a1a)"/>
        <circle cx="160" cy="126" r="128" fill="url(#fcg-aura)"/>
        {{-- Hanging Chains & 3 Moroccan Fanoos Lanterns --}}
        <g stroke="url(#fcg-gold)" stroke-width="1.5">
            <line x1="68" y1="0" x2="68" y2="54"/>
            <line x1="160" y1="0" x2="160" y2="34"/>
            <line x1="252" y1="0" x2="252" y2="58"/>
        </g>
        @foreach([[68, 54, 0.82], [160, 34, 1.0], [252, 58, 0.82]] as [$lx, $ly, $ls])
        <g transform="translate({{ $lx }}, {{ $ly }}) scale({{ $ls }})">
            <circle cx="0" cy="26" r="24" fill="url(#fcg-glow)"/>
            <path d="M-8 6 Q0 -4 8 6 L14 14 L-14 14 Z" fill="url(#fcg-gold)"/>
            <polygon points="-14,14 14,14 11,40 -11,40" fill="url(#fcg-flame)" stroke="url(#fcg-gold)" stroke-width="2"/>
            <line x1="0" y1="14" x2="0" y2="40" stroke="url(#fcg-gold)" stroke-width="1.5"/>
            <polygon points="-11,40 11,40 0,52" fill="url(#fcg-gold)"/>
        </g>
        @endforeach

        {{-- Sculpted 24k Gold Crescent Moon (Hilal) & Star --}}
        <path d="M136 96 A54 54 0 1 0 208 166 A42 42 0 1 1 136 96 Z" fill="url(#fcg-gold)"/>
        <use href="#fcp-starburst" transform="translate(184, 118) scale(1.05)"/>
        <use href="#fcp-starburst" transform="translate(104, 134) scale(0.55)"/>
        <use href="#fcp-starburst" transform="translate(226, 152) scale(0.55)"/>

        {{-- Horizon Mosque Domes & Minarets Silhouette --}}
        <g fill="var(--fc-primary, #14254d)" stroke="url(#fcg-gold)" stroke-width="1.2" opacity="0.9">
            <rect x="38" y="176" width="10" height="84"/>
            <polygon points="36,176 50,176 43,156"/>
            <rect x="272" y="176" width="10" height="84"/>
            <polygon points="270,176 284,176 277,156"/>
            <path d="M112 260 L112 222 Q160 176 208 222 L208 260 Z"/>
            <path d="M62 260 L62 232 Q88 204 114 232 L114 260 Z"/>
            <path d="M206 260 L206 232 Q232 204 258 232 L258 260 Z"/>
        </g>
    </symbol>

    {{-- =====================================================================
         5. DIWALI — Glowing Terracotta Diyas, Sacred Lotus Rangoli & Sparklers
         ===================================================================== --}}
    <symbol id="fc-art-diwali" viewBox="0 0 320 260">
        <rect width="320" height="260" fill="var(--fc-deep, #1f0b03)"/>
        <circle cx="160" cy="136" r="132" fill="url(#fcg-aura)"/>
        {{-- Sacred Rangoli Geometry Ring --}}
        <g transform="translate(160, 136)" fill="none" stroke="url(#fcg-gold)" opacity="0.45">
            <circle cx="0" cy="0" r="88" stroke-width="1.2" stroke-dasharray="4 4"/>
            <circle cx="0" cy="0" r="68" stroke-width="1"/>
            <polygon points="0,-84 73,42 -73,42" stroke-width="1"/>
            <polygon points="0,84 73,-42 -73,-42" stroke-width="1"/>
        </g>
        {{-- Upper Diwali Firework Sparklers --}}
        <g transform="translate(68, 62) scale(0.78)"><use href="#fcp-firework"/></g>
        <g transform="translate(252, 62) scale(0.78)"><use href="#fcp-firework"/></g>

        {{-- Sacred Lotus Petals Under Main Diya --}}
        <g transform="translate(160, 198)">
            <path d="M-74 6 Q-44 -20 0 0 Q44 -20 74 6 Q40 24 0 18 Q-40 24 -74 6 Z" fill="url(#fcg-crimson)" stroke="url(#fcg-gold)" stroke-width="1.4"/>
            <path d="M-54 12 Q-28 -14 0 4 Q28 -14 54 12 Q28 28 0 22 Q-28 28 -54 12 Z" fill="#fb7185" stroke="url(#fcg-gold)" stroke-width="1.2"/>
        </g>

        {{-- Central Grand Glowing Diya Lamp --}}
        <g transform="translate(160, 168)">
            <circle cx="0" cy="-42" r="42" fill="url(#fcg-glow)"/>
            <path d="M0 -76 C16 -52 18 -26 0 -14 C-18 -26 -16 -52 0 -76 Z" fill="url(#fcg-flame)"/>
            <path d="M0 -58 C8 -42 9 -26 0 -18 C-9 -26 -8 -42 0 -58 Z" fill="#ffffff" opacity="0.9"/>
            <path d="M-58 -12 Q0 4 58 -12 C48 22 26 32 0 32 C-26 32 -48 22 -58 -12 Z" fill="#b45309" stroke="url(#fcg-gold)" stroke-width="2.4"/>
            <path d="M-54 -8 Q0 8 54 -8" fill="none" stroke="url(#fcg-gold)" stroke-width="3"/>
            <circle cx="-26" cy="8" r="2.8" fill="url(#fcg-gold)"/><circle cx="0" cy="12" r="3.2" fill="url(#fcg-gold)"/><circle cx="26" cy="8" r="2.8" fill="url(#fcg-gold)"/>
        </g>

        {{-- Left & Right Companion Diyas --}}
        @foreach([[66, 206], [254, 206]] as [$dx, $dy])
        <g transform="translate({{ $dx }}, {{ $dy }}) scale(0.58)">
            <circle cx="0" cy="-38" r="34" fill="url(#fcg-glow)"/>
            <path d="M0 -68 C14 -46 16 -22 0 -12 C-16 -22 -14 -46 0 -68 Z" fill="url(#fcg-flame)"/>
            <path d="M-48 -10 Q0 4 48 -10 C40 18 22 26 0 26 C-22 26 -40 18 -48 -10 Z" fill="#b45309" stroke="url(#fcg-gold)" stroke-width="2.4"/>
        </g>
        @endforeach
    </symbol>

    {{-- =====================================================================
         6. THANKSGIVING — Bountiful Harvest Pumpkins, Golden Wheat & Maple Leaves
         ===================================================================== --}}
    <symbol id="fc-art-thanksgiving" viewBox="0 0 320 260">
        <rect width="320" height="260" fill="var(--fc-deep, #1f0b03)"/>
        <circle cx="160" cy="144" r="132" fill="url(#fcg-aura)"/>
        <path d="M0 202 Q90 184 160 198 T320 194 L320 260 L0 260 Z" fill="#451a03"/>
        {{-- Golden Wheat Sheaf Wreath & Autumn Maple Leaves --}}
        <g stroke="url(#fcg-gold)" stroke-width="2.2" fill="none" stroke-linecap="round">
            <path d="M84 196 C54 150 64 86 116 54"/>
            <path d="M236 196 C266 150 256 86 204 54"/>
        </g>
        <g fill="url(#fcg-gold)">
            @foreach([[68,144,-30],[68,118,-15],[80,92,5],[98,70,25],[252,144,30],[252,118,15],[240,92,-5],[222,70,-25]] as [$wx,$wy,$wa])
            <ellipse cx="{{ $wx }}" cy="{{ $wy }}" rx="5" ry="11" transform="rotate({{ $wa }} {{ $wx }} {{ $wy }})"/>
            @endforeach
        </g>
        {{-- Trio of Heirloom Harvest Pumpkins --}}
        <g transform="translate(102, 186)">
            <ellipse cx="0" cy="0" rx="30" ry="24" fill="#d97706" stroke="url(#fcg-gold)" stroke-width="1.5"/>
            <ellipse cx="0" cy="0" rx="16" ry="24" fill="#f59e0b" stroke="url(#fcg-gold)" stroke-width="1"/>
        </g>
        <g transform="translate(218, 186)">
            <ellipse cx="0" cy="0" rx="30" ry="24" fill="#b45309" stroke="url(#fcg-gold)" stroke-width="1.5"/>
            <ellipse cx="0" cy="0" rx="16" ry="24" fill="#d97706" stroke="url(#fcg-gold)" stroke-width="1"/>
        </g>
        <g transform="translate(160, 178)">
            <path d="M-4 -28 C-6 -44 8 -48 12 -38" fill="none" stroke="url(#fcg-gold)" stroke-width="4" stroke-linecap="round"/>
            <ellipse cx="0" cy="0" rx="44" ry="34" fill="#c2410c" stroke="url(#fcg-gold)" stroke-width="1.8"/>
            <ellipse cx="0" cy="0" rx="28" ry="34" fill="#ea580c" stroke="url(#fcg-gold)" stroke-width="1.4"/>
            <ellipse cx="0" cy="0" rx="13" ry="34" fill="#f97316" stroke="url(#fcg-gold)" stroke-width="1.2"/>
        </g>
        <use href="#fcp-starburst" transform="translate(160, 66) scale(1.1)"/>
    </symbol>

    {{-- =====================================================================
         7. MOTHER'S DAY — Couture Rose & Peony Bouquet in Gilded Vase
         ===================================================================== --}}
    <symbol id="fc-art-mothers_day" viewBox="0 0 320 260">
        <rect width="320" height="260" fill="var(--fc-deep, #2a0d18)"/>
        <circle cx="160" cy="128" r="126" fill="url(#fcg-aura)"/>
        {{-- Gilded Porcelain Vase & Ribbon Bow --}}
        <path d="M134 166 L186 166 L196 226 C196 238 124 238 124 226 Z" fill="#fff1f2" stroke="url(#fcg-gold)" stroke-width="2.4"/>
        <path d="M128 188 L192 188" stroke="url(#fcg-gold)" stroke-width="3"/>
        <path d="M160 188 C142 176 134 198 156 192 C144 206 140 218 148 222 M160 188 C178 176 186 198 164 192 C176 206 180 218 172 222" fill="none" stroke="url(#fcg-crimson)" stroke-width="2.6" stroke-linecap="round"/>

        {{-- Botanical Foliage & Stems --}}
        <g fill="url(#fcg-pine)" stroke="url(#fcg-gold)" stroke-width="0.9">
            <path d="M118 148 C82 138 66 112 76 92 C98 102 114 124 118 148 Z"/>
            <path d="M202 148 C238 138 254 112 244 92 C222 102 206 124 202 148 Z"/>
            <path d="M136 92 C112 64 118 38 136 32 C148 48 146 72 136 92 Z"/>
            <path d="M184 92 C208 64 202 38 184 32 C172 48 174 72 184 92 Z"/>
        </g>

        {{-- Couture Blooming English Roses & Peonies --}}
        @foreach([[114, 126, 26, '#fb7185'], [206, 126, 26, '#fb7185'], [134, 86, 24, '#fda4af'], [186, 86, 24, '#fda4af'], [160, 112, 34, 'url(#fcg-crimson)']] as [$rx, $ry, $rr, $rc])
        <g transform="translate({{ $rx }}, {{ $ry }})">
            <circle cx="0" cy="0" r="{{ $rr }}" fill="{{ $rc }}" stroke="url(#fcg-gold)" stroke-width="1.5"/>
            <path d="M-{{ (int)($rr*0.65) }} -{{ (int)($rr*0.2) }} Q0 -{{ (int)($rr*0.8) }} {{ (int)($rr*0.65) }} -{{ (int)($rr*0.2) }} Q{{ (int)($rr*0.4) }} {{ (int)($rr*0.65) }} 0 {{ (int)($rr*0.5) }} Q-{{ (int)($rr*0.5) }} {{ (int)($rr*0.4) }} -{{ (int)($rr*0.35) }} -{{ (int)($rr*0.15) }} Q0 -{{ (int)($rr*0.45) }} {{ (int)($rr*0.3) }} 0" fill="none" stroke="#fff1f2" stroke-width="1.8" stroke-linecap="round"/>
            <circle cx="0" cy="0" r="4" fill="url(#fcg-gold)"/>
        </g>
        @endforeach
        <use href="#fcp-starburst" transform="translate(76, 62) scale(0.75)"/>
        <use href="#fcp-starburst" transform="translate(244, 62) scale(0.75)"/>
    </symbol>

    {{-- =====================================================================
         8. FATHER'S DAY — Sovereign Heraldic Shield, Royal Crown & Oak Laurels
         ===================================================================== --}}
    <symbol id="fc-art-fathers_day" viewBox="0 0 320 260">
        <rect width="320" height="260" fill="var(--fc-deep, #040a1a)"/>
        <circle cx="160" cy="134" r="128" fill="url(#fcg-aura)"/>
        {{-- Sweeping Golden Laurel Wreath --}}
        <path d="M160 218 C92 206 64 148 86 78" fill="none" stroke="url(#fcg-gold)" stroke-width="2.6" stroke-linecap="round"/>
        <path d="M160 218 C228 206 256 148 234 78" fill="none" stroke="url(#fcg-gold)" stroke-width="2.6" stroke-linecap="round"/>
        <g fill="url(#fcg-gold)">
            @foreach([[82,172,-35],[72,144,-20],[72,114,-5],[80,88,15],[238,172,35],[248,144,20],[248,114,5],[240,88,-15]] as [$lx,$ly,$la])
            <ellipse cx="{{ $lx }}" cy="{{ $ly }}" rx="6" ry="13" transform="rotate({{ $la }} {{ $lx }} {{ $ly }})"/>
            @endforeach
        </g>
        {{-- Sovereign Heraldic Shield --}}
        <path d="M112 96 L208 96 L208 148 C208 184 160 208 160 214 C160 208 112 184 112 148 Z" fill="var(--fc-primary, #14254d)" stroke="url(#fcg-gold)" stroke-width="4"/>
        <path d="M120 104 L200 104 L200 146 C200 176 160 196 160 202 C160 196 120 176 120 146 Z" fill="none" stroke="url(#fcg-gold)" stroke-width="1.2"/>
        <line x1="160" y1="98" x2="160" y2="210" stroke="url(#fcg-gold)" stroke-width="1.6"/>
        <line x1="114" y1="148" x2="206" y2="148" stroke="url(#fcg-gold)" stroke-width="1.6"/>
        <use href="#fcp-starburst" transform="translate(160, 148) scale(1.3)"/>

        {{-- Jeweled 5-Point Royal Crown --}}
        <g transform="translate(160, 74)">
            <path d="M-36 12 L-42 -16 L-18 0 L0 -24 L18 0 L42 -16 L36 12 Z" fill="url(#fcg-gold)" stroke="#fffbeb" stroke-width="1.2"/>
            <rect x="-36" y="12" width="72" height="8" rx="3" fill="url(#fcg-crimson)" stroke="url(#fcg-gold)" stroke-width="1.4"/>
            <circle cx="-42" cy="-18" r="3" fill="#fffbeb"/><circle cx="-18" cy="-2" r="2.5" fill="url(#fcg-crimson)"/>
            <circle cx="0" cy="-26" r="3.8" fill="#fffbeb"/><circle cx="18" cy="-2" r="2.5" fill="url(#fcg-crimson)"/>
            <circle cx="42" cy="-18" r="3" fill="#fffbeb"/>
        </g>
    </symbol>

    {{-- =====================================================================
         9. REUNION — The Great Ancestral Family Tree & Hearth Lanterns
         ===================================================================== --}}
    <symbol id="fc-art-reunion" viewBox="0 0 320 260">
        <rect width="320" height="260" fill="var(--fc-deep, #1a1207)"/>
        <circle cx="160" cy="118" r="126" fill="url(#fcg-aura)"/>
        <path d="M0 208 Q100 186 160 202 T320 198 L320 260 L0 260 Z" fill="url(#fcg-pine-dark)"/>

        {{-- Lush Ancestral Tree Canopy --}}
        <circle cx="114" cy="114" r="38" fill="url(#fcg-pine)" stroke="url(#fcg-gold)" stroke-width="1.2"/>
        <circle cx="206" cy="114" r="38" fill="url(#fcg-pine)" stroke="url(#fcg-gold)" stroke-width="1.2"/>
        <circle cx="132" cy="78" r="40" fill="url(#fcg-pine)" stroke="url(#fcg-gold)" stroke-width="1.2"/>
        <circle cx="188" cy="78" r="40" fill="url(#fcg-pine)" stroke="url(#fcg-gold)" stroke-width="1.2"/>
        <circle cx="160" cy="68" r="44" fill="url(#fcg-pine)" stroke="url(#fcg-gold)" stroke-width="1.6"/>

        {{-- Golden Trunk, Branches & Deep Roots --}}
        <path d="M160 212 L160 112 M160 144 L116 112 M160 144 L204 112 M160 122 L134 82 M160 122 L186 82" stroke="url(#fcg-gold)" stroke-width="7" stroke-linecap="round"/>
        <path d="M160 206 C142 224 116 232 92 236 M160 206 C178 224 204 232 228 236 M160 208 L160 240" fill="none" stroke="url(#fcg-gold)" stroke-width="4" stroke-linecap="round"/>

        {{-- Hanging Hearth Lanterns & Golden Leaves --}}
        <line x1="104" y1="138" x2="104" y2="162" stroke="url(#fcg-gold)" stroke-width="1.5"/>
        <circle cx="104" cy="168" r="14" fill="url(#fcg-glow)"/><circle cx="104" cy="168" r="5" fill="url(#fcg-flame)"/>
        <line x1="216" y1="138" x2="216" y2="162" stroke="url(#fcg-gold)" stroke-width="1.5"/>
        <circle cx="216" cy="168" r="14" fill="url(#fcg-glow)"/><circle cx="216" cy="168" r="5" fill="url(#fcg-flame)"/>
        <g fill="url(#fcg-gold)">
            <circle cx="160" cy="54" r="4"/><circle cx="132" cy="72" r="3.5"/><circle cx="188" cy="72" r="3.5"/>
            <circle cx="110" cy="104" r="3.5"/><circle cx="210" cy="104" r="3.5"/><circle cx="160" cy="92" r="4"/>
        </g>
    </symbol>

    {{-- =====================================================================
         10. MILESTONE — Golden Jubilee Trophy, Laurel Wreath & Starburst
         ===================================================================== --}}
    <symbol id="fc-art-milestone" viewBox="0 0 320 260">
        <rect width="320" height="260" fill="var(--fc-deep, #1a0309)"/>
        <circle cx="160" cy="128" r="132" fill="url(#fcg-aura)"/>
        {{-- Laurel Wreath --}}
        <path d="M160 224 C86 212 58 148 84 72" fill="none" stroke="url(#fcg-gold)" stroke-width="2.6"/>
        <path d="M160 224 C234 212 262 148 236 72" fill="none" stroke="url(#fcg-gold)" stroke-width="2.6"/>
        {{-- 24k Gold Jubilee Trophy Cup --}}
        <path d="M122 78 C92 78 92 132 128 138" fill="none" stroke="url(#fcg-gold)" stroke-width="5"/>
        <path d="M198 78 C228 78 228 132 192 138" fill="none" stroke="url(#fcg-gold)" stroke-width="5"/>
        <path d="M118 68 L202 68 L192 132 C188 158 132 158 128 132 Z" fill="url(#fcg-gold)" stroke="#fffbeb" stroke-width="1.6"/>
        <rect x="153" y="152" width="14" height="34" fill="url(#fcg-gold)"/>
        <rect x="128" y="186" width="64" height="14" rx="3" fill="url(#fcg-gold)"/>
        <rect x="116" y="200" width="88" height="18" rx="4" fill="#1c1917" stroke="url(#fcg-gold)" stroke-width="2"/>
        <use href="#fcp-starburst" transform="translate(160, 104) scale(1.25)"/>
        <use href="#fcp-starburst" transform="translate(160, 38) scale(1.1)"/>
        <use href="#fcp-starburst" transform="translate(64, 58) scale(0.75)"/>
        <use href="#fcp-starburst" transform="translate(256, 58) scale(0.75)"/>
    </symbol>

    {{-- =====================================================================
         11. BIRTHDAY — Three-Tier Couture Birthday Cake, Lit Candles & Balloons
         ===================================================================== --}}
    <symbol id="fc-art-birthday" viewBox="0 0 320 260">
        <rect width="320" height="260" fill="var(--fc-deep, #1a1207)"/>
        <circle cx="160" cy="134" r="130" fill="url(#fcg-aura)"/>
        {{-- Top Festive Bunting Flags --}}
        <path d="M0 18 Q80 46 160 22 Q240 46 320 18" fill="none" stroke="url(#fcg-gold)" stroke-width="1.5"/>
        @foreach([[28,28,'url(#fcg-crimson)'],[58,34,'url(#fcg-gold)'],[88,34,'#f472b6'],[118,28,'url(#fcg-gold)'],[202,28,'url(#fcg-gold)'],[232,34,'#f472b6'],[262,34,'url(#fcg-gold)'],[292,28,'url(#fcg-crimson)']] as [$bx,$by,$bc])
        <polygon points="{{ $bx-11 }},{{ $by-4 }} {{ $bx+11 }},{{ $by-4 }} {{ $bx }},{{ $by+18 }}" fill="{{ $bc }}"/>
        @endforeach

        {{-- Floating Party Balloons --}}
        <g stroke="url(#fcg-gold)" stroke-width="1.3" fill="none">
            <path d="M58 132 Q66 168 54 204"/><path d="M262 132 Q254 168 266 204"/>
        </g>
        <ellipse cx="58" cy="104" rx="22" ry="28" fill="url(#fcg-crimson)" stroke="url(#fcg-gold)" stroke-width="1.2"/>
        <ellipse cx="50" cy="94" rx="5" ry="9" fill="#ffffff" opacity="0.45"/>
        <ellipse cx="262" cy="104" rx="22" ry="28" fill="url(#fcg-gold)" stroke="#fffbeb" stroke-width="1.2"/>
        <ellipse cx="254" cy="94" rx="5" ry="9" fill="#ffffff" opacity="0.55"/>

        {{-- Gilded Cake Stand & 3-Tier Couture Birthday Cake --}}
        <path d="M88 220 L232 220 L216 228 L104 228 Z" fill="url(#fcg-gold)"/>
        <rect x="146" y="228" width="28" height="16" rx="3" fill="url(#fcg-gold)"/>
        <ellipse cx="160" cy="244" rx="42" ry="5" fill="url(#fcg-gold)"/>

        {{-- Tier 1 (Bottom) --}}
        <rect x="102" y="176" width="116" height="44" rx="6" fill="#fff1f2" stroke="url(#fcg-gold)" stroke-width="2"/>
        <path d="M102 186 Q116 198 131 186 Q145 198 160 186 Q175 198 189 186 Q204 198 218 186" fill="none" stroke="url(#fcg-crimson)" stroke-width="3"/>
        {{-- Tier 2 (Middle) --}}
        <rect x="118" y="138" width="84" height="38" rx="5" fill="url(#fcg-crimson)" stroke="url(#fcg-gold)" stroke-width="2"/>
        <path d="M118 148 Q139 160 160 148 Q181 160 202 148" fill="none" stroke="url(#fcg-gold)" stroke-width="2.6"/>
        {{-- Tier 3 (Top) --}}
        <rect x="134" y="106" width="52" height="32" rx="5" fill="#fffbeb" stroke="url(#fcg-gold)" stroke-width="2"/>
        <path d="M134 115 Q160 126 186 115" fill="none" stroke="url(#fcg-crimson)" stroke-width="2.4"/>

        {{-- 5 Lit Birthday Candles & Flames --}}
        @foreach([142, 151, 160, 169, 178] as $cx)
        <rect x="{{ $cx - 2 }}" y="84" width="4" height="22" rx="1.5" fill="url(#fcg-gold)"/>
        <circle cx="{{ $cx }}" cy="76" r="10" fill="url(#fcg-glow)"/>
        <path d="M{{ $cx }} 64 C{{ $cx+5 }} 72 {{ $cx+5 }} 80 {{ $cx }} 83 C{{ $cx-5 }} 80 {{ $cx-5 }} 72 {{ $cx }} 64 Z" fill="url(#fcg-flame)"/>
        @endforeach
    </symbol>

    {{-- =====================================================================
         12. WEDDING — Intertwined 24k Gold Wedding Bands, Floral Arch & Doves
         ===================================================================== --}}
    <symbol id="fc-art-wedding" viewBox="0 0 320 260">
        <rect width="320" height="260" fill="var(--fc-deep, #050506)"/>
        <circle cx="160" cy="134" r="128" fill="url(#fcg-aura)"/>
        {{-- Gilded Floral Wedding Arch --}}
        <path d="M64 236 L64 124 A96 96 0 0 1 256 124 L256 236" fill="none" stroke="url(#fcg-gold)" stroke-width="2.5"/>
        <path d="M72 236 L72 124 A88 88 0 0 1 248 124 L248 236" fill="none" stroke="url(#fcg-gold)" stroke-width="1" stroke-dasharray="3 4"/>
        {{-- Arch Roses --}}
        @foreach([[88,64],[122,38],[160,28],[198,38],[232,64]] as [$rx,$ry])
        <circle cx="{{ $rx }}" cy="{{ $ry }}" r="12" fill="#fda4af" stroke="url(#fcg-gold)" stroke-width="1.2"/>
        <circle cx="{{ $rx }}" cy="{{ $ry }}" r="5" fill="url(#fcg-crimson)"/>
        @endforeach

        {{-- Intertwined 24k Gold Wedding Rings & Diamond Solitaire --}}
        <circle cx="142" cy="152" r="36" fill="none" stroke="url(#fcg-gold)" stroke-width="9"/>
        <circle cx="142" cy="152" r="36" fill="none" stroke="#fffbeb" stroke-width="1.5" opacity="0.7"/>
        <circle cx="178" cy="152" r="36" fill="none" stroke="url(#fcg-gold)" stroke-width="9"/>
        <circle cx="178" cy="152" r="36" fill="none" stroke="#fffbeb" stroke-width="1.5" opacity="0.7"/>
        {{-- Diamond Solitaire on Right Ring --}}
        <polygon points="178,96 190,106 178,118 166,106" fill="#ffffff" stroke="url(#fcg-gold)" stroke-width="1.6"/>
        <use href="#fcp-starburst" transform="translate(178, 96) scale(0.95)"/>

        {{-- Pair of White Doves --}}
        <path d="M84 114 C68 96 52 102 62 118 C48 116 46 128 64 130 C76 132 88 126 96 118 Z" fill="#ffffff" stroke="url(#fcg-gold)" stroke-width="1"/>
        <path d="M236 114 C252 96 268 102 258 118 C272 116 274 128 256 130 C244 132 232 126 224 118 Z" fill="#ffffff" stroke="url(#fcg-gold)" stroke-width="1"/>
    </symbol>

    {{-- =====================================================================
         13. NEW BABY — Dreamy Crescent Moon Cradle, Fluffy Clouds & Balloons
         ===================================================================== --}}
    <symbol id="fc-art-new_baby" viewBox="0 0 320 260">
        <rect width="320" height="260" fill="var(--fc-deep, #0f1119)"/>
        <circle cx="160" cy="132" r="126" fill="url(#fcg-aura)"/>
        {{-- Hanging Nursery Star Mobile --}}
        <line x1="82" y1="0" x2="82" y2="56" stroke="url(#fcg-gold)" stroke-width="1.2" stroke-dasharray="2 3"/>
        <line x1="160" y1="0" x2="160" y2="38" stroke="url(#fcg-gold)" stroke-width="1.2" stroke-dasharray="2 3"/>
        <use href="#fcp-starburst" transform="translate(82, 58) scale(0.8)"/>
        <use href="#fcp-starburst" transform="translate(160, 40) scale(0.95)"/>

        {{-- Pastel & Gold Floating Balloons --}}
        <g stroke="url(#fcg-gold)" stroke-width="1.3" fill="none">
            <path d="M226 106 Q216 146 202 184"/><path d="M258 122 Q238 156 206 186"/>
        </g>
        <ellipse cx="226" cy="82" rx="20" ry="25" fill="#fbcfe8" stroke="url(#fcg-gold)" stroke-width="1.4"/>
        <ellipse cx="258" cy="100" rx="18" ry="23" fill="#bae6fd" stroke="url(#fcg-gold)" stroke-width="1.4"/>

        {{-- Golden Crescent Moon Cradle --}}
        <path d="M118 82 A60 60 0 1 0 206 168 A46 46 0 1 1 118 82 Z" fill="url(#fcg-gold)"/>

        {{-- Fluffy Ivory Clouds --}}
        <g fill="url(#fcg-snow)" stroke="url(#fcg-gold)" stroke-width="1.2">
            <path d="M58 206 A24 24 0 0 1 98 186 A30 30 0 0 1 154 180 A28 28 0 0 1 206 188 A22 22 0 0 1 244 206 Z"/>
            <path d="M92 224 A20 20 0 0 1 128 208 A26 26 0 0 1 182 206 A20 20 0 0 1 218 224 Z"/>
        </g>
    </symbol>

    {{-- =====================================================================
         14. GRADUATION — Tossed Mortarboard Cap, Ribboned Diploma & Laurels
         ===================================================================== --}}
    <symbol id="fc-art-graduation" viewBox="0 0 320 260">
        <rect width="320" height="260" fill="var(--fc-deep, #040a1a)"/>
        <circle cx="160" cy="128" r="130" fill="url(#fcg-aura)"/>
        {{-- Victory Laurel Branches --}}
        <path d="M112 214 C72 194 58 148 76 96" fill="none" stroke="url(#fcg-gold)" stroke-width="2.5"/>
        <path d="M208 214 C248 194 262 148 244 96" fill="none" stroke="url(#fcg-gold)" stroke-width="2.5"/>

        {{-- Academic Mortarboard Cap & Golden Tassel --}}
        <g transform="translate(160, 102) rotate(-6)">
            <path d="M-42 8 L-42 32 C-42 46 42 46 42 32 L42 8 Z" fill="#1e293b" stroke="url(#fcg-gold)" stroke-width="2"/>
            <polygon points="0,-28 86,4 0,36 -86,4" fill="#0f172a" stroke="url(#fcg-gold)" stroke-width="2.6"/>
            <circle cx="0" cy="4" r="5" fill="url(#fcg-gold)"/>
            <path d="M0 4 Q34 14 48 48" fill="none" stroke="url(#fcg-gold)" stroke-width="3" stroke-linecap="round"/>
            <polygon points="48,46 42,62 54,62" fill="url(#fcg-gold)"/>
        </g>

        {{-- Ribbon-Tied Parchment Diploma Scroll --}}
        <g transform="translate(160, 192) rotate(8)">
            <rect x="-56" y="-12" width="112" height="24" rx="10" fill="#fbf3e4" stroke="url(#fcg-gold)" stroke-width="2"/>
            <ellipse cx="-54" cy="0" rx="5" ry="12" fill="#d6c7b2" stroke="url(#fcg-gold)" stroke-width="1.5"/>
            <rect x="-8" y="-13" width="16" height="26" rx="2" fill="url(#fcg-crimson)" stroke="url(#fcg-gold)" stroke-width="1.2"/>
            <path d="M-4 12 L-14 28 L0 22 L14 28 L4 12" fill="url(#fcg-crimson)"/>
        </g>
        <use href="#fcp-starburst" transform="translate(82, 54) scale(0.9)"/>
        <use href="#fcp-starburst" transform="translate(238, 54) scale(0.9)"/>
    </symbol>

    {{-- =====================================================================
         15. REMEMBRANCE — Peaceful White Dove, Olive Branch & Sanctuary Flame
         ===================================================================== --}}
    <symbol id="fc-art-remembrance" viewBox="0 0 320 260">
        <rect width="320" height="260" fill="var(--fc-deep, #0f1119)"/>
        <circle cx="160" cy="118" r="128" fill="url(#fcg-aura)"/>
        {{-- Peaceful White Dove with Golden Olive Branch --}}
        <g transform="translate(160, 96)">
            <circle cx="0" cy="0" r="52" fill="url(#fcg-glow)" opacity="0.45"/>
            <path d="M-12 6 C-42 -18 -72 -8 -54 14 C-38 22 -22 18 -10 14 C-24 28 -18 40 4 30 C22 22 32 8 38 -2 C28 -8 18 -6 10 2 C22 -22 4 -42 -12 -14 Z" fill="url(#fcg-snow)" stroke="url(#fcg-gold)" stroke-width="1.5"/>
            <circle cx="24" cy="-2" r="1.8" fill="#1e293b"/>
            <path d="M36 -2 Q54 -10 68 -2" fill="none" stroke="url(#fcg-gold)" stroke-width="2" stroke-linecap="round"/>
            <ellipse cx="48" cy="-8" rx="5" ry="2.5" transform="rotate(-20 48 -8)" fill="url(#fcg-gold)"/>
            <ellipse cx="60" cy="-6" rx="5" ry="2.5" transform="rotate(15 60 -6)" fill="url(#fcg-gold)"/>
        </g>

        {{-- Eternal Sanctuary Candle & White Lily Petals --}}
        <g transform="translate(160, 198)">
            <circle cx="0" cy="-34" r="28" fill="url(#fcg-glow)"/>
            <path d="M0 -54 C9 -38 9 -24 0 -16 C-9 -24 -9 -38 0 -54 Z" fill="url(#fcg-flame)"/>
            <rect x="-16" y="-14" width="32" height="44" rx="4" fill="#fffdf9" stroke="url(#fcg-gold)" stroke-width="1.8"/>
            <path d="M-48 26 Q-24 6 0 24 Q24 6 48 26 Q24 38 0 32 Q-24 38 -48 26 Z" fill="#ffffff" stroke="url(#fcg-gold)" stroke-width="1.2"/>
        </g>
    </symbol>

    {{-- =====================================================================
         AMBIENT CARD-FACE BACKDROP MOTIFS (400 x 500 — active on all cards!)
         ===================================================================== --}}
    <symbol id="fc-backdrop-christmas" viewBox="0 0 400 500">
        {{-- Top-left & top-right holly garlands --}}
        <use href="#fcp-holly" transform="translate(64, 42) rotate(-18) scale(0.95)"/>
        <use href="#fcp-holly" transform="translate(336, 42) rotate(18) scale(-0.95, 0.95)"/>
        {{-- Crystalline Snowflakes around the card frame --}}
        <use href="#fcp-snowflake" transform="translate(46, 118) scale(1.1)" opacity="0.65"/>
        <use href="#fcp-snowflake" transform="translate(354, 124) scale(1.0)" opacity="0.65"/>
        <use href="#fcp-snowflake" transform="translate(38, 250) scale(0.85)" opacity="0.5"/>
        <use href="#fcp-snowflake" transform="translate(362, 260) scale(0.9)" opacity="0.5"/>
        <use href="#fcp-snowflake" transform="translate(52, 390) scale(1.05)" opacity="0.6"/>
        <use href="#fcp-snowflake" transform="translate(348, 386) scale(1.05)" opacity="0.6"/>
        {{-- Miniature corner pine trees at bottom --}}
        <g opacity="0.42" fill="url(#fcg-gold)">
            <polygon points="52,442 40,466 64,466"/><polygon points="52,430 43,450 61,450"/>
            <polygon points="348,442 336,466 360,466"/><polygon points="348,430 339,450 357,450"/>
        </g>
    </symbol>

    <symbol id="fc-backdrop-new_year" viewBox="0 0 400 500">
        <g transform="translate(66, 68) scale(0.95)" opacity="0.78"><use href="#fcp-firework"/></g>
        <g transform="translate(334, 68) scale(0.95)" opacity="0.78"><use href="#fcp-firework"/></g>
        <g transform="translate(54, 428) scale(0.7)" opacity="0.6"><use href="#fcp-firework"/></g>
        <g transform="translate(346, 428) scale(0.7)" opacity="0.6"><use href="#fcp-firework"/></g>
        <use href="#fcp-starburst" transform="translate(42, 210) scale(0.7)" opacity="0.65"/>
        <use href="#fcp-starburst" transform="translate(358, 210) scale(0.7)" opacity="0.65"/>
    </symbol>

    <symbol id="fc-backdrop-easter" viewBox="0 0 400 500">
        <g opacity="0.65">
            <ellipse cx="54" cy="442" rx="12" ry="16" transform="rotate(-15 54 442)" fill="#fbcfe8" stroke="url(#fcg-gold)" stroke-width="1.5"/>
            <ellipse cx="346" cy="442" rx="12" ry="16" transform="rotate(15 346 442)" fill="#bbf7d0" stroke="url(#fcg-gold)" stroke-width="1.5"/>
            <use href="#fcp-starburst" transform="translate(56, 62) scale(0.75)"/>
            <use href="#fcp-starburst" transform="translate(344, 62) scale(0.75)"/>
        </g>
    </symbol>

    <symbol id="fc-backdrop-eid" viewBox="0 0 400 500">
        <g opacity="0.75" stroke="url(#fcg-gold)" fill="url(#fcg-gold)">
            <line x1="54" y1="0" x2="54" y2="54" stroke-width="1.4"/>
            <polygon points="44,54 64,54 59,80 49,80" fill="url(#fcg-flame)"/>
            <line x1="346" y1="0" x2="346" y2="54" stroke-width="1.4"/>
            <polygon points="336,54 356,54 351,80 341,80" fill="url(#fcg-flame)"/>
            <use href="#fcp-starburst" transform="translate(52, 436) scale(0.75)"/>
            <use href="#fcp-starburst" transform="translate(348, 436) scale(0.75)"/>
        </g>
    </symbol>

    <symbol id="fc-backdrop-diwali" viewBox="0 0 400 500">
        <g opacity="0.72">
            <g transform="translate(62, 66) scale(0.75)"><use href="#fcp-firework"/></g>
            <g transform="translate(338, 66) scale(0.75)"><use href="#fcp-firework"/></g>
            <g transform="translate(58, 448) scale(0.45)">
                <path d="M0 -54 C12 -36 12 -18 0 -8 C-12 -18 -12 -36 0 -54 Z" fill="url(#fcg-flame)"/>
                <path d="M-38 -8 Q0 4 38 -8 C30 16 16 22 0 22 C-16 22 -30 16 -38 -8 Z" fill="url(#fcg-gold)"/>
            </g>
            <g transform="translate(342, 448) scale(0.45)">
                <path d="M0 -54 C12 -36 12 -18 0 -8 C-12 -18 -12 -36 0 -54 Z" fill="url(#fcg-flame)"/>
                <path d="M-38 -8 Q0 4 38 -8 C30 16 16 22 0 22 C-16 22 -30 16 -38 -8 Z" fill="url(#fcg-gold)"/>
            </g>
        </g>
    </symbol>

    <symbol id="fc-backdrop-thanksgiving" viewBox="0 0 400 500">
        <g opacity="0.6" fill="url(#fcg-gold)">
            <ellipse cx="48" cy="72" rx="6" ry="14" transform="rotate(-35 48 72)"/>
            <ellipse cx="64" cy="56" rx="6" ry="14" transform="rotate(-15 64 56)"/>
            <ellipse cx="352" cy="72" rx="6" ry="14" transform="rotate(35 352 72)"/>
            <ellipse cx="336" cy="56" rx="6" ry="14" transform="rotate(15 336 56)"/>
            <ellipse cx="52" cy="442" rx="6" ry="14" transform="rotate(35 52 442)"/>
            <ellipse cx="348" cy="442" rx="6" ry="14" transform="rotate(-35 348 442)"/>
        </g>
    </symbol>

    <symbol id="fc-backdrop-mothers_day" viewBox="0 0 400 500">
        <g opacity="0.65">
            <circle cx="58" cy="60" r="12" fill="#fb7185" stroke="url(#fcg-gold)" stroke-width="1.2"/>
            <circle cx="342" cy="60" r="12" fill="#fb7185" stroke="url(#fcg-gold)" stroke-width="1.2"/>
            <circle cx="58" cy="440" r="10" fill="#fda4af" stroke="url(#fcg-gold)" stroke-width="1.2"/>
            <circle cx="342" cy="440" r="10" fill="#fda4af" stroke="url(#fcg-gold)" stroke-width="1.2"/>
        </g>
    </symbol>

    <symbol id="fc-backdrop-fathers_day" viewBox="0 0 400 500">
        <g opacity="0.65">
            <use href="#fcp-starburst" transform="translate(58, 60) scale(0.8)"/>
            <use href="#fcp-starburst" transform="translate(342, 60) scale(0.8)"/>
            <use href="#fcp-starburst" transform="translate(58, 440) scale(0.7)"/>
            <use href="#fcp-starburst" transform="translate(342, 440) scale(0.7)"/>
        </g>
    </symbol>

    <symbol id="fc-backdrop-reunion" viewBox="0 0 400 500">
        <g opacity="0.6" fill="url(#fcg-gold)">
            <use href="#fcp-starburst" transform="translate(58, 62) scale(0.75)"/>
            <use href="#fcp-starburst" transform="translate(342, 62) scale(0.75)"/>
            <circle cx="54" cy="440" r="5"/><circle cx="346" cy="440" r="5"/>
        </g>
    </symbol>

    <symbol id="fc-backdrop-milestone" viewBox="0 0 400 500">
        <g opacity="0.72">
            <use href="#fcp-starburst" transform="translate(58, 62) scale(0.9)"/>
            <use href="#fcp-starburst" transform="translate(342, 62) scale(0.9)"/>
            <use href="#fcp-starburst" transform="translate(58, 438) scale(0.75)"/>
            <use href="#fcp-starburst" transform="translate(342, 438) scale(0.75)"/>
        </g>
    </symbol>

    <symbol id="fc-backdrop-birthday" viewBox="0 0 400 500">
        <g opacity="0.72">
            <path d="M24 36 Q72 64 124 32 M276 32 Q328 64 376 36" fill="none" stroke="url(#fcg-gold)" stroke-width="1.5"/>
            <polygon points="46,44 62,48 52,64" fill="url(#fcg-crimson)"/>
            <polygon points="78,48 94,44 88,62" fill="url(#fcg-gold)"/>
            <polygon points="312,44 328,48 318,62" fill="url(#fcg-gold)"/>
            <polygon points="344,48 360,44 352,64" fill="url(#fcg-crimson)"/>
            <use href="#fcp-starburst" transform="translate(52, 438) scale(0.75)"/>
            <use href="#fcp-starburst" transform="translate(348, 438) scale(0.75)"/>
        </g>
    </symbol>

    <symbol id="fc-backdrop-wedding" viewBox="0 0 400 500">
        <g opacity="0.68">
            <circle cx="58" cy="60" r="10" fill="#fda4af" stroke="url(#fcg-gold)" stroke-width="1.2"/>
            <circle cx="342" cy="60" r="10" fill="#fda4af" stroke="url(#fcg-gold)" stroke-width="1.2"/>
            <use href="#fcp-starburst" transform="translate(58, 438) scale(0.75)"/>
            <use href="#fcp-starburst" transform="translate(342, 438) scale(0.75)"/>
        </g>
    </symbol>

    <symbol id="fc-backdrop-new_baby" viewBox="0 0 400 500">
        <g opacity="0.7">
            <use href="#fcp-starburst" transform="translate(58, 62) scale(0.8)"/>
            <use href="#fcp-starburst" transform="translate(342, 62) scale(0.8)"/>
            <circle cx="54" cy="438" r="8" fill="#bae6fd" stroke="url(#fcg-gold)" stroke-width="1.2"/>
            <circle cx="346" cy="438" r="8" fill="#fbcfe8" stroke="url(#fcg-gold)" stroke-width="1.2"/>
        </g>
    </symbol>

    <symbol id="fc-backdrop-graduation" viewBox="0 0 400 500">
        <g opacity="0.72">
            <use href="#fcp-starburst" transform="translate(58, 62) scale(0.85)"/>
            <use href="#fcp-starburst" transform="translate(342, 62) scale(0.85)"/>
            <use href="#fcp-starburst" transform="translate(58, 438) scale(0.75)"/>
            <use href="#fcp-starburst" transform="translate(342, 438) scale(0.75)"/>
        </g>
    </symbol>

    <symbol id="fc-backdrop-remembrance" viewBox="0 0 400 500">
        <g opacity="0.55" fill="url(#fcg-gold)">
            <circle cx="58" cy="64" r="3"/><circle cx="342" cy="64" r="3"/>
            <circle cx="58" cy="436" r="3"/><circle cx="342" cy="436" r="3"/>
        </g>
    </symbol>
</svg>
@endif
