-- cambios 20161004

--ALTER TABLE `gastossystema`.`entidad` DROP COLUMN `codger` , CHANGE COLUMN `cod_entidad` `cod_entidad` VARCHAR(40) NOT NULL COMMENT 'codger de la sucursal o id en nomina'  , CHANGE COLUMN `sessionflag` `sessionflag` VARCHAR(40) NULL DEFAULT NULL COMMENT 'esto es quien lo hizo yyyymmddhhmmss+godger+.+ficha'  , ADD COLUMN `sello` VARCHAR(40) NOT NULL COMMENT 'solo las ubicaciones usan selllos, departamentos usan codger'  AFTER `status` ;
--ALTER TABLE `gastossystema`.`registro_adjunto` CHANGE COLUMN `ruta_adjunto` `ruta_adjunto` VARCHAR(400) NOT NULL COMMENT 'ruta en el servidor para descargar opcional'  ;
--ALTER TABLE `gastossystema`.`registro_gastos` CHANGE COLUMN `cod_sucursal` `cod_sucursal` VARCHAR(40) NOT NULL COMMENT 'codger de la entidad al cual se le adjudica'  , CHANGE COLUMN `cod_categoria` `cod_categoria` VARCHAR(40) NULL DEFAULT NULL COMMENT 'compatibilidad CRUD - se puede sacar con el subcategoria'  ;
--ALTER TABLE `gastossystema`.`subcategoria` CHANGE COLUMN `cod_categoria` `cod_categoria` VARCHAR(40) NOT NULL COMMENT 'CATYYYYMMDDhhmmss tabla categoria'  , CHANGE COLUMN `cod_subcategoria` `cod_subcategoria` VARCHAR(40) NOT NULL COMMENT 'SUBYYYYMMDDhhmmss'  ;
--ALTER TABLE `gastossystema`.`entidad_usuario` CHANGE COLUMN `cod_entidad` `cod_entidad` VARCHAR(40) NOT NULL COMMENT 'codger al cual esta asociado'  , ADD COLUMN `sessionflag` VARCHAR(40) NULL DEFAULT NULL  AFTER `cod_entidad` ;
--ALTER TABLE `gastossystema`.`usuarios` CHANGE COLUMN `codger` `codger` VARCHAR(40) NOT NULL COMMENT 'OJO: este solo es para saber si es de tienda o administrativo, no es la pertenencia'  , CHANGE COLUMN `estado` `estado` VARCHAR(40) NOT NULL COMMENT 'ACTIVO INACTIVO SUSPENDIDO INVALIDO'  ;
--ALTER TABLE `gastossystema`.`registro_gastos` CHANGE COLUMN `cod_sucursal` `cod_entidad` VARCHAR(40) NOT NULL COMMENT 'codger de la entidad al cual se le adjudica'
--ALTER TABLE `gastossystema`.`categoria` CHANGE COLUMN `sessionflag` `sessionflag` VARCHAR(40) NULL DEFAULT NULL COMMENT 'esto es quien_registro YYYYMMDDhhmmss + codger + . + ficha'  ;
--ALTER TABLE `gastossystema`.`log` CHANGE COLUMN `sessionflag` `sessionflag` VARCHAR(40) NULL DEFAULT NULL COMMENT 'esto es quien_registro YYYYMMDDhhmmss + codger + . + ficha'  ;
--ALTER TABLE `gastossystema`.`registro_adjunto` CHANGE COLUMN `sessionflag` `sessionflag` VARCHAR(40) NULL DEFAULT NULL COMMENT 'esto es quien_registro YYYYMMDDhhmmss + codger + . + ficha'  ;
--ALTER TABLE `gastossystema`.`registro_gastos` CHANGE COLUMN `sessionflag` `sessionflag` VARCHAR(40) NULL DEFAULT NULL COMMENT 'esto es quien_registro YYYYMMDDhhmmss + codger + . + ficha'  ;
--ALTER TABLE `gastossystema`.`subcategoria` CHANGE COLUMN `sessionflag` `sessionflag` VARCHAR(40) NULL DEFAULT NULL COMMENT 'esto es quien_registro YYYYMMDDhhmmss + codger + . + ficha'  ;
--ALTER TABLE `gastossystema`.`usuarios` CHANGE COLUMN `sessionflag` `sessionflag` VARCHAR(40) NULL DEFAULT NULL COMMENT 'esto es quien_registro YYYYMMDDhhmmss + codger + . + ficha'  ;

-- 20161005
--ALTER TABLE `gastossystema`.`entidad` CHANGE COLUMN `sello` `sello` VARCHAR(40) NULL DEFAULT NULL COMMENT 'solo las ubicaciones usan selllos, departamentos usan codger'  , ADD COLUMN `cod_fondo` VARCHAR(40) NULL DEFAULT NULL COMMENT 'fondo o monto disponible si aplica'  AFTER `status` ;
--ALTER TABLE `gastossystema`.`registro_gastos` CHANGE COLUMN `mon_registro` `mon_registro` DECIMAL(20,2) NOT NULL COMMENT 'cuanto se gasto, com algunos decimales'  ;
--ALTER TABLE `gastossystema`.`usuarios` DROP COLUMN `sello` , ADD COLUMN `sello` VARCHAR(40) NOT NULL COMMENT 'OJO: este solo es para saber si es de tienda o administrativo, no es la pertenencia'  AFTER `clave` , ADD COLUMN `cod_fondo` VARCHAR(40) NULL DEFAULT NULL COMMENT 'fondo o monto asociado si aplica'  AFTER `nombre` ;
--ALTER TABLE `gastossystema`.`entidad` CHANGE COLUMN `sello` `sello` VARCHAR(40) NULL DEFAULT NULL COMMENT 'solo las ubicaciones usan selllos, departamentos usan codger'  , ADD COLUMN `cod_fondo` VARCHAR(40) NULL DEFAULT NULL COMMENT 'fondo o monto disponible si aplica'  AFTER `status` ;
--ALTER TABLE `gastossystema`.`registro_gastos` CHANGE COLUMN `mon_registro` `mon_registro` DECIMAL(20,2) NOT NULL COMMENT 'cuanto se gasto, com algunos decimales'  ;
--ALTER TABLE `gastossystema`.`usuarios` DROP COLUMN `sello` , ADD COLUMN `sello` VARCHAR(40) NOT NULL COMMENT 'OJO: este solo es para saber si es de tienda o administrativo, no es la pertenencia'  AFTER `clave` , ADD COLUMN `cod_fondo` VARCHAR(40) NULL DEFAULT NULL COMMENT 'fondo o monto asociado si aplica'  AFTER `nombre` ;
--ALTER TABLE `gastossystema`.`registro_adjunto` ADD COLUMN `bin_adjunto` BLOB NULL  AFTER `sessionflag` ;
--ALTER TABLE `gastossystema`.`registro_gastos` CHANGE COLUMN `estado` `estado` VARCHAR(40) NULL DEFAULT NULL COMMENT 'APROBADO|RECHAZADO|PROCESADO|INVALIDO'  AFTER `mon_registro` , CHANGE COLUMN `cod_adjunto` `hex_factura` BLOB NULL DEFAULT NULL  AFTER `num_factura` , CHANGE COLUMN `fecha_factura` `fecha_factura` VARCHAR(40) NULL DEFAULT NULL COMMENT 'YYYYMMDD de la factura si tiene'  AFTER `hex_factura` ;

-- -----------------------------------------------------
-- Data for table `categoria`
-- -----------------------------------------------------
START TRANSACTION;

INSERT INTO `categoria` (`cod_categoria`,`des_categoria`,`fecha_categoria`,`sessionflag`) VALUES ('CAT20161002000000','Especial','20161003',NULL);
INSERT INTO `categoria` (`cod_categoria`,`des_categoria`,`fecha_categoria`,`sessionflag`) VALUES ('CAT20161002010001','Virtual','20161003',NULL);
INSERT INTO `categoria` (`cod_categoria`,`des_categoria`,`fecha_categoria`,`sessionflag`) VALUES ('CAT20161003230812','TRANSPORTE','20161003230812',NULL);
INSERT INTO `categoria` (`cod_categoria`,`des_categoria`,`fecha_categoria`,`sessionflag`) VALUES ('CAT20161003230834','CONSTRUCCION','20161003230834',NULL);
INSERT INTO `categoria` (`cod_categoria`,`des_categoria`,`fecha_categoria`,`sessionflag`) VALUES ('CAT20161003230941','INMUEBLE','20161003230941','pepe20161004010101');
INSERT INTO `categoria` (`cod_categoria`,`des_categoria`,`fecha_categoria`,`sessionflag`) VALUES ('CAT20161004001430','EXTRAS','20161004001430',NULL);
INSERT INTO `categoria` (`cod_categoria`,`des_categoria`,`fecha_categoria`,`sessionflag`) VALUES ('CAT20161004001447','NOMINA','20161004001447','pepe20161004010117');

INSERT INTO `subcategoria` (`cod_categoria`, `cod_subcategoria`, `des_subcategoria`, `fecha_subcategoria`, `sessionflag`) VALUES ('CAT20161002000000', 'SUB20161002000000', 'Gastos especiales', '20161002', NULL);
INSERT INTO `subcategoria` (`cod_categoria`, `cod_subcategoria`, `des_subcategoria`, `fecha_subcategoria`, `sessionflag`) VALUES ('CAT20161002010001', 'SUB20161002000001', 'No contabilizado', '20161002', NULL);
INSERT INTO `subcategoria` (`cod_categoria`, `cod_subcategoria`, `des_subcategoria`, `fecha_subcategoria`, `sessionflag`) VALUES ('CAT20161002010001', 'SUB20161002000002', 'Pendiente y perdido', '20161002', NULL);
INSERT INTO `subcategoria` (`cod_categoria`,`cod_subcategoria`,`des_subcategoria`,`fecha_subcategoria`,`sessionflag`) VALUES ('CAT20161004001447','SUB20160928125200','Quincena primera',NULL,NULL);
INSERT INTO `subcategoria` (`cod_categoria`,`cod_subcategoria`,`des_subcategoria`,`fecha_subcategoria`,`sessionflag`) VALUES ('CAT20161004001447','SUB20160928125201','Quincena segunda',NULL,'pepe20161004004742');
INSERT INTO `subcategoria` (`cod_categoria`,`cod_subcategoria`,`des_subcategoria`,`fecha_subcategoria`,`sessionflag`) VALUES ('CAT20161003230812','SUB20161003233848','Pagos','20161003233848',NULL);
INSERT INTO `subcategoria` (`cod_categoria`,`cod_subcategoria`,`des_subcategoria`,`fecha_subcategoria`,`sessionflag`) VALUES ('CAT20161003230941','SUB20161004010607','Ventas','20161004010607','pepe20161004010634');

INSERT INTO `entidad` (`cod_entidad`, `abr_entidad`, `abr_zona`, `des_entidad`, `status`, `sello`, `sessionflag`) VALUES ('000', 'sys', 'tod', 'departamento de systemas', 'ACTIVO', '999', NULL);
INSERT INTO `entidad` (`cod_entidad`, `abr_entidad`, `abr_zona`, `des_entidad`, `status`, `sello`, `sessionflag`) VALUES ('001', 'vir', 'tod', 'seccion virtual transitoria', 'INACTIVO', '000', NULL);
INSERT INTO `entidad` (`cod_entidad`,`abr_entidad`,`abr_zona`,`des_entidad`,`status`,`cod_fondo`,`sello`,`sessionflag`) VALUES ('001','CCS','CAP','Caracas','ESPECIAL',NULL,'',NULL);
INSERT INTO `entidad` (`cod_entidad`,`abr_entidad`,`abr_zona`,`des_entidad`,`status`,`cod_fondo`,`sello`,`sessionflag`) VALUES ('002','VL1','CEN','Valencia','ESPECIAL',NULL,'',NULL);
INSERT INTO `entidad` (`cod_entidad`,`abr_entidad`,`abr_zona`,`des_entidad`,`status`,`cod_fondo`,`sello`,`sessionflag`) VALUES ('23','ADM-CAS','CAP','Castellana','INACTIVO',NULL,'','pepe20161004010538');

INSERT INTO `registro_adjunto` (`cod_adjunto`, `cod_registro`, `hex_adjunto`, `nam_adjunto`, `nam_archivo`, `ruta_adjunto`, `fecha_adjunto`, `sessionflag`) VALUES ('ADJ20161003000000', 'GAS20161003000000', NULL, 'factura.pdf', NULL, NULL, NULL, NULL);

INSERT INTO `registro_gastos` (`cod_registro`, `cod_sucursal`, `cod_categoria`, `cod_subcategoria`, `des_registro`, `mon_registro`, `num_factura`, `estado`, `fecha_registro`, `fecha_factura`, `sessionflag`) VALUES ('GAS20161003000000', '000', 'CAT20161002000000', 'SUB20161002000000', 'Inversion castellana', 100023400056.34, 'FR345632', 'PROCESADO', '20161004', '20160912', '');
INSERT INTO `registro_gastos` (`cod_registro`, `cod_sucursal`, `cod_categoria`, `cod_subcategoria`, `des_registro`, `mon_registro`, `num_factura`, `estado`, `fecha_registro`, `fecha_factura`, `sessionflag`) VALUES ('GAS20161004000300', '000', 'CAT20161002010001', 'SUB20161002000001', 'Comida para compartir', 70345.98, '', 'PROCESADO', '', '', NULL);

INSERT INTO `entidad_usuario` (`ficha`, `cod_entidad`, `sessionflag`) VALUES ('99999990', '000', NULL);

INSERT INTO `usuarios` (`ficha`, `intranet`, `clave`, `codger`, `nombre`, `estado`, `acc_lectura`, `acc_escribe`, `acc_modifi`, `fecha_ultimavez`, `fecha_ficha`, `sessionflag`) VALUES ('13303951', 'pedro_perez', '123', '34', 'Pedro Perez', 'ACTIVO', 'TODOS', 'TODOS', 'TODOS', '', '20161004', NULL);
INSERT INTO `usuarios` (`ficha`, `intranet`, `clave`, `codger`, `nombre`, `estado`, `acc_lectura`, `acc_escribe`, `acc_modifi`, `fecha_ultimavez`, `fecha_ficha`, `sessionflag`) VALUES ('14345678', 'pablo_repez', '123', '23', 'Pablo Repez', 'INACTIVO', 'TODOS', 'NADA', 'TODOS', '', '20161004', NULL);
INSERT INTO `usuarios` (`ficha`, `intranet`, `clave`, `codger`, `nombre`, `estado`, `acc_lectura`, `acc_escribe`, `acc_modifi`, `fecha_ultimavez`, `fecha_ficha`, `sessionflag`) VALUES ('11234567', 'uno_dos', '123', '11', 'Uno Dosc', 'ACTIVO', 'TODOS', 'NADA', 'TODOS', '', '20161003', NULL);
INSERT INTO `usuarios` (`ficha`, `intranet`, `clave`, `codger`, `nombre`, `estado`, `acc_lectura`, `acc_escribe`, `acc_modifi`, `fecha_ultimavez`, `fecha_ficha`, `sessionflag`) VALUES ('99999990', 'admin_user', '9990', '999', 'Administrador', 'ACTIVO', 'TODOS', 'TODOS', 'TODOS', '', '20160101', NULL);

COMMIT;

START TRANSACTION;

INSERT INTO `registro_gastos` (`cod_registro`,`cod_entidad`,`cod_categoria`,`cod_subcategoria`,`des_registro`,`mon_registro`,`num_factura`,`estado`,`fecha_registro`,`fecha_factura`,`sessionflag`) VALUES ('GAS20160922000004','','20160928125201','2016092812520120160928125201','Compra pan dulce',10000.00,'','PROCESADO','20160922','','admin_user20161004165954');
INSERT INTO `registro_gastos` (`cod_registro`,`cod_entidad`,`cod_categoria`,`cod_subcategoria`,`des_registro`,`mon_registro`,`num_factura`,`estado`,`fecha_registro`,`fecha_factura`,`sessionflag`) VALUES ('GAS20161004175500','23','CAT20161003230941','SUB20161004010607','compra puerta',123456123.00,'','PROCESADO','20161004','','admin_user20161004175500');
INSERT INTO `registro_gastos` (`cod_registro`,`cod_entidad`,`cod_categoria`,`cod_subcategoria`,`des_registro`,`mon_registro`,`num_factura`,`estado`,`fecha_registro`,`fecha_factura`,`sessionflag`) VALUES ('GAS20161004215141','23','CAT20161003230812','SUB20161003233848','Pago de obrero comida',123.00,'','PROCESADO','20161004','','admin_user20161004215141');
INSERT INTO `registro_gastos` (`cod_registro`,`cod_entidad`,`cod_categoria`,`cod_subcategoria`,`des_registro`,`mon_registro`,`num_factura`,`estado`,`fecha_registro`,`fecha_factura`,`sessionflag`) VALUES ('GAS20161004220911','23','CAT20161003230812','SUB20161003233848','comida para gatos',22222333333.00,'','PROCESADO','20161004','','admin_user20161004220911');

COMMIT;
