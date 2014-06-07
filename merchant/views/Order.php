<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();

//Rejecting order
if(isset($_GET['Reject']) || isset($_GET['Approve'])) {
	if(!empty($_GET['Reject'])) {
		$data	=	array(
					'OrderId'		=> $_GET['Reject'],
					'OrderStatus'	=> '2'
					);
	}
	if(!empty($_GET['Approve'])) {
		$data	=	array(
					'OrderId'		=> $_GET['Reject'],
					'OrderStatus'	=> '1'
					);
	}
	$url	=	WEB_SERVICE.'v1/orders/';
	$method	=	'PUT';
	$curlResponse	=	curlRequest($url,$method,json_encode($data));	
	if(isset($curlResponse) && is_array($curlResponse) && $curlResponse['meta']['code'] == 201) {
		$successMessage = $curlResponse['notifications'][0];		
	} else if(isset($curlResponse['meta']['errorMessage']) && $curlResponse['meta']['errorMessage'] != '') {
		$errorMessage	=	$curlResponse['meta']['errorMessage'];
	} else {
		$errorMessage 	= 	"Bad Request";
	}
}

if(isset($_SESSION['merchantInfo']['AccessToken'])){ 	

	//getting Order List
	$url					=	WEB_SERVICE.'v1/orders/?Type=1';
	$curlCategoryResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlCategoryResponse) && is_array($curlCategoryResponse) && $curlCategoryResponse['meta']['code'] == 201 && is_array($curlCategoryResponse['OrderList']) ) {
		if(isset($curlCategoryResponse['OrderList']))
			$OrderList = $curlCategoryResponse['OrderList'];
	} 
	//echo "<pre>"; echo print_r($OrderList); echo "</pre>";
	if(isset($OrderList) && !empty($OrderList)) {
		foreach($OrderList as $key=>$value){
			if($value['OrderStatus'] == '0')
				$newOrderList[] = $value;
			else {
				if($value['OrderDate'] == date('Y-m-d'))
					$todayOrderList[] 	= $value;
				else
					$oldOrderList[]		= $value;
			}
		}
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

<body class="skin-blue fixed" onload="fieldfocus('ItemName');">
		<?php top_header(); 
		if(isset($msg) && $msg != '') {
		?><br><br> 
		<div align="center" class="alert <?php  echo $class;  ?> alert-dismissable col-xs-5"><i class="fa <?php  echo $class_icon;  ?>"></i>  <?php echo $msg; ?></div>
		<?php } ?>
		<section class="content" align="center">
		<div class="col-md-10" style="margin:auto;float:none" >				
			<section class="content-header">
                <h1>New Orders</h1>
            </section>
			<div class="row product_list">
				<div class="box box-primary no-padding">
					<div class="box-body">
						<!-- Start New Orders List -->						
						<div class="row clear">
							<?php if(isset($newOrderList) && !empty($newOrderList)) {							
									foreach($newOrderList as $key=>$value) {
							?>
								<div class="col-md-2 col-xs-3">
									<div class="small-box " style="height:290px;">									
										<a href="<?php echo $value['Photo']?>" class="fancybox" title="<?php echo $value['FirstName']."&nbsp;".$value['LastName']; ?>">
											<img height="75" width="75" src="<?php echo $value['Photo']?>" alt=""/><br>
										</a>									
										<span><?php echo $value['FirstName']."&nbsp;".$value['LastName']; ?></span>&nbsp;&nbsp;&nbsp;&nbsp;
										<span><?php echo $value['UserId']?></span>&nbsp;&nbsp;&nbsp;&nbsp;
										<span>5seconds</span>&nbsp;&nbsp;&nbsp;&nbsp;<br>
										<div class="col-md-12 no-padding"><hr></div>
										<?php if(!empty($value['Products'])) {											
											foreach($value['Products'] as $pro_val) {
												echo $pro_val['ItemName'].' $'.$pro_val['ProductsCost']."<br>";
											}
										} ?>
										<div class="col-md-12 no-padding"><hr></div>										
										<span style="float:left;">Total</span><span style="float:right;"><?php echo "$".$value['TotalPrice']?></span><br><br>
										<div class="col-md-12 no-padding"><hr></div>
										<span style="float:left;"><a href="Order?Reject=<?php echo  $value['OrderId']; ?>">Reject</a></span>
										<a href="Order?Approve=<?php echo  $value['OrderId']; ?>"><input id="submit" class="btn btn-success" type="Button"  value="Approve" name="Approve" style="float:right;"></a>
									</div>
								</div> 				
							<?php } } else { ?>
								<div class="alert alert-danger alert-dismissable col-xs-5" align="center"><i class="fa fa-warning"></i>&nbsp;&nbsp;No new orders found.</div>
							<?php } ?>
						</div><!-- /row -->
						<div class="col-md-12 no-padding"><hr></div> <!-- sep line -->						
					<!-- End New Orders List -->						
					</div><!-- /.box-body -->
				</div>					
			</div>			
		 </div>
		 <div class="col-md-10" style="margin:auto;float:none" >				
			<section class="content-header">
                <h1>Today Orders</h1>
            </section>
			<div class="row product_list">
				<div class="box box-primary no-padding">
					<div class="box-body">
						<!-- Start Today Orders List -->						
						<div class="row clear">
							<?php if(isset($todayOrderList) && !empty($todayOrderList)) {
									foreach($todayOrderList as $key=>$value) {
							?>
								<div class="col-md-2 col-xs-3">
									<div class="small-box " style="height:220px;">									
										<a href="<?php echo $value['Photo']?>" class="fancybox" title="<?php echo $value['FirstName']."&nbsp;".$value['LastName']; ?>">
											<img height="75" width="75" src="<?php echo $value['Photo']?>" alt=""/><br>
										</a>									
										<span><?php echo $value['FirstName']."&nbsp;".$value['LastName']; ?></span>&nbsp;&nbsp;&nbsp;&nbsp;
										<span><?php echo $value['UserId']?></span>&nbsp;&nbsp;&nbsp;&nbsp;
										<span>5seconds</span>&nbsp;&nbsp;&nbsp;&nbsp;<br>
										<div class="col-md-12 no-padding"><hr></div>
										<?php if(!empty($value['Products'])) {											
											foreach($value['Products'] as $pro_val) {
												echo $pro_val['ItemName'].' $'.$pro_val['ProductsCost']."<br>";
											}
										} ?>									
										<div class="col-md-12 no-padding"><hr></div>										
										<span style="float:left;">Total</span><span style="float:right;"><?php echo "$".$value['TotalPrice']?></span><br><br>
										
									</div>
								</div> 				
							<?php } }  else { ?>
								<br><div class="alert alert-danger alert-dismissable col-xs-5" align="center"><br><i class="fa fa-warning"></i>&nbsp;&nbsp;No orders found.<br><br></div><br>
							<?php } ?>
						</div><!-- /row -->
						<div class="col-md-12 no-padding"><hr></div> <!-- sep line -->						
						<!-- End Today Orders List -->						
					</div><!-- /.box-body -->
				</div>					
			</div>			
		 </div>
		 <div class="col-md-10" style="margin:auto;float:none" >				
			<section class="content-header">
                <h1>Orders</h1>
            </section>
			<div class="row product_list">
				<div class="box box-primary no-padding">
					<div class="box-body">
					<!-- Start Orders List -->						
						<div class="row clear">
							<?php if(isset($oldOrderList) && !empty($oldOrderList)) {
									foreach($oldOrderList as $key=>$value) {
							?>
								<div class="col-md-2 col-xs-3">
									<div class="small-box " style="height:220px;">									
										<a href="<?php echo $value['Photo']?>" class="fancybox" title="<?php echo $value['FirstName']."&nbsp;".$value['LastName']; ?>">
											<img height="75" width="75" src="<?php echo $value['Photo']?>" alt=""/><br>
										</a>									
										<span><?php echo $value['FirstName']."&nbsp;".$value['LastName']; ?></span>&nbsp;&nbsp;&nbsp;&nbsp;
										<span><?php echo $value['UserId']?></span>&nbsp;&nbsp;&nbsp;&nbsp;
										<span>5seconds</span>&nbsp;&nbsp;&nbsp;&nbsp;<br>
										<div class="col-md-12 no-padding"><hr></div>
										<?php if(!empty($value['Products'])) {											
											foreach($value['Products'] as $pro_val) {
												echo $pro_val['ItemName'].' $'.$pro_val['ProductsCost']."<br>";
											}
										} ?>									
										<div class="col-md-12 no-padding"><hr></div>										
										<span style="float:left;">Total</span><span style="float:right;"><?php echo "$".$value['TotalPrice']?></span><br><br>
										
									</div>
								</div> 				
							<?php } }  else { ?>
								<div class="alert alert-danger alert-dismissable col-xs-5" align="center"><br><i class="fa fa-warning"></i>&nbsp;&nbsp;No orders found.<br></div>
							<?php } ?>
						</div><!-- /row -->
						<div class="col-md-12 no-padding"><hr></div> <!-- sep line -->						
					<!-- End Order List -->						
					</div><!-- /.box-body -->
				</div>					
			</div>			
		 </div>
		</section>
		<?php footerLogin();  commonFooter(); ?>
	<script type="text/javascript">
		$(document).ready(function() {
			$('.fancybox').fancybox();			
		});
	</script>
</html>
