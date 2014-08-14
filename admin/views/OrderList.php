<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
commonHead();
require_once('controllers/OrderController.php');
$OrderObj   =   new OrderController();
require_once('controllers/UserController.php');
$UserObj   =   new UserController();
require_once('controllers/MerchantController.php');
$MerchantObj   =   new MerchantController();

$condition = $companyname = $user_id =  $mer_id = $cond = '';
$show = 0;
$username = $merchantname  = $userimage = $merchantimage = '';

$orderedMerchants	=	$MerchantObj->merchantOrders();
//echo "<pre>"; echo print_r($orderedMerchants); echo "</pre>";
if(isset($_GET['cs']) && $_GET['cs']=='1') {
	destroyPagingControlsVariables();
	unset($_SESSION['tuplit_sess_order_user_name']);
	unset($_SESSION['tuplit_sess_order_company_name']);
	unset($_SESSION['tuplit_sess_order_price']);	
	unset($_SESSION['item_sess_Order_discount']);
	unset($_SESSION['tuplit_sess_order_status']	);
	unset($_SESSION['tuplit_sess_trans_id']);
}

if(isset($_GET['mer_id']) && !empty($_GET['mer_id'])) {
	$condition .= ' and m.id='.$_GET['mer_id'];
	$cond  = ' and fkMerchantsId ='.$_GET['mer_id'];
	$merchantwhere = ' id ='.$_GET['mer_id'];	
	$merchantdetailarray = $MerchantObj->selectMerchantDetails("CompanyName,Icon",$merchantwhere);
if(isset($merchantdetailarray) && is_array($merchantdetailarray) && count($merchantdetailarray) > 0){
	$merchantname = ucfirst($merchantdetailarray[0]->CompanyName);
	$icon_image_path = '';
	$merchant_image = $merchantdetailarray[0]->Icon;
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
	if(isset($icon_image_path) && $icon_image_path != ''){
			$merchantimage =  "<img  width='50' height='50' align='top' class='img_border' src='".$icon_image_path."' >";		
	}	
}
	$show = 1;
	$mer_id = $_GET['mer_id'];
}

if(isset($_GET['user_id']) && !empty($_GET['user_id'])) {
	$condition .= ' and u.id='.$_GET['user_id'];
	$cond  = ' and fkUsersId ='.$_GET['user_id'];
	$userwhere = ' id ='.$_GET['user_id'];
	$userfind = "FirstName,LastName,Photo ";
	$userdetailarray = $UserObj->selectUserDetails($userfind,$userwhere);
	if(isset($userdetailarray) && is_array($userdetailarray) && count($userdetailarray) > 0){
		$image_path = '';
		$photo = $userdetailarray[0]->Photo;
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
		$username = ucfirst($userdetailarray[0]->FirstName).' '.ucfirst($userdetailarray[0]->LastName);
		if(isset($image_path) && $image_path != ''){
			$userimage =  "<img  width='50' height='50'  class='img_border' src='".$image_path."' >";		
			}	
	}							
	$show = 2;
	$user_id = $_GET['user_id'];
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
	if(isset($_POST['trans_id']))
		$_SESSION['tuplit_sess_trans_id']				= $_POST['trans_id'];
}
if(isset($_GET['editId']) && $_GET['editId']!=''){
	$update_condition 		= " id = ".$_GET['editId'];
	$update_string 	= " Status = ".$_GET['status'];
	$updateResult  	= $OrderObj->updateOrderDetails($update_string,$update_condition);
	header("location:OrderList?msg=4");
}
setPagingControlValues('id',ADMIN_PER_PAGE_LIMIT);
$fields    = " o.*,m.CompanyName,m.Icon,u.FirstName,u.LastName,u.Photo ";
$condition .= " and o.Status in (1,2)";
$OrderListResult  = $OrderObj->getOrderList($fields,$condition);
$tot_rec 		 = $OrderObj->getTotalRecordCount();
if($tot_rec!=0 && !is_array($OrderListResult)) {
	$_SESSION['curpage'] = 1;
	$OrderListResult  = $OrderObj->getOrderList($fields,$condition);		
}
//echo "<pre>Orderlist"; print_r($OrderListResult); echo "</pre>";//

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
$totalprice = 0 ;
$noof_orders = $newprice = $acceptedprice = 0;
$noof_merchants =  $noof_users = $accepted_count = $new_count = $rejected_count = 0;
$mechants_array = array();
$user_array = array();

$OverallOrderList = $OrderObj->getOverallOrderList('',$cond);
if(isset($OverallOrderList) && is_array($OverallOrderList) && count($OverallOrderList) > 0){
	
	foreach($OverallOrderList as $overall_orderkey=>$overall_ordervalue){
	
		if($overall_ordervalue->TotalPrice != ''){
			$totalprice = $totalprice + $overall_ordervalue->TotalPrice;
		}
		if($overall_ordervalue->OrderDoneBy == 2){
			//if(!(in_array($overall_ordervalue->fkMerchantsId,$mechants_array)))
				$mechants_array[]  = $overall_ordervalue->id ;
		}
		if($overall_ordervalue->OrderDoneBy == 1){
		//  if(!(in_array($overall_ordervalue->fkUsersId,$user_array)))
			$user_array[]  =   $overall_ordervalue->id ;
		}
		
		if($overall_ordervalue->OrderStatus == 0){
			$new_count +=  1;
			//if($overall_ordervalue->TotalPrice != '')
				//$newprice = $newprice + $overall_ordervalue->TotalPrice;
			
		}else if($overall_ordervalue->OrderStatus == 1){
			$accepted_count +=  1;
			if($overall_ordervalue->TotalPrice != '')
				$acceptedprice = $acceptedprice + $overall_ordervalue->TotalPrice;
		}else if($overall_ordervalue->OrderStatus == 2){
			$rejected_count +=  1;
		}
		
	}
	$noof_orders = $OrderObj->getTotalRecordCount();
}
?>
<body class="skin-blue" onload="">
	<?php 
	if($show == 0)
		top_header(); 
	?>
	<!-- Content Header (Page header) -->
	<section class="content-header no-padding">
		<div class="col-xs-8 col-sm-7">
			<h1><i class="fa fa-list"></i> Order List</h1>
		</div>
		
		<?php if($show == 1) { ?>
		<div class="col-xs-4 col-sm-5">
			<h3><?php echo $merchantimage." ".$merchantname ;?></h3>
		</div>
		<?php } else if($show == 2) { ?>
		
		<div class="col-xs-4 col-sm-5">
			<h3><?php echo $userimage." ".$username ;?></h3>
		</div>
		<?php } ?>
	</section>	
	 <!-- Main content -->
	<section class="content">
		<?php if(isset($OverallOrderList) && is_array($OverallOrderList) && count($OverallOrderList) > 0){?>
		<div class="row">	
			<div class="col-xs-12">	
			<div class="box box-solid bg-green col-xs-12">
				<div class="col-sm-6 col-xs-12 no-padding ">	
					<div class="col-sm-8 col-lg-8 col-xs-8 no-padding">	
						<h3>Total No. of Orders</h3>
					</div>	
					<div class="col-sm-4 col-lg-3 col-xs-4">	
						<h3>:&nbsp;&nbsp;<?php echo $noof_orders ;?></h3>
					</div>
					
					<div class="col-sm-8 col-lg-8 col-xs-8 no-padding">	
						<h3>Total No. of New Orders</h3>
					</div>	
					<div class="col-sm-4 col-lg-3 col-xs-4">	
						<h3>:&nbsp;&nbsp;<?php echo $new_count; ?></h3>
					</div>
					
					<div class="col-sm-8 col-lg-8 col-xs-8 no-padding">	
						<h3>Total No. of Orders Accepted</h3>
					</div>	
					<div class="col-sm-4 col-lg-3 col-xs-4">	
						<h3>:&nbsp;&nbsp;<?php echo $accepted_count; ?></h3>
					</div>
				
					<div class="col-sm-8 col-lg-8 col-xs-8 no-padding">	
						<h3>Total No. of Orders Rejected</h3>
					</div>	
					<div class="col-sm-4 col-lg-3 col-xs-4">	
						<h3>:&nbsp;&nbsp;<?php echo $rejected_count; ?></h3>
					</div>
					
				</div>
				<div class="col-sm-6 col-xs-12 col-lg-5 pull-right  no-padding ">	
						<?php if($show != 1) {?>
						
						<div class="col-sm-8 col-lg-9 col-xs-8 no-padding">		
							<h3>Total No. of Orders Placed By Merchants</h3>
						</div>	
						<div class="col-sm-4 col-lg-2 col-xs-4">	
							<h3>:&nbsp;&nbsp;<?php echo count($mechants_array); ?></h3>
						</div>
						<?php } ?>
						
						<?php if($show != 2) {?>
						<div class="col-sm-8 col-lg-9 col-xs-8 no-padding">		
							<h3>Total No. of Orders Placed By Users</h3>
						</div>	
						<div class="col-sm-4 col-lg-2 col-xs-4 ">	
							<h3>:&nbsp;&nbsp;<?php echo count($user_array); ?></h3>
						</div>
						<?php }?>
				
						<div class="col-sm-8 col-lg-9 col-xs-8 no-padding">		
							<h3>Total Price of Accepted Orders</h3>
						</div>
						<div class="col-sm-4 col-lg-3 col-xs-4 ">	
								<h3>:&nbsp;&nbsp;<?php echo price_fomat($acceptedprice)."<span class='help-block' style='color:#fff;white-space:nowrap;'>&nbsp;&nbsp;&nbsp;(Approved Orders)</span>"; ?></h3>
						</div>	
				</div>
			</div>			
			</div>	
		</div>
	<?php }?>
	
		<div class="row">
			<div class="col-xs-12">
				<form name="search_merchant" action="OrderList<?php if($show == 1) echo "?mer_id=".$mer_id."&cs=1"; else if($show == 2) echo "?user_id=".$user_id."&cs=1";?>" method="post">
				<div class="box box-primary">
					<div class="box-body no-padding" >	
						<?php if($show != 2) {?>			
						<div class="col-sm-4 form-group">
							<label>User Name</label>
							<input type="text" class="form-control" name="UserName" id="UserName"  value="<?php  if(isset($_SESSION['tuplit_sess_order_user_name']) && $_SESSION['tuplit_sess_order_user_name'] != '') echo unEscapeSpecialCharacters($_SESSION['tuplit_sess_order_user_name']);  ?>" >
						</div>
						<?php }?>
						<?php if($show != 1) { ?>
						<div class="col-sm-4 form-group">
							<label>Merchant Name</label>
							<!--<input type="text" class="form-control" id="merchantname" name="merchantname"  value="<?php if(isset($_SESSION['tuplit_sess_order_company_name']) && $_SESSION['tuplit_sess_order_company_name'] != '') echo unEscapeSpecialCharacters($_SESSION['tuplit_sess_order_company_name']); ?>" >-->
							<select name="merchantname" id="merchantname" class="form-control col-sm-4">
								<option value="">Select</option>
								<?php if(isset($orderedMerchants) && is_array($orderedMerchants) && count($orderedMerchants) > 0 ) { 
										foreach($orderedMerchants as $ordervalue) { ?>
								<option value="<?php echo $ordervalue->CompanyName;?>" <?php if(isset($_SESSION['tuplit_sess_order_company_name']) && $_SESSION['tuplit_sess_order_company_name'] == $ordervalue->CompanyName && $_SESSION['tuplit_sess_order_company_name'] != '') echo 'selected';?> ><?php echo $ordervalue->CompanyName; ?></option>
								<?php } } ?>
							</select>
						</div>
						<?php }?>
						<div class="col-sm-4 <?php if($show == 1 ) {?>col-sm-2 <?}?>  col-lg-2 form-group"> <!--  if($show == 1)  for popup only -->
							<label>Price</label>
							<input type="text" class="form-control" id="price" name="price"  value="<?php if(isset($_SESSION['tuplit_sess_order_price']) && $_SESSION['tuplit_sess_order_price'] != '') echo unEscapeSpecialCharacters($_SESSION['tuplit_sess_order_price']);  ?>" >
						</div>	
						<div class="col-sm-4  <?php if($show == 1 ) {?>col-sm-3<? }?> col-lg-2 form-group"> <!--  if($show == 1)  for popup only -->
							<label>Transaction ID</label>
							<input type="text" class="form-control" id="trans_id" name="trans_id"  value="<?php if(isset($_SESSION['tuplit_sess_trans_id']) && $_SESSION['tuplit_sess_trans_id'] != '') echo unEscapeSpecialCharacters($_SESSION['tuplit_sess_trans_id']);  ?>" >
						</div>	
						<div class="col-sm-4  <?php if($show == 1 ) {?>col-sm-2 <? }?>  col-lg-2 form-group"> <!--  if($show == 1)  for popup only -->
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
			<div class="col-xs-12 col-sm-3">
				<?php if(isset($OrderListResult) && is_array($OrderListResult) && count($OrderListResult) > 0){ ?>
				<div class="dataTables_info">No. of Order(s)&nbsp:&nbsp;<strong><?php echo $tot_rec; ?></strong> </div>
				<?php } ?>
			</div>
			<div class="col-xs-12 col-sm-9">
				<div class="dataTables_paginate paging_bootstrap row">
				<?php if(is_array($OrderListResult) && count($OrderListResult) > 0 ) {
					if($show == 0)
						$href_link = 'OrderList';
					else if($show == 1)
						$href_link = 'OrderList?mer_id='.$mer_id;
					else if($show == 2)
						$href_link = 'OrderList?user_id='.$user_id;
					pagingControlLatest($tot_rec,$href_link); ?>
				<?php }?>
				</div>
			</div>
		 </div>	
		
		<?php if(isset($msg) && $msg != '') { ?>
		 <div class="row">
               <div align="center" class="alert <?php  echo $class;  ?> alert-dismissable col-sm-4 col-lg-3 col-xs-11"><i class="fa fa-check"></i>  <?php echo $msg; ?></div>
		 </div>	
		<?php } ?>
		
		<div class="row">
               <div class="col-xs-12">
			   	<?php if(isset($OrderListResult) && is_array($OrderListResult) && count($OrderListResult) > 0 ) { ?>
				<form action="OrderList" class="l_form" name="OrderList" id="OrderList"  method="post">
                   <div class="box <?php if($show == 1) {?> height-control <?}?>"> <!--  if($show == 1)  for popup only -->
                       <div class="box-body table-responsive no-padding">
                           <table class="table table-hover">
                               <tr>
                                  	<!--<th align="center" width="1%" style="text-align:center"><input onclick="checkAllDelete('OrderList');" type="Checkbox" name="checkAll"/></th>-->
									<th align="center" width="3%" style="text-align:center">#</th>									
			<?php if($show != 2) {?><th width="24%">UserDetails</th><?}?>
									<th width="28%">Transaction ID</th>
									<th width="7%">Price</th>
									<th width="10%">Order Date</th>
									<th width="8%">Order Status</th>
			<?php if($show != 1) {?><th width="20%">Merchant Details</th><?}?>
								</tr>
                              <?php
							  	foreach($OrderListResult as $key=>$value){
								?>
							<tr id="test_id_<?php echo $value->id;?>">
								<!--<td align="center"><input id="checkdelete" name="checkdelete[]" value="<?php  //if(isset($value->id) && $value->id != '') echo $value->id  ?>" type="checkbox" hashCount="<?php //if(isset($value->hash_count) && $value->hash_count > 0 ) echo $value->hash_count; ?>"/></td>-->
								<td align="center" nowrap><?php echo (($_SESSION['curpage'] - 1) * ($_SESSION['perpage']))+$key+1;?></td>												
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
							<?php if($show != 2) {?>
						   <td>
								<?php if(isset($image_path) && $image_path != ''){ ?>
									<div class=" col-sm-4  col-lg-3  no-padding">
										<a <?php if(isset($image_path) && basename($image_path) != "no_user.jpeg") { ?>href="<?php echo $original_path; ?>" class="fancybox" title="<?php echo  ucfirst($value->FirstName).' '.ucfirst($value->LastName);?>" <?php } ?> > 
											<img id="<?php echo $value->id ;?>"  width="36" height="36" align="top" class="img_border" src="<?php echo $image_path;?>" >
										</a>
									</div>
								<?php } ?>
									<div class="col-xs-9  col-sm-8 col-lg-9 no-padding" nowrap> 								
										<?php echo  ucfirst($value->FirstName).' '.ucfirst($value->LastName);?>								
									
										
									</div>
								<?php if($show == 0) {?>
									<div class="row-actions col-xs-12">		
										<a href="<?php echo SITE_PATH.'/admin/OrderProductList?cart_id='.base64_encode($value->fkCartId).'&cs=1'; ?>" class="view newWindow" data-toggle="tooltip" data-original-title="Purchased products"><i class="fa fa-shopping-cart"></i></a>		
									</div>
								<?php }?>
								
								
								</td><?php }?>
								<td><?php if(isset($value->TransactionId) && $value->TransactionId != ''){ echo $value->TransactionId; }else echo '-';?></td>
								<td nowrap><?php if(isset($value->TotalPrice) && $value->TotalPrice != ''){ echo price_fomat($value->TotalPrice); }else echo '-';?></td>
								<td nowrap><?php if(isset($value->OrderDate) && $value->OrderDate != '0000-00-00 00:00:00'){ echo date('m/d/Y',strtotime($value->OrderDate)); }else echo '-';?></td>
								<td><?php if(isset($value->OrderStatus) && $value->OrderStatus != ''){ echo $order_status_array[$value->OrderStatus];}?></td>
								<?php if($show != 1) {?>
								<td nowrap>
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
									<?}?>											
								
									
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
						<div class="alert alert-danger alert-dismissable col-sm-5 col-lg-3 col-xs-11"><i class="fa fa-warning"></i>&nbsp;&nbsp;<?php if($show == 0) echo "No Orders found"; else if($show == 1)  echo "No Orders found for this merchant";  else echo "No Orders found for this User";?></div> 
					<?php } ?>	
               </div>
           </div>
	
	</section><!-- /.content -->	
						  	
<?php commonFooter(); ?>
<script type="text/javascript">
$(document).ready(function() {
	$('.fancybox').fancybox();	
	
	$(".newWindow").fancybox({
			scrolling: 'auto',			
			type: 'iframe',
			width: '780',
			maxWidth: '100%',
			
			fitToView: false,
		});
});
</script>
</html>