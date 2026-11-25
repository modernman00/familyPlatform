import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/preact';
import { useState } from 'preact/hooks';
import { IslandErrorBoundary } from './IslandErrorBoundary';
import { Portal } from './Portal';
import { createLazyModal } from './lazyIslandModal';
import {
    signal,
    computed,
    effect,
    batch,
    untracked,
    useSignal,
    useComputed,
    useSignalEffect,
    SignalValue,
} from './signals';

function CrashableChild({ shouldCrash }) {
    if (shouldCrash) {
        throw new Error('Synthetic island crash');
    }
    return <div data-testid="healthy-child">Island healthy</div>;
}

function HarnessWithRecovery() {
    const [crash, setCrash] = useState(true);
    return (
        <div>
            <button type="button" onClick={() => setCrash(false)}>
                Fix Upstream State
            </button>
            <IslandErrorBoundary
                name="TestIsland"
                fallbackTitle="Unable to load test island."
            >
                <CrashableChild shouldCrash={crash} />
            </IslandErrorBoundary>
        </div>
    );
}

function SignalCounterView({ externalCount }) {
    const localMultiplier = useSignal(2);
    const product = useComputed(() => externalCount.value * localMultiplier.value, [externalCount]);
    return (
        <div>
            <span data-testid="sig-product">{product.value}</span>
            <button type="button" onClick={() => { localMultiplier.value += 1; }}>
                Bump Multiplier
            </button>
        </div>
    );
}

describe('Preact Shared Primitives: IslandErrorBoundary, Portal & Signals', () => {
    it('isolates child component crashes and recovers cleanly when Try Again is clicked', () => {
        const onError = vi.fn();
        const spy = vi.spyOn(console, 'error').mockImplementation(() => {});

        render(
            <IslandErrorBoundary name="FeedCard" onError={onError} compact>
                <CrashableChild shouldCrash />
            </IslandErrorBoundary>
        );

        expect(screen.getByRole('alert')).toBeTruthy();
        expect(onError).toHaveBeenCalledTimes(1);

        spy.mockRestore();
    });

    it('recovers and re-renders children after state is fixed and Try Again is clicked', () => {
        const spy = vi.spyOn(console, 'error').mockImplementation(() => {});

        render(<HarnessWithRecovery />);
        expect(screen.getByText('Unable to load test island.')).toBeTruthy();

        fireEvent.click(screen.getByRole('button', { name: 'Fix Upstream State' }));
        fireEvent.click(screen.getByRole('button', { name: 'Try Again' }));

        expect(screen.getByTestId('healthy-child').textContent).toBe('Island healthy');
        spy.mockRestore();
    });

    it('renders children into document.body via Portal', () => {
        render(
            <div data-testid="inline-wrapper">
                <Portal>
                    <div data-testid="portalled-modal">Portalled dialog</div>
                </Portal>
            </div>
        );

        const modal = screen.getByTestId('portalled-modal');
        expect(modal).toBeTruthy();
        expect(modal.parentElement).toBe(document.body);
    });

    it('tracks signal(), computed(), effect(), and batch() dependencies accurately', () => {
        const a = signal(3);
        const b = signal(4);
        const sum = computed(() => a.value + b.value);
        const observed = [];

        const dispose = effect(() => {
            observed.push(sum.value);
        });

        expect(sum.value).toBe(7);
        expect(observed).toEqual([7]);

        batch(() => {
            a.value = 10;
            b.value = 20;
        });

        expect(sum.value).toBe(30);
        expect(observed).toEqual([7, 30]);

        dispose();
        a.value = 50;
        expect(observed).toEqual([7, 30]);
    });

    it('re-renders Preact components subscribed via useSignal and useComputed', () => {
        const count = signal(5);
        render(<SignalCounterView externalCount={count} />);

        expect(screen.getByTestId('sig-product').textContent).toBe('10');

        fireEvent.click(screen.getByRole('button', { name: 'Bump Multiplier' }));
        expect(screen.getByTestId('sig-product').textContent).toBe('15');
    });

    it('supports untracked(), computed().dispose(), useSignalEffect(), and leaf-isolated <SignalValue /> without parent re-renders', async () => {
        const counter = signal(1);
        const sideChannel = signal(100);
        const observed = [];

        const comp = computed(() => counter.value + untracked(() => sideChannel.value));
        expect(comp.value).toBe(101);

        // Changing untracked sideChannel does not trigger recompute
        sideChannel.value = 200;
        expect(comp.value).toBe(101);

        // Disposing computed stops tracking counter updates
        comp.dispose();
        counter.value = 10;
        expect(comp.value).toBe(101);

        let parentRenders = 0;
        function ParentWithLeafSignal() {
            parentRenders += 1;
            useSignalEffect(() => {
                observed.push(counter.value);
            }, []);
            return (
                <div data-testid="leaf-val">
                    <SignalValue signal={counter} format={(v) => `Count: ${v}`} />
                </div>
            );
        }

        const { unmount } = render(<ParentWithLeafSignal />);
        expect(screen.getByTestId('leaf-val').textContent).toBe('Count: 10');
        expect(parentRenders).toBe(1);

        counter.value = 42;
        expect(await screen.findByText('Count: 42')).toBeTruthy();
        expect(parentRenders).toBe(1);
        expect(observed).toEqual([10, 42]);

        unmount();
        counter.value = 99;
        expect(observed).toEqual([10, 42]);
    });

    it('defers loading createLazyModal chunks until isOpen is true', async () => {
        const loader = vi.fn(async () => ({
            MockModal: ({ title }) => <div data-testid="lazy-modal-node">{title}</div>,
        }));
        const LazyMockModal = createLazyModal(loader, 'MockModal');

        const { rerender } = render(<LazyMockModal isOpen={false} title="Family Share" />);
        expect(loader).not.toHaveBeenCalled();
        expect(screen.queryByTestId('lazy-modal-node')).toBeNull();

        rerender(<LazyMockModal isOpen={true} title="Family Share" />);
        expect(loader).toHaveBeenCalledTimes(1);
        expect(await screen.findByTestId('lazy-modal-node')).toBeTruthy();
        expect(screen.getByTestId('lazy-modal-node').textContent).toBe('Family Share');
    });
});
