<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
commonHead();
require_once('controllers/OrderController.php');
$OrderObj   	=   new OrderController();
$condition = '';
$show = 0;

if(isset($_GET['cs']) && $_GET['cs']=='1') {
	destroyPagingControlsVariables();
	unset($_SESSION['item_sess_name']);
	unset($_SESSION['item_sess_cost']);
	unset($_SESSION['item_sess_mername']);	
	unset($_SESSION['item_sess_product_discount']);
	unset($_SESSION['item_sess_product_category']);
}

if(isset($_GET['cart_id']) && !empty($_GET['cart_id'])) {
	$cart_id	= base64_decode($_GET['cart_id']);
	$condition .= ' and c.CartId="'.$cart_id.'"';
}
if(isset($_GET['cs']) && $_GET['cs']=='1') {
	destroyPagingControlsVariables();
}
if(isset($_POST['Search']) && $_POST['Search'] != ''){
	$_POST          = unEscapeSpecialCharacters($_POST);
}
setPagingControlValues('c.id',ADMIN_PER_PAGE_LIMIT);
$fields    			= 	" c.TotalPrice as CartTotal,p.*,c.*,o.* ";
$condition 		   .= 	" and o.Status in (1,2)";
$cartListResult  	= 	$OrderObj->getCartList($fields,$condition);
$tot_rec			= 	$OrderObj->getTotalRecordCount();
if($tot_rec!=0 && !is_array($cartListResult)) {
	$_SESSION['curpage'] = 1;
	$cartListResult  = $OrderObj->getCartList($fields,$condition);
}
?>
<body class="skin-blue" onload="">
	<!-- Content Header (Page header) -->
	<section class="content-header no-padding">
		<div class="col-xs-12">
			<h1><i class="fa fa-list"></i> Product List</h1>
		</div>
	</section>	
	 <!-- Main content -->
	<section class="content">
	
	
		<div class="row paging">
			<div class="col-xs-12 col-sm-2">
				<?php if(isset($cartListResult) && is_array($cartListResult) && count($cartListResult) > 0){ ?>
				<div class="dataTables_info">No. of Product(s)&nbsp:&nbsp;<strong><?php echo $tot_rec; ?></strong> </div>
				<?php } ?>
			</div>
			<div class="col-xs-12 col-sm-10">
				<div class="dataTables_paginate paging_bootstrap row">
				<?php if(is_array($cartListResult) && count($cartListResult) > 0 ) {
						$href_link = 'OrderProductList?cart_id='.$_GET['cart_id'];
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
			   	<?php if(isset($cartListResult) && is_array($cartListResult) && count($cartListResult) > 0 ) { ?>
				<form action="OrderList" class="l_form" name="OrderList" id="OrderList"  method="post">
                   <div class="box">
                       <div class="box-body table-responsive no-padding">
                           <table class="table table-hover">
                               <tr>
									<th align="center" width="5%" style="text-align:center">#</th>									
									<th width="35%">Item Details</th>
									<th width="25%">Price Details</th>	
									<th width="10%" style="text-align:center">Quantity</th>	
									<th width="25%"  style="text-align:center">Total Amount</th>		
								</tr>
                              <?php foreach($cartListResult as $key=>$value){?>
								<tr>
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
										<div class="col-sm-3 col-xs-4 no-padding">
											<?php if(!empty($value->Photo)) { ?>
												<a href="<?php echo $image_path; ?>" class="fancybox" title="<?php echo ucfirst($value->ItemName); ?>">
													<img width="75" height="75"align="top" class="img_border" src="<?php echo $image_path;?>" >
												</a>
											<?php } else {?>
												<img width="75" height="75" align="top" class="img_border" src="<?php echo $image_path;?>" >
											<?php } ?>
										</div>
										<div class="col-sm-8">
											<?php if(isset($value->ItemName) && $value->ItemName != ''){ ?>
											<span title="Item Name">
												&nbsp;<?php echo ucfirst($value->ItemName);  ?>
											</span>
											<?php }?>
										</div>
									</td>
									<td>	
										<div class="col-xs-12  no-padding"> 
											<?php	
												if(isset($value->ProductsCost) && $value->ProductsCost > 0)
												{ 
												 	echo 'Price : '.price_fomat($value->ProductsCost);
												} 
												$discountedAmount	=	$value->ProductsCost - $value->DiscountPrice;
												/*echo '</br><b>Total Price</b> : $'.($value->ProductsCost*$value->ProductsQuantity); */
												if($discountedAmount > 0)
												{
													echo '</br>Discount Price : '.price_fomat($value->ProductsCost - $value->DiscountPrice); 
													echo '</br><strong>Amount</strong> : '.price_fomat($value->DiscountPrice); 
												}
												else
													echo '</br><strong>Amount</strong> : '.price_fomat($value->ProductsCost); 
											?>
										</div>						
									</td>
									<td align="center">	
										<div class="col-xs-12  no-padding"> 
											<?php if(isset($value->ProductsQuantity) && $value->ProductsQuantity > 0){ echo $value->ProductsQuantity; } ?>
										</div>						
									</td>
									<td align="center"><?php echo price_fomat($value->CartTotal);?></td>
								</tr>
							<?php } //end for ?>	
                           </table>
                       </div><!-- /.box-body -->
                   </div>
				</form>
					<?php } else { ?>	
						<div class="alert alert-danger alert-dismissable col-sm-5 col-xs-11"><i class="fa fa-warning"></i>&nbsp;&nbsp;No Products found</div> 
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