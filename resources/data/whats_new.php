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
    'version_key' => '2026_11_w21',
    'badge' => 'What’s New',
    'title' => 'Personalised Invite Picker (Uncle, Aunt & Custom Titles), Open Post Sharing & In-Feed Arcade',
    'summary' => 'Invite Others now organises relatives into fast Close, Extended, and Custom tabs — adding Uncle, Aunt, and an Add Your Own personaliser so every WhatsApp or SMS invitation greets your relative by their exact family title and name!',
    'primary_cta' => [
        'label' => 'Explore Family Feed',
        'url' => '/profilePage',
        'icon' => 'bi-person-plus-fill',
    ],
    'playbook_url' => '/playbook',
    'items' => [
        [
            'title' => 'Personalised Uncle, Aunt & Custom Invites',
            'description' => 'Navigate Close, Extended, and Custom kinship tabs in Invite Others — select Uncle, Aunt, or Add your own… to personalise any family title and name with reusable quick-picks.',
            'icon' => 'bi-people-fill',
            'color' => '#a31621',
        ],
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
    ],
];
