set SQL_SAFE_UPDATES = 0;
UPDATE adm_indefi_ventagasto des,
    venta ori 
SET 
    des.mon_ventatotal = ori.venta
WHERE
    des.cod_entidad = ori.cod_entidad
        and des.fecha_mes = ori.fecha;
set SQL_SAFE_UPDATES = 1;