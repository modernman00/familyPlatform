-- RFC-0019: Add template_style column to festive_cards
ALTER TABLE `festive_cards` 
ADD COLUMN IF NOT EXISTS `template_style` VARCHAR(32) NOT NULL DEFAULT 'classic' AFTER `theme`;
