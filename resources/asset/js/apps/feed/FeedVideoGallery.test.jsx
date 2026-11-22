import { describe, expect, it } from 'vitest';
import { render } from '@testing-library/preact';
import { FeedVideoGallery } from './FeedVideoGallery';
import { VideoModal } from './VideoModal';

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
});
