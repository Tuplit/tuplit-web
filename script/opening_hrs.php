<?php 

require_once('../admin/config/db_config.php');


error_reporting(E_ALL);
	//session_destroy();
	session_start();
	global $tableName;
	global $fieldnames;
	global $result;
	global $perpage;

$mysql_server 	= HOST_NAME;
$mysql_user 	= USER_NAME;
$mysql_pass 	= PASSWORD;
$mysql_name 	= DATABASE_NAME;
mysql_connect($mysql_server,$mysql_user,$mysql_pass) or die('Error in connecting MySQL');
$db_con = mysql_select_db($mysql_name) or die('Error in selecting databse MySQL');

$sql = "select id from merchants where 1";
$userResult  	= mysql_query($sql);
while($data = mysql_fetch_array($userResult))
{
	echo'<pre>';print_r($data);echo'</pre>';
	for($i=0;$i<=6;$i++) {
		$sql = "insert into merchantshoppinghours set 
							fkMerchantId 	= '".$data['id']."',
							OpeningDay 		= '".$i."',
							Start 			= '',
							End 			= '',
							DateType 		= '0',
							DateCreated 	= '".date('Y-m-d H:i:s')."'
							";
		$result1  	= mysql_query($sql);
		echo"<br>===================>".$sql;
	}
	
}
?>
