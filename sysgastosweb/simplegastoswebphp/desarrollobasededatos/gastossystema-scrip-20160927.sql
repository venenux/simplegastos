SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';


-- -----------------------------------------------------
-- Table `registro_gastos`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `registro_gastos` ;

CREATE  TABLE IF NOT EXISTS `registro_gastos` (
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
ENGINE = InnoDB
COMMENT = 'descripcion y monto de gastos (el detalle)';


-- -----------------------------------------------------
-- Table `categoria`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `categoria` ;

CREATE  TABLE IF NOT EXISTS `categoria` (
  `cod_categoria` TEXT NOT NULL COMMENT 'YYYYMMDDhhmmss' ,
  `des_categoria` VARCHAR(40) NULL COMMENT 'descripcion_categoria' ,
  `fecha_categoria` VARCHAR(40) NULL COMMENT 'innecesario, por conpatibilidad' ,
  `sessionflag` VARCHAR(40) NULL COMMENT 'esto es quien_registro YYYYMMDDhhmmss + cod_sucursal + . + ficha' )
ENGINE = InnoDB
COMMENT = 'los titulos de la matrix, el tipo general de gasto';


-- -----------------------------------------------------
-- Table `subcategoria`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `subcategoria` ;

CREATE  TABLE IF NOT EXISTS `subcategoria` (
  `cod_subcategoria` VARCHAR(40) NOT NULL COMMENT 'YYYYMMDDhhmmss' ,
  `des_subcategoria` VARCHAR(40) NULL COMMENT 'que tipo de gasto en la categoria' ,
  `fecha_subcategoria` VARCHAR(40) NULL COMMENT 'innecesario, por compatibilidad' ,
  `sessionflag` VARCHAR(40) NULL COMMENT 'esto es quien_registro YYYYMMDDhhmmss + cod_sucursal + . + ficha' )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `registro_adjunto`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `registro_adjunto` ;

CREATE  TABLE IF NOT EXISTS `registro_adjunto` (
  `cod_adjunto` TEXT NOT NULL COMMENT 'YYYYMMDDhhmmss' ,
  `cod_registro` VARCHAR(40) NULL COMMENT 'a cual registro de gasto le pertenece este adjunto' ,
  `hex_adjunto` VARCHAR(40000) NULL COMMENT 'la subida en base 64 del adjunto' ,
  `ruta_adjunto` VARCHAR(40) NULL COMMENT 'ruta opcinal del archivo si esta en el sistema de ficheros' ,
  `fecha_adjunto` VARCHAR(40) NULL COMMENT 'cuando se altero este adjunto' ,
  `sessionflag` VARCHAR(40) NULL COMMENT 'esto es quien_registro YYYYMMDDhhmmss + cod_sucursal + . + ficha' )
ENGINE = InnoDB
COMMENT = 'escaneados de los registro o gasto adjudicado';


-- -----------------------------------------------------
-- Table `usuarios`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `usuarios` ;

CREATE  TABLE IF NOT EXISTS `usuarios` (
  `ficha` VARCHAR(40) NOT NULL COMMENT 'cod_usuario, cedula en vnzla' ,
  `intranet` VARCHAR(40) NULL COMMENT 'login del usuario, id del correo' ,
  `clave` VARCHAR(40) NULL ,
  `nombre` VARCHAR(40) NULL COMMENT 'nombre y apellido' ,
  `estado` VARCHAR(40) NULL COMMENT 'ACTIVO INACTIVO SUSPENDIDO INVALIDO' ,
  `sessionflag` VARCHAR(40) NULL COMMENT 'esto es quien_registro YYYYMMDDhhmmss + cod_sucursal + . + ficha' ,
  `acc_lectura` VARCHAR(40) NULL COMMENT 'modulos o pagina controlador que puede leer separados por barra' ,
  `acc_escribe` VARCHAR(40) NULL COMMENT 'modulos o nombre controlador que puede crear registros separados por barra' ,
  `acc_modifi` VARCHAR(40) NULL COMMENT 'modulos o nombre controlador que puede alterar separados por barra' ,
  `fecha_ficha` VARCHAR(40) NULL ,
  `fecha_ultimavez` VARCHAR(40) NULL COMMENT 'cuando fue la ultima vez que entro sesion' )
ENGINE = InnoDB
COMMENT = 'tabla de usuarios';


-- -----------------------------------------------------
-- Table `log`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `log` ;

CREATE  TABLE IF NOT EXISTS `log` (
  `cod_log` TEXT NOT NULL COMMENT 'yyyymmddhhmmss' ,
  `sessionflag` VARCHAR(40) NULL COMMENT 'esto es quien_registro YYYYMMDDhhmmss + cod_sucursal + . + ficha' ,
  `operacion` VARCHAR(40) NULL COMMENT 'en que modulo controlador y que realizo... y que tablas afecto' )
ENGINE = InnoDB
COMMENT = 'tabla de chismoso, cada operacion se graba aqui';


-- -----------------------------------------------------
-- Table `sucursal_usuario`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `sucursal_usuario` ;

CREATE  TABLE IF NOT EXISTS `sucursal_usuario` (
  `cod_usuario` VARCHAR(40) NOT NULL COMMENT 'login intranet de la tabla usuario' ,
  `cod_sucursal` VARCHAR(40) NOT NULL COMMENT 'sello al cual esta asociado' )
ENGINE = InnoDB
COMMENT = 'relacion usuario y que sucursal adjudica gastos';


-- -----------------------------------------------------
-- Table `entidad`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `entidad` ;

CREATE  TABLE IF NOT EXISTS `entidad` (
  `cod_entidad` VARCHAR(40) NOT NULL COMMENT 'sello de la sucursal o entidad' ,
  `abr_entidad` VARCHAR(40) NULL COMMENT 'siglas sucursal' ,
  `des_entidad` VARCHAR(40) NULL COMMENT 'descripcion sucursal' )
ENGINE = InnoDB
COMMENT = 'las entidades que se le adjudican gastos';



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
