-- Create admin_notes table
CREATE TABLE IF NOT EXISTS `admin_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` TEXT,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Initialize default note if empty
INSERT INTO `admin_notes` (id, content) 
SELECT 1, 'Welcome to the admin panel!' 
WHERE NOT EXISTS (SELECT 1 FROM `admin_notes` WHERE id = 1);


-- Create schedule table
CREATE TABLE IF NOT EXISTS `schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `day_of_week` tinyint(1) NOT NULL, -- 0=Sunday, 1=Monday, ... 6=Saturday
  `day_name` varchar(20) NOT NULL,
  `open_time` TIME DEFAULT '08:00:00',
  `close_time` TIME DEFAULT '17:00:00',
  `is_closed` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `day_idx` (`day_of_week`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Initialize Schedule (Mon-Fri 7-17, Sat 8-17, Sun Closed)
-- Clear first to avoid dupes if running multiple times (safe for dev)
TRUNCATE TABLE `schedule`;

INSERT INTO `schedule` (day_of_week, day_name, open_time, close_time, is_closed) VALUES
(0, 'Sunday', '08:00:00', '17:00:00', 1),
(1, 'Monday', '07:00:00', '17:00:00', 0),
(2, 'Tuesday', '07:00:00', '17:00:00', 0),
(3, 'Wednesday', '07:00:00', '17:00:00', 0),
(4, 'Thursday', '07:00:00', '17:00:00', 0),
(5, 'Friday', '07:00:00', '17:00:00', 0),
(6, 'Saturday', '08:00:00', '17:00:00', 0);


-- Create Global Settings table (for Cafe Status)
CREATE TABLE IF NOT EXISTS `global_settings` (
  `key_name` varchar(50) NOT NULL,
  `value` varchar(255),
  PRIMARY KEY (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `global_settings` (key_name, value) VALUES ('cafe_status', 'open') ON DUPLICATE KEY UPDATE value=value;
