import { describe, expect, it, vi, beforeEach, afterEach } from 'vitest';
import { readIslandBootstrap, getCsrfToken } from './bootstrap';
import { apiFetch } from './apiClient';

describe('Island Bootstrap & API Client Security', () => {
    beforeEach(() => {
        document.head.innerHTML = '';
        document.body.innerHTML = '';
        delete window.$csrf;
        delete window.__FP_BOOTSTRAP__;
    });

    afterEach(() => {
        vi.restoreAllMocks();
    });

    it('parses valid JSON bootstrap script safely and falls back on malformed JSON', () => {
        const script = document.createElement('script');
        script.type = 'application/json';
        script.id = 'studio-bootstrap';
        script.textContent = JSON.stringify({ famCode: 'OLAO123', csrfToken: 'tok_abc' });
        document.body.appendChild(script);

        expect(readIslandBootstrap('studio-bootstrap')).toEqual({
            famCode: 'OLAO123',
            csrfToken: 'tok_abc',
        });

        script.textContent = '{broken_json';
        const warnSpy = vi.spyOn(console, 'warn').mockImplementation(() => {});
        expect(readIslandBootstrap('studio-bootstrap', { famCode: 'FALLBACK' })).toEqual({
            famCode: 'FALLBACK',
        });
        expect(warnSpy).toHaveBeenCalled();
    });

    it('resolves CSRF token from $csrf, meta tag, or bootstrap', () => {
        expect(getCsrfToken({ csrfToken: 'boot_tok' })).toBe('boot_tok');

        const meta = document.createElement('meta');
        meta.name = 'csrf-token';
        meta.content = 'meta_tok';
        document.head.appendChild(meta);
        expect(getCsrfToken({ csrfToken: 'boot_tok' })).toBe('meta_tok');

        window.$csrf = () => 'magic_tok';
        expect(getCsrfToken({ csrfToken: 'boot_tok' })).toBe('magic_tok');
    });

    it('attaches X-CSRF-TOKEN and csrf_token on mutating FormData requests and surfaces 403 errors', async () => {
        const fetchMock = vi.fn().mockResolvedValue({
            ok: false,
            status: 403,
            headers: new Headers({ 'content-type': 'application/json' }),
            json: async () => ({ message: 'Forbidden cross-family mutation' }),
        });
        globalThis.fetch = fetchMock;

        const fd = new FormData();
        fd.append('first_name', 'Amara');

        await expect(
            apiFetch('/member/organogram/editor/child', { method: 'POST', body: fd }, { csrfToken: 'sec_123' })
        ).rejects.toMatchObject({
            message: 'Forbidden cross-family mutation',
            status: 403,
        });

        const [, reqInit] = fetchMock.mock.calls[0];
        expect(reqInit.headers.get('X-CSRF-TOKEN')).toBe('sec_123');
        expect(reqInit.headers.get('X-Requested-With')).toBe('XMLHttpRequest');
        expect(fd.get('csrf_token')).toBe('sec_123');
    });
});
