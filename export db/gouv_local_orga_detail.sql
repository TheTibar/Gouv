CREATE TABLE `gouv_local_orga_detail` (
  `gouv_local_orga_detail_id` INT NOT NULL AUTO_INCREMENT,
  `gouv_local_orga_id` INT NULL,
  `region` VARCHAR(100) NULL,
  `departement` VARCHAR(100) NULL,
  `longit` FLOAT NULL,
  `latit` FLOAT NULL,
  `email` VARCHAR(100) NULL,
  `website` VARCHAR(100) NULL,
  `process_id` INT NULL,
  PRIMARY KEY (`gouv_local_orga_detail_id`));