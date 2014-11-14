<?php
	require_once('includes/CommonIncludes.php');
	merchant_login_check();
	$error =	$merchantInfo = $errorMessage = '';
	$prize_type	= $EmailNotification = 0;
	
	//getting merchant details
	$merchantId				= 	$_SESSION['merchantInfo']['MerchantId'];
	$url					=	WEB_SERVICE.'v1/merchants/'.$merchantId."?From=0";
	$curlMerchantResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201 && $curlMerchantResponse['merchant']['MerchantId'] != '' ) 
		$merchantInfo  = $_SESSION['merchantDetailsInfo']   =	$curlMerchantResponse['merchant'];
	
	//getting merchant products
	$url					=	WEB_SERVICE.'v1/products/';
	$curlMerchantResponse  	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201) {
		$ProductsArray   	=	$curlMerchantResponse['ProductList'];
		if(isset($ProductsArray[0]))
			unset($ProductsArray[0]);
	}
	
	//countries list
	$url						=	WEB_SERVICE.'v1/contents/countries/';
	$curlLocationResponse 		= 	curlRequest($url, 'GET', null);
	if(isset($curlLocationResponse) && is_array($curlLocationResponse) && $curlLocationResponse['meta']['code'] == 200 && is_array($curlLocationResponse['countries']) ) {
		if(isset($curlLocationResponse['countries']))
			$countries 			= 	$curlLocationResponse['countries'];
	} else if(isset($curlLocationResponse['meta']['errorMessage']) && $curlLocationResponse['meta']['errorMessage'] != '')
		$errorMessage			=	$curlLocationResponse['meta']['errorMessage'];
	else
		$errorMessage			= 	"Bad Request for countries";

	/*currencies list
	$url						=	WEB_SERVICE.'v1/contents/currencies/';
	$curlCurrenciesResponse 	= 	curlRequest($url, 'GET', null);
	if(isset($curlCurrenciesResponse) && is_array($curlCurrenciesResponse) && $curlCurrenciesResponse['meta']['code'] == 200 && is_array($curlCurrenciesResponse['currencies']) ) {
		if(isset($curlCurrenciesResponse['currencies']))
			$currencies 		= 	$curlCurrenciesResponse['currencies'];
	} else if(isset($curlCurrenciesResponse['meta']['errorMessage']) && $curlCurrenciesResponse['meta']['errorMessage'] != '')
		$errorMessage			=	$curlCurrenciesResponse['meta']['errorMessage'];
	else
		$errorMessage			= 	"Bad Request for currencies";*/
		
	if(isset($_POST['setting_submit']) && $_POST['setting_submit'] == 'SAVE'){
		
		if(isset($_POST['FirstName']))
			$merchantInfo['FirstName']				=	$_POST['FirstName'];
		if(isset($_POST['LastName']))
			$merchantInfo['LastName']				=	$_POST['LastName'];
		if(isset($_POST['Email']))
			$merchantInfo['Email']					=	$_POST['Email'];
		if(isset($_POST['Email']))
			$merchantInfo['Email']					=	$_POST['Email'];
		if(isset($_POST['PhoneNumber']))
			$merchantInfo['PhoneNumber']			=	$_POST['PhoneNumber'];
		if(isset($_POST['BusinessName']))
			$merchantInfo['BusinessName']			=	$_POST['BusinessName'];
		if(isset($_POST['BusinessType']))
			$merchantInfo['BusinessType']			=	$_POST['BusinessType'];
		/*if(isset($_POST['Currency']) && $_POST['Currency'] != 'k') {
			$merchantInfo['Currency']				=	$currencies[$_POST['Currency']]['Code'];
			$newcurrency							=	$currencies[$_POST['Currency']]['Code'];
		}
		else {
			$newcurrency							=	$merchantInfo['Currency'];
		}*/
		if(isset($_POST['Address']))
			$merchantInfo['Address']				=	$_POST['Address'];
		if(isset($_POST['CompanyName']))
			$merchantInfo['CompanyName']			=	$_POST['CompanyName'];
		if(isset($_POST['CompanyNumber']))
			$merchantInfo['RegisterCompanyNumber']	=	$_POST['CompanyNumber'];
		if(isset($_POST['Country']))
			$merchantInfo['Country']				=	$countries[$_POST['Country']]['Location'];
		if(isset($_POST['PostCode']))
			$merchantInfo['PostCode']				=	$_POST['PostCode'];
		if(isset($_POST['EmailNotification']))
			$merchantInfo['OrderMail']				=	$_POST['EmailNotification'];
		
		if(isset($_POST['DiscountTier']) && !empty($_POST['DiscountTier'])) {
			$merchantInfo['DiscountTier']			=	$discountTierArray[$_POST['DiscountTier']].'%';
		}
					
		/* product price scheme */
		$product_list = '';
		if(isset($_POST['Products_List']) && is_array($_POST['Products_List']) ){		
			if(in_array('all',$_POST['Products_List'])){
				$product_list = 'all';
			}else{
				if(isset($ProductsArray) && is_array($ProductsArray) && count($ProductsArray) > 0){
					foreach($ProductsArray as $key=>$value){
						foreach($value as $s_key=>$s_value){
							$proArray[$s_key] = $s_value['ProductId'];
						}
					}
					$productExists = array_diff($proArray,$_POST['Products_List']);
					if(is_array($productExists) && count($productExists) > 0 ){
						$product_list = implode(',',$_POST['Products_List']);
					}
					else
						$product_list = 'all';
				}
			}
		}
		$merchantInfo['DiscountProductId']  =	$product_list;
		if($product_list != '')
			$prize_type	=	1;
			
		
		$data	=	array(
						'FirstName' 			=> $_POST['FirstName'],
						'LastName' 				=> $_POST['LastName'],
						'Email' 				=> $_POST['Email'],
						'PhoneNumber' 			=> $_POST['PhoneNumber'],
						'BusinessName' 			=> $_POST['BusinessName'],
						'BusinessType' 			=> $_POST['BusinessType'],
						'Currency' 				=> 'GBP',
						'Address'				=> $_POST['Address'],
						'CompanyName' 			=> $_POST['CompanyName'],
						'RegisterCompanyNumber' => $_POST['CompanyNumber'],
						'Country' 				=> $countries[$_POST['Country']]['Location'],
						'PostCode' 				=> $_POST['PostCode'],
						'DiscountTier' 			=> $_POST['DiscountTier'],
						'DiscountProductId'		=> $product_list,
						'DiscountType'			=> $prize_type,
						'OrderMail'				=> $_POST['EmailNotification']
					);
		$url			=	WEB_SERVICE.'v1/merchants/settings';
		$method			=	'PUT';
		//echo json_encode($data); die();
		$curlResponse	=	curlRequest($url,$method,json_encode($data), $_SESSION['merchantInfo']['AccessToken']);
		if(isset($curlResponse) && is_array($curlResponse) && $curlResponse['meta']['code'] == 201) {
			$merchantId					= 	$_SESSION['merchantInfo']['MerchantId'];
			$url						=	WEB_SERVICE.'v1/merchants/'.$merchantId;
			$curlMerchantResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
			if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201 && $curlMerchantResponse['merchant']['MerchantId'] != '' ) 
			{
				$merchantInfo  						= 	$curlMerchantResponse['merchant'];
				$_SESSION['merchantDetailsInfo']	=	$merchantInfo;
			}		
			$successMessage	=	$curlResponse['notifications'][0];
		} else if(isset($curlResponse['meta']['errorMessage']) && $curlResponse['meta']['errorMessage'] != '') {
			$errorMessage		=	$curlResponse['meta']['errorMessage'];
		} else {
			$errorMessage		= 	"Bad Request";
		}
	}
	
	
	if(isset($errorMessage) && $errorMessage != ''){
		$msg				=	$errorMessage;
		$display 			= 	"block";
		$class   			= 	"alert-danger";
		$class_icon 		= 	"fa-warning";
		$errorMessage 		= 	'';
	}else if(isset($successMessage) && $successMessage != ''){
		$msg				=	$successMessage;
		$display			=	"block";
		$class 				= 	"alert-success";
		$class_icon 		= 	"fa-check";
		$successMessage 	= 	'';
	}
	commonHead();
?>

<body class="skin-blue fixed body_height" onload="fieldfocus('CompanyName');">
		<?php top_header(); ?>
		<section class="content">
		<div class="col-lg-12">
			<section class="content-header">
                <h1>Settings</h1>
            </section>
			<?php if(isset($msg) && $msg != '') { ?>
				   <div align="center" class="alert <?php  echo $class;  ?> alert-dismissable col-xs-12 col-sm-5 col-lg-3"><i class="fa <?php  echo $class_icon;  ?>"></i>  <?php echo $msg; ?></div>
			<?php } ?>
			<form action="" name="merchant_setting_form" id="merchant_setting_form"  method="post">
				<div class="col-xs-12 white_bg clear">
					<div class="col-md-6">
						<div class=" box-primary no-padding">
							<div class="box-header col-sm-12">
								<h3 class="box-title">Business Info</h3>
							</div>
							<div class="form-group  col-sm-6">
								<label>First Name</label>
								<input class="form-control" type="text" name="FirstName"  id="FirstName" value="<?php if(isset($merchantInfo['FirstName']) && !empty($merchantInfo['FirstName'])) echo ucfirst($merchantInfo['FirstName']);?>">
							</div>
							<div class="form-group  col-sm-6">
								<label>Last Name</label>
								<input class="form-control" type="text" name="LastName"  id="LastName" value="<?php if(isset($merchantInfo['LastName']) && !empty($merchantInfo['LastName'])) echo ucfirst($merchantInfo['LastName']);?>">
							</div>
							<div class="form-group col-sm-6 col-lg-12">
								<label>Email</label>
								<input class="form-control" type="text" name="Email"  id="Email" value="<?php if(isset($merchantInfo['Email']) && !empty($merchantInfo['Email'])) echo $merchantInfo['Email'];?>">
							</div>
							<div class="form-group col-sm-6  col-lg-12">
								<label>Password (<a href="ChangePassword" class="changePass" >Change Password</a>)</label>
								<input class="form-control" type="text" readonly id="Pass" value="**********">
							</div>
							<div class="form-group col-sm-6  col-lg-12" style="clear:none">
								<label>Mobile Number</label>
								<input class="form-control" type="text" name="PhoneNumber"  id="PhoneNumber" onkeypress="return isNumberKey(event);" maxlength="15" value="<?php if(isset($merchantInfo['PhoneNumber']) && !empty($merchantInfo['PhoneNumber'])) echo $merchantInfo['PhoneNumber'];?>">
							</div>	
							<div class="form-group  col-sm-6">
								<label>Business Name</label>
								<input class="form-control" type="text" name="BusinessName"  id="BusinessName" value="<?php if(isset($merchantInfo['BusinessName']) && !empty($merchantInfo['BusinessName'])) echo $merchantInfo['BusinessName'];?>">
							</div>
							<div class="form-group  col-sm-6" style="clear:none">
								<label>Business Type</label>
								<select class="form-control" name="BusinessType" id="BusinessType" />
								<option value="">Select</option>
								<?php foreach($BusinessType as $busi_key=>$busi_type){ ?>
									<option <?php if(isset($merchantInfo['BusinessType']) && $merchantInfo['BusinessType']==$busi_key){ echo 'selected'; } ?> value="<?php echo $busi_key; ?>"><?php echo $busi_type; ?></option>
								<?php } ?>
							</select>
							</div>
							<div class="form-group col-sm-6 col-lg-12">
								<label>Currency</label>
								<!--<select class="form-control" name="Currency1" id="Currency1" disabled/>
									<option value="">Choose Currency</option>
									<?php if(isset($currencies) && !empty($currencies) && count($currencies)>0) { foreach($currencies as $code){ ?>
										<option  value="<?php echo $code['fkLocationId']; ?>" <?php if(isset($merchantInfo['Currency']) && $merchantInfo['Currency'] == $code['Code']) echo "selected"; ?>><?php echo $code['Code']; ?></option>
									<?php } }?>
								</select>
								<input type="hidden" name="Currency" id="Currency" value="<?php if(isset($merchantInfo['Currency']) && !empty($merchantInfo['Currency'])) echo "k"; ?>">-->
								<input class="form-control" type="text" name="Currency" id="Currency" value="GBP" readonly/>
							</div>							
						</div>
					</div>					
					<div class="col-md-6 border-left ">
						<div class=" box-primary no-padding">
							<div class="box-header col-sm-12">
								<h3 class="box-title" style="margin-top:0px;">Company Details</h3>
							</div>
							<div class="form-group col-sm-6">
								<label>Company Name</label>
								<input class="form-control" type="text" name="CompanyName"  id="CompanyName"  placeholder="CompanyName" value="<?php if(isset($merchantInfo['CompanyName']) && !empty($merchantInfo['CompanyName'])) echo $merchantInfo['CompanyName'];?>">
							</div>
							<div class="form-group col-sm-6">
								<label>Registered Number</label>
								<input class="form-control" type="text" onkeypress="return isNumberKey(event);" name="CompanyNumber"  id="CompanyNumber"  placeholder="Registered Company Number" value="<?php if(isset($merchantInfo['RegisterCompanyNumber']) && !empty($merchantInfo['RegisterCompanyNumber'])) echo $merchantInfo['RegisterCompanyNumber'];?>">
							</div>
							<div class="form-group col-sm-6  col-md-12">
								<label>Address</label>
								<textarea class="form-control" id="Address" placeholder="Address" name="Address" cols="5" rows="5" style="height:100px"><?php if(isset($merchantInfo['Address']) && !empty($merchantInfo['Address'])) echo $merchantInfo['Address'];?></textarea>
							</div>
							<div class="form-group col-sm-6">
								<label>Country</label>
								<select class="form-control" name="Country" id="Country" />
									<option value="">Choose Country</option>
									<?php if(isset($countries) && !empty($countries) && count($countries)>0) { foreach($countries as $code){ ?>
										<option value="<?php echo $code['id']; ?>" <?php if(isset($merchantInfo['Country']) && $merchantInfo['Country'] == $code['Location']) echo "selected"; ?>><?php echo ucfirst($code['Location']); ?></option>
									<?php } }?>
								</select>
							</div>
							<div class="form-group col-sm-6">
								<label>Postcode</label>
								<input class="form-control" type="text" name="PostCode"  id="PostCode"  placeholder="Postcode" value="<?php if(isset($merchantInfo['PostCode']) && !empty($merchantInfo['PostCode'])) echo $merchantInfo['PostCode'];?>">
							</div>
						</div>
						<div class=" box-primary no-padding">
							<div class="col-lg-6 col-sm-6">
								<div class=" box-primary no-padding">
									<div class="box-header ">
										<h3 class="box-title">Payment Account</h3>
									</div>
									<?php if(isset($merchantInfo['MangoPayUniqueId']) && $merchantInfo['MangoPayUniqueId']!= ''){?>
									<div class="form-group col-md-12 error_msg_align no-padding">
										<h4 class="box-title text-teal"><strong>Connected with Mango Pay</strong></h4>
									</div>
									<?php } else {?>
									<div class="form-group col-md-12 no-padding error_msg_align ">
										<label class="pad5"></label><a href="MangoPayAccount" class="MangoPay">
										<button type="button" name="MangoPay" id="MangoPay" value="" class="btn bg-olive btn-md ">
											<i class="fa fa-plus"></i> Add Mango Pay Account
										</button></a> 
									</div>
									<?php } ?>
								</div>	
							</div>
							<div class="col-lg-6 col-sm-6">
								<div class=" box-primary no-padding">
									<div class="box-header ">
										<h3 class="box-title">Email Notification Settings</h3>
									</div>
									<div class="form-group col-md-12 no-padding   error_msg_align ">
										<label class="col-xs-7 no-padding mtop">Order Emails</label>
										<div class="col-xs-5 no-padding email_notification">
											
											<input checked="checked" style="display: none;" id="EmailNotification" name="EmailNotification" type="checkbox">
										</div>
										<!-- <div> 
											<label><input type="Radio" name="EmailNotification" id="EmailNotificationOn" value="1" <?php if(isset($merchantInfo['OrderMail']) && $merchantInfo['OrderMail'] == 1 ) echo "checked"; else echo "checked"; ?>>&nbsp;ON</label>&nbsp;&nbsp;
											<label><input type="Radio" name="EmailNotification" id="EmailNotificationOff" value="0" <?php if(isset($merchantInfo['OrderMail']) && $merchantInfo['OrderMail'] == 0 ) echo "checked"; ?>>&nbsp;OFF</label>
										</div> -->
									</div>				
								</div>
							</div> 
						</div>
						<div class="col-sm-12 bottom-space">
							<div class=" box-primary no-padding">
								<div class="box-header ">
									<h3 class="box-title">Discount Scheme</h3>
								</div>
								<div class="form-group col-md-12 no-padding">
									<label class="col-xs-7 no-padding">Select Discount Scheme</label>
									<div class="col-xs-5 no-padding"> 
									<select class="form-control" id="DiscountTier" name="DiscountTier" onclick="selectPrice(this.value,'<?php if(isset($ProductsArray) && count($ProductsArray) > 0) echo "1"; else echo "0"; ?>');">
										<option value="" >Select
										<?php if(isset($discountTierArray) && is_array($discountTierArray) && count($discountTierArray) > 0) {
												foreach($discountTierArray as $key=>$value){
										 ?>
										<option value="<?php echo $key; ?>" <?php if(isset($merchantInfo['DiscountTier']) &&  $merchantInfo['DiscountTier'] == $value.'%' ) echo 'selected';?>><?php echo $value.'%'; ?>
										<?php } } ?>
									</select>
									</div>
								</div>
								<?php 
								$show_opt = 0;
								if(isset($ProductsArray) && is_array($ProductsArray) && count($ProductsArray) > 0 && $show_opt) {?>
									<div class="form-group col-md-12 text-center">OR</div>
									<div class="form-group col-xs-12 no-padding">
										<label class="col-md-7 no-padding">Select the product list or menu to be discounted (30% and the whole menu)</label>
										<div class="col-md-5 no-padding"> 
											 <select multiple class="form-control" id="Products_List" name="Products_List[]" onclick="selectProduct(this.value);"><!-- return getPrice(this); -->
												<option value="all">Select All</option>
												<?php
													   if(isset($merchantInfo['DiscountProductId']) && $merchantInfo['DiscountProductId'] != ''){
															$productListArray = explode(',',$merchantInfo['DiscountProductId']);
														}
																foreach($ProductsArray as $key=>$value){
																	foreach($value as $s_key=>$s_value){
												 ?>										
												<option value="<?php echo $s_value['ProductId']; ?>" <?php if(isset($productListArray) &&  in_array($s_value['ProductId'],$productListArray)) { echo 'selected'; } else if($merchantInfo['DiscountProductId'] == 'all') echo 'selected';?> ><?php echo $s_value['ItemName']; ?></option>
												<?php }  } ?>
											</select>
										</div>
									</div>
								<?php } ?>
							</div>
						</div>	
					</div>
					<!-- <div class="col-lg-3 col-sm-6">
						<div class="box box-primary no-padding">
							<div class="box-header ">
								<h3 class="box-title">Payment Account</h3>
							</div>
							<?php if(isset($merchantInfo['MangoPayUniqueId']) && $merchantInfo['MangoPayUniqueId']!= ''){?>
							<div class="form-group col-md-12 error_msg_align ">
								<h4 class="box-title text-teal no-margin"><strong>Connected with Mango Pay</strong></h4>
							</div>
							<?php } else {?>
							<div class="form-group col-md-12 error_msg_align ">
								<label class="pad5"></label><a href="MangoPayAccount" class="MangoPay">
								<button type="button" name="MangoPay" id="MangoPay" value="" class="btn bg-olive btn-md ">
									<i class="fa fa-plus"></i> Add Mango Pay Account
								</button></a> 
							</div>
							<?php } ?>
						</div>						
					</div> 
					<div class="col-lg-3 col-sm-6">
						<div class="box box-primary no-padding">
							<div class="box-header ">
								<h3 class="box-title">Email Notification Settings</h3>
							</div>
							<div class="form-group col-md-12  error_msg_align ">
								<label class="col-xs-7 no-padding">Order Emails</label>
								<div> 
									<label><input type="Radio" name="EmailNotification" id="EmailNotificationOn" value="1" <?php if(isset($merchantInfo['OrderMail']) && $merchantInfo['OrderMail'] == 1 ) echo "checked"; else echo "checked"; ?>>&nbsp;ON</label>&nbsp;&nbsp;
									<label><input type="Radio" name="EmailNotification" id="EmailNotificationOff" value="0" <?php if(isset($merchantInfo['OrderMail']) && $merchantInfo['OrderMail'] == 0 ) echo "checked"; ?>>&nbsp;OFF</label>
								</div>
							</div>				
						</div>
					</div> -->
					
					
				</div>
			</div>
				<div class="footer col-xs-12" align="center" style="z-index:1000;position:relative;"> 
					<div class="col-sm-6 Rejected_class btn btn-default col-lg-3"> 
						<a  class="btn btn-default  col-xs-12  cancel_button" href="Dashboard">CANCEL</a>
					</div>
					<div class="col-sm-6 col-lg-9 no-padding approve_class save_button">
						<input type="submit" name="setting_submit" id="setting_submit" value="SAVE" class=" btn btn-success cancel_button">
					</div>
	
			</form>
		 </div>
		</section>
		<?php footerLogin(); ?>
	<?php commonFooter(); ?>
	<script type="text/javascript">
	/*$( "#Country" ).change(function() {
		value	=	$('#Country').val();
		$("#Currency1").val(value);
		$("#Currency").val(value);
	});
	$( "#Country" ).keyup(function() {
		value	=	$('#Country').val();
		$("#Currency1").val(value);
		$("#Currency").val(value);
	});	*/
	
	$(document).ready(function() {
		$(".changePass").fancybox({
				width: '380',
				maxWidth: '100%',
				scrolling: 'auto',			
				type: 'iframe',
				fitToView: true,
				autoSize: true
		});
		$(".MangoPay").fancybox({
				width: '320',
				maxWidth: '100%',
				scrolling: 'auto',			
				type: 'iframe',
				fitToView: true,
				autoSize: true,
				afterClose : function() {
										location.reload();
										return;
									}
		});

	});
	</script>
</html>
