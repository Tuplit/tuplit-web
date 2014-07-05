<?php
/**
 * MySQL server connection information
 * 
 * This file has configuration information to establish connection to the MySQL server
 *	- hostName = mysql server to connect
 *  - userName = database username to login
 *  - passWord = database password to login
 *  - dataBase = database name
 */
if ($_SERVER['HTTP_HOST'] == '172.21.4.104') { // Local
	define('HOST_NAME','localhost');
	define('USER_NAME','root');
	define('PASSWORD','');
	define('DATABASE_NAME','tuplit');
}
else {  // Main 
	define('HOST_NAME','aabs47mw39sher.cz2nwhtkwsx4.us-west-2.rds.amazonaws.com');
	define('USER_NAME','tupdbuser');
	define('PASSWORD','tu2pd0bu1se4r');
	define('DATABASE_NAME','ebdb');
}
$dbConfig['hostName'] = HOST_NAME;
$dbConfig['userName'] = USER_NAME;
$dbConfig['passWord'] = PASSWORD;
$dbConfig['dataBase'] = DATABASE_NAME;

?>
