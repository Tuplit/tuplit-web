<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
require_once('controllers/AdminController.php');
$msg = '';
require_once('controllers/TransferController.php');
$transferObj   =   new TransferController();
commonHead(); 
$errorMessage = 'No Record Found';
if(isset($_GET['cs']) && $_GET['cs']=='1') { 
	destroyPagingControlsVariables();	
	unset($_SESSION['tuplit_sess_DebitedName']);
	//unset($_SESSION['tuplit_sess_CreditedName']);
	unset($_SESSION['tuplit_sess_TransferAmount']);
	unset($_SESSION['tuplit_sess_TransferDateFrom']);
	unset($_SESSION['tuplit_sess_TransferDateTo']);
}

//echo "==>".__line__."<====<pre>";print_r($_POST);echo "</pre>=====";

if(isset($_POST['Search']) && $_POST['Search'] != ''){
	$_POST	=	escapeSpecialCharacters($_POST);
    if(isset($_POST['DebitedName']) && $_POST['DebitedName'] != ''){
		$DebitedName							=	$_POST['DebitedName'];
		$_SESSION['tuplit_sess_DebitedName'] 	= 	$DebitedName;
	}
	/* if(isset($_POST['CreditedName']) && $_POST['CreditedName'] != ''){
		$CreditedName							=	$_POST['CreditedName'];
		$_SESSION['tuplit_sess_CreditedName'] 	= 	$CreditedName;
	}*/
	 if(isset($_POST['TransferAmount']) && $_POST['TransferAmount'] != ''){
		$TransferAmount							=	$_POST['TransferAmount'];
		$_SESSION['tuplit_sess_TransferAmount'] = 	$TransferAmount;
	}
	 if(isset($_POST['TransferDateFrom']) && $_POST['TransferDateFrom'] != ''){
		$TransferDateFrom							=	$_POST['TransferDateFrom'];
		$_SESSION['tuplit_sess_TransferDateFrom'] 	= 	$TransferDateFrom;
	}
	 if(isset($_POST['TransferDateTo']) && $_POST['TransferDateTo'] != ''){
		$TransferDateTo								=	$_POST['TransferDateTo'];
		$_SESSION['tuplit_sess_TransferDateTo'] 	= 	$TransferDateTo;
	}
}
setPagingControlValues('t.id',ADMIN_PER_PAGE_LIMIT);
$condition = '';
$transferList  	= $transferObj->getTransferList($condition);
$tot_rec 		= $transferObj->getTotalRecordCount();
$userListTemp	= $transferObj->getUserDetail();
if(isset($userListTemp) && !empty($userListTemp)) {
foreach($userListTemp as $val) 
	$userList[$val->id]	=	ucfirst($val->FirstName).' '.ucfirst($val->LastName);
}
?>
<body class="skin-blue">
<?php 
	top_header(); 
	$activeTab = 3;
	?>
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<div class="row col-xs-10">
			<h1>Transfer Tracking</h1>			
		</div>
	</section>
	<!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-xs-12">
				<form name="search_Analytics" action="Transfer?cs=1" method="post">
				<div class="box box-primary box-padding report">	
					<div class="box-body no-padding" >				
						<div class="col-sm-3 form-group">
							<!--<label>User Name</label>-->
							<input type="text" class="form-control" name="DebitedName" id="DebitedName" placeholder="User Name" value="<?php  if(isset($_SESSION['tuplit_sess_DebitedName']) && $_SESSION['tuplit_sess_DebitedName'] != '') echo unEscapeSpecialCharacters($_SESSION['tuplit_sess_DebitedName']);  ?>" >
						</div>
					<!--<div class="box-body no-padding" >				
						<div class="col-sm-4 form-group">
							<label>Credited User</label>
							<input type="text" class="form-control" name="CreditedName" id="CreditedName"  value="<?php  if(isset($_SESSION['tuplit_sess_CreditedName']) && $_SESSION['tuplit_sess_CreditedName'] != '') echo $_SESSION['tuplit_sess_CreditedName'];  ?>" >
						</div>
					</div>-->
						<div class="col-sm-3 form-group">
							<!--<label>Transfer Amount </label>-->
							<input type="text" class="form-control" onkeypress="return isNumberKey(event);" name="TransferAmount" placeholder="Transfer Amount" id="TransferAmount"  value="<?php if(isset($_SESSION['tuplit_sess_TransferAmount']) && $_SESSION['tuplit_sess_TransferAmount'] != '') echo $_SESSION['tuplit_sess_TransferAmount'];  ?>" >
						</div>
						<div class="col-sm-3 form-group">
							<!--<label>From Date</label>-->
							<input type="text" class="form-control datepicker" name="TransferDateFrom" id="TransferDateFrom" placeholder="Start Date" value="<?php  if(isset($_SESSION['tuplit_sess_TransferDateFrom']) && $_SESSION['tuplit_sess_TransferDateFrom'] != '') echo $_SESSION['tuplit_sess_TransferDateFrom'];  ?>" >
						</div>
						<div class="col-sm-3 form-group">
							<!--<label>To Date</label>-->
							<input type="text" class="form-control datepicker" name="TransferDateTo" id="TransferDateTo"  placeholder="End Date" value="<?php  if(isset($_SESSION['tuplit_sess_TransferDateTo']) && $_SESSION['tuplit_sess_TransferDateTo'] != '') echo $_SESSION['tuplit_sess_TransferDateTo'];  ?>" >
						</div>
					</div>
					<div class="col-sm-12 clear" align="center">
						<!--<label>&nbsp;</label>-->
						<input type="submit" class="btn btn-success" name="Search" id="Search" value="Search" >
					</div>
				</div>
				</form>				
			</div>	
		</div>
		<?php require_once('StatisticsTabs.php');?>
		<div class="row paging-margin paging">
			<?php if(isset($transferList) && is_array($transferList) && count($transferList) > 0){ ?>
			<div class="col-xs-12 col-sm-3 dataTables_info no-padding">
				<span class="white_txt">Total Transfer(s) : <b><?php echo $tot_rec; ?></b></span>
			</div>
			<div class="col-xs-12 col-sm-9">
				<div class="dataTables_paginate paging_bootstrap row">
						<?php pagingControlLatest($tot_rec,'Transfer'); ?>
				</div>
			</div>
			<?php } ?>
		</div>
		<div class="row">
			<div class="col-xs-12">
			   <?php if(isset($transferList) && !empty($transferList)) { ?>
				  <div class="box">
				   <div class="box-body table-responsive no-padding no-margin">
					<table class="table table-hover">
						   <tr>
								<th align="center" width="2%" class="text-center">#</th>									
								<th width="10%">Debited user</th>
								<th width="10%">Credited user</th>
								<th width="10%" align="center">Notes</th>
								<th width="10%" class="text-right">Transfer Amount</th>									
								<th width="10%" class="text-left">Transfer Date</th>
							</tr>
						  <?php
							foreach($transferList as $key=>$value){ 
						  ?>
								<tr>
									<td align="center"><?php echo (($_SESSION['curpage'] - 1) * ($_SESSION['perpage']))+$key+1;?></td>												
									<td ><?php if(isset($userList[$value->fkUsersId]))echo $userList[$value->fkUsersId]; else echo "-"; ?></td>
									<td ><?php if(isset($userList[$value->fkTransferUsersId]))echo $userList[$value->fkTransferUsersId]; else echo "-"; ?></td>
									<td class="text-left"><?php  if(!empty($value->Notes)) echo $value->Notes; else echo "-"; ?></td>
									<td class="text-right"><?php  echo price_fomat($value->Amount); ?></td>										
									<td  class="text-left"><?php   if(isset($value->TransferDate)){
										$gmt_current_transfer_time = convertIntocheckinGmtSite($value->TransferDate);
										$transfer_time	=  displayConversationDateTimeForLog($gmt_current_transfer_time,$_SESSION['tuplit_ses_from_timeZone']);
										echo $transfer_time; } else echo '-'; ?></td>
								</tr>
						<?php } //end for ?>	
					   </table>
						<!-- End product List -->						 
					<?php } else { ?>
						<div class="row clear">		
							 <div align="center" class="alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10"><i class="fa fa-warning"></i> <?php echo $errorMessage; ?>	</div>							
						</div>							
					<?php } ?>						
				</div><!-- /.box-body -->
			</div>					
		</div>	
	 </div>
</section><!-- /.content -->	
<?php commonFooter(); ?>
<script type="text/javascript">
$(".datepicker").datepicker({
	showButtonPanel	:	true,        
    buttonText		:	'<i class="fa fa-calendar"></i>',
    buttonImageOnly	:	true,
    buttonImage		:	path+'webresources/images/calender.png',
    dateFormat		:	'mm/dd/yy',
	changeMonth		:	true,
	changeYear		:	true,
	hideIfNoPrevNext:	true,
	showWeek		:	true,
	yearRange		:	"c-30:c",
	closeText		:   "Close"
   });
</script>
</html>