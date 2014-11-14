<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();

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
	destroyPagingControlsVariables();
	$_SESSION['tuplit_sess_order_user_name'] = '';
	$_SESSION['tuplit_sess_order_visit']	=	'';
	$_SESSION['tuplit_sess_order_total_spend']	=	'';
}
$UserName = $VisitCount = $TotalSpend = '';
$load_more = $cur_page = $per_page = 0;
$count	=	$tot_rec = 0;
setPagingControlValues('c.id',MERCHANT_PER_PAGE_LIMIT);
if(isset($_POST['cur_page']) && $_POST['cur_page'] != ''){
	$cur_page		=	($_SESSION['curpage'] - 1) * ($_SESSION['perpage']);
	$per_page		=   $_SESSION['perpage'];
}
if(isset($_GET['viewId']) && $_GET['viewId'] != ''){
    $UserId			=	base64_decode($_GET['viewId']);
	//getting order list of users
	$url					=	WEB_SERVICE.'v1/comments/?MerchantId='.$merchantInfo["id"].'&UserId='.$UserId.'&Start='.$cur_page.'&Limit='.$per_page.'&Type=1';
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
commonHead();
?>

<body class="skin-blue fixed popup_bg">
<div class="popup_white">
		<div class="col-xs-12">	
			<section class="content-header">
                <h1 class="space_bottom">Comment List</h1>
            </section>
		<div class="box box-primary" style="padding-left:0px;padding-top:0px;margin-bottom:15px;">
			<div class="row no-margin space_bottom">
					<?php if(isset($commentList) && is_array($commentList) && count($commentList) > 0){ ?>
					<span class="totl_txt">Total Comments(s) : <b><?php echo $tot_rec; ?></b></span>
					<div class="list_pagnav">
							<?php pagingControlLatestAjax($tot_rec,'UserOrders?viewId='.$_GET['viewId'].''); ?>
					</div>
					<?php } ?>
				</div>
			<div class="product_list">
				<?php if(isset($commentList) && !empty($commentList)) { ?>
				<div class="box box-primary no-padding no-margin commentlist">
					<div class="box-body table-responsive no-padding no-margin">
						
						<table class="table table-hover">
                               <tr>
									<th align="center" width="3%" style="text-align:center">#</th>									
									<th width="30%">Comment</th>
									<th width="25%">Comment Date</th>
								</tr>
                              <?php
							  	foreach($commentList as $key=>$value){
									$gmt_current_created_time = convertIntocheckinGmtSite($value["CommentDate"]);
									$time		 =  displayDate($gmt_current_created_time,$_SESSION['tuplit_ses_from_timeZone']);
									$count += 1;
								?>
							<tr>
								<td align="center"><?php echo (($_SESSION['curpage'] - 1) * ($_SESSION['perpage']))+$key+1;?></td>												
								<td width="30%"><?php echo $value["CommentsText"];?></td>
								<td><?php echo $time	;?></td>
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
		 </div>
		<?php //footerLogin(); ?>
	<?php commonFooter(); ?>
	
</html>
