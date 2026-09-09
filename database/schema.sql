CREATE TABLE users (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 parent_id BIGINT UNSIGNED NULL,
 role ENUM('admin','reseller','sub_reseller','client') NOT NULL,
 name VARCHAR(150) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 status ENUM('active','suspended','pending') NOT NULL DEFAULT 'active',
 balance DECIMAL(18,8) NOT NULL DEFAULT 0,
 currency CHAR(3) NOT NULL DEFAULT 'USD',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_users_parent(parent_id), INDEX idx_users_role(role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE registrars (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL UNIQUE,
 adapter VARCHAR(100) NOT NULL,
 base_url VARCHAR(255) NULL,
 credentials_encrypted TEXT NULL,
 status ENUM('active','disabled') NOT NULL DEFAULT 'disabled',
 priority INT NOT NULL DEFAULT 100,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE tld_prices (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 registrar_id BIGINT UNSIGNED NOT NULL,
 tld VARCHAR(63) NOT NULL,
 register_price DECIMAL(18,8) NOT NULL,
 transfer_price DECIMAL(18,8) NOT NULL,
 renewal_price DECIMAL(18,8) NOT NULL,
 restore_price DECIMAL(18,8) NULL,
 UNIQUE KEY uq_registrar_tld(registrar_id,tld),
 FOREIGN KEY (registrar_id) REFERENCES registrars(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE promotions (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 code VARCHAR(50) NOT NULL UNIQUE,
 tld VARCHAR(63) NULL,
 action ENUM('register','transfer','renewal','all') NOT NULL DEFAULT 'all',
 discount_type ENUM('fixed','percent') NOT NULL,
 discount_value DECIMAL(18,8) NOT NULL,
 starts_at DATETIME NULL,
 ends_at DATETIME NULL,
 usage_limit INT NULL,
 status ENUM('active','disabled') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE domains (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 owner_id BIGINT UNSIGNED NOT NULL,
 registrar_id BIGINT UNSIGNED NULL,
 domain VARCHAR(253) NOT NULL UNIQUE,
 tld VARCHAR(63) NOT NULL,
 registrar_domain_id VARCHAR(190) NULL,
 status ENUM('pending','active','expired','transfer_pending','transferred','locked','cancelled') NOT NULL DEFAULT 'pending',
 registered_at DATETIME NULL,
 expires_at DATETIME NULL,
 auto_renew TINYINT(1) NOT NULL DEFAULT 1,
 nameservers_json JSON NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_domains_owner(owner_id), INDEX idx_domains_expiry(expires_at),
 FOREIGN KEY (owner_id) REFERENCES users(id), FOREIGN KEY (registrar_id) REFERENCES registrars(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE dns_records (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 domain_id BIGINT UNSIGNED NOT NULL,
 type VARCHAR(10) NOT NULL,
 name VARCHAR(253) NOT NULL,
 value TEXT NOT NULL,
 ttl INT NOT NULL DEFAULT 3600,
 priority INT NULL,
 external_record_id VARCHAR(190) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY (domain_id) REFERENCES domains(id) ON DELETE CASCADE,
 INDEX idx_dns_domain(domain_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE orders (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 domain_id BIGINT UNSIGNED NULL,
 type ENUM('register','transfer','renewal','restore') NOT NULL,
 registrar_id BIGINT UNSIGNED NULL,
 amount DECIMAL(18,8) NOT NULL,
 currency CHAR(3) NOT NULL DEFAULT 'USD',
 promo_code VARCHAR(50) NULL,
 status ENUM('pending','processing','completed','failed','cancelled') NOT NULL DEFAULT 'pending',
 external_order_id VARCHAR(190) NULL,
 error_message TEXT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY (user_id) REFERENCES users(id), FOREIGN KEY (domain_id) REFERENCES domains(id) ON DELETE SET NULL,
 FOREIGN KEY (registrar_id) REFERENCES registrars(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE transfer_jobs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 domain_id BIGINT UNSIGNED NOT NULL,
 source_registrar_id BIGINT UNSIGNED NULL,
 target_registrar_id BIGINT UNSIGNED NOT NULL,
 authorization_status ENUM('required','authorized','rejected') NOT NULL DEFAULT 'required',
 status ENUM('pending','processing','completed','failed') NOT NULL DEFAULT 'pending',
 scheduled_at DATETIME NULL,
 completed_at DATETIME NULL,
 error_message TEXT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (domain_id) REFERENCES domains(id) ON DELETE CASCADE,
 FOREIGN KEY (source_registrar_id) REFERENCES registrars(id) ON DELETE SET NULL,
 FOREIGN KEY (target_registrar_id) REFERENCES registrars(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE api_keys (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 name VARCHAR(100) NOT NULL,
 key_prefix VARCHAR(16) NOT NULL,
 secret_hash VARCHAR(255) NOT NULL,
 status ENUM('active','revoked') NOT NULL DEFAULT 'active',
 last_used_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE audit_logs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NULL,
 action VARCHAR(100) NOT NULL,
 entity_type VARCHAR(80) NULL,
 entity_id BIGINT UNSIGNED NULL,
 ip_address VARCHAR(45) NULL,
 metadata_json JSON NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_audit_created(created_at), INDEX idx_audit_entity(entity_type,entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
