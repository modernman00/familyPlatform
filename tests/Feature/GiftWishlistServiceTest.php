<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\services\GiftWishlistService;
use InvalidArgumentException;
use PDO;
use PHPUnit\Framework\TestCase;
use Src\Db;

class GiftWishlistServiceTest extends TestCase
{
    private PDO $pdo;
    private GiftWishlistService $service;
    private string $famCode = 'FAM_TEST01';
    private string $otherFamCode = 'FAM_TEST02';

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = Db::connect2();
        $this->service = new GiftWishlistService($this->pdo);
        $this->cleanup();
    }

    protected function tearDown(): void
    {
        $this->cleanup();
        parent::tearDown();
    }

    private function cleanup(): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM family_gift_wishlists WHERE family_code IN (?, ?)');
        $stmt->execute([$this->famCode, $this->otherFamCode]);
    }

    public function testPinnedWishlistPersistsAtTopUntilExpiry(): void
    {
        $expired = $this->service->createWishlist('usr_1', 'Alex', $this->famCode, [
            'occasion' => 'Birthday',
            'title' => 'Old Birthday List',
            'pin_days' => 7,
            'items' => [['item_title' => 'Mug']],
        ]);
        $upd = $this->pdo->prepare('UPDATE family_gift_wishlists SET pinned_until = DATE_SUB(NOW(), INTERVAL 1 DAY) WHERE id = ?');
        $upd->execute([(int) $expired['id']]);

        $pinned = $this->service->createWishlist('usr_2', 'Jordan', $this->famCode, [
            'occasion' => 'Christmas',
            'title' => 'Jordan Christmas List',
            'pin_days' => 30,
            'items' => [
                ['item_title' => 'Kindle', 'item_url' => 'https://amazon.co.uk/dp/test', 'approx_price' => 129.99],
                ['item_title' => 'Bad Link', 'item_url' => 'javascript:alert(1)'],
            ],
        ]);

        $this->assertNull($pinned['items'][1]['item_url']);

        $list = $this->service->listWishlistsForFamilyCodes([$this->famCode]);
        $this->assertCount(2, $list);
        $this->assertSame((int) $pinned['id'], (int) $list[0]['id']);
        $this->assertTrue($list[0]['is_pinned']);
        $this->assertGreaterThanOrEqual(29, $list[0]['days_remaining']);
        $this->assertFalse($list[1]['is_pinned']);
    }

    public function testCrossFamilyClaimIsBlockedAndAtomicClaimWorks(): void
    {
        $wl = $this->service->createWishlist('usr_1', 'Maya', $this->famCode, [
            'occasion' => 'Christmas',
            'pin_days' => 14,
            'items' => [['item_title' => 'Scarf', 'approx_price' => 35]],
        ]);
        $list = $this->service->listWishlistsForFamilyCodes([$this->famCode]);
        $itemId = (int) $list[0]['items'][0]['id'];

        $claimed = $this->service->claimItem($itemId, [$this->famCode], 'usr_3', 'Sam', false);
        $this->assertSame('Sam', $claimed['claimed_by_name']);

        $this->expectException(InvalidArgumentException::class);
        $this->service->claimItem($itemId, [$this->otherFamCode], 'usr_4', 'Intruder', false);
    }

    public function testDeleteRequiresAuthor(): void
    {
        $wl = $this->service->createWishlist('usr_1', 'Chloe', $this->famCode, [
            'occasion' => 'Birthday',
            'pin_days' => 7,
            'items' => [['item_title' => 'Candle']],
        ]);
        $id = (int) $wl['id'];

        $this->assertFalse($this->service->deleteWishlist($id, 'usr_other'));
        $this->assertTrue($this->service->deleteWishlist($id, 'usr_1'));
    }
}
