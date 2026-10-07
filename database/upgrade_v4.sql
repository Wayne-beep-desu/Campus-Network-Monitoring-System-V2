USE campus_network;

-- Advanced operations: interface telemetry, notification center, comments, API keys, room mapping.
CREATE TABLE IF NOT EXISTS device_interfaces (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 device_id INT NOT NULL,
 if_index INT NOT NULL,
 interface_name VARCHAR(180) NULL,
 interface_alias VARCHAR(180) NULL,
 speed_bps BIGINT NULL,
 enabled TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY device_if(device_id,if_index),
 FOREIGN KEY(device_id) REFERENCES devices(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS api_tokens (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NULL,
 token_hash CHAR(64) NOT NULL UNIQUE,
 label VARCHAR(120) NOT NULL,
 expires_at DATETIME NULL,
 last_used_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS notification_preferences (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NOT NULL,
 alert_email TINYINT(1) NOT NULL DEFAULT 1,
 ticket_email TINYINT(1) NOT NULL DEFAULT 1,
 security_email TINYINT(1) NOT NULL DEFAULT 1,
 report_email TINYINT(1) NOT NULL DEFAULT 1,
 UNIQUE KEY user_pref(user_id),
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS device_health_checks (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 device_id INT NOT NULL,
 check_type ENUM('ping','snmp','api') NOT NULL,
 status ENUM('Pass','Fail','Warning') NOT NULL,
 latency_ms DECIMAL(10,2) NULL,
 message VARCHAR(500) NULL,
 checked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(device_id) REFERENCES devices(id) ON DELETE CASCADE
);

INSERT IGNORE INTO permissions(role,permission_key) VALUES
('Admin','notifications.manage'),('Admin','permissions.manage'),('Admin','tokens.manage'),
('IT Staff','notifications.manage'),('IT Staff','health.view');


INSERT IGNORE INTO notification_preferences(user_id)
SELECT id FROM users;
