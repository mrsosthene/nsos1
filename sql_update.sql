ALTER TABLE users 
ADD verification_token VARCHAR(255) NULL AFTER date_inscription,
ADD is_verified TINYINT(1) DEFAULT 0 AFTER verification_token,
ADD token_expiry DATETIME NULL AFTER is_verified;
