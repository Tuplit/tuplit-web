<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
commonHead();
require_once('controllers/OrderController.php');
$OrderObj   =   new OrderController();
$condition = '';
$show = 0;

if(isset($_GET['cs']) && $_GET['cs']=='1') {
	destroyPagingControlsVariables();
	unset($_SESSION['tuplit_sess_order_user_name']);
	unset($_SESSION['tuplit_sess_order_company_name']);
	unset($_SESSION['tuplit_sess_order_price']);	
	unset($_SESSION['item_sess_Order_discount']);
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
	if(isset($_POST['UserName']))
		$_SESSION['tuplit_sess_order_user_name'] 		= $_POST['UserName'];
	if(isset($_POST['merchantname']))
		$_SESSION['tuplit_sess_order_company_name']		= $_POST['merchantname'];
	if(isset($_POST['price']))
		$_SESSION['tuplit_sess_order_price']			= $_POST['price'];
	if(isset($_POST['order_status']))
		$_SESSION['tuplit_sess_order_status']			= $_POST['order_status'];
}
if(isset($_GET['editId']) && $_GET['editId']!=''){
	$update_condition 		= " id = ".$_GET['editId'];
	$update_string 	= " Status = ".$_GET['status'];
	$updateResult  	= $OrderObj->updateOrderDetails($update_string,$update_condition);
	header("location:OrderList?msg=4");
}
setPagingControlValues('id',ADMIN_PER_PAGE_LIMIT);
$fields    = " o.*,m.CompanyName,m.Icon,concat(u.FirstName , ' ',u.LastName) as UserName,u.Photo ";
$condition .= " and o.Status in (1,2)";
$OrderListResult  = $OrderObj->getOrderList($fields,$condition);
$tot_rec 		 = $OrderObj->getTotalRecordCount();
if($tot_rec!=0 && !is_array($OrderListResult)) {
	$_SESSION['curpage'] = 1;
	$OrderListResult  = $OrderObj->getOrderList($fields,$condition);
}
if(isset($_GET['msg']) && $_GET['msg'] == 1){
	$msg 		= 	"Order added successfully";
	$display	=	"block";
	$class 		= 	"alert-success";
	$class_icon = "fa-check";
}
else if(isset($_GET['msg']) && $_GET['msg'] == 2){
	$msg 		= 	"Order updated successfully";
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
//echo "<pre>"; print_r($OrderListResult ); echo "</pre>";
?>
<body class="skin-blue" onload="">
	<?php 
	//if($show == 0)
		top_header(); 
	?>
	<!-- Content Header (Page header) -->
	<section class="content-header no-padding">
		<div class="col-xs-7">
			<h1><i class="fa fa-list"></i><?php if($show == 0) echo " Order List"; else echo " Merchant Order List"; ?></h1>
		</div>
		<div class="col-xs-5"><h3><a href="OrderManage"><i class="fa fa-plus-circle"></i>Add Order</a></h3></div>
	</section>	
	 <!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-xs-12">
				<form name="search_merchant" action="OrderList<?php if($show == 1) echo "?mer_id=".$mer_id."&cs=1"; ?>" method="post">
				<div class="box box-primary">
					<div class="box-body no-padding" >				
						<div class="col-sm-4 form-group">
							<label>User Name</label>
							<input type="text" class="form-control" name="UserName" id="UserName"  value="<?php  if(isset($_SESSION['tuplit_sess_order_user_name']) && $_SESSION['tuplit_sess_order_user_name'] != '') echo unEscapeSpecialCharacters($_SESSION['tuplit_sess_order_user_name']);  ?>" >
						</div>
						<div class="col-sm-4 form-group">
							<label>Price</label>
							<input type="text" class="form-control" id="price" name="price"  value="<?php if(isset($_SESSION['tuplit_sess_order_price']) && $_SESSION['tuplit_sess_order_price'] != '') echo unEscapeSpecialCharacters($_SESSION['tuplit_sess_order_price']);  ?>" >
						</div>	
						<div class="col-sm-4 form-group">
							<label>Merchant Name</label>
							<input type="text" class="form-control" id="merchantname" name="merchantname"  value="<?php if(isset($_SESSION['tuplit_sess_order_company_name']) && $_SESSION['tuplit_sess_order_company_name'] != '') echo unEscapeSpecialCharacters($_SESSION['tuplit_sess_order_company_name']); ?>" >
						</div>
						<div class="col-sm-4 form-group">
							<label>Order Status</label>
							<select name="order_status" id="order_status" class="form-control col-sm-4">
								<option value="">Select</option>
								<?php if(isset($order_status_array) && is_array($order_status_array) && count($order_status_array) > 0 ) { 
										foreach($order_status_array as $orderkey=>$ordervalue) { ?>
								<option value="<?php echo $orderkey;?>" <?php if(isset($_SESSION['tuplit_sess_order_status']) && $_SESSION['tuplit_sess_order_status'] == $orderkey && $_SESSION['tuplit_sess_order_status'] != '') echo 'selected';?> ><?php echo $ordervalue; ?></option>
								<?php } } ?>
							</select>
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
			<div class="col-xs-2">
				<?php if(isset($OrderListResult) && is_array($OrderListResult) && count($OrderListResult) > 0){ ?>
				<div class="dataTables_info">No. of Order(s)&nbsp:&nbsp;<strong><?php echo $tot_rec; ?></strong> </div>
				<?php } ?>
			</div>
			<div class="col-xs-10">
				<div class="dataTables_paginate paging_bootstrap row">
				<?php if(is_array($OrderListResult) && count($OrderListResult) > 0 ) {
					if($show == 0)
						$href_link = 'OrderList';
					else
						$href_link = 'OrderList?mer_id='.$mer_id;
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
			   	<?php if(isset($OrderListResult) && is_array($OrderListResult) && count($OrderListResult) > 0 ) { ?>
				<form action="OrderList" class="l_form" name="OrderList" id="OrderList"  method="post">
                   <div class="box">
                       <div class="box-body table-responsive no-padding">
                           <table class="table table-hover">
                               <tr>
                                  	<!--<th align="center" width="1%" style="text-align:center"><input onclick="checkAllDelete('OrderList');" type="Checkbox" name="checkAll"/></th>-->
									<th align="center" width="2%" style="text-align:center">#</th>									
									<th width="15%">UserDetails</th>
									<th width="10%">Cart ID</th>
									<th width="15%">Price</th>
									<th width="15%">Order Date</th>
									<th width="15%">Order Status</th>
									<th width="12%">Merchant Details</th>
								</tr>
                              <?php
							  	foreach($OrderListResult as $key=>$value){
								?>
							<tr>
								<!--<td align="center"><input id="checkdelete" name="checkdelete[]" value="<?php  //if(isset($value->id) && $value->id != '') echo $value->id  ?>" type="checkbox" hashCount="<?php //if(isset($value->hash_count) && $value->hash_count > 0 ) echo $value->hash_count; ?>"/></td>-->
								<td align="center"><?php echo (($_SESSION['curpage'] - 1) * ($_SESSION['perpage']))+$key+1;?></td>												
									<?
										$image_path = '';
										$photo = $value->Photo;
										$original_path = $image_path = ADMIN_IMAGE_PATH.'no_user.jpeg';
										
										if(isset($photo) && $photo != ''){
											$user_image = $photo;
											if(SERVER){
												if(image_exists(2,$user_image))
													$image_path = USER_THUMB_IMAGE_PATH.$user_image;
												if(image_exists(1,$user_image))
													$original_path = USER_IMAGE_PATH.$user_image;
												
											}else{
												if(file_exists(USER_IMAGE_PATH_REL.$user_image))
													$original_path = USER_IMAGE_PATH.$user_image;
												if(file_exists(USER_THUMB_IMAGE_PATH_REL.$user_image))
													$image_path = USER_THUMB_IMAGE_PATH.$user_image;
											}
										}
									?>
									<td>
								<?php if(isset($image_path) && $image_path != ''){ ?>
									<div class="col-xs-2 no-padding">
										<a <?php if(isset($image_path) && basename($image_path) != "no_user.jpeg") { ?>href="<?php echo $original_path; ?>" class="fancybox" title="<?php echo  ucfirst($value->UserName);?>" <?php } ?> > 
											<img id="<?php echo $value->id ;?>"  width="36" height="36" align="top" class="img_border" src="<?php echo $image_path;?>" >
										</a>
									</div>
								<?php } ?>
									<div class="col-xs-9 "> 								
										<?php if(isset($value->UserName) && $value->UserName != ''){ echo ucfirst($value->UserName); } ?>								
									
										
									</div>
									
								</td>
								<td><?php if(isset($value->fkCartId) && $value->fkCartId != ''){ echo $value->fkCartId; }else echo '-';?></td>
								<td><?php if(isset($value->TotalPrice) && $value->TotalPrice != ''){ echo '$'.$value->TotalPrice; }else echo '-';?></td>
								<td><?php if(isset($value->OrderDate) && $value->OrderDate != '0000-00-00 00:00:00'){ echo date('m/d/Y',strtotime($value->OrderDate)); }else echo '-';?></td>
								<td><?php if(isset($value->OrderStatus) && $value->OrderStatus != ''){ echo $order_status_array[$value->OrderStatus];}?></td>
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
											<img width="50" height="50"align="top" class="img_border" src="<?php echo $icon_image_path;?>" >
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
																				
								
									
							</tr>
							<?php } //end for ?>	
                           </table>
                       </div><!-- /.box-body -->
					  
                   </div><!-- /.box -->
				<!--<div class="row">
						<?php//if(isset($OrderListResult) && is_array($OrderListResult) && count($OrderListResult) > 0){ ?>
						<div class="col-xs-6"><button type="submit" onclick="return deleteAll('Users');" class="btn btn-danger" name="Delete" id="Delete" value="Delete" title="Delete" alt="Delete"><i class="fa fa-trash-o"></i>&nbsp;Delete</button></div>
						<?php //} ?>						
					</div-->
					</form>
					
					<?php } else { ?>	
						<div class="alert alert-danger alert-dismissable col-sm-5 "><i class="fa fa-warning"></i>&nbsp;&nbsp;<?php if($show == 0) echo "No Orders found"; else echo "No Orders found for this merchant"; ?></div> 
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