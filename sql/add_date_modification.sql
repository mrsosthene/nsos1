-- Ajout de la colonne date_modification à la table articles
ALTER TABLE articles
ADD COLUMN date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
