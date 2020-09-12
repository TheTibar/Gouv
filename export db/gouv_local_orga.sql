CREATE TABLE `gouv`.`gouv_local_orga` (
  `gouv_local_orga_id` INT NOT NULL AUTO_INCREMENT,
  `theme` VARCHAR(150) NULL,
  `id` INT NULL,
  `remote_id` INT NULL,
  `level` INT NULL,
  `link` TEXT NULL,
  `label` TEXT NULL,
  `father_id` INT NULL,
  `created_on` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  `process_id` INT NULL,
  PRIMARY KEY (`gouv_local_orga_id`));