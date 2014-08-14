<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();
$condition  = '';
$search		= $userSearch = '';

if((isset($_GET['cs']) && $_GET['cs'] == 1) && (isset($_GET['ajax']) && $_GET['ajax'] == 'true') ) {
	unset($_SESSION['CreateOrder']);
	die();
}
if(isset($_GET['cs']) && $_GET['cs'] == 1)
	unset($_SESSION['CreateOrder']);

if(isset($_SESSION['SuccessMsg']) && !empty($_SESSION['SuccessMsg'])) {
	$successMessage 		= 	$_SESSION['SuccessMsg'];
	unset($_SESSION['SuccessMsg']);
}

unset($_SESSION['tuplitCreateOrderUser']);
unset($_SESSION['tuplitCreateOrderTotalUser']);
	
//Order submit
if(isset($_POST['order_submit']) && !empty($_POST['order_submit'])) {
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
			$OrderTotal			= $_POST['OrderTotal'];
			$UserId				= $_POST['CurrentUserId'];
			$UsersImagepath		= $_POST['OrderUserImage'];
			$Username			= $_POST['OrderUserName'];
			
			$_SESSION['CreateOrder']['orderProductIds'] 	= $orderProductIds;
			$_SESSION['CreateOrder']['orderProducts'] 		= $orderProducts;
			$_SESSION['CreateOrder']['UserId'] 		  		= $UserId;
			$_SESSION['CreateOrder']['UsersImagepath'] 		= $UsersImagepath;
			$_SESSION['CreateOrder']['Username'] 			= $Username;
			$_SESSION['CreateOrder']['OrderTotal'] 			= $OrderTotal;
		}
		unset($_POST);		
	}
}
else {
	if(isset($_SESSION['CreateOrder']['orderProducts'])) {
		$orderProductIds	= $_SESSION['CreateOrder']['orderProductIds'];
		$orderProducts 		= $_SESSION['CreateOrder']['orderProducts'];
		$OrderTotal			= $_SESSION['CreateOrder']['OrderTotal'];
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
											'TotalPrice'		=> $OrderTotal,
											'CartDetails'		=> $CartDetails
										);
			$url				=	WEB_SERVICE.'v1/orders/';
			$curlResponse 		= 	curlRequest($url, 'POST', $data, $_SESSION['merchantInfo']['AccessToken']);
			if(isset($curlResponse) && is_array($curlResponse) && $curlResponse['meta']['code'] == 201) {				
				if(isset($curlResponse['notifications'][0])) {
					$_SESSION['SuccessMsg']	=	$curlResponse['notifications'][0];
					unset($_SESSION['CreateOrder']);
					header("location:CreateOrder?cs=1");
					die();
				}
			} else if(isset($curlResponse['meta']['errorMessage']) && $curlResponse['meta']['errorMessage'] != '') {
				$errorMessage	=	$curlResponse['meta']['errorMessage'];
			} else {
				$errorMessage	= 	"Bad Request";
			}
		}
		else
			$errorMessage		=  	"This user not having enough balance to accept this order";
	} else if(isset($curlCategoryResponse['meta']['errorMessage']) && $curlCategoryResponse['meta']['errorMessage'] != '') {
		$errorMessage	=	$curlCategoryResponse['meta']['errorMessage'];
	}
}

//getting merchant details
if(isset($_SESSION['merchantDetailsInfo']) && is_array($_SESSION['merchantDetailsInfo'])){
	$merchantInfo  				=	$_SESSION['merchantDetailsInfo'];	
	if(!empty($merchantInfo['DiscountTier']) || $merchantInfo['DiscountTier'] != 0 ) {
	}
	else {
		$hide					= 	1;	
	}
}

if(isset($_SESSION['merchantDetailsInfo']) && is_array($_SESSION['merchantDetailsInfo'])){
	$merchantInfo  				=	$_SESSION['merchantDetailsInfo'];	
}
else{
	$merchantId					= 	$_SESSION['merchantInfo']['MerchantId'];
	$url						=	WEB_SERVICE.'v1/merchants/'.$merchantId."?From=0";
	$curlMerchantResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201 && $curlMerchantResponse['merchant']['MerchantId'] != '' ) 
	 {
		$merchantInfo  			= 	$_SESSION['merchantDetailsInfo']   =	$curlMerchantResponse['merchant'];
	}
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
if(isset($errorMessage) && $errorMessage != ''){
	$msg			=	$errorMessage;
	$display 		= 	"block";
	$class   		= 	"alert-danger";
	$class_icon 	= 	"fa-warning";
	$errorMessage 	= 	'';
}else if(isset($successMessage) && $successMessage != ''){
	$msg			=	$successMessage;
	$display		=	"block";
	$class 			= 	"alert-success";
	$class_icon 	= 	"fa-check";
	$successMessage = 	'';
}

commonHead();
?>
<body class="skin-blue fixed" onload="fieldfocus('productsearch');">
		<?php top_header(); ?>
		
		<section class="content">
		<?php if(empty($merchantInfo['MangoPayUniqueId'])){?>
				<div align="center" class="alert alert-danger alert-dismissable  col-lg-5 col-sm-7  col-md-5 col-xs-12"><i class="fa fa-warning"></i>&nbsp;&nbsp;Please connect with MangoPay in Settings to create orders.</div>
		<?php }
		else{
			 if(isset($msg) && $msg != '') { ?>
			<div align="center" id="showmessage" class="alert <?php  echo $class;  ?> alert-dismissable  col-xs-10 col-sm-5 col-lg-4"><i class="fa <?php  echo $class_icon;  ?>"></i>  <?php echo $msg; ?></div>
			<?php } ?>		
		<div class="col-lg-12 box-center">
			<form method="post" action="CreateOrder" id="OrderForm" name="OrderForm">
			<div class="product_list" id="Oders_Merchant" style="<?php if(isset($showorder) && $showorder >0) echo ''; else echo "display:none;"; ?>">
				<div class="box box-primary no-padding">
					<div class="row box-body bg-gray">
						<div class="col-xs-12 col-sm-12  col-lg-7  box-center">
						<table width="100%" id="tbl1" align="center">							
							<tr>
								<td width="15%"><input type="hidden" id="OrderProductIds" name="OrderProductIds" value="<?php if(isset($orderProductIds) && !empty($orderProductIds) && isset($showorder) && $showorder >0) echo $orderProductIds;?>"></td>
								<td width="50%" align="left"></td>
								<td width="25%" align="right"></td>
							</tr>
							<?php if(isset($showorder) && $showorder >0) {
									if(isset($orderProducts) && is_array($orderProducts) && count($orderProducts) > 0) {
										foreach($orderProducts as $orderval) {
							?>
							<tr id="orderrow<?php echo $orderval['ProductId']; ?>">
								<td>
									<i class="fa fa-minus" onclick="return addRemoveQuantity(<?php echo $orderval['ProductId']; ?>,'2');"></i>
									<input id="quantity<?php echo $orderval['ProductId']; ?>" onkeyup="return addRemoveQuantity(<?php echo $orderval['ProductId']; ?>,'3');" type="text" onkeypress="return isNumberKeyQuantity(event);" value="<?php echo $orderval['ProductsQuantity']; ?>" style="width:50px;text-align:center;" name="quantity<?php echo $orderval['ProductId']; ?>">
									<i class="fa fa-plus" onclick="return addRemoveQuantity(<?php echo $orderval['ProductId']; ?>,'1');"></i>
									<input id="imagePath<?php echo $orderval['ProductId']; ?>" type="hidden" value="<?php echo $orderval['imagePath']; ?>" name="imagePath<?php echo $orderval['ProductId']; ?>">
								</td>
									<td>
									<img width="25" height="25" src="<?php echo $orderval['imagePath']; ?>" alt="">
									  <?php echo $orderval['ItemName']; ?>
								</td>
								<td align="right">
									$
									<span id="orderPrice<?php echo $orderval['ProductId']; ?>"><?php echo $orderval['TotalPrice']; ?></span>
									<input id="originalprice<?php echo $orderval['ProductId']; ?>" type="hidden" value="<?php echo $orderval['ProductsCost']; ?>" name="originalprice<?php echo $orderval['ProductId']; ?>">
									<input id="discountprice<?php echo $orderval['ProductId']; ?>" type="hidden" value="<?php echo $orderval['DiscountPrice']; ?>" name="discountprice<?php echo $orderval['ProductId']; ?>">
									<input id="originalTotalprice<?php echo $orderval['ProductId']; ?>" type="hidden" value="<?php echo $orderval['TotalPrice']; ?>" name="originalTotalprice<?php echo $orderval['ProductId']; ?>">
									<input id="orderItemName<?php echo $orderval['ProductId']; ?>" type="hidden" value="<?php echo $orderval['ItemName']; ?>" name="orderItemName<?php echo $orderval['ProductId']; ?>">
								</td>
							</tr>
							<?php } } } ?>
							
						</table>
						<div class="col-lg-10 col-xs-12 pull-right no-padding"><hr style="border-color:#b2b2b2;"></div>
						<table width="100%" id="tbl2" align="center">							
							<tr>
								<td width="15%"><input type="hidden" id="OrderTotal" name="OrderTotal" value="<?php if(isset($OrderTotal)) echo $OrderTotal; ?>"><input type="hidden" id="userImageValue" name="userImageValue" value="<?php if(isset($UsersImagepath)) echo '1'; ?>"></td>
								<td colspan="2" width="50%" ><b>Total</b></td>
								<td width="25%" align="right"><b>$<span class="OrderTotalShow"><?php if(isset($OrderTotal)) echo $OrderTotal; ?></span></b></td>
							</tr>
							<tr><td colspan="4" height="20"></td></tr>
							<tr id="userTable">
								<td ><a style="cursor:pointer" class="userTotal" onclick="return clearOrders()">Clear Order</a><input type="hidden" id="userDefaultImage" name="userDefaultImage" value="<?php echo MERCHANT_SITE_IMAGE_PATH."no_user.jpeg";?>"/></td>
								<td><img width="25" height="25" id="userImage" class="" src="<?php if(isset($UsersImagepath)) echo $UsersImagepath; else echo MERCHANT_SITE_IMAGE_PATH."no_user.jpeg";?>">&nbsp;&nbsp;<span id="username"><?php if(isset($Username)) echo $Username; else echo 'No Customer Selected'; ?></span> </td>
								<td><a id="bottom" href="#bottom"><?php if(isset($UserId)) echo "Change Customer"; else echo "Select Customer"; ?></a><input type="hidden" id="CurrentUserId" name="CurrentUserId" value="<?php if(isset($UserId)) echo $UserId; ?>"><input type="hidden" id="OrderUserImage" name="OrderUserImage" value="<?php if(isset($UsersImagepath)) echo $UsersImagepath; ?>"><input type="hidden" id="OrderUserName" name="OrderUserName" value="<?php if(isset($Username)) echo $Username;?>"> </td>
								<td align="right"><input id="order_submit" class="btn btn-success " type="Submit" name="order_submit" value="<?php if(isset($OrderTotal)) echo "Charge $".$OrderTotal; ?>" onclick="return checkBalance();"></td>
							</tr>
						</table>
						</div>
					</div>
				</div>
			</div>	
		</form>
			<div class="col-xs-12 product_list no-padding">
				<h1>Most Popular Items</h1>
				<div class="box box-primary no-padding">
					<div class="box-body">
						<?php if(isset($PopularProducts) && !empty($PopularProducts)) { ?>
						<!-- start product List -->
							<div class="row clear">										
								<?php foreach($PopularProducts as $key1=>$value1) { ?>	
									<div class="col-xs-6 col-sm-3 col-md-2 " style="cursor:pointer;">
										<div class="small-box ">
											<div align="right"><i class="fa fa-shopping-cart fa-lg" title="Add to cart" alt="Add to cart" onclick="return hideShowOrders('<?php echo $value1['fkProductsId'];?>','<?php echo $value1['ItemName'];?>','<?php echo $value1['Photo']; ?>','<?php echo $value1['Price']; ?>','<?php echo $value1['DiscountPrice']; ?>');"></i></div>
											<a href="<?php echo $value1['Photo'];?>" class="Product_fancybox" title="<?php echo ucfirst($value1['ItemName']);?>">
												<img height="100" width="100" src="<?php echo $value1['Photo']; ?>" alt=""><br>
											</a>
											<div class="product_price" style="cursor:text;">
											<span class="title_product" style=""><?php echo $value1['ItemName'];?></span>
											<?php echo "<div class='cal pull-right'><strong>".price_fomat($value1['Price'])."</strong></div> "; 
												if($value1['DiscountPrice'] != 0)
													echo "<div class='cal actual_price pull-right' style='color:gray;'>".price_fomat($value1['DiscountPrice'])."</div>";
											?>
											</div>
										</div>
									</div> 
								<?php } ?>										
							</div><!-- /row -->
							<!-- End product List -->						 
						<?php } else { ?>
							<div align="center" class="clear alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10"><i class="fa fa-warning"></i> No products found.</div>							
						<?php } ?>						
					</div><!-- /.box-body -->
				</div>					
			</div>
			<form method="post" action="CreateOrder" id="SearchForm" name="SearchForm" onsubmit="return createsubmitform();">
			<div class="col-xs-12 product_list no-padding">				
				<h1 class="col-sm-8 no-padding no-margin">Categories</h1>
				<div class="col-lg-2 col-md-3  col-sm-4 col-xs-12 pull-right no-padding">
					<div class="search-form no-margin">
						<i class="fa fa-search"></i>
                        <input type="text" placeholder="Search Products" value="<?php if(!empty($Search)) echo $Search; ?>" class="form-control LH12" name="productsearch" id="productsearch">
					</div>
                </div>
				
				<div class="box box-primary no-padding">
					<div class="box-body" id="products_block">
						<?php if(isset($dealsArray) && !empty($dealsArray) && count($dealsArray)>0) { ?>
							<div style="cursor:pointer"  class="col-xs-8 no-padding" onclick="return productCategoryHideShow('0_0')">
								<h4><strong>Deals</strong></h4>
							</div>
							<div class="col-xs-4 text-right pad" style="font-size:20px;cursor:pointer" onclick="return productCategoryHideShow('0_0')"><i id="plusMinus0_0" class="fa <?php if(empty($Search)) echo "fa-caret-down"; else echo "fa-caret-up"; ?>"></i><input type="hidden" id="rowHidden0_0" value="1"></div>
							
							<div class="row col-xs-12 clear" id="rowHide0_0" <?php if(empty($Search)) echo 'style="display:none;"'; ?>>										
								<?php foreach($dealsArray as $key1=>$value1) { ?>	
									<div class="col-xs-11  col-md-3 col-sm-4 col-lg-2 <?php if($value1['Status'] == 2) echo "inactive";?>" style="cursor:pointer;" >
										<div class="small-box ">
											<?php if($value1['Status'] != 2) { ?>
												<a class="edit"><i class="fa fa-shopping-cart fa-lg" title="Add to cart" alt="Add to cart"  onclick="return hideShowOrders('<?php echo $value1['ProductId'];?>','<?php echo $value1['ItemName'];?>','<?php echo $value1['Photo']; ?>','<?php echo $value1['Price']; ?>','<?php echo $value1['DiscountPrice']; ?>');"></i></a>
											<?php } ?>
											<a href="<?php echo $value1['Photo'];?>" class="Product_fancybox" title="<?php echo ucfirst($value1['ItemName']);?>">
												<img height="100" width="100" src="<?php echo $value1['Photo']; ?>" alt=""><br>
											</a>
											<div class="product_price" style="cursor:text;">
											<span class="title_product" style=""><?php echo $value1['ItemName'];?></span>
											<?php 	echo "<div class='cal actual_price pull-right' style='color:gray;'><strong>".price_fomat($value1['Price'])."</strong></div> ";?>
											</div>
										</div>
									</div> 
								<?php } ?>										
							</div><!-- /row -->
							<div class="col-xs-12 clear no-padding"><hr class="no-margin"></div> <!-- sep line -->
						<?php } 
						if(isset($specialArray) && !empty($specialArray) && count($specialArray)>0) { ?>
							<div style="cursor:pointer"  class="col-xs-8 no-padding" style="cursor:pointer;" onclick="return productCategoryHideShow('0_1')">
								<h4><strong>Specials</strong></h4>
							</div>
							<div class="col-xs-4 text-right pad" style="font-size:20px;cursor:pointer" onclick="return productCategoryHideShow('0_1')"><i id="plusMinus0_1" class="fa <?php if(empty($Search)) echo "fa-caret-down"; else echo "fa-caret-up"; ?>"></i><input type="hidden" id="rowHidden0_1" value="1"></div>
							
							<div class="row clear" id="rowHide0_1" <?php if(empty($Search)) echo 'style="display:none;"'; ?>>										
								<?php foreach($specialArray as $key1=>$value1) { ?>	
									<div class="col-xs-6 col-sm-3 col-md-2 <?php if($value1['Status'] == 2) echo "inactive";?>" style="cursor:pointer;" >
										<div class="small-box ">
											<?php if($value1['Status'] != 2) { ?>
												<a class="edit"><i class="fa fa-shopping-cart  fa-lg" title="Add to cart" alt="Add to cart"  onclick="return hideShowOrders('<?php echo $value1['ProductId'];?>','<?php echo $value1['ItemName'];?>','<?php echo $value1['Photo']; ?>','<?php echo $value1['Price']; ?>','<?php echo $value1['DiscountPrice']; ?>');"></i></a>
											<?php } ?>
											<a href="<?php echo $value1['Photo'];?>" class="Product_fancybox" title="<?php echo ucfirst($value1['ItemName']);?>">
												<img height="100" width="100" src="<?php echo $value1['Photo']; ?>" alt=""><br>
											</a>
											<div class="product_price" style="cursor:text;">
											<span class="title_product" style=""><?php echo $value1['ItemName'];?></span>
											<?php 	echo "<div class='cal pull-right'><strong>".price_fomat($value1['Price'])."</strong></div> ";
													echo "<div class='cal actual_price pull-right' style='color:gray;'>".price_fomat($value1['OriginalPrice'])."</div>";  
											?>
											</div>
										</div>
									</div> 
								<?php } ?>										
							</div><!-- /row -->
							<div class="col-xs-12 clear no-padding"><hr class="no-margin"></div> <!-- sep line -->
						<?php } ?>
					
						<?php if(isset($productList) && !empty($productList)) { ?>						
						<!-- start product List -->
						<?php foreach($productList as $key=>$value) { if(!empty($value[0]['ProductId'])) { 	?>
								<div style="cursor:pointer"  class="col-xs-8 no-padding" style="cursor:pointer;" onclick="return productCategoryHideShow(<?php echo $key; ?>)">
									<h4><strong><?php echo $value[0]['CategoryName']; ?></strong></h4>
								</div>
								<div class="col-xs-4 text-right pad" style="font-size:20px;cursor:pointer" onclick="return productCategoryHideShow(<?php echo $key; ?>)"><i id="plusMinus<?php echo $key; ?>" class="fa <?php if(empty($Search)) echo "fa-caret-down"; else echo "fa-caret-up"; ?>"></i><input type="hidden" id="rowHidden<?php echo $key; ?>" value="1"></div>
								
								<div class="row clear" id="rowHide<?php echo $key; ?>" <?php if(empty($Search)) echo 'style="display:none;"'; ?>>										
									<?php $value = subval_sort($value,'Ordering');  foreach($value as $key1=>$value1) { ?>	
										<div class="col-xs-6 col-sm-3 col-md-2 <?php if($value1['Status'] == 2) echo "inactive";?>" style="cursor:pointer;" >
											<div class="small-box ">
												<?php if($value1['Status'] != 2) { ?>
													<a class="edit"><i class="fa fa-shopping-cart  fa-lg" title="Add to cart" alt="Add to cart"  onclick="return hideShowOrders('<?php echo $value1['ProductId'];?>','<?php echo $value1['ItemName'];?>','<?php echo $value1['Photo']; ?>','<?php echo $value1['Price']; ?>','<?php echo $value1['DiscountPrice']; ?>');"></i></a>
												<?php } ?>
												<a href="<?php echo $value1['Photo'];?>" class="Product_fancybox" title="<?php echo ucfirst($value1['ItemName']);?>">
													<img height="100" width="100" src="<?php echo $value1['Photo']; ?>" alt=""><br>
												</a>
												<div class="product_price" style="cursor:text;">
												<span class="title_product" style=""><?php echo $value1['ItemName'];?></span>
												<?php 	
														if($value1['DiscountPrice'] > 0 )
															echo "<div class='cal pull-right'><strong>".price_fomat($value1['DiscountPrice'])."</strong></div> ";
														echo "<div class='cal actual_price pull-right' style='color:gray;'>".price_fomat($value1['Price'])."</div>";  
												?>
												</div>
											</div>
										</div> 
									<?php } ?>										
								</div><!-- /row -->
								<div class="col-xs-12 clear no-padding"><hr class="no-margin"></div> <!-- sep line -->
						<?php  }  ?>
							<!-- End product List -->						 
						<?php } } else { ?>
								 <div align="center" class="alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10 clear"><i class="fa fa-warning"></i>  No products found.</div>							
						<?php } ?>						
					</div><!-- /.box-body -->
				</div>		
			</div>	
			
			<div class="col-xs-12 product_list no-padding" id="userinstore">
			
				<h1 class="col-sm-8 no-padding no-margin" id="Totalusers">Customers in store   <?php if($TotalUsers > 0) echo "-   ".$TotalUsers; ?></h1> 
				<div class="col-lg-2 col-md-3  col-sm-4 col-xs-12 pull-right no-padding">
					<div class="search-form no-margin">
						<i class="fa fa-search"></i>
                        <input type="text" placeholder="Search Customers" value="<?php if(!empty($userSearch)) echo $userSearch; ?>" class="form-control LH12" name="usersearch" id="usersearch">
					</div>
                </div>
				
				<div class="box box-primary no-padding" id="store-users">
					<div class="row box-body" id="users_block">
						<?php if(isset($userList) && !empty($userList)) { ?>
						<!-- start user List -->
						<?php foreach($userList as $key=>$users) { ?>																		
							<div class="col-xs-6 col-sm-3 col-md-2 "  title="Add to cart" alt="Add to cart"  style="cursor:pointer;"  id="user<?php echo $users['id'];?>" onclick="return hideShowUsers('<?php echo $users['id'];?>','<?php echo $users['Photo'];?>','<?php echo ucfirst($users['FirstName']).' '.ucfirst($users['LastName']);?>','<?php echo $users['CurrentBalance'];?>');">
								<div class="small-box" style="min-height:70px;padding:5px;">
									<div class="col-xs-4 col-md-4  col-lg-3 no-padding text-left"><img width="50" height="60" style="padding:0" src="<?php echo $users['Photo']; ?>" alt=""></div>
									<div class="col-xs-8  col-md-8   col-lg-9  text-left">
										<?php echo ucfirst($users['FirstName']).'<br>'.ucfirst($users['LastName']);?>
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
						<div class="col-xs-12 clear text-center" id="loadmorehome"> <a style="cursor:pointer" class="loadmore" id="loadmore" name="loadmore" class="btn btn-success" title="Load More" onclick="return loadMoreUser(<?php echo $userLoadMore; ?>);"><i class="fa fa-download"></i> <strong>Load More</strong></a></div>

						<?php } ?>
				</div>				
					</div><!-- /.box-body -->
						<input type="hidden" id="UserStart" name="UserStart" value="0" />
						<input type="hidden" id="perviousUserSearch" name="perviousUserSearch" value="" />						
							
			</div>	
			<!-- <input type="submit" id="SearchSubmit" name="SearchSubmit" value="search" style="display:none;"/> -->
			</form>
		 </div>		
		 <?php } ?>
		</section>
		<?php footerLogin(); ?>
	<?php commonFooter(); ?>
	<script type="text/javascript">
		$('.Product_fancybox').fancybox();			
		$("a[href='#bottom']").click(function() {
			  var pos = $("#store-users").position().top;
			  var ht = $(document).height() - pos;
			 $("html, body").animate({ scrollTop: $("#userinstore").offset().top - $(".navbar").height()  }, {duration: $("#userinstore").offset().top});
			  return false; 
	    });
		
		$('#productsearch').keypress(function(event) {			
		    var keycode = (event.keyCode ? event.keyCode : event.which);
		    if(keycode == '13') {
			   var product = this.value;
			   		 var searchPath = '<?php echo SITE_PATH;?>/Search?search=1';
					 $.ajax({
				        type: "GET",
				        url: searchPath,
				        data: 'productsearch='+product,
				        success: function (result){
							$('#products_block').html(result);
				        }			
				    });
			   return false;
		    }
		});
		
		$('#usersearch').keypress(function(event) {	
			$('#UserStart').val('0');
			var start					= parseInt($('#UserStart').val());
		    var keycode = (event.keyCode ? event.keyCode : event.which);
		    if(keycode == '13') {
				var userKey  = this.value;			   
				 var searchPath = '<?php echo SITE_PATH;?>/Search?search=1';
				 $.ajax({
					type: "GET",
					url: searchPath,
					data: 'usersearch='+userKey+"&clear=1",
					success: function (result){
						$('#users_block').html(result);	
						start	= start + 6;
						$('#UserStart').val(start);
						$('#perviousUserSearch').val(userKey)
					}			
				});				
			   return false;
		    }
		});
		
	</script>
</html>
