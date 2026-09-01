<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\services\FamilyClaimService;
use Tests\Support\OrganogramTestCase;

/**
 * Covers FamilyClaimService — the tree-side of registration: turning a brand
 * new member into a family_nodes row, auto-claiming an unclaimed placeholder
 * that already represents them, and the HMAC-signed invite-token round trip
 * that Register::index() consumes from an ?invite_token deep link.
 *
 * Reuses OrganogramTestCase for its family_nodes / family_unions /
 * family_node_children fixture cleanup.
 */
final class RegistrationClaimTest extends OrganogramTestCase
{
    // ---- claimOrInitializeNode -----------------------------------------

    public function test_creates_a_root_node_when_the_family_tree_is_empty(): void
    {
        $userId = 'PU_REG_' . bin2hex(random_bytes(4));

        $nodeId = FamilyClaimService::claimOrInitializeNode($this->famCode, $userId, [
            'firstName' => 'Grace',
            'lastName' => 'Hopper',
            'email' => 'Grace.Hopper@Example.com',
            'gender' => 'Female',
        ]);

        $this->assertGreaterThan(0, $nodeId);
        $node = $this->nodeById($nodeId);
        $this->assertSame($userId, $node['user_id']);
        $this->assertSame('Grace', $node['first_name']);
        $this->assertSame('grace.hopper@example.com', $node['email'], 'Email is normalised to lower case.');
        $this->assertSame(0, (int) $node['generation_level'], 'The registering member is generation 0.');
    }

    public function test_is_idempotent_when_the_member_already_has_a_node(): void
    {
        $userId = 'PU_REG_' . bin2hex(random_bytes(4));
        $data = ['firstName' => 'Ada', 'lastName' => 'Byron'];

        $first = FamilyClaimService::claimOrInitializeNode($this->famCode, $userId, $data);
        $second = FamilyClaimService::claimOrInitializeNode($this->famCode, $userId, $data);

        $this->assertSame($first, $second, 'A second call returns the same node, not a duplicate.');
        $this->assertCount(1, $this->familyNodes());
    }

    public function test_auto_claims_an_unclaimed_placeholder_that_matches_on_email(): void
    {
        // A placeholder someone else added to the tree, not yet linked to an account.
        $placeholderId = $this->seedNode('Someone', 'Else', 'Female', 0);
        $this->pdo->prepare('UPDATE family_nodes SET email = ? WHERE id = ?')
            ->execute(['known@example.com', $placeholderId]);

        $userId = 'PU_REG_' . bin2hex(random_bytes(4));
        $nodeId = FamilyClaimService::claimOrInitializeNode($this->famCode, $userId, [
            'firstName' => 'Real',
            'lastName' => 'Name',
            'email' => 'KNOWN@example.com',
        ]);

        $this->assertSame($placeholderId, $nodeId, 'Registration links onto the existing placeholder.');
        $this->assertSame($userId, $this->nodeById($placeholderId)['user_id']);
        $this->assertCount(1, $this->familyNodes(), 'No new node was created.');
    }

    public function test_auto_claims_a_placeholder_that_matches_on_first_and_last_name(): void
    {
        $placeholderId = $this->seedNode('john', 'DOE', 'Male', 2);

        $userId = 'PU_REG_' . bin2hex(random_bytes(4));
        $nodeId = FamilyClaimService::claimOrInitializeNode($this->famCode, $userId, [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'jdoe@example.com',
        ]);

        $this->assertSame($placeholderId, $nodeId);
        $this->assertSame($userId, $this->nodeById($placeholderId)['user_id']);
    }

    public function test_explicit_claim_node_id_wins_over_fuzzy_matching(): void
    {
        $target = $this->seedNode('Placeholder', 'Person', 'Male', 1);

        $userId = 'PU_REG_' . bin2hex(random_bytes(4));
        $nodeId = FamilyClaimService::claimOrInitializeNode(
            $this->famCode,
            $userId,
            ['firstName' => 'Deep', 'lastName' => 'Link', 'email' => 'deep@example.com'],
            $target,
        );

        $this->assertSame($target, $nodeId);
        $node = $this->nodeById($target);
        $this->assertSame($userId, $node['user_id']);
        $this->assertSame('Deep', $node['first_name'], 'Blank-guarded fields are filled from the registration.');
    }

    public function test_creates_parent_placeholders_and_a_union_when_parent_names_are_supplied(): void
    {
        $userId = 'PU_REG_' . bin2hex(random_bytes(4));

        $rootId = FamilyClaimService::claimOrInitializeNode($this->famCode, $userId, [
            'firstName' => 'Sam',
            'lastName' => 'Carter',
            'father_name' => 'George Carter',
            'mother_name' => 'Mary Carter',
        ]);

        $names = array_map(static fn ($n) => $n['first_name'] . ' ' . $n['last_name'], $this->familyNodes());
        $this->assertContains('George Carter', $names);
        $this->assertContains('Mary Carter', $names);

        $union = $this->latestUnion();
        $this->assertNotNull($union);
        $links = $this->childrenOfUnion((int) $union['id']);
        $this->assertCount(1, $links);
        $this->assertSame($rootId, (int) $links[0]['child_id'], 'The new member is linked in as the parents\' child.');
    }

    // ---- signed invite token round trip ------------------------------

    public function test_a_freshly_signed_invite_token_verifies_back_to_its_payload(): void
    {
        $nodeId = $this->seedNode('Invited', 'Cousin', 'Female', 0);

        $token = FamilyClaimService::generateSignedInviteToken(
            $nodeId,
            $this->famCode,
            'Invited.Cousin@Example.com',
            'Invited',
            'Cousin',
        );

        $data = FamilyClaimService::verifySignedInviteToken($token);

        $this->assertIsArray($data);
        $this->assertSame($nodeId, $data['node_id']);
        $this->assertSame($this->famCode, $data['family_code']);
        $this->assertSame('invited.cousin@example.com', $data['email']);
    }

    public function test_a_tampered_invite_token_is_rejected(): void
    {
        $nodeId = $this->seedNode();
        $token = FamilyClaimService::generateSignedInviteToken($nodeId, $this->famCode, 'a@b.com', 'A', 'B');

        [$b64] = explode('.', $token, 2);
        $forged = $b64 . '.' . str_repeat('0', 64);

        $this->assertNull(FamilyClaimService::verifySignedInviteToken($forged));
        $this->assertNull(FamilyClaimService::verifySignedInviteToken('not-a-token'));
    }

    public function test_an_invite_token_for_an_already_claimed_node_is_rejected(): void
    {
        $nodeId = $this->seedNode('Taken', 'Node', 'Male', 0);
        $token = FamilyClaimService::generateSignedInviteToken($nodeId, $this->famCode, 'c@d.com', 'C', 'D');

        // Someone registers and claims the node before the invite is opened.
        $this->pdo->prepare('UPDATE family_nodes SET user_id = ? WHERE id = ?')
            ->execute(['PU_ALREADY', $nodeId]);

        $this->assertNull(FamilyClaimService::verifySignedInviteToken($token));
    }

    public function test_an_expired_invite_token_is_rejected(): void
    {
        $nodeId = $this->seedNode();
        $token = FamilyClaimService::generateSignedInviteToken(
            $nodeId,
            $this->famCode,
            'e@f.com',
            'E',
            'F',
            ttlSeconds: -10, // already in the past
        );

        $this->assertNull(FamilyClaimService::verifySignedInviteToken($token));
    }

    // ---- claimNodeById ----------------------------------------------

    public function test_claim_node_by_id_links_an_unclaimed_node(): void
    {
        $nodeId = $this->seedNode('Free', 'Node', 'Female', 0);
        $userId = 'PU_REG_' . bin2hex(random_bytes(4));

        $this->assertTrue(FamilyClaimService::claimNodeById($nodeId, $userId, $this->famCode));
        $this->assertSame($userId, $this->nodeById($nodeId)['user_id']);
    }

    public function test_claim_node_by_id_fails_for_a_node_someone_else_already_holds(): void
    {
        $nodeId = $this->seedNode('Held', 'Node', 'Male', 0);
        $this->pdo->prepare('UPDATE family_nodes SET user_id = ? WHERE id = ?')->execute(['PU_OWNER', $nodeId]);

        $this->assertFalse(FamilyClaimService::claimNodeById($nodeId, 'PU_INTRUDER', $this->famCode));
        $this->assertSame('PU_OWNER', $this->nodeById($nodeId)['user_id']);
    }

    // ---- findFuzzyUnclaimedMatches ---------------------------------

    public function test_fuzzy_match_surfaces_an_unclaimed_relative_by_surname(): void
    {
        $this->seedNode('Uncle', 'Stark', 'Male', 0);
        $userId = 'PU_REG_' . bin2hex(random_bytes(4));

        $match = FamilyClaimService::findFuzzyUnclaimedMatches($this->famCode, $userId, [
            'firstName' => 'Tony',
            'lastName' => 'Stark',
        ]);

        $this->assertIsArray($match);
        $this->assertSame('Uncle Stark', $match['full_name']);
    }

    public function test_fuzzy_match_returns_null_once_the_member_has_claimed_a_node(): void
    {
        $claimed = $this->seedNode('Me', 'Stark', 'Male', 0);
        $userId = 'PU_REG_' . bin2hex(random_bytes(4));
        $this->pdo->prepare('UPDATE family_nodes SET user_id = ? WHERE id = ?')->execute([$userId, $claimed]);
        $this->seedNode('Cousin', 'Stark', 'Female', 0);

        $this->assertNull(FamilyClaimService::findFuzzyUnclaimedMatches($this->famCode, $userId, [
            'firstName' => 'Me',
            'lastName' => 'Stark',
        ]));
    }
}
