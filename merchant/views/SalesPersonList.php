<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();
$salespersonlist	=	array();
$count	=	$salespersoncount	=	0;
//getting salespersons list
$url						=	WEB_SERVICE.'v1/merchants/salesperson/';
$curlSalespersonResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
if(isset($curlSalespersonResponse) && is_array($curlSalespersonResponse) && $curlSalespersonResponse['meta']['code'] == 201 && is_array($curlSalespersonResponse['salespersonlist']) ) {
	if(isset($curlSalespersonResponse['salespersonlist'])){
		$salespersonlist 	= 	$curlSalespersonResponse['salespersonlist'];
		$salespersoncount 	= 	$curlSalespersonResponse['meta']['totalCount'];	
	}
} else if(isset($curlSalespersonResponse['meta']['errorMessage']) && $curlSalespersonResponse['meta']['errorMessage'] != '')
	$errorMessage		=	$curlSalespersonResponse['meta']['errorMessage'];
else
	$errorMessage		= 	"Bad Request";

 if(count($salespersoncount) <= 0)
	$errorsales	=	"No salesPersons found";
if(isset($_GET['delId']) && !empty($_GET['delId'])) {
	$url					=	WEB_SERVICE.'v1/merchants/salesperson/'.$_GET['delId'];
	$curlDeleteResponse 	= 	curlRequest($url, 'DELETE', null, $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlDeleteResponse) && is_array($curlDeleteResponse) && $curlDeleteResponse['meta']['code'] == 201) {
		header("location:SalesPersonList?msg=2");
		die();
	}
	else if(isset($curlDeleteResponse['meta']['errorMessage']) && $curlDeleteResponse['meta']['errorMessage'] != '')
			$errorMessage		=	$curlDeleteResponse['meta']['errorMessage'];
	else
			$errorMessage		= 	"Bad Request";
}
if(isset($_GET['msg'])) {	
	if($_GET['msg'] == 2)
		$successMessage	=	'Salesperson has been deleted successfully';
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

<body class="skin-blue fixed body_height">
<div class="">
	<?php  top_header(); ?>
	<section class="content no-top-padding clear">
		<div class="col-lg-12 box-center">
			<br><br>
			<section class="content-header">
				<div class="col-sm-6 no-padding">
					<h1 class="no-top-margin pull-left">Salesperson List</h1>
				</div>
				<div class="col-sm-6 pull-right no-padding" align="right">
					<a href="<?php echo SITE_PATH; ?>/SalesPerson" class="salesperson"><input type="button" value="Create Salesperson" title="Create Salesperson" class="btn btn-success"></a><br><br>
				</div>
			</section>
			<div class="clear">
				<?php if(isset($msg) && $msg != '') { ?>
					<div align="center" class="alert <?php  echo $class;  ?> alert-dismissable col-xs-12 col-sm-5" style="margin-top:15px;"><i class="fa <?php  echo $class_icon;  ?>"></i>  <?php echo $msg; ?></div>
				<?php }
				if(isset($salespersonlist) && is_array($salespersonlist) && count($salespersonlist) > 0){ ?>
				<div class="col-xs-12 no-padding">
					<div class="box">
						<div class="box-body table-responsive no-padding">
							<table class="table table-hover" width="45%">
							<tr>
								<th align="center" width="3%" class="text-center">#</th>									
								<th width="15%">Name</th>
								<th width="15%">Email</th>
								<th width="10%">DateCreated</th>
								<th width="3%">Action</th>
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
								<td>
								<a class="edit salesperson" title="Edit" href="SalesPerson?editId=<?php echo $value['id']; ?>" ><i class="fa fa-edit "></i></a>
								&nbsp;&nbsp;&nbsp;
								<a class="delete" title="Delete" href="SalesPersonList?delId=<?php echo $value['id']; ?>" onclick="javascript:return confirm('Are you sure to delete?')"><i class="fa fa-trash-o "></i></a>
								</td>
							</tr>
							<?php } //end for ?>	
							</table>										
						</div><!-- /.box-body -->
					</div>					
				</div>
				<?php } ?>	
			</div>
		 </div>
	</section>
</div>
	<?php footerLogin(); ?>
	<?php commonFooter(); ?>
</html>
<script type="text/javascript">
$(document).ready(function() {
	//add salesperson
	$(".salesperson").fancybox({
				scrolling	: 'true',			
				type		: 'iframe',
				width		: '450',
				position	:'fixed',
				maxWidth	: '100%',  // for respossive width set					
				fitToView	: false, 
				afterClose 	: function() {
									//location.reload();
									location.href='SalesPersonList';
									return;
								}
		});
});

</script>
