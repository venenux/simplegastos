CREATE VIEW `matrixdatoscruda` AS 
    select 
        `a`.`cod_entidad` AS `cod_entidad`,
        `b`.`des_entidad` AS `des_entidad`,
        `a`.`cod_categoria` AS `cod_categoria`,
        `c`.`des_categoria` AS `des_categoria`,
        ifnull(`c`.`tipo_categoria`,'ADMINISTRATIVO') AS `tipo_categoria`,
        sum(ifnull(`a`.`mon_registro`, 0)) AS `mon_registro`,
        substr(`a`.`fecha_concepto`, 1, 6) AS `fecha_concepto`,
        `a`.`fecha_registro` AS `fecha_registro`
    from
        ((`registro_gastos` `a`
        left join `entidad` `b` ON ((`a`.`cod_entidad` = `b`.`cod_entidad`)))
        left join `categoria` `c` ON ((`a`.`cod_categoria` = `c`.`cod_categoria`)))
    group by `a`.`cod_entidad` , `a`.`cod_categoria` 
    union select 
        `entidad`.`cod_entidad` AS `cod_entidad`,
        `entidad`.`des_entidad` AS `des_entidad`,
        `categoria`.`cod_categoria` AS `cod_categoria`,
        `categoria`.`des_categoria` AS `des_categoria`,
		ifnull(`categoria`.`tipo_categoria`,'ADMINISTRATIVO') AS `tipo_categoria`,
        0 AS `mon_registro`,
        '' AS `fecha_concepto`,
        '' AS `fecha_registro`
    from
        (`categoria`
        join `entidad`)
    group by `entidad`.`cod_entidad` , `categoria`.`cod_categoria`;

CREATE  OR REPLACE VIEW `matrixdatostodas` 
AS 
	select 
		`matrixdatoscruda`.`cod_entidad` AS `cod_entidad`,
		`matrixdatoscruda`.`des_entidad` AS `des_entidad`,
		`matrixdatoscruda`.`cod_categoria` AS `cod_categoria`,
		`matrixdatoscruda`.`des_categoria` AS `des_categoria`,
		`matrixdatoscruda`.`tipo_categoria` AS `tipo_categoria`,
		`matrixdatoscruda`.`mon_registro` AS `mon_registro`,
		`matrixdatoscruda`.`fecha_concepto` AS `fecha_concepto`,
		`matrixdatoscruda`.`fecha_registro` AS `fecha_registro` 
	from 
		`matrixdatoscruda` 
	group by 
		`matrixdatoscruda`.`cod_entidad`,`matrixdatoscruda`.`cod_categoria` 
	order by 
		`matrixdatoscruda`.`cod_entidad`,`matrixdatoscruda`.`cod_categoria`;