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

-- -----------------------------------------------------
-- Data for table `categoria`
-- -----------------------------------------------------
START TRANSACTION;
USE `gastossystema`;
INSERT INTO `categoria` (`cod_categoria`, `des_categoria`, `fecha_categoria`, `sessionflag`) VALUES ('CAT20161002000000', 'Especial', '20161003', NULL);
INSERT INTO `categoria` (`cod_categoria`, `des_categoria`, `fecha_categoria`, `sessionflag`) VALUES ('CAT20161002010001', 'Virtual', '20161003', NULL);

COMMIT;

-- -----------------------------------------------------
-- Data for table `entidad`
-- -----------------------------------------------------
START TRANSACTION;
USE `gastossystema`;
INSERT INTO `entidad` (`cod_entidad`, `abr_entidad`, `abr_zona`, `des_entidad`, `status`, `sello`, `sessionflag`) VALUES ('000', 'sys', 'tod', 'departamento de systemas', 'ACTIVO', '999', NULL);
INSERT INTO `entidad` (`cod_entidad`, `abr_entidad`, `abr_zona`, `des_entidad`, `status`, `sello`, `sessionflag`) VALUES ('001', 'vir', 'tod', 'seccion virtual transitoria', 'INACTIVO', '000', NULL);

COMMIT;

-- -----------------------------------------------------
-- Data for table `registro_adjunto`
-- -----------------------------------------------------
START TRANSACTION;
USE `gastossystema`;
INSERT INTO `registro_adjunto` (`cod_adjunto`, `cod_registro`, `hex_adjunto`, `nam_adjunto`, `nam_archivo`, `ruta_adjunto`, `fecha_adjunto`, `sessionflag`) VALUES ('ADJ20161003000000', 'GAS20161003000000', NULL, 'factura.pdf', NULL, NULL, NULL, NULL);

COMMIT;

-- -----------------------------------------------------
-- Data for table `registro_gastos`
-- -----------------------------------------------------
START TRANSACTION;
USE `gastossystema`;
INSERT INTO `registro_gastos` (`cod_registro`, `cod_sucursal`, `cod_categoria`, `cod_subcategoria`, `des_registro`, `mon_registro`, `num_factura`, `estado`, `fecha_registro`, `fecha_factura`, `sessionflag`) VALUES ('GAS20161003000000', '000', 'CAT20161002000000', 'SUB20161002000000', 'Inversion castellana', 100023400056.34, 'FR345632', 'PROCESADO', '20161004', '20160912', '');
INSERT INTO `registro_gastos` (`cod_registro`, `cod_sucursal`, `cod_categoria`, `cod_subcategoria`, `des_registro`, `mon_registro`, `num_factura`, `estado`, `fecha_registro`, `fecha_factura`, `sessionflag`) VALUES ('GAS20161004000300', '000', 'CAT20161002010001', 'SUB20161002000001', 'Comida para compartir', 70345.98, '', 'PROCESADO', '', '', NULL);

COMMIT;

-- -----------------------------------------------------
-- Data for table `subcategoria`
-- -----------------------------------------------------
START TRANSACTION;
USE `gastossystema`;
INSERT INTO `subcategoria` (`cod_categoria`, `cod_subcategoria`, `des_subcategoria`, `fecha_subcategoria`, `sessionflag`) VALUES ('CAT20161002000000', 'SUB20161002000000', 'Gastos especiales', '20161002', NULL);
INSERT INTO `subcategoria` (`cod_categoria`, `cod_subcategoria`, `des_subcategoria`, `fecha_subcategoria`, `sessionflag`) VALUES ('CAT20161002010001', 'SUB20161002000001', 'No contabilizado', '20161002', NULL);
INSERT INTO `subcategoria` (`cod_categoria`, `cod_subcategoria`, `des_subcategoria`, `fecha_subcategoria`, `sessionflag`) VALUES ('CAT20161002010001', 'SUB20161002000002', 'Pendiente y perdido', '20161002', NULL);

COMMIT;

-- -----------------------------------------------------
-- Data for table `entidad_usuario`
-- -----------------------------------------------------
START TRANSACTION;
USE `gastossystema`;
INSERT INTO `entidad_usuario` (`ficha`, `cod_entidad`, `sessionflag`) VALUES ('99999990', '000', NULL);

COMMIT;

-- -----------------------------------------------------
-- Data for table `usuarios`
-- -----------------------------------------------------
START TRANSACTION;
USE `gastossystema`;
INSERT INTO `usuarios` (`ficha`, `intranet`, `clave`, `codger`, `nombre`, `estado`, `acc_lectura`, `acc_escribe`, `acc_modifi`, `fecha_ultimavez`, `fecha_ficha`, `sessionflag`) VALUES ('13303951', 'pedro_perez', '123', '34', 'Pedro Perez', 'ACTIVO', 'TODOS', 'TODOS', 'TODOS', '', '20161004', NULL);
INSERT INTO `usuarios` (`ficha`, `intranet`, `clave`, `codger`, `nombre`, `estado`, `acc_lectura`, `acc_escribe`, `acc_modifi`, `fecha_ultimavez`, `fecha_ficha`, `sessionflag`) VALUES ('14345678', 'pablo_repez', '123', '23', 'Pablo Repez', 'INACTIVO', 'TODOS', 'NADA', 'TODOS', '', '20161004', NULL);
INSERT INTO `usuarios` (`ficha`, `intranet`, `clave`, `codger`, `nombre`, `estado`, `acc_lectura`, `acc_escribe`, `acc_modifi`, `fecha_ultimavez`, `fecha_ficha`, `sessionflag`) VALUES ('11234567', 'uno_dos', '123', '11', 'Uno Dosc', 'ACTIVO', 'TODOS', 'NADA', 'TODOS', '', '20161003', NULL);
INSERT INTO `usuarios` (`ficha`, `intranet`, `clave`, `codger`, `nombre`, `estado`, `acc_lectura`, `acc_escribe`, `acc_modifi`, `fecha_ultimavez`, `fecha_ficha`, `sessionflag`) VALUES ('99999990', 'admin_user', '9990', '999', 'Administrador', 'ACTIVO', 'TODOS', 'TODOS', 'TODOS', '', '20160101', NULL);

COMMIT;
