DROP SCHEMA IF EXISTS `gastossystema` ;
CREATE SCHEMA IF NOT EXISTS `gastossystema` DEFAULT CHARACTER SET utf8 ;
USE `gastossystema` ;

-- -----------------------------------------------------
-- Table `categoria`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `categoria` ;
CREATE  TABLE IF NOT EXISTS `categoria` (
  `cod_categoria` VARCHAR(40) NOT NULL COMMENT 'CATYYYYMMDDhhmmss' ,
  `des_categoria` VARCHAR(400) NOT NULL COMMENT 'descripcion o nombre categoria' ,
  `fecha_categoria` VARCHAR(40) NULL DEFAULT NULL COMMENT 'fecha creacion al usuario' ,
  `sessionflag` VARCHAR(40) NULL DEFAULT NULL COMMENT 'quien altero YYYYMMDDhhmmss+codger+.+user' ,
  PRIMARY KEY (`cod_categoria`) )
COMMENT = 'nivel 1 clasificacion gasto';


-- -----------------------------------------------------
-- Table `entidad`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `entidad` ;
CREATE  TABLE IF NOT EXISTS `entidad` (
  `cod_entidad` VARCHAR(40) NOT NULL COMMENT 'codger entidad o id sucursal' ,
  `abr_entidad` VARCHAR(40) NOT NULL COMMENT 'abrebiacion de esta entidad' ,
  `abr_zona` VARCHAR(40) NOT NULL COMMENT 'siglas de la zona geografica' ,
  `des_entidad` VARCHAR(400) NOT NULL COMMENT 'descripcion sucursal' ,
  `status` VARCHAR(40) NOT NULL COMMENT 'ACTIVA|CERRADA|SUSPENDIDA|ESPECIAL' ,
  `sello` VARCHAR(40) NULL DEFAULT NULL COMMENT 'codigo ubicacion fisica solo' ,
  `sessionflag` VARCHAR(40) NULL DEFAULT NULL COMMENT 'quien altero YYYYMMDDhhmmss+codger+.+user' ,
  PRIMARY KEY (`cod_entidad`) )
COMMENT = 'las entidades que se le adjudican gastos';


-- -----------------------------------------------------
-- Table `entidad_usuario`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `entidad_usuario` ;
CREATE  TABLE IF NOT EXISTS `entidad_usuario` (
  `intranet` VARCHAR(40) NOT NULL COMMENT 'usuario relacionado' ,
  `cod_entidad` VARCHAR(40) NOT NULL COMMENT 'entidad asociado' ,
  `sessionflag` VARCHAR(40) NULL DEFAULT NULL COMMENT 'quien altero YYYYMMDDhhmmss+codger+.+user' ,
  PRIMARY KEY (`intranet`, `cod_entidad`) )
COMMENT = 'relacion usuario contra entidad que adjudica gastos';


-- -----------------------------------------------------
-- Table `registro_gastos`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `registro_gastos` ;
CREATE  TABLE IF NOT EXISTS `registro_gastos` (
  `cod_registro` VARCHAR(40) NOT NULL COMMENT 'GASYYYYMMDDhhmmss usa fecha y hora' ,
  `cod_entidad` VARCHAR(40) NOT NULL COMMENT 'codger de la entidad al cual se le adjudica' ,
  `cod_categoria` VARCHAR(40) NOT NULL COMMENT 'categoria del gasto' ,
  `cod_subcategoria` VARCHAR(40) NOT NULL COMMENT 'cual subcategoria no puede faltar' ,
  `mon_registro` DECIMAL(20,2) NOT NULL COMMENT 'monto de cuanto se gasto' ,
  `des_concepto` VARCHAR(400) NOT NULL COMMENT 'descripcion del gasto' ,
  `des_detalle` VARCHAR(10000) NULL DEFAULT NULL COMMENT 'detalle opcional del gasto' ,
  `des_estado` VARCHAR(400) NULL DEFAULT NULL COMMENT 'porque cambio de estado' ,
  `estado` VARCHAR(40) NULL DEFAULT 'PENDIENTE' COMMENT 'APROBADO|RECHAZADO|PENDIENTE|INVALIDO' ,
  `tipo_gasto` VARCHAR(40) NULL DEFAULT 'EGRESO' COMMENT 'EGRESO|CONTRIBUYENTE' ,
  `factura1_rif` VARCHAR(40) NULL DEFAULT NULL COMMENT 'rif si factura es contribuyente' ,
  `factura1_num` VARCHAR(40) NULL DEFAULT NULL COMMENT 'mumero de factura opcinal' ,
  `factura1_bin` VARCHAR(10000) NULL DEFAULT NULL COMMENT 'factura por defecto si la sube' ,
  `fecha_concepto` VARCHAR(40) NULL DEFAULT NULL COMMENT 'YYYYMMDD de la factura si tiene' ,
  `fecha_registro` VARCHAR(40) NULL DEFAULT NULL COMMENT 'para mostrar usuario y auditoria cuando' ,
  `sessionflag` VARCHAR(40) NULL DEFAULT NULL COMMENT 'quien modifico YYYYMMDDhhmmss + codger + . + ficha' ,
  `sessionficha` VARCHAR(40) NULL DEFAULT NULL COMMENT 'quien lo creo YYYYMMDDhhmmss + codger + . + ficha' ,
  PRIMARY KEY (`cod_registro`) )
COMMENT = 'descripcion y monto gastos o concepto';


-- -----------------------------------------------------
-- Table `subcategoria`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `subcategoria` ;
CREATE  TABLE IF NOT EXISTS `subcategoria` (
  `cod_categoria` VARCHAR(40) NOT NULL COMMENT 'CATYYYYMMDDhhmmss tabla categoria' ,
  `cod_subcategoria` VARCHAR(40) NOT NULL COMMENT 'SUBYYYYMMDDhhmmss' ,
  `des_subcategoria` VARCHAR(400) NOT NULL COMMENT 'descripcion o nombre subcategoria' ,
  `fecha_subcategoria` VARCHAR(40) NULL DEFAULT NULL COMMENT 'fecha creacion al usuario' ,
  `sessionflag` VARCHAR(40) NULL DEFAULT NULL COMMENT 'quien altero YYYYMMDDhhmmss+codger+.+user' ,
  PRIMARY KEY (`cod_subcategoria`) )
COMMENT = 'nivel 2 clasificacion gasto';


-- -----------------------------------------------------
-- Table `usuarios`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `usuarios` ;
CREATE  TABLE IF NOT EXISTS `usuarios` (
  `ficha` VARCHAR(40) NULL COMMENT 'id usuario, cedula en vnzla' ,
  `intranet` VARCHAR(40) NOT NULL COMMENT 'login del usuario, id del correo' ,
  `clave` VARCHAR(40) NULL DEFAULT NULL COMMENT 'clave de intranet' ,
  `sello` VARCHAR(40) NULL COMMENT 'OJO: usado como referencia' ,
  `nombre` VARCHAR(400) NULL DEFAULT NULL COMMENT 'nombre y apellido' ,
  `detalles` VARCHAR(400) NULL DEFAULT NULL COMMENT 'datos extra del usuario' ,
  `estado` VARCHAR(40) NOT NULL COMMENT 'ACTIVO|INACTIVO|SUSPENDIDO|INVALIDO' ,
  `acc_lectura` VARCHAR(4000) NULL COMMENT 'donde y que puede ver' ,
  `acc_escribe` VARCHAR(4000) NULL COMMENT 'donde y que puede adjudicar gasto' ,
  `acc_modifi` VARCHAR(4000) NULL COMMENT 'donde y que puede modificar gastos' ,
  `fecha_ultimavez` VARCHAR(40) NULL DEFAULT NULL COMMENT 'ultima vez que entro sesion' ,
  `sessionflag` VARCHAR(40) NULL DEFAULT NULL COMMENT 'quien altero YYYYMMDDhhmmss+codger+.+user' ,
  `sessionficha` VARCHAR(40) NULL COMMENT 'quien lo creo YYYYMMDDhhmmss+codger+.+user' ,
  PRIMARY KEY (`intranet`) )
COMMENT = 'tabla de usuarios';


START TRANSACTION;


-- -----------------------------------------------------
-- Data for table `categoria`
-- -----------------------------------------------------
INSERT INTO `categoria` (`cod_categoria`, `des_categoria`, `fecha_categoria`, `sessionflag`) VALUES ('CAT20161002000000', 'Especial', '20161003', NULL);
INSERT INTO `categoria` (`cod_categoria`, `des_categoria`, `fecha_categoria`, `sessionflag`) VALUES ('CAT20161002010001', 'CATE 1', '20161003', NULL);
INSERT INTO `categoria` (`cod_categoria`, `des_categoria`, `fecha_categoria`, `sessionflag`) VALUES ('CAT20161002010002', 'CATE 2', '20161007', NULL);

-- -----------------------------------------------------
-- Data for table `entidad`
-- -----------------------------------------------------
INSERT INTO `entidad` (`cod_entidad`, `abr_entidad`, `abr_zona`, `des_entidad`, `status`, `sello`, `sessionflag`) VALUES ('000', 'SYS', 'TODAS', 'systemas', 'ACTIVO', '999', NULL);
INSERT INTO `entidad` (`cod_entidad`, `abr_entidad`, `abr_zona`, `des_entidad`, `status`, `sello`, `sessionflag`) VALUES ('001', 'SUC1', 'CAPITAL', 'Sucursal 1', 'ACTIVO', '01', NULL);
INSERT INTO `entidad` (`cod_entidad`, `abr_entidad`, `abr_zona`, `des_entidad`, `status`, `sello`, `sessionflag`) VALUES ('002', 'SUC2', 'ZON-CEN', 'Sucursal 2', 'ACTIVO', '02', NULL);
INSERT INTO `entidad` (`cod_entidad`, `abr_entidad`, `abr_zona`, `des_entidad`, `status`, `sello`, `sessionflag`) VALUES ('003', 'SUC3', 'ZON-CEN', 'Sucursal 3', 'ACTIVO', '03', NULL);

-- -----------------------------------------------------
-- Data for table `entidad_usuario`
-- -----------------------------------------------------
INSERT INTO `entidad_usuario` (`intranet`, `cod_entidad`, `sessionflag`) VALUES ('usuario1', '001', NULL);
INSERT INTO `entidad_usuario` (`intranet`, `cod_entidad`, `sessionflag`) VALUES ('usuario2', '002', NULL);

-- -----------------------------------------------------
-- Data for table `registro_gastos`
-- -----------------------------------------------------
INSERT INTO `registro_gastos` (`cod_registro`, `cod_entidad`, `cod_categoria`, `cod_subcategoria`, `mon_registro`, `des_concepto`, `des_detalle`, `des_estado`, `estado`, `tipo_gasto`, `factura1_rif`, `factura1_num`, `factura1_bin`, `fecha_concepto`, `fecha_registro`, `sessionflag`, `sessionficha`) VALUES ('GAS20161007221632', '001', 'CAT20161002010001', 'SUB20161007221219', 1000.50, 'gastode suc 1 en cate 1 sub 1', 'datos extras', NULL, 'NULL', NULL, NULL, NULL, NULL, '20161007', '20161007', 'admin_user20161007221650', '');
INSERT INTO `registro_gastos` (`cod_registro`, `cod_entidad`, `cod_categoria`, `cod_subcategoria`, `mon_registro`, `des_concepto`, `des_detalle`, `des_estado`, `estado`, `tipo_gasto`, `factura1_rif`, `factura1_num`, `factura1_bin`, `fecha_concepto`, `fecha_registro`, `sessionflag`, `sessionficha`) VALUES ('GAS20161007221932', '001', 'CAT20161002010002', 'SUB20161007221439', 1000.50, 'gastp de suc 1 cate 2 sub2', 'otros 2', NULL, 'APROBADO', NULL, NULL, NULL, NULL, '20161007', '20161007', NULL, '');
INSERT INTO `registro_gastos` (`cod_registro`, `cod_entidad`, `cod_categoria`, `cod_subcategoria`, `mon_registro`, `des_concepto`, `des_detalle`, `des_estado`, `estado`, `tipo_gasto`, `factura1_rif`, `factura1_num`, `factura1_bin`, `fecha_concepto`, `fecha_registro`, `sessionflag`, `sessionficha`) VALUES ('GAS20161007222129', '001', 'CAT20161002010002', 'SUB20161007221415', 1000.50, 'gastp de suc 1 cate 2 sub 1', 'otros 3', NULL, 'APROBADO', NULL, NULL, NULL, NULL, '20161007', '20161007', 'admin_user20161007222221', '');
INSERT INTO `registro_gastos` (`cod_registro`, `cod_entidad`, `cod_categoria`, `cod_subcategoria`, `mon_registro`, `des_concepto`, `des_detalle`, `des_estado`, `estado`, `tipo_gasto`, `factura1_rif`, `factura1_num`, `factura1_bin`, `fecha_concepto`, `fecha_registro`, `sessionflag`, `sessionficha`) VALUES ('GAS20161007222327', '002', 'CAT20161002010002', 'SUB20161007221415', 1000.50, 'gasto de sucs 2 en cat 2 sub 1', 'otros', NULL, 'APROBADO', NULL, NULL, NULL, NULL, '20161007', '20161007', NULL, '');
INSERT INTO `registro_gastos` (`cod_registro`, `cod_entidad`, `cod_categoria`, `cod_subcategoria`, `mon_registro`, `des_concepto`, `des_detalle`, `des_estado`, `estado`, `tipo_gasto`, `factura1_rif`, `factura1_num`, `factura1_bin`, `fecha_concepto`, `fecha_registro`, `sessionflag`, `sessionficha`) VALUES ('GAS20161007222414', '002', 'CAT20161002010001', 'SUB20161007221219', 2000.50, 'gasto de suc 2 cat 1 sub 1', 'ninguno', NULL, 'NULL', NULL, NULL, NULL, NULL, '20161007', '20161007', 'admin_user20161007222428', '');
INSERT INTO `registro_gastos` (`cod_registro`, `cod_entidad`, `cod_categoria`, `cod_subcategoria`, `mon_registro`, `des_concepto`, `des_detalle`, `des_estado`, `estado`, `tipo_gasto`, `factura1_rif`, `factura1_num`, `factura1_bin`, `fecha_concepto`, `fecha_registro`, `sessionflag`, `sessionficha`) VALUES ('GAS20161007222736', '003', 'CAT20161002010001', 'SUB20161007221219', 1000.50, 'gasto de suc 3 cat 1 sub 1', 'otros 5', NULL, 'NULL', NULL, NULL, NULL, NULL, '20161007', '20161007', 'admin_user20161007222756', '');
INSERT INTO `registro_gastos` (`cod_registro`, `cod_entidad`, `cod_categoria`, `cod_subcategoria`, `mon_registro`, `des_concepto`, `des_detalle`, `des_estado`, `estado`, `tipo_gasto`, `factura1_rif`, `factura1_num`, `factura1_bin`, `fecha_concepto`, `fecha_registro`, `sessionflag`, `sessionficha`) VALUES ('GAS20161007222850', '003', 'CAT20161002010002', 'SUB20161007221415', 2000.50, 'gastos de suc 3 en cat 2 sub 1', 'est 1', NULL, 'NULL', NULL, NULL, NULL, NULL, '20161007', '20161007', NULL, '');
INSERT INTO `registro_gastos` (`cod_registro`, `cod_entidad`, `cod_categoria`, `cod_subcategoria`, `mon_registro`, `des_concepto`, `des_detalle`, `des_estado`, `estado`, `tipo_gasto`, `factura1_rif`, `factura1_num`, `factura1_bin`, `fecha_concepto`, `fecha_registro`, `sessionflag`, `sessionficha`) VALUES ('GAS20161007222941', '003', 'CAT20161002010002', 'SUB20161007221439', 1000.50, 'gastos de suc 3 en cat 2 sub 2', 'esta', NULL, 'NULL', NULL, NULL, NULL, NULL, '20161007', '20161007', NULL, '');
INSERT INTO `registro_gastos` (`cod_registro`, `cod_entidad`, `cod_categoria`, `cod_subcategoria`, `mon_registro`, `des_concepto`, `des_detalle`, `des_estado`, `estado`, `tipo_gasto`, `factura1_rif`, `factura1_num`, `factura1_bin`, `fecha_concepto`, `fecha_registro`, `sessionflag`, `sessionficha`) VALUES ('GAS20161008005532', '000', 'CAT20161002010001', 'SUB20161002000001', 3000.00, 'gasto especial', 'estra', NULL, 'NULL', NULL, NULL, NULL, NULL, '20161008', '20161008', NULL, '');

-- -----------------------------------------------------
-- Data for table `subcategoria`
-- -----------------------------------------------------
INSERT INTO `subcategoria` (`cod_categoria`, `cod_subcategoria`, `des_subcategoria`, `fecha_subcategoria`, `sessionflag`) VALUES ('CAT20161002010001', 'SUB20161007221219', 'Subcat de CAT1', '20161007', NULL);
INSERT INTO `subcategoria` (`cod_categoria`, `cod_subcategoria`, `des_subcategoria`, `fecha_subcategoria`, `sessionflag`) VALUES ('CAT20161002010002', 'SUB20161007221415', 'Subcat1 de CAT2', '20161007', NULL);
INSERT INTO `subcategoria` (`cod_categoria`, `cod_subcategoria`, `des_subcategoria`, `fecha_subcategoria`, `sessionflag`) VALUES ('CAT20161002010002', 'SUB20161007221439', 'Subcat2 de CAT2', '20161007', NULL);

-- -----------------------------------------------------
-- Data for table `usuarios`
-- -----------------------------------------------------
INSERT INTO `usuarios` (`ficha`, `intranet`, `clave`, `sello`, `nombre`, `detalles`, `estado`, `acc_lectura`, `acc_escribe`, `acc_modifi`, `fecha_ultimavez`, `sessionflag`, `sessionficha`) VALUES ('99999990', 'admin_user', '9990', '', 'Administrador', NULL, 'ACTIVO', '', '', '', '', '20160101', NULL);
INSERT INTO `usuarios` (`ficha`, `intranet`, `clave`, `sello`, `nombre`, `detalles`, `estado`, `acc_lectura`, `acc_escribe`, `acc_modifi`, `fecha_ultimavez`, `sessionflag`, `sessionficha`) VALUES ('12345678', 'usuario1', '123', '01', 'Usuario Apellido', NULL, 'ACTIVO', '', '', '', NULL, '20161007', 'admin_user20161007220657');
INSERT INTO `usuarios` (`ficha`, `intranet`, `clave`, `sello`, `nombre`, `detalles`, `estado`, `acc_lectura`, `acc_escribe`, `acc_modifi`, `fecha_ultimavez`, `sessionflag`, `sessionficha`) VALUES ('12345679', 'usuario2', '123', '02', 'Persona Apellido', NULL, 'ACTIVO', '', '', '', NULL, '20161007', 'admin_user20161007220816');


-- -----------------------------------------------------
-- Data for administracion
-- -----------------------------------------------------
INSERT INTO `gastossystema`.`entidad` (`cod_entidad`, `abr_zona`, `des_entidad`)
VALUES ('1000', 'VE-CAP', 'Administracion systema');
INSERT INTO `gastossystema`.`usuarios` (`ficha`,`intranet`, `clave`, `sello`, `nombre`, `estado`)
VALUES ('99999990', 'adminuser', '9990', '34', 'Admin user', 'ACTIVO');
INSERT INTO `entidad_usuario` (`intranet`, `cod_entidad`, `sessionflag`)
VALUES ('adminuser', '1000', NULL);


COMMIT;
