<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\services\ContentSafetyService;
use PHPUnit\Framework\TestCase;
use Src\Exceptions\ValidationException;

final class ContentSafetyServiceTest extends TestCase
{
    public function testTier1RacistSlursAreBlockedAcrossAllEvasionVectors(): void
    {
        $payloads = [
            'You are a nigger',
            'Total n1gg3r behaviour',
            "Hidden zero-width n\u{200B}i\u{200C}g\u{200D}g\u{FEFF}e\u{200B}r slur",
            'Cyrillic homoglyph nіggеr attack', // Cyrillic і and е
            'Dotted evasion n.i.g.g.e.r in post',
            'Spaced evasion n i g g e r in comment',
            'Asterisk masked n*gger slur',
            'Stretched niiiiiigger slur',
            'Racist kike and chink slurs',
            'Racist wetback and raghead slurs',
            'White power and sieg heil extremist phrases',
        ];

        foreach ($payloads as $payload) {
            $result = ContentSafetyService::inspectText($payload, 'post');
            $this->assertFalse($result['is_clean'], "Expected racist/extremist payload to be blocked: {$payload}");
            $this->assertSame(ContentSafetyService::TIER_1_HATE_SPEECH, $result['tier']);
            $this->assertSame(ContentSafetyService::CATEGORY_RACIAL_HATE, $result['category']);
        }
    }

    public function testTier1HomophobicAndTransphobicSlursAreBlocked(): void
    {
        $payloads = [
            'He is a faggot',
            'Leetspeak f@gg0t evasion',
            "Zero-width f\u{200B}аgg0t with Cyrillic a",
            'Dotted f.a.g.g.o.t evasion',
            'Asterisk f*ggot evasion',
            'Homophobic batty boy and chi chi man slurs',
            'Transphobic tranny and shemale slurs',
            'Homophobic dyke and carpet muncher slurs',
        ];

        foreach ($payloads as $payload) {
            $result = ContentSafetyService::inspectText($payload, 'comment');
            $this->assertFalse($result['is_clean'], "Expected homophobic/transphobic payload to be blocked: {$payload}");
            $this->assertSame(ContentSafetyService::TIER_1_HATE_SPEECH, $result['tier']);
            $this->assertSame(ContentSafetyService::CATEGORY_HOMOPHOBIC_HATE, $result['category']);
        }
    }

    public function testTier2SevereProfanityAndAbuseAreBlocked(): void
    {
        $payloads = [
            'What the fuck is this',
            'Stretched fuuuuucking nonsense',
            'Leetspeak sh1t and b!tch and a$$hole',
            'Asterisk f*ck and sh*t and c*nt',
            'Dotted f.u.c.k and b-i-t-c-h',
            'You absolute motherfucker and bellend',
            'Total wanker and dickhead',
            'Piece of shit comment',
        ];

        foreach ($payloads as $payload) {
            $result = ContentSafetyService::inspectText($payload, 'comment');
            $this->assertFalse($result['is_clean'], "Expected severe profanity payload to be blocked: {$payload}");
            $this->assertSame(ContentSafetyService::TIER_2_SEVERE_PROFANITY, $result['tier']);
            $this->assertSame(ContentSafetyService::CATEGORY_SEVERE_PROFANITY, $result['category']);
        }
    }

    public function testScunthorpeSafeWordAllowlistPreventsFalsePositives(): void
    {
        $benignFamilyMessages = [
            'We are driving through Scunthorpe and Penistone on our way to Cockfosters!',
            'Uncle Richard Dickson and Aunt Michelle Hitchcock visited Essex and Sussex.',
            'Grandma made delicious shiitake mushroom soup with cucumber salad.',
            'I mishit the golf ball into the grass near the peacock!',
            'Cousin Tobi is travelling from Nigeria and Niger to Pakistan next month.',
            'My physiotherapist and psychotherapist recommended a document analysis.',
            'The kitchen was spick and span before the family reunion in the 1990s.',
            'Watch this family video: https://www.youtube.com/watch?v=fuk123cunt99',
            'Pass the glass of water to the passenger in class.',
        ];

        foreach ($benignFamilyMessages as $message) {
            $result = ContentSafetyService::inspectText($message, 'post');
            $this->assertTrue($result['is_clean'], "False positive triggered on benign family text: {$message}");
            $this->assertNull($result['message']);
        }
    }

    public function testAssertCleanPayloadBlocksNestedPollOptions(): void
    {
        $this->expectException(ValidationException::class);
        ContentSafetyService::assertCleanPayload([
            'post' => 'Family Sunday Poll',
            'poll question' => 'Where should we meet?',
            'poll option' => [
                'Hyde Park',
                'You f@gg0t',
            ],
        ], 'test_suite');
    }

    public function testGdprRedactionMasksMatchedWords(): void
    {
        $this->assertSame('f***', ContentSafetyService::redactWord('fuck'));
        $this->assertSame('n*****', ContentSafetyService::redactWord('nigger'));
    }
}
