<?php
declare(strict_types=1);

namespace App\model;

use Src\Db;

class PollData extends Db {
    
    public static function createPoll(int $postNo, string $question, array $options): bool {
        $db = self::connect2();

        try {
            $db->beginTransaction();

            $stmt = $db->prepare("INSERT INTO post_polls (post_no, question, is_active) VALUES (?, ?, 1)");
            $stmt->execute([$postNo, $question]);
            
            $pollId = $db->lastInsertId();
            
            $stmtOpt = $db->prepare("INSERT INTO post_poll_options (poll_id, option_text) VALUES (?, ?)");
            foreach ($options as $opt) {
                if (!empty(trim($opt))) {
                    $stmtOpt->execute([$pollId, trim($opt)]);
                }
            }
            
            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
    
    public static function castVote(int $optionId, string $userId): bool {
        $db = self::connect2();
        
        // Find which poll this option belongs to
        $stmt = $db->prepare("SELECT poll_id FROM post_poll_options WHERE option_id = ?");
        $stmt->execute([$optionId]);
        $poll = $stmt->fetch();
        
        if (!$poll) return false;
        
        // Check if user already voted for THIS specific option
        $stmtCheck = $db->prepare("
            SELECT vote_id 
            FROM post_poll_votes 
            WHERE option_id = ? AND user_id = ?
        ");
        $stmtCheck->execute([$optionId, $userId]);
        $existing = $stmtCheck->fetch();
        
        if ($existing) {
            // Toggle off (remove vote)
            $stmtDel = $db->prepare("DELETE FROM post_poll_votes WHERE vote_id = ?");
            $stmtDel->execute([$existing['vote_id']]);
        } else {
            // Insert vote (allows multiple choice per poll)
            $stmtIns = $db->prepare("INSERT INTO post_poll_votes (option_id, user_id) VALUES (?, ?)");
            $stmtIns->execute([$optionId, $userId]);
        }
        
        return true;
    }
    
    public static function getPollsForPosts(array $postIds, string $userId): array {
        if (empty($postIds)) return [];
        $placeholders = implode(',', array_fill(0, count($postIds), '?'));
        $db = self::connect2();
        
        // Get Polls
        $stmt = $db->prepare("SELECT * FROM post_polls WHERE post_no IN ($placeholders) AND is_active = 1");
        $stmt->execute($postIds);
        $polls = $stmt->fetchAll();
        
        if (empty($polls)) return [];
        
        $pollIds = array_column($polls, 'poll_id');
        $pollPlaceholders = implode(',', array_fill(0, count($pollIds), '?'));
        
        // Get Options and Vote Counts
        $stmtOpt = $db->prepare("
            SELECT o.option_id, o.poll_id, o.option_text, COUNT(v.vote_id) as vote_count 
            FROM post_poll_options o
            LEFT JOIN post_poll_votes v ON o.option_id = v.option_id
            WHERE o.poll_id IN ($pollPlaceholders)
            GROUP BY o.option_id, o.poll_id, o.option_text
        ");
        $stmtOpt->execute($pollIds);
        $options = $stmtOpt->fetchAll();

        foreach ($options as &$optRow) {
            $optRow['option_id'] = (int) $optRow['option_id'];
            $optRow['poll_id'] = (int) $optRow['poll_id'];
            $optRow['vote_count'] = (int) $optRow['vote_count'];
        }
        unset($optRow);
        
        // Get User's Votes for these polls
        $userVotes = [];
        if ($userId !== '') {
            $params = $pollIds;
            array_unshift($params, $userId);
            
            $stmtVotes = $db->prepare("
                SELECT o.poll_id, v.option_id 
                FROM post_poll_votes v
                JOIN post_poll_options o ON v.option_id = o.option_id
                WHERE v.user_id = ? AND o.poll_id IN ($pollPlaceholders)
            ");
            $stmtVotes->execute($params);
            $userVotesRaw = $stmtVotes->fetchAll();
            foreach ($userVotesRaw as $uv) {
                $pId = (int) $uv['poll_id'];
                if (!isset($userVotes[$pId])) {
                    $userVotes[$pId] = [];
                }
                $userVotes[$pId][] = (int) $uv['option_id'];
            }
        }
        
        // Format Result
        $result = [];
        foreach ($polls as $poll) {
            $pId = (int) $poll['poll_id'];
            $poll['poll_id'] = $pId;
            $poll['post_no'] = (int) $poll['post_no'];
            $poll['options'] = array_values(array_filter($options, function($o) use ($pId) {
                return (int) $o['poll_id'] === $pId;
            }));
            
            // Calc total votes for percentage
            $totalVotes = array_reduce($poll['options'], function($carry, $item) {
                return $carry + (int) $item['vote_count'];
            }, 0);
            $poll['total_votes'] = $totalVotes;
            
            // Calculate percentage
            foreach ($poll['options'] as &$opt) {
                $opt['percentage'] = $totalVotes > 0 ? (int) round(($opt['vote_count'] / $totalVotes) * 100) : 0;
            }
            unset($opt);
            
            $poll['user_voted_option_id'] = $userVotes[$pId] ?? [];
            
            $result[$poll['post_no']] = $poll;
        }
        
        return $result;
    }
}
