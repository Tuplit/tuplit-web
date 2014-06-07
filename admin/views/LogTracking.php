<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
commonHead();
require_once('controllers/LogController.php');
$logObj   =   new LogController();
$display   =   'none';
$class  =  $msg    = $cover_path = '';
global $link_type_array;
$display	=	"none";
$today		=	date('m-d-Y');
$where		=	' ';

if(isset($_GET['cs']) && $_GET['cs']=='1') {
	destroyPagingControlsVariables();
	unset($_SESSION['sess_logtrack_to_date']);
	unset($_SESSION['sess_logtrack_from_date']);
	unset($_SESSION['sess_logtrack_process']);
	unset($_SESSION['sess_logtrack_searchUserName']);
	unset($_SESSION['sess_logtrack_searchIP']);
	unset($_SESSION['sess_logtrack_urlString']);
}
if(isset($_POST['Search']) && $_POST['Search'] != ''){
	destroyPagingControlsVariables();
	$_POST          = unEscapeSpecialCharacters($_POST);
    $_POST          = escapeSpecialCharacters($_POST);
	$_SESSION['sess_logtrack_to_date']      	= $_POST['to_date'];
	$_SESSION['sess_logtrack_from_date']     	= $_POST['from_date']; 
	//$_SESSION['sess_logtrack_process']       	= $_POST['process_type'];
	$_SESSION['sess_logtrack_searchUserName']	= trim($_POST['searchUserName']);
	$_SESSION['sess_logtrack_searchIP']      	= trim($_POST['searchIP']);
	$_SESSION['sess_logtrack_urlString']      	= trim($_POST['urlString']);
	//action_type
}
if(!isset($_SESSION['sess_logtrack_to_date'])) 
	$_SESSION['sess_logtrack_to_date']	=	date('Y-m-d');	//=	$today;//
if(!isset($_SESSION['sess_logtrack_from_date'])) 
	$_SESSION['sess_logtrack_from_date']	=	date('Y-m-d');
setPagingControlValues('l.id',ADMIN_PER_PAGE_LIMIT);
$logtracksResult	=	$logObj->logtrackDetails($where);
$tot_rec 		 = $logObj->getTotalRecordCount();
if($tot_rec==0 && !is_array($logtracksResult)) {
	$_SESSION['curpage'] = 1;
$logtracksResult	=	$logObj->logtrackDetails($where);
}
//echo '<pre>';print_r($logtracksResult);echo '</pre>';
?>
<body class="skin-blue">
<?php top_header(); ?>
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<div class="row col-xs-10">
			<h1><i class="fa fa-list"></i> Log Tracking</h1>
		</div>
	</section>
	<!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-xs-12">
				<form name="search_category" action="LogTracking" method="post">
				<div class="box box-primary">
					<div class="col-sm-3 form-group">
						<label>Name</label>
						<input  type="text" class="form-control " title="Name" name="searchUserName" value="<?php if(isset($_SESSION['sess_logtrack_searchUserName']) && $_SESSION['sess_logtrack_searchUserName'] != '') echo $_SESSION['sess_logtrack_searchUserName'];?>">
					</div>
					<div class="col-sm-3 form-group">
						<label>IP Address</label>
						<input type="text" class="form-control"  title="IP Address" name="searchIP" value="<?php if(isset($_SESSION['sess_logtrack_searchIP']) && $_SESSION['sess_logtrack_searchIP'] != '') echo $_SESSION['sess_logtrack_searchIP'];?>">
					</div>
					<div class="col-sm-2 form-group">
						<label>Start Date</label>
						<input  type="text" class="form-control datepicker" autocomplete="off" title="Select Date" name="from_date" value="<?php if(isset($_SESSION['sess_logtrack_from_date']) && $_SESSION['sess_logtrack_from_date'] != '') echo date('m/d/Y',strtotime($_SESSION['sess_logtrack_from_date']));?>">
					</div>
					<div class="col-sm-2 form-group">
						<label>End Date</label>
						<input type="text" class="form-control datepicker" autocomplete="off"  title="Select Date" name="to_date" value="<?php if(isset($_SESSION['sess_logtrack_to_date']) && $_SESSION['sess_logtrack_to_date'] != '') echo date('m/d/Y',strtotime($_SESSION['sess_logtrack_to_date']));?>">
					</div>
					<div class="col-sm-2 form-group">
						<label>String</label>
						<input type="text" class="form-control"   title="" name="urlString" value="<?php if(isset($_SESSION['sess_logtrack_urlString']) && $_SESSION['sess_logtrack_urlString'] != '') echo unEscapeSpecialCharacters($_SESSION['sess_logtrack_urlString']);?>">
					</div>
					
					<div class="col-sm-12 box-footer clear" align="center">
						<label>&nbsp;</label>
						<input type="submit" class="btn btn-success" name="Search" id="Search" value="Search">
					</div>
				</div>
				</form>
			</div>
		</div>
		<div class="row paging">
			<div class="col-xs-2">
				<?php if(isset($logtracksResult) && is_array($logtracksResult) && count($logtracksResult) > 0){ ?>
				<div class="dataTables_info">No. of Log(s)&nbsp:&nbsp;<strong><?php echo $tot_rec; ?></strong> </div>
				<?php } ?>
			</div>
			<div class="col-xs-10">
				<div class="dataTables_paginate paging_bootstrap row">
				<?php if(isset($logtracksResult)	&&	is_array($logtracksResult) && count($logtracksResult) > 0 ) {
							 	pagingControlLatest($tot_rec,'LogTracking'); ?>
				<?php }?>
				</div>
			</div>
		 </div>	
		
		<?php if(isset($msg) && $msg != '') { ?>
		 <div class="row">
               <div align="center" class="alert <?php  echo $class;  ?> alert-dismissable col-sm-5"><i class="fa fa-check"></i>  <?php echo $msg; ?></div>
		 </div>	
		<?php } ?>
		
		<div class="row">
               <div class="col-xs-12">
			    <?php if(isset($logtracksResult) && is_array($logtracksResult) && count($logtracksResult) > 0 ) {  ?>		
				<form action="LogTracking" class="l_form" name="LogTrackingForm" id="LogTrackingForm"  method="post"> 
                   <div class="box">
                       <div class="box-body table-responsive no-padding no-margin">
					  
                           <table class="table table-hover">
                               <tr>
                                <th width="3%" class="text-center">#</th>
								<th width="10%">Name</th>
								<th width="20%">URL</th>
								<th width="25%">Data</th>
								<th width="5%">Device</th>
								<th width="12%">Time</th>
								<th width="6%">Duration&nbsp;&nbsp;</th>
                               </tr>
                              		<?php 	foreach($logtracksResult as $key=>$value) { 
												$userName	=	'';
								if(isset($value->FirstName)	&&	isset($value->LastName)) 	
									$userName	=	ucfirst($value->FirstName).' '.ucfirst($value->LastName);
								else if(isset($value->FirstName))	
									$userName	=	 ucfirst($value->FirstName);
								else if(isset($value->LastName))	
									$userName	=	ucfirst($value->LastName);
									?>							
							<tr>
								<td align="center" valign="top"><?php echo (($_SESSION['curpage'] - 1) * ($_SESSION['perpage']))+$key+1;?></td>
						<td align="left" valign="top">	
							<?php if(isset($userName)	&&	$userName !='') echo ucfirst($userName);//ucfirst($value->firstName).'&nbsp;'.ucfirst($value->lastName) ; 
									else echo '-';?>								<br><br>
							<p><b class="head_color">IP :</b>
							<?php if(isset($value->ip_address)	&&	$value->ip_address !='') echo $value->ip_address; else echo '-';?>
							</p>
						</td>
						<td align="left" valign="top">
						<?php if(isset($value->log_stat)	&&	 ($value->log_stat ==1	||	$value->log_stat ==2)){ 
								echo '-';
							} else {?>
						<?php 	if(isset($value->url)	&&	$value->url !='') {
									if (SERVER)		echo "http://".$value->url;
									else 			echo "http://".$value->url;
								}
							  	else echo '-';?>
						<br><br><p><b class="head_color">Method : </b><?php if(isset($value->method)	&&	$value->method !='') echo $value->method; else echo '-';?></p>
						<?php }?>
						</td>
			<!--								<td align="left"><?php //if(isset($value->method)	&&	$value->method !='') echo $value->method; else echo '-';?></td>	-->
						<td align="left" class="brk_wrd_cell" valign="top">
							<?php if(isset($value->log_stat)	&&	 ($value->log_stat ==1	||	$value->log_stat ==2)){ 
								echo '-';
							} else {?>
							<p class="brk_wrd brk_wrd_cell"><b class="head_color">Request : </b><?php if(isset($value->content)	&&	$value->content !='') echo ''.$value->content.'<br><br>'; else echo '-<br><br>';?></p>
							<div class="brk_wrd brk_wrd_cell response_msg" ><b class="head_color">Response : </b><?php if(isset($value->response)	&&	$value->response !='') 
							echo strip_tags($value->response);
							//  echo $value->response;
							  else echo '-';?></div>
							<?php } ?>
			<!--										<textarea class="cledito"  name="description" id="description"><?php //if(isset($value->response) && $value->response != '') echo $value->response;?></textarea>
			-->
						</td>
			
			<!--									<td align="center"><p class="brk_wrd" ><?php //if(isset($value->response)	&&	$value->response !='') echo $value->response; else echo '-';?></p></td>	-->
						<td align="center" valign="top"><?php if(isset($value->device_type)	&&	$value->device_type !='') {
													if(isset($platformArray[$value->device_type]))
														echo $platformArray[$value->device_type];
													else echo '-';
												}else echo '-'; //$platformArray?></td>
						<td align="center" valign="top">
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
			<!--
						<td align="left"><?php //if(isset($value->ip_address)	&&	$value->ip_address !='') echo $value->ip_address; else echo '-';?></td>
			-->
						<td align="center"  valign="top"><?php if(isset($value->execution_time)	&&	$value->execution_time > 0) echo round($value->execution_time, 3).' sec'; else echo '-';?></td>
						<!-- <td align="left"><?php if(isset($value->log_stat)	&&	$value->log_stat > 0) echo $log_type_array[$value->log_stat]; else echo '-';?></td> -->
								
							</tr>
							<?php } //end for ?>	
                           </table>
                       </div><!-- /.box-body -->
                   </div><!-- /.box -->
					</form>
					
					<?php } else { ?>	
						<div class="alert alert-danger alert-dismissable col-sm-5"><i class="fa fa-warning"></i> No result found</div> 
					<?php } ?>	
               </div>
           </div>
	</section><!-- /.content -->	
<?php commonFooter(); ?>
<script type="text/javascript">
$(".datepicker").datepicker({
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
	closeText		:   "Close"
   });
   </script>
</html>
