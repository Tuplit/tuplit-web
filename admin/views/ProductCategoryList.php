<?php 
	
require_once('includes/CommonIncludes.php');
admin_login_check();
commonHead();
require_once('controllers/ManagementController.php');
$managementObj   =   new ManagementController();
require_once('controllers/MerchantController.php');
$merchantObj   =   new MerchantController();
$display   =   'none';
$class  =  $msg    = $cover_path = $class_icon   = '';
if(isset($_GET['cs']) && $_GET['cs']=='1') {
	destroyPagingControlsVariables();
	unset($_SESSION['tuplit_sess_product_category_name']);
	unset($_SESSION['tuplit_sess_product_category_status']);
	unset($_SESSION['tuplit_sess_product_category_registerdate']);
	unset($_SESSION['tuplit_sess_product_category_merchant']);
	if(isset($_SESSION['tuplit_ses_from_timeZone']))
		unset($_SESSION['tuplit_ses_from_timeZone']);
}
$_SESSION['tuplit_sess_search_type']=1;
if(isset($_POST['Search']) && $_POST['Search'] != ''){
	destroyPagingControlsVariables();
	$_POST          = unEscapeSpecialCharacters($_POST);
    $_POST          = escapeSpecialCharacters($_POST);
	if(isset($_POST['CategoryName']))
		$_SESSION['tuplit_sess_product_category_name'] 	= $_POST['CategoryName'];
	if(isset($_POST['Status']))
		$_SESSION['tuplit_sess_product_category_status']	= $_POST['Status'];
	if(isset($_POST['Merchant']))
		$_SESSION['tuplit_sess_product_category_merchant']	= $_POST['Merchant'];
	if(isset($_POST['SearchDate']) && $_POST['SearchDate'] != ''){
		$validate_date = dateValidation($_POST['SearchDate']);
		if($validate_date == 1){
			$date = date('Y-m-d',strtotime($_POST['SearchDate']));
			if($date != '' && $date != '1970-01-01' && $date != '0000-00-00' )
				$_SESSION['tuplit_sess_product_category_registerdate']	= $date;
			else 
				$_SESSION['tuplit_sess_product_category_registerdate']	= '';
		}
		else 
			$_SESSION['tuplit_sess_product_category_registerdate']	= '';
	}
	else 
		$_SESSION['tuplit_sess_product_category_registerdate']	= '';
} 
  
if(isset($_GET['editId']) && $_GET['editId']!=''){
	$condition = " id = ".$_GET['editId'];
	$update_string = " Status = ".$_GET['status'];
	$CategoryListResult  = $managementObj->updateProductCategoryDetails($update_string,$condition);
	header("location:ProductCategoryList?msg=4");
}
setPagingControlValues('id',ADMIN_PER_PAGE_LIMIT);
if(isset($_GET['show']) && $_GET['show'] == '2' ){
	$_SESSION['tuplit_sess_search_type']=2;
	$fields    = " pc.*,m.CompanyName,m.Icon ";
	$join_condition 	= ' LEFT JOIN  merchants as m ON (m.id = pc.fkMerchantId)';
	$condition = " and pc.Status in (1,2) and pc.fkMerchantId != 0";
}else{
	/*$_SESSION['tuplit_sess_search_type']=1;
	$fields    = " pc.* ";
	$join_condition 	= '';
	$condition = " and pc.Status in (1,2) and pc.fkMerchantId = 0";*/
	$_SESSION['tuplit_sess_search_type']=2;
	$fields    = " pc.*,m.CompanyName,m.Icon ";
	$join_condition 	= ' LEFT JOIN  merchants as m ON (m.id = pc.fkMerchantId)';
	$condition = " and pc.Status in (1,2) and pc.fkMerchantId != 0";
}

$CategoryListResult  = $managementObj->getProductCategoryList($fields,$condition,$join_condition );
$tot_rec 		 = $managementObj->getTotalRecordCount();
if($tot_rec!=0 && !is_array($CategoryListResult)) {
	$_SESSION['curpage'] = 1;
	$CategoryListResult  = $managementObj->getCategoryList($fields,$condition);
}

if(isset($_GET['msg']) && $_GET['msg'] == 1){
	$msg 		= 	"Product Category added successfully";
	$display	=	"block";
	$class 		= 	"alert-success";
	$class_icon = "fa-check";
}
else if(isset($_GET['msg']) && $_GET['msg'] == 2){
	$msg 		= 	"Product Category updated successfully";
	$display	=	"block";
	$class 		= 	"alert-success";
	$class_icon = "fa-check";
}

else if(isset($_GET['msg']) && $_GET['msg'] == 4){
	$msg 		= 	"Status changed successfully";
	$display	=	"block";
	$class 		= 	"alert-success";
	$class_icon = "fa-check";
}
$fields   		= " CompanyName,id";
$condition		= " Status in (1) ORDER BY CompanyName";
$MerchantList   = $merchantObj->selectMerchantDetails($fields,$condition);
if(!isset($_GET['show']))
	$_GET['show'] = 1;
?>
<body class="skin-blue">
	<?php top_header(); ?>
	
	<!-- Content Header (Page header) -->
	<section class="content-header no-padding">
		<div class="col-xs-12 col-sm-7">
			<h1><i class="fa fa-list"></i>&nbsp;Product Category List</h1>
		</div>
		<!--<div class="col-xs-12 col-sm-5"><h3><a href="ProductCategoryManage?type=1" class="addProductCategory"><i class="fa fa-plus-circle"></i> Add Product Category</a></h3></div>-->
	</section>
	
	 <!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-xs-12">
				<form name="search_category" action="ProductCategoryList?show=<?php if(isset($_GET['show'])) echo $_GET['show']; else echo '1' ;?>" method="post">
				<div class="box box-primary">
					
					<div class="box-body no-padding" >
				
						<div class="col-sm-3 form-group">
							<label>Category Name</label>
							<input type="text" class="form-control" name="CategoryName" id="CategoryName"  value="<?php  if(isset($_SESSION['tuplit_sess_product_category_name']) && $_SESSION['tuplit_sess_product_category_name'] != '') echo unEscapeSpecialCharacters($_SESSION['tuplit_sess_product_category_name']);  ?>" >
						</div>
						<?php if(isset($_SESSION['tuplit_sess_search_type']) && $_SESSION['tuplit_sess_search_type'] == 2){?>
						<div class="col-sm-4 form-group">
							<label>Merchant Name</label>
							<select name="Merchant" id="Merchant" class="form-control col-sm-4">
								<option value="">Select</option>
								<?php if(isset($MerchantList)) { 
									foreach($MerchantList as $key=>$val) { ?>
									<option value="<?php echo $val->id; ?>" <?php if(isset($_SESSION['tuplit_sess_product_category_merchant']) && $_SESSION['tuplit_sess_product_category_merchant'] ==  $val->id) echo "selected";?>><?php echo $val->CompanyName; ?></option>
								<?php } } ?>		
							</select>
						</div>
						<?php } ?>
						<div class="col-sm-2 form-group">
							<label>Status</label>
							<select name="Status" id="Status"  class="form-control col-sm-4">
								<option value="">Select</option>
								<option value="1" <?php  if(isset($_SESSION['tuplit_sess_product_category_status']) && $_SESSION['tuplit_sess_product_category_status'] != '' && $_SESSION['tuplit_sess_product_category_status'] == '1') echo 'Selected';  ?> >Active</option>
								<option value="2" <?php  if(isset($_SESSION['tuplit_sess_product_category_status']) && $_SESSION['tuplit_sess_product_category_status'] != '' && $_SESSION['tuplit_sess_product_category_status'] == '2') echo 'Selected';  ?>>Inactive</option>
							</select>
						</div>
						<div class="col-sm-3 col-xs-12 form-group">
							<label>Created Date</label>
							<div class="col-xs-6 no-padding"> <input type="text"  maxlength="10" class="form-control  fleft" name="SearchDate" id="SearchDate" title="Select Date" value="<?php if(isset($_SESSION['tuplit_sess_product_category_registerdate']) && $_SESSION['tuplit_sess_product_category_registerdate'] != '') echo date('m/d/Y',strtotime($_SESSION['tuplit_sess_product_category_registerdate'])); else echo '';?>" ></div>
							<div class="col-xs-6">(mm/dd/yyyy)</div>
						</div>
						
					</div>
					<div class="box-footer col-xs-12" align="center">
						<input type="submit" class="btn btn-success" name="Search" id="Search" value="Search">
					</div>
					
				</div>
				</form>
			</div>
		</div>
		<div class="row paging">
			<div class="col-xs-12 col-sm-2">
				<?php if(isset($CategoryListResult) && is_array($CategoryListResult) && count($CategoryListResult) > 0){ ?>
				<div class="dataTables_info">No. of Category(s)&nbsp:&nbsp;<strong><?php echo $tot_rec; ?></strong> </div>
				<?php } ?>
			</div>
			<div class="col-xs-12 col-sm-10">
				<div class="dataTables_paginate paging_bootstrap row">
				<?php if(is_array($CategoryListResult) && count($CategoryListResult) > 0 ) {
							 	pagingControlLatest($tot_rec,'ProductCategoryList?show='.$_GET['show']); ?>
				<?php }?>
				</div>
			</div>
		 </div>	
		
		<?php if(isset($msg) && $msg != '') { ?>
		 <div class="row">
               <div align="center" class="alert <?php  echo $class;  ?> alert-dismissable col-sm-5   col-lg-3 col-xs-11"><i class="fa fa-check"></i>  <?php echo $msg; ?></div>
		 </div>	
		<?php } ?>
		<div class="row">
			<div class="col-md-12">
		     <div class="">
                  <ul class="">
                      <!--<li <?php if(isset($_SESSION['tuplit_sess_search_type']) && $_SESSION['tuplit_sess_search_type'] == 1) { ?> class="active" <?php } ?>><a href="ProductCategoryList?show=1&cs=1" title="Admin Created" >Admin Created</a></li>-->
                     <!-- <li <?php if(isset($_SESSION['tuplit_sess_search_type']) && $_SESSION['tuplit_sess_search_type'] == 2) { ?> class="active" <?php } ?>><a href="ProductCategoryList?show=2&cs=1"  title="Merchant Created" >Merchant Created</a></li>-->
                  </ul>
                  <!--<div class="tab-content">-->
				  	  <?php if(isset($CategoryListResult) && is_array($CategoryListResult) && count($CategoryListResult) > 0 ) {?> 
                      <div  class="tab-pane active">
                          <form action="ProductCategoryList?show=<?php if(isset($_GET['show'])) echo $_GET['show']; else echo '1' ;?>" class="l_form" name="ProductCategoryList" id="ProductCategoryList"  method="post">
						  <div class="box">
		                       <div class="box-body table-responsive no-padding">
		                           <table class="table table-hover">
		                               <tr>
											<th align="center" width="2%" style="text-align:center">#</th>												
											<th width="%">Category Details</th>
											<th width="">&nbsp;</th>
											<?php if(count($CategoryListResult) > 1) { ?>
												<th align="center" width="2%" style="text-align:center">#</th>												
												<th width="%">Category Details</th>
											<th>&nbsp;</th>
											<?php } ?>
		                               </tr>
		                              <?php $i = 0;
									  foreach($CategoryListResult as $key=>$value){
											$i++;
											if(isset($_SESSION['tuplit_sess_search_type']) && $_SESSION['tuplit_sess_search_type'] == 2) { 
												$icon_image_path = '';
												$merchant_image = $value->Icon;
												$icon_image_path = ADMIN_IMAGE_PATH.'no_user.jpeg';
												if(isset($merchant_image) && $merchant_image != ''){
													if(SERVER){
														if(image_exists(6,$merchant_image))
															$icon_image_path = MERCHANT_ICONS_IMAGE_PATH.$merchant_image;
													}
													else{
														if(file_exists(MERCHANT_ICONS_IMAGE_PATH_REL.$merchant_image))
															$icon_image_path = MERCHANT_ICONS_IMAGE_PATH.$merchant_image;
													}
												}
											}
									 ?>									
									<?php 
										if( $i%2 == 0) { ?>
										<!-- first col -->
											<td align="center" nowrap><?php echo (($_SESSION['curpage'] - 1) * ($_SESSION['perpage']))+$key+1;?></td>												
											<td>
													<div class="mb_wrap_sm"> <?php if(isset($value->CategoryName) && $value->CategoryName != ''){ echo ucfirst($value->CategoryName).' ';  } ?></br>
													<i class="fa fa-fw fa-calendar"></i> <?php if(isset($value->DateCreated) && $value->DateCreated != '0000-00-00 00:00:00'){ echo date('m/d/Y',strtotime($value->DateCreated)); }else echo '-';?></br>
													<?php if(isset($_SESSION['tuplit_sess_search_type']) && $_SESSION['tuplit_sess_search_type'] == 2) { ?>
														<?php if(!empty($value->Icon)) { ?>
															 <a href="<?php echo $icon_image_path; ?>" class="fancybox" title="<?php echo $value->CompanyName; ?>">
																<img width="25" height="25" align="top" class="img_border" src="<?php echo $icon_image_path;?>" >
															 </a>
														<?php } else { ?>
															<div class="no_photo img25 valign"><i class="fa fa-user"></i></div><!-- <img width="25" height="25" align="top" class="img_border" src="<?php echo $icon_image_path;?>" > -->
														<?php }?>
														<!-- <a href="<?php //echo SITE_PATH.'/admin/MerchantDetail?viewId='.$value->fkMerchantId; ?>" class="fancybox" title="<?php //echo $value->CompanyName; ?>"> -->
													<?php	if($value->CompanyName != ''){?>
															<span title="Merchant Name">
														<?php echo "<b>".$value->CompanyName."</b>";  ?>
													</span> 
												<?php	} }?>
													</div> 
												
												<div class="row-actions">
												<?php if($value->Status == 1) { ?>
													<!--<a class="active_icon" onclick="javascript:return confirm('Are you sure want to change the status?')" href="ProductCategoryList?status=2&editId=<?php if(isset($value->id) && $value->id != '') echo $value->id;?>" alt="Click to Inactive" title="Click to Inactive"><i class="fa fa-thumbs-up "></i></a>-->
												<?php } else { ?>
													<!--<a class="inactive_icon" onclick="javascript:return confirm('Are you sure you want to change the status?')" title="Click to Active" alt="Click to Active" href="ProductCategoryList?status=1&editId=<?php if(isset($value->id) && $value->id != '') echo $value->id;?>"><i class="fa fa-thumbs-o-down "></i></a>-->
													<?php } ?>
													<a href="ProductCategoryManage?type=1&editId=<?php if(isset($value->id) && $value->id != '') echo $value->id; ?>&show=<?php if(isset($_GET['show'])) echo $_GET['show']; else echo '1' ;?>" title="" alt="Edit" class="addProductCategory edit"><i class="fa fa-edit "></i></a>
					
												<!--<a href="ProductCategoryManage?type=2&viewId=<?php //if(isset($value->id) && $value->id != '') echo $value->id; ?>&show=<?php //if(isset($_GET['show'])) echo $_GET['show']; else echo '1' ;?>" title="View" alt="View" class="addProductCategory view"><i class="fa fa-search "></i></a>	-->
															
												</div>
											</td>
											<td width="25%">&nbsp;</td>
											</tr>
										<?php  } else if( $i%2 != 0)   {?>
											<tr id="test_id_<?php echo $value->id;?>">
											<!-- sec col -->
											<td align="center" nowrap><?php echo (($_SESSION['curpage'] - 1) * ($_SESSION['perpage']))+$key+1;?></td>												
											<td>
													<div class="mb_wrap_sm"> <?php if(isset($value->CategoryName) && $value->CategoryName != ''){ echo ucfirst($value->CategoryName).' '; } ?></br>
													<i class="fa fa-fw fa-calendar"></i> <?php if(isset($value->DateCreated) && $value->DateCreated != '0000-00-00 00:00:00'){ echo date('m/d/Y',strtotime($value->DateCreated)); }else echo '-';?></br>
													<?php if(isset($_SESSION['tuplit_sess_search_type']) && $_SESSION['tuplit_sess_search_type'] == 2) { ?>
														
														<?php if(!empty($value->Icon)) { ?>
															 <a href="<?php echo $icon_image_path; ?>" class="fancybox" title="<?php echo $value->CompanyName; ?>">
																<img width="25" height="25" align="top" class="img_border" src="<?php echo $icon_image_path;?>" >
															 </a>
														<?php } else { ?>
															<div class="no_photo img25 valign"><i class="fa fa-user"></i></div><!-- <img width="25" height="25"align="top" class="img_border" src="<?php echo $icon_image_path;?>" > -->
														<?php }?>
														<!-- <a href="<?php //echo SITE_PATH.'/admin/MerchantDetail?viewId='.$value->fkMerchantId; ?>" class="fancybox" title="<?php //echo $value->CompanyName; ?>"> -->
													<?php	if($value->CompanyName != ''){?>
															<span title="Merchant Name">
														<?php echo "<b>".$value->CompanyName."</b>";  ?>
													</span> 
												<?php	} }?>
													</div> 
												
												<div class="row-actions">
												<?php if($value->Status == 1) { ?>
													<a class="active_icon" onclick="javascript:return confirm('Are you sure want to change the status?')" href="ProductCategoryList?status=2&editId=<?php if(isset($value->id) && $value->id != '') echo $value->id;?>"  data-placement="top"  data-toggle="tooltip" title="Click to Inactive"><i class="fa fa-thumbs-up "></i></a><?php } else { ?><a class="inactive_icon" onclick="javascript:return confirm('Are you sure you want to change the status?')" title="Click to Active" data-toggle="tooltip" href="ProductCategoryList?status=1&editId=<?php if(isset($value->id) && $value->id != '') echo $value->id;?>"><i class="fa fa-thumbs-o-down "></i></a><?php } ?>
																									
												<a href="ProductCategoryManage?type=1&editId=<?php if(isset($value->id) && $value->id != '') echo $value->id; ?>&show=<?php if(isset($_GET['show'])) echo $_GET['show']; else echo '1' ;?>" title=""  data-placement="top" data-toggle="tooltip" class="addProductCategory edit"><i class="fa fa-edit "></i></a>
					
												<!--<a href="ProductCategoryManage?type=2&viewId=<?php //if(isset($value->id) && $value->id != '') echo $value->id; ?>&show=<?php //if(isset($_GET['show'])) echo $_GET['show']; else echo '1' ;?>" title="View" data-toggle="tooltip" class="addProductCategory view"><i class="fa fa-search "></i></a>	-->
															
												</div>
											</td>
											<td width="25%">&nbsp;</td>
										<?php } ?>
									<?php } //end for ?>	
		                           </table>
		                       </div><!-- /.box-body -->
                  			 </div><!-- /.box -->
							</form> 
                      </div><!-- /.tab-pane -->
					  <?php } else { ?>	
					  <div class="alert alert-danger alert-dismissable col-sm-5  col-lg-3 col-xs-11"><i class="fa fa-warning"></i> No Product Category found</div> 
					  <?php } ?>	
                 <!-- </div><!-- /.tab-content -->
              </div>
			  </div>
		 </div>
		
	</section><!-- /.content -->	
<?php commonFooter(); ?>
<script type="text/javascript">
$("#SearchDate").datepicker({
	showButtonPanel	:	true,        
    buttonText		:	'',
    buttonImageOnly	:	true,
    buttonImage		:	path+'webresources/images/calender.png',
    dateFormat		:	'mm/dd/yy',
	changeMonth		:	true,
	changeYear		:	true,
	hideIfNoPrevNext:	true,
	showWeek		:	true,
	yearRange		:	"c-30:c",
	maxDate			:	"0",
	closeText		:   "Close"
   });
</script>
<script type="text/javascript">
$(document).ready(function() {
	$(".addProductCategory").fancybox({
			scrolling: 'none',			
			type: 'iframe',
			width: '380',
			maxWidth: '100%',  // for respossive width set					
			fitToView: false
	});

});
$(document).ready(function() {
	$('.fancybox').fancybox();	
});
</script>
</html>
