<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
require_once('controllers/AdminController.php');
$adminLoginObj   =   new AdminController();
$class =  $msg  = $class_icon = '';
$display = 'none';
if(isset($_POST['change_password_submit']) && $_POST['change_password_submit'] == 'Submit')
{
	$_POST          = unEscapeSpecialCharacters($_POST);
    $_POST          = escapeSpecialCharacters($_POST);
    $md5Pass        =  $_POST['old_password'];
	$condition      =   " id  = '1' AND Password = '{$md5Pass}'";
    $result         =   $adminLoginObj->checkAdminLogin($condition);	
    if($result)
    {
        $updateString   =   " password  = '".$_POST['new_password']."'";
        $condition      =   " id = 1 ";
        $adminLoginObj->updateAdminDetails($updateString,$condition);
		$msg            = "Password updated successfully";
		$class          = "alert-success";
		$class_icon          = "fa-check";
		$display        = "block";
	}
	else{
		$class    = "alert-danger";
		$class_icon          = "fa-warning";
		$display  = "block";
		$msg      = "Invalid Old Password";
	}
}
commonHead(); ?>
<body class="skin-blue">
<?php top_header(); ?>
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<div class="row col-sm-12"> 
			<h1><i class="fa fa-key"></i> Change Password</h1>
		</div>
	</section>
	<!-- Main content -->
	<section class="content">
		<div class="row">
		<!-- left column -->
			<div class="col-md-6">
				<form name="change_password_form" id="change_password_form" action="" method="post">
				<div class="box box-primary">
				<!-- form start -->
						<div class="box-body">
							<?php if(isset($msg) && $msg != '') { ?><div class="alert  alert-dismissable <?php  echo $class;  ?> col-sm-5"><span><i class="fa <?php  echo $class_icon;  ?>"></i> <?php echo $msg;  ?></span></div><?php } ?>
							<div class="form-group">
								<label>Old Password</label>
								<input type="Password" class="form-control" name="old_password" id="old_password"  value="" >
							</div>
							<div class="form-group">
								<label>New Password</label>
								<input type="Password" class="form-control" name="new_password" id="new_password"  value="" >
							</div>
							<div class="form-group">
								<label>Confirm Password</label>
								<input type="Password" class="form-control" id="confirm_password" name="confirm_password"  value="" >
							</div>
						</div><!-- /.box-body -->
					
						<div class="box-footer" align="center">
							<input type="submit" class="btn btn-success" name="change_password_submit" id="change_password_submit" value="Submit" title="Submit">
						</div>
				</div><!-- /.box -->
				</form>
			</div><!--/.col (left) -->
		</div><!-- /.box -->
	</section><!-- /.content -->	  	
<?php commonFooter(); ?>
</html>