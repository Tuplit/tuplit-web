<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();
$start	=	0;
$limit	= 	10;
$count	=	$tot_rec =  $start_count	= 0;
//getting merchant details

if(isset($_SESSION['merchantDetailsInfo']) && is_array($_SESSION['merchantDetailsInfo'])){
	$merchantInfo  =	$_SESSION['merchantDetailsInfo'];	
}
else{
	$merchantId					= 	$_SESSION['merchantInfo']['MerchantId'];
	$url						=	WEB_SERVICE.'v1/merchants/'.$merchantId;
	$curlMerchantResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);	
	if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201 && $curlMerchantResponse['merchant']['MerchantId'] != '' ) 
	 {
		$_SESSION['merchantDetailsInfo']   =	$curlMerchantResponse['merchant'];
		$merchantInfo  =	$_SESSION['merchantDetailsInfo'];
	}
}
if(isset($_GET['cs']) && $_GET['cs'] == 1){
}

if(isset($_POST['start']) && isset($_POST['Previous']) && isset($_POST['totalcount'])) {
	$start 			= 	$_POST['start'] - 10;	
	$start_count	=	$start;
} else if(isset($_POST['start']) && isset($_POST['Next']) && isset($_POST['totalcount'])) {
	$start = $_POST['start'] + 10;
	$start_count	=	$start;
}

if(isset($_GET['viewId']) && $_GET['viewId'] != ''){
    $UserId			=	base64_decode($_GET['viewId']);
	//getting order list of users
	$url					=	WEB_SERVICE.'v1/comments/?MerchantId='.$merchantInfo["id"].'&UserId='.$UserId.'&Start='.$start.'&Limit='.$limit.'&Type=1';
	$curlCommentResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlCommentResponse) && is_array($curlCommentResponse) && $curlCommentResponse['meta']['code'] == 201 && is_array($curlCommentResponse['comments']) ) {
		if(isset($curlCommentResponse['comments'])){
			$commentList 	  = $curlCommentResponse['comments'];	
			$tot_rec	  	= $curlCommentResponse['meta']['totalCount'];
		}
	} else if(isset($curlCommentResponse['meta']['errorMessage']) && $curlCommentResponse['meta']['errorMessage'] != '') {
			$errorMessage	=	$curlCommentResponse['meta']['errorMessage'];
	} else {
			$errorMessage	= 	"Bad Request";
	} 
}
if(!isset($_SESSION['tuplit_ses_from_timeZone']) || $_SESSION['tuplit_ses_from_timeZone'] == ''){
	 $time_zone = getTimeZone();
	 $_SESSION['tuplit_ses_from_timeZone'] = strval($time_zone);	 
}
popup_head();
?>

<body class="skin-blue fixed popup_bg">
<div class="popup_white">
		<div class="col-xs-12">	
			<section class="content-header">
                <h1 class="space_bottom">Comment List</h1>
            </section>
		<div class="box box-primary" style="padding-left:0px;padding-top:0px;margin-bottom:15px;min-height:100%;">
			<div class="row no-margin space_bottom">
					<?php if(isset($commentList) && is_array($commentList) && count($commentList) > 0){ ?>
					<span class="totl_txt">Total Comment(s) : <b><?php echo $tot_rec; ?></b></span>
					<!--<div class="list_pagnav" style="text-align:center;">
							<?php pagingControlLatestAjax($tot_rec,'UserComments?viewId='.$_GET['viewId'].''); ?>
					</div>-->
					<?php } ?>
				</div>
			<div class="product_list box">
				<?php if(isset($commentList) && !empty($commentList)) { ?>
				<div class="box box-primary no-padding no-margin commentlist">
					<div class="box-body table-responsive no-padding no-margin">
						
						<table class="table table-hover" width="30%">
                               <tr>
									<th align="center" width="3%" style="text-align:center">#</th>									
									<th width="30%">Comment</th>
									<th width="25%" align="center" class="text-center">Comment Date</th>
								</tr>
                              <?php
							  	foreach($commentList as $key=>$value){
									$gmt_current_created_time = convertIntocheckinGmtSite($value["CommentDate"]);
									$time		 =  displayDate($gmt_current_created_time,$_SESSION['tuplit_ses_from_timeZone']);
									$count += 1;
									$start_count += 1;		
								?>
							<tr>
								<td align="center"><?php echo $start_count; ?></td>												
								<td width="30%"><div class="cust_comment_text"><i class="fa fa-comment-o"></i>&nbsp;&nbsp;<?php echo $value["CommentsText"];?></div></td>
								<td align="center"><?php echo $time	;?></td>
							</tr>
							 
							<?php } //end for ?>	
                           </table>
							<!-- End product List -->						 
											
					</div><!-- /.box-body -->
				</div>		
				<?php } else { ?>
							<div class="row clear" style="padding-top:15px">		
								 <div align="center" class="alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10"><i class="fa fa-warning"></i> <?php echo $errorMessage;?></div>							
							</div>							
						<?php } ?>				
			</div>
			</div>	
		</div>
		<div class="customer_trans">
		<div class="col-xs-12 next-prev">
			<form method="post" action="">
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
		</div>
		<!--<div style="margin-top:15px;width:100%">
			<form method="post" action="">
				<input type="hidden" name="start" value="<?php echo $start; ?>"/> 
				<input type="hidden" name="totalcount" value="<?php echo $tot_rec; ?>"/>
				<?php if($start != 0) { ?> <div style="float:left;"><input class="btn bg-olive btn-md " type="submit" name="Previous" id="Previous" value="Previous" /></div><?php } ?>
				<?php $start = $start + 10; if($start < $tot_rec) { ?> <div style="float:right;"><input class="btn bg-olive btn-md " type="submit" name="Next" id="Next" value="Next" /></div><?php } ?>
			</form>
		</div>-->
		</div>
	<?php commonFooter(); ?>
	
</html>
