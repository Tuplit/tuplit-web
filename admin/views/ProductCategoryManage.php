<?php 
require_once('includes/CommonIncludes.php');
//require_once('includes/php_image_magician.php');
admin_login_check();
commonHead();
require_once('controllers/ManagementController.php');
$managementObj   =   new ManagementController();
require_once('controllers/AdminController.php');
$adminLoginObj   =   new AdminController();
require_once("includes/phmagick.php");
$CategoryName = $CategoryImage = $ImagePath  =  $class = $class_icon  = $msg = $ExistCondition = $error_msg = '';
$field_focus = 'CategoryName';
$display      = 'none';
$categoryName_exists  = 0;
$photoUpdateString	= $MerchantId = '';
unset($_SESSION['orderby']);
unset($_SESSION['ordertype']);
if(isset($_GET['type']) && $_GET['type'] == 2){
	if(isset($_GET['viewId']) && $_GET['viewId'] != '' ){
		$condition       = " id = ".$_GET['viewId']." and Status in (1,2) LIMIT 1 ";	
		$field				=	' id,CategoryName,DateCreated';
		$categoryDetailsResult  = $managementObj->selectProductCategoryDetails($field,$condition);
		if(isset($categoryDetailsResult) && is_array($categoryDetailsResult) && count($categoryDetailsResult) > 0){
			$_GET['viewId']				= $categoryDetailsResult[0]->id;
			$CategoryName  				= $categoryDetailsResult[0]->CategoryName;
			$dateCreated    			= $categoryDetailsResult[0]->DateCreated;
		}
	}
	if(isset($_GET['show'])) 
		$show	=	 $_GET['show']; 
	else
		 $show	= 1;
}
else{
	if(isset($_GET['editId']) && $_GET['editId'] != '' ){
		$condition       	= " id = ".$_GET['editId']." and Status in (1,2)";
		$field				=	' CategoryName,DateCreated,fkMerchantId';
		$categoryDetailsResult  = $managementObj->selectProductCategoryDetails($field,$condition);
		if(isset($categoryDetailsResult) && is_array($categoryDetailsResult) && count($categoryDetailsResult) > 0){
			$CategoryName 			= $categoryDetailsResult[0]->CategoryName;
			$MerchantId 			= $categoryDetailsResult[0]->fkMerchantId;
		}
	}
	if(isset($_POST['submit']) && $_POST['submit'] != ''){
		$_POST          =   unEscapeSpecialCharacters($_POST);
	   	$_POST          =   escapeSpecialCharacters($_POST);
		$Category_exists  = 0;
		if(isset($_POST['CategoryName']) )
			$CategoryName 			= $_POST['CategoryName'];
		if($CategoryName != '')
			$ExistCondition .= "  ( CategoryName = '".trim($CategoryName)."' ";
		if($_POST['submit'] == 'Save'){
			$id_exists = ") and id != '".$_POST['category_id']."' and fkMerchantId IN(0,".$MerchantId .")and Status in (1,2) ";
		}
		else
			$id_exists = " ) and fkMerchantId = 0 and Status in (1,2) ";
		$field = " * ";	
		$ExistCondition .= $id_exists;
		$alreadyExist   = $managementObj->selectProductCategoryDetails($field,$ExistCondition);	
		if(isset($alreadyExist) && is_array($alreadyExist) && count($alreadyExist) > 0){
			$Category_exists  = 1;
		}
		if($Category_exists != 1){
			if($_POST['submit'] == 'Save'){		
				if(isset($_POST['category_id']) && $_POST['category_id'] != ''){
					$fields    = "CategoryName            	= '".$CategoryName."'";
					$condition = ' id = '.$_POST['category_id'];
					$managementObj->updateProductCategoryDetails($fields,$condition);			
				$msg = 2;
				}
			}
			if($_POST['submit'] == 'Add'){
				$insert_id   		    = $managementObj->insertProductCategoryDetails($_POST);
				$msg = 1;
			}?>
			
			<script type="text/javascript">
				window.parent.location.href = 'ProductCategoryList?msg=<?php echo $msg; ?>&show=<?php if(isset($_GET['show'])) echo $_GET['show']; else echo '1' ;?>';
			</script>
		<?php }
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
			
}
?>

<body class="skin-blue" onload="fieldfocus('CategoryName');">
<?php if(isset($_GET['type']) && $_GET['type'] == 1){?>
			<div class="col-xs-12 no-padding"> 
				<h1><i class="fa <?php if(isset($_GET['editId']) && $_GET['editId'] != '' ) echo "fa-edit "; else echo 'fa-plus-circle ';?>"></i> <?php if(isset($_GET['editId']) && $_GET['editId'] != '' ) echo "Edit "; else echo 'Add ';?>Product Category</h1>
			</div>
			<?php if(isset($error_msg) && $error_msg != '') { ?>
				 <?php if(isset($error_msg) && $error_msg != '')  { ?> <div class="alert <?php echo $class;  ?> alert-dismissable col-sm-5"><i class="fa <?php echo $class_icon ;  ?>"></i>  <?php echo $error_msg;  ?></div> <?php } ?>
			<?php } ?>			
			<form action="" name="add_product_category_form" id="add_product_category_form"  method="post">
				<div class="row">
					<input type="Hidden" name="category_id" id="category_id" value="<?php if(isset($_GET['editId']) && $_GET['editId'] != '' ) echo $_GET['editId'];?>">
					<input type="Hidden" name="merchant_id" id="merchant_id" value="<?php if(isset($MerchantId) && $MerchantId != '' ) echo $MerchantId;?>">
					<div class="form-group col-md-12" style="height:80px">
						<label class="col-sm-6 no-padding">Product Category Name</label>
						<div class="col-sm-12 no-padding">
							<input type="text" class="form-control" id="CategoryName" name="CategoryName" maxlength="100" value="<?php if(isset($CategoryName) && $CategoryName != '') echo $CategoryName;  ?>" >
						</div>
					</div>	
					<div class="footer col-md-12 text-center"> 
						
						<?php if(isset($_GET['editId']) && $_GET['editId'] != ''){ ?>
						<input type="submit" class="btn btn-success" name="submit" id="submit" value="Save" title="Save" alt="Save">&nbsp;&nbsp;&nbsp;&nbsp;
						<?php } else { ?>
						<input type="submit" class="btn btn-success" name="submit" id="submit" value="Add" title="Add" alt="Add">&nbsp;&nbsp;&nbsp;&nbsp;
						<?php } ?>
						
						<a href="#" class="btn btn-default" onclick="parent.jQuery.fancybox.close();" name="Back" id="Back" title="Back" alt="Back" >Back </a>	
					</div>
				</div><!-- /row -->		
			</form>
	<?php } else  if(isset($_GET['type']) && $_GET['type'] == 2){?>
	<div class="col-xs-12"> 
		<h1><i class="fa fa-search"></i> View Category</h1>
	</div>
	<div class="row">
		<div class="form-group col-md-12">
			<label class="col-sm-4" >Category Name</label>
			<div  class="col-sm-8">
			<?php if(isset($CategoryName) && $CategoryName != '') echo ucfirst($CategoryName); else echo '-'; ?>	</div>
		</div>	
		<div class="form-group col-md-12">
			<label class="col-sm-4" >Created Date</label>
			<div  class="col-sm-8">
			<?php if(isset($dateCreated) && $dateCreated != '' ) echo date('m/d/Y',strtotime($dateCreated)); else echo '-'; ?></div>
		</div>	
		
		
		<div class="box-footer col-sm-12" align="center">
				<?php 
					$href_page = "ProductCategoryList?show=".$show."";
				?>
				<a href="ProductCategoryManage?type=1&editId=<?php if(isset($_GET['viewId']) && $_GET['viewId'] != '') echo $_GET['viewId']; ?>&show=<?php echo $show;?>" title="" id="edit" alt="Edit" class="ProductCategory btn btn-success">Edit</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<a href="#" onclick="parent.jQuery.fancybox.close();"  class="btn btn-default" name="Back" id="Back" title="Back" alt="Back" >Back </a>
		</div>
	</div>		
	<?php } ?>
	<?php commonFooter(); ?>
</html>
