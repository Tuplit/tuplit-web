<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
require_once('controllers/StatisticsController.php');
$appversionObj  =   new StatisticsController();
$class 			=  	$msg  = '';
$display 		= 	'none';
$error 			= 	$msg = '';
$fields		  	=	" * ";
$where		  	=	" 1 ";
global $device_name_array,$app_type_array;

$success_msg	= ''; // Hides success msg on password change;

if(isset($_GET['delete']) && $_GET['delete'] != '' ) {
	$delete 	= $appversionObj->deleteAppversion($_GET['delete']);
	header("location:Versions?msg=3");
	die();
}

// update App Status
if(isset($_POST['status_update'],$_POST['edit_id']) && $_POST['status_update'] != '') {
	$status_insertid 	= $appversionObj->updateAppversion(escapeSpecialCharacters($_POST),$_POST['edit_id']);
	header("location:Versions?msg=2");
	die();
}
if(isset($_POST['status_save'])){
	$post = escapeSpecialCharacters($_POST);
	$app_exists = $appversionObj->checkAppVersionExists($post['device_type'],$post['status']);
	if(is_array($app_exists) && count($app_exists) > 0 ){
		$status_insertid = $appversionObj->updateAppversion($post,$app_exists[0]->id);
		header("location:Versions?msg=2");
	}
	else{
		$status_insertid = $appversionObj->addAppversion($post);
		header("location:Versions?msg=1");
	}
	die();
}

$app_array = $appversionObj->getAppversionList();

if(isset($_GET['msg']) && $_GET['msg'] == 1){
	$msg 		= 	"App detail added successfully";
	$display	=	"block";
	$class 		= 	"alert-success";
	$class_icon =	"fa-check";
}
else if(isset($_GET['msg']) && $_GET['msg'] == 2){
	$msg 		= 	"App detail updated successfully";
	$display	=	"block";
	$class 		= 	"alert-success";
	$class_icon =	"fa-check";
}
else if(isset($_GET['msg']) && $_GET['msg'] == 3){
	$msg 		= 	"App detail deleted successfully";
	$display	=	"block";
	$class 		= 	"alert-success";
	$class_icon =	"fa-check";
}

//$JS = array('functions.js'); 
commonHead(); 
?>
<body class="skin-blue">
<?php 
	top_header(); 
	$activeTab = 5;
?>
	<!-- Content Header (Page header) -->
	<section class="content-header no-padding">
		<div class="col-xs-6">
			<h1><i class="fa fa-list"></i>App Versions</h1>
		</div>
		<div class="col-xs-6"><h3><a href="#ins" onclick="clear_app('add_appstatus_form'); Show('add_newapp');" title="Add app version"><i class="fa fa-plus-circle"></i>Add app version</a></h3></div>
	</section>
	<!-- Main content -->
	<section class="content">
		<?php require_once('StatisticsTabs.php');?>
		<?php if($msg !='') { ?><div class="alert alert-success alert-dismissable col-sm-5 col-xs-10" align="center"><i class="fa fa-check"></i>&nbsp;&nbsp;<?php echo $msg;?></div><?php  } ?>
		<div class="row">
			<div class="col-xs-12">
				<div class="box">
				    <div class="box-body table-responsive no-padding no-margin">
						<form action="" method="post" id="appstatus_form" name="appstatus_form">
							<table class="table table-hover" id="category_list">
								<tr>
									<th width="10%">Device Name</th>
									<th width="10%">App Type</th>
									<th width="10%" align="center">Version</th>
									<th width="10%" class="text-right">Build</th>									
									<th width="20%" class="text-left">Action</th>
								</tr>
								<?php if(isset($app_array) && is_array($app_array) && count($app_array) > 0 ) { 
									foreach($app_array as $key=>$value){
										$device_name	= (isset($device_name_array[$value->device_type]))?$device_name_array[$value->device_type]:'';
										$device_status	= (isset($app_type_array[$value->app_type]))?$app_type_array[$value->app_type]:'';
										$version		= (trim($value->version)!='')?trim($value->version):'-';
										$build			= (trim($value->build)!='')?trim($value->build):'-';
										$class = ($value->app_type == 1)?"Live":"Beta";
								if(isset($_GET['edit']) && $_GET['edit'] == $value->id) { ?>
								<tr>
									<td>
										<?php echo $device_name;?>
										<input type="hidden" name="edit_id" id="edit_id" value="<?php echo  $_GET['edit'] ;?>" >
									</td>
									<td>
										<select name="status" id="status_edit" title="Select Status" class="form-control">
											<option value="">Select</option>
												<?php if(is_array($app_type_array) && count($app_type_array) > 0) {
													foreach($app_type_array as $s_key => $s_value) {
												?>
											<option value="<?php echo $s_key;?>" <?php if($s_key == $value->app_type) echo "selected";?>><?php echo $s_value;?></option>
											 <?php } } ?>
										</select>
										<div id="status_edit_msg_container" style="display:none;"><div id="status_edit_msg" class="error_msg"></div></div>
									</td>
									<td><input type="text" title="Enter Version" onpaste="return false;" class="inputbox form-control" onkeypress="return isNumberKey_Enter(event);" style="width:80px;margin:auto;" name="device_version" id="device_version_edit" value="<?php echo trim(stripslashes($value->version));?>">
										<div id="device_version_edit_msg_container" style="display:none;"><div id="device_version_edit_msg" class="error_msg"></div></div>
									</td>
									<td><input type="text" title="Enter Build" class="inputbox form-control" onpaste="return false;" onkeypress="return isNumberKey_Enter(event);" style="width:80px;margin:auto;" name="device_build" id="device_build_edit" value="<?php echo trim(stripslashes($value->build));?>">
										<div id="device_build_edit_msg_container" style="display:none;"><div id="device_build_edit_msg" class="error_msg"></div></div>
									</td>	
									<td>
										<div class="button_center">
											<span class="butbor"><a id="ins" name="ins"></a><input class="trans-button" type="button"  onclick="location.href='Versions'" value="Cancel" title="Cancel" style="margin-right:16px;"/></span>
											<span class="butbor save-button"><input onclick="return validateAppStatus('_edit')" type="submit" id="status_update" name="status_update" value="Save" title="Save" class="btn btn-success mR-button " /></span>
										</div>
									</td>
								</tr>
								<?php } else { ?> 
								<tr>
									<td><?php echo $device_name; ?></td>
									<td><span class="<?php echo $class; ?>"><?php echo $device_status; ?></span>&nbsp;</td>
									<td><?php echo $version; ?></td>
									<td><?php echo $build; ?></td>
									<td>
										<a href="Versions?edit=<?php echo $value->id; ?>#e<?php echo $value->id; ?>" title="Edit"><i class="fa fa-edit "></i></a>&nbsp;&nbsp;&nbsp;
										<a href="Versions?delete=<?php echo $value->id; ?>#d<?php echo $value->id; ?>" class="viewUser" title="Delete" onclick="return confirm('Are you sure to delete');">&nbsp;&nbsp;&nbsp;<i class="fa fa-trash-o "></i></a>
									</td>
								</tr>
								<?php } } } ?>
								<input type="hidden" id="errorFlag" name="errorFlag" value="0">
							</table>
							<?php if(isset($app_array) && empty($app_array)) { ?>
							<div class="alert alert-danger alert-dismissable col-sm-5 col-lg-3  col-xs-11"><i class="fa fa-warning"></i>No App details Found</div>
							<div height="10%"></div>
							<?php } ?>
						</form>	
					</div><!-- /.box-body -->
				</div>
			</div>
		</div>	
		<div style="height:10px;"></div>
		<div class="box" style="margin-bottom:15px;">
			<div class="box-body table-responsive no-padding no-margin">
				<form action="Versions"  method="POST" id="add_appstatus_form" name="add_appstatus_form" onsubmit="return validateAppStatus('');">
					<table id="add_newapp" style="display:none;" class="headertable user_table user_actions table">
						<tr>					
						<td width="10%">
							<select class="form-control" name="device_type" id="device_type" title="Select Device" >
								<option value="">Select</option>
									<?php if(is_array($device_name_array) && count($device_name_array) > 0) {
										foreach($device_name_array as $d_key => $d_value) {
									?>
								<option value="<?php echo $d_key;?>"><?php echo $d_value;?></option>
								 <?php } }?>
							</select>
							<div id="device_type_msg_container" style="display:none;"><div id="device_type_msg" class="error_msg" ></div></div>
						</td>
						<td width="10%">
							<select class="form-control" name="status" id="status" title="Select Status">
								<option value="">Select</option>
									<?php if(is_array($app_type_array) && count($app_type_array) > 0) {
										foreach($app_type_array as $s_key => $s_value) {
									?>
								<option value="<?php echo $s_key;?>"><?php echo $s_value;?></option>
									<?php } } ?>
							</select>
							<div id="status_msg_container" style="display:none;"><div id="status_msg" class="error_msg"></div></div>
						</td>
						<td width="10%">
							<input type="text" class="inputbox form-control" onpaste="return false;" title="Enter Version" onkeypress="return isNumberKey_Enter(event);" id="device_version" name="device_version" value="" />
							<div id="device_version_msg_container" style="display:none;"><div id="device_version_msg" class="error_msg"></div></div>
						</td>
						<td width="10%">
							<input type="text" class="inputbox form-control" onpaste="return false;" title="Enter Build"  onkeypress="return isNumberKey_Enter(event);" id="device_build" name="device_build" value=""  />
							<div id="device_build_msg_container" style="display:none;"><div id="device_build_msg" class="error_msg"></div></div>
						</td>
						<td width="20%">
							<span class="borbut"><a id="ins" name="ins"></a><input class="trans-button" type="button" onclick="Cancel('add_newapp')" id="cancel_add" value="Cancel" title="Cancel" style="margin-right:16px;"/></span>
							<span class="borbut save-button"><input type="submit" id="status_save" name="status_save" value="Save" title="Save" class="btn btn-success mR-button" /></span>
						</td>
						</tr>
						<input type="hidden" id="errorFlag" name="errorFlag" value="0">
					</table>
				</form>
			</div>
		</div>
		<div class="add_appversion">
			<div class="col-xs-12 col-sm-6 col-md-6 no-padding"><a class="add-button" href="#ins" onclick="clear_app('add_appstatus_form'); Show('add_newapp');" title="Add app version"><i class="fa fa-plus-circle"></i>Add app version</a></div>
		</div>
		<div class="clear"><br /><br /><br /></div>	
	</section><!-- /.content -->	
<?php commonFooter(); ?>

</html>