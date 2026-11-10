-- Add optional official Guild Wars Wiki URL to the tonic catalog.
-- Apply once to an existing GWTTT database before deploying the updated PHP.
-- Existing tonic rows and all inventory quantities remain unchanged.
ALTER TABLE gwtonics
    ADD COLUMN wiki_url varchar(500) DEFAULT NULL AFTER display_order;
