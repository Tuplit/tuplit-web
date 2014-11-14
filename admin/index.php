<?php ob_start();
/*temp measure to solve for session problem*/
$dir = sys_get_temp_dir();
session_save_path($dir);
//ERROR_REPORTING(E_ALL);
if ((isset($_GET['page'])) && ($_GET['page'] != '') ){
	if(file_exists('views/'.$_GET['page'].'.php')){	
		require_once('views/'.$_GET['page'].'.php');
	}
	else {	
		//echo"<br>see here===============".$_GET['page'];
		header("location:Login");
	}
}else {
	header("location:Login");
}
