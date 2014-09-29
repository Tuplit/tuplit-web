<?php 

require_once('includes/CommonIncludes.php');
admin_login_check();
commonHead();
require_once('controllers/CurrencyController.php');
$currencyObj   		=   new CurrencyController();

if(isset($_GET['cs']) && $_GET['cs']=='1') {
	destroyPagingControlsVariables();
	unset($_SESSION['tuplit_sess_Currency_code']);
	unset($_SESSION['tuplit_sess_Currency_name']);
	unset($_SESSION['tuplit_sess_Currency_status']);
	unset($_SESSION['tuplit_sess_Location_code']);
	unset($_SESSION['tuplit_sess_Currency_registerdate']);
}

if(isset($_POST['Search']) && $_POST['Search'] != ''){
	destroyPagingControlsVariables();
	$_POST          = unEscapeSpecialCharacters($_POST);
    $_POST          = escapeSpecialCharacters($_POST);
	if(isset($_POST['Location'])) {
		$location									= $_POST['Location'];
		$_SESSION['tuplit_sess_Location_code'] 		= $_POST['Location'];
	}
	if(isset($_POST['CurrencyCode']))
		$_SESSION['tuplit_sess_Currency_code'] 		= $_POST['CurrencyCode'];
	if(isset($_POST['CurrencyName']))
		$_SESSION['tuplit_sess_Currency_name'] 		= $_POST['CurrencyName'];
	if(isset($_POST['Status']))
		$_SESSION['tuplit_sess_Currency_status']	= $_POST['Status'];
	if(isset($_POST['SearchDate']) && $_POST['SearchDate'] != ''){
		$validate_date = dateValidation($_POST['SearchDate']);
		if($validate_date == 1){
			$date = date('Y-m-d',strtotime($_POST['SearchDate']));
			if($date != '' && $date != '1970-01-01' && $date != '0000-00-00' )
				$_SESSION['tuplit_sess_Currency_registerdate']	= $date;
			else 
				$_SESSION['tuplit_sess_Currency_registerdate']	= '';
		}
		else 
			$_SESSION['tuplit_sess_Currency_registerdate']	= '';
	}
	else 
		$_SESSION['tuplit_sess_Currency_registerdate']	= '';
}
if(isset($_POST['Delete']) && $_POST['Delete'] != ''){
	if(isset($_POST['checkdelete']) && is_array($_POST['checkdelete']) && count($_POST['checkdelete']) > 0)
		$delete_id = implode(',',$_POST['checkdelete']);
}
if(isset($_GET['delId']) && $_GET['delId']!=''){
	$delete_id      = $_GET['delId'];
}

if(isset($delete_id) && $delete_id != ''){	
	$currencyObj->deleteCurrency($delete_id);
	header("location:CurrencyList?msg=3");
}

if(isset($_GET['editId']) && $_GET['editId']!=''){
	$condition = " id = ".$_GET['editId'];	
	$update_string = " Status = ".$_GET['status'];
	$currencyObj->updateDetails($update_string,$condition);
	header("location:CurrencyList?msg=4");
}
setPagingControlValues('id',ADMIN_PER_PAGE_LIMIT);
$CurrencyListResult  	= $currencyObj->getCurrencyList();
$tot_rec 		 		= $currencyObj->getTotalRecordCount();
if($tot_rec!=0 && !is_array($CurrencyListResult)) {
	$_SESSION['curpage'] = 1;
}
if(isset($_GET['msg']) && $_GET['msg'] == 1){
	$msg 		= 	"Currency added successfully";
	$display	=	"block";
	$class 		= 	"alert-success";
	$class_icon = 	"fa-check";
}
else if(isset($_GET['msg']) && $_GET['msg'] == 2){
	$msg 		= 	"Currency updated successfully";
	$display	=	"block";
	$class 		= 	"alert-success";
	$class_icon = 	"fa-check";
}
else if(isset($_GET['msg']) && $_GET['msg'] == 3){
	$msg 		= 	"Currency deleted successfully";
	$display	=	"block";
	$class 		= 	"alert-success";
	$class_icon = 	"alert-success";
}
else if(isset($_GET['msg']) && $_GET['msg'] == 4){
	$msg 		= 	"Status changed successfully";
	$display	=	"block";
	$class 		= 	"alert-success";
	$class_icon = 	"fa-check";
}

$LocationList		=	$currencyObj->getLocationList();
?>
<body class="skin-blue">
	<?php top_header(); ?>
	
	<!-- Content Header (Page header) -->
	<section class="content-header no-padding">
		<div class="col-xs-7">
			<h1><i class="fa fa-list"></i> Currency List</h1>
		</div>
		<div class="col-sm-5 col-xs-12"><h3><a href="CurrencyManage" title="Add Currency"><i class="fa fa-plus-circle"></i> Add Currency</a></h3></div>
	</section>
	
	 <!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-xs-12">
				<form name="search_Currency" action="CurrencyList" method="post">
				<div class="box box-primary">
					<div class="box-body no-padding" >
						<div class="col-sm-4 col-lg-3 form-group">
							<label>Location</label>
							<select name="Location" id="Location"  class="form-control col-sm-4">
								<option value="">Select</option>
								<?php if(isset($LocationList) && count($LocationList) > 0) { 
										foreach($LocationList as $val) { ?>
											<option value="<?php echo $val->id; ?>"  <?php if(isset($location) && $location == $val->id) echo "selected"; ?>><?php echo ucfirst($val->Location); ?></option>
								<?php } } ?>
							</select>
						</div>
						<div class="col-sm-4 col-lg-2 form-group">
							<label>Currency Code</label>
							<input type="text" class="form-control" name="CurrencyCode" id="CurrencyCode"  value="<?php  if(isset($_SESSION['tuplit_sess_Currency_code']) && $_SESSION['tuplit_sess_Currency_code'] != '') echo unEscapeSpecialCharacters($_SESSION['tuplit_sess_Currency_code']);  ?>" >
						</div>
						<div class="col-sm-4 col-lg-2 form-group">
							<label>Currency Name</label>
							<input type="text" class="form-control" name="CurrencyName" id="CurrencyName"  value="<?php  if(isset($_SESSION['tuplit_sess_Currency_name']) && $_SESSION['tuplit_sess_Currency_name'] != '') echo unEscapeSpecialCharacters($_SESSION['tuplit_sess_Currency_name']);  ?>" >
						</div>
						<div class="col-sm-4 col-lg-2 form-group">
							<label>Status</label>
							<select name="Status" id="Status"  class="form-control col-sm-4">
								<option value="">Select</option>
								<option value="1" <?php  if(isset($_SESSION['tuplit_sess_Currency_status']) && $_SESSION['tuplit_sess_Currency_status'] != '' && $_SESSION['tuplit_sess_Currency_status'] == '1') echo 'Selected';  ?> >Active</option>
								<option value="2" <?php  if(isset($_SESSION['tuplit_sess_Currency_status']) && $_SESSION['tuplit_sess_Currency_status'] != '' && $_SESSION['tuplit_sess_Currency_status'] == '2') echo 'Selected';  ?>>Inactive</option>
							</select>
						</div>
						<div class="col-sm-5 col-lg-3 col-xs-12 form-group">
							<label>Created Date</label>
							<div class="col-xs-6 no-padding"> <input type="text"  maxlength="10" class="form-control  fleft" name="SearchDate" id="SearchDate" title="Select Date" value="<?php if(isset($_SESSION['tuplit_sess_Currency_registerdate']) && $_SESSION['tuplit_sess_Currency_registerdate'] != '') echo date('m/d/Y',strtotime($_SESSION['tuplit_sess_Currency_registerdate'])); else echo '';?>" ></div>
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
				<?php if(isset($CurrencyListResult) && is_array($CurrencyListResult) && count($CurrencyListResult) > 0){ ?>
				<div class="dataTables_info">No. of Currency(s)&nbsp:&nbsp;<strong><?php echo $tot_rec; ?></strong> </div>
				<?php } ?>
			</div>
			<div class="col-xs-12 col-sm-10">
				<div class="dataTables_paginate paging_bootstrap row">
				<?php if(is_array($CurrencyListResult) && count($CurrencyListResult) > 0 ) {
							 	pagingControlLatest($tot_rec,'CurrencyList'); ?>
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
			 	<?php if(isset($CurrencyListResult) && is_array($CurrencyListResult) && count($CurrencyListResult) > 0 ) {?> 
               <div class="col-xs-12">
			  
				<form action="CurrencyList" class="l_form" name="CurrencyListForm" id="CurrencyListForm"  method="post">
                   <div class="box">
                       <div class="box-body table-responsive no-padding">
					  
                           <table class="table table-hover">
                               <tr>
                                  	<th align="center" width="1%" style="text-align:center"><input type="checkbox" onclick="checkAllDelete('CurrencyListForm');"  name="checkAll" id="checkAll"/></th>
									<th align="center" width="2%" style="text-align:center">#</th>												
									<th width="30%">Currency Details</th>
									<th>&nbsp;</th>
									<?php if(count($CurrencyListResult) > 1) { ?>
										<th align="center" width="1%" style="text-align:center"><!--<input type="checkbox" onclick="checkAllDelete('CurrencyListForm');"  name="checkAll" id="checkAll"/>--></th>
										<th align="center" width="2%" style="text-align:center">#</th>												
										<th width="30%">Currency Details</th>
									<th>&nbsp;</th>
									<?php } ?>
                               </tr>
							
                              <?php $i = 0;
							  foreach($CurrencyListResult as $key=>$value){  $i++;   if( $i%2 == 0) { ?>
								<!-- first col -->
									<td align="center"><input id="checkdelete" name="checkdelete[]" value="<?php  if(isset($value->id) && $value->id != '') echo $value->id  ?>" type="checkbox" hashCount="<?php if(isset($value->hash_count) && $value->hash_count > 0 ) echo $value->hash_count; ?>"/></td>
									<td align="center" nowrap><?php echo (($_SESSION['curpage'] - 1) * ($_SESSION['perpage']))+$key+1;?></td>												
									<td>
										<div class="mb_wrap">											
											<div class="col-xs-10 no-padding">
												<b>Code &nbsp;: </b><?php if(isset($value->Code) && $value->Code != '') echo strtoupper($value->Code); else echo '-';   ?></br>
												<b>Location &nbsp;: </b><?php if(isset($value->LocationName) && $value->LocationName != '') echo ucfirst($value->LocationName); else echo '-';   ?></br>
												<b>Name : </b><?php if(isset($value->Currency) && $value->Currency != '') echo ucfirst($value->Currency);  else echo '-'; ?></br>
												<i class="fa fa-fw fa-calendar"></i> <?php if(isset($value->DateCreated) && $value->DateCreated != '0000-00-00 00:00:00'){ echo date('m/d/Y',strtotime($value->DateCreated)); }else echo '-';?>
											</div> 
											<div class="row-actions col-xs-12">
												<?php if($value->Status == 1) { ?><a class="active_icon" onclick="javascript:return confirm('Are you sure want to change the status?')" href="CurrencyList?status=2&editId=<?php if(isset($value->id) && $value->id != '') echo $value->id;?>" title="Click to Inactive"><i class="fa fa-money "></i></a><?php } else { ?><a class="inactive_icon" onclick="javascript:return confirm('Are you sure you want to change the status?')" title="Click to Active" href="CurrencyList?status=1&editId=<?php if(isset($value->id) && $value->id != '') echo $value->id;?>"><i class="fa fa-money "></i></a><?php } ?>
												<a href="CurrencyManage?editId=<?php if(isset($value->id) && $value->id != '') echo $value->id; ?>" title="Edit" data-toggle="tooltip" alt="Edit" class="edit"><i class="fa fa-edit "></i></a>
												<a onclick="javascript:return confirm('Are you sure to delete?') " href="CurrencyList?delId=<?php if(isset($value->id) && $value->id != '') echo $value->id;?>" title="Delete" data-toggle="tooltip" alt="Delete" class="delete"><i class="fa fa-trash-o "></i></a>
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
										<div class="col-xs-10 no-padding"> 
											<b>Code &nbsp;: </b><?php if(isset($value->Code) && $value->Code != '') echo strtoupper($value->Code); else echo '-';   ?></br>
											<b>Location &nbsp;: </b><?php if(isset($value->LocationName) && $value->LocationName != '') echo ucfirst($value->LocationName); else echo '-';   ?></br>
											<b>Name : </b><?php if(isset($value->Currency) && $value->Currency != '') echo ucfirst($value->Currency);  else echo '-'; ?></br>
											<i class="fa fa-fw fa-calendar"></i> <?php if(isset($value->DateCreated) && $value->DateCreated != '0000-00-00 00:00:00'){ echo date('m/d/Y',strtotime($value->DateCreated)); }else echo '-';?>
										</div> 
										<div class="row-actions col-xs-12">
											<?php if($value->Status == 1) { ?><a class="active_icon" onclick="javascript:return confirm('Are you sure want to change the status?')" href="CurrencyList?status=2&editId=<?php if(isset($value->id) && $value->id != '') echo $value->id;?>" title="Click to Inactive"><i class="fa fa-money "></i></a><?php } else { ?><a class="inactive_icon" onclick="javascript:return confirm('Are you sure you want to change the status?')" title="Click to Active" href="CurrencyList?status=1&editId=<?php if(isset($value->id) && $value->id != '') echo $value->id;?>"><i class="fa fa-money "></i></a><?php } ?>
											<a href="CurrencyManage?editId=<?php if(isset($value->id) && $value->id != '') echo $value->id; ?>" title="Edit" data-toggle="tooltip" class="edit"><i class="fa fa-edit "></i></a>
											<a onclick="javascript:return confirm('Are you sure to delete?') " href="CurrencyList?delId=<?php if(isset($value->id) && $value->id != '') echo $value->id;?>" title="Delete" data-toggle="tooltip" class="delete"><i class="fa fa-trash-o "></i></a>
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
						<?php if(isset($CurrencyListResult) && is_array($CurrencyListResult) && count($CurrencyListResult) > 0){ ?>
						<div class="col-xs-6"><button type="submit" onclick="return deleteAll('currencies');" class="btn btn-danger" name="Delete" id="Delete" value="Delete" title="Delete" alt="Delete"><i class="fa fa-trash-o"></i>&nbsp;Delete</button></div>
						<?php } ?>
						<div class="col-xs-6"> </div>
					</div>
					</form>
               </div>
			   
			   <?php } else { ?>	
						<div class="alert alert-danger alert-dismissable col-sm-5  col-xs-11 "><i class="fa fa-warning"></i> No Currency found</div> 
					<?php } ?>	
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
$(document).ready(function() {
	 $('#CurrencyCode').keyup(function() {
        $(this).val($(this).val().toUpperCase());
    });
});
</script>

</html>
