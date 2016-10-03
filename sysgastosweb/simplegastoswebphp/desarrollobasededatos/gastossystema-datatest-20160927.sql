SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

SET SQL_SAFE_UPDATES = 0;

TRUNCATE `gastossystema`.`categoria`;
INSERT INTO `gastossystema`.`categoria`
 (    `cod_categoria`,`des_categoria`,`fecha_categoria`,`sessionflag`)
VALUES
 (   '20160928125200','ESPECIAL','',''),
 (   '20160928125201','Nomina','',''),
 (   '20160928125202','Mantenimiento','',''),
 (   '20160928125204','Servicios','','');

TRUNCATE `gastossystema`.`subcategoria`;
INSERT INTO `gastossystema`.`subcategoria`
( `cod_subcategoria`,`des_subcategoria`,`fecha_subcategoria`,`sessionflag`)
VALUES
-- insercion de na subcategoria especial (ver inserciones categorias)
('2016092812520020160928125200', 'Gastos especiales', '', ''),
-- insercion de una subcategoria de nomina (ver inserciones categorias)
('2016092812520120160928125200', 'Quincena primera', '', ''),
('2016092812520120160928125201', 'Quincena segunda', '', '');

TRUNCATE `gastossystema`.`usuarios`;
INSERT INTO `gastossystema`.`usuarios`
(`ficha`,`intranet`,`clave`,`codger`,`nombre`,`estado`,`sessionflag`,
`acc_lectura`,`acc_escribe`,`acc_modifi`,`fecha_ficha`,`fecha_ultimavez`)
VALUES
('123','pepe','123','001','pepe trueno','ACTIVO','',
'TODOS','TODOS','TODOS','',''),
('456','pablo','456','002','pablo tuno','ACTIVO','',
'TODOS','TODOS','TODOS','','');

INSERT INTO `gastossystema`.`entidad`
(`cod_entidad`,`abr_entidad`,`abr_zona`,`des_entidad`,`status`,`codger`)
VALUES
('001', 'SUC1', 'ADM-CAS', 'Sucursal 1', 'ESPECIAL', '999'),
('002', 'SUC2', 'ZON-CEN', 'Sucursal 2', 'ESPECIAL', '999');

INSERT INTO `gastossystema`.`sucursal_usuario`
(`cod_usuario`,`cod_sucursal`)
VALUES
('123', '001'),
('123', '001');

BEGIN;
INSERT INTO `gastossystema`.`registro_gastos`
(
`cod_registro`,`cod_sucursal`,
`cod_categoria`,`cod_subcategoria`,
`des_registro`,
`mon_registro`,
`fecha_registro`,`fecha_factura`,
`sessionflag`,`estado`,`num_factura`
)
VALUES
(
'GAS20160929120000','001',
SUBSTRING('2016092812520020160928125200',1,14),'2016092812520020160928125200',
'empanadas de la comite, que no le dieron a lenz nadita',
999999999999.999,
'20160929120000','',
'','PROCESADO',''
);
INSERT INTO `gastossystema`.`registro_adjunto`
(
`cod_adjunto`,
`cod_registro`,
`hex_adjunto`,
`ruta_adjunto`,
`fecha_adjunto`,
`sessionflag`,
`nam_adjunto`,
`nam_archivo`)
VALUES
(
'ADJ20160929120000',
'GAS20160929120000',
'',
'',
'/ruta/al/archivo',
'',
'GAS20160929120000ADJ20160929120000.vacio',
'escaneo hecho por una persona.pdf'
);
COMMIT;


-- 20160928
--ALTER TABLE `gastossystema`.`registro_adjunto` ADD COLUMN `nam_adjunto` VARCHAR(40) NULL COMMENT 'nombre del archivo despues cargarlo al sistema'  AFTER `sessionflag` , ADD COLUMN `nam_archivo` VARCHAR(40) NULL COMMENT 'nombre del archivo antes de cargarlo al sistema'  AFTER `nam_adjunto` ;
-- 20160928 : todas las columnas son varchar menos el monto
--ALTER TABLE `gastossystema`.`registro_adjunto` ADD COLUMN `nam_adjunto` VARCHAR(40) NULL COMMENT 'nombre del archivo despues cargarlo al sistema'  AFTER `sessionflag` , ADD COLUMN `nam_archivo` VARCHAR(40) NULL COMMENT 'nombre del archivo antes de cargarlo al sistema'  AFTER `nam_adjunto` ;
--ALTER TABLE `gastossystema`.`registro_gastos` CHANGE COLUMN `cod_registro` `cod_registro` VARCHAR(40) NOT NULL COMMENT 'usa fecha YYYYMMDDhhmmss era id_unico_autogenerado'  ;
--ALTER TABLE `gastossystema`.`categoria` CHANGE COLUMN `cod_categoria` `cod_categoria` VARCHAR(40) NOT NULL COMMENT 'YYYYMMDDhhmmss'  ;
--ALTER TABLE `gastossystema`.`log` CHANGE COLUMN `cod_log` `cod_log` VARCHAR(40) NOT NULL COMMENT 'yyyymmddhhmmss'  ;
--ALTER TABLE `gastossystema`.`registro_adjunto` CHANGE COLUMN `cod_adjunto` `cod_adjunto` VARCHAR(40) NOT NULL COMMENT 'YYYYMMDDhhmmss'  ;
--ALTER TABLE `gastossystema`.`registro_gastos` CHANGE COLUMN `cod_sucursal` `cod_sucursal` VARCHAR(40) NOT NULL COMMENT 'sello de la entidad al cual se le adjudica'  , CHANGE COLUMN `cod_categoria` `cod_categoria` VARCHAR(40) NULL DEFAULT NULL COMMENT 'si alguna vez se desasocia subcategorias'  , CHANGE COLUMN `cod_subcategoria` `cod_subcategoria` VARCHAR(40) NOT NULL COMMENT 'cual subcategoria no puede faltar'  , CHANGE COLUMN `mon_registro` `mon_registro` DECIMAL(10,6) NOT NULL COMMENT 'cuanto se gasto, com algunos decimales'  , COMMENT = 'descripcion y monto de gastos o el detalle' ;
--ALTER TABLE `gastossystema`.`subcategoria` COMMENT = 'en que renglon cargan los gastos' ;
-- 20160929 : usar internacional utf8
--ALTER SCHEMA `gastossystema`  DEFAULT CHARACTER SET utf8;
--USE `gastossystema`;
--ALTER TABLE `gastossystema`.`registro_gastos` CHARACTER SET = utf8;
--ALTER TABLE `gastossystema`.`categoria` CHARACTER SET = utf8;
--ALTER TABLE `gastossystema`.`subcategoria` CHARACTER SET = utf8;
--ALTER TABLE `gastossystema`.`usuarios` CHARACTER SET = utf8;
--ALTER TABLE `gastossystema`.`log` CHARACTER SET = utf8;
--ALTER TABLE `gastossystema`.`sucursal_usuario` CHARACTER SET = utf8;
--ALTER TABLE `gastossystema`.`entidad` CHARACTER SET = utf8;

ALTER TABLE `gastossystema`.`registro_adjunto` CHANGE COLUMN `ruta_adjunto` `ruta_adjunto` VARCHAR(400) NULL DEFAULT NULL COMMENT 'ruta opcinal del archivo si esta en el sistema de ficheros'  , CHANGE COLUMN `nam_adjunto` `nam_adjunto` VARCHAR(400) NULL DEFAULT NULL COMMENT 'nombre del archivo despues cargarlo al sistema'  , CHANGE COLUMN `nam_archivo` `nam_archivo` VARCHAR(400) NULL DEFAULT NULL COMMENT 'nombre del archivo antes de cargarlo al sistema'  ;

--20161001

ALTER TABLE `gastossystema`.`categoria` CHANGE COLUMN `cod_categoria` `cod_categoria` VARCHAR(40) NOT NULL COMMENT 'CATYYYYMMDDhhmmss'  , CHANGE COLUMN `des_categoria` `des_categoria` VARCHAR(400) NOT NULL COMMENT 'descripcion_categoria'  , CHANGE COLUMN `fecha_categoria` `fecha_categoria` VARCHAR(40) NULL DEFAULT NULL COMMENT 'innecesario, por conpatibilidad'  , CHANGE COLUMN `sessionflag` `sessionflag` VARCHAR(40) NULL DEFAULT NULL COMMENT 'esto es quien_registro YYYYMMDDhhmmss + cod_sucursal + . + ficha'  ;
ALTER TABLE `gastossystema`.`entidad` CHANGE COLUMN `cod_entidad` `cod_entidad` VARCHAR(40) NOT NULL COMMENT 'sello de la sucursal o entidad'  , CHANGE COLUMN `abr_entidad` `abr_entidad` VARCHAR(40) NOT NULL COMMENT 'abrebiacion de esta sucursal'  , CHANGE COLUMN `abr_zona` `abr_zona` VARCHAR(40) NOT NULL COMMENT 'siglas de la zona de la sucursal'  , CHANGE COLUMN `des_entidad` `des_entidad` VARCHAR(400) NOT NULL COMMENT 'descripcion sucursal'  , CHANGE COLUMN `status` `status` VARCHAR(40) NOT NULL COMMENT 'ACTIVA|CERRADA|SUSPENDIDA|ESPECIAL'  , CHANGE COLUMN `codger` `codger` VARCHAR(40) NOT NULL COMMENT 'necesario porque deps lo manejan como centro de costos'  , ADD COLUMN `sessionflag` VARCHAR(40) NULL DEFAULT NULL  AFTER `codger` ;
ALTER TABLE `gastossystema`.`log` CHANGE COLUMN `cod_log` `cod_log` VARCHAR(40) NOT NULL COMMENT 'yyyymmddhhmmss'  , CHANGE COLUMN `sessionflag` `sessionflag` VARCHAR(40) NULL DEFAULT NULL COMMENT 'esto es quien_registro YYYYMMDDhhmmss + cod_sucursal + . + ficha'  , CHANGE COLUMN `operacion` `operacion` VARCHAR(4000) NULL DEFAULT NULL COMMENT 'en que modulo controlador y que realizo... y que tablas afecto'  ;
ALTER TABLE `gastossystema`.`registro_adjunto` CHARACTER SET = utf8 , CHANGE COLUMN `nam_adjunto` `nam_adjunto` VARCHAR(4000) NULL DEFAULT NULL COMMENT 'nombre del archivo despues cargarlo al sistema'  AFTER `hex_adjunto` , CHANGE COLUMN `nam_archivo` `nam_archivo` VARCHAR(4000) NULL DEFAULT NULL COMMENT 'nombre del archivo antes de cargarlo al sistema'  AFTER `nam_adjunto` , CHANGE COLUMN `cod_registro` `cod_registro` VARCHAR(40) NOT NULL COMMENT 'a cual registro de gasto le pertenece este adjunto'  , CHANGE COLUMN `hex_adjunto` `hex_adjunto` VARCHAR(99999) NULL DEFAULT NULL COMMENT 'la subida en base 64 del adjunto'  , CHANGE COLUMN `ruta_adjunto` `ruta_adjunto` VARCHAR(4000) NULL DEFAULT NULL COMMENT 'ruta en el servidor para descargar opcional'  , ADD PRIMARY KEY (`cod_adjunto`) ;
ALTER TABLE `gastossystema`.`registro_gastos` CHANGE COLUMN `num_factura` `num_factura` VARCHAR(40) NULL DEFAULT NULL COMMENT 'mumero de factura opcinal'  AFTER `mon_registro` , CHANGE COLUMN `estado` `estado` VARCHAR(40) NULL DEFAULT NULL COMMENT 'APROBADO|RECHAZADO|PROCESADO|INVALIDO'  AFTER `num_factura` , CHANGE COLUMN `cod_registro` `cod_registro` VARCHAR(40) NOT NULL COMMENT 'usa fecha YYYYMMDDhhmmss era id_unico_autogenerado'  , CHANGE COLUMN `cod_sucursal` `cod_sucursal` VARCHAR(40) NOT NULL COMMENT 'sello de la entidad al cual se le adjudica'  , CHANGE COLUMN `cod_categoria` `cod_categoria` VARCHAR(40) NULL DEFAULT NULL COMMENT 'por compatibilidad no es necesario SUBSTRING(cod_subcategoria,1,14)'  , CHANGE COLUMN `cod_subcategoria` `cod_subcategoria` VARCHAR(40) NOT NULL COMMENT 'cual subcategoria no puede faltar'  , CHANGE COLUMN `des_registro` `des_registro` TEXT NOT NULL COMMENT 'cual fue el gasto esto era descripcion_gasto'  , CHANGE COLUMN `fecha_registro` `fecha_registro` VARCHAR(40) NULL DEFAULT NULL COMMENT 'YYYYMMDD innecesario, se deja por compatibilidad'  , CHANGE COLUMN `fecha_factura` `fecha_factura` VARCHAR(40) NULL DEFAULT NULL COMMENT 'YYYYMMDD de la factura si tiene'  , CHANGE COLUMN `sessionflag` `sessionflag` VARCHAR(40) NULL DEFAULT NULL COMMENT 'esto es quien_registro YYYYMMDDhhmmss + cod_sucursal + . + ficha'  ;
ALTER TABLE `gastossystema`.`subcategoria` CHANGE COLUMN `cod_subcategoria` `cod_subcategoria` VARCHAR(40) NOT NULL COMMENT 'CATYYYYMMDDhhmmssSUBYYYYMMDDhhmmss'  , CHANGE COLUMN `des_subcategoria` `des_subcategoria` VARCHAR(400) NOT NULL COMMENT 'que tipo de gasto en la categoria'  , CHANGE COLUMN `fecha_subcategoria` `fecha_subcategoria` VARCHAR(40) NULL DEFAULT NULL COMMENT 'innecesario, por compatibilidad'  , CHANGE COLUMN `sessionflag` `sessionflag` VARCHAR(40) NULL DEFAULT NULL COMMENT 'esto es quien_registro YYYYMMDDhhmmss + cod_sucursal + . + ficha'  , ADD COLUMN `cod_categoria` VARCHAR(40) NULL DEFAULT NULL COMMENT 'compatibilidad con grocerycrud: SUBSTRING(cod_subcategoria,1,14)'  FIRST ;
ALTER TABLE `gastossystema`.`sucursal_usuario` CHANGE COLUMN `cod_usuario` `cod_usuario` VARCHAR(40) NOT NULL COMMENT 'ficha del usuario o id del usuario'  , CHANGE COLUMN `cod_sucursal` `cod_sucursal` VARCHAR(40) NOT NULL COMMENT 'sello al cual esta asociado'  ;
ALTER TABLE `gastossystema`.`usuarios` CHANGE COLUMN `fecha_ultimavez` `fecha_ultimavez` VARCHAR(40) NULL DEFAULT NULL COMMENT 'cuando fue la ultima vez que entro sesion'  AFTER `acc_modifi` , CHANGE COLUMN `sessionflag` `sessionflag` VARCHAR(40) NULL DEFAULT NULL COMMENT 'esto es quien_registro YYYYMMDDhhmmss + cod_sucursal + . + ficha'  AFTER `fecha_ficha` , CHANGE COLUMN `ficha` `ficha` VARCHAR(40) NOT NULL COMMENT 'cod_usuario, cedula en vnzla'  , CHANGE COLUMN `intranet` `intranet` VARCHAR(40) NOT NULL COMMENT 'login del usuario, id del correo'  , CHANGE COLUMN `clave` `clave` VARCHAR(40) NOT NULL  , CHANGE COLUMN `codger` `codger` VARCHAR(40) NULL DEFAULT NULL COMMENT 'necesario porque deps lo manejan como centro de costos'  , CHANGE COLUMN `nombre` `nombre` VARCHAR(400) NULL DEFAULT NULL COMMENT 'nombre y apellido'  , CHANGE COLUMN `estado` `estado` VARCHAR(40) NULL DEFAULT NULL COMMENT 'ACTIVO INACTIVO SUSPENDIDO INVALIDO'  , CHANGE COLUMN `acc_lectura` `acc_lectura` VARCHAR(4000) NOT NULL COMMENT 'modulos o pagina controlador que puede leer separados por barra'  , CHANGE COLUMN `acc_escribe` `acc_escribe` VARCHAR(4000) NOT NULL COMMENT 'modulos o nombre controlador que puede crear registros separados por barra'  , CHANGE COLUMN `acc_modifi` `acc_modifi` VARCHAR(4000) NOT NULL COMMENT 'modulos o nombre controlador que puede alterar separados por barra'  , CHANGE COLUMN `fecha_ficha` `fecha_ficha` VARCHAR(40) NULL DEFAULT NULL  ;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
