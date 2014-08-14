<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
commonHead();
require_once('controllers/ServiceController.php');
$serviceObj   =   new ServiceController();
$type = 'Add Service';
$id_exists 	= 	'';
$rowCount	=	1;
if(isset($_GET['editId']) && $_GET['editId'] != '' ){
	$condition       = " and id = ".$_GET['editId'];
	$field			 = " * ";
	$serviceDetails  = $serviceObj->selectServiceDetails($field,$condition);
	if(isset($serviceDetails) && is_array($serviceDetails) && count($serviceDetails) > 0){
		$process		= $serviceDetails[0]->Process;
		$servicePath	= $serviceDetails[0]->ServicePath;
		$method			= $serviceDetails[0]->Method;
		$inputParam		= $serviceDetails[0]->InputParam;
		$outputParam	= $serviceDetails[0]->OutputParam;
		$moduleName		= $serviceDetails[0]->Module;		
		$authorization	= $serviceDetails[0]->Authorization;		
		$aspects		= $serviceDetails[0]->Aspects;				
		$type = 'Edit Service - '.$process;
	}
	$condition				=	"and ocept.fkEndpointId = ".$_GET['editId'];
	$field					=	"ocept.id as Id,FieldName,SampleData,Required,Explanation";
	$serviceParamsDetails	=	$serviceObj->selectServiceParamsDetails($field,$condition);
	$rowCount				=	count($serviceParamsDetails);
	if(isset($serviceParamsDetails) && is_array($serviceParamsDetails) && count($serviceParamsDetails) > 0) {
		foreach($serviceParamsDetails as $params)
		{
			$paramsIdArr[]		=	$params->Id;
			$fieldNameArr[]		=	$params->FieldName;
			$sampleDataArr[]	=	$params->SampleData;
			$requiredArr[]		=	$params->Required;
			$explanationArr[]	=	$params->Explanation;
		}
	}
}
if(isset($_POST['Add']) || isset($_POST['Save'])){
	$_POST          =   unEscapeSpecialCharacters($_POST);
   	$_POST          =   escapeSpecialCharacters($_POST);
	$fieldNameArr	=	$_POST["field_name"];
	$sampleDataArr	=	$_POST["sample_data"];
	$requiredArr	=	$_POST["required"];
	$explanationArr	=	$_POST["explanation"];
	if(isset($_POST['process']) && $_POST['process'] != '')
		$process  =  $_POST['process'];
	if($process != '')
		$ExistCondition = " and Process = '".$process."'";	
	if(isset($_POST['Save']) && $_POST['Save'] == 'Save')
		$id_exists = " and id != '".$_POST['service_id']."'";
	if(isset($_POST['authorization']) && $_POST['authorization'] != '')
		$authorization  =  $_POST['authorization'];
	else{
		$authorization  =  0;
		$_POST['authorization'] = 0 ;
	}
	if(isset($_POST['aspects']) && $_POST['aspects'] != '')
		$aspects  =  $_POST['aspects'];
	else{
		$aspects  =  '';
		$_POST['aspects'] = '' ;
	}
	$field = " * ";	
	$ExistCondition .= $id_exists;
	$alreadyExist   = $serviceObj->selectServiceDetails($field,$ExistCondition);
	$already_exists = 0;
	if(isset($alreadyExist) && is_array($alreadyExist) && count($alreadyExist) > 0){
		if($alreadyExist[0]->Process == $process)
			$already_exists = 1;
	}
	if($already_exists != '1')	
	{
		if(isset($_POST['Add']) && $_POST['Add'] == 'Add'){
			$insert_id   	= 	$serviceObj->insertServiceDetails($_POST);
			$fkEndpointId	=	$insert_id;
			$msg = '1&cs=1';
		}
		if(isset($_POST['Save']) && $_POST['Save'] == 'Save'){	
			if(isset($_POST['service_id']) && $_POST['service_id'] != ''){
				$fields    = "	Process       = '".$process."',
								ServicePath   = '".$_POST['service_path']."',
								Method		  = '".$_POST['method']."',
								InputParam	  = '".$_POST['input_param']."',
								OutputParam   = '".$_POST['output_param']."',
								Module		  = '".$_POST['module_name']."',
								Aspects		  = '".$_POST['aspects']."',
								Authorization = '".$authorization."'" ;
				$condition = ' id = '.$_POST['service_id'];
				$serviceObj->updateServiceDetails($fields,$condition);
				$fkEndpointId	=	$_GET['editId'];
				$msg = 2;
			}
		}
		$insertParamsValues	=	"";
		for($index = 0;$index < count($fieldNameArr);$index++) {
			if(isset($fieldNameArr[$index]) && !empty($fieldNameArr[$index]))
				$fieldName		=	$fieldNameArr[$index];
			else if(empty($fieldNameArr[$index]))
				continue;
			if(isset($sampleDataArr[$index]))
				$sampleData		=	$sampleDataArr[$index];
			if(isset($requiredArr[$index]))
				$required		=	$requiredArr[$index];
			if(isset($explanationArr[$index]))
				$explanation	=	$explanationArr[$index];
			$insertParamsValues	.=	"('".$fkEndpointId."','".$fieldName."','".$sampleData."','".$required."','".$explanation."'),";
		}
		$insertParamsValues	=	trim($insertParamsValues,",");
		$serviceObj->deleteServiceParamsDetails($fkEndpointId);
		$serviceObj->insertServiceParamsDetails($insertParamsValues);
		header("location:ServiceList?msg=".$msg);
	}
	else {
		$error         = "Purpose Already Exists";
	}
}
?>
<body class="skin-blue" onload="return fieldfocus('process');">
	<?php top_header(); ?>
	<!-- Content Header (Page header) -->
	<section class="content-header no-padding">
		<div class="col-xs-12"> 
			<h1><i class="fa <?php if(isset($_GET['editId']) && $_GET['editId'] != '' ) echo "fa-edit "; else echo 'fa-plus-circle ';?>"></i> <?php if(isset($_GET['editId']) && $_GET['editId'] != '' ) echo "Edit "; else echo 'Add ';?>Service</h1>
		</div>
	</section>
	<!-- Main content -->
	<section class="content">
		<div class="row">
			<form name="add_service_form" id="add_service_form" action="" method="post" >
				<div class="col-md-12"> 
					<div class="box box-primary"> 
						<div class="col-md-12 no-padding">
							<?php if(isset($error) && $error!='') {?> <div class="alert <?php echo $class;  ?> alert-dismissable col-lg-4  col-sm-5  col-xs-11 text-center"><i class="fa <?php echo $class_icon ;  ?>"></i>  <?php echo $error;  ?></div> <?php } ?>
							<input type="Hidden" name="service_id" id="service_id" value="<?php if(isset($_GET['editId']) && $_GET['editId'] != '' ) echo $_GET['editId'];?>">
						</div>
						<div class="form-group col-sm-6">
							<label>Purpose</label>
							<input type="text" tabindex="1" maxlength="250" value="<?php if(isset($process) && $process != '') echo $process;  ?>" id="process" name="process" class="form-control" >
						</div>
						<div class="form-group col-sm-6">
							<label>Endpoint</label>
							<input type="text" tabindex="2" maxlength="100" value="<?php if(isset($servicePath) && $servicePath != '') echo $servicePath;  ?>" id="service_path" name="service_path" class="form-control" >
						</div>
						<div class="form-group col-sm-6 clear">
							<label>Module Name</label>
							<input type="text" tabindex="3" maxlength="100" value="<?php if(isset($moduleName) && $moduleName != '') echo $moduleName;  ?>" id="module_name" name="module_name" class="form-control" >
						</div>
						<div class="form-group col-sm-6">
							<label>Aspects</label>
							<input type="text" tabindex="4" maxlength="100" value="<?php if(isset($aspects) && $aspects != '') echo $aspects;  ?>" id="aspects" name="aspects" class="form-control" >
						</div>
						<div class="form-group col-sm-6 col-xs-12 clear">
							<label class="notification col-xs-6   no-padding">Authorization</label>
							<div class=" col-xs-6 no-padding clear">
								<label class="col-xs-5 no-padding"><input type="Radio" value="1"  class=""  id="authorization"  name="authorization" <?php if(isset($authorization) && $authorization == '1' ) echo 'checked'; ?> > &nbsp;&nbsp;Yes</label>&nbsp;&nbsp;&nbsp;&nbsp;
								<label class="col-xs-5 no-padding"><input type="Radio" value="0" id="authorization" name="authorization" <?php if(isset($authorization) && $authorization == '0') echo 'checked';?> > &nbsp;&nbsp;No</label>
							</div>
						</div>
						
						<div class="form-group col-sm-6">
							<div class="col-xs-6 col-sm-6 col-md-5 no-padding">
								<label>Method</label>
								<div class="form-group col-md-12 col-lg-12 no-padding no-margin">
									<select class="form-control" id="method" name="method">
										<option value="">Select Method</option>
										<?php foreach($methodArray as $value) { ?>
										<option value="<?php echo $value; ?>"<?php if(isset($method) && $method ==$value) { ?>selected<?php } ?>><?php echo $value; ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
						</div>
						<div class="form-group   col-md-6 col-sm-12 clear" id="inputParamDefault">
							<label>Input Param</label>
							<textarea rows="10" cols="45" tabindex="7" class="form-control" id="input_param" name="input_param"><?php if(isset($inputParam) && $inputParam != '' ) echo $inputParam; ?></textarea>
							<p class="help-block">(Separate param with new line)</p>
						</div>
						<div class="form-group col-sm-12 clear" id="inputParamMultiple">
							<label>Input Param</label>
							<table cellpadding="0" cellspacing="7" id="inputParam" border="0" width="100%" align="center" class="table table-bordered no-margin">
								<tr align="center">
									<td width="23%" align="left"><strong>Field Name</strong></td>
									<td width="23%"  align="left"><strong>Data</strong></td>
									<td width="10%" align="left"><strong>Required</strong></td>
									<td width="36%"  align="left"><strong>Description</strong></td>
									<td width="4%"></td>
									<td width="4%"></td>
								</tr>
								<?php for($index = 0;$index < $rowCount;$index++) {?>
								<tr align="center" class="clone" clone="<?php echo $index;?>">
									<td valign="top" align="left"><input type="text" name="field_name[]" class="form-control" tabindex="8" maxlength="100" value="<?php if(isset($fieldNameArr) && is_array($fieldNameArr)) echo htmlspecialchars($fieldNameArr[$index]);?>" ></td>
									<td valign="top" align="left"><input type="text"  name="sample_data[]" class="form-control" tabindex="9" maxlength="100" value="<?php if(isset($sampleDataArr) && is_array($sampleDataArr)) echo htmlspecialchars($sampleDataArr[$index]);?>" ></td>
									<td valign="top" align="left">
										<select name="required[]" tabindex="10"  class="form-control">
											<option value="0" <?php if(isset($requiredArr) && is_array($requiredArr) && $requiredArr[$index] == 0) echo "selected";?>>No</option>
											<option value="1"<?php if(isset($requiredArr) && is_array($requiredArr) && $requiredArr[$index] == 1) echo "selected";?>>Yes</option>
										</select>
									</td>
									<td valign="top" align="left"><textarea rows="2" cols="32" tabindex="11" class="form-control" name="explanation[]"><?php if(isset($explanationArr) && is_array($explanationArr)) echo htmlspecialchars($explanationArr[$index]);?></textarea></td>
									<td class="text-center"><a href="javascript:void(0)" onclick="addRowWeb(this)"><i class="fa fa-plus-circle fa-lg"></i></a></td>
									<td class="text-center"><a href="javascript:void(0)" onclick="delRowWeb(this)"><i class="fa fa-minus-circle fa-lg text-red"></i></a></td>
								</tr>
								<?php }?>
							</table>
						</div>
						<div class="form-group  col-md-6 col-sm-12 clear">
							<label>Output Param</label>
							<textarea rows="10" cols="45" tabindex="12" id="output_param" class="form-control" name="output_param"><?php if(isset($outputParam) && $outputParam != '' ) echo $outputParam;  ?></textarea>
						</div>
						<div class="box-footer  col-xs-12" align="center">
							<?php if(isset($_GET['editId']) && $_GET['editId'] != '' ){ ?>
							<input type="submit" value="Save" id="Save" name="Save" class="btn btn-success" title="Save" alt="Save" tabindex="13">
							<?php } else { ?>
							<input type="submit" value="Add" id="Add" name="Add" class="btn btn-success" title="Add" alt="Add" tabindex="14">
							<?php } ?>
							&nbsp;&nbsp;&nbsp;&nbsp;<a href="ServiceList" class="btn btn-default" name="Back" id="Back" title="Back" alt="Back" >Back</a>	
						</div>						
				</div><!-- /.box -->
			</div><!-- /.col -->
			</form>	
		</div><!-- /.row -->
	</section><!-- /.content -->	
<?php commonFooter(); ?>
</html>
<script type="text/javascript">
	$(document).ready(function() {
		var tabindex = $("#method").attr("tabindex");
		settabindex(+tabindex+1);
		showHideInputParam();
		//For Method Change Event
		$("#method").change(function() {
			showHideInputParam();
		});
	});
</script>