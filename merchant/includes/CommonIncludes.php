<?php
if(isset($_SERVER['HTTP_ACCEPT_ENCODING']) && substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip'))
ob_start('ob_gzhandler');
else
ob_start();

session_start();
require_once('controllers/Controller.php');
//require_once('models/Model.php');
require_once('config/config.php');
require_once('includes/Templates.php');
require_once('includes/CommonFunctions.php');
?>