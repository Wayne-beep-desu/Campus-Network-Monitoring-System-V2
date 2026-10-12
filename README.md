# Campus Network Monitoring System — ULTRA COMPLETE V2

PHP 8+, MySQL/MariaDB, HTML/CSS/JavaScript. This V2 is an additive upgrade to the supplied project; it keeps the existing dashboard, device inventory, bandwidth, alerts, reports, help desk, assets, maintenance, floor plan, security, permissions, notifications, backup and SNMP integration.

## V2 additions
- Network Diagnostics page with TCP reachability tests against devices already registered in the database.
- Diagnostic history with timestamp, operator, target device, result and latency.
- Network Assistant with rule-based troubleshooting guidance based on current database counts. It is local rule-based assistance, not a hosted generative AI.
- Multi-campus and building database foundation.
- Configurable alert-rule, report-subscription and monitoring-preference tables.
- Additional indexes for metrics, device health and alert history.

## Installation / upgrade
1. Back up the existing database and project folder.
2. Extract this ZIP into your web root (for example `htdocs/campus-network`).
3. Import `database/upgrade_v2.sql` into the existing `campus_network` database after the existing schema and upgrades (`upgrade.sql`, `upgrade_v3.sql`, and `upgrade_v4.sql`) have been applied.
4. Confirm PHP 8+, PDO MySQL, and MySQL/MariaDB are enabled.
5. Sign in using the credentials configured for your existing installation.
6. Open Diagnostics and run TCP tests only against devices you own or are authorized to administer.

For a clean install, import `database/schema.sql`, then `database/upgrade_v3.sql`, `database/upgrade_v4.sql`, and finally `database/upgrade_v2.sql`. If your MySQL version does not support `ADD COLUMN IF NOT EXISTS` used by older upgrade scripts, apply the schema with a compatible MySQL/MariaDB version or adapt those older migrations first.

## SIMULATED versus REAL monitoring
The original `api/monitor.php` is a demo simulator and produces generated values. Those values are not real traffic measurements. The real SNMP endpoints require the PHP SNMP extension and a device configured for authorized polling. The existing IF-MIB endpoint currently returns interface counters; accurate Mbps requires selecting an interface and calculating counter deltas from at least two timestamped samples. Do not label simulated values as real.

## Security and production notes
- Change all default/demo passwords immediately. Remove any default credentials from public-facing documentation.
- Enable HTTPS and secure session cookies.
- Use a least-privilege MySQL account; do not deploy with the MySQL root account.
- Prefer SNMPv3, least-privilege device ACLs, and a private management VLAN/VPN.
- Restrict the admin interface and backups from the public internet.
- Store secrets outside source control and outside the public web root.
- Encrypt backups and test restoration in a separate environment.
- Review role permissions and enable MFA before production use.
- Only scan or poll network devices for which you have explicit authorization.

## Important limitations
- Network discovery, bandwidth shaping, 3D visualization, ISP integration, mobile push notifications, and predictive forecasting are not falsely represented as completed live integrations in this build. They require device/vendor APIs, infrastructure configuration, or additional deployment services.
- TCP tests indicate whether a TCP port accepts a connection; they are not a full vulnerability scan.
- The built-in assistant is rule-based and does not send network data to an external AI provider.

## V2.5 — Wi-Fi Connected Device Discovery

Added `wifi_clients.php` and `api/wifi_discovery.php` for authorized LAN discovery using the monitoring server's ARP table. Discovered clients are stored in `wifi_clients`, while Access Points are stored in `access_points`.

**Important:** ARP/LAN discovery can show reachable clients but cannot reliably determine the exact Access Point/SSID association. For exact AP mapping, configure an integration adapter for the campus router/AP controller (for example MikroTik, UniFi, Omada, or another supported controller). Do not label generic ARP results as exact AP associations.


## Railway deployment (Docker + MySQL)

This repository includes a `Dockerfile` for Railway. Create a Railway project, deploy this project from GitHub (or upload/push this folder to a GitHub repository), and add a Railway MySQL service. In the web service Variables, map the database service's connection variables to `MYSQLHOST`, `MYSQLPORT`, `MYSQLDATABASE`, `MYSQLUSER`, and `MYSQLPASSWORD` if Railway does not automatically expose them. Never commit a real `.env` file or database passwords.

For a new empty database, import `database/schema.sql` and then the required upgrade SQL files in dependency order: `upgrade.sql`, `upgrade_v3.sql`, `upgrade_v4.sql`, `upgrade_v2.sql`, and `upgrade_v5_wifi_discovery.sql`. Test this migration on a disposable database first; do not blindly rerun migrations against an existing database because some statements add columns/tables. Change all demo passwords before public access.

The web app can be hosted online, but LAN/ARP discovery only sees devices visible from the Railway container and will not discover private campus LAN clients over the public internet. For real campus client discovery, deploy the web app online and connect it to an authorized on-campus agent or router/AP controller over a secure VPN/API integration.
