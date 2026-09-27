-- Add a stable GWCA title-track identity to GWTTT.
-- Safe for the existing production database. Run once before deploying the
-- importer version that reads gwtitles.gwca_title_id.

ALTER TABLE `gwtitles`
  ADD COLUMN `gwca_title_id` smallint(5) unsigned DEFAULT NULL
    COMMENT 'GWCA TitleID used by GWTTT JSON import; NULL = no direct mapping' AFTER `titlenameid`,
  ADD UNIQUE KEY `uq_gwtitles_gwca_title_id` (`gwca_title_id`);

-- GWCA TitleID -> existing GWTTT title definition.
-- Deprecated GWCA IDs 8, 11 and 12 intentionally remain unmapped.
UPDATE `gwtitles` SET `gwca_title_id` = 0  WHERE `titlenameid` = 20; -- Hero
UPDATE `gwtitles` SET `gwca_title_id` = 1  WHERE `titlenameid` = 25; -- Tyrian Cartographer
UPDATE `gwtitles` SET `gwca_title_id` = 2  WHERE `titlenameid` = 41; -- Canthan Cartographer
UPDATE `gwtitles` SET `gwca_title_id` = 3  WHERE `titlenameid` = 19; -- Gladiator
UPDATE `gwtitles` SET `gwca_title_id` = 4  WHERE `titlenameid` = 16; -- Champion
UPDATE `gwtitles` SET `gwca_title_id` = 5  WHERE `titlenameid` = 1;  -- Kurzick
UPDATE `gwtitles` SET `gwca_title_id` = 6  WHERE `titlenameid` = 2;  -- Luxon
UPDATE `gwtitles` SET `gwca_title_id` = 7  WHERE `titlenameid` = 5;  -- Drunkard
UPDATE `gwtitles` SET `gwca_title_id` = 9  WHERE `titlenameid` = 30; -- Survivor
UPDATE `gwtitles` SET `gwca_title_id` = 10 WHERE `titlenameid` = 27; -- Kind of a Big Deal
UPDATE `gwtitles` SET `gwca_title_id` = 13 WHERE `titlenameid` = 28; -- Protector of Tyria
UPDATE `gwtitles` SET `gwca_title_id` = 14 WHERE `titlenameid` = 47; -- Protector of Cantha
UPDATE `gwtitles` SET `gwca_title_id` = 15 WHERE `titlenameid` = 21; -- Lucky
UPDATE `gwtitles` SET `gwca_title_id` = 16 WHERE `titlenameid` = 22; -- Unlucky
UPDATE `gwtitles` SET `gwca_title_id` = 17 WHERE `titlenameid` = 33; -- Sunspear
UPDATE `gwtitles` SET `gwca_title_id` = 18 WHERE `titlenameid` = 42; -- Elonian Cartographer
UPDATE `gwtitles` SET `gwca_title_id` = 19 WHERE `titlenameid` = 48; -- Protector of Elona
UPDATE `gwtitles` SET `gwca_title_id` = 20 WHERE `titlenameid` = 6;  -- Lightbringer
UPDATE `gwtitles` SET `gwca_title_id` = 21 WHERE `titlenameid` = 32; -- Legendary Defender of Ascalon
UPDATE `gwtitles` SET `gwca_title_id` = 22 WHERE `titlenameid` = 24; -- Commander
UPDATE `gwtitles` SET `gwca_title_id` = 23 WHERE `titlenameid` = 18; -- Gamer
UPDATE `gwtitles` SET `gwca_title_id` = 24 WHERE `titlenameid` = 29; -- Tyrian Skill Hunter
UPDATE `gwtitles` SET `gwca_title_id` = 25 WHERE `titlenameid` = 31; -- Tyrian Vanquisher
UPDATE `gwtitles` SET `gwca_title_id` = 26 WHERE `titlenameid` = 51; -- Canthan Skill Hunter
UPDATE `gwtitles` SET `gwca_title_id` = 27 WHERE `titlenameid` = 49; -- Canthan Vanquisher
UPDATE `gwtitles` SET `gwca_title_id` = 28 WHERE `titlenameid` = 52; -- Elonian Skill Hunter
UPDATE `gwtitles` SET `gwca_title_id` = 29 WHERE `titlenameid` = 50; -- Elonian Vanquisher
UPDATE `gwtitles` SET `gwca_title_id` = 30 WHERE `titlenameid` = 37; -- Legendary Cartographer
UPDATE `gwtitles` SET `gwca_title_id` = 31 WHERE `titlenameid` = 38; -- Legendary Guardian
UPDATE `gwtitles` SET `gwca_title_id` = 32 WHERE `titlenameid` = 39; -- Legendary Skill Hunter
UPDATE `gwtitles` SET `gwca_title_id` = 33 WHERE `titlenameid` = 40; -- Legendary Vanquisher
UPDATE `gwtitles` SET `gwca_title_id` = 34 WHERE `titlenameid` = 7;  -- Sweet Tooth
UPDATE `gwtitles` SET `gwca_title_id` = 35 WHERE `titlenameid` = 26; -- Guardian of Tyria
UPDATE `gwtitles` SET `gwca_title_id` = 36 WHERE `titlenameid` = 45; -- Guardian of Cantha
UPDATE `gwtitles` SET `gwca_title_id` = 37 WHERE `titlenameid` = 46; -- Guardian of Elona
UPDATE `gwtitles` SET `gwca_title_id` = 38 WHERE `titlenameid` = 3;  -- Asura
UPDATE `gwtitles` SET `gwca_title_id` = 39 WHERE `titlenameid` = 4;  -- Deldrimor
UPDATE `gwtitles` SET `gwca_title_id` = 40 WHERE `titlenameid` = 34; -- Ebon Vanguard
UPDATE `gwtitles` SET `gwca_title_id` = 41 WHERE `titlenameid` = 36; -- Norn
UPDATE `gwtitles` SET `gwca_title_id` = 42 WHERE `titlenameid` = 35; -- Master of the North
UPDATE `gwtitles` SET `gwca_title_id` = 43 WHERE `titlenameid` = 10; -- Party Animal
UPDATE `gwtitles` SET `gwca_title_id` = 44 WHERE `titlenameid` = 23; -- Zaishen
UPDATE `gwtitles` SET `gwca_title_id` = 45 WHERE `titlenameid` = 8;  -- Treasure Hunter
UPDATE `gwtitles` SET `gwca_title_id` = 46 WHERE `titlenameid` = 9;  -- Wisdom

SELECT `titlenameid`, `gwca_title_id`, `titlename`, `titletype`, `autofilled`
FROM `gwtitles`
WHERE `gwca_title_id` IS NOT NULL
ORDER BY `gwca_title_id`;
