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
		header("Location:CommonSettings?msg=2");
		$msg            = "Password updated successfully";
		$class          = "alert-success";
		$class_icon          = "fa-check";
		$display        = "block";
	}
	else{
		header("Location:CommonSettings?msg=3");
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
			<div class="col-md-12 col-lg-6">
				<form name="change_password_form" id="change_password_form" action="" method="post">
				<div class="box box-primary">
				<!-- form start -->
						<div class="box-body row">
							<?php if(isset($msg) && $msg != '') { ?><div class="alert  alert-dismissable <?php  echo $class;  ?> col-sm-5  col-xs-10" align="center"><span><i class="fa <?php  echo $class_icon;  ?>"></i> <?php echo $msg;  ?></span></div><?php } ?>
							<div class="form-group col-xs-12">
								<label>Old Password</label>
								<div class="col-sm-6 col-xs-12  no-padding">
									<input type="Password" class="form-control" name="old_password" id="old_password"  value="" >
								</div>
							</div>
							<div class="form-group col-xs-12">
								<label>New Password</label>
								<div class="col-sm-6 col-xs-12  no-padding">
								<input type="Password" class="form-control" name="new_password" id="new_password"  value="" >
								</div>
							</div>
							<div class="form-group col-xs-12">
								<label>Confirm Password</label>
								<div class="col-sm-6 col-xs-12  no-padding">
								<input type="Password" class="form-control" id="confirm_password" name="confirm_password"  value="" >
								</div>
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