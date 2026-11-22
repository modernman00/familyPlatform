-- Expand Family Arcade game_slug from strict 4-enum to VARCHAR(32)
-- Enables Phase 1 expansion (Wordie, Cheeseboard, Minesweeper) and future Phase 2 (Sudoku, Nonograms, KenKen)
-- Date: 2026-11-22

ALTER TABLE `family_arcade_scores` MODIFY COLUMN `game_slug` VARCHAR(32) NOT NULL;
ALTER TABLE `family_arcade_races` MODIFY COLUMN `game_slug` VARCHAR(32) NOT NULL;
