<?php
	require_once('includes/CommonIncludes.php');
	merchant_login_check();
	$error = '';
	$merchantInfo = $errorMessage = '';

	//getting merchant details
	$merchantId				= 	$_SESSION['merchantInfo']['MerchantId'];
	$url					=	WEB_SERVICE.'v1/merchants/'.$merchantId."?From=0";
	$curlMerchantResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201 && $curlMerchantResponse['merchant']['MerchantId'] != '' ) 
	{
		$merchantInfo  = $_SESSION['merchantDetailsInfo']   =	$curlMerchantResponse['merchant'];
	}
	//echo "<pre>"; echo print_r($merchantInfo); echo "</pre>";
	//getting merchant products
	$url					=	WEB_SERVICE.'v1/products/';
	$curlMerchantResponse  	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201) {
		$ProductsArray   	=	$curlMerchantResponse['ProductList'];		
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

<body class="skin-blue fixed" onload="fieldfocus('CompanyName');">
		<?php top_header(); ?>
		<section class="content">
		<div class="col-lg-12">
			<section class="content-header">
                <h1>Settings</h1>
            </section>
			<?php if(isset($msg) && $msg != '') { ?>
				   <div align="center" class="alert <?php  echo $class;  ?> alert-dismissable col-xs-12 col-sm-5 col-lg-3"><i class="fa <?php  echo $class_icon;  ?>"></i>  <?php echo $msg; ?></div>
			<?php } ?>
			<form action="" name="setting_form" id="setting_form"  method="post">
				<div class="row clear">
					<div class="col-md-6">
						<div class="box box-primary no-padding">
							<div class="box-header no-padding">
								<h3 class="box-title">Business Info</h3>
							</div>
							<div class="form-group  col-sm-6 col-md-12">
								<label>First Name</label>
								<input class="form-control" type="text" name="FirstName"  id="FirstName" value="<?php if(isset($merchantInfo['FirstName']) && !empty($merchantInfo['FirstName'])) echo $merchantInfo['FirstName'];?>">
							</div>
							<div class="form-group  col-sm-6 col-md-12">
								<label>Last Name</label>
								<input class="form-control" type="text" name="LastName"  id="LastName" value="<?php if(isset($merchantInfo['LastName']) && !empty($merchantInfo['LastName'])) echo $merchantInfo['LastName'];?>">
							</div>
							<div class="form-group col-sm-6 col-md-12">
								<label>Email</label>
								<input class="form-control" type="text" name="Email"  id="Email" value="<?php if(isset($merchantInfo['Email']) && !empty($merchantInfo['Email'])) echo $merchantInfo['Email'];?>">
							</div>
							<div class="form-group col-sm-6  col-md-12">
								<label>Password (<a href="ChangePassword" class="changePass" >Change Password</a>)</label>
								<input class="form-control" type="text" readonly id="Pass" value="**********">
							</div>
							<div class="form-group col-sm-6  col-md-12">
								<label>Mobile Number</label>
								<input class="form-control" type="text" name="PhoneNumber"  id="PhoneNumber" onkeypress="return isNumberKey(event);" maxlength="15" value="<?php if(isset($merchantInfo['PhoneNumber']) && !empty($merchantInfo['PhoneNumber'])) echo $merchantInfo['PhoneNumber'];?>">
							</div>	
							<div class="form-group  col-sm-6 col-md-12">
								<label>Business Name</label>
								<input class="form-control" type="text" name="BusinessName"  id="BusinessName" value="<?php if(isset($merchantInfo['BusinessName']) && !empty($merchantInfo['BusinessName'])) echo $merchantInfo['BusinessName'];?>">
							</div>
							<div class="form-group  col-sm-6 col-md-12">
								<label>Business Type</label>&nbsp;&nbsp;&nbsp;
								<select class="form-control" name="BusinessType" id="BusinessType" placeholder="Business Type"/>
									<option value="">Select</option>
									<?php if(isset($BusinessType) && !empty($BusinessType)) { 
											foreach($BusinessType as $key=>$val) {
									?>
										<option value="<?php echo $key; ?>"><?php echo $val; ?></option>
									<?php } } ?>	
								</select>
							</div>
							<div class="form-group col-sm-6  col-md-12">
								<label>Currency</label>
								<input class="form-control" placeholder="Currency" type="text" name="Currency"  id="Currency" value="<?php if(isset($merchantInfo['Currency']) && !empty($merchantInfo['Currency'])) echo $merchantInfo['Currency'];?>">
							</div>
							<div class="form-group col-sm-6  col-md-12">
									<label>Address</label>
									<textarea class="form-control" id="Address" placeholder="Address" name="Address" cols="5"><?php if(isset($merchantInfo['Address']) && !empty($merchantInfo['Address'])) echo $merchantInfo['Address'];?></textarea>
								</div>
						</div>
					</div>
					<div class="col-md-6 ">
						<div class="box box-primary no-padding">
							<div class="box-header ">
								<h3 class="box-title">Company Details</h3>
							</div>
							<div class="form-group col-sm-6  col-md-12">
								<label>Company Name</label>
								<input class="form-control" type="text" name="CompanyName"  id="CompanyName"  placeholder="CompanyName" value="">
							</div>
							<div class="form-group col-sm-6  col-md-12">
								<label>Registered Number</label>
								<input class="form-control" type="text" name="CompanyNumber"  id="CompanyNumber"  placeholder="Registered Company Number" value="">
							</div>
							<div class="form-group col-sm-6  col-md-12">
								<label>Country</label>
								<input class="form-control" type="text" name="Country"  id="Country"  placeholder="Country" value="">
							</div>
							<div class="form-group col-sm-6  col-md-12">
								<label>Postcode</label>
								<input class="form-control" type="text" name="Postcode"  id="Postcode"  placeholder="Postcode" value="">
							</div>
						</div>
					</div>
					<div class="col-md-6 col-sm-12">
						<div class="box box-primary no-padding">
							<div class="box-header ">
								<h3 class="box-title">Payment Account</h3>
							</div>
							<div class="form-group col-md-12 error_msg_align ">
								<label class="pad5"></label>
								<button type="button" name="MangoPay" id="MangoPay" value="" class="btn bg-olive btn-md ">
									<i class="fa fa-plus"></i> Add Mango Pay Account
								</button>
							</div>
						</div>
					</div>
					<div class="col-md-6 col-sm-12">
						<div class="box box-primary no-padding">
							<div class="box-header ">
								<h3 class="box-title">Price Scheme</h3>
							</div>
							<div class="form-group col-md-12 ">
								<label class="col-xs-7 no-padding">Select Price Scheme</label>
								<div class="col-xs-5 no-padding""> 
								<select class="form-control" id="DiscountTier" name="DiscountTier" onclick="selectPrice(this.value);">
									<option value="" >Select
									<?php if(isset($discountTierArray) && is_array($discountTierArray) && count($discountTierArray) > 0) {
											foreach($discountTierArray as $key=>$value){
									 ?>
									<option value="<?php echo $key; ?>" <?php if(isset($merchantInfo['DiscountTier']) &&  $merchantInfo['DiscountTier'] == $value.'%' ) echo 'selected';?>><?php echo $value.'%'; ?>
									<?php } } ?>
								</select>
								</div>
							</div>
							<?php if(isset($ProductsArray) && is_array($ProductsArray) && count($ProductsArray) > 0) {?>
								<div class="form-group col-md-12 text-center">OR</div>
								<div class="form-group col-xs-12 ">
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
		</div>
				<div class="footer col-xs-12" align="center"> 
					<input type="submit" name="setting_submit" id="setting_submit" value="SAVE" class="btn btn-success col-xs-3 box-center"><br><br>
				</div>
			</form>
		 </div>
		</section>
		<?php footerLogin(); ?>
	<?php commonFooter(); ?>
</html>
