<?php

/**
 * Structured "What's New" Member Feed Banner Data.
 *
 * GOVERNANCE INVARIANT:
 * Whenever an important new user-facing feature or major update is shipped
 * (Tier 2 or Tier 3), update this file in this exact 4-item format and bump
 * `version_key` (e.g. '2026_11_w4' -> '2026_12_w1') so members who dismissed
 * the previous week's banner see the new updates on their feed.
 *
 * Copy must strictly follow British English (en-GB).
 *
 * @return array{
 *     version_key: string,
 *     badge: string,
 *     title: string,
 *     summary: string,
 *     primary_cta: array{label: string, url: string, icon: string},
 *     playbook_url: string,
 *     items: list<array{title: string, description: string, icon: string, color: string}>
 * }
 */
return [
    'version_key' => '2026_11_w9',
    'badge' => 'What’s New',
    'title' => 'Dynamic Festive Xmas & New Year Look, Living Backdrops & Family Arcade',
    'summary' => 'Enjoy our automatic festive Christmas look until 30 December (followed by a champagne New Year celebration from 31 December to 2 January), plus 4 festive Xmas backdrops and daily games.',
    'primary_cta' => [
        'label' => 'Share a Moment',
        'url' => '/profilePage',
        'icon' => 'bi-stars',
    ],
    'playbook_url' => '/playbook',
    'items' => [
        [
            'title' => 'Multi-Festival Dynamic Seasonal Look',
            'description' => 'FamilyPlatform automatically celebrates major global & family festivals — Christmas (until 30 Dec), New Year (31 Dec–2 Jan), Eid, Diwali, Lunar New Year, Easter, Valentine’s, Mother’s & Father’s Day — with 1-tap Pause FX & Standard Look controls.',
            'icon' => 'bi-snow2',
            'color' => '#991b1b',
        ],
        [
            'title' => 'Living Backdrops, Xmas & Solar Galaxy',
            'description' => 'Backdrops now animate with twinkling stars, rising fireflies, orbiting Solar System planets & galaxies, plus 4 festive Xmas scenes: Snowfall, Cosy Hearth, Fairy Lights, and Winter Gold.',
            'icon' => 'bi-stars',
            'color' => '#0284c7',
        ],
        [
            'title' => 'Family Arcade (Daily Games & Leagues)',
            'description' => 'Play daily family brain-teasers including Cheeseboard, Minesweeper, and Wordie with family leagues, crown badges, and live challenges.',
            'icon' => 'bi-trophy-fill',
            'color' => '#d97706',
        ],
        [
            'title' => 'Weekly Family Fun Facts (16:9 & 9:16)',
            'description' => 'Generate a 60fps animated recap of your family’s top storytellers, liveliest hour, and most-loved moments in both landscape and vertical formats.',
            'icon' => 'bi-film',
            'color' => '#059669',
        ],
    ],
];
