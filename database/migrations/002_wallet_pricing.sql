CREATE TABLE IF NOT EXISTS wallet_transactions (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 type ENUM('credit','debit','refund','adjustment') NOT NULL,
 amount DECIMAL(18,8) NOT NULL,
 balance_after DECIMAL(18,8) NOT NULL,
 reference_type VARCHAR(50) NULL,
 reference_id BIGINT UNSIGNED NULL,
 description VARCHAR(255) NULL,
 created_by BIGINT UNSIGNED NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_wallet_user_created(user_id,created_at),
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS reseller_prices (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 tld VARCHAR(63) NOT NULL,
 register_price DECIMAL(18,8) NULL,
 transfer_price DECIMAL(18,8) NULL,
 renewal_price DECIMAL(18,8) NULL,
 UNIQUE KEY uq_user_tld(user_id,tld),
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
