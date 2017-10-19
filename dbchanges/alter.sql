ALTER TABLE `pim_ser_num`
ADD COLUMN `islatest` TINYINT(1) NOT NULL AFTER `col13`;

ALTER TABLE `pim_ser_num`
ADD COLUMN `result` TINYINT(1) NOT NULL AFTER `islatest`;
