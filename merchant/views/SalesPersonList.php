<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();
$salespersonlist	=	array();
$count	=	0;
//getting salespersons list
$url					=	WEB_SERVICE.'v1/merchants/salesperson/';
$curlSalespersonResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
if(isset($curlSalespersonResponse) && is_array($curlSalespersonResponse) && $curlSalespersonResponse['meta']['code'] == 201 && is_array($curlSalespersonResponse['salespersonlist']) ) {
	if(isset($curlSalespersonResponse['salespersonlist'])){
		$salespersonlist = $curlSalespersonResponse['salespersonlist'];
		$salespersoncount = $curlSalespersonResponse['meta']['totalCount'];	
	}
} else if(isset($curlSalespersonResponse['meta']['errorMessage']) && $curlSalespersonResponse['meta']['errorMessage'] != '') {
		$errorMessage	=	$curlSalespersonResponse['meta']['errorMessage'];
} else {
		$errorMessage	= 	"Bad Request";
}

 if(count($salespersonlist) <= 0) {
	header("location:SalesPerson");
	die();
 }
if(isset($_GET['msg']) && $_GET['msg'] == 1) {
	$successMessage	=	'Salesperson has been created successfully';
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
	<?php  top_header(); ?>
	<section class="content no-top-padding clear">
		<div class="col-lg-10 no-padding box-center">
			<br><br>
			<section class=" content-header">
				<div align="left">
					<h1 class="no-top-margin pull-left">Salesperson List</h1>
				</div>
				<div align="right">
					<a href="SalesPerson"><input type="button" value="Create Salesperson" title="Create Salesperson" class="btn btn-success"></a>
				</div>
			</section>
			<?php if(isset($msg) && $msg != '') { ?>
				<div align="center" class="alert <?php  echo $class;  ?> alert-dismissable col-xs-12 col-sm-5"><i class="fa <?php  echo $class_icon;  ?>"></i>  <?php echo $msg; ?></div>
			<?php } ?>	
			<div class="clear">
				<div class="col-xs-12 no-padding">
					  <div class="box">
						<div class="box-body table-responsive no-padding no-margin">
							<?php if(isset($salespersonlist) && is_array($salespersonlist) && count($salespersonlist) > 0){ ?>
							<table class="table table-hover" width="45%">
							   <tr>
									<th align="center" width="3%" class="text-center">#</th>									
									<th width="15%">Name</th>
									<th width="15%">Email</th>
									<th width="10%">DateCreated</th>
								</tr>
							  <?php
								foreach($salespersonlist as $key=>$value){									
									$count += 1;
								?>
							<tr>
								<td align="center"><?php echo $count; ?></td>
								<td><?php echo $value['Name']; ?></td>
								<td><?php echo $value['Email']; ?></td>
								<td><?php 
										if(isset($value['DateCreated']) && $value['DateCreated'] != '0000-00-00 00:00:00'){
											$gmt_current_start_time = convertIntocheckinGmtSite($value['DateCreated']);
											$DateCreated	=  displayConversationDateTimeForLog($gmt_current_start_time,$_SESSION['tuplit_ses_from_timeZone']);
											echo $DateCreated; 
										}else echo '-';
								?></td>
							</tr>
							<?php } //end for ?>	
						   </table>
						<?php } else { ?>
							<div class="row clear">		
								 <div align="center" class="alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10"><i class="fa fa-warning"></i> <?php echo $errorMessage	;?>	</div>							
							</div>							
						<?php } ?>						
					</div><!-- /.box-body -->
				</div>					
			</div>	
		 </div>
		 </div>
	</section>
	<?php footerLogin(); ?>
	<?php commonFooter(); ?>
</html>
