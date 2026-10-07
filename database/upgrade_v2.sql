USE campus_network;

CREATE TABLE IF NOT EXISTS campuses (
 id INT AUTO_INCREMENT PRIMARY KEY,
 campus_code VARCHAR(40) NOT NULL UNIQUE,
 campus_name VARCHAR(160) NOT NULL,
 address VARCHAR(255) NULL,
 timezone VARCHAR(80) NOT NULL DEFAULT 'Asia/Manila',
 is_active TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS buildings (
 id INT AUTO_INCREMENT PRIMARY KEY,
 campus_id INT NOT NULL,
 building_code VARCHAR(40) NOT NULL,
 building_name VARCHAR(160) NOT NULL,
 floors INT NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY campus_building_code(campus_id,building_code),
 FOREIGN KEY(campus_id) REFERENCES campuses(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS diagnostic_runs (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 device_id INT NOT NULL,
 requested_by INT NULL,
 check_type ENUM('ping','tcp') NOT NULL,
 target_port INT NULL,
 status ENUM('Pass','Fail','Error') NOT NULL,
 latency_ms DECIMAL(10,2) NULL,
 result_message VARCHAR(500) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX diagnostic_device_time(device_id,created_at),
 FOREIGN KEY(device_id) REFERENCES devices(id) ON DELETE CASCADE,
 FOREIGN KEY(requested_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS alert_rules (
 id INT AUTO_INCREMENT PRIMARY KEY,
 rule_name VARCHAR(120) NOT NULL,
 metric_key ENUM('bandwidth_percent','device_offline','latency_ms') NOT NULL,
 operator ENUM('gt','gte','eq') NOT NULL DEFAULT 'gt',
 threshold DECIMAL(12,2) NOT NULL,
 severity ENUM('Low','Medium','High','Critical') NOT NULL DEFAULT 'Medium',
 is_enabled TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS report_subscriptions (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NOT NULL,
 report_type ENUM('daily_summary','weekly_summary','monthly_summary') NOT NULL,
 recipient_email VARCHAR(180) NOT NULL,
 is_enabled TINYINT(1) NOT NULL DEFAULT 1,
 last_sent_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS assistant_sessions (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NOT NULL,
 prompt TEXT NOT NULL,
 response TEXT NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX assistant_user_time(user_id,created_at),
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS monitoring_preferences (
 id INT AUTO_INCREMENT PRIMARY KEY,
 setting_key VARCHAR(100) NOT NULL UNIQUE,
 setting_value VARCHAR(500) NOT NULL,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT IGNORE INTO monitoring_preferences(setting_key,setting_value) VALUES
('monitoring_mode','simulated'),
('poll_interval_seconds','60'),
('history_retention_days','90'),
('campus_timezone','Asia/Manila');

INSERT IGNORE INTO campuses(campus_code,campus_name,address)
VALUES ('MAIN','Main Campus','');

INSERT IGNORE INTO alert_rules(rule_name,metric_key,operator,threshold,severity)
VALUES ('High bandwidth usage','bandwidth_percent','gte',85,'High'),
       ('Critical bandwidth usage','bandwidth_percent','gte',95,'Critical'),
       ('High latency','latency_ms','gte',250,'Medium');

CREATE INDEX idx_metrics_device_recorded ON network_metrics(device_id,recorded_at);
CREATE INDEX idx_health_device_checked ON device_health_checks(device_id,checked_at);
CREATE INDEX idx_alert_open_created ON alerts(is_resolved,created_at);
