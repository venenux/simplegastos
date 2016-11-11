
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

/* 20161109 unificacion de info en subcategorias */

-- todo eventual unificado
SET SQL_SAFE_UPDATES = 0;
UPDATE `gastossystema`.`registro_gastos` SET `cod_subcategoria`='SUB20161009151904'
WHERE cod_subcategoria = 'SUB20161009151905' or cod_subcategoria = 'SUB20161009151906';
DELETE FROM `gastossystema`.`subcategoria` WHERE `cod_subcategoria`='SUB20161009151905';
DELETE FROM `gastossystema`.`subcategoria` WHERE `cod_subcategoria`='SUB20161009151906';

-- todo temporada unificado
SET SQL_SAFE_UPDATES = 0;
UPDATE `gastossystema`.`registro_gastos` SET `cod_subcategoria`='SUB20161009151901'
WHERE cod_subcategoria = 'SUB20161009151903';
DELETE FROM `gastossystema`.`subcategoria` WHERE `cod_subcategoria`='SUB20161009151903';

-- todo liquidacion unificado
SET SQL_SAFE_UPDATES = 0;
UPDATE `gastossystema`.`registro_gastos` SET `cod_subcategoria`='SUB20161009151916'
	WHERE cod_subcategoria = 'SUB20161009151917'
	OR  cod_subcategoria = 'SUB20161009151918'
	OR  cod_subcategoria = 'SUB20161009151919'
	OR cod_subcategoria = 'SUB20161020105303';
DELETE FROM `gastossystema`.`subcategoria` WHERE `cod_subcategoria`='SUB20161009151917';
DELETE FROM `gastossystema`.`subcategoria` WHERE `cod_subcategoria`='SUB20161009151918';
DELETE FROM `gastossystema`.`subcategoria` WHERE `cod_subcategoria`='SUB20161009151919';
DELETE FROM `gastossystema`.`subcategoria` WHERE `cod_subcategoria`='SUB20161020105303';


/* ********** 20161102 actualizacion soporte minimo gastos erroneos y notificaciones */

ALTER TABLE `gastossystema`.`categoria` CHARACTER SET = utf8 ,
	CHANGE COLUMN `tipo_categoria` `tipo_categoria` VARCHAR(40) NULL DEFAULT 'NORMAL' COMMENT 'ADMINISTRATIVO|NORMAL'  ,
	COMMENT = 'nivel 1 clasificacion gasto' ;
ALTER TABLE `gastossystema`.`entidad` CHARACTER SET = utf8 ,
	CHANGE COLUMN `tipo_entidad` `tipo_entidad` VARCHAR(40) NULL DEFAULT 'SUCURSAL' COMMENT 'ADMINISTRATIVA|SUCURSAL'  ,
	COMMENT = 'las entidades que se le adjudican gastos' ;
ALTER TABLE `gastossystema`.`entidad_usuario` CHARACTER SET = utf8 ,
	COMMENT = 'relacion usuario contra entidad que adjudica gastos' ;
ALTER TABLE `gastossystema`.`fondo` CHARACTER SET = utf8 ,
	CHANGE COLUMN `sessionficha` `sessionficha` VARCHAR(40) NULL DEFAULT NULL COMMENT 'quien le adjudico el fondo'  ;
ALTER TABLE `gastossystema`.`log` CHARACTER SET = utf8 ,
	CHANGE COLUMN `sessionficha` `sessionficha` VARCHAR(40) NULL DEFAULT NULL COMMENT 'quien lo hizo YYYYMMDDhhmmss+codger+.+user'  ;
ALTER TABLE `gastossystema`.`registro_gastos` CHARACTER SET = utf8 ,
	CHANGE COLUMN `des_concepto` `des_concepto` VARCHAR(4000) NOT NULL COMMENT 'descripcion del gasto'  ,
	CHANGE COLUMN `tipo_concepto` `tipo_concepto` VARCHAR(40) NULL DEFAULT 'NORMAL' COMMENT 'ADMINISTRATIVO|NORMAL'  ,
	CHANGE COLUMN `des_detalle` `des_detalle` TEXT NULL DEFAULT NULL COMMENT 'detalle opcional del gasto'  ,
	CHANGE COLUMN `des_estado` `des_estado` TEXT NULL DEFAULT NULL COMMENT 'porque cambio de estado'  ,
	CHANGE COLUMN `factura_rif` `factura_rif` VARCHAR(40) NULL DEFAULT NULL COMMENT 'rif si factura es contribuyente'  ,
	CHANGE COLUMN `factura_bin` `factura_bin` TEXT NULL DEFAULT NULL COMMENT 'ruta/hex64 de factura por defecto si la sube'  ,
	CHANGE COLUMN `fecha_concepto` `fecha_concepto` VARCHAR(40) NULL DEFAULT NULL COMMENT 'YYYYMMDD de cuadno es el gasto registrado'  ,
	CHANGE COLUMN `fecha_registro` `fecha_registro` VARCHAR(40) NULL DEFAULT NULL COMMENT 'YYYYMMDD de cuando se creo cada entrada'  ,
	COMMENT = 'descripcion y monto gastos o concepto' ;
ALTER TABLE `gastossystema`.`subcategoria` CHARACTER SET = utf8 ,
	CHANGE COLUMN `tipo_subcategoria` `tipo_subcategoria` VARCHAR(40) NULL DEFAULT 'NORMAL' COMMENT 'ADMINISTRATIVA|NORMAL'  ,
	COMMENT = 'nivel 2 clasificacion gasto' ;
ALTER TABLE `gastossystema`.`usuarios` CHARACTER SET = utf8 ,
	CHANGE COLUMN `clave` `clave` VARCHAR(40) NULL DEFAULT NULL COMMENT 'clave de intranet'  ,
	CHANGE COLUMN `detalles` `detalles` VARCHAR(400) NULL DEFAULT NULL COMMENT 'datos extra del usuario'  ,
	COMMENT = 'tabla de usuarios' ;

CREATE  TABLE IF NOT EXISTS `gastossystema`.`registro_errado` (
  `cod_registro` VARCHAR(40) NOT NULL COMMENT 'codigo del registro gasto corregir' ,
  `cod_entidad` VARCHAR(40) NOT NULL COMMENT 'a quien se le exige corregir' ,
  `msg_enviado` VARCHAR(40) NOT NULL COMMENT 'si ya se envio un mensage correo no volver enviar' ,
  `sessionerror` VARCHAR(40) NULL DEFAULT NULL COMMENT 'quien erro y cuando YYYYMMDDhhmmss + codger + . + ficha' ,
  `sessionficha` VARCHAR(40) NULL DEFAULT NULL COMMENT 'quien lo creo YYYYMMDDhhmmss + codger + . + ficha' ,
  PRIMARY KEY (`cod_registro`) )
COMMENT = 'notificaciones de errores cargados a usuarios' ;


/* TODO 20161110 actualizacion al modelo y soporte de errores notificaciones */

start transaction;

ALTER TABLE `gastossystema`.`categoria`
	CHANGE COLUMN `tipo_categoria` `tipo_categoria` VARCHAR(40) NULL DEFAULT 'NORMAL' COMMENT 'ADMINISTRATIVO|NORMAL'  ,
	COMMENT = 'nivel 1 clasificacion gasto' ;
ALTER TABLE `gastossystema`.`entidad`
	CHANGE COLUMN `tipo_entidad` `tipo_entidad` VARCHAR(40) NULL DEFAULT 'SUCURSAL' COMMENT 'ADMINISTRATIVA|SUCURSAL'  ,
	COMMENT = 'las entidades que se le adjudican gastos' ;
ALTER TABLE `gastossystema`.`entidad_usuario`
	COMMENT = 'relacion usuario contra entidad que adjudica gastos' ;
ALTER TABLE `gastossystema`.`fondo`
	CHANGE COLUMN `sessionficha` `sessionficha` VARCHAR(40) NULL DEFAULT NULL COMMENT 'quien lo hizo YYYYMMDDhhmmss+.+codger+.+user'  ,
	COMMENT = 'fondos de montos manejados' ;
ALTER TABLE `gastossystema`.`log`
	CHANGE COLUMN `sessionficha` `sessionficha` VARCHAR(40) NULL DEFAULT NULL COMMENT 'quien hizo que YYYYMMDDhhmmss+codger+.+user'  ,
	COMMENT = 'tabla de chismoso, cada operacion se graba aqui' ;
ALTER TABLE `gastossystema`.`registro_gastos`
	CHANGE COLUMN `des_concepto` `des_concepto` VARCHAR(8000) NOT NULL COMMENT 'descripcion del gasto'  ,
	CHANGE COLUMN `tipo_concepto` `tipo_concepto` VARCHAR(40) NULL DEFAULT 'SUCURSAL' COMMENT 'ADMINISTRATIVO|SUCURSAL'  ,
	CHANGE COLUMN `des_detalle` `des_detalle` VARCHAR(4000) NULL DEFAULT NULL COMMENT 'detalle opcional del gasto'  ,
	CHANGE COLUMN `des_estado` `des_estado` VARCHAR(4000) NULL DEFAULT NULL COMMENT 'porque cambio de estado'  ,
	CHANGE COLUMN `factura_rif` `factura_rif` VARCHAR(40) NULL DEFAULT NULL COMMENT 'rif si factura es contribuyente'  ,
	CHANGE COLUMN `factura_bin` `factura_bin` VARCHAR(4000) NULL DEFAULT NULL COMMENT 'ruta/hex64 de factura por defecto si la sube, es una factura o archivo por gasto'  ,
	CHANGE COLUMN `fecha_concepto` `fecha_concepto` VARCHAR(40) NULL DEFAULT NULL COMMENT 'YYYYMMDD de cuadno es el gasto registrado'  , CHANGE COLUMN `fecha_registro` `fecha_registro` VARCHAR(40) NULL DEFAULT NULL COMMENT 'YYYYMMDD de cuando se creo cada entrada'  ,
	COMMENT = 'descripcion y monto gastos o concepto' ;
ALTER TABLE `gastossystema`.`usuarios`
	CHANGE COLUMN `clave` `clave` VARCHAR(40) NULL DEFAULT NULL COMMENT 'clave de intranet'  ,
	CHANGE COLUMN `detalles` `detalles` VARCHAR(400) NULL DEFAULT NULL COMMENT 'datos extra del usuario'  ,
	COMMENT = 'tabla de usuarios' ;
ALTER TABLE `gastossystema`.`subcategoria` CHANGE COLUMN `tipo_subcategoria` `tipo_subcategoria` VARCHAR(40) NULL DEFAULT 'NORMAL' COMMENT 'ADMINISTRATIVA|NORMAL'  ,
	COMMENT = 'nivel 2 clasificacion gasto' ;
ALTER TABLE `gastossystema`.`registro_errado` DROP COLUMN `msg_enviado` , CHANGE COLUMN `cod_entidad` `cod_entidad` VARCHAR(40) NOT NULL COMMENT 'entidad la cual se le exige corregir'  , ADD COLUMN `intranet` VARCHAR(40) NOT NULL COMMENT 'usuario que cometio el error (puede este en mas de una entidad)'  AFTER `cod_entidad` , ADD COLUMN `msg_errado` VARCHAR(40) NULL DEFAULT NULL  AFTER `intranet` , ADD COLUMN `msg_enviar` VARCHAR(40) NULL DEFAULT NULL COMMENT 'si ya se envio un correo'  AFTER `msg_errado` ,
	COMMENT = 'notificaciones de auditoria a errores cargados por usuarios' ;

DROP VIEW IF EXISTS `gastossystema`.`fondos` ;
DROP VIEW IF EXISTS `gastossystema`.`marixtodoscruda` ;

UPDATE `gastossystema`.`registro_gastos`
	SET `tipo_concepto`='SUCURSAL' WHERE `tipo_concepto`='NORMAL';

commit;
