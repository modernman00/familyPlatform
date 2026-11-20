import { useMemo } from 'preact/hooks';
import { PersonNodeCard } from './PersonNodeCard';
import styles from './OrganogramShell.module.css';

export function TreeCanvas({
    nodes,
    selectedNodeId,
    zoom,
    isReadOnly,
    onInspectNode,
    onAddBranch,
    onAddFirstRelative,
}) {
    const groupedGenerations = useMemo(() => {
        const buckets = new Map();
        for (let i = 0; i < nodes.length; i += 1) {
            const node = nodes[i];
            const gen = Number.isFinite(Number(node.generation)) ? Number(node.generation) : 0;
            if (!buckets.has(gen)) buckets.set(gen, []);
            buckets.get(gen).push(node);
        }
        return Array.from(buckets.entries()).sort((a, b) => a[0] - b[0]);
    }, [nodes]);

    if (nodes.length === 0) {
        return (
            <div className={styles.canvasViewport}>
                <div className={styles.emptyState}>
                    <i className="bi bi-diagram-3" aria-hidden="true" style={{ fontSize: '2rem', color: '#a31621' }} />
                    <span>No relatives match the current filter or your family tree is ready to begin.</span>
                    {!isReadOnly && (
                        <button
                            type="button"
                            className={`${styles.btn} ${styles.btnPrimary}`}
                            onClick={onAddFirstRelative}
                        >
                            <i className="bi bi-person-plus-fill" aria-hidden="true" />
                            <span>Add first relative</span>
                        </button>
                    )}
                </div>
            </div>
        );
    }

    return (
        <div className={styles.canvasViewport} aria-label="Family tree canvas">
            <div
                className={styles.generationStage}
                style={{ transform: `scale(${zoom})` }}
            >
                {groupedGenerations.map(([genLevel, genNodes]) => (
                    <section
                        key={genLevel}
                        className={styles.generationRow}
                        aria-label={`Generation ${genLevel}`}
                    >
                        <div className={styles.generationLabel}>
                            {genLevel === 0
                                ? 'Current Generation'
                                : genLevel < 0
                                    ? `Ancestors (Gen ${genLevel})`
                                    : `Descendants (Gen +${genLevel})`}
                        </div>
                        <div className={styles.nodeGrid}>
                            {genNodes.map((node) => (
                                <PersonNodeCard
                                    key={String(node.id)}
                                    node={node}
                                    isSelected={String(node.id) === String(selectedNodeId)}
                                    isReadOnly={isReadOnly}
                                    onInspect={onInspectNode}
                                    onAddBranch={onAddBranch}
                                />
                            ))}
                        </div>
                    </section>
                ))}
            </div>
        </div>
    );
}
