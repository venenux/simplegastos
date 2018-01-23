# instalacion simplegastos

## Requisitos

1. webserver : lighttpd, apache2, nginx, hiawatta
2. php5: mcryp, gd, curl, mysql/mysqlnd
3. database: mysql 5.0+ / mariadb 5.1+

## Procedimiento

1. crear un directorio en el htdocs del servidor web como "simplegastos"...
2. cargar alli lso directorios (`appsys`, `appweb`, `assets`) del proyecto simplegastosweb/simplegastoswebphp
3. configura un usuario y db asi como el acceso a la db, asumamos `simplegastos` db user/name
4. cargar/construir SQL script `desarrollobasededatos/gastossystema-1-database.sql` a la base de datos
5. configurar la coneccion a db en el config file en `appweb/config/database.php` 
6. configurar la conecion activa como se acordo el username/dbname/password el los pasos anteriores.
7. abrir el navegador y visitar `http://127.0.0.1/simplegastos`

Professional way recommended to setup a place aliasing event putt directly the files in the htdocs.

This project was supersed by "elsistema", and only acept code fixeds and some minor issues.
