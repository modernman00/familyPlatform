import { describe, expect, it, vi } from 'vitest';
import { fireEvent, render, waitFor } from '@testing-library/preact';
import { FeedVideoGallery } from './FeedVideoGallery';
import { VideoModal } from './VideoModal';
import { resetMediaPlaybackStore } from './mediaPlaybackStore';

describe('FeedVideoGallery & VideoModal Aspect Ratio & Framing', () => {
    it('clamps portrait video feed preview to 4/5 full-width with object-fit: cover', () => {
        const { container } = render(
            <FeedVideoGallery
                videos={[{
                    type: 'direct',
                    embedUrl: '/resources/videos/portrait.mp4',
                    aspect_ratio: '9 / 16',
                    duration_sec: 120,
                }]}
            />
        );

        const frame = container.querySelector('.video-rendition-frame');
        const video = container.querySelector('video');
        expect(frame).toBeTruthy();
        expect(video).toBeTruthy();
        expect(frame.style.width).toBe('100%');
        expect(frame.style.maxWidth).toBe('100%');
        expect(frame.style.aspectRatio).toBe('4 / 5');
        expect(video.style.objectFit).toBe('cover');
    });

    it('clamps ultra-wide landscape video feed preview to 16/10 with object-fit: cover', () => {
        const { container } = render(
            <FeedVideoGallery
                videos={[{
                    type: 'direct',
                    embedUrl: '/resources/videos/landscape.mp4',
                    aspect_ratio: '16 / 9',
                    duration_sec: 240,
                }]}
            />
        );

        const frame = container.querySelector('.video-rendition-frame');
        const video = container.querySelector('video');
        expect(frame).toBeTruthy();
        expect(video).toBeTruthy();
        expect(frame.style.width).toBe('100%');
        expect(frame.style.aspectRatio).toBe('16 / 10');
        expect(video.style.objectFit).toBe('cover');
    });

    it('keeps full uncropped object-fit: contain in VideoModal', () => {
        const { container } = render(
            <VideoModal
                isOpen={true}
                videos={[{
                    type: 'direct',
                    embedUrl: '/resources/videos/portrait.mp4',
                    aspect_ratio: '9 / 16',
                }]}
                initialIndex={0}
                onClose={() => {}}
            />
        );

        const video = container.querySelector('video');
        expect(video).toBeTruthy();
        expect(video.style.objectFit).toBe('contain');
        expect(video.style.maxHeight).toBe('78vh');
    });

    it('keeps full 9 / 16 aspect ratio for portrait iframe shorts in VideoModal', () => {
        const { container } = render(
            <VideoModal
                isOpen={true}
                videos={[{
                    type: 'youtube',
                    embedUrl: 'https://www.youtube.com/embed/abc123',
                    originalUrl: 'https://www.youtube.com/shorts/abc123',
                    isShort: true,
                }]}
                initialIndex={0}
                onClose={() => {}}
            />
        );

        const iframe = container.querySelector('iframe');
        expect(iframe).toBeTruthy();
        expect(iframe.parentElement.style.aspectRatio).toBe('9 / 16');
        expect(iframe.parentElement.classList.contains('ratio-16x9')).toBe(false);
    });

    it('does not pause native <video controls> playback when clicked on iOS/mobile', () => {
        const { container } = render(
            <FeedVideoGallery
                videos={[{
                    type: 'direct',
                    embedUrl: '/resources/videos/sample.mp4',
                    aspect_ratio: '9 / 16',
                }]}
            />
        );

        const video = container.querySelector('video');
        expect(video).toBeTruthy();
        let pauseCalls = 0;
        Object.defineProperty(video, 'paused', { value: false, configurable: true });
        video.pause = () => { pauseCalls += 1; };

        video.click();
        expect(pauseCalls).toBe(0);
    });

    it('frames generic Facebook video embeds in an unclipped 4/5 centred stage instead of 16/10', () => {
        const { container } = render(
            <FeedVideoGallery
                videos={[{
                    type: 'facebook',
                    embedUrl: 'https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2Fschoolofgreatness%2Fvideos%2F123456789%2F&show_text=false&autoplay=0',
                    originalUrl: 'https://www.facebook.com/schoolofgreatness/videos/123456789/',
                }]}
            />
        );

        const stage = container.querySelector('.facebook-embed-stage');
        const iframe = container.querySelector('iframe');
        expect(stage).toBeTruthy();
        expect(iframe).toBeTruthy();
        expect(stage.style.aspectRatio).toBe('4 / 5');
        expect(stage.style.maxWidth).toBe('min(520px, 100%)');
    });

    it('frames Facebook Reels in a 9/16 portrait stage in both FeedVideoGallery and VideoModal', () => {
        const reelVid = {
            type: 'facebook',
            embedUrl: 'https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2Freel%2F1659146825685560%2F&show_text=false&autoplay=0',
            originalUrl: 'https://www.facebook.com/reel/1659146825685560/',
        };
        const { container } = render(<FeedVideoGallery videos={[reelVid]} />);
        const stage = container.querySelector('.facebook-embed-stage');
        expect(stage).toBeTruthy();
        expect(stage.style.aspectRatio).toBe('9 / 16');
        expect(stage.style.maxWidth).toBe('min(420px, 100%)');

        const { container: modalContainer } = render(
            <VideoModal isOpen={true} videos={[reelVid]} initialIndex={0} onClose={() => {}} />
        );
        const modalIframe = modalContainer.querySelector('iframe');
        expect(modalIframe).toBeTruthy();
        expect(modalIframe.parentElement.style.aspectRatio).toBe('9 / 16');
        expect(modalIframe.parentElement.classList.contains('ratio-16x9')).toBe(false);
    });

    it('respects explicit landscape 16/9 aspect_ratio on Facebook video embeds', () => {
        const { container } = render(
            <FeedVideoGallery
                videos={[{
                    type: 'facebook',
                    embedUrl: 'https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2Fexample%2Fvideos%2F999%2F&show_text=false&autoplay=0',
                    originalUrl: 'https://www.facebook.com/example/videos/999/',
                    aspect_ratio: '16 / 9',
                }]}
            />
        );

        const stage = container.querySelector('.facebook-embed-stage');
        expect(stage).toBeTruthy();
        expect(stage.style.aspectRatio).toBe('16 / 9');
        expect(stage.style.maxWidth).toBe('100%');
    });

    it('renders CC button, Subtitles popover (Off / English Auto-generated / Options), and fetches captions', async () => {
        resetMediaPlaybackStore();
        const originalFetch = globalThis.fetch;
        globalThis.fetch = vi.fn().mockResolvedValue({
            ok: true,
            status: 200,
            headers: new Headers({ 'content-type': 'application/json' }),
            json: async () => ({
                success: true,
                language: 'en-GB',
                label: 'English (Auto-generated)',
                vtt: 'WEBVTT\n\n00:00:00.000 --> 00:00:05.000\nWelcome home to the family.',
                cues: [{ start: 0, end: 5, text: 'Welcome home to the family.' }],
            }),
        });

        try {
            const { container } = render(
                <FeedVideoGallery
                    videos={[{
                        type: 'direct',
                        embedUrl: '/resources/videos/posts/cc-test.mp4',
                        aspect_ratio: '16 / 9',
                        duration_sec: 15,
                    }]}
                />
            );

            const ccBtn = container.querySelector('[data-testid="video-cc-btn"]');
            expect(ccBtn).toBeTruthy();
            expect(ccBtn.style.minWidth).toBe('44px');
            expect(ccBtn.style.minHeight).toBe('44px');

            fireEvent.click(ccBtn);
            const popover = container.querySelector('[data-testid="video-cc-popover"]');
            expect(popover).toBeTruthy();
            expect(popover.textContent).toContain('Subtitles');
            expect(popover.textContent).toContain('Off');
            expect(popover.textContent).toContain('English (Auto-generated)');

            const optionsBtn = container.querySelector('[data-testid="video-cc-options-btn"]');
            fireEvent.click(optionsBtn);
            expect(container.querySelector('[data-testid="video-cc-options-panel"]')).toBeTruthy();

            fireEvent.click(optionsBtn); // back to tracks
            const autoEnBtn = container.querySelector('[data-testid="video-cc-auto-en"]');
            fireEvent.click(autoEnBtn);

            await waitFor(() => {
                const overlay = container.querySelector('[data-testid="video-cc-overlay"]');
                expect(overlay).toBeTruthy();
                expect(overlay.textContent).toContain('Welcome home to the family.');
            });
        } finally {
            globalThis.fetch = originalFetch;
            resetMediaPlaybackStore();
        }
    });

    it('renders CC control button inside VideoModal for direct videos', () => {
        const { container } = render(
            <VideoModal
                isOpen={true}
                videos={[{
                    type: 'direct',
                    embedUrl: '/resources/videos/posts/modal-cc.mp4',
                    aspect_ratio: '16 / 9',
                }]}
                initialIndex={0}
                onClose={() => {}}
            />
        );
        const modalCcBtn = container.querySelector('[data-testid="modal-video-cc-btn"]');
        expect(modalCcBtn).toBeTruthy();
    });
});
