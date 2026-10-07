USE campus_network;

ALTER TABLE users ADD COLUMN IF NOT EXISTS email VARCHAR(180) NULL AFTER username;
ALTER TABLE users ADD COLUMN IF NOT EXISTS is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER role;
ALTER TABLE users ADD COLUMN IF NOT EXISTS failed_logins INT NOT NULL DEFAULT 0 AFTER is_active;
ALTER TABLE users ADD COLUMN IF NOT EXISTS locked_until DATETIME NULL AFTER failed_logins;

ALTER TABLE devices ADD COLUMN IF NOT EXISTS monitor_mode ENUM('simulated','snmp','api') NOT NULL DEFAULT 'simulated';
ALTER TABLE devices ADD COLUMN IF NOT EXISTS monitor_endpoint VARCHAR(255) NULL;
ALTER TABLE devices ADD COLUMN IF NOT EXISTS snmp_community VARCHAR(120) NULL;
ALTER TABLE devices ADD COLUMN IF NOT EXISTS snmp_port INT NOT NULL DEFAULT 161;

CREATE TABLE IF NOT EXISTS tickets (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 ticket_no VARCHAR(30) NOT NULL UNIQUE,
 title VARCHAR(180) NOT NULL,
 description TEXT NOT NULL,
 priority ENUM('Low','Medium','High','Critical') NOT NULL DEFAULT 'Medium',
 status ENUM('Open','In Progress','Pending','Resolved','Closed') NOT NULL DEFAULT 'Open',
 category VARCHAR(80) NOT NULL DEFAULT 'Network',
 reporter_id INT NULL,
 technician_id INT NULL,
 device_id INT NULL,
 room VARCHAR(120) NULL,
 resolution TEXT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 resolved_at DATETIME NULL,
 FOREIGN KEY(reporter_id) REFERENCES users(id) ON DELETE SET NULL,
 FOREIGN KEY(technician_id) REFERENCES users(id) ON DELETE SET NULL,
 FOREIGN KEY(device_id) REFERENCES devices(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS ticket_comments (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 ticket_id BIGINT NOT NULL,
 user_id INT NULL,
 comment TEXT NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS assets (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 asset_tag VARCHAR(80) NOT NULL UNIQUE,
 asset_name VARCHAR(180) NOT NULL,
 category VARCHAR(80) NOT NULL,
 brand VARCHAR(100) NULL,
 model VARCHAR(120) NULL,
 serial_no VARCHAR(150) NULL,
 room VARCHAR(120) NULL,
 assigned_to INT NULL,
 status ENUM('Active','Maintenance','Retired','Lost') NOT NULL DEFAULT 'Active',
 purchase_date DATE NULL,
 warranty_until DATE NULL,
 cost DECIMAL(12,2) NULL,
 notes TEXT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(assigned_to) REFERENCES users(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS maintenance_records (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 asset_id BIGINT NOT NULL,
 technician_id INT NULL,
 maintenance_type VARCHAR(100) NOT NULL,
 description TEXT,
 cost DECIMAL(12,2) DEFAULT 0,
 maintenance_date DATE NOT NULL,
 next_due DATE NULL,
 FOREIGN KEY(asset_id) REFERENCES assets(id) ON DELETE CASCADE,
 FOREIGN KEY(technician_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS network_metrics (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 device_id INT NULL,
 source ENUM('simulated','snmp','api') NOT NULL,
 download_mbps DECIMAL(12,3) NOT NULL DEFAULT 0,
 upload_mbps DECIMAL(12,3) NOT NULL DEFAULT 0,
 latency_ms DECIMAL(10,2) NULL,
 packet_loss DECIMAL(6,2) NULL,
 uptime_seconds BIGINT NULL,
 recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(device_id) REFERENCES devices(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS device_status_history (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 device_id INT NOT NULL,
 status ENUM('Online','Offline','Warning') NOT NULL,
 changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(device_id) REFERENCES devices(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS security_events (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NULL,
 event_type VARCHAR(100) NOT NULL,
 severity ENUM('Info','Warning','Critical') NOT NULL DEFAULT 'Info',
 ip_address VARCHAR(45) NULL,
 user_agent VARCHAR(500) NULL,
 details TEXT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS permissions (
 id INT AUTO_INCREMENT PRIMARY KEY,
 role VARCHAR(40) NOT NULL,
 permission_key VARCHAR(100) NOT NULL,
 UNIQUE KEY role_permission(role,permission_key)
);
INSERT IGNORE INTO permissions(role,permission_key) VALUES
('Admin','*'),
('IT Staff','dashboard.view'),('IT Staff','devices.manage'),('IT Staff','monitoring.view'),('IT Staff','tickets.manage'),('IT Staff','assets.manage'),('IT Staff','reports.view'),('IT Staff','alerts.manage'),('IT Staff','floorplan.manage'),('IT Staff','backup.manage'),
('Viewer','dashboard.view'),('Viewer','monitoring.view'),('Viewer','reports.view'),('Viewer','floorplan.view');

CREATE TABLE IF NOT EXISTS floorplan_rooms (
 id INT AUTO_INCREMENT PRIMARY KEY,
 room_code VARCHAR(50) NOT NULL UNIQUE,
 room_name VARCHAR(120) NOT NULL,
 building VARCHAR(120) NULL,
 x_pos DECIMAL(6,2) NOT NULL DEFAULT 10,
 y_pos DECIMAL(6,2) NOT NULL DEFAULT 10,
 width DECIMAL(6,2) NOT NULL DEFAULT 20,
 height DECIMAL(6,2) NOT NULL DEFAULT 15,
 room_type VARCHAR(80) NULL
);
CREATE TABLE IF NOT EXISTS room_devices (
 room_id INT NOT NULL,
 device_id INT NOT NULL,
 PRIMARY KEY(room_id,device_id),
 FOREIGN KEY(room_id) REFERENCES floorplan_rooms(id) ON DELETE CASCADE,
 FOREIGN KEY(device_id) REFERENCES devices(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS notification_queue (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 recipient_email VARCHAR(180) NOT NULL,
 subject VARCHAR(255) NOT NULL,
 body TEXT NOT NULL,
 status ENUM('Pending','Sent','Failed') NOT NULL DEFAULT 'Pending',
 attempts INT NOT NULL DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 sent_at DATETIME NULL
);
CREATE TABLE IF NOT EXISTS report_schedules (
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(120) NOT NULL,
 recipient_email VARCHAR(180) NOT NULL,
 frequency ENUM('daily','weekly') NOT NULL DEFAULT 'daily',
 enabled TINYINT(1) NOT NULL DEFAULT 1,
 last_run DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS backups (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 filename VARCHAR(255) NOT NULL,
 file_path VARCHAR(500) NOT NULL,
 created_by INT NULL,
 size_bytes BIGINT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL
);

INSERT INTO settings(setting_key,setting_value) VALUES
('monitoring_mode','simulated'),('snmp_default_community','public'),('snmp_timeout','1000000'),('smtp_from','no-reply@campus.local'),('email_alerts_enabled','0'),('backup_dir','backups')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

INSERT IGNORE INTO floorplan_rooms(room_code,room_name,building,x_pos,y_pos,width,height,room_type) VALUES
('LAB1','Computer Laboratory 1','Main Building',5,10,27,25,'Laboratory'),
('LAB2','Computer Laboratory 2','Main Building',35,10,27,25,'Laboratory'),
('SRV','Server Room','Main Building',65,10,27,25,'Server Room'),
('FAC','Faculty Room','Main Building',5,45,27,25,'Office'),
('LIB','Library','Main Building',35,45,27,25,'Library'),
('ADM','Administration','Main Building',65,45,27,25,'Office');

INSERT INTO assets(asset_tag,asset_name,category,brand,model,serial_no,room,status,warranty_until)
SELECT * FROM (SELECT 'AST-ROUTER-001','Core Router','Networking','Cisco','Demo Router','SN-ROUTER-001','Server Room','Active','2027-12-31') x
WHERE NOT EXISTS(SELECT 1 FROM assets WHERE asset_tag='AST-ROUTER-001');

UPDATE users SET email=CONCAT(username,'@campus.local') WHERE email IS NULL;
