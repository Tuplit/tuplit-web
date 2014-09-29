<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();

$firstname	=	$lastname	=	$email	=	'';
if(isset($_POST) && !empty($_POST)){
	$firstname	=	$_POST['FirstName'];
	$lastname	=	$_POST['LastName'];
	$email		=	$_POST['Email'];
	$password	=	$_POST['Password'];
	$data	=	array(
					'FirstName' 		=> 	$firstname,
					'LastName' 			=> 	$lastname,
					'Email' 			=> 	$email,
					'Password' 			=> 	$password
				);
	$url					=	WEB_SERVICE.'v1/merchants/salesperson/';
	$method					=	'POST';
	$curlResponse			=	curlRequest($url,$method,$data,$_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlResponse) && is_array($curlResponse) && $curlResponse['meta']['code'] == 201) {
		$firstname	=	$lastname	=	$email	=	'';
		$successMessage	=	$curlResponse['notifications'][0];
		header("location:SalesPersonList?msg=1");
		die();
	} else if(isset($curlResponse['meta']['errorMessage']) && $curlResponse['meta']['errorMessage'] != '')
		$errorMessage	=	$curlResponse['meta']['errorMessage'];
	else
		$errorMessage 	= 	"Bad Request";
	
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

<body class="skin-blue fixed" onload="fieldfocus('FirstName');">
		<?php top_header(); ?>
		<section class="content">
		<div class="col-lg-8 col-md-11 box-center">
			<section class="content-header">
                <h1>Create Salesperson</h1>
            </section>
			<?php if(isset($msg) && $msg != '') { ?>
				<div align="center" class="alert <?php  echo $class;  ?> alert-dismissable col-xs-12 col-sm-5"><i class="fa <?php  echo $class_icon;  ?>"></i>  <?php echo $msg; ?></div>
			<?php } ?>
			<form action="" name="subuser_form" id="subuser_form"  method="post">
				<div class="row clear">
					<div class="col-sm-12 col-md-12">					
					<div class="box box-primary no-padding">
						<div class="box-header no-padding">
							<h3 class="box-title"></h3>
						</div>						
						<div class="form-group col-xs-12 col-md-12 clearfix no-padding">
							<div class="col-sm-4  col-lg-3 col-xs-12  no-padding LH30"><strong><span class="rowshow col-sm-12">First Name</span></strong></div>
							<div class="col-xs-6 col-sm-4 col-xs-6 no-padding LH30" style="width:51%;"><input type="text"  id="FirstName" name="FirstName" value="<?php echo $firstname; ?>" placeholder="" class="form-control"></div>	
						</div>
						<div class="form-group col-xs-12 col-md-12 clearfix no-padding">
							<div class="col-sm-4  col-lg-3 col-xs-12  no-padding LH30"><strong><span class="rowshow col-sm-12">Last Name</span></strong></div>
							<div class="col-xs-6 col-sm-4 col-xs-6 no-padding LH30" style="width:51%;"><input type="text"  id="LastName" name="LastName" value="<?php echo $lastname; ?>" placeholder="" class="form-control"></div>	
						</div>
						<div class="form-group col-xs-12 col-md-12 clearfix no-padding">
							<div class="col-sm-4  col-lg-3 col-xs-12  no-padding LH30"><strong><span class="rowshow col-sm-12">Email</span></strong></div>
							<div class="col-xs-6 col-sm-4 col-xs-6 no-padding LH30" style="width:51%;"><input type="text"  id="Email" name="Email" value="<?php echo $email; ?>" placeholder="" class="form-control"></div>	
						</div>
						<div class="form-group col-xs-12 col-md-12 clearfix no-padding">
							<div class="col-sm-4  col-lg-3 col-xs-12  no-padding LH30"><strong><span class="rowshow col-sm-12">Password</span></strong></div>
							<div class="col-xs-6 col-sm-4 col-xs-6 no-padding LH30" style="width:51%;"><input type="password"  id="Password" name="Password" value="" placeholder="" class="form-control"></div>	
						</div>
						<div class="form-group col-xs-12 col-md-12 clearfix no-padding">
							<div class="col-sm-4  col-lg-3 col-xs-12  no-padding LH30"><strong><span class="rowshow col-sm-12">Confirm Password</span></strong></div>
							<div class="col-xs-6 col-sm-4 col-xs-6 no-padding LH30" style="width:51%;"><input type="password"  id="C_Password" name="C_Password" value="" placeholder="" class="form-control"></div>	
						</div>
					</div>				
				</div>
				<div class="footer col-xs-12 " align="center"> 
						<input type="submit" name="subuser_submit" id="subuser_submit" value="Create Salesperson" title="Create Salesperson" class="btn btn-success col-xs-12 col-sm-5 box-center">
				</div>				
			</form>
		 </div>
		</section>
		<?php footerLogin(); ?>
	<?php commonFooter(); ?>
</html>
<script type="text/javascript">

</script>
