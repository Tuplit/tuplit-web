<?php
$start_val = 0;
if(isset($_GET['start']) && $_GET['start'] !=''){
	$start_val = $_GET['start'];
}
if(isset($_GET['action']) && $_GET['action'] == 'GET_MORE_MERCHANTS'){
	require_once('includes/CommonIncludes.php');
	admin_login_check();
	require_once('controllers/MerchantController.php');
	$MerchantObj   	=   new MerchantController();
	$condition = "";
	$fields    		= " m.* ";
	$append_approve = "";
	$searchtxt	=	0;
	if(isset($_GET['search']) && !empty($_GET['search'])){
		$_SESSION['merchantSearch'] = $_GET['search'];
		$condition .= " and ( m.CompanyName LIKE '%".addslashes($_SESSION['merchantSearch'])."%')";
		if(isset($_GET['type']) && $_GET['type'] == 0){
			$startValue		= 0;
		}else {$startValue	= $_GET['start']; }
		$_SESSION['startlimit'] = $startValue;
	}else { 
		$_SESSION['startlimit'] = $_GET['start'];
		$startValue		= $_GET['start'];
	}
	
	if(isset($_GET['prosearch']) && !empty($_GET['prosearch'])){
		$_SESSION['productSearch'] = $_GET['prosearch'];
		$searchtxt = $_GET['prosearch'];
	}
	
	if(isset($_SESSION['approve']) && $_SESSION['approve'] == 1)
		$condition 		.= " and m.Status in (0)";
	else
		$condition 		.= " and m.Status in (0,1,2)";
	$i = $_GET['start'];
	$merchantListResult  	= $MerchantObj->getMerchantImagesList($fields,$condition,$searchtxt);
	$tot_rec 		 		= $MerchantObj->getTotalRecordCount();
}
if(is_array($merchantListResult)){
		foreach($merchantListResult as $key=>$value){
		//echo "<pre>"; echo print_r($value); echo "</pre>";die();
		$image_path = '';
		$photo = $value->Icon;
		$image_path = ADMIN_IMAGE_PATH.'no_user.jpeg';
		if(isset($photo) && $photo != ''){
			if(SERVER){
				if(image_exists(6,$photo))
					$image_path = MERCHANT_ICONS_IMAGE_PATH.$photo;
			}else{
				if(file_exists(MERCHANT_ICONS_IMAGE_PATH_REL.$photo))
					$image_path = MERCHANT_ICONS_IMAGE_PATH.$photo;
			}
		}
		$append_approve ='';
		$append_select = '';
		if(isset($_GET['action']) && $_GET['action'] == 'GET_MORE_MERCHANTS'){ 
			if(isset($value->Status) && $value->Status == '0'){
				$append_approve ='<a title="Approve" class="mer-approve" id="id_'.$value->id.'" onclick=" approve(this.id);"><i class="fa fa-thumbs-up"></i></a><input type="hidden" name="id_'.$value->id.'_value" id="id_'.$value->id.'_value" value="'.$value->id.'">';
			}
			if($start_val == 0 && $key == 0) 
				$append_select = '<span style="display:none;" id="current_slide">'.$value->id.'</span>';
			$resultArray[] = '<img id="'.$value->id.'"  title="'.$value->CompanyName.'" class="merchantImage" width="115" height="110" align="top" src="'.$image_path.'" ><br><span class="name" id="more_images">'.ucfirst($value->CompanyName).'</span>'.$append_approve.$append_select;//onclick="getMerchantItems('.$value->id.')"
		?>
		
		<?php } else { 
		?>
		<li class="<?php if($start_val == 0 && $key == 0) echo 'select';?>">
			<img id="<?php echo $value->id ;?>"  title="<?php if(isset($value->CompanyName) && $value->CompanyName != ''){ echo ucfirst($value->CompanyName); } ?>" class="merchantImage " width="115" height="110" align="top" src="<?php echo $image_path;?>" ><!-- onclick="getMerchantItems('<?php echo $value->id; ?>');" -->
			<span class="name"><?php if(isset($value->CompanyName) && $value->CompanyName != ''){ echo ucfirst($value->CompanyName); } ?></span>
			<?php if(isset($value->Status) && $value->Status == 0){?>
				<a title="Approve" class="mer-approve" id="id_<?php echo $value->id ;?>" onclick=" approve(this.id);"><i class="fa fa-thumbs-up"></i></a>
				<input type="hidden" name="id_<?php echo $value->id ;?>_value" id="id_<?php echo $value->id ;?>_value" value="<?php echo $value->id ;?>">
			<?php }?>
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
