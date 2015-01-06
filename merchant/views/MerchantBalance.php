<?php
require_once('includes/CommonIncludes.php');
//cookies check
merchant_login_check();
require_once("../admin/includes/mangopay/functions.php");
$balance = 0;
if(isset($_GET['WalletId']) && $_GET['WalletId'] != '' ){
	$walletId					=	base64_decode($_GET['WalletId']);
	$url						=	WEB_SERVICE.'v1/merchants/balance/?WalletId='.$walletId;
	$curlMerchantResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201 && isset($curlMerchantResponse['mangopay']['Id'])) {
		$walletDetails 			= 	$curlMerchantResponse['mangopay'];
	}
	if(isset($walletDetails)){
		if(isset($walletDetails['Balance'])){
			$balance	=	$walletDetails['Balance']['Amount'];
			$currency	=	$walletDetails['Balance']['Currency'];
		}
	}
}
commonHead();
?>
<body class="skin-blue fixed body_height popup_bg" onload="fieldfocus('FirstName');">
	<div class="popup_white">
		<div class="col-xs-12 no-padding" id="mangopay-box">
			<section class="content-header col-xs-12 no-padding">
					<h1 class=" ">Current Balance</h1>
			</section>
				<div class="col-xs-12">
				<div class="form-group  col-xs-9 no-padding mangopay_bal">
					<div  class="current_bal">
					<strong><?php if(isset($balance) && $balance != '') echo price_fomat($balance/100); else echo price_fomat(0); ?></strong>	</div>
				</div>	
				<!-- <div class="form-group  col-xs-12 no-padding">
					<label>Currency</label>
					<div  class="form-control">
					<?php if(isset($currency) && $currency != '') echo $currency; else echo ''; ?>	</div>
				</div> -->
				</div>
		</div>
	</div>
	<?php commonFooter(); ?>
</html>
