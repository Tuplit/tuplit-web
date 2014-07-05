<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
require_once('controllers/AdminController.php');
$adminLoginObj   =   new AdminController();
$class =  $msg  = '';
$display = 'none';
$error = $msg = '';
$fields		  =	" * ";
$where		  =	" 1 ";
$user_details = $adminLoginObj->getAdminDetails($fields,$where);
if(isset($user_details) && is_array($user_details) && count($user_details)>0){
	foreach($user_details as $key => $value){
		$user_name 	= 	$value->UserName;
		$email		=	$value->EmailAddress;
		$limit 		=	$value->LocationLimit;
	}
}
if(isset($_POST['general_settings_submit']) && $_POST['general_settings_submit'] != '' )
{	
	$updateString   =   " UserName  = '".$_POST['user_name']."',EmailAddress = '".$_POST['email']."',LocationLimit= '".$_POST['limit']."'";
	$condition      =   " id = 1 ";
	$adminLoginObj->updateAdminDetails($updateString,$condition);
	header('location:GeneralSettings?msg=1');
}
if(isset($_GET['msg']) && $_GET['msg'] != '')
	$msg = "General settings updated successfully";
commonHead(); ?>
<body class="skin-blue">
	<?php top_header(); ?>
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<div class="row col-md-12 col-lg-6"> 
			<h1><i class="fa fa-cog"></i> General Settings</h1>
		</div>
	</section>
	<!-- Main content -->
	<section class="content">
		<div class="row">
		<!-- left column -->
			<div class="col-md-12 col-lg-6">
				<div class="box box-primary">
				<!-- form start -->
					<form name="general_settings_form" id="general_settings_form" action="" method="post">
						<div class="box-body row ">
							<?php if($msg !='') { ?><div class="alert alert-success alert-dismissable col-sm-5 col-xs-10" align="center"><i class="fa fa-check"></i>&nbsp;&nbsp;<?php echo $msg;?></div><?php  } ?>
							<div class="form-group col-xs-12">
								<label>Username</label>
								<div class="col-sm-6 col-xs-12  no-padding">
									<input type="text" readonly="readonly"  class="form-control" name="user_name" id="user_name" value="<?php  if(isset($user_name) && $user_name) echo $user_name  ?>" />
								</div>
							</div>
							<div class="form-group  col-xs-12">
								<label>Email</label>
								<div class="col-sm-6 col-xs-12  no-padding">
									<input type="text" class="form-control" name="email" id="email" value="<?php  if(isset($email) && $email) echo $email  ?>"  >
								</div>
							</div>
							<div class="form-group col-xs-12">
								<label>Location Limit</label>
								<div class="col-sm-3 col-xs-5  no-padding">
									<input type="text" class="form-control" name="limit" id="limit" maxlength="6" onkeypress="return isNumberKey_numbers(event);" value="<?php if(isset($limit)) echo $limit ?>"  >
								</div>
								<span class="help-block LH30">&nbsp;&nbsp;(In kilometer)</span>
							</div>
						</div><!-- /.box-body -->
						<div class="box-footer" align="center">
							<input type="submit" class="btn btn-success" name="general_settings_submit" id="general_settings_submit" value="Submit" title="Submit" >
						</div>
					</form>
				</div><!-- /.box -->
			</div>
		</div><!-- /.row -->
	</section><!-- /.content -->	
<?php commonFooter(); ?>
</html>