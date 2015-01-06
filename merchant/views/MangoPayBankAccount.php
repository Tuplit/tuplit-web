<?php
require_once('includes/CommonIncludes.php');
//cookies check
merchant_login_check();
$test = 0;
$type =  '';
if(isset($_GET['Type']) && $_GET['Type'] !=''){
	$type 	= 	$_GET['Type'];
}
$merchantId					= 	$_SESSION['merchantInfo']['MerchantId'];
$url						=	WEB_SERVICE.'v1/merchants/connect/'.$merchantId.'?Type='.$type.'';
$curlMerchantResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201 && !empty($curlMerchantResponse['mangopay'])) 
{
	$merchantInfo  			= 	$curlMerchantResponse['mangopay'];
}
$msg_class 					= 	"alert alert-danger alert-dismissable col-xs-12";
$class_icon   				= 	"fa-warning";

/*if(isset($_SESSION['ErrorMessages']) && $_SESSION['ErrorMessages'] !=''){
	$responseMessage 		= 	$_SESSION['ErrorMessages'];
	unset($_SESSION['ErrorMessages']);
}
*/
$error 						= 	'';
if(isset($_POST['mangopay_submit']) && $_POST['mangopay_submit'] == 'SUBMIT'){
	$FirstName = $LastName = $Address = $BankType = $IBAN = $BIC = $BankAccount = $SortCode = $ABA = $BankName = $InstitutionNumber = $BranchCode = $Country = '';
	$address	=	$_POST['Address'];
	$address1	=	str_replace('/n/r','',$address);
	if(isset($_POST['FirstName']) && $_POST['FirstName'] !='')
		$FirstName 			= $_POST['FirstName'];
	if(isset($_POST['LastName']) && $_POST['LastName'] !='')
		$LastName 			= $_POST['LastName'];
	if(isset($_POST['Address']) && $_POST['Address'] !='')
		$Address 			= $_POST['Address'];
	if(isset($_POST['BankType']) && $_POST['BankType'] !='')
		$BankType 			= $_POST['BankType'];
	if(isset($_POST['IBAN']) && $_POST['IBAN'] !='')
		$IBAN 				= $_POST['IBAN'];
	if(isset($_POST['BIC']) && $_POST['BIC'] !='')
		$BIC 				= $_POST['BIC'];
	if(isset($_POST['BankAccount']) && $_POST['BankAccount'] !='')
		$BankAccount 		= $_POST['BankAccount'];
	if(isset($_POST['SortCode']) && $_POST['SortCode'] !='')
		$SortCode 			= $_POST['SortCode'];
	if(isset($_POST['ABA']) && $_POST['ABA'] !='')
		$ABA 				= $_POST['ABA'];
	if(isset($_POST['BankName']) && $_POST['BankName'] !='')
		$BankName 			= $_POST['BankName'];
	if(isset($_POST['InstitutionNumber']) && $_POST['InstitutionNumber'] !='')
		$InstitutionNumber 	= $_POST['InstitutionNumber'];
	if(isset($_POST['BranchCode']) && $_POST['BranchCode'] !='')
		$BranchCode 		= $_POST['BranchCode'];
	if(isset($_POST['Country']) && $_POST['Country'] !='')
		$Country 			= $_POST['Country'];
	$data	=	array(					
					'FirstName' 		=> $FirstName,
					'LastName'	 		=> $LastName,
					'Address' 			=> $Address,
					'BankType' 			=> $BankType,
					'IBAN'				=> $IBAN,
					'BIC'				=> $BIC,
					'AccountNumber'		=> $BankAccount,
					'SortCode'			=> $SortCode,
					'ABA'				=> $ABA,
					'BankName'			=> $BankName,
					'InstitutionNumber'	=> $InstitutionNumber,
					'BranchCode'		=> $BranchCode,
					'Country'			=> $Country,
					'MangoPayId' 		=> base64_decode($_POST['MangopayId'])
				);
				
	$url					=	WEB_SERVICE.'v1/merchants/bankaccount';
	$method					=	'POST';
	$curlResponse			=	curlRequest($url,$method,$data,$_SESSION['merchantInfo']['AccessToken']);
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
if(isset($_POST['transfer_submit']) && $_POST['transfer_submit'] == 'SUBMIT'){
	$data	=	array(					
					'BankAccountId'		=> $_POST['BankAccountId'],
					'Amount'			=> $_POST['Amount'],					
					'MangoPayId' 		=> base64_decode($_POST['MangopayId']),
					'WalletId' 			=> base64_decode($_POST['WalletId'])
				);
	$url					=	WEB_SERVICE.'v1/merchants/banktransfer';
	$method					=	'POST';
	$curlResponse			=	curlRequest($url,$method,$data,$_SESSION['merchantInfo']['AccessToken']);
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
//echo "<pre>"; print_r($curlResponse); echo "</pre>";
commonHead();
?>
<body class="skin-blue fixed body_height popup_bg">
<div class="popup_white">
	<?php if(isset($responseMessage) && $responseMessage != '') { ?>
	<div id="display_msg" style="margin:30px auto 10px auto;" class="<?php echo $msg_class; ?> alert alert-dismissable col-xs-10 col-xs-offset-1"><i class="fa <?php echo $class_icon; ?>"></i>&nbsp;&nbsp;
		<?php echo $responseMessage;?>
	</div>
	<?php  } ?>
	<?php if($type == 1){?>
	<div class="col-xs-12 no-padding" id="mangopay-box">
		<section class="content-header col-xs-12 no-padding">
			<h1 class=" ">Bank Account</h1>
		</section>
		<form action="" name="add_mangopay_bank_account" id="add_mangopay_bank_account" class="add_mangopay_bank_account" method="post">
			<input type="hidden" name="MangopayId"  id="MangopayId" value="<?php if(isset($_GET['MId']) && !empty($_GET['MId'])) echo $_GET['MId']; else echo '';?>">
			<div class="col-xs-12">						
				<div class="form-group  col-xs-12 no-padding LH68">
					<label>First Name</label>
					<input class="form-control" type="text" name="FirstName" id="FirstName" value="<?php if(isset($merchantInfo['FirstName']) && !empty($merchantInfo['FirstName'])) echo $merchantInfo['FirstName'];?>"   required="">
					<span for="FirstName" generated="true" class="error">&nbsp;</span>
				</div>
				<div class="form-group  col-xs-12 no-padding LH68">
					<label>Last Name</label>
					<input class="form-control" type="text" name="LastName"  id="LastName" value="<?php if(isset($merchantInfo['LastName']) && !empty($merchantInfo['LastName'])) echo $merchantInfo['LastName'];?>"  required="">
					<span for="LastName" generated="true" class="error">&nbsp;</span>
				</div>
				<div class="form-group  col-xs-12 no-padding LH68">
					<label>Address</label>
					<input class="form-control" type="text" name="Address"  id="Address" value="<?php if(isset($merchantInfo['Address']) && !empty($merchantInfo['Address'])) echo $merchantInfo['Address'];?>" required="">
					<span for="Address" generated="true" class="error">&nbsp;</span>
				</div>
				<div class="form-group  col-xs-12 no-padding LH68">
					<label>Type</label>
						<div class="custom-select ">
							<select name="BankType" id="BankType" class="form-control" onchange="showBankType(this.value);">
								<option value="Iban" <?php if(isset($BankType) && $BankType == 'Iban') echo 'selected';?>>IBAN</option>
								<option value="Gb" <?php if(isset($BankType) && $BankType == 'Gb') echo 'selected';?>>GB</option>
								<option value="Us" <?php if(isset($BankType) && $BankType == 'Us') echo 'selected';?>>US</option>
								<option value="Ca" <?php if(isset($BankType) && $BankType == 'Ca') echo 'selected';?>>CA</option>
								<option value="Other" <?php if(isset($BankType) && $BankType == 'Other') echo 'selected';?>>OTHER</option>
							</select>
							<span for="BankAccountId" generated="true" class="error">&nbsp;</span>
							<span id="njkj"></span>
						</div>
				</div>
				
				<div id="showType">
					<?php if(isset($_POST['BankType']) && $_POST['BankType'] == 'Iban' ){
					
					?>
						<div class="form-group  col-xs-12 no-padding LH68">
							<label>IBAN</label>
							<input class="form-control" type="text" name="IBAN"  id="IBAN" value="<?php if(isset($IBAN) && !empty($IBAN)) echo $IBAN; else echo "";?>" required="" maxlength="" placeholder="IBAN Format expected">
							<span for="IBAN" generated="true" class="error">&nbsp;</span>
						</div>
						<div class="form-group  col-xs-12 no-padding LH68">
							<label>BIC</label>
							<input class="form-control" type="text" name="BIC"  id="BIC" value="<?php if(isset($BIC) && !empty($BIC)) echo $BIC; else echo "";?>" required="" maxlength="" placeholder="BIC Format expected">
							<span for="BIC" generated="true" class="error">&nbsp;</span>
						</div>
					<?php } ?>
					<?php if(isset($_POST['BankType']) && $_POST['BankType'] == 'Gb' ){?>
						<div class="form-group  col-xs-12 no-padding LH68">
							<label>Account Number</label>
							<input class="form-control" type="text" name="BankAccount"  id="BankAccount" value="<?php if(isset($BankAccount) && !empty($BankAccount)) echo $BankAccount; else echo "";?>" required="" maxlength="8" onkeypress="return isNumberKey(event);">
							<span for="BankAccount" generated="true" class="error">&nbsp;</span>
						</div>
						<div class="form-group  col-xs-12 no-padding LH68">
							<label>SortCode</label>
							<input class="form-control" type="text" name="SortCode"  id="SortCode" value="<?php if(isset($SortCode) && !empty($SortCode)) echo $SortCode; else echo "";?>" required="" maxlength="6" onkeypress="return isNumberKey(event);">
							<span for="SortCode" generated="true" class="error">&nbsp;</span>
						</div>
					<?php } ?>
					<?php if(isset($_POST['BankType']) && $_POST['BankType'] == 'Us' ){
					?>
						<div class="form-group  col-xs-12 no-padding LH68">
							<label>Account Number</label>
							<input class="form-control" type="text" name="BankAccount"  id="BankAccount" value="<?php if(isset($BankAccount) && !empty($BankAccount)) echo $BankAccount; else echo "";?>" required="" maxlength="20" onkeypress="return isNumberKey(event);">
							<span for="BankAccount" generated="true" class="error">&nbsp;</span>
						</div>
						<div class="form-group  col-xs-12 no-padding LH68">
							<label>ABA</label>
							<input class="form-control" type="text" name="ABA"  id="ABA" value="<?php if(isset($ABA) && !empty($ABA)) echo $ABA; else echo "";?>" required="" maxlength="9" onkeypress="return isNumberKey(event);">
							<span for="ABA" generated="true" class="error">&nbsp;</span>
						</div>
				  <?php } ?>
				  <?php if(isset($_POST['BankType']) && $_POST['BankType'] == 'Ca' ){?>
						<div class="form-group  col-xs-12 no-padding LH68">
							<label>Bank Name</label>
							<input class="form-control" type="text" name="BankName"  id="BankName" value="<?php if(isset($BankName) && !empty($BankName)) echo $BankName; else echo "";?>" required="" maxlength="50" onkeypress="">
							<span for="BankName" generated="true" class="error">&nbsp;</span>
						</div>
						<div class="form-group  col-xs-12 no-padding LH68">
							<label>Institution Number</label>
							<input class="form-control" type="text" name="InstitutionNumber"  id="InstitutionNumber" value="<?php if(isset($InstitutionNumber) && !empty($InstitutionNumber)) echo $InstitutionNumber; else echo "";?>" required="" maxlength="4" onkeypress="return isNumberKey(event);">
							<span for="InstitutionNumber" generated="true" class="error">&nbsp;</span>
						</div>
						<div class="form-group  col-xs-12 no-padding LH68">
							<label>Branch Code</label>
							<input class="form-control" type="text" name="BranchCode"  id="BranchCode" value="<?php if(isset($BranchCode) && !empty($BranchCode)) echo $BranchCode; else echo "";?>" required="" maxlength="5" onkeypress="return isNumberKey(event);">
							<span for="BranchCode" generated="true" class="error">&nbsp;</span>
						</div>
						<div class="form-group  col-xs-12 no-padding LH68">
							<label>Account Number</label>
							<input class="form-control" type="text" name="BankAccount"  id="BankAccount" value="<?php if(isset($BankAccount) && !empty($BankAccount)) echo $BankAccount; else echo "";?>" required="" maxlength="20" onkeypress="return isNumberKey(event);">
							<span for="BankAccount" generated="true" class="error">&nbsp;</span>
						</div>
					<?php } ?>
					<?php if(isset($_POST['BankType']) && $_POST['BankType'] == 'Other' ){?>
						<div class="form-group  col-xs-12 no-padding LH68">
							<label>Country</label>
							<input class="form-control" type="text" name="Country"  id="Country" value="<?php if(isset($Country) && !empty($Country)) echo $Country; else echo "";?>" required="" maxlength="" placeholder="ISO 3166-1 alpha-2 format is expected">
							<span for="Country" generated="true" class="error">&nbsp;</span>
						</div>
						<div class="form-group  col-xs-12 no-padding LH68">
							<label>BIC</label>
							<input class="form-control" type="text" name="BIC"  id="BIC" value="<?php if(isset($BIC) && !empty($BIC)) echo $BIC; else echo "";?>" required="" maxlength="" placeholder="BIC Format expected">
							<span for="BIC" generated="true" class="error">&nbsp;</span>
						</div>
						<div class="form-group  col-xs-12 no-padding LH68">
							<label>Account Number</label>
							<input class="form-control" type="text" name="BankAccount"  id="BankAccount" value="<?php if(isset($BankAccount) && !empty($BankAccount)) echo $BankAccount; else echo "";?>" required="" maxlength="20" onkeypress="return isNumberKey(event);">
							<span for="BankAccount" generated="true" class="error">&nbsp;</span>
						</div>
					<?php } ?>
				</div>
			</div>
			<div class="footer col-xs-12 no-padding text-center approve_class"> 
				<input type="hidden" name="MerchantId" id="MerchantId" value="<?php if(isset($merchantInfo['id']) && !empty($merchantInfo['id'])) echo $merchantInfo['id'];?>"/>
				<input type="submit" name="mangopay_submit" id="mangopay_submit" title="SUBMIT" value="SUBMIT" class="btn btn-success col-xs-12" style="color:#fff;">
			</div>
		</form>
	</div>
		<?php } else if($type == 2){?>
		<div class="col-xs-12 no-padding" id="mangopay-box">
			<section class="content-header col-xs-12 no-padding">
				<h1 class=" ">Transfer Money to Bank</h1>
			</section>
			<form action="" name="transfer_to_bank" id="transfer_to_bank"  method="post" >
				<input type="hidden" name="MangopayId"  id="MangopayId" value="<?php if(isset($_GET['MId']) && !empty($_GET['MId'])) echo $_GET['MId']; else echo '';?>">	
				<input type="hidden" name="WalletId"  id="WalletId" value="<?php if(isset($_GET['WalletId']) && !empty($_GET['WalletId'])) echo $_GET['WalletId']; else echo '';?>">
				<div class="col-xs-12">						
					<div class="form-group  col-xs-12 no-padding LH68">
						<label>Account Number</label>
							<div class="custom-select ">
								<select name="BankAccountId" id="BankAccountId" class="form-control">
									<option value="">Select</option>	
									<?php if(isset($merchantInfo) && is_array($merchantInfo) && count($merchantInfo) > 0) {
										foreach($merchantInfo as $key=>$val) {
											if($val['AccountNumber'] != '')
												$bank	=	$val['AccountNumber'];
											else if($val['IBAN'] != '')
												$bank	=	$val['IBAN'];
									?>
									<option value="<?php echo $val['AccountId'];?>"><?php echo $val['OwnerName'].' - '.$bank; ?></option>
									
									<?php } } ?>
								</select>
								<span for="BankAccountId" generated="true" class="error">&nbsp;</span>
								<span id="njkj"></span>
							</div>
					</div>
					<div class="form-group  col-xs-12 no-padding LH68">
						<label>Amount</label>
						<input class="form-control" type="text" name="Amount"  id="Amount" value="" required="" onkeypress="return isNumberKey(event);">
						<span for="Amount" generated="true" class="error">&nbsp;</span>
					</div>
				</div>
				<div class="footer col-xs-12 no-padding text-center approve_class"> 
					<input type="hidden" name="MerchantId" id="MerchantId" value="<?php if(isset($merchantInfo['id']) && !empty($merchantInfo['id'])) echo $merchantInfo['id'];?>"/>
					<input type="submit" name="transfer_submit" id="transfer_submit" title="SUBMIT" value="SUBMIT" class="btn btn-success col-xs-12" style="color:#fff;">
				</div>
			</form>
		</div>
		<?php } ?>
	</div>
	<?php commonFooter(); ?>
</html>
<script type="text/javascript">
<?php if(!isset($BankType ) && $type == 1){?>
showBankType('Iban');
<?php } ?>

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
 
 function informUsers(){
 	if(confirm("Mangopay charges a fee for each withdrawal, for more info see https://www.mangopay.com/pricing/"))
		return true;
	else
		return false;
 }
</script>