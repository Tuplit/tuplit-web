<?php 
$start_val = 0;
if(isset($_GET['start']) && $_GET['start'] !=''){
	$start_val = $_GET['start'];
}
if(isset($_GET['action']) && $_GET['action'] == 'GET_MORE_CUSTOMERS'){
	require_once('includes/CommonIncludes.php');
	admin_login_check();
	require_once('controllers/UserController.php');
	$userObj   		=   new UserController();
	$condition		= " ";
	$fields    		= " u.* ";
	if(isset($_GET['search']) && !empty($_GET['search'])){
		$_SESSION['customerSearch'] = $_GET['search'];
		$condition .= " and ( u.FirstName LIKE '%".$_GET['search']."%' || u.LastName LIKE '%".$_GET['search']."%' || u.Email LIKE '%".$_GET['search']."%')";
		if(isset($_GET['type']) && $_GET['type'] == 0){
			$startValue		= 0;
		}else{
			$startValue	= $_GET['start']; }
			//$_SESSION['startlimit'] = $startValue;
	}else{ 
		//$_SESSION['startlimit'] = $_GET['start'];
		$startValue		= $_GET['start'];
		//unset($_SESSION['customerSearch']);
	}
	$condition 		.= " and u.Status in (1,2)";
	$limit			= 	$startValue;
	$i = $_GET['start'];
	
	$userListResult = $userObj->getCustomerList($fields,$condition,$limit);
	$tot_rec 		= $userObj->getTotalRecordCount();
}

if(is_array($userListResult) && count($userListResult)>0){
	foreach($userListResult as $key=>$value){
	//echo "<pre>"; echo print_r($value); echo "</pre>";die();
	$image_path = '';
	$photo = $value->Photo;
	$original_path = $image_path = ADMIN_IMAGE_PATH.'no_user.jpeg';
	$user_image = $photo;
	if(SERVER){
		if(image_exists(2,$user_image))
			$image_path = USER_THUMB_IMAGE_PATH.$user_image;
		if(image_exists(1,$user_image))
			$original_path = USER_IMAGE_PATH.$user_image;
	}else{
		if(file_exists(USER_IMAGE_PATH_REL.$user_image))
			$original_path = USER_IMAGE_PATH.$user_image;
		if(file_exists(USER_THUMB_IMAGE_PATH_REL.$user_image))
			$image_path = USER_THUMB_IMAGE_PATH.$user_image;
	}
	if($photo == '' ){
		$image_path	= ADMIN_IMAGE_PATH.'no_user.jpeg';
	}
	$append_select = '';
	if(isset($_GET['action']) && $_GET['action'] == 'GET_MORE_CUSTOMERS'){ 
		if($start_val == 0 && $key == 0) 
				$append_select = '<span style="display:none;" id="current_slide">'.$value->id.'</span>';
				$resultArray[] = '<img id="'.$value->id.'"  title="'.$value->FirstName.'" class="customerImage" width="115" height="110" align="top" src="'.$image_path.'" ><br><span class="name" id="more_images">'.ucfirst($value->FirstName).'&nbsp;'.(!empty($value->LastName)?$value->LastName:'').'</span>'.$append_select;
	
	?>	
	<?php } else { 
		?>
		<li class="<?php if($start_val == 0 && $key == 0) echo 'select';?>">
			<?php  if($photo != '' ){ ?>
			<img id="<?php echo $value->id ;?>"  title="<?php if(isset($value->FirstName) && $value->FirstName != ''){ echo ucfirst($value->FirstName); } ?>" class="customerImage " width="115" height="110" align="top" src="<?php echo $image_path;?>" >
			<?php } else{ ?>
			<img id="<?php echo $value->id ;?>"  title="<?php if(isset($value->FirstName) && $value->FirstName != ''){ echo ucfirst($value->FirstName); } ?>" class="customerImage " width="115" height="110" align="top" src="<?php echo ADMIN_IMAGE_PATH.'no_user.jpeg';?>" >
			<?}?>

		<span class="name"><?php if(isset($value->FirstName) && $value->FirstName != ''){ echo ucfirst($value->FirstName); } echo "&nbsp;"; if(isset($value->LastName) && $value->LastName != ''){ echo ucfirst($value->LastName); }?></span>
		</li>
		 <?php 
		  }
		 $i++;
		 } ?>			
<?php 
	if(isset($resultArray) &&  count($resultArray) > 0){
		echo json_encode($resultArray);
	}
} 
else{
	echo 'fails';
}
?>
		
		

