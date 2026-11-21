import { useEffect, useState, useCallback } from 'preact/hooks';
import { apiFetch } from '../shared/apiClient';

function resolveAvatar(img) {
    if (!img) return '/resources/images/profile/avatarM.png';
    return String(img).startsWith('/') ? img : `/resources/images/profile/${img}`;
}

export function KinRequestsIsland({ bootstrap = {} }) {
    const userId = String(bootstrap?.id || '');
    const [requests, setRequests] = useState([]);
    const [loading, setLoading] = useState(true);

    const fetchRequests = useCallback(async () => {
        if (!userId) {
            setLoading(false);
            return;
        }
        setLoading(true);
        try {
            const res = await apiFetch(`/getFriendRequestById?id=${encodeURIComponent(userId)}`, {
                timeoutMs: 12000,
            }, bootstrap);
            const list = res?.message;
            if (Array.isArray(list)) {
                setRequests(list.map((req) => ({
                    id: req?.id ?? req?.requesterId,
                    firstName: req?.firstName ?? req?.requesterFirstName ?? 'Unknown',
                    lastName: req?.lastName ?? req?.requesterLastName ?? '',
                    img: req?.img ?? req?.requesterProfileImg ?? 'avatarM.png',
                    famCode: req?.famCode ?? req?.requesterFamCode ?? '',
                    relationship_category: req?.relationship_category ?? '',
                    relationship_category_label: req?.relationship_category_label ?? '',
                    relationship_role: req?.relationship_role ?? '',
                    relationship_note: req?.relationship_note ?? '',
                    relationship_full_label: req?.relationship_full_label ?? '',
                })));
            } else {
                setRequests([]);
            }
        } catch {
            setRequests([]);
        } finally {
            setLoading(false);
        }
    }, [userId]);

    useEffect(() => {
        if (!userId) {
            setLoading(false);
            return undefined;
        }
        try {
            localStorage.setItem('requesterFamCode', bootstrap.famCode || '');
            localStorage.setItem('requesterId', userId);
            localStorage.setItem('yourName', `${bootstrap.firstName || ''} ${bootstrap.lastName || ''}`.trim());
        } catch {
            // ignore storage errors
        }

        fetchRequests();
        const onReceived = () => fetchRequests();
        window.addEventListener('kin-request:received', onReceived);
        return () => window.removeEventListener('kin-request:received', onReceived);
    }, [userId, fetchRequests]);

    if (loading || requests.length === 0) {
        return null;
    }

    const getAcceptUrl = (req) => {
        const requestId = encodeURIComponent(req?.id || '');
        const approverId = encodeURIComponent(userId);
        const requestCode = encodeURIComponent(req?.famCode || 'kinship');
        return `/member/request/${requestId}/${approverId}/50/${requestCode}/pp`;
    };

    const getDeclineUrl = (req) => {
        const requestId = encodeURIComponent(req?.id || '');
        const approverId = encodeURIComponent(userId);
        const requestCode = encodeURIComponent(req?.famCode || 'none');
        return `/member/request/${requestId}/${approverId}/10/${requestCode}/pp`;
    };

    return (
        <div
            id="kinshipRequestSidebar"
            className="card border-0 shadow-sm mt-4 kinship-request-card"
            style={{ borderRadius: '20px', backgroundColor: 'var(--card-bg)' }}
        >
            <div className="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-4 pb-2 px-4">
                <h5 className="mb-0 fw-bold" style={{ color: 'var(--text-color)', fontSize: '1.05rem' }}>
                    {requests.length > 1 ? `${requests.length} Kin Requests` : 'Kin Request'}
                </h5>
                {requests.length > 1 && (
                    <a
                        href="/allMembers"
                        className="text-decoration-none"
                        style={{ color: 'var(--primary-color)', fontWeight: 600, fontSize: '0.85rem' }}
                    >
                        See All <i className="bi bi-chevron-right" style={{ fontSize: '0.75rem' }} />
                    </a>
                )}
            </div>

            <div className="card-body">
                <div className="d-flex flex-column gap-3">
                    {requests.map((req) => {
                        const relLabel = req.relationship_full_label
                            || req.relationship_role
                            || req.relationship_category_label
                            || req.relationship_category;
                        return (
                            <div
                                key={String(req.id)}
                                className="d-flex flex-column align-items-center text-center p-3 border rounded-3"
                                style={{
                                    backgroundColor: 'var(--hover-color)',
                                    borderColor: 'var(--border-color)',
                                    borderRadius: '14px',
                                }}
                            >
                                <img
                                    src={resolveAvatar(req.img)}
                                    alt={`${req.firstName} ${req.lastName}`.trim()}
                                    className="rounded-circle shadow-sm mb-2"
                                    style={{ width: '56px', height: '56px', objectFit: 'cover' }}
                                />
                                <span className="fw-semibold mb-1" style={{ fontSize: '0.9rem' }}>
                                    {req.firstName} {req.lastName}
                                </span>

                                {relLabel && (
                                    <span
                                        className="badge rounded-pill bg-light text-dark border mb-2 px-2 py-1"
                                        style={{ fontSize: '0.76rem' }}
                                    >
                                        <span className="fw-semibold text-primary">{relLabel}</span>
                                    </span>
                                )}

                                {req.relationship_note && (
                                    <p
                                        className="small text-muted fst-italic mb-2 px-2"
                                        style={{ fontSize: '0.78rem', lineHeight: 1.4 }}
                                    >
                                        “{req.relationship_note}”
                                    </p>
                                )}

                                <div
                                    className="small text-muted mb-2 px-1 text-center"
                                    style={{ fontSize: '0.72rem', opacity: 0.85 }}
                                >
                                    <i className="bi bi-shield-check text-success me-1" /> Connected Kin: Your family tree stays 100% private
                                </div>

                                <div className="d-flex gap-2 w-100">
                                    <a
                                        href={getAcceptUrl(req)}
                                        className="btn btn-primary btn-sm flex-grow-1 fw-bold rounded-pill d-inline-flex align-items-center justify-content-center gap-1"
                                        title="Accept as Connected Kin"
                                        style={{ textDecoration: 'none', minHeight: '44px' }}
                                    >
                                        <i className="bi bi-check-lg" /> Accept
                                    </a>
                                    <a
                                        href={getDeclineUrl(req)}
                                        className="btn btn-outline-danger btn-sm flex-grow-1 fw-bold rounded-pill d-inline-flex align-items-center justify-content-center gap-1"
                                        title="Decline"
                                        style={{ textDecoration: 'none', minHeight: '44px' }}
                                    >
                                        <i className="bi bi-x-lg" /> Decline
                                    </a>
                                </div>
                            </div>
                        );
                    })}
                </div>
            </div>
        </div>
    );
}
