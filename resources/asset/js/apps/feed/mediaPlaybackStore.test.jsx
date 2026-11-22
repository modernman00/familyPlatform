import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest';
import { render, fireEvent, waitFor } from '@testing-library/preact';
import { normalizePost } from './feedUtils';
import { FeedVideoGallery } from './FeedVideoGallery';
import {
    activePostId,
    isGlobalMuted,
    registerMediaPlayer,
    requestActivePlayer,
    toggleGlobalMute,
    resetMediaPlaybackStore,
} from './mediaPlaybackStore';

describe('NormalizedMedia Contract & MediaPlaybackStore', () => {
    let observerCallbacks = [];
    let observedElements = [];
    let originalIO;

    beforeEach(() => {
        resetMediaPlaybackStore();
        observerCallbacks = [];
        observedElements = [];
        originalIO = globalThis.IntersectionObserver;

        globalThis.IntersectionObserver = class MockIntersectionObserver {
            constructor(cb) {
                observerCallbacks.push(cb);
            }
            observe(el) {
                observedElements.push(el);
            }
            unobserve(el) {
                observedElements = observedElements.filter((x) => x !== el);
            }
            disconnect() {
                observedElements = [];
            }
        };
    });

    afterEach(() => {
        resetMediaPlaybackStore();
        globalThis.IntersectionObserver = originalIO;
        vi.useRealTimers();
    });

    it('normalizes images, native videos, inline iframes, and blocked external cards into normalized_media', () => {
        const post = normalizePost({
            post_no: '9001',
            id: 'usr_1',
            postMessage: 'Family highlights',
            images: ['/resources/images/posts/pic1.jpg'],
            videos: [
                {
                    type: 'direct',
                    embedUrl: '/resources/videos/posts/clip.mp4',
                    aspect_ratio: '9 / 16',
                    duration_sec: 30,
                },
                {
                    type: 'youtube',
                    videoId: 'abc12345678',
                    embedUrl: 'https://www.youtube.com/embed/abc12345678',
                    thumbnailUrl: 'https://img.youtube.com/vi/abc12345678/hqdefault.jpg',
                },
            ],
        });

        expect(Array.isArray(post.normalized_media)).toBe(true);
        expect(post.normalized_media).toHaveLength(3);
        expect(post.normalized_media[0].renderMode).toBe('image');
        expect(post.normalized_media[1].renderMode).toBe('native_video');
        expect(post.normalized_media[1].videoSrc).toBe('/resources/videos/posts/clip.mp4');
        expect(post.normalized_media[2].renderMode).toBe('inline_iframe');
        expect(post.normalized_media[2].embedSrc).toContain('abc12345678');

        const blockedPost = normalizePost({
            post_no: '9002',
            id: 'usr_2',
            postMessage: 'Blocked embed',
            link_preview: {
                url: 'https://blocked.example.com/page',
                title: 'Blocked Page',
                image: '/public/uploads/link_previews/hash.jpg',
                frame_blocked: true,
                embed_url: 'https://blocked.example.com/embed',
            },
        });
        expect(blockedPost.normalized_media[0].renderMode).toBe('external_card');
        expect(blockedPost.normalized_media[0].embedSrc).toBeNull();
    });

    it('debounces IntersectionObserver by 250ms and activates only the single centre-viewport player', () => {
        vi.useFakeTimers();

        const elA = document.createElement('div');
        elA.getBoundingClientRect = () => ({ top: 50, height: 200 }); // center 150
        const elB = document.createElement('div');
        elB.getBoundingClientRect = () => ({ top: 350, height: 200 }); // center 450 (viewport center ~450)

        const aActivate = vi.fn();
        const aDeactivate = vi.fn();
        const bActivate = vi.fn();
        const bDeactivate = vi.fn();

        registerMediaPlayer('post-1', elA, { onActivate: aActivate, onDeactivate: aDeactivate });
        registerMediaPlayer('post-2', elB, { onActivate: bActivate, onDeactivate: bDeactivate });

        // Trigger IntersectionObserver with both intersecting >= 0.6
        observerCallbacks[0]([
            { target: elA, isIntersecting: true, intersectionRatio: 0.7 },
            { target: elB, isIntersecting: true, intersectionRatio: 0.85 },
        ]);

        // Before 250ms debounce, nothing should activate yet
        vi.advanceTimersByTime(200);
        expect(activePostId.peek()).toBeNull();

        // After 250ms debounce, post-2 (centre viewport & 0.85) wins
        vi.advanceTimersByTime(60);
        expect(activePostId.peek()).toBe('post-2');
        expect(bActivate).toHaveBeenCalledTimes(1);
        expect(aActivate).not.toHaveBeenCalled();

        // Visibility change to hidden pauses active player immediately
        Object.defineProperty(document, 'visibilityState', { value: 'hidden', configurable: true });
        document.dispatchEvent(new Event('visibilitychange'));
        expect(activePostId.peek()).toBeNull();
        expect(bDeactivate).toHaveBeenCalledTimes(1);

        Object.defineProperty(document, 'visibilityState', { value: 'visible', configurable: true });
    });

    it('keeps off-screen third-party iframes unmounted as poster facades and mounts on click', async () => {
        const { container } = render(
            <FeedVideoGallery
                postNo="777"
                videos={[{
                    type: 'youtube',
                    videoId: 'xyz98765432',
                    embedUrl: 'https://www.youtube.com/embed/xyz98765432',
                    thumbnailUrl: 'https://img.youtube.com/vi/xyz98765432/hqdefault.jpg',
                }]}
            />
        );

        // Off-screen: iframe is NOT mounted, poster facade IS rendered
        expect(container.querySelector('iframe')).toBeNull();
        const facade = container.querySelector('[data-testid="iframe-poster-facade"]');
        expect(facade).toBeTruthy();

        // Click play on facade -> iframe mounts and post becomes active
        const playBtn = facade.querySelector('button');
        fireEvent.click(playBtn);
        expect(container.querySelector('iframe')).toBeTruthy();
        expect(activePostId.peek()).toBe('post-777');

        // Another post becomes active -> off-screen iframe unmounts back to poster facade
        requestActivePlayer('post-888');
        await waitFor(() => {
            expect(container.querySelector('iframe')).toBeNull();
            expect(container.querySelector('[data-testid="iframe-poster-facade"]')).toBeTruthy();
        });
    });

    it('syncs isGlobalMuted across native <video> players and preserves video src/buffer when paused', async () => {
        const { container } = render(
            <FeedVideoGallery
                postNo="555"
                videos={[{
                    type: 'direct',
                    embedUrl: '/resources/videos/posts/sample.mp4',
                    aspect_ratio: '16 / 9',
                }]}
            />
        );

        const video = container.querySelector('video');
        expect(video).toBeTruthy();
        expect(video.muted).toBe(true);
        expect(isGlobalMuted.peek()).toBe(true);

        // Toggle global mute
        toggleGlobalMute();
        await waitFor(() => {
            expect(isGlobalMuted.peek()).toBe(false);
            expect(video.muted).toBe(false);
        });

        // Deactivating player pauses without clearing src (buffer preserved)
        requestActivePlayer('post-555');
        requestActivePlayer('post-999');
        expect(video.getAttribute('src')).toBe('/resources/videos/posts/sample.mp4');
    });

    it('renders multi-video gallery from normalizedMedia with global mute sync and iframe poster facade', async () => {
        const { container } = render(
            <FeedVideoGallery
                postNo="888"
                normalizedMedia={[
                    {
                        id: 'm1',
                        provider: 'direct',
                        renderMode: 'native_video',
                        videoSrc: '/resources/videos/posts/clip1.mp4',
                        aspectRatio: '16 / 9',
                    },
                    {
                        id: 'm2',
                        provider: 'youtube',
                        renderMode: 'inline_iframe',
                        embedSrc: 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                        posterUrl: 'https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg',
                    },
                ]}
            />
        );

        const video = container.querySelector('video');
        expect(video).toBeTruthy();
        expect(video.muted).toBe(true);
        expect(container.querySelector('iframe')).toBeNull();
        expect(container.querySelector('[data-testid="iframe-poster-facade"]')).toBeTruthy();

        toggleGlobalMute();
        await waitFor(() => {
            expect(video.muted).toBe(false);
        });

        fireEvent.click(container.querySelector('[data-testid="iframe-poster-facade"] button'));
        expect(container.querySelector('iframe')).toBeTruthy();
    });
});
