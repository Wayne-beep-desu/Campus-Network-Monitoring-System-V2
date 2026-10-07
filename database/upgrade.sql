USE campus_network;
CREATE TABLE IF NOT EXISTS settings(setting_key VARCHAR(80) PRIMARY KEY,setting_value TEXT NOT NULL);
INSERT INTO settings VALUES ('warning_threshold','80'),('critical_threshold','95'),('campus_name','Nova Schola Tanauan'),('internet_provider','Campus Internet') ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
INSERT INTO users(name,username,password_hash,role) SELECT 'IT Staff Demo','itstaff',(SELECT password_hash FROM users WHERE username='admin'),'IT Staff' WHERE NOT EXISTS(SELECT 1 FROM users WHERE username='itstaff');
