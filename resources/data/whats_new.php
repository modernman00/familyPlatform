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
    'version_key' => '2026_11_w14',
    'badge' => 'What’s New',
    'title' => 'Daily Arcade Live-Play Previews, Guest Share Gate & Weekly Fun Facts',
    'summary' => 'Preview today’s featured Daily Family Arcade puzzle in live step-by-step motion right on the feed before jumping in, share posts and Sparks with our sleek Guest Gate, and enjoy 60fps Weekly Family Fun Facts.',
    'primary_cta' => [
        'label' => 'Play Daily Arcade',
        'url' => '/arcade',
        'icon' => 'bi-controller',
    ],
    'playbook_url' => '/playbook',
    'items' => [
        [
            'title' => 'Daily Arcade Live-Play Motion Previews',
            'description' => 'The Daily Family Arcade banner on your feed now animates today’s featured challenge — from Wordie tile flips to Ayò seed sowing and KinZip path traces — with a one-tap Pause/Watch Demo control.',
            'icon' => 'bi-controller',
            'color' => '#d97706',
        ],
        [
            'title' => 'One-Tap Guest Share Gate & Spark Previews',
            'description' => 'Unregistered relatives opening a shared post or Daily Spark now see a rich preview behind a sleek sign-in overlay with Google, Facebook, Apple, Email, and a Skip option to browse first.',
            'icon' => 'bi-share-fill',
            'color' => '#1d72fe',
        ],
        [
            'title' => 'Weekly Family Fun Facts 60fps Rendition',
            'description' => 'Your weekly family activity is now automatically curated into a 60fps motion presentation highlighting your Top Storyteller, Golden Hour, Most-Loved Moment, and "Do You Know?" Family Historian cards.',
            'icon' => 'bi-film',
            'color' => '#b8202c',
        ],
        [
            'title' => 'Then & Now Heirloom Frame Studio',
            'description' => 'Redesigned Polaroid-style Then & Now cards integrate inline date-stamp headers, live year-apart calculation badges, Family Archive & Ghost-Pose Camera actions, and an auto-collapsing backdrop bar.',
            'icon' => 'bi-hourglass-split',
            'color' => '#a31621',
        ],
    ],
];
