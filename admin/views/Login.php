<?php
ob_start();
require_once('includes/CommonIncludes.php');
require_once('controllers/AdminController.php');
$adminLoginObj   =   new AdminController();
if(isset($_SESSION['tuplit_admin_user_name'])){
	header('location:Merchants?cs=1');
	die();
}
$error = '';
if(isset($_POST['admin_login_submit']) && $_POST['admin_login_submit'] == 'LOG IN'){
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
		header('location:Merchants?cs=1');
		die();
	}
	else{
		$error = "Invalid Username or Password";
	}
}
commonHead();
?>
<body onload="fieldfocus('user_name');" class="login_bg">
		<div class="navbar-inner">
			<div class="navbar-user-list">
				<a href="http://www.tuplit.com" target="_blank">USER</a>
				<a href="<?php echo SITE_PATH?>/merchant/" target="_blank">MERCHANT</a>
			</div>
		</div>
		<div style="margin-top:100px;">
			<div class="tuplit_logo">
		        <div class="login-box" id="login-box">
		            	 <!-- <div class="header">Tuplit - Login</div> --> 
		          		<form action="" name="admin_login_form" id="admin_login_form"  method="post">
						
		                <div class="body">
							<div class="header">Log In to Tuplit</div> 
							<div class="login_error_msg_hgt" align="center"> 
							<?php if($error !='') { ?><div class="alert alert-danger alert-dismissable col-xs-10" style="margin:0px"><i class="fa fa-warning"></i>&nbsp;&nbsp;<?php echo $error;?></div><?php  } ?>
		                    </div>
							<div class="login-bg">
							<div class="form-group msg_hgt">
		                       <!-- <label>Username</label> -->
							    <input type="text" name="user_name"  id="user_name" class="form-control" placeholder="Username"/>
		                    </div>
		                    <div class="form-group msg_hgt">
		                       <!-- <label>Password</label> -->
		                        <input type="password" name="password" id="password" class="form-control" placeholder="Password"/>
		                    </div> 
		                    <input type="submit" name="admin_login_submit" id="admin_login_submit" value="LOG IN" title="Log In" class="btn btn-success btn-block">
							</div>
		                <div class="footer">                                                               
		                    
		                    <p><a href="ForgotPassword" title="Reset Password"><!-- <i class="fa fa-lock"></i> -->RESET PASSWORD</a></p>
							<!--<p><a href="CreateAccount" title="Create Account"> <i class="fa fa-lock"></i> CREATE ACCOUNT</a></p>-->
		                    
		                </div>
		                </div>
		            </form>
		        </div>
			</div>
		</div>
<?php commonFooter(); ?>
</html>