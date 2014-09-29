<?php ob_start();
/*temp measure to solve for session problem*/
if($_SERVER['SERVER_ADDR'] !='172.21.4.104')
{
	$dir = sys_get_temp_dir();
	session_save_path($dir);
}
//ERROR_REPORTING(E_ALL);
require_once('includes/CommonIncludes.php');
//die('-----------');
if ((isset($_GET['page_id'])) && ($_GET['page_id'] != '')) {
	if(file_exists('views/'.$_GET['page_id'].'.php')) {
		require_once('views/'.$_GET['page_id'].'.php');
	}
	else if ((isset($_GET['page'])) && ($_GET['page'] != '')) {
		if(file_exists('views/'.$_GET['page'].'.php'))
			require_once('views/'.$_GET['page'].'.php');
		else
			header('Location: Login');
	}
	else
		header('Location: Login');
}
else if ((isset($_GET['page'])) && ($_GET['page'] != '')) {
	if(file_exists('views/'.$_GET['page'].'.php'))
		require_once('views/'.$_GET['page'].'.php');
	else
		header('Location: Login');
}
else
	header('Location: Login');
?>