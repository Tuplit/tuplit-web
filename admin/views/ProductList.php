<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
commonHead();
require_once('controllers/ProductController.php');
$ProductObj   	=   new ProductController();
require_once('controllers/ManagementController.php');
$ManagementObj 	=   new ManagementController();
$condition 		= 	'';
$show 			= 	0;

if(isset($_GET['cs']) && $_GET['cs']=='1') {
	destroyPagingControlsVariables();
	unset($_SESSION['item_sess_name']);
	unset($_SESSION['item_sess_cost']);
	unset($_SESSION['item_sess_mername']);	
	unset($_SESSION['item_sess_product_discount']);
	unset($_SESSION['item_sess_product_category']);
}

if(isset($_GET['mer_id']) && !empty($_GET['mer_id'])) {
	$condition 	.= 	' and pc.Status =1 and m.id='.$_GET['mer_id'];
	$show 		= 	1;
	$mer_id 	= 	$_GET['mer_id'];
}

if(isset($_POST['Search']) && $_POST['Search'] != ''){
	destroyPagingControlsVariables();
	$_POST      = unEscapeSpecialCharacters($_POST);
    $_POST      = escapeSpecialCharacters($_POST);
	if(isset($_POST['itemname']))
		$_SESSION['item_sess_name'] 			= $_POST['itemname'];
	if(isset($_POST['cost']))
		$_SESSION['item_sess_cost']				= $_POST['cost'];
	if(isset($_POST['merchantname']))
		$_SESSION['item_sess_mername']			= $_POST['merchantname'];
	if(isset($_POST['DiscountApplied']))
		$_SESSION['item_sess_product_discount']	= $_POST['DiscountApplied'];
	if(isset($_POST['ProductCategory']))
		$_SESSION['item_sess_product_category']	= $_POST['ProductCategory'];
}
if(isset($_GET['editId']) && $_GET['editId']!=''){
	$update_condition 		= 	" id = ".$_GET['editId'];
	$update_string 			= 	" Status = ".$_GET['status'];
	$updateResult  			= 	$ProductObj->updateProductDetails($update_string,$update_condition);
	header("location:ProductList?msg=4");
}
setPagingControlValues('id',ADMIN_PER_PAGE_LIMIT);
$fields    				= 	" p.*,m.CompanyName,m.Icon,pc.CategoryName,m.DiscountTier as Discount ";
$condition 				.= 	" and p.Status in (1,2)";
$productListResult  	= 	$ProductObj->getProductList($fields,$condition);
$tot_rec 		 		= 	$ProductObj->getTotalRecordCount();
if($tot_rec!=0 && !is_array($productListResult)) {
	$_SESSION['curpage']= 	1;
	$productListResult  = 	$ProductObj->getProductList($fields,$condition);
}
if(isset($_GET['msg']) && $_GET['msg'] == 1){
	$msg 		= 	"Product added successfully";
	$display	=	"block";
	$class 		= 	"alert-success";
	$class_icon = "fa-check";
}
else if(isset($_GET['msg']) && $_GET['msg'] == 2){
	$msg 		= 	"Product updated successfully";
	$display	=	"block";
	$class 		= 	"alert-success";
	$class_icon = "fa-check";
}
else if(isset($_GET['msg']) && $_GET['msg'] == 4){
	$msg 		= 	"Status changed successfully";
	$display	=	"block";
	$class 		= 	"alert-success";
	$class_icon = "fa-check";
}
$fields   = " CategoryName,id";
$condition = " Status in (1) ORDER BY CategoryName";
$CategoryListResult  = $ManagementObj->selectProductCategoryDetails($fields,$condition);
?>
<body class="skin-blue" onload="">
	<?php 
	if($show == 0)
		top_header(); 
	?>
	<!-- Content Header (Page header) -->
	<section class="content-header no-padding">
		<div <?php if($show == 0) { ?>class="col-xs-7"<?php } else { ?> class="col-xs-12" <? } ?>>
			<h1><i class="fa fa-list"></i><?php if($show == 0) echo " Product List"; else echo " Product List"; ?></h1>
		</div>
		<?php if($show == 0){ ?><div class="col-sm-5"><h3><a href="ProductManage"><i class="fa fa-plus-circle"></i> Add Product</a></h3></div><?php } ?>
	</section>	
	 <!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-xs-12">
				<form name="search_merchant" action="ProductList<?php if($show == 1) echo "?mer_id=".$mer_id."&cs=1"; ?>" method="post">
				<div class="box box-primary">
					<div class="box-body no-padding" >				
						<div class="col-md-4  col-sm-4 <?php if($show != 0) { ?>col-sm-3<?}?> form-group">
							<label>Item Name</label>
							<input type="text" class="form-control" name="itemname" id="itemname"  value="<?php if(isset($_SESSION['item_sess_name']) && $_SESSION['item_sess_name'] != '') echo unEscapeSpecialCharacters($_SESSION['item_sess_name']); ?>" >
						</div>
						<div class="col-md-4 col-sm-4 <?php if($show != 0) { ?>col-sm-2<?}?> form-group">
							<label>Price</label>
							<input type="text" class="form-control" id="cost" name="cost"  value="<?php if(isset($_SESSION['item_sess_cost']) && $_SESSION['item_sess_cost'] != '') echo unEscapeSpecialCharacters($_SESSION['item_sess_cost']);  ?>" >
						</div>	
						
						<?php if($show == 0) { ?>	
						<div class="col-md-4 col-sm-4  form-group">
							<label>Merchant Name</label>
							<input type="text" class="form-control" id="merchantname" name="merchantname"  value="<?php if(isset($_SESSION['item_sess_mername']) && $_SESSION['item_sess_mername'] != '') echo unEscapeSpecialCharacters($_SESSION['item_sess_mername']); ?>" >
						</div>
						<?php } ?>
						<div class="col-md-4 col-sm-4 form-group">
							<label>Product Category</label>
							<select name="ProductCategory" id="ProductCategory" class="form-control col-sm-4">
								<option value="">Select</option>
								<?php if(isset($CategoryListResult)) { 
									foreach($CategoryListResult as $key=>$val) { ?>
									<option value="<?php echo $val->id; ?>" <?php if(isset($_SESSION['item_sess_product_category']) && $_SESSION['item_sess_product_category'] ==  $val->id) echo "selected";?>><?php echo ucfirst($val->CategoryName); ?></option>
								<?php } } ?>		
							</select>
						</div>
						<div class="form-group col-sm-5 <?php if($show != 0) { ?>col-sm-3<?}?> col-md-4">
							<label class="notification">Discount Applied</label>
							<div class="radio ">
							<label class="col-xs-3 no-padding"><input type="Radio" id="DiscountApplied" value="1" name="DiscountApplied" <?php if(isset($_SESSION['item_sess_product_discount']) && $_SESSION['item_sess_product_discount'] == '1') echo 'checked';?> > &nbsp;&nbsp;Yes</label>
							<label class="col-xs-3 no-padding"><input type="Radio" id="DiscountApplied" value="0" name="DiscountApplied" <?php if(isset($_SESSION['item_sess_product_discount']) && $_SESSION['item_sess_product_discount'] == '0') echo 'checked';?> > &nbsp;&nbsp;No</label>
							</div>
						
						</div>
					</div>
					<div class="box-footer col-sm-12" align="center">
						<input type="submit" class="btn btn-success" name="Search" id="Search" value="Search">
					</div>					
				</div>
				</form>
			</div>
		</div>
		<div class="row paging">
			<div class="col-xs-12 col-sm-3">
				<?php if(isset($productListResult) && is_array($productListResult) && count($productListResult) > 0){ 
						//echo "<pre>"; echo print_r($productListResult); echo "</pre>";
				?>
				<div class="dataTables_info">No. of Product(s)&nbsp:&nbsp;<strong><?php echo $tot_rec; ?></strong> </div>
				<?php } ?>
			</div>
			<div class="col-xs-12 col-sm-9">
				<div class="dataTables_paginate paging_bootstrap row">
				<?php if(is_array($productListResult) && count($productListResult) > 0 ) {
					if($show == 0)
						$href_link = 'ProductList';
					else
						$href_link = 'ProductList?mer_id='.$mer_id;
					pagingControlLatest($tot_rec,$href_link); 
					?>
				<?php }?>
				</div>
			</div>
		 </div>	
		
		<?php if(isset($msg) && $msg != '') { ?>
		 <div class="row">
               <div align="center" class="alert <?php  echo $class;  ?> alert-dismissable col-sm-4"><i class="fa fa-check"></i>  <?php echo $msg; ?></div>
		 </div>	
		<?php } ?>
		
		<div class="row">
               <div class="col-xs-12">
			   	<?php if(isset($productListResult) && is_array($productListResult) && count($productListResult) > 0 ) { ?>
				<form action="ProductList" class="l_form" name="ProductList" id="ProductList"  method="post">
                   <div class="box <?php if($show != 0) { ?>height-control<?}?>">
                       <div class="box-body table-responsive no-padding">
                           <table class="table table-hover">
                               <tr>
									<th align="center" width="2%" style="text-align:center">#</th>									
									<th width="15%">Item Details</th>
									<th width="23%">Price Details</th>			
									<?php if($show == 0) { ?>							
									<th width="12%">Merchant Details</th>
									<?php } ?>
								</tr>
                              <?php
							  	foreach($productListResult as $key=>$value){
								?>
							<tr id="test_id_<?php echo $value->id;?>">
								<td align="center"><?php echo (($_SESSION['curpage'] - 1) * ($_SESSION['perpage']))+$key+1;?></td>												
								<td>
									<?
										$image_path = '';
										$photo = $value->Photo;
										$image_path = SITE_PATH.'/Refer/site_source/no_photo_product1.png';
										if(isset($photo) && $photo != ''){
											if(SERVER){
												if(image_exists(3,$photo))
													$image_path = PRODUCT_IMAGE_PATH.$photo;
											}
											else{
												if(file_exists(PRODUCT_IMAGE_PATH_REL.$photo))
													$image_path = PRODUCT_IMAGE_PATH.$photo;
											}
										}
									?>
									<div class="mb_wrap_sm">
										<div class="col-xs-3 col-sm-4 col-lg-3 no-padding">
											<?php if(!empty($value->Photo)) { ?>
												<a href="<?php echo $image_path; ?>" class="fancybox" title="<?php echo $value->ItemName; ?>">
													<img width="75" height="75"align="top" class="img_border" src="<?php echo $image_path;?>" >
												</a>
											<?php } else {?>
												<img width="75" height="75" align="top" class="img_border" src="<?php echo $image_path;?>" >
											<?php } ?>
										</div>
										<div class="col-sm-8 col-xs-9">
											<?php if(isset($value->ItemName) && $value->ItemName != ''){ ?>
												<span title="Item Name" data-toggle="tooltip">
													<?php echo "<b>".ucfirst($value->ItemName)."</b>";  ?>
												</span><br>   
											<?php }?>
											<?php $cat_name 		= 	'';
												if(isset($value->CategoryName) && !empty($value->CategoryName))
													$cat_name		=	ucfirst($value->CategoryName);
												if($value->CategoryName == '') {
													if($value->ItemType == 2)
														$cat_name	=	'Deals';
													else if($value->ItemType == 3)
														$cat_name	=	'Specials';
												}										
												if(!empty($cat_name)) {
											?>
												<span title="Category Name" data-toggle="tooltip">
													<?php echo "<b>".$cat_name."</b>";  ?>
												</span><br>   
											<?php } ?>
										</div>																
										<div class="row-actions col-xs-12">			
																														
											<?php if($show == 0) { ?>
												<?php if($value->Status == 1) { ?>
												<a class="active_icon" onclick="javascript:return confirm('Are you sure want to change the status?')" href="ProductList?status=2&editId=<?php if(isset($value->id) && $value->id != '') echo $value->id;?>" alt="Click to Inactive" title="Click to Inactive"><i class="fa fa-thumbs-up "></i></a>
												<?php } else { ?>
												<a class="inactive_icon" onclick="javascript:return confirm('Are you sure you want to change the status?')" title="Click to Active" alt="Click to Active" href="ProductList?status=1&editId=<?php if(isset($value->id) && $value->id != '') echo $value->id;?>"><i class="fa fa-thumbs-o-down "></i></a>
												<?php } ?>
												<a href="ProductDetail?viewId=<?php if(isset($value->id) && $value->id != '') echo $value->id; ?>" title="View" alt="View" class="view"><i class="fa fa-search "></i></a>	
												<a href="ProductManage?editId=<?php if(isset($value->id) && $value->id != '') echo $value->id; ?>" title="Edit" alt="Edit" class="Edit"><i class="fa fa-pencil "></i></a>		
											<?php } ?>
										</div>
									</div>
								</td>
								<td>	
									<?php
									if($value->Discount > 0)
										$dis_cost = floatval($value->Price - (($value->Price / 100) * $discountTierArray[$value->Discount]));
									else
										$dis_cost = 0;
									?>
									<div class="col-xs-12  no-padding"> 
										<b>Discount Applied</b>&nbsp;:&nbsp;<?php if(isset($value->DiscountApplied) && $value->DiscountApplied == 1){ echo 'Yes'; }else echo 'No';?></br>
										<?php if(isset($value->DiscountApplied) && $value->DiscountApplied == 1){
												if(isset($value->Discount) && $value->Discount > 0){?>
													<span title="Price"><?php echo '<b>Discount Tier</b> : '.$discountTierArray[$value->Discount]."%"; ?></span><br>
										<?php  } } if(isset($value->Price) && $value->Price >= 0){ echo '<b>Price</b> : '.price_fomat($value->Price); } ?><br>
										<?php if(isset($value->DiscountApplied) && $value->DiscountApplied == 1){					
												 if(isset($dis_cost) && $dis_cost > 0){ echo '<b>Discounted Price</b> : '.price_fomat($dis_cost);
										 }}?>
									</div>						
								</td>
								<?php if($show == 0) { ?>	
								<td>
									<?
										$icon_image_path = '';
										$merchant_image = $value->Icon;
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
								<div> 
										<?php if($show == 1) {
											if(isset($value->CompanyName) && $value->CompanyName != ''){ echo $value->CompanyName; }
										 } else {
										 ?>
										
										
										 <?php if(!empty($value->Icon)) { ?>
											 <a href="<?php echo $icon_image_path; ?>" class="fancybox" title="<?php echo $value->CompanyName; ?>">
												<img width="50" height="50" align="top" class="img_border" src="<?php echo $icon_image_path;?>" >
											 </a>
										<?php } else { ?>
											<div class="no_photo img50 valign"><i class="fa fa-user"></i></div><!-- <img width="50" height="50"align="top" class="img_border" src="<?php echo $icon_image_path;?>" > -->
										<?php }?>
										<a href="<?php echo SITE_PATH.'/admin/MerchantDetail?viewId='.$value->fkMerchantsId.'&proview=1'; ?>" class="fancybox" title="<?php echo $value->CompanyName; ?>">
											<?php if(isset($value->CompanyName) && $value->CompanyName != ''){ ?>
											<span title="Company Name">
												<?php echo "<b>".$value->CompanyName."</b>";  ?>
											</span><br>   
										<?php }?>
										</a>
									<?php  } ?>
									</div>
								</td>
																				
								<?php } ?>
								
									
							</tr>
							<?php } //end for ?>	
                           </table>
                       </div><!-- /.box-body -->
					  
                   </div><!-- /.box -->
				<!--<div class="row">
						<?php//if(isset($productListResult) && is_array($productListResult) && count($productListResult) > 0){ ?>
						<div class="col-xs-6"><button type="submit" onclick="return deleteAll('Users');" class="btn btn-danger" name="Delete" id="Delete" value="Delete" title="Delete" alt="Delete"><i class="fa fa-trash-o"></i>&nbsp;Delete</button></div>
						<?php //} ?>						
					</div-->
					</form>
					
					<?php } else { ?>	
						<div class="alert alert-danger alert-dismissable col-sm-5 col-lg-3 col-xs-11 "><i class="fa fa-warning"></i>&nbsp;&nbsp;<?php if($show == 0) echo "No Products found"; else echo "No Products found for this merchant"; ?></div> 
					<?php } ?>	
               </div>
           </div>
	
	</section><!-- /.content -->	
						  	
<?php commonFooter(); ?>
<script type="text/javascript">
$(document).ready(function() {
	$('.fancybox').fancybox();	
});
</script>
</html>