<?php
ob_start();
require_once('includes/CommonIncludes.php');
require_once('controllers/AdminController.php');
$adminLoginObj   =   new AdminController();
if(isset($_SESSION['tuplit_admin_user_name'])){
	header('location:UserList?cs=1');
	die();
}
$error = '';
if(isset($_POST['admin_login_submit']) && $_POST['admin_login_submit'] == 'Submit'){
	$_POST          =   unEscapeSpecialCharacters($_POST);
   	$_POST          =   escapeSpecialCharacters($_POST);
    $md5Pass        =   $_POST['password'];	
    $condition  	=   " UserName = '{$_POST['user_name']}' AND Password = '{$md5Pass}'";
	$result 		=   $adminLoginObj->checkAdminLogin($condition);
	if($result)
    {
		$_SESSION['tuplit_admin_user_id'] 		= $result[0]->id;
		$_SESSION['tuplit_admin_user_name'] 	    = $result[0]->UserName;
		$_SESSION['tuplit_admin_user_email'] 	= $result[0]->EmailAddress;
		$fields     = " LastLoginDate = '".date('Y-m-d H:i:s')."'";
		$condition  = " Id = ".$result[0]->id;
		$result     =   $adminLoginObj->updateAdminDetails($fields,$condition);
		header('location:UserList?cs=1');
		die();
	}
	else{
		$error = "Invalid Username or Password";
	}
}
commonHead();
?>
<body onload="fieldfocus('user_name');">
        <div class="form-box" id="login-box">
            	<div class="header">Tuplit - Login</div>
          		<form action="" name="admin_login_form" id="admin_login_form"  method="post">
				
                <div class="body bg-gray">
					<div class="login_error_msg_hgt"> 
					<?php if($error !='') { ?><div class="alert alert-danger alert-dismissable col-xs-12" style="margin:0px"><i class="fa fa-warning"></i>&nbsp;&nbsp;<?php echo $error;?></div><?php  } ?>
                    </div>
					<div class="form-group msg_hgt">
                       <label>Username</label>
					    <input type="text" name="user_name"  id="user_name" class="form-control"/>
                    </div>
                    <div class="form-group msg_hgt">
                       <label>Password</label>
                        <input type="password" name="password" id="password" class="form-control" />
                    </div> 
                </div>
                <div class="footer">                                                               
                    <input type="submit" name="admin_login_submit" id="admin_login_submit" value="Submit" title="Submit" class="btn btn-success btn-block">
                    
                    <p><a href="ForgotPassword" title="I forgot my password"><i class="fa fa-lock"></i>&nbsp;&nbsp;I forgot my password</a></p>
                    
                </div>
            </form>
        </div>
<?php commonFooter(); ?>
</html>