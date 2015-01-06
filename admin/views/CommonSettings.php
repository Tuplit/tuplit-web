<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
require_once('controllers/AdminController.php');
$adminLoginObj   =   new AdminController();
require_once('controllers/ManagementController.php');
$managementObj   =   new ManagementController();
require_once('controllers/ProductController.php');
$productObj   =   new ProductController();
require_once('controllers/LocationController.php');
$locationObj   =   new LocationController();
require_once('controllers/CurrencyController.php');
$currencyObj   		=   new CurrencyController();
$tutorial_count = 0;
$class =  $msg  = $class_icon = $cover_path = ''; //general setting msg
$class_pswd =  $msg_pswd  = $class_icon_pswd = ''; // change pswd msg
$class_cate =  $msg_cate  = $class_icon_cate = ''; // category mgmt msg
$display = 'none';
$error = $msg = '';
$fields		  =	" * ";
$where		  =	" 1 ";
$fees         = '';
if(isset($_GET['cs']) && $_GET['cs'] == 1){
 	unset($_SESSION['startlimit']);
	unset($_SESSION['tuplit_sess_Location_status']);
	unset($_SESSION['tuplit_sess_Currency_status']);
}

/*--------General Setting----------*/
$user_details = $adminLoginObj->getAdminDetails($fields,$where);
if(isset($user_details) && is_array($user_details) && count($user_details)>0){
		$user_name 	= 	$user_details[0]->UserName;
		$email		=	$user_details[0]->EmailAddress;
		$limit 		=	$user_details[0]->LocationLimit;
		$fees 		=	$user_details[0]->MangoPayFees;
		if(isset($user_details[0]->AdminLogo) && $user_details[0]->AdminLogo != ''){
		$AdminLogoName 		= 	$user_details[0]->AdminLogo;
		$AdminLogoPath 	= 	$AdminLogoPath = '';
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
/*--------Category manangement----------*/
if(isset($_GET['delId']) && $_GET['delId']!=''){
	$delete_id      = $_GET['delId'];
}

if(isset($delete_id) && $delete_id != ''){	
	$managementObj->deleteCategoryReleatedEntries($delete_id);
	$field			 = " CategoryIcon ";
	$delete             = explode(',',$delete_id);
	if(isset($delete) && is_array($delete) && count($delete) >0 )
	{
		foreach($delete as $key=>$value){
			$unlink_comdition   = " id = ".$value;
			$CategoryListResult     = $managementObj->selectCategoryDetails($field,$unlink_comdition);
			if(isset($CategoryListResult) && is_array($CategoryListResult) && count($CategoryListResult) > 0){
				if(isset($CategoryListResult[0]->CategoryIcon) && $CategoryListResult[0]->CategoryIcon != ''){
					$Category_image = $CategoryListResult[0]->CategoryIcon;	
					if(SERVER){
						deleteImages(3,$Category_image);
					}
					else{
						if(file_exists(CATEGORY_IMAGE_PATH_REL . $Category_image))
							unlink(CATEGORY_IMAGE_PATH_REL . $Category_image);
					}
				}
			}
		}
	}
	header("location:CommonSettings?msg=6&cs=1");	
}

$fields    			= " c.CategoryName,c.CategoryIcon,count(p.id) as ProductsCount ";
$condition 			= " and c.Status in (1) ";
$CategoryListResult = $managementObj->getCategoriesList($fields,$condition);
$tot_rec 		 	= $managementObj->getTotalRecordCount();



/*---------Location management----------*/
unset($_SESSION['curpage']);
$_SESSION['tuplit_sess_Location_status'] = '1';
$LocationListResult = $locationObj->getLocationList();
$tot_rec_location 	= $locationObj->getTotalRecordCount();

if(isset($_GET['locDelId']) && $_GET['locDelId'] != ''){
	$result = $locationObj->deleteLocation($_GET['locDelId']);
	if($result){ ?>
			<script>
				window.parent.location.href = "CommonSettings?msg=10";
			</script>
<?php }
}
if(isset($_GET['curDelId']) && $_GET['curDelId'] != ''){
	//echo $_GET['curDelId'];
	//die();
	$result = $currencyObj->deleteCurrency($_GET['curDelId']);
	//echo ($_GET['curDelId']);
	
	if($result){ ?>
			<script>
				window.parent.location.href = "CommonSettings?msg=12";
			</script>
<?php }
}
/*--------Currency Management----------*/
//$_SESSION['tuplit_sess_Currency_status'] = '1';
$CurrencyListResult  	= $currencyObj->getCurrencyList();
$tot_rec_currency 		= $currencyObj->getTotalRecordCount();

/*--------Succuss/Error messages----------*/
if(isset($_GET['msg']) && $_GET['msg'] == 1){
	$msg = "General Settings updated successfully";
}else if(isset($_GET['msg'])&& $_GET['msg'] == 2){
	$msg_pswd            = "Password updated successfully";
	$class_pswd          = "alert-success";
	$class_icon_pswd     = "fa-check";
	$display_pswd        = "block";
}else if(isset($_GET['msg'])&& $_GET['msg'] == 3){
	$msg_pswd      		= "Invalid Old Password";
	$class_pswd    		= "alert-danger";
	$class_icon_pswd    = "fa-warning";
	$display_pswd  		= "block";
}else if(isset($_GET['msg']) && $_GET['msg'] == 4){
	$msg_cate 			= 	"Category added successfully";
	$display_cate		=	"block";
	$class_cate 		= 	"alert-success";
	$class_icon_cate 	=  	"fa-check";
}else if(isset($_GET['msg']) && $_GET['msg'] == 5){
	$msg_cate 			= 	"Category updated successfully";
	$display_cate		=	"block";
	$class_cate 		= 	"alert-success";
	$class_icon_cate 	= 	"fa-check";
}else if(isset($_GET['msg']) && $_GET['msg'] == 6){
	$msg_cate 			= 	"Category deleted successfully";
	$display_cate		=	"block";
	$class_cate 		= 	"alert-success";
	$class_icon_cate    = 	"fa-check";
}else if(isset($_GET['msg']) && $_GET['msg'] == 7){
	$msg_cate 			= 	"Status changed successfully";
	$display_cate		=	"block";
	$class_cate 		= 	"alert-success";
	$class_icon_cate 	= 	"fa-check";
}else if(isset($_GET['msg']) && $_GET['msg'] == 8){
	$msg_loc 			= 	"Location Updated successfully";
	$display_loc		=	"block";
	$class_loc 			= 	"alert-success";
	$class_icon_loc 	= 	"fa-check";
}else if(isset($_GET['msg']) && $_GET['msg'] == 10){
	$msg_loc 			= 	"Location deleted successfully";
	$display_loc		=	"block";
	$class_loc			= 	"alert-success";
	$class_icon_loc 	= 	"fa-check";
}else if(isset($_GET['msg']) && $_GET['msg'] == 9){
	$msg_cur 			= 	"Currency Updated successfully";
	$display_cur		=	"block";
	$class_cur			= 	"alert-success";
	$class_icon_cur 	= 	"fa-check";
}else if(isset($_GET['msg']) && $_GET['msg'] == 12){
	$msg_cur 			= 	"Currency deleted successfully";
	$display_cur		=	"block";
	$class_cur			= 	"alert-success";
	$class_icon_cur 	= 	"fa-check";
}
/*--------Slider Welcome/Tutorial Images----------*/
require_once('controllers/UserController.php');
$userObj   =   new UserController();
$HomeSlideImageArray		=	$userObj->getSliderImageDetails('*', 'Status = 1 and SliderType = 1  ORDER BY `Order` asc ');
$HomeSlideOrder		=	$userObj->getSliderImageDetails('MAX(`Order`) as home_order', 'Status = 1 and SliderType = 1  ORDER BY `Order` asc ');
if(isset($HomeSlideOrder) && count($HomeSlideOrder) ){
	$home_count	 	= $HomeSlideOrder[0]->home_order;
}
$TutorialSlideImageArray	=	$userObj->getSliderImageDetails('*', 'Status = 1 and SliderType = 2  ORDER BY `Order` asc ');
$TutorialSlideOrder	=	$userObj->getSliderImageDetails('MAX(`Order`) as tutorial_order', 'Status = 1 and SliderType = 2  ORDER BY `Order` asc ');
if(isset($TutorialSlideOrder) && count($TutorialSlideOrder) ){
	$tutorial_count	= $TutorialSlideOrder[0]->tutorial_order;
}

commonHead(); ?>
<?php 
if(isset($_GET['msg']) && ($_GET['msg'] == 4 || $_GET['msg'] == 5 || $_GET['msg'] == 6)){ ?>
<style>
h2.tabsection a, h2.tabsection span {
    display: block;
}
</style>
<?php } 
?>
<body class="skin-blue">
	<?php top_header(); ?>
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<h1 class="col-xs-5 col-sm-5 no-padding">Settings</h1>
		<!--<div class="col-xs-7 col-sm-7 search-box no-padding" align="right">
			<input type="text" placeholder="Search" >
			<input type="submit" name="Search" value="Search" title="Search">  
		</div>-->
	</section>
	<!-- Main content -->
	<section class="content">
		<div class="row">
		<!-- left column -->
			<div class="col-md-12"><!--  col-lg-6 -->
				<div class="box box-primary">				
					<!-- GENERAL SETTINGS-->
					<div class="general-setting sett-menu">	
						<h2 class="tabsection" id="general">General Settings</h2>
						<!-- form start -->
						<div id="general_block" style="display:<?php if(isset($_GET['msg']) && $_GET['msg'] == 1){ echo "block";} else {echo "none";}?>;">
							<form name="general_settings_form" id="general_settings_form" action="GeneralSettings" method="post">
								<div class="box-body row ">
									<?php if($msg !='') { ?><div class="alert alert-success alert-dismissable col-sm-5 col-xs-10" align="center"><i class="fa fa-check"></i>&nbsp;&nbsp;<?php echo $msg;?></div><?php  } ?>
									<div class="form-group col-sm-5 col-xs-12">
										<label>Username</label>
										<div class="col-sm-8 col-xs-12  no-padding">
											<input type="text" readonly="readonly"  class="form-control" name="user_name" id="user_name" value="<?php  if(isset($user_name) && $user_name) echo $user_name  ?>" />
										</div>
									</div>
									<div class="form-group col-sm-5 col-xs-12">
										<label>Email</label>
										<div class="col-sm-8 col-xs-12  no-padding">
											<input type="text" class="form-control" name="email" id="email" value="<?php  if(isset($email) && $email) echo $email;  ?>"  >
										</div>
									</div>
									<div class="form-group col-sm-5 col-xs-12">
										<label>Location Limit</label>
										<div class="col-sm-8 col-xs-5  no-padding">
											<input type="text" class="form-control" name="limit" id="limit" maxlength="6" onkeypress="return isNumberKey_numbers(event);" value="<?php if(isset($limit)) echo $limit; ?>"  >
										</div>
										<span class="help-block LH30">&nbsp;&nbsp;(In kilometer)</span>
									</div>
									<div class="form-group col-sm-5 col-xs-12">
										<label>MangoPay Fees </label>
										<div class="col-sm-8 col-xs-12  no-padding">
											<input type="text" class="form-control" name="fees" id="fees" maxlength="3" onkeypress="return isNumberKey_numbers(event);" value="<?php if(isset($fees)) echo $fees; ?>"  >
										</div>
										<span class="help-block LH30">&nbsp;&nbsp;(In %)</span>
									</div>
									<div class="form-group col-sm-6 col-lg-6 clear">
										<label>Logo</label>
										<div class="row">
										    <div class="col-sm-8  col-lg-5"> 
												<input type="file"  name="admin_logo" id="admin_logo" title="Admin Logo" onclick="" onchange="return ajaxAdminFileUploadProcess('admin_logo');"  /> 
												<p class="help-block">(Dimension should be 100x100)</p>
												<span class="error" for="empty_admin_logo" generated="true" style="display: none">Admin logo is required</span>
											</div>
										    <div class="col-sm-4">
										         <div id="admin_logo_img">
													<?php  
													if(isset($AdminLogoPath) && $AdminLogoPath != ''){  ?>
										                 <a <?php if(isset($AdminLogoPath) && $AdminLogoPath != '') { ?> href="<?php echo $AdminLogoPath; ?>" class="admin_logo_pop_up"<?php } else { ?> href="Javascript:void(0);"<?php } ?> title="Click here" alt="Click here" ><img class="img_border" src="<?php  echo $AdminLogoPath;  ?>" width="75" height="75" alt="Image"/></a>
													<?php  }  ?>
										         </div>
										    </div>
										</div>
										<?php  if(isset($_POST['admin_logo_upload']) && $_POST['admin_logo_upload'] != ''){  ?><input type="Hidden" name="admin_logo_upload" id="admin_logo_upload" value="<?php  echo $_POST['admin_logo_upload'];  ?>"><?php  }  ?>
										<input type="Hidden" name="empty_admin_logo" id="empty_admin_logo" value="<?php  if(isset($AdminLogoName) && $AdminLogoName != '') { echo $AdminLogoName; }  ?>" />
										<input type="Hidden" name="name_admin_logo" id="name_admin_logo" value="<?php  if(isset($AdminLogoName) && $AdminLogoName != '') { echo $AdminLogoName; }  ?>" />
									</div>
									<div class="col-xs-12" align="left">
										<input type="submit" class="btn btn-success" name="general_settings_submit" id="general_settings_submit" value="Submit" title="Submit" >
									</div>
								</div><!-- /.box-body -->
							</form>
						</div>
					</div>
					<!-- GENERAL SETTINGS END-->
					
					<!-- CHANGE PASSWORD-->
					<div class="change-password sett-menu">	
						<h2 class="tabsection" id="change_password">Change Password</h2><!-- form start -->
						<div id="change_password_block" style="display:<?php if(isset($_GET['msg']) && ($_GET['msg'] == 2 || $_GET['msg'] == 3)){ echo "block";} else {echo "none";}?>;">
							<form name="change_password_form" id="change_password_form" action="ChangePassword" method="post">
								<div class="box-body row">
									<?php if(isset($msg_pswd) && $msg_pswd != '') { ?><div class="alert  alert-dismissable <?php  echo $class_pswd;  ?> col-sm-5  col-xs-10" align="center"><span><i class="fa <?php  echo $class_icon_pswd;  ?>"></i> <?php echo $msg_pswd;  ?></span></div><?php } ?>
									<div class="form-group col-sm-5 col-xs-12">
										<label>Old Password</label>
										<div class="col-sm-8 col-xs-12  no-padding">
											<input type="Password" class="form-control" name="old_password" id="old_password"  value="" >
										</div>
									</div>
									<div class="col-xs-12"></div>
									<div class="form-group col-sm-5 col-xs-12">
										<label>New Password</label>
										<div class="col-sm-8 col-xs-12  no-padding">
										<input type="Password" class="form-control" name="new_password" id="new_password"  value="" >
										</div>
									</div>
									<div class="form-group col-sm-5 col-xs-12">
										<label>Confirm Password</label>
										<div class="col-sm-8 col-xs-12  no-padding">
										<input type="Password" class="form-control" id="confirm_password" name="confirm_password"  value="" >
										</div>
									</div>
									<div class="col-xs-12">
										<input type="submit" class="btn btn-success" name="change_password_submit" id="change_password_submit" value="Submit" title="Submit">
									</div>
								</div><!-- /.box-body -->
							</form>
						</div>
					</div>
					<!-- CHANGE PASSWORD END-->
					<!-- SLIDER IMAGES -->
					<div class="slider-image" >
						<h2 class="tabsection" id="home_slider">Slider Images</h2>
						<div class="slider-image-form" id="home_slider_block" style="display:none;">
							<form name="add_home_slider_form" id="add_home_slider_form" action="" method="post" class="slider_page">
								<div class="box-header no-padding">
									<h3 class="box-title">Welcome Slider Images</h3>
									<i class="imgsize-text">(Please upload only JPG or PNG files. Image dimension should be 640x1136)</i>
								</div>										
								<div class="box-body row">					
									<div id="home_center" class="row_bg ">
										<div id="image_body" class="sortable">
										<?php if(isset($HomeSlideImageArray) && is_array($HomeSlideImageArray) && count($HomeSlideImageArray) > 0){
										$home_id_count	=	count($HomeSlideImageArray);
										$i = 0;
										//echo "<pre>"; echo var_dump($HomeSlideImageArray); echo "</pre>";
										foreach($HomeSlideImageArray as $h_key=>$h_val){
											$image_path = '';
											$home_photo = $h_val->SliderImages;
											$original_path = $image_path = ADMIN_IMAGE_PATH.'no_image.jpeg';
											if(isset($home_photo) && $home_photo != ''){
												$home_image = $home_photo;
												if(SERVER){
													if(image_exists(4,$home_image))
														$original_path = SLIDER_IMAGE_PATH.$home_image;
												}else{
													if(file_exists(SLIDER_IMAGE_PATH_REL.$home_image))
														$original_path = SLIDER_IMAGE_PATH.$home_image;
												}
											}
											?>
										<?php if(isset($h_val->id) && $h_val->id != ''){?>
												<div class="sort up-img"  id="Slider_Image_Old_<?php echo $h_val->id; ?>" >
													 <div class="" id="image_container_<?php echo $i;?>" > 
														<?php $i++;?>
														<div id="Slider_Image_Old_<?php echo $i;?>_img"  class="pad">
														<?php  if(isset($original_path) && $original_path != '') {  ?>
															<a onclick="return loaded;" <?php if(isset($original_path) && $original_path != '') {  ?> href="<?php echo $original_path; ?>" class="fancybox"<?php } else { ?> href="Javascript:void(0);"<?php } ?> ><?php if(isset($original_path) && $original_path != '') { ?> <img class="img_border" width="145" height="145" src="<?php echo $original_path;?>"><?php } ?></a>
														<?php } ?>
														<input type="Hidden" name="name_Slider_Image_Old_<?php echo $i;?>" id="name_Slider_Image_Old_<?php echo $i;?>" value="<?php  if(isset($home_photo) && $home_photo != '') { echo $home_photo; }  ?>" />
														<input type="hidden" name="Slider_Image_Old_<?php echo $i;?>_upload" id="Slider_Image_Old_<?php echo $i;?>_upload" value="Slider_Image_Old_<?php  if(isset($home_photo) && $home_photo != '') { echo $home_photo; }  ?>" />
														<input type="hidden" name="delete_Slider_Image_Old_<?php echo $i;?>" id="delete_Slider_Image_Old_<?php echo $i;?>" value="<?php  if(isset($h_val->id) && $h_val->id != '') { echo $h_val->id; }  ?>" />
														<div  width="200" height="50" class="delete"  id="Slider_Image_Old_<?php echo $i;?>_delete"><i class="fa fa-minus-circle fa-lg"></i></div>														
														</div>
														
													</div> 
												</div> 
										<?php } 
												}?>
												<div class="upload-div unsortable" id="welcome_Slider_upload">
													<?php ++$i;?>
													<div class="upload-img" id="file_input"> 
														<input type="file"  class="file_input_old" name="Slider_Image_Old_<?php echo $i;?>" id="Slider_Image_Old_<?php echo $i;?>" title="Home Slider" onclick="" />
														<span><i class="fa fa-plus"></i>Add Photo</span>
													</div>					
												</div>
											<?php }else{?>
												<div class="upload-div unsortable" id="welcome_Slider_upload">
													<div class="upload-img" id="file_input">
														<input type="file"  class="file_input_old" name="Slider_Image_Old_1" id="Slider_Image_Old_1" title="Home Slider" onclick=""/>
														<span><i class="fa fa-plus"></i>Add Photo</span>
													</div>					
												</div>
											<?php }?>		
										</div>								
									</div>
								</div>								
							</form>
						<!-- SLIDER IMAGES END-->					
						
						<!--TUTORAIL SLIDER IMAGES-->
						  
							  <form name="add_tutorial_slider_form" id="add_tutorial_slider_form" action="" method="post" class="slider_page" >
								<div class="box-header no-padding">
									<h3 class="box-title">Tutorial Slider Images</h3>
									<i class="imgsize-text">(Please upload only JPG or PNG files. Image dimension should be 640x1136)</i>
								</div>
								<?php if(isset($msg1) && $msg1 != '') { ?>
									<div align="center" class="alert <?php  echo $class1;  ?> alert-dismissable col-sm-5 col-xs-11"><i class="fa fa-check"></i>  <?php echo $msg1; ?>
									</div>
								<?php } ?>
								<div class="box-body row">						
									<div id="tutorial_center" class="row_bg ">
											<div id="tutor_img_body" class="sortable">
												<?php if(isset($TutorialSlideImageArray) && count($TutorialSlideImageArray) > 0){
													$i_val = 0;
													$tutorial_id_count	=	count($TutorialSlideImageArray);
													foreach($TutorialSlideImageArray as $t_key=>$t_val){
														$image_path_tutorial = '';
														$tutorial_photo = $t_val->SliderImages;
														$original_path_tutorial = $image_path_tutorial = ADMIN_IMAGE_PATH.'no_image.jpeg';
														if(isset($tutorial_photo) && $tutorial_photo != ''){
															$tutorial_image = $tutorial_photo;
															if(SERVER){
																if(image_exists(4,$tutorial_image))
																	$original_path_tutorial = SLIDER_IMAGE_PATH.$tutorial_image;
															}else{
																if(file_exists(SLIDER_IMAGE_PATH_REL.$tutorial_image))
																	$original_path_tutorial = SLIDER_IMAGE_PATH.$tutorial_image;
															}
													}$i_val++;?>
													
												<?php if(isset($t_val->id) && $t_val->id != ''){?>
													<div class="up-img" id="Tutorial_Image_Old_<?php echo $t_val->id; ?>"  >
														<div class="" id="image_container_<?php echo $i_val;?>" > 
															<div id="Tutorial_Image_Old_<?php echo $i_val;?>_img"  class="pad">
															<?php  if(isset($original_path_tutorial) && $original_path_tutorial != '') {  ?>
																<a onclick="return loaded;" <?php if(isset($original_path_tutorial) && $original_path_tutorial != '') {  ?> href="<?php echo $original_path_tutorial; ?>" class="fancybox"<?php } else { ?> href="Javascript:void(0);"<?php } ?> ><?php if(isset($original_path_tutorial) && $original_path_tutorial != '') { ?> <img class="img_border" width="145" height="145" src="<?php echo $original_path_tutorial;?>"><?php } ?></a>
															<?php } ?>
																<input type="hidden" name="name_Tutorial_Image_Old_<?php echo $i_val;?>" id="name_Tutorial_Image_Old_<?php echo $i_val;?>" value="<?php  if(isset($tutorial_phototutorial_photo) && $tutorial_photo != '') { echo $tutorial_photo; }  ?>" />
																<input type="hidden" name="Tutorial_Image_Old_<?php echo $i_val;?>_upload" id="Tutorial_Image_Old_<?php echo $i_val;?>_upload" value="Tutorial_Image_Old_<?php  if(isset($tutorial_photo) && $tutorial_photo != '') { echo $tutorial_photo; }  ?>" />
																<input type="hidden" name="delete_Tutorial_Image_Old_<?php echo $i_val;?>" id="delete_Tutorial_Image_Old_<?php echo $i_val;?>" value="<?php  if(isset($t_val->id) && $t_val->id != '') { echo $t_val->id; }  ?>" />
																<div  width="200" height="50" class="tutor_delete" id="Tutorial_Image_Old_<?php echo $i_val;?>_delete"><i class="fa fa-minus-circle fa-lg"></i></div>												
															</div>
										
														</div> 
													</div>
												<?php } 
													}?>
												<div class="upload-div unsortable" id="welcome_Tutorial_upload">
												<?php  ++$i_val;?>									
													<div class="upload-img" id="tutorial_input"> <input type="file"  class="tutorial_input_old" name="Tutorial_Image_Old_<?php echo $i_val;?>" id="Tutorial_Image_Old_<?php echo $i_val;?>" title="Tutorial Slider" onclick="" />
													<span><i class="fa fa-plus"></i>Add Photo</span>
													</div>					
												</div>
										
											<?php }else{ ?>
											<div class="upload-div" id="welcome_Tutorial_upload">
												<div class="upload-img" id="tutorial_input"> 
													<input type="file"  class="tutorial_input_old" name="Tutorial_Image_Old_1" id="Tutorial_Image_Old_1" title="Tutorial Slider" onclick=""/>
													<span><i class="fa fa-plus"></i>Add Photo</span>
												</div>					
											</div>
											<?php }?>
										</div>
									</div>
								</div>
							</form>
						<!--TUTORAIL SLIDER IMAGES END-->
						</div>
					</div>
					
			
					<!-- CATEGORY LIST -->
					<div class="Catego ry-manage sett-menu">
						<h2 class="tabsection" id="cat_list">Category Management <a href="CategoryManage" class="newWindow fright" title="Add Category" onclick="return showLoaderPopup();"><i class="fa fa-plus"></i> Add Category</a></h2>
						<div id="cat_list_block" style="display:<?php if(isset($_GET['msg']) && ($_GET['msg'] == 4 || $_GET['msg'] == 5 || $_GET['msg'] == 6)){ echo "block";} else {echo "none";}?>;">
						 
							<!-- form start -->
							<form action="CategoryList" class="l_form" name="CategoryListForm" id="CategoryListForm"  method="post">
								
								<div class="box-body table-responsive no-padding no-margin row" style="border:0;">
								<?php if(isset($msg_cate) && $msg_cate != '') { ?><div class="alert  alert-dismissable <?php  echo $class_cate;  ?> col-sm-5  col-xs-10" align="center"><span><i class="fa <?php  echo $class_icon_cate;  ?>"></i> <?php echo $msg_cate;  ?></span></div><?php } ?>
								  <table class="table table-hover" id="category_list" width="100%">
									<tr>
										<th align="center" width="3%" style="text-align:center">#</th>												
										<th width="30%">Category Name</th>
										<th width="25%">Category Icon</th>
										<th width="25%">Number of Products</th>
										<th></th>
									</tr>
	
									<?php 	
									$i=0 ; 
									require_once("CategoryListing.php");
									?>	
									
									</table>
									
									<input type="hidden" id="result_count" value="<?php echo $i;  ?>">
									<input type="hidden" id="total_count" value="<?php echo $tot_rec;  ?>">
									<div style="text-align:center;height: 50px" class="seemore-link" >
									<?php  
								if($i>2 && $i<$tot_rec){ ?>
										<div  id="seeMoreLink">
											<a href="javascript:void(0);" title="see more" onclick="seeMoreCategory()" >See more</a>
										</div>	
									<?php } ?>
									</div>	
								</div>
							</form>
						</div>
					</div>
					<!-- CATEGORY LIST END -->
					<div class="Locations sett-menu" style="display:none;">
						<h2 class="tabsection" id="location_list">Locations</h2>
						<div id="location_list_block" class="locations-list table-responsive" style="display:<?php if(isset($_GET['msg']) && ($_GET['msg'] == 8  || $_GET['msg'] == 10 || $_GET['msg'] == 11 )){ echo "block";} else {echo "none";}?>;">
						<?php if(isset($msg_loc) && $msg_loc != '') { ?><div class="alert  alert-dismissable <?php  echo $class_loc;  ?> col-sm-5  col-xs-10" align="center"><span><i class="fa <?php  echo $class_icon_loc;  ?>"></i> <?php echo $msg_loc;  ?></span></div><?php } ?>
					<?php if(is_array($LocationListResult)){ //echo "<pre>"; print_r($LocationListResult); echo "</pre>";?>
							<table class="table table-bordered">
								
								<tr>
								<?php $row = 1;	
									foreach($LocationListResult as $key=>$value){?>
									<td>
										<input type="radio" name="rGroup" value="1" id="<?php echo $value->id;?>" checked="checked" />
									<label class="radio" for="<?php echo $value->id;?>"><?php echo $value->Location;?><a href="LocationManage?more=1&editId=<?php if(isset($value->id) && $value->id!=''){ echo $value->id;}?>" title="Edit Location" class="newWindow locationPopup" style="padding-left:10px;"><i class="fa fa-pencil"></i></a></label>
									<span class="sub-name"><?php echo $value->Code;?></span>
									</td>
									<?php if($row!=0 && ($row%4) == 0){ //ECHO $key;?>
								</tr>
								<tr>
								<?php	} $row++;
									 }?>
									<td style="text-align: left">
										<a href="LocationManage?more=1" class="newWindow locationPopup add-more" title="Add more">Add more&nbsp;&nbsp;<i class="fa fa-plus"></i></a>
									</td>
								</tr>
							<?php	}  ?>
							</table>
						</div>
					</div>					
					<div class="Currency" style="display:none;">
						<h2 class="tabsection" id="currency_list">Currency<span>Tuplit is to work with multi-currency</span></h2>
						<div id="currency_list_block" class="currency-list table-responsive" style="display:<?php if(isset($_GET['msg']) &&  ( $_GET['msg'] == 9 ||  $_GET['msg'] == 12 ||  $_GET['msg'] == 13 )){ echo "block";} else {echo "none";}?>;">
						<?php if(isset($msg_cur) && $msg_cur!= '') { ?><div class="alert  alert-dismissable <?php  echo $class_cur;  ?> col-sm-5  col-xs-10" align="center"><span><i class="fa <?php  echo $class_icon_cur;  ?>"></i> <?php echo $msg_cur;  ?></span></div><?php } ?>
						
						<?php //echo "<pre>";print_r($CurrencyListResult);echo "</pre>";
							if(is_array($CurrencyListResult)){?>
							<table class="table table-bordered">
								<tr>
									<?php	$row = 1;
											foreach($CurrencyListResult as $key=>$value){?>
									<td>
										<?php echo $value->Code;?><a href="CurrencyManage?more=1&editId=<?php if(isset($value->id) && $value->id!=''){ echo $value->id;}?>" title="Edit Currency" class="newWindow currencyPopup" style="padding-left:10px;"><i class="fa fa-pencil"></i></a>
									</td>
									<?php 		if($row!=0 && ($row%2) == 0){//ECHO $key;?>
								</tr>
								<tr>
									<?php 		}$row++;
											} ?>
									<td>
										<a href="CurrencyManage?more=1" class="newWindow currencyPopup add-more" title="Add more">Add more&nbsp;&nbsp;<i class="fa fa-plus"></i></a>
									</td>
								</tr>
								<?php	}  ?>
							</table>
						</div>
					</div>
					
				</div><!-- /.box -->
			</div>
		</div><!-- /.row -->
	</section><!-- /.content -->
	
	
	
<?php commonFooter(); ?>
<style>
.loader-merchant{    
	background-color: rgba(0, 0, 0, 0.3);
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
<script type="text/javascript">
	$(".Category_image_pop_up").fancybox({title:true});
	$(".admin_logo_pop_up").fancybox({title:true});
	$('.fancybox').fancybox();	
	function showLoaderPopup(){
		$('.loader-merchant').show();
	}
	
$(document).ready(function() {
	$('.loader-merchant').hide();
	$.fancybox.showLoading();
	$(".newWindow").fancybox({
		scrolling	: 'none',			
		type		: 'iframe',
		width		: '400',
		position	:'fixed',
		maxWidth	: '100%',  // for respossive width set					
		fitToView	: false,
		title:true,
		'hideOnContentClick' : true,
		afterShow 	: function() {
			//location.reload();
			$('.loader-merchant').hide();
		},
		//width: '50%',
		//height:	'40%',
		//fitToView: false,
		  'onComplete' : function() {
				$('#fancybox-frame').load(function() { // wait for frame to load and then gets it's height
				$('#fancybox-content').height($(this).contents().find('body').height()+30);
				//$('.loader-merchant').hide();
		  });
		}
	});
	$(".locationPopup").fancybox({
		scrolling	: 'none',			
		type		: 'iframe',
		width		: '400',
		position	:'fixed',
		maxWidth	: '100%',  // for respossive width set					
		fitToView	: false,
		title:true,
		'hideOnContentClick' : true,
		'afterClose' : function() {
			 window.parent.location.href = "CommonSettings?msg=11";
			 //window.location.reload();
    	},
		  'onComplete' : function() {
				$('#fancybox-frame').load(function() { // wait for frame to load and then gets it's height
				$('#fancybox-content').height($(this).contents().find('body').height()+30);
		  });
		}
	});
	$(".currencyPopup").fancybox({
		scrolling	: 'none',			
		type		: 'iframe',
		width		: '400',
		position	:'fixed',
		maxWidth	: '100%',  // for respossive width set					
		fitToView	: false,
		title:true,
		'hideOnContentClick' : true,
		'afterClose' : function() {
			 window.parent.location.href = "CommonSettings?msg=13";
			 //window.location.reload();
    	},
		  'onComplete' : function() {
				$('#fancybox-frame').load(function() { // wait for frame to load and then gets it's height
				$('#fancybox-content').height($(this).contents().find('body').height()+30);
		  });
		}
	});
	$.fancybox.update();
	
	/*
	$(".addLocation").fancybox({
		title	:true,
		type	: 'iframe',
		width	: '80%',
		height	:	'70%',
		href 	: 'LocationManage?more=1'
	});
*/

});


</script>
</html>