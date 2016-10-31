SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

CREATE SCHEMA IF NOT EXISTS `gastossystema` DEFAULT CHARACTER SET utf8 ;
USE `gastossystema` ;

-- -----------------------------------------------------
-- Table `categoria`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `categoria` ;
CREATE  TABLE IF NOT EXISTS `categoria` (
  `cod_categoria` VARCHAR(40) NOT NULL COMMENT 'CATYYYYMMDDhhmmss' ,
  `des_categoria` VARCHAR(400) NOT NULL COMMENT 'descripcion o nombre categoria' ,
  `tipo_categoria` VARCHAR(40) NULL DEFAULT 'NORMAL' COMMENT 'ADMINISTRATIVO|NORMAL' ,
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
  `tipo_entidad` VARCHAR(40) NULL DEFAULT 'SUCURSAL' COMMENT 'ADMINISTRATIVA|SUCURSAL' ,
  `cod_fondo` VARCHAR(40) NULL DEFAULT NULL COMMENT 'fondo o monto disponible si aplica' ,
  `sello` VARCHAR(40) NULL DEFAULT NULL COMMENT 'sello asociado oasis' ,
  `rif_sucursal` VARCHAR(40) NULL COMMENT 'Rif razon social' ,
  `rif_razonsocial` VARCHAR(40) NULL COMMENT 'Nombre razon social' ,
  `des_administradora` VARCHAR(40) NULL COMMENT 'Nombre del usuario administradora' ,
  `num_telefonofijo` VARCHAR(40) NULL COMMENT 'Numero telefono fijo' ,
  `des_nombreenc1` VARCHAR(40) NULL COMMENT 'Nombre encargado o jefe' ,
  `num_celularenc1` VARCHAR(40) NULL COMMENT 'Numero telefono encargado o jefe' ,
  `des_nombreenc2` VARCHAR(40) NULL COMMENT 'Nombre segundo encargado' ,
  `num_celularenc2` VARCHAR(40) NULL COMMENT 'Numero Telefono segundo' ,
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
-- Table `fondo`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fondo` ;
CREATE  TABLE IF NOT EXISTS `fondo` (
  `cod_fondo` VARCHAR(40) NOT NULL COMMENT 'FONYYYYMMDDhhmmss' ,
  `fecha_fondo` VARCHAR(40) NOT NULL COMMENT 'por cada codigo hay una fecha y monto' ,
  `mon_fondo` DECIMAL(20,2) NOT NULL COMMENT 'monto adjudicado a la fecha' ,
  `sessionflag` VARCHAR(40) NULL DEFAULT NULL COMMENT 'si alguien lo modifico a mano' ,
  `sessionficha` VARCHAR(40) NULL DEFAULT NULL COMMENT 'quien le adjudico el fondo' ,
  PRIMARY KEY (`cod_fondo`, `fecha_fondo`) )
COMMENT = 'fondos de montos manejados';


-- -----------------------------------------------------
-- Table `log`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `log` ;
CREATE  TABLE IF NOT EXISTS `log` (
  `cod_log` VARCHAR(40) NOT NULL COMMENT 'LOGyyyymmddhhmmss' ,
  `operacion` VARCHAR(20000) NULL DEFAULT NULL COMMENT 'en que modulo controlador y que realizo... y que tablas afecto' ,
  `sessionficha` VARCHAR(40) NULL DEFAULT NULL COMMENT 'quien lo hizo YYYYMMDDhhmmss+codger+.+user' ,
  PRIMARY KEY (`cod_log`) )
COMMENT = 'tabla de chismoso, cada operacion se graba aqui';


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
  `des_concepto` VARCHAR(4000) NOT NULL COMMENT 'descripcion del gasto' ,
  `tipo_concepto` VARCHAR(40) NULL DEFAULT 'NORMAL' COMMENT 'ADMINISTRATIVO|NORMAL' ,
  `des_detalle` TEXT NULL DEFAULT NULL COMMENT 'detalle opcional del gasto' ,
  `des_estado` TEXT NULL DEFAULT NULL COMMENT 'porque cambio de estado' ,
  `estado` VARCHAR(40) NULL DEFAULT 'PENDIENTE' COMMENT 'APROBADO|RECHAZADO|PENDIENTE|INVALIDO' ,
  `factura_tipo` VARCHAR(40) NULL DEFAULT 'EGRESO' COMMENT 'EGRESO|CONTRIBUYENTE' ,
  `factura_rif` VARCHAR(40) NULL DEFAULT NULL COMMENT 'rif si factura es contribuyente' ,
  `factura_num` VARCHAR(40) NULL DEFAULT NULL COMMENT 'mumero de factura opcinal' ,
  `factura_bin` TEXT NULL DEFAULT NULL COMMENT 'ruta/hex64 de factura por defecto si la sube' ,
  `fecha_concepto` VARCHAR(40) NULL DEFAULT NULL COMMENT 'YYYYMMDD de cuadno es el gasto registrado' ,
  `fecha_registro` VARCHAR(40) NULL DEFAULT NULL COMMENT 'YYYYMMDD de cuando se creo cada entrada' ,
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
  `tipo_subcategoria` VARCHAR(40) NULL DEFAULT 'NORMAL' COMMENT 'ADMINISTRATIVA|NORMAL' ,
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
  `cod_fondo` VARCHAR(40) NULL DEFAULT NULL COMMENT 'fondo o monto asociado si aplica' ,
  `estado` VARCHAR(40) NOT NULL COMMENT 'ACTIVO|INACTIVO|SUSPENDIDO|INVALIDO' ,
  `tipo_usuario` VARCHAR(40) NULL COMMENT 'ADMINISTRATIVO|NORMAL' ,
  `acc_lectura` VARCHAR(4000) NULL COMMENT 'donde y que puede ver' ,
  `acc_escribe` VARCHAR(4000) NULL COMMENT 'donde y que puede adjudicar gasto' ,
  `acc_modifi` VARCHAR(4000) NULL COMMENT 'donde y que puede modificar gastos' ,
  `fecha_ultimavez` VARCHAR(40) NULL DEFAULT NULL COMMENT 'ultima vez que entro sesion' ,
  `sessionflag` VARCHAR(40) NULL DEFAULT NULL COMMENT 'quien altero YYYYMMDDhhmmss+codger+.+user' ,
  `sessionficha` VARCHAR(40) NULL COMMENT 'quien lo creo YYYYMMDDhhmmss+codger+.+user' ,
  PRIMARY KEY (`intranet`) )
COMMENT = 'tabla de usuarios';

USE `gastossystema` ;

-- -----------------------------------------------------
-- Placeholder table for view `matrixdatoscruda`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `matrixdatoscruda` (`cod_entidad` INT, `des_entidad` INT, `cod_categoria` INT, `des_categoria` INT, `mon_registro` INT, `fecha_concepto` INT, `fecha_registro` INT);

-- -----------------------------------------------------
-- View `matrixdatoscruda`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `matrixdatoscruda` ;
DROP TABLE IF EXISTS `matrixdatoscruda`;
USE `gastossystema`;
CREATE  OR REPLACE VIEW `matrixdatoscruda` AS
		SELECT
			a.cod_entidad, b.des_entidad,
			a.cod_categoria, c.des_categoria,
			SUM(IFNULL(a.mon_registro,0)) as mon_registro,
			SUBSTRING(a.fecha_concepto,1,6) as fecha_concepto, a.fecha_registro
		FROM registro_gastos a
			LEFT JOIN entidad b on a.cod_entidad=b.cod_entidad /* todas las entiddes deben registrar gasto*/
			LEFT JOIN categoria c ON a.cod_categoria=c.cod_categoria /*solo en las categorias que haya gasto */
		GROUP BY
			a.cod_entidad, a.cod_categoria, a.fecha_concepto, a.fecha_registro /*se debe agrupar la fecha aqui interno para que separa cuando fue realizado de la suma total */
	UNION
		SELECT
			cod_entidad, des_entidad,
			cod_categoria, des_categoria,
			0 as mon_registro, 		/* se une con el resto de las categorias para que muestre "0" como monto en esta */
			'' as fecha_concepto, '' as fecha_registro
		FROM categoria
			CROSS JOIN entidad
		GROUP BY cod_entidad, cod_categoria
;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

