-- Emojis (e.g. 🎉) in notification text were stored as '?' because these columns were utf8mb3.
ALTER TABLE `notification`
  MODIFY `notification_content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  MODIFY `notification_type` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  MODIFY `notification_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
