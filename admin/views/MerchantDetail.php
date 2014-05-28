<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
commonHead();
require_once('controllers/MerchantController.php');
$MerchantObj   =   new MerchantController();
$show = 0;
$categories = $MerchantObj->getCategories();

if(isset($_GET['proview']) && $_GET['proview'] != ''){
	$show = $_GET['proview'];
}
if(isset($_GET['viewId']) && $_GET['viewId'] != '' ){
	$merchantListResult  = $MerchantObj->selectMerchantDetail($_GET['viewId']);
	$merchantcategorylist  = $MerchantObj->selectMerchantCategory($_GET['viewId']);	
	$cat_id_array = array(); $cat_id_values = '';
	if(count($merchantcategorylist) > 0) {		
		$cat_id_array  = explode(',',$merchantcategorylist[0]->cat_id);
		$cat_id_values = $merchantcategorylist[0]->cat_id;			
	}
	if(!empty($cat_id_values))
		$cat_id_values = rtrim($cat_id_values,',');
	if(isset($merchantListResult) && is_array($merchantListResult) && count($merchantListResult) > 0){
?>
<body class="skin-blue">
	<?php if($show == 0 || $show == 1) top_header(); ?>
	
	<!-- Content Header (Page header) -->
	<section class="content-header no-padding">
		<div class="col-xs-12"> 
			<h1><i class="fa fa-search"></i> View Merchant</h1>
		</div>
	</section>
	 <!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-md-12 view-page"> 
				<div class="box box-primary"> 
					<div class="form-group col-sm-6 row">
						<label class="col-sm-4" >First Name</label>
						<div  class="col-sm-8">
						<?php if(!empty($merchantListResult[0]->FirstName)) { echo ucfirst($merchantListResult[0]->FirstName); } else { echo "-"; } ?></div>
					</div>	
					<div class="form-group col-sm-6 row">									
						<label class="col-sm-4" >Last Name</label>
						<div  class="col-sm-8">										
						<?php if(!empty($merchantListResult[0]->LastName)) { echo ucfirst($merchantListResult[0]->LastName); } else { echo "-"; } ?></div>									
					</div>
					<div class="form-group col-sm-6 row">
						<label class="col-sm-4" >Email</label>
						<div  class="col-sm-8"><?php if(!empty($merchantListResult[0]->Email)) { echo $merchantListResult[0]->Email; } else { echo "-"; } ?></div>	
					</div>									
					<div class="form-group col-sm-6 row">
						<label class="col-sm-4" >Company Name</label>
						<div  class="col-sm-8">
						<?php if(!empty($merchantListResult[0]->CompanyName)) { echo ucfirst($merchantListResult[0]->CompanyName); } else { echo "-"; } ?></div>
					</div>
					<div class="form-group col-sm-6 row">
						<label class="col-sm-4" >Phone Number</label>
						<div  class="col-sm-8">
						<?php if(!empty($merchantListResult[0]->PhoneNumber)) { echo $merchantListResult[0]->PhoneNumber; } else { echo "-"; } ?></div>
					</div>	
					<div class="form-group col-sm-6 row">
						<label class="col-sm-4" >Website Url</label>
						<div  class="col-sm-8">
						<?php if(!empty($merchantListResult[0]->WebsiteUrl)) { echo $merchantListResult[0]->WebsiteUrl; } else { echo "-"; } ?></div>						
					</div>	
					<div class="form-group col-sm-6 row">
						<label class="col-sm-4" >Location</label>
						<div  class="col-sm-8">
						<?php if(!empty($merchantListResult[0]->Location)) { echo ucfirst($merchantListResult[0]->Location); } else { echo "-"; } ?></div>
					</div>										
					<div class="form-group col-sm-6 row">
						<label class="col-sm-4" >Address</label>
						<div  class="col-sm-8">
						<?php if(!empty($merchantListResult[0]->Address)) { echo $merchantListResult[0]->Address; } else { echo "-"; } ?></div>
					</div>
					<div class="form-group col-sm-6 row">
						<label class="col-sm-4" >Short Description</label>
						<div  class="col-sm-8">
						<?php if(!empty($merchantListResult[0]->ShortDescription)) { echo "<p align='justify'>".$merchantListResult[0]->ShortDescription."</p>"; } else { echo "-"; } ?></div>
					</div>
					<div class="form-group col-sm-6 row">
						<label class="col-sm-4" >Description</label>
						<div  class="col-sm-8">
						<?php if(!empty($merchantListResult[0]->Description)) { echo "<p align='justify'>".$merchantListResult[0]->Description."</p>"; } else { echo "-"; } ?></div>
					</div>
					<?php
				  	    $image_path = '';
						$merchant_image = $merchantListResult[0]->Icon;
						$image_path = ADMIN_IMAGE_PATH.'no_user.jpeg';
						if(isset($merchant_image) && $merchant_image != ''){
							if(SERVER){
								if(image_exists(6,$merchant_image))
									$image_path = MERCHANT_ICONS_IMAGE_PATH.$merchant_image;
							}
							else{
								if(file_exists(MERCHANT_ICONS_IMAGE_PATH_REL.$merchant_image))
									$image_path = MERCHANT_ICONS_IMAGE_PATH.$merchant_image;
							}
						}
				 	?>	
					<div class="form-group col-sm-6 row">
						<label class="col-sm-4" >Icon</label>
						<div  class="col-sm-8">
							<img width="100" height="100" align="top" class="img_border" src="<?php echo $image_path;?>" >
						</div>
					</div>
					<?php
						$cimage_path = '';
						$cphoto = $merchantListResult[0]->Image;
						$cimage_path = ADMIN_IMAGE_PATH.'no_merchant_image.jpg';
						if(isset($cphoto) && $cphoto != ''){
							$cmerchant_image = $cphoto;
							if(SERVER){
								if(image_exists(7,$cmerchant_image))
									$cimage_path = MERCHANT_IMAGE_PATH.$cmerchant_image;
							}
							else{
								if(file_exists(MERCHANT_IMAGE_PATH_REL.$cmerchant_image))
									$cimage_path = MERCHANT_IMAGE_PATH.$cmerchant_image;
							}
						}
					?>
					<div class="form-group col-sm-6 row">
						<label class="col-sm-4" >Image</label>
						<div  class="col-sm-8">
							<img width="200" align="top" class="img_border" src="<?php echo $cimage_path;?>" >
						</div>
					</div>
					<div class="form-group col-sm-6 row">
						<label class="col-sm-4" >Category</label>
						<div  class="col-sm-8">
							<?php if(isset($categories) && !empty($categories)) {
								foreach($categories as $val) {
							?>
								<span id="cat_id_<?php echo $val->Id; ?>" style="<?php if(in_array($val->Id,$cat_id_array)) echo "display:inline-block"; else echo "display:none;";?>" class="cat_box">
									<img width="30" height="30" src="<?php echo CATEGORY_IMAGE_PATH.$val->CategoryIcon; ?>"/>
									<span class="cname"><?php echo $val->CategoryName;?></i></span>
								</span>
							<?php } } else echo "-"; ?>
						</div>
					</div>
					<div class="form-group col-sm-6 row">
						<label class="col-sm-4" >Opening Hours</label>
						<div  class="col-sm-8">
						<?php if(!empty($merchantListResult[0]->OpeningHours)) { echo $merchantListResult[0]->OpeningHours; } else { echo "-"; } ?></div>
					</div>
					<div class="form-group col-sm-6 row">
						<label class="col-sm-4" >Items Sold</label>
						<div  class="col-sm-8">
						<?php if($merchantListResult[0]->ItemsSold != 0) { echo $merchantListResult[0]->ItemsSold; } else { echo "-"; } ?></div>						
					</div>
					<div class="form-group col-sm-6 row">
						<label class="col-sm-4" >Price Scheme</label>
						<div  class="col-sm-8">
						<?php if($merchantListResult[0]->DiscountTier != 0) { echo $discount_array[$merchantListResult[0]->DiscountTier]."%"; } else { echo "-"; } ?></div>						
					</div>
					<div class="form-group col-sm-6 row">
						<label class="col-sm-4" >Price Range</label>
						<div  class="col-sm-8">
						<?php if($merchantListResult[0]->PriceRange != 0) { 
							$priceran = explode(',',$merchantListResult[0]->PriceRange);
							echo "$".$priceran[0]." - $".$priceran[1];
							} else { echo "-"; } ?></div>						
					</div>
					<div class="form-group col-sm-6 row">
						<label class="col-sm-4" >Status</label>
						<div  class="col-sm-8">
						<?php if($merchantListResult[0]->Status == 1) { echo 'Active'; } else { echo "Inactive"; } ?></div>
					</div>
					<div class="box-footer col-sm-12" align="center">
						<?php 
						if($show == 0) {
							$href_page = "MerchantList";
						?>
						<a href="MerchantManage?editId=<?php if(isset($_GET['viewId']) && $_GET['viewId'] != '') echo $_GET['viewId']; ?>" title="Edit" alt="Edit" class="btn btn-success">Edit</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<a href="<?php if(isset($href_page) && $href_page != '' ) echo $href_page; else echo 'MerchantList';?>" class="btn btn-default" name="Back" id="Back" title="Back" alt="Back" >Back </a>
						<?php
						}
						else {
							$href_page = "ProductList";
						?>
						<a href="<?php if(isset($href_page) && $href_page != '' ) echo $href_page; else echo 'ProductList';?>" class="btn btn-default" name="Back" id="Back" title="Back" alt="Back" >Back </a>
						<?php
						}
						?>	
						
					
				</div>
				</div>		
			</div>		
		</div><!-- /.row -->
	</section><!-- /.content -->				  	
<?php }
}commonFooter(); ?>
</html>
