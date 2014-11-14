<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
popup_head();
require_once('controllers/OrderController.php');
$OrderObj   =   new OrderController();
$condition = $OrderListResult = '';

if(isset($_GET['cs']) && $_GET['cs']=='1') {
	destroyPagingControlsVariables();
}
if(isset($_GET['cart_id']) && !empty($_GET['cart_id'])) {
	$cart_id	= base64_decode($_GET['cart_id']);
	$condition .= ' and c.CartId="'.$cart_id.'"';
}
setPagingControlValues('id',ADMIN_PER_PAGE_LIMIT);
$fields    			= " p.ItemName,c.CartId,c.TotalPrice,p.Photo,p.Status ";
$condition 			.= " and p.Status in (1,2,3) ";
$OrderListResult  	= $OrderObj->getProductList($fields,$condition);
$tot_rec 		 	= $OrderObj->getTotalRecordCount();
//print_r($OrderListResult);
$show = 1;
?>
<body class="skin-blue fancy-popup" onload="">
	<h1>Product List</h1>
	
	<div class="row paging">
		<div class="col-xs-12">
			<div class="col-xs-12 col-sm-2">
				<?php if(isset($OrderListResult) && is_array($OrderListResult) && count($OrderListResult) > 0){ ?>
				<div class="dataTables_info">No. of Product(s)&nbsp:&nbsp;<strong><?php echo $tot_rec; ?></strong> </div>
				<?php } ?>
			</div>
			<div class="col-xs-12 col-sm-10">
				<div class="dataTables_paginate paging_bootstrap row">
				<?php if(is_array($OrderListResult) && count($OrderListResult) > 0 ) {
						$href_link = 'ProductPopup?cart_id='.$_GET['cart_id'];
						pagingControlLatest($tot_rec,$href_link);
					?>
				<?php }?>
				</div>
			</div>
		</div>
	 </div>	
	<div class="row">
		   <div class="col-xs-12">
			<?php if(isset($OrderListResult) && is_array($OrderListResult) && count($OrderListResult) > 0 ) { ?>
			<form action=" " class="l_form col-xs-12" name="productPopup" id="productPopup"  method="post">
			   <div class="box"> <!--  if($show == 1)  for popup only -->
				   <div class="table-responsive">
					   <table class="table table-hover">
						   <tr>
								<th align="center" style="text-align:center">#</th>
								<th style="text-align: left" >Product Details</th>
								<th>Price</th>
							</tr>
							  <?php
								foreach($OrderListResult as $key=>$value){?>
								<tr>
									<td align="center" nowrap><?php echo (($_SESSION['curpage'] - 1) * ($_SESSION['perpage']))+$key+1;?></td>												
									<td style="text-align: left;vertical-align: middle">
									<?php
										$image_path = '';
										$photo = $value->Photo;
										$image_path = SITE_PATH.'/Refer/site_source/no_photo_product1.png';
										$noImagePath	= SITE_PATH.'/Refer/site_source/no_photo_product1.png';
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
									
										<?php if(!empty($value->Photo)) { ?>
												<img width="75" height="75"align="top" class="img_border" src="<?php echo $image_path;?>" >
										<?php } else {?>
											<img width="75" height="75" align="top" class="img_border" src="<?php echo $noImagePath;?>" >
										<?php } ?>
										<?php if(isset($value->ItemName) && $value->ItemName != ''){ ?>
										<span title="Item Name">
											&nbsp;<?php echo ucfirst($value->ItemName);  ?>
										</span>
										<?php }?>
										<?php if(isset($value->Status) && $value->Status == 3){ ?>
										<span class="sub-name">
											(Deleted)
										</span>
										<?php }?>
									
								</td>
									<td nowrap><?php if(isset($value->TotalPrice) && $value->TotalPrice != ''){ echo price_fomat($value->TotalPrice); }else echo '-';?></td>
								</tr>
							<?php } //end for ?>	
					   </table>
				   </div><!-- /.box-body -->
			   </div><!-- /.box -->
				</form>
				<?php } else { ?>	
					<div class="alert alert-danger alert-dismissable col-sm-5 col-lg-3 col-xs-11"><i class="fa fa-warning"></i><?php echo "No Orders found"; ?></div> 
				<?php } ?>	
		   </div>
	   </div>

<?php commonFooter(); ?>
</html>