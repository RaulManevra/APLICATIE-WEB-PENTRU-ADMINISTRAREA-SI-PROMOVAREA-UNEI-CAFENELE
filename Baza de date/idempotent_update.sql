-- Safely add columns if they don't exist
-- We use a stored procedure to check existence because MySQL 5.7 doesn't support IF NOT EXISTS for ADD COLUMN
DROP PROCEDURE IF EXISTS upgrade_database;

DELIMITER $$
CREATE PROCEDURE upgrade_database()
BEGIN
    -- Add points_earned if not exists
    IF NOT EXISTS (
        SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = 'mazi_coffee' AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'points_earned'
    ) THEN
        ALTER TABLE `orders` ADD COLUMN `points_earned` INT DEFAULT 0;
    END IF;

    -- Add points_spent if not exists
    IF NOT EXISTS (
        SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = 'mazi_coffee' AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'points_spent'
    ) THEN
        ALTER TABLE `orders` ADD COLUMN `points_spent` INT DEFAULT 0;
    END IF;
END $$
DELIMITER ;

CALL upgrade_database();
DROP PROCEDURE upgrade_database;
