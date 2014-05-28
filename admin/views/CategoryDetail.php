<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
commonHead();
require_once('controllers/ManagementController.php');
$managementObj   =   new ManagementController();
$original_image_path =  $original_cover_image_path = $actualPassword = '';
unset($_SESSION['orderby']);
unset($_SESSION['ordertype']);
if(isset($_GET['viewId']) && $_GET['viewId'] != '' ){
	$condition       = " id = ".$_GET['viewId']." and Status in (1,2) LIMIT 1 ";	
	$field				=	' id,CategoryName,CategoryIcon,DateCreated';
	$categoryDetailsResult  = $managementObj->selectCategoryDetails($field,$condition);
	if(isset($categoryDetailsResult) && is_array($categoryDetailsResult) && count($categoryDetailsResult) > 0){
		$_GET['viewId']				= $categoryDetailsResult[0]->id;
		$CategoryName  				= $categoryDetailsResult[0]->CategoryName;
		$dateCreated    			= $categoryDetailsResult[0]->DateCreated;
		$original_image_path = '';
		$noimage = SITE_PATH.'/admin/webresources/images/no_category.jpeg';
 		if(isset($categoryDetailsResult[0]->CategoryIcon) && $categoryDetailsResult[0]->CategoryIcon != ''){
			$category_image = $categoryDetailsResult[0]->CategoryIcon;
			if (!SERVER){
				if(file_exists(CATEGORY_IMAGE_PATH_REL.$category_image))
					$original_image_path = CATEGORY_IMAGE_PATH.$category_image;
			}
			else{
				if(image_exists(3,$category_image))
					$original_image_path = CATEGORY_IMAGE_PATH.$category_image;
			}
		}
	}
}
?>
<body class="skin-blue">
	<?php top_header(); ?>
	
	<!-- Content Header (Page header) -->
	<section class="content-header no-padding">
		<div class="col-xs-12"> 
			<h1><i class="fa fa-search"></i> View Category</h1>
		</div>
	</section>
	 <!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-md-6 view-page"> 
				<div class="box box-primary"> 
			
				<div class="form-group col-sm-12 row">
					<label class="col-sm-4" >Category Name</label>
					<div  class="col-sm-8">
					<?php if(isset($CategoryName) && $CategoryName != '') echo ucfirst($CategoryName); else echo '-'; ?>	</div>
				</div>	
				<div class="form-group col-sm-12 row">
					<label class="col-sm-4" >Created Date</label>
					<div  class="col-sm-8">
					<?php if(isset($dateCreated) && $dateCreated != '' ) echo date('m/d/Y',strtotime($dateCreated)); else echo '-'; ?></div>
				</div>	
				<div class="form-group col-sm-12 row">
					<label class="col-sm-4" >Category Icon</label>
					<div  class="col-sm-8">
					<a <?php if(isset($original_image_path) && $original_image_path != '') {  ?> href="<?php echo $original_image_path; ?>" class="category_photo_pop_up"<?php } else { ?> href="Javascript:void(0);"<?php } ?> title="Click here" alt="Click here" ><?php if(isset($original_image_path) && $original_image_path != '') { ?> <img width="75" height="75" src="<?php echo $original_image_path;?>"><?php } else {?><img width="75" height="75" src="<?php echo $noimage; ?>"<?php }?></a></div>
				</div>
				
				<div class="box-footer col-sm-12" align="center">
						<?php 
							$href_page = "CategoryList";
						?>	
						<a href="CategoryManage?editId=<?php if(isset($_GET['viewId']) && $_GET['viewId'] != '') echo $_GET['viewId']; ?>" title="Edit" alt="Edit" class="btn btn-success">Edit</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<a href="<?php if(isset($href_page) && $href_page != '' ) echo $href_page; else echo 'UserList';?>" class="btn btn-default" name="Back" id="Back" title="Back" alt="Back" >Back </a>
					
				</div>
			</div>		
			</div>		
		</div><!-- /.row -->
	</section><!-- /.content -->				  	
<?php commonFooter(); ?>
<script type="text/javascript">	
	$(document).ready(function() {		
		
		$(".category_photo_pop_up").fancybox({title:true});
	});	
	
</script>
<script>
$(document).ready(function() {		
	$(".pop_up").colorbox(
		{
			iframe:true,
			width:"30%", 
			height:"60%",
			title:true,
			opacity:0.7
	});
});
</script>
</html>
