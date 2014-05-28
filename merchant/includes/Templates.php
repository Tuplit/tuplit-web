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
</head>
<?php } 
   function top_header() { 
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
                        <!-- User Account: style can be found in dropdown.less -->
                        <li class="user user-menu">
                            <a href="Myaccount" title="My Account" class="<?php echo $active_class;?>" >
                                <i class="fa fa-user"></i>
                                <span>My Account</span>
                            </a>
                           <!-- <ul class="dropdown-menu">
                                <li class="user-footer">
                                    <div class="pull-left">
                                        <a href="#" class="btn btn-default btn-flat">Profile</a>
                                    </div>
                                    <div class="pull-right">
                                        <a href="Logout" class="btn btn-default btn-flat">Sign out</a>
                                    </div>
                                </li>
                            </ul> -->
                        </li>
						<li><a href="Logout" class="" title="Log out"><i class="fa fa-power-off"></i>&nbsp;&nbsp;Log out</a></li>
                    </ul>
                </div>
            </nav>
				<? } ?>
        </header>
   		 <div class="wrapper row-offcanvas row-offcanvas-left">
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
	
<?php } 
 function footerLogin() { ?>
 		<footer>&copy; <?php echo date('Y');?> Tuplit Inc. <p class=""> <a href="#" title="About Tuplit">About Tuplit</a> | <a href="#" title="Privacy Policy">Privacy Policy</a>  | <a href="#" title="Terms of Service">Terms of Service</a>  | <a href="#" title="Help">Help</a>  | <a href="#" title="Contact Us">Contact Us</a> </p></footer>
 
	
<?php } ?>