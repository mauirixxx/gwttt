-- Add a per-user GWTTT session idle timeout preference.
-- 1800 seconds preserves the existing 30-minute default.
-- 0 means GWTTT will not intentionally expire the session for idle or absolute age.

ALTER TABLE user_preferences
    ADD COLUMN session_timeout_seconds int(10) unsigned NOT NULL DEFAULT 1800
    AFTER treasure_email_enabled;
