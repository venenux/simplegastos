# instalacion simplegastos

## Requisitos

1. webserver : lighttpd, apache2, nginx, hiawatta
2. php5: mcryp, gd, curl, mysql/mysqlnd
3. database: mysql 5.0+ / mariadb 5.1+

## Procedimiento

1. make a directory in the htdocs webserver root directory as "simplegastos"...
2. load specific directory sources (`appsys`, `appweb`, `assets`) in the webserver root htdocs
3. setup a database user, db and password for db access, we asumed `simplegastos` db user/name
4. load/build the db SQL script `desarrollobasededatos/gastossystema-1-database.sql` only to the database
5. setup the database connection in the config file `appweb/config/database.php` 
6. configure the active group database connection with the username/dbname/password done already.
7. open your browser and hit to visit `http://127.0.0.1/simplegastos`

Professional way recommended to setup a place aliasing event putt directly the files in the htdocs.

This project was supersed by "elsistema", and only acept code fixeds and some minor issues.
