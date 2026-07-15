<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/config/init.php';

use Src\Db;

class GrowthMigration extends Db {
    public function up() {
        $db = self::connect2();
        
        $sqlReactions = "
        CREATE TABLE IF NOT EXISTS post_reactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_no INT NOT NULL,
            user_id VARCHAR(255) NOT NULL,
            reaction_type VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY user_post_reaction (post_no, user_id)
        );
        ";
        $db->exec($sqlReactions);
        echo "Table post_reactions created successfully.\n";

        $sqlPolls = "
        CREATE TABLE IF NOT EXISTS post_polls (
            poll_id INT AUTO_INCREMENT PRIMARY KEY,
            post_no INT NOT NULL,
            question VARCHAR(255) NOT NULL,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (post_no) REFERENCES post(post_no) ON DELETE CASCADE
        );
        ";
        $db->exec($sqlPolls);
        echo "Table post_polls created successfully.\n";

        $sqlPollOptions = "
        CREATE TABLE IF NOT EXISTS post_poll_options (
            option_id INT AUTO_INCREMENT PRIMARY KEY,
            poll_id INT NOT NULL,
            option_text VARCHAR(255) NOT NULL,
            FOREIGN KEY (poll_id) REFERENCES post_polls(poll_id) ON DELETE CASCADE
        );
        ";
        $db->exec($sqlPollOptions);
        echo "Table post_poll_options created successfully.\n";

        $sqlPollVotes = "
        CREATE TABLE IF NOT EXISTS post_poll_votes (
            vote_id INT AUTO_INCREMENT PRIMARY KEY,
            option_id INT NOT NULL,
            user_id VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY user_poll_vote (option_id, user_id),
            FOREIGN KEY (option_id) REFERENCES post_poll_options(option_id) ON DELETE CASCADE
        );
        ";
        $db->exec($sqlPollVotes);
        echo "Table post_poll_votes created successfully.\n";
    }
}

$m = new GrowthMigration();
$m->up();
