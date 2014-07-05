<?php 
session_start();
if (!isset($_SESSION['userid'])) {
    die('<div style="color:red;">No payment has been started<div>');
}

if(isset($_POST['submit']) && $_POST['submit'] != '') {
	
	if(isset($_POST['amount'])) {
		header('Location:cardregistration.php?userid='.$_SESSION['userid'].'&amount='.$_POST['amount'].'&currency='.$_POST['currency']);
		die();
	}
	else
		echo 'Unable to create new user. Try again later. ';
	//echo '<pre>===>';print_r($createdUser->Id);echo '<===</pre>';
}

?>
<!DOCTYPE HTML>
<html>
	<header>
		<title>Buy Products using MANGOPAY API</title>
	</header>
	<body>
		<div>
			<div style="color:red;float:right;">Your MangoPay User id: <?php echo $_SESSION['userid']; ?></div><br><br>
			Enter Amount
			 <table border="0" width="870">
            <tr>
                <td style="width: 250px" valign="top"></td>
                <td style="width: 20px"></td>
                <td style="width: 600px; padding: 0px;" valign="top">
					<form name="input" action="" method="post" enctype="multipart/form-data">
						<table>
							<tr>
								<td>Enter Amount to Debit:</td>
								<td>
									<input type="text" required name="amount" placeholder="eg.1500" value=""/>
								</td>
							</tr>
							<tr>
								<td>Enter Currency code:</td>
								<td>
									<input type="text" required placeholder="eg. EUR" name="currency" value=""/>
								</td>
							</tr>
							<tr>
								<td>
								</td>
								<td>
									<input type="submit" name="submit" value="Pay" />
								</td>
							</tr>
						</table>
						<input type="hidden" name="_postback" value="1"/>
				</form>
				</td>
            </tr>
        </table>
		<div style="clear:both;float:left">&nbsp;</div><div style="float:right"><a href="products.php">Shop now</a></div>
		</div>
	</body>
</html>