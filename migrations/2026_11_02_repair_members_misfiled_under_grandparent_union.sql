-- Repair registered members (user_id set, generation 0) that were mis-filed as CHILDREN of a
-- grandparent-generation union (partner generation <= -2) by the old healOrphanedGen0Members heuristic.
--
-- They are moved to the single generation -1 union in the same family whose partner surname matches
-- the member's surname. Rows with no matching generation -1 union are LEFT UNTOUCHED (never guessed).
-- Idempotent: after the first run no link satisfies the WHERE clause.

UPDATE family_node_children c
JOIN family_unions bad ON bad.id = c.union_id
JOIN family_nodes n ON n.id = c.child_id
LEFT JOIN family_nodes bp1 ON bp1.id = bad.partner_1_id
LEFT JOIN family_nodes bp2 ON bp2.id = bad.partner_2_id
SET c.union_id = (
    SELECT MIN(t.id)
    FROM family_unions t
    LEFT JOIN family_nodes tp1 ON tp1.id = t.partner_1_id
    LEFT JOIN family_nodes tp2 ON tp2.id = t.partner_2_id
    WHERE t.family_code = bad.family_code
      AND COALESCE(tp1.generation_level, -1) = -1
      AND COALESCE(tp2.generation_level, -1) = -1
      AND (tp1.id IS NOT NULL OR tp2.id IS NOT NULL)
      AND (LOWER(TRIM(tp1.last_name)) = LOWER(TRIM(n.last_name)) OR LOWER(TRIM(tp2.last_name)) = LOWER(TRIM(n.last_name)))
)
WHERE n.generation_level = 0
  AND n.user_id IS NOT NULL
  AND TRIM(COALESCE(n.last_name, '')) <> ''
  AND (bp1.generation_level <= -2 OR bp2.generation_level <= -2)
  AND (
    SELECT COUNT(DISTINCT t2.id)
    FROM family_unions t2
    LEFT JOIN family_nodes t2p1 ON t2p1.id = t2.partner_1_id
    LEFT JOIN family_nodes t2p2 ON t2p2.id = t2.partner_2_id
    WHERE t2.family_code = bad.family_code
      AND COALESCE(t2p1.generation_level, -1) = -1
      AND COALESCE(t2p2.generation_level, -1) = -1
      AND (t2p1.id IS NOT NULL OR t2p2.id IS NOT NULL)
      AND (LOWER(TRIM(t2p1.last_name)) = LOWER(TRIM(n.last_name)) OR LOWER(TRIM(t2p2.last_name)) = LOWER(TRIM(n.last_name)))
  ) = 1;
