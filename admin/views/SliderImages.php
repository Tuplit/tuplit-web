<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
require_once('controllers/UserController.php');
$userObj   =   new UserController();
require_once("includes/phmagick.php");
$class =  $msg  = $class_icon = '';
$display = 'none';
$rowCount	=	1;
$home_count	=	0;
$tutorial_count	=	$home_id_count = $tutorial_id_count =0	;
$photoUpdateString	= $imgorder = '';
$orderArray = array();
$HomeSlideOrder		=	$userObj->getSliderImageDetails('MAX(`Order`) as home_order', 'Status = 1 and SliderType = 1  ORDER BY `Order` asc ');
if(isset($HomeSlideOrder) && count($HomeSlideOrder) ){
	$home_count	 	= $HomeSlideOrder[0]->home_order;
}
$TutorialSlideOrder	=	$userObj->getSliderImageDetails('MAX(`Order`) as tutorial_order', 'Status = 1 and SliderType = 2  ORDER BY `Order` asc ');
if(isset($TutorialSlideOrder) && count($TutorialSlideOrder) ){
	$tutorial_count	= $TutorialSlideOrder[0]->tutorial_order;
}
$HomeSlideImageArray		=	$userObj->getSliderImageDetails('*', 'Status = 1 and SliderType = 1  ORDER BY `Order` asc ');
$TutorialSlideImageArray	=	$userObj->getSliderImageDetails('*', 'Status = 1 and SliderType = 2  ORDER BY `Order` asc ');
if(isset($_POST['upload_home_slider']) && $_POST['upload_home_slider'] == 'Submit')
{	
	if(isset($_POST['hidden_home_count']) && $_POST['hidden_home_count'] != '')
	{		
		if(isset($_POST['order']) && is_array($_POST['order']))
		{
			$orderArray		=	array_unique($_POST['order']);
			$order_count	=	count($orderArray);
			for($k = 1; $k<=$order_count;$k++)
			{
				$imgorder = $orderArray[$k-1];
				if(isset($_POST['Slider_Image_'.$imgorder.'_upload']) && $_POST['Slider_Image_'.$imgorder.'_upload'] != '')
				{
					$insert_id   		    = $userObj->insertHomeSlide($imgorder);	
					$userObj->updateSliderDetails('`Order` = "'.$imgorder.'"','id = "'.$insert_id.'"');
					$date_now = date('Y-m-d H:i:s');
					if(isset($insert_id) && $insert_id != '' )
					{
						$imageName 				= $insert_id . '_' . strtotime($date_now) . '.png';
					   	$tempImagePath 			= TEMP_USER_IMAGE_PATH_REL . $_POST['Slider_Image_'.$imgorder.'_upload'];
						$imagePath 				= UPLOAD_SLIDER_PATH_REL . $imageName;
						$oldSliderName			= $_POST['name_Slider_Image_'.$imgorder];
						if ( !file_exists(UPLOAD_SLIDER_PATH_REL) ){
					  		mkdir (UPLOAD_SLIDER_PATH_REL, 0777);
						}
						copy($tempImagePath,$imagePath);
						/*$phMagick = new phMagick($imagePath);
						$phMagick->setDestination($imageThumbPath)->resize(100,100);*/
						if (SERVER){
							if($oldSliderName!='') {
								if(image_exists(4,$oldSliderName)) {
									deleteImages(4,$oldSliderName);
								}
							}
							uploadImageToS3($imagePath,4,$imageName);
							unlink($imagePath);
						}
						$photoUpdateString	= " SliderImages = '" . $imageName . "'";
						unlink(TEMP_USER_IMAGE_PATH_REL.$_POST['Slider_Image_'.$imgorder.'_upload']);
						if($photoUpdateString!='')
						{
							$condition 			= "id = ".$insert_id;
							$userObj->updateSliderDetails($photoUpdateString,$condition);
						}
					}
				}
				if(isset($_POST['Slider_Image_Old_'.$imgorder.'_upload']) && $_POST['Slider_Image_Old_'.$imgorder.'_upload'] != '')
				{
					$update_id   		    = $_POST['hdden_id_val_'.$imgorder];
					$date_now = date('Y-m-d H:i:s');
					if(isset($update_id) && $update_id != '' )
					{
						$updateImageName 		= $update_id . '_' . strtotime($date_now) . '.png';
					   	$updateTempImagePath 	= TEMP_USER_IMAGE_PATH_REL . $_POST['Slider_Image_Old_'.$imgorder.'_upload'];
						$updateImagePath 		= UPLOAD_SLIDER_PATH_REL . $updateImageName;
						$updateSliderName		= $_POST['name_Slider_Image_Old_'.$imgorder];
						
						if ( !file_exists(UPLOAD_SLIDER_PATH_REL) ){
					  		mkdir (UPLOAD_SLIDER_PATH_REL, 0777);
						}
						copy($updateTempImagePath,$updateImagePath);
						/*$phMagick = new phMagick($imagePath);
						$phMagick->setDestination($imageThumbPath)->resize(100,100);*/
						if (SERVER){
							if($updateSliderName!='') {
								if(image_exists(4,$updateSliderName)) {
									deleteImages(4,$updateSliderName);
								}
							}
							uploadImageToS3($updateImagePath,4,$updateImageName);
							unlink($updateImagePath);
						}else{
							if(file_exists(UPLOAD_SLIDER_PATH_REL.$updateSliderName))
								unlink(UPLOAD_SLIDER_PATH_REL.$updateSliderName);
						}
						$photoUpdateString	= " SliderImages = '" . $updateImageName . "'";
						unlink(TEMP_USER_IMAGE_PATH_REL.$_POST['Slider_Image_Old_'.$imgorder.'_upload']);
						if($photoUpdateString!='')
						{
							$condition 			= "id = ".$update_id;
							$userObj->updateSliderDetails($photoUpdateString,$condition);
						}
					}
				}
			}
		}
	}	
	header("location:SliderImages?msg=1");
}
if(isset($_POST['upload_tutorial_slider']) && $_POST['upload_tutorial_slider'] == 'Submit')
{
	if(isset($_POST['hidden_tutorial_count']) && $_POST['hidden_tutorial_count'] != ''){
		if(isset($_POST['order']) && is_array($_POST['order'])){
			$orderArray		=	array_unique($_POST['order']);
			$order_count	=	count($orderArray);
			for($k = 1; $k<=$order_count;$k++)
			{
				$imgorder = $orderArray[$k-1];
				if(isset($_POST['Tutorial_Image_'.$imgorder.'_upload']) && $_POST['Tutorial_Image_'.$imgorder.'_upload'] != ''){
					$insert_id   		    = $userObj->insertTutorialSlide($imgorder);	
					$userObj->updateSliderDetails('`Order` = "'.$imgorder.'"','id = "'.$insert_id.'"');
					$date_now = date('Y-m-d H:i:s');
					if(isset($insert_id) && $insert_id != '' ){
						$imageName 				= $insert_id . '_' . strtotime($date_now) . '.png';
					   	$tempImagePath 			= TEMP_USER_IMAGE_PATH_REL . $_POST['Tutorial_Image_'.$imgorder.'_upload'];
						$imagePath 				= UPLOAD_SLIDER_PATH_REL . $imageName;
						$oldSliderName			= $_POST['name_Tutorial_Image_'.$imgorder];
						if ( !file_exists(UPLOAD_SLIDER_PATH_REL) ){
					  		mkdir (UPLOAD_SLIDER_PATH_REL, 0777);
						}
						copy($tempImagePath,$imagePath);
						/*$phMagick = new phMagick($imagePath);
						$phMagick->setDestination($imageThumbPath)->resize(100,100);*/
						if (SERVER){
							if($oldSliderName!='') {
								if(image_exists(4,$oldSliderName)) {
									deleteImages(4,$oldSliderName);
								}
							}
							uploadImageToS3($imagePath,4,$imageName);
							unlink($imagePath);
						}
						$photoUpdateString	= " SliderImages = '" . $imageName . "'";
						unlink(TEMP_USER_IMAGE_PATH_REL.$_POST['Tutorial_Image_'.$imgorder.'_upload']);
						if($photoUpdateString!='')
						{
							$condition 			= "id = ".$insert_id;
							$userObj->updateSliderDetails($photoUpdateString,$condition);
						}
					}
				}
				if(isset($_POST['Tutorial_Image_Old_'.$imgorder.'_upload']) && $_POST['Tutorial_Image_Old_'.$imgorder.'_upload'] != ''){
					$update_id   		    = $_POST['hdden_id_val_'.$imgorder];
					$date_now = date('Y-m-d H:i:s');
					if(isset($update_id) && $update_id != '' ){
						$updateImageName 		= $update_id . '_' . strtotime($date_now) . '.png';
					   	$updateTempImagePath 	= TEMP_USER_IMAGE_PATH_REL . $_POST['Tutorial_Image_Old_'.$imgorder.'_upload'];
						$updateImagePath 		= UPLOAD_SLIDER_PATH_REL . $updateImageName;
						$upadateSliderName		= $_POST['name_Tutorial_Image_Old_'.$imgorder];
						if ( !file_exists(UPLOAD_SLIDER_PATH_REL) ){
					  		mkdir (UPLOAD_SLIDER_PATH_REL, 0777);
						}
						copy($updateTempImagePath,$updateImagePath);
						/*$phMagick = new phMagick($imagePath);
						$phMagick->setDestination($imageThumbPath)->resize(100,100);*/
						if (SERVER){
							if($upadateSliderName!='') {
								if(image_exists(4,$upadateSliderName)) {
									deleteImages(4,$upadateSliderName);
								}
							}
							uploadImageToS3($updateImagePath,4,$updateImageName);
							unlink($updateImagePath);
						}
						else{
							if(file_exists(UPLOAD_SLIDER_PATH_REL.$upadateSliderName))
								unlink(UPLOAD_SLIDER_PATH_REL.$upadateSliderName);
						}
						$photoUpdateString	= " SliderImages = '" . $updateImageName . "'";
						unlink(TEMP_USER_IMAGE_PATH_REL.$_POST['Tutorial_Image_Old_'.$imgorder.'_upload']);
						if($photoUpdateString!='')
						{
							$condition 			= "id = ".$update_id;
							$userObj->updateSliderDetails($photoUpdateString,$condition);
						}
					}
				}
			}
		}
	}
	header("location:SliderImages?msg=2");
}

if(isset($_GET['msg']) && $_GET['msg'] == 1){
	$msg 		= 	"Home Slider images updated successfully";
	$display	=	"block";
	$class 		= 	"alert-success";
	$class_icon = 	"fa-check";
}
if(isset($_GET['msg']) && $_GET['msg'] == 2){
	$msg1 			= 	"Tutorial Slider images updated successfully";
	$display1		=	"block";
	$class1 		= 	"alert-success";
	$class_icon1	= 	"fa-check";
}
commonHead(); ?>
<body class="skin-blue">
<?php top_header(); ?>
	<!-- Content Header (Page header) -->
	<section class="content-header no-padding">
		<div class="col-xs-12"> 
			<h1><i class="fa fa-picture-o"></i> Slider Images</h1>
		</div>
	</section>
	<!-- Main content -->
	<section class="content">
	
		<div class="row">
		<!-- left column -->
			<div class="col-md-12 slider_page">
			<form name="add_home_slider_form" id="add_home_slider_form" action="" method="post" >
				<div class="box box-primary no-padding">
					<div class="box-header no-padding">
						<i class="fa fa-picture-o"></i><h3 class="box-title">Home Slider Images</h3>
					</div>
					
					<?php if(isset($msg) && $msg != '') { ?>
					<div align="center" class="alert <?php  echo $class;  ?> alert-dismissable col-sm-5"><i class="fa fa-check"></i>  <?php echo $msg; ?></div>
					<?php } ?>
					
					<div class="box-body">
						<div class="col-sm-12 no-padding">
							<div class="col-sm-9">&nbsp;</div>
							<div class="col-sm-3"><strong>Order of Image</strong></div>
						</div>
						
						<div id="home_center" class="row_bg ">
						<?php if(isset($HomeSlideImageArray) && count($HomeSlideImageArray) > 0){
						$home_id_count	=	count($HomeSlideImageArray);
						$i = 0;
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
							$i++;?>
						<?php if(isset($h_val->id) && $h_val->id != ''){?>
							<div class="row  pad" id="exist_id_home_<?php echo $h_val->id;?>" clone="<?php echo $i;?>">
								<div class="col-md-3">Slider Image <?php echo $i;?></div>
								<div class="col-md-6" id="">
									<div class="col-sm-8 "> <input type="file"  class="file_input_old" name="Slider_Image_Old_<?php echo $i;?>" id="Slider_Image_Old_<?php echo $i;?>" title="Home Slider" onclick="" onchange="return ajaxAdminFileUploadProcess('Slider_Image_Old_<?php echo $i;?>');"  /></div>
								
									<div class="col-sm-4 " align="center">
										<div id="Slider_Image_Old_<?php echo $i;?>_img">
										<?php  if(isset($original_path) && $original_path != '') {  ?>
											<a <?php if(isset($original_path) && $original_path != '') {  ?> href="<?php echo $original_path; ?>" class="fancybox"<?php } else { ?> href="Javascript:void(0);"<?php } ?> ><?php if(isset($original_path) && $original_path != '') { ?> <img class="img_border" width="75" height="75" src="<?php echo $original_path;?>"><?php } ?></a>
										<?php } ?>
										</div>
									</div>
								</div>
								<div class="col-md-3 ">
									<div class="col-sm-6 no-padding">
										<input type="text" class="form-control" maxlength="4"  value="<?php echo $h_val->Order;?>" id="order_0" name="order[]" onchange="setOrderingSlider(this.value,'<?php echo $h_val->id;?>',1);" onkeypress="return isNumberKey(event);" maxlength="5"> 
										<input type="hidden" class=""  value="<?php echo $h_val->id;?>" id="hdden_id_val_<?php echo $i;?>" name="hdden_id_val_<?php echo $i;?>">
										
									</div> 
								
									<div class="col-sm-6 pad5">
										<a href="javascript:void(0)" <?php if($i != $home_id_count){?> style="display:none;"  <?php } ?> id="AddHome_<?php echo $i;?>" title="Add" class="AddNew" onclick="show_field('<?php echo $i;?>');" ><i class="fa fa-lg fa-plus-circle"></i></a>&nbsp;&nbsp;
										<a href="javascript:void(0)" id="RemoveHome_<?php echo $i;?>" title="Remove" onclick="deleteRow('<?php echo $h_val->id;?>',1);"><i class="fa fa-lg fa-minus-circle"></i></a>&nbsp;&nbsp; 
									</div>
								</div> 
								<?php  if(isset($_POST['Slider_Image_Old_'.$i]) && $_POST['Slider_Image_Old_'.$i] != ''){  ?><input type="Hidden" name="Slider_Image_Old_<?php echo $i;?>" id="Slider_Image_Old_<?php echo $i;?>" value="<?php  echo $_POST['Slider_Image_Old_'.$i];  ?>"><?php  }  ?>
								<input type="Hidden" name="empty_Slider_Image_Old_<?php echo $i;?>" id="empty_Slider_Image_Old_<?php echo $i;?>" value="<?php  if(isset($home_photo) && $home_photo != '') { echo $home_photo; }  ?>" />
								<input type="Hidden" name="name_Slider_Image_Old_<?php echo $i;?>" id="name_Slider_Image_Old_<?php echo $i;?>" value="<?php  if(isset($home_photo) && $home_photo != '') { echo $home_photo; }  ?>" />
<input type="hidden" class="" maxlength="4"  value="<?php echo $home_id_count;?>" id="hidden_home_count" name="hidden_home_count">
							</div>
						<?php } ?>
							
						<?php } } ?>
						
							<div <?php if ($home_count != 0) {?> style="display:none" <?php } ?>class="row pad" id="slider_home" clone="<?php echo $home_count+1;?>">
								<div class="col-md-3" id="Slider_image_name">Slider Image <?php echo $home_id_count+1;?></div>
								
								<div class="col-md-6">
									<div class="col-sm-8 " id="slider_home_new"><input type="file" class="file_input"   name="Slider_Image_<?php echo $home_count+1;?>" id="Slider_Image_<?php echo $home_count+1;?>" title="Home Slider" onclick="" onchange="return ajaxAdminFileUploadProcess(this.name);"  />
										<span class="error" for="empty_Slider_Image_<?php echo $home_count+1;?>" generated="true" style="display: none">Slider Image is required</span>
									</div>
								
									<div class="col-sm-4" align="center">
										<div class="image_disp" id="Slider_Image_<?php echo $home_count+1;?>_img"></div>
									</div>
								</div><!-- /col-md-6 -->
								<?php  if(isset($_POST['Slider_Image_'.$home_count+1]) && $_POST['Slider_Image_'.$home_count+1] != ''){  ?>
								<input class="hidd_3" type="Hidden" name="Slider_Image_<?php echo $home_count+1;?>" id="Slider_Image_<?php echo $home_count+1;?>" value="<?php  echo $_POST['Slider_Image_'.$home_count+1];  ?>">
								<?php  }  ?>
								<input class="hidd_1" type="Hidden" name="empty_Slider_Image_<?php echo $home_count+1;?>" id="empty_Slider_Image_<?php echo $home_count+1;?>" value="" />
								<input class="hidd_2" type="Hidden" name="name_Slider_Image_<?php echo $home_count+1;?>" id="name_Slider_Image_<?php echo $home_count+1;?>" value="" />
								<div class="col-md-3 ">
									<div class="col-sm-6 no-padding">
										<input type="text" class="form-control" maxlength="4"  value="<?php echo $home_count+1;?>" id="order_<?php echo $home_count+1;?>" name="order[]" onchange="ChangeImageName(1,this.value,this.id);">
										<input type="hidden" value="<?php echo $home_count+1;?>" id="hidden_home_count_<?php echo $home_count+1;?>" name="hidden_home_count" class="Hidden4"> 
									</div>
									
									<div class="col-sm-6 pad5">
										<a href="javascript:void(0)"  id="AddNew1_<?php echo $home_count+1;?>" title="Add" class="AddNew" onclick="addRow(this,'<?php //echo $home_count+1;?>','slider_home',1);" ><i class="fa fa-lg fa-plus-circle"></i></a>&nbsp;&nbsp;

										<a href="javascript:void(0)" title="Remove" class="Remove_Home_<?php echo $home_count+1;?>" <?php if($home_count== 0){?> style="display:none;" <?php } ?>onclick="delRow(this,'<?php echo $home_count+1;?>','slider_home',1);"><i class="fa fa-lg fa-minus-circle"></i></a><!-- data-toggle="tooltip"  -->
									</div>
								</div>
							</div>
							
						</div>
					</div><!-- /box-body -->
				
					<div class="box-footer col-sm-12" align="center">
						<input type="submit" class="btn btn-success" name="upload_home_slider" id="upload_home_slider" value="Submit" title="Submit" onclick="return Chkempty('<?php echo $home_count+1;?>',1);">
					</div>
				</div><!-- /.box -->
				</form>
			
				<form name="add_tutorial_slider_form" id="add_tutorial_slider_form" action="" method="post" >
				<div class="box box-primary no-padding">
					<div class="box-header no-padding">
						<i class="fa fa-picture-o"></i><h3 class="box-title">Tutorial Slider Images</h3>
					</div>
					<?php if(isset($msg1) && $msg1 != '') { ?>
						<div align="center" class="alert <?php  echo $class1;  ?> alert-dismissable col-sm-5"><i class="fa fa-check"></i>  <?php echo $msg1; ?></div>
					<?php } ?>
					<div class="box-body">
					
						<div class="col-sm-12 no-padding">
							<div class="col-sm-9">&nbsp;</div>
							<div class="col-sm-3"><strong>Order of Image</strong></div>
						</div>
						
						<div id="tutorial_center" class="row_bg ">
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
							<div class="row  pad" id="exist_id_tutorial_<?php echo $t_val->id;?>" clone="<?php echo $i_val;?>">
								<div class="col-md-3">Tutorial Image <?php echo $i_val;?></div>
								<div class="col-md-6" id="">
									<div class="col-sm-8 ">
										<input type="file"  class="file_input_old" name="Tutorial_Image_Old_<?php echo $i_val;?>" id="Tutorial_Image_Old_<?php echo $i_val;?>" title="Tutorial Slider" onclick="" onchange="return ajaxAdminFileUploadProcess('Tutorial_Image_Old_<?php echo $i_val;?>');"  />
									</div>
									<div class="col-sm-4" align="center">
										<div id="Tutorial_Image_Old_<?php echo $i_val;?>_img">
										<?php  if(isset($original_path_tutorial) && $original_path_tutorial != ''){  ?>
											<a <?php if(isset($original_path_tutorial) && $original_path_tutorial != '') {  ?> href="<?php echo $original_path_tutorial; ?>" class="fancybox"<?php } else { ?> href="Javascript:void(0);"<?php } ?>  >
<?php if(isset($original_path_tutorial) && $original_path_tutorial != '') { ?> <img class="img_border" width="75" height="75" src="<?php echo $original_path_tutorial;?>"><?php } ?></a>
										<?php } ?>
										</div>
									</div>
								</div><!-- /col-md-6 -->
								
								<div class="col-md-3">
									<div class="col-sm-6 no-padding">
										<input type="text" class="form-control" maxlength="4"  value="<?php echo $t_val->Order;?>" id="order_0" name="order[]" onchange="setOrderingSlider(this.value,'<?php echo $t_val->id;?>',2);" onkeypress="return isNumberKey(event);" maxlength="5"> 
										<input type="hidden" class="" maxlength="4"  value="<?php echo $t_val->id;?>" id="hdden_id_val_<?php echo $i_val;?>" name="hdden_id_val_<?php echo $i_val;?>">
									</div>
									<div class="col-sm-6 pad5">
									<a href="javascript:void(0)" <?php if($i_val != $tutorial_id_count){?> style="display:none;"  <?php } ?> id="AddTutorial_<?php echo $i_val;?>" title="Add" class="AddNew" onclick="show_field_tutorial('<?php echo $i_val;?>');" ><i class="fa fa-lg fa-plus-circle"></i></a>&nbsp;&nbsp;
										<a href="javascript:void(0)" title="Remove" onclick="deleteRow('<?php echo $t_val->id;?>',2);"><i class="fa fa-lg fa-minus-circle"></i></a>&nbsp;&nbsp;
									</div>
								</div>
								<?php  if(isset($_POST['Tutorial_Image_Old_'.$i_val]) && $_POST['Tutorial_Image_Old_'.$i_val] != ''){  ?><input type="Hidden" name="Tutorial_Image_Old_<?php echo $i_val;?>" id="Tutorial_Image_Old_<?php echo $i_val;?>" value="<?php  echo $_POST['Tutorial_Image_Old_'.$i_val];  ?>"><?php  }  ?>
									<input type="Hidden" name="empty_Tutorial_Image_Old_<?php echo $i_val;?>" id="empty_Tutorial_Image_Old_<?php echo $i_val;?>" value="<?php  if(isset($tutorial_photo) && $tutorial_photo != '') { echo $tutorial_photo; }  ?>" />
									<input type="Hidden" name="name_Tutorial_Image_Old_<?php echo $i_val;?>" id="name_Tutorial_Image_Old_<?php echo $i_val;?>" value="<?php  if(isset($tutorial_photo) && $tutorial_photo != '') { echo $tutorial_photo; }  ?>" />
							</div><!-- /row -->
						<?php } ?>
							<input type="hidden" class="" maxlength="4"  value="<?php echo $tutorial_id_count;?>" id="hidden_tutorial_count" name="hidden_tutorial_count">
						<?php } } ?>
							<div class="row pad" <?php if ($tutorial_count != 0) {?> style="display:none" <?php } ?> id="slider_tutorial" clone="<?php echo $tutorial_count+1;?>">
								<div class="col-md-3" id="Tutorial_image_name">Tutorial Image <?php echo $tutorial_id_count+1;?></div>
								<div class="col-md-6" id="slider_tutorial_new">
									<div class="col-sm-8 ">
										<input type="file" class="file_input"   name="Tutorial_Image_<?php echo $tutorial_count+1;?>" id="Tutorial_Image_<?php echo $tutorial_count+1;?>" title="Tutorial Slider" onclick="" onchange="return ajaxAdminFileUploadProcess(this.name);"  />
										<span class="error" for="empty_Tutorial_Image_<?php echo $tutorial_count+1;?>" generated="true" style="display: none">Slider Image is required</span>
									</div>
									<div class="col-sm-4" align="center">
										<div class="image_disp" id="Tutorial_Image_<?php echo $tutorial_count+1;?>_img"></div>
									</div>
								</div>
								<?php  if(isset($_POST['Tutorial_Image_'.$tutorial_count+1]) && $_POST['Tutorial_Image_'.$tutorial_count+1] != ''){  ?>
								<input class="hidd_3" type="Hidden" name="Tutorial_Image_<?php echo $tutorial_count+1;?>" id="Tutorial_Image_<?php echo $tutorial_count+1;?>" value="<?php  echo $_POST['Tutorial_Image_'.$tutorial_count+1];  ?>">
								<?php  }  ?>
								<input class="hidd_1" type="Hidden" name="empty_Tutorial_Image_<?php echo $tutorial_count+1;?>" id="empty_Tutorial_Image_<?php echo $tutorial_count+1;?>" value="<?php  //if(isset($SLIDERImageName) && $SLIDERImageName != '') { echo $SLIDERImageName; }  ?>" />
								<input class="hidd_2" type="Hidden" name="name_Tutorial_Image_<?php echo $tutorial_count+1;?>" id="name_Tutorial_Image_<?php echo $tutorial_count+1;?>" value="<?php  //if(isset($SLIDERImageName) && $SLIDERImageName != '') { echo $SLIDERImageName; }  ?>" />
								<div class="col-md-3">
									<div class="col-sm-6 no-padding">
										<input type="text" class="form-control" maxlength="4"  onchange="ChangeImageName(2,this.value,this.id);" value="<?php echo $tutorial_count+1;?>" id="order_<?php echo $tutorial_count+1;?>" name="order[]">
										<input type="Hidden" value="<?php echo $tutorial_count+1;?>" id="hidden_tutorial_count_<?php echo $tutorial_count+1;?>" name="hidden_tutorial_count" class="Hidden4"> 
									</div>
								
									<div class="col-sm-6 pad5">
										<a href="javascript:void(0)" title="Add" id="AddNew2_<?php echo $tutorial_count+1;?>" class="AddNewSlider" onclick="addRow(this,'<?php echo $tutorial_count+1;?>','slider_tutorial',2);"><i class="fa fa-lg fa-plus-circle"></i></a>&nbsp;&nbsp;
										<a href="javascript:void(0)" class="Remove_Tutorial_<?php echo  $tutorial_count+1;?>"  title="Remove" <?php if($tutorial_count== 0){?> style="display:none;" <?php } ?> onclick="delRow(this,'<?php echo $tutorial_count+1;?>','slider_tutorial',2);"><i class="fa fa-lg fa-minus-circle"></i></a>
									</div>
								</div>
							</div>
						</div><!-- /row_bg -->
						</div><!-- /box-body -->
						<div class="box-footer" align="center">
							<input type="submit" class="btn btn-success" name="upload_tutorial_slider" id="upload_tutorial_slider" value="Submit" title="Submit" onclick="return Chkempty('<?php echo $tutorial_count+1;?>',2);">
						</div>
					
				</div><!-- /.box -->
				</form>
				
			</div><!--/.slider_page -->
		</div><!-- /.row -->
	</section><!-- /.content -->	
<?php commonFooter(); ?>
<script type="text/javascript">
$(document).ready(function() {
	$('.fancybox').fancybox();
	
});

function show_field(id_val){
   $("#slider_home").attr('style','display:block');
   $("#AddHome_"+id_val).hide();

}
function show_field_tutorial(id_val){

   $("#slider_tutorial").attr('style','display:block');
   $("#AddTutorial_"+id_val).hide();

}
</script>
</html>