-- Ensure notification table and columns fully support 4-byte UTF-8 emojis (🎬, ⏳, 🌳, 📢, 📖, ❤️)
ALTER TABLE `notification`
    CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE `notification`
    MODIFY COLUMN `sender_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
    MODIFY COLUMN `receiver` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
    MODIFY COLUMN `notification_type` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
    MODIFY COLUMN `notification_content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL;
