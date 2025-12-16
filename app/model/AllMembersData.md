getAllMembers(string $famCode, string $requesterId): array

SELECT – we’re telling the database which columns we want to retrieve.

p.id – the person’s ID.

p.firstName, p.lastName – first and last names from the personal table (aliased as p later).

p.famCode – their family code.

p.created_at – when their record was created.

ofm.father_name, ofm.mother_name, ofm.spouse_name – extra family info from otherFamily table (aliased as ofm).

pp.img – profile picture path from profilePics table (pp).

c.email, c.country, c.mobile – contact details from contact table (c).

So this says:

“From a bunch of joined tables, give me each person’s core info + extra family details + profile picture + contact info.”

CASE expression – computing relationType
            CASE 
                WHEN p.famCode = :famCode_case THEN 'family'
                WHEN rm.requester_id = :req_case_approved_you
                     AND rm.approver_id = p.id
                     AND rm.status = 'Approved'
                     THEN 'approved_you'
                WHEN rm.approver_id = :req_case_you_approved
                     AND rm.requester_id = p.id
                     AND rm.status = 'Approved'
                     THEN 'you_approved'
                ELSE 'other'
            END AS relationType


This is SQL’s version of if / else if / else:

CASE ... END AS relationType – we’re defining a computed column called relationType.

WHEN p.famCode = :famCode_case THEN 'family'
If this person’s family code equals the given code, set relationType to "family".

WHEN rm.requester_id = :req_case_approved_you AND rm.approver_id = p.id AND rm.status = 'Approved' THEN 'approved_you'
If the current user is the requester and this person is the approver, and the request is approved, label them "approved_you".

WHEN rm.approver_id = :req_case_you_approved AND rm.requester_id = p.id AND rm.status = 'Approved' THEN 'you_approved'
If the current user is the approver and this person is the requester, and the request is approved, label them "you_approved".

ELSE 'other' – if none of those match, label as "other".

So:

“For each person, figure out how they relate to the logged-in user: family, someone you approved, someone who approved you, or just ‘other’.”

The weird parameter names like :famCode_case are just placeholders; we’ll bind values into them later in PHP.

FROM and JOINs – which tables we’re reading
        FROM personal AS p
        INNER JOIN otherFamily AS ofm ON p.id = ofm.id
        INNER JOIN profilePics AS pp   ON p.id = pp.id
        INNER JOIN contact AS c        ON p.id = c.id


FROM personal AS p – main table is personal, aliased as p.

INNER JOIN otherFamily AS ofm ON p.id = ofm.id – join otherFamily where IDs match. INNER JOIN means: only rows where there is a match in both tables.

INNER JOIN profilePics AS pp ON p.id = pp.id – join profile pictures by id.

INNER JOIN contact AS c ON p.id = c.id – join contact info by id.

So:

“Start with each row in personal, then attach matching rows from otherFamily, profilePics, and contact using the person’s ID.”

LEFT JOIN requestMgt – relationship info
        LEFT JOIN requestMgt AS rm
            ON (
                (rm.requester_id = :req_join_outgoing AND rm.approver_id = p.id)
                OR
                (rm.approver_id = :req_join_incoming AND rm.requester_id = p.id)
            )
            AND rm.status = 'Approved'


LEFT JOIN requestMgt AS rm – we join the requestMgt table to see any “connection requests” between the logged-in user and this person.

ON (...) – this defines how rows should match:

(rm.requester_id = :req_join_outgoing AND rm.approver_id = p.id)
Means: the logged-in user sent a request to this person.

OR (rm.approver_id = :req_join_incoming AND rm.requester_id = p.id)
Means: this person sent a request to the logged-in user.

AND rm.status = 'Approved' – only consider approved requests.

LEFT JOIN means: if there’s no matching requestMgt row, we still keep the person; rm.* will just be NULL.

So:

“For each person, try to find an approved connection row between them and the current user in requestMgt, either direction. If none exists, we still keep the person but with no rm data.”

WHERE – filter which people we include
        WHERE 
            p.id != :req_where_not_me
            AND (
                p.famCode = :famCode_where_family
                OR rm.status = 'Approved'
            )


p.id != :req_where_not_me – don’t include the logged-in user themself.

AND (...) – extra filter:

p.famCode = :famCode_where_family – include everyone with the same famCode, or

rm.status = 'Approved' – include people with an approved relationship (the LEFT JOIN gives rm values for that).

So:

“Return everyone except me, where they either share my family code or have an approved connection with me.”

This matches the behaviour you want for “My Network”.

ORDER BY – how we sort results
        ORDER BY 
            CASE 
                WHEN p.famCode = :famCode_order_family THEN 1
                WHEN rm.requester_id = :req_order_outgoing
                     AND rm.approver_id = p.id
                     AND rm.status = 'Approved'
                     THEN 2
                WHEN rm.approver_id = :req_order_incoming
                     AND rm.requester_id = p.id
                     AND rm.status = 'Approved'
                     THEN 3
                ELSE 4
            END,
            p.firstName
    ";


ORDER BY – defines sorting order.

CASE ... END – we’re building a sort priority:

WHEN p.famCode = :famCode_order_family THEN 1
Family members → sort group 1 (top).

WHEN rm.requester_id = :req_order_outgoing ... THEN 2
People you requested and were approved → group 2.

WHEN rm.approver_id = :req_order_incoming ... THEN 3
People who requested you and were approved → group 3.

ELSE 4 – everyone else is group 4.

Then , p.firstName – within each group, sort alphabetically by first name.

So:

“Show family at the top, then people you approved, then people who approved you, then everyone else, and within each group, sort by first name.”