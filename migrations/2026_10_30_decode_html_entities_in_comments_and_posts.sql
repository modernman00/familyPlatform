-- Migration: 2026_10_30_decode_html_entities_in_comments_and_posts.sql
-- Description: Clean legacy HTML entities (&#039;, &amp;#039;) into canonical UTF-8 characters across comments and posts
-- Author: BRATS & Technical Approval Team

UPDATE `comment`
SET `comment` = REPLACE(REPLACE(`comment`, '&amp;#039;', '\''), '&#039;', '\'')
WHERE `comment` LIKE '%&#039;%';

UPDATE `post`
SET `postMessage` = REPLACE(REPLACE(`postMessage`, '&amp;#039;', '\''), '&#039;', '\'')
WHERE `postMessage` LIKE '%&#039;%';
