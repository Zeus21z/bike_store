ALTER TABLE `usuarios`
  ADD COLUMN `verified` TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN `verification_token` VARCHAR(255) DEFAULT NULL;

-- (Opcional) Hacer email único si no hay duplicados. Quitar si produciría error.
ALTER TABLE `usuarios`
  ADD UNIQUE INDEX `idx_email_unique` (`email`);

-- Asegurar user_id como PRIMARY KEY AUTO_INCREMENT si no lo es (ejecutar sólo si user_id no tiene PK)
-- ALTER TABLE `usuarios` MODIFY `user_id` INT(11) NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`user_id`);

-- Tabla para manejar tokens de reset de contraseña
CREATE TABLE IF NOT EXISTS `password_resets` (
  `email` VARCHAR(255) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  PRIMARY KEY (`token`),
  INDEX (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
