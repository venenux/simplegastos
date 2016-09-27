SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';


-- -----------------------------------------------------
-- Table `registro_gastos`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `registro_gastos` ;

CREATE  TABLE IF NOT EXISTS `registro_gastos` (
  `cod_registro` TEXT NOT NULL COMMENT 'era id_unico_autogenerado, es generado manual asi YYYYMMDDhhmmss' ,
  `cod_sucursal` VARCHAR(40) NULL COMMENT 'esto es el sello de la tabla sucursal mas abajo',
  `cod_categoria` VARCHAR(40) NULL ,
  `cod_subcategoria` VARCHAR(40) NULL ,
  `des_registro` VARCHAR(40) NOT NULL COMMENT 'esto era descripcion_gasto' ,
  `mon_registro` DECIMAL NOT NULL ,
  `fecha_registro` VARCHAR(40) NULL ,
  `fecha_factura` VARCHAR(40) NULL ,
  `sessionflag` VARCHAR(40) NULL COMMENT 'esto es quien_registro' ,
  `estado` VARCHAR(40) NULL ,
  `num_factura` VARCHAR(40) NULL COMMENT 'mumero de factura opcinal' )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `categoria`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `categoria` ;

CREATE  TABLE IF NOT EXISTS `categoria` (
  `cod_categoria` TEXT NOT NULL ,
  `des_categoria` VARCHAR(40) NULL COMMENT 'descripcion_categoria' ,
  `fecha_categoria` VARCHAR(40) NULL ,
  `sessionflag` VARCHAR(40) NULL )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `subcategoria`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `subcategoria` ;

CREATE  TABLE IF NOT EXISTS `subcategoria` (
  `cod_subcategoria` INT(11) NOT NULL ,
  `des_subcategoria` VARCHAR(40) NULL ,
  `fecha_subcategoria` VARCHAR(40) NULL ,
  `sessionflag` VARCHAR(40) NULL )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `registro_adjunto`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `registro_adjunto` ;

CREATE  TABLE IF NOT EXISTS `registro_adjunto` (
  `cod_adjunto` TEXT NOT NULL ,
  `cod_registro` VARCHAR(40) NULL ,
  `hex_adjunto` VARCHAR(40000) NULL ,
  `fecha_adjunto` VARCHAR(40) NULL ,
  `sessionflag` VARCHAR(40) NULL )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `usuarios`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `usuarios` ;

CREATE  TABLE IF NOT EXISTS `usuarios` (
  `ficha` INT(11) NOT NULL COMMENT 'cod_usuario' ,
  `intranet` VARCHAR(40) NULL ,
  `clave` VARCHAR(40) NULL ,
  `nombre` VARCHAR(40) NULL ,
  `estado` VARCHAR(40) NULL ,
  `sessionflag` VARCHAR(40) NULL ,
  `acc_lectura` VARCHAR(40) NULL ,
  `acc_escribe` VARCHAR(40) NULL ,
  `acc_modifi` VARCHAR(40) NULL ,
  `fecha_ficha` VARCHAR(40) NULL ,
  `fecha_ultimavez` VARCHAR(40) NULL )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `log`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `log` ;

CREATE  TABLE IF NOT EXISTS `log` (
  `cod_log` TEXT NOT NULL COMMENT 'string con fecha y hora yyyymmddhhmmss' ,
  `sessionflag` VARCHAR(40) NULL ,
  `operacion` VARCHAR(40) NULL )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sucursal_usuario`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `sucursal_usuario` ;

CREATE  TABLE IF NOT EXISTS `sucursal_usuario` (
  `cod_usuario` INT(11) NOT NULL ,
  `cod_sucursal` VARCHAR(40) NOT NULL )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `entidad`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `entidad` ;

CREATE  TABLE IF NOT EXISTS `entidad` (
  `cod_entidad` INT(11) NOT NULL ,
  `abr_entidad` VARCHAR(40) NULL COMMENT 'siglas sucursal' ,
  `des_entidad` VARCHAR(40) NULL COMMENT 'descripcion sucursal' )
ENGINE = InnoDB
COMMENT = 'es lo que era sucursales';



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
