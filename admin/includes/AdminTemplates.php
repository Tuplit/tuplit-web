<?php
function commonHead() { ?>
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
	
	<link rel="STYLESHEET" type="text/css" href="<?php echo ADMIN_STYLE_PATH; ?>jquery.fancybox.css"> 
	
	<link rel="STYLESHEET" type="text/css" href="<?php echo ADMIN_STYLE_PATH; ?>jquery-ui.css"> 
	<link rel="STYLESHEET" type="text/css" href="<?php echo ADMIN_STYLE_PATH; ?>jquery.ui.theme.css">
</head>
<?php } 
   function top_header() { 
   	 $main_link_array = array();
	if(SERVER){
		$menu_management_array = array(
            'Settings' => array(
                'General Settings' => array('GeneralSettings'),
                'Change Password' => array('ChangePassword'),
				'CMS' => array('StaticPages'),
				'Slider Images' => array('SliderImages')
            ),
			'User' => array(
				'Add User'	 => array('UserManage'),
                'User List' => array('UserList?cs=1'),
			),
			'Merchant' => array(
				'Merchant List'	 => array('MerchantList?cs=1'),
			),
			'Category Management' => array(
				'Add Category'	 => array('CategoryManage'),
                'Category List' => array('CategoryList?cs=1'),
			),
			'Product' => array(
				'Product List'	 => array('ProductList?cs=1'),
                'Product Category List' => array('ProductCategoryList?cs=1'),
			),
			'Report / Analytics' => array(
				'Log Tracking'	 => array('LogTracking?cs=1'),
			),
        );
	}
	else{
		$menu_management_array = array(
            'Settings' => array(
                'General Settings' => array('GeneralSettings'),
                'Change Password' => array('ChangePassword'),
				'CMS' => array('StaticPages'),
				'Slider Images' => array('SliderImages')
            ),
			'User' => array(
				'Add User'	 => array('UserManage'),
                'User List' => array('UserList?cs=1'),
			),
			'Merchant' => array(
				'Merchant List'	 => array('MerchantList?cs=1'),
			),
			'Category Management' => array(
				'Add Category'	 => array('CategoryManage'),
                'Category List' => array('CategoryList?cs=1'),
			),
			
			'Product' => array(
				'Product List'	 => array('ProductList?cs=1'),
                'Product Category List' => array('ProductCategoryList?cs=1'),
			),
			'Order' => array(
				'Order List'	 => array('OrderList?cs=1'),
			),
			'Report / Analytics' => array(
				'Log Tracking'	 => array('LogTracking?cs=1'),
			),
        );
	}
    $main_link_array['Settings'] 			= 	array('GeneralSettings', 'ChangePassword','StaticPages','SliderImages');
	$main_link_array['User'] 				=	array('UserManage','UserDetail','UserList','Messages','Activity','MyActivity');
	$main_link_array['Merchant'] 			=	array('MerchantList','MerchantDetail','MerchantManage');
	$main_link_array['Product'] 			=	array('ProductList','ProductDetail','ProductCategoryManage','ProductCategoryDetail','ProductCategoryList','ProductManage');
	$main_link_array['Order'] 				=	array('OrderList','OrderDetail','OrderManage');
	$main_link_array['Category Management'] =	array('CategoryManage','CategoryDetail','CategoryList');
	$main_link_array['Report / Analytics'] 	=	array('LogTracking');
	
	$page = getCurrPage();
	if(isset($_GET['st']) && $_GET['st']!='') {
		$page_st = 'st='.$_GET['st'];
	}	
   ?>
   <header class="header">
            <a  title="Tuplit" href="login" class="logo">
                Tuplit
            </a>
            <!-- Header Navbar: style can be found in header.less -->
            <nav class="navbar navbar-static-top" role="navigation">
                <!-- Sidebar toggle button-->
                <a href="#" class="navbar-btn sidebar-toggle" data-toggle="offcanvas" role="button" title="menu">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </a>
                <div class="navbar-right">
                    <ul class="nav navbar-nav">
                        <!-- User Account: style can be found in dropdown.less -->
						<li class="dropdown notifications-menu" id='merchant_approve' style="display:none;">
                            <a href="MerchantList?cs=1&status=0" data-toggle="tooltip"  data-placement="bottom" title="Merchant waiting for approval">
                                <i class="fa fa-thumbs-up fa-lg"></i>
                                <span class="label label-warning" id="merchant_approve_value"></span>
                            </a>                            
                        </li>
						
                        <li class="user user-menu">
                            <a href="#" style="cursor:default">
                                <i class="glyphicon glyphicon-user"></i>
                                <span>Welcome Admin, </span>
                            </a>
                        </li>
						<li><a href="Logout" class="logout"><i class="fa fa-power-off"></i>&nbsp;&nbsp;<span>Sign out</span></a></li>
                    </ul>
                </div>
            </nav>
        </header>
   		 <div class="wrapper row-offcanvas row-offcanvas-left">
            <!-- Left side column. contains the logo and sidebar -->
            <aside class="left-side sidebar-offcanvas">
                <!-- sidebar: style can be found in sidebar.less -->
                <section class="sidebar">
                    <!-- sidebar menu: : style can be found in sidebar.less -->
                    <ul class="sidebar-menu">
                        <?php  foreach ($menu_management_array as $menu_key => $menu_value) {  ?>
			
                     	<li class="treeview <?php if (in_array($page, $main_link_array[$menu_key])) { ?>active<?php } ?> " > 
						<a style="cursor:pointer" href="#ngo" title="<?php echo $menu_key; ?>" class="tab <?php echo $menu_key; ?>">
							<b class="fa "></b>
							<span class="<?php echo $menu_key; ?>"> <?php echo $menu_key; ?></span>
							<i class="fa fa-angle-right pull-right"></i>
						</a>
                         <ul class="treeview-menu">
                     	<?php 
								foreach ($menu_value as $m_key => $m_value) { 
								foreach($m_value as $m_val) {?>
								<li <?php if (strstr($m_val, $page)) { ?>class="active"<?php }} ?> ><a href="<?php echo $m_value[0]; ?>" title="<?php echo $m_key; ?>" ><i class="fa fa-angle-double-right"></i> <?php echo $m_key;?></a></li>
						<?php } ?>
                         </ul>
                     </li>
					<?php } ?>
                    </ul>
                </section>
                <!-- /.sidebar -->
            </aside>

			 <!-- Right side column. Contains the navbar and content of the page -->
            <aside class="right-side">
<?php } 
function commonFooter() { 
if(!strstr($_SERVER['PHP_SELF'],'ResetPassword.php')){
	require_once('controllers/MerchantController.php');
	$MerchantObj   =   new MerchantController();
	$result = $MerchantObj->getMerchantNotApproved();
	$merchantApproveTotal = $result[0]->total;?>
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
	
	<script type="text/javascript">
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