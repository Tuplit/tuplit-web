<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
commonHead();
require_once('controllers/ProductController.php');
$ProductObj   =   new ProductController();
require_once('controllers/ManagementController.php');
$managementObj   =   new ManagementController();
require_once('controllers/MerchantController.php');
$merchantObj   =   new MerchantController();
require_once("includes/phmagick.php");
$ItemName = $Discount   = $DiscountApplied = $ExistCondition = $Status =  '';
$field_focus = 'ItemName';
$display      = 'none';
$photoUpdateString	= '';
$Product_exists = 0;
unset($_SESSION['orderby']);
unset($_SESSION['ordertype']);
if(isset($_GET['editId']) && $_GET['editId'] != '' ){
	$productListResult  = $ProductObj->selectProductDetail($_GET['editId']);
	//echo "<pre>"; print_r( $productListResult); echo "</pre>";
	if(isset($productListResult) && is_array($productListResult) && count($productListResult) > 0){
		$ItemName 			= $productListResult[0]->ItemName;
		$MerchantId			= $productListResult[0]->fkMerchantsId;
		$ItemPrice     		= $productListResult[0]->Price;
		$DiscountApplied   	= $productListResult[0]->DiscountApplied;
		$CategoryId		   	= $productListResult[0]->fkCategoryId;
		$Discount		   	= $productListResult[0]->Discount;
		$Status				= $productListResult[0]->ProductStat;
		if(isset($productListResult[0]->Photo) && $productListResult[0]->Photo != ''){
			$ImageName 		= $productListResult[0]->Photo;
			$image_path = '';
			if(SERVER){
				if(image_exists(8,$ImageName))
					$image_path = PRODUCT_IMAGE_PATH.$ImageName;
			}
			else{
				if(file_exists(PRODUCT_IMAGE_PATH_REL.$ImageName))
					$image_path = PRODUCT_IMAGE_PATH.$ImageName;
			}
		}
	}
	
}
if(isset($_POST['submit']) && $_POST['submit'] != ''){
	$_POST          =   unEscapeSpecialCharacters($_POST);
   	$_POST          =   escapeSpecialCharacters($_POST);
	if(isset($_POST['ItemName']) )
		$ItemName 			= $_POST['ItemName'];
	if(isset($_POST['ItemPrice']) )
		$ItemPrice 			= $_POST['ItemPrice'];
	if(isset($_POST['DiscountApplied']))
		$DiscountApplied 	= $_POST['DiscountApplied'];
	if(isset($_POST['Category']) )
		$CategoryId    		= $_POST['Category'];
	if(isset($_POST['Merchant']) )
		$MerchantId    		= $_POST['Merchant'];
	if(isset($_POST['Status']) )
		$Status    		= $_POST['Status'];
	if (isset($_POST['product_photo_upload']) && !empty($_POST['product_photo_upload'])) {
		$ImageName	 = $_POST['product_photo_upload'];
		$image_path = TEMP_USER_IMAGE_PATH.$ImageName;
	}
	if($ItemName != '')
		$ExistCondition .= "  ( ItemName = '".trim($ItemName)."' ";
	if($_POST['submit'] == 'Save')
		$id_exists = ") and fkMerchantsId = '".$MerchantId."' and id != '".$_POST['product_id']."' and Status = 1 ";
	else
		$id_exists = " ) and fkMerchantsId = '".$_POST['Merchant']."' and Status = 1";
	$field = " * ";	
	$ExistCondition .= $id_exists;
	$alreadyExist   = $ProductObj->selectProductDetails($field,$ExistCondition);	
	if(isset($alreadyExist) && is_array($alreadyExist) && count($alreadyExist) > 0){
		$Product_exists  = 1;
	}
	if($Product_exists != 1){
		if($_POST['submit'] == 'Save'){		
			if(isset($_POST['product_id']) && $_POST['product_id'] != ''){
				$fields    = "ItemName            	= '".trim($ItemName)."',
							  Price            		= '".$ItemPrice."',
							  fkCategoryId 			= '".$CategoryId."',
							  DiscountApplied		= '".$DiscountApplied."',
							  Status				= '".$Status."',
							  DateModified			= '".date('Y-m-d H:i:s')."'";
				$condition = ' id = '.$_POST['product_id'];
				$ProductObj->updateProductDetails($fields,$condition);			
				$insert_id = $_POST['product_id'];
				if (isset($_POST['product_photo_upload']) && !empty($_POST['product_photo_upload'])) {
					if(isset($_POST['name_product_photo']) && $_POST['name_product_photo'] != ''){
						$ImagePath = $_POST['name_product_photo'];
						if(!SERVER){
							if(file_exists(PRODUCT_IMAGE_PATH_REL.$ImagePath))
								unlink(PRODUCT_IMAGE_PATH_REL . $ImagePath);
						}
					}
				}
			$msg = 2;
			}
		}
		if($_POST['submit'] == 'Add'){
			$insert_id   		    = $ProductObj->insertProductDetails($_POST);
			$msg = 1;
		}
		$date_now = date('Y-m-d H:i:s');
		if(isset($insert_id) && $insert_id != '' ){
			if (isset($_POST['product_photo_upload']) && !empty($_POST['product_photo_upload'])) {
				$ImageName 				= $insert_id . '_' . strtotime($date_now) . '.png';
			   	$tempImagePath 			= TEMP_USER_IMAGE_PATH_REL . $_POST['product_photo_upload'];
				$ImagePath 				= UPLOAD_PRODUCT_IMAGE_PATH_REL . $ImageName;
				$oldName			= $_POST['name_product_photo'];
				if ( !file_exists(UPLOAD_PRODUCT_IMAGE_PATH_REL) ){
			  		mkdir (UPLOAD_PRODUCT_IMAGE_PATH_REL, 0777);
				}
				//copy($tempImagePath,$ImagePath);
				imagethumb_addbg($tempImagePath, $ImagePath,'','',300,300);
				if (SERVER){
					if($oldUserName!='') {
						if(image_exists(8,$oldName)) {
							deleteImages(8,$oldName);
						}
					}
					uploadImageToS3($ImagePath,8,$ImageName);
					unlink($ImagePath);
				}
				$photoUpdateString	.= " Photo = '" . $ImageName . "'";
				unlink(TEMP_USER_IMAGE_PATH_REL.$_POST['product_photo_upload']);
			}
			if($photoUpdateString!='')
			{
				$condition 			= "id = ".$insert_id;
				$ProductObj->updateProductDetails($photoUpdateString,$condition);
			}
		}
		header("location:ProductList?msg=".$msg);
	}
	else{
		if($Product_exists == 1){
			$error_msg   = "Product already exists";
			$field_focus = 'ItemName';
		}
		$display = "block";
		$class   = "alert-danger";
		$class_icon          = "fa-warning";
	}
}
if(isset($_GET['editId'])){
	$field				= ' id,CategoryName';
	$condition       	= " fkMerchantId IN(".$MerchantId.",0) and Status =1 ORDER BY fkMerchantId,CategoryName asc";
	$productCategories  = $managementObj->selectProductCategoryDetails($field,$condition);
}
if(isset($_POST['Merchant']) && $_POST['Merchant'] != ''){
	$field				= ' id,CategoryName';
	$condition       	= " fkMerchantId IN(".$_POST['Merchant'].",0) and Status =1 ORDER BY fkMerchantId,CategoryName asc";
	$productCategories  = $managementObj->selectProductCategoryDetails($field,$condition);
}
$condition       	= "  Status =1 ";
$field				=	' id,CompanyName,DiscountTier';
$merchantList		= $merchantObj->selectMerchantDetails($field,$condition);
$clear_class = $clear_class2 = '';


if(isset($_GET['editId'])){
	$clear_class = 'clear';
}
else {
	$clear_class2 = 'clear';
}
?>
<body class="skin-blue" onload="return fieldfocus('<?php echo $field_focus; ?>');">
	<?php top_header(); ?>
	<!-- Content Header (Page header) -->
	<section class="content-header no-padding">
		<div class="col-xs-12"> 
			<h1><i class="fa <?php if(isset($_GET['editId']) && $_GET['editId'] != '' ) echo "fa-edit "; else echo 'fa-plus-circle ';?>"></i> <?php if(isset($_GET['editId']) && $_GET['editId'] != '' ) echo "Edit "; else echo 'Add ';?>Product</h1>
		</div>
	</section>
	<!-- Main content -->
	<section class="content">
		<div class="row">
			<form name="add_product_form" id="add_product_form" action="" method="post">
			<div class="col-sm-12"> 
				<div class="box box-primary"> 
					<div class="col-sm-12 no-padding">
					<?php if(isset($error_msg) && $error_msg != '')  { ?> <div class="alert <?php echo $class;  ?> alert-dismissable col-sm-4"><i class="fa <?php echo $class_icon ;  ?>"></i>  <?php echo $error_msg;  ?></div> <?php } ?>
					<input type="Hidden" name="product_id" id="product_id" value="<?php if(isset($_GET['editId']) && $_GET['editId'] != '' ) echo $_GET['editId'];?>">
					<input type="Hidden" name="DiscounTtier" id="DiscounTtier" value="<?php if(isset($Discount) && $Discount != '' ) echo $discountTierArray[$Discount];?>">
					
					</div>
					
					<div class="form-group col-sm-6 col-xs-12">
						<label>Item Name</label>
						<input type="text" class="form-control" id="ItemName" name="ItemName" maxlength="100" value="<?php if(isset($ItemName) && $ItemName != '') echo $ItemName;  ?>" >
					</div>
					<?php if(!isset($_GET['editId'])){?>
					<div class="form-group col-sm-6 col-xs-12">
						<label>Merchant</label>
						<select class="form-control " name="Merchant" id="Merchant" onchange="getProductCategory(this.value);">
							<option value="" >Select</option>								
							<?php if(isset($merchantList) && !empty($merchantList)) {
								foreach($merchantList as $m_key=>$m_val) {								
							?>
							<option value="<?php echo $m_val->id;?>" discount_val="<?php if( $m_val->DiscountTier > 0 ) echo $discountTierArray[$m_val->DiscountTier]; else '0';?>" <?php if(isset($MerchantId) && $MerchantId == $m_val->id) echo "selected"; ?>><?php echo ucfirst($m_val->CompanyName);?></option>
							<?php } } ?>								
						</select>
					</div>
					<?php } ?>
					<div class="form-group col-sm-6 col-xs-12 <?php echo $clear_class2; ?>" >
						<label>Category</label>
						<div id="category_box">
						<select class="form-control " name="Category">
							<option value="" >Select</option>								
							<?php if(isset($productCategories) && !empty($productCategories)) {
								foreach($productCategories as $key=>$val) {								
							?>
							<option value="<?php echo $val->id;?>" <?php if(isset($CategoryId) && $val->id == $CategoryId) echo "selected"; ?>><?php echo ucfirst($val->CategoryName);?></option>
							<?php } } ?>								
						</select>
						</div>
					</div>
					
					<div class="form-group col-sm-6 col-xs-12 <?php echo $clear_class; ?>">
						<label>Item Price($)</label>
						<div class="col-sm-6 no-padding">
							<input type="text" class="form-control" name="ItemPrice" id="ItemPrice" maxlength="10" onkeypress="return isNumberKey(event);" onkeyup="return calculateDiscountPrice();" onchange="return calculateDiscountPrice();" value="<?php if(isset($ItemPrice) && $ItemPrice != '') echo $ItemPrice;  ?>" >
						</div>
					</div>
					<div class="form-group col-sm-6  col-xs-12">
						<label>Discount Applied</label>
						<div class="radio ">
						<label class="col-xs-3 no-padding"><input type="Radio" id="DiscountApplied" value="1" onchange="return calculateDiscountPrice();" name="DiscountApplied" <?php if(isset($DiscountApplied) && ($DiscountApplied == '1' || $DiscountApplied == '')) echo 'checked';?> >&nbsp;&nbsp;Yes</label>
						<label class="col-xs-3 no-padding"><input type="Radio" id="DiscountApplied" value="0" onchange="return calculateDiscountPrice();" name="DiscountApplied" <?php if(isset($DiscountApplied) && $DiscountApplied == '0') echo 'checked';?> >&nbsp;&nbsp;No</label>
						</div>
					</div>
					<div class="form-group col-sm-6 col-xs-12 <?php echo $clear_class; ?>">
						<?php
						if($Discount > 0)
							$dis_cost = floatval($ItemPrice - (($ItemPrice / 100) * $discountTierArray[$Discount]));
							else
						$dis_cost = 0;
						?>
						<label>Discounted Price($)</label>
						<div class="col-sm-6 no-padding">
							<input type="text" class="form-control" name="DiscountPrice" id="DiscountPrice" maxlength="10" readonly value="<?php if($DiscountApplied == 1) echo number_format((float)$dis_cost, 2, '.', ''); else echo '0'; ?>">
						</div>
					</div>
					
					
					
					<?php if(!isset($_GET['editId'])) { ?>
					<div class="form-group col-sm-6 col-xs-12 clear">
						<label>Image</label>
						<div class="row">
						    <div class="col-sm-8 col-md-6"> 
								<input type="file"  name="product_photo" id="product_photo" title="User Photo" onclick="" onchange="return ajaxAdminFileUploadProcess('product_photo');"  /> 
								<p class="help-block"> The best resolution is 300X300 pixels.</p>
								<span class="error" for="empty_product_photo" generated="true" style="display: none">User Image is required</span>
							</div>
						    <div class="col-sm-4 ">
						         <div id="product_photo_img">
									<?php  
									if(isset($image_path) && $image_path != ''){  ?>
						                 <a <?php if(isset($image_path) && $image_path != '') { ?> href="<?php echo $image_path; ?>" class="product_photo_pop_up"<?php } else { ?> href="Javascript:void(0);"<?php } ?> title="Click here" alt="Click here" ><img class="img_border" src="<?php  echo $image_path;  ?>" width="75" height="75" alt="Image"/></a>
									<?php  }  ?>
						         </div>
						    </div>
						</div>
						<?php  if(isset($_POST['product_photo_upload']) && $_POST['product_photo_upload'] != ''){  ?><input type="Hidden" name="product_photo_upload" id="product_photo_upload" value="<?php  echo $_POST['product_photo_upload'];  ?>"><?php  }  ?>
						<input type="Hidden" name="empty_product_photo" id="empty_product_photo" value="<?php  if(isset($ImageName) && $ImageName != '') { echo $ImageName; }  ?>" />
						<input type="Hidden" name="name_product_photo" id="name_product_photo" value="<?php  if(isset($ImageName) && $ImageName != '') { echo $ImageName; }  ?>" />
					</div>	
					<?php } else { ?>
				
					<div class="form-group col-sm-6 col-xs-12 clear ">
						<label>Image</label>
						<div class="row">
							<div class="col-sm-8 col-md-7"> 
								<input type="file"  name="product_photo" id="product_photo" title="User Photo" onclick="" onchange="return ajaxAdminFileUploadProcess('product_photo');"  /> 
								<p class="help-block">(Minimum dimension 100x100)</p>
								<span class="error" for="empty_product_photo" generated="true" style="display: none">User Image is required</span>
							</div><!-- imageValidation('empty_cat_sel_photo'); -->
						
							<div class="col-sm-4 " >
						      <div id="product_photo_img">
								<?php  
								if(isset($image_path) && $image_path != ''){  ?>
						              <a <?php if(isset($image_path) && $image_path != '') { ?> href="<?php echo $image_path; ?>" class="product_photo_pop_up"<?php } else { ?> href="Javascript:void(0);"<?php } ?> title="Click here" alt="Click here" ><img class="img_border" src="<?php  echo $image_path;  ?>" width="75" height="75" alt="Image"/></a>
								<?php  }  ?>
						      </div>
							</div>
							<?php  if(isset($_POST['product_photo_upload']) && $_POST['product_photo_upload'] != ''){  ?><input type="Hidden" name="product_photo_upload" id="product_photo_upload" value="<?php  echo $_POST['product_photo_upload'];  ?>"><?php  }  ?>
							<input type="Hidden" name="empty_product_photo" id="empty_product_photo" value="<?php  if(isset($ImageName) && $ImageName != '') { echo $ImageName; }  ?>" />
							<input type="Hidden" name="name_product_photo" id="name_product_photo" value="<?php  if(isset($ImageName) && $ImageName != '') { echo $ImageName; }  ?>" />
						</div>
					</div>						
					<?php } ?>
					
					<div class="form-group col-sm-6 col-xs-12 ">
						<label class="notification">Status</label>
						<div class="radio ">
							<label class="col-xs-3 no-padding"><input type="Radio" value="1"  class=""  id="Status"  name="Status" <?php if(isset($Status) && ($Status == '1' || $Status == '' )) echo 'checked';?> > &nbsp;&nbsp;Active</label>&nbsp;&nbsp;&nbsp;&nbsp;
							<label class="col-xs-3 no-padding"><input type="Radio" value="2" id="Status" name="Status" <?php if(isset($Status) && $Status == '2') echo 'checked';?> > &nbsp;&nbsp;Inactive</label>
						</div>
					</div>
					
					<div class="box-footer col-xs-12" align="center">
						<?php if(isset($_GET['editId']) && $_GET['editId'] != ''){ ?>
							<input type="submit" class="btn btn-success" name="submit" id="submit" value="Save" title="Save" alt="Save">&nbsp;&nbsp;&nbsp;&nbsp;
						<?php } else { ?>
							<input type="submit" class="btn btn-success" name="submit" id="submit" value="Add" title="Add" alt="Add">&nbsp;&nbsp;&nbsp;&nbsp;
						<?php } ?>
						<?php $href_page = "ProductList"; 	?>		
						<a href="<?php if(isset($href_page) && $href_page != '' ) echo $href_page; else echo 'ProductList';?>" class="btn btn-default" name="Back" id="Back" title="Back" alt="Back" >Back </a>	
					</div>
					
				</div><!-- /.box -->
			</div><!-- /.col -->
			</form>	
		</div><!-- /.row -->
	</section><!-- /.content -->	
<?php commonFooter(); ?>
<script type="text/javascript">
$(".product_photo_pop_up").fancybox({title:true});
</script>
</html>