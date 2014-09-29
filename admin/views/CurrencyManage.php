<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();

require_once('controllers/CurrencyController.php');
$currencyObj   		=   new CurrencyController();

$code	=	$name	=	$location	=	$id = '';
$status	=	1;

if(isset($_GET['ajax']) && $_GET['ajax'] != ''){
	$result		=	$currencyObj->checkExist($_POST);
	if($result) {		
		if($result[0]->fkLocationId == $_POST['Location']) {
			echo "2";	die();
		} 
		else if($result[0]->Code == $_POST['CurrencyCode']) {
			echo "3"; 	die();
		} 
		else if($result[0]->Currency == $_POST['CurrencyName']) {
			echo "4";	die();
		}
	}
	else 
		echo "1";
	die();
}

$LocationList		=	$currencyObj->getLocationList();
//echo "<pre>"; echo print_r($LocationList); echo "</pre>";
if(isset($_GET['editId']) && $_GET['editId'] != ''){
	$result		=	$currencyObj->getCurrencyDetails($_GET['editId']);
	if($result) {		
		$id			=	$result[0]->id;	
		$location	=	$result[0]->fkLocationId;	
		$code		=	$result[0]->Code;	
		$name		=	$result[0]->Currency;	
		$status		=	$result[0]->Status;	
	}
}

if(isset($_POST)  && !empty($_POST) && $_POST['Currency_id'] != ''){
	$_POST     		= 	unEscapeSpecialCharacters($_POST);
    $_POST     		= 	escapeSpecialCharacters($_POST);
	
	if(isset($_POST['Location']))
		$location	=	$_POST['Location'];
	if(isset($_POST['CurrencyCode']))
		$code		=	$_POST['CurrencyCode'];
	if(isset($_POST['CurrencyName']))
		$name		=	$_POST['CurrencyName'];
	if(isset($_POST['Status']))
		$status		=	$_POST['Status'];
		
	$result			=	$currencyObj->updateCurrencyDetails($_POST);
	if($result)
		header("location:CurrencyList?msg=2");
}
if(isset($_POST)  && !empty($_POST) && $_POST['Currency_id'] == ''){
	$_POST     		= 	unEscapeSpecialCharacters($_POST);
    $_POST     		= 	escapeSpecialCharacters($_POST);
	if(isset($_POST['CurrencyCode']))
		$code		=	$_POST['CurrencyCode'];
	if(isset($_POST['CurrencyName']))
		$name		=	$_POST['CurrencyName'];
	if(isset($_POST['Status']))
		$status		=	$_POST['Status'];
		
	$id				=	$currencyObj->insertCurrencyDetails($_POST);
	if(!empty($id)) {
		$msg 		= 	"Currency added successfully";
		$display	=	"block";
		$class 		= 	"alert-success";
		$class_icon = 	"fa-check";
		$code	=	$name	=	$id = '';
		$status	=	1;
		if(empty($_POST['type']))
			header("location:CurrencyList?cs=1&msg=1");
	} else {		
		$msg 		= 	"Error in adding Currency";
		$display	=	"block";
		$class 		= 	"alert-danger";
		$class_icon = 	"fa-warning";
	}		
}	
commonHead();
?>
<body class="skin-blue" onload="return fieldfocus('CurrencyCode');">
	<?php top_header(); ?>		
	<!-- Content Header (Page header) -->
	<section class="content-header no-padding">
		<div class="col-xs-7">
			<h1><i class="fa <?php if(isset($_GET['editId']) && $_GET['editId'] != '' ) echo "fa-edit "; else echo 'fa-plus-circle ';?>"></i> <?php if(isset($_GET['editId']) && $_GET['editId'] != '' ) echo "Edit "; else echo 'Add ';?>Currency</h1>
		</div>
		<!--<div class="col-sm-5 col-xs-12"><h3><a href="CurrencyList?cs=1" title="Currency List"><i class="fa fa-list"></i></i> Currency List</a></h3></div>-->
	</section>
	 <!-- Main content -->
	<section class="content">		
		<div class="row">
			
			<div class="col-xs-12 col-lg-6"> 
				<div class="box box-primary"> 
					<div  class="alert alert-danger alert-dismissable col-sm-5 col-xs-10 " id="error1" style="display:none;"><i class="fa fa-warning"></i><span id="error2"></span> </div> 
					<?php if(isset($msg) && !empty($msg)) { ?>	
						<div class="alert <?php echo $class; ?> alert-dismissable col-sm-5 col-xs-10 " id="success1"><i class="fa <?php echo $class_icon; ?>"></i> <?php echo $msg; ?></div> 
					<?php } ?>
					
					<!-- left column -->
					<form name="add_currency_form" id="add_currency_form" action="" method="post" onsubmit="return currencyAlreadyExist();">
						<?php if(isset($error_msg) && $error_msg != '')  { ?> <div class="alert <?php echo $class;  ?> alert-dismissable col-sm-5 col-xs-11"><i class="fa <?php echo $class_icon ;  ?>"></i>  <?php echo $error_msg;  ?></div> <?php } ?>
						<input type="Hidden" name="Currency_id" id="Currency_id" value="<?php if(isset($_GET['editId']) && $_GET['editId'] != '' ) echo $_GET['editId'];?>">
						<input type="Hidden" name="ajax" id="ajax" value="">
						<div class="form-group col-xs-12">
							<label>Location</label>
							<select name="Location" id="Location"  class="form-control col-sm-4" <?php if(isset($_GET['editId']) && $_GET['editId'] != '' ) echo "disabled"; ?>>
								<option value="">Select</option>
								<?php if(isset($LocationList) && count($LocationList) > 0) { 
										foreach($LocationList as $val) { 
											if(isset($_GET['editId']) && $_GET['editId'] != '' ) { ?>
												<option value="<?php echo $val->id; ?>"  <?php if($location == $val->id) echo "selected"; ?>><?php echo ucfirst($val->Location); ?></option>
										<?php	} else {
											if($val->fkCurrencyId == 0 && $val->Status == 1) {	?>
											<option value="<?php echo $val->id; ?>"  <?php if($location == $val->id) echo "selected"; ?>><?php echo ucfirst($val->Location); ?></option>
								<?php } } } } ?>
							</select>
							<?php if(isset($_GET['editId']) && $_GET['editId'] != '' ) { ?>
								<input type="hidden" name="Location" id="Location" value="<?php echo $location; ?>"/>
							<?php } ?>
						</div>
						<div class="form-group col-xs-12">
							<label>Currency Code</label>
							<input type="text" class="form-control" id="CurrencyCode" name="CurrencyCode" maxlength="100" value="<?php echo $code; ?>"/>
						</div>
						<div class="form-group col-xs-12">
							<label>Currency Name</label>
							<input type="text" class="form-control" id="CurrencyName" name="CurrencyName" maxlength="100" value="<?php  echo $name; ?>"/>
						</div>
						<div class="form-group col-xs-12">
							<label class="notification col-xs-6 no-padding">Currency Status</label>
							<div class=" col-xs-12 no-padding">
								<label class="col-xs-3 no-padding">
									<input id="Status" type="Radio" name="Status" value="1" <?php if(isset($status) && $status == 1 ) echo 'checked'; else echo 'checked'; ?>>&nbsp;&nbsp;Active
								</label>
								<label class="col-xs-5 no-padding">
									<input id="Status" type="Radio" name="Status" value="2" <?php if(isset($status) && $status == 0) echo 'checked'; ?>>&nbsp;&nbsp;Inactive
								</label>
							</div>
						</div>
						<input type="hidden" name="type" id="type" value=""/>
						<div class="box-footer col-xs-12" align="center">
							<?php if(isset($_GET['editId']) && $_GET['editId'] != ''){ ?>
								<input type="submit" class="btn btn-success" name="Save" id="Save" value="Save" title="Save" alt="Save">&nbsp;&nbsp;&nbsp;&nbsp;
							<?php } else { ?>
								<input type="submit" class="btn btn-success" name="Add" id="Add" value="Save" title="Save" alt="Save">&nbsp;&nbsp;&nbsp;&nbsp;
								<input type="submit" class="btn btn-success" name="AddNew" id="AddNew" value="Save & Add new" title="Save & Add new" alt="Save & Add new" onclick="return typeSubmit();">&nbsp;&nbsp;&nbsp;&nbsp;
							<?php } ?>
							<a href="CurrencyList<?php if(isset($_GET['editId']) && $_GET['editId'] != '') echo ""; else echo "?cs=1"; ?>" class="btn btn-default" name="Back" id="Back" title="Back" alt="Back" >Back </a>	
						</div>
					</form>	
				</div><!-- /.box -->
			</div>			
		</div><!-- /.row -->
	</section><!-- /.content -->	
<?php commonFooter(); ?>
<script type="text/javascript">
$(document).ready(function() {
	 $('#CurrencyCode').keyup(function() {
        $(this).val($(this).val().toUpperCase());
    });
});
</script>
</html>