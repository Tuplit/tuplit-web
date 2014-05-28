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
						<label class="col-sm-4" >Item Name</label>
						<div  class="col-sm-8">
						<?php if(!empty($productListResult[0]->ItemName)) { echo $productListResult[0]->ItemName; } else { echo "-"; } ?></div>
					</div>	
					<div class="form-group col-sm-6 row">									
						<label class="col-sm-4" >Item Description</label>
						<div  class="col-sm-8">										
						<?php if(!empty($productListResult[0]->ItemDescription)) { echo $productListResult[0]->ItemDescription; } else { echo "-"; } ?></div>									
					</div>
					<div class="form-group col-sm-6 row">
						<label class="col-sm-4" >Price</label>
						<div  class="col-sm-8"><?php if(!empty($productListResult[0]->Price)) { echo "$".$productListResult[0]->Price; } else { echo "-"; } ?></div>	
					</div>	
					<div class="form-group col-sm-6 row">
						<label class="col-sm-4" >Discount Percentage</label>
						<div  class="col-sm-8"><?php if(isset($productListResult[0]->DiscountTier)){?><span title="Price"><?php echo $discount_array[$productListResult[0]->DiscountTier]."%"; ?></span><?php  } ?>								</div>	
					</div>
					<?php
						$dis_cost = $productListResult[0]->Price - (($productListResult[0]->Price / 100) * $discount_array[$productListResult[0]->DiscountTier]);
					?>
					<div class="form-group col-sm-6 row">
						<label class="col-sm-4" >Discount Price</label>
						<div  class="col-sm-8">
						<?php echo '$'.number_format((float)$dis_cost, 2, '.', ''); ?>
						</div>
					</div>
					<div class="form-group col-sm-6 row">
						<label class="col-sm-4" >Item Type</label>
						<div  class="col-sm-8">
						<?php if(isset($productListResult[0]->ItemType) && $productListResult[0]->ItemType != ''){?><span title="Type"><?php echo $item_type_array[$productListResult[0]->ItemType]; ?></span><br><?php  } ?>
						</div>
					</div>	
					<div class="form-group col-sm-6 row">
						<label class="col-sm-4" >Quantity</label>
						<div  class="col-sm-8">
						<?php if(!empty($productListResult[0]->Quantity)) { echo $productListResult[0]->Quantity; } else { echo "-"; } ?></div>						
					</div>	
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
					?>
					<div class="form-group col-sm-6 row">
						<label class="col-sm-4" >Image</label>
						<div  class="col-sm-8">
							<?php if(!empty($productListResult[0]->Photo)) { ?>
								<a href="<?php echo $image_path; ?>" class="fancybox" title="<?php echo $productListResult[0]->ItemName; ?>">
									<img width="100" height="100"align="top" class="img_border" src="<?php echo $image_path;?>" >
								</a>
							<?php } else {?>
								<img width="100" height="100" align="top" class="img_border" src="<?php echo $image_path;?>" >
							<?php } ?>
						</div>
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
