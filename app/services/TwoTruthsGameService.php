<?php

declare(strict_types=1);

namespace App\services;

use App\classes\Pusher;
use App\model\AllMembersData;
use App\model\FamilyJournal;
use App\model\Post;
use Src\Db;
use PDO;

/**
 * TwoTruthsGameService
 *
 * Core engine for "Two Truths & a Lie: Heritage Edition" family game ritual.
 * Governed under Chamber 1 (Social & Lifestyle) & Chamber 2 (Engineering Council / TAT).
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class TwoTruthsGameService
{
    /**
     * Retrieve the active or most recently revealed game for the specified family.
     *
     * @param string $famCode Family network code
     * @param string $userId Current authenticated member ID
     * @return array<string, mixed>|null
     */
    public static function getActiveGame(string $famCode, string $userId): ?array
    {
        $db = Db::connect2();

        // Check for any active game whose reveal_at has passed and auto-reveal it
        $expiredStmt = $db->prepare(
            "SELECT id FROM family_game_two_truths
             WHERE famCode = ? AND status = 'active' AND reveal_at <= NOW()
             LIMIT 1"
        );
        $expiredStmt->execute([$famCode]);
        $expiredId = $expiredStmt->fetchColumn();
        if ($expiredId) {
            self::revealGame((string)$expiredId, $famCode);
        }

        // Fetch latest active or recently revealed game (within 14 days)
        $stmt = $db->prepare(
            "SELECT id, famCode, author_id, author_name, post_no, title,
                    statement_1, statement_2, statement_3, lie_index,
                    reveal_story_1, reveal_story_2, reveal_story_3, photo_url,
                    status, reveal_at, created_at
             FROM family_game_two_truths
             WHERE famCode = ? AND status IN ('active', 'revealed')
             ORDER BY created_at DESC
             LIMIT 1"
        );
        $stmt->execute([$famCode]);
        /** @var array<string, mixed>|false $game */
        $game = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$game) {
            return null;
        }

        $gameId = (string)$game['id'];
        $isRevealed = ($game['status'] === 'revealed');

        // Look up author's avatar if available
        $avatarStmt = $db->prepare("SELECT img FROM profilePics WHERE id = ? LIMIT 1");
        $avatarStmt->execute([$game['author_id']]);
        $authorAvatar = $avatarStmt->fetchColumn() ?: null;
        $game['author_avatar'] = $authorAvatar;

        // Check if current user has voted
        $voteStmt = $db->prepare(
            "SELECT guessed_lie_index, is_correct, points_awarded, voted_at
             FROM family_game_two_truths_votes
             WHERE game_id = ? AND user_id = ?
             LIMIT 1"
        );
        $voteStmt->execute([$gameId, $userId]);
        /** @var array<string, mixed>|false $userVote */
        $userVote = $voteStmt->fetch(PDO::FETCH_ASSOC);
        $game['user_vote'] = $userVote ?: null;
        $game['has_voted'] = !empty($userVote);

        // Fetch vote statistics
        $countStmt = $db->prepare(
            "SELECT guessed_lie_index, COUNT(*) as cnt
             FROM family_game_two_truths_votes
             WHERE game_id = ?
             GROUP BY guessed_lie_index"
        );
        $countStmt->execute([$gameId]);
        /** @var array<int, array{guessed_lie_index: int|string, cnt: int|string}> $rows */
        $rows = $countStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $totalVotes = 0;
        $distribution = [1 => 0, 2 => 0, 3 => 0];
        foreach ($rows as $r) {
            $idx = (int)$r['guessed_lie_index'];
            $cnt = (int)$r['cnt'];
            $distribution[$idx] = $cnt;
            $totalVotes += $cnt;
        }

        $game['total_votes'] = $totalVotes;

        // Mask server-side secret until reveal
        if (!$isRevealed) {
            unset($game['lie_index']);
            unset($game['reveal_story_1']);
            unset($game['reveal_story_2']);
            unset($game['reveal_story_3']);
            // Only show percentage distribution if the caller has already voted
            if (!$game['has_voted']) {
                $game['vote_distribution'] = null;
            } else {
                $game['vote_distribution'] = $distribution;
            }
        } else {
            $game['vote_distribution'] = $distribution;
            $correctGuesses = $distribution[(int)$game['lie_index']] ?? 0;
            $game['correct_guesses'] = $correctGuesses;
        }

        return $game;
    }

    /**
     * Register a user's vote for which statement is the lie.
     *
     * @param string $gameId
     * @param string $famCode
     * @param string $userId
     * @param string $userName
     * @param int $guessedLieIndex Must be 1, 2, or 3
     * @return array{status: string, message: string, total_votes?: int}
     */
    public static function castVote(
        string $gameId,
        string $famCode,
        string $userId,
        string $userName,
        int $guessedLieIndex
    ): array {
        if (!in_array($guessedLieIndex, [1, 2, 3], true)) {
            return ['status' => 'error', 'message' => 'Invalid choice. Must select option 1, 2, or 3.'];
        }

        $db = Db::connect2();

        // 1. Check game exists, belongs to family, and is active
        $stmt = $db->prepare(
            "SELECT id, author_id, status, reveal_at
             FROM family_game_two_truths
             WHERE id = ? AND famCode = ?
             LIMIT 1"
        );
        $stmt->execute([$gameId, $famCode]);
        /** @var array<string, mixed>|false $game */
        $game = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$game) {
            return ['status' => 'error', 'message' => 'Game round not found.'];
        }

        if ($game['status'] !== 'active') {
            return ['status' => 'error', 'message' => 'Voting for this round is closed.'];
        }

        if (strtotime((string)$game['reveal_at']) <= time()) {
            self::revealGame($gameId, $famCode);
            return ['status' => 'error', 'message' => 'Voting has ended for this round.'];
        }

        if ((string)$game['author_id'] === $userId) {
            return ['status' => 'error', 'message' => 'As the storyteller, you cannot vote on your own statements.'];
        }

        // 2. Insert vote with unique constraint guard
        $voteId = 'ttv_' . bin2hex(random_bytes(10));
        try {
            $insStmt = $db->prepare(
                "INSERT INTO family_game_two_truths_votes
                 (id, game_id, famCode, user_id, user_name, guessed_lie_index, voted_at)
                 VALUES (?, ?, ?, ?, ?, ?, NOW())"
            );
            $insStmt->execute([
                $voteId,
                $gameId,
                $famCode,
                $userId,
                $userName,
                $guessedLieIndex,
            ]);
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                return ['status' => 'error', 'message' => 'You have already voted in this round.'];
            }
            throw $e;
        }

        // 3. Count total votes
        $cntStmt = $db->prepare("SELECT COUNT(*) FROM family_game_two_truths_votes WHERE game_id = ?");
        $cntStmt->execute([$gameId]);
        $totalVotes = (int)$cntStmt->fetchColumn();

        // 4. Real-time broadcast if Pusher is available
        if (class_exists(Pusher::class)) {
            try {
                Pusher::broadcastToFamily($famCode, 'two-truths-voted', [
                    'game_id'     => $gameId,
                    'total_votes' => $totalVotes,
                ]);
            } catch (\Throwable $th) {
                error_log('[TwoTruthsGameService] Pusher broadcast failed: ' . $th->getMessage());
            }
        }

        return [
            'status'      => 'success',
            'message'     => 'Your guess has been recorded! Check back on Sunday for the grand reveal.',
            'total_votes' => $totalVotes,
        ];
    }

    /**
     * Create a new round of Two Truths & a Lie.
     *
     * @param string $famCode
     * @param string $authorId
     * @param string $authorName
     * @param array<string, mixed> $data
     * @return array{status: string, message: string, game_id?: string}
     */
    public static function createGame(
        string $famCode,
        string $authorId,
        string $authorName,
        array $data
    ): array {
        $statement1 = trim((string)($data['statement_1'] ?? ''));
        $statement2 = trim((string)($data['statement_2'] ?? ''));
        $statement3 = trim((string)($data['statement_3'] ?? ''));
        $lieIndex = (int)($data['lie_index'] ?? 0);

        if ($statement1 === '' || $statement2 === '' || $statement3 === '') {
            return ['status' => 'error', 'message' => 'All three statements are required.'];
        }

        if (!in_array($lieIndex, [1, 2, 3], true)) {
            return ['status' => 'error', 'message' => 'You must indicate which statement (1, 2, or 3) is the lie.'];
        }

        $revealStory1 = trim((string)($data['reveal_story_1'] ?? ''));
        $revealStory2 = trim((string)($data['reveal_story_2'] ?? ''));
        $revealStory3 = trim((string)($data['reveal_story_3'] ?? ''));
        $title = trim((string)($data['title'] ?? '')) ?: 'Heritage Challenge: Can you spot the lie?';

        // Reveal date: default to 48 hours from now
        $revealAt = !empty($data['reveal_at']) && is_string($data['reveal_at'])
            ? date('Y-m-d H:i:s', strtotime($data['reveal_at']))
            : date('Y-m-d H:i:s', strtotime('+48 hours'));

        $gameId = 'tt_' . bin2hex(random_bytes(10));
        $db = Db::connect2();

        // 1. Archive any existing active games for this family
        $archStmt = $db->prepare(
            "UPDATE family_game_two_truths
             SET status = 'archived'
             WHERE famCode = ? AND status = 'active'"
        );
        $archStmt->execute([$famCode]);

        // 2. Insert new game
        $insStmt = $db->prepare(
            "INSERT INTO family_game_two_truths
             (id, famCode, author_id, author_name, title,
              statement_1, statement_2, statement_3, lie_index,
              reveal_story_1, reveal_story_2, reveal_story_3,
              status, reveal_at, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, NOW())"
        );
        $insStmt->execute([
            $gameId,
            $famCode,
            $authorId,
            $authorName,
            $title,
            $statement1,
            $statement2,
            $statement3,
            $lieIndex,
            $revealStory1 ?: null,
            $revealStory2 ?: null,
            $revealStory3 ?: null,
            $revealAt,
        ]);

        // 3. Publish an announcement post to the family feed
        $postMessage = "🎭 New Family Game: Two Truths & a Lie!\n\n"
            . "{$authorName} has laid down a heritage challenge:\n"
            . "1️⃣ {$statement1}\n"
            . "2️⃣ {$statement2}\n"
            . "3️⃣ {$statement3}\n\n"
            . "Two of these are true life memories, but one is a complete lie. Can you spot it? Head over to The Fun Place Game Show arena to place your vote!";

        $postStmt = $db->prepare(
            "INSERT INTO post (id, fullName, postMessage, postFamCode, post_time, post_status, date_created)
             VALUES (?, ?, ?, ?, ?, 'publish', NOW())"
        );
        $postTime = (string)milliSeconds();
        $postStmt->execute([
            $authorId,
            $authorName,
            $postMessage,
            $famCode,
            $postTime,
        ]);
        $postNo = (int)$db->lastInsertId();

        // Link post_no to the game
        $linkStmt = $db->prepare("UPDATE family_game_two_truths SET post_no = ? WHERE id = ?");
        $linkStmt->execute([$postNo, $gameId]);

        // 4. Notify family network
        self::notifyFamilyOfGame($famCode, $authorId, $authorName, $gameId);

        return [
            'status'  => 'success',
            'message' => 'Your Two Truths & a Lie challenge is live across the family network!',
            'game_id' => $gameId,
        ];
    }

    /**
     * Unmask the lie, score all votes, and permanently archive verified memories to the Family Journal.
     *
     * @param string $gameId
     * @param string $famCode
     * @return array{status: string, message: string}
     */
    public static function revealGame(string $gameId, string $famCode): array
    {
        $db = Db::connect2();

        $stmt = $db->prepare(
            "SELECT id, famCode, author_id, author_name, title,
                    statement_1, statement_2, statement_3, lie_index,
                    reveal_story_1, reveal_story_2, reveal_story_3, status
             FROM family_game_two_truths
             WHERE id = ? AND famCode = ?
             LIMIT 1"
        );
        $stmt->execute([$gameId, $famCode]);
        /** @var array<string, mixed>|false $game */
        $game = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$game) {
            return ['status' => 'error', 'message' => 'Game round not found.'];
        }

        if ($game['status'] === 'revealed') {
            return ['status' => 'success', 'message' => 'Game is already revealed.'];
        }

        $lieIndex = (int)$game['lie_index'];

        // 1. Grade all cast votes
        $evalCorrect = $db->prepare(
            "UPDATE family_game_two_truths_votes
             SET is_correct = 1, points_awarded = 50
             WHERE game_id = ? AND guessed_lie_index = ?"
        );
        $evalCorrect->execute([$gameId, $lieIndex]);

        $evalWrong = $db->prepare(
            "UPDATE family_game_two_truths_votes
             SET is_correct = 0, points_awarded = 10
             WHERE game_id = ? AND guessed_lie_index != ?"
        );
        $evalWrong->execute([$gameId, $lieIndex]);

        // 2. Mark game as revealed
        $updStmt = $db->prepare(
            "UPDATE family_game_two_truths
             SET status = 'revealed', updated_at = NOW()
             WHERE id = ?"
        );
        $updStmt->execute([$gameId]);

        // 3. Permanent Heritage Archival Bridge: Save the 2 verified true stories into Family Journal
        try {
            $authorId = (string)$game['author_id'];
            $authorName = (string)$game['author_name'];

            for ($i = 1; $i <= 3; $i++) {
                if ($i === $lieIndex) {
                    continue; // Skip the lie!
                }
                $stKey = "statement_{$i}";
                $rvKey = "reveal_story_{$i}";
                $statementText = (string)$game[$stKey];
                $backstory = (string)($game[$rvKey] ?? '');
                $safeStatementText = htmlspecialchars($statementText, ENT_QUOTES, 'UTF-8');
                $safeBackstory = htmlspecialchars($backstory, ENT_QUOTES, 'UTF-8');

                $journalTitle = "Heritage Memory: " . mb_substr($statementText, 0, 50) . (mb_strlen($statementText) > 50 ? '...' : '');
                $journalContent = "<p><strong>Verified Family Truth:</strong> {$safeStatementText}</p>";
                if ($backstory !== '') {
                    $journalContent .= "<p><em>The Story Behind It:</em> {$safeBackstory}</p>";
                }
                $journalContent .= "<p class='text-muted small'>Unmasked during Two Truths & a Lie on FamilyPlatform.</p>";

                FamilyJournal::createJournal([
                    'family_code' => $famCode,
                    'author_id'   => $authorId,
                    'title'       => $journalTitle,
                    'slug'        => 'memory-' . bin2hex(random_bytes(6)),
                    'summary'     => $statementText,
                    'content'     => $journalContent,
                    'category'    => 'Tradition',
                    'mood'        => 'heartwarming',
                    'status'      => 'published',
                ]);
            }
        } catch (\Throwable $jErr) {
            error_log('[TwoTruthsGameService] Failed to archive memories to FamilyJournal: ' . $jErr->getMessage());
        }

        // 4. Notify family network of the grand reveal
        self::notifyFamilyOfReveal($famCode, (string)$game['author_name'], $gameId);

        return [
            'status'  => 'success',
            'message' => 'The truth has been unmasked and memories archived to the Family Journal!',
        ];
    }

    /**
     * Seed a starter sample game if the family network has zero games.
     * Ensures "The Fun Place" is immediately playable and never a cold empty state.
     *
     * @param string $famCode
     * @param string $userId
     * @param string $userName
     * @return array<string, mixed>|null
     */
    public static function ensureStarterGameIfEmpty(string $famCode, string $userId, string $userName): ?array
    {
        $db = Db::connect2();
        $checkStmt = $db->prepare("SELECT COUNT(*) FROM family_game_two_truths WHERE famCode = ?");
        $checkStmt->execute([$famCode]);
        $count = (int)$checkStmt->fetchColumn();

        if ($count > 0) {
            return self::getActiveGame($famCode, $userId);
        }

        // Create an authentic starter heritage round
        $data = [
            'title'          => 'Inaugural Family Challenge: Spot the Lie!',
            'statement_1'    => 'Our grandparents first met at a community dance hall in the 1960s.',
            'statement_2'    => 'An uncle once accidentally locked himself inside a bakery van overnight.',
            'statement_3'    => 'A great-aunt won a national radio quiz show in 1982 for identifying classical music.',
            'lie_index'      => 2, // Statement 2 is the lie!
            'reveal_story_1' => 'True! They danced to highlife and rock-and-roll for 4 hours before Grandpa worked up the courage to ask for her address.',
            'reveal_story_2' => 'Lie! He actually got locked in a corner grocery store pantry for 20 minutes before someone heard him banging.',
            'reveal_story_3' => 'True! She correctly named Mozart\'s Symphony No. 40 in G minor within 3 seconds of hearing the opening bars.',
            'reveal_at'      => date('Y-m-d H:i:s', strtotime('+3 days')),
        ];

        self::createGame($famCode, $userId, $userName ?: 'Family Platform Heritage', $data);

        return self::getActiveGame($famCode, $userId);
    }

    /**
     * Multi-channel notification for a newly published game.
     */
    private static function notifyFamilyOfGame(
        string $familyCode,
        string $authorId,
        string $authorName,
        string $gameId
    ): void {
        try {
            $members = AllMembersData::getAllMembers($familyCode);
            foreach ($members as $member) {
                $uid = (string)($member['id'] ?? '');
                if ($uid === '' || $uid === $authorId) {
                    continue;
                }
                NotificationOrchestrator::dispatch(
                    userId: $uid,
                    category: 'game',
                    priority: 'normal',
                    title: '🎭 Two Truths & a Lie Challenge!',
                    body: "{$authorName} dropped 3 life statements. Two are true, one is a lie. Can you spot it?",
                    actionUrl: '/member/family-ai#tab-trivia',
                    tag: "two-truths-{$gameId}",
                    familyCode: $familyCode,
                    metadata: ['game_id' => $gameId]
                );
            }
        } catch (\Throwable $e) {
            error_log('[TwoTruthsGameService] Game notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Multi-channel notification when the lie is unmasked.
     */
    private static function notifyFamilyOfReveal(
        string $familyCode,
        string $authorName,
        string $gameId
    ): void {
        try {
            $members = AllMembersData::getAllMembers($familyCode);
            foreach ($members as $member) {
                $uid = (string)($member['id'] ?? '');
                if ($uid === '') {
                    continue;
                }
                NotificationOrchestrator::dispatch(
                    userId: $uid,
                    category: 'game',
                    priority: 'high',
                    title: '🎭 The Lie Has Been Unmasked!',
                    body: "The truth behind {$authorName}'s challenge is out! Check your score and read the real stories.",
                    actionUrl: '/member/family-ai#tab-trivia',
                    tag: "two-truths-reveal-{$gameId}",
                    familyCode: $familyCode,
                    metadata: ['game_id' => $gameId]
                );
            }
        } catch (\Throwable $e) {
            error_log('[TwoTruthsGameService] Reveal notification failed: ' . $e->getMessage());
        }
    }
}
