import { apiFetch } from '../../shared/apiClient';
import { getCsrfToken, notifyToast } from '../../shared/bootstrap';
import { emailIsRegistered } from '../../../components/api/index';

export function isSuccessPayload(data) {
    if (!data || typeof data !== 'object') return false;
    return (
        data.status === 200 ||
        data.status === 'success' ||
        data.success === true ||
        data.ok === true
    );
}

export function extractMessage(data, fallback = 'Changes saved.') {
    if (!data) return fallback;
    if (typeof data === 'string' && data.trim() !== '') return data.trim();
    const candidates = [
        data.message,
        data.data && data.data.message,
        data.error,
        data.data && data.data.error,
    ];
    for (const item of candidates) {
        if (typeof item === 'string' && item.trim() !== '' && item.trim() !== '[object Object]') {
            return item.trim();
        }
        if (item && typeof item === 'object') {
            if (typeof item.message === 'string' && item.message.trim() !== '') return item.message.trim();
            if (typeof item.body === 'string' && item.body.trim() !== '') return item.body.trim();
            if (typeof item.content === 'string' && item.content.trim() !== '') return item.content.trim();
        }
    }
    return fallback;
}

export function extractFamilyCode(data, fallback = '') {
    if (!data || typeof data !== 'object') return fallback;
    return (
        (data.data && data.data.family_code) ||
        (data.token && data.token.family_code) ||
        data.family_code ||
        fallback
    );
}

export function toastMessage(title, icon = 'success') {
    notifyToast(title, icon);
}

export async function postAccountForm(fieldsOrFormData, bootstrap = {}) {
    const csrf = getCsrfToken(bootstrap);
    let body;
    if (fieldsOrFormData instanceof FormData) {
        body = fieldsOrFormData;
    } else {
        body = new FormData();
        Object.entries(fieldsOrFormData || {}).forEach(([k, v]) => {
            if (v !== undefined && v !== null) {
                body.append(k, String(v));
            }
        });
    }

    if (csrf) {
        if (!body.has('token')) body.append('token', csrf);
        if (!body.has('_token')) body.append('_token', csrf);
        if (!body.has('csrf_token')) body.append('csrf_token', csrf);
    }

    const headers = {};
    if (csrf) {
        headers['X-CSRF-TOKEN'] = csrf;
        headers['X-XSRF-TOKEN'] = csrf;
    }

    const data = await apiFetch('/accountSetting', {
        method: 'POST',
        body,
        headers,
    }, bootstrap);

    const ok = isSuccessPayload(data);
    return {
        ok,
        message: extractMessage(data, ok ? 'Changes successfully saved.' : 'Could not save changes.'),
        data,
    };
}

export async function fetchAccountSettingsState(bootstrap = {}) {
    const res = await apiFetch('/api/account-settings/state', { method: 'GET' }, bootstrap);
    if (res && res.data && typeof res.data === 'object') {
        return res.data;
    }
    if (res && res.accountData) {
        return res;
    }
    return null;
}

export async function upsertMemberRestriction(member, bootstrap = {}) {
    const csrf = getCsrfToken(bootstrap);
    const data = await apiFetch('/api/privacy/restrictions/update', {
        method: 'POST',
        headers: csrf ? { 'X-CSRF-TOKEN': csrf, 'X-XSRF-TOKEN': csrf } : {},
        body: {
            restricted_user_id: member.id,
            restrict_posts: Boolean(member.restrict_posts),
            restrict_comments: Boolean(member.restrict_comments),
            restrict_reels: Boolean(member.restrict_reels),
            restrict_events: Boolean(member.restrict_events),
            restrict_journals: Boolean(member.restrict_journals),
            restrict_announcements: Boolean(member.restrict_announcements),
            restrict_capsules: Boolean(member.restrict_capsules),
            restrict_profile: Boolean(member.restrict_profile),
            note: String(member.note || '').trim(),
            token: csrf,
        },
    }, bootstrap);
    if (!isSuccessPayload(data)) {
        throw new Error(extractMessage(data, 'Unable to update member restriction.'));
    }
    return data;
}

export async function removeMemberRestriction(memberId, bootstrap = {}) {
    const csrf = getCsrfToken(bootstrap);
    const data = await apiFetch('/api/privacy/restrictions/remove', {
        method: 'POST',
        headers: csrf ? { 'X-CSRF-TOKEN': csrf, 'X-XSRF-TOKEN': csrf } : {},
        body: {
            restricted_user_id: memberId,
            token: csrf,
        },
    }, bootstrap);
    if (!isSuccessPayload(data)) {
        throw new Error(extractMessage(data, 'Unable to remove member restriction.'));
    }
    return data;
}

export async function checkEmailExists(email) {
    return emailIsRegistered(email);
}

export async function sendMemberEmailInvite({ email, name, yourName, familyCode }, bootstrap = {}) {
    const csrf = getCsrfToken(bootstrap);
    return apiFetch('/register/contactNewMember', {
        method: 'POST',
        headers: csrf ? { 'X-CSRF-TOKEN': csrf, 'X-XSRF-TOKEN': csrf } : {},
        body: {
            mobile: '',
            viewPath: 'msg/contactNewMember',
            data: {
                email,
                name,
                yourName,
                familyCode,
            },
            subject: `${yourName || 'A family member'} wants you to join the family network`,
            token: csrf,
        },
    }, bootstrap);
}

export async function exportPersonalData(bootstrap = {}) {
    const csrf = getCsrfToken(bootstrap);
    const headers = { 'X-Requested-With': 'XMLHttpRequest' };
    if (csrf) {
        headers['X-CSRF-TOKEN'] = csrf;
        headers['X-XSRF-TOKEN'] = csrf;
    }
    const res = await fetch('/account/data-export', {
        method: 'POST',
        credentials: 'same-origin',
        headers,
    });
    if (!res.ok) {
        throw new Error(`Export failed (${res.status})`);
    }
    const blob = await res.blob();
    const cd = res.headers.get('Content-Disposition') || '';
    const match = /filename="?([^"]+)"?/.exec(cd);
    return {
        blob,
        filename: match ? match[1] : 'familyplatform-data.json',
    };
}

export async function requestAccountDeletion(mode, bootstrap = {}) {
    const csrf = getCsrfToken(bootstrap);
    return apiFetch('/account/request-deletion', {
        method: 'POST',
        headers: csrf ? { 'X-CSRF-TOKEN': csrf, 'X-XSRF-TOKEN': csrf } : {},
        body: { mode, token: csrf },
    }, bootstrap);
}

export async function sendTestPushNotification(bootstrap = {}) {
    const csrf = getCsrfToken(bootstrap);
    const data = await apiFetch('/api/notifications/test', {
        method: 'POST',
        headers: csrf ? { 'X-CSRF-TOKEN': csrf, 'X-XSRF-TOKEN': csrf } : {},
        body: { token: csrf },
    }, bootstrap);
    if (!isSuccessPayload(data)) {
        throw new Error(extractMessage(data, 'Could not dispatch test notification.'));
    }
    return data;
}

export function base64URLToBuffer(base64URL) {
    const base64 = String(base64URL).replace(/-/g, '+').replace(/_/g, '/');
    const padLength = (4 - (base64.length % 4)) % 4;
    const padded = base64.padEnd(base64.length + padLength, '=');
    const binary = atob(padded);
    const bytes = new Uint8Array(binary.length);
    for (let i = 0; i < binary.length; i++) {
        bytes[i] = binary.charCodeAt(i);
    }
    return bytes.buffer;
}

export function bufferToBase64URL(buffer) {
    const bytes = new Uint8Array(buffer);
    let binary = '';
    for (let i = 0; i < bytes.byteLength; i++) {
        binary += String.fromCharCode(bytes[i]);
    }
    return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
}

export async function registerPasskeyDevice(bootstrap = {}) {
    if (
        typeof window === 'undefined' ||
        (window.location.protocol !== 'https:' &&
            window.location.hostname !== 'localhost' &&
            window.location.hostname !== '127.0.0.1')
    ) {
        throw new Error('Passkey registration requires a secure connection (HTTPS).');
    }
    if (typeof window === 'undefined' || !window.PublicKeyCredential || !navigator.credentials) {
        throw new Error('Passkeys are not supported on this browser or platform.');
    }

    const csrf = getCsrfToken(bootstrap);
    const optData = await apiFetch('/webauthn/register/options', {
        method: 'POST',
        headers: csrf ? { 'X-CSRF-TOKEN': csrf, 'X-XSRF-TOKEN': csrf } : {},
        body: { token: csrf },
    }, bootstrap);

    if (!isSuccessPayload(optData)) {
        throw new Error(extractMessage(optData, 'Failed to initialise biometric challenge.'));
    }

    const options = optData.data || optData.token;
    const isIp = /^(\d{1,3}\.){3}\d{1,3}$/.test(window.location.hostname);
    const rpConfig = { name: 'FamilyPlatform' };
    if (!isIp && window.location.hostname !== 'localhost') {
        rpConfig.id = window.location.hostname;
    }

    const publicKey = {
        challenge: base64URLToBuffer(options.challenge),
        rp: rpConfig,
        user: {
            id: base64URLToBuffer(options.user.id),
            name: options.user.name,
            displayName: options.user.displayName,
        },
        pubKeyCredParams: options.pubKeyCredParams,
        authenticatorSelection: options.authenticatorSelection || {
            authenticatorAttachment: 'platform',
            userVerification: 'preferred',
        },
        timeout: options.timeout || 60000,
        attestation: options.attestation || 'none',
    };

    const credential = await navigator.credentials.create({ publicKey });
    if (!credential) {
        throw new Error('Passkey creation was cancelled.');
    }

    const verifyData = await apiFetch('/webauthn/register', {
        method: 'POST',
        headers: csrf ? { 'X-CSRF-TOKEN': csrf, 'X-XSRF-TOKEN': csrf } : {},
        body: {
            id: credential.id,
            rawId: bufferToBase64URL(credential.rawId),
            type: credential.type,
            response: {
                clientDataJSON: bufferToBase64URL(credential.response.clientDataJSON),
                attestationObject: bufferToBase64URL(credential.response.attestationObject),
            },
            token: csrf,
        },
    }, bootstrap);

    if (!isSuccessPayload(verifyData)) {
        throw new Error(extractMessage(verifyData, 'Device registration failed on server.'));
    }

    return verifyData;
}

export async function revokePasskeyDevice(passkey, bootstrap = {}) {
    const csrf = getCsrfToken(bootstrap);
    const data = await apiFetch('/webauthn/revoke', {
        method: 'POST',
        headers: csrf ? { 'X-CSRF-TOKEN': csrf, 'X-XSRF-TOKEN': csrf } : {},
        body: {
            passkey_id: passkey.id,
            credential_id: passkey.credential_id,
            token: csrf,
        },
    }, bootstrap);
    if (!isSuccessPayload(data)) {
        throw new Error(extractMessage(data, 'Failed to revoke biometric device.'));
    }
    return data;
}
