<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
commonHead();
require_once('controllers/ServiceController.php');
$serviceObj   =   new ServiceController();
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
	<?php top_header(); ?>
	
	<form name="search_category" action="" method="post">
 		 <table align="center" cellpadding="0" cellspacing="0" border="0" class="list" width="100%">
       
		<tr><td colspan="9"><h2><?php if(isset($process) && $process != '' ) { echo 'View Service - '.$process; } else echo 'View Service'?></h2></td></td></tr>
		<tr><td height="20"></td></tr>
		<tr>
			<td align="center">
				<table align="center" cellpadding="0" cellspacing="0" border="0"  width="75%">
					<tr>
						<td width="15%" align="left"  valign="top"><label>Purpose</label></td>
						<td width="3%" align="center" valign="top">:</td>
						<td align="left" valign="top">
							<?php if(isset($process) && $process != '') echo $process; else echo '-'; ?>
						</td>
					</tr>									
					<tr><td height="20"></td></tr>
					<tr>
						<td align="left"  valign="top"><label>Endpoint</label></td>
						<td align="center" valign="top">:</td>
						<td align="left" valign="top"><?php if(isset($servicePath) && $servicePath != '') echo SITE_PATH.$servicePath;  else echo '-'; ?></td>
					</tr>
					<tr><td height="20"></td></tr>
					<tr>
						<td align="left"  valign="top"><label>Module Name</label></td>
						<td align="center" valign="top">:</td>
						<td align="left" valign="top"><?php if(isset($moduleName) && $moduleName != '' ) echo $moduleName; else echo '-'; ?></td>
					</tr>	
					<tr><td height="20"></td></tr>
					<tr>
						<td align="left"  valign="top"><label>Aspects</label></td>
						<td align="center" valign="top">:</td>
						<td align="left" valign="top"><?php if(isset($aspects) && $aspects != '' ) echo $aspects; else echo '-'; ?></td>
					</tr>										
					<tr><td height="20"></td></tr>
					<tr>
						<td align="left"  valign="top"><label>Authorization</label></td>
						<td align="center" valign="top">:</td>
						<td align="left" valign="top"><?php if(isset($authorization) && $authorization == '1' ) echo 'Yes'; else echo 'No'; ?></td>
					</tr>									
					<tr><td height="20"></td></tr>
					<tr>
						<td align="left"  valign="top"><label>Method</label></td>
						<td align="center" valign="top">:</td>
						<td align="left" valign="top"><?php if(isset($method) && $method != '' ) echo $method; else echo '-'; ?></td>
						<input type="Hidden" id="method" name="method" value="<?php if(isset($method) && $method != '' ) echo $method; else echo '-'; ?>">
					</tr>													
					<tr><td height="20"></td></tr>
					<tr class="inputParamDefault">
						<td align="left"  valign="top"><label>Input Param</label></td>
						<td  align="center" valign="top">:</td>
						<td align="left"  valign="top">
						<div class="param">
							<?php if(isset($inputParam) && $inputParam != '' )  {
								echo '<ul><li>'.str_replace(array("\r","\n\n","\n"),array('',"\n","</li>\n<li>"),trim($inputParam,"\n\r")).'</li></ul>';
							} 
							else echo '-'; ?>
							</div>
							</td>
					</tr>									
					<tr><td height="20"></td></tr>
					<tr class="inputParamMultiple">
						<td align="left"  valign="top"><label>Input Param</label></td>
						<td align="center" valign="top">:</td>
						<td>
							<table cellpadding="5" class="service_list_input_param" cellspacing="0" width="100%" border="1">
								<tr>
									<th width="25%" style="letter-spacing:1px;font-family:trebuchet ms,Arial,Helvetica,sans-serif;border:1px solid #2BACED;color:#494949;">Field Name</th>
									<th width="25%" style="letter-spacing:1px;font-family:trebuchet ms,Arial,Helvetica,sans-serif;border:1px solid #2BACED;color:#494949;">Data</th>
									<th width="50%" style="letter-spacing:1px;font-family:trebuchet ms,Arial,Helvetica,sans-serif;border:1px solid #2BACED;color:#494949;">Description</th>
								</tr>
								<?php for($index = 0;$index < $rowCount;$index++) {?>
								<tr>
									<td style="padding-left:5px;padding-right:5px;text-align:left;height:25px;border:1px solid #2BACED;"><?php if(isset($fieldNameArr[$index])) echo htmlspecialchars($fieldNameArr[$index]); else if(empty($fieldNameArr[$index])) echo "-";?></td>
									<td style="padding-left:5px;padding-right:5px;text-align:left;border:1px solid #2BACED;"><?php if(isset($sampleDataArr[$index]) && !empty($sampleDataArr[$index])) echo htmlspecialchars($sampleDataArr[$index]); else if(empty($sampleDataArr[$index])) echo "-";?></td>
									<td style="padding-left:5px;padding-right:5px;text-align:justify;border:1px solid #2BACED;"><?php if(isset($requiredArr[$index]) && $requiredArr[$index] == 1) { echo "<b>Required: </b>"; } if(isset($explanationArr[$index]) && !empty($explanationArr[$index])) echo $explanationArr[$index]; else if(empty($explanationArr[$index])) echo "-";?></td>
								</tr>
								<?php }?>
							</table>
						</td>
					</tr>
					<tr>
						<td  align="left"  valign="top"><label>Output Param</label></td>
						<td align="center" valign="top">:</td>
						<td align="left" valign="top">
						<div class="param">
						<?php if(isset($outputParam) && $outputParam != '' ) echo '<pre>'.$outputParam.'</pre>'; else echo '-'; ?>
						</div>
						</td>
					</tr>									
					<tr><td height="20"></td></tr>
					 <tr>										
						<td colspan="2">&nbsp;</td>
						<td align="left">
							<a href="ServiceManage?editId=<?php echo $_GET['id'];?>" class="submit" name="Edit" id="Edit" value="Edit" title="Edit" alt="Edit" >Edit </a> &nbsp;&nbsp;
							<a href="ServiceList" class="submit" name="Back" id="Back" value="Back" title="Back" alt="Back">Back </a>
						</td>
					</tr> 
				</table>
			</td>
		</tr>
	</table>
	</form>	
						 
<?php commonFooter(); ?>
</html>
