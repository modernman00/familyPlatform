import axios from "axios";
import Swal from "sweetalert2";
import { getCsrfToken } from "../../global";

export const feedStageAndPolls = {
        extractPostStyle(rawText, explicitStyle = null, postObj = null) {
            const allowed = new Set(this.textStageCatalog.map(s => s.slug));
            if (explicitStyle && typeof explicitStyle === 'string') {
                const clean = explicitStyle.trim().toLowerCase();
                if (clean === 'none') return null;
                if (allowed.has(clean)) return clean;
            }
            if (!rawText || typeof rawText !== 'string') return null;
            const m = rawText.match(/\[style:([a-z0-9_]+)\]/i);
            if (m && m[1]) {
                const candidate = m[1].toLowerCase();
                if (candidate === 'none') return null;
                if (allowed.has(candidate)) return candidate;
            }

            // Automatic 3D Stage inference for platform-generated milestone & heritage posts
            // (so both existing historical posts and newly created platform broadcasts render in 3D stages)
            const lower = rawText.toLowerCase();
            if (lower.includes('joined our family circle') || lower.includes('warm welcome to the family')) {
                return 'grand_announcement';
            }
            if (lower.includes('officially turned 18') || lower.includes('happy 18th birthday') || lower.includes('milestone celebration:')) {
                return 'aurora_prism';
            }
            if (lower.includes('family chronicle') || lower.includes('/familyjournal/')) {
                return 'heirloom_velvet';
            }
            if (lower.includes('[memory flashback') || lower.includes('on this day flashback')) {
                return 'heirloom_velvet';
            }
            if (lower.includes('vote to change our family code') || lower.includes('wants to change our family code')) {
                return 'alert_pulse';
            }
            if (lower.includes('welcome to your page') || postObj?.is_official || postObj?.id === 'FAMILY_PLATFORM_OFFICIAL') {
                return 'dynasty_holo';
            }
            if (lower.includes('family roundtable') || lower.includes('roundtable accord')) {
                return 'cyber_glass';
            }
            return null;
        },

        stripStyleToken(text) {
            if (!text || typeof text !== 'string') return '';
            return text.replace(/\[style:[a-z0-9_]+\]\s*/gi, '').trim();
        },

        getPostStageMeta(post) {
            const slug = post?.post_style || this.extractPostStyle(post?.postMessage, post?.post_style, post);
            if (!slug) return null;
            const base = this.textStageCatalog.find(s => s.slug === slug);
            if (!base) return null;

            const raw = String(post?.postMessage || '').toLowerCase();
            let customBadge = base.badge;
            if (raw.includes('joined our family circle') || raw.includes('warm welcome to the family')) {
                customBadge = '🎉 FAMILY WELCOME MILESTONE';
            } else if (raw.includes('officially turned 18') || raw.includes('happy 18th birthday')) {
                customBadge = '🎂 18TH BIRTHDAY GRADUATION';
            } else if (raw.includes('family chronicle') || raw.includes('/familyjournal/')) {
                customBadge = '📖 FAMILY CHRONICLE DECREE';
            } else if (raw.includes('[memory flashback')) {
                customBadge = '⏳ ON THIS DAY FLASHBACK';
            } else if (raw.includes('change our family code')) {
                customBadge = '🗳️ FAMILY CODE GOVERNANCE';
            } else if (raw.includes('welcome to your page')) {
                customBadge = '🏡 SANCTUARY WELCOME';
            } else if (post?.is_official || post?.id === 'FAMILY_PLATFORM_OFFICIAL') {
                customBadge = '✨ OFFICIAL PLATFORM BROADCAST';
            }
            return { ...base, badge: customBadge };
        },

        getChronicleLink(post) {
            const raw = String(post?.postMessage || '');
            const m = raw.match(/(?:https?:\/\/[^\s<>"']+)?(\/familyJournal\/\d+)/i);
            return m && m[1] ? m[1] : null;
        },

        hasPollArena(post) {
            return Boolean(post?.poll && Array.isArray(post.poll.options) && post.poll.options.length > 0);
        },

        getPollArenaMeta(post) {
            const raw = String(post?.postMessage || '');
            const lower = raw.toLowerCase();
            if (lower.includes('weekly family trivia')) {
                return {
                    theme: 'trivia_gold',
                    badge: '🏆 WEEKLY FAMILY TRIVIA ARENA',
                    subtitle: 'Sunday Family Ritual • Cast your vote below & debate in the comments!',
                    icon: 'bi-trophy-fill',
                };
            }
            if (lower.includes('family game show')) {
                return {
                    theme: 'gameshow_neon',
                    badge: '🎮 FAMILY GAME SHOW ARENA',
                    subtitle: 'Test how well you know our family stories & see who gets top marks!',
                    icon: 'bi-controller',
                };
            }
            if (lower.includes('weekend outings')) {
                return {
                    theme: 'outings_emerald',
                    badge: '🗺️ FAMILY WEEKEND OUTINGS VOTE',
                    subtitle: 'Scouted live activities for our family • Tap your pick for this weekend!',
                    icon: 'bi-compass-fill',
                };
            }
            if (lower.includes('change our family code')) {
                return {
                    theme: 'alert_vote',
                    badge: '🗳️ FAMILY CODE GOVERNANCE VOTE',
                    subtitle: 'Cast your vote — the code updates automatically if Yes wins.',
                    icon: 'bi-shield-check',
                };
            }
            return {
                theme: 'cyber_poll',
                badge: '📊 LIVE 3D FAMILY POLL',
                subtitle: 'Tap an option below to cast your family vote',
                icon: 'bi-bar-chart-fill',
            };
        },

        getPollCleanIntro(post) {
            if (!post) return '';
            const rawCaption = this.getMediaCaption(post);
            if (!rawCaption) return '';
            const question = String(post?.poll?.question || '').trim();

            const lines = rawCaption
                .split(/\r?\n/)
                .map(l => l.trim())
                .filter(Boolean)
                .filter(line => {
                    if (question && (line === question || line.includes(question) || question.includes(line))) {
                        return false;
                    }
                    const l = line.toLowerCase();
                    if (l.includes('sunday family ritual: weekly family trivia')) return false;
                    if (l.includes('cast your vote below and debate in the comments')) return false;
                    if (l.includes('round 1 question is live in the poll below')) return false;
                    if (l.includes('we just launched our family game show')) return false;
                    if (l.includes('we just scouted live activities and museum events')) return false;
                    if (l.includes('voting closes friday evening')) return false;
                    return true;
                });

            return lines.join('\n').trim();
        },

        getPollOptionLetter(idx) {
            const letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
            return letters[idx] || String(idx + 1);
        },

        getLeadingPollOptionId(post) {
            const opts = post?.poll?.options;
            if (!Array.isArray(opts) || opts.length === 0) return null;
            const total = Number(post?.poll?.total_votes || 0);
            if (total <= 0) return null;
            let maxVotes = 0;
            let leaderId = null;
            opts.forEach(o => {
                const v = Number(o?.vote_count ?? o?.percentage ?? 0);
                if (v > maxVotes) {
                    maxVotes = v;
                    leaderId = o.option_id;
                }
            });
            return maxVotes > 0 ? leaderId : null;
        },

        hasFamilyTreeCall(post) {
            const text = String(post?.displayMessage || post?.postMessage || '').toLowerCase();
            return (
                text.includes('family tree')
                || text.includes('adding your branch')
                || text.includes('add your branch')
                || text.includes('organogram')
                || post?.post_style === 'dynasty_holo'
            );
        },

        toggleStagePicker(postNo) {
            if (!postNo) return;
            this.stagePickerOpen[postNo] = !this.stagePickerOpen[postNo];
        },

        async setPostStage(post, styleSlug, ev = null) {
            if (!post || !post.post_no || this.convertingStagePosts[post.post_no]) return;
            const prevStyle = post.post_style || null;
            const prevMsg = post.postMessage || '';
            const cleanSlug = (!styleSlug || styleSlug === 'none') ? null : String(styleSlug).toLowerCase();

            // Optimistic UI update
            post.post_style = cleanSlug;
            const stripped = this.stripStyleToken(prevMsg);
            post.postMessage = cleanSlug ? `[style:${cleanSlug}] ${stripped}` : stripped;
            post.displayMessage = this.getMediaCaption(post);
            this.convertingStagePosts[post.post_no] = true;

            if (typeof window.triggerHaptic === 'function') {
                window.triggerHaptic('impact');
            }

            try {
                const res = await axios.put(
                    `/post/${post.post_no}`,
                    { post_style: cleanSlug || 'none' },
                    this.csrfOptions
                );
                const payload = (res?.data?.message && typeof res.data.message === 'object')
                    ? res.data.message
                    : res?.data;
                if (payload?.post_style !== undefined) {
                    post.post_style = payload.post_style || null;
                }
                if (payload?.postMessage) {
                    post.postMessage = this.decodeHtml(payload.postMessage);
                    post.displayMessage = this.getMediaCaption(post);
                }
                const meta = this.getPostStageMeta(post);
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: meta ? `Converted to ${meta.label}` : 'Reverted to standard text post',
                    showConfirmButton: false,
                    timer: 2200
                });
            } catch (err) {
                console.error('Failed to convert post stage:', err);
                post.post_style = prevStyle;
                post.postMessage = prevMsg;
                post.displayMessage = this.getMediaCaption(post);
                Swal.fire({
                    icon: 'error',
                    title: 'Stage Conversion Failed',
                    text: err?.response?.data?.message || 'Unable to update post design right now.',
                    confirmButtonColor: '#a31621'
                });
            } finally {
                this.convertingStagePosts[post.post_no] = false;
            }
        },

        async cyclePostStage(post, ev = null) {
            if (!post || !post.post_no) return;
            const order = this.hasFamilyTreeCall(post)
                ? ['dynasty_holo', 'starlight_bokeh', 'golden_ember', 'sunset_horizon', 'emerald_canopy', 'ocean_biolum', 'rose_stardust', 'northern_sky', 'royal_parchment', 'xmas_snowfall', 'xmas_hearth', 'xmas_lights', 'xmas_winter_gold', 'solar_galaxy', 'cyber_glass', 'alert_pulse', 'grand_announcement', 'aurora_prism', 'heirloom_velvet']
                : ['starlight_bokeh', 'golden_ember', 'sunset_horizon', 'emerald_canopy', 'ocean_biolum', 'rose_stardust', 'northern_sky', 'royal_parchment', 'xmas_snowfall', 'xmas_hearth', 'xmas_lights', 'xmas_winter_gold', 'solar_galaxy', 'cyber_glass', 'alert_pulse', 'grand_announcement', 'dynasty_holo', 'aurora_prism', 'heirloom_velvet'];
            const current = post.post_style || null;
            const idx = current ? order.indexOf(current) : -1;
            const nextSlug = order[(idx + 1) % order.length];
            this.stagePickerOpen[post.post_no] = true;
            await this.setPostStage(post, nextSlug, ev);
        },
};
