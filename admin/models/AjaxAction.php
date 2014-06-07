
<?php 
ob_start();
require_once('../includes/AdminCommonIncludes.php');

if(isset($_GET['action']) && $_GET['action'] == 'SET_ORDERING_WEBSERVICE'){
	$order_value = 0;
	require_once('../controllers/ServiceController.php');
	$serviceObj   =   new ServiceController();
	$ExistCondition = '';
	$service_exists = '0';
	if(isset($_GET['orderValue']) && $_GET['orderValue'] != '')
		$order_value = $_GET['orderValue'];
	if(isset($_GET['serviceId']) && $_GET['serviceId'] != '')
		$service_id = $_GET['serviceId'];
	if($order_value != '' && $service_id != '' )
		$ExistCondition = " and Ordering = ".$order_value." and id != ".$service_id." and Ordering!='0' ";		
	$field = " Ordering ";	
	$alreadyExist   = $serviceObj->selectServiceDetails($field,$ExistCondition);	
	if(isset($alreadyExist) && is_array($alreadyExist) && count($alreadyExist) > 0){
			$service_exists = 1;
	}	
	if($service_exists != '1'){
		if($order_value != '' && $service_id != '' ){
			$update_string 	    = " Ordering = ".$order_value;
			$condition 		    = " id = ".$service_id;
			$OrderingResult     = $serviceObj->updateServiceDetails($update_string,$condition);
		}
	}
	echo $service_exists;
}
if(isset($_POST['action']) && $_POST['action'] == 'DELETE_SLIDER'){
	require_once('../controllers/UserController.php');
	$userObj   =   new UserController();
	if(isset($_POST['idValue']) && $_POST['idValue'] != '')
	$delete_id		=	$_POST['idValue'];
	$deleteSlider   = 	$userObj->deleteSlider($delete_id);	
	echo 1;
}
if(isset($_GET['action']) && $_GET['action'] == 'SET_ORDERING_SLIDER'){
	$order_value = 0;
	require_once('../controllers/UserController.php');
	$userObj   =   new UserController();
	$ExistCondition = $slider_id = '';
	$service_exists = 0;
	if(isset($_GET['orderValue']) && $_GET['orderValue'] != '')
		$order_value = $_GET['orderValue'];
	if(isset($_GET['SliderId']) && $_GET['SliderId'] != '')
		$slider_id = $_GET['SliderId'];
	if(isset($_GET['slide_type']) && $_GET['slide_type'] != '')
		$slider_type = $_GET['slide_type'];
	if($order_value != '' && $slider_id != '' && $slider_type != '')
		$ExistCondition = "  `Order`  = ".$order_value." and id != '".$slider_id."' and `Order` !='0' and Status = 1 and SliderType = ".$slider_type." ";		
	$field = " `Order`,id ";	
	$alreadyExist   = $userObj->getSliderImageDetails($field,$ExistCondition);	
	if(isset($alreadyExist) && is_array($alreadyExist) && count($alreadyExist) > 0){
			$service_exists = 1;
	}	
	if($service_exists != '1'){
		if($order_value != '' && $slider_id != '' ){
			$update_string 	    = " `Order`= ".$order_value;
			$condition 		    = " id = ".$slider_id;
			$OrderingResult     = $userObj->updateSliderDetails($update_string,$condition);
		}
	}
	echo $service_exists;
}
if(isset($_POST['action']) && ($_POST['action']=='GET_PRODUCT_CATEGORY')){
	$sub_val	 = '';
	require_once('../controllers/ManagementController.php');
	$managementObj   =   new ManagementController();
	$merchant_id		 	 	= 	$_POST['m_id'];
	if($merchant_id != ''){
		$condition       	= " fkMerchantId IN( ".$merchant_id.",0) and Status =1 ORDER BY fkMerchantId,CategoryName asc";
		$field				=	' id,CategoryName';
		$productCategories  = $managementObj->selectProductCategoryDetails($field,$condition);
	}
	if(isset($productCategories) && is_array($productCategories) && count($productCategories) > 0)
	{
?>
	<select class="form-control " name="Category">
		<option value="" >Select</option>								
		<?php if(isset($productCategories) && !empty($productCategories)) {
			foreach($productCategories as $key=>$val) {								
		?>
		<option value="<?php echo $val->id;?>" <?php //if(isset($CategoryId) && $val->id == $CategoryId) echo "selected"; ?>><?php echo ucfirst($val->CategoryName);?></option>
		<?php } } ?>								
	</select>
<?php } 
}
?>