
/* campo de tipo admin o normal en tablas */
ALTER TABLE `gastossystema`.`categoria`
	ADD COLUMN `tipo_categoria` VARCHAR(40) NULL DEFAULT NULL COMMENT 'ADMINISTRATIVO|NORMAL'
	AFTER `des_categoria`
ALTER TABLE `gastossystema`.`entidad`
	ADD COLUMN `tipo_entidad` VARCHAR(40) NULL DEFAULT NULL COMMENT 'ADMINISTRATIVA|SUCURSAL'
	AFTER `status`
ALTER TABLE `gastossystema`.`subcategoria`
	ADD COLUMN `tipo_subcategoria` VARCHAR(40) NULL DEFAULT NULL COMMENT 'ADMINISTRATIVA|NORMAL'
	AFTER `des_subcategoria`
ALTER TABLE `gastossystema`.`usuarios`
	ADD COLUMN `tipo_usuario` VARCHAR(40) NULL DEFAULT NULL COMMENT 'ADMINISTRATIVO|NORMAL'
	AFTER `estado`



ALTER TABLE `gastossystema`.`usuarios`
	CHANGE COLUMN `clave` `clave` VARCHAR(40) NULL DEFAULT NULL COMMENT 'clave de intranet',
	CHANGE COLUMN `detalles` `detalles` VARCHAR(400) NULL DEFAULT NULL COMMENT 'datos extra del usuario',

ALTER TABLE `gastossystema`.`fondo`
	CHANGE COLUMN `sessionficha` `sessionficha` VARCHAR(40) NULL DEFAULT NULL COMMENT 'quien adjudico el fondo YYYYMMDDhhmmss+codger+.+user'
ALTER TABLE `gastossystema`.`log`
	CHANGE COLUMN `sessionficha` `sessionficha` VARCHAR(40) NULL DEFAULT NULL COMMENT 'quien lo hizo YYYYMMDDhhmmss+codger+.+user'
ALTER TABLE `gastossystema`.`entidad_usuario`
	DROP PRIMARY KEY, ADD PRIMARY KEY (`intranet`, `cod_entidad`)


ALTER TABLE `gastossystema`.`registro_gastos`
	CHANGE COLUMN `des_estado` `des_estado` VARCHAR(400) NULL DEFAULT NULL COMMENT 'porque cambio de estado'  ,
	CHANGE COLUMN `tipo_gasto` `factura_tipo` VARCHAR(40) NULL DEFAULT 'EGRESO' COMMENT 'EGRESO|CONTRIBUYENTE'  , CHANGE COLUMN `factura1_rif` `factura_rif` VARCHAR(40) NULL DEFAULT NULL COMMENT 'rif si factura es contribuyente'  , CHANGE COLUMN `factura1_num` `factura_num` VARCHAR(40) NULL DEFAULT NULL COMMENT 'mumero de factura opcinal'  , CHANGE COLUMN `factura1_bin` `factura_bin` VARCHAR(10000) NULL DEFAULT NULL COMMENT 'ruta/hex64 de factura por defecto si la sube'  , CHANGE COLUMN `fecha_registro` `fecha_registro` VARCHAR(40) NULL DEFAULT NULL COMMENT 'para mostrar usuario y auditoria cuando'  ;

ALTER TABLE `gastossystema`.`subcategoria` ADD COLUMN `tipo_subcategoria` VARCHAR(40) NULL DEFAULT NULL COMMENT 'ADMINISTRATIVA|NORMAL'  AFTER `des_subcategoria` ;

ALTER TABLE `gastossystema`.`usuarios` CHANGE COLUMN `clave` `clave` VARCHAR(40) NULL DEFAULT NULL COMMENT 'clave de intranet'  , CHANGE COLUMN `detalles` `detalles` VARCHAR(400) NULL DEFAULT NULL COMMENT 'datos extra del usuario'  , ADD COLUMN `tipo_usuario` VARCHAR(40) NULL DEFAULT NULL  AFTER `estado` ;


-- /* lo de alter */

ALTER TABLE `gastossystema`.`categoria` ADD COLUMN `tipo_categoria` VARCHAR(40) NULL DEFAULT NULL COMMENT ' /* comment truncated */ /*ADMINISTRATIVO|NORMAL*/'  AFTER `des_categoria` ;

ALTER TABLE `gastossystema`.`entidad` ADD COLUMN `tipo_entidad` VARCHAR(40) NULL DEFAULT NULL COMMENT ' /* comment truncated */ /*ADMINISTRATIVA|SUCURSAL*/'  AFTER `status` ;

ALTER TABLE `gastossystema`.`fondo` CHANGE COLUMN `sessionficha` `sessionficha` VARCHAR(40) NULL DEFAULT NULL COMMENT ' /* comment truncated */ /*quien le adjudico el fondo*/'  ;

ALTER TABLE `gastossystema`.`log` CHANGE COLUMN `sessionficha` `sessionficha` VARCHAR(40) NULL DEFAULT NULL COMMENT ' /* comment truncated */ /*quien lo hizo YYYYMMDDhhmmss+codger+.+user*/'  ;

ALTER TABLE `gastossystema`.`registro_gastos` DROP COLUMN `factura1_bin` , DROP COLUMN `factura1_num` , DROP COLUMN `factura1_rif` , DROP COLUMN `tipo_gasto` , CHANGE COLUMN `des_estado` `des_estado` VARCHAR(400) NULL DEFAULT NULL COMMENT ' /* comment truncated */ /*porque cambio de estado*/'  , CHANGE COLUMN `fecha_registro` `fecha_registro` VARCHAR(40) NULL DEFAULT NULL COMMENT ' /* comment truncated */ /*para mostrar usuario y auditoria cuando*/'  , ADD COLUMN `tipo_concepto` VARCHAR(40) NULL DEFAULT NULL COMMENT ' /* comment truncated */ /*ADMINISTRATIVO|NORMAL*/'  AFTER `des_concepto` , ADD COLUMN `factura_tipo` VARCHAR(40) NULL DEFAULT 'EGRESO' COMMENT ' /* comment truncated */ /*EGRESO|CONTRIBUYENTE*/'  AFTER `estado` , ADD COLUMN `factura_rif` VARCHAR(40) NULL DEFAULT NULL COMMENT ' /* comment truncated */ /*rif si factura es contribuyente*/'  AFTER `factura_tipo` , ADD COLUMN `factura_num` VARCHAR(40) NULL DEFAULT NULL COMMENT ' /* comment truncated */ /*mumero de factura opcinal*/'  AFTER `factura_rif` , ADD COLUMN `factura_bin` VARCHAR(10000) NULL DEFAULT NULL COMMENT ' /* comment truncated */ /*ruta/hex64 de factura por defecto si la sube*/'  AFTER `factura_num` ;

ALTER TABLE `gastossystema`.`subcategoria` ADD COLUMN `tipo_subcategoria` VARCHAR(40) NULL DEFAULT NULL COMMENT ' /* comment truncated */ /*ADMINISTRATIVA|NORMAL*/'  AFTER `des_subcategoria` ;

ALTER TABLE `gastossystema`.`usuarios` CHANGE COLUMN `clave` `clave` VARCHAR(40) NULL DEFAULT NULL COMMENT ' /* comment truncated */ /*clave de intranet*/'  , CHANGE COLUMN `detalles` `detalles` VARCHAR(400) NULL DEFAULT NULL COMMENT ' /* comment truncated */ /*datos extra del usuario*/'  , ADD COLUMN `tipo_usuario` VARCHAR(40) NULL DEFAULT NULL COMMENT ' /* comment truncated */ /*ADMINISTRATIVO|NORMAL*/'  AFTER `estado` ;


-- -----------------------------------------------------
-- Placeholder table for view `gastossystema`.`fondos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `gastossystema`.`fondos` (`cod_fondo` INT, `mon_fondo` INT, `fecha_fondo` INT, `cod_quien` INT, `quien` INT);


USE `gastossystema`;

-- -----------------------------------------------------
-- View `gastossystema`.`fondos`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `gastossystema`.`fondos`;
USE `gastossystema`;
CREATE   OR REPLACE VIEW `gastossystema`.`fondos` AS
SELECT
     ifnull(fo.cod_fondo,'N/A') as cod_fondo,
     ifnull(fo.mon_fondo,'N/A') as mon_fondo,
     fo.fecha_fondo, en.cod_entidad as cod_quien, en.des_entidad as quien
 FROM gastossystema.fondo AS fo
 RIGHT JOIN gastossystema.entidad AS en
 ON en.cod_fondo = fo.cod_fondo
 UNION
 SELECT
     ifnull(fo.cod_fondo,'N/A') as cod_fondo,
     ifnull(fo.mon_fondo,'N/A') as mon_fondo,
     fo.fecha_fondo, us.intranet as cod_quien, us.nombre as quien
 FROM gastossystema.fondo AS fo
 RIGHT JOIN gastossystema.usuarios AS us
 ON us.cod_fondo = fo.cod_fondo
ORDER BY fecha_fondo DESC;
