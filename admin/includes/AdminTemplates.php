<?php
mysql_set_charset('utf8');//
$fields		  =	" AdminLogo ";
$where		  =	" 1 ";
function commonHead() { 
$page = '';
$page = getCurrPage(); ?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title><?php echo SITE_TITLE; ?></title>			
	<meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
	
	<link rel="icon" href="<?php echo ADMIN_IMAGE_PATH; ?>favicon.ico" type="image/x-icon" />
	<link rel="shortcut icon" href="<?php echo ADMIN_IMAGE_PATH; ?>favicon.ico" type="image/x-icon" />
	
	<link href="<?php echo ADMIN_STYLE_PATH; ?>theme/bootstrap.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo ADMIN_STYLE_PATH; ?>theme/font-awesome.min.css" rel="stylesheet" type="text/css" />
	<!-- Theme style -->
	<link href="<?php echo ADMIN_STYLE_PATH; ?>theme/AdminLTE.css" rel="stylesheet" type="text/css" />	

	<script type="text/javascript">
	
		if (Function('/*@cc_on return document.documentMode===10@*/')()){
			 document.documentElement.className+='ie10';
		}
	</script>
	<style>
.fa fa-spinner fa-spin fa-lg
.fa-spin,.small-box,.photo_load  {display: none;}
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
</head>
<?php } 
function popup_head(){ ?>
	<!DOCTYPE html>
<html class="new-popup">
<head>
	<link href="<?php echo ADMIN_STYLE_PATH; ?>theme/bootstrap.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo ADMIN_STYLE_PATH; ?>theme/font-awesome.min.css" rel="stylesheet" type="text/css" />
	<!-- Theme style -->
	<link href="<?php echo ADMIN_STYLE_PATH; ?>theme/AdminLTE.css" rel="stylesheet" type="text/css" />		


</head>
<?php } 
   function top_header() { 
   	 $main_link_array = array();
		$menu_management_array = array(
			'Manage Merchants' 		=> array('Merchants?cs=1'),
			'Manage Customers' 		=> array('Customers?cs=1'),
			'Manage Content' 		=> array('ManageContent?cs=1'),
			'Manage Transactions' 	=> array('Transactions?cs=1'),
			'Reports' 				=> array('ReportOverview?cs=1'),
			'Settings' 				=> array('CommonSettings?cs=1'),
			'Statistics/Tracking'	=> array('Statistics?cs=1'),
			/*'Statistics/Tracking' 	=> array(
				'Log Tracking'	 		=> array('LogTracking?cs=1'),
				'Statistics'			=> array('Statistics?cs=1'),
				//'Customer Analytics'	=> array('CustomerAnalytics?cs=1'),
				//'Products/Category Analytics'	=> array('ProductAnalytics?cs=1'),
				//'Transaction Analytics'	=> array('TransactionAnalytics?cs=1'),
				'Transfer'				=> array('Transfer?cs=1'),
				'Service List'				=> array('ServiceList?cs=1'),
				//'Transactions'			=> array('TransactionHistory?cs=1'),
				//'LocationBreakdown'		=> array('LocationBreakdown?cs=1'),
			),*/
			'Logout' 		=> array('Logout'),
        );
    $main_link_array['Settings'] 				= 	array('CommonSettings');
	$main_link_array['Reports'] 				= 	array('ReportOverview','TransactionHistory','Performance','CustomerReport','LocationBreakdown','Demographics');
	$main_link_array['Manage Customers'] 		=	array('Customers','CustomerManage');
	$main_link_array['Manage Merchants'] 		=	array('Merchants','MerchantManage');
	$main_link_array['Order Management'] 		=	array('OrderList');
	$main_link_array['Category Management'] 	=	array('CategoryManage','CategoryDetail','CategoryList');
	$main_link_array['Manage Content'] 			=	array('ManageContent');
	$main_link_array['Webservice Management'] 	=	array('ServiceList','ServiceDetail','ServiceManage');
	$main_link_array['Statistics/Tracking'] 	=	array('LogTracking','Statistics','Transfer','ServiceList','ServiceManage','ServiceDetail');
	$main_link_array['Logout'] 					=	array('Logout');
	$main_link_array['Manage Transactions'] 	=	array('Transactions');
	$page = getCurrPage();
	if(isset($_GET['st']) && $_GET['st']!='') {
		$page_st = 'st='.$_GET['st'];
	}	
	$AdminLogoPath = ADMIN_IMAGE_PATH.'profile-logo.png';
	require_once('controllers/AdminController.php');
	$adminLoginObj   =   new AdminController();
	$logo_details = $adminLoginObj->getAdminDetails('AdminLogo',1);
	if(!empty($logo_details)){
		if(isset($logo_details[0]->AdminLogo) && $logo_details[0]->AdminLogo != ''){
			$AdminLogoName 		= 	$logo_details[0]->AdminLogo;
			if(SERVER){
				if(image_exists(11,$AdminLogoName))
					$AdminLogoPath 	= 	ADMIN_LOGO_PATH.$AdminLogoName;
			}
			else{
				if(file_exists(ADMIN_LOGO_PATH_REL.$AdminLogoName))
					$AdminLogoPath 	= 	ADMIN_LOGO_PATH.$AdminLogoName;
			}
		}
	}
   ?>
   <div class="loader-merchant"><!--loader-->
				<div class="mercha-loader">
					<!-- <img  src="<?php // echo ADMIN_IMAGE_PATH?>bx_loader.gif" alt="loading.."> -->
					<i class="fa fa-spinner fa-spin fa-lg"></i>
			
				</div>
			</div>
   <div class="wrapper row-offcanvas row-offcanvas-left">
		<!-- Left side column. contains the logo and sidebar -->
		<aside class="left-side sidebar-offcanvas">
			<!-- sidebar: style can be found in sidebar.less -->
			<section class="sidebar">
				<div class="profile-img">
					<a href="CommonSettings" class="no-padding no-margin"><img src="<?php echo $AdminLogoPath; ?>" width="76" height="76" /></a>
					<a href="CommonSettings" class="no-padding no-margin"><span>Admin</span></a>
				</div>
				<!-- sidebar menu: : style can be found in sidebar.less -->
				<ul class="sidebar-menu">
					<?php  foreach ($menu_management_array as $menu_key => $menu_value) { 
					 ?>
						<?php if(is_array($menu_value) && count($menu_value) == 1) {   ?>
							<li class=" <?php if (in_array($page, $main_link_array[$menu_key])) { ?>active<?php } ?> " > 
								<a style="cursor:pointer" href="<?php echo $menu_value[0];?>" title="<?php echo $menu_key; ?>" class="tab <?php echo ($menu_key == 'Statistics/Tracking'?'Statistics':$menu_key); ?>">
									<span class="<?php echo ($menu_key == 'Statistics/Tracking'?'Statistics':$menu_key);  ?>"> <?php echo $menu_key; ?></span>
								</a>
							</li>
						 <?php } else { ?>
						 	<li class="treeview <?php if (in_array($page, $main_link_array[$menu_key])) { ?>active<?php } ?> " > 
								<a style="cursor:pointer" href="#ngo" title="<?php echo $menu_key; ?>" class="tab <?php echo ($menu_key == 'Statistics/Tracking'?'Statistics':$menu_key); ?>">
									<span class="<?php echo ($menu_key == 'Statistics/Tracking'?'Statistics':$menu_key);  ?>"> <?php echo $menu_key; ?></span>
								</a>
								 <ul class="treeview-menu">
								<?php 
										foreach ($menu_value as $m_key => $m_value) { 
										foreach($m_value as $m_val) {?>
										<li <?php if (strstr($m_val, $page)) { ?>class="active"<?php }} ?> ><a href="<?php echo $m_value[0]; ?>" title="<?php echo $m_key; ?>" ><?php echo $m_key;?></a></li>
								<?php } ?>
								 </ul>
							 </li>
						 <?php } ?>
						 
					
				<?php } ?>
				</ul>
			</section>
			<!-- /.sidebar -->
		</aside>

		 <!-- Right side column. Contains the navbar and content of the page -->
         <aside class="right-side">
			<div  class="header nav-header">
				<!-- Header Navbar: style can be found in header.less -->
				<nav class="navbar navbar-static-top" role="navigation">
					<!-- Sidebar toggle button-->
					<div class="toogle-icon">
						 <a href="#" class="navbar-btn sidebar-toggle" data-toggle="offcanvas" role="button" title="menu">
							<span class="sr-only">Toggle navigation</span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</a>
						<a class="logo-icon" href="#" title="tuplit"></a>
						<!-- <a class="alert-icon" class="dropdown notifications-menu" id='merchant_approve' style="display:none;"  href="MerchantList?cs=1&status=0"   data-placement="bottom" title="Merchant waiting for approval" href="" title="">data-toggle="tooltip"
							<span class="label label-warning" id="merchant_approve_value"></span>
						</a> -->
						<span class="alert-icon"></span>
						
					</div>
					<a href="Merchants?cs=1&status=0" class="merchat-alert" id='merchant_approve' style="display:none;" >
						<span id="merchant_approve_value"></span> Merchant waiting for approval
					</a>
				</nav>
				
			</div>
		 
			<?php } 
			function commonFooter() { 
			if(!strstr($_SERVER['PHP_SELF'],'ResetPassword.php')){
				require_once('controllers/MerchantController.php');
				$MerchantObj   =   new MerchantController();
				$result = $MerchantObj->getMerchantNotApproved();
				$merchantApproveTotal = $result[0]->total; 
				$page = getCurrPage(); 
			?>
			 <input type="Hidden" id="mer_app_tot" value="<?php echo $merchantApproveTotal; ?>">
			<?php } ?>
			
		</aside><!-- /.right-side -->
     </div><!-- ./wrapper -->
</body>
 	<!-- jquery.validate -->
 	<script src="<?php echo ADMIN_SCRIPT_PATH; ?>jquery-latest.js" type="text/javascript"></script>
	<script src="<?php echo ADMIN_SCRIPT_PATH; ?>Util.js" type="text/javascript"></script>
	<script src="<?php echo ADMIN_SCRIPT_PATH; ?>jquery.validate.js" type="text/javascript"></script>
	<script src="<?php echo ADMIN_SCRIPT_PATH; ?>Validate.js" type="text/javascript"></script>
	<script src="<?php echo ADMIN_SCRIPT_PATH; ?>AjaxDirector.js" type="text/javascript"></script>
	<script src="<?php echo ADMIN_SCRIPT_PATH; ?>AjaxFileUpload.js" type="text/javascript"></script>
	<script src="<?php echo ADMIN_SCRIPT_PATH; ?>fancybox/jquery.fancybox.js" type="text/javascript"></script>
	<script src="<?php echo ADMIN_SCRIPT_PATH; ?>jquery-ui.js" type="text/javascript"></script>
		
	<?php if(isset($page) && $page	==	'MerchantManage') { ?>	
	<script src="<?php echo ADMIN_SCRIPT_PATH; ?>TimeSelect.js" type="text/javascript"></script>
	<?php }  if(isset($page) && ($page	==	'ContentManage' || $page	==  'ManageContent')) { ?>	
	<script src="<?php echo ADMIN_SCRIPT_PATH; ?>tinymce/tinymce.min.js" type="text/javascript"></script>
	<?php } ?>
	
	
	<script src="<?php echo ADMIN_SCRIPT_PATH; ?>jquery.ui.touch-punch.js" type="text/javascript"></script> 
<!-- Morris.js charts -->
 	<?php if(isset($page) && ($page	 == 'Demographics' || $page	 == 'ReportOverview')) { ?>
	<script src="<?php echo ADMIN_SCRIPT_PATH; ?>theme/plugins/morris/morris.js" type="text/javascript"></script>
	<?php }else{?>
    <script src="<?php echo ADMIN_SCRIPT_PATH; ?>theme/plugins/morris/morris.min.js" type="text/javascript"></script>
	<?php }?>
	<script type="text/javascript">
		var loaded = false;
		function SetLoaded() 
		{ loaded = true; }
		window.onload = SetLoaded;
		var tot = $('#mer_app_tot').val();
		//alert(tot)
		if(tot > 0) {
			$('#merchant_approve').css('display','block');
			$('#merchant_approve_value').html(tot);
		}
			$(window).scroll(function() {    
			    var scroll = $(window).scrollTop();
			
			    if (scroll >= 100) {
			        $(".adm_head").addClass("fixed");
			    }	 else {
	        $(".adm_head").removeClass("fixed");
	    }
			}); 
		/*$('.sidebar-menu .treeview').click(function(){
				$('.treeview').removeClass('active');
				$('.treeview-menu').css('display','none');
				
				$(this).addClass('active');
				$(this).children(".treeview-menu").show('slow');
		});*/
	</script>
	
        <!-- Bootstrap -->
        <script src="<?php echo ADMIN_SCRIPT_PATH; ?>theme/bootstrap.min.js" type="text/javascript"></script>
        <!-- AdminLTE App -->
		<?php
			if(!strstr($_SERVER['PHP_SELF'],'ResetPassword.php')){
		?>
        <script src="<?php echo ADMIN_SCRIPT_PATH; ?>theme/AdminLTE/app.js" type="text/javascript"></script>
		<?php
			}
		?>
<?php } ?>