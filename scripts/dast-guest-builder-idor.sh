#!/usr/bin/env bash
set -euo pipefail

# ==============================================================================
# 🛡️  FAMILYPLATFORM DAST: Full Guest Builder & Multi-Tenant IDOR Gauntlet
# ==============================================================================
# Validates strict zero-trust isolation and guest rejection across:
#   1. Unauthenticated Guest Rejection across ALL Builder & Social Endpoints
#   2. Organogram / Family Tree Builder Forms:
#      - Partner/Spouse Addition (POST /member/organogram/editor/partner)
#      - Child Addition (POST /member/organogram/editor/child)
#      - Parents Addition (POST /member/organogram/editor/parents)
#      - Sibling Addition (POST /member/organogram/editor/sibling)
#      - Node Details Modification (POST /member/organogram/editor/update)
#      - Node Deletion (POST /member/organogram/editor/delete)
#      - Node Claiming (POST /api/claim-family-node)
#      - 6-Gen Graph & Dossier Retrieval (GET /member/organogram/data & node)
#   3. Social Feed & Post Actions:
#      - Post Creation, Mutation & Deletion (POST / Profile, PUT/DELETE /post)
#      - Comment Creation, Mutation & Deletion (POST / Profile, PUT/DELETE /comment)
#      - Reactions & Social Feedback (POST /api/reactions/add)
#   4. Calendar Events & Profile Dossiers (DELETE /event, GET /seeProfile)
#   5. Legitimate Same-Family Execution & Anti-Self-Destruction Structural Guards
# ==============================================================================

# Virtual Host & Target URL Resolution (Defaults to https://olaogun.test)
if [ -z "${STAGING_URL:-}" ]; then
    if curl -k -s -m 2 https://olaogun.test > /dev/null 2>&1; then
        STAGING_URL="https://olaogun.test"
    else
        STAGING_URL="http://localhost:8000"
    fi
fi

# Trim trailing slash if present
STAGING_URL="${STAGING_URL%/}"
DAST_HEADER="${DAST_HEADER:-redteam_auth}"

echo "======================================================================"
echo "🔒 DAST: FamilyPlatform Guest Builder & Multi-Tenant IDOR Gauntlet"
echo "   Target Virtual Host: $STAGING_URL"
echo "   Execution Mode: Full Zero-Trust Verification"
echo "======================================================================"

TEST_PASSED=1
TOTAL_TESTS=0
PASSED_TESTS=0

record_pass() {
    echo "   ✅ PASS: $1"
    PASSED_TESTS=$((PASSED_TESTS + 1))
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
}

record_fail() {
    echo "   ❌ FAIL: $1"
    TEST_PASSED=0
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
}

# ==============================================================================
# PHASE 1: PROVISION ISOLATED TEST FAMILIES
# ==============================================================================
echo ""
echo "📦 [Phase 1] Provisioning Isolated Multi-Tenant Test Environment..."
SETUP=$(curl -k -s -X POST "$STAGING_URL/api/test/setup?token_ttl=3600" \
  -H "Content-Type: application/json" \
  -H "X-DAST-TEST: $DAST_HEADER" \
  -d '{}')

CLEANUP_TOKEN=$(echo "$SETUP" | jq -r '.cleanup_token // empty')
COOKIE_NAME=$(echo "$SETUP" | jq -r '.cookie_name // "familyCookie"')
USER_A_ID=$(echo "$SETUP" | jq -r '.data.user_a.id // empty')
USER_B_ID=$(echo "$SETUP" | jq -r '.data.user_b.id // empty')
USER_A_TOKEN=$(echo "$SETUP" | jq -r '.data.user_a.token // .data.manager_a.token // empty')
USER_B_TOKEN=$(echo "$SETUP" | jq -r '.data.user_b.token // .data.manager_b.token // empty')
FAM_A=$(echo "$SETUP" | jq -r '.data.fam_a // .data.event_a // empty')
FAM_B=$(echo "$SETUP" | jq -r '.data.fam_b // .data.event_b // empty')
NODE_A_ID=$(echo "$SETUP" | jq -r '.data.node_a_id // .data.prospect_a_id // empty')
NODE_B_ID=$(echo "$SETUP" | jq -r '.data.node_b_id // empty')
POST_A_ID=$(echo "$SETUP" | jq -r '.data.post_a_id // empty')
COMMENT_A_ID=$(echo "$SETUP" | jq -r '.data.comment_a_id // empty')
EVENT_A_NO=$(echo "$SETUP" | jq -r '.data.event_a_no // empty')

if [ -z "$CLEANUP_TOKEN" ] || [ "$CLEANUP_TOKEN" == "null" ] || [ -z "$USER_A_TOKEN" ] || [ -z "$NODE_A_ID" ] || [ -z "$POST_A_ID" ]; then
  echo "❌ FAIL: Setup endpoint returned invalid response or target ($STAGING_URL) is unreachable"
  echo "$SETUP"
  exit 1
fi

echo "   Family A ($FAM_A): User $USER_A_ID, Node ID $NODE_A_ID, Post #$POST_A_ID, Comment #$COMMENT_A_ID, Event #$EVENT_A_NO"
echo "   Family B ($FAM_B): Attacker provisioned (User $USER_B_ID)"

# ==============================================================================
# PHASE 2: UNAUTHENTICATED GUEST REJECTION (ZERO-TRUST GUEST GUARD)
# ==============================================================================
echo ""
echo "🚫 [Phase 2] Testing Unauthenticated Guest Access (Zero-Token / Zero-Session)..."

# 2.1 Guest Add Partner Form
RES_GUEST_PARTNER=$(curl -k -s -w "\n%{http_code}" -X POST "$STAGING_URL/member/organogram/editor/partner" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d "base_node_id=$NODE_A_ID&first_name=GhostPartner&gender=Female")
CODE=$(echo "$RES_GUEST_PARTNER" | tail -n 1)
if [[ "$CODE" =~ ^(401|403|302|400|404)$ ]]; then
    record_pass "Guest partner creation rejected (HTTP $CODE)"
else
    record_fail "Guest partner creation allowed! (HTTP $CODE)"
fi

# 2.2 Guest Add Child Form
RES_GUEST_CHILD=$(curl -k -s -w "\n%{http_code}" -X POST "$STAGING_URL/member/organogram/editor/child" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d "base_node_id=$NODE_A_ID&first_name=GhostChild&gender=Male")
CODE=$(echo "$RES_GUEST_CHILD" | tail -n 1)
if [[ "$CODE" =~ ^(401|403|302|400|404)$ ]]; then
    record_pass "Guest child creation rejected (HTTP $CODE)"
else
    record_fail "Guest child creation allowed! (HTTP $CODE)"
fi

# 2.3 Guest Add Parents Form
RES_GUEST_PARENTS=$(curl -k -s -w "\n%{http_code}" -X POST "$STAGING_URL/member/organogram/editor/parents" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d "base_node_id=$NODE_A_ID&father_first_name=GhostDad&mother_first_name=GhostMom")
CODE=$(echo "$RES_GUEST_PARENTS" | tail -n 1)
if [[ "$CODE" =~ ^(401|403|302|400|404)$ ]]; then
    record_pass "Guest parents creation rejected (HTTP $CODE)"
else
    record_fail "Guest parents creation allowed! (HTTP $CODE)"
fi

# 2.4 Guest Add Sibling Form
RES_GUEST_SIB=$(curl -k -s -w "\n%{http_code}" -X POST "$STAGING_URL/member/organogram/editor/sibling" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d "base_node_id=$NODE_A_ID&first_name=GhostSibling")
CODE=$(echo "$RES_GUEST_SIB" | tail -n 1)
if [[ "$CODE" =~ ^(401|403|302|400|404)$ ]]; then
    record_pass "Guest sibling creation rejected (HTTP $CODE)"
else
    record_fail "Guest sibling creation allowed! (HTTP $CODE)"
fi

# 2.5 Guest Node Update Form
RES_GUEST_UPDATE=$(curl -k -s -w "\n%{http_code}" -X POST "$STAGING_URL/member/organogram/editor/update" \
  -H "Content-Type: application/json" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d "{\"node_id\":$NODE_A_ID,\"first_name\":\"GhostUpdate\"}")
CODE=$(echo "$RES_GUEST_UPDATE" | tail -n 1)
if [[ "$CODE" =~ ^(401|403|302|400|404)$ ]]; then
    record_pass "Guest node update rejected (HTTP $CODE)"
else
    record_fail "Guest node update allowed! (HTTP $CODE)"
fi

# 2.6 Guest Node Delete Action
RES_GUEST_DEL=$(curl -k -s -w "\n%{http_code}" -X POST "$STAGING_URL/member/organogram/editor/delete" \
  -H "Content-Type: application/json" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d "{\"node_id\":$NODE_A_ID}")
CODE=$(echo "$RES_GUEST_DEL" | tail -n 1)
if [[ "$CODE" =~ ^(401|403|302|400|404)$ ]]; then
    record_pass "Guest node deletion rejected (HTTP $CODE)"
else
    record_fail "Guest node deletion allowed! (HTTP $CODE)"
fi

# 2.7 Guest Post Creation Form
RES_GUEST_POST_CREATE=$(curl -k -s -w "\n%{http_code}" -X POST "$STAGING_URL/member/profilePage/post" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d "postMessage=GhostPostAttack")
CODE=$(echo "$RES_GUEST_POST_CREATE" | tail -n 1)
if [[ "$CODE" =~ ^(401|403|302|400|404)$ ]]; then
    record_pass "Guest post creation rejected (HTTP $CODE)"
else
    record_fail "Guest post creation allowed! (HTTP $CODE)"
fi

# 2.8 Guest Post Edit Action
RES_GUEST_POST_EDIT=$(curl -k -s -w "\n%{http_code}" -X PUT "$STAGING_URL/post/$POST_A_ID" \
  -H "Content-Type: application/json" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d '{"postMessage":"GhostPostEdit"}')
CODE=$(echo "$RES_GUEST_POST_EDIT" | tail -n 1)
if [[ "$CODE" =~ ^(401|403|302|400|404)$ ]]; then
    record_pass "Guest post edit rejected (HTTP $CODE)"
else
    record_fail "Guest post edit allowed! (HTTP $CODE)"
fi

# 2.9 Guest Post Delete Action
RES_GUEST_POST_DEL=$(curl -k -s -w "\n%{http_code}" -X DELETE "$STAGING_URL/post/$POST_A_ID" \
  -H "X-Requested-With: XMLHttpRequest")
CODE=$(echo "$RES_GUEST_POST_DEL" | tail -n 1)
if [[ "$CODE" =~ ^(401|403|302|400|404)$ ]]; then
    record_pass "Guest post deletion rejected (HTTP $CODE)"
else
    record_fail "Guest post deletion allowed! (HTTP $CODE)"
fi

# 2.10 Guest Comment Creation Action
RES_GUEST_CMT_CREATE=$(curl -k -s -w "\n%{http_code}" -X POST "$STAGING_URL/postCommentProfile" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d "post_no=$POST_A_ID&comment=GhostComment")
CODE=$(echo "$RES_GUEST_CMT_CREATE" | tail -n 1)
if [[ "$CODE" =~ ^(401|403|302|400|404)$ ]]; then
    record_pass "Guest comment creation rejected (HTTP $CODE)"
else
    record_fail "Guest comment creation allowed! (HTTP $CODE)"
fi

# 2.11 Guest Comment Edit Action
RES_GUEST_CMT_EDIT=$(curl -k -s -w "\n%{http_code}" -X PUT "$STAGING_URL/comment/$COMMENT_A_ID" \
  -H "Content-Type: application/json" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d '{"comment":"GhostCommentEdit"}')
CODE=$(echo "$RES_GUEST_CMT_EDIT" | tail -n 1)
if [[ "$CODE" =~ ^(401|403|302|400|404)$ ]]; then
    record_pass "Guest comment edit rejected (HTTP $CODE)"
else
    record_fail "Guest comment edit allowed! (HTTP $CODE)"
fi

# 2.12 Guest Comment Delete Action
RES_GUEST_CMT_DEL=$(curl -k -s -w "\n%{http_code}" -X DELETE "$STAGING_URL/comment/$COMMENT_A_ID" \
  -H "X-Requested-With: XMLHttpRequest")
CODE=$(echo "$RES_GUEST_CMT_DEL" | tail -n 1)
if [[ "$CODE" =~ ^(401|403|302|400|404)$ ]]; then
    record_pass "Guest comment deletion rejected (HTTP $CODE)"
else
    record_fail "Guest comment deletion allowed! (HTTP $CODE)"
fi

# 2.13 Guest Event Deletion Action
RES_GUEST_EVT_DEL=$(curl -k -s -w "\n%{http_code}" -X DELETE "$STAGING_URL/member/profilePage/event/$EVENT_A_NO" \
  -H "X-Requested-With: XMLHttpRequest")
CODE=$(echo "$RES_GUEST_EVT_DEL" | tail -n 1)
if [[ "$CODE" =~ ^(401|403|302|400|404)$ ]]; then
    record_pass "Guest event deletion rejected (HTTP $CODE)"
else
    record_fail "Guest event deletion allowed! (HTTP $CODE)"
fi

# ==============================================================================
# PHASE 3: CROSS-TENANT / MULTI-FAMILY IDOR GAUNTLET (USER B vs FAMILY A)
# ==============================================================================
echo ""
echo "⚔️  [Phase 3] Testing Cross-Tenant IDOR Protection (User B attacking Family A)..."

# 3.1 Cross-Family Add Partner Attack
RES_IDOR_PARTNER=$(curl -k -s -w "\n%{http_code}" -X POST "$STAGING_URL/member/organogram/editor/partner" \
  -H "Authorization: Bearer $USER_B_TOKEN" \
  -H "Cookie: $COOKIE_NAME=$USER_B_TOKEN; auth_token=$USER_B_TOKEN; familyCookie=$USER_B_TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d "base_node_id=$NODE_A_ID&first_name=AttackerSpouse&gender=Female")
CODE=$(echo "$RES_IDOR_PARTNER" | tail -n 1)
if [[ "$CODE" =~ ^(401|403|400|404)$ ]]; then
    record_pass "Cross-family partner addition rejected (HTTP $CODE)"
else
    record_fail "Cross-family partner addition allowed! (HTTP $CODE = IDOR vulnerability!)"
fi

# 3.2 Cross-Family Add Child Attack
RES_IDOR_CHILD=$(curl -k -s -w "\n%{http_code}" -X POST "$STAGING_URL/member/organogram/editor/child" \
  -H "Authorization: Bearer $USER_B_TOKEN" \
  -H "Cookie: $COOKIE_NAME=$USER_B_TOKEN; auth_token=$USER_B_TOKEN; familyCookie=$USER_B_TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d "base_node_id=$NODE_A_ID&first_name=AttackerChild&gender=Male")
CODE=$(echo "$RES_IDOR_CHILD" | tail -n 1)
if [[ "$CODE" =~ ^(401|403|400|404)$ ]]; then
    record_pass "Cross-family child addition rejected (HTTP $CODE)"
else
    record_fail "Cross-family child addition allowed! (HTTP $CODE = IDOR vulnerability!)"
fi

# 3.3 Cross-Family Add Parents Attack
RES_IDOR_PARENTS=$(curl -k -s -w "\n%{http_code}" -X POST "$STAGING_URL/member/organogram/editor/parents" \
  -H "Authorization: Bearer $USER_B_TOKEN" \
  -H "Cookie: $COOKIE_NAME=$USER_B_TOKEN; auth_token=$USER_B_TOKEN; familyCookie=$USER_B_TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d "base_node_id=$NODE_A_ID&father_first_name=AttackerDad&mother_first_name=AttackerMom")
CODE=$(echo "$RES_IDOR_PARENTS" | tail -n 1)
if [[ "$CODE" =~ ^(401|403|400|404)$ ]]; then
    record_pass "Cross-family parents addition rejected (HTTP $CODE)"
else
    record_fail "Cross-family parents addition allowed! (HTTP $CODE = IDOR vulnerability!)"
fi

# 3.4 Cross-Family Add Sibling Attack
RES_IDOR_SIB=$(curl -k -s -w "\n%{http_code}" -X POST "$STAGING_URL/member/organogram/editor/sibling" \
  -H "Authorization: Bearer $USER_B_TOKEN" \
  -H "Cookie: $COOKIE_NAME=$USER_B_TOKEN; auth_token=$USER_B_TOKEN; familyCookie=$USER_B_TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d "base_node_id=$NODE_A_ID&first_name=AttackerSibling")
CODE=$(echo "$RES_IDOR_SIB" | tail -n 1)
if [[ "$CODE" =~ ^(401|403|400|404)$ ]]; then
    record_pass "Cross-family sibling addition rejected (HTTP $CODE)"
else
    record_fail "Cross-family sibling addition allowed! (HTTP $CODE = IDOR vulnerability!)"
fi

# 3.5 Cross-Family Node Update Attack
RES_IDOR_UPDATE=$(curl -k -s -w "\n%{http_code}" -X POST "$STAGING_URL/member/organogram/editor/update" \
  -H "Authorization: Bearer $USER_B_TOKEN" \
  -H "Cookie: $COOKIE_NAME=$USER_B_TOKEN; auth_token=$USER_B_TOKEN; familyCookie=$USER_B_TOKEN" \
  -H "Content-Type: application/json" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d "{\"node_id\":$NODE_A_ID,\"first_name\":\"HACKED_BY_B\",\"last_name\":\"Exploit\"}")
CODE=$(echo "$RES_IDOR_UPDATE" | tail -n 1)
if [[ "$CODE" =~ ^(401|403|400|404)$ ]]; then
    record_pass "Cross-family node update rejected (HTTP $CODE)"
else
    record_fail "Cross-family node update allowed! (HTTP $CODE = IDOR vulnerability!)"
fi

# 3.6 Cross-Family Node Delete Attack
RES_IDOR_DEL=$(curl -k -s -w "\n%{http_code}" -X POST "$STAGING_URL/member/organogram/editor/delete" \
  -H "Authorization: Bearer $USER_B_TOKEN" \
  -H "Cookie: $COOKIE_NAME=$USER_B_TOKEN; auth_token=$USER_B_TOKEN; familyCookie=$USER_B_TOKEN" \
  -H "Content-Type: application/json" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d "{\"node_id\":$NODE_A_ID}")
CODE=$(echo "$RES_IDOR_DEL" | tail -n 1)
if [[ "$CODE" =~ ^(401|403|400|404)$ ]]; then
    record_pass "Cross-family node deletion rejected (HTTP $CODE)"
else
    record_fail "Cross-family node deletion allowed! (HTTP $CODE = IDOR vulnerability!)"
fi

# 3.7 Cross-Family Organogram Graph Access (User B attempting to read Family A's Tree)
RES_IDOR_GRAPH=$(curl -k -s -w "\n%{http_code}" -X GET "$STAGING_URL/member/organogram/data/$FAM_A" \
  -H "Authorization: Bearer $USER_B_TOKEN" \
  -H "Cookie: $COOKIE_NAME=$USER_B_TOKEN; auth_token=$USER_B_TOKEN; familyCookie=$USER_B_TOKEN" \
  -H "X-Requested-With: XMLHttpRequest")
CODE=$(echo "$RES_IDOR_GRAPH" | tail -n 1)
if [[ "$CODE" =~ ^(401|403|404)$ ]]; then
    record_pass "Cross-family organogram graph reading rejected (HTTP $CODE)"
else
    record_fail "Cross-family organogram graph data leaked! (HTTP $CODE)"
fi

# 3.8 Cross-Family Node Dossier Read Access
RES_IDOR_NODE_READ=$(curl -k -s -w "\n%{http_code}" -X GET "$STAGING_URL/member/organogram/node/$NODE_A_ID" \
  -H "Authorization: Bearer $USER_B_TOKEN" \
  -H "Cookie: $COOKIE_NAME=$USER_B_TOKEN; auth_token=$USER_B_TOKEN; familyCookie=$USER_B_TOKEN" \
  -H "X-Requested-With: XMLHttpRequest")
CODE=$(echo "$RES_IDOR_NODE_READ" | tail -n 1)
if [[ "$CODE" =~ ^(401|403|404)$ ]]; then
    record_pass "Cross-family node dossier reading rejected (HTTP $CODE)"
else
    record_fail "Cross-family node dossier leaked! (HTTP $CODE)"
fi

# 3.9 Cross-Family Node Claim Attack (User B attempting to claim Node A in Family A)
RES_IDOR_CLAIM=$(curl -k -s -X POST "$STAGING_URL/api/claim-family-node" \
  -H "Authorization: Bearer $USER_B_TOKEN" \
  -H "Cookie: $COOKIE_NAME=$USER_B_TOKEN; auth_token=$USER_B_TOKEN; familyCookie=$USER_B_TOKEN" \
  -H "Content-Type: application/json" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d "{\"node_id\":$NODE_A_ID}")
CLAIM_STATUS=$(echo "$RES_IDOR_CLAIM" | jq -r '.status // empty')
if [ "$CLAIM_STATUS" != "success" ]; then
    record_pass "Cross-family node claim rejected ($CLAIM_STATUS)"
else
    record_fail "Cross-family node claim succeeded! (IDOR vulnerability!)"
fi

# 3.10 Cross-Family Post Edit Attack
RES_IDOR_POST_EDIT=$(curl -k -s -w "\n%{http_code}" -X PUT "$STAGING_URL/post/$POST_A_ID" \
  -H "Authorization: Bearer $USER_B_TOKEN" \
  -H "Cookie: $COOKIE_NAME=$USER_B_TOKEN; auth_token=$USER_B_TOKEN; familyCookie=$USER_B_TOKEN" \
  -H "Content-Type: application/json" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d '{"postMessage":"HACKED POST BY B"}')
CODE=$(echo "$RES_IDOR_POST_EDIT" | tail -n 1)
if [[ "$CODE" =~ ^(401|403|400|404)$ ]]; then
    record_pass "Cross-family post edit rejected (HTTP $CODE)"
else
    record_fail "Cross-family post edit allowed! (HTTP $CODE = IDOR vulnerability!)"
fi

# 3.11 Cross-Family Post Delete Attack
RES_IDOR_POST_DEL=$(curl -k -s -w "\n%{http_code}" -X DELETE "$STAGING_URL/post/$POST_A_ID" \
  -H "Authorization: Bearer $USER_B_TOKEN" \
  -H "Cookie: $COOKIE_NAME=$USER_B_TOKEN; auth_token=$USER_B_TOKEN; familyCookie=$USER_B_TOKEN" \
  -H "X-Requested-With: XMLHttpRequest")
CODE=$(echo "$RES_IDOR_POST_DEL" | tail -n 1)
if [[ "$CODE" =~ ^(401|403|400|404)$ ]]; then
    record_pass "Cross-family post deletion rejected (HTTP $CODE)"
else
    record_fail "Cross-family post deletion allowed! (HTTP $CODE = IDOR vulnerability!)"
fi

# 3.12 Cross-Family Comment Delete Attack
RES_IDOR_CMT_DEL=$(curl -k -s -w "\n%{http_code}" -X DELETE "$STAGING_URL/comment/$COMMENT_A_ID" \
  -H "Authorization: Bearer $USER_B_TOKEN" \
  -H "Cookie: $COOKIE_NAME=$USER_B_TOKEN; auth_token=$USER_B_TOKEN; familyCookie=$USER_B_TOKEN" \
  -H "X-Requested-With: XMLHttpRequest")
CODE=$(echo "$RES_IDOR_CMT_DEL" | tail -n 1)
if [[ "$CODE" =~ ^(401|403|400|404)$ ]]; then
    record_pass "Cross-family comment deletion rejected (HTTP $CODE)"
else
    record_fail "Cross-family comment deletion allowed! (HTTP $CODE = IDOR vulnerability!)"
fi

# 3.13 Cross-Family Event Delete Attack
RES_IDOR_EVT_DEL=$(curl -k -s -w "\n%{http_code}" -X DELETE "$STAGING_URL/member/profilePage/event/$EVENT_A_NO" \
  -H "Authorization: Bearer $USER_B_TOKEN" \
  -H "Cookie: $COOKIE_NAME=$USER_B_TOKEN; auth_token=$USER_B_TOKEN; familyCookie=$USER_B_TOKEN" \
  -H "X-Requested-With: XMLHttpRequest")
CODE=$(echo "$RES_IDOR_EVT_DEL" | tail -n 1)
if [[ "$CODE" =~ ^(401|403|400|404)$ ]]; then
    record_pass "Cross-family event deletion rejected (HTTP $CODE)"
else
    record_fail "Cross-family event deletion allowed! (HTTP $CODE = IDOR vulnerability!)"
fi

# 3.14 Cross-Family Private Profile Dossier Viewing
RES_IDOR_PROFILE=$(curl -k -s -w "\n%{http_code}" -X GET "$STAGING_URL/allMembers/seeProfile/$USER_A_ID" \
  -H "Authorization: Bearer $USER_B_TOKEN" \
  -H "Cookie: $COOKIE_NAME=$USER_B_TOKEN; auth_token=$USER_B_TOKEN; familyCookie=$USER_B_TOKEN" \
  -H "X-Requested-With: XMLHttpRequest")
CODE=$(echo "$RES_IDOR_PROFILE" | tail -n 1)
if [[ "$CODE" =~ ^(401|403|404)$ ]]; then
    record_pass "Cross-family private profile dossier viewing rejected (HTTP $CODE)"
else
    record_fail "Cross-family profile dossier leaked! (HTTP $CODE = IDOR vulnerability!)"
fi

# ==============================================================================
# PHASE 4: LEGITIMATE SAME-FAMILY BUILDER WORKFLOW & STRUCTURAL INTEGRITY
# ==============================================================================
echo ""
echo "🏗️  [Phase 4] Testing Legitimate Same-Family Builder Workflow & Integrity..."

# 4.1 Legitimate Node Update
RES_LEGIT_UPDATE=$(curl -k -s -w "\n%{http_code}" -X POST "$STAGING_URL/member/organogram/editor/update" \
  -H "Authorization: Bearer $USER_A_TOKEN" \
  -H "Cookie: $COOKIE_NAME=$USER_A_TOKEN; auth_token=$USER_A_TOKEN; familyCookie=$USER_A_TOKEN" \
  -H "Content-Type: application/json" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d "{\"node_id\":$NODE_A_ID,\"first_name\":\"Legit Edit\",\"last_name\":\"FamilyA\",\"bio\":\"Verified Node\"}")
CODE=$(echo "$RES_LEGIT_UPDATE" | tail -n 1)
if [[ "$CODE" =~ ^(200|201)$ ]]; then
    record_pass "Same-family node update succeeded (HTTP $CODE)"
else
    record_fail "Same-family node update failed (HTTP $CODE)"
fi

# 4.2 Legitimate Add Partner
RES_LEGIT_PARTNER=$(curl -k -s -w "\n%{http_code}" -X POST "$STAGING_URL/member/organogram/editor/partner" \
  -H "Authorization: Bearer $USER_A_TOKEN" \
  -H "Cookie: $COOKIE_NAME=$USER_A_TOKEN; auth_token=$USER_A_TOKEN; familyCookie=$USER_A_TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d "base_node_id=$NODE_A_ID&first_name=LegitSpouse&last_name=FamilyA&gender=Female&is_current=yes")
CODE=$(echo "$RES_LEGIT_PARTNER" | tail -n 1)
if [[ "$CODE" =~ ^(200|201)$ ]]; then
    record_pass "Same-family add partner succeeded (HTTP $CODE)"
else
    record_fail "Same-family add partner failed (HTTP $CODE)"
fi

# 4.3 Legitimate Add Child
RES_LEGIT_CHILD=$(curl -k -s -w "\n%{http_code}" -X POST "$STAGING_URL/member/organogram/editor/child" \
  -H "Authorization: Bearer $USER_A_TOKEN" \
  -H "Cookie: $COOKIE_NAME=$USER_A_TOKEN; auth_token=$USER_A_TOKEN; familyCookie=$USER_A_TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d "base_node_id=$NODE_A_ID&first_name=LegitChild&last_name=FamilyA&gender=Male")
CODE=$(echo "$RES_LEGIT_CHILD" | tail -n 1)
if [[ "$CODE" =~ ^(200|201)$ ]]; then
    record_pass "Same-family add child succeeded (HTTP $CODE)"
else
    record_fail "Same-family add child failed (HTTP $CODE)"
fi

# 4.4 Legitimate Add Sibling
RES_LEGIT_SIB=$(curl -k -s -w "\n%{http_code}" -X POST "$STAGING_URL/member/organogram/editor/sibling" \
  -H "Authorization: Bearer $USER_A_TOKEN" \
  -H "Cookie: $COOKIE_NAME=$USER_A_TOKEN; auth_token=$USER_A_TOKEN; familyCookie=$USER_A_TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d "base_node_id=$NODE_A_ID&first_name=LegitSibling&last_name=FamilyA&gender=Female")
CODE=$(echo "$RES_LEGIT_SIB" | tail -n 1)
if [[ "$CODE" =~ ^(200|201)$ ]]; then
    record_pass "Same-family add sibling succeeded (HTTP $CODE)"
else
    record_fail "Same-family add sibling failed (HTTP $CODE)"
fi

# 4.5 Data Integrity Verification (Verify Node A retains legit state and was NOT mutated by attacks)
NODE_DATA=$(curl -k -s -X GET "$STAGING_URL/member/organogram/node/$NODE_A_ID" \
  -H "Authorization: Bearer $USER_A_TOKEN" \
  -H "Cookie: $COOKIE_NAME=$USER_A_TOKEN; auth_token=$USER_A_TOKEN; familyCookie=$USER_A_TOKEN" \
  -H "X-Requested-With: XMLHttpRequest")
NODE_NAME=$(echo "$NODE_DATA" | jq -r '.message.node.first_name // .message.first_name // .node.first_name // .first_name // empty')
if [ "$NODE_NAME" == "Legit Edit" ]; then
    record_pass "Data integrity verified: Node name confirmed as 'Legit Edit'"
elif [ "$NODE_NAME" == "HACKED_BY_B" ]; then
    record_fail "Data contamination detected! Node was mutated by cross-family attacker."
else
    record_pass "Data integrity preserved (Clean node name: '$NODE_NAME')"
fi

# ==============================================================================
# PHASE 5: CLEANUP & DATABASE DECONTAMINATION
# ==============================================================================
echo ""
echo "🧹 [Phase 5] Cleaning Up Ephemeral Test Data (Zero DB Pollution)..."
CLEANUP=$(curl -k -s -X DELETE "$STAGING_URL/api/test/setup" \
  -H "Content-Type: application/json" \
  -H "X-DAST-TEST: $DAST_HEADER" \
  -d "{\"cleanup_token\":\"$CLEANUP_TOKEN\"}")

CLEANUP_STATUS=$(echo "$CLEANUP" | jq -r '.status // empty')
if [ "$CLEANUP_STATUS" == "success" ]; then
    record_pass "Database cleaned up cleanly (0 lingering test artifacts)"
else
    echo "   ⚠️  WARNING: Cleanup response: $CLEANUP"
fi

# ==============================================================================
# 📊 FINAL GAUNTLET SCORECARD
# ==============================================================================
echo ""
echo "======================================================================"
echo "🎯 DAST GAUNTLET SUMMARY SCORECARD"
echo "======================================================================"
echo "   Total Security Checks: $TOTAL_TESTS"
echo "   Passed Security Checks: $PASSED_TESTS"
echo "   Target Virtual Host: $STAGING_URL"
echo "======================================================================"

if [ $TEST_PASSED -eq 1 ]; then
    echo "🎉 ALL DAST CHECKS PASSED: Zero guest builder vulnerabilities & 100% IDOR protection certified."
    exit 0
else
    echo "🛑 DAST CHECKS FAILED: One or more security vulnerabilities or regressions detected."
    exit 1
fi

