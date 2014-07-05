<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
commonHead();
require_once('controllers/ProductController.php');
$ProductObj   =   new ProductController();
if(isset($_GET['viewId']) && $_GET['viewId'] != '' ){
	$productListResult  = $ProductObj->selectProductDetail($_GET['viewId']);
	if(isset($productListResult) && is_array($productListResult) && count($productListResult) > 0){
?>
<body class="skin-blue">
	<?php top_header(); ?>	
	<!-- Content Header (Page header) -->
	<section class="content-header no-padding">
		<div class="col-xs-12"> 
			<h1><i class="fa fa-search"></i> View Product</h1>
		</div>
	</section>
	 <!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-md-12 view-page"> 
				<div class="box box-primary"> 
					<div class="form-group col-sm-6 row">
						<label class="col-sm-5  col-xs-5 col-lg-3  " >Item Name</label>
						<div  class="col-sm-7  col-xs-7">
						<?php if(!empty($productListResult[0]->ItemName)) { echo ucfirst($productListResult[0]->ItemName); } else { echo "-"; } ?></div>
					</div>	
					<div class="form-group col-sm-6 row">
						<label class="col-sm-5  col-xs-5 col-lg-3 " >Product Category</label>
						<div  class="col-sm-7  col-xs-7">
						<?php if(!empty($productListResult[0]->CategoryName)) { echo ucfirst($productListResult[0]->CategoryName); } else { echo "-"; } ?></div>
					</div>
					<!-- <div class="form-group col-sm-6 row">									
						<label class="col-sm-5  col-xs-5 col-lg-3 " >Item Description</label>
						<div  class="col-sm-7  col-xs-7">										
						<?php //if(!empty($productListResult[0]->ItemDescription)) { echo $productListResult[0]->ItemDescription; } else { echo "-"; } ?></div>									
					</div> -->
					<div class="form-group col-sm-6 row">									
						<label class="col-sm-5  col-xs-5 col-lg-3 " >Discount Applied</label>
						<div  class="col-sm-7  col-xs-7">										
						<?php if($productListResult[0]->DiscountApplied == '1') { echo 'Yes'; } else { echo 'No'; } ?></div>									
					</div>
					<div class="form-group col-sm-6 row">
						<label class="col-sm-5  col-xs-5 col-lg-3 " >Price</label>
						<div  class="col-sm-7  col-xs-7"><?php if(!empty($productListResult[0]->Price)) { echo price_fomat($productListResult[0]->Price); } else { echo "-"; } ?></div>	
					</div>	        
					<?php if($productListResult[0]->DiscountApplied == '1') {?>
					<div class="form-group col-sm-6 row">
						<label class="col-sm-5  col-xs-5 col-lg-3 " >Discount Tier</label>
						<div  class="col-sm-7  col-xs-7"><?php if(isset($productListResult[0]->Discount)){?><span title="Price"><?php echo $discountTierArray[$productListResult[0]->Discount]."%"; ?></span><?php  } ?>								</div>	
					</div>
						
					<?php
						if($productListResult[0]->Discount > 0)
							$dis_cost = floatval($productListResult[0]->Price - (($productListResult[0]->Price / 100) * $discountTierArray[$productListResult[0]->Discount]));
						else
							$dis_cost = 0;
					?>
					<div class="form-group col-sm-6 row">
						<label class="col-sm-5  col-xs-5 col-lg-3 " >Discounted Price</label>
						<div  class="col-sm-7  col-xs-7">
						<?php echo price_fomat($dis_cost);?>
						</div>
					</div>
					<?php } ?>
					
				
					<!-- <div class="form-group col-sm-6 row">
						<label class="col-sm-5  col-xs-5 col-lg-3 " >Item Type</label>
						<div  class="col-sm-7  col-xs-7">
						<?php //if(isset($productListResult[0]->ItemType) && $productListResult[0]->ItemType != ''){?><span title="Type"><?php //echo $item_type_array[$productListResult[0]->ItemType]; ?></span><br><?php  //} ?>
						</div>
					</div>	 -->
					<!-- <div class="form-group col-sm-6 row">
						<label class="col-sm-5  col-xs-5 col-lg-3 " >Quantity</label>
						<div  class="col-sm-7  col-xs-7">
						<?php //if(!empty($productListResult[0]->Quantity)) { echo $productListResult[0]->Quantity; } else { echo "-"; } ?></div>						
					</div>	 -->
					<?
						$image_path = '';
						$photo = $productListResult[0]->Photo;
						$image_path = SITE_PATH.'/Refer/site_source/no_photo_product1.png';
						if(isset($photo) && $photo != ''){
							if(SERVER){
								if(image_exists(8,$photo))
									$image_path = PRODUCT_IMAGE_PATH.$photo;
							}
							else{
								if(file_exists(PRODUCT_IMAGE_PATH_REL.$photo))
									$image_path = PRODUCT_IMAGE_PATH.$photo;
							}
						}
						$icon_image_path = '';
						$merchant_image = $productListResult[0]->Icon;
						$icon_image_path = ADMIN_IMAGE_PATH.'no_user.jpeg';
						if(isset($merchant_image) && $merchant_image != ''){
							if(SERVER){
								if(image_exists(6,$merchant_image))
									$icon_image_path = MERCHANT_ICONS_IMAGE_PATH.$merchant_image;
							}
							else{
								if(file_exists(MERCHANT_ICONS_IMAGE_PATH_REL.$merchant_image))
									$icon_image_path = MERCHANT_ICONS_IMAGE_PATH.$merchant_image;
							}
						}
					?>
					<div class="form-group col-sm-6 row">
						<label class="col-sm-5  col-xs-5 col-lg-3 " >Image</label>
						<div  class="col-sm-7  col-xs-7">
							<?php if(!empty($productListResult[0]->Photo)) { ?>
								<a href="<?php echo $image_path; ?>" class="fancybox" title="<?php echo $productListResult[0]->ItemName; ?>">
									<img width="100" height="100" align="top" class="img_border" src="<?php echo $image_path;?>" >
								</a>
							<?php } else {?>
								<img width="100" height="100" align="top" class="img_border" src="<?php echo $image_path;?>" >
							<?php } ?>
						</div>
					</div>	
					<div class="form-group col-sm-6 row">
						<label class="col-sm-5  col-xs-5 col-lg-3 " >Merchant Details</label>
						<div  class="col-sm-7  col-xs-7">
							<?php if(!empty($productListResult[0]->Icon)) { ?>
								
								<a href="<?php echo $icon_image_path; ?>" class="fancybox" title="<?php echo $productListResult[0]->CompanyName; ?>">
									<img width="50" height="50" align="top" class="img_border" src="<?php echo $icon_image_path;?>" >
								</a>
							<?php } else {?>
								<img width="50" height="50" align="top" class="img_border" src="<?php echo $icon_image_path;?>" >
							<?php } ?>
							<?php if(isset($productListResult[0]->CompanyName) && $productListResult[0]->CompanyName != ''){ ?>
								<span title="Company Name">
									<?php echo "<b>".$productListResult[0]->CompanyName."</b>";  ?>
								</span><br> 
								<?php } ?>  
						</div>
					</div>	
					<div class="form-group col-sm-6 row">
						<label class="col-sm-5  col-xs-5 col-lg-3 " >Status</label>
						<div  class="col-sm-7  col-xs-7">
						<?php if($productListResult[0]->Status == 1) { echo 'Active'; } else { echo "Inactive"; } ?></div>
					</div>
					<div class="box-footer col-sm-12" align="center">
						<?php 
							$href_page = "ProductList";
						?>	
						<!--<a href="MerchantManage?editId=<?php//if(isset($_GET['viewId']) && $_GET['viewId'] != '') echo $_GET['viewId']; ?>" title="Edit" alt="Edit" class="btn btn-success">Edit</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-->
						<a href="<?php if(isset($href_page) && $href_page != '' ) echo $href_page; else echo 'ProductList';?>" class="btn btn-default" name="Back" id="Back" title="Back" alt="Back" >Back </a>
					
				</div>
				</div>		
			</div>		
		</div><!-- /.row -->
	</section><!-- /.content -->				  	
<?php }
}commonFooter(); ?>
<script type="text/javascript">
$(document).ready(function() {
	$('.fancybox').fancybox();	
});
</script>

</html>
