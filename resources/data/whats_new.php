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
    'version_key' => '2026_11_w7',
    'badge' => 'What’s New',
    'title' => 'Native Instagram Reels, Scenic Backdrops & fresh family updates',
    'summary' => 'Paste Instagram Reels for native 720p HD playback with clean uncropped posters and real captions, frame quotes over Starlight Bokeh, and play daily games in the Family Arcade.',
    'primary_cta' => [
        'label' => 'Share a Moment',
        'url' => '/profilePage',
        'icon' => 'bi-stars',
    ],
    'playbook_url' => '/playbook',
    'items' => [
        [
            'title' => 'Native Instagram Reels & Rich Previews',
            'description' => 'Paste any Instagram Reel or post link to play 720p HD video directly in your feed with clean, uncropped posters and real creator captions.',
            'icon' => 'bi-play-circle-fill',
            'color' => '#2563eb',
        ],
        [
            'title' => 'Scenic Post Backdrops (Starlight & More)',
            'description' => 'Tap Backdrop (Aa) when posting — or restyle any text post in one tap — with Starlight Bokeh, Golden Fireflies, Sunset Horizon, Enchanted Forest, and Royal Noir.',
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
