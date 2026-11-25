import { useState, useLayoutEffect } from 'preact/hooks';

/**
 * On-Demand Lazy Modal Wrapper for Preact Islands.
 * - Avoids downloading, parsing, or mounting heavy modal chunks while `isOpen` is false.
 * - Once `isOpen` becomes true (or `.preload()` is invoked), dynamically imports the module
 *   and caches the resolved component for instant subsequent renders.
 */
export function createLazyModal(loader, exportName = 'default') {
    let cachedComponent = null;
    let inflightPromise = null;

    const loadComponent = () => {
        if (cachedComponent) {
            return Promise.resolve(cachedComponent);
        }
        if (!inflightPromise) {
            let rawPromise;
            try {
                rawPromise = Promise.resolve(loader());
            } catch (syncErr) {
                rawPromise = Promise.reject(syncErr);
            }
            inflightPromise = rawPromise
                .then((mod) => {
                    const resolved =
                        (exportName && mod && mod[exportName]) ||
                        (mod && mod.default) ||
                        mod;
                    if (typeof resolved === 'function') {
                        cachedComponent = resolved;
                    }
                    return cachedComponent;
                })
                .catch((err) => {
                    inflightPromise = null;
                    console.warn(`[LazyIslandModal] Failed to load ${exportName}:`, err);
                    return null;
                });
        }
        return inflightPromise;
    };

    function LazyModalWrapper(props) {
        const isOpen = Boolean(props?.isOpen);
        const [Component, setComponent] = useState(() => cachedComponent);

        useLayoutEffect(() => {
            if (!isOpen || cachedComponent) {
                if (cachedComponent && Component !== cachedComponent) {
                    setComponent(() => cachedComponent);
                }
                return undefined;
            }
            let active = true;
            loadComponent().then((Loaded) => {
                if (active && Loaded) {
                    setComponent(() => Loaded);
                }
            });
            return () => {
                active = false;
            };
        }, [isOpen, Component]);

        if (!isOpen || !Component) {
            return null;
        }

        return <Component {...props} />;
    }

    LazyModalWrapper.preload = loadComponent;
    return LazyModalWrapper;
}
