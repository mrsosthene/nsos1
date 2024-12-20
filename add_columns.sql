-- Ajout de la colonne verification_token
ALTER TABLE users
ADD COLUMN verification_token VARCHAR(255) NULL AFTER date_inscription;

-- Ajout de la colonne is_verified
ALTER TABLE users
ADD COLUMN is_verified TINYINT(1) DEFAULT 0 AFTER verification_token;

-- Ajout de la colonne token_expiry
ALTER TABLE users
ADD COLUMN token_expiry DATETIME NULL AFTER is_verified;
