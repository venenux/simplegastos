
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
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
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
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
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
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
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
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
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
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
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
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
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
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
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
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'tabla de usuarios';


-- -----------------------------------------------------
-- Table `registro_errado`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `registro_errado` ;

CREATE  TABLE IF NOT EXISTS `registro_errado` (
  `cod_registro` VARCHAR(40) NOT NULL COMMENT 'codigo del registro gasto corregir' ,
  `cod_entidad` VARCHAR(40) NOT NULL COMMENT 'a quien se le exige corregir' ,
  `sessionerror` VARCHAR(40) NULL COMMENT 'quien erro y cuando YYYYMMDDhhmmss + codger + . + ficha' ,
  `sessionficha` VARCHAR(40) NULL COMMENT 'quien lo creo YYYYMMDDhhmmss + codger + . + ficha' ,
  PRIMARY KEY (`cod_registro`) )
ENGINE = InnoDB
COMMENT = 'notificaciones de errores cargados a usuarios';

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


-- -----------------------------------------------------
-- Data for table `categoria`
-- -----------------------------------------------------
START TRANSACTION;
USE `gastossystema`;
INSERT INTO `categoria` (`cod_categoria`, `des_categoria`, `tipo_categoria`, `fecha_categoria`, `sessionflag`) VALUES ('CAT20161002000000', 'Especial', 'ADMINISRATIVO', '20161003', NULL);
INSERT INTO `categoria` (`cod_categoria`, `des_categoria`, `tipo_categoria`, `fecha_categoria`, `sessionflag`) VALUES ('CAT20161002010001', 'CATE 1', 'NORMAL', '20161003', NULL);
INSERT INTO `categoria` (`cod_categoria`, `des_categoria`, `tipo_categoria`, `fecha_categoria`, `sessionflag`) VALUES ('CAT20161002010002', 'CATE 2', 'NORMAL', '20161007', NULL);

COMMIT;

-- -----------------------------------------------------
-- Data for table `entidad`
-- -----------------------------------------------------
START TRANSACTION;
USE `gastossystema`;
INSERT INTO `entidad` (`cod_entidad`, `abr_entidad`, `abr_zona`, `des_entidad`, `status`, `tipo_entidad`, `cod_fondo`, `sello`, `rif_sucursal`, `rif_razonsocial`, `des_administradora`, `num_telefonofijo`, `des_nombreenc1`, `num_celularenc1`, `des_nombreenc2`, `num_celularenc2`, `sessionflag`) VALUES ('000', 'SYS', 'TODAS', 'systemas', 'ACTIVO', 'ADMINISTRATIVA', NULL, '999', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `entidad` (`cod_entidad`, `abr_entidad`, `abr_zona`, `des_entidad`, `status`, `tipo_entidad`, `cod_fondo`, `sello`, `rif_sucursal`, `rif_razonsocial`, `des_administradora`, `num_telefonofijo`, `des_nombreenc1`, `num_celularenc1`, `des_nombreenc2`, `num_celularenc2`, `sessionflag`) VALUES ('001', 'SUC1', 'CAPITAL', 'Sucursal 1', 'ACTIVO', 'SUCURSAL', 'FON20160101010101', '01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `entidad` (`cod_entidad`, `abr_entidad`, `abr_zona`, `des_entidad`, `status`, `tipo_entidad`, `cod_fondo`, `sello`, `rif_sucursal`, `rif_razonsocial`, `des_administradora`, `num_telefonofijo`, `des_nombreenc1`, `num_celularenc1`, `des_nombreenc2`, `num_celularenc2`, `sessionflag`) VALUES ('222', 'SUC2', 'ZON-CEN', 'Sucursal 2', 'ACTIVO', 'SUCURSAL', NULL, '02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `entidad` (`cod_entidad`, `abr_entidad`, `abr_zona`, `des_entidad`, `status`, `tipo_entidad`, `cod_fondo`, `sello`, `rif_sucursal`, `rif_razonsocial`, `des_administradora`, `num_telefonofijo`, `des_nombreenc1`, `num_celularenc1`, `des_nombreenc2`, `num_celularenc2`, `sessionflag`) VALUES ('333', 'SUC3', 'ZON-CEN', 'Sucursal 3', 'ACTIVO', 'SUCURSAL', 'FON20160101010103', '03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

COMMIT;

-- -----------------------------------------------------
-- Data for table `entidad_usuario`
-- -----------------------------------------------------
START TRANSACTION;
USE `gastossystema`;
INSERT INTO `entidad_usuario` (`intranet`, `cod_entidad`, `sessionflag`) VALUES ('usuario1', '001', NULL);
INSERT INTO `entidad_usuario` (`intranet`, `cod_entidad`, `sessionflag`) VALUES ('usuario2', '222', NULL);

COMMIT;

-- -----------------------------------------------------
-- Data for table `fondo`
-- -----------------------------------------------------
START TRANSACTION;
USE `gastossystema`;
INSERT INTO `fondo` (`cod_fondo`, `fecha_fondo`, `mon_fondo`, `sessionflag`, `sessionficha`) VALUES ('FON20160101010100', '20160101', 0.0, NULL, NULL);
INSERT INTO `fondo` (`cod_fondo`, `fecha_fondo`, `mon_fondo`, `sessionflag`, `sessionficha`) VALUES ('FON20160101010101', '20160101', 10000.00, NULL, NULL);
INSERT INTO `fondo` (`cod_fondo`, `fecha_fondo`, `mon_fondo`, `sessionflag`, `sessionficha`) VALUES ('FON20160101010102', '20160101', 5000.00, NULL, NULL);
INSERT INTO `fondo` (`cod_fondo`, `fecha_fondo`, `mon_fondo`, `sessionflag`, `sessionficha`) VALUES ('FON20160101010103', '20160101', 10000.00, NULL, NULL);
INSERT INTO `fondo` (`cod_fondo`, `fecha_fondo`, `mon_fondo`, `sessionflag`, `sessionficha`) VALUES ('FON20160101010104', '20160101', 15000.00, NULL, NULL);
INSERT INTO `fondo` (`cod_fondo`, `fecha_fondo`, `mon_fondo`, `sessionflag`, `sessionficha`) VALUES ('FON20160101010105', '20160101', 20000.50, NULL, NULL);

COMMIT;

-- -----------------------------------------------------
-- Data for table `registro_gastos`
-- -----------------------------------------------------
START TRANSACTION;
USE `gastossystema`;
INSERT INTO `registro_gastos` (`cod_registro`, `cod_entidad`, `cod_categoria`, `cod_subcategoria`, `mon_registro`, `des_concepto`, `tipo_concepto`, `des_detalle`, `des_estado`, `estado`, `factura_tipo`, `factura_rif`, `factura_num`, `factura_bin`, `fecha_concepto`, `fecha_registro`, `sessionflag`, `sessionficha`) VALUES ('GAS20161007221632', '001', 'CAT20161002010001', 'SUB20161007221219', 1000.50, 'gastode suc 1 en cate 1 sub 1', 'NORMAL', 'datos extras', NULL, 'PENDIENTE', 'EGRESO', NULL, NULL, NULL, '20161007', '20161007', 'admin_user20161007221650', '');
INSERT INTO `registro_gastos` (`cod_registro`, `cod_entidad`, `cod_categoria`, `cod_subcategoria`, `mon_registro`, `des_concepto`, `tipo_concepto`, `des_detalle`, `des_estado`, `estado`, `factura_tipo`, `factura_rif`, `factura_num`, `factura_bin`, `fecha_concepto`, `fecha_registro`, `sessionflag`, `sessionficha`) VALUES ('GAS20161007221932', '001', 'CAT20161002010002', 'SUB20161007221439', 1000.50, 'gastp de suc 1 cate 2 sub2', 'NORMAL', 'otros 2', NULL, 'APROBADO', 'EGRESO', NULL, NULL, NULL, '20161007', '20161007', NULL, '');
INSERT INTO `registro_gastos` (`cod_registro`, `cod_entidad`, `cod_categoria`, `cod_subcategoria`, `mon_registro`, `des_concepto`, `tipo_concepto`, `des_detalle`, `des_estado`, `estado`, `factura_tipo`, `factura_rif`, `factura_num`, `factura_bin`, `fecha_concepto`, `fecha_registro`, `sessionflag`, `sessionficha`) VALUES ('GAS20161007222129', '001', 'CAT20161002010002', 'SUB20161007221415', 1000.50, 'gastp de suc 1 cate 2 sub 1', 'ADMINISTRATIVO', 'otros 3', NULL, 'APROBADO', 'EGRESO', NULL, NULL, NULL, '20161007', '20161007', 'admin_user20161007222221', '');
INSERT INTO `registro_gastos` (`cod_registro`, `cod_entidad`, `cod_categoria`, `cod_subcategoria`, `mon_registro`, `des_concepto`, `tipo_concepto`, `des_detalle`, `des_estado`, `estado`, `factura_tipo`, `factura_rif`, `factura_num`, `factura_bin`, `fecha_concepto`, `fecha_registro`, `sessionflag`, `sessionficha`) VALUES ('GAS20161007222327', '002', 'CAT20161002010002', 'SUB20161007221415', 1000.50, 'gasto de sucs 2 en cat 2 sub 1', 'NORMAL', 'otros', NULL, 'APROBADO', 'EGRESO', NULL, NULL, NULL, '20161007', '20161007', NULL, '');
INSERT INTO `registro_gastos` (`cod_registro`, `cod_entidad`, `cod_categoria`, `cod_subcategoria`, `mon_registro`, `des_concepto`, `tipo_concepto`, `des_detalle`, `des_estado`, `estado`, `factura_tipo`, `factura_rif`, `factura_num`, `factura_bin`, `fecha_concepto`, `fecha_registro`, `sessionflag`, `sessionficha`) VALUES ('GAS20161007222414', '002', 'CAT20161002010001', 'SUB20161007221219', 2000.50, 'gasto de suc 2 cat 1 sub 1', 'NORMAL', 'viatico de pasaje', NULL, 'PENDIENTE', 'EGRESO', NULL, NULL, NULL, '20161007', '20161007', 'admin_user20161007222428', '');
INSERT INTO `registro_gastos` (`cod_registro`, `cod_entidad`, `cod_categoria`, `cod_subcategoria`, `mon_registro`, `des_concepto`, `tipo_concepto`, `des_detalle`, `des_estado`, `estado`, `factura_tipo`, `factura_rif`, `factura_num`, `factura_bin`, `fecha_concepto`, `fecha_registro`, `sessionflag`, `sessionficha`) VALUES ('GAS20161007222736', '003', 'CAT20161002010001', 'SUB20161007221219', 1000.50, 'gasto de suc 3 cat 1 sub 1', 'NORMAL', 'otro costos de almuerzos', NULL, 'PENDIENTE', 'EGRESO', NULL, NULL, NULL, '20161007', '20161007', 'admin_user20161007222756', '');
INSERT INTO `registro_gastos` (`cod_registro`, `cod_entidad`, `cod_categoria`, `cod_subcategoria`, `mon_registro`, `des_concepto`, `tipo_concepto`, `des_detalle`, `des_estado`, `estado`, `factura_tipo`, `factura_rif`, `factura_num`, `factura_bin`, `fecha_concepto`, `fecha_registro`, `sessionflag`, `sessionficha`) VALUES ('GAS20161007222850', '003', 'CAT20161002010002', 'SUB20161007221415', 2000.50, 'gastos de suc 3 en cat 2 sub 1', 'NORMAL', 'se rompio una tuba', NULL, 'PENDIENTE', 'EGRESO', NULL, NULL, NULL, '20161007', '20161007', NULL, '');
INSERT INTO `registro_gastos` (`cod_registro`, `cod_entidad`, `cod_categoria`, `cod_subcategoria`, `mon_registro`, `des_concepto`, `tipo_concepto`, `des_detalle`, `des_estado`, `estado`, `factura_tipo`, `factura_rif`, `factura_num`, `factura_bin`, `fecha_concepto`, `fecha_registro`, `sessionflag`, `sessionficha`) VALUES ('GAS20161007222941', '003', 'CAT20161002010002', 'SUB20161007221439', 1000.50, 'gastos de suc 3 en cat 2 sub 2', 'NORMAL', 'se rompio un tubo', NULL, 'PENDIENTE', 'EGRESO', NULL, NULL, NULL, '20161007', '20161007', NULL, '');
INSERT INTO `registro_gastos` (`cod_registro`, `cod_entidad`, `cod_categoria`, `cod_subcategoria`, `mon_registro`, `des_concepto`, `tipo_concepto`, `des_detalle`, `des_estado`, `estado`, `factura_tipo`, `factura_rif`, `factura_num`, `factura_bin`, `fecha_concepto`, `fecha_registro`, `sessionflag`, `sessionficha`) VALUES ('GAS20161008005532', '000', 'CAT20161002010001', 'SUB20161002000001', 3000.00, 'gasto especial', 'NORMAL', 'es un muy especial gasto de dulces', NULL, 'PENDIENTE', 'EGRESO', NULL, NULL, NULL, '20161008', '20161008', NULL, '');

COMMIT;

-- -----------------------------------------------------
-- Data for table `subcategoria`
-- -----------------------------------------------------
START TRANSACTION;
USE `gastossystema`;
INSERT INTO `subcategoria` (`cod_categoria`, `cod_subcategoria`, `des_subcategoria`, `tipo_subcategoria`, `fecha_subcategoria`, `sessionflag`) VALUES ('CAT20161002010001', 'SUB20161007221219', 'Subcat de CAT1', 'NORMAL', '20161007', NULL);
INSERT INTO `subcategoria` (`cod_categoria`, `cod_subcategoria`, `des_subcategoria`, `tipo_subcategoria`, `fecha_subcategoria`, `sessionflag`) VALUES ('CAT20161002010002', 'SUB20161007221415', 'Subcat1 de CAT2', 'NORMAL', '20161007', NULL);
INSERT INTO `subcategoria` (`cod_categoria`, `cod_subcategoria`, `des_subcategoria`, `tipo_subcategoria`, `fecha_subcategoria`, `sessionflag`) VALUES ('CAT20161002010002', 'SUB20161007221439', 'Subcat2 de CAT2', 'NORMAL', '20161007', NULL);

COMMIT;

-- -----------------------------------------------------
-- Data for table `usuarios`
-- -----------------------------------------------------
START TRANSACTION;
USE `gastossystema`;
INSERT INTO `usuarios` (`ficha`, `intranet`, `clave`, `sello`, `nombre`, `detalles`, `cod_fondo`, `estado`, `tipo_usuario`, `acc_lectura`, `acc_escribe`, `acc_modifi`, `fecha_ultimavez`, `sessionflag`, `sessionficha`) VALUES ('99999990', 'admin_user', '9990', '', 'Administrador', NULL, NULL, 'ACTIVO', 'ADMINISTRATIVO', '', '', '', '', '20160101', NULL);
INSERT INTO `usuarios` (`ficha`, `intranet`, `clave`, `sello`, `nombre`, `detalles`, `cod_fondo`, `estado`, `tipo_usuario`, `acc_lectura`, `acc_escribe`, `acc_modifi`, `fecha_ultimavez`, `sessionflag`, `sessionficha`) VALUES ('12345678', 'usuario1', '123', '01', 'Usuario Apellido', NULL, 'FON20160101010105', 'ACTIVO', 'NORMAL', '', '', '', NULL, '20161007', 'admin_user20161007220657');
INSERT INTO `usuarios` (`ficha`, `intranet`, `clave`, `sello`, `nombre`, `detalles`, `cod_fondo`, `estado`, `tipo_usuario`, `acc_lectura`, `acc_escribe`, `acc_modifi`, `fecha_ultimavez`, `sessionflag`, `sessionficha`) VALUES ('12345679', 'usuario2', '123', '02', 'Persona Apellido', NULL, NULL, 'ACTIVO', 'NORMAL', '', '', '', NULL, '20161007', 'admin_user20161007220816');

COMMIT;
