-- Upgrade percentage-based Cartographer tracks from whole-percent storage to
-- tenths-of-a-percent storage so GWTTT can preserve GWCA values such as 22.4%.
--
-- point_scale is presentation/storage metadata:
--   1  = ordinary title points
--   10 = titlepoints/stpoints are tenths of one percent
--
-- The point_scale=1 predicates make the data conversion safe to re-run: after
-- the first successful conversion the three tracks are marked point_scale=10.

ALTER TABLE gwtitles
    ADD COLUMN IF NOT EXISTS point_scale TINYINT UNSIGNED NOT NULL DEFAULT 1
    COMMENT 'Storage/display scale: 1=normal points, 10=tenths of one percent'
    AFTER gwca_title_id;

-- Existing user progress: 21% -> 210, 18% -> 180, etc.
UPDATE gwstats s
JOIN gwtitles t ON t.titlenameid = s.titlenameid
SET s.titlepoints = s.titlepoints * 10
WHERE t.gwca_title_id IN (1, 2, 18)
  AND t.point_scale = 1;

-- Rank thresholds for the same tracks must use the same units as titlepoints.
UPDATE gwsubtitles st
JOIN gwtitles t ON t.titlenameid = st.titlenameid
SET st.stpoints = st.stpoints * 10
WHERE t.gwca_title_id IN (1, 2, 18)
  AND t.point_scale = 1;

UPDATE gwtitles
SET point_scale = 10
WHERE gwca_title_id IN (1, 2, 18)
  AND point_scale = 1;

SELECT titlenameid, gwca_title_id, point_scale, titlename
FROM gwtitles
WHERE gwca_title_id IN (1, 2, 18)
ORDER BY gwca_title_id;
