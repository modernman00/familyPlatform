import Pusher from 'pusher-js';

/**
 * Shared Hybrid Realtime Client (Pusher primary + SovereignBroadcaster SSE failover).
 * Handles both WebSocket failures AND server-side Pusher daily quota exhaustion
 * (via /api/broadcasting/status) using named EventSource listeners.
 */
export function createHybridRealtimeClient({ key = '', cluster = 'eu', getCsrfToken = () => '' } = {}) {
    const channelMap = new Map();
    let pusherInstance = null;
    let usingSseFallback = false;
    let disconnected = false;

    const activateSseForChannel = (channelName, entry) => {
        if (disconnected || entry.es || typeof window === 'undefined' || typeof EventSource === 'undefined') {
            return;
        }

        const connect = () => {
            if (disconnected || !channelMap.has(channelName)) return;

            const url = new URL('/api/broadcasting/events', window.location.origin);
            url.searchParams.set('channel', channelName);
            if (entry.lastEventId > 0) {
                url.searchParams.set('last_event_id', String(entry.lastEventId));
            } else {
                url.searchParams.set('since_now', '1');
            }

            const es = new EventSource(url.toString(), { withCredentials: true });
            entry.es = es;

            const attachListener = (eventName) => {
                if (entry.attachedEvents.has(eventName)) return;
                entry.attachedEvents.add(eventName);
                es.addEventListener(eventName, (ev) => {
                    if (ev.lastEventId) {
                        const parsedId = parseInt(ev.lastEventId, 10);
                        if (!Number.isNaN(parsedId)) entry.lastEventId = parsedId;
                    }
                    let payload = ev.data;
                    try {
                        payload = JSON.parse(ev.data);
                    } catch (_) {
                        // Keep raw string payload if not JSON
                    }
                    const cbs = entry.events.get(eventName) || [];
                    cbs.forEach((cb) => {
                        try {
                            cb(payload);
                        } catch (err) {
                            console.warn(`[SovereignSSE] Handler error on ${eventName}:`, err);
                        }
                    });
                });
            };

            entry.events.forEach((_, eventName) => attachListener(eventName));
            entry.attachListener = attachListener;

            es.onopen = () => {
                entry.reconnectAttempts = 0;
            };

            es.onerror = () => {
                if (es.readyState === EventSource.CLOSED && !disconnected) {
                    entry.es = null;
                    entry.attachedEvents.clear();
                    entry.reconnectAttempts = (entry.reconnectAttempts || 0) + 1;
                    const delay = Math.min(1000 * Math.pow(1.5, entry.reconnectAttempts), 15000);
                    setTimeout(connect, delay);
                }
            };
        };

        connect();
    };

    const failoverToSse = (reason) => {
        if (usingSseFallback || disconnected) return;
        usingSseFallback = true;
        console.warn(`[HybridRealtime] Failing over to SovereignBroadcaster SSE (${reason})`);
        channelMap.forEach((entry, channelName) => {
            activateSseForChannel(channelName, entry);
        });
    };

    if (key && cluster) {
        try {
            pusherInstance = new Pusher(key, {
                cluster,
                forceTLS: true,
                channelAuthorization: {
                    endpoint: '/pusher/auth',
                    headersProvider: () => ({
                        'X-CSRF-Token': getCsrfToken(),
                        'X-XSRF-TOKEN': getCsrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    }),
                },
            });

            if (pusherInstance.connection) {
                pusherInstance.connection.bind('error', () => failoverToSse('pusher connection error'));
                pusherInstance.connection.bind('unavailable', () => failoverToSse('pusher unavailable'));
                pusherInstance.connection.bind('failed', () => failoverToSse('pusher connection failed'));

                setTimeout(() => {
                    if (pusherInstance && pusherInstance.connection && pusherInstance.connection.state !== 'connected') {
                        failoverToSse('pusher connect timeout');
                    }
                }, 5000);
            }
        } catch (err) {
            failoverToSse('pusher init exception');
        }
    } else {
        usingSseFallback = true;
    }

    // Check server-side quota circuit-breaker (catches over-quota even when Pusher WebSocket stays connected)
    if (typeof fetch === 'function' && !usingSseFallback) {
        fetch('/api/broadcasting/status', {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        })
            .then((res) => (res.ok ? res.json() : null))
            .then((status) => {
                if (status && status.pusher_degraded) {
                    failoverToSse('pusher quota/outbound degraded');
                }
            })
            .catch(() => {});
    }

    return {
        subscribe(channelName) {
            let entry = channelMap.get(channelName);
            if (!entry) {
                entry = {
                    events: new Map(),
                    attachedEvents: new Set(),
                    pusherChannel: pusherInstance ? pusherInstance.subscribe(channelName) : null,
                    es: null,
                    lastEventId: 0,
                    reconnectAttempts: 0,
                    attachListener: null,
                };
                channelMap.set(channelName, entry);

                if (usingSseFallback) {
                    activateSseForChannel(channelName, entry);
                }
            }

            const proxy = {
                bind(eventName, callback) {
                    if (!entry.events.has(eventName)) {
                        entry.events.set(eventName, []);
                    }
                    entry.events.get(eventName).push(callback);

                    if (entry.pusherChannel) {
                        entry.pusherChannel.bind(eventName, callback);
                    }
                    if (usingSseFallback && entry.attachListener) {
                        entry.attachListener(eventName);
                    }
                    return proxy;
                },
            };

            return proxy;
        },

        disconnect() {
            disconnected = true;
            if (pusherInstance && typeof pusherInstance.disconnect === 'function') {
                try {
                    pusherInstance.disconnect();
                } catch (_) {}
            }
            channelMap.forEach((entry) => {
                if (entry.es) {
                    entry.es.close();
                    entry.es = null;
                }
            });
            channelMap.clear();
        },
    };
}
