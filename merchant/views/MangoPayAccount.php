<?php
require_once('includes/CommonIncludes.php');
//cookies check
merchant_login_check();
$merchantId					= 	$_SESSION['merchantInfo']['MerchantId'];
$url						=	WEB_SERVICE.'v1/merchants/'.$merchantId."?From=0";
$curlMerchantResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201 && $curlMerchantResponse['merchant']['MerchantId'] != '' ) 
 {
	$merchantInfo  			= 	$_SESSION['merchantDetailsInfo']   =	$curlMerchantResponse['merchant'];
}

$msg_class 					= 	"alert alert-danger alert-dismissable col-xs-12";
$class_icon   				= 	"fa-warning";

if(isset($_SESSION['ErrorMessages']) && $_SESSION['ErrorMessages'] !=''){
	$responseMessage 		= 	$_SESSION['ErrorMessages'];
	unset($_SESSION['ErrorMessages']);
}
$error 						= 	'';
$show						=	0;
if(isset($_POST['mangopay_submit']) && $_POST['mangopay_submit'] == 'SUBMIT'){
	$data	=	array(					
					'CompanyName' 		=> $_POST['CompanyName'],
					'Email' 			=> $_POST['Email'],
					'FirstName' 		=> $_POST['FirstName'],
					'LastName'	 		=> $_POST['LastName'],
					'Address' 			=> $_POST['Address'],
					'Birthday' 			=> $_POST['DOB'],
					'Country' 			=> $_POST['Country'],
					'Currency' 			=> $_POST['Currency'],
				);
	$url					=	WEB_SERVICE.'v1/merchants/connect';
	$method					=	'POST';
	$curlResponse			=	curlRequest($url,$method,$data, $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlResponse) && is_array($curlResponse) && $curlResponse['meta']['code'] == 201 ) {
		$responseMessage 	= 	$curlResponse['notifications'][0];
		$msg_class 			= 	"alert alert-success alert-col-xs-4";
		$class_icon 		= 	"fa-check";
		$show				=	1;
	} else if(isset($curlResponse['meta']['errorMessage']) && $curlResponse['meta']['errorMessage'] != '') {
		$responseMessage	=	$curlResponse['meta']['errorMessage'];
	} else {
		$responseMessage 	= 	"Bad Request";
	}
}
commonHead();
?>
<body class="skin-blue fixed" onload="fieldfocus('user_name');">
		<section class="content-header col-xs-12 no-padding">
			<h1 class=" no-margin"><?php if($show == 1)  echo '&nbsp;&nbsp;';  else  echo "Mango Pay"; ?></h1>
		</section>
		
		<div class="col-xs-12 no-padding" id="mangopay-box">
			<?php if(isset($responseMessage) && $responseMessage != '') { ?>
				<div class="<?php echo $msg_class; ?>"><i class="fa <?php echo $class_icon; ?>"></i>&nbsp;&nbsp;
					<?php echo $responseMessage;?>
				</div>
			<?php  } 	if($show == 0) {	?>
			<form action="" name="add_mangopay_account" id="add_mangopay_account"  method="post">
				<div class="col-xs-12 no-padding">						
					<div class="form-group  col-xs-12 no-padding">
						<label>Company Name</label>
						<input class="form-control" type="text" name="CompanyName"  id="CompanyName"  readonly="" value="<?php if(isset($merchantInfo['CompanyName']) && !empty($merchantInfo['CompanyName'])) echo $merchantInfo['CompanyName'];?>">
					</div>
					<div class="form-group  col-xs-12 no-padding">
						<label>Email</label>
						<input class="form-control" type="text" readonly="" name="Email"  id="Email" value="<?php if(isset($merchantInfo['Email']) && !empty($merchantInfo['Email'])) echo $merchantInfo['Email'];?>">
					</div>
					<div class="form-group  col-xs-12 no-padding">
						<label>First Name</label>
						<input class="form-control" type="text" name="FirstName"  id="FirstName" value="<?php if(isset($merchantInfo['FirstName']) && !empty($merchantInfo['FirstName'])) echo $merchantInfo['FirstName'];?>" required="">
					</div>
					<div class="form-group  col-xs-12 no-padding">
						<label>Last Name</label>
						<input class="form-control" type="text" name="LastName"  id="LastName" value="<?php if(isset($merchantInfo['LastName']) && !empty($merchantInfo['LastName'])) echo $merchantInfo['LastName'];?>" required="">
					</div>
					<div class="form-group  col-xs-12 no-padding">
						<label>Birth Date</label>
						<input class="form-control datepicker" type="text" name="DOB"  id="DOB" value="<?php if(isset($_POST['DOB']) && !empty($_POST['DOB'])) echo $_POST['DOB'];?>" required="">
					</div>
					<div class="form-group  col-xs-12 no-padding">
						<label>Address</label>
						<textarea class="form-control" id="Address" name="Address" cols="5"><?php if(isset($merchantInfo['Address']) && !empty($merchantInfo['Address'])) echo $merchantInfo['Address'];?></textarea>
					</div>
					<div class="row">
						<div class="form-group col-xs-6 ">
							<label>Country</label>
							<select name="Country" id="Country" class="form-control col-xs-6">
							<option value="">Select</option>	
							<?php if(isset($countryArray) && !empty($countryArray)) {
								foreach($countryArray as $key=>$val) {
							?>
							<option value="<?php echo $val;?>" <?php if(isset($_POST['Country']) && $_POST['Country'] == $val) echo 'selected';?> ><?php echo ucfirst($val);?></option>
							<?php } }  ?>
							</select>		
						</div>
						<div class="form-group col-xs-6">
							<label>Currency</label>
							<select name="Currency" id="Currency" class="form-control col-xs-6">
								<option value="">Select</option>	
								<?php if(isset($currencyArray) && !empty($currencyArray)) {
									foreach($currencyArray as $key=>$val) {
								?>
								<option value="<?php echo $val;?>" <?php if(isset($_POST['Currency']) && $_POST['Currency'] == $val) echo 'selected';?> ><?php echo ucfirst($val);?></option>
								<?php } }  ?>
							</select>			
						</div>
					</div>
				</div>
				<div class="footer col-xs-12 no-padding text-center">                                                               
					<input type="submit" name="mangopay_submit" id="mangopay_submit" title="SUBMIT" value="SUBMIT" class="btn btn-success col-xs-12">
				</div>
			</form>
			<?php } ?>
		</div>
	<?php commonFooter(); ?>
</html>
<script type="text/javascript">
$(".datepicker").datepicker({
	showButtonPanel	:	true,        
    buttonText		:	'<i class="fa fa-calendar"></i>',
    buttonImageOnly	:	true,
    buttonImage		:	path+'webresources/images/calender.png',
    dateFormat		:	'dd-mm-yy',
	changeMonth		:	true,
	changeYear		:	true,
	hideIfNoPrevNext:	true,
	showWeek		:	true,
	yearRange		:	"c-30:c",
	closeText		:   "Close"
   });
 
</script>