-- Add columns to orders to track loyalty points
ALTER TABLE `orders` ADD COLUMN `points_earned` INT DEFAULT 0;
ALTER TABLE `orders` ADD COLUMN `points_spent` INT DEFAULT 0;

-- Insert default loyalty settings
INSERT INTO `global_settings` (`key_name`, `value`) VALUES 
('loyalty_earn_threshold', '25'),
('loyalty_earn_reward', '5'),
('loyalty_spend_unit_points', '10'),
('loyalty_spend_unit_value', '1'),
('loyalty_max_spend_points', '100')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);
