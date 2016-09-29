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
(
`ficha`,`intranet`,`clave`,`codger`,`nombre`,`estado`,`sessionflag`,
`acc_lectura`,`acc_escribe`,`acc_modifi`,`fecha_ficha`,`fecha_ultimavez`
)
VALUES
(
'123','pepe','123','001','pepe trueno','ACTIVO','',
'TODOS','TODOS','TODOS','',''
),
(
'456','pablo','456','002','pablo tuno','ACTIVO','',
'TODOS','TODOS','TODOS','',''
);


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
