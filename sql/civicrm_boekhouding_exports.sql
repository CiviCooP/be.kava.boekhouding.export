CREATE TABLE IF NOT EXISTS `civicrm_boekhouding_exports` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `contact_id` INT NOT NULL,
  `periode_start` DATE NOT NULL,
  `periode_stop` DATE NOT NULL,
  `created_at` DATE NOT NULL,
  `filename` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  INDEX `year_contact INDEX` (`contact_id` ASC))
ENGINE = InnoDB
CHARACTER SET utf8;