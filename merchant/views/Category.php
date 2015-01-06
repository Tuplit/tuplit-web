<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();

$delShow	=	$cat_pro 	=	0;
if(isset($_SESSION['merchantDetailsInfo']) && is_array($_SESSION['merchantDetailsInfo'])){
	$merchantInfo  =	$_SESSION['merchantDetailsInfo'];
}
$categoryName	=	'';
$categoryId		=   '';
if(isset($_GET['edit']) && $_GET['edit'] != '') {
	$categoryName   = base64_decode($_GET['categoryName']);
	$cat_pro		= base64_decode($_GET['cat_pro']);
	$categoryId 	= $_GET['edit'];
}

commonHead();
?>

<body class="skin-blue fixed  popup_bg" onload="fieldfocus('CategoryName');" >
	<div class="popup_white category_list">
		<?php	if($delShow	 == 0){ ?>
			<div>			
			<div align="center" id="error_msg" style="margin-top:10px;margin-bottom:0;display:none;" class="alert alert-dismissable col-xs-10 col-xs-offset-1" ><i class="fa fa-check"></i> </div>
			</div>
			<form action="" name="add_category_form" id="add_category_form"  method="post">
				<div class="row popup" id="firstDiv">
					<div class="form-group col-xs-12" id="secondDiv" >
						<!-- <label class="col-xs-12">Category Name</label> -->
						<div class="col-xs-12 popup_title text-center"><h1><?php if(isset($categoryId) && !empty($categoryId)) echo "Edit"; else echo "Add"; ?> category</h1></div>
						<div class="col-xs-10 delete_cat box-center">
							<input class="form-control col-xs-12 " type="text" maxlength="30" placeholder="Category name" name="CategoryName" id="CategoryName" value="<?php if(isset($categoryName)&& $categoryName != '') echo ucfirst($categoryName);?>" />
							<!-- <span id="cat_error" class="error"></span> -->
							<input class="form-control" type="hidden" maxlength="30" name="CategoryId" id="CategoryId" value="<?php if(isset($categoryId)&& $categoryId != '') echo $categoryId;?>" />
						</div>
						<p class="help-block col-xs-12 text-center">Max. 30 characters</p>
						<span id="cat_error" class="error text-center"></span>
						<?php if(!empty($categoryId))  { ?>
							<div class="col-xs-12" align="center">
								<a class=" col-xs-12" title="Delete category" style="cursor:pointer;color:gray;" onclick="category_operation(3);"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;<b>DELETE CATEGORY</b></a>
							</div>
						<?php } ?>
					</div>	
					<div class="footer col-md-12 text-center clear"> 
						<a href="#" class="link col-xs-3 cancel" onclick="parent.jQuery.fancybox.close();">CANCEL</a>
						<?php if(isset($_GET['edit']) && $_GET['edit'] != '') { ?>
							<input type="button" name="merchant_category_submit" id="merchant_category_submit" value="Save" onclick="category_operation(2);" class="btn btn-success col-xs-9 ">
						<?php }	else {  ?>
							<input type="button" name="merchant_category_submit" id="merchant_category_submit" value="Add" onclick="category_operation(1);" class="btn btn-success col-xs-9 ">
						<?php } ?>
					</div>
				</div><!-- /row -->		
			</form>
		<?php } ?>
	</div>
	<?php commonFooter(); ?>
</html>
<script type="text/javascript">
	$('#CategoryName').keypress(function(event) {
	    var keycode = (event.keyCode ? event.keyCode : event.which);
	    if(keycode == '13') {
			<?php if(isset($_GET['edit']) && $_GET['edit'] != '') { ?>
				category_operation(2);
			<?php } else{ ?>
				category_operation(1);
			<?php } ?>
	         return false;
	    }
	});	
	
	function category_operation(val_type){
		//alert(val_type);
			var categoryName	=	$('#CategoryName').val();
			var categoryId		=	$('#CategoryId').val();
			//alert(categoryName);
			cat_pro				=	<?php echo $cat_pro; ?>;
			if((val_type == 3 && cat_pro == 0) || val_type == 4) {
				if(confirm('Are you sure to delete?')) {
				} else {
					return false;
				}
			}			
		    if(categoryName != '') {
				$.ajax({
			        type: "GET",
			        url: '<?php echo SITE_PATH;?>/AjaxWork',
			        data: "action=ADD_EDIT_DELETE_CATEGORY&CategoryName="+categoryName+"&CategoryId="+categoryId+"&type="+val_type,
			        success: function (result){
						//alert(result);
						$('#error_msg').html('');
						result 			= $.parseJSON(result);
						var details 	= result.message.split("###");
						var post_type  	= details[0];
						var types  	   	= details[2];
						var htmldata   	= details[3];
						if(htmldata == '') {
							if(post_type == 1){
								$('#error_msg').removeClass('alert alert-danger alert-dismissable col-xs-12');
								$('#error_msg').addClass('alert alert-success alert-dismissable col-xs-12');
								$('#error_msg').html('<i class="fa fa-check"></i>');
								$('#firstDiv').hide();
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
								$('#firstDiv').hide();
							}
							$('#error_msg').html(details[1]);
							$('#error_msg').show();
							$(".fancybox-inner").attr('style','height:180'); 
						} else {
							$(".fancybox-inner").attr('style','height:190'); 							
							$('#firstDiv').html(htmldata);						
						}
			        }			
			    });
			   return false;
		    }
			else{
				$('#cat_error').html('Category Name is required');
			}
	}
	
</script>