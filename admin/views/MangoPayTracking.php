<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
commonHead();
require_once('controllers/LogController.php');
$logObj   	=   new LogController();
$display   	=   'none';
$class  	=  	$msg    = $cover_path = '';
$display	=	"none";
$today		=	date('m-d-Y');
$where		=	$userName = ' ';

if(isset($_GET['cs']) && $_GET['cs']=='1') {
	destroyPagingControlsVariables();
	unset($_SESSION['sess_mangopay_to_date']);
	unset($_SESSION['sess_mangopay_from_date']);
	unset($_SESSION['sess_mangopay_process']);
	unset($_SESSION['sess_mangopay_searchUserName']);
	unset($_SESSION['sess_mangopay_searchMerchantName']);
	unset($_SESSION['sess_mangopay_searchIP']);
	unset($_SESSION['sess_mangopay_searchResponse']);
	unset($_SESSION['sess_mangopay_log_method']);
	unset($_SESSION['sess_mangopay_searchUrl']);
}
if(isset($_POST['Search']) && $_POST['Search'] != ''){
	destroyPagingControlsVariables();
	$_POST          = unEscapeSpecialCharacters($_POST);
    $_POST          = escapeSpecialCharacters($_POST);
	$_SESSION['sess_mangopay_to_date']      			= $_POST['to_date'];
	$_SESSION['sess_mangopay_from_date']     			= $_POST['from_date']; 
	$_SESSION['sess_mangopay_searchMerchantName']       = trim($_POST['searchMerchantName']);
	$_SESSION['sess_mangopay_searchUserName']			= trim($_POST['searchUserName']);
	$_SESSION['sess_mangopay_searchIP']      			= trim($_POST['searchIP']);
	$_SESSION['sess_mangopay_searchResponse']   		= trim($_POST['searchResponse']);
	$_SESSION['sess_mangopay_log_method']      			= $_POST['log_method'];
	$_SESSION['sess_mangopay_searchUrl']      			= $_POST['searchUrl'];
	//action_type
}

if(!isset($_SESSION['sess_mangopay_to_date'])) 
	$_SESSION['sess_mangopay_to_date']	=	date('Y-m-d');	//=	$today;//
if(!isset($_SESSION['sess_mangopay_from_date'])) 
	$_SESSION['sess_mangopay_from_date']	=	date('Y-m-d');



setPagingControlValues('ml.id',ADMIN_PER_PAGE_LIMIT);
$fields			=	"ml.*,u.FirstName,u.LastName,u.Status,m.FirstName as MerchantFirstName,m.LastName as MerchantLastName";
$condition 		=	" AND u.Status != 3 "; 
$mangopayResult	=	$logObj->MangoPayTracking($fields,$condition);
$tot_rec		=	$logObj->getTotalRecordCount();

//echo '<pre>';print_r($mangopayResult);echo '</pre>';
?>
<body class="skin-blue">
<?php top_header(); ?>
<?php 
	$activeTab = 6;
	?>
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<div class="row col-xs-10">
			<h1><i class="fa fa-list"></i>MangoPay API Tracking</h1>
		</div>
	</section>
	<!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-xs-12">
				<form name="search_category" action="MangoPayTracking" method="post">
				<div class="box box-primary box-padding report">
					<div class="col-sm-4 form-group">
						<!--<label>Name</label>-->
						<input  type="text" class="form-control " title="Name" name="searchUserName" placeholder="Customer Name" value="<?php if(isset($_SESSION['sess_mangopay_searchUserName']) && $_SESSION['sess_mangopay_searchUserName'] != '') echo $_SESSION['sess_mangopay_searchUserName'];?>">
					</div>
					<div class="col-sm-4 form-group">
						<!--<label>Name</label>-->
						<input  type="text" class="form-control " title="Name" name="searchMerchantName" placeholder="Merchant Name" value="<?php if(isset($_SESSION['sess_mangopay_searchMerchantName']) && $_SESSION['sess_mangopay_searchMerchantName'] != '') echo $_SESSION['sess_mangopay_searchMerchantName'];?>">
					</div>
					<div class="col-sm-4 form-group">
						<!--<label>IP Address</label>-->
						<input type="text" class="form-control"  title="IP Address" name="searchIP" placeholder="IP Address" value="<?php if(isset($_SESSION['sess_mangopay_searchIP']) && $_SESSION['sess_mangopay_searchIP'] != '') echo $_SESSION['sess_mangopay_searchIP'];?>">
					</div>
					<div class="col-sm-4 form-group">
						<!--<label>Response</label>-->
						<input type="text" class="form-control"   title="Response" name="searchResponse" placeholder="Response" value="<?php if(isset($_SESSION['sess_mangopay_searchResponse']) && $_SESSION['sess_mangopay_searchResponse'] != '') echo unEscapeSpecialCharacters($_SESSION['sess_mangopay_searchResponse']);?>">
					</div>
					<div class="col-sm-2 form-group">
						<!--<label>Start Date</label>-->
						<div class="col-lg-12 no-padding">
							<input  type="text" class="form-control datepicker" autocomplete="off" title="Start Date" name="from_date" value="<?php if(isset($_SESSION['sess_mangopay_from_date']) && $_SESSION['sess_mangopay_from_date'] != '') echo date('m/d/Y',strtotime($_SESSION['sess_mangopay_from_date']));?>">
						</div>
					</div>
					<div class="col-sm-2 form-group">
						<!--<label>End Date</label>-->
						<div class="col-lg-12 no-padding">
							<input type="text" class="form-control datepicker" autocomplete="off"  title="End Date" name="to_date" value="<?php if(isset($_SESSION['sess_mangopay_to_date']) && $_SESSION['sess_mangopay_to_date'] != '') echo date('m/d/Y',strtotime($_SESSION['sess_mangopay_to_date']));?>">
						</div>
					</div>
					<div class="col-sm-2 form-group">
						<!--<label>Method</label>-->
						<div class="col-lg-12 no-padding">
							<select name="log_method" id="log_method" class="form-control">
								<option value="">Method</option>
								<?php foreach($methodArray as $key=>$value){?>
								<option value="<?php echo $value;?>" <?php if(isset($_SESSION['sess_mangopay_log_method']) && ($_SESSION['sess_mangopay_log_method']== $value	)) echo 'selected';?>><?php echo $value;?></option>
								<?php }?>
							</select>
						</div>
					</div>
					<div class="col-sm-6 form-group">
						<!--<label>Url</label>-->
						<input  type="text" class="form-control " title="Url" name="searchUrl" placeholder="Url" value="<?php if(isset($_SESSION['sess_mangopay_searchUrl']) && $_SESSION['sess_mangopay_searchUrl'] != '') echo $_SESSION['sess_mangopay_searchUrl'];?>">
					</div>
					
					<div class="col-sm-12 clear" align="center">
						<!--<label>&nbsp;</label>-->
						<input type="submit" class="btn btn-success" name="Search" id="Search" value="Search">
					</div>
				</div>
				</form>
			</div>
		</div>
		<?php require_once('StatisticsTabs.php');?>
		<div class="row paging paging-margin">
			<div class="col-xs-12 col-sm-2 no-padding">
				<?php if(isset($mangopayResult) && is_array($mangopayResult) && count($mangopayResult) > 0){ ?>
				<div class="dataTables_info"><span class="white_txt">No. of Log(s)&nbsp:&nbsp;<strong><?php echo $tot_rec; ?></strong></span> </div>
				<?php } ?>
			</div>
			<div class="col-xs-12 col-sm-10">
				<div class="dataTables_paginate paging_bootstrap row">
				<?php if(isset($mangopayResult)	&&	is_array($mangopayResult) && count($mangopayResult) > 0 ) {
							 	pagingControlLatest($tot_rec,'MangoPayTracking'); ?>
				<?php }?>
				</div>
			</div>
		 </div>	
		
		<?php if(isset($msg) && $msg != '') { ?>
		 <div class="row">
               <div align="center" class="alert <?php  echo $class;  ?> alert-dismissable col-sm-5 col-xs-11"><i class="fa fa-check"></i>  <?php echo $msg; ?></div>
		 </div>	
		<?php } ?>
		
		<div class="row">
               <div class="col-xs-12">
			    <?php if(isset($mangopayResult) && is_array($mangopayResult) && count($mangopayResult) > 0 ) {  ?>		
				<form action="MangoPayTracking" class="l_form" name="MangoPayTrackingForm" id="MangoPayTrackingForm"  method="post"> 
                   <div class="box">
                       <div class="box-body table-responsive no-padding no-margin">
					  
                           <table class="table table-hover">
                               <tr>
                                <th width="3%" class="text-center">#</th>
								<th width="10%">User Name</th>
								<th width="10%">Merchant Name</th>
								<th width="20%">Process</th>
								<th width="25%">Data</th>
								<th width="15%">Time</th>
								<th width="6%">Duration&nbsp;&nbsp;</th>
                               </tr>
                              		<?php 	foreach($mangopayResult as $key=>$value) { 
									$userName	=	$merchantName	=	'';
									if(isset($value->FirstName)	&&	isset($value->LastName)) 	
										$userName	=	ucfirst($value->FirstName).' '.ucfirst($value->LastName);
									else if(isset($value->FirstName))	
										$userName	=	 ucfirst($value->FirstName);
									else if(isset($value->LastName))	
										$userName	=	ucfirst($value->LastName);
									
									if(isset($value->MerchantFirstName)	&&	isset($value->MerchantLastName)) 	
										$merchantName	=	ucfirst($value->MerchantFirstName).' '.ucfirst($value->MerchantLastName);
									else if(isset($value->MerchantFirstName))	
										$merchantName	=	 ucfirst($value->MerchantFirstName);
									else if(isset($value->MerchantLastName))	
										$merchantName	=	ucfirst($value->MerchantLastName);
									
									?>							
							<tr id="td_<?php echo $value->id;?>">
								<td align="center" valign="top"><?php echo (($_SESSION['curpage'] - 1) * ($_SESSION['perpage']))+$key+1;?></td>
								<td align="left" valign="top">	
									<?php if(isset($userName)	&&	$userName !='') echo ucfirst($userName); else echo '-';?>								<br><br>
									<p><b class="head_color">IP :</b>
									<?php if(isset($value->ip_address)	&&	$value->ip_address !='') echo $value->ip_address; else echo '-';?>
									</p>
								</td>
								<td>
									<?php if(isset($merchantName)	&&	$merchantName !='') echo ucfirst($merchantName); else echo '-';?>
								</td>
								<td align="left" valign="top">
									<div class="mb_wrap">
										<?php 	if(isset($value->url)	&&	$value->url !='') { echo $value->url; } else echo '-';?>
									</div>
								</td>
								<td align="left" class="brk_wrd_cell" valign="top">
									<div class="mb_wrap" style="text-align:left;">
										<p class="brk_wrd brk_wrd_cell"><b class="head_color">Request : </b><?php if(isset($value->content)	&&	$value->content !='') echo ''.$value->content.'<br><br>'; else echo '-<br><br>';?></p>
										<div class="brk_wrd brk_wrd_cell response_msg" ><b class="head_color">Response : </b>
											<?php if(isset($value->response)	&&	$value->response !='') { ?>
													<div class="more_content">
														<?php echo substr(strip_tags($value->response),0,1000); if(strlen($value->response)>1000) echo '...'; ?>
													</div> 
													<?php if(strlen($value->response)>1000) { ?>
														<a href="javascript:void(0);" class="more" style="float: right" title="More..">More..</a>
														<div class="hide_content" style="display:none">
															<?php echo strip_tags($value->response); ?><br/>
															<a href="javascript:void(0);" class="hide_tag" style="float: right;" title="Hide">Hide</a>
														</div> 
												   <?php 	}
											  } else echo '-';?>
										</div>
									</div>
								</td>
								<td align="left" valign="top">
										<div class="div_no_wrap">
										<?php if(isset($value->log_stat)	&&	 $value->log_stat ==1	){
													if(isset($value->start_time) && $value->start_time != '0000-00-00 00:00:00'){
														$gmt_current_start_time = convertIntocheckinGmtSite($value->start_time);
														$start_time	=  displayConversationDateTimeForLog($gmt_current_start_time,$_SESSION['tuplit_ses_from_timeZone']);
														echo '<br>'.$start_time; 
													}else echo '<br>-';
											} 
											else if(isset($value->log_stat)	&&	 $value->log_stat ==2	){
													if(isset($value->end_time) && $value->end_time != '0000-00-00 00:00:00'){
														$gmt_current_end_time = convertIntocheckinGmtSite($value->end_time);
														$end_time	=  displayConversationDateTimeForLog($gmt_current_end_time,$_SESSION['tuplit_ses_from_timeZone']);
														echo '<br>'.$end_time; 
													}else echo '<br>-';
											} 
											else { ?>
										<?php 	if(isset($value->start_time) && $value->start_time != '0000-00-00 00:00:00'){
													$gmt_current_start_time = convertIntocheckinGmtSite($value->start_time);
													$start_time	=  displayConversationDateTimeForLog($gmt_current_start_time,$_SESSION['tuplit_ses_from_timeZone']);
													echo $start_time; 
												}else echo '-';?>
											<p align="center">to</p>
										<?php 	if(isset($value->end_time) && $value->end_time != '0000-00-00 00:00:00'){
													$gmt_current_end_time = convertIntocheckinGmtSite($value->end_time);
													$end_time	=  displayConversationDateTimeForLog($gmt_current_end_time,$_SESSION['tuplit_ses_from_timeZone']);
													echo $end_time; 
												}else echo '-';?>
										<?php } ?>
										</div>
								</td>
								<td align=""  valign="top"><?php if(isset($value->execution_time)	&&	$value->execution_time > 0) echo round($value->execution_time, 3).' sec'; else echo '-';?></td>
							</tr>
							<?php } //end for ?>	
                        </table>
                    </div><!-- /.box-body -->
					</div><!-- /.box -->
				</form>
					<?php } else { ?>	
						<div class="alert alert-danger alert-dismissable col-sm-5 col-lg-3  col-xs-11"><i class="fa fa-warning"></i> No result found</div> 
					<?php } ?>	
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
   
   $(".more").click(function() {
     $(this).hide();
	 $(this).prev(".more_content").hide();
	 $(this).next(".hide_content").show();
   });
 
	$(".hide_tag").click(function() {
	  var getId	= ($(this).closest('tr').attr('id'));
	  $(this).parent().prev(".more").show();
	  $(this).parent().prev(".more").prev(".more_content").show();
	  $(this).parent().hide();
	  $('html, body').animate({ scrollTop: $('#'+getId).offset().top }, 'slow')
    });
</script>
</html>
