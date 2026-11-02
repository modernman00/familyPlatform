-- Allow "link this relative to an existing member account" confirmation requests.
ALTER TABLE `family_relationship_requests`
  MODIFY COLUMN `kind` ENUM('half_sibling','step_sibling','former_partner','link_account') NOT NULL;
