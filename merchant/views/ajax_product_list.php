<?php
require_once('includes/CommonIncludes.php');
//Search Submit
if(isset($_GET['search']) && !empty($_GET['search'])) {
	if(isset($_GET['productsearch'])) {
		$Search 								= $_GET['productsearch'];
		$url1				=	WEB_SERVICE.'v1/products/?Search='.$Search;
		$curlCategoryResponse 	= 	curlRequest($url1, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
		if(isset($curlCategoryResponse) && is_array($curlCategoryResponse) && $curlCategoryResponse['meta']['code'] == 201 && is_array($curlCategoryResponse['ProductList']) ) {
			$productList 		= 	$curlCategoryResponse['ProductList'];	
		}
	}
	if(isset($_GET['usersearch'])) {
		$userSearch 							= $_GET['usersearch'];	
		$url2				=	WEB_SERVICE.'v1/users/search?Search='.$userSearch.'&Latitude=5.25&Longitude=20.25';
		$curlCategoryResponse 	= 	curlRequest($url2, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
		if(isset($curlCategoryResponse) && is_array($curlCategoryResponse) && $curlCategoryResponse['meta']['code'] == 201 && is_array($curlCategoryResponse['userList']) ) {
			$userList 			= 	$curlCategoryResponse['userList'];	
		}
	}
}
die();

?>
<body class="skin-blue fixed" onload="fieldfocus('productsearch');">
		<?php top_header(); ?>
		
		<section class="content">
		<?php if(isset($msg) && $msg != '') { ?>
			<div align="center" id="showmessage" class="alert <?php  echo $class;  ?> alert-dismissable  col-xs-10 col-sm-5 col-lg-4"><i class="fa <?php  echo $class_icon;  ?>"></i>  <?php echo $msg; ?></div>
		<?php } ?>		
		<div class="col-lg-12 box-center">
			<form method="post" action="CreateOrder" id="OrderForm" name="OrderForm">
			<div class="product_list" id="Oders_Merchant" style="<?php if(isset($showorder) && $showorder >0) echo ''; else echo "display:none;"; ?>">
				<div class="box box-primary no-padding">
					<div class="row box-body bg-gray">
						<div class="col-xs-12 col-sm-10 box-center">
						<table width="100%" id="tbl1" align="center">							
							<tr>
								<td width="25%"><input type="hidden" id="OrderProductIds" name="OrderProductIds" value="<?php if(isset($orderProductIds) && !empty($orderProductIds) && isset($showorder) && $showorder >0) echo $orderProductIds;?>"></td>
								<td width="50%" align="left"></td>
								<td width="25%" align="right"></td>
							</tr>
							<?php if(isset($showorder) && $showorder >0) {
									if(isset($orderProducts) && is_array($orderProducts) && count($orderProducts) > 0) {
										foreach($orderProducts as $orderval) {
							?>
							<tr id="orderrow<?php echo $orderval['ProductId']; ?>">
								<td>
									<i class="fa fa-minus" onclick="return removequantity(<?php echo $orderval['ProductId']; ?>);"></i>
									<input id="quantity<?php echo $orderval['ProductId']; ?>" type="text" readonly="" onkeypress="return isNumberKey(event);" value="<?php echo $orderval['ProductsQuantity']; ?>" style="width:50px;text-align:center;" name="quantity<?php echo $orderval['ProductId']; ?>">
									<i class="fa fa-plus" onclick="return addquantity(<?php echo $orderval['ProductId']; ?>);"></i>
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
						<div class="col-xs-12 no-padding"><hr style="border-color:#b2b2b2;"></div>
						<table width="100%" id="tbl2" align="center">							
							
							<tr>
								<td width="25%"><input type="hidden" id="OrderTotal" name="OrderTotal" value="<?php if(isset($OrderTotal)) echo $OrderTotal; ?>"><input type="hidden" id="userImageValue" name="userImageValue" value="<?php if(isset($UsersImagepath)) echo '1'; ?>"></td>
								<td colspan="2" width="50%" ><b>Total</b></td>
								<td width="25%" align="right"><b>$<span class="OrderTotalShow"><?php if(isset($OrderTotal)) echo $OrderTotal; ?></span></b></td>
							</tr>
							<tr><td colspan="4" height="20"></td></tr>
							<tr id="userTable">
								<td ><a style="cursor:pointer" class="userTotal" onclick="return clearOrders()">Clear Order</a><input type="hidden" id="userDefaultImage" name="userDefaultImage" value="<?php echo MERCHANT_SITE_IMAGE_PATH."no_user.jpeg";?>"/></td>
								<td><img width="25" height="25" id="userImage" class="img_border" src="<?php if(isset($UsersImagepath)) echo $UsersImagepath; else echo MERCHANT_SITE_IMAGE_PATH."no_user.jpeg";?>">&nbsp;&nbsp;<span id="username"><?php if(isset($Username)) echo $Username; else echo 'No User Selected'; ?></span> </td>
								<td><a id="bottom" href="#bottom"><?php if(isset($UserId)) echo "Change User"; else echo "Select User"; ?></a><input type="hidden" id="CurrentUserId" name="CurrentUserId" value="<?php if(isset($UserId)) echo $UserId; ?>"><input type="hidden" id="OrderUserImage" name="OrderUserImage" value="<?php if(isset($UsersImagepath)) echo $UsersImagepath; ?>"><input type="hidden" id="OrderUserName" name="OrderUserName" value="<?php if(isset($Username)) echo $Username;?>"> </td>
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
									<div class="col-xs-3 col-sm-3 col-md-2 " onclick="return hideShowOrders('<?php echo $value1['fkProductsId'];?>','<?php echo $value1['ItemName'];?>','<?php echo $value1['Photo']; ?>','<?php echo $value1['Price']; ?>','<?php echo $value1['DiscountPrice']; ?>');">
										<div class="small-box ">
												<img height="100" width="100" src="<?php echo $value1['Photo']; ?>" alt=""><br>
											<div class="product_price">
											<span class="title_product" style=""><?php echo $value1['ItemName'];?></span>
											<?php echo "<div class='cal'>$".$value1['Price']."</div> "; 
												if($value1['DiscountPrice'] != 0)
													echo "<div class=''>$".$value1['DiscountPrice']."</div>";
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
                        <input type="text" placeholder="Search Products" value="<?php if(!empty($Search)) echo $Search; ?>" class="form-control" name="productsearch" id="productsearch">
					</div>
                </div>
				
				<div class="box box-primary no-padding">
					<div class="box-body">
						<?php if(isset($productList) && !empty($productList)) { ?>						
						<!-- start product List -->
						<?php foreach($productList as $key=>$value) { if(!empty($value[0]['ProductId'])) {	?>
								<div  class="col-xs-8 no-padding">
									<h4><?php echo $value[0]['CategoryName']; ?></h4>
								</div>
								<div class="col-xs-4 text-right pad" style="font-size:20px;cursor:pointer" onclick="return productCategoryHideShow(<?php echo $key; ?>)"><i id="plusMinus<?php echo $key; ?>" class="fa <?php if(empty($Search)) echo "fa-caret-down"; else echo "fa-caret-up"; ?>"></i><input type="hidden" id="rowHidden<?php echo $key; ?>" value="1"></div>
								
								<div class="row clear" id="rowHide<?php echo $key; ?>" <?php if(empty($Search)) echo 'style="display:none;"'; ?>>										
									<?php foreach($value as $key1=>$value1) { ?>	
										<div class="col-xs-3 col-sm-3 col-md-2 <?php if($value1['Status'] == 2) echo "inactive";?>" onclick="return hideShowOrders('<?php echo $value1['ProductId'];?>','<?php echo $value1['ItemName'];?>','<?php echo $value1['Photo']; ?>','<?php echo $value1['Price']; ?>','<?php echo $value1['DiscountPrice']; ?>');">
											<div class="small-box ">
												<img height="100" width="100" src="<?php echo $value1['Photo']; ?>" alt=""><br>
												<div class="product_price">
												<span class="title_product" style=""><?php echo $value1['ItemName'];?></span>
												<?php 	echo "<div class='cal'>$".$value1['Price']."</div> ";
														if($value1['DiscountPrice'] != 0)
															echo "<div class=''>$".$value1['DiscountPrice']."</div>";  
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
			
				<h1 class="col-sm-8 no-padding no-margin">Users in store</h1>
				<div class="col-lg-2 col-md-3  col-sm-4 col-xs-12 pull-right no-padding">
					<div class="search-form no-margin">
						<i class="fa fa-search"></i>
                        <input type="text" placeholder="Search Users" value="<?php if(!empty($userSearch)) echo $userSearch; ?>" class="form-control" name="usersearch" id="usersearch">
					</div>
                </div>
				
				<div class="box box-primary no-padding" id="store-users">
					<div class="row box-body">
						<?php if(isset($userList) && !empty($userList)) { ?>
						<!-- start product List -->
						<?php foreach($userList as $key=>$users) { ?>																		
							<div class="col-xs-10 col-sm-3 col-md-2 " id="user<?php echo $users['id'];?>" onclick="return hideShowUsers('<?php echo $users['id'];?>','<?php echo $users['Photo'];?>','<?php echo $users['FirstName'].' '.$users['LastName'];?>','<?php echo $users['CurrentBalance'];?>');">
								<div class="small-box" style="min-height:85px;padding:5px;">
									<div class="col-xs-5 no-padding text-left"><img height="75" width="75" src="<?php echo $users['Photo']; ?>" alt=""></div>
									<div class="col-xs-7  text-left">
										<?php echo $users['FirstName'].'<br>'.$users['LastName'];?>
									</div>
								</div>
							</div> 	
						<?php }  ?>
							<!-- End product List -->						 
						<?php } else { ?>
								 <div align="center" class="clear alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10"><i class="fa fa-warning"></i>  No users found in your location.</div>							
						<?php } ?>						
					</div><!-- /.box-body -->
				</div>					
			</div>	
			<input type="submit" id="SearchSubmit" name="SearchSubmit" value="search" style="display:none;"/>
			</form>
		 </div>		
		</section>
		<?php footerLogin(); ?>
	<?php commonFooter(); ?>
	<script type="text/javascript">
		$(document).ready(function() {
			$('.order-block').hide();		
			$('.logo').html('Tuplit Merchant Portal - Create New Order');		
		});
		
		$("a[href='#bottom']").click(function() {
			//
			  var pos = $("#store-users").position().top;
			  var ht = $(document).height() - pos;
			 $("html, body").animate({ scrollTop: $("#userinstore").offset().top - $(".navbar").height()  }, {duration: $("#userinstore").offset().top});
			  return false; 
	    });
		
		$('#productsearch').keypress(function(event) {
			
		    var keycode = (event.keyCode ? event.keyCode : event.which);
		    if(keycode == '13') {
			   var product = this.value;
			   if(product != ''){
			   	 var searchPath = '<?php echo SITE_PATH;?>/search.php?search='+product;
			   }
			   else{
			   	 return false;
			   }
		      
			   return false;
		    }
		});
		
	</script>
</html>
