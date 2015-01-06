<?php 
	require_once('includes/CommonIncludes.php');
	require_once("includes/mangopay/functions.php");

	$userDetails['AuthorId']			=	'3761410';
 	$userDetails['CreditedUserId']		=	'4163285';
	$userDetails['Currency']			=	'GBP';
	$userDetails['Amount']				=	'56.2';
	$userDetails['FeesAmount']			=	'5';
	$userDetails['DebitedWalletId']		=	'3761411';
	$userDetails['CreditedWalletId']	=	'4163286';
	
	$payment		=	payment($userDetails);
	echo "<pre>"; print_r($payment); echo "</pre>";
?>