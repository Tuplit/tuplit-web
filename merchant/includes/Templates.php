<?php
function commonHead() { 
		$page = getCurrPage();
		if(isset($_GET['st']) && $_GET['st']!='') {
			$page_st = 'st='.$_GET['st'];
		}
		
		// HTML coder		
		if(isset($page ) && $page == 'Login')
		$PAGE_TITLE		=	'- Login';
		
		elseif(isset($page ) && $page == 'Signup')
		$PAGE_TITLE		=	'- Sign Up';
		
		elseif(isset($page ) && $page == 'ForgotPassword')
		$PAGE_TITLE		=	'- Forgot Password';
		
		else $PAGE_TITLE  =	' ';
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title> <?php echo SITE_TITLE; ?> <?php echo $PAGE_TITLE; ?></title>			
	<meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
	<link href="webresources/css/bootstrap.css" rel="stylesheet" type="text/css" />
	<!-- Theme style -->
	<link href="webresources/css/themes.css" rel="stylesheet" type="text/css" />
	<!-- font Awesome -->
	<link href="<?php echo MERCHANT_STYLE_PATH; ?>theme/font-icon.css" rel="stylesheet" type="text/css" />
	<link rel="STYLESHEET" type="text/css" href="<?php echo MERCHANT_STYLE_PATH; ?>jquery.fancybox.css"> 	
	<link rel="STYLESHEET" type="text/css" href="<?php echo MERCHANT_STYLE_PATH; ?>jquery-ui.css"> 
	<link rel="STYLESHEET" type="text/css" href="<?php echo MERCHANT_STYLE_PATH; ?>jquery.ui.theme.css">
	
	
	<link rel="icon" href="<?php echo MERCHANT_IMAGE_PATH; ?>favicon.ico" type="image/x-icon" />
	<link rel="shortcut icon" href="<?php echo MERCHANT_IMAGE_PATH; ?>favicon.ico" type="image/x-icon" />
	
	<link rel="STYLESHEET" type="text/css" href="<?php echo MERCHANT_STYLE_PATH; ?>jquery.switch.css">
</head>
<?php } 
   function top_header() { 
		if(isset($_SESSION['merchantInfo']['AccessToken'])){ 
			//getting new Order List
			$url					=	WEB_SERVICE.'v1/orders/';
			$curlCategoryResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
			if(isset($curlCategoryResponse) && is_array($curlCategoryResponse) && $curlCategoryResponse['meta']['code'] == 201 && is_array($curlCategoryResponse['OrderList']) ) {
				if(isset($curlCategoryResponse['OrderList']))
					$newOrderList = $curlCategoryResponse['OrderList'];
			} 
		}
   		$page = getCurrPage();
		if(isset($_GET['st']) && $_GET['st']!='') {
			$page_st = 'st='.$_GET['st'];
		}
		
		// HTML coder
		if(isset($page ) && $page == 'Login')
		$PAGE_TITLE		=	'- Login';
		
		elseif(isset($page ) && $page == 'Signup')
		$PAGE_TITLE		=	'- Sign Up';
		
		
		elseif(isset($page ) && $page == 'ForgotPassword')
		$PAGE_TITLE		=	'- Forgot Password';
		
		else $PAGE_TITLE		=	' ';
		
		
		$active_class		=	' ';
		
		if(isset($page ) && $page == 'Myaccount')
		$active_class		=	'active';
   ?>
   <header class="header">
		<a  title="Tuplit" href="login" class="logo">
			<?php echo SITE_TITLE; ?> <?php echo $PAGE_TITLE; ?>
		</a>
		<?php  if(isset($_SESSION['merchantInfo']['AccessToken'])){?> 
		<!-- Header Navbar: style can be found in header.less -->
		<nav class="navbar navbar-static-top" role="navigation">
			
			<div class="navbar-right">
				<ul class="nav navbar-nav">
				   <li><a href="Dashboard" class="" title="Back to main menu"><i class="fa fa-chevron-left"></i><span>&nbsp;&nbsp;Back to main menu</span></a></li>
				   <li class="user user-menu"><a href="Myaccount" title="My Account" class="<?php echo $active_class;?>" ><i class="fa fa-user"></i><span>&nbsp;&nbsp;My Account</span></a></li>
				   <li><a href="Logout" class="logout" title="Log out"><i class="fa fa-power-off"></i><span>&nbsp;&nbsp;Log out</span></a></li>
				</ul>
			</div>
		</nav>
			<? } ?>
	</header>
	<div class="wrapper row-offcanvas row-offcanvas-left">
	<div class="order-block">
	<?php  if(isset($_SESSION['merchantInfo']['AccessToken']) && isset($newOrderList)){?> 
	 	
			<?php foreach($newOrderList as $key=>$value) { ?>
			<div  class="col-md-12  bg">
				<div class="col-md-7 col-xs-12 m-center no-padding">
					<div class="col-xs-6">new Order from <?php echo $value['FirstName'].' '.$value['LastName']; ?></div>
					<div class="col-xs-2">$<?php echo $value['TotalPrice']; ?></div>
					<div class="col-xs-4"><input type="button" name="order" id="order" value="Open" class="btn"></div>						
				</div>	
			</div>
			<?php } ?>
		
	<? } ?>
	</div>
		<aside class="right-side">
	
<?php } 
 function commonFooter() { ?>

            </aside><!-- /.right-side -->
        </div><!-- ./wrapper -->
</body>

 	<!-- jquery.validate -->
 	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>jquery-latest.js" type="text/javascript"></script>
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>Util.js" type="text/javascript"></script>
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>jquery.validate.js" type="text/javascript"></script>
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>Validate.js" type="text/javascript"></script>
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>AjaxDirector.js" type="text/javascript"></script>
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>AjaxFileUpload.js" type="text/javascript"></script>
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>fancybox/jquery.fancybox.js" type="text/javascript"></script>
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>jquery-ui.js" type="text/javascript"></script>
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>jquery.switch.min.js" type="text/javascript"></script>
	
	<script type="text/javascript">
		$(window).scroll(function() {    
		    var scroll = $(window).scrollTop();
		
		    if (scroll >= 100) {
		        $(".adm_head").addClass("fixed");
		    }	 else {
        $(".adm_head").removeClass("fixed");
    }
		}); 
	</script>
	
	<!-- <script src="<?php echo MERCHANT_SCRIPT_PATH; ?>theme/app.js" type="text/javascript"></script>
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>theme/dashboard.js" type="text/javascript"></script> -->
	<!-- http://www.designcouch.com/home/why/2013/09/19/ios7-style-pure-css-toggle/ -->
	
<?php } 
 function footerLogin() { ?>
 		<footer>&copy; <?php echo date('Y');?> Tuplit Inc. <p class=""> <a href="#" title="About Tuplit">About Tuplit</a> | <a href="#" title="Privacy Policy">Privacy Policy</a>  | <a href="#" title="Terms of Service">Terms of Service</a>  | <a href="#" title="Help">Help</a>  | <a href="#" title="Contact Us">Contact Us</a> </p></footer>
 
	
<?php } ?>