SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

DROP SCHEMA IF EXISTS `gastossystema` ;
CREATE SCHEMA IF NOT EXISTS `gastossystema` DEFAULT CHARACTER SET utf8 ;
USE `gastossystema` ;

-- -----------------------------------------------------
-- Table `categoria`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `categoria` ;

CREATE  TABLE IF NOT EXISTS `categoria` (
  `cod_categoria` VARCHAR(40) NOT NULL COMMENT 'CATYYYYMMDDhhmmss' ,
  `des_categoria` VARCHAR(400) NOT NULL COMMENT 'descripcion_categoria' ,
  `fecha_categoria` VARCHAR(40) NULL DEFAULT NULL COMMENT 'innecesario, por conpatibilidad' ,
  `sessionflag` VARCHAR(40) NULL DEFAULT NULL COMMENT 'esto es quien_registro YYYYMMDDhhmmss + codger + . + ficha' ,
  PRIMARY KEY (`cod_categoria`) )
COMMENT = 'los titulos de la matrix, el tipo general de gasto';


-- -----------------------------------------------------
-- Table `entidad`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `entidad` ;

CREATE  TABLE IF NOT EXISTS `entidad` (
  `cod_entidad` VARCHAR(40) NOT NULL COMMENT 'codger de la sucursal o id en nomina' ,
  `abr_entidad` VARCHAR(40) NOT NULL COMMENT 'abrebiacion de esta sucursal' ,
  `abr_zona` VARCHAR(40) NOT NULL COMMENT 'siglas de la zona de la sucursal' ,
  `des_entidad` VARCHAR(400) NOT NULL COMMENT 'descripcion sucursal' ,
  `status` VARCHAR(40) NOT NULL COMMENT 'ACTIVA|CERRADA|SUSPENDIDA|ESPECIAL' ,
  `cod_fondo` VARCHAR(40) NULL DEFAULT NULL COMMENT 'fondo o monto disponible si aplica' ,
  `sello` VARCHAR(40) NULL DEFAULT NULL COMMENT 'solo las ubicaciones usan selllos, departamentos usan codger' ,
  `sessionflag` VARCHAR(40) NULL DEFAULT NULL COMMENT 'esto es quien lo hizo yyyymmddhhmmss+godger+.+ficha' ,
  PRIMARY KEY (`cod_entidad`) )
COMMENT = 'las entidades que se le adjudican gastos';


-- -----------------------------------------------------
-- Table `entidad_usuario`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `entidad_usuario` ;

CREATE  TABLE IF NOT EXISTS `entidad_usuario` (
  `intranet` VARCHAR(40) NOT NULL COMMENT 'usuario relacionado' ,
  `cod_entidad` VARCHAR(40) NOT NULL COMMENT 'codger al cual esta asociado' ,
  `sessionflag` VARCHAR(40) NULL DEFAULT NULL ,
  PRIMARY KEY (`intranet`, `cod_entidad`) )
COMMENT = 'relacion usuario y que sucursal adjudica gastos';


-- -----------------------------------------------------
-- Table `fondo`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fondo` ;

CREATE  TABLE IF NOT EXISTS `fondo` (
  `cod_fondo` VARCHAR(40) NOT NULL COMMENT 'identificador del fondo' ,
  `fecha_fondo` VARCHAR(40) NOT NULL COMMENT 'cada nuevo deposito es por fecha' ,
  `mon_fondo` VARCHAR(40) NOT NULL COMMENT 'monto adjudicado a la fecha' ,
  `sessionflag` VARCHAR(40) NULL DEFAULT NULL COMMENT 'si alguien lo modifico a mano' ,
  `sessionficha` VARCHAR(40) NULL COMMENT 'quien le adjudico el fondo' ,
  PRIMARY KEY (`cod_fondo`, `fecha_fondo`) )
COMMENT = 'fondos para quienes manejen su propio fondo';


-- -----------------------------------------------------
-- Table `log`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `log` ;

CREATE  TABLE IF NOT EXISTS `log` (
  `cod_log` VARCHAR(40) NOT NULL COMMENT 'yyyymmddhhmmss' ,
  `sessionficha` VARCHAR(40) NULL COMMENT 'esto es quien_registro YYYYMMDDhhmmss + codger + . + ficha' ,
  `operacion` VARCHAR(20000) NULL DEFAULT NULL COMMENT 'en que modulo controlador y que realizo... y que tablas afecto' ,
  PRIMARY KEY (`cod_log`) )
COMMENT = 'tabla de chismoso, cada operacion se graba aqui';


-- -----------------------------------------------------
-- Table `registro_adjuntos`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `registro_adjuntos` ;

CREATE  TABLE IF NOT EXISTS `registro_adjuntos` (
  `cod_registro` VARCHAR(40) NOT NULL COMMENT 'nombre del archivo despues cargarlo al sistema' ,
  `cod_facturas` VARCHAR(40) NOT NULL COMMENT 'a cual registro de gasto le pertenece este adjunto' ,
  `cod_adjuntos` VARCHAR(40) NOT NULL COMMENT 'YYYYMMDDhhmmss' ,
  `bin_adjunto` BLOB NOT NULL ,
  `hex_adjunto` VARCHAR(20000) NULL DEFAULT NULL COMMENT 'la subida en base 64 del adjunto' ,
  `original` VARCHAR(400) NULL DEFAULT NULL COMMENT 'nombre del archivo antes de cargarlo al sistema' ,
  `ruta` VARCHAR(400) NOT NULL COMMENT 'ruta en el servidor para descargar opcional' ,
  `sessionflag` VARCHAR(40) NULL DEFAULT NULL COMMENT 'quien modifico YYYYMMDDhhmmss + codger + . + ficha' ,
  `sessionficha` VARCHAR(40) NULL DEFAULT NULL COMMENT 'quien lo creo YYYYMMDDhhmmss + codger + . + ficha' ,
  PRIMARY KEY (`cod_facturas`, `cod_adjuntos`) )
COMMENT = 'escaneados de los registro o gasto adjudicado';


-- -----------------------------------------------------
-- Table `registro_gastos`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `registro_gastos` ;

CREATE  TABLE IF NOT EXISTS `registro_gastos` (
  `cod_fondo` VARCHAR(40) NOT NULL COMMENT 'el fonde de la entidad que se le adjudica gasto' ,
  `cod_registro` VARCHAR(40) NOT NULL COMMENT 'GASYYYYMMDDhhmmss usa fecha y hora' ,
  `cod_entidad` VARCHAR(40) NOT NULL COMMENT 'codger de la entidad al cual se le adjudica' ,
  `cod_categoria` VARCHAR(40) NOT NULL COMMENT 'categoria del gasto' ,
  `cod_subcategoria` VARCHAR(40) NOT NULL COMMENT 'cual subcategoria no puede faltar' ,
  `mon_registro` DECIMAL(20,2) NOT NULL COMMENT 'monto de cuanto se gasto' ,
  `des_concepto` VARCHAR(400) NOT NULL COMMENT 'descripcion del gasto' ,
  `des_registro` VARCHAR(10000) NULL DEFAULT NULL COMMENT 'detalle opcional del gasto' ,
  `estado` VARCHAR(40) NULL DEFAULT NULL COMMENT 'APROBADO|RECHAZADO|PROCESADO|INVALIDO' ,
  `bin_factura1` BLOB NULL DEFAULT NULL COMMENT 'factura por defecto si la sube' ,
  `num_factura1` VARCHAR(40) NULL DEFAULT NULL COMMENT 'mumero de factura opcinal' ,
  `fecha_factura1` VARCHAR(40) NULL DEFAULT NULL COMMENT 'YYYYMMDD de la factura si tiene' ,
  `fecha_registro` VARCHAR(40) NOT NULL COMMENT 'para compatibilidad busquedas' ,
  `cod_facturas` VARCHAR(40) NULL COMMENT 'asociacion 1-n de otras facturas' ,
  `cod_adjuntos` VARCHAR(40) NULL COMMENT 'asociacion 1-n de otras facturas' ,
  `sessionflag` VARCHAR(40) NULL DEFAULT NULL COMMENT 'quien modifico YYYYMMDDhhmmss + codger + . + ficha' ,
  `sessionficha` VARCHAR(40) NULL COMMENT 'quien lo creo YYYYMMDDhhmmss + codger + . + ficha' ,
  PRIMARY KEY (`cod_registro`) )
COMMENT = 'descripcion y monto gastos o concepto';


-- -----------------------------------------------------
-- Table `subcategoria`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `subcategoria` ;

CREATE  TABLE IF NOT EXISTS `subcategoria` (
  `cod_categoria` VARCHAR(40) NOT NULL COMMENT 'CATYYYYMMDDhhmmss tabla categoria' ,
  `cod_subcategoria` VARCHAR(40) NOT NULL COMMENT 'SUBYYYYMMDDhhmmss' ,
  `des_subcategoria` VARCHAR(400) NOT NULL COMMENT 'que tipo de gasto en la categoria' ,
  `fecha_subcategoria` VARCHAR(40) NULL DEFAULT NULL COMMENT 'innecesario, por compatibilidad' ,
  `sessionflag` VARCHAR(40) NULL DEFAULT NULL COMMENT 'esto es quien_registro YYYYMMDDhhmmss + codger + . + ficha' ,
  PRIMARY KEY (`cod_subcategoria`) )
COMMENT = 'en que renglon cargan los gastos';


-- -----------------------------------------------------
-- Table `usuarios`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `usuarios` ;

CREATE  TABLE IF NOT EXISTS `usuarios` (
  `ficha` VARCHAR(40) NOT NULL COMMENT 'cod_usuario, cedula en vnzla' ,
  `intranet` VARCHAR(40) NOT NULL COMMENT 'login del usuario, id del correo' ,
  `clave` VARCHAR(40) NOT NULL ,
  `sello` VARCHAR(40) NOT NULL COMMENT 'OJO: este solo es para saber si es de tienda o administrativo, no es la pertenencia' ,
  `nombre` VARCHAR(400) NULL DEFAULT NULL COMMENT 'nombre y apellido' ,
  `cod_fondo` VARCHAR(40) NULL DEFAULT NULL COMMENT 'fondo o monto asociado si aplica' ,
  `estado` VARCHAR(40) NOT NULL COMMENT 'ACTIVO INACTIVO SUSPENDIDO INVALIDO' ,
  `acc_lectura` VARCHAR(4000) NOT NULL COMMENT 'modulos o pagina controlador que puede leer separados por barra' ,
  `acc_escribe` VARCHAR(4000) NOT NULL COMMENT 'modulos o nombre controlador que puede crear registros separados por barra' ,
  `acc_modifi` VARCHAR(4000) NOT NULL COMMENT 'modulos o nombre controlador que puede alterar separados por barra' ,
  `fecha_ultimavez` VARCHAR(40) NULL DEFAULT NULL COMMENT 'cuando fue la ultima vez que entro sesion' ,
  `sessionflag` VARCHAR(40) NULL DEFAULT NULL COMMENT 'quien modifico YYYYMMDDhhmmss + codger + . + ficha' ,
  `sessionficha` VARCHAR(40) NULL COMMENT 'quien lo creo YYYYMMDDhhmmss + codger + . + ficha' ,
  PRIMARY KEY (`intranet`) )
COMMENT = 'tabla de usuarios';


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;


START TRANSACTION;
USE `gastossystema`;


-- -----------------------------------------------------
-- Data for table `categoria`
-- -----------------------------------------------------
INSERT INTO `categoria` (`cod_categoria`, `des_categoria`, `fecha_categoria`, `sessionflag`) VALUES ('CAT20161002000000', 'Especial', '20161003', 'NULL');
INSERT INTO `categoria` (`cod_categoria`, `des_categoria`, `fecha_categoria`, `sessionflag`) VALUES ('CAT20161002010001', 'CATE 1', '20161003', 'admin_user20161007221320');
INSERT INTO `categoria` (`cod_categoria`, `des_categoria`, `fecha_categoria`, `sessionflag`) VALUES ('CAT20161002010002', 'CATE 2', '20161007', 'NULL');

-- -----------------------------------------------------
-- Data for table `entidad`
-- -----------------------------------------------------
INSERT INTO `entidad` (`cod_entidad`, `abr_entidad`, `abr_zona`, `des_entidad`, `status`, `cod_fondo`, `sello`, `sessionflag`) VALUES ('000', 'sys', 'TODAS', 'departamento de systemas', 'ACTIVO', '20160101', '999', 'NULL');
INSERT INTO `entidad` (`cod_entidad`, `abr_entidad`, `abr_zona`, `des_entidad`, `status`, `cod_fondo`, `sello`, `sessionflag`) VALUES ('001', 'SUC1', 'CAPITAL', 'Sucursal 1', 'ACTIVO', 'NULL', 'NULL', 'NULL');
INSERT INTO `entidad` (`cod_entidad`, `abr_entidad`, `abr_zona`, `des_entidad`, `status`, `cod_fondo`, `sello`, `sessionflag`) VALUES ('002', 'SUC2', 'ZON-CEN', 'Sucursal 2', 'ACTIVO', 'NULL', 'NULL', 'NULL');
INSERT INTO `entidad` (`cod_entidad`, `abr_entidad`, `abr_zona`, `des_entidad`, `status`, `cod_fondo`, `sello`, `sessionflag`) VALUES ('003', 'SUC3', 'ZON-CEN', 'Sucursal 3', 'ACTIVO', 'FON003', '03', 'NULL');

-- -----------------------------------------------------
-- Data for table `entidad_usuario`
-- -----------------------------------------------------
INSERT INTO `entidad_usuario` (`intranet`, `cod_entidad`, `sessionflag`) VALUES ('usuario1', '001', 'NULL');
INSERT INTO `entidad_usuario` (`intranet`, `cod_entidad`, `sessionflag`) VALUES ('usuario2', '002', 'NULL');

-- -----------------------------------------------------
-- Data for table `registro_gastos`
-- -----------------------------------------------------
INSERT INTO `registro_gastos` (`cod_registro`, `cod_entidad`, `cod_categoria`, `cod_subcategoria`, `mon_registro`, `des_concepto`, `des_registro`, `estado`, `bin_factura1`, `num_factura1`, `fecha_factura1`, `fecha_registro`, `cod_facturas`, `cod_adjuntos`, `sessionflag`, `sessionficha`) VALUES ('GAS20161007221632', '001', 'CAT20161002010001', 'SUB20161007221219', 1000.50, 'gastode suc 1 en cate 1 sub 1', 'datos extras', 'NULL', 0x4E554C4C, 'NULL', '20161007', '20161007', '', '', 'admin_user20161007221650', '');
INSERT INTO `registro_gastos` (`cod_registro`, `cod_entidad`, `cod_categoria`, `cod_subcategoria`, `mon_registro`, `des_concepto`, `des_registro`, `estado`, `bin_factura1`, `num_factura1`, `fecha_factura1`, `fecha_registro`, `cod_facturas`, `cod_adjuntos`, `sessionflag`, `sessionficha`) VALUES ('GAS20161007221932', '001', 'CAT20161002010002', 'SUB20161007221439', 1000.50, 'gastp de suc 1 cate 2 sub2', 'otros 2', 'NULL', 0x4E554C4C, 'NULL', '20161007', '20161007', '', '', 'NULL', '');
INSERT INTO `registro_gastos` (`cod_registro`, `cod_entidad`, `cod_categoria`, `cod_subcategoria`, `mon_registro`, `des_concepto`, `des_registro`, `estado`, `bin_factura1`, `num_factura1`, `fecha_factura1`, `fecha_registro`, `cod_facturas`, `cod_adjuntos`, `sessionflag`, `sessionficha`) VALUES ('GAS20161007222129', '001', 'CAT20161002010002', 'SUB20161007221415', 1000.50, 'gastp de suc 1 cate 2 sub 1', 'otros 3', 'NULL', 0x4E554C4C, 'NULL', '20161007', '20161007', '', '', 'admin_user20161007222221', '');
INSERT INTO `registro_gastos` (`cod_registro`, `cod_entidad`, `cod_categoria`, `cod_subcategoria`, `mon_registro`, `des_concepto`, `des_registro`, `estado`, `bin_factura1`, `num_factura1`, `fecha_factura1`, `fecha_registro`, `cod_facturas`, `cod_adjuntos`, `sessionflag`, `sessionficha`) VALUES ('GAS20161007222327', '002', 'CAT20161002010002', 'SUB20161007221415', 1000.50, 'gasto de sucs 2 en cat 2 sub 1', 'otros', 'NULL', 0x4E554C4C, 'NULL', '20161007', '20161007', '', '', 'NULL', '');
INSERT INTO `registro_gastos` (`cod_registro`, `cod_entidad`, `cod_categoria`, `cod_subcategoria`, `mon_registro`, `des_concepto`, `des_registro`, `estado`, `bin_factura1`, `num_factura1`, `fecha_factura1`, `fecha_registro`, `cod_facturas`, `cod_adjuntos`, `sessionflag`, `sessionficha`) VALUES ('GAS20161007222414', '002', 'CAT20161002010001', 'SUB20161007221219', 2000.50, 'gasto de suc 2 cat 1 sub 1', 'ninguno', 'NULL', 0x4E554C4C, 'NULL', '20161007', '20161007', '', '', 'admin_user20161007222428', '');
INSERT INTO `registro_gastos` (`cod_registro`, `cod_entidad`, `cod_categoria`, `cod_subcategoria`, `mon_registro`, `des_concepto`, `des_registro`, `estado`, `bin_factura1`, `num_factura1`, `fecha_factura1`, `fecha_registro`, `cod_facturas`, `cod_adjuntos`, `sessionflag`, `sessionficha`) VALUES ('GAS20161007222736', '003', 'CAT20161002010001', 'SUB20161007221219', 1000.50, 'gasto de suc 3 cat 1 sub 1', 'otros 5', 'NULL', 0x4E554C4C, 'NULL', '20161007', '20161007', '', '', 'admin_user20161007222756', '');
INSERT INTO `registro_gastos` (`cod_registro`, `cod_entidad`, `cod_categoria`, `cod_subcategoria`, `mon_registro`, `des_concepto`, `des_registro`, `estado`, `bin_factura1`, `num_factura1`, `fecha_factura1`, `fecha_registro`, `cod_facturas`, `cod_adjuntos`, `sessionflag`, `sessionficha`) VALUES ('GAS20161007222850', '003', 'CAT20161002010002', 'SUB20161007221415', 2000.50, 'gastos de suc 3 en cat 2 sub 1', 'est 1', 'NULL', 0x4E554C4C, 'NULL', '20161007', '20161007', '', '', 'NULL', '');
INSERT INTO `registro_gastos` (`cod_registro`, `cod_entidad`, `cod_categoria`, `cod_subcategoria`, `mon_registro`, `des_concepto`, `des_registro`, `estado`, `bin_factura1`, `num_factura1`, `fecha_factura1`, `fecha_registro`, `cod_facturas`, `cod_adjuntos`, `sessionflag`, `sessionficha`) VALUES ('GAS20161007222941', '003', 'CAT20161002010002', 'SUB20161007221439', 1000.50, 'gastos de suc 3 en cat 2 sub 2', 'esta', 'NULL', 0x4E554C4C, 'NULL', '20161007', '20161007', '', '', 'NULL', '');
INSERT INTO `registro_gastos` (`cod_registro`, `cod_entidad`, `cod_categoria`, `cod_subcategoria`, `mon_registro`, `des_concepto`, `des_registro`, `estado`, `bin_factura1`, `num_factura1`, `fecha_factura1`, `fecha_registro`, `cod_facturas`, `cod_adjuntos`, `sessionflag`, `sessionficha`) VALUES ('GAS20161008005532', '000', 'CAT20161002010001', 'SUB20161002000001', 3000.00, 'gasto especial', 'estra', 'NULL', 0x2E2E2E, 'NULL', '20161008', '20161008', '', '', 'NULL', '');

-- -----------------------------------------------------
-- Data for table `subcategoria`
-- -----------------------------------------------------
INSERT INTO `subcategoria` (`cod_categoria`, `cod_subcategoria`, `des_subcategoria`, `fecha_subcategoria`, `sessionflag`) VALUES ('CAT20161002010001', 'SUB20161007221219', 'Subcat de CAT1', '20161007221219', 'NULL');
INSERT INTO `subcategoria` (`cod_categoria`, `cod_subcategoria`, `des_subcategoria`, `fecha_subcategoria`, `sessionflag`) VALUES ('CAT20161002010002', 'SUB20161007221415', 'Subcat de CAT2', '20161007221415', 'NULL');
INSERT INTO `subcategoria` (`cod_categoria`, `cod_subcategoria`, `des_subcategoria`, `fecha_subcategoria`, `sessionflag`) VALUES ('CAT20161002010002', 'SUB20161007221439', 'Subcat2 de CAT2', '20161007221439', 'NULL');

-- -----------------------------------------------------
-- Data for table `usuarios`
-- -----------------------------------------------------
INSERT INTO `usuarios`
(`ficha`, `intranet`, `clave`, `sello`, `nombre`, `cod_fondo`, `estado`, `acc_lectura`, `acc_escribe`, `acc_modifi`, `fecha_ultimavez`, `sessionflag`, `sessionficha`)
VALUES
('99999990', 'admin_user', '9990', '', 'Administrador', NULL, 'ACTIVO', '', '', '', '', '20160101', 'admin_user20161007220434'),
('10100100', 'usuario1', '', '01', 'Usuario Apellido', 'FON20160101010105', 'ACTIVO', '', '', '', 'NULL', '20161007', 'admin_user20161007220657'),
('11100100', 'usuario2', '', '02', 'Persona Apellido', NULL, 'ACTIVO', '', '', '', 'NULL', '20161007', 'admin_user20161007220816');

-- -----------------------------------------------------
-- Data for table `fondo`
-- -----------------------------------------------------
INSERT INTO `gastossystema`.`fondo` (`cod_fondo`, `fecha_fondo`, `mon_fondo`) VALUES ('FON20160101010100', '20160101', '0.0');
INSERT INTO `gastossystema`.`fondo` (`cod_fondo`, `fecha_fondo`, `mon_fondo`) VALUES ('FON20160101010101', '20160101', '10000.00');
INSERT INTO `gastossystema`.`fondo` (`cod_fondo`, `fecha_fondo`, `mon_fondo`) VALUES ('FON20160101010102', '20160101', '5000.00');
INSERT INTO `gastossystema`.`fondo` (`cod_fondo`, `fecha_fondo`, `mon_fondo`) VALUES ('FON20160101010103', '20160101', '10000.00');
INSERT INTO `gastossystema`.`fondo` (`cod_fondo`, `fecha_fondo`, `mon_fondo`) VALUES ('FON20160101010104', '20160101', '15000.00');
INSERT INTO `gastossystema`.`fondo` (`cod_fondo`, `fecha_fondo`, `mon_fondo`) VALUES ('FON20160101010105', '20160101', '20000.50');

COMMIT;

-- -----------------------------------------------------
-- Set fondo asociaciones
-- -----------------------------------------------------

START TRANSACTION;
USE `gastossystema`;

-- ALTER TABLE `gastossystema`.`entidad`
-- ADD CONSTRAINT `cod_fondo_UNIQUE` UNIQUE KEY (`cod_fondo`);

-- ALTER TABLE `gastossystema`.`usuarios`  -- nolopuedo hacer aqui unique, puesto algunas nomanejan
-- ADD CONSTRAINT `cod_fondo_UNIQUE` UNIQUE KEY (`cod_fondo`);

UPDATE `gastossystema`.`entidad` SET `cod_fondo`='FON20160101010101' WHERE `cod_entidad`='001';
UPDATE `gastossystema`.`entidad` SET `cod_fondo`='FON20160101010103' WHERE `cod_entidad`='003';
UPDATE `gastossystema`.`entidad` SET `cod_fondo`='FON20160101010102' WHERE `cod_entidad`='002';
UPDATE `gastossystema`.`entidad` SET `cod_fondo`='FON20160101010100' WHERE `cod_entidad`='000';


COMMIT;

