import { describe, expect, it, vi, beforeEach, afterEach } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/preact';
import { CommentThread } from './CommentThread';

describe('Mobile PWA @mention Autocomplete & Sync Regression Suite', () => {
    let originalBootstrap;

    beforeEach(() => {
        originalBootstrap = window.__FP_FEED_BOOTSTRAP__;
        delete window.__FP_FEED_BOOTSTRAP__;
        delete window.__fpInternalTextareaSync;
        delete window.currentUserId;
        document.head.innerHTML = '<meta name="user-id" content="u_self">';
        document.body.innerHTML = '';
        vi.resetModules();
    });

    afterEach(() => {
        window.__FP_FEED_BOOTSTRAP__ = originalBootstrap;
        document.body.innerHTML = '';
        document.head.innerHTML = '';
    });

    function mountComposerDom() {
        document.body.innerHTML = `
            <script type="application/json" id="feed-bootstrap">
                ${JSON.stringify({
                    userId: 'u_self',
                    familyRoster: [
                        { id: 'u_self', firstName: 'Wally', lastName: 'Olaogun', famCode: 'OLA001', img: 'avatarM.png' },
                        { id: 'u_abiola', firstName: 'Abiola', lastName: 'Olaogun', famCode: 'OLA001', img: 'avatarF.png' },
                        { id: 'u_damilare', firstName: 'Damilare', lastName: 'Olaogun', famCode: 'OLA001', img: 'avatarM.png' },
                    ],
                })}
            </script>
            <div id="postModal" class="modal show">
                <form id="formPostMessageModal">
                    <span id="composerTagHeaderWrapper" class="d-none">
                        <span id="composerTagHeaderNames"></span>
                    </span>
                    <div id="selectedTagsTray" class="d-none"></div>
                    <div id="taggedUserInputs"></div>
                    <div id="postMessageEditor" contenteditable="true"></div>
                    <textarea id="postMessage" class="d-none"></textarea>
                    <div id="composerMentionDropdown" class="mention-autocomplete mention-autocomplete--below d-none">
                        <div id="composerMentionList"></div>
                    </div>
                </form>
            </div>
        `;
    }

    it('seeds from #feed-bootstrap at 0ms, ignores hidden textarea selectionEnd=0 sync, and inserts mention on touch', async () => {
        mountComposerDom();
        const mod = await import('../../components/profilePage/postEmojiImgProcess.js');

        const editor = document.getElementById('postMessageEditor');
        const textarea = document.getElementById('postMessage');
        const dropdown = document.getElementById('composerMentionDropdown');
        const list = document.getElementById('composerMentionList');

        // Simulate iOS typing "@" inside contenteditable
        editor.textContent = '@';
        const range = document.createRange();
        range.setStart(editor.firstChild, 1);
        range.collapse(true);
        const sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);

        // Simulate post.blade.php syncEditorToTextarea() firing BEFORE document input listener completes
        textarea.value = '@';
        textarea.selectionStart = 0;
        textarea.selectionEnd = 0; // iOS WebKit d-none textarea returns 0!
        window.__fpInternalTextareaSync = true;
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
        window.__fpInternalTextareaSync = false;

        // Now editor's own input event bubbles to document
        editor.dispatchEvent(new Event('input', { bubbles: true }));

        expect(dropdown.classList.contains('d-none')).toBe(false);
        expect(list.textContent).toContain('Abiola Olaogun');
        expect(list.textContent).toContain('Damilare Olaogun');
        expect(list.textContent).not.toContain('Wally Olaogun');

        // Even if another internal textarea sync fires right after, dropdown must stay open
        window.__fpInternalTextareaSync = true;
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
        window.__fpInternalTextareaSync = false;
        expect(dropdown.classList.contains('d-none')).toBe(false);

        // Simulate touch tap losing selection briefly before click
        sel.removeAllRanges();
        const abButton = Array.from(list.querySelectorAll('button')).find((b) =>
            b.textContent.includes('Abiola Olaogun')
        );
        expect(abButton).toBeTruthy();
        abButton.click();

        expect(editor.textContent).toContain('@Abiola Olaogun ');
        expect(dropdown.classList.contains('d-none')).toBe(true);
        expect(document.querySelectorAll('input[name="tagged_user_ids[]"]').length).toBe(1);
        expect(mod.getComposerMentionQuery(editor)).toBeNull();
    });

    it('handles iOS zero-width spaces, ELEMENT_NODE caret lag, compositionend, and undefined keyup safely', async () => {
        mountComposerDom();
        const mod = await import('../../components/profilePage/postEmojiImgProcess.js');
        const editor = document.getElementById('postMessageEditor');
        const dropdown = document.getElementById('composerMentionDropdown');

        // Zero-width space + non-breaking space + @da
        editor.innerHTML = 'Hello\u00A0\u200B@da';
        const sel = window.getSelection();
        const lagRange = document.createRange();
        lagRange.setStart(editor, 0); // iOS WebKit selection caret lag at offset 0 on ELEMENT_NODE
        lagRange.collapse(true);
        sel.removeAllRanges();
        sel.addRange(lagRange);

        expect(mod.getComposerMentionQuery(editor)).toBe('da');

        editor.dispatchEvent(new Event('compositionend', { bubbles: true }));
        expect(dropdown.classList.contains('d-none')).toBe(false);
        expect(document.getElementById('composerMentionList').textContent).toContain('Damilare Olaogun');

        // Synthetic keyup without `key` property (mobile autofill / IME) must not throw
        expect(() => {
            editor.dispatchEvent(new KeyboardEvent('keyup', { bubbles: true }));
        }).not.toThrow();
    });

    it('opens Preact CommentThread @mention autocomplete immediately on @ and across spaces', () => {
        const post = {
            post_no: '42',
            id: 'u_self',
            comments: [],
            reactions: {},
        };
        const bootstrap = {
            userId: 'u_self',
            familyRoster: [
                { id: 'u_self', firstName: 'Wally', lastName: 'Olaogun' },
                { id: 'u_abiola', firstName: 'Abiola', lastName: 'Olaogun' },
            ],
        };

        render(
            <CommentThread
                post={post}
                bootstrap={bootstrap}
                isExpanded={true}
                onMutateComments={vi.fn()}
            />
        );

        const input = screen.getByPlaceholderText('Write a comment...');
        fireEvent.input(input, { target: { value: '@' } });
        expect(screen.getByText('@Abiola Olaogun')).toBeTruthy();

        fireEvent.input(input, { target: { value: '@Abiola Ol' } });
        expect(screen.getByText('@Abiola Olaogun')).toBeTruthy();

        fireEvent.click(screen.getByText('@Abiola Olaogun'));
        expect(input.value).toBe('@Abiola Olaogun ');
    });
});
