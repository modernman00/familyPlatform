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
    'version_key' => '2026_11_w15',
    'badge' => 'What’s New',
    'title' => 'Auto-Generated Video Subtitles (CC), Daily Arcade Previews & Guest Share Gate',
    'summary' => 'Toggle Closed Captions (CC) on any family video with English (Auto-generated) subtitles and customisable text size, background, and position, alongside Daily Arcade live previews and Weekly Fun Facts.',
    'primary_cta' => [
        'label' => 'Play Daily Arcade',
        'url' => '/arcade',
        'icon' => 'bi-controller',
    ],
    'playbook_url' => '/playbook',
    'items' => [
        [
            'title' => 'Closed Captions & Auto-Generated Subtitles (CC)',
            'description' => 'Tap the new CC button on any feed video or full-screen player to toggle English (Auto-generated) subtitles or customise text size, background contrast, and screen position.',
            'icon' => 'bi-badge-cc-fill',
            'color' => '#a31621',
        ],
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
    ],
];
