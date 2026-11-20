import styles from './OrganogramShell.module.css';

export function TreeControlsBar({
    searchQuery,
    onSearchChange,
    maxDepth,
    onMaxDepthChange,
    lineageFilter,
    onLineageFilterChange,
    zoom,
    onZoomIn,
    onZoomOut,
    onZoomReset,
    isReadOnly,
    onAddRelative,
    searchInputRef,
}) {
    return (
        <div className={styles.controlsBar} role="toolbar" aria-label="Family tree controls">
            <div className={styles.controlsGroup}>
                <input
                    ref={searchInputRef}
                    type="search"
                    className={styles.searchInput}
                    placeholder="Find a relative…"
                    aria-label="Find a relative in this tree"
                    value={searchQuery}
                    onInput={(e) => onSearchChange(e.target.value)}
                    onChange={(e) => onSearchChange(e.target.value)}
                />

                <select
                    className={styles.select}
                    aria-label="Generation depth"
                    value={String(maxDepth)}
                    onChange={(e) => onMaxDepthChange(Number(e.target.value) || 0)}
                >
                    <option value="0">All generations</option>
                    <option value="2">2 generations</option>
                    <option value="3">3 generations</option>
                    <option value="5">5 generations</option>
                    <option value="7">7 generations</option>
                </select>

                <select
                    className={styles.select}
                    aria-label="Lineage filter"
                    value={lineageFilter}
                    onChange={(e) => onLineageFilterChange(e.target.value)}
                >
                    <option value="all">All relatives</option>
                    <option value="birth">Birth lineage</option>
                    <option value="married">Married / In-laws</option>
                </select>
            </div>

            <div className={styles.controlsGroup}>
                <button
                    type="button"
                    className={styles.btn}
                    aria-label="Zoom out"
                    onClick={onZoomOut}
                >
                    <i className="bi bi-zoom-out" aria-hidden="true" />
                    <span>-</span>
                </button>
                <button
                    type="button"
                    className={styles.btn}
                    aria-label="Reset zoom"
                    onClick={onZoomReset}
                >
                    <span>{Math.round(zoom * 100)}%</span>
                </button>
                <button
                    type="button"
                    className={styles.btn}
                    aria-label="Zoom in"
                    onClick={onZoomIn}
                >
                    <i className="bi bi-zoom-in" aria-hidden="true" />
                    <span>+</span>
                </button>

                {!isReadOnly && (
                    <button
                        type="button"
                        className={`${styles.btn} ${styles.btnPrimary}`}
                        onClick={onAddRelative}
                    >
                        <i className="bi bi-person-plus-fill" aria-hidden="true" />
                        <span>Add relative</span>
                    </button>
                )}
            </div>
        </div>
    );
}
