-- Migration: Restructure OLAO60446 family graph and eliminate silent auto-linking
-- 1. Taiwo (#46) is NOT Wally's brother; disconnect from Oluyomi & Iyabo (Union 1)
DELETE FROM family_node_children WHERE union_id = 1 AND child_id = 46;

-- 2. Fayokanmi (#84) is Wally's nephew (Femi's son); disconnect from Union 1, set Gen 1, link to Femi's union
DELETE FROM family_node_children WHERE union_id = 1 AND child_id = 84;
UPDATE family_nodes SET gender = 'Male', generation_level = 1, bio = 'Child' WHERE id = 84;

-- Ensure Femi (#28) has a union
INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, is_current)
SELECT 'OLAO60446', 28, 28, 'partner', 1
WHERE NOT EXISTS (
    SELECT 1 FROM family_unions WHERE family_code = 'OLAO60446' AND (partner_1_id = 28 OR partner_2_id = 28)
);

-- Link Fayokanmi as child of Femi's union
INSERT INTO family_node_children (union_id, child_id, relationship_type)
SELECT u.id, 84, 'biological'
FROM family_unions u
WHERE u.family_code = 'OLAO60446' AND (u.partner_1_id = 28 OR u.partner_2_id = 28)
AND NOT EXISTS (
    SELECT 1 FROM family_node_children WHERE union_id = u.id AND child_id = 84
)
LIMIT 1;

-- 3. Segun (#86) is Wally's half-brother (same father Oluyomi); disconnect from Union 1, link to Oluyomi's half-union
DELETE FROM family_node_children WHERE union_id = 1 AND child_id = 86;
UPDATE family_nodes SET bio = 'Half-sibling' WHERE id = 86;

-- Ensure Oluyomi (#2) has an isolated half-union (distinct from Union 1 with Iyabo)
INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, is_current)
SELECT 'OLAO60446', 2, 2, 'partner', 0
WHERE NOT EXISTS (
    SELECT 1 FROM family_unions WHERE family_code = 'OLAO60446' AND partner_1_id = 2 AND partner_2_id = 2
);

-- Link Segun as child of Oluyomi's half-union
INSERT INTO family_node_children (union_id, child_id, relationship_type)
SELECT u.id, 86, 'biological'
FROM family_unions u
WHERE u.family_code = 'OLAO60446' AND u.partner_1_id = 2 AND u.partner_2_id = 2
AND NOT EXISTS (
    SELECT 1 FROM family_node_children WHERE union_id = u.id AND child_id = 86
)
LIMIT 1;

-- 4. Olusola (#98): Set gender to Male so kinship renders as Brother
UPDATE family_nodes SET gender = 'Male' WHERE id = 98 AND family_code = 'OLAO60446';
