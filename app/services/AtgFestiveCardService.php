<?php

declare(strict_types=1);

namespace App\services;

use PDO;
use Src\Db;
use RuntimeException;
use InvalidArgumentException;

/**
 * AtgFestiveCardService — Sovereign Omni-Occasion Festive Card & Living Envelope Engine
 *
 * Authored by: Advanced Technology Group (ATG Pods 3 & 4)
 * Governed by: TAT 2.0 / Technical Approval Team (Olutobi & Victor)
 * Compliant with: Universal British English, Zero SQL Interpolation, Safe Touch Targets
 */
final class AtgFestiveCardService
{
    private PDO $db;

    public const OCCASIONS = [
        'christmas' => [
            'key' => 'christmas',
            'title' => 'Christmas & Boxing Day',
            'icon' => 'bi-tree-fill',
            'default_theme' => 'royal_crimson',
            'particle' => 'snow',
            'badge' => 'Festive Season',
            'default_headline' => 'Merry Christmas & Joyous Festivities',
            'default_verse' => 'Wishing our dearest family and friends a season filled with warmth, radiant laughter, and treasured moments together.',
            'default_sign_off' => 'With all our love,',
            'sample_verses' => [
                'May the peace and splendour of Christmas illuminate your home with joy and prosperity throughout the coming year.',
                'Sending warmest holiday blessings across the miles. May your festive season be wrapped in joy and filled with family love.',
                'From our hearth to yours: wishing you a Christmas filled with heartwarming memories, festive delights, and endless celebration.',
            ],
        ],
        'new_year' => [
            'key' => 'new_year',
            'title' => 'New Year’s Celebration',
            'icon' => 'bi-stars',
            'default_theme' => 'gold_champagne',
            'particle' => 'confetti',
            'badge' => 'New Beginnings',
            'default_headline' => 'Happy New Year & Bright New Horizons',
            'default_verse' => 'Here is to twelve fresh chapters of health, boundless joy, and extraordinary family triumphs in the New Year.',
            'default_sign_off' => 'Warmest new year wishes,',
            'sample_verses' => [
                'May the dawn of this New Year bring soaring prosperity, resilient good health, and joyful reunions to you and yours.',
                'Embracing 365 new opportunities to love, celebrate, and create unforgettable family legacies together. Happy New Year!',
                'Cheers to fresh beginnings, cherished milestones, and lifelong kinship. May the New Year exceed your highest aspirations.',
            ],
        ],
        'easter' => [
            'key' => 'easter',
            'title' => 'Easter & Spring Renewal',
            'icon' => 'bi-flower1',
            'default_theme' => 'pastel_spring',
            'particle' => 'petals',
            'badge' => 'Spring Joy',
            'default_headline' => 'Blessed & Joyous Easter Wishes',
            'default_verse' => 'May the renewing grace and sunshine of Easter fill your hearts with hope, peace, and restorative blessings.',
            'default_sign_off' => 'With Easter blessings,',
            'sample_verses' => [
                'Wishing you a season of renewed faith, blooming spring joy, and sweet celebration with all those you hold dear.',
                'May the vibrant promise of springtime and the timeless wonder of Easter bring continuous abundance into your lives.',
                'Rejoicing in this season of new beginnings, family feasts, and cherished togetherness. Have a truly wonderful Easter.',
            ],
        ],
        'eid' => [
            'key' => 'eid',
            'title' => 'Eid Mubarak (al-Fitr & al-Adha)',
            'icon' => 'bi-moon-stars-fill',
            'default_theme' => 'celestial_navy',
            'particle' => 'stardust',
            'badge' => 'Eid Mubarak',
            'default_headline' => 'Eid Mubarak & Sacred Blessings',
            'default_verse' => 'May the divine blessings of Eid bestow endless happiness, tranquility, and harmony upon your home and loved ones.',
            'default_sign_off' => 'Eid Mubarak from,',
            'sample_verses' => [
                'Wishing you and your family a blessed Eid filled with joyous feastings, spiritual fulfillment, and deep gratitude.',
                'On this auspicious occasion, may your prayers be answered and your home enriched with enduring peace and warmth.',
                'May the grace of this sacred day bring you and your kin closer together in celebration, charity, and enduring love.',
            ],
        ],
        'diwali' => [
            'key' => 'diwali',
            'title' => 'Diwali (Festival of Lights)',
            'icon' => 'bi-brightness-high-fill',
            'default_theme' => 'autumn_harvest',
            'particle' => 'sparks',
            'badge' => 'Shubh Diwali',
            'default_headline' => 'Shubh Diwali & Radiant Prosperity',
            'default_verse' => 'May the golden light of the diyas banish all darkness, blessing your path with wisdom, good health, and glorious prosperity.',
            'default_sign_off' => 'Warmest Diwali greetings,',
            'sample_verses' => [
                'Wishing you a dazzling Diwali filled with luminous celebrations, delicious sweets, and lifelong family happiness.',
                'May this Festival of Lights illuminate your future and usher in a year brimming with boundless joy and success.',
                'Sending heartwarming wishes of illumination, triumph, and togetherness as we celebrate the sacred spirit of Diwali.',
            ],
        ],
        'thanksgiving' => [
            'key' => 'thanksgiving',
            'title' => 'Thanksgiving & Harvest Gratitude',
            'icon' => 'bi-cup-hot-fill',
            'default_theme' => 'autumn_harvest',
            'particle' => 'leaves',
            'badge' => 'Gratitude',
            'default_headline' => 'Happy Thanksgiving & Abundant Blessings',
            'default_verse' => 'Reflecting with grateful hearts on the gift of family, the joy of companionship, and the bountiful blessings of this year.',
            'default_sign_off' => 'With deepest gratitude,',
            'sample_verses' => [
                'Countless blessings, bountiful tables, and cherished kinship. Wishing you a truly heartwarming and restful Thanksgiving.',
                'May your Thanksgiving table be filled with delightful food and surrounded by the warmest family laughter.',
                'In this harvest season, we hold our kin and friends in deepest appreciation. Thank you for being an indispensable part of our lives.',
            ],
        ],
        'mothers_day' => [
            'key' => 'mothers_day',
            'title' => 'Mother’s Day Honour',
            'icon' => 'bi-heart-fill',
            'default_theme' => 'pastel_spring',
            'particle' => 'petals',
            'badge' => 'Honouring Mothers',
            'default_headline' => 'Happy Mother’s Day with Boundless Love',
            'default_verse' => 'Celebrating the guiding light, boundless sacrifices, and enduring grace of our cherished mothers and matriarchs.',
            'default_sign_off' => 'Forever grateful,',
            'sample_verses' => [
                'Thank you for being the heart and soul of our family. Your unconditional love shapes generations.',
                'To the world you are one person, but to our family you are the world. Happy Mother’s Day!',
                'Wishing a serene and celebratory Mother’s Day to an extraordinary mother who inspires us every single day.',
            ],
        ],
        'fathers_day' => [
            'key' => 'fathers_day',
            'title' => 'Father’s Day Honour',
            'icon' => 'bi-shield-fill-check',
            'default_theme' => 'celestial_navy',
            'particle' => 'confetti',
            'badge' => 'Honouring Fathers',
            'default_headline' => 'Happy Father’s Day with Deep Gratitude',
            'default_verse' => 'Honouring the pillars of strength, wisdom, and protective shelter who guide our family dynasty forward.',
            'default_sign_off' => 'With respect and love,',
            'sample_verses' => [
                'Thank you for your tireless dedication, patient wisdom, and steady guidance through every season of life.',
                'To our family’s great champion and protector: wishing you a restful and joyful Father’s Day.',
                'Your resilience and honour illuminate our heritage. Happy Father’s Day to our family rock!',
            ],
        ],
        'reunion' => [
            'key' => 'reunion',
            'title' => 'Annual Family Gathering & Reunion',
            'icon' => 'bi-people-fill',
            'default_theme' => 'gold_champagne',
            'particle' => 'confetti',
            'badge' => 'Clan Reunion',
            'default_headline' => 'Welcome to Our Family Reunion',
            'default_verse' => 'Uniting across oceans, generations, and branches to celebrate the unbreakable bonds of our shared ancestral lineage.',
            'default_sign_off' => 'In celebration of our clan,',
            'sample_verses' => [
                'A single tree with many blossoming branches. Reunited in love, laughter, and lifelong heritage.',
                'Bridging past traditions with bright tomorrows. Welcome home to our beloved family reunion!',
                'Distance cannot sever what blood and devotion have woven. Together again in joy and solidarity.',
            ],
        ],
        'milestone' => [
            'key' => 'milestone',
            'title' => 'Family Milestone & Anniversary',
            'icon' => 'bi-trophy-fill',
            'default_theme' => 'royal_crimson',
            'particle' => 'confetti',
            'badge' => 'Dynasty Milestone',
            'default_headline' => 'Celebrating Our Extraordinary Milestone',
            'default_verse' => 'Honouring a momentous chapter of endurance, achievement, and triumph in our family’s enduring history.',
            'default_sign_off' => 'Celebrating together,',
            'sample_verses' => [
                'Years of love, resilience, and mutual devotion. Here is to celebrating our golden anniversary in style!',
                'Reaching this summit with dignity, perseverance, and family pride. Congratulations on this milestone!',
                'Every achievement is sweeter when shared with the ones who believed in us from the very beginning.',
            ],
        ],
    ];

    public const THEMES = [
        'royal_crimson' => [
            'key' => 'royal_crimson',
            'label' => 'Royal Crimson & Gold',
            'primary_color' => '#881337',
            'accent_color' => '#f59e0b',
            'gradient' => 'linear-gradient(135deg, #1e0910 0%, #4c0519 50%, #881337 100%)',
            'card_border' => '1.5px solid rgba(245, 158, 11, 0.45)',
            'seal_color' => '#b91c1c',
            'particle_type' => 'snow',
        ],
        'frosted_emerald' => [
            'key' => 'frosted_emerald',
            'label' => 'Frosted Emerald & Silver',
            'primary_color' => '#064e3b',
            'accent_color' => '#34d399',
            'gradient' => 'linear-gradient(135deg, #021a14 0%, #064e3b 50%, #047857 100%)',
            'card_border' => '1.5px solid rgba(52, 211, 153, 0.45)',
            'seal_color' => '#047857',
            'particle_type' => 'snow',
        ],
        'gold_champagne' => [
            'key' => 'gold_champagne',
            'label' => 'Gold Champagne & Velvet',
            'primary_color' => '#78350f',
            'accent_color' => '#fbbf24',
            'gradient' => 'linear-gradient(135deg, #1c0d02 0%, #451a03 50%, #78350f 100%)',
            'card_border' => '1.5px solid rgba(251, 191, 36, 0.55)',
            'seal_color' => '#d97706',
            'particle_type' => 'confetti',
        ],
        'pastel_spring' => [
            'key' => 'pastel_spring',
            'label' => 'Pastel Spring & Rose',
            'primary_color' => '#581c87',
            'accent_color' => '#ec4899',
            'gradient' => 'linear-gradient(135deg, #1e082e 0%, #3b0764 50%, #701a75 100%)',
            'card_border' => '1.5px solid rgba(236, 72, 153, 0.45)',
            'seal_color' => '#9d174d',
            'particle_type' => 'petals',
        ],
        'celestial_navy' => [
            'key' => 'celestial_navy',
            'label' => 'Celestial Navy & Gold',
            'primary_color' => '#0f172a',
            'accent_color' => '#38bdf8',
            'gradient' => 'linear-gradient(135deg, #020617 0%, #0f172a 50%, #1e293b 100%)',
            'card_border' => '1.5px solid rgba(56, 189, 248, 0.45)',
            'seal_color' => '#0369a1',
            'particle_type' => 'stardust',
        ],
        'autumn_harvest' => [
            'key' => 'autumn_harvest',
            'label' => 'Autumn Harvest & Amber',
            'primary_color' => '#7c2d12',
            'accent_color' => '#ea580c',
            'gradient' => 'linear-gradient(135deg, #1c0903 0%, #431407 50%, #7c2d12 100%)',
            'card_border' => '1.5px solid rgba(234, 88, 12, 0.5)',
            'seal_color' => '#c2410c',
            'particle_type' => 'leaves',
        ],
    ];

    public const TEMPLATE_STYLES = [
        'classic' => [
            'key' => 'classic',
            'name' => 'Classic Heritage',
            'tagline' => 'Embossed Parchment & Vintage Wax',
            'icon' => 'bi-award-fill',
            'badge' => 'Timeless Heritage',
            'card_class' => 'fp-festive-card-classic',
        ],
        'spatial_3d' => [
            'key' => 'spatial_3d',
            'name' => 'Luxury 3D Spatial',
            'tagline' => 'Multi-Layer Depth & Gyro Tilt',
            'icon' => 'bi-box',
            'badge' => 'Royal Luxe 3D',
            'card_class' => 'fp-festive-card-spatial',
        ],
        'translucent' => [
            'key' => 'translucent',
            'name' => 'Translucent Frost',
            'tagline' => 'Frosted Acrylic & Ambient Glow',
            'icon' => 'bi-gem',
            'badge' => 'Glassmorphic Aura',
            'card_class' => 'fp-festive-card-translucent',
        ],
        'hollow' => [
            'key' => 'hollow',
            'name' => 'Hollow Die-Cut',
            'tagline' => 'Laser-Cut Filigree & Negative Space',
            'icon' => 'bi-scissors',
            'badge' => 'Bespoke Papercraft',
            'card_class' => 'fp-festive-card-hollow',
        ],
        'plaque' => [
            'key' => 'plaque',
            'name' => 'Plaque & Royal Engraving',
            'tagline' => 'Monumental Obsidian & Bevelled Brass',
            'icon' => 'bi-shield-shaded',
            'badge' => 'Dynasty Plaque',
            'card_class' => 'fp-festive-card-plaque',
        ],
        'hyper_reality' => [
            'key' => 'hyper_reality',
            'name' => 'Hyper-Reality Hologram',
            'tagline' => 'Prism Sheen & Iridescent Chrome',
            'icon' => 'bi-rainbow',
            'badge' => 'Prism Refraction',
            'card_class' => 'fp-festive-card-holographic',
        ],
        'origami' => [
            'key' => 'origami',
            'name' => '3D Origami Pop-Up',
            'tagline' => 'Multi-Plane Interactive Spring Stage',
            'icon' => 'bi-gift-fill',
            'badge' => 'Signature Pop-Up',
            'card_class' => 'fp-festive-card-origami',
        ],
    ];

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Db::connect2();
    }

    /**
     * Create a new festive greeting card with cryptographic token and sanitized inputs.
     *
     * @param string $userId
     * @param string $famCode
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function createCard(string $userId, string $famCode, array $data): array
    {
        $userId = trim($userId);
        $famCode = trim($famCode);

        if ($userId === '') {
            throw new InvalidArgumentException('User ID is required to author a festive card.');
        }

        $occasionKey = strtolower(trim((string)($data['occasion'] ?? 'christmas')));
        if (!isset(self::OCCASIONS[$occasionKey])) {
            $occasionKey = 'christmas';
        }

        $themeKey = strtolower(trim((string)($data['theme'] ?? self::OCCASIONS[$occasionKey]['default_theme'])));
        if (!isset(self::THEMES[$themeKey])) {
            $themeKey = self::OCCASIONS[$occasionKey]['default_theme'];
        }

        $templateStyle = strtolower(trim((string)($data['template_style'] ?? 'classic')));
        if (!isset(self::TEMPLATE_STYLES[$templateStyle])) {
            $templateStyle = 'classic';
        }

        // Defense-in-depth: strip HTML/script tags from card text fields
        $senderName = strip_tags(trim((string)($data['sender_name'] ?? 'The Family')));
        $recipientName = strip_tags(trim((string)($data['recipient_name'] ?? '')));
        $headline = strip_tags(trim((string)($data['headline'] ?? self::OCCASIONS[$occasionKey]['default_headline'])));
        $verse = strip_tags(trim((string)($data['verse'] ?? self::OCCASIONS[$occasionKey]['default_verse'])));
        $signOff = strip_tags(trim((string)($data['sign_off'] ?? self::OCCASIONS[$occasionKey]['default_sign_off'])));

        $heroType = strtolower(trim((string)($data['hero_type'] ?? 'photo')));
        if (!in_array($heroType, ['photo', 'avatar', 'crest'], true)) {
            $heroType = 'photo';
        }

        $heroPhotoUrl = trim((string)($data['hero_photo_url'] ?? ''));
        if ($heroPhotoUrl !== '' && !preg_match('#^/resources/[a-zA-Z0-9_\-\./]+$#', $heroPhotoUrl)) {
            $heroPhotoUrl = '';
        }

        $audioVoiceUrl = trim((string)($data['audio_voice_url'] ?? ''));
        if ($audioVoiceUrl !== '' && !preg_match('#^/resources/[a-zA-Z0-9_\-\./]+$#', $audioVoiceUrl)) {
            $audioVoiceUrl = '';
        }

        // Generate 128-bit cryptographically secure UUID and public share token
        $uuid = bin2hex(random_bytes(16));
        $shareToken = bin2hex(random_bytes(16));

        $sql = 'INSERT INTO festive_cards (
                    uuid, user_id, fam_code, occasion, theme, template_style, sender_name, recipient_name,
                    headline, verse, sign_off, hero_type, hero_photo_url, audio_voice_url,
                    share_token, view_count, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $uuid,
            $userId,
            $famCode,
            $occasionKey,
            $themeKey,
            $templateStyle,
            $senderName,
            $recipientName !== '' ? $recipientName : null,
            $headline,
            $verse,
            $signOff,
            $heroType,
            $heroPhotoUrl !== '' ? $heroPhotoUrl : null,
            $audioVoiceUrl !== '' ? $audioVoiceUrl : null,
            $shareToken,
        ]);

        $cardId = (int)$this->db->lastInsertId();
        $appUrl = rtrim((string)($_ENV['APP_URL'] ?? (getenv('APP_URL') ?: 'https://familyplatform.com')), '/');

        $cardPayload = [
            'id' => $cardId,
            'uuid' => $uuid,
            'share_token' => $shareToken,
            'public_url' => '/greetings/f/' . $shareToken,
            'image_url' => '/greetings/i/' . $shareToken . '.png',
            'absolute_public_url' => $appUrl . '/greetings/f/' . $shareToken,
            'absolute_image_url' => $appUrl . '/greetings/i/' . $shareToken . '.png',
            'occasion' => $occasionKey,
            'theme' => $themeKey,
            'template_style' => $templateStyle,
            'headline' => $headline,
            'verse' => $verse,
            'sign_off' => $signOff,
            'sender_name' => $senderName,
            'recipient_name' => $recipientName,
            'occasion_meta' => self::OCCASIONS[$occasionKey] ?? self::OCCASIONS['christmas'],
            'theme_meta' => self::THEMES[$themeKey] ?? self::THEMES['royal_crimson'],
            'template_style_meta' => self::TEMPLATE_STYLES[$templateStyle] ?? self::TEMPLATE_STYLES['classic'],
        ];

        // Asynchronously/eagerly pre-render PNG so crawlers and recipients never experience cold GD delays
        try {
            AtgFestiveCardRenderer::renderPng($cardPayload);
        } catch (\Throwable $e) {
            error_log('[AtgFestiveCardService] Pre-rendering PNG failed: ' . $e->getMessage());
        }

        return $cardPayload;
    }

    /**
     * Retrieve a festive card by its public share token.
     *
     * @param string $token
     * @return array<string, mixed>|null
     */
    public function getCardByToken(string $token): ?array
    {
        $token = trim($token);
        if ($token === '' || !ctype_xdigit($token)) {
            return null;
        }

        $stmt = $this->db->prepare('SELECT * FROM festive_cards WHERE share_token = ? AND deleted_at IS NULL LIMIT 1');
        $stmt->execute([$token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || !is_array($row)) {
            return null;
        }

        $occasionKey = (string)($row['occasion'] ?? 'christmas');
        $themeKey = (string)($row['theme'] ?? 'royal_crimson');
        $styleKey = (string)($row['template_style'] ?? 'classic');

        $occasionMeta = self::OCCASIONS[$occasionKey] ?? self::OCCASIONS['christmas'];
        $themeMeta = self::THEMES[$themeKey] ?? self::THEMES['royal_crimson'];
        $styleMeta = self::TEMPLATE_STYLES[$styleKey] ?? self::TEMPLATE_STYLES['classic'];

        $appUrl = rtrim((string)($_ENV['APP_URL'] ?? (getenv('APP_URL') ?: 'https://familyplatform.com')), '/');

        return array_merge($row, [
            'occasion_meta' => $occasionMeta,
            'theme_meta' => $themeMeta,
            'template_style_meta' => $styleMeta,
            'public_url' => '/greetings/f/' . $token,
            'image_url' => '/greetings/i/' . $token . '.png',
            'absolute_public_url' => $appUrl . '/greetings/f/' . $token,
            'absolute_image_url' => $appUrl . '/greetings/i/' . $token . '.png',
        ]);
    }

    /**
     * Increment anonymous public view counter safely.
     *
     * @param string $token
     */
    public function recordCardView(string $token): void
    {
        $token = trim($token);
        if ($token === '' || !ctype_xdigit($token)) {
            return;
        }

        $stmt = $this->db->prepare('UPDATE festive_cards SET view_count = view_count + 1 WHERE share_token = ? LIMIT 1');
        $stmt->execute([$token]);
    }

    /**
     * Cross-post festive card directly to the internal family feed.
     *
     * @param string $userId
     * @param string $famCode
     * @param string $token
     * @return array<string, mixed>
     */
    public function postCardToFeed(string $userId, string $famCode, string $token): array
    {
        $card = $this->getCardByToken($token);
        if (!$card) {
            throw new RuntimeException('Festive card not found or has been removed.');
        }

        // BOLA / IDOR Verification: Ensure current user belongs to the card or same family
        if ($card['user_id'] !== $userId && $card['fam_code'] !== $famCode) {
            throw new RuntimeException('Unauthorized: You cannot post this card to this family feed.');
        }

        // Check if already posted to feed
        if (!empty($card['feed_post_id'])) {
            return [
                'status' => 'already_posted',
                'post_no' => (int)$card['feed_post_id'],
                'message' => 'This card is already published on your family feed.',
            ];
        }

        // Resolve author profile info
        $userStmt = $this->db->prepare('SELECT firstName AS name, lastName, (SELECT img FROM profilePics WHERE id = account.id LIMIT 1) as profile_img FROM account WHERE id = ? LIMIT 1');
        $userStmt->execute([$userId]);
        $author = $userStmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $fullName = trim(($author['name'] ?? '') . ' ' . ($author['lastName'] ?? ''));
        if ($fullName === '') {
            $fullName = $card['sender_name'];
        }
        $profileImg = (string)($author['profile_img'] ?? 'default.png');

        $occasionMeta = $card['occasion_meta'];
        $headline = htmlspecialchars($card['headline'], ENT_QUOTES, 'UTF-8');
        $sender = htmlspecialchars($card['sender_name'], ENT_QUOTES, 'UTF-8');
        $verse = htmlspecialchars($card['verse'], ENT_QUOTES, 'UTF-8');
        $publicUrl = '/greetings/f/' . $card['share_token'];

        // Compose high-fidelity 3D stage message
        $stagePrefix = '[style:aurora_prism]';
        $postMessage = "{$stagePrefix} 🎄✨ {$headline} ✨🎄\n\n"
                     . "{$verse}\n\n"
                     . "— Warmest blessings, {$sender}\n\n"
                     . "💌 [Open Living Festive Envelope]({$publicUrl})";

        $heroPhoto = !empty($card['hero_photo_url']) ? (string)$card['hero_photo_url'] : null;

        $insertSql = 'INSERT INTO post (
                        id, fullName, profileImg, postMessage, postFamCode, post_time, post_status, post_img0, date_created
                      ) VALUES (?, ?, ?, ?, ?, ?, "published", ?, NOW())';
        
        $postTime = date('Y-m-d H:i:s');
        $postStmt = $this->db->prepare($insertSql);
        $postStmt->execute([
            $userId,
            $fullName,
            $profileImg,
            $postMessage,
            $famCode,
            $postTime,
            $heroPhoto,
        ]);

        $postId = (int)$this->db->lastInsertId();

        // Update card record with feed_post_id
        $updStmt = $this->db->prepare('UPDATE festive_cards SET feed_post_id = ? WHERE id = ? LIMIT 1');
        $updStmt->execute([$postId, $card['id']]);

        return [
            'status' => 'success',
            'post_no' => $postId,
            'message' => 'Festive card successfully published to family feed!',
        ];
    }

    /**
     * Retrieve recent festive cards authored in this family.
     *
     * @param string $userId
     * @param string $famCode
     * @param int $limit
     * @return array<int, array<string, mixed>>
     */
    public function getFamilyCards(string $userId, string $famCode, int $limit = 20): array
    {
        $limit = max(1, min(50, $limit));
        $stmt = $this->db->prepare(
            'SELECT * FROM festive_cards 
             WHERE fam_code = ? AND deleted_at IS NULL 
             ORDER BY id DESC LIMIT ' . (int)$limit
        );
        $stmt->execute([$famCode]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $results = [];
        foreach ($rows as $row) {
            $occ = (string)($row['occasion'] ?? 'christmas');
            $thm = (string)($row['theme'] ?? 'royal_crimson');
            $stl = (string)($row['template_style'] ?? 'classic');
            $results[] = array_merge($row, [
                'occasion_meta' => self::OCCASIONS[$occ] ?? self::OCCASIONS['christmas'],
                'theme_meta' => self::THEMES[$thm] ?? self::THEMES['royal_crimson'],
                'template_style_meta' => self::TEMPLATE_STYLES[$stl] ?? self::TEMPLATE_STYLES['classic'],
                'public_url' => '/greetings/f/' . $row['share_token'],
                'image_url' => '/greetings/i/' . $row['share_token'] . '.png',
            ]);
        }

        return $results;
    }

    /**
     * Sanitize uploaded image by reading pixel data and re-encoding to strip EXIF GPS metadata.
     *
     * @param string $sourcePath
     * @return bool
     */
    public static function stripExifGps(string $sourcePath): bool
    {
        if (!file_exists($sourcePath) || !is_readable($sourcePath)) {
            return false;
        }

        $imageInfo = @getimagesize($sourcePath);
        if (!$imageInfo) {
            return false;
        }

        $width = (int)($imageInfo[0] ?? 0);
        $height = (int)($imageInfo[1] ?? 0);
        if ($width <= 0 || $height <= 0 || $width > 4096 || $height > 4096) {
            return false;
        }

        $mime = $imageInfo['mime'] ?? '';
        $image = null;

        if ($mime === 'image/jpeg') {
            $image = @imagecreatefromjpeg($sourcePath);
            if ($image) {
                imagejpeg($image, $sourcePath, 92);
                imagedestroy($image);
                return true;
            }
        } elseif ($mime === 'image/png') {
            $image = @imagecreatefrompng($sourcePath);
            if ($image) {
                imagealphablending($image, false);
                imagesavealpha($image, true);
                imagepng($image, $sourcePath, 8);
                imagedestroy($image);
                return true;
            }
        } elseif ($mime === 'image/webp' && function_exists('imagecreatefromwebp') && function_exists('imagewebp')) {
            $image = @imagecreatefromwebp($sourcePath);
            if ($image) {
                imagealphablending($image, false);
                imagesavealpha($image, true);
                imagewebp($image, $sourcePath, 88);
                imagedestroy($image);
                return true;
            }
        }

        return false;
    }
}
