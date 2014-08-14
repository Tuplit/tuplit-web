<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();
if(isset($_GET['cs']) && $_GET['cs']==1) {
	unset($_SESSION['tuplitNewOrderTotal']);
	unset($_SESSION['tuplitNewOrderStart']);
}

//Rejecting order
if(isset($_GET['Reject']) || isset($_GET['Approve'])) {
	if(isset($_GET['Reject']) && !empty($_GET['Reject'])) {
		$data	=	array(
					'OrderId'		=> $_GET['Reject'],
					'OrderStatus'	=> '2'
					);
	}
	if(isset($_GET['Approve']) && !empty($_GET['Approve'])) {	
		$data	=	array(
					'OrderId'		=> $_GET['Approve'],
					'OrderStatus'	=> '1'
					);
	}
	$url				=	WEB_SERVICE.'v1/orders/';
	$method				=	'PUT';
	$curlResponse		=	curlRequest($url,$method,json_encode($data),$_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlResponse) && is_array($curlResponse) && $curlResponse['meta']['code'] == 201) {
		$_SESSION['successMessage'] = $curlResponse['notifications'][0];		
	} else if(isset($curlResponse['meta']['errorMessage']) && $curlResponse['meta']['errorMessage'] != '') {
		$errorMessage	=	$curlResponse['meta']['errorMessage'];
	} else {
		$errorMessage 	= 	"Bad Request";
	}
	header("location:Orders");
	die();
}

if(isset($_SESSION['successMessage']) && !empty($_SESSION['successMessage'])) {
	$successMessage		=	$_SESSION['successMessage'];
	unset($_SESSION['successMessage']);
}
//getting merchant details
if(isset($_SESSION['merchantDetailsInfo']) && is_array($_SESSION['merchantDetailsInfo'])){
	$merchantInfo  				=	$_SESSION['merchantDetailsInfo'];	
	if(!empty($merchantInfo['DiscountTier']) || $merchantInfo['DiscountTier'] != 0 ) {
	}
	else {
		$hide					= 	1;	
	}
}

if(isset($_SESSION['merchantDetailsInfo']) && is_array($_SESSION['merchantDetailsInfo'])){
	$merchantInfo  				=	$_SESSION['merchantDetailsInfo'];	
}
else{
	$merchantId					= 	$_SESSION['merchantInfo']['MerchantId'];
	$url						=	WEB_SERVICE.'v1/merchants/'.$merchantId."?From=0";
	$curlMerchantResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201 && $curlMerchantResponse['merchant']['MerchantId'] != '' ) 
	 {
		$merchantInfo  			= 	$_SESSION['merchantDetailsInfo']   =	$curlMerchantResponse['merchant'];
	}
}
if(isset($_SESSION['merchantInfo']['AccessToken'])){ 	

	//getting Order List
	$url					=	WEB_SERVICE.'v1/orders/?Type=1';
	$curlCategoryResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlCategoryResponse) && is_array($curlCategoryResponse) && $curlCategoryResponse['meta']['code'] == 201 && is_array($curlCategoryResponse['OrderList']) ) {
		if(isset($curlCategoryResponse['OrderList']))
			$todayOrderList = $curlCategoryResponse['OrderList'];
	} 
	
	//getting new Order List
	$TotalNewOrders	 =	$totalorderlist =	0;
	if(isset($_SESSION['tuplitNewOrderStart']))
		$End	=	$_SESSION['tuplitNewOrderStart'];
	else
		$End	=	12;
	
	$url					=	WEB_SERVICE.'v1/orders/new?Type=1&End='.$End;
	//echo $url;
	$curlCategoryResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlCategoryResponse) && is_array($curlCategoryResponse) && $curlCategoryResponse['meta']['code'] == 201 && is_array($curlCategoryResponse['newOrderDetails']) ) {
		if(isset($curlCategoryResponse['newOrderDetails']))
			$TotalNewOrders		=	$curlCategoryResponse['meta']['totalCount'];
			$newOrderList = $curlCategoryResponse['newOrderDetails'];
			$_SESSION['tuplitNewOrderTotal']	=	$TotalNewOrders;
			if($TotalNewOrders > 12) {
				if($TotalNewOrders > $End)
					$totalorderlist	= $End;
				else
					$totalorderlist	=	$TotalNewOrders;
			}
			else
				$totalorderlist	=	$TotalNewOrders;
	} 
}

if(isset($errorMessage) && $errorMessage != ''){
	$msg			=	$errorMessage;
	$display 		= 	"block";
	$class   		= 	"alert-danger";
	$class_icon 	= 	"fa-warning";
	$errorMessage 	= 	'';
}else if(isset($successMessage) && $successMessage != ''){
	$msg			=	$successMessage;
	$display		=	"block";
	$class 			= 	"alert-success";
	$class_icon 	= 	"fa-check";
	$successMessage = 	'';	
}
commonHead();
?>
<body class="skin-blue fixed">
		<?php top_header(); ?>		
		<section class="content top-spacing" align="center">
		<?php if(empty($merchantInfo['MangoPayUniqueId'])){?>
				<div align="center" class="alert alert-danger alert-dismissable  col-lg-5 col-sm-7  col-md-5 col-xs-12"><i class="fa fa-warning"></i>&nbsp;&nbsp;Please connect with MangoPay in Settings to view orders.</div>
		<?php }else{
			 if(isset($msg) && $msg != '') { ?>
			<div align="center" class="alert <?php  echo $class;  ?> alert-dismissable col-xs-10 col-sm-5 col-lg-3"><i class="fa <?php  echo $class_icon;  ?>"></i>  <?php echo $msg; ?></div>
			<?php } ?>			
			<div class="col-md-12 col-lg-12 box-center" >				
				<section class="content-header">
					
	                <h1 class="col-sm-9 col-lg-10 no-padding no-margin text-left">New Orders <?php if(isset($_SESSION['tuplitNewOrderTotal']) && !empty($_SESSION['tuplitNewOrderTotal'])) echo " - ".$_SESSION['tuplitNewOrderTotal']; ?></h1>
					<a href="OrderHistory?cs=1" class="col-sm-3  col-lg-2  btn btn-success margin-bottom" title="View Orders History"><i class="fa fa-history"></i> View Orders History</a>
					
	                <?php  if ($_SERVER['HTTP_HOST'] == '172.21.4.104') { ?>
						<div class="no-padding col-xs-12 text-left" style="color:#01a99a;font-size:20px;" id="OrdersDisplayed" name="OrdersDisplayed">Orders Displayed 
							<?php 
								if(isset($totalorderlist) && !empty($totalorderlist)) {
									echo " - ".$totalorderlist; 
								}
							?>
						</div>		
					<?php } ?>
				</section>
				<div class="clear box box-primary  order_list">
					<div class="box-body no-padding" id="NewOrderListHtml">
						<!-- Start New Orders List -->						
							<?php if(isset($newOrderList) && !empty($newOrderList)) {									
									foreach($newOrderList as $key=>$value) {
										//echo "<pre>"; echo print_r($value); echo "</pre>"; die();
										$ordersClass	=	'';
										if($value['TotalItems'] == 1)
											$ordersClass	=	'one_item';
										else if($value['TotalItems'] == 2)
											$ordersClass	=	'two_item';
										else if($value['TotalItems'] >= 3)
											$ordersClass	=	'more_item';
										$name = ucfirst($value['FirstName']).' '.ucfirst($value['LastName']);
							?>
							<div class="col-md-3 col-sm-4 col-lg-2 col-xs-12">
									<div class="small-box">									
										<div class="col-xs-3 no-padding">
											<a href="<?php echo $value['Photo']?>" class="fancybox" title="<?php echo $name; ?>">
												<img height="50" width="50" src="<?php echo $value['ThumbPhoto']?>" alt=""/>
											</a>		
											<?php if(!SERVER){ ?>
											<!--<a class="newWindow" href="PrintOrder?cs=1&printId=<?php echo  $value['OrderId']; ?>" title="Print"><i class="fa fa-print"></i></a>&nbsp;&nbsp;-->
											<a class="newWindow" title="View Products" href="OrderProductDetail?cs=1&orderId=<?php echo  $value['OrderId']; ?>"><i class="fa fa-search fa-lg" style=" font-size: 0.99em;vertical-align: 3%;" ></i></a>
											<?php } ?>									
										</div>					
										<div class="col-xs-9">
											<span class="text-small" data-toggle="tooltip" title="<?php echo $name; ?>" data-original-title="<?php echo $name; ?>"><?php echo displayText($name,17); ?></span>
											<span class="help-block no-margin"><?php echo $value['Email']?></span>
											<span class="help-block no-margin"><?php echo $value['UserId']?></span>
											<span class="help-block no-margin"><?php echo time_ago($value['OrderDate']); ?></span>
											
										</div>
										
										<div class="col-xs-12 no-padding list_height clear <?php echo $ordersClass; ?>">
											<?php if(!empty($value['Products'])) {											
													foreach($value['Products'] as $key1=>$pro_val) {
														if($pro_val['Refund'] != 2) {
															if($key1 < 2) { ?>
															
															<div class="col-xs-7 no-padding"><?php echo  $pro_val['ItemName']?> </div>
															<div class="col-xs-5 no-padding text-right"><?php echo $pro_val['ProductsQuantity'].'&nbsp;&nbsp;&nbsp;'.price_fomat($pro_val['TotalPrice']); ?></div>
																
															<?php } else {  ?> 
															
															<div class="col-xs-12 no-padding otherItemsNew<?php echo $key;?>" style="display:none;">
																<div class="col-xs-7 no-padding"><?php echo  $pro_val['ItemName']?> </div>
																<div class="col-xs-5 no-padding text-right"><?php echo $pro_val['ProductsQuantity'].'&nbsp;&nbsp;&nbsp;'.price_fomat($pro_val['TotalPrice']); ?></div>
															</div>
											<?php } }  }?>
												<div class="text-center col-xs-12 no-padding " <?php if(count($value['Products']) <= 2) {  ?>style="visibility:hidden;"<?php } ?>><a style="cursor:pointer" id="linkNew<?php echo $key; ?>" onclick="return showAllItems('New<?php echo $key; ?>');">Show all items</a></div>
											<?php }  ?>
										</div>
										
										<div class="col-xs-12 no-padding"><hr></div>											
										<div class="col-xs-8 no-padding"><strong>Total</strong> </div>
										<div class="col-xs-4 no-padding text-right"><strong><?php echo price_fomat($value['TotalPrice']); ?></strong></div>		
																
										<!-- <div class="col-md-12 no-padding"><hr></div> -->
										<div class="col-xs-12" style="padding-top:7px;"></div>
										<div class="col-xs-4 no-padding"><a class="text-red" class="Reject" href="?Reject=<?php echo  $value['OrderId']; ?>" onclick="return approveReject('reject');" ><i class="fa fa-trash-o"></i> Reject</a></div>
										<?php if($value['OrderDoneBy'] == 1) { ?>
											<div class="col-xs-8 no-padding text-right"><a href="?Approve=<?php echo  $value['OrderId']; ?>"  id="submit" class="btn btn-success" title="Approve" onclick="return approveReject('approve');"><i class="fa fa-check"></i>  Approve</a></div>		
										<?php } ?>
									</div>
								</div> 				
							<?php } } else { ?>
								<div class="alert alert-danger alert-dismissable col-xs-10 col-sm-5 col-lg-3" align="center"><i class="fa fa-warning"></i>&nbsp;&nbsp;No new orders found.</div>
							<?php } ?>
						<!-- End New Orders List -->						
							<?php if(isset($_SESSION['tuplitNewOrderTotal']) && !empty($_SESSION['tuplitNewOrderTotal']) && $_SESSION['tuplitNewOrderTotal'] > $End) { ?>
									<div class="col-xs-12 clear text-center" id="loadmorehome"> <a style="cursor:pointer" class="loadmore" id="loadmore" name="loadmore" class="btn btn-success" title="Load More" onclick="return loadMoreNewOrders();"><i class="fa fa-download"></i> <strong>Load More</strong></a></div>
							<?php } ?>
					</div>
					
					<!-- /.box-body -->
			
				</div>
				<input type="hidden" id="newOrderStart" name="newOrderStart" value="<?php if($End > 12) echo ($End - 12); else echo "0"; ?>" />
				<input type="hidden" id="newOrderTotalhide" name="newOrderTotalhide" value="<?php if(isset($_SESSION['tuplitNewOrderTotal'])) echo $_SESSION['tuplitNewOrderTotal']; ?>"/>
		 </div>
		 <div class="col-md-12 col-lg-12 box-center">				
				<section class="content-header">
	                <h1 class="col-sm-9 col-lg-10 no-padding no-margin text-left">Approved / Rejected Today Orders</h1>
	            </section>
				<div class="box box-primary  order_list clear ">
					<div class="box-body no-padding">
					<!-- Start Today Orders List -->						
						<?php if(isset($todayOrderList) && !empty($todayOrderList)) {
								foreach($todayOrderList as $key=>$value) {
									$ordersClass	=	'';
									if($value['TotalItems'] == 1)
										$ordersClass	=	'one_item';
									else if($value['TotalItems'] == 2)
										$ordersClass	=	'two_item';
									else if($value['TotalItems'] >= 3)
										$ordersClass	=	'more_item';
									$name = ucfirst($value['FirstName']).' '.ucfirst($value['LastName']);
						?>
						<div class="col-md-3 col-sm-4 col-lg-2 col-xs-12">
							<div class="small-box ">		
								<div class="col-xs-3 no-padding">					
									<a href="<?php echo $value['Photo']?>" class="fancybox" title="<?php echo $name; ?>">
										<img height="50" width="50" src="<?php echo $value['ThumbPhoto']?>" alt=""/>
									</a>
									<?php if(!SERVER){ ?>
										<a class="newWindow" href="PrintOrder?cs=1&printId=<?php echo  $value['OrderId']; ?>" title="Print"><i class="fa fa-print"></i></a>&nbsp;&nbsp;
										<a class="newWindow" title="View Products" href="OrderProductDetail?cs=1&orderId=<?php echo  $value['OrderId']; ?>"><i class="fa fa-search fa-lg" style=" font-size: 0.99em;vertical-align: 3%;" ></i></a>
									<?php } ?>
								</div>		
								<div class="col-xs-9">									
									<span data-toggle="tooltip" title="<?php echo $name; ?>"><?php echo displayText($name,17); ?></span>
									<span class="help-block no-margin"><?php echo $value['Email']?></span>
									<span class="help-block no-margin"><?php echo $value['UserId']?></span>
									<span class="help-block no-margin"> <?php	echo time_ago($value['OrderDate']); ?> </span>
									
									<!--<a class="newWindow" href="PrintOrder?cs=1&printId=<?php echo  $value['OrderId']; ?>" ><i class="fa fa-print"></i></a> -->
								</div>
								<div class="col-xs-12 no-padding list_height <?php echo $ordersClass; ?>">
									<?php if(!empty($value['Products'])) {											
											foreach($value['Products'] as $key1=>$pro_val) {
												if($key1 < 2) { ?>
												<div class="col-xs-7 no-padding"><?php echo  $pro_val['ItemName']?> </div>
												<div class="col-xs-5 no-padding text-right"><?php echo $pro_val['ProductsQuantity'].'&nbsp;&nbsp;&nbsp;'.price_fomat($pro_val['TotalPrice']); ?></div>
												<?php } else {  ?> 
												
												<div class="col-xs-12 no-padding otherItemsToday<?php echo $key;?>" style="display:none;">
													<div class="col-xs-7 no-padding"><?php echo  $pro_val['ItemName']?> </div>
													<div class="col-xs-5 no-padding text-right"><?php echo $pro_val['ProductsQuantity'].'&nbsp;&nbsp;&nbsp;'.price_fomat($pro_val['TotalPrice']); ?></div>
												</div>
												
									<?php } } 
										if(count($value['Products']) > 2) { ?>
												<div class="text-center col-xs-12 no-padding "><a style="cursor:pointer" id="linkToday<?php echo $key; ?>" onclick="return showAllItems('Today<?php echo $key; ?>');">Show all items</a></div>
									<?php } } ?>	
								</div>							
								<div class="col-xs-12 no-padding"><hr></div>	
										
								<div class="col-xs-8 no-padding"><strong>Total</strong> </div>			
								<div class="col-xs-4 no-padding text-right"><strong><?php echo price_fomat($value['TotalPrice']); ?></strong></div>
								<div class="col-xs-12 no-padding"><hr></div>	
								<div class="col-xs-8 no-padding text-right">
									<?php 
									if($value['OrderStatus'] == 2) {$statusText = 'Rejected'; $class='btn-danger';}else {$statusText = 'Approved'; $class='btn-success';}
									?>
									<a id="submit" class="btn <?php echo $class;?>" title="<?php echo $statusText;?>"><?php echo $statusText;?></a>
								</div>
							</div>
						</div> 				
						<?php } }  else { ?>
							<div class="alert alert-danger alert-dismissable col-xs-10 col-sm-5 col-lg-3" align="center"><i class="fa fa-warning"></i>&nbsp;&nbsp;No orders approved / rejected today.</div>
						<?php } ?>
						<!-- End Today Orders List -->						
					</div><!-- /.box-body -->
				</div>					
		 </div>		
		 <?php } ?>
		</section>
		<?php footerLogin();  commonFooter(); ?>
	<script type="text/javascript">
		$(document).ready(function() {
			$('.fancybox').fancybox();			
			$(".newWindow").fancybox({
				scrolling: 'auto',			
				type: 'iframe',
				width: '800',
				maxWidth: '100%',	
					title: null,			
				fitToView: false
			});	
			$(".productWindow").fancybox({
					width: '800',
					scrolling: 'auto',			
					fitToView: true,
					title: null,
					autoSize: true
			});			
		});
		/*$('.content').click(function(event) {
			$('.alert').hide();
		});*/
	</script>
</html>
