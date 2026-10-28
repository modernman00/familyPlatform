// Run: node tests/js/kinshipLayout.test.mjs
// Layout invariants for the KinshipTree engine, however a relative was added:
//   1. both partners of a couple sit side by side on the same row
//   2. no two nodes on a row overlap
//   3. children sit on a lower row than their parents
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

const src = readFileSync(new URL('../../public/js/vendor/kinshiptree.js', import.meta.url), 'utf8');
const sandbox = { console: { warn() {}, log() {} } };
vm.runInNewContext(src.replace("})(typeof window !== 'undefined' ? window : this);", '})(globalThis);'), sandbox);
const Engine = sandbox.KinshipTree.LayoutEngine;

/** Mirror of the graph -> engine input mapping in organogram.blade.php (self-unions included on purpose). */
function toEngineInput(g) {
    const parents = {};
    g.children.forEach(c => {
        const u = g.unions.find(x => x.id === c.union_id);
        if (!u) return;
        const p1 = g.nodes.find(n => n.id === u.partner_1_id);
        const p2 = g.nodes.find(n => n.id === u.partner_2_id);
        let fid = null, mid = null;
        [p1, p2].forEach(p => { if (p && p.gender === 'Male') fid = p.id; else if (p && p.gender === 'Female') mid = p.id; });
        if (p1 && !fid && !mid) { if (p1.gender === 'Female') mid = p1.id; else fid = p1.id; }
        parents[c.child_id] = { fid, mid };
    });
    const pids = {};
    g.unions.forEach(u => {
        (pids[u.partner_1_id] ??= []); (pids[u.partner_2_id] ??= []);
        if (!pids[u.partner_1_id].includes(u.partner_2_id)) pids[u.partner_1_id].push(u.partner_2_id);
        if (!pids[u.partner_2_id].includes(u.partner_1_id)) pids[u.partner_2_id].push(u.partner_1_id);
    });
    return {
        nodes: g.nodes.map(n => ({
            id: n.id, name: n.name, gender: n.gender.toLowerCase(), pids: pids[n.id] || [],
            fid: parents[n.id]?.fid || undefined, mid: parents[n.id]?.mid || undefined,
        })),
        unions: g.unions.map(u => ({
            id: u.id, p1: u.partner_1_id, p2: u.partner_2_id, union_type: 'married', is_current: u.is_current ?? 1,
            children: g.children.filter(c => c.union_id === u.id).map(c => c.child_id), kinds: {},
        })),
    };
}

function layout(g, rootId) {
    const { nodes, unions } = toEngineInput(g);
    const engine = new Engine({ rootId: String(rootId), nodeWidth: 210, partnerGap: 28 });
    return { engine, out: engine.computeLayout(nodes, new Set(), unions) };
}

function assertInvariants(g, out, engine, label) {
    const step = engine.nodeWidth + engine.partnerGap;
    const at = new Map(out.nodes.map(n => [String(n.id), n]));
    g.unions.filter(u => u.partner_1_id !== u.partner_2_id).forEach(u => {
        const a = at.get(String(u.partner_1_id)), b = at.get(String(u.partner_2_id));
        assert.ok(a && b, `${label}: partners placed`);
        assert.equal(Math.round(a.y), Math.round(b.y), `${label}: partners ${a.id}/${b.id} share a row`);
        const gap = Math.abs(a.x - b.x);
        assert.ok(gap >= engine.nodeWidth - 1 && gap <= step + 1, `${label}: partners ${a.id}/${b.id} adjacent (gap ${Math.round(gap)})`);
    });
    const rows = new Map();
    out.nodes.forEach(n => { const k = Math.round(n.y); (rows.get(k) ?? rows.set(k, []).get(k)).push(n); });
    rows.forEach(row => {
        row.sort((p, q) => p.x - q.x);
        for (let i = 1; i < row.length; i++) {
            assert.ok(row[i].x - row[i - 1].x >= engine.nodeWidth - 1, `${label}: ${row[i - 1].id}/${row[i].id} overlap`);
        }
    });
    g.children.forEach(c => {
        const u = g.unions.find(x => x.id === c.union_id);
        [u.partner_1_id, u.partner_2_id].forEach(pid => {
            assert.ok(at.get(String(c.child_id)).y > at.get(String(pid)).y, `${label}: child ${c.child_id} below parent ${pid}`);
        });
    });
}

// ── Regression: spouse added to someone who already had a single-parent (self) union ──
// Laf (69) has kids stored on a self-union (69,69); adding Tobi (31187) used to strand her at the row's far end.
const laf = {
    nodes: [
        { id: 70, name: 'Shola', gender: 'Male' }, { id: 6009, name: 'Testing', gender: 'Female' },
        { id: 75, name: 'Atinuke', gender: 'Female' }, { id: 73, name: 'Samu', gender: 'Male' },
        { id: 69, name: 'Laf', gender: 'Female' }, { id: 74, name: 'Ajibike', gender: 'Female' },
        { id: 76, name: 'Femba', gender: 'Male' }, { id: 6007, name: 'Barton', gender: 'Male' },
        { id: 25526, name: 'Not Provided', gender: 'Female' }, { id: 80, name: 'London', gender: 'Female' },
        { id: 389, name: 'Olawale', gender: 'Male' }, { id: 31187, name: 'Tobi', gender: 'Female' },
    ],
    unions: [
        { id: 1208, partner_1_id: 70, partner_2_id: 6009 },
        { id: 1209, partner_1_id: 69, partner_2_id: 69 },
        { id: 5008, partner_1_id: 6007, partner_2_id: 25526 },
        { id: 6763, partner_1_id: 69, partner_2_id: 31187 },
    ],
    children: [75, 73, 69, 74, 76, 6007].map(c => ({ union_id: 1208, child_id: c }))
        .concat([80, 389].map(c => ({ union_id: 1209, child_id: c }))),
};
{
    const { engine, out } = layout(laf, 69);
    assertInvariants(laf, out, engine, 'laf+tobi');
    const x = (id) => out.nodes.find(n => n.id === String(id)).x;
    assert.equal(Math.abs(x(69) - x(31187)), engine.nodeWidth + engine.partnerGap, 'Tobi docks right beside Laf');
}

// ── Safety net: a couple whose partner was left stranded is re-docked, making room for the move ──
{
    const { engine, out } = layout(laf, 69);
    const tobi = out.nodes.find(n => n.id === '31187');
    const far = out.nodes.filter(n => Math.round(n.y) === Math.round(tobi.y)).reduce((m, n) => Math.max(m, n.x), 0);
    tobi.x = far + 600;                                   // simulate the strand
    const nodeMap = new Map(out.nodes.map(n => [n.id, n]));
    assert.ok(engine._enforcePartnerAdjacency(nodeMap, out.unions) > 0, 'repair reported');
    assertInvariants(laf, { nodes: Array.from(nodeMap.values()) }, engine, 'after repair');
}

// ── Property: randomly grown families, relatives added in random order, always lay out cleanly ──
let seed = 20261024;
const rnd = () => (seed = (seed * 1664525 + 1013904223) >>> 0) / 2 ** 32;
const pick = (a) => a[Math.floor(rnd() * a.length)];

function growFamily(steps) {
    const g = { nodes: [], unions: [], children: [] };
    let nid = 1, uid = 100;
    const person = (gender) => { const n = { id: nid++, name: 'P' + nid, gender }; g.nodes.push(n); return n; };
    const gen = () => (rnd() < 0.5 ? 'Male' : 'Female');
    const root = person('Female');
    const coupleOf = (p) => g.unions.filter(u => u.partner_1_id !== u.partner_2_id && (u.partner_1_id === p.id || u.partner_2_id === p.id));
    const hasParent = (p) => g.children.some(c => c.child_id === p.id);
    for (let i = 0; i < steps; i++) {
        const p = pick(g.nodes);
        const action = pick(['partner', 'child', 'child', 'sibling', 'parents']);
        if (action === 'partner' && coupleOf(p).length < 2) {
            const s = person(gen());
            // a second partner is a former one: the engine chains [former, person, current] so both stay adjacent
            g.unions.push({ id: uid++, partner_1_id: p.id, partner_2_id: s.id, is_current: coupleOf(p).length === 0 ? 1 : 0 });
        } else if (action === 'child') {
            let u = pick(coupleOf(p).concat(g.unions.filter(x => x.partner_1_id === p.id && x.partner_2_id === p.id)));
            if (!u) { u = { id: uid++, partner_1_id: p.id, partner_2_id: p.id }; g.unions.push(u); }   // single-parent union
            g.children.push({ union_id: u.id, child_id: person(gen()).id });
        } else if (action === 'sibling' && hasParent(p)) {
            const link = g.children.find(c => c.child_id === p.id);
            g.children.push({ union_id: link.union_id, child_id: person(gen()).id });
        } else if (action === 'parents' && !hasParent(p) && p.id !== root.id) {
            const f = person('Male'), m = person('Female');
            const u = { id: uid++, partner_1_id: f.id, partner_2_id: m.id };
            g.unions.push(u);
            g.children.push({ union_id: u.id, child_id: p.id });
        }
    }
    return g;
}

let checked = 0;
for (let i = 0; i < 300; i++) {
    const g = growFamily(4 + Math.floor(rnd() * 14));
    const { engine, out } = layout(g, g.nodes[0].id);
    assertInvariants(g, out, engine, `random #${i}`);
    checked++;
}

// ── Regression: 3-Union Multi-Partner Chain ("Best Professional" / Oluyomi + Iyabo + Biliki) ──
// Oluyomi (41297) has 3 unions: EASKIT's mother (63103), Iyabo (41298, current), and Biliki (63105).
// Best Professional (63106) is child of Oluyomi (41297) + Biliki (63105).
// The union drop point (midX) for (Oluyomi, Biliki) MUST NOT land inside Iyabo's card bounds!
{
    const multiUnionGraph = {
        nodes: [
            { id: 41297, name: 'Oluyomi Olaogun', gender: 'Male' },
            { id: 41298, name: 'Iyabo Olaogun', gender: 'Female' },
            { id: 63103, name: 'Easkit Mother', gender: 'Female' },
            { id: 63105, name: 'Biliki', gender: 'Female' },
            { id: 38585, name: 'Segun Olaogun', gender: 'Male' },
            { id: 63101, name: 'Pos Dents', gender: 'Male' },
            { id: 63104, name: 'Easkit Presurd', gender: 'Male' },
            { id: 63106, name: 'Best Professional', gender: 'Male' },
        ],
        unions: [
            { id: 9734, partner_1_id: 41297, partner_2_id: 41298, is_current: 1 },
            { id: 16039, partner_1_id: 41297, partner_2_id: 63103, is_current: 0 },
            { id: 16040, partner_1_id: 41297, partner_2_id: 63105, is_current: 0 },
        ],
        children: [
            { union_id: 9734, child_id: 38585 },
            { union_id: 9734, child_id: 63101 },
            { union_id: 16039, child_id: 63104 },
            { union_id: 16040, child_id: 63106 },
        ]
    };
    const { engine, out } = layout(multiUnionGraph, 38585);
    const iyabo = out.nodes.find(n => n.id === '41298');
    const biliki = out.nodes.find(n => n.id === '63105');
    const bilikiUnion = out.unions.find(u => u.id === 'u_16040');
    assert.ok(iyabo && biliki && bilikiUnion, 'Oluyomi/Iyabo/Biliki placed');
    // Verify u_16040 drop-point is anchored to Biliki, NOT inside Iyabo's horizontal card bounds
    const inIyaboBounds = bilikiUnion.midX >= iyabo.x && bilikiUnion.midX <= (iyabo.x + engine.nodeWidth);
    assert.equal(inIyaboBounds, false, 'Biliki union midX must never emerge from Iyabo card');
    assert.equal(Math.round(bilikiUnion.midX), Math.round(biliki.x + engine.nodeWidth / 2), 'Biliki union midX anchors cleanly to Biliki');
}

// ── Regression: Spouse / In-Law Extended Family Branch Hidden by Default, Expanded on Toggle ──
{
    const { nodes, unions } = toEngineInput(laf);
    // Mark nodes 70 and 6009 as in-law branch via spouse 31187
    nodes.forEach(n => {
        if (n.id === 70 || n.id === 6009) {
            n.is_inlaw_branch = true;
            n.inlaw_spouse_id = 31187;
        }
    });
    const engine = new Engine({ rootId: '69', nodeWidth: 210, partnerGap: 28 });
    const collapsedOut = engine.computeLayout(nodes, new Set(), unions, new Set());
    assert.equal(collapsedOut.nodes.some(n => n.id === '70' || n.id === '6009'), false, 'In-law branch nodes hidden by default');
    const expandedOut = engine.computeLayout(nodes, new Set(), unions, new Set(['31187']));
    assert.equal(expandedOut.nodes.some(n => n.id === '70') && expandedOut.nodes.some(n => n.id === '6009'), true, 'In-law branch nodes visible when spouse expanded');
}

console.log(`kinshipLayout: regression + safety net + multi-union + inlaw-collapse + ${checked} random families OK`);
