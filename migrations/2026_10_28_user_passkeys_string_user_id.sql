-- Passkeys: account.id is varchar(255) (e.g. '117540OLAWALE'); user_passkeys.user_id was INT,
-- causing SQLSTATE 1265 "Data truncated for column 'user_id'". Also add user_role (used by WebAuthnFunctionality).
ALTER TABLE `user_passkeys`
  MODIFY COLUMN `user_id` varchar(255) NOT NULL,
  ADD COLUMN `user_role` varchar(50) NOT NULL DEFAULT 'users' AFTER `user_id`;
