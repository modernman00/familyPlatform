import { describe, expect, it, vi, beforeEach, afterEach } from 'vitest';
import { readIslandBootstrap, getCsrfToken } from './bootstrap';
import { apiFetch } from './apiClient';

describe('Island Bootstrap & API Client Security', () => {
    beforeEach(() => {
        document.head.innerHTML = '';
        document.body.innerHTML = '';
        document.cookie = 'XSRF-TOKEN=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/';
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
        expect(reqInit.headers.get('X-XSRF-TOKEN')).toBe('sec_123');
        expect(reqInit.headers.get('X-Requested-With')).toBe('XMLHttpRequest');
        expect(fd.get('csrf_token')).toBe('sec_123');
    });

    it('prefers live 64-hex XSRF-TOKEN cookie over stale page-load meta tag', () => {
        const meta = document.createElement('meta');
        meta.name = 'csrf-token';
        meta.content = 'a'.repeat(64);
        document.head.appendChild(meta);

        const rotatedCookie = 'b'.repeat(64);
        document.cookie = `XSRF-TOKEN=${rotatedCookie}; path=/`;

        expect(getCsrfToken({ csrfToken: 'a'.repeat(64) })).toBe(rotatedCookie);
    });

    it('retries DELETE /post/:id once when 401 stale CSRF rotates XSRF-TOKEN cookie and syncs meta tag', async () => {
        const staleToken = '1'.repeat(64);
        const freshToken = '2'.repeat(64);

        const meta = document.createElement('meta');
        meta.name = 'csrf-token';
        meta.content = staleToken;
        document.head.appendChild(meta);
        document.cookie = `XSRF-TOKEN=${staleToken}; path=/`;

        const fetchMock = vi.fn().mockImplementationOnce(async () => {
            // Simulate server response Set-Cookie: XSRF-TOKEN=<freshToken>
            document.cookie = `XSRF-TOKEN=${freshToken}; path=/`;
            return {
                ok: false,
                status: 401,
                headers: new Headers({ 'content-type': 'application/json' }),
                json: async () => ({
                    status: 'error',
                    code: 401,
                    message: 'We are not familiar with the nature of your activities.',
                }),
            };
        }).mockImplementationOnce(async () => ({
            ok: true,
            status: 200,
            headers: new Headers({ 'content-type': 'application/json' }),
            json: async () => ({
                status: 'success',
                message: 'post deleted successfully',
            }),
        }));

        globalThis.fetch = fetchMock;

        const res = await apiFetch('/post/901', { method: 'DELETE' }, { csrfToken: staleToken });
        expect(res).toEqual({ status: 'success', message: 'post deleted successfully' });
        expect(fetchMock).toHaveBeenCalledTimes(2);

        const [, firstInit] = fetchMock.mock.calls[0];
        const [, secondInit] = fetchMock.mock.calls[1];
        expect(firstInit.headers.get('X-CSRF-TOKEN')).toBe(staleToken);
        expect(secondInit.headers.get('X-CSRF-TOKEN')).toBe(freshToken);
        expect(secondInit.headers.get('X-XSRF-TOKEN')).toBe(freshToken);
        expect(meta.getAttribute('content')).toBe(freshToken);
    });
});
