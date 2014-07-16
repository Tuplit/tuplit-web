<?php
require_once('includes/CommonIncludes.php');
//Search Submit
if(isset($_GET['search']) && !empty($_GET['search'])) {
	if(isset($_GET['productsearch'])) {
		$Search 				= 	$_GET['productsearch'];
		$Search 				= 	urlencode($Search);
		if(!empty($Search))
			$url1					=	WEB_SERVICE."v1/products/?Search=".$Search."";
		else
			$url1					=	WEB_SERVICE."v1/products/";
		//echo "=====>".$url1;
		$curlCategoryResponse 	= 	curlRequest($url1, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
		if(isset($curlCategoryResponse) && is_array($curlCategoryResponse) && $curlCategoryResponse['meta']['code'] == 201 && is_array($curlCategoryResponse['ProductList']) ) {
			$productList 		= 	$curlCategoryResponse['ProductList'];	
		}
		 if(isset($productList) && !empty($productList)) { 
			if(isset($productList) && !empty($productList)) { 
				if(isset($productList[0]) && count($productList[0]) > 0) {
					foreach($productList[0] as $data) {
						if($data['ItemType']	==	2) 
							$dealsArray[]	=	$data;
						if($data['ItemType']	==	3) 
							$specialArray[]	=	$data;
					}
					unset($productList[0]);
				}
			}
		 ?>		
		<?php if(isset($dealsArray) && count($dealsArray) > 0) { ?>
		<!-- start product List -->
			<div style="cursor:pointer"  class="col-xs-8 no-padding" onclick="return productCategoryHideShow('0_0')">
				<h4>Deals</h4>
			</div>
			<div class="col-xs-4 text-right pad" style="font-size:20px;cursor:pointer" onclick="return productCategoryHideShow('0_0')"><i id="plusMinus0_0" class="fa <?php if(empty($Search)) echo "fa-caret-down"; else echo "fa-caret-up"; ?>"></i><input type="hidden" id="rowHidden0_0" value="1"></div>
			<div class="row clear" id="rowHide0_0" <?php if(empty($Search)) echo 'style="display:none;"'; ?>>										
				<?php foreach($dealsArray as $key1=>$value1) { ?>	
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
			</div>
			<div class="col-xs-12 clear no-padding"><hr class="no-margin"></div> <!-- sep line -->
		<?php } ?>
		<?php if(isset($specialArray) && count($specialArray) > 0) { ?>
		<!-- start product List -->
			<div style="cursor:pointer" class="col-xs-8 no-padding" onclick="return productCategoryHideShow('0_1')">
				<h4>Specials</h4>
			</div>
			<div class="col-xs-4 text-right pad" style="font-size:20px;cursor:pointer" onclick="return productCategoryHideShow('0_1')"><i id="plusMinus0_1" class="fa <?php if(empty($Search)) echo "fa-caret-down"; else echo "fa-caret-up"; ?>"></i><input type="hidden" id="rowHidden0_1" value="1"></div>
			<div class="row clear" id="rowHide0_1" <?php if(empty($Search)) echo 'style="display:none;"'; ?>>										
				<?php foreach($specialArray as $key1=>$value1) { ?>	
					<div class="col-xs-3 col-sm-3 col-md-2 <?php if($value1['Status'] == 2) echo "inactive";?>" onclick="return hideShowOrders('<?php echo $value1['ProductId'];?>','<?php echo $value1['ItemName'];?>','<?php echo $value1['Photo']; ?>','<?php echo $value1['Price']; ?>','<?php echo $value1['DiscountPrice']; ?>');">
						<div class="small-box ">
							<img height="100" width="100" src="<?php echo $value1['Photo']; ?>" alt=""><br>
							<div class="product_price">
							<span class="title_product" style=""><?php echo $value1['ItemName'];?></span>
							<?php 	echo "<div class='cal'>$".$value1['OriginalPrice']."</div> ";									
									echo "<div class=''>$".$value1['Price']."</div>";  
							?>
							</div>
						</div>
					</div> 
				<?php } ?>										
			</div>
			<div class="col-xs-12 clear no-padding"><hr class="no-margin"></div> <!-- sep line -->
		<?php } ?>
		<?php foreach($productList as $key=>$value) { if(!empty($value[0]['ProductId'])) {	?>
				<div style="cursor:pointer"  class="col-xs-8 no-padding" onclick="return productCategoryHideShow(<?php echo $key; ?>)">
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
				</div>
				<div class="col-xs-12 clear no-padding"><hr class="no-margin"></div> <!-- sep line -->
		<?php  }  ?>
		<!-- End product List -->						 
		<?php } } else { ?>
				 <div align="center" class="alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10 clear"><i class="fa fa-warning"></i>  No products found.</div>							
		<?php } ?>
	<?php }
	if(isset($_GET['usersearch']) || isset($_GET['load'])) {
		if(isset($_GET['clear']) && $_GET['clear']==1) {
			unset($_SESSION['tuplitCreateOrderUser']);
			unset($_SESSION['tuplitCreateOrderTotalUser']);
		}
	
		$userSearch	=	'';
		if(isset($_GET['usersearch']) && !empty($_GET['usersearch'])) {
			$userSearch 				= 	$_GET['usersearch'];
			$userSearch 				= 	urlencode($userSearch);
		}
		
		if(isset($_SESSION['tuplitCreateOrderUser'])) {			
			$_SESSION['tuplitCreateOrderUser']	=	$_SESSION['tuplitCreateOrderUser']	+ $userLoadMore;
			$start								=	$_SESSION['tuplitCreateOrderUser'];
		}
		else {
			if(isset($_GET['usersearch']) && !empty($_GET['usersearch'])){
				$_SESSION['tuplitCreateOrderUser']	=	0;
				$start			=	0;
			}else if(isset($_GET['usersearch']) && empty($_GET['usersearch'])){
				$_SESSION['tuplitCreateOrderUser']	=	0;
				$start			=	0;
			} 
			else { 
				$_SESSION['tuplitCreateOrderUser']	=	$userLoadMore;
				$start			=	$userLoadMore;
			}
		}		
	
		if(!empty($userSearch))
			$url2				=	WEB_SERVICE.'v1/users/?Search='.$userSearch.'&Latitude='.$_SESSION['merchantDetailsInfo']['Latitude'].'&Longitude='.$_SESSION['merchantDetailsInfo']['Longitude'].'&Start='.$start;
		else
			$url2				=	WEB_SERVICE.'v1/users/?Latitude='.$_SESSION['merchantDetailsInfo']['Latitude'].'&Longitude='.$_SESSION['merchantDetailsInfo']['Longitude'].'&Start='.$start;
		
		//echo "=====>".$url2;
		$curlCategoryResponse 	= 	curlRequest($url2, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
		if(isset($curlCategoryResponse) && is_array($curlCategoryResponse) && $curlCategoryResponse['meta']['code'] == 201 && is_array($curlCategoryResponse['userList']) ) {
			$userList 			= 	$curlCategoryResponse['userList'];
			$TotalUsers			=	$curlCategoryResponse['meta']['TotalUsers'];
			$_SESSION['tuplitCreateOrderTotalUser']	=	$TotalUsers;
		}
	?>
			<?php if(isset($userList) && !empty($userList)) { ?>
			<!-- start product List -->
			<?php foreach($userList as $key=>$users) { ?>																		
				<div class="col-xs-6 col-sm-3 col-md-2 " id="user<?php echo $users['id'];?>" onclick="return hideShowUsers('<?php echo $users['id'];?>','<?php echo $users['Photo'];?>','<?php echo $users['FirstName'].' '.$users['LastName'];?>','<?php echo $users['CurrentBalance'];?>');" style="cursor:pointer;">
					<div class="small-box" style="min-height:70px;padding:5px;">
						<div class="col-xs-4 col-md-4 col-lg-3 no-padding text-left"><img height="60" width="50" src="<?php echo $users['Photo']; ?>" alt="" style="padding:0"></div>
						<div class="col-xs-8 col-md-8 col-lg-9 text-left">
							<?php echo $users['FirstName'].'<br>'.$users['LastName'];?>
						</div>
					</div>
				</div> 	
			<?php }  ?>
			<!-- End product List -->	
			<input type="hidden" id="userTotalhide" name="userTotalhide" value="<?php if(isset($_SESSION['tuplitCreateOrderTotalUser'])) echo $_SESSION['tuplitCreateOrderTotalUser']; ?>"/>
			<?php $start = $start + 12;  if(($_SESSION['tuplitCreateOrderTotalUser'] - $start) > 0) { ?>
				<div align="center" id="loadmorehome"><a class="loadmore" id="loadmore" name="loadmore" class="btn btn-success" title="Load More" onclick="return loadMoreUser();"><i class="fa fa-download"></i> Load More</a></div>
			<?php } ?>
			<?php  } else { if(isset($_GET['usersearch'])) { ?>
				<div align="center" class="clear alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10"><i class="fa fa-warning"></i>  No customers found in your location.</div>							
			<?php } } ?>	
			<script type="text/javascript">			
				$('#Totalusers').html('Customers in store  - <?php if(isset($TotalUsers))echo $TotalUsers; else echo "0"; ?>');
			</script>
		<?php
	}
} ?>

<?php die(); ?>


