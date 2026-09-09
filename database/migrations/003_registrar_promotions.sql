ALTER TABLE promotions
  ADD COLUMN registrar_id BIGINT UNSIGNED NULL AFTER code,
  DROP INDEX code,
  ADD UNIQUE KEY uq_promo_registrar_code (registrar_id, code),
  ADD INDEX idx_promo_registrar_status (registrar_id, status),
  ADD CONSTRAINT fk_promotions_registrar FOREIGN KEY (registrar_id) REFERENCES registrars(id) ON DELETE CASCADE;
