<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\services\FamilyRelationshipRequestService as R;
use App\services\MemberLookupService as M;
use Tests\Support\OrganogramTestCase;

/**
 * "Already a registered member?" lookup while adding a relative, and linking the card to that account.
 */
final class MemberLookupLinkTest extends OrganogramTestCase
{
    protected function tearDown(): void
    {
        $this->pdo->prepare('DELETE FROM family_relationship_requests WHERE family_code = ?')->execute([$this->famCode]);
        parent::tearDown();
    }

    public function testTokenIsBoundToViewerAndExpires(): void
    {
        $t = M::makeToken('viewer-1', 'member-9', 1000);
        $this->assertSame('member-9', M::verifyToken('viewer-1', $t, 1500));
        $this->assertNull(M::verifyToken('viewer-2', $t, 1500), 'another viewer cannot redeem it');
        $this->assertNull(M::verifyToken('viewer-1', $t, 1000 + 901), 'expired');
        $this->assertNull(M::verifyToken('viewer-1', $t . 'x', 1500), 'tampered signature');
        $this->assertNull(M::verifyToken('viewer-1', 'garbage', 1500));
        $forged = rtrim(strtr(base64_encode('viewer-1|admin|' . (time() + 9999)), '+/', '-_'), '=') . '.' . hash('sha256', 'guess');
        $this->assertNull(M::verifyToken('viewer-1', $forged));
    }

    public function testMaskHidesMostOfTheName(): void
    {
        $this->assertSame('Wa***', M::mask('Wally'));
        $this->assertSame('A member', M::mask(''));
        $this->assertStringNotContainsString('lly', M::mask('Wally'));
    }

    public function testLookupFindsRegisteredEmailWithoutLeakingIdentity(): void
    {
        $email = (string)$this->pdo->query("SELECT email FROM account WHERE id = '34747TEST' LIMIT 1")->fetchColumn();
        $this->assertNotSame('', $email);
        $res = M::lookup($this->pdo, 'someone-else', $this->famCode, strtoupper($email), '');
        $this->assertTrue($res['found']);
        $this->assertFalse($res['same_family']);
        $this->assertArrayNotHasKey('id', $res);
        $this->assertArrayNotHasKey('family_code', $res);
        $this->assertStringNotContainsString('34747TEST', json_encode($res));
        $this->assertSame('34747TEST', M::verifyToken('someone-else', $res['link_token']));
    }

    public function testLookupDoesNotMatchTheViewerOrJunk(): void
    {
        $email = (string)$this->pdo->query("SELECT email FROM account WHERE id = '34747TEST' LIMIT 1")->fetchColumn();
        $this->assertFalse(M::lookup($this->pdo, '34747TEST', $this->famCode, $email, '')['found'], 'you cannot look yourself up');
        $this->assertFalse(M::lookup($this->pdo, 'v', $this->famCode, 'nobody-' . bin2hex(random_bytes(6)) . '@example.invalid', '')['found']);
        $this->assertFalse(M::lookup($this->pdo, 'v', $this->famCode, "a' OR '1'='1", '')['found']);
        $this->assertFalse(M::lookup($this->pdo, 'v', $this->famCode, 'ab', '12')['found']);
    }

    public function testLinkRequestApprovalClaimsTheCardForThatMember(): void
    {
        $node = $this->seedNode('Wally', 'Relative', 'Male', 0);
        $made = R::createLinkRequest($this->pdo, $this->famCode, 'requester-x', $node, 'member-' . $this->famCode);
        $this->assertCount(1, $made);
        $this->assertSame([], R::createLinkRequest($this->pdo, $this->famCode, 'requester-x', $node, 'member-' . $this->famCode), 'no duplicate request');

        $this->assertFalse(R::resolve($this->pdo, $made[0]['id'], 'intruder', true)['ok']);
        $this->assertNull($this->nodeById($node)['user_id']);

        $res = R::resolve($this->pdo, $made[0]['id'], 'member-' . $this->famCode, true);
        $this->assertTrue($res['ok'], $res['message']);
        $this->assertSame('member-' . $this->famCode, $this->nodeById($node)['user_id']);
    }

    public function testDecliningALinkKeepsTheRelativeOnTheTree(): void
    {
        $node = $this->seedNode('Wally', 'Relative', 'Male', 0);
        $made = R::createLinkRequest($this->pdo, $this->famCode, 'requester-x', $node, 'member-' . $this->famCode);
        $this->assertSame('declined', R::resolve($this->pdo, $made[0]['id'], 'member-' . $this->famCode, false)['status']);
        $this->assertNotNull($this->nodeById($node));
        $this->assertNull($this->nodeById($node)['user_id']);
    }

    public function testLinkRefusesBadTokenAndClaimedNodes(): void
    {
        $node = $this->seedNode('Wally', 'Relative', 'Male', 0);
        $this->assertFalse(M::link($this->pdo, $this->famCode, 'viewer-1', 'Viewer', $node, 'bad')['ok']);
        $claimed = $this->seedNode('Claimed', 'Person', 'Male', 0, 'someone-' . $this->famCode);
        $tok = M::makeToken('viewer-1', 'member-z');
        $this->assertFalse(M::link($this->pdo, $this->famCode, 'viewer-1', 'Viewer', $claimed, $tok)['ok']);
        $this->assertFalse(M::link($this->pdo, 'OTHER_FAMILY', 'viewer-1', 'Viewer', $node, $tok)['ok'], 'node of another family');
    }
}
