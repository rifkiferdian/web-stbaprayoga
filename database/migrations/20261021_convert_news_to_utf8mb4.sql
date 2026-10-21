-- Allow four-byte Unicode characters (including emoji) in news content.
-- Safe to run more than once.
ALTER TABLE tbl_news
    CONVERT TO CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
