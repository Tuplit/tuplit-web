<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();
$merchantCategory = array();
if(isset($_SESSION['merchantDetailsInfo']) && is_array($_SESSION['merchantDetailsInfo'])){
	$merchantInfo  				=	$_SESSION['merchantDetailsInfo'];
	$newCategory				=	$_SESSION['merchantDetailsInfo']['Category'];
}
else{
	$merchantId					= 	$_SESSION['merchantInfo']['MerchantId'];
	$url						=	WEB_SERVICE.'v1/merchants/'.$merchantId;
	$curlMerchantResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201 && $curlMerchantResponse['merchant']['MerchantId'] != '' ) 
	 {
		$merchantInfo  			= $_SESSION['merchantDetailsInfo']   =	$curlMerchantResponse['merchant'];
		$newCategory			=	$merchantInfo['Category'];
	}
}

$url							=	WEB_SERVICE.'v1/categories/';
$curlCategoryResponse 			= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
if(isset($curlCategoryResponse) && is_array($curlCategoryResponse) && $curlCategoryResponse['meta']['code'] == 201 && is_array($curlCategoryResponse['categoryDetails']) ) {
	if(isset($curlCategoryResponse['categoryDetails']))
	$categories = $curlCategoryResponse['categoryDetails'];
	if(isset($_POST['categorySelected']))
		$newCategory			=	$_POST['categorySelected'];
	
} else if(isset($curlCategoryResponse['meta']['errorMessage']) && $curlCategoryResponse['meta']['errorMessage'] != '') {
		$errorMessage			=	$curlCategoryResponse['meta']['errorMessage'];
} else {
		$errorMessage			= 	"Bad Request";
}
$merchantInfo['OpeningHours']	=	formOpeningHours($merchantInfo['OpeningHours']);
if(isset($merchantInfo['Category']) && !empty($merchantInfo['Category'])) {
	$merchantCategory			= 	explode(',',$merchantInfo['Category']);
}
commonHead();
?>

<body class="skin-blue fixed" onload="fieldfocus('Address');">
		<?php top_header(); ?>
		<section class="content">
		<div class="col-lg-8 col-md-11 box-center">
			<section class="content-header">
                <h1>My Store</h1>
            </section>
			<?php if(isset($msg) && $msg != '') { ?>
				<div align="center" class="alert <?php  echo $class;  ?> alert-dismissable col-xs-12 col-sm-5 col-lg-3"><i class="fa <?php  echo $class_icon;  ?>"></i>  <?php echo $msg; ?></div>
			<?php } ?>
			<form action="" name="mystore_form" id="mystore_form"  method="post">
				<div class="row clear">
					<div class="col-sm-12 col-md-12">					
					<div class="box box-primary no-padding">
						<div class="box-header no-padding">
							<h3 class="box-title"></h3>
						</div>
						<div class="form-group col-sm-12 col-md-12">
							<div class="col-sm-8 col-md-8 control-label no-padding">
							<label class="control-label" >Shop Name</label>
							<p class="help-block col-sm-12 no-padding">Name is visible on your card in mobile app</p>
							</div>
							<div class="col-sm-4 col-md-4 no-padding"><input type="text" name="ShopName" class="form-control valid"  id="ShopName" value="<?php if(isset($merchantInfo['CompanyName']) && !empty($merchantInfo['CompanyName'])) echo $merchantInfo['CompanyName'];?>"></div>
							
						</div>
						<div class="form-group col-sm-12 col-md-12">
							<label class="col-sm-12 col-md-12 control-label no-padding border-right"><span>Shop Description</span><em></em></label>
							<p class="help-block col-sm-12 no-padding">Shop Description(max. 80 characters)</p>
							<div class="col-sm-12 no-padding"><input type="text" name="ShopDescription"  id="ShopDescription" maxlength="80" placeholder="e.g: The best burger" class="form-control valid" value="<?php if(isset($merchantInfo['ShortDescription']) && !empty($merchantInfo['ShortDescription'])) echo $merchantInfo['ShortDescription'];?>"></div>
						</div>
						<div class="form-group col-sm-12 col-sm-12">
							<label class="col-sm-8 control-label no-padding">Category</label>
							<div class="col-sm-4 control-label no-padding">
								<select name="Category" id="Category" class="form-control" onchange="showCategory(this.value)">
									<option value="">Select</option>	
									<?php if(isset($categories) && !empty($categories)) {
										foreach($categories as $key=>$val) {
										if($key != 'totalCount') {
									?>
									<option value="<?php echo $val['CategoryId'];?>"  style="background-image:url(<?php echo $val['CategoryIcon']; ?>);"><?php echo ucfirst($val['CategoryName']);?></option>
									<?php } } } ?>
								</select><span id="njkj"></span>
							</div>
						</div>
						<div class="form-group col-sm-12 col-md-12">
							<?php if(isset($categories) && !empty($categories)) {
								foreach($categories as $key=>$val) {
								//echo "<pre>"; echo print_r($val); echo "</pre>";
							?>
								<span id="cat_id_<?php echo $val['CategoryId']; ?>" <?php if(in_array($val['CategoryId'],$merchantCategory )){ ?> class="cat_box" <?php } else {?> style="display:none;" class="cat_box" <?php } ?>>
									<img width="30" src="<?php echo $val['CategoryIcon']; ?>"/>
									<span class="cname"><?php echo ucfirst($val['CategoryName']);?></span>
									<a class="delete" title="Remove" href="javascript:void(0)" onclick="removeCategory(<?php echo $val['CategoryId']; ?>)">
										<i class="fa fa-trash-o "></i>
									</a>
								</span>
							<?php  } } ?>
							<input type="Hidden" id="categorySelected" name="categorySelected" value="<?php //if(isset($newCategory) && $newCategory>0) echo $newCategory;?>"/>
						</div>
						<div class="form-group col-sm-12 col-md-12 clear">
							<label class="col-sm-8 control-label no-padding">Price Range</label>
							<span class="col-sm-4 control-label no-padding">
								<select name="PriceRange" id="PriceRange" class="form-control">
									<option value="">Select</option>								
								</select>
							</span>
						</div>						    
						<div class="form-group col-sm-12">
							<label class="col-sm-12 control-label no-padding border-right"><span>Slideshow Pictures</span><em></em></label>
							<p class="help-block no-padding">Upload upto 10 pictures(resolution is 1000*350 pixels, bigger images will scaled down automatically)</p>


							<div class="row">
								<div class="col-sm-6 col-xs-12 form-group">
										<div class="col-xs-1 no-padding">1.</div>
										<div class="col-xs-11 no-padding" align="center">
											<div  class="photo_gray_bg">
												<img style="vertical-align:top" class="resize" src="<?php SITE_PATH;?>webresources/images/no_photo_my_store.png" width="330" height="160" alt="">
												<!-- upload image place it here -->
												<!-- <img style="vertical-align:top" class="" src="<?php SITE_PATH;?>webresources/images/banner1.jpg" width="330" height="160" alt=""> -->
											</div>
										</div>
										<div class="col-xs-12">&nbsp;</div>
										
										<div class="col-xs-2 col-md-1 clear">&nbsp;</div>
										<div class="col-xs-10 col-md-11" align="center">
											<input type="button" name="" id="" value="Delete" title="Delete" class="box-center btn btn-danger  col-xs-10">
										</div>
								</div>
								
								<div class="col-sm-6 col-xs-12 form-group">
										<div class="col-xs-1 no-padding">2.</div>
										<div class="col-xs-11 no-padding" align="center">
											<div  class="photo_gray_bg">
											<img style="vertical-align:top" class="resize" src="<?php SITE_PATH;?>webresources/images/no_photo_my_store.png" width="330" height="160" alt="">
											</div>
										</div>
										<div class="col-xs-12">&nbsp;</div>
										
										<div class="col-xs-2 col-md-1 clear">&nbsp;</div>
										<div class="col-xs-10 col-md-11" align="center">
											<input type="button" name="" id="" value="Delete" title="Delete" class="box-center btn btn-danger  col-xs-10">
										</div>
								</div>
							</div>
							
							<div class="row">
								<div class="col-sm-6 col-xs-12 form-group">
										<div class="col-xs-1 no-padding">3.</div>
										<div class="col-xs-11 no-padding" align="center">
											<div  class="photo_gray_bg">
												<img style="vertical-align:top" class="resize" src="<?php SITE_PATH;?>webresources/images/no_photo_my_store.png" width="330" height="160" alt="">
												<!-- upload image place it here -->
												<!-- <img style="vertical-align:top" class="" src="<?php SITE_PATH;?>webresources/images/banner1.jpg" width="330" height="160" alt=""> -->
											</div>
										</div>
										<div class="col-xs-12">&nbsp;</div>
										
										<div class="col-xs-2 col-md-1 clear">&nbsp;</div>
										<div class="col-xs-10 col-md-11" align="center">
											<input type="button" name="" id="" value="Delete" title="Delete" class="box-center btn btn-danger  col-xs-10">
										</div>
								</div>
								
								<div class="col-sm-6 col-xs-12 form-group">
										<div class="col-xs-1 no-padding">4.</div>
										<div class="col-xs-11 no-padding" align="center">
											<div  class="photo_gray_bg_light">
												<img style="vertical-align:top" class="resize" src="<?php SITE_PATH;?>webresources/images/no_photo_my_store.png" width="330" height="160" alt="">
												
												<!-- upload image place it here -->
												<!-- <img style="vertical-align:top" class="" src="<?php SITE_PATH;?>webresources/images/banner1.jpg" width="330" height="160" alt=""> -->
											</div>
											
											<div class="drag_pos">
												Drag & drop an image or
												<span>
												choose a file to upload
												<input type="file">
												</span>
											</div>
											
										</div>
										<div class="col-xs-12">&nbsp;</div>
										
										<div class="col-xs-2 col-md-1 clear">&nbsp;</div>
										<div class="col-xs-10 col-md-11" align="center">
											<input type="button" name="" id="" value="Upload" title="Upload" class="box-center btn btn-success  col-xs-10">
										</div>
								</div>
							</div>
							
						</div>
						<div class="form-group col-sm-12 col-md-12">
							<label class="col-sm-12 control-label no-padding border-right"><span>More Info</span><em></em></label>
							<p class="help-block no-padding">More info ...............(max. 300 characters) </p>
							<span class="col-md-12 no-padding"><input type="text" name="MoreInfo"  id="MoreInfo" maxlength="300" placeholder="More Info" class="form-control valid" value="<?php if(isset($merchantInfo['Description']) && !empty($merchantInfo['Description'])) echo $merchantInfo['Description'];?>"></span>	
						</div>
						<div class="form-group col-sm-12 col-md-12 clearfix no-padding">
						<div class="form-group col-sm-12 col-md-12">
							<label class="col-sm-12  control-label no-padding border-right"><span>Contact Info</span><em></em></label>
							<div class="form-group col-sm-3 col-md-2 no-padding"><input type="button" title="Use my location" value="Use my location" class="btn bg-olive btn-md " /></div>
						
							<div class="col-sm-9 col-md-10 no-padding">
								<div class="show-grid form-group col-sm-7 no-padding">
									<div class="form-group col-sm-12 no-padding"><input type="text"  id="Street" name="Street" value="<?php if(isset($merchantInfo['Street']) && !empty($merchantInfo['Street'])) echo $merchantInfo['Street'];?>" placeholder="Street" class="form-control"></div>
									<div class="form-group col-sm-7 no-padding"><input type="text"  id="City" name="City" value="<?php if(isset($merchantInfo['City']) && !empty($merchantInfo['City'])) echo $merchantInfo['City'];?>" placeholder="City" class="form-control"></div>
									<div class="form-group col-sm-5 no-padding-right"><input type="text"  id="ZipCode" name="ZipCode" value="<?php if(isset($merchantInfo['ZipCode']) && !empty($merchantInfo['ZipCode'])) echo $merchantInfo['ZipCode'];?>" placeholder="ZIP code" class="form-control"></div>
									<div class="form-group col-sm-12 no-padding"><input type="text"  id="State" name="State" value="<?php if(isset($merchantInfo['State']) && !empty($merchantInfo['State'])) echo $merchantInfo['State'];?>" placeholder="State" class="form-control"></div>	
									<div class="form-group col-sm-12 no-padding"><input type="text"  id="Country" name="Country" value="<?php if(isset($merchantInfo['Country']) && !empty($merchantInfo['Country'])) echo $merchantInfo['Country'];?>" placeholder="Country" class="form-control"></div>	
								</div>
								<div class="show-grid form-group col-sm-7 no-padding">
									<div class="form-group col-sm-12 no-padding"><input type="text"  id="Phone" name="Phone" value="<?php if(isset($merchantInfo['PhoneNumber']) && !empty($merchantInfo['PhoneNumber'])) echo $merchantInfo['PhoneNumber'];?>" placeholder="Phone" class="form-control"></div>	
									<div class="form-group col-sm-12 no-padding"><input type="text"  id="Email" name="Email" value="<?php if(isset($merchantInfo['Email']) && !empty($merchantInfo['Email'])) echo $merchantInfo['Email'];?>" placeholder="Email" class="form-control"></div>	
									<div class="form-group col-sm-12 no-padding"><input type="text"  id="Website" name="Website" value="<?php if(isset($merchantInfo['WebsiteUrl']) && !empty($merchantInfo['WebsiteUrl'])) echo $merchantInfo['WebsiteUrl'];?>" placeholder="Website" class="form-control"></div>
								</div>
								<div class="form-group col-sm-7 no-padding"><input type="text"  id="Facebook" name="Facebook" value="<?php if(isset($merchantInfo['Facebook']) && !empty($merchantInfo['Facebook'])) echo $merchantInfo['Facebook'];?>" placeholder="Facebook" class="form-control"></div>	
								<div class="form-group col-sm-7 no-padding"><input type="text"  id="Twiter" name="Twiter" value="<?php if(isset($merchantInfo['Twiter']) && !empty($merchantInfo['Twiter'])) echo $merchantInfo['Twiter'];?>" placeholder="Twiter" class="form-control"></div>													
							</div>
						</div>
						<div class="form-group col-sm-12 col-md-12">
							<label class="col-sm-12 col-md-12 control-label no-padding border-right"><span>Business Hours</span><em></em></label>
							<p class="help-block col-sm-12 no-padding">Business Hours leave as HH MM AM/PM for not service</p>
							<?php 
							if(isset($days_array) && count($days_array)>0) {
							foreach($days_array as $key=>$val){ ?>
							<div class="col-xs-12 no-padding form-group <?php if($key != 0) echo "rowHide";?>"  <?php if(isset($merchantInfo['OpeningHours'][0]['DateType']) && $merchantInfo['OpeningHours'][0]['DateType'] == '1' && $key != 0) echo 'style="display:none;"'; ?>>
								<?php if($key == 0) { ?>
									<div class="col-sm-4 col-lg-3 col-xs-12 no-padding">
										<input type="checkbox" name="samehours" id="samehours"  onclick="return hideAllDays();" <?php if(isset($merchantInfo['OpeningHours'][0]['DateType']) && $merchantInfo['OpeningHours'][0]['DateType'] == '1') echo "checked"; ?>>&nbsp;Same for all days 
										<input type="hidden" id="showdays" name="showdays" value="<?php if(isset($merchantInfo['OpeningHours'][0]['DateType']) && $merchantInfo['OpeningHours'][0]['DateType'] == '1') echo 'checked'; ?>"/>
									</div>
									<div class="col-sm-4 col-xs-6 no-padding LH30">From :</div>
									<div class="col-sm-4 col-xs-6 no-padding LH30">To :</div>
									
								<?php } ?>
								<div class="col-sm-4  col-lg-3 col-xs-12  no-padding LH30"><strong><span class="<?php if($key == 0) echo "rowshow";?>"><?php if(isset($merchantInfo['OpeningHours'][0]['DateType']) && $merchantInfo['OpeningHours'][0]['DateType'] == '1' && $key == 0) echo "Monday to Sunday"; else echo $val.""; ?></span></strong></div>
								<div class="col-sm-4 col-xs-6  no-padding select_sm">
									
										<select class="form-control" id="fromhours_list<?php echo $key; ?>" name="fromhours_list<?php echo $key; ?>" onchange="return setTime('<?php echo $key; ?>');">
											<option value="">HH</option>
											<?php foreach($admin_hours_array as $keyhr=>$value){  ?>
													<option value="<?php echo $keyhr; ?>" <?php if(isset($merchantInfo['OpeningHours'][$key]['Start']['hr']) && $merchantInfo['OpeningHours'][$key]['Start']['hr'] == $keyhr) echo "selected"; ?>><?php echo $value; ?></option>
											<?php } ?>
										</select>
										<select class="form-control" id="fromminute_list<?php echo $key; ?>" name="fromminute_list<?php echo $key; ?>" onchange="return setTime('<?php echo $key; ?>');">
											<option value="">MM</option>
											<?php foreach($admin_minute_array as $keymin=>$value){  ?>
													<option value="<?php echo $keymin; ?>" <?php if(isset($merchantInfo['OpeningHours'][$key]['Start']['min']) && $merchantInfo['OpeningHours'][$key]['Start']['min'] == $keymin) echo "selected"; ?>><?php echo $value; ?></option>
											<?php } ?>
										</select>
										<select class="form-control" id="fromampm_list<?php echo $key; ?>" name="fromampm_list<?php echo $key; ?>" onchange="return setTime('<?php echo $key; ?>');">
											<option value="">AM/PM</option>
											<?php foreach($admin_ampm_array as $keyampm=>$value){  ?>
													<option value="<?php echo $keyampm; ?>" <?php if(isset($merchantInfo['OpeningHours'][$key]['Start']['ampm']) && $merchantInfo['OpeningHours'][$key]['Start']['ampm'] == $keyampm) echo "selected"; ?>><?php echo $value; ?></option>
											<?php } ?>
										</select>
										<input type="hidden"  class="form-control" id="from1_<?php echo $key; ?>" name="from1_<?php echo $key; ?>" onchange="return setTime('<?php echo $key; ?>');" value="<?php if(isset($merchantInfo['OpeningHours'][$key]['Start']['fromTime'])) echo $merchantInfo['OpeningHours'][$key]['Start']['fromTime']; ?>" >
								</div>
								<div class="col-sm-4 col-xs-6  no-padding select_sm">
										<select class="form-control" id="tohours_list<?php echo $key; ?>" name="tohours_list<?php echo $key; ?>" onchange="return setTime('<?php echo $key; ?>');">
											<option value="">HH</option>
											<?php foreach($admin_hours_array as $keyhr=>$value){  ?>
													<option value="<?php echo $keyhr; ?>" <?php if(isset($merchantInfo['OpeningHours'][$key]['End']['hr']) && $merchantInfo['OpeningHours'][$key]['End']['hr'] == $keyhr) echo "selected"; ?>><?php echo $value; ?></option>
											<?php } ?>
										</select>
										<select class="form-control" id="tominute_list<?php echo $key; ?>" name="tominute_list<?php echo $key; ?>" onchange="return setTime('<?php echo $key; ?>');">
											<option value="">MM</option>
											<?php foreach($admin_minute_array as $keymin=>$value){  ?>
													<option value="<?php echo $keymin; ?>" <?php if(isset($merchantInfo['OpeningHours'][$key]['End']['min']) && $merchantInfo['OpeningHours'][$key]['End']['min'] == $keymin) echo "selected"; ?>><?php echo $value; ?></option>
											<?php } ?>
										</select>
										<select class="form-control" id="toampm_list<?php echo $key; ?>" name="toampm_list<?php echo $key; ?>" onchange="return setTime('<?php echo $key; ?>');">
											<option value="">AM/PM</option>
											<?php foreach($admin_ampm_array as $keyampm=>$value){  ?>
													<option value="<?php echo $keyampm; ?>" <?php if(isset($merchantInfo['OpeningHours'][$key]['End']['ampm']) && $merchantInfo['OpeningHours'][$key]['End']['ampm'] == $keyampm) echo "selected"; ?>><?php echo $value; ?></option>
											<?php } ?>
										</select>
									<input type="hidden" class="form-control" id="to1_<?php echo $key; ?>" name="to1_<?php echo $key; ?>" onchange="return setTime('<?php echo $key; ?>');" value="<?php if(isset($merchantInfo['OpeningHours'][$key]['End']['toTime'])) echo $merchantInfo['OpeningHours'][$key]['End']['toTime']; ?>" ></div>
								<input type="hidden" id="id_<?php echo $key; ?>" name="id_<?php echo $key; ?>" value="<?php if(isset($merchantInfo['OpeningHours'][$key]['id'])) echo $merchantInfo['OpeningHours'][$key]['id']; ?>" >
							</div>
							<div class="col-md-12">
								<input type="hidden" id="row_<?php echo $key; ?>" name="row_<?php echo $key; ?>" value="<?php if(!empty($merchantInfo['OpeningHours'][$key]['Start']['fromTime']) || !empty($merchantInfo['OpeningHours'][$key]['End']['toTime'])) echo "1"; ?>" />
								<span id="error_<?php echo $key; ?>" style="color:red;"></span>
							</div>
							<?php } } ?>
						</div>
					</div>
				</div>				
				</div>
				<div class="footer col-xs-12 " align="center"> 
						<input type="submit" name="mystore_submit" id="mystore_submit" value="SAVE CHANGES" title="Save Changes" class="btn btn-success col-xs-5 box-center">
				</div>
				<div class="footer col-xs-12 " align="center"> <br>
						<a href="Dashboard" name="cancel" id="cancel">Cancel</a>
				</div>
			</form>
		 </div>
		</section>
		<?php footerLogin(); ?>
	<?php commonFooter(); ?>
</html>
