-- Add show_birth_year preference to contact table (default 'off' to protect privacy unless specified)
ALTER TABLE `contact`
  ADD COLUMN `show_birth_year` varchar(10) NOT NULL DEFAULT 'off' AFTER `show_my_profile`;
