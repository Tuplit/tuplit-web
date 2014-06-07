<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();

//getting merchant details
if(isset($_SESSION['merchantDetailsInfo']) && is_array($_SESSION['merchantDetailsInfo'])){
	$merchantInfo  =	$_SESSION['merchantDetailsInfo'];	
	if(!empty($merchantInfo['DiscountTier']) || $merchantInfo['DiscountTier'] != 0 ) {
	}
	else {
		$hide	= 	1;	
	}
}
else{
	$merchantId					= 	$_SESSION['merchantInfo']['MerchantId'];
	$url						=	WEB_SERVICE.'v1/merchants/'.$merchantId;
	$curlMerchantResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);	
	if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201 && $curlMerchantResponse['merchant']['MerchantId'] != '' ) 
	 {
		$_SESSION['merchantDetailsInfo']   =	$curlMerchantResponse['merchant'];
		$merchantInfo  =	$_SESSION['merchantDetailsInfo'];
	}
}
//getting product List
$url					=	WEB_SERVICE.'v1/products/';
$curlCategoryResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
if(isset($curlCategoryResponse) && is_array($curlCategoryResponse) && $curlCategoryResponse['meta']['code'] == 201 && is_array($curlCategoryResponse['ProductList']) ) {
	if(isset($curlCategoryResponse['ProductList']))
	$productList = $curlCategoryResponse['ProductList'];	
} else if(isset($curlCategoryResponse['meta']['errorMessage']) && $curlCategoryResponse['meta']['errorMessage'] != '') {
		$errorMessage	=	$curlCategoryResponse['meta']['errorMessage'];
} else {
		$errorMessage	= 	"Bad Request";
} 
commonHead();
?>

<body class="skin-blue fixed" onload="fieldfocus('ItemName');">
		<?php top_header(); ?>
		<section class="content">
		<div class="col-lg-10" style="margin:auto;float:none" >	
			<?php if(isset($hide) && $hide == 1) { ?> <br><br><br>
				<div align="center" class="alert alert-danger alert-dismissable  col-lg-5 col-sm-7  col-md-5 col-xs-12"><i class="fa fa-warning"></i>&nbsp;&nbsp;Cannot add products until Price Scheme is selected in Settings</div>
			<?php } else { ?>
			<section class="content-header">
                <h1>Product List</h1>
            </section>
			<div class="row product_list">
				<div class="box box-primary no-padding">
					<div class="box-body">
						<div class="col-xs-8 no-padding">
							<h4><strong>Categories</strong> Drag & drop items to reorder them</h4>
						</div>
						<div class="col-xs-4 text-right pad"><a  href="Category?show=0" class="cateWindow"><i class="fa fa-plus"></i> Add Category</a></div>
						<?php if(isset($productList) && !empty($productList)) { ?>
						<!-- start product List -->
						<div class="col-xs-12 no-padding"><hr></div>
						<?php foreach($productList as $key=>$value) { ?>																		
								<div  class="col-xs-8 no-padding">
									<h4><strong><?php echo $value[0]['CategoryName']; ?></strong>
									<?php if($value[0]['CategoryMerchnatId'] == 0) {   ?>
										This Category cant't be renamed
									<?php } else {?>
										&nbsp;&nbsp;<a class="cateWindow" href="Category?show=0&edit=<?php echo $value[0]['fkCategoryId']; ?>&categoryName=<?php echo base64_encode($value[0]['CategoryName']); ?>"><i class="fa fa-edit"></i></a> 
									<?php } ?>
									</h4>
								</div>
								<div class="col-xs-4 text-right pad"><a href="Product?show=0&add=<?php echo $key; ?>" class="newWindow"><i class="fa fa-plus"></i> Add Item</a></div>
								<div class="row clear">										
									<?php foreach($value as $key1=>$value1) { ?>	
										<div class="col-xs-3 col-sm-3 col-md-2 <?php if($value1['Status'] == 2) echo "inactive";?>">
											<div class="small-box ">
												<a href="<?php echo $value1['Photo'];?>" class="Product_fancybox" title="<?php echo $value1['ItemName'];?>">
													<img height="100" width="100" src="<?php echo $value1['Photo']; ?>" alt=""><br>
												</a>
												<a class="edit newWindow" href="Product?show=0&edit=<?php echo $value1['ProductId']; ?>"><i class="fa fa-pencil fa-lg"></i></a>
												<div class="product_price">
												<span class="title_product" style=""><?php echo $value1['ItemName'];?></span>
												<?php echo "<div class='cal'>$".$value1['Price']."</div> "; 
												if($merchantInfo['DiscountType'] == 0){
													if($value1['DiscountApplied'] == 1) { 
														$discount = $value1['Price'] - (($value1['Price']/100) * $merchantInfo['DiscountTier']);
														echo "<div class='cal'>$".floatval($discount)."</div>";  
													}	
												}
												else if($merchantInfo['DiscountType'] == 1)
												{
													if($merchantInfo['DiscountProductId'] == 'all') { 
														if($value1['DiscountApplied'] == 1) { 
															$discount = $value1['Price'] - (($value1['Price']/100) * $merchantInfo['DiscountTier']);
															echo "<div class=''>$".floatval($discount) ."</div>"; 
														}
													}
													else{
														if(isset($merchantInfo['DiscountProductId']) && $merchantInfo['DiscountProductId'] != ''){
															$productListArray = explode(',',$merchantInfo['DiscountProductId']);
															 if(isset($productListArray) &&  in_array($value1['ProductId'],$productListArray)) { 
																if($value1['DiscountApplied'] == 1) { 
																	$discount = $value1['Price'] - (($value1['Price']/100) * $merchantInfo['DiscountTier']);
																	echo "<div class=''>$".floatval($discount)."</div>";  
																}
															 }
														}
													}
												}?>
												</div>
											</div>
										</div> 
									<?php } ?>										
								</div><!-- /row -->
								<div class="col-md-12 no-padding"><hr></div> <!-- sep line -->
						<?php }  ?>
							<!-- End product List -->						 
						<?php } else { ?>
							<div class="col-xs-4 text-right pad"><a href="Product?show=0" class="newWindow"><i class="fa fa-plus"></i> Add Item</a></div>
							<div class="row clear">		
								 <div align="center" class="alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10"><i class="fa fa-warning"></i>  No items found. Please add items</div>							
							</div>							
						<?php } ?>						
					</div><!-- /.box-body -->
				</div>					
			</div>	
			<?php } ?>
		 </div>
		</section>
		<?php footerLogin(); ?>
	<?php commonFooter(); ?>
	<script type="text/javascript">
		$(document).ready(function() {
			$('.Product_fancybox').fancybox();			
			$(".newWindow").fancybox({
					scrolling: 'none',			
					type: 'iframe',
					width: '380',
					maxWidth: '100%',  // for respossive width set					
					fitToView: false,
					afterClose : function() {
					location.reload();
					return;
				}
			});
				
			$(".cateWindow").fancybox({
					scrolling: 'none',			
					type: 'iframe',
					width: '280',
					maxWidth: '100%',  // for respossive width set					
					fitToView: false,
					afterClose : function() {
					location.reload();
					return;
				}
			});
			

		});
	</script>
</html>
