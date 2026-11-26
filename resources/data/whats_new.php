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
    'version_key' => '2026_11_w20',
    'badge' => 'What’s New',
    'title' => 'Family Post Sharing with Web Push Alerts, Playable In-Feed Games & Kinship @Mentions',
    'summary' => 'Every family member can now share permitted posts across WhatsApp, social apps, or the family feed with an instant Web Push alert sent to the original author — while authors can toggle sharing on or off for any post at any time!',
    'primary_cta' => [
        'label' => 'Explore Family Feed',
        'url' => '/profilePage',
        'icon' => 'bi-share-fill',
    ],
    'playbook_url' => '/playbook',
    'items' => [
        [
            'title' => 'Open Post Sharing & Author Web Push Alerts',
            'description' => 'Any authorised family member can share a relative’s post or repost it to the feed, automatically notifying the author via Web Push — or authors can lock sharing on individual posts in one tap.',
            'icon' => 'bi-send-fill',
            'color' => '#15803d',
        ],
        [
            'title' => 'Playable In-Feed Arcade Mini-Games',
            'description' => 'Sow seeds in Ayò Olópon, flip 3D letters in KinWordie, echo harmonic pads in KinPulse, and solve Mate-in-1 Chess puzzles directly inside your family post stream before launching the full game.',
            'icon' => 'bi-controller',
            'color' => '#d97706',
        ],
        [
            'title' => 'Respectful Kinship @Mentions & Post Tags',
            'description' => 'Tag any relative normally as @First Last — grandchildren automatically read @Grandpa, children read @Dad or @Mum, nephews read @Uncle, and peers see their full name, with a personal toggle in Account Settings.',
            'icon' => 'bi-at',
            'color' => '#059669',
        ],
        [
            'title' => 'Secret Group Card & Goodwill Video Drop',
            'description' => 'Tap Sign / Send a Card or Record Goodwill Video on any relative’s milestone — strictly hidden from the celebrant or couple themselves so the surprise stays intact.',
            'icon' => 'bi-envelope-heart-fill',
            'color' => '#1d72fe',
        ],
    ],
];
