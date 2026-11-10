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
 *
 * v2 (Couture overhaul): adds bespoke "Your Own Celebration" occasions with a mood,
 * life-event occasions (birthday, wedding, new baby, graduation, remembrance),
 * couture palettes, hardened hero photo processing (orientation, resize, EXIF purge)
 * and a single hydration path shared by the studio, archive, envelope and renderer.
 */
final class AtgFestiveCardService
{
    private PDO $db;

    /** Public web path for uploaded hero photos (served from the project root, deploy-protected). */
    public const HERO_WEB_DIR = '/resources/images/festive_cards';

    public const CUSTOM_OCCASION_MAX = 80;
    public const VERSE_MAX = 600;

    /**
     * Moods drive particles, chime, envelope copy and default palette.
     * Used directly by "Your Own Celebration" and inherited by every curated occasion.
     */
    public const MOODS = [
        'joyful' => [
            'key' => 'joyful',
            'default_headline' => 'A Moment Worth Celebrating',
            'label' => 'Joyful',
            'description' => 'Birthdays, wins, weddings, homecomings',
            'icon' => 'bi-balloon-heart-fill',
            'particle' => 'confetti',
            'default_theme' => 'gold_champagne',
            'emoji' => '🎉',
            'envelope_prompt' => 'Something wonderful is inside',
            'default_sign_off' => 'Celebrating with you,',
            'sample_verses' => [
                'Today belongs to you. May it be wrapped in laughter, crowned with love and remembered for years to come.',
                'Here is to this beautiful moment and to every joy still waiting for you. Celebrating you with all our hearts.',
                'Some moments deserve to be marked in gold. This is one of them, and we are so proud to share it with you.',
            ],
        ],
        'reverent' => [
            'key' => 'reverent',
            'default_headline' => 'Forever Remembered, Forever Loved',
            'label' => 'Reverent & Remembrance',
            'description' => 'Memorials, anniversaries of loss, tributes',
            'icon' => 'bi-feather',
            'particle' => 'embers',
            'default_theme' => 'pearl_dove',
            'emoji' => '🕊️',
            'envelope_prompt' => 'A message of remembrance',
            'default_sign_off' => 'Forever in our hearts,',
            'sample_verses' => [
                'Though the years have passed, your light has never dimmed. We carry your love, your laughter and your lessons with us every single day.',
                'Gone from our sight, but never from our hearts. Today we pause to honour a life that shaped us all.',
                'Your love remains our quiet compass. We remember you today with gratitude, with tears and with endless pride.',
            ],
        ],
        'grateful' => [
            'key' => 'grateful',
            'default_headline' => 'With Heartfelt Thanks',
            'label' => 'Grateful',
            'description' => 'Thank-yous, appreciation, honouring someone',
            'icon' => 'bi-flower2',
            'particle' => 'golddust',
            'default_theme' => 'autumn_harvest',
            'emoji' => '🙏',
            'envelope_prompt' => 'A note of heartfelt thanks',
            'default_sign_off' => 'With deepest gratitude,',
            'sample_verses' => [
                'Words feel too small for what you have given us, but please know how deeply you are appreciated.',
                'Thank you for your kindness, your time and your unwavering support. You have made a lasting difference.',
                'Some people make the world gentler simply by being in it. You are one of them. Thank you, truly.',
            ],
        ],
        'romantic' => [
            'key' => 'romantic',
            'default_headline' => 'Here’s to Us, Always',
            'label' => 'Romantic',
            'description' => 'Anniversaries, engagements, love notes',
            'icon' => 'bi-heart-fill',
            'particle' => 'petals',
            'default_theme' => 'rose_blush',
            'emoji' => '💞',
            'envelope_prompt' => 'Sealed with love',
            'default_sign_off' => 'All my love, always,',
            'sample_verses' => [
                'Every chapter with you has been my favourite. Here is to the love we have built and all the pages still to come.',
                'You are my calm, my adventure and my home. Celebrating us today and every day.',
                'Through every season, you have been my constant. Thank you for a love that grows more beautiful with time.',
            ],
        ],
    ];

    public const OCCASIONS = [
        'christmas' => [
            'key' => 'christmas',
            'title' => 'Christmas & Boxing Day',
            'short' => 'Christmas',
            'icon' => 'bi-tree-fill',
            'emoji' => '🎄',
            'mood' => 'joyful',
            'default_theme' => 'royal_crimson',
            'particle' => 'snow',
            'badge' => 'Season’s Greetings',
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
            'short' => 'New Year',
            'icon' => 'bi-stars',
            'emoji' => '🥂',
            'mood' => 'joyful',
            'default_theme' => 'onyx_gold',
            'particle' => 'golddust',
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
            'short' => 'Easter',
            'icon' => 'bi-flower1',
            'emoji' => '🌷',
            'mood' => 'joyful',
            'default_theme' => 'rose_blush',
            'particle' => 'petals',
            'badge' => 'Easter Blessings',
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
            'short' => 'Eid',
            'icon' => 'bi-moon-stars-fill',
            'emoji' => '🌙',
            'mood' => 'joyful',
            'default_theme' => 'celestial_navy',
            'particle' => 'stardust',
            'badge' => 'Eid Mubarak',
            'default_headline' => 'Eid Mubarak & Sacred Blessings',
            'default_verse' => 'May the divine blessings of Eid bestow endless happiness, tranquility, and harmony upon your home and loved ones.',
            'default_sign_off' => 'Eid Mubarak from,',
            'sample_verses' => [
                'Wishing you and your family a blessed Eid filled with joyous feastings, spiritual fulfilment, and deep gratitude.',
                'On this auspicious occasion, may your prayers be answered and your home enriched with enduring peace and warmth.',
                'May the grace of this sacred day bring you and your kin closer together in celebration, charity, and enduring love.',
            ],
        ],
        'diwali' => [
            'key' => 'diwali',
            'title' => 'Diwali (Festival of Lights)',
            'short' => 'Diwali',
            'icon' => 'bi-brightness-high-fill',
            'emoji' => '🪔',
            'mood' => 'joyful',
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
            'short' => 'Thanksgiving',
            'icon' => 'bi-cup-hot-fill',
            'emoji' => '🍂',
            'mood' => 'grateful',
            'default_theme' => 'autumn_harvest',
            'particle' => 'leaves',
            'badge' => 'With Gratitude',
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
            'short' => 'Mother’s Day',
            'icon' => 'bi-heart-fill',
            'emoji' => '💐',
            'mood' => 'grateful',
            'default_theme' => 'rose_blush',
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
            'short' => 'Father’s Day',
            'icon' => 'bi-shield-fill-check',
            'emoji' => '👔',
            'mood' => 'grateful',
            'default_theme' => 'celestial_navy',
            'particle' => 'golddust',
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
            'short' => 'Family Reunion',
            'icon' => 'bi-people-fill',
            'emoji' => '🏡',
            'mood' => 'joyful',
            'default_theme' => 'gold_champagne',
            'particle' => 'confetti',
            'badge' => 'Family Reunion',
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
            'short' => 'Milestone',
            'icon' => 'bi-trophy-fill',
            'emoji' => '🏆',
            'mood' => 'joyful',
            'default_theme' => 'royal_crimson',
            'particle' => 'golddust',
            'badge' => 'A Milestone Moment',
            'default_headline' => 'Celebrating Our Extraordinary Milestone',
            'default_verse' => 'Honouring a momentous chapter of endurance, achievement, and triumph in our family’s enduring history.',
            'default_sign_off' => 'Celebrating together,',
            'sample_verses' => [
                'Years of love, resilience, and mutual devotion. Here is to celebrating our golden anniversary in style!',
                'Reaching this summit with dignity, perseverance, and family pride. Congratulations on this milestone!',
                'Every achievement is sweeter when shared with the ones who believed in us from the very beginning.',
            ],
        ],
        'birthday' => [
            'key' => 'birthday',
            'title' => 'Birthday Celebration',
            'short' => 'Birthday',
            'icon' => 'bi-cake2-fill',
            'emoji' => '🎂',
            'mood' => 'joyful',
            'default_theme' => 'gold_champagne',
            'particle' => 'confetti',
            'badge' => 'Happy Birthday',
            'default_headline' => 'Happy Birthday',
            'default_verse' => 'Another year wiser, more radiant and more loved. May this birthday open the most beautiful chapter yet.',
            'default_sign_off' => 'With love and celebration,',
            'sample_verses' => [
                'Wishing you a birthday as bright and generous as your heart. Here is to health, joy and dreams fulfilled.',
                'Today we celebrate you: every laugh you have shared, every life you have touched and every year still to come.',
                'May your candles light up a year of wonderful surprises, deep contentment and endless love.',
            ],
        ],
        'wedding' => [
            'key' => 'wedding',
            'title' => 'Wedding & Wedding Anniversary',
            'short' => 'Wedding',
            'icon' => 'bi-gem',
            'emoji' => '💍',
            'mood' => 'romantic',
            'default_theme' => 'onyx_gold',
            'particle' => 'petals',
            'badge' => 'With Love & Honour',
            'default_headline' => 'Two Hearts, One Beautiful Journey',
            'default_verse' => 'Celebrating a love that is patient, kind and everlasting. Wishing you a lifetime of joy, laughter and togetherness.',
            'default_sign_off' => 'With love and blessings,',
            'sample_verses' => [
                'May your marriage be a shelter in every storm and a celebration in every season.',
                'Here is to the beautiful union of two families and a love story that will inspire generations.',
                'Another year of choosing each other. Happy anniversary to a love that only grows more radiant.',
            ],
        ],
        'new_baby' => [
            'key' => 'new_baby',
            'title' => 'New Baby & Naming Ceremony',
            'short' => 'New Baby',
            'icon' => 'bi-balloon-fill',
            'emoji' => '👶',
            'mood' => 'joyful',
            'default_theme' => 'pearl_dove',
            'particle' => 'stardust',
            'badge' => 'Welcome to the World',
            'default_headline' => 'Welcome, Little One',
            'default_verse' => 'A new heartbeat has joined our family tree. May this precious child grow surrounded by love, wisdom and endless blessings.',
            'default_sign_off' => 'With joyful hearts,',
            'sample_verses' => [
                'The newest branch on our family tree has arrived, and our hearts have grown overnight.',
                'Tiny hands, a great big future. Welcome to the family, sweet one.',
                'May this little life be blessed with good health, a curious mind and a lifetime of being loved.',
            ],
        ],
        'graduation' => [
            'key' => 'graduation',
            'title' => 'Graduation & Achievement',
            'short' => 'Graduation',
            'icon' => 'bi-mortarboard-fill',
            'emoji' => '🎓',
            'mood' => 'joyful',
            'default_theme' => 'celestial_navy',
            'particle' => 'confetti',
            'badge' => 'Congratulations',
            'default_headline' => 'Congratulations, Graduate',
            'default_verse' => 'Your dedication, late nights and quiet courage have brought you here. The whole family is bursting with pride.',
            'default_sign_off' => 'So very proud of you,',
            'sample_verses' => [
                'This is only the beginning. The world is ready for everything you are about to give it.',
                'Hard work, resilience and a brilliant mind. You earned every moment of this celebration.',
                'Today we celebrate your achievement and the extraordinary person you have become.',
            ],
        ],
        'remembrance' => [
            'key' => 'remembrance',
            'title' => 'In Loving Memory & Remembrance',
            'short' => 'Remembrance',
            'icon' => 'bi-feather',
            'emoji' => '🕊️',
            'mood' => 'reverent',
            'default_theme' => 'pearl_dove',
            'particle' => 'embers',
            'badge' => 'In Loving Memory',
            'default_headline' => 'Forever Remembered, Forever Loved',
            'default_verse' => 'Your love remains our quiet compass. Today we remember you with gratitude, with tears and with endless pride.',
            'default_sign_off' => 'Forever in our hearts,',
            'sample_verses' => [
                'Though the years have passed, your light has never dimmed. We carry your love with us every single day.',
                'Gone from our sight, but never from our hearts. Today we pause to honour a life that shaped us all.',
                'What we once enjoyed, we can never lose. All that we love deeply becomes a part of us.',
            ],
        ],
        'custom' => [
            'key' => 'custom',
            'title' => 'Your Own Celebration',
            'short' => 'Your Own Celebration',
            'icon' => 'bi-pencil-square',
            'emoji' => '✨',
            'mood' => 'joyful',
            'default_theme' => 'gold_champagne',
            'particle' => 'confetti',
            'badge' => 'A Special Occasion',
            'default_headline' => 'A Moment Worth Celebrating',
            'default_verse' => 'Today belongs to you. May it be wrapped in laughter, crowned with love and remembered for years to come.',
            'default_sign_off' => 'With love,',
            'sample_verses' => [],
            'is_custom' => true,
        ],
    ];

    /**
     * Couture palettes. primary/accent/gradient/card_border/seal_color/particle_type keep the v1 contract;
     * deep/light/paper/ink extend it for the couture card system and the GD renderer.
     */
    public const THEMES = [
        'royal_crimson' => [
            'key' => 'royal_crimson',
            'label' => 'Royal Crimson & Gold',
            'primary_color' => '#7a0f2a',
            'accent_color' => '#d4af37',
            'deep_color' => '#1a0309',
            'light_color' => '#f6e7a8',
            'paper_color' => '#fbf3e4',
            'ink_color' => '#5c0a1f',
            'gradient' => 'linear-gradient(135deg, #1a0309 0%, #4c0519 50%, #7a0f2a 100%)',
            'card_border' => '1.5px solid rgba(212, 175, 55, 0.55)',
            'seal_color' => '#9f1239',
            'particle_type' => 'snow',
        ],
        'frosted_emerald' => [
            'key' => 'frosted_emerald',
            'label' => 'Frosted Emerald & Silver',
            'primary_color' => '#0b4d3b',
            'accent_color' => '#c9d6d3',
            'deep_color' => '#02140f',
            'light_color' => '#ffffff',
            'paper_color' => '#f1f6f3',
            'ink_color' => '#08392c',
            'gradient' => 'linear-gradient(135deg, #02140f 0%, #064e3b 50%, #0b6b50 100%)',
            'card_border' => '1.5px solid rgba(201, 214, 211, 0.55)',
            'seal_color' => '#065f46',
            'particle_type' => 'snow',
        ],
        'gold_champagne' => [
            'key' => 'gold_champagne',
            'label' => 'Gold Champagne & Velvet',
            'primary_color' => '#6b4a1c',
            'accent_color' => '#e2bf6a',
            'deep_color' => '#1a1207',
            'light_color' => '#fff3cf',
            'paper_color' => '#fbf6ea',
            'ink_color' => '#4a3110',
            'gradient' => 'linear-gradient(135deg, #1a1207 0%, #3d2a0e 50%, #6b4a1c 100%)',
            'card_border' => '1.5px solid rgba(226, 191, 106, 0.6)',
            'seal_color' => '#a16207',
            'particle_type' => 'confetti',
        ],
        'rose_blush' => [
            'key' => 'rose_blush',
            'label' => 'Rose Blush & Pearl',
            'primary_color' => '#9d4b63',
            'accent_color' => '#e8b4a2',
            'deep_color' => '#2a0d18',
            'light_color' => '#fde9e3',
            'paper_color' => '#fdf4f1',
            'ink_color' => '#7a2e45',
            'gradient' => 'linear-gradient(135deg, #2a0d18 0%, #5e1f35 50%, #9d4b63 100%)',
            'card_border' => '1.5px solid rgba(232, 180, 162, 0.6)',
            'seal_color' => '#9f1239',
            'particle_type' => 'petals',
        ],
        'pastel_spring' => [
            'key' => 'pastel_spring',
            'label' => 'Amethyst & Orchid',
            'primary_color' => '#5b2a86',
            'accent_color' => '#e9b7e6',
            'deep_color' => '#16081f',
            'light_color' => '#fbeefc',
            'paper_color' => '#f9f3fb',
            'ink_color' => '#46206a',
            'gradient' => 'linear-gradient(135deg, #16081f 0%, #3b0764 50%, #5b2a86 100%)',
            'card_border' => '1.5px solid rgba(233, 183, 230, 0.55)',
            'seal_color' => '#86198f',
            'particle_type' => 'petals',
        ],
        'celestial_navy' => [
            'key' => 'celestial_navy',
            'label' => 'Celestial Navy & Gold',
            'primary_color' => '#14254d',
            'accent_color' => '#d9b45a',
            'deep_color' => '#040a1a',
            'light_color' => '#f5e6b5',
            'paper_color' => '#f3f1ea',
            'ink_color' => '#14254d',
            'gradient' => 'linear-gradient(135deg, #040a1a 0%, #0f1c3d 50%, #14254d 100%)',
            'card_border' => '1.5px solid rgba(217, 180, 90, 0.55)',
            'seal_color' => '#1e3a8a',
            'particle_type' => 'stardust',
        ],
        'autumn_harvest' => [
            'key' => 'autumn_harvest',
            'label' => 'Autumn Harvest & Copper',
            'primary_color' => '#8a3b12',
            'accent_color' => '#e5a061',
            'deep_color' => '#1f0b03',
            'light_color' => '#ffe0bd',
            'paper_color' => '#fbf2e6',
            'ink_color' => '#6b2c0c',
            'gradient' => 'linear-gradient(135deg, #1f0b03 0%, #4a1a07 50%, #8a3b12 100%)',
            'card_border' => '1.5px solid rgba(229, 160, 97, 0.55)',
            'seal_color' => '#c2410c',
            'particle_type' => 'leaves',
        ],
        'onyx_gold' => [
            'key' => 'onyx_gold',
            'label' => 'Onyx & 24k Gold',
            'primary_color' => '#1d1c21',
            'accent_color' => '#d4af37',
            'deep_color' => '#050506',
            'light_color' => '#f8e7a2',
            'paper_color' => '#f6f2e9',
            'ink_color' => '#151417',
            'gradient' => 'linear-gradient(135deg, #050506 0%, #121115 50%, #2a2830 100%)',
            'card_border' => '1.5px solid rgba(212, 175, 55, 0.6)',
            'seal_color' => '#1c1917',
            'particle_type' => 'golddust',
        ],
        'pearl_dove' => [
            'key' => 'pearl_dove',
            'label' => 'Pearl Dove & Silver',
            'primary_color' => '#4f5872',
            'accent_color' => '#d3d7e0',
            'deep_color' => '#0f1119',
            'light_color' => '#ffffff',
            'paper_color' => '#f5f5f7',
            'ink_color' => '#2f3548',
            'gradient' => 'linear-gradient(135deg, #0f1119 0%, #262c3d 50%, #4f5872 100%)',
            'card_border' => '1.5px solid rgba(211, 215, 224, 0.6)',
            'seal_color' => '#475569',
            'particle_type' => 'embers',
        ],
    ];

    public const TEMPLATE_STYLES = [
        'classic' => [
            'key' => 'classic',
            'name' => 'Classic Heritage',
            'tagline' => 'Letterpress ivory, gold-foil filigree & wax seal',
            'icon' => 'bi-award-fill',
            'badge' => 'Timeless Heritage',
            'card_class' => 'fp-festive-card-classic',
            'surface' => 'paper',
        ],
        'spatial_3d' => [
            'key' => 'spatial_3d',
            'name' => 'Luxury 3D Spatial',
            'tagline' => 'Floating layers, gilded arch & gyro parallax',
            'icon' => 'bi-box',
            'badge' => 'Royal Luxe 3D',
            'card_class' => 'fp-festive-card-spatial',
            'surface' => 'velvet',
        ],
        'translucent' => [
            'key' => 'translucent',
            'name' => 'Translucent Frost',
            'tagline' => 'Frosted crystal pane over living aurora light',
            'icon' => 'bi-gem',
            'badge' => 'Crystal Aura',
            'card_class' => 'fp-festive-card-translucent',
            'surface' => 'glass',
        ],
        'hollow' => [
            'key' => 'hollow',
            'name' => 'Hollow Die-Cut',
            'tagline' => 'Laser-cut lace paper over a foil underlay',
            'icon' => 'bi-scissors',
            'badge' => 'Bespoke Papercraft',
            'card_class' => 'fp-festive-card-hollow',
            'surface' => 'paper',
        ],
        'plaque' => [
            'key' => 'plaque',
            'name' => 'Plaque & Royal Engraving',
            'tagline' => 'Black marble slab, bevelled brass & engraving',
            'icon' => 'bi-shield-shaded',
            'badge' => 'Dynasty Plaque',
            'card_class' => 'fp-festive-card-plaque',
            'surface' => 'obsidian',
        ],
        'hyper_reality' => [
            'key' => 'hyper_reality',
            'name' => 'Hyper-Reality Hologram',
            'tagline' => 'Iridescent foil that shifts as you tilt',
            'icon' => 'bi-rainbow',
            'badge' => 'Prism Hologram',
            'card_class' => 'fp-festive-card-holographic',
            'surface' => 'holo',
        ],
        'origami' => [
            'key' => 'origami',
            'name' => '3D Origami Pop-Up',
            'tagline' => 'Folded paper planes with a pop-up photo stage',
            'icon' => 'bi-gift-fill',
            'badge' => 'Signature Pop-Up',
            'card_class' => 'fp-festive-card-origami',
            'surface' => 'paper',
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

        // Bespoke "Your Own Celebration": free-text occasion name + mood
        $customOccasion = null;
        $mood = null;
        if ($occasionKey === 'custom') {
            $customOccasion = self::cleanText((string)($data['custom_occasion'] ?? ''), self::CUSTOM_OCCASION_MAX);
            if ($customOccasion === '') {
                throw new InvalidArgumentException('Please name your celebration, e.g. "Dad’s 25th Remembrance".');
            }
            $moodKey = strtolower(trim((string)($data['mood'] ?? 'joyful')));
            $mood = isset(self::MOODS[$moodKey]) ? $moodKey : 'joyful';
        }

        $defaultTheme = $mood !== null ? self::MOODS[$mood]['default_theme'] : self::OCCASIONS[$occasionKey]['default_theme'];
        $themeKey = strtolower(trim((string)($data['theme'] ?? $defaultTheme)));
        if (!isset(self::THEMES[$themeKey])) {
            $themeKey = $defaultTheme;
        }

        $templateStyle = strtolower(trim((string)($data['template_style'] ?? 'classic')));
        if (!isset(self::TEMPLATE_STYLES[$templateStyle])) {
            $templateStyle = 'classic';
        }

        $occ = self::OCCASIONS[$occasionKey];
        $defaultHeadline = $mood !== null ? self::MOODS[$mood]['default_headline'] : $occ['default_headline'];
        $defaultSignOff = $mood !== null ? self::MOODS[$mood]['default_sign_off'] : $occ['default_sign_off'];
        $defaultVerse = $mood !== null ? self::MOODS[$mood]['sample_verses'][0] : $occ['default_verse'];

        // Defense-in-depth: strip HTML/script tags from card text fields
        $senderName = self::cleanText((string)($data['sender_name'] ?? ''), 128);
        $recipientName = self::cleanText((string)($data['recipient_name'] ?? ''), 128);
        $headline = self::cleanText((string)($data['headline'] ?? ''), 160);
        $verse = self::cleanText((string)($data['verse'] ?? ''), self::VERSE_MAX, true);
        $signOff = self::cleanText((string)($data['sign_off'] ?? ''), 128);

        $senderName = $senderName !== '' ? $senderName : 'The Family';
        $headline = $headline !== '' ? $headline : $defaultHeadline;
        $verse = $verse !== '' ? $verse : $defaultVerse;
        $signOff = $signOff !== '' ? $signOff : $defaultSignOff;

        // Sovereign Content Safety & Abuse Guardrail Gate (ATG Pod 1 & Marcus SecOps)
        ContentSafetyService::assertCleanPayload([
            'sender name' => $senderName,
            'recipient name' => $recipientName,
            'card headline' => $headline,
            'card verse' => $verse,
            'card sign-off' => $signOff,
        ], 'festive_card');

        $heroType = strtolower(trim((string)($data['hero_type'] ?? 'photo')));
        if (!in_array($heroType, ['photo', 'avatar', 'crest'], true)) {
            $heroType = 'photo';
        }

        $heroPhotoUrl = trim((string)($data['hero_photo_url'] ?? ''));
        if ($heroPhotoUrl !== '' && !preg_match('#^/resources/[a-zA-Z0-9_\-\./]+$#', $heroPhotoUrl)) {
            $heroPhotoUrl = '';
        }
        if (str_contains($heroPhotoUrl, '..')) {
            $heroPhotoUrl = '';
        }
        if ($heroPhotoUrl === '' && $heroType === 'photo') {
            $heroType = 'crest';
        }

        $audioVoiceUrl = trim((string)($data['audio_voice_url'] ?? ''));
        if ($audioVoiceUrl !== '' && (!preg_match('#^/resources/[a-zA-Z0-9_\-\./]+$#', $audioVoiceUrl) || str_contains($audioVoiceUrl, '..'))) {
            $audioVoiceUrl = '';
        }

        // Generate 128-bit cryptographically secure UUID and public share token
        $uuid = bin2hex(random_bytes(16));
        $shareToken = bin2hex(random_bytes(16));

        $hasCustomCols = $this->hasCustomOccasionColumns();

        if ($hasCustomCols) {
            $sql = 'INSERT INTO festive_cards (
                        uuid, user_id, fam_code, occasion, custom_occasion, mood, theme, template_style, sender_name, recipient_name,
                        headline, verse, sign_off, hero_type, hero_photo_url, audio_voice_url,
                        share_token, view_count, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())';
            $params = [
                $uuid, $userId, $famCode, $occasionKey, $customOccasion, $mood, $themeKey, $templateStyle,
                $senderName, $recipientName !== '' ? $recipientName : null, $headline, $verse, $signOff, $heroType,
                $heroPhotoUrl !== '' ? $heroPhotoUrl : null, $audioVoiceUrl !== '' ? $audioVoiceUrl : null, $shareToken,
            ];
        } else {
            $sql = 'INSERT INTO festive_cards (
                        uuid, user_id, fam_code, occasion, theme, template_style, sender_name, recipient_name,
                        headline, verse, sign_off, hero_type, hero_photo_url, audio_voice_url,
                        share_token, view_count, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())';
            $params = [
                $uuid, $userId, $famCode, $occasionKey, $themeKey, $templateStyle,
                $senderName, $recipientName !== '' ? $recipientName : null, $headline, $verse, $signOff, $heroType,
                $heroPhotoUrl !== '' ? $heroPhotoUrl : null, $audioVoiceUrl !== '' ? $audioVoiceUrl : null, $shareToken,
            ];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $cardId = (int)$this->db->lastInsertId();

        $cardPayload = self::hydrate([
            'id' => $cardId,
            'uuid' => $uuid,
            'user_id' => $userId,
            'fam_code' => $famCode,
            'share_token' => $shareToken,
            'occasion' => $occasionKey,
            'custom_occasion' => $customOccasion,
            'mood' => $mood,
            'theme' => $themeKey,
            'template_style' => $templateStyle,
            'headline' => $headline,
            'verse' => $verse,
            'sign_off' => $signOff,
            'sender_name' => $senderName,
            'recipient_name' => $recipientName,
            'hero_type' => $heroType,
            'hero_photo_url' => $heroPhotoUrl !== '' ? $heroPhotoUrl : null,
            'audio_voice_url' => $audioVoiceUrl !== '' ? $audioVoiceUrl : null,
            'view_count' => 0,
            'feed_post_id' => null,
        ]);

        // Eagerly pre-render so crawlers (WhatsApp, iMessage) and recipients never hit a cold GD render
        try {
            // Only the link-preview image is time-critical; PNG/portrait render lazily on first request.
            AtgFestiveCardRenderer::renderJpeg($cardPayload, 'og');
        } catch (\Throwable $e) {
            error_log('[AtgFestiveCardService] Pre-rendering card images failed: ' . $e->getMessage());
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

        return self::hydrate($row);
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
     * The feed image is the fully designed card (portrait render), not the raw photo.
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

        // BOLA / IDOR Verification: Ensure current user authored the card or belongs to the same family
        if ($card['user_id'] !== $userId && $card['fam_code'] !== $famCode) {
            throw new RuntimeException('Unauthorized: You cannot post this card to this family feed.');
        }

        if (!empty($card['feed_post_id'])) {
            return [
                'status' => 'already_posted',
                'post_no' => (int)$card['feed_post_id'],
                'message' => 'This card is already on your family feed.',
            ];
        }

        $userStmt = $this->db->prepare('SELECT firstName AS name, lastName, (SELECT img FROM profilePics WHERE id = account.id LIMIT 1) as profile_img FROM account WHERE id = ? LIMIT 1');
        $userStmt->execute([$userId]);
        $author = $userStmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $fullName = trim(($author['name'] ?? '') . ' ' . ($author['lastName'] ?? ''));
        if ($fullName === '') {
            $fullName = (string)$card['sender_name'];
        }
        $profileImg = (string)($author['profile_img'] ?? 'default.png');

        // Feed renders text safely itself; store plain text (already tag-stripped at creation).
        $emoji = (string)$card['emoji'];
        $postMessage = "{$emoji} {$card['headline']}\n\n"
                     . "{$card['verse']}\n\n"
                     . "{$card['sign_off']} {$card['sender_name']}\n\n"
                     . "💌 Open the living envelope: {$card['absolute_public_url']}";

        // Persist the designed card as a real image file so the feed shows the full design
        $feedImage = AtgFestiveCardRenderer::exportFeedImage($card);

        $insertSql = 'INSERT INTO post (
                        id, fullName, profileImg, postMessage, postFamCode, post_time, post_status, post_img0, date_created
                      ) VALUES (?, ?, ?, ?, ?, ?, "published", ?, NOW())';

        $postTime = function_exists('milliSeconds') ? (string)milliSeconds() : date('Y-m-d H:i:s');
        $postStmt = $this->db->prepare($insertSql);
        $postStmt->execute([
            $userId,
            $fullName,
            $profileImg,
            $postMessage,
            $famCode,
            $postTime,
            $feedImage,
        ]);

        $postId = (int)$this->db->lastInsertId();

        $updStmt = $this->db->prepare('UPDATE festive_cards SET feed_post_id = ? WHERE id = ? LIMIT 1');
        $updStmt->execute([$postId, $card['id']]);

        return [
            'status' => 'success',
            'post_no' => $postId,
            'message' => 'Card published to your family feed!',
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

        return array_map(static fn(array $row): array => self::hydrate($row), $rows);
    }

    /**
     * Single hydration path: attaches metadata, resolved asset URLs and display helpers.
     *
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    public static function hydrate(array $row): array
    {
        $occasionKey = (string)($row['occasion'] ?? 'christmas');
        $themeKey = (string)($row['theme'] ?? 'royal_crimson');
        $styleKey = (string)($row['template_style'] ?? 'classic');
        $token = (string)($row['share_token'] ?? '');

        $occasionMeta = self::OCCASIONS[$occasionKey] ?? self::OCCASIONS['christmas'];
        $themeMeta = self::THEMES[$themeKey] ?? self::THEMES['royal_crimson'];
        $styleMeta = self::TEMPLATE_STYLES[$styleKey] ?? self::TEMPLATE_STYLES['classic'];

        $custom = trim((string)($row['custom_occasion'] ?? ''));
        $isCustom = $occasionKey === 'custom' && $custom !== '';
        $moodKey = $isCustom ? (string)($row['mood'] ?? 'joyful') : (string)($occasionMeta['mood'] ?? 'joyful');
        $moodMeta = self::MOODS[$moodKey] ?? self::MOODS['joyful'];

        $occasionLabel = $isCustom ? $custom : (string)$occasionMeta['title'];
        $eyebrow = $isCustom ? $custom : (string)$occasionMeta['badge'];
        $particle = $isCustom ? (string)$moodMeta['particle'] : (string)$occasionMeta['particle'];
        $emoji = $isCustom ? (string)$moodMeta['emoji'] : (string)($occasionMeta['emoji'] ?? '✨');

        $sender = (string)($row['sender_name'] ?? 'The Family');
        $appUrl = self::appUrl();

        return array_merge($row, [
            'occasion_meta' => $occasionMeta,
            'theme_meta' => $themeMeta,
            'template_style_meta' => $styleMeta,
            'mood' => $moodKey,
            'mood_meta' => $moodMeta,
            'occasion_label' => $occasionLabel,
            'eyebrow' => $eyebrow,
            'particle' => $particle,
            'emoji' => $emoji,
            'monogram' => self::monogram($sender),
            'hero_url' => self::resolveAssetUrl((string)($row['hero_photo_url'] ?? '')),
            'public_url' => '/greetings/f/' . $token,
            'image_url' => '/greetings/i/' . $token . '.png',
            'og_image_url' => '/greetings/i/' . $token . '.jpg',
            'portrait_image_url' => '/greetings/p/' . $token . '.jpg',
            'absolute_public_url' => $appUrl . '/greetings/f/' . $token,
            'absolute_image_url' => $appUrl . '/greetings/i/' . $token . '.png',
            'absolute_og_image_url' => $appUrl . '/greetings/i/' . $token . '.jpg',
            'absolute_portrait_image_url' => $appUrl . '/greetings/p/' . $token . '.jpg',
            'whatsapp_text' => self::whatsAppMessage($row, $moodKey, $emoji, $appUrl . '/greetings/f/' . $token),
        ]);
    }

    /**
     * Inline CSS custom properties for the couture card component (festive/card.blade.php).
     * Mirrors cardVars() in public/js/festive-cards.js so server and live previews match.
     *
     * @param array<string, mixed> $theme
     */
    public static function cssVars(array $theme, string $headline, string $verse): string
    {
        $hex = static fn(string $key, string $fallback): string => preg_match('/^#[0-9a-fA-F]{6}$/', (string)($theme[$key] ?? '')) ? (string)$theme[$key] : $fallback;
        $vl = mb_strlen($verse);
        $hl = mb_strlen($headline);
        $vs = $vl < 120 ? 1.0 : ($vl < 200 ? 0.92 : ($vl < 300 ? 0.82 : ($vl < 420 ? 0.72 : 0.64)));
        $hs = $hl < 26 ? 1.0 : ($hl < 44 ? 0.9 : ($hl < 70 ? 0.8 : 0.7));

        return sprintf(
            '--fc-primary:%s;--fc-accent:%s;--fc-deep:%s;--fc-light:%s;--fc-paper:%s;--fc-ink:%s;--fc-seal:%s;--fc-vs:%s;--fc-hs:%s',
            $hex('primary_color', '#7a0f2a'),
            $hex('accent_color', '#d4af37'),
            $hex('deep_color', '#1a0309'),
            $hex('light_color', '#f6e7a8'),
            $hex('paper_color', '#fbf3e4'),
            $hex('ink_color', '#5c0a1f'),
            $hex('seal_color', '#9f1239'),
            $vs,
            $hs
        );
    }

    /**
     * WhatsApp-native message (uses WhatsApp *bold* and _italic_). Link last so the rich preview renders.
     *
     * @param array<string, mixed> $row
     */
    public static function whatsAppMessage(array $row, string $mood, string $emoji, string $url): string
    {
        $recipient = trim((string)($row['recipient_name'] ?? ''));
        $sender = trim((string)($row['sender_name'] ?? 'The Family'));
        $headline = trim((string)($row['headline'] ?? ''));

        $lines = [];
        $lines[] = $recipient !== ''
            ? "💌 *A card for {$recipient}*"
            : "💌 *A card for you*";
        $lines[] = "{$emoji} _{$headline}_";
        $lines[] = '';
        $lines[] = "From {$sender}, with love.";
        $lines[] = $mood === 'reverent'
            ? 'Open it whenever you are ready.'
            : 'Tap to break the seal and open your envelope ✨';
        $lines[] = $url;

        return implode("\n", $lines);
    }

    /**
     * Process an uploaded hero photo: validate, auto-orient, downscale, re-encode (purges ALL EXIF incl. GPS).
     * Returns the public web path or throws with a user-facing message.
     */
    public static function storeHeroPhoto(string $tmpPath): string
    {
        if (!is_file($tmpPath) || !is_readable($tmpPath)) {
            throw new InvalidArgumentException('We could not read that photo. Please try another one.');
        }

        $info = @getimagesize($tmpPath);
        $mime = is_array($info) ? (string)($info['mime'] ?? '') : '';
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            throw new InvalidArgumentException('Please upload a JPG, PNG or WebP photo.');
        }

        $w = (int)($info[0] ?? 0);
        $h = (int)($info[1] ?? 0);
        if ($w < 32 || $h < 32 || $w > 12000 || $h > 12000) {
            throw new InvalidArgumentException('That photo’s dimensions are not supported.');
        }

        $src = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($tmpPath),
            'image/png' => @imagecreatefrompng($tmpPath),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($tmpPath) : false,
            default => false,
        };
        if (!$src) {
            throw new InvalidArgumentException('That photo appears to be damaged. Please try another one.');
        }

        // Respect camera orientation before the EXIF block is discarded
        if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($tmpPath);
            $orientation = is_array($exif) ? (int)($exif['Orientation'] ?? 1) : 1;
            $rotated = match ($orientation) {
                3 => imagerotate($src, 180, 0),
                6 => imagerotate($src, -90, 0),
                8 => imagerotate($src, 90, 0),
                default => null,
            };
            if ($rotated) {
                imagedestroy($src);
                $src = $rotated;
            }
        }

        $sw = imagesx($src);
        $sh = imagesy($src);
        $max = 1800;
        $scale = min(1, $max / max($sw, $sh));
        $dw = max(1, (int)round($sw * $scale));
        $dh = max(1, (int)round($sh * $scale));

        $dst = imagecreatetruecolor($dw, $dh);
        // Flatten transparency onto warm ivory so PNG cut-outs look intentional
        $ivory = imagecolorallocate($dst, 250, 246, 238);
        imagefilledrectangle($dst, 0, 0, $dw, $dh, (int)$ivory);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $dw, $dh, $sw, $sh);
        imagedestroy($src);

        $dir = self::projectRoot() . self::HERO_WEB_DIR;
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            imagedestroy($dst);
            throw new RuntimeException('Photo storage is unavailable. Please try again shortly.');
        }

        $filename = 'festive_' . bin2hex(random_bytes(10)) . '.jpg';
        imageinterlace($dst, true);
        $ok = imagejpeg($dst, $dir . '/' . $filename, 88);
        imagedestroy($dst);

        if (!$ok) {
            throw new RuntimeException('We could not save that photo. Please try again.');
        }

        return self::HERO_WEB_DIR . '/' . $filename;
    }

    /**
     * Resolve a stored /resources/... path to a URL that is actually served.
     * v1 wrote uploads under /public/resources (never served); keep those rows working.
     */
    public static function resolveAssetUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '' || !str_starts_with($url, '/resources/') || str_contains($url, '..')) {
            return '';
        }
        $root = self::projectRoot();
        if (is_file($root . $url)) {
            return $url;
        }
        if (is_file($root . '/public' . $url)) {
            return '/public' . $url;
        }
        return $url;
    }

    /** Absolute filesystem path for a resolved asset URL, or null. */
    public static function assetPath(string $url): ?string
    {
        $resolved = self::resolveAssetUrl($url);
        if ($resolved === '') {
            return null;
        }
        $path = self::projectRoot() . $resolved;
        return is_file($path) ? $path : null;
    }

    public static function projectRoot(): string
    {
        return dirname(__DIR__, 2);
    }

    public static function appUrl(): string
    {
        return rtrim((string)($_ENV['APP_URL'] ?? (getenv('APP_URL') ?: 'https://familyplatform.com')), '/ ');
    }

    /** One or two initials for the wax seal / crest monogram. */
    public static function monogram(string $name): string
    {
        $clean = preg_replace('/^(the|our|from)\s+/i', '', trim($name)) ?? '';
        $clean = preg_replace('/\s+(family|clan|household|crew)$/i', '', $clean) ?? $clean;
        $parts = preg_split('/[\s\-]+/u', trim($clean)) ?: [];
        $parts = array_values(array_filter($parts, static fn($p) => $p !== ''));
        if ($parts === []) {
            return 'F';
        }
        $first = mb_strtoupper(mb_substr($parts[0], 0, 1));
        if (count($parts) === 1) {
            return $first;
        }
        return $first . mb_strtoupper(mb_substr($parts[count($parts) - 1], 0, 1));
    }

    private static function cleanText(string $value, int $max, bool $multiline = false): string
    {
        $value = strip_tags($value);
        $value = $multiline
            ? (preg_replace("/\r\n?/", "\n", $value) ?? $value)
            : (preg_replace('/\s+/u', ' ', $value) ?? $value);
        if ($multiline) {
            $value = preg_replace("/\n{3,}/", "\n\n", $value) ?? $value;
        }
        $value = trim($value);
        return mb_strlen($value) > $max ? rtrim(mb_substr($value, 0, $max)) : $value;
    }

    private ?bool $customColsCache = null;

    /** Schema-parity guard: tolerate environments where the v2 migration has not yet run. */
    private function hasCustomOccasionColumns(): bool
    {
        if ($this->customColsCache !== null) {
            return $this->customColsCache;
        }
        try {
            $stmt = $this->db->query("SHOW COLUMNS FROM festive_cards LIKE 'custom_occasion'");
            $this->customColsCache = $stmt !== false && $stmt->fetch() !== false;
        } catch (\Throwable) {
            $this->customColsCache = false;
        }
        return $this->customColsCache;
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
