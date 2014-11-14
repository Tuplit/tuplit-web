<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
commonHead();
require_once('controllers/ServiceController.php');
$serviceObj   =   new ServiceController();
$fieldNameArr = $sampleDataArr = $requiredArr = $explanationArr = array();
if(isset($_GET['id']) && $_GET['id'] != '' ){
	$condition       = " and id = ".$_GET['id'];
	$field			 = " * ";
	$serviceDetails  = $serviceObj->selectServiceDetails($field,$condition);
	if(isset($serviceDetails) && is_array($serviceDetails) && count($serviceDetails) > 0) {
		$process		= $serviceDetails[0]->Process;
		$servicePath	= $serviceDetails[0]->ServicePath;
		$method			= $serviceDetails[0]->Method;
		$inputParam		= $serviceDetails[0]->InputParam;
		$outputParam	= $serviceDetails[0]->OutputParam;
		$moduleName		= $serviceDetails[0]->Module;		
		$authorization	= $serviceDetails[0]->Authorization;
		$aspects		= $serviceDetails[0]->Aspects;
	}
	$field					=	"FieldName,SampleData,Required,Explanation";
	$condition				=	" and ocept.fkEndpointId = ".$_GET['id'];
	$rowCount				=	1;
	$serviceParamsDetails	=	$serviceObj->selectServiceParamsDetails($field,$condition);
	$rowCount				=	count($serviceParamsDetails);
	if(isset($serviceParamsDetails) && is_array($serviceParamsDetails) && count($serviceParamsDetails) > 0) {
		foreach($serviceParamsDetails as $params)
		{
			$fieldNameArr[]		=	$params->FieldName;
			$sampleDataArr[]	=	$params->SampleData;
			$requiredArr[]		=	$params->Required;
			$explanationArr[]	=	$params->Explanation;
		}
	}
}

?>
<body class="skin-blue">
	<?php top_header();
	$activeTab = 4;
	require_once('StatisticsTabs.php');
	?>
	
	<!-- Content Header (Page header) -->
	<section class="content-header no-padding">
		<div class="col-xs-12"> 
			<h1><i class="fa fa-search"></i> <?php if(isset($process) && $process != '' ) { echo 'View Service - '.$process; } else echo 'View Service'?></h1>
		</div>
	</section>
	 <!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-md-12 view-page"> 
			 <form name="search_category" action="" method="post">
				<div class="box box-primary box-padding"> 
					<div class="form-group col-sm-6 row">
						<label class=" col-sm-5   col-xs-5   col-md-4" >Purpose</label>
						<div  class="col-sm-7 col-xs-7  col-md-8">
							<?php if(isset($process) && $process != '') echo $process; else echo '-'; ?>
						</div>
					</div>	
					<div class="form-group col-sm-6 row">
						<label class=" col-sm-5   col-xs-5   col-md-4" >Endpoint</label>
						<div  class="col-sm-7 col-xs-7  col-md-8">
							<?php if(isset($servicePath) && $servicePath != '') echo SITE_PATH.$servicePath;  else echo '-'; ?>
						</div>
					</div>	
					<div class="form-group col-sm-6 row">
						<label class=" col-sm-5   col-xs-5   col-md-4" >Module Name</label>
						<div  class="col-sm-7 col-xs-7  col-md-8">
							<?php if(isset($moduleName) && $moduleName != '' ) echo $moduleName; else echo '-'; ?>
						</div>
					</div>						
					<div class="form-group col-sm-6 row">
						<label class=" col-sm-5   col-xs-5   col-md-4" >Aspects</label>
						<div  class="col-sm-7 col-xs-7  col-md-8">
							<?php if(isset($aspects) && $aspects != '' ) echo $aspects; else echo '-'; ?>
						</div>
					</div>	
					<div class="form-group col-sm-6 row">
						<label class=" col-sm-5   col-xs-5   col-md-4" >Authorization</label>
						<div  class="col-sm-7 col-xs-7  col-md-8">
							<?php if(isset($authorization) && $authorization == '1' ) echo 'Yes'; else echo 'No'; ?>
						</div>
					</div>	
					<div class="form-group col-sm-6 row">
						<label class=" col-sm-5   col-xs-5   col-md-4" >Method</label>
						<div  class="col-sm-7 col-xs-7  col-md-8">
							<?php if(isset($method) && $method != '' ) echo $method; else echo '-'; ?>
							<input type="Hidden"  id="method" name="method" value="<?php if(isset($method) && $method != '' ) echo $method; else echo '-'; ?>">
						</div>
					</div>
					<div class="form-group col-sm-6 row" id="inputParamDefault">
						<label class=" col-sm-5   col-xs-5   col-md-4" >Input Param</label>
						<div  class="col-sm-7 col-xs-7  col-md-8">
							<?php if(isset($inputParam) && $inputParam != '' )  {
								echo '<ul><li>'.str_replace(array("\r","\n\n","\n"),array('',"\n","</li>\n<li>"),trim($inputParam,"\n\r")).'</li></ul>';
							} 
							else echo '-'; ?>
						</div>
					</div>										
					<div class="form-group col-sm-6 row" id="inputParamMultiple">
						<label class=" col-sm-5   col-xs-5   col-md-4" >Input Param</label>
						<div  class="col-sm-7 col-xs-7  col-md-8">
							<table class="table table-bordered" border="0" >
								<tr>
									<th width="25%">Field Name</th>
									<th width="25%">Data</th>
									<th width="50%">Description</th>
								</tr>
								<?php if(isset($serviceParamsDetails) && is_array($serviceParamsDetails) && count($serviceParamsDetails) > 0) {
								 for($index = 0;$index < $rowCount;$index++) {?>
								<tr>
									<td><?php if(isset($fieldNameArr[$index])) echo htmlspecialchars($fieldNameArr[$index]); else if(empty($fieldNameArr[$index])) echo "-";?></td>
									<td><?php if(isset($sampleDataArr[$index]) && !empty($sampleDataArr[$index])) echo htmlspecialchars($sampleDataArr[$index]); else if(empty($sampleDataArr[$index])) echo "-";?></td>
									<td><?php 
										if(isset($requiredArr[$index]) && $requiredArr[$index] == 1) 
										{ 
											echo "<b>Required </b>"; 
										} 
										if(isset($explanationArr[$index]) && !empty($explanationArr[$index])) { 
											if($requiredArr[$index] == 1) 
											{ 
												echo "<b> : </b>"; 
											} 
											echo $explanationArr[$index]; 
										} 
										else if($requiredArr[$index] != 1 && empty($explanationArr[$index])) echo "-";?></td>
								</tr>
								<?php } }?>
							</table>
						</div>
					</div>									
					<div class="form-group col-sm-6 row">
						<label class=" col-sm-5   col-xs-5   col-md-4" >Output Param</label>
						<div  class="col-sm-7 col-xs-7  col-md-8">
							<?php if(isset($outputParam) && $outputParam != '' ) echo '<pre>'.$outputParam.'</pre>'; else echo '-'; ?>
						</div>
					</div>	
					<div class="box-footer col-sm-12" align="center">
						<a href="ServiceManage?editId=<?php echo $_GET['id'];?>" title="Edit" alt="Edit" class="btn btn-success">Edit</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<a href="ServiceList" class="btn btn-default" name="Back" id="Back" title="Back" alt="Back" >Back </a>
				</div>
				</div>		
			</div>		
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