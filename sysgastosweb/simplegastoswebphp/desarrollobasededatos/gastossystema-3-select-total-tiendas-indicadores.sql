-- consulta de total por entidad en cada categoria, para gestion de indicadores

SELECT 
    CONVERT( CONVERT( DATE_FORMAT(NOW(),'%Y%m%d%H%i%s'), UNSIGNED) + CONVERT(a.cod_entidad,UNSIGNED), CHAR) as cod_indicador,
    a.cod_entidad, 
    -- b.des_entidad, 
    IFNULL(SUM(IFNULL(a.mon_registro, 0)), 0) as mon_gastototal,
    0 as mon_ventatotal,
    SUBSTRING(a.fecha_concepto, 1, 6) as fecha_mes, 
    '20160117998systemas' as sessionficha, 
    null as sessionflag
FROM
    registro_gastos a
LEFT JOIN entidad b ON a.cod_entidad = b.cod_entidad
where
    a.cod_registro <> ''
        and b.status <> 'INACTIVO'
        and CONVERT( a.fecha_concepto , UNSIGNED) >= CONVERT( '20170400' , UNSIGNED)
        and CONVERT( a.fecha_concepto , UNSIGNED) <= CONVERT( '20170490' , UNSIGNED)
group by cod_entidad
order by b.des_entidad
