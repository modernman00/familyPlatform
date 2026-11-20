import { useEffect, useMemo, useRef, useState } from 'preact/hooks';
import { TreeControlsBar } from './TreeControlsBar';
import { TreeCanvas } from './TreeCanvas';
import { MemberDetailDrawer } from './MemberDetailDrawer';
import { BranchEditorModal } from './BranchEditorModal';
import styles from './OrganogramShell.module.css';

export function normaliseGraphNodes(bootstrap = {}) {
    if (Array.isArray(bootstrap.nodes)) {
        return bootstrap.nodes;
    }
    const graph = bootstrap.graphData || {};
    if (Array.isArray(graph.nodes)) {
        return graph.nodes;
    }
    if (graph.nodes && typeof graph.nodes === 'object') {
        return Object.values(graph.nodes);
    }
    return [];
}

export function OrganogramShell({ bootstrap = {} }) {
    const [nodes, setNodes] = useState(() => normaliseGraphNodes(bootstrap));
    const [searchQuery, setSearchQuery] = useState('');
    const [maxDepth, setMaxDepth] = useState(0);
    const [lineageFilter, setLineageFilter] = useState('all');
    const [zoom, setZoom] = useState(1);
    const [inspectedNode, setInspectedNode] = useState(null);
    const [branchAnchorNode, setBranchAnchorNode] = useState(null);
    const [isBranchModalOpen, setIsBranchModalOpen] = useState(false);

    const searchInputRef = useRef(null);
    const isReadOnly = Boolean(bootstrap.isReadOnly);

    useEffect(() => {
        const onKeyDown = (e) => {
            if (e.key === '/' && document.activeElement && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
                e.preventDefault();
                if (searchInputRef.current) searchInputRef.current.focus();
            }
        };
        window.addEventListener('keydown', onKeyDown);
        return () => window.removeEventListener('keydown', onKeyDown);
    }, []);

    const filteredNodes = useMemo(() => {
        const q = searchQuery.trim().toLowerCase();
        return nodes.filter((node) => {
            if (q) {
                const name = String(node.fullName || node.name || `${node.first_name || ''} ${node.last_name || ''}`).toLowerCase();
                const rel = String(node.relationLabel || node.relationship || '').toLowerCase();
                if (!name.includes(q) && !rel.includes(q)) return false;
            }
            if (maxDepth > 0) {
                const gen = Math.abs(Number(node.generation) || 0);
                if (gen > maxDepth) return false;
            }
            if (lineageFilter === 'birth') {
                if (node.lineage === 'married' || node.isInLaw) return false;
            } else if (lineageFilter === 'married') {
                if (node.lineage !== 'married' && !node.isInLaw) return false;
            }
            return true;
        });
    }, [nodes, searchQuery, maxDepth, lineageFilter]);

    const handleOpenBranchModal = (anchor = null) => {
        setBranchAnchorNode(anchor);
        setIsBranchModalOpen(true);
    };

    const handleNodeSaved = (newNode) => {
        setNodes((prev) => [...prev, newNode]);
    };

    const handleNodeDeleted = (deletedId) => {
        setNodes((prev) => prev.filter((n) => String(n.id) !== String(deletedId)));
    };

    return (
        <div className={styles.shell} data-island="organogram">
            <header className={styles.header}>
                <div className={styles.titleBlock}>
                    <h1 className={styles.title}>
                        {bootstrap.treeTitle || 'Family Tree'}
                    </h1>
                    <div className={styles.metaRow}>
                        <span className={styles.badge}>
                            <i className="bi bi-shield-lock-fill" aria-hidden="true" />
                            <span>Private · #{bootstrap.familyCode || 'FAMILY'}</span>
                        </span>
                        <span className={styles.badge}>
                            <i className="bi bi-people-fill" aria-hidden="true" />
                            <span>{nodes.length} {nodes.length === 1 ? 'member' : 'members'}</span>
                        </span>
                    </div>
                </div>

                <div className={styles.controlsGroup}>
                    <a
                        href={`/organogram?v=legacy${bootstrap.familyCode ? `&family_code=${encodeURIComponent(bootstrap.familyCode)}` : ''}`}
                        className={styles.btn}
                    >
                        <i className="bi bi-layout-wtf" aria-hidden="true" />
                        <span>Classic View</span>
                    </a>
                </div>
            </header>

            <TreeControlsBar
                searchQuery={searchQuery}
                onSearchChange={setSearchQuery}
                maxDepth={maxDepth}
                onMaxDepthChange={setMaxDepth}
                lineageFilter={lineageFilter}
                onLineageFilterChange={setLineageFilter}
                zoom={zoom}
                onZoomIn={() => setZoom((z) => Math.min(1.6, Number((z + 0.1).toFixed(2))))}
                onZoomOut={() => setZoom((z) => Math.max(0.6, Number((z - 0.1).toFixed(2))))}
                onZoomReset={() => setZoom(1)}
                isReadOnly={isReadOnly}
                onAddRelative={() => handleOpenBranchModal(null)}
                searchInputRef={searchInputRef}
            />

            <TreeCanvas
                nodes={filteredNodes}
                selectedNodeId={inspectedNode ? inspectedNode.id : null}
                zoom={zoom}
                isReadOnly={isReadOnly}
                onInspectNode={setInspectedNode}
                onAddBranch={(node) => handleOpenBranchModal(node)}
                onAddFirstRelative={() => handleOpenBranchModal(null)}
            />

            <MemberDetailDrawer
                node={inspectedNode}
                bootstrap={bootstrap}
                isReadOnly={isReadOnly}
                onClose={() => setInspectedNode(null)}
                onOpenBranchEditor={(node) => handleOpenBranchModal(node)}
                onNodeDeleted={handleNodeDeleted}
            />

            <BranchEditorModal
                isOpen={isBranchModalOpen}
                anchorNode={branchAnchorNode}
                bootstrap={bootstrap}
                onClose={() => setIsBranchModalOpen(false)}
                onSaved={handleNodeSaved}
            />
        </div>
    );
}
