<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();
$condition  = '';
$search		= $userSearch = '';
$OrderArr	= array();

global	$ProductVAT;
if((isset($_GET['cs']) && $_GET['cs'] == 1) && (isset($_GET['ajax']) && $_GET['ajax'] == 'true')) {
	unset($_SESSION['CreateOrder']);
	die();
}
if(isset($_GET['cs']) && $_GET['cs'] == 1){
	unset($_SESSION['CreateOrder']);
	unset($_SESSION['orderComplete']);
}
if(isset($_SESSION['SuccessMsg']) && !empty($_SESSION['SuccessMsg'])) {
	$successMessage 		= 	$_SESSION['SuccessMsg'];
	unset($_SESSION['SuccessMsg']);
}
unset($_SESSION['tuplitCreateOrderUser']);
unset($_SESSION['tuplitCreateOrderTotalUser']);
	
//Order submit
if(isset($_POST['OrderTotal']) && !empty($_POST['OrderTotal'])) {
	unset($_SESSION['orderComplete']);
	$orderProductIds = $_POST['OrderProductIds'];
	if(!empty($_POST['OrderProductIds'])) {
		$prodcutids = explode(',',$_POST['OrderProductIds']);
		if(count($prodcutids) >=1)
			$showorder	= 1;
		$alloworderProducts	=	0;
		foreach($prodcutids as $val) {
			if($_POST['quantity'.$val] != 0 && $_POST['quantity'.$val] != '') {
				$alloworderProducts							=	1;
				$orderProducts[$val]['ProductId'] 			= $val;
				$orderProducts[$val]['ProductsQuantity'] 	= $_POST['quantity'.$val];
				$orderProducts[$val]['ItemName'] 			= $_POST['orderItemName'.$val];
				$orderProducts[$val]['imagePath'] 			= $_POST['imagePath'.$val];
				$orderProducts[$val]['ProductsCost'] 		= $_POST['originalprice'.$val];
				$orderProducts[$val]['DiscountPrice'] 		= $_POST['discountprice'.$val];
				$orderProducts[$val]['TotalPrice'] 			= $_POST['originalTotalprice'.$val];
			}
		}
		if($alloworderProducts	==	1) {
			$SubTotal			= $_POST['SubTotal'];
			$OrderTotal			= $_POST['OrderTotal'];
			$VatTotal			= $_POST['VatTotal'];
			$UserId				= $_POST['CurrentUserId'];
			$UsersImagepath		= $_POST['OrderUserImage'];
			$Username			= $_POST['OrderUserName'];
			
			$_SESSION['CreateOrder']['orderProductIds'] 	= $orderProductIds;
			$_SESSION['CreateOrder']['orderProducts'] 		= $orderProducts;
			$_SESSION['CreateOrder']['UserId'] 		  		= $UserId;
			$_SESSION['CreateOrder']['UsersImagepath'] 		= $UsersImagepath;
			$_SESSION['CreateOrder']['Username'] 			= $Username;
			$_SESSION['CreateOrder']['OrderTotal'] 			= $OrderTotal;
			$_SESSION['CreateOrder']['SubTotal']			= $SubTotal;
			$_SESSION['CreateOrder']['VatTotal']			= $VatTotal;
		}
		//unset($_POST);		
	}
}
else {
	if(isset($_SESSION['CreateOrder']['orderProducts']) && !isset($_GET['orderdone'])) {
		$orderProductIds	= $_SESSION['CreateOrder']['orderProductIds'];
		$orderProducts 		= $_SESSION['CreateOrder']['orderProducts'];
		$SubTotal			= $_SESSION['CreateOrder']['SubTotal'];
		$OrderTotal			= $_SESSION['CreateOrder']['OrderTotal'];
		$VatTotal			= $_SESSION['CreateOrder']['VatTotal'];
		$UserId				= $_SESSION['CreateOrder']['UserId'];
		$UsersImagepath		= $_SESSION['CreateOrder']['UsersImagepath'];
		$Username			= $_SESSION['CreateOrder']['Username'];
		$showorder			= 1;
		
	}	
}

//Search Submit
if(isset($_POST['SearchSubmit']) && !empty($_POST['SearchSubmit'])) {
	if(isset($_POST['productsearch'])) {
		$Search 								= $_POST['productsearch'];
		$_SESSION['CreateOrder']['Search'] 		= $Search;
	}
	if(isset($_POST['usersearch'])) {
		$userSearch 							= $_POST['usersearch'];	
		$_SESSION['CreateOrder']['userSearch'] 	= $userSearch;
	}
	unset($_POST);
}
else {
	if(isset($_SESSION['CreateOrder']['userSearch']))
		$userSearch 	= $_SESSION['CreateOrder']['userSearch'];	
	if(isset($_SESSION['CreateOrder']['Search']))
		$Search			= $_SESSION['CreateOrder']['Search'];	
	
}


//checking user balance
if(isset($showorder) && $showorder = 1 && isset($UserId) && !empty($UserId)) {	
	$data	=	array(
					'PaymentAmount' => $OrderTotal,
					'UserId'		=> $UserId
				);	
	$url						=	WEB_SERVICE.'v1/users/checkbalance';
	$curlCategoryResponse 		= 	curlRequest($url, 'POST', $data, $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlCategoryResponse) && is_array($curlCategoryResponse) && $curlCategoryResponse['meta']['code'] == 201 && is_array($curlCategoryResponse['AllowPayment']) ) {
		$AllowPayment 			= 	$curlCategoryResponse['AllowPayment'];
		if($AllowPayment['AllowPayment'] == 1) {
			$totalQuantity 		= 	count($orderProducts);
			$orderProducts 		= 	array_values($orderProducts);
			$CartDetails 		= 	json_encode($orderProducts);
			$data				=	array(
											'UserId'			=> $UserId,
											'TotalItems'		=> $totalQuantity,
											'TotalPrice'		=> $SubTotal,
											'CartDetails'		=> $CartDetails
										);
			$url				=	WEB_SERVICE.'v1/orders/';
			$curlResponse 		= 	curlRequest($url, 'POST', $data, $_SESSION['merchantInfo']['AccessToken']);
			if(isset($curlResponse) && is_array($curlResponse) && $curlResponse['meta']['code'] == 201) {				
				if(isset($curlResponse['notifications'][0])) {
					$_SESSION['SuccessMsg']	=	$curlResponse['notifications'][0];
					//unset($_SESSION['CreateOrder']);
					//header("location:CreateOrder?cs=1");
					header("location:CreateOrder?orderdone=1");
					die();
				}
			} else if(isset($curlResponse['meta']['errorMessage']) && $curlResponse['meta']['errorMessage'] != '') {
				$errorMessage	=	$curlResponse['meta']['errorMessage'];
			} else {
				$errorMessage	= 	"Bad Request";
			}
		}
		else{
				$errorMessage		=  	"This user not having enough balance to accept this order";
			}
	} else if(isset($curlCategoryResponse['meta']['errorMessage']) && $curlCategoryResponse['meta']['errorMessage'] != '') {
		$errorMessage	=	$curlCategoryResponse['meta']['errorMessage'];
	}
}

//getting merchant details
$merchantId					= 	$_SESSION['merchantInfo']['MerchantId'];
$url						=	WEB_SERVICE.'v1/merchants/'.$merchantId."?From=0";
$curlMerchantResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201 && $curlMerchantResponse['merchant']['MerchantId'] != '' ) {
	$merchantInfo  			= 	$_SESSION['merchantDetailsInfo']   =	$curlMerchantResponse['merchant'];
}

if(isset($merchantInfo) && !empty($merchantInfo['DiscountTier']) || $merchantInfo['DiscountTier'] != 0 ) {
}
else {
	$hide	= 	1;	
}
$Latitude	=	$Longitude	=	0;
if(isset($merchantInfo) && !empty($merchantInfo['Latitude'])) {
	$Latitude				=	$merchantInfo['Latitude'];
}
if(isset($merchantInfo) && !empty($merchantInfo['Longitude'])) {
	$Longitude				=	$merchantInfo['Longitude'];
}
if(isset($merchantInfo) && !empty($merchantInfo['ProductVAT'])) {
	$VAT				=	$merchantInfo['ProductVAT'];
	$VATAmount			=	$ProductVAT[$VAT]/100;
}
//getting popular products
$url						=	WEB_SERVICE.'v1/products/popular/';
$curlCategoryResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
if(isset($curlCategoryResponse) && is_array($curlCategoryResponse) && $curlCategoryResponse['meta']['code'] == 201 && is_array($curlCategoryResponse['PopularProducts']) ) {
	$PopularProducts 		= 	$curlCategoryResponse['PopularProducts'];	
}

//getting product List
if(!empty($Search))
	$url1					=	WEB_SERVICE.'v1/products/?Search='.$Search;
else
	$url1					=	WEB_SERVICE.'v1/products/';
$curlCategoryResponse 		= 	curlRequest($url1, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
if(isset($curlCategoryResponse) && is_array($curlCategoryResponse) && $curlCategoryResponse['meta']['code'] == 201 && is_array($curlCategoryResponse['ProductList']) ) {
	$productList 			= 	$curlCategoryResponse['ProductList'];	
}

if(isset($productList) && !empty($productList)) {
	if(isset($productList[0]) && count($productList[0])>0) {
		foreach($productList[0] as $data) {
			if($data['ItemType']	==	2) 
				$dealsArray[]	=	$data;
			if($data['ItemType']	==	3) 
				$specialArray[]	=	$data;
		}
		unset($productList[0]);
	}
}


//getting user List
$userList					=	array();
$TotalUsers					=	0;
if(!empty($userSearch))
	$url2					=	WEB_SERVICE.'v1/users/?Search='.$userSearch.'&Latitude='.$Latitude.'&Longitude='.$Longitude;
else
	$url2					=	WEB_SERVICE.'v1/users/?Latitude='.$Latitude.'&Longitude='.$Longitude;
$curlCategoryResponse 		= 	curlRequest($url2, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
if(isset($curlCategoryResponse) && is_array($curlCategoryResponse) && $curlCategoryResponse['meta']['code'] == 201 && is_array($curlCategoryResponse['userList']) ) {
	$TotalUsers				=	$curlCategoryResponse['meta']['TotalUsers'];					
	$userList 				= 	$curlCategoryResponse['userList'];	
	$_SESSION['tuplitCreateOrderTotalUser']	=	$TotalUsers;
	$_SESSION['tuplitCreateOrderUser']		=	0;
}
//$successMessage = 1;
if(isset($errorMessage) && $errorMessage != ''){
	$msg			=	$errorMessage;
	$display 		= 	"block";
	$class   		= 	"alert-danger";
	$class_icon 	= 	"fa-warning";
	$errorMessage 	= 	'';
}/*else if(isset($successMessage) && $successMessage != ''){
	$msg			=	$successMessage;
	$display		=	"block";
	$class 			= 	"alert-success";
	$class_icon 	= 	"fa-check";
	$successMessage = 	'';
}*/
commonHead();
?>
<!--
<style>
.fancybox-skin {
    background-color:rgba(255,255,255,1.0)
}
</style>-->
<body class="skin-blue fixed outer_cont body_height" onload="fieldfocus('productsearch');">
		<?php top_header();?>
		<section class="content new_order">
		<?php if(empty($merchantInfo['MangoPayUniqueId'])){?>
				<div align="center" class="alert alert-danger alert-dismissable  col-lg-5 col-sm-7  col-md-5 col-xs-12"><i class="fa fa-warning"></i>&nbsp;&nbsp;Please connect with MangoPay in Settings to create orders.</div>
		<?php }
		else{
			 if(isset($msg) && $msg != '') { ?>
			<div align="center" id="showmessage" style="margin-bottom:50px;" class="alert <?php  echo $class;  ?> alert-dismissable  col-xs-10 col-sm-6 col-lg-4"><i class="fa <?php  echo $class_icon;  ?>"></i>  <?php echo $msg; ?></div>
			<?php } ?>		
<form method="post" action="CreateOrder" id="OrderForm" name="OrderForm">
			<div class="product_list" id="Oders_Merchant" style="margin-bottom:15px;margin-top:-25px; <?php if(isset($showorder) && $showorder >0) echo ''; else echo "display:none;"; ?>">
				<div class="box box-primary no-padding">
					<div class="row box-body box-border" style="padding-bottom:0px;">
						<div class="col-xs-12 col-sm-12  col-lg-12  box-center no-padding">
							<div class="col-xs-8 col-sm-8 col-md-8 col-lg-8 no-padding">							
								<h1 style="color:#202020;">Create new order</h1>
							</div>
							<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 text-right print-top" id="orderPrint" style="display:none">
								<a id="printout" class="print_out" onclick="printFunction('1');"><em></em>PRINT</a>
							</div>
						<div class="col-lg-12 col-xs-12 no-padding"><hr style="border-color:#dfdfdf;margin:10px 0px 0px;"></div>
						<div style="background-color:#f2f2f2;float:left;width:100%;">
						<div class="order-prt1 order_minh">
							<div class="order_minh_sub">
								<table width="98%" id="tbl1" align="left" border="0" style="margin-left:15px;line-height:62px;margin-top:10px;">	
								<tr><td>
									<div width="24%"><input type="hidden" id="OrderProductIds" name="OrderProductIds" value="<?php if(isset($orderProductIds) && !empty($orderProductIds) && isset($showorder) && $showorder >0) echo $orderProductIds;?>"></div>
									<?php 
									if(isset($showorder) && $showorder >0) {
											if(isset($orderProducts) && is_array($orderProducts) && count($orderProducts) > 0) {
												foreach($orderProducts as $orderval) {
									?>
									<div id="orderrow<?php  echo $orderval['ProductId']; ?>" class="clear">
											<div class="order-iname no-padding">
												<img width="50" height="50" src="<?php echo $orderval['imagePath']; ?>" alt="">
												  <div class="new_oreder_name"><?php echo $orderval['ItemName']; ?></div>
											</div>
											
										<div class="dotted-line"><span>&nbsp;</span></div>
										<div align="right" class="pull-right col-xs-7 col-sm-4 col-md-3 col-lg-2 order-price">
											<div align="right" class="col-xs-5 col-sm-7 col-md-7 col-lg-7 no-padding">
											<input id="quantity<?php echo $orderval['ProductId']; ?>" onkeyup="return addRemoveQuantity(<?php echo $orderval['ProductId']; ?>,'3');" maxlength="3" type="text" onkeypress="return isNumberKeyQuantity(event);" value="<?php echo $orderval['ProductsQuantity']; ?>" style="width:26px;text-align:center;border:0px solid #fff;" name="quantity<?php echo $orderval['ProductId']; ?>">&nbsp;&nbsp;<i class="fa fa-plus fa-plus-bgcolor" onclick="return addRemoveQuantity(<?php echo $orderval['ProductId']; ?>,'1');"></i><i class="fa fa-minus fa-minus-bgcolor" onclick="return addRemoveQuantity(<?php echo $orderval['ProductId']; ?>,'2');"></i>
											<input id="imagePath<?php echo $orderval['ProductId']; ?>" type="hidden" value="<?php echo $orderval['imagePath']; ?>" name="imagePath<?php echo $orderval['ProductId']; ?>">
										</div>
											<strong><?php echo '&pound;' ?>
											<span id="orderPrice<?php echo $orderval['ProductId']; ?>"><?php echo number_format((float)$orderval['TotalPrice'], 2, '.', ''); ?></span></strong>
											<input id="originalprice<?php echo $orderval['ProductId']; ?>" type="hidden" value="<?php echo $orderval['ProductsCost']; ?>" name="originalprice<?php echo $orderval['ProductId']; ?>">
											<input id="discountprice<?php echo $orderval['ProductId']; ?>" type="hidden" value="<?php echo $orderval['DiscountPrice']; ?>" name="discountprice<?php echo $orderval['ProductId']; ?>">
											<input id="originalTotalprice<?php echo $orderval['ProductId']; ?>" type="hidden" value="<?php echo $orderval['TotalPrice']; ?>" name="originalTotalprice<?php echo $orderval['ProductId']; ?>">
											<input id="orderItemName<?php echo $orderval['ProductId']; ?>" type="hidden" value="<?php echo $orderval['ItemName']; ?>" name="orderItemName<?php echo $orderval['ProductId']; ?>">
										</div>
									</div>
		
									<?php } } } ?>
									</td></tr>						
								</table>
							</div>
						<div class="pull-right col-xs-7 col-sm-4 col-md-3 col-lg-2 subtotal" id="showProductPrice" style="display:none">
							<div class="col-xs-6 col-sm-6 col-md-6 col-lg-7 LH18 no-right-pad" align="left">
								<input type="hidden" id="SubTotal" name="SubTotal" value="<?php if(isset($SubTotal)) echo $SubTotal; ?>">
								<b>SUB TOTAL</b>
							</div>
							<div class="col-xs-6 col-sm-6 col-md-6 col-lg-5 LH18 no-left-pad" align="right">
								<b>&pound; <span class="SubTotalShow"><?php if(isset($SubTotal)) echo $SubTotal; ?></span></b>
							</div>
							<div class="col-xs-5 col-sm-7 col-md-7 col-lg-7 LH18 no-right-pad" align="left">
								<input type="hidden" id="VatTotal" name="VatTotal" value="<?php if(isset($VatTotal)) echo $VatTotal; ?>">
								VAT
							</div>
							<div class="col-xs-7 col-sm-5 col-md-5 col-lg-5 LH18 no-left-pad" align="right">
								&pound; <span class="VatTotalShow"><?php if(isset($VatTotal)) echo $VatTotal; ?></span>
							</div>
						</div>
						</div>
						<div class="order-prt2">
						<table width="100%" id="tbl2" align="right" style="background-color:#f2f2f2;min-height:100%;max-height:100%;height:100%;">
							<tr id="userTable">
								<!-- <td ><a style="cursor:pointer" class="userTotal" onclick="return clearOrders()">Clear Order</a><input type="hidden" id="userDefaultImage" name="userDefaultImage" value="<?php echo MERCHANT_SITE_IMAGE_PATH."no_user.jpeg";?>"/></td> -->
								<td align="center"><img width="50" height="50" id="userImage" class="user_image" src="<?php if(isset($UsersImagepath)) echo $UsersImagepath; else echo MERCHANT_SITE_IMAGE_PATH."no_user.jpeg";?>">
								</td>
							</tr>
							<tr>
							<td align="center"><span id="username" class="customer_name"><?php if(isset($Username)) echo $Username; else echo 'No User Selected'; ?></span> </td>
							</tr>
							<tr style="margin:0px;">
								<td align="center"><a id="bottom" href="#bottom" class="change_user"><?php if(isset($UserId)) echo "Change user"; else echo "Select user"; ?></a><input type="hidden" id="CurrentUserId" name="CurrentUserId" value="<?php if(isset($UserId)) echo $UserId; ?>"><input type="hidden" id="OrderUserImage" name="OrderUserImage" value="<?php if(isset($UsersImagepath)) echo $UsersImagepath; ?>"><input type="hidden" id="OrderUserName" name="OrderUserName" value="<?php if(isset($Username)) echo $Username;?>"> </td>
							</tr>
							<!-- <tr>
								<td style="background-color:#000;min-height:auto;height:100%;">&nbsp;</td>
							</tr> -->
							
<!-- 							<tr>
								<td align="center"><input id="order_submit" class="btn btn-success checkout" type="Submit" name="order_submit" value="<?php if(isset($OrderTotal)) echo "Charge $".$OrderTotal; ?>" onclick="return checkBalance();"></td>
							</tr>
 -->						</table>
						</div>
						</div>
						<div id="tbl2" align="left" class="order-bottom clearorder"  style="display:none" >
							<!-- <tr width="100%">
								<td colspan="5"><div class="col-lg-12 col-xs-12 no-padding" style="padding-right:0px;"><hr style="border-color:#dfdfdf;margin:0px;width:100%;"></div></td>
								<td><div class="col-lg-12 col-xs-12 no-padding" style="padding-right:0px;"><hr class="border-checkout"></hr></td>
							</tr> -->							
							<!--  <div> -->
								<div class="pull-left order-prt1"><a style="cursor:pointer" class="userTotal pull-left" onclick="return clearOrders()"><!-- <i class="fa fa-trash-o fa-lg"></i> --> <img src="webresources/images/dustbin.png" width="12" height="16" alt="">&nbsp; Clear Order</a><input type="hidden" id="userDefaultImage" name="userDefaultImage" value="<?php echo MERCHANT_SITE_IMAGE_PATH."no_user.jpeg";?>"/>
								
									<div><input type="hidden" id="OrderTotal" name="OrderTotal" value="<?php if(isset($OrderTotal)) echo $OrderTotal; ?>"><input type="hidden" id="userImageValue" name="userImageValue" value="<?php if(isset($UsersImagepath)) echo '1'; ?>"></div>
									<div class="pull-right col-xs-7 col-sm-4 col-md-3 col-lg-2">
										<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 no-right-pad" align="left"><b>TOTAL</b></div>
										<div><input type="hidden" id="ProductVAT" name="ProductVAT" value="<?php if(isset($VATAmount)) echo $VATAmount; ?>"></div>
										<div align="right" class="col-xs-6 col-sm-6 col-md-6 col-lg-6 no-left-pad" style="position:relative;"><b><?php echo '&pound;'?><span class="OrderTotalShow"><?php if(isset($OrderTotal)) echo $OrderTotal; ?></span></b><!-- <p class="help-block vat_include">VAT included</p> --></div>
									</div>
								</div>
								
<!-- 								<td align="center" width="18%"><input id="order_submit" class="btn btn-success checkout" type="Submit" name="order_submit" value="<?php //if(isset($OrderTotal)) echo "Charge $".$OrderTotal; ?>" onclick="return checkBalance();"></td> -->
								<div align="center" class="pull-right order-prt2"> 
								<a id="order_submit" class="btn btn-success checkout" onclick="return checkBalance();"><em></em>CHECKOUT</a>
								</div>
							<!-- </tr> -->
						</div>
						</div>
					</div>
				</div>
			</div>	
		</form>
		<div class="col-lg-12 box-center new_order_list">
			<div class="box box-primary no-padding">
			<!-- <a href="#" class="jcarousel-control-prev">&lsaquo;</a> -->
			<div class="col-xs-12 product_list no-padding">
				<h1 style="color:#202020;">Most Popular Items</h1>
					<div class="box-body" style="background-color:#fff;">
						<?php if(isset($PopularProducts) && !empty($PopularProducts)) { ?>
						<!-- start product List -->
							<div class="clear col-xs-12 col-lg-12 col-sm-12 jcarousel">		
								<ul>
									<?php foreach($PopularProducts as $key1=>$value1) { ?>	
									<li>
										<div class="col-xs-12 no-padding " style="cursor:pointer;">
											<div class="small-box ">
												<a href="javascript:void(0);" onclick="return hideShowOrders('<?php echo $value1['fkProductsId'];?>','<?php echo addslashes($value1['ItemName']);?>','<?php echo $value1['Photo']; ?>','<?php echo number_format((float)$value1['Price'], 2, '.', ''); ?>','<?php echo number_format((float)$value1['DiscountPrice'], 2, '.', ''); ?>');" class="Product_fancybox" title="<?php echo ucfirst($value1['ItemName']);?>">
													<img src="<?php echo $value1['Photo']; ?>" width="116" height="108"  alt="" style="margin-bottom:15px;"><br>
												</a>
												<div class="product_price" style="cursor:text;">
												<span class="title_product" style="" ><a href="javascript:void(0);" title="Add to cart" alt="Add to cart" onclick="return hideShowOrders('<?php echo $value1['fkProductsId'];?>','<?php echo addslashes($value1['ItemName']);?>','<?php echo $value1['Photo']; ?>','<?php echo number_format((float)$value1['Price'], 2, '.', ''); ?>','<?php echo number_format((float)$value1['DiscountPrice'], 2, '.', ''); ?>');"><?php echo $value1['ItemName'];?></a></span>
												<?php echo "<div class='cal'><strong>".price_fomat($value1['Price'])."</strong></div> "; 
													if($value1['DiscountPrice'] != 0)
														echo "<div class='cal actual_price' style='color:gray;'><strong>".price_fomat($value1['DiscountPrice'])."</strong></div>";
												?>
												<!-- <p class="help-block vat_include_pro">VAT included</p> -->
												</div>
											</div>
										</div>
									</li>
									<?php } ?>		
								</ul>																
							</div><!-- /row -->
							
				<a href="#" class="jcarousel-control-prev">&lsaquo;</a>
				<a href="#" class="jcarousel-control-next">&rsaquo;</a>
							<!-- End product List -->						 
						<?php } else { ?>
							<div align="center" class="clear alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10"><i class="fa fa-warning"></i> No products found.</div>							
						<?php } ?>						
					</div><!-- /.box-body -->
				</div>					
			</div>
			<form method="post" action="CreateOrder" id="SearchForm" name="SearchForm" onsubmit="return createsubmitform();">
			<div class="col-xs-12 product_list no-padding">				
				<h1 class="col-sm-8 col-md-9 col-lg-9 col-xs-12">Categories</h1>
				<div class="col-lg-2 col-md-3 col-sm-4 col-xs-12 pull-right no-padding">
					<div class="search-form">
                        <input type="text" placeholder="Search Products" value="<?php if(!empty($Search)) echo $Search; ?>" class="form-control LH16" name="productsearch" id="productsearch">
						<!-- <i class="fa fa-search search_icon"></i> -->
						<!--<input type="button" onclick="ProductSearch(0)" value="&nbsp;" class="search_icon" title="Search">-->
						<input type="button" onclick="Search(0)" value="&nbsp;" class="search_icon" title="Search">
					</div>
                </div>

				<div class="box-primary no-padding">
					<div class="box-body" id="products_block" style="padding:0px 0px;">
						<?php if(isset($specialArray) && !empty($specialArray) && count($specialArray)>0) { ?>
						
 							<div style="cursor:pointer"  id="hideSep0_1" class="sep_title col-xs-12 no-padding blockdown" style="cursor:pointer;" onclick="return productCategoryHideShow('0_1','')">
							<div class="col-xs-8 no-padding">
								<h4>Specials</h4>
							</div>
							<div class="col-xs-4 text-right pad" style="font-size:20px;cursor:pointer;color:#e3e3e3;"><i id="plusMinus0_1" class="fa <?php if(empty($Search)) echo "fa-caret-down"; else echo "fa-caret-up"; ?>"></i><input type="hidden" id="rowHidden0_1" value="1"></div>
							</div>
							<div class="clear product_show sep_title blockdown" id="rowHide0_1" <?php if(empty($Search)) echo 'style="display:none;"'; ?>>										
								<?php foreach($specialArray as $key1=>$value1) { ?>	
									<div class="col-xs-12 col-sm-4 col-md-2 col-lg-2 LH196 <?php if($value1['Status'] == 2) echo "inactive";?>" style="cursor:pointer;" >
										<div class="small-box " >
											<a href="javascript:void(0);" class="Product_fancybox" onclick="return hideShowOrders('<?php echo $value1['ProductId'];?>','<?php echo addslashes($value1['ItemName']);?>','<?php echo $value1['Photo']; ?>','<?php echo $value1['Price']; ?>','<?php echo $value1['DiscountPrice']; ?>');" title="<?php echo ucfirst($value1['ItemName']);?>">
												<img height="115" width="110" src="<?php echo $value1['Photo']; ?>" alt="" style="margin-bottom:15px;"><br>
											</a>
											<div class="product_price" style="cursor:text;">
											<span class="title_product" style=""><?php  if($value1['Status'] != 2) { ?><a href="javascript:void(0);" title="Add to cart" alt="Add to cart"  onclick="return hideShowOrders('<?php echo $value1['ProductId'];?>','<?php echo addslashes($value1['ItemName']);?>','<?php echo $value1['Photo']; ?>','<?php echo $value1['Price']; ?>','<?php echo $value1['DiscountPrice']; ?>');"><?php echo $value1['ItemName'];?></a><?php } else echo $value1['ItemName'];?> </span>
											
											<?php 	echo "<div class='cal'><strong>".price_fomat($value1['Price'])."</strong></div> ";
													echo "<div class='cal actual_price' style='color:gray;'>".price_fomat($value1['OriginalPrice'])."</div>";  
											?>
											<!-- <p class="help-block vat_include_pro">VAT included</p> -->
											</div>
										</div>
									</div> 
								<?php } ?>										
							</div><!-- /row -->
							<div class="col-xs-12 clear no-padding"><hr class="no-margin"></div> <!-- sep line -->
						<?php } ?>
					
						<?php if(isset($productList) && !empty($productList)) { $categoryIds = ''; ?>						
						<!-- start product List -->
						<?php foreach($productList as $key=>$value) { if(!empty($value[0]['ProductId'])) { $categoryIds .= $key.',';	?>


 								<div style="cursor:pointer" id="hideSep<?php echo $key; ?>" class="col-xs-12 no-padding sep_title blockdown" style="cursor:pointer;" onclick="return productCategoryHideShow(<?php echo $key; ?>,'')">
									<div class="col-xs-8 no-padding">
										<h4><?php echo $value[0]['CategoryName']; ?></h4>
									</div>
									<div class="col-xs-4 text-right pad" style="font-size:20px;cursor:pointer;color:#e3e3e3;"><i id="plusMinus<?php echo $key; ?>" class="fa <?php if(empty($Search)) echo "fa-caret-down"; else echo "fa-caret-up"; ?>"></i><input type="hidden" id="rowHidden<?php echo $key; ?>" value="1"></div>
								</div>
								<div class="clear product_show sep_title blockdown" id="rowHide<?php echo $key; ?>" <?php if(empty($Search)) echo 'style="display:none;"'; ?>>										
									<?php $value = subval_sort($value,'Ordering');  foreach($value as $key1=>$value1) { ?>	
										<div class="col-xs-12 col-sm-4 col-md-2 col-lg-2 LH196 <?php if($value1['Status'] == 2) echo "inactive";?>" style="cursor:pointer;" >
											<div class="small-box ">												
												<a href="javascript:void(0);" class="Product_fancybox"  onclick="return hideShowOrders('<?php echo $value1['ProductId'];?>','<?php echo addslashes($value1['ItemName']);?>','<?php echo $value1['Photo']; ?>','<?php echo $value1['Price']; ?>','<?php echo $value1['DiscountPrice']; ?>');"  title="<?php echo ucfirst($value1['ItemName']);?>">
													<img height="115" width="110" src="<?php echo $value1['Photo']; ?>" alt="" style="margin-bottom:15px;"><br>
												</a>
												<div class="product_price" style="cursor:text;">
												<span class="title_product" style="">
												<?php if($value1['Status'] != 2) { ?>
												<a  href="javascript:void(0);" title="Add to cart" alt="Add to cart"  onclick="return hideShowOrders('<?php echo $value1['ProductId'];?>','<?php echo addslashes($value1['ItemName']);?>','<?php echo $value1['Photo']; ?>','<?php echo $value1['Price']; ?>','<?php echo $value1['DiscountPrice']; ?>');"><?php echo $value1['ItemName'];?></a>
												<?php } else { echo $value1['ItemName']; }?></span>
												<?php 	
														if($value1['DiscountPrice'] > 0 )
															echo "<div class='cal'><strong>".price_fomat($value1['DiscountPrice'])."</strong></div> ";
														echo "<div class='cal actual_price' style='color:gray;'>".price_fomat($value1['Price'])."</div>";  
												?>
												<!-- <p class="help-block vat_include_pro">VAT included</p> -->
												</div>
											</div>
										</div> 
									<?php } ?>										
								</div><!-- /row -->
								<div class="col-xs-12 clear no-padding"><hr class="no-margin"></div> <!-- sep line -->
						<?php  }  ?>
							<!-- End product List -->						 
						<?php } ?> 
						<input type="hidden" id="categoryIds_val" value="<?php echo rtrim($categoryIds,','); ?>" />
						<?php } else { ?>
								 <div align="center" class="alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10 clear"><i class="fa fa-warning"></i>  No products found.</div>							
						<?php } ?>						
					</div><!-- /.box-body -->
					<input type="hidden" id="ProductStart" name="ProductStart" value="0" />
						<input type="hidden" id="perviousProductSearch" name="perviousProductSearch" value="" />	
				</div>		
			</div>	
			
			<div class="col-xs-12 product_list no-padding" id="userinstore">
			
				<h1 class="col-sm-8 col-md-9 col-lg-9 col-xs-12" id="Totalusers">Users in store   <?php if($TotalUsers > 0) echo "-   ".$TotalUsers; ?></h1> 
				<div class="col-lg-2 col-md-3 col-sm-4 col-xs-12 pull-right no-padding">
					<div class="search-form">
                        <input type="text" placeholder="Search Users" value="<?php if(!empty($userSearch)) echo $userSearch; ?>" class="form-control LH16" name="usersearch" id="usersearch">
						 <input type="button" onclick="UserSearch(0)" value="&nbsp;" class="search_icon" title="Search"> 
					</div>
                </div>
				
				<div class="box box-primary no-padding" id="store-users">
					<div class=" box-body" id="users_block">
						<?php if(isset($userList) && !empty($userList)) { ?>
						<!-- start user List -->
						<?php foreach($userList as $key=>$users) { ?>																		
							<div class="col-xs-12 col-sm-4 col-md-2  col-lg-2 margin-bottom LH160"  title="Add to cart" alt="Add to cart"   id="user<?php echo $users['id'];?>">
								<div class="small-box" style="min-height:70px;padding:5px;margin:auto;">
									<div class="show_usersphoto">
									<div class="col-xs-12 no-padding text-center"  onclick="return hideShowUsers('<?php echo $users['id'];?>','<?php echo $users['Photo'];?>','<?php echo ucfirst($users['FirstName']).' '.ucfirst($users['LastName']);?>','<?php echo $users['CurrentBalance'];?>');" style="cursor:pointer;"><img width="100" height="100" style="padding:0" src="<?php echo $users['Photo']; ?>" alt=""></div>
									</div>
									<div class="col-xs-12 text-center" style="cursor:pointer;"  onclick="return hideShowUsers('<?php echo $users['id'];?>','<?php echo $users['Photo'];?>','<?php echo ucfirst($users['FirstName']).' '.ucfirst($users['LastName']);?>','<?php echo $users['CurrentBalance'];?>');">
										<?php echo ucfirst($users['FirstName']).'&nbsp;'.ucfirst($users['LastName']);?>
									</div>
								</div>
							</div> 	
						<?php }  ?>						
							<!-- End user List -->
							<input type="hidden" id="userTotalhide" name="userTotalhide" value="<?php if(isset($_SESSION['tuplitCreateOrderTotalUser'])) echo $_SESSION['tuplitCreateOrderTotalUser']; ?>"/>
						<?php } else { ?>
								 <div align="center" class="clear alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10"><i class="fa fa-warning"></i>  No customers found in your location.</div>							
						<?php } ?>
						<?php if(isset($userList) && !empty($userList) && $_SESSION['tuplitCreateOrderTotalUser'] > 12) { ?>
						<div class="col-xs-12 clear text-center" id="loadmorehome"> <a style="cursor:pointer;margin-bottom:15px;" class="loadmore" id="loadmore" name="loadmore" class="btn btn-success" title="Load More" onclick="return loadMoreUser(<?php echo $userLoadMore; ?>);"><i class="fa fa-download"></i> <strong>Load More</strong></a></div>

						<?php } ?>
						
				</div>				
					</div><!-- /.box-body -->
						<input type="hidden" id="UserStart" name="UserStart" value="0" />
						<input type="hidden" id="perviousUserSearch" name="perviousUserSearch" value="" />						
							
			</div>	
			<!-- <input type="submit" id="SearchSubmit" name="SearchSubmit" value="search" style="display:none;"/> -->
			</form>
		 </div>

<!-- Rejected Popup -->
<div  id="rejectedPopup" style="display:none;">
   <form action="" name="rejected_popup"  method="post" >
		<div class="popup" id="firstDiv">
			<div class="form-group col-xs-12 no-padding" id="secondDiv" >
				<!-- <label class="col-xs-12">Category Name</label> -->
				<div class="col-xs-12 popup_title text-center"><h3>Your Order</h3></div>
				<div class="col-xs-12 delete_cat" style="padding-bottom:6px;">
					<p class="help-block col-xs-12 text-center">You have rejected the order. Now you can go back to Merchant or Home screen.</p>
				</div>
			</div>
			<div class="form-group col-xs-12 no-padding">
				<div class="complete_img">
					<img src="webresources/images/rejected.png" width="73" height="75" alt="">
					<h2 class="text-center text-red no-margin">Rejected!</h2>
				</div>
			</div>
			<div class="form-group col-xs-12 text-center no-padding">
				<a href="CreateOrder?cs=1" class="back_arrow">
					<i class="fa fa-show"></i> Back to Merchant screen
				</a>
			</div>
			<div class="footer col-xs-12 text-center clear no-padding"> 
				<a href="Dashboard" class="text-center btn btn-success col-xs-12">BACK TO HOME SCREEN</a>						
			</div>
		</div><!-- /row -->		
	</form>
</div>
<!-- /Rejected Popup -->

<!-- Accepted Popup -->
<div id="acceptedPopup"  style="display:none;">
<form action="" name="accepted_popup" id=""  method="post">
				<div class="popup" id="firstDiv">
					<div class="form-group col-xs-12" id="secondDiv" >
						<!-- <label class="col-xs-12">Category Name</label> -->
						<div class="col-xs-12 popup_title text-center"><h3>Your Order</h3></div>
						<div class="col-xs-12 delete_cat">
								<p class="help-block col-xs-12 text-center">Your order has been completed.<br>Please collect purchased items.</p>
						</div>
					</div>
					<div class="form-group col-xs-12 no-padding">
						<div class="complete_img">
							<img src="webresources/images/completed.png" width="73" height="75" alt="">
							<h2 class="text-center text-green no-margin">Completed!</h2>
						</div>
					</div>
					<div class="col-xs-12 text-center no-padding">
						<a class="show_receipt" style="cursor:pointer;" id="showReceipt" onclick="rejectOrderPopup('sessionVal',1);">
							<i class="fa fa-show"></i> Show the receipt
						</a>
					</div>
					<div class="form-group col-xs-12 text-center no-padding">
						<a href="CreateOrder?cs=1" class="back_arrow">
							<i class="fa fa-show"></i> Back to Merchant screen
						</a>
					</div>
					<div class="footer col-xs-12 text-center clear no-padding"> 
						<a href="Dashboard" class="text-center btn btn-success col-xs-12">BACK TO HOME SCREEN</a>						
					</div>
				</div><!-- /row -->		
			</form>
</div>
<!-- /Accepted Popup -->
<!-- Your Order -->
<!--<div style="display:none;">-->
<div id="acceptOrReject" style="display:none;">
<form action="" name="your_order_form" id=""  method="post" >
			<div class="popup" id="firstDiv">
				<div class="col-xs-12" id="secondDiv" style="border-bottom:2px dotted #dfdfdf;">
					<!-- <label class="col-xs-12">Category Name</label> -->
					<div class="col-xs-12 popup_title text-center"><h1>Your Order</h1></div>
					<div class="col-xs-12 delete_cat">
						<p class="help-block col-xs-12 text-center">Please confirm or reject your order<br>initiated by the merchant</p>
					</div>
				</div>
				<div style="box-shadow:none;border-bottom:0px solid #fff;">
				<div>
							<div class="col-md-12 col-sm-12 col-lg-12 col-xs-11 no-padding"  style="padding-top:15px;">
									<div class="clear"></div>
									<div class="col-xs-12 no-padding list_height no-margin" id="displayAcc">
		                        
												<!--<div class="col-xs-1 col-sm-1 col-md-1" style="margin-bottom:8px;">	
														<div class="col-xs-12  no-padding"> 
															1x
														</div>						
												</div>
												<div class="col-xs-4 col-sm-4 col-md-6 no-padding" style="margin-bottom:8px;">
															<span title="Item Name">
																WHOOPER Meal Deal
															</span>
												</div>
												<div class="col-xs-7 col-sm-5 col-md-5 text-right" style="margin-bottom:8px;">
													<strong>$1.5</strong>
												</div>
										<div class="clear "><br></div>											
										<div class="col-xs-12 col-sm-12 col-md-12" style="border-top:1px solid #dbdbdb;padding-top:5px;padding-bottom:5px;">
											 	<div class="col-xs-3 col-sm-6 col-md-6 no-padding">
													<strong>Total</strong>
												</div>
												<div class="col-xs-9 col-sm-6 col-md-6 text-right no-padding">
													<strong>$4.5</strong>
												</div>
										</div>-->
									</div>
							</div> 				
					<!-- End New Orders List -->						
				</div><!-- /.box-body -->
			</div>
				<div class="form-group col-xs-12 text-center print_top">
					<a  href="javascript:void(0);" class="print_order print_out" title="Print" onclick="printBill('newprint')">
						<em></em> PRINT
					</a>
				</div>
				<div class="footer col-xs-12 text-center clear no-padding">
					<a class="text-center btn btn-success reject col-xs-4" id="rejectOrder" style="float:left;width:34%;" onclick="rejectOrderPopup('rejectedPopup','');">REJECT</a>
					<a class="text-center btn btn-success col-xs-8" id="acceptOrder" onclick="submitForm();" style="float:right;width:66%;">CONFIRM ORDER</a>
				</div>
			</div><!-- /row -->		
		</form>
</div>
<!--</div>-->
<!-- /Your Order -->
<div style="display:none;">
<div id="sessionVal" >
<form action="" name="your_order_form" id=""  method="post" >
				<div class="popup" id="firstDiv" style="border:0px solid red;">
				<table align="center" border="0" style="width:300px;border:1px solid #dbdbdb;padding:5px 5px" class="your_receipt">
				<tr>
					<td style="border-bottom:1px dotted #dbdbdb;" align="center" width="100%" colspan="2">
					<div class="form-group col-xs-12 no-padding" id="secondDiv" >
						<!-- <label class="col-xs-12">Category Name</label> -->
						<div class="col-xs-12 popup_title text-center"><h3>Your Receipt</h3></div>
					
					</div>
					</td>
				</tr>
				<tr style="padding:10px 10px;">
					<!-- <div class="" style="box-shadow:none;border-bottom:0px solid #fff;"> -->
					<!-- <div class="space_top" > -->
								<!-- <div class="col-md-12 col-sm-12 col-lg-12 col-xs-11 no-padding"  style="padding-top:15px;"> -->
									<!-- <div class="clear"></div> -->
										<!-- <div class="col-xs-12 no-padding list_height no-margin" > -->
											<!-- <div class="col-xs-12 col-sm-12 col-md-12"> -->
			                        			<?php if(isset($_SESSION['CreateOrder']) && !empty($_SESSION['CreateOrder'])){
															//echo "<pre>";print_r($_SESSION['CreateOrder']);echo "<pre>";
															$OrderArr	= $_SESSION['CreateOrder'];
															$orderVal	= $OrderArr['orderProducts'];
															foreach($orderVal as $k=>$v){ ?>
															<td class="item-name" width="90%" style="line-height:20px;padding:5px 20px;"><div class="col-xs-3 col-sm-6 col-md-6 no-padding" style="margin-bottom:8px;">
															<?php echo $v['ProductsQuantity'];?>x&nbsp;&nbsp;
																		<span title="Item Name">
																			<?php echo $v['ItemName'];?>
																		</span>
															</div>
															</td>
															<td class="item-price" width="10%" style="line-height:20px;padding:5px 20px;" align="right">
															<div class="col-xs-9 col-sm-6 col-md-6 text-right no-padding" style="margin-bottom:8px;">
																<strong><?php echo price_fomat($v['DiscountPrice']);?></strong>
					
															</div>
															</td>
														</tr>
														<?php } ?>
														<tr id="itemTotal" class="print_subtotal" style="font-size: 11px;line-height:18px;">
															<td style="border-top:1px dotted #DFDFDF;padding:20px 20px 0px 20px;" align="left" class="item-total LH18" width="90%">
																<strong>SUB TOTAL</strong></td>
															<td  style="border-top:1px dotted #DFDFDF;padding:20px 20px 0px 20px;"  width="10%"  class="LH18"  align="right"><strong><?php echo price_fomat($OrderArr['SubTotal']);?></strong></td>
														</tr>
														<tr id=""  class="print_vat LH18" style="font-size: 10px;line-height:18px;">
															<td align="left" class="item-total LH18" width="90%" style="padding:0px 20px;font-weight:normal;">VAT</td>
															<td width="10%" style="padding:0px 20px;font-weight:normal;" class="LH18" align="right"><?php echo price_fomat($OrderArr['VatTotal']);?></td>
														</tr>
														<tr class="print_total" style="line-height:40px;border-top:1px dotted #DFDFDF;">
															
															<td align="left" class="item-total" style="line-height:45px;padding:0px 20px;border-top:1px dotted #DFDFDF;" width="90%">

																 	<div class="col-xs-3 col-sm-6 col-md-6 no-padding">
																		<strong>TOTAL</strong>
																	</div>
															</td>
															<td width="10%" style="padding:0px 20px;border-top:1px dotted #DFDFDF;"  align="right">
																	<div class="col-xs-9 col-sm-6 col-md-6 text-right no-padding">
																		<strong><?php echo price_fomat($OrderArr['OrderTotal']);?></strong>
																	</div>
															</td>
														</tr>
														</table>
															<!-- </div> -->
															
															<div class="form-group col-xs-12 text-center print_top no-margin" id="no-print">
																	<input type="checkbox" name="" id="" value="">
																	<a  href="javascript:void(0)" class="print_order print_out" onclick="printReceipt();" id="printReceipt" title="Print" style="margin-left:6px;">
																	<em></em> PRINT
																	</a>
															</div>
													<?php } ?>
										<!-- </div> -->
								<!-- </div> --></td>
			</tr>
			</table>
						<!-- End New Orders List -->						
					<!-- </div> --><!-- /.box-body -->
				<!-- </div> -->
				</div><!-- /row -->		
				<div class="footer col-xs-12 text-center clear no-padding" id="no-print1"> 
						<a class="text-center btn btn-success col-xs-4 reject" style="background-color:#2F2F2F;padding:18px 0px;float:left;width:34%;" id="rejectOrder" onclick="rejectOrderPopup('acceptedPopup','');"><strong>Back</strong></a>
						<a href="CreateOrder?cs=1" class="text-center btn btn-success col-xs-8" style="padding:18px 0px;float:right;width:66%;"><strong>Back To Home Page</strong></a>
				</div>
			</form>
</div>
</div>

<!-- print content display --->
<div style="display:none;">
	<div id="newprint" style="background-color:#f2f2f2;float:left;width:100%;"></div>
</div>
<!-- print content-->
<input type="hidden" id="accOrRej">
		 <?php } ?>
		 <input type="hidden" id="orderComplete" value="0">
	</section>
<?php footerLogin(); ?>
<?php commonFooter(); ?>
<!--print div start-->
<script type="text/javascript">
$(document).ready(function() {		
	$('.clearorder').show();
	$('#showProductPrice').show();
	$('#orderPrint').show();
});	
/*---- printout fancybox ---*/
$('#printout').click(function () {
	var content_print = $('#newprint').html();
	$.fancybox({
     	//type: 'iframe',
      	content: content_print,
		'width': 350,
        height: 'auto',
        'autoSize' : false,
  	});
});
/*---- print window ---*/
$(function() {
	$("#printout").click(function() {
		// Print the DIV.
		$("#newprint").print();
		return (false);
	});
});
function printReceipt(){
	//alert('------->');
	$("#no-print").hide(); //hide PRINT link in printed sheet
	$("#no-print1").hide(); // hide BACK link in printed sheet
	$("#sessionVal").print();
		return (false);
}
function submitForm(){
	$('#OrderForm').submit()
}
</script>
<!--print div end-->
<script type="text/javascript">
$('#accOrRej').click(function() {
	var aor	=   $('#acceptOrReject').html(); 
	    $.fancybox({
	       // type: 'iframe',
	    	content: aor, 
			'width': '350',
        	'height': 'auto',
			autoSize: false,
		});
});
 //ready
function rejectOrderPopup(datas,type){
	clearOrders(type); 
	$("#no-print").show(); //show PRINT link in popup 
	$("#no-print1").show(); //show BACK link in popup
	var aor		=   $('#'+datas).html(); 
	    $.fancybox({
	        width: '350',
        	height: 'auto',
			autoSize: false,
			//type: 'iframe',
	    	content: aor, 
		});
} //ready

<?php if (isset($_GET['orderdone']) && $_GET['orderdone']== 1 && !isset($_SESSION['orderComplete']) ){  
$_SESSION['orderComplete'] = 1;
		?>
		setTimeout( function() {
		var aor	=   $('#acceptedPopup').html(); 
		$.fancybox({
	        width: '350',
        	height: 'auto',
			autoSize: false,
			//type: 'iframe',
	    	content: aor, 
		});
	 },100);
<?php } ?>
</script>
	<script type="text/javascript">
		$('.Product_fancybox').fancybox();			
		$("a[href='#bottom']").click(function() {
			  var pos = $("#store-users").position().top;
			  var ht = $(document).height() - pos;
			 $("html, body").animate({ scrollTop: $("#userinstore").offset().top - $(".menu_header").height()  }, {duration: $("#userinstore").offset().top});
			  return false; 
	    });
	</script>
	<style>
		@media print { body {background:#fff !important}}
	</style>
</html>



