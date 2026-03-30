<?php
/**
 * Migration 000: Base schema — roles, users, app_settings, role_capabilities
 *
 * Generic admin scaffold. Run first.
 */
return [
    'name' => 'migration_000_initial',
    'up' => function (\PDO $db): void {
        $db->exec("
            CREATE TABLE IF NOT EXISTS roles (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(50) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $db->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(100) NOT NULL UNIQUE,
                email VARCHAR(255) DEFAULT NULL,
                display_name VARCHAR(255) NULL DEFAULT NULL,
                password_hash VARCHAR(255) NOT NULL,
                password_changed_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                role_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (role_id) REFERENCES roles(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $db->exec("
            CREATE TABLE IF NOT EXISTS app_settings (
                setting_key VARCHAR(100) PRIMARY KEY,
                setting_value TEXT,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $db->exec("
            CREATE TABLE IF NOT EXISTS role_capabilities (
                id INT AUTO_INCREMENT PRIMARY KEY,
                role_id INT NOT NULL,
                capability VARCHAR(100) NOT NULL,
                FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
                UNIQUE KEY uk_role_cap (role_id, capability)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $db->exec("INSERT IGNORE INTO roles (name) VALUES ('Administrator'), ('Standard User'), ('Coordinator')");
        $stmt = $db->query("SELECT id FROM roles WHERE name = 'Administrator' LIMIT 1");
        $adminRoleId = $stmt ? $stmt->fetchColumn() : null;
        if ($adminRoleId) {
            $db->prepare("INSERT INTO users (username, password_hash, role_id) VALUES ('admin', ?, ?) ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)")
                ->execute(['$2y$10$rBrZEQ7IKxJRkXZ2fzRdpO4MHdYqOd.wERK/VbgIFLxfx6JEowMGS', $adminRoleId]);
        }

        $adminId = $db->query("SELECT id FROM roles WHERE name = 'Administrator'")->fetchColumn();
        $coordId = $db->query("SELECT id FROM roles WHERE name = 'Coordinator'")->fetchColumn();
        $caps = [
            'add_users', 'delete_users', 'edit_roles', 'edit_users', 'export_users',
            'manage_email_settings', 'manage_security_settings', 'manage_settings',
            'view_email_settings', 'view_roles', 'view_security_settings', 'view_settings',
            'view_users',
        ];
        $ins = $db->prepare('INSERT IGNORE INTO role_capabilities (role_id, capability) VALUES (?, ?)');
        if ($adminId) {
            foreach ($caps as $c) {
                $ins->execute([$adminId, $c]);
            }
        }
        if ($coordId) {
            foreach (['view_users'] as $c) {
                $ins->execute([$coordId, $c]);
            }
        }
    },
    'down' => null,
];
