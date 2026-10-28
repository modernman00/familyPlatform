<?php

declare(strict_types=1);

namespace App\services;

use PDO;
use Src\Db;

/**
 * 🔍 OmniSearchService: Sovereign Universal Discovery & Navigation Engine
 *
 * Provides cross-system discovery across:
 * 1. Functions & Activities (deep-link commands, tools, modals, form triggers)
 * 2. Members & Kinship (profiles, relatives, contact cards)
 * 3. Family Journals & Chronicles
 * 4. Timeline Posts & Memories
 * 5. Upcoming & Celebrated Events
 * 6. Video Reels & Announcements
 *
 * Enforces strict multi-tenant boundary isolation and zero-interpolation SQL.
 */
class OmniSearchService
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Db::connect2();
    }

    /**
     * Resolves all family codes associated with a user (primary, maternal/birth, and user_families).
     *
     * @param string $userId
     * @param string $sessionFamCode
     * @return list<string>
     */
    public function resolveUserFamilyCodes(string $userId, string $sessionFamCode = ''): array
    {
        $famCodes = [];
        $cleanSessionCode = strtoupper(trim(str_replace('#', '', $sessionFamCode)));
        if ($cleanSessionCode !== '') {
            $famCodes[] = $cleanSessionCode;
        }

        if ($userId !== '') {
            try {
                // Fetch codes from personal table
                $stmt = $this->pdo->prepare('SELECT famCode, otherFamCode, familyCode2 FROM personal WHERE id = ? LIMIT 1');
                $stmt->execute([$userId]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    foreach (['famCode', 'otherFamCode', 'familyCode2'] as $col) {
                        $val = strtoupper(trim(str_replace('#', '', (string)($row[$col] ?? ''))));
                        if ($val !== '' && !in_array($val, $famCodes, true)) {
                            $famCodes[] = $val;
                        }
                    }
                }

                // Fetch codes from user_families table
                $stmtUf = $this->pdo->prepare('SELECT family_code FROM user_families WHERE user_id = ?');
                $stmtUf->execute([$userId]);
                $ufRows = $stmtUf->fetchAll(PDO::FETCH_ASSOC) ?: [];
                foreach ($ufRows as $ufRow) {
                    $val = strtoupper(trim(str_replace('#', '', (string)($ufRow['family_code'] ?? ''))));
                    if ($val !== '' && !in_array($val, $famCodes, true)) {
                        $famCodes[] = $val;
                    }
                }
            } catch (\Throwable $e) {
                \error_log('[OmniSearchService::resolveUserFamilyCodes] Error: ' . $e->getMessage());
            }
        }

        return array_values(array_unique($famCodes));
    }

    /**
     * Master catalog of system functions, interactive tools, and direct deep-links.
     *
     * @param string $userId
     * @param string $primaryFamCode
     * @return array<int, array<string, mixed>>
     */
    public function getActionCatalog(string $userId = '', string $primaryFamCode = ''): array
    {
        $treeUrl = $userId !== '' ? "/organogram/{$userId}?mode=canvas" : '/organogram';

        return [
            // --- Lineage & Family Codes ---
            [
                'id' => 'act_create_family_code',
                'title' => 'Create Family Code',
                'description' => 'Generate a unique birth or household family code for your lineage',
                'keywords' => ['create family code', 'make family code', 'new family code', 'birth family code', 'dual lineage', 'family code', 'generate code', 'household code', 'create code', 'register family code', 'found family'],
                'category' => 'Functions & Tools',
                'icon' => 'bi-key-fill',
                'iconColor' => '#b8202c',
                'badge' => 'Lineage',
                'url' => '/accountSetting#v-pills-familycode',
                'targetElement' => '#birthFamilyCard',
                'actionType' => 'deep_link',
                'priority' => 100,
            ],
            [
                'id' => 'act_link_birth_code',
                'title' => 'Link to Birth / Maiden Family Code',
                'description' => 'Connect your maternal lineage without changing your household code',
                'keywords' => ['link family code', 'connect birth code', 'maiden name code', 'maternal family code', 'dual family', 'link code'],
                'category' => 'Functions & Tools',
                'icon' => 'bi-link-45deg',
                'iconColor' => '#0d9488',
                'badge' => 'Lineage',
                'url' => '/accountSetting#v-pills-familycode',
                'targetElement' => '#linkBirthCodeForm',
                'actionType' => 'deep_link',
                'priority' => 95,
            ],
            [
                'id' => 'act_rename_family_code',
                'title' => 'Rename Family Code',
                'description' => 'Request an official surname or identifier rename for your family code',
                'keywords' => ['rename family code', 'change family code', 'edit family code', 'update family code', 'rename code'],
                'category' => 'Functions & Tools',
                'icon' => 'bi-pencil-fill',
                'iconColor' => '#d97706',
                'badge' => 'Lineage',
                'url' => '/accountSetting#v-pills-familycode',
                'targetElement' => '#renameCodeCard',
                'actionType' => 'deep_link',
                'priority' => 90,
            ],
            [
                'id' => 'act_family_migration',
                'title' => 'Family Migration & Code Discovery',
                'description' => 'Discover related family codes and request kinship network alignment',
                'keywords' => ['family migration', 'migrate family', 'discover family', 'switch family code', 'merge family'],
                'category' => 'Functions & Tools',
                'icon' => 'bi-arrow-left-right',
                'iconColor' => '#4f46e5',
                'badge' => 'Network',
                'url' => '/family/migrate',
                'targetElement' => '',
                'actionType' => 'navigate',
                'priority' => 85,
            ],

            // --- Family Tree & Kinship ---
            [
                'id' => 'act_add_relative',
                'title' => 'Add Relative to Family Tree',
                'description' => 'Add parents, siblings, children, spouses, in-laws or aunts/uncles',
                'keywords' => ['add relative', 'add member', 'add parent', 'add child', 'add father', 'add mother', 'add sibling', 'add brother', 'add sister', 'add spouse', 'add wife', 'add husband', 'add in law', 'add uncle', 'add aunt', 'tree editor', 'lineage wizard'],
                'category' => 'Functions & Tools',
                'icon' => 'bi-person-plus-fill',
                'iconColor' => '#16a34a',
                'badge' => 'Family Tree',
                'url' => '/organogram/editor',
                'targetElement' => '#treeEditorApp',
                'actionType' => 'navigate',
                'priority' => 98,
            ],
            [
                'id' => 'act_view_tree',
                'title' => 'View Interactive Family Tree',
                'description' => 'Explore the visual multi-generational organogram canvas with pan & zoom',
                'keywords' => ['family tree', 'organogram', 'view tree', 'visual tree', 'ancestry', 'pedigree', 'canvas tree', 'kinship tree'],
                'category' => 'Functions & Tools',
                'icon' => 'bi-diagram-3-fill',
                'iconColor' => '#0284c7',
                'badge' => 'Family Tree',
                'url' => $treeUrl,
                'targetElement' => '',
                'actionType' => 'navigate',
                'priority' => 96,
            ],

            // --- Media & Memories ---
            [
                'id' => 'act_family_studio',
                'title' => 'Family Studio & Photo Albums',
                'description' => 'Browse, organise, and upload family photographs, milestones, and albums',
                'keywords' => ['family studio', 'photos', 'pictures', 'photo albums', 'gallery', 'images', 'upload photo', 'memories studio'],
                'category' => 'Functions & Tools',
                'icon' => 'bi-stars',
                'iconColor' => '#8b5cf6',
                'badge' => 'Media',
                'url' => '/familyStudio',
                'targetElement' => '',
                'actionType' => 'navigate',
                'priority' => 92,
            ],
            [
                'id' => 'act_family_reels',
                'title' => 'Family Video Reels',
                'description' => 'Watch, record, and share short immersive family video moments',
                'keywords' => ['family reels', 'reels', 'videos', 'video clips', 'shorts', 'record video', 'watch reels'],
                'category' => 'Functions & Tools',
                'icon' => 'bi-camera-reels-fill',
                'iconColor' => '#e11d48',
                'badge' => 'Media',
                'url' => '/reels',
                'targetElement' => '',
                'actionType' => 'navigate',
                'priority' => 88,
            ],
            [
                'id' => 'act_family_journal',
                'title' => 'Family Journal & Chronicles',
                'description' => 'Read chronicles, heritage reflections, and family milestones',
                'keywords' => ['family journal', 'journal', 'journals', 'diary', 'stories', 'chronicles', 'reflections', 'milestones', 'family chronicles', 'read journal'],
                'category' => 'Functions & Tools',
                'icon' => 'bi-journal-bookmark-fill',
                'iconColor' => '#ea580c',
                'badge' => 'Stories',
                'url' => '/familyJournal',
                'targetElement' => '',
                'actionType' => 'navigate',
                'priority' => 94,
            ],
            [
                'id' => 'act_write_journal',
                'title' => 'Write New Journal Entry',
                'description' => 'Record a new family story, chronicle, or memory with photos',
                'keywords' => ['write journal', 'new journal', 'create journal', 'add memory', 'write story', 'record reflection', 'new story', 'family journal', 'add journal'],
                'category' => 'Functions & Tools',
                'icon' => 'bi-pencil-square',
                'iconColor' => '#ca8a04',
                'badge' => 'Stories',
                'url' => '/familyJournal/create',
                'targetElement' => '',
                'actionType' => 'navigate',
                'priority' => 93,
            ],
            [
                'id' => 'act_create_post',
                'title' => 'Create Post / Share Update',
                'description' => 'Share a celebration, announcement, or message on the family timeline',
                'keywords' => ['create post', 'new post', 'share update', 'post message', 'timeline update', 'celebration post', 'share photo'],
                'category' => 'Functions & Tools',
                'icon' => 'bi-chat-heart-fill',
                'iconColor' => '#b8202c',
                'badge' => 'Community',
                'url' => '/profilePage?action=newPost',
                'targetElement' => '#postModal',
                'actionType' => 'modal_trigger',
                'priority' => 93,
            ],

            // --- Community & Growth ---
            [
                'id' => 'act_invite_others',
                'title' => 'Invite Relatives & Friends',
                'description' => 'Send WhatsApp, SMS, or email invitation links with your family code',
                'keywords' => ['invite', 'invite others', 'invite relatives', 'bring family', 'share link', 'refer', 'invite code', 'recommend friends'],
                'category' => 'Functions & Tools',
                'icon' => 'bi-person-plus-fill',
                'iconColor' => '#f59e0b',
                'badge' => 'Growth',
                'url' => 'modal:recommendFriendsModal',
                'targetElement' => '#recommendFriendsModal',
                'actionType' => 'modal_trigger',
                'priority' => 97,
            ],
            [
                'id' => 'act_all_members',
                'title' => 'Family Directory & All Members',
                'description' => 'Search verified members, kinship connections, and view profiles',
                'keywords' => ['directory', 'all members', 'family members', 'members list', 'relatives list', 'kinship directory', 'contacts'],
                'category' => 'Functions & Tools',
                'icon' => 'bi-people-fill',
                'iconColor' => '#2563eb',
                'badge' => 'Directory',
                'url' => '/allMembers',
                'targetElement' => '',
                'actionType' => 'navigate',
                'priority' => 89,
            ],

            // --- AI & Kinship Governance ---
            [
                'id' => 'act_roundtable',
                'title' => 'Family RoundTable & Dispute Council',
                'description' => 'Mediate household disagreements and kinship reconciliation councils',
                'keywords' => ['roundtable', 'dispute', 'council', 'arbitration', 'kinship reconciliation', 'disagreement', 'mediate', 'referee', 'family tribunal', 'judge'],
                'category' => 'Functions & Tools',
                'icon' => 'bi-shield-check',
                'iconColor' => '#059669',
                'badge' => 'Council',
                'url' => '/family/roundtable',
                'targetElement' => '',
                'actionType' => 'navigate',
                'priority' => 84,
            ],
            [
                'id' => 'act_ai_hub',
                'title' => 'Family AI Hub & Games',
                'description' => 'AI fridge recipes, family trivia generator, voice memos and event flyers',
                'keywords' => ['ai hub', 'the fun place', 'fridge recipe', 'inspect fridge', 'trivia', 'family games', 'voice memo', 'flyer generator', 'weekend outings', 'ai assistant'],
                'category' => 'Functions & Tools',
                'icon' => 'bi-robot',
                'iconColor' => '#9333ea',
                'badge' => 'AI Tools',
                'url' => '/family/fun-place',
                'targetElement' => '',
                'actionType' => 'navigate',
                'priority' => 86,
            ],

            // --- Account, Security & Supervision ---
            [
                'id' => 'act_account_settings',
                'title' => 'Account Settings & Preferences',
                'description' => 'Manage notification preferences, display options, and account details',
                'keywords' => ['settings', 'account settings', 'preferences', 'notifications setting', 'configuration', 'my account'],
                'category' => 'Functions & Tools',
                'icon' => 'bi-gear-fill',
                'iconColor' => '#64748b',
                'badge' => 'Account',
                'url' => '/accountSetting',
                'targetElement' => '',
                'actionType' => 'navigate',
                'priority' => 80,
            ],
            [
                'id' => 'act_edit_profile',
                'title' => 'Edit Profile Details & Avatar',
                'description' => 'Update your full name, bio, telephone number, and profile photograph',
                'keywords' => ['edit profile', 'profile photo', 'profile picture', 'avatar', 'change photo', 'update bio', 'personal details', 'phone number'],
                'category' => 'Functions & Tools',
                'icon' => 'bi-person-badge-fill',
                'iconColor' => '#0284c7',
                'badge' => 'Account',
                'url' => '/accountSetting#v-pills-profile',
                'targetElement' => '#v-pills-profile',
                'actionType' => 'deep_link',
                'priority' => 82,
            ],
            [
                'id' => 'act_security_passwords',
                'title' => 'Security, PIN & Passkeys',
                'description' => 'Update login password, parent/child PIN, and configure biometric passkeys',
                'keywords' => ['password', 'change password', 'pin', 'change pin', 'security', 'passkeys', 'biometrics', 'face id', 'fingerprint', 'credentials'],
                'category' => 'Functions & Tools',
                'icon' => 'bi-shield-lock-fill',
                'iconColor' => '#dc2626',
                'badge' => 'Security',
                'url' => '/accountSetting#v-pills-password',
                'targetElement' => '#v-pills-password',
                'actionType' => 'deep_link',
                'priority' => 81,
            ],
            [
                'id' => 'act_child_accounts',
                'title' => 'Child Accounts & Parental Supervision',
                'description' => 'Switch between child accounts and manage underage family members',
                'keywords' => ['child accounts', 'kids', 'children', 'switch child', 'parent pin', 'child profile', 'kid account', 'supervision'],
                'category' => 'Functions & Tools',
                'icon' => 'bi-balloon-heart-fill',
                'iconColor' => '#ec4899',
                'badge' => 'Family',
                'url' => '/profilePage#childAccountsSection',
                'targetElement' => '#childAccountSelector',
                'actionType' => 'navigate',
                'priority' => 79,
            ],
            [
                'id' => 'act_privacy_gdpr',
                'title' => 'Privacy & GDPR Data Export',
                'description' => 'Download an archive of your personal data or review privacy controls',
                'keywords' => ['privacy', 'gdpr', 'export data', 'download data', 'data archive', 'delete account', 'data protection'],
                'category' => 'Functions & Tools',
                'icon' => 'bi-file-earmark-lock-fill',
                'iconColor' => '#475569',
                'badge' => 'Privacy',
                'url' => '/accountSetting#v-pills-privacy',
                'targetElement' => '#v-pills-privacy',
                'actionType' => 'deep_link',
                'priority' => 75,
            ],
            [
                'id' => 'act_playbook',
                'title' => 'Living Guide & Playbook',
                'description' => 'Explore the architectural guide, how-to-use workflows, and tutorials',
                'keywords' => ['guide', 'how to use', 'playbook', 'help', 'documentation', 'tutorial', 'instructions', 'faq', 'support'],
                'category' => 'Functions & Tools',
                'icon' => 'bi-book-half',
                'iconColor' => '#6366f1',
                'badge' => 'Help',
                'url' => '/playbook',
                'targetElement' => '',
                'actionType' => 'navigate',
                'priority' => 77,
            ],
            [
                'id' => 'act_toggle_theme',
                'title' => 'Toggle Theme (Dark / Light Mode)',
                'description' => 'Switch between high-contrast dark mode and daylight mode',
                'keywords' => ['theme', 'dark mode', 'light mode', 'night mode', 'toggle theme', 'appearance', 'colour mode'],
                'category' => 'Functions & Tools',
                'icon' => 'bi-moon-stars-fill',
                'iconColor' => '#f59e0b',
                'badge' => 'Appearance',
                'url' => 'action:toggleTheme',
                'targetElement' => '#darkModeToggle',
                'actionType' => 'action',
                'priority' => 76,
            ],
            [
                'id' => 'act_signout',
                'title' => 'Sign Out',
                'description' => 'Securely log off and end your active FamilyPlatform session',
                'keywords' => ['logout', 'sign out', 'exit', 'log off', 'leave'],
                'category' => 'Functions & Tools',
                'icon' => 'bi-box-arrow-right',
                'iconColor' => '#ef4444',
                'badge' => 'Auth',
                'url' => '/signout',
                'targetElement' => '',
                'actionType' => 'navigate',
                'priority' => 50,
            ],
        ];
    }

    /**
     * Filters action catalog by user query using intelligent synonym and token matching.
     *
     * @param string $query
     * @param string $userId
     * @param string $primaryFamCode
     * @return array<int, array<string, mixed>>
     */
    public function searchActions(string $query, string $userId = '', string $primaryFamCode = ''): array
    {
        $cleanQuery = strtolower(trim($query));
        $catalog = $this->getActionCatalog($userId, $primaryFamCode);

        if ($cleanQuery === '') {
            // Return top priority suggestions
            usort($catalog, static fn(array $a, array $b): int => ((int)($b['priority'] ?? 0)) <=> ((int)($a['priority'] ?? 0)));
            return array_slice($catalog, 0, 8);
        }

        $queryTokens = array_filter(preg_split('/\s+/', $cleanQuery) ?: [], static fn(string $t): bool => strlen($t) > 1);
        $matches = [];

        foreach ($catalog as $item) {
            $score = 0;
            $titleLower = strtolower((string)$item['title']);
            $descLower = strtolower((string)$item['description']);
            $keywords = array_map('strtolower', (array)($item['keywords'] ?? []));

            // 1. Exact phrase match in title or keywords
            if (str_contains($titleLower, $cleanQuery)) {
                $score += 250;
            }
            foreach ($keywords as $kw) {
                if ($kw === $cleanQuery) {
                    $score += 300;
                    break;
                }
                if (str_contains($kw, $cleanQuery)) {
                    $score += 150;
                }
            }

            // 2. Token overlap & partial word matching
            foreach ($queryTokens as $tok) {
                if (str_contains($titleLower, $tok)) {
                    $score += 60;
                }
                if (str_contains($descLower, $tok)) {
                    $score += 30;
                }
                foreach ($keywords as $kw) {
                    if (str_contains($kw, $tok)) {
                        $score += 50;
                    }
                }
            }

            // Direct intent boosts
            if (str_contains($cleanQuery, 'journal') && in_array($item['id'], ['act_family_journal', 'act_write_journal'], true)) {
                $score += 200;
            }
            if (str_contains($cleanQuery, 'code') && in_array($item['id'], ['act_create_family_code', 'act_link_birth_code', 'act_rename_family_code'], true)) {
                $score += 180;
            }
            if (str_contains($cleanQuery, 'tree') && in_array($item['id'], ['act_view_tree', 'act_add_relative'], true)) {
                $score += 180;
            }

            if ($score > 0) {
                $item['matchScore'] = $score + ($item['priority'] ?? 0);
                $matches[] = $item;
            }
        }

        usort($matches, static fn(array $a, array $b): int => ((int)$b['matchScore']) <=> ((int)$a['matchScore']));

        return array_slice($matches, 0, 8);
    }

    /**
     * Searches multi-tenant data (Members, Journals, Posts, Events, Announcements, Reels).
     *
     * @param string $query
     * @param string $userId
     * @param list<string> $famCodes
     * @return array<string, list<array<string, mixed>>>
     */
    public function searchData(string $query, string $userId, array $famCodes): array
    {
        $cleanQuery = trim($query);
        if ($cleanQuery === '') {
            return [
                'members' => [],
                'journals' => [],
                'posts' => [],
                'events' => [],
                'reels' => [],
            ];
        }

        $cleanLower = strtolower($cleanQuery);
        $tokens = array_filter(preg_split('/\s+/', $cleanLower) ?: [], static fn(string $t): bool => strlen($t) >= 2);
        $like = '%' . $cleanQuery . '%';

        $results = [
            'members' => [],
            'journals' => [],
            'posts' => [],
            'events' => [],
            'reels' => [],
        ];

        // 1. Members Search
        try {
            $memberParams = [$like, $like, $like, $like];
            $tokenSql = '';
            foreach ($tokens as $tok) {
                $tokLike = '%' . $tok . '%';
                $tokenSql .= ' OR p.firstName LIKE ? OR p.lastName LIKE ?';
                $memberParams[] = $tokLike;
                $memberParams[] = $tokLike;
            }

            $famFilter = '';
            if (!empty($famCodes)) {
                $placeholders = implode(',', array_fill(0, count($famCodes), '?'));
                $placeholdersUf = implode(',', array_fill(0, count($famCodes), '?'));
                $famFilter = " AND (UPPER(TRIM(REPLACE(p.famCode, '#', ''))) IN ({$placeholders}) OR EXISTS (
                    SELECT 1 FROM user_families uf 
                    WHERE uf.user_id = p.id 
                      AND UPPER(TRIM(REPLACE(uf.family_code, '#', ''))) IN ({$placeholdersUf})
                ))";
                $memberParams = array_merge($memberParams, $famCodes, $famCodes);
            }

            $sqlMembers = "
                SELECT DISTINCT 
                    p.id, 
                    p.firstName, 
                    p.lastName, 
                    p.famCode, 
                    CONCAT(p.firstName, ' ', p.lastName) AS fullName,
                    pp.img AS profileImg
                FROM personal p
                LEFT JOIN profilepics pp ON pp.id = p.id
                WHERE ((p.firstName LIKE ? OR p.lastName LIKE ? OR CONCAT(p.firstName, ' ', p.lastName) LIKE ? OR p.famCode LIKE ?) {$tokenSql})
                {$famFilter}
                ORDER BY p.lastName ASC, p.firstName ASC
                LIMIT 8
            ";
            $stmtM = $this->pdo->prepare($sqlMembers);
            $stmtM->execute($memberParams);
            $rows = $stmtM->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach ($rows as $r) {
                $results['members'][] = [
                    'id' => (string)$r['id'],
                    'title' => trim((string)$r['fullName']),
                    'description' => 'Family Code: #' . ltrim((string)$r['famCode'], '#'),
                    'category' => 'Family Members',
                    'icon' => 'bi-person-circle',
                    'badge' => '#' . ltrim((string)$r['famCode'], '#'),
                    'url' => '/organogram/' . urlencode((string)$r['id']) . '?mode=canvas',
                    'avatar' => (string)($r['profileImg'] ?? ''),
                ];
            }
        } catch (\Throwable $e) {
            \error_log('[OmniSearchService::searchData Members] ' . $e->getMessage());
        }

        // 2. Family Journals Search
        try {
            $isJournalIntent = str_contains($cleanLower, 'journal');
            $journalParams = [$like, $like, $like, $like];
            $tokenJSql = '';
            foreach ($tokens as $tok) {
                if ($tok !== 'journal' && strlen($tok) >= 3) {
                    $tLike = '%' . $tok . '%';
                    $tokenJSql .= ' OR title LIKE ? OR summary LIKE ? OR content LIKE ?';
                    $journalParams[] = $tLike;
                    $journalParams[] = $tLike;
                    $journalParams[] = $tLike;
                }
            }

            $jFamFilter = '';
            if (!empty($famCodes)) {
                $jPlaceholders = implode(',', array_fill(0, count($famCodes), '?'));
                $jFamFilter = " AND (author_id = ? OR UPPER(TRIM(REPLACE(family_code, '#', ''))) IN ({$jPlaceholders}))";
                $journalParams = array_merge($journalParams, [$userId], $famCodes);
            } else {
                $jFamFilter = " AND author_id = ?";
                $journalParams[] = $userId;
            }

            $sqlJournals = "
                SELECT id, family_code, author_id, title, summary, category, created_at
                FROM family_journals
                WHERE ((title LIKE ? OR summary LIKE ? OR content LIKE ? OR category LIKE ?) {$tokenJSql})
                  AND status != 'archived'
                  {$jFamFilter}
                ORDER BY created_at DESC
                LIMIT 6
            ";
            $stmtJ = $this->pdo->prepare($sqlJournals);
            $stmtJ->execute($journalParams);
            $jRows = $stmtJ->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach ($jRows as $jr) {
                $results['journals'][] = [
                    'id' => (string)$jr['id'],
                    'title' => (string)$jr['title'],
                    'description' => (string)($jr['summary'] ?: 'Family chronicle and memory'),
                    'category' => 'Family Journals',
                    'icon' => 'bi-journal-text',
                    'badge' => (string)($jr['category'] ?: 'Story'),
                    'url' => '/familyJournal/' . (int)$jr['id'],
                    'date' => (string)$jr['created_at'],
                ];
            }

            // Fallback for generic journal discovery intent if specific text matched few items
            if ($isJournalIntent && count($results['journals']) < 3) {
                $existingIds = array_column($results['journals'], 'id');
                $recentParams = [$userId];
                $recentFamSql = '';
                if (!empty($famCodes)) {
                    $rPlaceholders = implode(',', array_fill(0, count($famCodes), '?'));
                    $recentFamSql = " OR UPPER(TRIM(REPLACE(family_code, '#', ''))) IN ({$rPlaceholders})";
                    $recentParams = array_merge($recentParams, $famCodes);
                }

                $sqlRecent = "
                    SELECT id, family_code, author_id, title, summary, category, created_at
                    FROM family_journals
                    WHERE status != 'archived'
                      AND (author_id = ? {$recentFamSql})
                    ORDER BY created_at DESC
                    LIMIT 4
                ";
                $stmtRecent = $this->pdo->prepare($sqlRecent);
                $stmtRecent->execute($recentParams);
                $recentRows = $stmtRecent->fetchAll(PDO::FETCH_ASSOC) ?: [];
                foreach ($recentRows as $rr) {
                    $idStr = (string)$rr['id'];
                    if (!in_array($idStr, $existingIds, true)) {
                        $results['journals'][] = [
                            'id' => $idStr,
                            'title' => (string)$rr['title'],
                            'description' => (string)($rr['summary'] ?: 'Family chronicle and memory'),
                            'category' => 'Family Journals',
                            'icon' => 'bi-journal-text',
                            'badge' => (string)($rr['category'] ?: 'Story'),
                            'url' => '/familyJournal/' . (int)$rr['id'],
                            'date' => (string)$rr['created_at'],
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            \error_log('[OmniSearchService::searchData Journals] ' . $e->getMessage());
        }

        // 3. Timeline Posts Search
        try {
            $postParams = [$like, $like];
            $tokenPSql = '';
            foreach ($tokens as $tok) {
                if (strlen($tok) >= 3) {
                    $tokenPSql .= ' OR postMessage LIKE ? OR fullName LIKE ?';
                    $postParams[] = '%' . $tok . '%';
                    $postParams[] = '%' . $tok . '%';
                }
            }

            $postFamFilter = '';
            if (!empty($famCodes)) {
                $pPlaceholders = implode(',', array_fill(0, count($famCodes), '?'));
                $postFamFilter = " AND UPPER(TRIM(REPLACE(postFamCode, '#', ''))) IN ({$pPlaceholders})";
                $postParams = array_merge($postParams, $famCodes);
            }

            $sqlPosts = "
                SELECT post_no, id, fullName, postMessage, postFamCode, post_time, post_img0
                FROM post
                WHERE ((postMessage LIKE ? OR fullName LIKE ?) {$tokenPSql})
                  AND post_status = 'active'
                  {$postFamFilter}
                ORDER BY post_no DESC
                LIMIT 6
            ";
            $stmtP = $this->pdo->prepare($sqlPosts);
            $stmtP->execute($postParams);
            $pRows = $stmtP->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach ($pRows as $pr) {
                $msg = strip_tags((string)$pr['postMessage']);
                $snippet = strlen($msg) > 90 ? substr($msg, 0, 87) . '...' : $msg;
                $results['posts'][] = [
                    'id' => (string)$pr['post_no'],
                    'title' => (string)$pr['fullName'] . ' update',
                    'description' => $snippet !== '' ? $snippet : 'Family timeline update',
                    'category' => 'Timeline Posts',
                    'icon' => 'bi-chat-left-quote-fill',
                    'badge' => '#' . ltrim((string)$pr['postFamCode'], '#'),
                    'url' => '/profilePage#post-' . (int)$pr['post_no'],
                    'hasImage' => !empty($pr['post_img0']),
                ];
            }
        } catch (\Throwable $e) {
            \error_log('[OmniSearchService::searchData Posts] ' . $e->getMessage());
        }

        // 4. Events Search
        try {
            $eventParams = [$like, $like, $like, $like];
            $tokenESql = '';
            foreach ($tokens as $tok) {
                if (strlen($tok) >= 3) {
                    $tokenESql .= ' OR eventName LIKE ? OR eventDescription LIKE ?';
                    $eventParams[] = '%' . $tok . '%';
                    $eventParams[] = '%' . $tok . '%';
                }
            }

            $sqlEvents = "
                SELECT no, id, eventName, eventDate, eventType, eventLocation, eventDescription
                FROM events
                WHERE ((eventName LIKE ? OR eventDescription LIKE ? OR eventLocation LIKE ? OR eventType LIKE ?) {$tokenESql})
                  AND deleted_at IS NULL
                ORDER BY eventDate ASC
                LIMIT 6
            ";
            $stmtE = $this->pdo->prepare($sqlEvents);
            $stmtE->execute($eventParams);
            $eRows = $stmtE->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach ($eRows as $er) {
                $results['events'][] = [
                    'id' => (string)$er['no'],
                    'title' => (string)$er['eventName'],
                    'description' => trim((string)$er['eventType'] . ' • ' . (string)$er['eventLocation']),
                    'category' => 'Events & Gatherings',
                    'icon' => 'bi-calendar-event-fill',
                    'badge' => (string)$er['eventDate'],
                    'url' => '/profilePage#event-' . (int)$er['no'],
                ];
            }
        } catch (\Throwable $e) {
            \error_log('[OmniSearchService::searchData Events] ' . $e->getMessage());
        }

        // 5. Video Reels Search
        try {
            $reelParams = [$like, $like];
            $rFamFilter = '';
            if (!empty($famCodes)) {
                $rPlaceholders = implode(',', array_fill(0, count($famCodes), '?'));
                $rFamFilter = " AND UPPER(TRIM(REPLACE(famCode, '#', ''))) IN ({$rPlaceholders})";
                $reelParams = array_merge($reelParams, $famCodes);
            }

            $sqlReels = "
                SELECT id, caption, thumbnail_url, category, music_title
                FROM family_reels
                WHERE (caption LIKE ? OR category LIKE ?)
                {$rFamFilter}
                ORDER BY id DESC
                LIMIT 4
            ";
            $stmtR = $this->pdo->prepare($sqlReels);
            $stmtR->execute($reelParams);
            $rRows = $stmtR->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach ($rRows as $rr) {
                $cap = strip_tags((string)$rr['caption']);
                $results['reels'][] = [
                    'id' => (string)$rr['id'],
                    'title' => $cap !== '' ? (strlen($cap) > 50 ? substr($cap, 0, 47) . '...' : $cap) : 'Family Video Moment',
                    'description' => (string)($rr['music_title'] ?: ($rr['category'] ?: 'Video Reel')),
                    'category' => 'Video Reels',
                    'icon' => 'bi-camera-reels-fill',
                    'badge' => (string)($rr['category'] ?: 'Reel'),
                    'url' => '/reels',
                    'thumbnail' => (string)($rr['thumbnail_url'] ?? ''),
                ];
            }
        } catch (\Throwable $e) {
            \error_log('[OmniSearchService::searchData Reels] ' . $e->getMessage());
        }

        return $results;
    }

    /**
     * Unified OmniSearch execution: combines scored actions and live tenant data.
     *
     * @param string $query
     * @param string $userId
     * @param string $sessionFamCode
     * @return array<string, mixed>
     */
    public function search(string $query, string $userId, string $sessionFamCode = ''): array
    {
        $famCodes = $this->resolveUserFamilyCodes($userId, $sessionFamCode);
        $primaryCode = $famCodes[0] ?? $sessionFamCode;

        $actions = $this->searchActions($query, $userId, $primaryCode);
        $data = $this->searchData($query, $userId, $famCodes);

        $totalMatches = count($actions)
            + count($data['members'])
            + count($data['journals'])
            + count($data['posts'])
            + count($data['events'])
            + count($data['reels']);

        return [
            'query' => trim($query),
            'total' => $totalMatches,
            'categories' => [
                'actions' => $actions,
                'members' => $data['members'],
                'journals' => $data['journals'],
                'posts' => $data['posts'],
                'events' => $data['events'],
                'reels' => $data['reels'],
            ],
            'meta' => [
                'user_id' => $userId,
                'primary_family_code' => $primaryCode,
                'has_actions' => !empty($actions),
                'has_data' => ($totalMatches - count($actions)) > 0,
            ],
        ];
    }
}
