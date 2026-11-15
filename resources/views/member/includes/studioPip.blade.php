@php
    $pipMode = $mode ?? 'canvas';
    $pipTitle = $title ?? 'Live preview';
    $pipBlocked = $blockedExpr ?? 'false';
    $pipVideo = !empty($hasVideoControls);
@endphp

{{-- Floating PiP Dock --}}
<div class="spip-dock"
     :class="{ 'spip-dock--bottom': pipBottom }"
     x-show="isPipDockVisible({{ $pipBlocked }})"
     x-cloak
     x-transition.opacity
     role="region"
     aria-label="{{ $pipTitle }} pop-out">
    <div class="spip-dock__head">
        <span class="spip-dock__title">{{ $pipTitle }}</span>
        <div class="spip-dock__actions">
            <button type="button" class="spip-icon-btn d-lg-none" @click="toggleCorner()" title="Move corner" aria-label="Move preview corner">
                <i class="bi" :class="pipBottom ? 'bi-arrow-up-short' : 'bi-arrow-down-short'" aria-hidden="true"></i>
            </button>
            <button type="button" class="spip-icon-btn" @click="expandPip()" title="Expand preview" aria-label="Expand preview">
                <i class="bi bi-arrows-angle-expand" aria-hidden="true"></i>
            </button>
            <button type="button" class="spip-icon-btn" @click="minimisePip()" title="Minimise preview" aria-label="Minimise preview">
                <i class="bi bi-dash-lg" aria-hidden="true"></i>
            </button>
        </div>
    </div>

    <div class="spip-dock__body" @click="expandPip()" role="button" tabindex="0" @keydown.enter.prevent="expandPip()" @keydown.space.prevent="expandPip()" aria-label="Tap to expand {{ strtolower($pipTitle) }}">
        @if($pipMode === 'card')
            <div class="spip-dock__card-wrap">
                @include('festive.card', ['live' => true, 'thumb' => true, 'tilt' => false, 'animateIn' => false, 'styleExpr' => 'c.style', 'card' => []])
            </div>
        @else
            <canvas class="spip-dock__canvas" x-ref="pipCanvas" aria-label="{{ $pipTitle }}"></canvas>
        @endif
    </div>

    @if($pipVideo)
        <div class="spip-dock__foot">
            <button type="button" class="spip-icon-btn" @click="triggerVideoPlayPause()" :aria-label="pipPlaying ? 'Pause video' : 'Play video'">
                <i class="bi" :class="pipPlaying ? 'bi-pause-fill' : 'bi-play-fill'" aria-hidden="true"></i>
            </button>
            <span class="spip-dock__time" x-text="pipTimecode"></span>
        </div>
    @endif
</div>

{{-- Minimised Pill --}}
<button type="button"
        class="spip-pill"
        x-show="isPipPillVisible({{ $pipBlocked }})"
        x-cloak
        x-transition.opacity
        @click="restorePip()"
        aria-label="Show {{ strtolower($pipTitle) }}">
    <i class="bi bi-pip" aria-hidden="true"></i>
    <span>Show preview</span>
</button>

{{-- Expanded Full-Screen Preview Modal --}}
<div class="spip-modal"
     x-show="pipExpanded"
     x-cloak
     x-trap.noscroll="pipExpanded"
     x-transition.opacity
     @keydown.escape.window="closeExpandedPip()"
     @click.self="closeExpandedPip()"
     role="dialog"
     aria-modal="true"
     aria-labelledby="spipModalTitle">
    <div class="spip-modal__dialog" @click.stop>
        <div class="spip-modal__head">
            <h3 class="spip-modal__title" id="spipModalTitle">{{ $pipTitle }}</h3>
            <button type="button" class="spip-icon-btn" @click="closeExpandedPip()" aria-label="Close expanded preview">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </div>

        <div class="spip-modal__body">
            @if($pipMode === 'card')
                <div class="spip-modal__card">
                    @include('festive.card', ['live' => true, 'tilt' => true, 'thumb' => false, 'animateIn' => false, 'styleExpr' => 'c.style', 'card' => []])
                </div>
            @else
                <canvas class="spip-modal__canvas" x-ref="pipModalCanvas" aria-label="Expanded {{ strtolower($pipTitle) }}"></canvas>
            @endif
        </div>

        <div class="spip-modal__foot">
            @if($pipVideo)
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="spip-trigger" @click="triggerVideoPlayPause()">
                        <i class="bi" :class="pipPlaying ? 'bi-pause-fill' : 'bi-play-fill'" aria-hidden="true"></i>
                        <span x-text="pipPlaying ? 'Pause' : 'Play'"></span>
                    </button>
                    <span class="spip-dock__time" x-text="pipTimecode"></span>
                </div>
            @else
                <span class="spip-dock__time">Live updates as you edit</span>
            @endif

            <button type="button" class="spip-trigger" @click="closeExpandedPip()">
                <i class="bi bi-check2" aria-hidden="true"></i>
                <span>Back to editor</span>
            </button>
        </div>
    </div>
</div>
