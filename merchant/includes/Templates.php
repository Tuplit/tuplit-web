<?php
error_reporting(E_ALL);

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
		$url = MERCHANT_IMAGE_PATH."InnerPages_Bg.jpg";
		
		if($page{0} === strtoupper($page{0})) { 
			$_SESSION['tuplit_check_pin'] = 1;
		} else {
			$_SESSION['tuplit_check_pin'] = 0;
		}
		//echo "===========>".$_SESSION['tuplit_check_pin'];

		
		if(isset($_SESSION['merchantDetailsInfo']['Background']) && !empty($_SESSION['merchantDetailsInfo']['Background'])){
			$url = $_SESSION['merchantDetailsInfo']['Background'];
		}	
		//echo'<pre>';print_r($_GET);echo'</pre>';
		if((isset($_GET['reset']) && $_GET['reset'] != '1') &&  isset($_SESSION['MerchantPortalAskPin']) && $_SESSION['MerchantPortalAskPin'] == 1){
			die();
		}
?>
<!DOCTYPE html>
<html class=""><!-- mm-opened mm-background mm-opening -->
<head>
	<meta charset="UTF-8">
	<title> <?php echo SITE_TITLE; ?> <?php echo $PAGE_TITLE; ?></title>			
	<meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
	<link rel="icon" href="<?php echo MERCHANT_IMAGE_PATH; ?>favicon.ico" type="image/x-icon" />
	<link rel="shortcut icon" href="<?php echo MERCHANT_IMAGE_PATH; ?>favicon.ico" type="image/x-icon" />
	
	<link href="webresources/css/bootstrap.css" rel="stylesheet" type="text/css" />
	
	<?php 
	if(isset($page ) && ($page == 'Login' || $page == 'ForgotPassword' || $page == 'Signup' || $page == 'privacy_policy' || $page == 'about_tuplit' || $page == 'terms_of_service' || $page == 'help_tuplit' || $page == 'help' || $page == 'contact_us')) { ?>
	<link rel="STYLESHEET" type="text/css" href="<?php echo MERCHANT_STYLE_PATH; ?>login.css">
	<? } else { ?>
	
	
	
	<!-- Theme style -->
	<link href="webresources/css/themes.css" rel="stylesheet" type="text/css" />
	<!-- font Awesome -->
	<link href="<?php echo MERCHANT_STYLE_PATH; ?>theme/font-icon.css" rel="stylesheet" type="text/css" />
	<link rel="STYLESHEET" type="text/css" href="<?php echo MERCHANT_STYLE_PATH; ?>jquery.fancybox.css"> 	
	<link rel="STYLESHEET" type="text/css" href="<?php echo MERCHANT_STYLE_PATH; ?>jquery-ui.css"> 
	<!-- <link rel="STYLESHEET" type="text/css" href="<?php // echo MERCHANT_STYLE_PATH; ?>jquery.ui.theme.css"> -->
	<!-- <link rel="STYLESHEET" type="text/css" href="<?php echo MERCHANT_STYLE_PATH; ?>jquery.switch.css">-->
	<link rel="STYLESHEET" type="text/css" href="<?php echo MERCHANT_STYLE_PATH; ?>menu.css">
	<!-- <link rel="STYLESHEET" type="text/css" href="<?php // echo MERCHANT_STYLE_PATH; ?>jquery.mmenu.dragopen.css"> -->
	<!-- <link rel="STYLESHEET" type="text/css" href="<?php // echo MERCHANT_STYLE_PATH; ?>jquery.mmenu.css"> -->
	<link rel="STYLESHEET" type="text/css" href="<?php echo MERCHANT_STYLE_PATH; ?>popup.css">
	
	<?php if(isset($page) && $page == 'CreateOrder'){ ?>
		<link rel="STYLESHEET" type="text/css" href="<?php echo MERCHANT_STYLE_PATH; ?>jcarousel.responsive.css">
	<?php } ?>
	
	<!-- Manage Orders-->
	<?php if(isset($page) && ($page == 'Orders' || $page == 'UserOrders')){
		if($page == 'UserOrders')
			$_SESSION['MerchantPortalAskPin'] =0;
	?>
		<link rel="STYLESHEET" type="text/css" href="<?php echo MERCHANT_STYLE_PATH; ?>jquery.scrollbar.css">
	<?php } ?>
	
	<? } ?>
	<link rel="STYLESHEET" type="text/css" href="<?php echo MERCHANT_STYLE_PATH; ?>TimeSelect.css">
	<?php if(isset($page) && $page != 'PrintOrder'){ ?>
	<style type="text/css">		
		body {
			background-image : url(<?php echo  $url; ?>) !important;
			background-repeat : no-repeat;
		}
		.fa fa-spinner fa-spin fa-lg
		.fa-spin{display: none;}
		.fancybox-loading,#fancybox-loading div{display: none;}
		#fancybox-loading{display: none;}
		.mercha-loader{
			 background-color: #000;
			left: 50%;
		    margin-left: -22px;
		    margin-top: -22px;
		    position: fixed;
		    text-align: center;
		    top: 50%;
		    z-index: 1000;
			padding: 15px;
			border-radius: 5px
		}
		.loader-merchant{    
			background-color: rgba(0, 0, 0, 0.5);
			left: 0;
		    position: fixed;
		    top: 0;
		    z-index: 1000;
			display: none;
			width: 100%;
			height: 100%;
			font-size:15px;
			color:#ccc;
		
		}

</style>
	<?php } ?>
</head>
<?php } 
function popup_head(){ 
$_SESSION['MerchantPortalAskPin'] =0;
?>
	<!DOCTYPE html>
<html>
<head>
	<link href="webresources/css/themes.css" rel="stylesheet" type="text/css" />
	<link href="webresources/css/bootstrap.css" rel="stylesheet" type="text/css" />
	<link rel="STYLESHEET" type="text/css" href="webresources/css/popup.css">
	<link href="<?php echo MERCHANT_STYLE_PATH; ?>theme/font-icon.css" rel="stylesheet" type="text/css" />
</head>
<?php } 

   function top_header() {
		$newOrder	=	$showback	=	0;
		$active_class	=	$font_class	= $sub_class	= '';
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
		
		elseif(isset($page ) && ($page == 'TopSales' || $page == 'CustomerList' || $page == 'TopOrders')) {
			$PAGE_TITLE		=	'- Analytics';
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
		/*elseif(isset($page ) && $page == 'Myaccount') {
			$active_class	=	'active';
			$PAGE_TITLE		=	'- Settings';
		}*/
		elseif(isset($page ) && $page == 'TransactionList') {
			$active_class	=	'active';
			$PAGE_TITLE		=	'- Transactions';
		}
		elseif(isset($page ) && $page == 'SalesPersonList') {
			$active_class	=	'active';
			$PAGE_TITLE		=	'- Salesperson List';
		}
		elseif(isset($page ) && $page == 'SalesPerson') {
			$active_class	=	'active';
			$PAGE_TITLE		=	'- Create SalesPerson';
		}
		else $PAGE_TITLE	=	'';
   ?>
   
   
	<!-- Start : Left Menu -->   
	<div class="loader-merchant"><!--loader-->
		<div class="mercha-loader">
			<i class="fa fa-spinner fa-spin fa-lg"></i>
		</div>
	</div>
	<?php  if(isset($_SESSION['merchantInfo']['AccessToken'])){ ?> 
	<?php
	$subuser		=	0;	
	if(isset($_SESSION['merchantSubuser']) && !empty($_SESSION['merchantSubuser']) && $_SESSION['merchantSubuser'] == 1) { 
		$subuser	=	1;
	} else { ?>
			<!-- <li class="user user-menu"><a href="Myaccount" title="Settings" class="<?php echo $active_class;?>" ><i class="fa msitting"></i><span>&nbsp;&nbsp;Settings</span></a></li> -->
	<?php } ?>
	<nav id="#navbar" class="navbar navbar-static-top mm-menu mm-horizontal mm-offcanvas " role="navigation"><!-- mm-current mm-opened -->
		
		<ul class="clear mm-list mm-panel mm-opened mm-current">
			<li style="height:171px;background:#01beae;width:210px;text-align:center;display:table-cell;vertical-align:middle;padding-top:24px;">
				<a href="MyStore" class="no-padding no-margin"><img src="<?php if(isset($_SESSION['merchantDetailsInfo']['Icon']) && !empty($_SESSION['merchantDetailsInfo']['Icon'])) echo $_SESSION['merchantDetailsInfo']['Icon']; else echo MERCHANT_IMAGE_PATH."no_user.jpeg"; ?>" width="86" height="86" alt="" style="-webkit-border-radius: 43px; -moz-border-radius: 43px; -khtml-border-radius: 43px;border-radius: 43px;"></a><br>
				<a href="MyStore" class="no-padding marginb20"><span style="font-size:20px;color:#fff;padding-top:0px;margin-top:10px;">
					<?php 
						if(isset($_SESSION['merchantDetailsInfo']['FirstName'])) 
							echo $_SESSION['merchantDetailsInfo']['FirstName'];
						if(isset($_SESSION['merchantDetailsInfo']['LastName'])) 
							echo ' '.$_SESSION['merchantDetailsInfo']['LastName'];
					?>
				</span></a>
			</li>
			<li style="padding-bottom:18px;margin-bottom:22px;"><a href="CreateOrder?cs=1" title="Create Order"><b class="fa mplus"></b> Create Order</a></li>
			<li style="padding-bottom:18px;margin-bottom:22px;"><a href="Orders?cs=1" title="Manage Orders"><b class="fa morder"></b> Manage Orders</a></li>
			<?php if($subuser != 1) { ?>
			<li class="menu_active" id="left_analytics" style="padding-bottom:36px;"><a class="menu-analytics" href="#" title="Analytics"><b class="fa mnalytics"></b> Analytics</a> <!-- menu_active -->
				<ul class="treeview-menu">
					<li id="customer_analytics"><a href="CustomerAnalyticsOverview?cs=1" title="Customer Analytics" ><b class="fa custanalytics"></b> Customer</a></li>
					<li id="product_analytics"><a href="ProductAnalytics?cs=1" title="Product Analytics" ><b class="fa prodanalytics"></b> Product</a></li>
					<li id="transaction_analytics"><a href="TransactionOverview?cs=1" title="Transaction Analytics"><b class="fa transanalytics"></b> Transaction</a></li>
				</ul>
			</li>
			<?php } ?>
			<li style="padding-bottom:18px;margin-bottom:22px;"><a href="ProductList" title="Products"><b class="fa mproduct"></b> Products</a></li>
			<?php if($subuser != 1) { ?>
			<li style="padding-bottom:18px;margin-bottom:22px;"><a href="MyStore" title="My Store"><b class="fa mshop"></b> My Store</a></li>
			<?php } ?>
			<li style="padding-bottom:18px;margin-bottom:22px;"><a href="CustomerTransaction?cs=1" title="Transactions"><b class="fa mtrans"></b> Transactions </a></li>

			<!-- <li><a href="SalesPersonList" title=""><b class="fa msalespers"></b> Sales Person</a></li> -->
			
			
			<li style="padding-bottom:18px;margin-bottom:22px;"><a href="Logout" class="logout" title="Log out"><i class="fa mlogout"></i><span>Logout</span></a></li>
		</ul>	
	</nav>

	<?php } ?>
	<!-- End : Left Menu -->   
   <div class="mm-page" style="">
   <div class="page-wrap"></div>
   <div class="header FixedTop">
		
		<?php  if(isset($_SESSION['merchantInfo']['AccessToken'])){ 
			$twohour	=	$other	=	0;
			//getting new Order List
			$url					=	WEB_SERVICE.'v1/orders/new';
			$curlCategoryResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
			if(isset($curlCategoryResponse) && is_array($curlCategoryResponse) && $curlCategoryResponse['meta']['code'] == 201) {
				$twohour = $curlCategoryResponse['meta']['TwoHourCount'];
				$other 	= $curlCategoryResponse['meta']['OtherCount'];
			}			
		?> 
		<!-- Header Navbar: style can be found in header.less -->
		<div style="">
			<div class="menu_icon">
				<a href="#menu" class="navbar-btn sidebar-toggle" data-toggle="offcanvas" role="button" title="menu">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </a>
				<a href="Dashboard"  title="<?php echo SITE_TITLE; ?>" href="Dashboard" class="logo"><img src="webresources/images/tuplit_logo_in.png" width="52" height="52" alt=""></a>
			</div>
		</div>
		<?php if($twohour > 0 || $other > 0) { ?>
			<div class="menu_header">
			<div class="menu_header_msg">
				<?php if($twohour > 0) { ?>
					<p>You've got <?php echo  $twohour; ?> new incoming <?php if($twohour == 1) echo "order"; else echo "orders"; ?>!</p>
				<?php } if($other > 0 && $twohour <= 0) { ?>
					<p onclick="location.href='Orders?cs=1'">You've got <?php if($other == 1) echo $other." order"; else echo $other." orders"; ?> pending!</p>
				<?php } else if($other > 0){?>
					<a href="Orders?cs=1" title="Orders pending"><?php if($other == 1) echo $other." order"; else echo $other." orders"; ?> pending.</a>
				<?php } ?>
			</div>
		<? } } ?>
		</div>
	</div>
	<div class="wrapper row-offcanvas row-offcanvas-left">
		<!-- <aside class="left-side sidebar-offcanvas collapse-left">
                <!-- sidebar: style can be found in sidebar.less 
                <section class="sidebar">
                    <!-- sidebar menu: : style can be found in sidebar.less -->
                <!--     <ul class="sidebar-menu">
                     	<li class="treeview active" > <a href="" title=""><b class="fa "></b> Create Order</a></li>
						<li class="treeview"><a href="" title=""><b class="fa "></b> Manage Orders</a></li>
						<li class="treeview"><a href="" title=""><b class="fa "></b> Analytics</a></li>
						<li class="treeview"><a href="" title=""><b class="fa "></b> Products</a></li>
						<li class="treeview"><a href="" title=""><b class="fa "></b> My Shop</a></li>
						<li class="treeview"><a href="" title=""><b class="fa "></b> Settings</a></li>
						<li class="treeview"><a href="" title=""><b class="fa "></b> Logout</a></li>
                    </ul>
                </section>
                <!-- /.sidebar -->
           <!--   </aside> -->
	<div class="order-block" style="display:none;">
	<?php 
			if(isset($_SESSION['merchantInfo']['AccessToken'])){
				//getting new Order List
				$url					=	WEB_SERVICE.'v1/orders/new';
				$curlCategoryResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
				if(isset($curlCategoryResponse) && is_array($curlCategoryResponse) && $curlCategoryResponse['meta']['code'] == 201 && isset($curlCategoryResponse['newOrderDetails']) ) {
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
 function commonFooter() { 
	$page 			= getCurrPage();	
 ?>
	
	<!-- Ask pin Popup -->
	<body >
		<div id="AskPin" style="display:none;">
			<div class="AskPin  no-margin">
				<div class="col-xs-12" id="secondDiv" >
					<div class="col-xs-12 popup_title text-center"><h2>Verify PIN</h2></div>
				</div>
				<div class="col-xs-12 AskPin_error" style="display:none"><div align="center" id="pin_error" class="pin_error alert alert-danger no-margin" ></div></div>
				<div class="form-group col-xs-12 pin_label">
					<div class="col-xs-12 no-padding"><h5>Enter PIN code</h5></div>
					<input type="hidden" name="Pincode_box" id="Pincode_box" value=""/>
					<div class="screen col-xs-12"  align="center">
						<div class="screen-inner" id="screen"></div>
					</div> 
				</div>
				<div class="col-xs-12 no-padding">
					<div class="keys" align="center" id="pin_keys">
						<span id="1" onclick="addPincode(1)">1</span>
						<span id="2" onclick="addPincode(2)">2</span>
						<span id="3" onclick="addPincode(3)">3</span></br>
						<span id="4" onclick="addPincode(4)">4</span>
						<span id="5" onclick="addPincode(5)">5</span>
						<span id="6" onclick="addPincode(6)">6</span></br>
						<span id="7" onclick="addPincode(7)">7</span>
						<span id="8" onclick="addPincode(8)">8</span>
						<span id="9" onclick="addPincode(9)">9</span></br>
						<span id="0" onclick="addPincode(0)">0</span>
					</div>
					<span onclick="clearPin()" style="font-size:20px;" class="btn btn-primary top-margin15 col-xs-6">Clear</span>
					<span onclick="location.href='MyStore?cs=1&reset=1'" style="font-size:20px;" class="btn btn-primary top-margin15 col-xs-6">Reset</span>
				</div>
			</div>			
		</div>
	</body>
	<!-- Ask pin Popup -->
	<input type="hidden"  name="tuplit_merchant_lastaccess" id="tuplit_merchant_lastaccess" value="<?php if(isset($_SESSION['MerchantPortalAccessTime']) && !empty($_SESSION['MerchantPortalAccessTime'])) echo $_SESSION['MerchantPortalAccessTime']; ?>">
	<input type="hidden" name="tuplit_merchant_autolock" id="tuplit_merchant_autolock" value="<?php if(isset($_SESSION['tuplit_merchant_autolock']) && !empty($_SESSION['tuplit_merchant_autolock'])) echo $_SESSION['tuplit_merchant_autolock']; ?>">
	<input type="hidden" name="tuplit_merchant_jsaccess" id="tuplit_merchant_jsaccess" value="<?php if(isset($_SESSION['MerchantPortalAccessTime']) && !empty($_SESSION['MerchantPortalAccessTime'])) echo $_SESSION['MerchantPortalAccessTime']; ?>">
            </aside><!-- /.right-side -->
        </div><!-- ./wrapper -->
		<div class="body_bg_color"></div>
</body>

 	<!-- jquery.validate -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script> 

	 	<!--  <script src="<?php echo MERCHANT_SCRIPT_PATH; ?>jquery-latest.js" type="text/javascript"></script> -->
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>Util.js" type="text/javascript"></script>
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>jquery.validate.js" type="text/javascript"></script>
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>Validate.js" type="text/javascript"></script>
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>AjaxDirector.js" type="text/javascript"></script>
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>AjaxFileUpload.js" type="text/javascript"></script>
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>fancybox/jquery.fancybox.js" type="text/javascript"></script>
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>jquery-ui.js" type="text/javascript"></script>
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>jquery.mmenu.min.js" type="text/javascript"></script>
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>jquery.mmenu.dragopen.min.js" type="text/javascript"></script>
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>jquery.mmenu.fixedelements.min.js" type="text/javascript"></script>
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>h5utils.js" type="text/javascript"></script> 
	<?php if($page == 'MyStore') { ?>
		<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>GeoLocation.js" type="text/javascript"></script>
		<script type="text/javascript" src="https://maps.google.com/maps/api/js?sensor=false"></script>
		<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>TimeSelect.js" type="text/javascript"></script> 
	<?php } ?>		 
	<?php if($page == 'TransactionOverview') { ?>
		<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>TimeSelect.js" type="text/javascript"></script> 
	<?php } ?>		
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>jquery.ui.touch-punch.js" type="text/javascript"></script> 
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>jquery.shapeshift.js"></script>
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>jQuery.print.js" type="text/javascript"></script>
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>bootstrap.min.js" type="text/javascript"></script>
	
	<!-- Manage Orders-->
	<?php if(isset($page) && ($page == 'Orders' || $page == 'UserOrders')){ ?>
		<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>jquery.scrollbar.js" type="text/javascript"></script>
	<?php } ?>
	
	<script type="text/javascript">
		/*$('body').click(function(){
			if($('.navbar').hasClass('mm-current mm-opened')){
				$('html').removeClass('mm-opened mm-background mm-opening');
				$('.navbar').removeClass('mm-current mm-opened');
				//$('.mm-page').css('min-height','');
			}
		});*/
		//$("body").click(function(e) {
		$("body").on("click", function(e){ 
			// :not(#navbar, #navbar *)
			//
			//alert('---------------------'+e.target.className);
			 if (e.target.className == "navbar-btn" || e.target.className == "navbar-btn sidebar-toggle"  || e.target.className == "icon-bar" ) {
				if($('.navbar').hasClass('mm-current mm-opened')){
					$('html').removeClass('mm-opened mm-background mm-opening');
					$('.navbar').removeClass('mm-current mm-opened');
				}
				else{
					$('html').addClass('mm-opened mm-background mm-opening');
					$('.navbar').addClass('mm-current mm-opened');
				}
				
    		}
			else{
				if(e.target.className == "menu-analytics"){
					$('html').addClass('mm-opened mm-background mm-opening');
					$('.navbar').addClass('mm-current mm-opened');
					$('.treeview-menu').show();
					if($('#left_analytics').hasClass('opened')){
						$('.treeview-menu').hide();
						$('#left_analytics').removeClass('opened');
					}
					else{
						$('#left_analytics').addClass('opened');
						
					}
				}
				else { 
					
				}
			}
		});

		$(window).scroll(function() {    
			var scroll = $(window).scrollTop();
			if (scroll >= 100) {
				$(".adm_head").addClass("fixed");
			}	 else {
				$(".adm_head").removeClass("fixed");
			}
		});	
		
		
		function slideSwitch() {
   		 	var $active = $('#slideshow div.active');
		    if ( $active.length == 0 ) $active = $('#slideshow div:last');
 
    		// use this to pull the images in the order they appear in the markup
  			var $next =  $active.next().length ? $active.next()
       		: $('#slideshow div:first');
 
			    // uncomment the 3 lines below to pull the images in random order
			 
			    // var $sibs  = $active.siblings();
			    // var rndNum = Math.floor(Math.random() * $sibs.length );
			    // var $next  = $( $sibs[ rndNum ] );
 
    		$active.addClass('last-active');
 
    		$next.css({opacity: 0.0})
       		 .addClass('active')
       		 .animate({opacity: 1.0}, 1000, function() {
           		 $active.removeClass('active last-active');
       		 });
		}
 
		$(function() {    
				setInterval( "slideSwitch()", 5000 ); 
				
				// SLIDE MENU ::
				$('nav#menu').mmenu();
				/*var $menu = $('nav#menu'),
					$html = $('html, body');
				$menu.mmenu({
					dragOpen: true
				});*/
		});
		
		/* Super Simple Fancy Checkbox by davemacaulay.com updated by schoberg.net  */
		(function( $ ) {
			$.fn.simpleCheckbox = function(options) {
				var defaults = {
					newElementClass: 'tog',
					activeElementClass: 'on'
				  };
				var options = $.extend(defaults, options);
				this.each(function() {
					//Assign the current checkbox to obj
					var obj = $(this);
					//Create new span element to be styled
					var newObj = $('<div/>', {
						'id': '#' + obj.attr('id'),
						'class': options.newElementClass,
						'style': 'display: block;'
					}).insertAfter(this);
					//Make sure pre-checked boxes are rendered as checked
					if(obj.is(':checked')) {
						newObj.addClass(options.activeElementClass);
					}
					obj.hide(); //Hide original checkbox
					//Labels can be painful, let's fix that
					if($('[for=' + obj.attr('id') + ']').length) {

						var label = $('[for=' + obj.attr('id') + ']');
						label.click(function() {
							newObj.trigger('click'); //Force the label to fire our element
							return false;
						});
					}
					//Attach a click handler
					newObj.click(function() {
						//Assign current clicked object
						var obj = $(this);
						//Check the current state of the checkbox
						if(obj.hasClass(options.activeElementClass)) {
							obj.removeClass(options.activeElementClass);
							$(obj.attr('id')).attr('checked',false);
						} else {
							obj.addClass(options.activeElementClass);
							$(obj.attr('id')).attr('checked',true);
						}
						//Kill the click function
						return false; 
					});
				});
			};
		})(jQuery);

		$(document).ready(function(){
			$('#Discount').simpleCheckbox();
			$('#EmailNotification').simpleCheckbox();
			<?php if(isset($_SESSION['merchantDetailsInfo']) && !empty($_SESSION['merchantDetailsInfo']['AutoLock']) && !empty($_SESSION['merchantDetailsInfo']['Pincode'])) { if(isset($_SESSION['MerchantPortalAskPin']) && isset($_SESSION['MerchantPortalAccessTime'])){ ?>
				$(".header").click(function(){
				  updatePin(1);
				  return true;
				});
				$(".wrapper").click(function(){
				  updatePin(1);
				  return true;
				});
				$(".page-wrap").click(function(){
				  updatePin(1);
				  return true;
				});
				$(".popup_white").click(function(){
				  updatePin(2);
				  return true;
				});
			<?php } if(isset($_SESSION['tuplit_check_pin']) && $_SESSION['tuplit_check_pin'] == 1) {  ?>
				$(".site-footer").click(function(){
				  updatePin(2);
				  return true;
				});				
			<?php } if(isset($_SESSION['MerchantPortalAskPin']) && $_SESSION['MerchantPortalAskPin'] == 1 && isset($_SESSION['tuplit_check_pin']) && $_SESSION['tuplit_check_pin'] == 1) {  ?>
					var acceptedContent	 =   $('#AskPin').html(); 
					$.fancybox({
						content		: acceptedContent, 
						width		: '280',
						height		: 'auto',
						autoSize	: false,
						type		: 'iframe',
						closeBtn	: false,
						closeClick  : false,
						helpers 	: { 
										overlay : {closeClick: false}
										},
						keys 		: {
										close  : null
										}
					});										
			<?php } } ?>
			/*// Get all the keys from document
			var keys = document.querySelectorAll('#AskPin span');
			console.log(keys);
			
			for(var i = 0; i < keys.length; i++) {
				//var a = document.getElementById(keys[i].id);
				$('#pin_1').attr('onclick',addPincode);
				/*a.onclick = function(e) {
					alert(keys[0].id)
					// Get the input and button values
					var input = document.querySelector('.screen');
					var inputVal = input.innerHTML;
					var btnVal = this.innerHTML;
					e.preventDefault();
				} 
			}*/
			
			//scroll top arrow
			//Check to see if the window is top if not then display button
			$(window).scroll(function(){
				if ($(this).scrollTop() > 100) {
					$('.scrollToTop').fadeIn();
				} else {
					$('.scrollToTop').fadeOut();
				}
			});
			
			//Click event to scroll to top
			$('.scrollToTop').click(function(){
				$('html, body').animate({scrollTop : 0},800);
				return false;
			});	
		});// end document.ready
		
</script>
	
	<?php if(isset($page) && $page == 'CreateOrder'){ ?>
		<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>jquery.jcarousel.min.js" type="text/javascript"></script>
		<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>jcarousel.responsive.js" type="text/javascript"></script> 
	<?php } ?>
<?php } 
 function footerLogin() { 
 ?>
 	</div><!-- /page-wrap -->
	<div class="site-footer">
		<p class=""> 
		<a href="about_tuplit" title="About Tuplit">About Tuplit</a>
		<a href="privacy_policy" title="Privacy Policy">Privacy Policy</a>
		<a href="terms_of_service" title="Terms of Service">Terms of Service</a>
		<a href="help" title="Help">Help</a>
		<a href="contact_us" title="Contact Us">Contact Us</a>
		</p>
	
		<p>&copy; <?php echo date('Y');?> Tuplit Inc. </p>
	<a class="page_arrow scrollToTop" style="display:none;" href="#">
		<i class="fa fa-angle-up fa-6"></i>
	</a>

	</div>
	</div>
	<script type="text/javascript">
		var loaded = false;
		function SetLoaded() { 
		loaded = true; 
		$('html').removeClass('mm-opened mm-background mm-opening');
		$('.navbar').removeClass('mm-current mm-opened');
		}
		window.onload = SetLoaded;
	</script>
	
	<!--<nav id="menu">
		<ul>
			<li><a href="#content">Introduction</a></li>
			<li><a href="#first">First section</a></li>
			<li><a href="#second">Second section</a></li>
			<li><a href="#third">Third section</a></li>
		</ul>
	</nav> -->
	
		
<?php 
	
} 
function AnalyticsTab() { 
		$page 			= getCurrPage();
		if(isset($page ) && $page == 'CustomerList') 
			$c_class		=	'btn-success';
		else if(isset($page ) && $page == 'TransactionAnalytics') 
			$t_class		=	'btn-success';
		else if(isset($page ) && $page == 'TopOrders') 
			$p_class		=	'btn-success';
		else if(isset($page ) && $page == 'TransactionList')
			$l_class		=	'btn-success';
		else
			$c_class = $p_class = $t_class = $l_class = '';?>
		
			<div class="col-xs-8 ">
				<div class="btn-inline space_top">
				<a href="ProductCustomer" title="Customer List" class="col-xs-12 btn  <?php if(isset($c_class) && $c_class != '') echo $c_class; else  echo 'btn-default';?>">Customer Analytics</a>
				</div>
				<div class="btn-inline">
				<a href="TopOrders?cs=1" title="Product Analytics" class="col-xs-12 btn <?php if(isset($p_class) && $p_class != '') echo $p_class; else  echo 'btn-default'; ?>">Product Analytics</a>
				</div>
				<div class="btn-inline">
				<a href="TransactionAnalytics?cs=1" title="Transaction Analytics" class="col-xs-12 btn <?php if(isset($t_class) && $t_class != '') echo $t_class; else  echo 'btn-default'; ?>">Transaction Analytics</a>
				</div>				
			</div>
	
<?php } 

	function CustomerAnalyticsTab() { 
		$page 		= 	getCurrPage();		
		$success	=	0;
		if(isset($page ) && $page == 'CustomerAnalyticsOverview') 
			$success		=	1;
		if(isset($page ) && $page == 'ProductCustomer') 
			$success		=	2;
		if(isset($page ) && $page == 'ProductComments') 
			$success		=	3;
		if(isset($page ) && $page == 'CustomerTransaction') 
			$success		=	4;
		if(isset($page ) && $page == 'Demographics') 
			$success		=	6;
		if(isset($page ) && $page == 'Performance') 
			$success		=	5;
		
		?>
			
			<div class="col-xs-12 col-sm-10 col-md-10 col-lg-10 product_analytics LH56 no-padding">
				<div class="btn-inline">
					<a href="CustomerAnalyticsOverview?cs=1" title="Overview" class="col-xs-12 btn  <?php if($success == 1) echo ' btn-success btn-list'; else  echo ' btn-default btn-list'; ?>">Overview</a>
				</div>
				<div class="btn-inline">
					<a href="ProductCustomer" title="Customer List" class="col-xs-12 btn  <?php if($success == 2) echo ' btn-success btn-list'; else  echo ' btn-default btn-list';?>">Customers</a>
				</div>				
				<div class="btn-inline">
					<a href="ProductComments?cs=1&analytics=customer" title="Comments" class="col-xs-12 btn  <?php if($success == 3) echo ' btn-success btn-list'; else  echo ' btn-default btn-list';?>">Comments</a>
				</div>
				<div class="btn-inline">
					<a href="CustomerTransaction?analytics=customer" title="Transaction history" class="col-xs-12 btn  <?php if($success == 4) echo ' btn-success btn-list'; else  echo ' btn-default btn-list';?>">Transaction history</a>
				</div>
				<div class="btn-inline">
					<a href="Performance?cs=1" title="Performance" class="col-xs-12 btn  <?php if($success == 5) echo ' btn-success btn-list'; else  echo ' btn-default btn-list';?>">Performance</a>
				</div>
				<div class="btn-inline">
					<a href="Demographics?cs=1" title="Demographics" class="col-xs-12 btn  <?php if($success == 6) echo ' btn-success btn-list'; else  echo ' btn-default btn-list';?>">Demographics</a>
				</div>					
			</div>		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script> 
		<script>
			$(document).ready(function(){
				$("#left_analytics").addClass("menu_active");
				$("#customer_analytics").addClass("sub_menu_active");
				$('.treeview-menu').show();
				$('#left_analytics').addClass('opened');
			});
		</script>
<?php }
	function ProductAnalyticsTab() { 
		$page 		= 	getCurrPage();
		
		$success	=	0;
		/*if(isset($page ) && $page == 'TopOrders') 
			$success		=	1;
		if(isset($page ) && $page == 'ProductComments') 
			$success		=	3;
		if(isset($page ) && $page == 'ProductSales') 
			$success		=	4;
		if(isset($page ) && $page == 'Performance') 
			$success		=	5;*/
		if(isset($page ) && $page == 'AnalyticsCategory') 
			$success		=	6;
		if(isset($page ) && $page == 'TopSellers') 
			$success		=	7;
		if(isset($page ) && $page == 'ProductAnalytics') 
			$success		=	8;
		?>
	
			<div class="col-xs-12 col-sm-10 col-md-10 col-lg-10 product_analytics LH56 no-padding">
				<!--<div class="btn-inline">
					<a href="TopOrders?cs=1" title="Top Sellers" class="col-xs-12 btn  <?php if($success == 1) echo ' btn-success btn-list'; else  echo ' btn-default btn-list';?>">Orders</a>
				</div>
				<div class="btn-inline">
					<a href="ProductComments?cs=1&analytics=product" title="" class="col-xs-12 btn  <?php if($success == 3) echo ' btn-success btn-list'; else  echo ' btn-default btn-list';?>">Comments</a>
				</div>
				<div class="btn-inline">
					<a href="ProductSales?cs=1" title="" class="col-xs-12 btn  <?php if($success == 4) echo ' btn-success btn-list'; else  echo ' btn-default btn-list';?>">Sales</a>
				</div>-->
				<!--<div class="btn-inline">
					<a href="CustomerTransaction?analytics=product" title="" class="col-xs-12 btn  <?php if($success == 5) echo ' btn-success btn-list'; else  echo ' btn-default btn-list';?>">Transactions</a>
				</div>-->
				<div class="btn-inline">
					<a href="ProductAnalytics?cs=1" title="Products" class="col-xs-12 btn  <?php if($success == 8) echo ' btn-success btn-list'; else  echo ' btn-default btn-list';?>">Products</a>
				</div>
				<div class="btn-inline">
					<a href="AnalyticsCategory?cs=1" title="Categories" class="col-xs-12 btn  <?php if($success == 6) echo ' btn-success btn-list'; else  echo ' btn-default btn-list';?>">Categories</a>
				</div>	
				<div class="btn-inline">
					<a href="TopSellers?cs=1" title="" class="col-xs-12 btn  <?php if($success == 7) echo ' btn-success btn-list'; else  echo ' btn-default btn-list';?>">Top Sellers</a>
				</div>
			</div>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script> 
		<script>
			$(document).ready(function(){
				$("#left_analytics").addClass("menu_active");
				$("#product_analytics").addClass("sub_menu_active");
				$('.treeview-menu').show();
				$('#left_analytics').addClass('opened');
			});
		</script>

<?php } function top_header_before_login () { 
	global $backgroundSliderArray;?>
	<div class="page-wrap">
		<div id="slideshow">
			<?php foreach($backgroundSliderArray as $key=>$values){ ?>
		    <div <?php if($key == 1) { echo 'class="active"'; } ?> style="background-image: url('<?php echo $values;?>');"></div>
			<?php } ?>
		    <!-- <div style="background-image: url('webresources/images/HomePage_Bg2@2x.png');"></div>
		    <div style="background-image: url('webresources/images/HomePage_Bg3@2x.png');"></div>
		    <div style="background-image: url('webresources/images/HomePage_Bg4@2x.png');"></div>
		    <div style="background-image: url('webresources/images/HomePage_Bg5@2x.png');"></div> -->
		</div>
		<div class="content" id="login-box">
				<!--<h1 align="center"><span>Tuplit</span><a href="Login" class="logo"><img src="webresources/images/tuplit_logo.png" width="120" height="120" alt=""></a></h1>-->
				<h1 align="center">	<a href="Login" class="logo">
					<img src="webresources/images/tuplit_logo.png" width="120" height="120" alt=""></a></h1>
<?php } ?>
