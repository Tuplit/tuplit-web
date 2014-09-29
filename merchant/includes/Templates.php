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
	<link rel="STYLESHEET" type="text/css" href="<?php echo MERCHANT_STYLE_PATH; ?>TimeSelect.css">
	<link rel="icon" href="<?php echo MERCHANT_IMAGE_PATH; ?>favicon.ico" type="image/x-icon" />
	<link rel="shortcut icon" href="<?php echo MERCHANT_IMAGE_PATH; ?>favicon.ico" type="image/x-icon" />
	
	<link rel="STYLESHEET" type="text/css" href="<?php echo MERCHANT_STYLE_PATH; ?>jquery.switch.css">
</head>
<?php } 
   function top_header() {
		$newOrder	=	$showback	=	0;
		$active_class	=	$font_class	=	'';
   		$page 			= getCurrPage();
		if(isset($_GET['st']) && $_GET['st']!='') {
			$page_st = 'st='.$_GET['st'];
		}
		
		// HTML coder
		if(isset($page ) && $page == 'Login')
		$PAGE_TITLE			=	'- Login';
		
		elseif(isset($page ) && $page == 'Signup')
		$PAGE_TITLE			=	'- Sign Up';
		
		
		elseif(isset($page ) && $page == 'ForgotPassword')
		$PAGE_TITLE			=	'- Forgot Password';
		
		elseif(isset($page ) && $page == 'MyStore')
		$PAGE_TITLE			=	'- My Store';
		
		elseif(isset($page ) && $page == 'Settings')
		$PAGE_TITLE			=	'- Settings';
		
		elseif(isset($page ) && $page == 'OrderHistory')
		$PAGE_TITLE			=	'- Order History';
		
		/*elseif(isset($page ) && $page == 'CustomerList')
		$PAGE_TITLE			=	'- Customer List';*/
		
		elseif(isset($page ) && ($page == 'TransactionAnalytics' || $page == 'CustomerList' || $page == 'ProductAnalytics')) {
			$PAGE_TITLE			=	'- Analytics';
			$font_class		=	'logo-xs';
		}
		
		elseif(isset($page ) && $page == 'Dashboard') {
		$PAGE_TITLE			=	'- Dashboard';
		$showback			=	1;
		}
		elseif(isset($page ) && $page == 'ProductList')
		$PAGE_TITLE			=	'- Product List';
		
		elseif(isset($page ) && $page == 'CreateOrder') {
			$newOrder		=	1;
			$PAGE_TITLE		=	'- Create Order';
			$font_class		=	'logo-sm';
		}
		elseif(isset($page ) && $page == 'Orders') {
			$newOrder		=	1;
			$PAGE_TITLE		=	'- Manage Orders';
			$font_class		=	'logo-sm';
		}
		elseif(isset($page ) && $page == 'Myaccount') {
			$active_class	=	'active';
			$PAGE_TITLE		=	'- Settings';
		}
		elseif(isset($page ) && $page == 'TransactionList') {
			$active_class	=	'active';
			$PAGE_TITLE		=	'- Transactions';
		}
		else $PAGE_TITLE	=	'';
		
   ?>
   <header class="header">
		<a  title="Tuplit" href="Dashboard" class="logo <?php echo $font_class;?>">
			<?php echo SITE_TITLE; ?> <?php echo $PAGE_TITLE; ?>
		</a>
		<?php  if(isset($_SESSION['merchantInfo']['AccessToken'])){?> 
		<!-- Header Navbar: style can be found in header.less -->
		<nav class="navbar navbar-static-top" role="navigation">
			
			<div class="navbar-right">
				<ul class="nav navbar-nav">
				   <li><?php //echo "<br>==================>".LAYOUT;  ;?></li>
				   <?php if($showback == 0) { ?><li><a href="Dashboard" class="" title="Back to main menu"><i class="fa fa-chevron-left"></i><span>&nbsp;&nbsp;Back to main menu</span></a></li><?php } ?>
				   <?php if(isset($_SESSION['merchantSubuser']) && !empty($_SESSION['merchantSubuser']) && $_SESSION['merchantSubuser'] == 1) { } else { ?>
				   <li class="user user-menu"><a href="Myaccount" title="Settings" class="<?php echo $active_class;?>" ><i class="fa fa-user"></i><span>&nbsp;&nbsp;Settings</span></a></li>
					<?php } ?>
				  <li><a href="Logout" class="logout" title="Log out"><i class="fa fa-power-off"></i><span>&nbsp;&nbsp;Log out</span></a></li>
				</ul>
			</div>
		</nav>
			<? } ?>
	</header>
	<div class="wrapper row-offcanvas row-offcanvas-left">
	<div class="order-block">
	<?php 
			if(isset($_SESSION['merchantInfo']['AccessToken']) && $newOrder == 0){
				//getting new Order List
				$url					=	WEB_SERVICE.'v1/orders/new';
				$curlCategoryResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
				if(isset($curlCategoryResponse) && is_array($curlCategoryResponse) && $curlCategoryResponse['meta']['code'] == 201 && is_array($curlCategoryResponse['newOrderDetails']) ) {
					if(isset($curlCategoryResponse['newOrderDetails']))
						$newOrderList = $curlCategoryResponse['newOrderDetails'];
				} 
				if(isset($newOrderList) && !empty($newOrderList) && count($newOrderList)) {
				foreach($newOrderList as $key=>$value) { ?>
					<div class="col-md-12 bg">
						<div class="col-md-8 col-lg-5 col-xs-12 m-center no-padding">
							<div class="col-xs-7 col-sm-8 tspace_order">New Order
								 <?php 
								 if($value['OrderDoneBy'] == 2) echo  ' to '; else echo ' from ';
								 echo ucfirst($value['FirstName']).' '.ucfirst($value['LastName']); ?>
							 </div>
							<div class="col-xs-2 text-right tspace_order"><?php echo price_fomat($value['TotalPrice']); ?></div>
							
							<?php //if ($_SERVER['HTTP_HOST'] == '172.21.4.104'){ ?>
									<div class="col-xs-3 col-sm-2"><a href="Orders"><input type="button" name="order" id="order" value="Open" class="btn col-xs-12"></a></div>
							<?php //} else { ?>
									<!-- <div class="col-xs-4"><input type="button" name="order" id="order" value="Open" class="btn"></div> -->
							<?php //} ?>
						</div>	
					</div>
	<?php 	} 	}  	} 	?>
	</div>
		<aside class="right-side">
	
<?php } 
 function commonFooter() { ?>

            </aside><!-- /.right-side -->
        </div><!-- ./wrapper -->
</body>

 	<!-- jquery.validate -->
	<?php if( preg_match('/(iPad)/i', $_SERVER['HTTP_USER_AGENT']) || LAYOUT == 'tablet') { ?>	
		<script src="<?php echo SCRIPT_PATH; ?>jquery.ui.touch-punch.js" type="text/javascript"></script>
	<?php } ?>

 	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>jquery-latest.js" type="text/javascript"></script>
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>Util.js" type="text/javascript"></script>
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>jquery.validate.js" type="text/javascript"></script>
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>Validate.js" type="text/javascript"></script>
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>AjaxDirector.js" type="text/javascript"></script>
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>AjaxFileUpload.js" type="text/javascript"></script>
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>fancybox/jquery.fancybox.js" type="text/javascript"></script>
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>jquery-ui.js" type="text/javascript"></script>
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>jquery.switch.min.js" type="text/javascript"></script>
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>h5utils.js" type="text/javascript"></script> 
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>TimeSelect.js" type="text/javascript"></script> 
	<!-- <script src="<?php echo MERCHANT_SCRIPT_PATH; ?>highcharts.src.js" type="text/javascript"></script> -->
	
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
 function footerLogin() { 
 ?>
	<footer>&copy; <?php echo date('Y');?> Tuplit Inc. <p class=""> 
	<?php // if(!SERVER){ ?>
		<a href="about_tuplit" title="About Tuplit">About Tuplit</a> | 
		<a href="privacy_policy" title="Privacy Policy">Privacy Policy</a>  | 
		<a href="terms_of_service" title="Terms of Service">Terms of Service</a>  | 
		<a href="help_tuplit" title="Help">Help</a>  | 
		<a href="contact_us" title="Contact Us">Contact Us</a> 
	<?php // } ?>
	<?php // if(SERVER){ ?>
		<!--<a href="#ngo" title="About Tuplit">About Tuplit</a> | 
		<a href="#ngo" title="Privacy Policy">Privacy Policy</a>  | 
		<a href="#ngo" title="Terms of Service">Terms of Service</a>  | 
		<a href="#ngo" title="Help">Help</a>  | 
		<a href="#ngo" title="Contact Us">Contact Us</a> -->
	<?php //} ?>
	</p></footer>
 
	
<?php 
	
} 

function AnalyticsTab() { 
		$page 			= getCurrPage();
		if(isset($page ) && $page == 'CustomerList') 
			$c_class		=	'btn-success';
		else if(isset($page ) && $page == 'TransactionAnalytics') 
			$t_class		=	'btn-success';
		else if(isset($page ) && $page == 'ProductAnalytics') 
			$p_class		=	'btn-success';
		else if(isset($page ) && $page == 'TransactionList')
			$l_class		=	'btn-success';
		else
			$c_class = $p_class = $t_class = $l_class = '';?>
		<div class="row">
			<div class="col-xs-12 ">
				<div class="btn-inline space_top">
				<a href="CustomerList?cs=1" title="Customer List" class="col-xs-12 btn  <?php if(isset($c_class) && $c_class != '') echo $c_class; else  echo 'btn-default';?>">Customer Analytics</a>
				</div>
				<div class="btn-inline">
				<a href="ProductAnalytics?cs=1" title="Product Analytics" class="col-xs-12 btn <?php if(isset($p_class) && $p_class != '') echo $p_class; else  echo 'btn-default'; ?>">Product Analytics</a>
				</div>
				<div class="btn-inline">
				<a href="TransactionAnalytics?cs=1" title="Transaction Analytics" class="col-xs-12 btn <?php if(isset($t_class) && $t_class != '') echo $t_class; else  echo 'btn-default'; ?>">Transaction Analytics</a>
				</div>				
			</div>
		</div>
<?php } ?>