import { describe, expect, it, vi, afterEach, beforeEach } from 'vitest';
import { render, fireEvent, screen, waitFor } from '@testing-library/preact';

const mockChannelBindings = {};
const mockDisconnect = vi.fn();

vi.mock('../../components/profilePage/sovereignRealtimeBridge', () => ({
    createHybridRealtimeClient: vi.fn(() => ({
        subscribe: vi.fn(() => ({
            bind: vi.fn((event, cb) => {
                mockChannelBindings[event] = cb;
            }),
        })),
        disconnect: mockDisconnect,
    })),
}));

import { FeedShell } from './FeedShell';

describe('Profile Feed Preact Island (Phases 1–4)', () => {
    const baseBootstrap = {
        famCode: 'OLAO60446',
        userId: 'u_1',
        firstName: 'Tunde',
        lastName: 'Olaogun',
        avatar: '/resources/images/profile/tunde.jpg',
        csrfToken: 'tok_feed_csrf',
        familyRoster: [
            { id: 'u_2', firstName: 'Kemi', lastName: 'Olaogun' },
        ],
        postAudiences: [
            { scope: 'birth', label: 'Birth Family' },
        ],
    };

    // Real PHP PostMessage::index DTO shape
    const samplePost = {
        post_no: '101',
        id: 'u_1',
        fullName: 'Tunde Olaogun',
        img: '/bad-avatar.png',
        postMessage: 'Sunday jollof with the family #Heritage',
        postFamCode: 'OLAO60446',
        post_likes: 2,
        views_count: 14,
        user_reaction: null,
        images: ['/img/sample1.jpg', '/img/sample2.jpg'],
        videos: [{ originalUrl: '/resources/videos/sample.mp4', embedUrl: '/resources/videos/sample.mp4', isDirect: true }],
        comments: [
            {
                comment_no: '501',
                post_no: '101',
                id: 'u_2',
                fullName: 'Kemi Olaogun',
                comment: 'Looks delicious!',
                reactions: [],
                post_time: '2026-11-20 12:00:00',
            },
        ],
        poll: {
            question: 'Which weekend?',
            total_votes: 4,
            options: [
                { option_id: 'opt_1', option_text: 'Saturday Picnic', vote_count: 3, percentage: 75 },
                { option_id: 'opt_2', option_text: 'Sunday Dinner', vote_count: 1, percentage: 25 },
            ],
            user_voted_option_id: [],
        },
        post_time: '2026-11-20 10:00:00',
    };

    beforeEach(() => {
        Object.keys(mockChannelBindings).forEach((k) => delete mockChannelBindings[k]);
        window.location.hash = '';
    });

    afterEach(() => {
        vi.restoreAllMocks();
    });

    it('Phase 1: unwraps real nested PHP envelope, handles broken avatar fallback, pagination, empty and error/retry states', async () => {
        const longNamePost = {
            ...samplePost,
            post_no: '102',
            id: 'u_3',
            fullName: `Abi ${'X'.repeat(80)}`,
            img: '',
            images: [],
            videos: [],
            poll: null,
            comments: [],
            postMessage: 'Hi',
        };

        let feedAttempt = 0;
        globalThis.fetch = vi.fn((url) => {
            if (String(url).includes('/api/hashtags/trending')) {
                return Promise.resolve({
                    ok: true,
                    status: 200,
                    headers: new Headers({ 'content-type': 'application/json' }),
                    json: async () => ({ status: 'success', tags: [{ tag: 'Heritage', count: 3 }] }),
                });
            }
            if (String(url).includes('filter=tagged')) {
                return Promise.resolve({
                    ok: true,
                    status: 200,
                    headers: new Headers({ 'content-type': 'application/json' }),
                    json: async () => ({
                        status: 'success',
                        message: { message: [], pagination: { has_more: false } },
                    }),
                });
            }
            feedAttempt += 1;
            if (feedAttempt === 1) {
                return Promise.resolve({
                    ok: false,
                    status: 500,
                    headers: new Headers({ 'content-type': 'application/json' }),
                    json: async () => ({ message: 'Server busy' }),
                });
            }
            if (feedAttempt === 2) {
                // Exact nested PHP response from PostMessage::index
                return Promise.resolve({
                    ok: true,
                    status: 200,
                    headers: new Headers({ 'content-type': 'application/json' }),
                    json: async () => ({
                        status: 'success',
                        message: {
                            message: [samplePost],
                            pagination: { has_more: true },
                        },
                    }),
                });
            }
            return Promise.resolve({
                ok: true,
                status: 200,
                headers: new Headers({ 'content-type': 'application/json' }),
                json: async () => ({
                    status: 'success',
                    message: {
                        message: [longNamePost],
                        pagination: { has_more: false },
                    },
                }),
            });
        });

        render(<FeedShell bootstrap={baseBootstrap} />);

        await waitFor(() => {
            expect(screen.getByRole('alert')).toBeTruthy();
        });

        fireEvent.click(screen.getByRole('button', { name: 'Retry' }));

        await waitFor(() => {
            expect(screen.getByText(/Sunday jollof/i)).toBeTruthy();
            expect(screen.getByText('Which weekend?')).toBeTruthy();
            expect(screen.getByText(/14 views/i)).toBeTruthy();
        });

        // Broken avatar falls back to initials "TO"
        const avatars = screen.getAllByAltText('Tunde Olaogun');
        fireEvent.error(avatars[0]);
        await waitFor(() => {
            expect(screen.getByText('TO')).toBeTruthy();
        });

        // Pagination: Load More Memories
        fireEvent.click(screen.getByRole('button', { name: /Load More Memories/i }));
        await waitFor(() => {
            expect(screen.getByText('Hi')).toBeTruthy();
        });

        // Switch to Tagged With You -> Empty state
        fireEvent.click(screen.getByRole('tab', { name: /Tagged With You/i }));
        await waitFor(() => {
            expect(screen.getByText(/No tagged memories yet/i)).toBeTruthy();
        });
    });

    it('Phase 2: supports optimistic reactions with rollback, poll voting, optimistic comments, LightboxModal & VideoModal', async () => {
        globalThis.fetch = vi.fn((url) => {
            if (String(url).includes('/profileCard/postLikes')) {
                return Promise.resolve({
                    ok: false,
                    status: 500,
                    headers: new Headers({ 'content-type': 'application/json' }),
                    json: async () => ({ message: 'Reaction failed' }),
                });
            }
            if (String(url).includes('/api/poll/vote')) {
                return Promise.resolve({
                    ok: true,
                    status: 200,
                    headers: new Headers({ 'content-type': 'application/json' }),
                    json: async () => ({ status: 'success' }),
                });
            }
            if (String(url).includes('/postCommentProfile')) {
                return Promise.resolve({
                    ok: true,
                    status: 200,
                    headers: new Headers({ 'content-type': 'application/json' }),
                    json: async () => ({
                        status: 'success',
                        message: { comment_no: '999', comment: 'Welcome home!' },
                    }),
                });
            }
            return Promise.resolve({
                ok: true,
                status: 200,
                headers: new Headers({ 'content-type': 'application/json' }),
                json: async () => ({ status: 'success', tags: [] }),
            });
        });

        render(
            <FeedShell
                bootstrap={{
                    ...baseBootstrap,
                    initialPosts: [samplePost],
                    initialHasMore: false,
                }}
            />
        );

        // Reaction optimistic bump then rollback on 500
        fireEvent.click(screen.getByRole('button', { name: 'React to this post' }));
        fireEvent.click(screen.getByRole('button', { name: 'Love' }));
        await waitFor(() => {
            expect(screen.getByRole('button', { name: 'React to this post' })).toBeTruthy();
        });

        // Poll vote
        fireEvent.click(screen.getByRole('button', { name: /Saturday Picnic/i }));
        await waitFor(() => {
            expect(screen.getByText('80%')).toBeTruthy();
        });

        // Comment submission with CSRF
        fireEvent.click(screen.getByRole('button', { name: 'Comment on this post' }));
        const commentInput = screen.getByLabelText('Write a comment');
        fireEvent.input(commentInput, { target: { value: 'Welcome home!' } });
        fireEvent.click(screen.getByRole('button', { name: 'Send' }));

        await waitFor(() => {
            expect(screen.getByText('Welcome home!')).toBeTruthy();
        });

        // LightboxModal open, next, close
        fireEvent.click(screen.getByRole('button', { name: /View photo 1 of 2/i }));
        expect(screen.getByRole('dialog', { name: 'Photo lightbox' })).toBeTruthy();
        fireEvent.click(screen.getByRole('button', { name: 'Next' }));
        expect(screen.getByText('Photo 2 of 2')).toBeTruthy();
        fireEvent.click(screen.getByRole('button', { name: 'Close lightbox' }));
        expect(screen.queryByRole('dialog', { name: 'Photo lightbox' })).toBeNull();

        // VideoModal open & close
        fireEvent.click(screen.getByRole('button', { name: 'Play in full video mode' }));
        expect(screen.getByRole('dialog', { name: 'Video player' })).toBeTruthy();
        fireEvent.click(screen.getByRole('button', { name: 'Close video player' }));
        expect(screen.queryByRole('dialog', { name: 'Video player' })).toBeNull();
    });

    it('Phase 3: bridges post-created and post-updated events from #postModal and deletes with confirmation', async () => {
        globalThis.fetch = vi.fn(() =>
            Promise.resolve({
                ok: true,
                status: 200,
                headers: new Headers({ 'content-type': 'application/json' }),
                json: async () => ({ status: 'success', tags: [] }),
            })
        );

        render(
            <FeedShell
                bootstrap={{
                    ...baseBootstrap,
                    initialPosts: [],
                    initialHasMore: false,
                }}
            />
        );

        // Simulate classic #postModal (allEvents.js) dispatching post-created
        window.dispatchEvent(new CustomEvent('post-created', {
            detail: {
                post_no: '202',
                id: 'u_1',
                fullName: 'Tunde Olaogun',
                postMessage: 'Brand new memory!',
                post_likes: 0,
                comments: [],
                post_time: new Date().toISOString(),
            },
        }));

        await waitFor(() => {
            expect(screen.getByText('Brand new memory!')).toBeTruthy();
        });

        // Simulate post-updated event
        window.dispatchEvent(new CustomEvent('post-updated', {
            detail: {
                postNo: '202',
                postMessage: 'Updated via modal!',
                post_style: 'grand_announcement',
            },
        }));

        await waitFor(() => {
            expect(screen.getByText('Updated via modal!')).toBeTruthy();
        });

        // Inline edit fallback via kebab menu
        fireEvent.click(screen.getByRole('button', { name: 'Edit Post' }));
        const editBox = await waitFor(() => screen.getByLabelText('Edit post message'));
        fireEvent.input(editBox, { target: { value: 'Edited family memory!' } });
        fireEvent.click(screen.getByRole('button', { name: 'Save Changes' }));

        await waitFor(() => {
            expect(screen.getByText('Edited family memory!')).toBeTruthy();
        });

        // Delete with confirmation
        fireEvent.click(screen.getByRole('button', { name: 'Delete Post' }));
        fireEvent.click(screen.getByRole('button', { name: 'Confirm Delete' }));

        await waitFor(() => {
            expect(screen.queryByText('Edited family memory!')).toBeNull();
        });
    });

    it('Phase 4: handles realtime pending-live-posts banner and deep-link post fetch & highlight', async () => {
        window.location.hash = '#post-777';
        globalThis.fetch = vi.fn((url) => {
            if (String(url).includes('/api/hashtags/trending')) {
                return Promise.resolve({
                    ok: true,
                    status: 200,
                    headers: new Headers({ 'content-type': 'application/json' }),
                    json: async () => ({ status: 'success', tags: [] }),
                });
            }
            return Promise.resolve({
                ok: true,
                status: 200,
                headers: new Headers({ 'content-type': 'application/json' }),
                json: async () => ({
                    status: 'success',
                    message: {
                        post_no: '777',
                        id: 'u_2',
                        fullName: 'Kemi Olaogun',
                        postMessage: 'Deep-linked milestone',
                        post_likes: 0,
                        comments: [],
                    },
                }),
            });
        });

        render(
            <FeedShell
                bootstrap={{
                    ...baseBootstrap,
                    initialPosts: [samplePost],
                    initialHasMore: false,
                }}
            />
        );

        await waitFor(() => {
            expect(screen.getByText('Deep-linked milestone')).toBeTruthy();
        });

        // Trigger realtime new-post event
        expect(typeof mockChannelBindings['new-post']).toBe('function');
        mockChannelBindings['new-post']([{
            post_no: '888',
            id: 'u_2',
            fullName: 'Kemi Olaogun',
            postMessage: 'Live broadcast memory!',
            post_likes: 0,
            comments: [],
        }]);

        await waitFor(() => {
            expect(screen.getByText(/1 new family memory shared/i)).toBeTruthy();
        });

        fireEvent.click(screen.getByRole('button', { name: 'Show New Posts' }));
        await waitFor(() => {
            expect(screen.getByText('Live broadcast memory!')).toBeTruthy();
        });
    });

    it('Google Photos multi-video album uses signed /resources/videos/external-stream proxy, poster thumbnails, no-referrer, and horizontal carousel', async () => {
        const gpAlbumPost = {
            post_no: '901',
            id: 'u_1',
            fullName: 'Tunde Olaogun',
            postMessage: 'Family trip clips https://photos.app.goo.gl/NycbauJZiwj3JpSc6',
            post_likes: 0,
            comments: [],
            link_preview: {
                is_google_photo: true,
                is_photo_link: true,
                url: 'https://photos.app.goo.gl/NycbauJZiwj3JpSc6',
                album_videos: [
                    'https://lh3.googleusercontent.com/vid1=m18',
                    'https://lh3.googleusercontent.com/vid2=m18',
                    'https://lh3.googleusercontent.com/vid3=m18',
                    'https://lh3.googleusercontent.com/vid4=m18',
                ],
                proxied_album_videos: [
                    '/resources/videos/external-stream?url=vid1&sig=s1',
                    '/resources/videos/external-stream?url=vid2&sig=s2',
                    '/resources/videos/external-stream?url=vid3&sig=s3',
                    '/resources/videos/external-stream?url=vid4&sig=s4',
                ],
            },
        };

        globalThis.fetch = vi.fn(() =>
            Promise.resolve({
                ok: true,
                status: 200,
                headers: new Headers({ 'content-type': 'application/json' }),
                json: async () => ({ status: 'success', tags: [] }),
            })
        );

        const { container } = render(
            <FeedShell
                bootstrap={{
                    ...baseBootstrap,
                    initialPosts: [gpAlbumPost],
                    initialHasMore: false,
                }}
            />
        );

        // Horizontal carousel renders instead of vertical stacked "Expand Video" boxes
        const track = container.querySelector('.multi-video-scroll-track');
        expect(track).toBeTruthy();
        expect(screen.queryByRole('button', { name: 'Expand Video' })).toBeNull();

        const videoEls = Array.from(container.querySelectorAll('.multi-video-scroll-track video'));
        expect(videoEls).toHaveLength(4);
        videoEls.forEach((vEl, idx) => {
            expect(vEl.getAttribute('src')).toBe(`/resources/videos/external-stream?url=vid${idx + 1}&sig=s${idx + 1}`);
            expect(vEl.getAttribute('referrerpolicy') || vEl.referrerPolicy).toBe('no-referrer');
            expect(vEl.getAttribute('poster')).toBe(`https://lh3.googleusercontent.com/vid${idx + 1}=s1600-no`);
        });

        const posterImgs = Array.from(container.querySelectorAll('.multi-video-scroll-track img'));
        expect(posterImgs).toHaveLength(4);
        posterImgs.forEach((imgEl, idx) => {
            expect(imgEl.getAttribute('src')).toBe(`https://lh3.googleusercontent.com/vid${idx + 1}=s1600-no`);
            expect(imgEl.getAttribute('referrerpolicy') || imgEl.referrerPolicy).toBe('no-referrer');
        });

        // Clicking "Play in full video mode" opens VideoModal with signed proxy src
        const fullscreenBtns = screen.getAllByRole('button', { name: 'Play in full video mode' });
        expect(fullscreenBtns).toHaveLength(4);
        fireEvent.click(fullscreenBtns[0]);
        const modal = screen.getByRole('dialog', { name: 'Video player' });
        const modalVideo = modal.querySelector('video');
        expect(modalVideo.getAttribute('src')).toBe('/resources/videos/external-stream?url=vid1&sig=s1');
    });

    it('Suppresses "new family memory shared" banner for own or already-inserted new-post broadcasts', async () => {
        globalThis.fetch = vi.fn(() =>
            Promise.resolve({
                ok: true,
                status: 200,
                headers: new Headers({ 'content-type': 'application/json' }),
                json: async () => ({ status: 'success', tags: [] }),
            })
        );

        render(
            <FeedShell
                bootstrap={{
                    ...baseBootstrap,
                    initialPosts: [samplePost],
                    initialHasMore: false,
                }}
            />
        );

        await waitFor(() => {
            expect(typeof mockChannelBindings['new-post']).toBe('function');
        });

        // Broadcast for already-present post or own post should NOT trigger pending banner
        mockChannelBindings['new-post']([
            {
                post_no: '101',
                id: 'u_1',
                fullName: 'Tunde Olaogun',
                postMessage: 'Duplicate echo',
            },
            {
                post_no: '955',
                id: 'u_1',
                fullName: 'Tunde Olaogun',
                postMessage: 'Own broadcast post',
            },
        ]);

        await waitFor(() => {
            expect(screen.getByText('Own broadcast post')).toBeTruthy();
        });
        expect(screen.queryByText(/new family memory shared/i)).toBeNull();
    });

    it('renders Facebook Reel link preview with decoded CDN URL, play badge, no-referrer, and collapses media box cleanly on image error', async () => {
        const fbReelPost = {
            post_no: '988',
            id: 'u_1',
            fullName: 'Wally Olaogun',
            postMessage: 'https://www.facebook.com/share/v/1FRX2SDq6t/',
            post_likes: 0,
            comments: [],
            images: [],
            videos: [],
            link_preview: {
                url: 'https://www.facebook.com/reel/1659146825685560/',
                original_url: 'https://www.facebook.com/share/v/1FRX2SDq6t/',
                title: '325K views | Reel by AI Savvy',
                description: 'Watch this reel on Facebook',
                image: 'https://scontent-man2-1.xx.fbcdn.net/v/t15.5256-10/840698712.jpg?stp=cmp610&amp;_nc_cat=106&amp;oh=00_AQ&amp;oe=6B078276',
                site_name: 'Facebook',
                domain: 'facebook.com',
                is_social_video: true,
                cta_label: 'Watch on Facebook →',
            },
        };

        globalThis.fetch = vi.fn(() =>
            Promise.resolve({
                ok: true,
                status: 200,
                headers: new Headers({ 'content-type': 'application/json' }),
                json: async () => ({ status: 'success', tags: [] }),
            })
        );

        const { container } = render(
            <FeedShell
                bootstrap={{
                    ...baseBootstrap,
                    initialPosts: [fbReelPost],
                    initialHasMore: false,
                }}
            />
        );

        const previewImg = container.querySelector('.link-preview-card__media img');
        expect(previewImg).toBeTruthy();
        expect(previewImg.getAttribute('src')).toBe(
            'https://scontent-man2-1.xx.fbcdn.net/v/t15.5256-10/840698712.jpg?stp=cmp610&_nc_cat=106&oh=00_AQ&oe=6B078276'
        );
        expect(previewImg.getAttribute('referrerpolicy') || previewImg.referrerPolicy).toBe('no-referrer');
        expect(container.querySelector('.link-preview-card__play-badge')).toBeTruthy();
        expect(screen.getByText('Watch on Facebook →')).toBeTruthy();

        // Raw URLs must NOT be displayed in the post caption or card footer when Rich Link Preview is rendered
        expect(container.querySelector('.post-text-body')).toBeNull();
        expect(screen.queryByText('https://www.facebook.com/share/v/1FRX2SDq6t/')).toBeNull();
        expect(screen.queryByText('https://www.facebook.com/reel/1659146825685560/')).toBeNull();

        // If CDN image ever fails/expires, the entire 16:9 media container collapses and reveals the Facebook icon tile
        fireEvent.error(previewImg);
        await waitFor(() => {
            expect(container.querySelector('.link-preview-card__media')).toBeNull();
            expect(container.querySelector('.link-preview-card__icon-tile .bi-facebook')).toBeTruthy();
        });
    });

    it('hides redundant BBC raw URL from post caption and card footer when Rich Link Preview card is rendered', async () => {
        const bbcPost = {
            post_no: '989',
            id: 'u_1',
            fullName: 'Wally Olaogun',
            postMessage: 'https://www.bbc.co.uk/news/articles/c6jreyqgw4e9o',
            post_likes: 0,
            comments: [],
            images: [],
            videos: [],
            link_preview: {
                url: 'https://www.bbc.co.uk/news/articles/c6jreyqgw4e9o',
                original_url: 'https://www.bbc.co.uk/news/articles/c6jreyqgw4e9o',
                title: 'Andy Burnham: UK to explore even closer ties with EU after agreeing deal',
                description: 'The UK-EU deal agreed on Friday covers British food exports, emissions trading and a youth experience scheme.',
                image: 'https://ichef.bbci.co.uk/news/1024/branded_news/sample.jpg',
                site_name: 'BBC News',
                domain: 'bbc.co.uk',
                is_social_video: false,
                cta_label: 'Read Article →',
            },
        };

        globalThis.fetch = vi.fn(() =>
            Promise.resolve({
                ok: true,
                status: 200,
                headers: new Headers({ 'content-type': 'application/json' }),
                json: async () => ({ status: 'success', tags: [] }),
            })
        );

        const { container } = render(
            <FeedShell
                bootstrap={{
                    ...baseBootstrap,
                    initialPosts: [bbcPost],
                    initialHasMore: false,
                }}
            />
        );

        expect(
            screen.getByText('Andy Burnham: UK to explore even closer ties with EU after agreeing deal')
        ).toBeTruthy();
        expect(container.querySelector('.post-text-body')).toBeNull();
        expect(screen.queryByText('https://www.bbc.co.uk/news/articles/c6jreyqgw4e9o')).toBeNull();
    });

    it('renders single portrait/poster photo uncropped with contain and falls back cleanly on image error', async () => {
        const posterPost = {
            post_no: '990',
            id: 'u_1',
            fullName: 'Wally Olaogun',
            postMessage: '',
            post_likes: 0,
            comments: [],
            images: ['/resources/images/posts/idecide-poster.jpg'],
            videos: [],
        };

        globalThis.fetch = vi.fn(() =>
            Promise.resolve({
                ok: true,
                status: 200,
                headers: new Headers({ 'content-type': 'application/json' }),
                json: async () => ({ status: 'success', tags: [] }),
            })
        );

        const { container } = render(
            <FeedShell
                bootstrap={{
                    ...baseBootstrap,
                    initialPosts: [posterPost],
                    initialHasMore: false,
                }}
            />
        );

        const stage = container.querySelector('.post-media-stage');
        const img = container.querySelector('img.post-media-first-img');
        expect(stage).toBeTruthy();
        expect(stage.style.aspectRatio).toBe('auto');
        expect(img).toBeTruthy();
        expect(img.style.objectFit).toBe('contain');
        expect(img.getAttribute('referrerpolicy') || img.referrerPolicy).toBe('no-referrer');

        fireEvent.error(img);
        await waitFor(() => {
            expect(container.querySelector('.post-media-stage__fallback')).toBeTruthy();
            expect(screen.getByText('Photo unavailable')).toBeTruthy();
        });
    });

    it('renders 3D Living Family Emojis (:id_3d: & ar_emoji) and Me-Stickers (:me_reaction@uid:) interactively without raw token text', async () => {
        const tapLive = vi.fn();
        const tapSticker = vi.fn();
        window.AtgEmojiEngine = { tapLive, tapSticker };
        window.ATG_LIVING_EMOJIS = {
            catalog: [{ id: 'family_roots_3d', title: 'Family Roots' }],
            toneable: [],
            tones: [],
        };

        const emojiPost = {
            post_no: '991',
            id: 'u_1',
            fullName: 'Wally Olaogun',
            postMessage: ':family_roots_3d: :me_love@u_1:',
            ar_emoji: 'family_roots_3d',
            post_likes: 0,
            comments: [
                {
                    comment_no: 'c_991',
                    post_no: '991',
                    id: 'u_2',
                    fullName: 'Bisi Olaogun',
                    comment: 'Sending love :family_roots_3d:',
                    reactions: [{ label: 'love', count: 2 }],
                    user_reaction: null,
                    date_created: '2026-11-22T12:00:00Z',
                },
            ],
            images: [],
            videos: [],
        };

        globalThis.fetch = vi.fn(() =>
            Promise.resolve({
                ok: true,
                status: 200,
                headers: new Headers({ 'content-type': 'application/json' }),
                json: async () => ({ status: 'success', tags: [] }),
            })
        );

        const { container } = render(
            <FeedShell
                bootstrap={{
                    ...baseBootstrap,
                    initialPosts: [emojiPost],
                    initialHasMore: false,
                }}
            />
        );

        const liveEmoji = container.querySelector('.atg-live-emoji--jumbo');
        const meSticker = container.querySelector('[data-testid="me-sticker"].atg-me-sticker--jumbo');
        expect(liveEmoji).toBeTruthy();
        expect(meSticker).toBeTruthy();
        expect(screen.queryByText(/:family_roots_3d:/)).toBeNull();

        fireEvent.click(liveEmoji);
        fireEvent.click(meSticker);
        expect(tapLive).toHaveBeenCalledWith(liveEmoji, 'family_roots_3d');
        expect(tapSticker).toHaveBeenCalledWith(meSticker, 'love');

        delete window.AtgEmojiEngine;
        delete window.ATG_LIVING_EMOJIS;
    });

    it('keeps PostCard reaction dock open across hover-gap grace window and renders 34px comment reactions + summary pill', async () => {
        const postWithComment = {
            post_no: '992',
            id: 'u_1',
            fullName: 'Wally Olaogun',
            postMessage: 'Sunday dinner',
            post_likes: 1,
            user_reaction: null,
            comments: [
                {
                    comment_no: 'c_992',
                    post_no: '992',
                    id: 'u_2',
                    fullName: 'Bisi Olaogun',
                    comment: 'Delicious!',
                    reactions: [{ label: 'love', count: 3 }],
                    user_reaction: null,
                    date_created: '2026-11-22T12:00:00Z',
                },
            ],
            images: [],
            videos: [],
        };

        globalThis.fetch = vi.fn(() =>
            Promise.resolve({
                ok: true,
                status: 200,
                headers: new Headers({ 'content-type': 'application/json' }),
                json: async () => ({ status: 'success', tags: [] }),
            })
        );

        const { container } = render(
            <FeedShell
                bootstrap={{
                    ...baseBootstrap,
                    initialPosts: [postWithComment],
                    initialHasMore: false,
                }}
            />
        );

        // Hover post reaction button, trigger mouseLeave (simulating 8px gap) and verify dock stays open during 180ms grace
        const reactTrigger = screen.getByRole('button', { name: 'React to this post' });
        fireEvent.mouseEnter(reactTrigger);
        fireEvent.mouseLeave(reactTrigger.parentElement);
        const lovePostBtn = screen.getByRole('button', { name: 'Love' });
        expect(lovePostBtn).toBeTruthy();
        fireEvent.click(lovePostBtn);
        expect(globalThis.fetch).toHaveBeenCalledWith(
            expect.stringContaining('/profileCard/postLikes?postNo=992&reaction=love'),
            expect.any(Object)
        );

        // Comment reaction summary pill shows 20px SVG + count 3
        const summaryPill = container.querySelector('[data-testid="comment-reaction-summary"]');
        expect(summaryPill).toBeTruthy();
        expect(summaryPill.textContent).toContain('3');

        // Open comment reaction dock -> verify 34px SVG + optimistic count bump to 4
        const commentReactBtn = screen.getByRole('button', { name: 'React' });
        fireEvent.click(commentReactBtn);
        const commentDock = container.querySelector('.comment-reaction-float-bar');
        expect(commentDock).toBeTruthy();
        const svg34 = commentDock.querySelector('svg[width="34"][height="34"]');
        expect(svg34).toBeTruthy();
        const commentLoveBtn = commentDock.querySelector('button[aria-label="Love"]');
        fireEvent.click(commentLoveBtn);

        await waitFor(() => {
            expect(container.querySelector('[data-testid="comment-reaction-summary"]').textContent).toContain('4');
        });
    });

    it('restores Family GIF picker and multi-tab Emoji picker in CommentThread and submits gif_url', async () => {
        const postForGif = {
            post_no: '993',
            id: 'u_1',
            fullName: 'Wally Olaogun',
            postMessage: 'Family reunion!',
            post_likes: 0,
            comments: [],
            images: [],
            videos: [],
        };

        globalThis.fetch = vi.fn((url) => {
            if (String(url).includes('/postCommentProfile')) {
                return Promise.resolve({
                    ok: true,
                    status: 200,
                    headers: new Headers({ 'content-type': 'application/json' }),
                    json: async () => ({
                        status: 'success',
                        message: {
                            comment_no: 'c_new_993',
                            post_no: '993',
                            comment: 'So excited!',
                            gif_url: '/images/brand/gifs/atg_heritage_tree_bloom.gif',
                        },
                    }),
                });
            }
            return Promise.resolve({
                ok: true,
                status: 200,
                headers: new Headers({ 'content-type': 'application/json' }),
                json: async () => ({ status: 'success', tags: [] }),
            });
        });

        render(
            <FeedShell
                bootstrap={{
                    ...baseBootstrap,
                    initialPosts: [postForGif],
                    initialHasMore: false,
                }}
            />
        );

        // Expand comments
        fireEvent.click(screen.getByRole('button', { name: 'Comment on this post' }));

        // Open GIF picker (lazy-loaded)
        const gifBtn = screen.getByTestId('comment-gif-btn');
        fireEvent.click(gifBtn);

        await waitFor(() => {
            expect(screen.getByTestId('emoji-gif-picker')).toBeTruthy();
            expect(screen.getByTestId('comment-gif-picker')).toBeTruthy();
        });

        // Verify Standard, Living, Me, and GIFs tabs exist
        expect(screen.getByTestId('comment-emoji-tab-standard')).toBeTruthy();
        expect(screen.getByTestId('comment-emoji-tab-living')).toBeTruthy();
        expect(screen.getByTestId('comment-emoji-tab-me')).toBeTruthy();
        expect(screen.getByTestId('comment-emoji-tab-gif')).toBeTruthy();

        // Pick first Family GIF
        const firstGifBtn = screen.getByRole('button', { name: /Select GIF: Heritage Tree/i });
        fireEvent.click(firstGifBtn);

        expect(screen.getByTestId('comment-gif-preview')).toBeTruthy();

        // Submit comment with GIF
        const input = screen.getByLabelText('Write a comment');
        fireEvent.input(input, { target: { value: 'So excited!' } });
        fireEvent.click(screen.getByRole('button', { name: 'Send' }));

        await waitFor(() => {
            expect(globalThis.fetch).toHaveBeenCalledWith(
                '/postCommentProfile',
                expect.objectContaining({ method: 'POST' })
            );
            expect(screen.getByTestId('comment-gif')).toBeTruthy();
        });
    });

    it('renders post and comment view counts and opens ViewersModal when author clicks views', async () => {
        const postWithViews = {
            post_no: '994',
            id: 'u_1',
            fullName: 'Wally Olaogun',
            postMessage: 'Family milestone!',
            post_likes: 0,
            views_count: 9,
            comments: [
                {
                    comment_no: 'c_994',
                    post_no: '994',
                    id: 'u_1',
                    fullName: 'Wally Olaogun',
                    comment: 'Thanks everyone!',
                    views_count: 7,
                    reactions: [],
                    date_created: '2026-11-22T12:00:00Z',
                },
            ],
            images: [],
            videos: [],
        };

        globalThis.fetch = vi.fn((url) => {
            if (String(url).includes('/api/posts/994/viewers')) {
                return Promise.resolve({
                    ok: true,
                    status: 200,
                    headers: new Headers({ 'content-type': 'application/json' }),
                    json: async () => ({
                        status: 'success',
                        views_count: 9,
                        viewers: [{ id: 'u_2', firstName: 'Bisi', lastName: 'Olaogun' }],
                    }),
                });
            }
            return Promise.resolve({
                ok: true,
                status: 200,
                headers: new Headers({ 'content-type': 'application/json' }),
                json: async () => ({ status: 'success', tags: [] }),
            });
        });

        const { container } = render(
            <FeedShell
                bootstrap={{
                    ...baseBootstrap,
                    initialPosts: [postWithViews],
                    initialHasMore: false,
                }}
            />
        );

        const postViewsBtn = container.querySelector('button.post-views-count');
        expect(postViewsBtn).toBeTruthy();
        expect(postViewsBtn.textContent).toContain('9 views');

        const commentViewsBtn = container.querySelector('button.comment-views-count');
        expect(commentViewsBtn).toBeTruthy();
        expect(commentViewsBtn.textContent).toContain('7 views');

        fireEvent.click(postViewsBtn);
        await waitFor(() => {
            expect(screen.getByTestId('viewers-modal')).toBeTruthy();
            expect(screen.getByText('Bisi Olaogun')).toBeTruthy();
        });
    });

    it('renders :family_crest_3d: as Generational Crest living emoji alongside Facebook link_preview without literal shortcode text', async () => {
        window.ATG_LIVING_EMOJIS = {
            catalog: [{ id: 'family_crest_3d', title: 'Generational Crest' }],
            toneable: [],
            tones: [],
        };

        const fbEmojiPost = {
            post_no: '995',
            id: 'u_1',
            fullName: 'Wally Olaogun',
            postFamCode: 'OLA001',
            target_scope: 'birth',
            postMessage: ':family_crest_3d:\nhttps://www.facebook.com/share/v/123456/',
            link_preview: {
                url: 'https://www.facebook.com/share/v/123456/',
                original_url: 'https://www.facebook.com/share/v/123456/',
                domain: 'facebook.com',
                site_name: 'Facebook',
                title: 'Damiloju Celebration',
                image: 'https://example.com/fb-thumb.jpg',
                is_social_video: true,
                frame_blocked: true,
            },
            post_likes: 0,
            comments: [],
            images: [],
            videos: [],
        };

        globalThis.fetch = vi.fn(() =>
            Promise.resolve({
                ok: true,
                status: 200,
                headers: new Headers({ 'content-type': 'application/json' }),
                json: async () => ({ status: 'success', tags: [] }),
            })
        );

        const { container } = render(
            <FeedShell
                bootstrap={{
                    ...baseBootstrap,
                    initialPosts: [fbEmojiPost],
                    initialHasMore: false,
                }}
            />
        );

        try {
            const img = container.querySelector('.atg-live-emoji--jumbo img.atg-3d-emoji');
            expect(img).toBeTruthy();
            expect(img.getAttribute('src')).toBe('/public/img/emoji/family_crest_3d.svg?v=20261107a');
            expect(img.getAttribute('alt')).toBe('Generational Crest');
            expect(screen.queryByText(/:family_crest_3d:/)).toBeNull();
            expect(container.querySelector('.link-preview-card')).toBeTruthy();
        } finally {
            delete window.ATG_LIVING_EMOJIS;
        }
    });

    it('renders attached ar_emoji on link-preview and photo posts even when caption text is empty', async () => {
        window.ATG_LIVING_EMOJIS = {
            catalog: [{ id: 'family_crest_3d', title: 'Generational Crest' }],
            toneable: [],
            tones: [],
        };

        const linkOnlyArPost = {
            post_no: '996',
            id: 'u_1',
            fullName: 'Wally Olaogun',
            postMessage: 'https://www.facebook.com/share/v/999/',
            ar_emoji: 'family_crest_3d',
            link_preview: {
                url: 'https://www.facebook.com/share/v/999/',
                domain: 'facebook.com',
                site_name: 'Facebook',
                title: 'Family Reunion Clip',
                image: 'https://example.com/fb-999.jpg',
                is_social_video: true,
            },
            post_likes: 0,
            comments: [],
            images: [],
            videos: [],
        };

        const photoOnlyArPost = {
            post_no: '997',
            id: 'u_1',
            fullName: 'Wally Olaogun',
            postMessage: '',
            ar_emoji: 'family_crest_3d',
            post_likes: 0,
            comments: [],
            images: ['/img/sample1.jpg'],
            videos: [],
        };

        globalThis.fetch = vi.fn(() =>
            Promise.resolve({
                ok: true,
                status: 200,
                headers: new Headers({ 'content-type': 'application/json' }),
                json: async () => ({ status: 'success', tags: [] }),
            })
        );

        const { container } = render(
            <FeedShell
                bootstrap={{
                    ...baseBootstrap,
                    initialPosts: [linkOnlyArPost, photoOnlyArPost],
                    initialHasMore: false,
                }}
            />
        );

        try {
            const imgs = container.querySelectorAll('.atg-live-emoji--jumbo img.atg-3d-emoji[alt="Generational Crest"]');
            expect(imgs.length).toBe(2);
            expect(screen.queryByText(/:family_crest_3d:/)).toBeNull();
        } finally {
            delete window.ATG_LIVING_EMOJIS;
        }
    });

    it('renders :family_crest_3d: as an image when window.ATG_LIVING_EMOJIS.catalog is empty', async () => {
        window.ATG_LIVING_EMOJIS = {
            catalog: [],
            toneable: [],
            tones: [],
        };

        const emptyCatalogPost = {
            post_no: '998',
            id: 'u_1',
            fullName: 'Wally Olaogun',
            postMessage: 'Heritage pride :family_crest_3d:',
            post_likes: 0,
            comments: [],
            images: [],
            videos: [],
        };

        globalThis.fetch = vi.fn(() =>
            Promise.resolve({
                ok: true,
                status: 200,
                headers: new Headers({ 'content-type': 'application/json' }),
                json: async () => ({ status: 'success', tags: [] }),
            })
        );

        const { container } = render(
            <FeedShell
                bootstrap={{
                    ...baseBootstrap,
                    initialPosts: [emptyCatalogPost],
                    initialHasMore: false,
                }}
            />
        );

        try {
            const img = container.querySelector('.atg-live-emoji img.atg-3d-emoji');
            expect(img).toBeTruthy();
            expect(img.getAttribute('src')).toBe('/public/img/emoji/family_crest_3d.svg?v=20261107a');
            expect(img.getAttribute('alt')).toBe('Family Crest');
            expect(screen.queryByText(/:family_crest_3d:/)).toBeNull();
        } finally {
            delete window.ATG_LIVING_EMOJIS;
        }
    });

    it('preserves visual 3D emoji spans in #postMessageEditor via syncTextareaToEditor when editing a post', async () => {
        const editableEmojiPost = {
            post_no: '999',
            id: 'u_1',
            fullName: 'Wally Olaogun',
            postMessage: 'Checking in :family_crest_3d:',
            post_likes: 0,
            comments: [],
            images: [],
            videos: [],
        };

        const editPostNo = document.createElement('input');
        editPostNo.id = 'editPostNo';
        const textarea = document.createElement('textarea');
        textarea.id = 'postMessage';
        const editor = document.createElement('div');
        editor.id = 'postMessageEditor';
        const modalEl = document.createElement('div');
        modalEl.id = 'postModal';
        const notice = document.createElement('div');
        notice.id = 'editPostNotice';
        notice.classList.add('d-none');
        document.body.append(editPostNo, textarea, editor, modalEl, notice);

        const showSpy = vi.fn();
        window.bootstrap = {
            Modal: {
                getOrCreateInstance: vi.fn(() => ({ show: showSpy })),
            },
        };
        window.syncTextareaToEditor = vi.fn(() => {
            editor.innerHTML = 'Checking in <span class="atg-emoji-container atg-live-emoji" data-emoji-id="family_crest_3d"><img src="/img/emoji/family_crest_3d.svg"></span>';
        });

        globalThis.fetch = vi.fn(() =>
            Promise.resolve({
                ok: true,
                status: 200,
                headers: new Headers({ 'content-type': 'application/json' }),
                json: async () => ({ status: 'success', tags: [] }),
            })
        );

        render(
            <FeedShell
                bootstrap={{
                    ...baseBootstrap,
                    initialPosts: [editableEmojiPost],
                    initialHasMore: false,
                }}
            />
        );

        try {
            fireEvent.click(screen.getByRole('button', { name: 'Post options' }));
            fireEvent.click(screen.getByRole('button', { name: 'Edit Post' }));

            expect(window.syncTextareaToEditor).toHaveBeenCalledWith(true);
            expect(textarea.value).toBe('Checking in :family_crest_3d:');
            expect(editor.querySelector('[data-emoji-id="family_crest_3d"]')).toBeTruthy();
            expect(editor.textContent).not.toContain(':family_crest_3d:');
            expect(showSpy).toHaveBeenCalled();
        } finally {
            editPostNo.remove();
            textarea.remove();
            editor.remove();
            modalEl.remove();
            notice.remove();
            delete window.bootstrap;
            delete window.syncTextareaToEditor;
        }
    });

    it('renders cohesive CommentThread composer tools, themeable comment bubbles, active send state, and file attachment chip', async () => {
        const postWithExistingComment = {
            post_no: '1000',
            id: 'u_1',
            fullName: 'Wally Olaogun',
            postMessage: 'Typical day creating value for the world',
            post_likes: 0,
            comments: [
                {
                    comment_no: 'c_1000',
                    post_no: '1000',
                    id: 'u_2',
                    firstName: 'Kemi',
                    lastName: 'Olaogun',
                    fullName: 'Kemi Olaogun',
                    comment: 'Love this!',
                    reactions: [],
                    date_created: '2026-11-22T12:00:00Z',
                },
            ],
            images: [],
            videos: [],
        };

        globalThis.fetch = vi.fn(() =>
            Promise.resolve({
                ok: true,
                status: 200,
                headers: new Headers({ 'content-type': 'application/json' }),
                json: async () => ({ status: 'success', tags: [] }),
            })
        );

        const { container } = render(
            <FeedShell
                bootstrap={{
                    ...baseBootstrap,
                    initialPosts: [postWithExistingComment],
                    initialHasMore: false,
                }}
            />
        );

        // Themeable comment bubble with no hardcoded inline light background
        const bubble = container.querySelector('.comment-bubble-box');
        expect(bubble).toBeTruthy();
        expect(bubble.style.backgroundColor).toBe('');
        const authorEl = bubble.querySelector('.comment-bubble-author');
        expect(authorEl).toBeTruthy();
        expect(authorEl.textContent).toBe('Kemi Olaogun');

        // Expand composer
        fireEvent.click(screen.getByRole('button', { name: 'Comment on this post' }));

        const toolsWrap = container.querySelector('.comment-composer__tools');
        expect(toolsWrap).toBeTruthy();
        const emojiBtn = toolsWrap.querySelector('button[aria-label="Add emoji"]');
        const gifBtn = toolsWrap.querySelector('[data-testid="comment-gif-btn"]');
        expect(emojiBtn).toBeTruthy();
        expect(gifBtn).toBeTruthy();
        expect(gifBtn.previousElementSibling).toBe(emojiBtn);

        const sendBtn = screen.getByRole('button', { name: 'Send' });
        expect(sendBtn.disabled).toBe(true);
        expect(sendBtn.classList.contains('is-active')).toBe(false);

        const input = screen.getByLabelText('Write a comment');
        fireEvent.input(input, { target: { value: 'So inspiring!' } });
        expect(sendBtn.disabled).toBe(false);
        expect(sendBtn.classList.contains('is-active')).toBe(true);

        // Attach a file inside .comment-composer__field and remove it cleanly
        fireEvent.input(input, { target: { value: '' } });
        expect(sendBtn.disabled).toBe(true);

        const fileInput = toolsWrap.querySelector('input[type="file"]');
        const mockFile = new File(['hello'], 'family-note.pdf', { type: 'application/pdf' });
        fireEvent.change(fileInput, { target: { files: [mockFile] } });

        const previewChip = container.querySelector('.comment-composer__field [data-testid="comment-file-preview"]');
        expect(previewChip).toBeTruthy();
        expect(previewChip.textContent).toContain('family-note.pdf');
        expect(sendBtn.disabled).toBe(false);
        expect(sendBtn.classList.contains('is-active')).toBe(true);

        fireEvent.click(screen.getByRole('button', { name: 'Remove attachment' }));
        expect(container.querySelector('[data-testid="comment-file-preview"]')).toBeNull();
        expect(sendBtn.disabled).toBe(true);
    });

    it('BRATS Regression Guard: supports _x_dataStack programmatic bridge, In-Feed 3D Stage Converter, bare image URL resolution, and 2-reply thread collapsing', async () => {
        globalThis.fetch = vi.fn(() =>
            Promise.resolve({
                ok: true,
                status: 200,
                headers: { get: () => 'application/json' },
                json: () => Promise.resolve({ status: 'success', message: { post_style: 'aurora_prism' } }),
            })
        );

        const initialPost = {
            post_no: '3001',
            id: 'u_owner',
            firstName: 'Wally',
            lastName: 'Olaogun',
            postMessage: 'Family heritage text post',
            images: [],
            videos: [],
            comments: [
                { comment_no: 'c1', post_no: '3001', id: 'u_2', firstName: 'Ayo', comment: 'Root comment', parent_comment_no: null },
                { comment_no: 'c2', post_no: '3001', id: 'u_3', firstName: 'Bisi', comment: 'Reply 1', parent_comment_no: 'c1' },
                { comment_no: 'c3', post_no: '3001', id: 'u_4', firstName: 'Tunde', comment: 'Reply 2', parent_comment_no: 'c1' },
                { comment_no: 'c4', post_no: '3001', id: 'u_5', firstName: 'Kemi', comment: 'Reply 3', parent_comment_no: 'c1' },
            ],
        };

        const { container } = render(
            <FeedShell
                bootstrap={{
                    userId: 'u_owner',
                    firstName: 'Wally',
                    lastName: 'Olaogun',
                    csrfToken: 'csrf_brats',
                    initialPosts: [initialPost],
                    initialHasMore: false,
                }}
            />
        );

        // 1. Own text post renders In-Feed 3D Stage Converter
        const stageConverter = container.querySelector('#post3001 [data-testid="infeed-stage-converter"]');
        expect(stageConverter).toBeTruthy();

        // 2. Thread with 3 replies collapses to 2 visible replies + "View 1 more reply" toggle, then expands and collapses
        expect(container.querySelectorAll('#post3001 [data-reply="true"]').length).toBe(2);
        const toggleBtn = container.querySelector('#post3001 [data-testid="thread-collapse-toggle"]');
        expect(toggleBtn).toBeTruthy();
        expect(toggleBtn.textContent).toMatch(/View 1 more reply/);
        fireEvent.click(toggleBtn);
        expect(container.querySelectorAll('#post3001 [data-reply="true"]').length).toBe(3);
        expect(toggleBtn.textContent).toBe('Collapse replies');

        // 3. Programmatic _x_dataStack bridge supports posts.unshift (link-only + bare image filename) & openLightbox
        const feedRootEl = container.querySelector('.feed-posts-container');
        expect(feedRootEl._x_dataStack).toBeTruthy();
        const bridge = feedRootEl._x_dataStack[0];

        bridge.posts.unshift({
            post_no: 9999992,
            id: 'u_owner',
            is_own_post: true,
            fullName: 'E2E Link Tester',
            postMessage: 'https://example.com/family-reunion',
            displayMessage: 'https://example.com/family-reunion',
            images: [],
            videos: [],
            link_preview: {
                url: 'https://example.com/family-reunion',
                original_url: 'https://example.com/family-reunion',
                domain: 'example.com',
                title: 'Annual Family Reunion 2026',
            },
        });

        await waitFor(() => {
            expect(container.querySelector('#post9999992 .link-preview-card')).toBeTruthy();
        });
        // Link-only post suppresses 3D stage converter
        expect(container.querySelector('#post9999992 [data-testid="infeed-stage-converter"]')).toBeNull();

        bridge.posts.unshift({
            post_no: 9999991,
            id: 'u_owner',
            fullName: 'E2E Image Tester',
            postMessage: 'Testing 404 image path',
            images: ['missing-e2e-404-test.jpg'],
            videos: [],
        });

        await waitFor(() => {
            const img = container.querySelector('#post9999991 img.post-media-first-img');
            expect(img).toBeTruthy();
            expect(img.getAttribute('src')).toContain('/resources/images/post/missing-e2e-404-test.jpg');
        });
    });
});
