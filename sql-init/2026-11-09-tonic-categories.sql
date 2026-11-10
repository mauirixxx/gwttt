-- Categorize tonics without changing their IDs or account inventory.
-- Existing catalog entries remain birthday tonics until recategorized in admin.
ALTER TABLE gwtonics
  ADD COLUMN category enum('birthday','ingame') NOT NULL DEFAULT 'birthday' AFTER rarity;
