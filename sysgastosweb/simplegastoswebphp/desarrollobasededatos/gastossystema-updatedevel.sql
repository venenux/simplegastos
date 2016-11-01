
/*
ALTER TABLE `gastossystema`.`registro_gastos`
	CHANGE COLUMN `des_estado` `des_estado` VARCHAR(400) NULL DEFAULT NULL COMMENT 'porque cambio de estado'  ,
	CHANGE COLUMN `tipo_gasto` `factura_tipo` VARCHAR(40) NULL DEFAULT 'EGRESO' COMMENT 'EGRESO|CONTRIBUYENTE'  , 
	CHANGE COLUMN `factura1_rif` `factura_rif` VARCHAR(40) NULL DEFAULT NULL COMMENT 'rif si factura es contribuyente'  , 
	CHANGE COLUMN `factura1_num` `factura_num` VARCHAR(40) NULL DEFAULT NULL COMMENT 'mumero de factura opcinal'  , 
	CHANGE COLUMN `factura1_bin` `factura_bin` VARCHAR(10000) NULL DEFAULT NULL COMMENT 'ruta/hex64 de factura por defecto si la sube'  , 
	CHANGE COLUMN `fecha_registro` `fecha_registro` VARCHAR(40) NULL DEFAULT NULL COMMENT 'para mostrar usuario y auditoria cuando' ;

ALTER TABLE `gastossystema`.`registro_gastos`
	ADD COLUMN `tipo_concepto` VARCHAR(40) NULL DEFAULT NULL COMMENT 'ADMINISTRATIVO|NORMAL'  AFTER `des_concepto` ;
*/


SELECT SUBSTRING(a.sessionficha,1,8),a.* FROM gastossystema.registro_gastos as a 
	WHERE CONVERT(`cod_entidad`,UNSIGNED)>399 and CONVERT(`cod_entidad`,UNSIGNED)< 987;


SET SQL_SAFE_UPDATES = 0;
UPDATE `gastossystema`.`registro_gastos` 
	SET `tipo_concepto`='ADMINISTRATIVO' 
	WHERE CONVERT(`cod_entidad`,UNSIGNED)<399 or CONVERT(`cod_entidad`,UNSIGNED)>987;

SET SQL_SAFE_UPDATES = 0;
UPDATE `gastossystema`.`registro_gastos` 
	SET `tipo_concepto`='SUCURSAL' 
	WHERE CONVERT(`cod_entidad`,UNSIGNED)>399 and CONVERT(`cod_entidad`,UNSIGNED)<987;

SET SQL_SAFE_UPDATES = 0;
UPDATE `gastossystema`.`registro_gastos` 
	SET `tipo_concepto`='SUCURSAL' 
	WHERE `tipo_concepto`='NORMAL' ;

UPDATE `gastossystema`.`registro_gastos` 
	SET `fecha_registro`='20161017' WHERE `fecha_registro`='' and `sessionflag` <> '';

UPDATE `gastossystema`.`registro_gastos` 
	SET `fecha_registro`= SUBSTRING(`sessionficha`,1,8) WHERE `sessionficha`<>'' and `fecha_registro` = '';
