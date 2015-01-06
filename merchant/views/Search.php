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
		<?php if(isset($specialArray) && count($specialArray) > 0) { ?>
		<!-- start product List -->
			<div style="cursor:pointer"  id="hideSep0_1" class="sep_title col-xs-12 no-padding <?php if(isset($Search) && !empty($Search)) echo "blockup"; else echo "blockdown";?>" style="cursor:pointer;" onclick="return productCategoryHideShow('0_1','2')">
				<div class="col-xs-8 no-padding">
					<h4>Specials</h4>
				</div>
				<div class="col-xs-4 text-right pad" style="font-size:20px;cursor:pointer;color:#e3e3e3;"><i id="plusMinus0_1" class="fa fa-caret-up"></i><input type="hidden" id="rowHidden0_1" value="<?php if(isset($Search) && !empty($Search)) { echo "0";} else { echo "1";}?>"></div>
			</div>		
			<div class="clear product_show sep_title <?php if(isset($Search) && !empty($Search)) echo "blockup"; else echo "blockdown";?>" id="rowHide0_1" style="display:<?php if(isset($Search) && !empty($Search)) echo "block"; else echo "none";?>">										
				<?php foreach($specialArray as $key1=>$value1) { ?>	
					<div class="col-xs-12 col-sm-4 col-md-2 col-lg-2 LH196 <?php if($value1['Status'] == 2) echo "inactive";?>" style="cursor:pointer;" >
						<div class="small-box " >
							<a href="javascript:void(0);" class="Product_fancybox" onclick="return loaded; return hideShowOrders('<?php echo $value1['ProductId'];?>','<?php echo addslashes($value1['ItemName']);?>','<?php echo $value1['Photo']; ?>','<?php echo $value1['Price']; ?>','<?php echo $value1['DiscountPrice']; ?>');" title="<?php echo ucfirst($value1['ItemName']);?>">
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
		<?php $categoryIds = ''; foreach($productList as $key=>$value) { if(!empty($value[0]['ProductId'])) { $categoryIds .= $key.',';	?>
				<div style="cursor:pointer" id="hideSep<?php echo $key; ?>" class="col-xs-12 no-padding sep_title <?php if(isset($Search) && !empty($Search)) echo "blockup"; else echo "blockdown";?>" style="cursor:pointer;" onclick="return productCategoryHideShow(<?php echo $key; ?>,'2')">
					<div class="col-xs-8 no-padding">
						<h4><?php echo $value[0]['CategoryName']; ?></h4>
					</div>
					<div class="col-xs-4 text-right pad" style="font-size:20px;cursor:pointer;color:#e3e3e3;"><i id="plusMinus<?php echo $key; ?>" class="fa fa-caret-up"></i><input type="hidden" id="rowHidden<?php echo $key; ?>" value="<?php if(isset($Search) && !empty($Search)) { echo "0"; } else { echo "1";}?>"></div>
				</div>		
				<div class="clear product_show sep_title <?php if(isset($Search) && !empty($Search)) echo "blockup"; else echo "blockdown";?>" id="rowHide<?php echo $key; ?>" style="display:<?php if(isset($Search) && !empty($Search)) echo "block"; else echo "none";?>">										
					<?php $value = subval_sort($value,'Ordering');  foreach($value as $key1=>$value1) { ?>	
						<div class="col-xs-12 col-sm-4 col-md-2 col-lg-2 LH196 <?php if($value1['Status'] == 2) echo "inactive";?>" style="cursor:pointer;" >
							<div class="small-box ">												
								<a href="javascript:void(0);" class="Product_fancybox"  onclick="return loaded; return hideShowOrders('<?php echo $value1['ProductId'];?>','<?php echo addslashes($value1['ItemName']);?>','<?php echo $value1['Photo']; ?>','<?php echo $value1['Price']; ?>','<?php echo $value1['DiscountPrice']; ?>');"  title="<?php echo ucfirst($value1['ItemName']);?>">
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
		<input type="hidden" id="categoryIds_val" value="<?php echo rtrim($categoryIds,','); ?>" />
		<!-- End product List -->						 
		<?php } } else { ?>
				 <div align="center" class="alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10 clear" style="margin-top:20px"><i class="fa fa-warning"></i>  No products found.</div>							
		<?php } ?>
	<?php }
	if(isset($_GET['usersearch']) || isset($_GET['load'])) {
		
		$Latitude	=	$Longitude	=	'';
		$TotalUsers	=	0;
		//echo "<pre>";print_r($_SESSION['merchantDetailsInfo']);echo "</pre>";
		if(isset($_SESSION['merchantDetailsInfo']['Latitude']) && isset($_SESSION['merchantDetailsInfo']['Longitude']) && !empty($_SESSION['merchantDetailsInfo']['Latitude']) && !empty($_SESSION['merchantDetailsInfo']['Longitude'])) {
			$Latitude 	= $_SESSION['merchantDetailsInfo']['Latitude'];
			$Longitude 	= $_SESSION['merchantDetailsInfo']['Longitude'];
		}
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
		
		if(!empty($Latitude) && !empty($Longitude)) {		
			if(!empty($userSearch))
				$url2				=	WEB_SERVICE.'v1/users/?Search='.$userSearch.'&Latitude='.$Latitude.'&Longitude='.$Longitude.'&Start='.$start;
			else
				$url2				=	WEB_SERVICE.'v1/users/?Latitude='.$Latitude.'&Longitude='.$Longitude.'&Start='.$start;
			
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
				<div class="col-xs-12 col-sm-4 col-md-2 col-lg-2 margin-bottom LH196" title="Add to cart" alt="Add to cart"  id="user<?php echo $users['id'];?>" onclick="return hideShowUsers('<?php echo $users['id'];?>','<?php echo $users['Photo'];?>','<?php echo ucfirst($users['FirstName']).' '.ucfirst($users['LastName']);?>','<?php echo $users['CurrentBalance'];?>');" style="cursor:pointer;">
					<div class="small-box" style="min-height:70px;padding:5px;margin:auto;">
						<div class="show_usersphoto">
							<div class="col-xs-12 col-md-12 col-lg-12 no-padding text-center"><img height="100" width="100" src="<?php echo $users['Photo']; ?>" alt="" style="padding:0"></div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 text-center">
							<?php echo ucfirst($users['FirstName']).'&nbsp;'.ucfirst($users['LastName']);?>
						</div>
					</div>
				</div> 	
			<?php }  ?>
			<!-- End product List -->	
			<input type="hidden" id="userTotalhide" name="userTotalhide" value="<?php if(isset($_SESSION['tuplitCreateOrderTotalUser'])) echo $_SESSION['tuplitCreateOrderTotalUser']; ?>"/>
			<?php $start = $start + 12;  if(($_SESSION['tuplitCreateOrderTotalUser'] - $start) > 0) { ?>
				<div align="center" id="loadmorehome" class="col-xs-12"><a class="loadmore" id="loadmore" name="loadmore" class="btn btn-success" title="Load More" onclick="return loadMoreUser();"><i class="fa fa-download"></i> Load More</a></div>
			<?php } ?>
			<?php  } else { if(isset($_GET['usersearch'])) { ?>
				<div align="center" class="clear alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10" style="margin-top:10px"><i class="fa fa-warning"></i>  No users found in your location.</div>							
			<?php } } } else { ?>	
				<div align="center" class="clear alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10"><i class="fa fa-warning"></i>  Please give valid address in Setting to search customers in your location.</div>							
			<?php } ?>
			<script type="text/javascript">			
				$('#Totalusers').html('Users in store  - <?php if(isset($TotalUsers))echo $TotalUsers; else echo "0"; ?>');
			</script>
		<?php
	}
} ?>

<?php die(); ?>


