{{-- Register v2: drives the 3-step sign-up on registration/register.blade.php.
     Runs before the webpack bundle (index.js / register.js), so its capture
     listeners on window see the submit click first and can stop it when a
     step is incomplete. The shared registerHandler still does the actual POST. --}}
@php
    $fprMsg = [
        'required' => __('reg.err_required', 'Please fill this in.'),
        'name' => __('reg.err_name', 'Use at least 2 letters.'),
        'email' => __('reg.err_email', 'Enter an email like name@example.com.'),
        'dob' => __('reg.err_dob', 'Choose your day, month and year of birth.'),
        'gender' => __('reg.err_gender', 'Please choose your gender.'),
        'mobile' => __('reg.err_mobile', 'Enter your full mobile number.'),
        'password' => __('reg.err_password', 'Use at least 8 characters.'),
        'code' => __('reg.err_code', 'Enter the family code (at least 4 characters).'),
        'inviter' => __('reg.err_inviter', 'Tell us who invited you, or send the request to the family admin.'),
        'consent' => __('reg.err_consent', 'Please tick to agree before continuing.'),
        'failed' => __('reg.err_failed', 'Something went wrong. Please check your details and try again.'),
        'sending' => __('reg.sending', 'Creating your account…'),
        'show' => __('reg.show', 'Show'),
        'hide' => __('reg.hide', 'Hide'),
        'you' => __('reg.tree_you', 'You'),
        'family' => __('reg.tree_family', 'Your family'),
    ];
@endphp
<script nonce="{{ csp_nonce($nonce ?? null) }}">
(function () {
    'use strict';
    const MSG = @json($fprMsg);
    const root = document.getElementById('fpr');
    const form = document.getElementById('register');
    if (!root || !form) return;

    const $ = (sel, ctx) => (ctx || document).querySelector(sel);
    const $$ = (sel, ctx) => Array.from((ctx || document).querySelectorAll(sel));
    const val = (id) => { const el = document.getElementById(id); return el ? String(el.value || '').trim() : ''; };
    const isOauth = root.dataset.oauth === '1';

    const steps = $$('[data-fpr-step]', form);
    const stepper = $$('#fprStepper li');
    const btnBack = document.getElementById('fprBack');
    const btnNext = document.getElementById('fprNext');
    const btnSubmit = document.getElementById('btnSubmit');
    // Invite links arrive with the family code confirmed, so they start on step 2.
    const startStep = Math.min(Math.max(Number(root.dataset.startStep) || 1, 1), 3);
    let current = startStep;

    /* ---------- helpers ---------- */
    const setError = (id, msg) => {
        const err = document.getElementById(id + '_error');
        if (err) err.textContent = msg || '';
        const input = document.getElementById(id);
        const field = input ? input.closest('.fpr-field') : (err ? err.closest('.fpr-field') : null);
        if (field) field.dataset.valid = msg ? 'false' : 'true';
        if (input) input.setAttribute('aria-invalid', msg ? 'true' : 'false');
        return !msg;
    };

    const alpineState = () => {
        try { return window.Alpine && window.Alpine.$data ? window.Alpine.$data(form) : null; } catch (_) { return null; }
    };

    const mode = () => ($('input[name="account_type"]:checked', form) || {}).value || 'create';

    /* ---------- field validators (return error message or '') ---------- */
    const rules = {
        famCode: () => (mode() !== 'join' ? '' : (val('famCode').replace(/^#\s*/, '').length >= 4 ? '' : MSG.code)),
        familySurname: () => (mode() !== 'create' ? '' : (val('familySurname').length >= 2 ? '' : MSG.name)),
        firstName: () => (val('firstName').length >= 2 ? '' : MSG.name),
        lastName: () => (val('lastName').length >= 2 ? '' : MSG.name),
        Date_of_Birth: () => (['day', 'month', 'year'].every((k) => { const v = val(k); return v && v !== 'select'; }) ? '' : MSG.dob),
        gender: () => (['Female', 'Male', 'Other'].includes(val('gender')) ? '' : MSG.gender),
        email: () => (/^[\w\-.+]+@[a-zA-Z0-9.\-]+\.[a-zA-Z0-9]{2,}$/.test(val('email')) ? '' : MSG.email),
        mobile: () => (val('mobile').replace(/\D/g, '').length >= 8 ? '' : MSG.mobile),
        password: () => (isOauth || val('password').length >= 8 ? '' : MSG.password),
        checkbox: () => (document.getElementById('checkbox') && document.getElementById('checkbox').checked ? '' : MSG.consent),
    };
    const stepFields = { 1: ['famCode', 'familySurname'], 2: ['firstName', 'lastName', 'Date_of_Birth', 'gender', 'email', 'mobile'], 3: ['password', 'checkbox'] };

    const validateStep = (n) => {
        let firstBad = null;
        stepFields[n].forEach((f) => {
            const ok = setError(f, rules[f]());
            if (!ok && !firstBad) firstBad = f;
        });
        // Joining an existing family: the inviter must be confirmed first.
        if (n === 1 && mode() === 'join' && !firstBad) {
            const s = alpineState();
            if (s && s.checkingCode) { firstBad = 'famCode'; }
            else if (s && s.codeExists && !s.codeVerified) {
                setError('famCode', MSG.inviter);
                firstBad = 'inviter_first_name';
            }
        }
        if (firstBad) {
            const target = document.getElementById(firstBad) || document.getElementById(firstBad === 'Date_of_Birth' ? 'day' : firstBad);
            if (target && typeof target.focus === 'function') target.focus({ preventScroll: false });
        }
        return !firstBad;
    };

    /* ---------- mode (join / create) ---------- */
    const applyMode = () => {
        const m = mode();
        $$('[data-fpr-mode]', form).forEach((el) => { el.hidden = el.dataset.fprMode !== m; });
        const code = document.getElementById('famCode');
        // A new family must not post a code: the server only generates one
        // when famCode is empty. Disabled inputs are left out of FormData.
        if (code) code.disabled = m === 'create';
        setError('famCode', '');
        setError('familySurname', '');
        updateTree();
    };

    /* ---------- steps ---------- */
    const show = (n, focus) => {
        current = n;
        steps.forEach((s) => { s.hidden = Number(s.dataset.fprStep) !== n; });
        stepper.forEach((li) => {
            const k = Number(li.dataset.step);
            li.dataset.state = k < n ? 'done' : (k === n ? 'current' : 'todo');
            if (k === n) li.setAttribute('aria-current', 'step'); else li.removeAttribute('aria-current');
        });
        $$('[data-fpr-social]').forEach((el) => { el.hidden = n !== startStep; });
        if (btnBack) btnBack.hidden = n === 1;
        if (btnNext) btnNext.hidden = n === steps.length;
        if (btnSubmit) btnSubmit.hidden = n !== steps.length;
        if (focus) {
            const title = $('[data-fpr-step="' + n + '"] .fpr-step-title', form);
            if (title) { title.setAttribute('tabindex', '-1'); title.focus({ preventScroll: true }); }
            const top = $('.fpr-main', root);
            if (top && top.getBoundingClientRect().top < 0) top.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    };

    // If the family code is still being checked, wait for the answer (max ~6s)
    // and then try again, instead of silently ignoring the click.
    let waitTimer = null;
    const next = (attempt = 0) => {
        const s = alpineState();
        if (current === 1 && mode() === 'join' && s && s.checkingCode && attempt < 40) {
            if (btnNext) btnNext.setAttribute('aria-busy', 'true');
            clearTimeout(waitTimer);
            waitTimer = setTimeout(() => next(attempt + 1), 150);
            return;
        }
        if (btnNext) btnNext.removeAttribute('aria-busy');
        if (validateStep(current)) show(Math.min(current + 1, steps.length), true);
    };
    const back = () => show(Math.max(current - 1, 1), true);

    if (btnNext) btnNext.addEventListener('click', () => next());
    if (btnBack) btnBack.addEventListener('click', back);

    // Enter on steps 1-2 moves forward instead of submitting the whole form.
    form.addEventListener('keydown', (e) => {
        if (e.key !== 'Enter' || current === steps.length) return;
        const t = e.target;
        if (t && (t.tagName === 'TEXTAREA' || t.tagName === 'BUTTON')) return;
        if (t && t.closest && t.closest('.fpr-inviter')) { e.preventDefault(); return; }
        e.preventDefault();
        next();
    });

    // Final gate: runs before the shared registerHandler (capture on window).
    const gate = (e) => {
        const isSubmitClick = e.type === 'click' && e.target && e.target.closest && e.target.closest('#btnSubmit');
        const isSubmit = e.type === 'submit' && e.target === form;
        if (!isSubmitClick && !isSubmit) return;
        // This page always handles the POST itself, so the shared
        // registerHandler (toast + fixed redirect to /login) never runs here.
        e.preventDefault();
        e.stopPropagation();
        if (e.stopImmediatePropagation) e.stopImmediatePropagation();
        mirrorPassword();
        for (let n = 1; n <= steps.length; n++) {
            if (!validateStep(n)) {
                if (n !== current) { show(n, false); validateStep(n); }
                return;
            }
        }
        submit();
    };

    /* ---------- submission ---------- */
    let submitting = false;
    const formError = document.getElementById('fprFormError');
    const showFormError = (msg) => {
        if (!formError) return;
        formError.textContent = msg || '';
        formError.hidden = !msg;
        if (msg) formError.scrollIntoView({ behavior: 'smooth', block: 'center' });
    };
    const readCookie = (name) => {
        const m = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/[$()*+.?[\\\]^{|}-]/g, '\\$&') + '=([^;]*)'));
        return m ? decodeURIComponent(m[1]) : '';
    };
    const recaptcha = async () => {
        const key = window.RECAPTCHA_SITE_KEY || window.recaptchaSiteKey || null;
        if (!key || !window.grecaptcha) return null;
        try {
            if (window.grecaptcha.enterprise && typeof window.grecaptcha.enterprise.execute === 'function') {
                return await window.grecaptcha.enterprise.execute(key, { action: 'SUBMIT' });
            }
            if (typeof window.grecaptcha.execute === 'function') return await window.grecaptcha.execute(key, { action: 'SUBMIT' });
        } catch (err) {
            console.warn('reCAPTCHA skipped:', err && err.message);
        }
        return null;
    };
    const errorText = (data) => {
        if (!data) return MSG.failed;
        if (Array.isArray(data.message)) return data.message.join(' ');
        // Debug builds prefix "Error on line N in /path/File.php: " — never show paths.
        if (typeof data.message === 'string' && data.message.trim()) return data.message.replace(/^Error on line \d+ in [^:]+:\s*/, '').trim();
        if (typeof data.error === 'string' && data.error.trim()) return data.error.trim();
        if (Array.isArray(data.errors) && data.errors.length) return data.errors.join(' ');
        return MSG.failed;
    };

    async function submit() {
        if (submitting) return;
        submitting = true;
        showFormError('');
        const label = btnSubmit ? btnSubmit.innerHTML : '';
        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.classList.add('is-loading');
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> ' + MSG.sending;
        }
        try {
            // Same payload the shared postFormData() sends.
            const fd = new FormData(form);
            fd.delete('submit');
            fd.delete('checkbox');
            const token = await recaptcha();
            if (token) { fd.append('siteKey', token); fd.append('action', 'SUBMIT'); }

            const headers = { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' };
            const xsrf = readCookie('XSRF-TOKEN');
            if (xsrf) headers['X-XSRF-TOKEN'] = xsrf;

            const res = await fetch('/register', { method: 'POST', body: fd, credentials: 'include', headers });
            let data = null;
            try { data = await res.json(); } catch (_) { data = null; }

            if (!res.ok || !data || data.status === 'error') {
                const msg = errorText(data);
                if (/already registered/i.test(msg) || /email/i.test(msg)) {
                    setError('email', msg);
                }
                showFormError(msg);
                return;
            }
            try { localStorage.setItem('redirect', '/login'); } catch (_) {}
            if (typeof window.fprShowDone === 'function') {
                window.fprShowDone({
                    code: data.show_code_modal ? data.family_code : '',
                    notFound: data.code_not_found || '',
                    redirect: data.redirect || data.token || '/login',
                    message: typeof data.message === 'string' ? data.message : '',
                });
            } else {
                window.location.href = data.redirect || data.token || '/login';
            }
        } catch (err) {
            console.error('Register submit failed:', err);
            showFormError(MSG.failed);
        } finally {
            submitting = false;
            if (btnSubmit) {
                btnSubmit.disabled = false;
                btnSubmit.classList.remove('is-loading');
                btnSubmit.innerHTML = label;
            }
        }
    }
    window.addEventListener('click', gate, true);
    window.addEventListener('submit', gate, true);

    // If an error lands in a step that is not on screen (server or shared
    // validator), jump to that step so the person can see it.
    new MutationObserver(() => {
        const bad = $$('[id$="_error"]', form).find((el) => el.textContent.trim() !== '');
        if (!bad) return;
        const s = bad.closest('[data-fpr-step]');
        if (s && s.hidden) show(Number(s.dataset.fprStep), false);
    }).observe(form, { subtree: true, childList: true, characterData: true });

    /* ---------- live feedback ---------- */
    const fieldFor = (id) => (['day', 'month', 'year'].includes(id) ? 'Date_of_Birth' : id);
    form.addEventListener('focusout', (e) => {
        const id = e.target && fieldFor(e.target.id);
        if (!id || !rules[id] || e.target.value === '' || e.target.value === 'select') return;
        setError(id, rules[id]());
    });
    form.addEventListener('input', (e) => {
        const id = e.target && fieldFor(e.target.id);
        if (id && rules[id]) {
            const field = (document.getElementById(id) || e.target).closest('.fpr-field');
            if (field && field.dataset.valid === 'false' && !rules[id]()) setError(id, '');
        }
        if (e.target && (e.target.id === 'firstName' || e.target.id === 'familySurname')) updateTree();
    });
    form.addEventListener('change', (e) => {
        if (e.target && e.target.name === 'account_type') applyMode();
        if (e.target && e.target.id === 'checkbox' && e.target.checked) setError('checkbox', '');
        const id = e.target && fieldFor(e.target.id);
        if (id === 'Date_of_Birth' && !rules.Date_of_Birth()) setError('Date_of_Birth', '');
    });

    /* ---------- password ---------- */
    const pw = document.getElementById('password');
    const pw2 = document.getElementById('confirm_password');
    const meter = document.getElementById('fprMeter');
    const toggle = document.getElementById('fprPwToggle');
    function mirrorPassword() { if (pw && pw2) pw2.value = pw.value; }
    const scorePassword = () => {
        if (!pw) return;
        const v = pw.value;
        const r = { len: v.length >= 8, case: /[a-z]/.test(v) && /[A-Z]/.test(v), num: /\d/.test(v) };
        $$('#fprPwRules li').forEach((li) => { li.dataset.ok = r[li.dataset.rule] ? 'true' : 'false'; });
        let score = (r.len ? 1 : 0) + (r.case ? 1 : 0) + (r.num ? 1 : 0) + (v.length >= 12 && /[^A-Za-z0-9]/.test(v) ? 1 : 0);
        if (!v) score = 0;
        if (meter) {
            meter.dataset.score = String(score);
            const bar = meter.firstElementChild;
            if (bar) bar.style.width = (v ? Math.max(score, 1) * 25 : 0) + '%';
        }
    };
    if (pw) {
        pw.addEventListener('input', () => { mirrorPassword(); scorePassword(); });
        mirrorPassword();
        scorePassword();
    }
    if (toggle && pw) {
        toggle.addEventListener('click', () => {
            const showing = pw.type === 'text';
            pw.type = showing ? 'password' : 'text';
            toggle.textContent = showing ? MSG.show : MSG.hide;
            toggle.setAttribute('aria-pressed', showing ? 'false' : 'true');
            pw.focus();
        });
    }

    /* ---------- live family tree ---------- */
    const treeFamily = $('[data-fpr-tree="family"]', root);
    const treeYou = $('[data-fpr-tree="you"]', root);
    const initialFamily = treeFamily ? treeFamily.textContent : MSG.family;
    const clip = (s, n) => (s.length > n ? s.slice(0, n - 1) + '…' : s);
    function updateTree() {
        if (treeYou) treeYou.textContent = clip(val('firstName') || MSG.you, 12);
        if (treeFamily) {
            const fam = mode() === 'create' ? val('familySurname') : '';
            treeFamily.textContent = clip(fam || initialFamily, 16);
        }
    }

    /* ---------- init ---------- */
    applyMode();
    show(startStep, false);
})();
</script>
