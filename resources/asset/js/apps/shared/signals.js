import { useEffect, useMemo, useRef, useState } from 'preact/hooks';

/**
 * Fine-Grained Preact 11 Signals Engine (zero external dependency).
 * Provides signal(), computed(), effect(), batch(), useSignal(), useComputed(),
 * and useSignalValue() with automatic dependency tracking and batched notifications.
 */

let currentObserver = null;
let batchDepth = 0;
let isFlushing = false;
const pendingListeners = new Set();

function flushPending() {
    if (batchDepth > 0 || isFlushing) return;
    isFlushing = true;
    const prevObserver = currentObserver;
    currentObserver = null;
    try {
        while (pendingListeners.size > 0) {
            const queue = Array.from(pendingListeners);
            pendingListeners.clear();
            for (let i = 0; i < queue.length; i += 1) {
                try {
                    queue[i]();
                } catch {
                    // Isolate subscriber errors
                }
            }
        }
    } finally {
        currentObserver = prevObserver;
        isFlushing = false;
    }
}

export function batch(fn) {
    batchDepth += 1;
    try {
        return fn();
    } finally {
        batchDepth -= 1;
        if (batchDepth === 0) {
            flushPending();
        }
    }
}

export function createSignal(initialValue) {
    let current = initialValue;
    const listeners = new Set();

    const notify = () => {
        const snapshot = Array.from(listeners);
        if (batchDepth > 0) {
            snapshot.forEach((fn) => {
                pendingListeners.add(() => fn(current));
            });
            return;
        }
        const prevObserver = currentObserver;
        currentObserver = null;
        try {
            snapshot.forEach((fn) => {
                try {
                    fn(current);
                } catch {
                    // Isolate subscriber errors
                }
            });
        } finally {
            currentObserver = prevObserver;
        }
    };

    return {
        get value() {
            if (currentObserver) {
                currentObserver.addDependency(this);
            }
            return current;
        },
        set value(next) {
            if (Object.is(current, next)) return;
            current = next;
            notify();
        },
        peek() {
            return current;
        },
        subscribe(fn) {
            listeners.add(fn);
            return () => listeners.delete(fn);
        },
    };
}

export const signal = createSignal;

export function effect(computeFn) {
    let cleanupFn = null;
    let disposed = false;
    const unsubscribers = [];

    const clearDeps = () => {
        while (unsubscribers.length > 0) {
            const unsub = unsubscribers.pop();
            try {
                unsub();
            } catch {
                // ignore
            }
        }
    };

    const run = () => {
        if (disposed) return;
        if (typeof cleanupFn === 'function') {
            try {
                cleanupFn();
            } catch {
                // ignore
            }
            cleanupFn = null;
        }
        clearDeps();
        const prevObserver = currentObserver;
        const tracked = new Set();
        currentObserver = {
            addDependency(sig) {
                if (!tracked.has(sig) && sig && typeof sig.subscribe === 'function') {
                    tracked.add(sig);
                    unsubscribers.push(sig.subscribe(() => run()));
                }
            },
        };
        try {
            const res = computeFn();
            if (typeof res === 'function') {
                cleanupFn = res;
            }
        } finally {
            currentObserver = prevObserver;
        }
    };

    run();

    return () => {
        disposed = true;
        clearDeps();
        if (typeof cleanupFn === 'function') {
            try {
                cleanupFn();
            } catch {
                // ignore
            }
            cleanupFn = null;
        }
    };
}

export function computed(computeFn) {
    const out = createSignal(undefined);
    effect(() => {
        const nextVal = computeFn();
        const prevObserver = currentObserver;
        currentObserver = null;
        try {
            out.value = nextVal;
        } finally {
            currentObserver = prevObserver;
        }
    });
    return {
        get value() {
            return out.value;
        },
        peek() {
            return out.peek();
        },
        subscribe(fn) {
            return out.subscribe(fn);
        },
    };
}

export function useSignalValue(sig) {
    const [val, setVal] = useState(() => (sig && typeof sig.peek === 'function' ? sig.peek() : undefined));
    useEffect(() => {
        if (!sig || typeof sig.subscribe !== 'function') return undefined;
        setVal(sig.peek());
        return sig.subscribe((next) => setVal(next));
    }, [sig]);
    return val;
}

export function useSignal(initialValue) {
    const sigRef = useRef(null);
    if (!sigRef.current) {
        sigRef.current = createSignal(
            typeof initialValue === 'function' ? initialValue() : initialValue
        );
    }
    useSignalValue(sigRef.current);
    return sigRef.current;
}

export function useComputed(computeFn, deps = []) {
    // eslint-disable-next-line react-hooks/exhaustive-deps
    const comp = useMemo(() => computed(computeFn), deps);
    useSignalValue(comp);
    return comp;
}
