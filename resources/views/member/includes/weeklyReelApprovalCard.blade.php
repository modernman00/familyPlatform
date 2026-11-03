@php
    $hasPendingReel = !empty($pendingWeeklyReel);
    $reel = $pendingWeeklyReel ?? null;
    $storyboard = $reel['storyboard'] ?? [];
    $scenes = $storyboard['scenes'] ?? [];
    $audioUrl = $reel['audio_url'] ?? '/public/audio/reels/library/acoustic_warmth.wav';
    $musicTitle = $reel['music_title'] ?? 'Acoustic Warmth (Family Highlights)';
    $reelId = $reel['id'] ?? 0;
    $reelTitle = $reel['title'] ?? 'Our Family Highlights';
    $duration = $reel['duration_seconds'] ?? 45;
@endphp

@if($hasPendingReel)
<div id="weeklyReelApprovalCard" 
     x-data="weeklyReelPlayer({{ (int)$reelId }}, {{ json_encode($scenes, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) }}, '{{ addslashes((string)$audioUrl) }}')" 
     x-show="visible" 
     x-cloak
     class="card mb-4 shadow-sm border-0 weekly-reel-card"
     style="border-radius: 20px; overflow: hidden; background: linear-gradient(145deg, #ffffff 0%, #fdfbf7 100%); border: 1.5px solid #e2e8f0;">
    
    <!-- Header Banner -->
    <div class="px-4 pt-3 pb-2 d-flex align-items-center justify-content-between flex-wrap gap-2 border-bottom" style="background: rgba(248, 250, 252, 0.85);">
        <div class="d-flex align-items-center gap-2">
            <span class="badge rounded-pill px-3 py-2 text-white font-weight-bold" 
                  style="background: linear-gradient(135deg, #b8202c 0%, #8b131d 100%); font-size: 0.78rem; letter-spacing: 0.04em;">
                <i class="fa fa-film mr-1"></i> WEEKLY RECAP ({{ $duration }}s)
            </span>
            <span class="text-muted small font-weight-medium">Ready for your approval</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge badge-light border text-dark font-weight-normal px-2 py-1 small">
                <i class="fa fa-music text-primary mr-1"></i> <span class="d-none d-sm-inline">{{ $musicTitle }}</span>
            </span>
            <button type="button" 
                    @click="dismissReel()" 
                    class="btn btn-sm btn-link text-muted p-1 d-inline-flex align-items-center justify-content-center" 
                    title="Dismiss for this week"
                    aria-label="Dismiss weekly recap"
                    style="min-width: 44px; min-height: 44px; text-decoration: none;">
                <i class="fa fa-times" aria-hidden="true"></i>
            </button>
        </div>
    </div>

    <!-- Interactive Reel Player & Storyboard -->
    <div class="position-relative bg-dark text-white text-center" style="min-height: 380px; max-height: 480px; overflow: hidden;">
        <!-- Top Story Progress Bars -->
        <div class="position-absolute top-0 left-0 right-0 p-2 d-flex gap-1" style="z-index: 10; width: 100%;">
            <template x-for="(scene, index) in scenes" :key="index">
                <div class="flex-grow-1 bg-white-50 rounded" style="height: 3.5px; overflow: hidden; background: rgba(255, 255, 255, 0.35);">
                    <div class="h-100 bg-white" 
                         :style="'width: ' + (currentSceneIndex > index ? '100%' : (currentSceneIndex === index ? progressPercent + '%' : '0%')) + '; transition: width 0.1s linear;'"></div>
                </div>
            </template>
        </div>

        <!-- Audio Player (Hidden Element) -->
        <audio x-ref="audioTrack" preload="auto" loop>
            <source :src="audioUrl" type="audio/wav">
            <source :src="audioUrl" type="audio/mpeg">
        </audio>

        <!-- Scene Slides (Smooth 60fps Carousel) -->
        <template x-for="(scene, index) in scenes" :key="index">
            <div x-show="currentSceneIndex === index" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="h-100 w-100 d-flex flex-column justify-content-center align-items-center p-4 position-relative"
                 style="min-height: 380px;">
                
                <!-- Background Image or Decorative Gradient -->
                <template x-if="safeMediaUrl(scene.image_url)">
                    <img :src="safeMediaUrl(scene.image_url)" 
                         class="position-absolute w-100 h-100" 
                         style="object-fit: cover; filter: brightness(0.72); top: 0; left: 0;"
                         alt="Family highlight photo">
                </template>
                <template x-if="!safeMediaUrl(scene.image_url)">
                    <div class="position-absolute w-100 h-100" 
                         style="top: 0; left: 0; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);"></div>
                </template>

                <!-- Foreground Content Overlay -->
                <div class="position-relative z-index-2 px-3 py-2 text-center" style="max-width: 540px; text-shadow: 0 2px 8px rgba(0,0,0,0.85);">
                    <template x-if="scene.title">
                        <h4 class="font-weight-bold text-white mb-2" x-text="scene.title" style="letter-spacing: -0.02em;"></h4>
                    </template>
                    <template x-if="scene.subtitle">
                        <p class="text-white-50 small mb-2 text-uppercase font-weight-bold" x-text="scene.subtitle" style="letter-spacing: 0.08em;"></p>
                    </template>
                    <template x-if="scene.caption">
                        <p class="text-white font-weight-medium mb-1" x-text="scene.caption" style="font-size: 1.05rem; line-height: 1.4;"></p>
                    </template>
                    <template x-if="scene.author">
                        <span class="badge badge-pill badge-dark px-3 py-1 mt-2 text-white-50" style="background: rgba(0,0,0,0.55); backdrop-filter: blur(4px);">
                            Shared by <strong class="text-white" x-text="scene.author"></strong>
                        </span>
                    </template>
                </div>
            </div>
        </template>

        <!-- Player Controls (Floating Overlay) -->
        <div class="position-absolute bottom-0 left-0 right-0 p-3 d-flex justify-content-between align-items-center" style="z-index: 10; background: linear-gradient(to top, rgba(0,0,0,0.65), transparent);">
            <button type="button" 
                    @click="prevScene()" 
                    class="btn btn-sm btn-dark rounded-circle text-white" 
                    style="width: 44px; height: 44px; background: rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.25);"
                    aria-label="Previous scene">
                <i class="fa fa-chevron-left" aria-hidden="true"></i>
            </button>

            <button type="button" 
                    @click="togglePlay()" 
                    class="btn btn-light rounded-circle text-dark shadow font-weight-bold" 
                    style="width: 48px; height: 48px;"
                    aria-label="Play or pause reel">
                <i :class="isPlaying ? 'fa fa-pause' : 'fa fa-play'" aria-hidden="true"></i>
            </button>

            <div class="d-flex align-items-center gap-2">
                <button type="button" 
                        @click="toggleMute()" 
                        class="btn btn-sm btn-dark rounded-circle text-white" 
                        style="width: 44px; height: 44px; background: rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.25);"
                        aria-label="Mute or unmute music">
                    <i :class="isMuted ? 'fa fa-volume-off' : 'fa fa-volume-up'" aria-hidden="true"></i>
                </button>
                <button type="button" 
                        @click="nextScene()" 
                        class="btn btn-sm btn-dark rounded-circle text-white" 
                        style="width: 44px; height: 44px; background: rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.25);"
                        aria-label="Next scene">
                    <i class="fa fa-chevron-right" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Action Confirmation & Approval Bar -->
    <div class="p-3 bg-white d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="pr-2" style="max-width: 60%;">
            <div class="font-weight-bold text-dark" style="font-size: 0.95rem;">
                <i class="fa fa-heart text-danger mr-1"></i> {{ $reelTitle }}
            </div>
            <div class="text-muted small">
                Approve this curated montage to automatically share it to your family feed and the Reels tray.
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" 
                    @click="dismissReel()" 
                    class="btn btn-outline-secondary px-3" 
                    style="min-height: 44px; border-radius: 12px; font-weight: 500;">
                Not This Week
            </button>

            <button type="button" 
                    @click="approveReel($event)" 
                    class="btn btn-success px-4 text-white font-weight-bold btn-spin-on-click" 
                    data-spin="true"
                    style="min-height: 44px; border-radius: 12px; background: #16a34a; border-color: #16a34a; white-space: nowrap;">
                <i class="fa fa-check mr-1" aria-hidden="true"></i> Approve &amp; Post to Feed
            </button>
        </div>
    </div>
</div>

<script>
function weeklyReelPlayer(reelId, scenes, audioUrl) {
    return {
        reelId: reelId,
        scenes: scenes || [],
        audioUrl: audioUrl,
        currentSceneIndex: 0,
        progressPercent: 0,
        isPlaying: true,
        isMuted: false,
        visible: true,
        timer: null,
        progressInterval: null,

        init() {
            if (this.scenes.length === 0) return;
            this.$nextTick(() => {
                this.startPlayback();
            });
        },

        safeMediaUrl(url) {
            if (!url || typeof url !== 'string') return '';
            const trimmed = url.trim();
            if (trimmed.startsWith('/') && !trimmed.startsWith('//')) return trimmed;
            if (trimmed.startsWith('https://') || trimmed.startsWith('http://')) return trimmed;
            return '';
        },

        startPlayback() {
            this.isPlaying = true;
            this.progressPercent = 0;
            const currentDuration = (this.scenes[this.currentSceneIndex]?.duration || 5) * 1000;
            const stepMs = 50;
            const increment = (stepMs / currentDuration) * 100;

            clearInterval(this.progressInterval);
            this.progressInterval = setInterval(() => {
                if (!this.isPlaying) return;
                this.progressPercent += increment;
                if (this.progressPercent >= 100) {
                    this.nextScene();
                }
            }, stepMs);

            if (this.$refs.audioTrack && !this.isMuted) {
                this.$refs.audioTrack.play().catch(() => {
                    // Autoplay restriction handled gracefully
                    this.isMuted = true;
                });
            }
        },

        togglePlay() {
            this.isPlaying = !this.isPlaying;
            if (this.$refs.audioTrack) {
                if (this.isPlaying && !this.isMuted) {
                    this.$refs.audioTrack.play().catch(() => {});
                } else {
                    this.$refs.audioTrack.pause();
                }
            }
        },

        toggleMute() {
            this.isMuted = !this.isMuted;
            if (this.$refs.audioTrack) {
                this.$refs.audioTrack.muted = this.isMuted;
                if (!this.isMuted && this.isPlaying) {
                    this.$refs.audioTrack.play().catch(() => {});
                }
            }
        },

        nextScene() {
            if (this.currentSceneIndex < this.scenes.length - 1) {
                this.currentSceneIndex++;
                this.startPlayback();
            } else {
                // Loop back to beginning
                this.currentSceneIndex = 0;
                this.startPlayback();
            }
        },

        prevScene() {
            if (this.currentSceneIndex > 0) {
                this.currentSceneIndex--;
            } else {
                this.currentSceneIndex = 0;
            }
            this.startPlayback();
        },

        async approveReel(event) {
            const btn = event?.currentTarget || event?.target;
            if (window.ScoreTenantSpinner) {
                window.ScoreTenantSpinner.start(btn);
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                              document.querySelector('input[name="token"]')?.value || '';

            try {
                const response = await fetch('/api/weekly-reel/approve', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        reel_id: this.reelId,
                        token: csrfToken
                    })
                });

                const data = await response.json();

                if (window.ScoreTenantSpinner) {
                    window.ScoreTenantSpinner.stop(btn);
                }

                if (data.success) {
                    this.visible = false;
                    clearInterval(this.progressInterval);
                    if (this.$refs.audioTrack) {
                        this.$refs.audioTrack.pause();
                    }

                    if (window.Swal) {
                        Swal.fire({
                            title: 'Reel Published!',
                            text: 'Your Weekly Family Reel has been posted to the feed and shared with your family circle.',
                            icon: 'success',
                            confirmButtonColor: '#16a34a'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        alert('Weekly Family Reel posted successfully!');
                        window.location.reload();
                    }
                } else {
                    alert(data.error || 'Failed to approve weekly reel. Please try again.');
                }
            } catch (err) {
                if (window.ScoreTenantSpinner) {
                    window.ScoreTenantSpinner.stop(btn);
                }
                alert('A network error occurred. Please try again.');
            }
        },

        async dismissReel() {
            if (!confirm('Would you like to dismiss this week’s recap? You can still view photos in the feed.')) {
                return;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                              document.querySelector('input[name="token"]')?.value || '';

            try {
                await fetch('/api/weekly-reel/dismiss', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        reel_id: this.reelId,
                        token: csrfToken
                    })
                });
            } catch (e) {
                // Silently dismiss on error
            }

            this.visible = false;
            clearInterval(this.progressInterval);
            if (this.$refs.audioTrack) {
                this.$refs.audioTrack.pause();
            }
        }
    };
}
</script>
@endif
