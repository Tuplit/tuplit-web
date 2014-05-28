<?php
require_once('includes/CommonIncludes.php');
$error = $msg = '';
require_once('controllers/AdminController.php');
$adminLoginObj   =   new AdminController();
$error = $msg = '';
if(isset($_POST['forget_password_submit']) && $_POST['forget_password_submit'] == 'Submit')
{
	$_POST          = unEscapeSpecialCharacters($_POST);
    $_POST          = escapeSpecialCharacters($_POST);
    $_POST          = unEscapeSpecialCharacters($_POST);
    $_POST          = escapeSpecialCharacters($_POST);
    $condition  	= " EmailAddress = '{$_POST['email']}'";
    $login_result 	= $adminLoginObj->checkAdminLogin($condition);
    if($login_result){		
		$mailContentArray['name'] 		= $login_result[0]->UserName;
		$mailContentArray['toemail'] 	= $login_result[0]->EmailAddress;
		$mailContentArray['password'] 	= $login_result[0]->Password;
		$mailContentArray['subject'] 	= 'Forget Password Mail';
		$mailContentArray['userType']	= 'Admin';
		$mailContentArray['from'] 		= $login_result[0]->EmailAddress;
		$mailContentArray['fileName']	= 'adminForgotPasswordMail.html';
		sendMail($mailContentArray,'3');
		$msg = "Login information has been sent to your mail"; 
	}
	else{
		$error = "Invalid Email Address ";
	}
}
commonHead();?>
<body class="" onload="fieldfocus('email');">

        <div class="form-box" id="login-box">
            	<div class="header">Tuplit - Forgot Password</div>
          		<form action="" class="l_form" name="forget_password_form" id="forget_password_form"  method="post">
                <div class="body bg-gray">
					<div class="login_error_msg_hgt">
						<?php if($error !='') { ?><div class="alert alert-danger alert-dismissable col-xs-12 no-margin" align="center"><i class="fa fa-warning"></i>&nbsp;&nbsp;<?php echo $error;?></div><?php  } ?>
						<?php if($msg !='') { ?><div class="alert alert-success alert-dismissable col-xs-12 no-margin" align="center"><i class="fa fa-check"></i>&nbsp;&nbsp;<?php echo $msg;?></div><?php  } ?>
                	</div> 
					<div class="form-group msg_hgt">
						<label>Email</label>
						<input type="text" class="form-control" name="email" id="email" value="" />
					</div>
                </div>
                <div class="footer">                                                               
                    <input type="submit" class="btn btn-success btn-block" title="Submit" alt="Submit" name="forget_password_submit" id="forget_password_submit" value="Submit" />
                    <p><a href="Login" title="Back to login"><i class="fa fa-reply"></i>&nbsp;&nbsp;Back to login</a></p>
                </div>
            </form>
        </div>
</body>
<?php commonFooter(); ?>
</html>

