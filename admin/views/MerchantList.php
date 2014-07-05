<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
commonHead();
require_once('controllers/AdminController.php');
$adminLoginObj   =   new AdminController();
require_once('controllers/MerchantController.php');
$MerchantObj   =   new MerchantController();
require_once('controllers/ManagementController.php');
$managementObj   =   new ManagementController();

if(isset($_GET['cs']) && $_GET['cs']=='1') {
	destroyPagingControlsVariables();
	unset($_SESSION['mer_sess_name']);
	unset($_SESSION['mer_sess_email']);
	unset($_SESSION['mer_sess_company']);
	unset($_SESSION['mer_sess_location']);
	unset($_SESSION['mer_sess_status']);
	unset($_SESSION['mer_sess_Category']);
}

$fields1   = " c.* ";
$condition1 = " and c.Status in (1)";
$CategoryListResult  = $managementObj->getCategoryList($fields1,$condition1);

if(isset($_GET['approveId']) && $_GET['approveId']!=''){
	$approveId  = $_GET['approveId'];
	$MerchantObj->approveMerchant($approveId);
	$fields = '*';
	$condition = ' 1';
	$login_result = $adminLoginObj->getAdminDetails($fields,$condition);
	$merchantListResult  = $MerchantObj->selectMerchantDetail($approveId);
	
	if(isset($merchantListResult) && is_array($merchantListResult) && count($merchantListResult) > 0){
		$mailContentArray['name'] 		= ucfirst($merchantListResult[0]->FirstName.' '. $merchantListResult[0]->LastName);
		$mailContentArray['toEmail'] 	= $merchantListResult[0]->Email;
		$mailContentArray['subject'] 	= 'Merchant Approval Mail';		
		$mailContentArray['from'] 		= $login_result[0]->EmailAddress;
		$mailContentArray['fileName']	= 'merchant.html';
		sendMail($mailContentArray,'7');
	}	
	//die();
	header("location:MerchantList?msg=4");
}
$result = $MerchantObj->getMerchantNotApproved();
$merchantApproveTotal = $result[0]->total; 

if(isset($_POST['Search']) && $_POST['Search'] != ''){
	destroyPagingControlsVariables();
	$_POST          = unEscapeSpecialCharacters($_POST);
    $_POST          = escapeSpecialCharacters($_POST);
	if(isset($_POST['Merchantname']))
		$_SESSION['mer_sess_name'] 	= $_POST['Merchantname'];
	if(isset($_POST['Email']))
		$_SESSION['mer_sess_email']	= $_POST['Email'];
	if(isset($_POST['CompanyName']))
		$_SESSION['mer_sess_company']	= $_POST['CompanyName'];
	if(isset($_POST['Location']))
		$_SESSION['mer_sess_location']	= $_POST['Location'];
	if(isset($_POST['Status']))
		$_SESSION['mer_sess_status']	= $_POST['Status'];
	if(isset($_POST['merchantCategory']))
		$_SESSION['mer_sess_Category']	= $_POST['merchantCategory'];
	//print_r($_POST);
}

if(isset($_POST['Delete']) && $_POST['Delete'] != ''){
	if(isset($_POST['checkdelete']) && is_array($_POST['checkdelete']) && count($_POST['checkdelete']) > 0)
		$delete_id = implode(',',$_POST['checkdelete']);
}

if(isset($_GET['delId']) && $_GET['delId']!=''){
	$delete_id  = $_GET['delId'];
}
if(isset($delete_id) && $delete_id != ''){
	$MerchantObj->deleteMerchantEntries($delete_id);
	$delete = explode(',',$delete_id);
	if(isset($delete) && is_array($delete) && count($delete) >0 )
	{
		foreach($delete as $key=>$value){
			$merchantListResult  = $MerchantObj->selectMerchantDetail($value);
			if(isset($merchantListResult) && is_array($merchantListResult) && count($merchantListResult) > 0){
				$mer_image = $merchantListResult[0]->Icon;
				$com_image = $merchantListResult[0]->Image;
				if(SERVER){
					if(image_exists(6,$mer_image))
						deleteImages(6,$mer_image);
					if(image_exists(7,$com_image))
						deleteImages(7,$com_image);
				}
				else{
					if(file_exists(MERCHANT_ICONS_IMAGE_PATH_REL.$mer_image))
						unlink(MERCHANT_ICONS_IMAGE_PATH_REL . $mer_image);
					if(file_exists(MERCHANT_IMAGE_PATH_REL.$com_image))
						unlink(MERCHANT_IMAGE_PATH_REL . $com_image);
				}
			}
		}
	}	//die();
	header("location:MerchantList?msg=3");
}
if(isset($_GET['msg']) && $_GET['msg'] == 2){
	$msg 		= 	"Merchant detail updated successfully";
	$display	=	"block";
	$class 		= 	"alert-success";
	$class_icon = "fa-check";
}
else if(isset($_GET['msg']) && $_GET['msg'] == 3){
	$msg 		= 	"Merchant deleted successfully";
	$display	=	"block";
	$class 		= 	"alert-success";
	$class_icon    = "fa-check";
} 
else if(isset($_GET['msg']) && $_GET['msg'] == 4){
	$msg 		= 	"Merchant approved and mail sent successfully";
	$display	=	"block";
	$class 		= 	"alert-success";
	$class_icon =   "fa-check";
}
setPagingControlValues('id',ADMIN_PER_PAGE_LIMIT);
$fields    = " m.* ";
if(isset($_GET['status'])  && $_GET['status'] == 0)
	$condition = " and m.Status in (0)";
else
	$condition = " and m.Status in (0,1)";
$merchantListResult  = $MerchantObj->getMerchantList($fields,$condition);
$tot_rec 		 = $MerchantObj->getTotalRecordCount();

if($tot_rec!=0 && !is_array($merchantListResult)) {
	$_SESSION['curpage'] = 1;
	$merchantListResult  = $MerchantObj->getMerchantList($fields,$condition);
}
//echo "<pre>"; echo print_r($merchantListResult); echo "</pre>";

?>
<body class="skin-blue" onload="">
	<?php top_header(); ?>
	<!-- Content Header (Page header) -->
	<section class="content-header no-padding">
		<div class="col-xs-8">
			<h1><i class="fa fa-list"></i> Merchant List</h1>
		</div>
	</section>	
	 <!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-xs-12">
				<form name="search_merchant" action="MerchantList" method="post">
				<div class="box box-primary">
					<div class="box-body no-padding" >				
						<div class="col-sm-4 form-group">
							<label>Name</label>
							<input type="text" class="form-control" name="Merchantname" id="MerchantName"  value="<?php if(isset($_SESSION['mer_sess_name']) && $_SESSION['mer_sess_name'] != '') echo unEscapeSpecialCharacters($_SESSION['mer_sess_name']); ?>" >
						</div>
						<div class="col-sm-4 form-group">
							<label>Email</label>
							<input type="text" class="form-control" id="Email" name="Email"  value="<?php if(isset($_SESSION['mer_sess_email']) && $_SESSION['mer_sess_email'] != '') echo unEscapeSpecialCharacters($_SESSION['mer_sess_email']);  ?>" >
						</div>					
						<div class="col-sm-4 form-group">
							<label>Company Name</label>
							<input type="text" class="form-control" id="CompanyName" name="CompanyName"  value="<?php if(isset($_SESSION['mer_sess_company']) && $_SESSION['mer_sess_company'] != '') echo unEscapeSpecialCharacters($_SESSION['mer_sess_company']); ?>" >
						</div>						
						<div class="col-sm-4 form-group">
							<label>Location</label>
							<input type="text"  maxlength="10" class="form-control" name="Location" id="Location" value="<?php if(isset($_SESSION['mer_sess_location']) && $_SESSION['mer_sess_location'] != '') echo unEscapeSpecialCharacters($_SESSION['mer_sess_location']); ?>" >
						</div>
						<div class="col-sm-4 form-group">
							<label>Status</label>
							<select name="Status" id="Status" class="form-control col-sm-4">
								<option value="">Select</option>
								<option value="0" <?php  if(isset($_SESSION['mer_sess_status']) && $_SESSION['mer_sess_status'] != '' && $_SESSION['mer_sess_status'] == '0') echo 'Selected';  ?> >Not Activated</option>
								<option value="1" <?php  if(isset($_SESSION['mer_sess_status']) && $_SESSION['mer_sess_status'] != '' && $_SESSION['mer_sess_status'] == '1') echo 'Selected';  ?>>Activated</option>
							</select>
						</div>
						<div class="col-sm-4 form-group">
							<label>Category</label>
							<select name="merchantCategory" id="merchantCategory" class="form-control col-sm-4">
								<option value="">Select</option>
								<?php if(isset($CategoryListResult)) { 
									foreach($CategoryListResult as $key=>$catval) { ?>
									<option value="<?php echo $catval->id; ?>" <?php  if(isset($_SESSION['mer_sess_Category']) && $_SESSION['mer_sess_Category'] == $catval->id) echo 'Selected';  ?>><?php echo $catval->CategoryName; ?></option>
								<?php } } ?>		
							</select>
						</div>
					</div>
					<div class="box-footer col-sm-12" align="center">
						<input type="submit" class="btn btn-success" name="Search" id="Search" value="Search">
					</div>					
				</div>
				</form>
			</div>
		</div>
	
	
	
		<div class="row paging">
			<div class="col-xs-12 col-sm-2">
				<?php if(isset($merchantListResult) && is_array($merchantListResult) && count($merchantListResult) > 0){ ?>
				<div class="dataTables_info">No. of Merchant(s)&nbsp;:&nbsp;<strong><?php echo $tot_rec; ?></strong> </div>
				<?php } ?>
			</div>
			<div class="col-xs-12 col-sm-10">
				<div class="dataTables_paginate paging_bootstrap row">
				<?php if(is_array($merchantListResult) && count($merchantListResult) > 0 ) {
						pagingControlLatest($tot_rec,'MerchantList'); ?>
				<?php }?>
				</div>
			</div>
		 </div>	
		
		<?php if(isset($msg) && $msg != '') { ?>
		 <div class="row">
               <div class="alert <?php  echo $class;  ?> alert-dismissable col-xs-11  col-sm-5  col-lg-3"><i class="fa fa-check"></i>  <?php echo $msg; ?></div>
		 </div>	
		<?php } ?>
		
		<div class="row">
               <div class="col-xs-12">
			   	<?php if(isset($merchantListResult) && is_array($merchantListResult) && count($merchantListResult) > 0 ) { ?>
				<form action="MerchantList" class="l_form" name="MerchantList" id="MerchantList"  method="post">
                   <div class="box">
                       <div class="box-body table-responsive no-padding">
                           <table class="table table-hover" id="myTable">
                               <tr>
                                  	<th align="center" width="1%" class="text-center"><input onclick="checkAllDelete('MerchantList');" type="Checkbox" name="checkAll"/></th>
									<th align="center" width="1%" class="text-center">#</th>												
									<th width="30%">Merchant Details</th>
									<th width="30%">Company Details</th>
									<th width="25%" align="center">Location</th>		
									<th width="8%">Image</th>							
								</tr>
                              <?php
							  	foreach($merchantListResult as $key=>$value){
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
							 ?>									
							<tr id="test_id_<?php echo $value->id;?>">
								<td align="center"><input id="checkdelete" name="checkdelete[]" value="<?php  if(isset($value->id) && $value->id != '') echo $value->id  ?>" type="checkbox" hashCount="<?php if(isset($value->hash_count) && $value->hash_count > 0 ) echo $value->hash_count; ?>"/></td>
								<td align="center" nowrap><?php echo (($_SESSION['curpage'] - 1) * ($_SESSION['perpage']))+$key+1;?></td>												
								<td>
									<div class="col-xs-11 col-md-5 col-lg-3 no-padding">
									<?php if(isset($image_path) && $image_path != ''){ ?>
									<?php if($value->Status == 0) { ?>
									
										<a data-toggle="tooltip" title="Approve" onclick="javascript:return confirm('Are you sure to approve this merchant?')" href="MerchantList?approveId=<?php if(isset($value->id) && $value->id != '') echo $value->id;?>"><i class="fa fa-thumbs-up text-olive fa-2x icon-shadow"></i></a>
									
									
									<?php } ?>
									
										<?php if(!empty($value->Icon)) { ?>
										<a href="<?php echo $image_path; ?>" class="fancybox" title="<?php echo ucfirst($value->CompanyName);?>">
											<img id="<?php echo $value->id ;?>"  width="36" height="36" align="top" class="img_border" src="<?php echo $image_path;?>" >
										</a>
										<?php } else {?>
										<img id="<?php echo $value->id ;?>"  width="36" height="36" align="top" class="img_border" src="<?php echo $image_path;?>" >
										<?php } ?>
									</div> 
									<?php } ?>								
									<div class="col-xs-12 col-md-12  col-lg-9 no-padding "> 
										<i class="fa fa-fw fa-user"></i> <?php if(isset($value->FirstName) && $value->FirstName != ''){ echo ucfirst($value->FirstName).' '; } ?>
										<?php if(isset($value->LastName) && $value->LastName != ''){ echo ucfirst($value->LastName); } ?><br>
										<?php if(isset($value->Email) && $value->Email != '' ){ ?> <i class="fa fa-fw fa-envelope"></i> <?php echo $value->Email;}?><br>								
									</div>								
									<div class="row-actions col-xs-12">																						
										<a href="MerchantManage?editId=<?php if(isset($value->id) && $value->id != '') echo $value->id; ?>" data-toggle="tooltip" data-original-title="Edit" class="edit"><i class="fa fa-edit "></i></a>		
										<a href="MerchantDetail?viewId=<?php if(isset($value->id) && $value->id != '') echo $value->id; ?>" data-toggle="tooltip" data-original-title="View" class="view"><i class="fa fa-search "></i></a>		
										<a href="<?php echo SITE_PATH.'/admin/ProductList?mer_id='.$value->id.'&cs=1'; ?>" class="view newWindow" data-toggle="tooltip" data-original-title="Product in action"><i class="fa fa fa-tags"></i></a>	
										<a href="<?php echo SITE_PATH.'/admin/OrderList?mer_id='.$value->id.'&cs=1'; ?>"  class="view newWindow" data-toggle="tooltip" data-original-title="order list"><i class="fa fa fa-shopping-cart"></i></a>
										<?php if(!empty($value->commentId)) { ?>
										<a href="<?php if(isset($value->id) && $value->id != '') echo SITE_PATH.'/admin/CommentList?mer_id='.$value->id.'&cs=1'; else echo "#"; ?>"  class="view newWindow" data-toggle="tooltip" data-original-title="comment list"><i class="fa fa-comments"></i></a>
										<?php } ?>										
										<a onclick="javascript:return confirm('Are you sure to delete?')" href="MerchantList?delId=<?php if(isset($value->id) && $value->id != '') echo $value->id;?>" data-toggle="tooltip" data-original-title="Delete" class="delete"><i class="fa fa-trash-o "></i></a>
									</div>
								</td>
								<td>
									<div class="col-xs-12 no-padding mb_wrap">
										<?php if(isset($value->CompanyName) && $value->CompanyName != ''){ ?><span><i class="fa fa-fw fa-building-o"></i>  <?php echo "<b>".ucfirst($value->CompanyName)."</b>";  ?></span><br>   <?php } ?>
										<?php if(isset($value->ShortDescription) && $value->ShortDescription != ''){ ?><span data-toggle="tooltip" title="<?php echo $value->Description; ?>"><i class="fa fa-fw fa-list-alt"></i>   <?php echo displayText($value->ShortDescription,250);?></span><br> <?php } ?>
										<?php $merchantOpeningHoursResult = $MerchantObj->selectOpeningHoursDetail($value->id);
												if(isset($merchantOpeningHoursResult) && !empty($merchantOpeningHoursResult)){
													$openinghours = openingHoursStringupdated($merchantOpeningHoursResult);													
													if(count($openinghours['Open']) > 0 ) {
														foreach($openinghours['Open'] as $val) {
															
															echo $val."<br>";
														}									
													}
													if(!empty($openinghours['Closed'])) {
														echo "<font color='#01A99A'><b>Closed ".$openinghours['Closed']."</b></font><br>";							
													} 
												}
										 ?>
										<?php if(isset($value->DiscountTier) && $value->DiscountTier != ''){?><span><?php echo "<b>Discount Tier</b> : ".$discountTierArray[$value->DiscountTier]."%";?></span><br>	 <?php  } ?>
										<?php if($value->PriceRange != 0) { $priceran = explode(',',$value->PriceRange);  echo "<b>Price Range</b> : $".$priceran[0]." to $".$priceran[1]; } ?>
									</div>	
								</td>
								<td>
									<div class="col-xs-12 no-padding mb_wrap">
									<?php 
										if(isset($value->Address) && $value->Address == '' && isset($value->PhoneNumber) && $value->PhoneNumber == '' && isset($value->WebsiteUrl) && $value->WebsiteUrl == '') {
											echo "-";
										}
									?>
									<?php if(isset($value->Address) && $value->Address != ''){ ?><span title="Address"><i class="fa fa-fw fa-map-marker"></i> <?php echo nl2br(ucfirst($value->Address)); } ?></span><br>
									<?php if(isset($value->PhoneNumber) && $value->PhoneNumber != ''){?><span title="Phone Number"><i class="fa fa-fw fa-phone"></i> <?php echo $value->PhoneNumber; } ?></span><br>
									<?php if(isset($value->WebsiteUrl) && $value->WebsiteUrl != ''){ ?><span title="Website Url"><i class="fa fa-fw fa-link"></i> <a href="<?php echo $value->WebsiteUrl;?>" target="_blank"> <?php echo $value->WebsiteUrl; } ?></a></span><br>
									</div>	
								</td>
								<?
									$cimage_path = '';
									$cphoto = $value->Image;
									$cimage_path = ADMIN_IMAGE_PATH.'no_merchant_image.jpg';
									if(isset($cphoto) && $cphoto != ''){
										$cmerchant_image = $cphoto;
										//echo MERCHANT_IMAGE_PATH_REL;
										if(SERVER){
											if(image_exists(7,$cmerchant_image))
												$cimage_path = MERCHANT_IMAGE_PATH.$cmerchant_image;
										}else{
											if(file_exists(MERCHANT_IMAGE_PATH_REL.$cmerchant_image))
												$cimage_path = MERCHANT_IMAGE_PATH.$cmerchant_image;
										}
									}
								?>
								<td style="white-space:normal">
									<div class="col-xs-10 no-padding"> 
									<?php if(!empty($value->Image)) { ?>
										<a href="<?php echo $cimage_path; ?>" class="fancybox" title="<?php echo ucfirst($value->CompanyName); ?>"><img id="<?php echo $value->id ;?>"  width="200" height="100" align="top" class="img_border" src="<?php echo $cimage_path;?>" ></a>
									<?php } else {?><img id="<?php echo $value->id ;?>"  width="200"  height="100" align="top" class="img_border" src="<?php echo $cimage_path;?>" ><?php } ?>
									</div>
								</td>	

							</tr>
							<?php } //end for ?>	
                           </table>
                       </div><!-- /.box-body -->
					  
                   </div><!-- /.box -->
				    <div class="row">
						<?php if(isset($merchantListResult) && is_array($merchantListResult) && count($merchantListResult) > 0){ ?>
						<div class="col-xs-6"><button type="submit" onclick="return deleteAll('Users');" class="btn btn-danger" name="Delete" id="Delete" value="Delete" title="Delete" alt="Delete"><i class="fa fa-trash-o"></i>&nbsp;Delete</button></div>
						<?php } ?>						
					</div>
					</form>
					
					<?php } else { ?>	
						<div class="alert alert-danger alert-dismissable col-sm-5 col-xs-11"><i class="fa fa-warning"></i> No Merchants found</div> 
					<?php } ?>	
               </div>
           </div>
	
	</section><!-- /.content -->	
						  	
<?php commonFooter(); ?>
<script type="text/javascript">
	$(document).ready(function() {
		$('.fancybox').fancybox();	
		
		$(".newWindow").fancybox({
				scrolling: 'auto',			
				type: 'iframe',
				width: '800',
				maxWidth: '100%',
				
				fitToView: false,
			});
	});
$(document).ready(function() {
	 $("table#myTable tr").not(':first').hover(
	     function () {
	      jQuery(this).find("div.row-actions a").css("visibility","visible");
	     }, 
	     function () {
	      jQuery(this).find("div.row-actions a").css("visibility","hidden");
	     }
	  );
});
</script>
</html>