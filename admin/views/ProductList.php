<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
commonHead();
require_once('controllers/ProductController.php');
$ProductObj   =   new ProductController();
$condition = '';
$show = 0;
if(isset($_GET['cs']) && $_GET['cs']=='1') {
	destroyPagingControlsVariables();
	unset($_SESSION['item_sess_name']);
	unset($_SESSION['item_sess_cost']);
	unset($_SESSION['item_sess_mername']);	
}

if(isset($_GET['mer_id']) && !empty($_GET['mer_id'])) {
	$condition .= ' and m.id='.$_GET['mer_id'];
	$show = 1;
	$mer_id = $_GET['mer_id'];
}

if(isset($_POST['Search']) && $_POST['Search'] != ''){
	destroyPagingControlsVariables();
	$_POST          = unEscapeSpecialCharacters($_POST);
    $_POST          = escapeSpecialCharacters($_POST);
	if(isset($_POST['itemname']))
		$_SESSION['item_sess_name'] 	= $_POST['itemname'];
	if(isset($_POST['cost']))
		$_SESSION['item_sess_cost']	= $_POST['cost'];
	if(isset($_POST['merchantname']))
		$_SESSION['item_sess_mername']	= $_POST['merchantname'];
}

setPagingControlValues('id',ADMIN_PER_PAGE_LIMIT);
$fields    = " p.*,m.CompanyName ";
$condition .= " and p.Status in (1)";
$productListResult  = $ProductObj->getProductList($fields,$condition);
$tot_rec 		 = $ProductObj->getTotalRecordCount();
if($tot_rec!=0 && !is_array($productListResult)) {
	$_SESSION['curpage'] = 1;
	$productListResult  = $ProductObj->getProductList($fields,$condition);
}

?>
<body class="skin-blue" onload="">
	<?php 
	if($show == 0)
		top_header(); 
	?>
	<!-- Content Header (Page header) -->
	<section class="content-header no-padding">
		<div class="col-xs-8">
			<h1><i class="fa fa-list"></i><?php if($show == 0) echo " Product List"; else echo " Merchant Product List"; ?></h1>
		</div>
	</section>	
	 <!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-xs-12">
				<form name="search_merchant" action="ProductList<?php if($show == 1) echo "?mer_id=".$mer_id."&cs=1"; ?>" method="post">
				<div class="box box-primary">
					<div class="box-body no-padding" >				
						<div class="col-sm-4 form-group">
							<label>Item Name</label>
							<input type="text" class="form-control" name="itemname" id="itemname"  value="<?php if(isset($_SESSION['item_sess_name']) && $_SESSION['item_sess_name'] != '') echo unEscapeSpecialCharacters($_SESSION['item_sess_name']); ?>" >
						</div>
						<div class="col-sm-4 form-group">
							<label>Cost</label>
							<input type="text" class="form-control" id="cost" name="cost"  value="<?php if(isset($_SESSION['item_sess_cost']) && $_SESSION['item_sess_cost'] != '') echo unEscapeSpecialCharacters($_SESSION['item_sess_cost']);  ?>" >
						</div>	
						<?php if($show == 0) { ?>	
						<div class="col-sm-4 form-group">
							<label>Merchant Name</label>
							<input type="text" class="form-control" id="merchantname" name="merchantname"  value="<?php if(isset($_SESSION['item_sess_mername']) && $_SESSION['item_sess_mername'] != '') echo unEscapeSpecialCharacters($_SESSION['item_sess_mername']); ?>" >
						</div>
						<?php } ?>
					</div>
					<div class="box-footer col-sm-12" align="center">
						<input type="submit" class="btn btn-success" name="Search" id="Search" value="Search">
					</div>					
				</div>
				</form>
			</div>
		</div>
	
	
	
		<div class="row paging">
			<div class="col-xs-2">
				<?php if(isset($productListResult) && is_array($productListResult) && count($productListResult) > 0){ ?>
				<div class="dataTables_info">No. of Product(s)&nbsp:&nbsp;<strong><?php echo $tot_rec; ?></strong> </div>
				<?php } ?>
			</div>
			<div class="col-xs-10">
				<div class="dataTables_paginate paging_bootstrap row">
				<?php if(is_array($productListResult) && count($productListResult) > 0 ) {
					if($show == 0)
						$href_link = 'ProductList';
					else
						$href_link = 'ProductList?mer_id='.$mer_id;
					pagingControlLatest($tot_rec,$href_link); ?>
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
                   <div class="box">
                       <div class="box-body table-responsive no-padding">
                           <table class="table table-hover">
                               <tr>
                                  	<!--<th align="center" width="1%" style="text-align:center"><input onclick="checkAllDelete('ProductList');" type="Checkbox" name="checkAll"/></th>-->
									<th align="center" width="2%" style="text-align:center">#</th>									
									<th width="15%">Item Details</th>
									<th width="10%">Item Type</th>
									<th width="23%">Price</th>									
									<th width="12%">Merchant Name</th>
									<th width="5%">Quantity</th>		
									<th width="3%">Image</th>							
								</tr>
                              <?php
							  	foreach($productListResult as $key=>$value){
								?>
							<tr>
								<!--<td align="center"><input id="checkdelete" name="checkdelete[]" value="<?php  //if(isset($value->id) && $value->id != '') echo $value->id  ?>" type="checkbox" hashCount="<?php //if(isset($value->hash_count) && $value->hash_count > 0 ) echo $value->hash_count; ?>"/></td>-->
								<td align="center"><?php echo (($_SESSION['curpage'] - 1) * ($_SESSION['perpage']))+$key+1;?></td>												
								<td>
									<div>
										<?php if(isset($value->ItemName) && $value->ItemName != ''){ ?><span title="Item Name"><?php echo "<b>".$value->ItemName."</b>";  ?></span><br>   <?php } ?>
										<?php if(isset($value->ItemDescription) && $value->ItemDescription != ''){ ?><span data-toggle="tooltip" title="<?php echo $value->ItemDescription; ?>"><?php echo displayText($value->ItemDescription,100);?></span><br> <?php } ?>																		
									</div>
									<div class="row-actions col-xs-12">																						
										<!---<a href="MerchantManage?editId=<?php //if(isset($value->id) && $value->id != '') echo $value->id; ?>" title="Edit" alt="Edit" class="edit"><i class="fa fa-edit "></i></a>-->		
										<?php if($show == 0) { ?>
										<a href="ProductDetail?viewId=<?php if(isset($value->id) && $value->id != '') echo $value->id; ?>" title="View" alt="View" class="view"><i class="fa fa-search "></i></a>		
										<?php } ?>
										<!--<a onclick="javascript:return confirm('Are you sure to delete?')" href="ProductList?delId=<?php //if(isset($value->id) && $value->id != '') echo $value->id;?>" title="Delete" alt="Delete" class="delete"><i class="fa fa-trash-o "></i></a>-->
										
											
									</div>
								</td>
								<td>
									<div class="col-xs-12 no-padding"> 
										<?php if(isset($value->ItemType) && $value->ItemType != ''){?><span title="Type"><?php echo $item_type_array[$value->ItemType]; ?></span><br><?php  } ?>
									</div>						
								</td>
								<?php
									$dis_cost = $value->Price - (($value->Price / 100) * $discount_array[$value->DiscountTier]);
								?>
								<td>
									<div class="col-xs-12  no-padding"> 
										<b>Price</b> : <?php if(isset($value->Price) && $value->Price >= 0){ echo '$'.$value->Price; } ?><br>
										<b>Discount Percentage</b> : <?php if(isset($value->DiscountTier) && $value->DiscountTier != ''){?><span title="Price"><?php echo $discount_array[$value->DiscountTier]."%"; ?></span><br><?php  } ?>								
										<b>Discount Price</b> : <?php echo '$'.number_format((float)$dis_cost, 2, '.', ''); ?>
									</div>						
								</td>
								<td>
									<div class="col-xs-12  no-padding"> 
										<?php if($show == 1) {
											if(isset($value->CompanyName) && $value->CompanyName != ''){ echo $value->CompanyName; }
										 } else { ?>
										<a href="<?php echo SITE_PATH.'/admin/MerchantDetail?viewId='.$value->fkMerchantsId.'&proview=1'; ?>" class="fancybox" title="<?php echo $value->CompanyName; ?>">
											<?php if(isset($value->CompanyName) && $value->CompanyName != ''){ echo $value->CompanyName; } ?>	
										</a>
										<?php } ?>
									</div>						
								</td>
								<td>
									<div class="col-xs-12  no-padding"> 
										<?php if(isset($value->Quantity) && $value->Quantity >= 0){ echo $value->Quantity; } ?><br>
									</div>						
								</td>														
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
								<td>
									<?php if(!empty($value->Photo)) { ?>
										<a href="<?php echo $image_path; ?>" class="fancybox" title="<?php echo $value->ItemName; ?>">
											<img width="100" height="100"align="top" class="img_border" src="<?php echo $image_path;?>" >
										</a>
									<?php } else {?>
										<img width="100" height="100" align="top" class="img_border" src="<?php echo $image_path;?>" >
									<?php } ?>
								</td>	
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
						<div class="alert alert-danger alert-dismissable col-sm-5 "><i class="fa fa-warning"></i>&nbsp;&nbsp;<?php if($show == 0) echo "No Products found"; else echo "No Products found for this merchant"; ?></div> 
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