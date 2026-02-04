-- Insert default loyalty settings (ignore if exist)
INSERT IGNORE INTO `global_settings` (`key_name`, `value`) VALUES 
('loyalty_earn_threshold', '25'),
('loyalty_earn_reward', '5'),
('loyalty_spend_unit_points', '10'),
('loyalty_spend_unit_value', '1'),
('loyalty_max_spend_points', '100');

-- Give test points to first user (Admin/User) to verify UI
UPDATE `users` SET `PuncteFidelitate` = 200 WHERE `id` = 1;
