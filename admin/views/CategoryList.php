<?php 

require_once('includes/CommonIncludes.php');
admin_login_check();
commonHead();
require_once('controllers/ManagementController.php');
$managementObj   =   new ManagementController();
$display   =   'none';
$class  =  $msg    = $cover_path = $class_icon   = '';
if(isset($_GET['cs']) && $_GET['cs']=='1') {
	destroyPagingControlsVariables();
	unset($_SESSION['tuplit_sess_Category_name']);
	unset($_SESSION['tuplit_sess_Category_status']);
	unset($_SESSION['tuplit_sess_Category_registerdate']);
	if(isset($_SESSION['tuplit_ses_from_timeZone']))
		unset($_SESSION['tuplit_ses_from_timeZone']);
}
if(isset($_POST['Search']) && $_POST['Search'] != ''){
	destroyPagingControlsVariables();
	$_POST          = unEscapeSpecialCharacters($_POST);
    $_POST          = escapeSpecialCharacters($_POST);
	if(isset($_POST['CategoryName']))
		$_SESSION['tuplit_sess_Category_name'] 	= $_POST['CategoryName'];
	if(isset($_POST['Status']))
		$_SESSION['tuplit_sess_Category_status']	= $_POST['Status'];
	if(isset($_POST['SearchDate']) && $_POST['SearchDate'] != ''){
		$validate_date = dateValidation($_POST['SearchDate']);
		if($validate_date == 1){
			$date = date('Y-m-d',strtotime($_POST['SearchDate']));
			if($date != '' && $date != '1970-01-01' && $date != '0000-00-00' )
				$_SESSION['tuplit_sess_Category_registerdate']	= $date;
			else 
				$_SESSION['tuplit_sess_Category_registerdate']	= '';
		}
		else 
			$_SESSION['tuplit_sess_Category_registerdate']	= '';
	}
	else 
		$_SESSION['tuplit_sess_Category_registerdate']	= '';
}
if(isset($_POST['Delete']) && $_POST['Delete'] != ''){
	if(isset($_POST['checkdelete']) && is_array($_POST['checkdelete']) && count($_POST['checkdelete']) > 0)
		$delete_id = implode(',',$_POST['checkdelete']);
}
if(isset($_GET['delId']) && $_GET['delId']!=''){
	$delete_id      = $_GET['delId'];
}

if(isset($delete_id) && $delete_id != ''){	
	$managementObj->deleteCategoryReleatedEntries($delete_id);
	$field			 = " CategoryIcon ";
	$delete             = explode(',',$delete_id);
	if(isset($delete) && is_array($delete) && count($delete) >0 )
	{
		foreach($delete as $key=>$value){
			$unlink_comdition   = " id = ".$value;
			$CategoryListResult     = $managementObj->selectCategoryDetails($field,$unlink_comdition);
			if(isset($CategoryListResult) && is_array($CategoryListResult) && count($CategoryListResult) > 0){
				if(isset($CategoryListResult[0]->CategoryIcon) && $CategoryListResult[0]->CategoryIcon != ''){
					$Category_image = $CategoryListResult[0]->CategoryIcon;	
					if(SERVER){
						deleteImages(3,$Category_image);
					}
					else{
						if(file_exists(CATEGORY_IMAGE_PATH_REL . $Category_image))
							unlink(CATEGORY_IMAGE_PATH_REL . $Category_image);
					}
				}
			}
		}
	}
	header("location:CategoryList?msg=3");	
}

if(isset($_GET['editId']) && $_GET['editId']!=''){
	$condition = " id = ".$_GET['editId'];
	$update_string = " Status = ".$_GET['status'];
	$CategoryListResult  = $managementObj->updateCategoryDetails($update_string,$condition);
	header("location:CategoryList?msg=4");
}
setPagingControlValues('id',ADMIN_PER_PAGE_LIMIT);
$fields    = " c.* ";
$condition = " and c.Status in (1,2)";
$CategoryListResult  = $managementObj->getCategoryList($fields,$condition);
$tot_rec 		 = $managementObj->getTotalRecordCount();
if($tot_rec!=0 && !is_array($CategoryListResult)) {
	$_SESSION['curpage'] = 1;
	$CategoryListResult  = $managementObj->getCategoryList($fields,$condition);
}

if(isset($_GET['msg']) && $_GET['msg'] == 1){
	$msg 		= 	"Category added successfully";
	$display	=	"block";
	$class 		= 	"alert-success";
	$class_icon = "fa-check";
}
else if(isset($_GET['msg']) && $_GET['msg'] == 2){
	$msg 		= 	"Category updated successfully";
	$display	=	"block";
	$class 		= 	"alert-success";
	$class_icon = "fa-check";
}
else if(isset($_GET['msg']) && $_GET['msg'] == 3){
	$msg 		= 	"Category deleted successfully";
	$display	=	"block";
	$class 		= 	"alert-success";
		$class_icon          = "alert-success";
}
else if(isset($_GET['msg']) && $_GET['msg'] == 4){
	$msg 		= 	"Status changed successfully";
	$display	=	"block";
	$class 		= 	"alert-success";
	$class_icon = "fa-check";
}

?>
<body class="skin-blue">
	<?php top_header(); ?>
	
	<!-- Content Header (Page header) -->
	<section class="content-header no-padding">
		<div class="col-xs-7">
			<h1><i class="fa fa-list"></i> Category List</h1>
		</div>
		<div class="col-sm-5 col-xs-12"><h3><a href="CategoryManage" title="Add Category"><i class="fa fa-plus-circle"></i> Add Category</a></h3></div>
	</section>
	
	 <!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-xs-12">
				<form name="search_category" action="CategoryList" method="post">
				<div class="box box-primary">
					<!-- <div class="box-header">
						<div class="pull-right box-tools">
							<button class="btn btn-danger btn-sm" data-widget='collapse' data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
						</div>
						<i class="fa  fa-search"></i>
						<h3 class="box-title">Search</h3>
					</div> -->
					<div class="box-body no-padding" >
				
						<div class="col-sm-3 form-group">
							<label>Category Name</label>
							<input type="text" class="form-control" name="CategoryName" id="CategoryName"  value="<?php  if(isset($_SESSION['tuplit_sess_Category_name']) && $_SESSION['tuplit_sess_Category_name'] != '') echo unEscapeSpecialCharacters($_SESSION['tuplit_sess_Category_name']);  ?>" >
						</div>
						<div class="col-sm-3 form-group">
							<label>Status</label>
							<select name="Status" id="Status"  class="form-control col-sm-4">
								<option value="">Select</option>
								<option value="1" <?php  if(isset($_SESSION['tuplit_sess_Category_status']) && $_SESSION['tuplit_sess_Category_status'] != '' && $_SESSION['tuplit_sess_Category_status'] == '1') echo 'Selected';  ?> >Active</option>
								<option value="2" <?php  if(isset($_SESSION['tuplit_sess_Category_status']) && $_SESSION['tuplit_sess_Category_status'] != '' && $_SESSION['tuplit_sess_Category_status'] == '2') echo 'Selected';  ?>>Inactive</option>
							</select>
						</div>
						<div class="col-sm-5 col-xs-12 form-group">
							<label>Created Date</label>
							<div class="col-xs-6 no-padding"> <input type="text"  maxlength="10" class="form-control  fleft" name="SearchDate" id="SearchDate" title="Select Date" value="<?php if(isset($_SESSION['tuplit_sess_Category_registerdate']) && $_SESSION['tuplit_sess_Category_registerdate'] != '') echo date('m/d/Y',strtotime($_SESSION['tuplit_sess_Category_registerdate'])); else echo '';?>" ></div>
							<div class="col-xs-6 LH30">(mm/dd/yyyy)</div>
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
							 	pagingControlLatest($tot_rec,'CategoryList'); ?>
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
			 	<?php if(isset($CategoryListResult) && is_array($CategoryListResult) && count($CategoryListResult) > 0 ) {?> 
               <div class="col-xs-12">
			  
				<form action="CategoryList" class="l_form" name="CategoryListForm" id="CategoryListForm"  method="post">
                   <div class="box">
                       <div class="box-body table-responsive no-padding">
					  
                           <table class="table table-hover">
                               <tr>
                                  	<th align="center" width="1%" style="text-align:center"><input type="checkbox" onclick="checkAllDelete('CategoryListForm');"  name="checkAll" id="checkAll"/></th>
									<th align="center" width="2%" style="text-align:center">#</th>												
									<th width="30%">Category Details</th>
									<th>&nbsp;</th>
									<?php if(count($CategoryListResult) > 1) { ?>
										<th align="center" width="1%" style="text-align:center"><input type="checkbox" onclick="checkAllDelete('CategoryListForm');"  name="checkAll" id="checkAll"/></th>
										<th align="center" width="2%" style="text-align:center">#</th>												
										<th width="30%">Category Details</th>
									<th>&nbsp;</th>
									<?php } ?>
                               </tr>
							
                              <?php $i = 0;
							  foreach($CategoryListResult as $key=>$value){
									    $image_path = '';
										$photo = $value->CategoryIcon;
										$original_path = ADMIN_IMAGE_PATH.'no_category.jpeg';
										if(isset($photo) && $photo != ''){
											$Category_image = $photo;
											if(SERVER){
												if(image_exists(3,$Category_image))
													$original_path = CATEGORY_IMAGE_PATH.$Category_image;
											}else{
												if(file_exists(CATEGORY_IMAGE_PATH_REL.$Category_image))
													$original_path = CATEGORY_IMAGE_PATH.$Category_image;
											}
										}
									$i++;
								
							 ?>									
							
							<?php 
								if( $i%2 == 0) { ?>
								<!-- first col -->
									<td align="center"><input id="checkdelete" name="checkdelete[]" value="<?php  if(isset($value->id) && $value->id != '') echo $value->id  ?>" type="checkbox" hashCount="<?php if(isset($value->hash_count) && $value->hash_count > 0 ) echo $value->hash_count; ?>"/></td>
									<td align="center" nowrap><?php echo (($_SESSION['curpage'] - 1) * ($_SESSION['perpage']))+$key+1;?></td>												
									<td>
									<div class="mb_wrap">
											<?php if(isset($original_path) && $original_path != ''){ ?>
												<div class="col-xs-2 col-sm-2 col-lg-1 no-padding"><a <?php if(isset($original_path) && basename($original_path) != "no_category.jpeg") { ?>href="<?php echo $original_path; ?>" class="Category_image_pop_up" title="View Photo" <?php } ?> ><img width="36" height="36" align="top" class="img_border" src="<?php echo $original_path;?>" ></a></div>
											<?php } ?>
											<div class="col-xs-10">
												<?php if(isset($value->CategoryName) && $value->CategoryName != ''){ echo ucfirst($value->CategoryName).' ';  } ?></br>
												<i class="fa fa-fw fa-calendar"></i> <?php if(isset($value->DateCreated) && $value->DateCreated != '0000-00-00 00:00:00'){ echo date('m/d/Y',strtotime($value->DateCreated)); }else echo '-';?>
											</div> 
											<div class="row-actions col-xs-12">
												<?php if($value->Status == 1) { ?>
												<a class="active_icon" onclick="javascript:return confirm('Are you sure want to change the status?')" href="CategoryList?status=2&editId=<?php if(isset($value->id) && $value->id != '') echo $value->id;?>" alt="Click to Inactive" data-toggle="tooltip" title="Click to Inactive"><i class="fa fa-tag"></i></a><?php } else { ?><a class="inactive_icon" onclick="javascript:return confirm('Are you sure you want to change the status?')" title="Click to Active" alt="Click to Active" href="CategoryList?status=1&editId=<?php if(isset($value->id) && $value->id != '') echo $value->id;?>"><i class="fa fa-tag"></i></a><?php } ?>
												<a href="CategoryManage?editId=<?php if(isset($value->id) && $value->id != '') echo $value->id; ?>" title="Edit" data-toggle="tooltip" alt="Edit" class="edit"><i class="fa fa-edit "></i></a>
												<a href="CategoryDetail?viewId=<?php if(isset($value->id) && $value->id != '') echo $value->id; ?>" title="View" data-toggle="tooltip" alt="View" class="view"><i class="fa fa-search "></i></a>	
												<a onclick="javascript:return confirm('Are you sure to delete?') " href="CategoryList?delId=<?php if(isset($value->id) && $value->id != '') echo $value->id;?>" title="Delete" data-toggle="tooltip" alt="Delete" class="delete"><i class="fa fa-trash-o "></i></a>
											</div>
										</div>
									</td>
									<td width="10%">&nbsp;</td>
								</tr>
								<?php  } else if( $i%2 != 0)   {?>
									<tr id="test_id_<?php echo $value->id;?>">
									<!-- sec col -->
									<td align="center"><input id="checkdelete" name="checkdelete[]" value="<?php  if(isset($value->id) && $value->id != '') echo $value->id  ?>" type="checkbox" hashCount="<?php if(isset($value->hash_count) && $value->hash_count > 0 ) echo $value->hash_count; ?>"/></td>
									<td align="center" nowrap><?php echo (($_SESSION['curpage'] - 1) * ($_SESSION['perpage']))+$key+1;?></td>												
									<td>
									<div class="mb_wrap_sm">
										<?php if(isset($original_path) && $original_path != ''){ ?>
											<div class="col-xs-2 col-sm-2 col-lg-1 no-padding"><a <?php if(isset($original_path) && basename($original_path) != "no_category.jpeg") { ?>href="<?php echo $original_path; ?>" class="Category_image_pop_up" title="View Photo" <?php } ?> ><img width="36" height="36" align="top" class="img_border" src="<?php echo $original_path;?>" ></a></div>
										<?php } ?>
										<div class="col-xs-10"> <?php if(isset($value->CategoryName) && $value->CategoryName != ''){ echo ucfirst($value->CategoryName).' '; } ?></br>
										<i class="fa fa-fw fa-calendar"></i> <?php if(isset($value->DateCreated) && $value->DateCreated != '0000-00-00 00:00:00'){ echo date('m/d/Y',strtotime($value->DateCreated)); }else echo '-';?></div> 
										<div class="row-actions col-xs-12">
											<?php if($value->Status == 1) { ?>
											<a class="active_icon" onclick="javascript:return confirm('Are you sure want to change the status?')" href="CategoryList?status=2&editId=<?php if(isset($value->id) && $value->id != '') echo $value->id;?>" data-toggle="tooltip" title="Click to Inactive"><i class="fa fa-tag "></i></a><?php } else { ?><a class="inactive_icon" onclick="javascript:return confirm('Are you sure you want to change the status?')" title="Click to Active" data-toggle="tooltip" href="CategoryList?status=1&editId=<?php if(isset($value->id) && $value->id != '') echo $value->id;?>"><i class="fa fa-tag "></i></a><?php } ?>
																							
											<a href="CategoryManage?editId=<?php if(isset($value->id) && $value->id != '') echo $value->id; ?>" title="Edit" data-toggle="tooltip" class="edit"><i class="fa fa-edit "></i></a>
			
											<a href="CategoryDetail?viewId=<?php if(isset($value->id) && $value->id != '') echo $value->id; ?>" title="View" data-toggle="tooltip" class="view"><i class="fa fa-search "></i></a>	
													
											<a onclick="javascript:return confirm('Are you sure to delete?') " href="CategoryList?delId=<?php if(isset($value->id) && $value->id != '') echo $value->id;?>" title="Delete" data-toggle="tooltip" class="delete"><i class="fa fa-trash-o "></i></a>
										</div>
									</div>
									</td>
									<td width="10%">&nbsp;</td>
								<?php } ?>
								
							
							<?php } //end for ?>	
                           </table>
                       </div><!-- /.box-body -->
                   </div><!-- /.box -->
				    <div class="row">
						<?php if(isset($CategoryListResult) && is_array($CategoryListResult) && count($CategoryListResult) > 0){ ?>
						<div class="col-xs-6"><button type="submit" onclick="return deleteAll('Categorys');" class="btn btn-danger" name="Delete" id="Delete" value="Delete" title="Delete" alt="Delete"><i class="fa fa-trash-o"></i>&nbsp;Delete</button></div>
						<?php } ?>
						<div class="col-xs-6"> </div>
					</div>
					</form>
               </div>
			   
			   <?php } else { ?>	
						<div class="alert alert-danger alert-dismissable col-sm-5  "><i class="fa fa-warning"></i> No Category found</div> 
					<?php } ?>	
           </div>
	</section><!-- /.content -->	
<?php commonFooter(); ?>
<script type="text/javascript">
$(".Category_image_pop_up").fancybox({title:true});
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

</html>
