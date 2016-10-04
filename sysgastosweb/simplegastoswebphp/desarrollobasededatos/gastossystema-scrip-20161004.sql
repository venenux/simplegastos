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
  `fecha_categoria` VARCHAR(40) NULL COMMENT 'innecesario, por conpatibilidad' ,
  `sessionflag` VARCHAR(40) NULL COMMENT 'esto es quien_registro YYYYMMDDhhmmss + cod_sucursal + . + ficha' ,
  PRIMARY KEY (`cod_categoria`) )
COMMENT = 'los titulos de la matrix, el tipo general de gasto';


-- -----------------------------------------------------
-- Table `entidad`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `entidad` ;

CREATE  TABLE IF NOT EXISTS `entidad` (
  `cod_entidad` VARCHAR(40) NOT NULL COMMENT 'sello de la sucursal o entidad' ,
  `abr_entidad` VARCHAR(40) NOT NULL COMMENT 'abrebiacion de esta sucursal' ,
  `abr_zona` VARCHAR(40) NOT NULL COMMENT 'siglas de la zona de la sucursal' ,
  `des_entidad` VARCHAR(400) NOT NULL COMMENT 'descripcion sucursal' ,
  `status` VARCHAR(40) NOT NULL COMMENT 'ACTIVA|CERRADA|SUSPENDIDA|ESPECIAL' ,
  `codger` VARCHAR(40) NOT NULL COMMENT 'necesario porque deps lo manejan como centro de costos' ,
  `sessionflag` VARCHAR(40) NULL ,
  PRIMARY KEY (`cod_entidad`) )
COMMENT = 'las entidades que se le adjudican gastos';


-- -----------------------------------------------------
-- Table `log`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `log` ;

CREATE  TABLE IF NOT EXISTS `log` (
  `cod_log` VARCHAR(40) NOT NULL COMMENT 'yyyymmddhhmmss' ,
  `sessionflag` VARCHAR(40) NULL COMMENT 'esto es quien_registro YYYYMMDDhhmmss + cod_sucursal + . + ficha' ,
  `operacion` VARCHAR(4000) NULL COMMENT 'en que modulo controlador y que realizo... y que tablas afecto' ,
  PRIMARY KEY (`cod_log`) )
COMMENT = 'tabla de chismoso, cada operacion se graba aqui';


-- -----------------------------------------------------
-- Table `registro_adjunto`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `registro_adjunto` ;

CREATE  TABLE IF NOT EXISTS `registro_adjunto` (
  `cod_adjunto` VARCHAR(40) NOT NULL COMMENT 'YYYYMMDDhhmmss' ,
  `cod_registro` VARCHAR(40) NOT NULL COMMENT 'a cual registro de gasto le pertenece este adjunto' ,
  `hex_adjunto` VARCHAR(20000) NULL COMMENT 'la subida en base 64 del adjunto' ,
  `nam_adjunto` VARCHAR(40) NULL COMMENT 'nombre del archivo despues cargarlo al sistema' ,
  `nam_archivo` VARCHAR(400) NULL COMMENT 'nombre del archivo antes de cargarlo al sistema' ,
  `ruta_adjunto` VARCHAR(400) NULL COMMENT 'ruta en el servidor para descargar opcional' ,
  `fecha_adjunto` VARCHAR(40) NULL COMMENT 'cuando se altero este adjunto' ,
  `sessionflag` VARCHAR(40) NULL COMMENT 'esto es quien_registro YYYYMMDDhhmmss + cod_sucursal + . + ficha' ,
  PRIMARY KEY (`cod_adjunto`) )
COMMENT = 'escaneados de los registro o gasto adjudicado';


-- -----------------------------------------------------
-- Table `registro_gastos`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `registro_gastos` ;

CREATE  TABLE IF NOT EXISTS `registro_gastos` (
  `cod_registro` VARCHAR(40) NOT NULL COMMENT 'usa fecha YYYYMMDDhhmmss era id_unico_autogenerado' ,
  `cod_sucursal` VARCHAR(40) NOT NULL COMMENT 'sello de la entidad al cual se le adjudica' ,
  `cod_categoria` VARCHAR(40) NULL COMMENT 'por compatibilidad no es necesario SUBSTRING(cod_subcategoria,1,14)' ,
  `cod_subcategoria` VARCHAR(40) NOT NULL COMMENT 'cual subcategoria no puede faltar' ,
  `des_registro` TEXT NOT NULL COMMENT 'cual fue el gasto esto era descripcion_gasto' ,
  `mon_registro` DECIMAL(20,4) NOT NULL COMMENT 'cuanto se gasto, com algunos decimales' ,
  `num_factura` VARCHAR(40) NULL COMMENT 'mumero de factura opcinal' ,
  `estado` VARCHAR(40) NULL COMMENT 'APROBADO|RECHAZADO|PROCESADO|INVALIDO' ,
  `fecha_registro` VARCHAR(40) NULL COMMENT 'YYYYMMDD innecesario, se deja por compatibilidad' ,
  `fecha_factura` VARCHAR(40) NULL COMMENT 'YYYYMMDD de la factura si tiene' ,
  `sessionflag` VARCHAR(40) NULL COMMENT 'esto es quien_registro YYYYMMDDhhmmss + cod_sucursal + . + ficha' ,
  PRIMARY KEY (`cod_registro`) )
COMMENT = 'descripcion y monto de gastos o el detalle';


-- -----------------------------------------------------
-- Table `subcategoria`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `subcategoria` ;

CREATE  TABLE IF NOT EXISTS `subcategoria` (
  `cod_categoria` VARCHAR(40) NOT NULL COMMENT 'CATYYYYMMDDhhmmss tabla categoria' ,
  `cod_subcategoria` VARCHAR(40) NOT NULL COMMENT 'SUBYYYYMMDDhhmmss' ,
  `des_subcategoria` VARCHAR(400) NOT NULL COMMENT 'que tipo de gasto en la categoria' ,
  `fecha_subcategoria` VARCHAR(40) NULL COMMENT 'innecesario, por compatibilidad' ,
  `sessionflag` VARCHAR(40) NULL COMMENT 'esto es quien_registro YYYYMMDDhhmmss + cod_sucursal + . + ficha' ,
  PRIMARY KEY (`cod_subcategoria`) )
COMMENT = 'en que renglon cargan los gastos';


-- -----------------------------------------------------
-- Table `entidad_usuario`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `entidad_usuario` ;

CREATE  TABLE IF NOT EXISTS `entidad_usuario` (
  `ficha` VARCHAR(40) NOT NULL COMMENT 'ficha del usuario o id del usuario' ,
  `cod_entidad` VARCHAR(40) NOT NULL COMMENT 'sello al cual esta asociado' ,
  PRIMARY KEY (`ficha`, `cod_entidad`) )
COMMENT = 'relacion usuario y que sucursal adjudica gastos';


-- -----------------------------------------------------
-- Table `usuarios`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `usuarios` ;

CREATE  TABLE IF NOT EXISTS `usuarios` (
  `ficha` VARCHAR(40) NOT NULL COMMENT 'cod_usuario, cedula en vnzla' ,
  `intranet` VARCHAR(40) NOT NULL COMMENT 'login del usuario, id del correo' ,
  `clave` VARCHAR(40) NOT NULL ,
  `codger` VARCHAR(40) NULL COMMENT 'necesario porque deps lo manejan como centro de costos' ,
  `nombre` VARCHAR(400) NULL DEFAULT NULL COMMENT 'nombre y apellido' ,
  `estado` VARCHAR(40) NULL DEFAULT NULL COMMENT 'ACTIVO INACTIVO SUSPENDIDO INVALIDO' ,
  `acc_lectura` VARCHAR(4000) NOT NULL COMMENT 'modulos o pagina controlador que puede leer separados por barra' ,
  `acc_escribe` VARCHAR(4000) NOT NULL COMMENT 'modulos o nombre controlador que puede crear registros separados por barra' ,
  `acc_modifi` VARCHAR(4000) NOT NULL COMMENT 'modulos o nombre controlador que puede alterar separados por barra' ,
  `fecha_ultimavez` VARCHAR(40) NULL COMMENT 'cuando fue la ultima vez que entro sesion' ,
  `fecha_ficha` VARCHAR(40) NULL ,
  `sessionflag` VARCHAR(40) NULL COMMENT 'esto es quien_registro YYYYMMDDhhmmss + cod_sucursal + . + ficha' ,
  PRIMARY KEY (`ficha`) )
COMMENT = 'tabla de usuarios';

USE `gastossystema` ;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
