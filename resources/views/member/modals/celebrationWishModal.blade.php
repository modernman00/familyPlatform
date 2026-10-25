{{--
  Send wishes (RFC-0008). Opened by the Celebrations widget ("celebration:open"
  event) or by a notification link: /profilePage#celebrate-birthday-{userId}
  or #celebrate-anniversary-{unionId}. Wishes are private to the celebrant.
--}}
<div class="modal fade" id="celebrationWishModal" tabindex="-1" aria-labelledby="celebrationWishTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" id="celebrationWishTitle" style="font-size: 1.1rem;">Send your wishes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="min-width: 44px; min-height: 44px;"></button>
            </div>
            <form id="celebrationWishForm" novalidate>
                <div class="modal-body px-4 py-3">
                    <p class="text-muted small mb-2" id="celebrationWishHint">Only they will see your message.</p>
                    <input type="hidden" name="token" value="{{ $_SESSION['token'] ?? '' }}">
                    <input type="hidden" name="type" id="celebrationWishType">
                    <input type="hidden" name="target_id" id="celebrationWishTarget">
                    <label for="celebrationWishMessage" class="form-label fw-semibold small mb-1">Your message</label>
                    <textarea class="form-control rounded-3" id="celebrationWishMessage" name="message" rows="4" maxlength="500" required
                              placeholder="Happy birthday! Wishing you a wonderful year ahead."></textarea>
                    <div class="d-flex justify-content-between mt-1 small">
                        <span class="text-danger" id="celebrationWishError" role="alert"></span>
                        <span class="text-muted" id="celebrationWishCount" aria-live="polite">0 / 500</span>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal" style="min-height: 44px;">Cancel</button>
                    <button type="submit" class="btn rounded-pill px-4 fw-bold" id="celebrationWishSend" style="min-height: 44px; background: #db2777; color: #fff;">Send wishes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script nonce="{{ $nonce ?? '' }}">
(function () {
    if (window.__celebrationWishModalReady) return;
    window.__celebrationWishModalReady = true;

    const modalEl = document.getElementById('celebrationWishModal');
    const form = document.getElementById('celebrationWishForm');
    if (!modalEl || !form) return;

    const message = document.getElementById('celebrationWishMessage');
    const count = document.getElementById('celebrationWishCount');
    const errorEl = document.getElementById('celebrationWishError');
    const sendBtn = document.getElementById('celebrationWishSend');
    const title = document.getElementById('celebrationWishTitle');

    const showModal = () => window.bootstrap?.Modal?.getOrCreateInstance(modalEl)?.show();
    const hideModal = () => window.bootstrap?.Modal?.getInstance(modalEl)?.hide();
    const updateCount = () => { count.textContent = `${message.value.length} / 500`; };
    message.addEventListener('input', updateCount);

    const open = (occasion) => {
        if (!occasion?.type || !occasion?.target_id) return;
        document.getElementById('celebrationWishType').value = occasion.type;
        document.getElementById('celebrationWishTarget').value = occasion.target_id;
        title.textContent = occasion.type === 'anniversary'
            ? `💞 Wishes for ${occasion.title || 'their anniversary'}`
            : `🎂 Wishes for ${occasion.title || 'their birthday'}`;
        message.placeholder = occasion.type === 'anniversary'
            ? 'Happy anniversary! Wishing you many more years together.'
            : 'Happy birthday! Wishing you a wonderful year ahead.';
        errorEl.textContent = '';
        message.value = '';
        updateCount();
        showModal();
        setTimeout(() => message.focus(), 300);
    };
    window.addEventListener('celebration:open', (e) => open(e.detail));

    const csrf = () => {
        const fromCookie = (document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/) || [])[1];
        return fromCookie ? decodeURIComponent(fromCookie) : (form.querySelector('input[name="token"]')?.value || '');
    };

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const text = message.value.trim();
        if (text === '') {
            errorEl.textContent = 'Please write a message.';
            message.focus();
            return;
        }
        sendBtn.disabled = true;
        errorEl.textContent = '';
        try {
            const response = await fetch('/api/celebrations/wish', {
                method: 'POST',
                body: new FormData(form),
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-XSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest' },
            });
            const json = await response.json().catch(() => ({}));
            if (!response.ok || json?.status !== 'success') {
                errorEl.textContent = json?.message || 'Your wishes could not be sent. Please try again.';
                return;
            }
            hideModal();
            (window.swalSuccess || window.alert)(json?.message || 'Your wishes have been sent 🎉');
            window.refreshCelebrations?.();
        } catch (err) {
            errorEl.textContent = 'You seem to be offline. Please try again.';
        } finally {
            sendBtn.disabled = false;
        }
    });

    // Notification links: #celebrate-birthday-<id> / #celebrate-anniversary-<id>, and #celebrationWishes.
    // A hashchange means the page was already open, so its data may be stale: refresh first.
    const handleHash = async (event) => {
        const fromHashChange = event?.type === 'hashchange';
        const hash = decodeURIComponent(window.location.hash || '');
        const match = hash.match(/^#celebrate-(birthday|anniversary)-(.+)$/);
        if ((match || hash === '#celebrationWishes') && fromHashChange) {
            window.refreshCelebrations?.();
        }
        if (match && typeof window.loadCelebrations === 'function') {
            const data = await window.loadCelebrations();
            const occasion = data.occasions.find((o) => o?.type === match[1] && String(o?.target_id) === match[2]);
            if (occasion) {
                open(occasion);
            } else {
                const passed = 'This celebration has passed. Wishes can be sent up to 2 days late.';
                window.swalToast ? window.swalToast(passed, 'info') : console.info(passed);
            }
            history.replaceState(null, '', window.location.pathname + window.location.search);
        } else if (hash === '#celebrationWishes') {
            const card = [...document.querySelectorAll('.celebrations-widget')].find((el) => el.offsetParent !== null);
            card?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    };
    window.addEventListener('hashchange', handleHash);
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', handleHash);
    } else {
        handleHash();
    }
})();
</script>
