<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();
$condition	=	'';

if(isset($_SESSION['tuplit_transactionlist_condition']))
	$condition			=	$_SESSION['tuplit_transactionlist_condition'];

//getting transaction list
$url					=	WEB_SERVICE.'v1/merchants/transactionlist/'.$condition;
$curlCustomerResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
if(isset($curlCustomerResponse) && is_array($curlCustomerResponse) && $curlCustomerResponse['meta']['code'] == 201 && is_array($curlCustomerResponse['TransactionsList']) ) {
	if(isset($curlCustomerResponse['TransactionsList'])){
		$TransactionsList 	= $curlCustomerResponse['TransactionsList'];
	}
} else if(isset($curlCustomerResponse['meta']['errorMessage']) && $curlCustomerResponse['meta']['errorMessage'] != '') {
		$errorMessage	=	$curlCustomerResponse['meta']['errorMessage'];
} else {
		$errorMessage	= 	"Bad Request";
} 
commonHead();
?>

<body class="skin-blue fixed popup_bg print_transaction" onload="window.print()">	
	<section class="content">
	<div class="col-lg-10">	
		<div class="row clear" style="padding-top:10px;">
			<div class="col-xs-12 no-padding">
			   <?php if(isset($TransactionsList) && !empty($TransactionsList)) { 
					$TransactionsList = subval_sort($TransactionsList,'Id',1);
			   ?>
				  <div class="box">
				   <div class="box-body table-responsive no-padding no-margin">
					<table class="table table-hover" width="100%" cellpadding="0" cellspacing="0" align="center">
					   <tr>
							<th align="center" class="text-center">#</th>									
							<th>Transaction ID</th>
							<th>Date</th>
							<th>Time</th>
							<th>Customer Name</th>
							<th class="text-right">Amount Debited</th>
							<th class="text-right">Amount Credited</th>
							<th class="text-right">Transaction Fee</th>
							<th class="">Nature</th>
							<th class="">Status</th>
							<th>Status Message</th>
						</tr>
					  <?php $count	=	0;
						foreach($TransactionsList as $key=>$value){	
							$count 	+= 1;
						?>
						<tr>
							<td align="center" class="text-center"><?php echo $count; ?></td>									
							<td><?php echo $value['Id']; ?></td>
							<td><?php echo date("m/d/Y", $value['CreationDate']); ?></td>
							<td><?php echo date("H:i:s", $value['CreationDate']); ?></td>
							<td><?php if(!empty($value['Customer'])) echo $value['Customer']; else echo "Test Transaction"; ?></td>
							<td class="text-right"><?php echo '<b>'.price_fomat($value['DebitedFunds']['Amount']/100)."</b>"; ?></td>
							<td class="text-right"><?php echo '<b>'.price_fomat($value['CreditedFunds']['Amount']/100)."</b>"; ?></td>
							<td class="text-right"><?php echo '<b>'.price_fomat($value['Fees']['Amount']/100)."</b>";//echo $value['DebitedFunds']['Currency'].' '.$value['Fees']['Amount']; ?></td>
							<td class=""><?php echo $value['Nature']; ?></td>
							<td class=""><?php 
								if($value['Status'] == 'SUCCEEDED')
									echo "<b class='text-teal'>".$value['Status']."</b>"; 
								else if($value['Status'] == 'FAILED')
									echo "<b class='text-red'>".$value['Status']."</b>";
								?>
							</td>
							<td><?php echo $value['ResultMessage']; ?></td>
						</tr>
					<?php } //end for ?>	
				   </table>
				</div>
			</div>	
			 <?php } ?>
		</div>	
	 </div>
	 </section>
</html>
