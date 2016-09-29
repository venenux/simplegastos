SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';


-- -----------------------------------------------------
-- Table `registro_gastos`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `gastossystema`.`registro_gastos` ;

CREATE  TABLE IF NOT EXISTS `gastossystema`.`registro_gastos` (
  `cod_registro` TEXT NOT NULL COMMENT 'usa fecha YYYYMMDDhhmmss era id_unico_autogenerado' ,
  `cod_sucursal` VARCHAR(40) NULL COMMENT 'sello de la entidad al cual se le adjudica' ,
  `cod_categoria` VARCHAR(40) NULL COMMENT 'a que categoria de gasto' ,
  `cod_subcategoria` VARCHAR(40) NULL COMMENT 'cual subcategoria' ,
  `des_registro` VARCHAR(40) NOT NULL COMMENT 'cual fue el gasto esto era descripcion_gasto' ,
  `mon_registro` DECIMAL NOT NULL COMMENT 'cuanto se gasto' ,
  `fecha_registro` VARCHAR(40) NULL COMMENT 'YYYYMMDD innecesario, se deja por compatibilidad' ,
  `fecha_factura` VARCHAR(40) NULL COMMENT 'YYYYMMDD de la factura si tiene' ,
  `sessionflag` VARCHAR(40) NULL COMMENT 'esto es quien_registro YYYYMMDDhhmmss + cod_sucursal + . + ficha' ,
  `estado` VARCHAR(40) NULL ,
  `num_factura` VARCHAR(40) NULL COMMENT 'mumero de factura opcinal' )
COMMENT = 'descripcion y monto de gastos (el detalle)';


-- -----------------------------------------------------
-- Table `categoria`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `gastossystema`.`categoria` ;

CREATE  TABLE IF NOT EXISTS `gastossystema`.`categoria` (
  `cod_categoria` TEXT NOT NULL COMMENT 'YYYYMMDDhhmmss' ,
  `des_categoria` VARCHAR(40) NULL COMMENT 'descripcion_categoria' ,
  `fecha_categoria` VARCHAR(40) NULL COMMENT 'innecesario, por conpatibilidad' ,
  `sessionflag` VARCHAR(40) NULL COMMENT 'esto es quien_registro YYYYMMDDhhmmss + cod_sucursal + . + ficha' )
COMMENT = 'los titulos de la matrix, el tipo general de gasto';


-- -----------------------------------------------------
-- Table `subcategoria`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `gastossystema`.`subcategoria` ;

CREATE  TABLE IF NOT EXISTS `gastossystema`.`subcategoria` (
  `cod_subcategoria` VARCHAR(40) NOT NULL COMMENT 'YYYYMMDDhhmmss' ,
  `des_subcategoria` VARCHAR(40) NULL COMMENT 'que tipo de gasto en la categoria' ,
  `fecha_subcategoria` VARCHAR(40) NULL COMMENT 'innecesario, por compatibilidad' ,
  `sessionflag` VARCHAR(40) NULL COMMENT 'esto es quien_registro YYYYMMDDhhmmss + cod_sucursal + . + ficha'
);


-- -----------------------------------------------------
-- Table `registro_adjunto`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `gastossystema`.`registro_adjunto` ;

CREATE  TABLE IF NOT EXISTS `gastossystema`.`registro_adjunto` (
  `cod_adjunto` TEXT NOT NULL COMMENT 'YYYYMMDDhhmmss' ,
  `cod_registro` VARCHAR(40) NULL COMMENT 'a cual registro de gasto le pertenece este adjunto' ,
  `hex_adjunto` VARCHAR(40000) NULL COMMENT 'la subida en base 64 del adjunto' ,
  `ruta_adjunto` VARCHAR(40) NULL COMMENT 'ruta opcinal del archivo si esta en el sistema de ficheros' ,
  `fecha_adjunto` VARCHAR(40) NULL COMMENT 'cuando se altero este adjunto' ,
  `sessionflag` VARCHAR(40) NULL COMMENT 'esto es quien_registro YYYYMMDDhhmmss + cod_sucursal + . + ficha' ),
  `nam_adjunto` VARCHAR(40) NULL COMMENT 'nombre del archivo despues cargarlo al sistema',
  `nam_archivo` VARCHAR(40) NULL COMMENT 'nombre del archivo antes de cargarlo al sistema'
COMMENT = 'escaneados de los registro o gasto adjudicado';


-- -----------------------------------------------------
-- Table `usuarios`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `gastossystema`.`usuarios` ;

CREATE  TABLE IF NOT EXISTS `gastossystema`.`usuarios` (
  `ficha` VARCHAR(40) NOT NULL COMMENT 'cod_usuario, cedula en vnzla' ,
  `intranet` VARCHAR(40) NULL COMMENT 'login del usuario, id del correo' ,
  `clave` VARCHAR(40) NULL ,
  `codger` VARCHAR(40) NOT NULL COMMENT 'necesario porque deps lo manejan como centro de costos' ,
  `nombre` VARCHAR(40) NULL COMMENT 'nombre y apellido' ,
  `estado` VARCHAR(40) NULL COMMENT 'ACTIVO INACTIVO SUSPENDIDO INVALIDO' ,
  `sessionflag` VARCHAR(40) NULL COMMENT 'esto es quien_registro YYYYMMDDhhmmss + cod_sucursal + . + ficha' ,
  `acc_lectura` VARCHAR(40) NULL COMMENT 'modulos o pagina controlador que puede leer separados por barra' ,
  `acc_escribe` VARCHAR(40) NULL COMMENT 'modulos o nombre controlador que puede crear registros separados por barra' ,
  `acc_modifi` VARCHAR(40) NULL COMMENT 'modulos o nombre controlador que puede alterar separados por barra' ,
  `fecha_ficha` VARCHAR(40) NULL ,
  `fecha_ultimavez` VARCHAR(40) NULL COMMENT 'cuando fue la ultima vez que entro sesion' )
COMMENT = 'tabla de usuarios';


-- -----------------------------------------------------
-- Table `log`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `gastossystema`.`log` ;

CREATE  TABLE IF NOT EXISTS `gastossystema`.`log` (
  `cod_log` TEXT NOT NULL COMMENT 'yyyymmddhhmmss' ,
  `sessionflag` VARCHAR(40) NULL COMMENT 'esto es quien_registro YYYYMMDDhhmmss + cod_sucursal + . + ficha' ,
  `operacion` VARCHAR(40) NULL COMMENT 'en que modulo controlador y que realizo... y que tablas afecto' )
COMMENT = 'tabla de chismoso, cada operacion se graba aqui';


-- -----------------------------------------------------
-- Table `sucursal_usuario`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `gastossystema`.`sucursal_usuario` ;

CREATE  TABLE IF NOT EXISTS `gastossystema`.`sucursal_usuario` (
  `cod_usuario` VARCHAR(40) NOT NULL COMMENT 'login intranet de la tabla usuario' ,
  `cod_sucursal` VARCHAR(40) NOT NULL COMMENT 'sello al cual esta asociado' )
COMMENT = 'relacion usuario y que sucursal adjudica gastos';


-- -----------------------------------------------------
-- Table `entidad`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `gastossystema`.`entidad` ;

CREATE  TABLE IF NOT EXISTS `gastossystema`.`entidad` (
  `cod_entidad` VARCHAR(40) NOT NULL COMMENT 'sello de la sucursal o entidad' ,
  `abr_entidad` VARCHAR(40) NOT NULL COMMENT 'abrebiacion de esta sucursal' ,
  `abr_zona` VARCHAR(40) NULL COMMENT 'siglas de la zona de la sucursal' ,
  `des_entidad` VARCHAR(40) NULL COMMENT 'descripcion sucursal' ,
  `status` VARCHAR(40) NULL COMMENT 'ACTIVA|CERRADA|SUSPENDIDA|ESPECIAL' ,
  `codger` VARCHAR(40) NOT NULL COMMENT 'necesario porque deps lo manejan como centro de costos' )
COMMENT = 'las entidades que se le adjudican gastos';



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
