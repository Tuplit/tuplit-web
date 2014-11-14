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
	if($result){
		if(isset($_GET['more']) || $_GET['more'] !=''){?>
			<script>
				window.parent.location.href = "CommonSettings?msg=8";
			</script>
<?php		}else{
			header("location:LocationList?msg=2");
		}
	}	
}
if(isset($_POST['Delete']) && $_POST['Delete'] != ''){
	$result = $locationObj->deleteLocation($_GET['editId']);
	if($result){ ?>
			<script>
				window.parent.location.href = "CommonSettings?msg=10";
			</script>
<?php }
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
	//print_r($_POST);
	$id				=	$locationObj->insertLocationDetails($_POST);
	if(!empty($id)) {
		$msg 		= 	"Location added successfully";
		$display	=	"block";
		$class 		= 	"alert-success";
		$class_icon = 	"fa-check";
		$code		=	$name	=	$id = '';
		$status		=	1;
		if(empty($_POST['type'])){
			header("location:LocationList?cs=1&msg=1");
		}
	} else {		
		$msg 		= 	"Error in adding location";
		$display	=	"block";
		$class 		= 	"alert-danger";
		$class_icon = 	"fa-warning";
	}	
}	
popup_head();
?>
<body class="skin-blue fancy-popup" onload="return fieldfocus('LocationCode');">
	<?php if(!isset($_GET['more'])){ top_header(); ?>		
	<!-- Content Header (Page header) -->
	<section class="content-header no-padding">
		<div class="col-xs-7">
			 <h1><i class="fa <?php if(isset($_GET['editId']) && $_GET['editId'] != '' ) echo "fa-edit "; else echo 'fa-plus-circle ';?>"></i> <?php if(isset($_GET['editId']) && $_GET['editId'] != '' ) echo "Edit "; else echo 'Add ';?>Location</h1>
		</div>
		<!--<div class="col-sm-5 col-xs-12"><h3><a href="LocationList?cs=1" title="Location List"><i class="fa fa-list"></i></i> Location List</a></h3></div>-->
	</section>
	<?php }?>
	 <!-- Main content -->
	
		<div class="row">
			
			<div class="col-md-12 col-lg-6"> 
				<div class="box box-primary"> 
					<?php if(isset($_GET['more']) || $_GET['more'] !=''){?>
						<h1 style="color:#000;" align="center"><?php if(isset($_GET['editId']) && $_GET['editId'] != '' ) echo "Edit "; else echo 'Add ';?>Location</h1>
					<?php }?>
					<div  class="alert alert-danger alert-dismissable col-sm-5 col-xs-10 " id="error1" style="display:none;"><i class="fa fa-warning"></i><span id="error2"></span> </div> 
					<?php if(isset($msg) && !empty($msg)) { ?>	
						<div  class="alert <?php echo $class; ?> alert-dismissable col-sm-5 col-xs-10 " id="success1"><i class="fa <?php echo $class_icon; ?>"></i> <?php echo $msg; ?></div> 
					<?php } ?>
					<!-- left column -->
					<form name="add_location_form" id="add_location_form" action="" method="post" onsubmit="return locationAlreadyExist();">
						<?php if(isset($error_msg) && $error_msg != '')  { ?> <div class="alert <?php echo $class;  ?> alert-dismissable col-sm-5 col-xs-11"><i class="fa <?php echo $class_icon ;  ?>"></i>  <?php echo $error_msg;  ?></div> <?php } ?>
						<input type="Hidden" name="location_id" id="location_id" value="<?php if(isset($_GET['editId']) && $_GET['editId'] != '' ) echo $_GET['editId'];?>">
						<input type="Hidden" name="ajax" id="ajax" value="">
						<div class="form-group col-xs-12">
							<label>Location Code</label>
							<input type="text" class="form-control" id="LocationCode" name="LocationCode" maxlength="100" value="<?php echo $code; ?>"/>
						</div>
						<div class="form-group col-xs-12">
							<label>Location Name</label>
							<input type="text" class="form-control capital" id="LocationName" name="LocationName" maxlength="100" value="<?php echo $name; ?>"/> 
							<!-- <label><?php echo $name;?></label> -->
						</div>
						<div class="form-group col-xs-12">
							<label class="notification col-xs-12 no-padding">Location Status</label>
							<div class="col-xs-12 no-padding radio-label">
								<label class="">
									<input id="Status" style="margin-top: 0" type="Radio" name="Status" value="1" <?php if(isset($status) && $status == 1 ) echo 'checked'; else echo 'checked'; ?> style="vertical-align:middle">&nbsp;&nbsp;Active
								</label>
								<label class="">
									<input id="Status" style="margin-top: 0" type="Radio" name="Status" value="2" <?php if(isset($status) && $status == 0) echo 'checked'; ?> style="vertical-align:middle">&nbsp;&nbsp;Inactive
								</label>
							</div>
						</div>
						<input type="hidden" name="type" id="type" value=""/>
						<div>
							<?php if(isset($_GET['editId']) && $_GET['editId'] != ''){ ?>
								<input type="submit" onclick="deleteLocation();" class="btn cancle col-xs-3" name="Delete" id="Delete" value="Delete" title="DELETE" alt="DELETE"> 
								<input type="submit" class="btn btn-success   col-xs-9" name="Save" id="Save" value="Save" title="SAVE" alt="SAVE">
								
							<?php } else { 
										if(!isset($_GET['more']) || $_GET['more'] == ''){?>
								<input type="submit" class="btn btn-success" name="Add" id="Add" value="Save" title="Save" alt="Save">
										<?php }?>
								<input type="submit" class="btn btn-success" name="AddNew" id="AddNew" value="Save & Add new" title="Save & Add new" alt="Save & Add new" onclick="return typeSubmit();">
							<?php }if(!isset($_GET['more']) || $_GET['more'] == '') {?>
							<a href="LocationList" class="btn btn-default" name="Back" id="Back" title="Back" alt="Back" >Back </a>	
							<?php }?>
						</div>
					</form>	
				</div><!-- /.box -->
			</div>			
		</div><!-- /.row -->
	
<?php commonFooter(); ?>
<style type="text/css">
.capital{
	text-transform: capitalize;
}
</style>
<script type="text/javascript">
$(document).ready(function() {
	 $('#LocationCode').keyup(function() {
        $(this).val($(this).val().toUpperCase());
    });
});
function deleteLocation(){
	var delId = $("#location_id").val();
	window.location.href = actionPath+'CommonSettings?locDelId='+delId;
}
</script>
</html>