<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();

$catId	=	'';
if(isset($_SESSION['merchantDetailsInfo']) && is_array($_SESSION['merchantDetailsInfo'])){
	$merchantInfo  =	$_SESSION['merchantDetailsInfo'];
}
$categoryName	=	'';
$categoryId		=   '';
if(isset($_GET['edit']) && $_GET['edit'] != '') {
	$categoryName   = base64_decode($_GET['categoryName']);
	$categoryId 	= $_GET['edit'];
}
if((isset($_POST['merchant_category_submit']) && $_POST['merchant_category_submit'] == 'Add') || (isset($_POST['merchant_category_submit']) && $_POST['merchant_category_submit'] == 'Save'))
{	
	$categoryName 	= $_POST['CategoryName'];
	$categoryId 	= $_POST['CategoryId'];
	$data	=	array(
					'CategoryName' 			=> $_POST['CategoryName'],
					'CategoryId'			=> $categoryId
				);
	$url			=	WEB_SERVICE.'v1/categories/products';
	if($_POST['merchant_category_submit'] == 'Add'){
		$method			=	'POST';		
		$curlResponse	=	curlRequest($url,$method,$data, $_SESSION['merchantInfo']['AccessToken']);	
	}
	else{
		$method			=	'PUT';		
		$curlResponse	=	curlRequest($url,$method,json_encode($data), $_SESSION['merchantInfo']['AccessToken']);	
	}
	
	if(isset($curlResponse) && is_array($curlResponse) && $curlResponse['meta']['code'] == 201) {
		$successMessage = $curlResponse['notifications'][0];
	} else if(isset($curlResponse['meta']['errorMessage']) && $curlResponse['meta']['errorMessage'] != '') {
		$errorMessage	=	$curlResponse['meta']['errorMessage'];
	} else {
		$errorMessage 	= 	"Bad Request";
	}
}


if(isset($errorMessage) && $errorMessage != ''){
	$msg			=	$errorMessage;
	$display 		= 	"block";
	$class   		= 	"alert-danger";
	$class_icon 	= 	"fa-warning";
	$errorMessage 	= 	'';
}else if(isset($successMessage) && $successMessage != ''){
	$msg			=	$successMessage;
	$display		=	"block";
	$class 			= 	"alert-success";
	$class_icon 	= 	"fa-check";
	$successMessage = 	'';
}
commonHead();
?>

<body class="skin-blue fixed" onload="fieldfocus('CategoryName');">
		<?php if(isset($_GET['show']) && $_GET['show'] ==0) {
				}
				else top_header(); 
		?>
			<?php if(isset($msg) && $msg != '') { ?>
				  <div align="center" class="alert <?php  echo $class;  ?> alert-dismissable col-xs-12"><i class="fa <?php  echo $class_icon;  ?>"></i>  <?php echo $msg; ?></div>
			<?php } ?>			
			<form action="" name="add_category_form" id="add_category_form"  method="post">
				<div class="row">
					<div class="form-group col-xs-12" style="height:70px">
						<label class="col-xs-5 no-padding">Category Name</label>
						<p class="help-block col-xs-7 ">Max. 30 characters</p>
						<div class="col-xs-12 no-padding">
							<input class="form-control" type="text" maxlength="30" name="CategoryName" id="CategoryName" value="<?php if(isset($categoryName)&& $categoryName != '') echo $categoryName;?>" />
							<input class="form-control" type="hidden" maxlength="30" name="CategoryId" id="CategoryId" value="<?php if(isset($categoryId)&& $categoryId != '') echo $categoryId;?>" />
						</div>
					</div>	
					<div class="footer col-md-12 text-center clear"> 
						<a href="#" class="link" onclick="parent.jQuery.fancybox.close();">Cancel</a>&nbsp;&nbsp;&nbsp;
						<?php if(isset($_GET['edit']) && $_GET['edit'] != '') {?>
							<input type="submit" name="merchant_category_submit" id="merchant_category_submit" value="Save" class="btn btn-success ">
						<?php }	else {  ?>
							<input type="submit" name="merchant_category_submit" id="merchant_category_submit" value="Add" class="btn btn-success ">
						<?php } ?>
					</div>
				</div><!-- /row -->		
			</form>
		<?php if(isset($_GET['show']) && $_GET['show'] ==0) {
				}
				else footerLogin(); 
		?>
	<?php commonFooter(); ?>
</html>
