CREATE DATABASE IF NOT EXISTS campus_network
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE campus_network;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    username VARCHAR(80) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('Admin','IT Staff','Viewer') NOT NULL DEFAULT 'Viewer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_name VARCHAR(120) NOT NULL,
    ip_address VARCHAR(45) NOT NULL UNIQUE,
    mac_address VARCHAR(32) DEFAULT NULL,
    device_type VARCHAR(50) NOT NULL DEFAULT 'Computer',
    location VARCHAR(120) DEFAULT NULL,
    status ENUM('Online','Offline','Warning') NOT NULL DEFAULT 'Online',
    bandwidth_limit DECIMAL(10,2) NOT NULL DEFAULT 10,
    download_mbps DECIMAL(10,2) NOT NULL DEFAULT 0,
    upload_mbps DECIMAL(10,2) NOT NULL DEFAULT 0,
    last_seen DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS bandwidth_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    device_id INT NULL,
    download_mbps DECIMAL(10,2) NOT NULL,
    upload_mbps DECIMAL(10,2) NOT NULL,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_bandwidth_device
        FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id INT NULL,
    title VARCHAR(180) NOT NULL,
    message TEXT NOT NULL,
    severity ENUM('Low','Medium','High','Critical') NOT NULL DEFAULT 'Low',
    is_resolved TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at DATETIME NULL,
    CONSTRAINT fk_alert_device
        FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS activity_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(120) NOT NULL,
    details TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_activity_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

INSERT INTO users (name, username, password_hash, role)
SELECT 'System Administrator', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC9aG7x0rM6f9p1QmY3a', 'Admin'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username='admin');

INSERT INTO devices
(device_name, ip_address, mac_address, device_type, location, status, bandwidth_limit, download_mbps, upload_mbps)
SELECT * FROM (
    SELECT 'Core Router', '192.168.1.1', 'AA:BB:CC:DD:EE:01', 'Router', 'Server Room', 'Online', 100.00, 62.50, 18.20
    UNION ALL SELECT 'Main Server', '192.168.1.5', 'AA:BB:CC:DD:EE:05', 'Server', 'Server Room', 'Online', 100.00, 45.20, 12.40
    UNION ALL SELECT 'Lab PC 01', '192.168.1.10', 'AA:BB:CC:DD:EE:10', 'Computer', 'Computer Lab', 'Online', 10.00, 7.80, 2.10
    UNION ALL SELECT 'Lab PC 02', '192.168.1.11', 'AA:BB:CC:DD:EE:11', 'Computer', 'Computer Lab', 'Warning', 10.00, 9.30, 3.20
    UNION ALL SELECT 'Faculty AP', '192.168.1.20', 'AA:BB:CC:DD:EE:20', 'Access Point', 'Faculty Room', 'Online', 50.00, 21.50, 7.80
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM devices WHERE ip_address = seed.ip_address);
