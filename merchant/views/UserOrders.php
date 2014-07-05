<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();

//getting merchant details

if(isset($_SESSION['merchantDetailsInfo']) && is_array($_SESSION['merchantDetailsInfo'])){
	$merchantInfo  =	$_SESSION['merchantDetailsInfo'];	
}
else{
	$merchantId					= 	$_SESSION['merchantInfo']['MerchantId'];
	$url						=	WEB_SERVICE.'v1/merchants/'.$merchantId;
	$curlMerchantResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);	
	if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201 && $curlMerchantResponse['merchant']['MerchantId'] != '' ) 
	 {
		$_SESSION['merchantDetailsInfo']   =	$curlMerchantResponse['merchant'];
		$merchantInfo  =	$_SESSION['merchantDetailsInfo'];
	}
}
if(isset($_GET['cs']) && $_GET['cs'] == 1){
	destroyPagingControlsVariables();
	$_SESSION['tuplit_sess_order_user_name'] = '';
	$_SESSION['tuplit_sess_order_visit']	=	'';
	$_SESSION['tuplit_sess_order_total_spend']	=	'';
}
$UserName = $VisitCount = $TotalSpend = '';
$load_more = $cur_page = $per_page = 0;
$count	=	$tot_rec = 0;
$FromDate = $ToDate = date('Y-m-d');


if(!isset($_SESSION['tuplit_sess_order_from_date'])) 
	$_SESSION['tuplit_sess_order_from_date']	=	$FromDate;	//=	$today;//
if(!isset($_SESSION['tuplit_sess_order_to_date'])) 
	$_SESSION['tuplit_sess_order_to_date']	=	 $ToDate;
	
	
setPagingControlValues('ord.id',MERCHANT_PER_PAGE_LIMIT);

if(isset($_POST['Search']) && $_POST['Search'] != ''){
	if(isset($_POST['from_date']) && !empty($_POST['from_date']) && isset($_POST['to_date']) && !empty($_POST['to_date'])) {
		$FromDate			=	$_POST['from_date'];
		$_SESSION['tuplit_sess_order_from_date']	=	$FromDate;
		$ToDate				=	$_POST['to_date'];
		$_SESSION['tuplit_sess_order_to_date']	=	$ToDate;
	}
}

if(isset($_POST['cur_page']) && $_POST['cur_page'] != ''){
	$cur_page		=	($_SESSION['curpage'] - 1) * ($_SESSION['perpage']);
	$per_page		=   $_SESSION['perpage'];
}
if(isset($_SESSION['tuplit_sess_order_from_date']) && $_SESSION['tuplit_sess_order_from_date'] != ''){
	 $FromDate		=	$_SESSION['tuplit_sess_order_from_date'];
}
if(isset($_SESSION['tuplit_sess_order_to_date']) && $_SESSION['tuplit_sess_order_to_date'] != ''){
	 $ToDate		=	$_SESSION['tuplit_sess_order_to_date'];
}
if(isset($_GET['viewId']) && $_GET['viewId'] != ''){
    $UserId			=	base64_decode($_GET['viewId']);
	//getting order list of users
	$url					=	WEB_SERVICE.'v1/orders/?UserId='.$UserId.'&Start='.$cur_page.'&Limit='.$per_page.'&FromDate='.$FromDate.'&ToDate='.$ToDate;
	$curlOrderResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
	//echo "<pre>"; print_r( $curlOrderResponse); echo "</pre>";
	if(isset($curlOrderResponse) && is_array($curlOrderResponse) && $curlOrderResponse['meta']['code'] == 201 && is_array($curlOrderResponse['OrderList']) ) {
		if(isset($curlOrderResponse['OrderList'])){
			$orderList 	  = $curlOrderResponse['OrderList'];	
			$tot_rec	  = $curlOrderResponse['meta']['totalCount'];
		}
	} else if(isset($curlOrderResponse['meta']['errorMessage']) && $curlOrderResponse['meta']['errorMessage'] != '') {
			$errorMessage	=	$curlOrderResponse['meta']['errorMessage'];
	} else {
			$errorMessage	= 	"Bad Request";
	} 
}
commonHead();
?>

<body class="skin-blue fixed">
			<section class="row content-header no-margin">
                <h1 class="no-margin space_bottom">Order List</h1>
            </section>
			<div class="row no-margin">
				<div class="product_list">
					<form name="search_merchant" action="UserOrders?viewId=<?php echo $_GET['viewId'];?>&cs=1" method="post">
					<div class="box box-primary">
						
						<div class="box-body no-padding" >				
							<div class="col-sm-3 col-xs-3 form-group">
								<label>Start Date</label>
								<input  type="text" id = "from_date" class="form-control datepicker" autocomplete="off" title="Select Date" name="from_date" value="<?php if(isset($FromDate) && $FromDate != '') echo date('m/d/Y',strtotime($FromDate));?>" onchange="return emptyDates(this);">
							</div>
						</div>
						<div class="box-body no-padding" >				
							<div class="col-sm-3 col-xs-3 form-group">
								<label>End Date</label>
								<input type="text" id = "to_date" class="form-control datepicker" autocomplete="off"  title="Select Date" name="to_date" value="<?php if(isset($ToDate) && $ToDate != '') echo date('m/d/Y',strtotime($ToDate));?>" onchange="return emptyDates(this);">
							</div>
						</div>
						<div class="box-footer col-sm-12 clear" align="center">
							<input type="submit" class="btn btn-success" name="Search" id="Search" value="Search">
						</div>					
					</div>
					</form>
				</div>
			</div>
			<div class="row no-margin product_list paging">
				<div class="col-xs-12 col-sm-3 no-padding">
					<span class="totl_txt">Total Order(s) : <b><?php echo $tot_rec; ?></b></span>
				</div>
				<div class="col-xs-12 col-sm-9 no-padding">
					<div class="dataTables_paginate paging_bootstrap row no-margin">
							<?php pagingControlLatestAjax($tot_rec,'UserOrders?viewId='.$_GET['viewId'].'');?>
					</div>
				</div>
			</div>
			<div class="product_list space_top">
				<?php if(isset($orderList) && !empty($orderList)) { ?>
				<div class="box box-primary no-padding no-margin">
					<div class="box-body table-responsive no-padding no-margin">
						
						<table class="table "> <!-- table-hover -->
								<tr>
									<th align="center" width="4%" class="text-center" rowspan="2"> #</th>									
									<th width="25%" rowspan="2">Transaction Id</th>
									<th width="10%" rowspan="2" class="text-right">Price</th>
									<th width="10%" rowspan="2" class="text-center">Order Date</th>
									<th width="10%" rowspan="2">Order Status</th>
									<th width="25%" colspan="3" class="text-center">Product Details</th>
								</tr>
								<tr>
									<th>Item Name</th>
									<th class="text-center">Quantity</th>
									<th class="text-right">Total Price</th>
								</tr>
								<?php if(isset($orderList) && is_array($orderList) && count($orderList) > 0 ) {
									$i = 0;
									foreach($orderList as $key => $value) { 
										if(count($value["Products"]) > 0 ) 
											$rowspan	=	count($value["Products"]);
										else
											$rowspan	= '';
									?>
									 <tr>
										<td align="center" rowspan="<?php echo $rowspan;?>"><?php echo (($_SESSION['curpage'] - 1) * ($_SESSION['perpage']))+$key+1;?></td>
										<td align="left" rowspan="<?php echo $rowspan;?>"><?php if(isset($value["TransactionId"]) && $value["TransactionId"] != ''){ echo $value["TransactionId"]; }else echo '-';?></td>
										<td align="right" rowspan="<?php echo $rowspan;?>"><?php if(isset($value["TotalPrice"]) && $value["TotalPrice"] != ''){ echo '$'.number_format($value["TotalPrice"],2,'.',','); }else echo '-';?></td>
										<td align="center" rowspan="<?php echo $rowspan;?>"><?php if(isset($value["OrderDate"]) && $value["OrderDate"] != '0000-00-00 00:00:00'){ echo date('m/d/Y',strtotime($value["OrderDate"])); }else echo '-';?></td>
										<td align="left" rowspan="<?php echo $rowspan;?>"><?php if(isset($value["OrderStatus"]) && $value["OrderStatus"] != ''){ echo $order_status_array[$value["OrderStatus"]];}?></td>
											<?php 
											 $j= 1;
											foreach($value["Products"] as $p_key => $p_value) { 
											?>
										<td><?php if(isset($p_value["ItemName"]) && $p_value["ItemName"] != ''){ echo $p_value["ItemName"]; }else echo '-';?></td>
										<td align="center"><?php if(isset($p_value["ProductsQuantity"]) && $p_value["ProductsQuantity"] != ''){ echo $p_value["ProductsQuantity"]; }else echo '-';?></td>
										<td align="right"><?php if(isset($p_value["TotalPrice"]) && $p_value["TotalPrice"] != ''){ echo '$'.number_format($p_value["TotalPrice"],2,'.',','); }else echo '-';?></td>
									</tr>
									<?php $j++; } ?>
											
						<?php 		$i++;  }
								 }
							?>
                               					
					</div><!-- /.box-body -->
				</div>	
				<?php } else { ?>
					<div class="row clear">		
						 <div align="center" class="alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10"><i class="fa fa-warning"></i> <?php echo $errorMessage	;?>	</div>							
					</div>	
			   <?php  } ?>
								
			</div>	
		<?php //footerLogin(); ?>
	<?php commonFooter(); ?>
	<script type="text/javascript">
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
			closeText		:   "Close"
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
