<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the 'Database Connection'
| page of the User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['hostname'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to
|	['dbdriver'] The database type. ie: mysql.  Currently supported:
				 mysql, mysqli, postgre, odbc, mssql, sqlite, oci8
|	['dbprefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Active Record class
|	['pconnect'] TRUE/FALSE - Whether to use a persistent connection
|	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
|	['cache_on'] TRUE/FALSE - Enables/disables query caching
|	['cachedir'] The path to the folder where cache files should be stored
|	['char_set'] The character set used in communicating with the database
|	['dbcollat'] The character collation used in communicating with the database
|				 NOTE: For MySQL and MySQLi databases, this setting is only used
| 				 as a backup if your server is running PHP < 5.2.3 or MySQL < 5.0.7
|				 (and in table creation queries made with DB Forge).
| 				 There is an incompatibility in PHP with mysql_real_escape_string() which
| 				 can make your site vulnerable to SQL injection if you are using a
| 				 multi-byte character set and are running versions lower than these.
| 				 Sites using Latin-1 or UTF-8 database character set and collation are unaffected.
|	['swap_pre'] A default table prefix that should be swapped with the dbprefix
|	['autoinit'] Whether or not to automatically initialize the database.
|	['stricton'] TRUE/FALSE - forces 'Strict Mode' connections
|							- good for ensuring strict SQL while developing
|
| The $active_group variable lets you choose which connection group to
| make active.  By default there is only one group (the 'default' group).
|
| The $active_record variables lets you determine whether or not to load
| the active record class
*/

$active_group = 'gastossystema';
$active_record = TRUE;

// base de datos principal de la aplicacion, active record solo sirve aqui
$db['gastossystema']['hostname'] = 'localhost';
$db['gastossystema']['username'] = 'gastossystema';
$db['gastossystema']['password'] = 'gastossystema.1';
$db['gastossystema']['database'] = 'gastossystema';
$db['gastossystema']['dbdriver'] = 'mysql';
$db['gastossystema']['dbprefix'] = ''; // <<--- NO USAR PREFIJO, GROCERY CRUD NO ENCONTRARA LAS TABLAS
$db['gastossystema']['pconnect'] = TRUE;
$db['gastossystema']['db_debug'] = TRUE;
$db['gastossystema']['cache_on'] = FALSE;
$db['gastossystema']['cachedir'] = '';
$db['gastossystema']['char_set'] = 'utf8';
$db['gastossystema']['dbcollat'] = 'utf8_general_ci';
$db['gastossystema']['swap_pre'] = '';
$db['gastossystema']['autoinit'] = FALSE;
$db['gastossystema']['stricton'] = FALSE;

// base de datos de oasis/sybase para datos de productos y ventas
$db['oasis']['hostname'] = 'oasis0';//"dsn=oasis1;uid=dba;pwd=sql";
$db['oasis']['username'] = 'ordendespacho';
$db['oasis']['password'] = 'ordendespacho.1.com';
$db['oasis']['database'] = 'oasis';
$db['oasis']['dbdriver'] = 'odbc';
$db['oasis']['dbprefix'] = 'dbo';
$db['oasis']['pconnect'] = FALSE;
$db['oasis']['db_debug'] = TRUE;
$db['oasis']['cache_on'] = FALSE;
$db['oasis']['cachedir'] = '';
$db['oasis']['char_set'] = 'utf8';
$db['oasis']['dbcollat'] = 'utf8_general_ci';
$db['oasis']['swap_pre'] = '';
$db['oasis']['autoinit'] = FALSE;
$db['oasis']['stricton'] = FALSE;

// base de datos de la nomina vnzla
$db['nominasaint']['hostname'] = 'nomina0';//"dsn=nomina0;uid=dba;pwd=sql";
$db['nominasaint']['username'] = 'dba';
$db['nominasaint']['password'] = 'sql';
$db['nominasaint']['database'] = 'snowden';
$db['nominasaint']['dbdriver'] = 'odbc';
$db['nominasaint']['dbprefix'] = '';
$db['nominasaint']['pconnect'] = FALSE;
$db['nominasaint']['db_debug'] = TRUE;
$db['nominasaint']['cache_on'] = FALSE;
$db['nominasaint']['cachedir'] = '';
$db['nominasaint']['char_set'] = '';
$db['nominasaint']['dbcollat'] = '';
$db['nominasaint']['swap_pre'] = '';
$db['nominasaint']['autoinit'] = FALSE;
$db['nominasaint']['stricton'] = FALSE;

// base de datos del chat
$db['simplexmpp']['hostname'] = '37.10.252.99';
$db['simplexmpp']['username'] = 'simplexmpp';
$db['simplexmpp']['password'] = 'simplexmpp.1.com.ve';
$db['simplexmpp']['database'] = 'simplexmpp';
$db['simplexmpp']['dbdriver'] = 'postgre';
$db['simplexmpp']['dbprefix'] = ''; //blanks means use public , catalogo not use that due xtreme security
$db['simplexmpp']['pconnect'] = FALSE;
$db['simplexmpp']['db_debug'] = TRUE;
$db['simplexmpp']['cache_on'] = FALSE;
$db['simplexmpp']['cachedir'] = '';
$db['simplexmpp']['char_set'] = 'utf8';
$db['simplexmpp']['dbcollat'] = 'utf8_general_ci';
$db['simplexmpp']['swap_pre'] = '';
$db['simplexmpp']['autoinit'] = FALSE;
$db['simplexmpp']['stricton'] = FALSE;

/*

$db['default']['hostname'] = '37.10.252.96';
$db['default']['username'] = 'sistemas';
$db['default']['password'] = 'sistemas.1.ve';
$db['default']['database'] = 'sistemaasistenciaweb';
$db['default']['dbdriver'] = 'postgre';
$db['default']['dbprefix'] = ''; //blanks means use public , catalogo not use that due xtreme security
$db['default']['pconnect'] = FALSE;
$db['default']['db_debug'] = TRUE;
$db['default']['cache_on'] = FALSE;
$db['default']['cachedir'] = '';
$db['default']['char_set'] = 'utf8';
$db['default']['dbcollat'] = 'utf8_general_ci';
$db['default']['swap_pre'] = '';
$db['default']['autoinit'] = FALSE;
$db['default']['stricton'] = FALSE;


*/
/* End of file database.php */
/* Location: ./application/config/database.php */
