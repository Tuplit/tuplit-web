<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
require_once('controllers/LocationController.php');
$locationObj   		=   new LocationController();

$code	=	$name	=	$id = '';
$status	=	1;

if(isset($_GET['ajax']) && $_GET['ajax'] != ''){
	
	$result		=	$locationObj->checkExist($_POST);
	if($result) {
		if($result[0]->Code == $_POST['LocationCode']) {
			echo "2"; 	die();
		} 
		if($result[0]->Location == $_POST['LocationName']) {
			echo "3";	die();
		} 
	}
	else 
		echo "1";
	die();
}

if(isset($_GET['editId']) && $_GET['editId'] != ''){
	$result		=	$locationObj->getLocationDetails($_GET['editId']);
	if($result) {		
		$id		=	$result[0]->id;	
		$code	=	$result[0]->Code;	
		$name	=	$result[0]->Location;	
		$status	=	$result[0]->Status;	
	}
}

if(isset($_POST) && !empty($_POST) && $_POST['location_id'] != ''){
	$_POST     		= 	unEscapeSpecialCharacters($_POST);
    $_POST     		= 	escapeSpecialCharacters($_POST);
	if(isset($_POST['LocationCode']))
		$code		=	$_POST['LocationCode'];
	if(isset($_POST['LocationName']))
		$name		=	$_POST['LocationName'];
	if(isset($_POST['Status']))
		$status		=	$_POST['Status'];
		
	$result			=	$locationObj->updateLocationDetails($_POST);
	if($result)
		header("location:LocationList?msg=2");
}
if(isset($_POST) && !empty($_POST) && $_POST['location_id'] == ''){
	$_POST     		= 	unEscapeSpecialCharacters($_POST);
    $_POST     		= 	escapeSpecialCharacters($_POST);
	if(isset($_POST['LocationCode']))
		$code		=	$_POST['LocationCode'];
	if(isset($_POST['LocationName']))
		$name		=	$_POST['LocationName'];
	if(isset($_POST['Status']))
		$status		=	$_POST['Status'];
		
	$id				=	$locationObj->insertLocationDetails($_POST);
	if(!empty($id)) {
		$msg 		= 	"Location added successfully";
		$display	=	"block";
		$class 		= 	"alert-success";
		$class_icon = 	"fa-check";
		$code		=	$name	=	$id = '';
		$status		=	1;
		if(empty($_POST['type']))
			header("location:LocationList?msg=1");
	} else {		
		$msg 		= 	"Error in adding location";
		$display	=	"block";
		$class 		= 	"alert-danger";
		$class_icon = 	"fa-warning";
	}		
}	
commonHead();
?>
<body class="skin-blue" onload="return fieldfocus('LocationCode');">
	<?php top_header(); ?>		
	<!-- Content Header (Page header) -->
	<section class="content-header no-padding">
		<div class="col-xs-7">
			<h1><i class="fa <?php if(isset($_GET['editId']) && $_GET['editId'] != '' ) echo "fa-edit "; else echo 'fa-plus-circle ';?>"></i> <?php if(isset($_GET['editId']) && $_GET['editId'] != '' ) echo "Edit "; else echo 'Add ';?>Location</h1>
		</div>
		<!--<div class="col-sm-5 col-xs-12"><h3><a href="LocationList?cs=1" title="Location List"><i class="fa fa-list"></i></i> Location List</a></h3></div>-->
	</section>
	 <!-- Main content -->
	<section class="content">		
		<div class="row">
			<div  class="alert alert-danger alert-dismissable col-xs-4 " id="error1" style="display:none;"><i class="fa fa-warning"></i><span id="error2"></span> </div> 
			<?php if(isset($msg) && !empty($msg)) { ?>	
				<div  class="alert <?php echo $class; ?> alert-dismissable col-xs-4 " id="success1"><i class="fa <?php echo $class_icon; ?>"></i> <?php echo $msg; ?></div> 
			<?php } ?>
			<div class="col-md-12 col-lg-6"> 
				<div class="box box-primary"> 
					<!-- left column -->
					<form name="add_location_form" id="add_location_form" action="" method="post" onsubmit="return locationAlreadyExist();">
						<?php if(isset($error_msg) && $error_msg != '')  { ?> <div class="alert <?php echo $class;  ?> alert-dismissable col-sm-5 col-xs-11"><i class="fa <?php echo $class_icon ;  ?>"></i>  <?php echo $error_msg;  ?></div> <?php } ?>
						<input type="Hidden" name="location_id" id="location_id" value="<?php if(isset($_GET['editId']) && $_GET['editId'] != '' ) echo $_GET['editId'];?>">
						<input type="Hidden" name="ajax" id="ajax" value="">
						<div class="form-group col-md-12">
							<label>Location Code</label>
							<input type="text" class="form-control" id="LocationCode" name="LocationCode" maxlength="100" value="<?php echo $code; ?>"/>
						</div>
						<div class="form-group col-md-12">
							<label>Location Name</label>
							<input type="text" class="form-control" id="LocationName" name="LocationName" maxlength="100" value="<?php  echo $name; ?>"/>
						</div>
						<div class="form-group col-md-12">
							<label class="notification col-xs-6 no-padding">Location Status</label>
							<div class=" col-xs-6 no-padding">
								<label class="col-xs-5 no-padding">
									<input id="Status" type="Radio" name="Status" value="1" <?php if(isset($status) && $status == 1 ) echo 'checked'; else echo 'checked'; ?>>Active
								</label>
								<label class="col-xs-5 no-padding">
									<input id="Status" type="Radio" name="Status" value="0" <?php if(isset($status) && $status == 0) echo 'checked'; ?>>Inactive
								</label>
							</div>
						</div>
						<input type="hidden" name="type" id="type" value=""/>
						<div class="box-footer col-md-12" align="center">
							<?php if(isset($_GET['editId']) && $_GET['editId'] != ''){ ?>
								<input type="submit" class="btn btn-success" name="Save" id="Save" value="Save" title="Save" alt="Save">&nbsp;&nbsp;&nbsp;&nbsp;
							<?php } else { ?>
								<input type="submit" class="btn btn-success" name="Add" id="Add" value="Save" title="Save" alt="Save">&nbsp;&nbsp;&nbsp;&nbsp;
								<input type="submit" class="btn btn-success" name="AddNew" id="AddNew" value="Save & Add new" title="Save & Add new" alt="Save & Add new" onclick="return typeSubmit();">&nbsp;&nbsp;&nbsp;&nbsp;
							<?php } ?>
							<a href="LocationList" class="btn btn-default" name="Back" id="Back" title="Back" alt="Back" >Back </a>	
						</div>
					</form>	
				</div><!-- /.box -->
			</div>			
		</div><!-- /.row -->
	</section><!-- /.content -->	
<?php commonFooter(); ?>
<script type="text/javascript">
$(document).ready(function() {
	 $('#LocationCode').keyup(function() {
        $(this).val($(this).val().toUpperCase());
    });
});
</script>
</html>