<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();

$UserName 	= 	$TransactionId = $OrderStatus = $OrderDoneBy = '';
$load_more 	= 	$cur_page 	= $per_page = 0;
$count		=	$tot_rec 	= $Price	= 0;
$FromDate 	= 	$ToDate 	= '';
$today		=	date('m/d/Y');
//getting merchant details
if(isset($_SESSION['merchantDetailsInfo']) && is_array($_SESSION['merchantDetailsInfo'])){
	$merchantInfo  			=	$_SESSION['merchantDetailsInfo'];	
}
else{
	$merchantId				= 	$_SESSION['merchantInfo']['MerchantId'];
	$url					=	WEB_SERVICE.'v1/merchants/'.$merchantId;
	$curlMerchantResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);	
	if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201 && $curlMerchantResponse['merchant']['MerchantId'] != '' ) 
	 {
		$merchantInfo		=	$_SESSION['merchantDetailsInfo']   =	$curlMerchantResponse['merchant'];
	}
}
if((isset($_GET['cs']) && $_GET['cs'] == 1) || (isset($_GET['all']) && $_GET['all'] == 1)){
	destroyPagingControlsVariables();
	unset($_SESSION['tuplit_sess_order_user_name']);
	unset($_SESSION['tuplit_sess_TransactionId']);
	unset($_SESSION['tuplit_sess_Price']);
	unset($_SESSION['tuplit_sess_OrderStatus']);
	unset($_SESSION['tuplit_sess_from_date']);
	unset($_SESSION['tuplit_sess_to_date']);
	unset($_SESSION['tuplit_sess_all']);
	unset($_SESSION['tuplit_sess_OrderDoneBy']);
	if(isset($_GET['cs']) && $_GET['cs'] == 1) {
		$_SESSION['tuplit_sess_from_date'] 	= 	date('m/d/Y');
		$_SESSION['tuplit_sess_to_date'] 	=	date('m/d/Y');
	}
	if(isset($_GET['all']) && $_GET['all'] == 1) {
		$_SESSION['tuplit_sess_all'] 		= 	1;		
	}
}


if(isset($_SESSION['tuplit_sess_order_user_name'])){
	$UserName			=	$_SESSION['tuplit_sess_order_user_name'];
}
if(isset($_SESSION['tuplit_sess_Price'])){
	$Price				=	$_SESSION['tuplit_sess_Price'];
}
if(isset($_SESSION['tuplit_sess_TransactionId'])){
	$TransactionId		=	$_SESSION['tuplit_sess_TransactionId'];
}
 if(isset($_SESSION['tuplit_sess_OrderStatus'])){
	$OrderStatus		=	$_SESSION['tuplit_sess_OrderStatus'];
}	 
if(isset($_SESSION['tuplit_sess_OrderDoneBy'])){
	$OrderDoneBy		=	$_SESSION['tuplit_sess_OrderDoneBy'];
}	
if(isset($_SESSION['tuplit_sess_from_date']) && isset($_SESSION['tuplit_sess_to_date'])) {
	$FromDate			=	$_SESSION['tuplit_sess_from_date'];
	$ToDate				=	$_SESSION['tuplit_sess_to_date'];
}

if(isset($_POST['Search'])){
    if(isset($_POST['UserName']) && $_POST['UserName'] != ''){
		$UserName									=	$_POST['UserName'];
		$_SESSION['tuplit_sess_order_user_name'] 	= 	$UserName;
	}
	if(isset($_POST['TransactionId'])){
		$TransactionId								=	trim($_POST['TransactionId']);
		$_SESSION['tuplit_sess_TransactionId'] 		= 	$TransactionId;
	}
	if(isset($_POST['Price'])){
		$Price										=	trim($_POST['Price']);
		$_SESSION['tuplit_sess_Price'] 		= 	$Price;
	}
	if(isset($_POST['OrderStatus'])){
		$OrderStatus								=	trim($_POST['OrderStatus']);
		$_SESSION['tuplit_sess_OrderStatus'] 		= 	$OrderStatus;
	} 
	if(isset($_POST['OrderDoneBy'])){
		$OrderDoneBy								=	trim($_POST['OrderDoneBy']);
		$_SESSION['tuplit_sess_OrderDoneBy'] 		= 	$OrderDoneBy;
	} 
	if(isset($_POST['from_date']) && isset($_POST['to_date'])) {
		$FromDate									=	$_POST['from_date'];
		$_SESSION['tuplit_sess_from_date']			=	$FromDate;
		$ToDate										=	$_POST['to_date'];
		$_SESSION['tuplit_sess_to_date']			=	$ToDate;
	}
}

setPagingControlValues('ord.id',MERCHANT_PER_PAGE_LIMIT);

if(isset($_POST['cur_page']) && $_POST['cur_page'] != ''){
	$cur_page			=	($_SESSION['curpage'] - 1) * ($_SESSION['perpage']);
	$per_page			=   $_SESSION['perpage'];
}

//getting order list
$url					=	WEB_SERVICE.'v1/orders/?Start='.$cur_page.'&Limit='.$per_page.'&FromDate='.$FromDate.'&ToDate='.$ToDate.'&UserName='.$UserName.'&TransactionId='.$TransactionId.'&OrderStatus='.$OrderStatus.'&Price='.$Price.'&OrderDoneBy='.$OrderDoneBy;
//echo $url;
$curlOrderResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
if(isset($curlOrderResponse) && is_array($curlOrderResponse) && $curlOrderResponse['meta']['code'] == 201 && is_array($curlOrderResponse['OrderList']) ) {
	if(isset($curlOrderResponse['OrderList'])){
		$orderList 	  	= 	$curlOrderResponse['OrderList'];	
		$tot_rec	  	= 	$curlOrderResponse['meta']['totalCount'];
	}
} else if(isset($curlOrderResponse['meta']['errorMessage']) && $curlOrderResponse['meta']['errorMessage'] != '') {
		$errorMessage	=	$curlOrderResponse['meta']['errorMessage'];
} else {
		$errorMessage	= 	"Bad Request";
}
commonHead();
top_header(); ?>


<body class="skin-blue fixed body_height">
	<section>
		<div class="col-lg-10 box-center">	
			<!--<section class="content-header">
				<h1>Order History</h1>
				<a href="OrderHistory?all=1" name="View All Orders" alt="View All Orders" title="View All Orders">View All Orders</a>
			</section>-->
				<section class="content-header" style="margin-top: 20px;">					
	                <h1 class="col-sm-9 col-lg-10 no-padding text-left" style="margin-top:0px;">Order History</h1>
					<a href="OrderHistory?all=1" class="col-sm-3  col-lg-2  btn btn-success margin-bottom padding10" title="View All Orders">View All Orders</a>
				</section>
				<div class="no-padding col-xs-12 text-left margin-bottom" style="margin-bottom:25px;">
					<form name="search_Orders" action="OrderHistory?cs=1" method="post">
					<div class="box box-primary">
							<div class="col-sm-4 col-lg-3 form-group">
								<label>Customer Name</label>
								<input type="text" class="form-control" name="UserName" id="UserName"  value="<?php  if(isset($UserName) && !empty($UserName)) echo $UserName;  ?>" >
							</div>
							<div class="col-sm-4 col-lg-3 form-group">
								<label>Total Amount</label>
								<input type="text" class="form-control" name="Price" id="Price" onkeypress="return isNumberKey_price(event);" value="<?php  if(isset($Price) && !empty($Price)) echo $Price;  ?>" >
							</div>
							<div class="col-sm-4 col-lg-4 form-group">
								<label>Transaction Id</label>
								<input type="text" class="form-control" name="TransactionId" id="TransactionId"  value="<?php  if(isset($TransactionId) && !empty($TransactionId)) echo $TransactionId;  ?>" >
							</div>
							<div class="col-sm-4 col-lg-2 form-group">
								<label>Order Status</label>
								<select class="form-control " name="OrderStatus">
									<option value="" >Select</option>								
									<?php if(isset($orderStatusArray) && count($orderStatusArray)>0) {
										foreach($orderStatusArray as $key=>$val) {								
									?>
									<option value="<?php echo $key;?>" <?php if(isset($OrderStatus) && $OrderStatus == $key) echo "selected";?>><?php echo ucfirst($val);?></option>
									<?php } } ?>								
								</select>
							</div>
							<div class="col-sm-4 col-lg-3 form-group">
								<label>Order DoneBy</label>
								<select class="form-control" name="OrderDoneBy">
									<option value="" >Select</option>								
									<option value="1" <?php if(isset($OrderDoneBy) && $OrderDoneBy == 1) echo "selected";?>>Customer</option>
									<option value="2" <?php if(isset($OrderDoneBy) && $OrderDoneBy == 2) echo "selected";?>>Merchant</option>
								</select>
							</div>
							<div class="col-sm-4 col-lg-3 form-group">
								<label>From Date</label>
								<input  type="text" id = "from_date" class="form-control datepicker" autocomplete="off" title="Select Date" name="from_date" value="<?php if(isset($_SESSION['tuplit_sess_from_date'])) echo $_SESSION['tuplit_sess_from_date']; else if((isset($_SESSION['tuplit_sess_all']) && $_SESSION['tuplit_sess_all'] == 1)) echo ""; else echo $today; ?>" onchange="return emptyDates(this);">
							</div>
							<div class="col-sm-4 col-lg-3 form-group">
								<label>To Date</label>
								<input type="text" id = "to_date" class="form-control datepicker" autocomplete="off"  title="Select Date" name="to_date" value="<?php if(isset($_SESSION['tuplit_sess_to_date'])) echo $_SESSION['tuplit_sess_to_date']; else  if((isset($_SESSION['tuplit_sess_all']) && $_SESSION['tuplit_sess_all'] == 1)) echo ""; else echo $today; ?>" onchange="return emptyDates(this);">
							</div>
						<div class="box-footer col-sm-12" align="center">
							<input type="submit" class="btn btn-success" name="Search" id="Search" value="Search">
						</div>					
					</div>
					</form>
				</div>
			<?php if(isset($orderList) && !empty($orderList)) { 
					//echo "<pre>"; echo print_r($orderList); echo "</pre>";
			   ?>
			<div class="row product_list paging no-margin">
				<div class="col-xs-12 col-sm-3 no-padding">
					<span class="totl_txt">Total Order(s) : <b><?php echo $tot_rec; ?></b>
						
					</span>
				</div>
				<div class="col-xs-12 col-sm-9 no-padding">
					<div class="dataTables_paginate paging_bootstrap row no-margin">
							<?php pagingControlLatestAjax($tot_rec,'OrderHistory'); ?>
					</div>
				</div>
			</div>			
				<div class="col-xs-12 no-padding top-margin">				 
					<div class="box">
					   <div class="box-body table-responsive no-padding no-margin">
						<table class="table table-hover">
							   <tr>
									<th align="center" width="3%" class="text-center"> #</th>									
									<th width="20%" class="text-left" colspan="2">Customer Details</th>
									<th width="10%" class="text-left">Transaction Id</th>
									<th width="7%" class="text-left">Order DoneBy</th>
									<th width="7%" class="text-center">Total Items</th>
									<th width="10%" class="text-right">Total Amount</th>
									<th width="10%" class="text-center">Order Date</th>
									<th width="7%" class="text-center">Order Status</th>
									<th width="3%" class="text-center">Action</th>
								</tr>
							  <?php
								foreach($orderList as $key=>$value){
									//echo "<pre>"; echo print_r($orderList); echo "</pre>";die();
									$count += 1;
									$name	=	ucfirst($value["FirstName"]).' '.ucfirst($value["LastName"]);
								?>
							<tr>
								<td align="center"><?php echo (($_SESSION['curpage'] - 1) * ($_SESSION['perpage']))+$key+1;?></td>
								<td width="4%" align="left">
									<?php if(isset($value["Photo"]) && $value["Photo"] != ''){ ?>
										<a <?php if(isset($value["Photo"]) ) { ?>href="<?php echo $value["Photo"]; ?>" class="fancybox" title="<?php echo  $name;?>" <?php } ?> > 
											<img  width="36" height="36" align="top" class="img_border" src="<?php echo  $value["ThumbPhoto"];?>" >
										</a>
									<?php } else {?> <img  width="36" height="36" align="top" class="img_border" src="<?php echo MERCHANT_IMAGE_PATH.'no_user.jpeg';?>" > <?php  } ?>
								</td>
								<td width="14%" align="left"><?php if(isset($name) && $name != ''){ ?><a href="UserDetail?viewId=<?php echo base64_encode($value["PUserId"]);?>&cs=1" class="userWindow white-space" > <?php echo $name; ?></a><?php }else echo '-';?></td>
								<td width="10%" class="white-space" align="left"><?php if(isset($value["TransactionId"]) && $value["TransactionId"] != ''){ echo $value["TransactionId"]; }else echo '-';?></td>
								<td align="left"><?php if(isset($value["OrderDoneBy"]) && $value["OrderDoneBy"] != ''){ if($value["OrderDoneBy"] == 1) echo "Customer"; if($value["OrderDoneBy"] == 2) echo "Merchant";}else echo '-';?></td>
								<td align="center"><?php if(isset($value["TotalItems"]) && $value["TotalItems"] != ''){ echo $value["TotalItems"]; }else echo '-';?></td>
								<td align="right"><?php if(isset($value["TotalPrice"]) && $value["TotalPrice"] != ''){ echo price_fomat($value["TotalPrice"]); }else echo '-';?></td>
								<td align="center"><?php if(isset($value["OrderDate"]) && $value["OrderDate"] != '0000-00-00 00:00:00'){ echo date('m/d/Y',strtotime($value["OrderDate"])); }else echo '-';?></td>
								<td align="center" class="<?php if(isset($value["OrderStatus"]) && $value["OrderStatus"] != ''){ echo $order_status_array[$value["OrderStatus"]];}?>"><strong><?php if(isset($value["OrderStatus"]) && $value["OrderStatus"] != ''){ echo $order_status_array[$value["OrderStatus"]];}?></strong></td>
								<td align="center">
									<?php if(isset($value['Products']) && count($value['Products'])>0) {	 ?>
									<a href="#<?php echo $value["CartId"]; ?>" class="productWindow" title="View Products"><i class="fa fa-search fa-lg"></i></a>
									<?php } ?>
								</td>
							</tr>							
							<?php } //end for ?>	
						   </table>
							<!-- End product List -->						 
							<?php } else { ?>
								<div class="row clear">		
									<div align="center" class="alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10"><i class="fa fa-warning"></i> <?php echo 'No orders found';?>	</div>							
								</div>							
							<?php } ?>						
						</div><!-- /.box-body -->
				</div>	
			</div>
		</div>
	</section>
	<?php if(isset($orderList) && !empty($orderList)) { 
		foreach($orderList as $key=>$value){
	 ?>
	<div class=" popup_width fixed popup_bg" id="<?php echo $value["CartId"]; ?>" style="display:none;">	
	<div class="popup_white">
	<div class="col-sm-12 no-padding ">
		<section class="content-header">
			<h1 class="">Product List</h1>
		</section>
		<div class="row no-margin space_bottom">
			<span class="totl_txt" style="margin-top:0px;">Total Product(s) : <b><?php echo count($value['Products']); ?></b></span>										
			<span class="totl_txt pull-right">Total Amount : <b><?php echo price_fomat($value["TotalPrice"]); ?></b></span>										
		</div>
		<div class="product_list">
			<div class="box box-primary no-padding no-margin">
				<div class="box-body table-responsive no-padding no-margin">
					<?php if(isset($value['Products']) && count($value['Products']) > 0) { ?>
					<table class="table table-hover" width="100%">
						   <tr>
								<th align="center" width="3%" style="text-align:center">#</th>									
								<th width="">Item Details</th>
								<th width="10%" class="text-center">Quantity</th>
								<th width="10%" class="text-right">Price</th>
								<th width="10%" class="text-right">Discounted Price</th>
								<th width="10%" class="text-right">Subtotal</th>
							</tr>
							<?php
							foreach($value['Products'] as $key=>$value1){
								$key += 1;
							?>
							<tr>
								<td align="center"><?php echo $key;?></td>												
								<td>
								<div class="col-xs-3 col-sm-4 col-md-3 no-padding">
								<?php if(isset($value1["Photo"]) && $value1["Photo"] != ''){ ?>
									<!--<a <?php if(isset($value1["Photo"]) ) { ?>href="<?php echo  PRODUCT_IMAGE_PATH.$value1["Photo"];?>" class="fancybox" <?php } ?> > -->
										<img  width="36" height="36" align="top" class="img_border" src="<?php echo  PRODUCT_IMAGE_PATH.$value1["Photo"];?>" >
									<!--</a>-->
								<?php } else { ?> <img  width="36" height="36" align="top" class="img_border" src="<?php echo MERCHANT_IMAGE_PATH.'no_photo_burger.jpg';?>" > <?php  } ?>
								</div>
								<div class="col-xs-9 col-sm-8 col-md-9">
									<?php echo $value1["ItemName"]; ?>
								</div>
								</td>
								<td align='center'><?php echo $value1["ProductsQuantity"]; ?></td>
								<td align="right"><?php echo price_fomat($value1["ProductsCost"]); ?></td>
								<td align="right"><?php echo price_fomat($value1["DiscountPrice"]); ?></td>
								<td align="right"><?php echo price_fomat($value1["TotalPrice"]); ?></td>
							</tr>
						<?php } ?>	
					   </table>
						<!-- End product List -->						 
					<?php } else { ?>
						<div class="row clear">		
							 <div align="center" class="alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10"><i class="fa fa-warning"></i><?php echo "No Products found.";?></div>							
						</div>							
					<?php } ?>						
				</div><!-- /.box-body -->
			</div>					
		</div>	
		</div>
	 </div>				
</div>							
	<?php } } footerLogin(); ?>
	<?php commonFooter(); ?>
<script type="text/javascript">
$(document).ready(function() {
	$(".productWindow").fancybox({
			width: '500',
			scrolling: 'auto',			
			title: null,
			//maxWidth: '100%', 
			fitToView: true
						
	});
	$(".fancybox").fancybox();
	$(".userWindow").fancybox({
			scrolling: 'none',			
			type: 'iframe',
			width: '350',
			//minHeight : 180,
			maxWidth: '100%',  // for respossive width set					
			fitToView: false,
			 title: null,
			//afterClose : function() {
			//location.reload();
			//return;
		//}
	});
});
$(".datepicker").datepicker({
	showButtonPanel	:	true,        
	buttonText		:	'<i class="fa fa-calendar"></i>',
	buttonImageOnly	:	true,
	buttonImage		:	path+'webresources/images/calender.png',
	dateFormat		:	'mm/dd/yy',
	changeMonth		:	true,
	changeYear		:	true,
	hideIfNoPrevNext:	true,
	showWeek		:	true,
	yearRange		:	"c-30:c",
	closeText		:   "Close",
	maxDate			: new Date()
   });
 function emptyDates(arg) { 
	var id = arg.getAttribute('name');		
	if(id == 'year' || id == 'month') {
		$('#from_date').val('');
		$('#to_date').val('');		
	}
	else {
		$("#year").children("option[value ='']").attr('selected', true);
		$("#month").children("option[value ='']").attr('selected', true);
	}
 }
</script>
</html>
