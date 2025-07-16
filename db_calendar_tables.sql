-- Table for storing external calendar connections
CREATE TABLE IF NOT EXISTS `external_calendars` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `source` enum('google','outlook','ical','microsoft365') NOT NULL,
  `source_id` varchar(512) NOT NULL,
  `color` varchar(20) NOT NULL DEFAULT '#039be5',
  `auto_refresh` tinyint(1) NOT NULL DEFAULT 0,
  `access_token` text DEFAULT NULL,
  `refresh_token` text DEFAULT NULL,
  `token_expires` datetime DEFAULT NULL,
  `last_synced` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `external_calendars_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for storing events from external calendars
CREATE TABLE IF NOT EXISTS `calendar_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `calendar_id` int(11) NOT NULL,
  `event_uid` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `all_day` tinyint(1) NOT NULL DEFAULT 0,
  `url` varchar(1000) DEFAULT NULL,
  `recurrence_rule` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `calendar_event_uid` (`calendar_id`,`event_uid`),
  KEY `calendar_id` (`calendar_id`),
  CONSTRAINT `calendar_events_ibfk_1` FOREIGN KEY (`calendar_id`) REFERENCES `external_calendars` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 