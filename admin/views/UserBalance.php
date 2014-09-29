<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
require_once("includes/mangopay/functions.php");
$balance = 0;
if(isset($_GET['walletId']) && $_GET['walletId'] != '' ){
	$walletId		=	$_GET['walletId'];
	$walletDetails	=	getWalletDetails($walletId);
	if(isset($walletDetails)){
		if(isset($walletDetails->Balance)){
			$balance	=	$walletDetails->Balance->Amount;
			$currency	=	$walletDetails->Balance->Currency;
		}
	}
}
commonHead();
?>
<body class="skin-blue">
	<!-- Content Header (Page header) -->
	<section class="content-header no-padding">
		<div class="col-xs-8 col-sm-7">
			<h1><i class="fa fa-search"></i>Balance</h1>
		</div>
	</section>
	 <!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="box box-primary"> 
				<div class="form-group col-xs-12 row">
					<label class="col-xs-6 col-sm-4" >Balance</label>
					<div  class="col-xs-6 col-sm-8">
					<?php if(isset($balance) && $balance != '') echo price_fomat($balance/100); else echo price_fomat(0); ?>	</div>
				</div>	
				<div class="form-group col-xs-12 row">
					<label class="col-xs-6 col-sm-4" >Currency</label>
					<div  class="col-xs-6 col-sm-8">
					<?php if(isset($currency) && $currency != '') echo $currency; else echo ''; ?>	</div>
				</div>	
			</div>		
		</div><!-- /.row -->
	</section><!-- /.content --><!-- /.content -->				  	
<?php commonFooter(); ?>
<script type="text/javascript">	
	$(document).ready(function() {		
		$('.fancybox').fancybox();	
	});	
	
</script>
</html>