<?php 
require_once('includes/CommonIncludes.php');
//require_once('includes/php_image_magician.php');
admin_login_check();
commonHead();
require_once('controllers/ManagementController.php');
$managementObj   =   new ManagementController();
//require_once('controllers/AdminController.php');
//$adminLoginObj   =   new AdminController();
require_once("includes/phmagick.php");
$CategoryName = $CategoryImage = $ImagePath  =  $class = $class_icon  = $msg = $ExistCondition = $error_msg = '';
$field_focus = 'CategoryName';
$display      = 'none';
$categoryName_exists  = 0;
$photoUpdateString	= '';
unset($_SESSION['orderby']);
unset($_SESSION['ordertype']);
if(isset($_GET['editId']) && $_GET['editId'] != '' ){
	$condition       	= " id = ".$_GET['editId']." and Status in (1,2)";
	$field				=	' CategoryName,CategoryIcon,DateCreated';
	$categoryDetailsResult  = $managementObj->selectCategoryDetails($field,$condition);
	if(isset($categoryDetailsResult) && is_array($categoryDetailsResult) && count($categoryDetailsResult) > 0){
		$CategoryName 			= $categoryDetailsResult[0]->CategoryName;
		if(isset($categoryDetailsResult[0]->CategoryIcon) && $categoryDetailsResult[0]->CategoryIcon != ''){
			$CategoryImageName = $categoryDetailsResult[0]->CategoryIcon;
			$OriginalImagePath =  '';
			if(SERVER){
				if(image_exists(3,$CategoryImageName))
					$OriginalImagePath = CATEGORY_IMAGE_PATH.$CategoryImageName;
			}
			else{
				if(file_exists(CATEGORY_IMAGE_PATH_REL.$CategoryImageName))
					$OriginalImagePath = CATEGORY_IMAGE_PATH.$CategoryImageName;
			}
		}
	}
}
if(isset($_POST['submit']) && $_POST['submit'] != ''){
	$_POST          =   unEscapeSpecialCharacters($_POST);
   	$_POST          =   escapeSpecialCharacters($_POST);
	$Category_exists  = 0;
	if(isset($_POST['CategoryName']) )
		$CategoryName 			= $_POST['CategoryName'];
	if (isset($_POST['category_photo_upload']) && !empty($_POST['category_photo_upload'])) {
		$CategoryImageName = $_POST['category_photo_upload'];
		$CategoryImagePath = TEMP_USER_IMAGE_PATH_REL.$CategoryImageName;
	}
	if($CategoryName != '')
		$ExistCondition .= "  ( CategoryName = '".$CategoryName."' ";
	if($_POST['submit'] == 'Save')
		$id_exists = ") and id != '".$_POST['category_id']."' and Status in (1,2) ";
	else
		$id_exists = " ) and Status in (1,2) ";
	$field = " * ";	
	$ExistCondition .= $id_exists;
	$alreadyExist   = $managementObj->selectCategoryDetails($field,$ExistCondition);	
	if(isset($alreadyExist) && is_array($alreadyExist) && count($alreadyExist) > 0){
		$Category_exists  = 1;
	}
	if($Category_exists != 1){
		if($_POST['submit'] == 'Save'){		
			if(isset($_POST['category_id']) && $_POST['category_id'] != ''){
				$fields    = "CategoryName            	= '".$CategoryName."'";
				$condition = ' id = '.$_POST['category_id'];
				$managementObj->updatecategoryDetails($fields,$condition);			
				$insert_id = $_POST['category_id'];
				if (isset($_POST['category_photo_upload']) && !empty($_POST['category_photo_upload'])) {
					if(isset($_POST['name_category_photo']) && $_POST['name_category_photo'] != ''){
						$ImagePath = $_POST['name_category_photo'];
						if(!SERVER){
							if(file_exists(CATEGORY_IMAGE_PATH_REL.$ImagePath))
								unlink(CATEGORY_IMAGE_PATH_REL . $ImagePath);
						}
					}
				}
			$msg = 2;
			}
		}
		if($_POST['submit'] == 'Add'){
			$insert_id   		    = $managementObj->insertcategoryDetails($_POST);
			$msg = 1;
		}
		$date_now = date('Y-m-d H:i:s');
		if(isset($insert_id) && $insert_id != '' ){
			if (isset($_POST['category_photo_upload']) && !empty($_POST['category_photo_upload'])) {
				$imageName 				= $insert_id . '_' . strtotime($date_now) . '.png';
			   	$tempImagePath 			= TEMP_USER_IMAGE_PATH_REL . $_POST['category_photo_upload'];
				$imagePath 				= UPLOAD_CATEGORY_PATH_REL . $imageName;
				$oldcategoryName			= $_POST['name_category_photo'];
				if ( !file_exists(UPLOAD_CATEGORY_PATH_REL) ){
			  		mkdir (UPLOAD_CATEGORY_PATH_REL, 0777);
				}
				copy($tempImagePath,$imagePath);
				if (SERVER){
					if($oldcategoryName!='') {
						if(image_exists(3,$oldcategoryName)) {
							deleteImages(3,$oldcategoryName);
						}
					}
					uploadImageToS3($imagePath,3,$imageName);
					unlink($imagePath);
				}
				$photoUpdateString	.= " CategoryIcon = '" . $imageName . "'";
				unlink(TEMP_USER_IMAGE_PATH_REL.$_POST['category_photo_upload']);
			}
			if($photoUpdateString!='')
			{
				$condition 			= "id = ".$insert_id;
				$managementObj->updatecategoryDetails($photoUpdateString,$condition);
			}			
		}
		header("location:CategoryList?msg=".$msg);
	}
	else {
		if($Category_exists == 1){
			$error_msg   = "Category already exists";
			$field_focus = 'CategoryName';
		}
		$display = "block";
		$class   = "alert-danger";
		$class_icon          = "fa-warning";
	}
}
		
?>
<body class="skin-blue" onload="return fieldfocus('<?php echo $field_focus; ?>');">
	<?php top_header(); ?>
	<!-- Content Header (Page header) -->
	<section class="content-header no-padding">
		<div class="col-xs-12"> 
			<h1><i class="fa <?php if(isset($_GET['editId']) && $_GET['editId'] != '' ) echo "fa-edit "; else echo 'fa-plus-circle ';?>"></i> <?php if(isset($_GET['editId']) && $_GET['editId'] != '' ) echo "Edit "; else echo 'Add ';?>Category</h1>
		</div>
	</section>
	 <!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-md-12 col-lg-6"> 
			<div class="box box-primary"> 
		<!-- left column -->
			<form name="add_category_form" id="add_category_form" action="" method="post">
					<?php if(isset($error_msg) && $error_msg != '')  { ?> <div class="alert <?php echo $class;  ?> alert-dismissable col-sm-5 col-xs-11"><i class="fa <?php echo $class_icon ;  ?>"></i>  <?php echo $error_msg;  ?></div> <?php } ?>
					<input type="Hidden" name="category_id" id="category_id" value="<?php if(isset($_GET['editId']) && $_GET['editId'] != '' ) echo $_GET['editId'];?>">
					<div class="form-group col-md-12">
						<label>Category Name</label>
						<input type="text" class="form-control" id="CategoryName" name="CategoryName" maxlength="100" value="<?php if(isset($CategoryName) && $CategoryName != '') echo $CategoryName;  ?>" >
					</div>
					<?php if(!isset($_GET['editId'])) { ?>
					<div class="form-group col-md-12">
						<label>Category Icon</label>
						<div class="row">
						    <div class="col-md-6 col-sm-5"> 
								<input type="file"  name="category_photo" id="category_photo" title="category Icon" onchange="return ajaxAdminFileUploadProcess('category_photo');"  /> 
								<p class="help-block">(Min dimension 80x80, Max dimension 100x100)</p>
								<span class="error" for="empty_category_photo" generated="true" style="display: none">category Image is required</span>
							</div><!-- imageValidation('empty_cat_sel_photo'); -->
						    <div class="col-md-6 col-sm-6">								
						         <div id="category_photo_img">									
									<?php  
									if(isset($OriginalImagePath) && $OriginalImagePath != ''){  ?>
						                 <a <?php if(isset($OriginalImagePath) && $OriginalImagePath != '') { ?> href="<?php echo $OriginalImagePath; ?>" class="category_photo_pop_up"<?php } else { ?> href="Javascript:void(0);"<?php } ?> title="Click here" alt="Click here" ><img class="img_border" src="<?php  echo $OriginalImagePath;  ?>" width="75" height="75" alt="Image" /></a>
									<?php  }  ?>
						         </div>
						     </div>
						 </div>
					<?php  if(isset($_POST['category_photo_upload']) && $_POST['category_photo_upload'] != ''){  ?><input type="Hidden" name="category_photo_upload" id="category_photo_upload" value="<?php  echo $_POST['category_photo_upload'];  ?>"><?php  }  ?>
					<input type="Hidden" name="empty_category_photo" id="empty_category_photo" value="<?php  if(isset($CategoryImageName) && $CategoryImageName != '') { echo $CategoryImageName; }  ?>" />
					<input type="Hidden" name="name_category_photo" id="name_category_photo" value="<?php  if(isset($CategoryImageName) && $CategoryImageName != '') { echo $CategoryImageName; }  ?>" />
					</div>	
					<?php }
							else{
					?>
					<div class="form-group col-md-12 ">
						<label>Category Icon</label>
						<div class="row">
							<div class="col-md-7"> 
								<input type="file"  name="category_photo" id="category_photo" title="category Photo" onclick="" onchange="return ajaxAdminFileUploadProcess('category_photo');"  /> 
								<p class="help-block">(Min dimension 80x80, Max dimension 100x100)</p>
								<span class="error" for="empty_category_photo" generated="true" style="display: none">category Image is required</span>
							</div><!-- imageValidation('empty_cat_sel_photo'); -->
						
						  <div class="col-md-5 " >
						      <div id="category_photo_img">
								<?php  
								if(isset($OriginalImagePath) && $OriginalImagePath != ''){  ?>
						              <a <?php if(isset($OriginalImagePath) && $OriginalImagePath != '') { ?> href="<?php echo $OriginalImagePath; ?>" class="category_photo_pop_up"<?php } else { ?> href="Javascript:void(0);"<?php } ?> title="Click here" alt="Click here" ><img  class="img_border" src="<?php  echo $OriginalImagePath;  ?>" width="75" height="75" alt="Image" /></a>
								<?php  }  ?>
						      </div>
						  </div>
						<?php  if(isset($_POST['category_photo_upload']) && $_POST['category_photo_upload'] != ''){  ?><input type="Hidden" name="category_photo_upload" id="category_photo_upload" value="<?php  echo $_POST['category_photo_upload'];  ?>"><?php  }  ?>
						<input type="Hidden" name="empty_category_photo" id="empty_category_photo" value="<?php  if(isset($CategoryImageName) && $CategoryImageName != '') { echo $CategoryImageName; }  ?>" />
						<input type="Hidden" name="name_category_photo" id="name_category_photo" value="<?php  if(isset($CategoryImageName) && $CategoryImageName != '') { echo $CategoryImageName; }  ?>" />
						</div>
					</div>						
					<?php } ?>
					
					
					<div class="box-footer col-md-12" align="center">
					<?php if(isset($_GET['editId']) && $_GET['editId'] != ''){ ?>
					<input type="submit" class="btn btn-success" name="submit" id="submit" value="Save" title="Save" alt="Save">&nbsp;&nbsp;&nbsp;&nbsp;
					<?php } else { ?>
					<input type="submit" class="btn btn-success" name="submit" id="submit" value="Add" title="Add" alt="Add">&nbsp;&nbsp;&nbsp;&nbsp;
					<?php } ?>
					<?php $href_page = "CategoryList"; 	?>		
					<a href="<?php if(isset($href_page) && $href_page != '' ) echo $href_page; else echo 'CategoryList';?>" class="btn btn-default" name="Back" id="Back" title="Back" alt="Back" >Back </a>	
					<!-- <a href="CategoryList"  class="submit" name="Back" id="Back"  value="Back" title="Back" alt="Back">Back </a> -->
					</div>
					</form>	
				</div><!-- /.box -->
				</div>
			
		</div><!-- /.row -->
	</section><!-- /.content -->	
						  	
<?php commonFooter(); ?>
<script type="text/javascript">
$(".category_photo_pop_up").fancybox({title:true});
</script>
</html>