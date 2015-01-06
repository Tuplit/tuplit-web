<?php
require_once('includes/CommonIncludes.php');
if(isset($_POST['action']) && $_POST['action']=='GET_COMMENTS') { 
	if(isset($_POST['dataType']) && $_POST['dataType'] != '') {
		$date_type			=	$_POST['dataType'];
		$_SESSION['TuplitAnalyticsView']	=	$date_type;
	}
	if(isset($_POST['Starts']) && $_POST['Starts'] != '')
		$start			=	$_POST['Starts'];
	$_SESSION['tuplit_sess_comment_start'] 		= 	$start;
	
}
if(!isset($_SESSION['tuplit_ses_from_timeZone']) || $_SESSION['tuplit_ses_from_timeZone'] == ''){
	 $time_zone = getTimeZone();
	 $_SESSION['tuplit_ses_from_timeZone'] = strval($time_zone);	 
}
$start_count = $start;
$tot_rec	 = 0;
$limit		= '';
//$limit		= 10;
//getting comment list of users
$url					=	WEB_SERVICE.'v1/comments/productcomments?DataType='.$date_type.'&Start='.$start.'&TimeZone='.$_SESSION['tuplit_ses_from_timeZone'];
$curlCommentResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
if(isset($curlCommentResponse) && is_array($curlCommentResponse) && $curlCommentResponse['meta']['code'] == 201 && is_array($curlCommentResponse['comments']) ) {
	if(isset($curlCommentResponse['comments'])){
		$commentList 	= 	$curlCommentResponse['comments'];
		$tot_rec	  	= 	$curlCommentResponse['meta']['totalCount'];
	}
} else if(isset($curlCommentResponse['meta']['errorMessage']) && $curlCommentResponse['meta']['errorMessage'] != '') {
		$errorMessage	=	$curlCommentResponse['meta']['errorMessage'];
} else {
		$errorMessage	= 	"Bad Request";
} 
if(!isset($_SESSION['tuplit_ses_from_timeZone']) || $_SESSION['tuplit_ses_from_timeZone'] == ''){
	 $time_zone = getTimeZone();
	 $_SESSION['tuplit_ses_from_timeZone'] = strval($time_zone);	 
}
?>

<div class="col-xs-12 no-padding commentList">
   <?php if(isset($commentList) && !empty($commentList)) { ?>
		<div class="box">
			<div class="box-body table-responsive no-padding no-margin">
				<table class="table table-hover customer_list" width="100%">
					<tr>
						<th width="5%" class="text-center">#</th>
						<th width="15%">Customer</th>
						<th width="30%">Comments</th>
						<th width="10%" class="text-center">Time</th>
					</tr>
					<?php $count = 0;
					
					foreach($commentList as $key=>$value){
						$gmt_current_created_time = convertIntocheckinGmtSite($value["CommentDate"]);
						$displautime		 =  displayDate($gmt_current_created_time,$_SESSION['tuplit_ses_from_timeZone']);
						$count += 1;
						$start_count += 1;					
					?>
					<tr>	
						<td class="text-center"><?php echo $start_count; ?></td>
						<td nowrap><b><?php echo $value['UserName']; ?></b></td>
						<td><div class="cust_comment_text"><i class="fa fa-comment-o"></i> <?php echo $value['CommentsText']; ?></div></td>
						<td class="text-center" style="color:#b5b5b5;"><?php if(isset($displautime) && $displautime != ''){ echo $displautime; }else echo '-';?></td>
					</tr>
					<?php } ?>	
			   </table>
			<?php } else { ?>
				<div class="col-sm-12 no-padding ">		
					 <div align="center" class="alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10"><i class="fa fa-warning"></i> <?php echo $errorMessage	;?>	</div>							
				</div>									
			<?php } ?>		
			</div>
		</div>					
	</div>	
</div>
<div class="col-xs-12 next-prev">
	<form method="post" action="ProductComments">
		<input type="hidden" name="start" value="<?php echo $start; ?>"/> 
		<input type="hidden" name="totalcount" value="<?php echo $tot_rec; ?>"/>
			<?php if($start != 0) { ?>
				<div id="prevTransaction"  class="col-xs-6 col-sm-6" align="center">	
					<input class="btn bg-olive btn-md prev_button" type="submit" name="Previous" id="Previous" value="Previous" />
				</div>
			<?php } ?>
			<?php $start = $start + 10; if($start < $tot_rec) { ?>
				<div id="nextTransaction"  class="col-xs-6 col-sm-6" align="center" style="float:right;">
					<input class="btn bg-olive btn-md next_button" type="submit" name="Next" id="Next" value="Next" />
				</div>
			<?php } ?>
	</form>
</div>
<!-- <div style="margin-top:15px;width:100%">
	<form method="post" action="ProductComments">
		<input type="hidden" name="start" value="<?php echo $start; ?>"/> 
		<input type="hidden" name="totalcount" value="<?php echo $tot_rec; ?>"/>
		<?php if($start != 0) { ?> <div style="float:left;"><input class="btn bg-olive btn-md " type="submit" name="Previous" id="Previous" value="Previous" /></div><?php } ?>
		<?php $start = $start + 10; if($start < $tot_rec) { ?> <div style="float:right;"><input class="btn bg-olive btn-md " type="submit" name="Next" id="Next" value="Next" /></div><?php } ?>
	</form>
</div>-->
	