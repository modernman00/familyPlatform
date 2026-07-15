<?php
declare(strict_types=1);

namespace App\model;

use Src\Db;

class ReactionData extends Db {
    
    public static function toggleReaction(int $postNo, string $userId, string $reactionType): bool {
        $db = self::connect2();
        
        // Check if existing reaction
        $stmt = $db->prepare("SELECT reaction_type FROM post_reactions WHERE post_no = ? AND user_id = ?");
        $stmt->execute([$postNo, $userId]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Delete existing
            $stmt = $db->prepare("DELETE FROM post_reactions WHERE post_no = ? AND user_id = ?");
            $stmt->execute([$postNo, $userId]);
            
            // If they clicked a different reaction, insert the new one
            if ($existing['reaction_type'] !== $reactionType) {
                $stmt = $db->prepare("INSERT INTO post_reactions (post_no, user_id, reaction_type) VALUES (?, ?, ?)");
                $stmt->execute([$postNo, $userId, $reactionType]);
            }
        } else {
            // Insert new
            $stmt = $db->prepare("INSERT INTO post_reactions (post_no, user_id, reaction_type) VALUES (?, ?, ?)");
            $stmt->execute([$postNo, $userId, $reactionType]);
        }
        return true;
    }
    
    public static function getReactionsForPosts(array $postIds): array {
        if (empty($postIds)) return [];
        $placeholders = implode(',', array_fill(0, count($postIds), '?'));
        $db = self::connect2();
        $stmt = $db->prepare("SELECT post_no, reaction_type, COUNT(*) as count FROM post_reactions WHERE post_no IN ($placeholders) GROUP BY post_no, reaction_type");
        $stmt->execute($postIds);
        return $stmt->fetchAll();
    }
    
    public static function getUserReactionsForPosts(string $userId, array $postIds): array {
         if (empty($postIds)) return [];
        $placeholders = implode(',', array_fill(0, count($postIds), '?'));
        $db = self::connect2();
        
        $params = $postIds;
        array_unshift($params, $userId); // prepend user_id
        
        $stmt = $db->prepare("SELECT post_no, reaction_type FROM post_reactions WHERE user_id = ? AND post_no IN ($placeholders)");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
