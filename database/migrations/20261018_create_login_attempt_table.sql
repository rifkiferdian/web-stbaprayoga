CREATE TABLE IF NOT EXISTS tbl_login_attempt (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    identifier CHAR(64) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempted_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    INDEX idx_login_identifier_time (identifier, attempted_at),
    INDEX idx_login_ip_time (ip_address, attempted_at),
    INDEX idx_login_attempted_at (attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
