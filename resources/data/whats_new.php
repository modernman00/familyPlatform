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
    'version_key' => '2026_11_w23',
    'badge' => 'What’s New',
    'title' => '3D Spatial FamChat Experience, 1-Tap Animate & Share to Feed, Full-Width Milestone Cards & In-Feed Arcade',
    'summary' => 'Animate any family photo or milestone card in 60fps living motion and share it directly to your family post feed in one tap, enjoy full-width 1:1 Family Milestone Spotlight cards designed for crystal-clear mobile and desktop reading, and explore the 3D Spatial FamChat experience!',
    'primary_cta' => [
        'label' => 'Open 3D FamChat',
        'url' => '/kin-whispers',
        'icon' => 'bi-chat-heart-fill',
    ],
    'playbook_url' => '/playbook',
    'items' => [
        [
            'title' => '3D Living FamChat Emblem & Interactive Cube',
            'description' => 'Spot FamChat instantly across desktop and mobile with a 3D spatial heart-bubble orb, orbiting sparks, live presence indicator, and an interactive WebGL 3D Heritage Cube inside your chat sanctuary.',
            'icon' => 'bi-chat-heart-fill',
            'color' => '#a31621',
        ],
        [
            'title' => 'Animate Any Photo & Share Directly to Feed',
            'description' => 'Tap 🎬 Animate on any feed photo or Family Historian card to bring portraits to life with 60fps reactions (Laugh, Dance, Love, Wave) and tap ✨ Share to Feed to publish the animated clip directly to your family timeline.',
            'icon' => 'bi-film',
            'color' => '#15803d',
        ],
        [
            'title' => 'Full-Width 1:1 Family Milestone Spotlight Cards',
            'description' => 'Family Historian birthday and anniversary spotlight cards now render in a bold 1200×1200 full-width centred portrait layout with large display typography and zero empty canvas space across mobile and desktop.',
            'icon' => 'bi-award-fill',
            'color' => '#d97706',
        ],
        [
            'title' => 'Respectful Kinship @Mentions & Post Tags',
            'description' => 'Tag any relative normally as @First Last — grandchildren automatically read @Grandpa, children read @Dad or @Mum, nephews read @Uncle, and peers see their full name, with a personal toggle in Account Settings.',
            'icon' => 'bi-at',
            'color' => '#059669',
        ],
    ],
];
