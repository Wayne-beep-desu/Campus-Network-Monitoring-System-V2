USE campus_network;

CREATE TABLE IF NOT EXISTS access_points (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ap_name VARCHAR(120) NOT NULL,
    ssid VARCHAR(120) DEFAULT NULL,
    management_ip VARCHAR(45) DEFAULT NULL,
    location VARCHAR(120) DEFAULT NULL,
    controller_type VARCHAR(50) DEFAULT 'Generic',
    enabled TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS wifi_clients (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    mac_address VARCHAR(32) DEFAULT NULL,
    hostname VARCHAR(180) DEFAULT NULL,
    device_type VARCHAR(50) DEFAULT 'Unknown',
    access_point_id INT NULL,
    ssid VARCHAR(120) DEFAULT NULL,
    location VARCHAR(120) DEFAULT NULL,
    source VARCHAR(40) NOT NULL DEFAULT 'lan_arp',
    status ENUM('Online','Offline','Unknown') NOT NULL DEFAULT 'Unknown',
    first_seen DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_seen DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_wifi_client_ip (ip_address),
    KEY idx_wifi_client_mac (mac_address),
    KEY idx_wifi_client_ap (access_point_id),
    CONSTRAINT fk_wifi_client_ap FOREIGN KEY (access_point_id) REFERENCES access_points(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS wifi_discovery_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    source VARCHAR(40) NOT NULL,
    client_count INT NOT NULL DEFAULT 0,
    started_at DATETIME NOT NULL,
    finished_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    notes TEXT NULL
);

INSERT INTO access_points (ap_name, ssid, management_ip, location, controller_type)
SELECT 'AP-01', 'Campus-WiFi', NULL, 'Computer Lab', 'Generic'
WHERE NOT EXISTS (SELECT 1 FROM access_points WHERE ap_name='AP-01');
