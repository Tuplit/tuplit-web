<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
commonHead();
require_once('controllers/UserController.php');
$userObj   =   new UserController();

$display   =   'none';
$class  =  $msg    = $cover_path = $class_icon   = '';
if(isset($_GET['cs']) && $_GET['cs']=='1') {
	destroyPagingControlsVariables();
	unset($_SESSION['tuplit_sess_user_platform']);
	unset($_SESSION['tuplit_sess_user_name']);
	unset($_SESSION['tuplit_sess_email']);
	unset($_SESSION['tuplit_sess_user_status']);
	unset($_SESSION['tuplit_sess_country']);
	unset($_SESSION['tuplit_sess_spent']);
	unset($_SESSION['tuplit_sess_spent7']);
	unset($_SESSION['tuplit_sess_order']);
	unset($_SESSION['tuplit_sess_user_registerdate']);
	if(isset($_SESSION['tuplit_ses_from_timeZone']))
		unset($_SESSION['tuplit_ses_from_timeZone']);
}

if(isset($_POST['Search']) && $_POST['Search'] != ''){
	destroyPagingControlsVariables();
	$_POST          = unEscapeSpecialCharacters($_POST);
    $_POST          = escapeSpecialCharacters($_POST);
	//print_r($_POST);die();
	if(isset($_POST['Platform']))
		$_SESSION['tuplit_sess_user_platform'] 	    = $_POST['Platform'];
	if(isset($_POST['UserName']))
		$_SESSION['tuplit_sess_user_name'] 	= $_POST['UserName'];
	if(isset($_POST['Email']))
		$_SESSION['tuplit_sess_email']	    = $_POST['Email'];
	if(isset($_POST['Status']))
		$_SESSION['tuplit_sess_user_status']	= $_POST['Status'];
	if(isset($_POST['Country']))
		$_SESSION['tuplit_sess_country']	= $_POST['Country'];
	if(isset($_POST['TotalSpent']))
		$_SESSION['tuplit_sess_spent']	= $_POST['TotalSpent'];
	if(isset($_POST['TotalSpent7']))
		$_SESSION['tuplit_sess_spent7']	= $_POST['TotalSpent7'];
	if(isset($_POST['Ordercount']))
		$_SESSION['tuplit_sess_order']	= $_POST['Ordercount'];
	if(isset($_POST['SearchDate']) && $_POST['SearchDate'] != ''){
		$validate_date = dateValidation($_POST['SearchDate']);
		if($validate_date == 1){
			$date = date('Y-m-d',strtotime($_POST['SearchDate']));
			if($date != '' && $date != '1970-01-01' && $date != '0000-00-00' )
				$_SESSION['tuplit_sess_user_registerdate']	= $date;
			else 
				$_SESSION['tuplit_sess_user_registerdate']	= '';
		}
		else 
			$_SESSION['tuplit_sess_user_registerdate']	= '';
	}
	else 
		$_SESSION['tuplit_sess_user_registerdate']	= '';
}
if(isset($_POST['Delete']) && $_POST['Delete'] != ''){
	if(isset($_POST['checkdelete']) && is_array($_POST['checkdelete']) && count($_POST['checkdelete']) > 0)
		$delete_id = implode(',',$_POST['checkdelete']);
}
if(isset($_GET['delId']) && $_GET['delId']!=''){
	$delete_id      = $_GET['delId'];
}

if(isset($delete_id) && $delete_id != ''){	
	$userObj->deleteUserReleatedEntries($delete_id);
	$field			 = " Photo ";
	$delete             = explode(',',$delete_id);
	if(isset($delete) && is_array($delete) && count($delete) >0 )
	{
		foreach($delete as $key=>$value){
			$unlink_comdition   = " id = ".$value;
			$userListResult     = $userObj->selectUserDetails($field,$unlink_comdition);
			if(isset($userListResult) && is_array($userListResult) && count($userListResult) > 0){
				if(isset($userListResult[0]->Photo) && $userListResult[0]->Photo != ''){
					$user_image = $userListResult[0]->Photo;	
					if(SERVER){
						deleteImages(1,$user_image);
						deleteImages(2,$user_image);
					}
					else{
						if(file_exists(USER_THUMB_IMAGE_PATH_REL . $user_image))
							unlink(USER_THUMB_IMAGE_PATH_REL . $user_image);
						if(file_exists(USER_IMAGE_PATH_REL . $user_image))
							unlink(USER_IMAGE_PATH_REL . $user_image);
					}
				}
			}
		}
	}
	//header("location:UserList?msg=3");	
	header("location:Customers?msg=3");
}

if(isset($_GET['editId']) && $_GET['editId']!=''){
	$condition = " id = ".$_GET['editId'];
	$update_string = " Status = ".$_GET['status'];
	$userListResult  = $userObj->updateUserDetails($update_string,$condition);
	header("location:UserList?msg=4");
}
setPagingControlValues('u.id',ADMIN_PER_PAGE_LIMIT);
$fields    = " u.* ";
$condition = " and u.Status in (1,2)";
$userListResult  = $userObj->getUserList($fields,$condition);
$tot_rec 		 = $userObj->getTotalRecordCount();
if($tot_rec!=0 && !is_array($userListResult)) {
	$_SESSION['curpage'] = 1;
	$userListResult  = $userObj->getUserList($fields,$condition);
}

if(isset($_GET['msg']) && $_GET['msg'] == 1){
	$msg 		= 	"Customer added successfully";
	$display	=	"block";
	$class 		= 	"alert-success";
	$class_icon = "fa-check";
}
else if(isset($_GET['msg']) && $_GET['msg'] == 2){
	$msg 		= 	"Customer updated successfully";
	$display	=	"block";
	$class 		= 	"alert-success";
	$class_icon = "fa-check";
}
else if(isset($_GET['msg']) && $_GET['msg'] == 3){
	$msg 		= 	"Customer deleted successfully";
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
else if(isset($_GET['msg']) && $_GET['msg'] == 5){
	$msg 		= 	"Message sent successfully";
	$display	=	"block";
	$class 		= 	"alert-success";
	$class_icon = "fa-check";
}
?>
<style>
.img_border{border-radius: 43px;}
</style>
<body class="skin-blue">
	<?php top_header(); ?>
	<!-- Content Header (Page header) -->
	<section class="content-header no-padding">
		<div class="col-xs-12 col-sm-7">
			<h1><i class="fa fa-list"></i> Customer List</h1>
		</div>
		<!--<div class="col-sm-5 col-xs-12"><h3><a href="UserManage" title="Add Customer"><i class="fa fa-plus-circle"></i> Add Customer</a></h3></div>-->
	</section>
	<!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-xs-12">
				<form name="search_category" action="UserList" method="post">
				<div class="box box-primary box-padding report">
					<div class="box-body no-padding" >
						<div class="col-sm-3 col-xs-12 form-group">
							<!--<label>Name</label>-->
							<input type="text" placeholder="Name" class="form-control" name="UserName" id="UserName"  value="<?php  if(isset($_SESSION['tuplit_sess_user_name']) && $_SESSION['tuplit_sess_user_name'] != '') echo unEscapeSpecialCharacters($_SESSION['tuplit_sess_user_name']);  ?>" >
						</div>
						<div class="col-sm-3 col-xs-12 form-group">
							<!--<label>Email</label>-->
							<input type="text" placeholder="Email" class="form-control" id="Email" name="Email"  value="<?php  if(isset($_SESSION['tuplit_sess_email']) && $_SESSION['tuplit_sess_email'] != '') echo unEscapeSpecialCharacters($_SESSION['tuplit_sess_email']);  ?>" >
						</div>
						<div class="col-sm-3 col-xs-12 form-group">
							<!--<label>Country</label>-->
							<input type="text" placeholder="Country" class="form-control" id="Country" name="Country"  value="<?php  if(isset($_SESSION['tuplit_sess_country']) && $_SESSION['tuplit_sess_country'] != '') echo unEscapeSpecialCharacters($_SESSION['tuplit_sess_country']);  ?>" >
						</div>
						<div class="col-sm-3 col-xs-12  form-group">
							<!--<label>Total spent</label>-->
							<input type="text" placeholder="Total Spent" class="form-control" name="TotalSpent" id="TotalSpent" onkeypress="return isNumberKey(event);" value="<?php  if(isset($_SESSION['tuplit_sess_spent']) && $_SESSION['tuplit_sess_spent'] != '') echo unEscapeSpecialCharacters($_SESSION['tuplit_sess_spent']);  ?>" >
						</div>
						<div class="col-sm-3 col-xs-12 form-group">
							<!--<label>Total spent in 7 days</label>-->
							<input type="text" placeholder="Total spent in 7 days" class="form-control" id="TotalSpent7" name="TotalSpent7" onkeypress="return isNumberKey(event);" value="<?php if(isset($_SESSION['tuplit_sess_spent7']) && $_SESSION['tuplit_sess_spent7'] != '') echo unEscapeSpecialCharacters($_SESSION['tuplit_sess_spent7']);  ?>" >
						</div>
						<!--
						<div class="col-xs-6  col-sm-4  form-group">
							<label>Status</label>
							<select name="Status" id="Status" class="form-control col-sm-4">
								<option value="">Select</option>
								<option value="1" <?php  if(isset($_SESSION['tuplit_sess_user_status']) && $_SESSION['tuplit_sess_user_status'] != '' && $_SESSION['tuplit_sess_user_status'] == '1') echo 'Selected';  ?> >Active</option>
								<option value="2" <?php  if(isset($_SESSION['tuplit_sess_user_status']) && $_SESSION['tuplit_sess_user_status'] != '' && $_SESSION['tuplit_sess_user_status'] == '2') echo 'Selected';  ?>>Inactive</option>
							</select>
						</div>
						<div class="col-xs-6  col-sm-4  form-group">
							<label>Platform</label>
							<select name="Platform" id="Platform" class="form-control col-sm-4">
								<option value="">Select</option>
								<?php if(isset($platformArray) && is_array($platformArray) && count($platformArray) > 0 ) { 
										foreach($platformArray as $platformkey=>$platformvalue) { ?>
								<option value="<?php echo $platformkey;?>" <?php if(isset($_SESSION['tuplit_sess_user_platform']) && $_SESSION['tuplit_sess_user_platform'] == $platformkey && $_SESSION['tuplit_sess_user_platform'] != '') echo 'selected';?> ><?php echo $platformvalue; ?></option>
								<?php } } ?>
							</select>
						</div>-->
						<div class="col-sm-3 col-xs-12 form-group">
							<!--<label>Order count</label>-->
							<input type="text" placeholder="Order count" class="form-control" id="Ordercount" name="Ordercount" onkeypress="return isNumberKey(event);" value="<?php  if(isset($_SESSION['tuplit_sess_order']) && $_SESSION['tuplit_sess_order'] != '') echo unEscapeSpecialCharacters($_SESSION['tuplit_sess_order']);  ?>" >
						</div>
					</div>
					<!--<div class="col-xs-12 ">
						<h3 class="box-title no-padding" data-toggle="collapse" href="#advSearchBlock"   title="Advanced Search Options" style="cursor:pointer"><i class="fa fa-plus"></i> Advanced Search Options</h3>							
					</div>-->
					<div class="col-sm-4 col-xs-12 form-group">
					<div id="advSearchBlock" class="col-xs-12 no-padding panel-collapse collapse" style="display:block;">
							<!--<label>Registered Date</label>-->
							<div class="col-xs-6 no-padding mb_clr"><input type="text" placeholder="Registered Date" maxlength="10" class="form-control col-sm-4 fleft" name="SearchDate" id="SearchDate" title="Select Date" value="<?php if(isset($_SESSION['tuplit_sess_user_registerdate']) && $_SESSION['tuplit_sess_user_registerdate'] != '') echo date('m/d/Y',strtotime($_SESSION['tuplit_sess_user_registerdate'])); else echo '';?>" > </div>
						</div>
					</div>
					<div class="col-sm-12 clear" align="center">
						<input type="submit" class="btn btn-success" name="Search" id="Search" value="Search">
					</div>
				</div>
				</form>
			</div>
		</div>
		<div class="row paging paging-margin report">
			<div class="col-xs-12 col-sm-2 no-padding">
				<?php if(isset($userListResult) && is_array($userListResult) && count($userListResult) > 0){ ?>
				<div class="dataTables_info"><span class="white_txt">No. of Customer(s)&nbsp:&nbsp;<strong><?php echo $tot_rec; ?></strong> </span></div>
				<?php } ?>
			</div>
			<div class="col-xs-12 col-sm-10">
				<div class="dataTables_paginate paging_bootstrap row">
				<?php if(is_array($userListResult) && count($userListResult) > 0 ) {
						pagingControlLatest($tot_rec,'UserList'); ?>
				<?php }?>
				</div>
			</div>
		 </div>	
		
		<?php if(isset($msg) && $msg != '') { ?>
		 <div class="row">
               <div align="center" class="alert <?php  echo $class;  ?> alert-dismissable col-xs-11  col-sm-4"><i class="fa fa-check"></i>  <?php echo $msg; ?></div>
		 </div>	
		<?php } ?>
		
		<div class="row">
               <div class="col-xs-12">
			   	<?php if(isset($userListResult) && is_array($userListResult) && count($userListResult) > 0 ) { ?>
				<form action="UserList" class="l_form" name="UserListForm" id="UserListForm"  method="post">
                   <div class="box">
                       <div class="box-body table-responsive no-padding no-margin">
                           <table class="table table-hover">
                               <tr>
                                  	<th align="center" width="2%" class="text-center"><input onclick="checkAllDelete('UserListForm');" type="Checkbox" name="checkAll"/></th>
									<th align="center" width="3%" class="text-center">#</th>												
									<th width="25%" align="left" style="text-align:left;">Customer Details<?php //echo SortColumn('UserName','Username'); ?></th>
									<!--<th width="15%">Social Media Ids</th>-->
									<th width="20%">Location Details</th>
									<!--<th width="3%">Platform</th>-->
									<th width="5%"><?php echo SortColumn('DateCreated','Registered Date'); ?></th>
                               </tr>
                              <?php 	foreach($userListResult as $key=>$value){
									    $image_path = '';
										$photo = $value->Photo;
										$original_path = $image_path = ADMIN_IMAGE_PATH.'no_user.jpeg';
										
										if(isset($photo) && $photo != ''){
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
										}
							 ?>									
							<tr id="test_id_<?php echo $value->id;?>">
								<td align="center"><input id="checkdelete" name="checkdelete[]" value="<?php  if(isset($value->id) && $value->id != '') echo $value->id  ?>" type="checkbox" hashCount="<?php if(isset($value->hash_count) && $value->hash_count > 0 ) echo $value->hash_count; ?>"/></td>
								<td align="center" nowrap><?php echo (($_SESSION['curpage'] - 1) * ($_SESSION['perpage']))+$key+1;?></td>												
								<td align="left" style="text-align:left;">
								<?php if(isset($image_path) && $image_path != ''){ ?>
									<div class="col-xs-2 col-md-1 no-padding">
										<?php if(isset($image_path) && basename($image_path) != "no_user.jpeg") { ?>
										<a href="<?php echo $original_path; ?>" class="fancybox" title="<?php echo  ucfirst($value->FirstName).' '.ucfirst($value->LastName);?>"  > 
											<img id="<?php echo $value->id ;?>"  width="36" height="36" align="top" class="img_border" src="<?php echo $image_path;?>" >
										</a>
										<?php } else { ?>
										<div class="no_photo img36 img_border"><i class="fa fa-user"></i></div>
										<? } ?>
									</div>
								<?php } ?>
									<div class="col-xs-10 col-md-11 "> 								
										<?php if(isset($value->FirstName) && $value->FirstName != ''){ echo ucfirst($value->FirstName).' '; } ?>								
									
										<?php if(isset($value->LastName) && $value->LastName != ''){ echo ucfirst($value->LastName); } ?><br>
										<i class="fa fa-fw fa-envelope"></i> <?php if(isset($value->Email) && $value->Email != '' ){ echo $value->Email;} ?><br>
										<?php if(isset($value->CellNumber) && $value->CellNumber != ''){?><i class="fa fa-fw fa-phone"></i>  <?php echo $value->CellNumber; } ?>
									</div>
									<div style="float:left;display:none;" class="row-actions col-xs-12"><!--inline-block;-->
										<?php if($value->Status == 1) { ?><a class="active_icon" onclick="javascript:return confirm('Are you sure want to change the status?')" href="UserList?status=2&editId=<?php if(isset($value->id) && $value->id != '') echo $value->id;?>" title="Click to Inactive"><i class="fa fa-user "></i></a><?php } else { ?><a class="inactive_icon" onclick="javascript:return confirm('Are you sure you want to change the status?')" title="Click to Active" href="UserList?status=1&editId=<?php if(isset($value->id) && $value->id != '') echo $value->id;?>"><i class="fa fa-user "></i></a><?php } ?>
																						
										<a href="UserManage?editId=<?php if(isset($value->id) && $value->id != '') echo $value->id; ?>" title="Edit"  class="edit"><i class="fa fa-edit "></i></a>
		
										<a href="UserDetail?viewId=<?php if(isset($value->id) && $value->id != '') echo $value->id; ?>" title="View"  class="view"><i class="fa fa-search "></i></a>	<!-- data-toggle="tooltip" -->
											
										<a href="<?php if(isset($value->id) && $value->id != '') echo SITE_PATH.'/admin/OrderList?user_id='.$value->id.'&cs=1'; else echo "#"; ?>"  class="view newWindow" data-toggle="tooltip" data-original-title="order list"><i class="fa fa-shopping-cart"></i></a>
										<a href="<?php if(isset($value->id) && $value->id != '') echo SITE_PATH.'/admin/CommentList?user_id='.$value->id.'&cs=1'; else echo "#"; ?>"  class="view newWindow" data-toggle="tooltip" data-original-title="comment list"><i class="fa fa-comments"></i></a>
										<a href="UserBalance?walletId=<?php if(isset($value->WalletId) && $value->WalletId != '') echo $value->WalletId; ?>" title="Balance"  class="view newWindow"><i class="fa fa-money"></i></a>
											
										<a onclick="javascript:return confirm('Are you sure to delete?')" href="UserList?delId=<?php if(isset($value->id) && $value->id != '') echo $value->id;?>" title="Delete" class="delete"><i class="fa fa-trash-o "></i></a>
									</div>
								</td>
								<!--<td>
								<?php if( $value->FBId == '' && $value->GooglePlusId == ''){
										echo '-';
									} else {  if(isset($value->FBId) && $value->FBId != ''){ ?>
									<i class="fa fa-fw fa-facebook" title="facebook"></i><?php echo $value->FBId;?></br>
								<?php }  if(isset($value->GooglePlusId) && $value->GooglePlusId != ''){ ?>
									<i class="fa fa-fw fa-google-plus" title="googleplus"></i><?php echo $value->GooglePlusId; ?>
								<?php } } ?>
								</td>-->			
								<td>
									<?php 
									if(isset($value->Country) && $value->Country != ''){
										echo ucwords($value->Country);
									}else{
										echo "-";
									}
									/*if($value->Location == '' && $value->Country == '' && $value->ZipCode == ''){ 
											echo '-';
										 } else { ?>
									 <div class="col-xs-2 col-md-1 no-padding"><i class="fa fa-lg fa-map-marker "></i></div> 
									 <div class="col-xs-10 col-md-11 no-padding">
										<?php if(isset($value->Location) && $value->Location != ''){ echo ucfirst($value->Location).'</br>'; }//else echo '-';?>
										
										<?php if(isset($value->Country) && $value->Country != ''){ echo ucfirst($value->Country).'</br>'; }//else echo '-';?>
										
										<?php if(isset($value->ZipCode) && $value->ZipCode != ''){ echo $value->ZipCode; }//else echo '-';?><?php //if(isset($value->Location) && $value->Location != ''){ echo $value->Location; }else echo '-';?>
									</div>	
									<?php } */?>
								</td>	
								<!--<td><?php //if(isset($value->Platform) && $value->Platform != ''){ echo $platformArray[$value->Platform]; }else echo '-';?></td>-->	
								<td><?php if(isset($value->DateCreated) && $value->DateCreated != '0000-00-00 00:00:00'){ echo date('m/d/Y',strtotime($value->DateCreated)); }else echo '-';?></td>
							</tr>
							<?php } //end for ?>	
                           </table>
                       </div><!-- /.box-body -->
                   </div><!-- /.box -->
				    <div class="row">
						<?php if(isset($userListResult) && is_array($userListResult) && count($userListResult) > 0){ ?>
						<div class="col-xs-12">
						<!--<div class="btn-inline">
							<button type="submit" onclick="return deleteAll('Users');" class="btn btn-danger" name="Delete" id="Delete" value="Delete" title="Delete" alt="Delete"><i class="fa fa-trash-o"></i>&nbsp;Delete</button>
						</div>-->
						<div class="btn-inline">
							<a href="javascript:void(0);" onclick="return pushNotificationCheck(this);"  class="btn btn-success pnclass"  name="sendPush" id="sendPush" value="sendPush">Send Push Notification </a>
						</div>
						<div class="btn-inline">
							<a href="SendPushNotification?cs=1" onclick=""  class="notification_popup btn btn-success"  name="sendPushAll" id="sendPushAll" value="sendPushAll">Send Push Notification To All</a>
						</div>
						</div>
						<?php } ?>
						<div class="col-xs-6"> </div>
					</div>
					</form>
					
					<?php } else { ?>	
						<div class="alert alert-danger alert-dismissable col-sm-4 col-xs-12 "><i class="fa fa-warning"></i> No Customer found</div> 
					<?php } ?>	
               </div>
           </div>
	</section><!-- /.content -->	

	
<?php commonFooter(); ?>
<script type="text/javascript">
$(document).ready(function() {
	$('.fancybox').fancybox();
	$(".fancybox-manual-b").click(function() {
		$.fancybox.open({
			href : '<?php echo SITE_PATH.'/admin/UserList?cs=1'?>',
			type : 'iframe',
			padding : 5
		});
	})
	$(".notification_popup").fancybox({
			
			type : 'iframe',
			width:"700", 
			height:"100%",
		}
	);
	$(".newWindow").fancybox({
				scrolling: 'auto',			
				type: 'iframe',
				width: '800',
				maxWidth: '100%',
				
				fitToView: false,
			});
	
});
//$(".user_image_pop_up").colorbox({title:true});
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
