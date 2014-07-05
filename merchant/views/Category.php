<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();

$catId	=	'';
$delStat=	1;
$delShow	= 0;
if(isset($_SESSION['merchantDetailsInfo']) && is_array($_SESSION['merchantDetailsInfo'])){
	$merchantInfo  =	$_SESSION['merchantDetailsInfo'];
}
$categoryName	=	'';
$categoryId		=   '';
if(isset($_GET['edit']) && $_GET['edit'] != '') {
	$categoryName   = base64_decode($_GET['categoryName']);
	$categoryId 	= $_GET['edit'];
	if(isset($_GET['delStatus']) && $_GET['delStatus'] != '') {
		$delStat	=	1;
	}
	else{
		$delStat	=	0;
	}
}

commonHead();
?>

<body class="skin-blue fixed" onload="fieldfocus('CategoryName');">
		<?php if(isset($_GET['show']) && $_GET['show'] ==0) {
				}
				else top_header(); 
		?>
			<?php
			if($delShow	 == 0){ ?>			
			<div align="center" id="error_msg" class="alert alert-dismissable col-xs-12" style="display:none;"></div>
			<form action="" name="add_category_form" id="add_category_form"  method="post">
				<div class="row" id="firstDiv">
					<div class="form-group col-xs-12" id="secondDiv"   style="height:70px">
						<label class="col-xs-5 no-padding">Category Name</label>
						<p class="help-block col-xs-7 ">Max. 30 characters</p>
						<div class="col-xs-12 no-padding delete_cat">
							<input class="form-control" type="text" maxlength="30" name="CategoryName" id="CategoryName" value="<?php if(isset($categoryName)&& $categoryName != '') echo ucfirst($categoryName);?>" />
							<span id="cat_error" class="error"></span>
							<input class="form-control" type="hidden" maxlength="30" name="CategoryId" id="CategoryId" value="<?php if(isset($categoryId)&& $categoryId != '') echo $categoryId;?>" />
							<?php if(isset($delStat) && $delStat == 0 ) {?><a class="delete_category" title="Delete Category" onclick="category_operation(3);">Delete Category</a><?php } ?>

						</div>
					</div>	
					<div class="footer col-md-12 text-center clear"> 
						<a href="#" class="link" onclick="parent.jQuery.fancybox.close();">Cancel</a>&nbsp;&nbsp;&nbsp;
						<?php if(isset($_GET['edit']) && $_GET['edit'] != '') {?>
							<input type="button" name="merchant_category_submit" id="merchant_category_submit" value="Save" onclick="category_operation(2);" class="btn btn-success ">
						<?php }	else {  ?>
							<input type="button" name="merchant_category_submit" id="merchant_category_submit" value="Add" onclick="category_operation(1);" class="btn btn-success ">
						<?php } ?>
					</div>
				</div><!-- /row -->		
			</form>
		<?php if(isset($_GET['show']) && $_GET['show'] ==0) {
				}
				else footerLogin(); 
			}
		?>
	<?php commonFooter(); ?>
</html>
<script type="text/javascript">
	function category_operation(val_type){
			var categoryName	=	$('#CategoryName').val();
			var categoryId		=	$('#CategoryId').val();
			
		    if(categoryName != '') {
				$.ajax({
			        type: "GET",
			        url: '<?php echo SITE_PATH;?>/AjaxWork',
			        data: 'action=ADD_EDIT_DELETE_CATEGORY&CategoryName='+categoryName+'&CategoryId='+categoryId+'&type='+val_type,
			        success: function (result){
						$('#error_msg').html('');
						result 			= $.parseJSON(result);
						var details 	= result.message.split("###");
						var post_type  	= details[0];
						var types  	   	= details[2];
						if(post_type == 1){
							$('#error_msg').removeClass('alert alert-danger alert-dismissable col-xs-12');
							$('#error_msg').addClass('alert alert-success alert-dismissable col-xs-12');
							$('#error_msg').html('<i class="fa fa-check"></i>');
						}
						else if(post_type == 2 && types == 3){
							$('#error_msg').removeClass('alert alert-success alert-dismissable col-xs-12');
							$('#error_msg').addClass('alert alert-danger alert-dismissable col-xs-12');
							$('#error_msg').html('<i class="fa fa-warning"></i>');
							$('#firstDiv').hide();
							
						}
						else{
							$('#error_msg').removeClass('alert alert-success alert-dismissable col-xs-12');
							$('#error_msg').addClass('alert alert-danger alert-dismissable col-xs-12');
							$('#error_msg').html('<i class="fa fa-warning"></i>');
						}
						$('#error_msg').html(details[1]);
						$('#error_msg').show();
						$(".fancybox-inner").attr('style','height:180');
						
			        }			
			    });
			   return false;
		    }
			else{
				$('#cat_error').html('Category Name is required');
			}
	}

</script>