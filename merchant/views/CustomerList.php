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
	$_SESSION['tuplit_sess_order_user_name']	= 	'';
	$_SESSION['tuplit_sess_order_visit']		=	'';
	$_SESSION['tuplit_sess_order_total_spend']	=	'';
	$_SESSION['tuplit_sess_from_date']			=	'';
	$_SESSION['tuplit_sess_to_date']			=	'';
}
setPagingControlValues('o.id',MERCHANT_PER_PAGE_LIMIT);
$UserName = $VisitCount = $TotalSpend = '';
$load_more = $cur_page = $per_page = $show = 0;
$count	=	$tot_rec = 0;
$FromDate = $ToDate = date('Y-m-d');


if(!isset($_SESSION['tuplit_sess_from_date'])) 
	$_SESSION['tuplit_sess_from_date']	=	$FromDate;	//=	$today;//
if(!isset($_SESSION['tuplit_sess_to_date'])) 
	$_SESSION['tuplit_sess_to_date']	=	 $ToDate;

if(isset($_GET['Show']) && $_GET['Show'] == 1){
	$show = 1;
}
if(isset($_POST['Search']) && $_POST['Search'] != ''){
    if(isset($_POST['UserName']) && $_POST['UserName'] != ''){
		$UserName		=	$_POST['UserName'];
		$_SESSION['tuplit_sess_order_user_name'] 	= 	$UserName;
	}
	 if(isset($_POST['Vistit']) && $_POST['Vistit'] != ''){
		$VisitCount		=	trim($_POST['Vistit']);
		$_SESSION['tuplit_sess_order_visit'] 		= 	$VisitCount;
	}
	 if(isset($_POST['TotalSpend']) && $_POST['TotalSpend'] != ''){
		$TotalSpend		=	trim($_POST['TotalSpend']);
		$_SESSION['tuplit_sess_order_total_spend'] 	= 	$TotalSpend;
	}
	if(isset($_POST['from_date']) && !empty($_POST['from_date']) && isset($_POST['to_date']) && !empty($_POST['to_date'])) {
		$FromDate			=	$_POST['from_date'];
		$_SESSION['tuplit_sess_from_date']	=	$FromDate;
		$ToDate				=	$_POST['to_date'];
		$_SESSION['tuplit_sess_to_date']	=	$ToDate;
	}
}

$cur_page		=	($_SESSION['curpage'] - 1) * ($_SESSION['perpage']);
$per_page		=   $_SESSION['perpage'];
if(isset($_SESSION['tuplit_sess_order_user_name']) && $_SESSION['tuplit_sess_order_user_name'] != ''){
	 $UserName		=	$_SESSION['tuplit_sess_order_user_name'];
}
if(isset($_SESSION['tuplit_sess_order_visit']) && $_SESSION['tuplit_sess_order_visit'] != ''){
	 $VisitCount	=	$_SESSION['tuplit_sess_order_visit'];
}
if(isset($_SESSION['tuplit_sess_order_total_spend']) && $_SESSION['tuplit_sess_order_total_spend'] != ''){
	 $TotalSpend	=	$_SESSION['tuplit_sess_order_total_spend'];
}
if(isset($_SESSION['tuplit_sess_from_date']) && $_SESSION['tuplit_sess_from_date'] != ''){
	 $FromDate		=	$_SESSION['tuplit_sess_from_date'];
}
if(isset($_SESSION['tuplit_sess_to_date']) && $_SESSION['tuplit_sess_to_date'] != ''){
	 $ToDate		=	$_SESSION['tuplit_sess_to_date'];
}

//getting customer list
$url					=	WEB_SERVICE.'v1/merchants/customerList/?UserName='.$UserName.'&TotalOrders='.$VisitCount.'&TotalPrice='.$TotalSpend.'&Start='.$cur_page.'&Limit='.$per_page.'&Type='.$show.'';//'&FromDate='.$FromDate.'&ToDate='.$ToDate.
$curlCustomerResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
if(isset($curlCustomerResponse) && is_array($curlCustomerResponse) && $curlCustomerResponse['meta']['code'] == 201 && is_array($curlCustomerResponse['CustomerList']) ) {
	if(isset($curlCustomerResponse['CustomerList'])){
		$customerList = $curlCustomerResponse['CustomerList'];	
		$tot_rec	  = $curlCustomerResponse['meta']['totalCount'];
	}
} else if(isset($curlCustomerResponse['meta']['errorMessage']) && $curlCustomerResponse['meta']['errorMessage'] != '') {
		$errorMessage	=	$curlCustomerResponse['meta']['errorMessage'];
} else {
		$errorMessage	= 	"Bad Request";
} 
commonHead();

?>

<body class="skin-blue fixed">
	<?php if($show ==1){?>
	
		<div class="col-xs-12 no-padding">	 
			<section class="content-header"> 
                <h1 class="no-margin space_bottom">Top 5 Customers</h1>
            </section>
			<div class="product_list">
				<div class="box box-primary no-padding no-margin">
					<div class="box-body table-responsive no-padding no-margin">
						
						<?php if(isset($customerList) && !empty($customerList)) { ?>
						<table class="table table-hover" border="0">
                               <tr>
									<th class="text-center" width="3%">#</th>									
									<th width="55%">Name</th>
									<th width="10%" class="text-right">Total Amount</th>
									<th width="" class="text-center">No.of Transactions</th>
								</tr>
                              <?php
							  	foreach($customerList as $key=>$value){
									
									$count += 1;
								?>
							<tr>
								<td align="center"><?php echo $key+1;?></td>												
								<td>
									<div class="col-xs-3 col-sm-1 col-md-1 no-padding">
									<?php if(isset($value["Photo"]) && $value["Photo"] != ''){ 
											?>
									
										<a <?php if(isset($value["OriginalPhoto"]) ) { ?>href="<?php echo $value["OriginalPhoto"]; ?>" class="fancybox" title="<?php  echo  ucfirst($value["FirstName"]).' '.ucfirst($value["LastName"]);?>" <?php } ?> > 
											<img  width="36" height="36" align="top" class="img_border" src="<?php echo  $value["Photo"];?>" >
										</a>
									
								<?php } else {?> <img  width="36" height="36" align="top" class="img_border" src="<?php echo MERCHANT_IMAGE_PATH.'no_user.jpeg';?>" > <?php  } ?>
								</div>
								<div class="col-xs-9 col-sm-5 col-md-11 "> 										
										<?php  echo  ucfirst($value["FirstName"]).' '.ucfirst($value["LastName"]);?>					
								</div>
								</td>
								<td align="right"><?php if(isset($value["TotalPrice"]) && $value["TotalPrice"]!= ''){ echo '$'.number_format((float)$value["TotalPrice"],2,'.',',');}?></td>
								<td align="center"><?php if(isset($value["TotalOrders"]) && $value["TotalOrders"]!= ''){ echo $value["TotalOrders"];}?></td>
							</tr>
							<?php } //end for ?>	
                           </table>
							<!-- End product List -->						 
						<?php } else { ?>
							<div class="row clear">		
								 <div align="center" class="alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10"><i class="fa fa-warning"></i><?php echo $errorMessage	;?>	</div>							
							</div>							
						<?php } ?>						
					</div><!-- /.box-body -->
				</div>					
			</div>
		</div>
	
		<?php } else {?>
		<?php  top_header(); ?>
		<section class="content no-padding">
		<div class="col-lg-10 box-center">	
				<?php  AnalyticsTab(); ?>
		<div class="row">
		<div class="btn-top left-padding">
		<div class="col-sm-3 btn-inline space_top pull-right no-margin">
			<?php if(isset($customerList) && !empty($customerList)) { ?>
				<a href="CustomerList?Show=1" class="newWindow col-xs-12 btn btn-success"><i class="fa fa-users"></i> View top 5 customers</a>
			<?php } ?>
		</div>
		</div>
		</div>
		</section>
		<section class="content no-top-padding">
		<div class="col-lg-10" style="margin:auto;float:none;" >	
			<section class="row content-header">
                <h1 class="no-top-margin pull-left">Customer Analytics</h1>
            </section>
				<div class="row">
				<div class="product_list">
					<form name="search_merchant" action="CustomerList?cs=1" method="post">
					<div class="box box-primary">
						<div class="box-body no-padding" >				
							<div class="col-sm-4 form-group">
								<label>User Name</label>
								<input type="text" class="form-control" name="UserName" id="UserName"  value="<?php  if(isset($_SESSION['tuplit_sess_order_user_name']) && $_SESSION['tuplit_sess_order_user_name'] != '') echo unEscapeSpecialCharacters($_SESSION['tuplit_sess_order_user_name']);  ?>" >
							</div>
						</div>
						<div class="box-body no-padding" >				
							<div class="col-sm-4 form-group">
								<label>No.of Orders</label>
								<input type="text" class="form-control" name="Vistit" id="Vistit"  value="<?php  if(isset($_SESSION['tuplit_sess_order_visit']) && $_SESSION['tuplit_sess_order_visit'] != '') echo $_SESSION['tuplit_sess_order_visit'];  ?>" >
							</div>
						</div>
						<div class="box-body no-padding" >				
							<div class="col-sm-4 form-group">
								<label>Total Spend</label>
								<input type="text" class="form-control" name="TotalSpend" id="TotalSpend"  value="<?php  if(isset($_SESSION['tuplit_sess_order_total_spend']) && $_SESSION['tuplit_sess_order_total_spend'] != '') echo $_SESSION['tuplit_sess_order_total_spend'];  ?>" >
							</div>
						</div>
						<!-- <div class="box-body no-padding" >				
							<div class="col-sm-2 form-group">
								<label>Start Date</label>
								<input  type="text" id = "from_date" class="form-control datepicker" autocomplete="off" title="Select Date" name="from_date" value="<?php //if(isset($FromDate) && $FromDate != '') echo date('m/d/Y',strtotime($FromDate));?>" onchange="return emptyDates(this);">
							</div>
						</div>
						<div class="box-body no-padding" >				
							<div class="col-sm-2 form-group">
								<label>End Date</label>
								<input type="text" id = "to_date" class="form-control datepicker" autocomplete="off"  title="Select Date" name="to_date" value="<?php //if(isset($ToDate) && $ToDate != '') echo date('m/d/Y',strtotime($ToDate));?>" onchange="return emptyDates(this);">
							</div>
						</div> -->
						<div class="box-footer col-sm-12" align="center">
							<input type="submit" class="btn btn-success" name="Search" id="Search" value="Search" title="Search">
						</div>					
					</div>
					</form>
				</div>
			</div>
			<div class="row product_list paging">
					<?php if(isset($customerList) && is_array($customerList) && count($customerList) > 0){ ?>
					<div class="col-xs-12 col-sm-3 no-padding">
						<span class="totl_txt">Total Customer(s) : <b><?php echo $tot_rec; ?></b>
							
						</span>
					</div>
					<div class="col-xs-12 col-sm-9 no-padding">
						<div class="dataTables_paginate paging_bootstrap row no-margin">
								<?php pagingControlLatestAjax($tot_rec,'CustomerList'); ?>
						</div>
					</div>
					<?php } ?>
			</div>
			<div class="row">
            	<div class="col-xs-12 no-padding">
				   <?php if(isset($customerList) && !empty($customerList)) { ?>
		              <div class="box">
		               <div class="box-body table-responsive no-padding no-margin">
						<table class="table table-hover">
                               <tr>
									<th align="center" width="3%" class="text-center">#</th>									
									<th width="25%">Name</th>
									<th width="10%">First Order</th>
									<th width="10%">Last Order</th>
									<th width="10%" class="text-right">Total Amount</th>
									<th width="10%" class="text-right">Avg. Transaction</th>
									<th width="10%" class="text-center word-break">No.of Transactions</th>
									<th width="10%" class="text-center">Days between orders (average)</th>
									<!-- <th width="5%" class="text-center word-break">Average</th> -->
									<th colspan="2" width="10%" class="text-center">Actions</th>
								</tr>
                              <?php
							  	foreach($customerList as $key=>$value){
									
									$count += 1;
								?>
							<tr>
								<td align="center"><?php echo (($_SESSION['curpage'] - 1) * ($_SESSION['perpage']))+$key+1;?></td>												
								<td>
									<div class="col-xs-10 col-md-11 no-padding"> 										
										<a href="UserDetail?viewId=<?php echo base64_encode($value["userId"]);?>&cs=1" class="userWindow white-space" title="View Customer Details" ><?php  echo  ucfirst($value["FirstName"]).' '.ucfirst($value["LastName"]);?></a>								
									</div>
								</td>
								<td nowrap><?php if(isset($value["FirstVisit"]) && $value["FirstVisit"] != '0000-00-00 00:00:00'){ echo date('m/d/Y',strtotime($value["FirstVisit"])); }else echo '-';?></td>
								<td nowrap><?php if(isset($value["LastVisit"]) && $value["LastVisit"] != '0000-00-00 00:00:00'){ echo date('m/d/Y',strtotime($value["LastVisit"])); }else echo '-';?></td>
								<td align="right"><?php if(isset($value["TotalPrice"]) && $value["TotalPrice"]!= ''){ echo '$'.number_format((float)$value["TotalPrice"],2,'.',',');}?></td>
								<td align="right"><?php if(isset($value["AverageSpend"]) && $value["AverageSpend"]!= ''){ echo '$'.number_format((float)$value["AverageSpend"],2,'.',',');}?></td>
								<td align="center"><?php if(isset($value["TotalOrders"]) && $value["TotalOrders"]!= ''){ echo $value["TotalOrders"];}?></td>
								<td align="center"><?php echo $value["DayDifference"]; ?></td>
								
								<td width="5%" class="text-center">
									<?php if(!empty($value["Comments"])) { ?>
										<a href="UserComments?viewId=<?php echo base64_encode($value["userId"]);?>&cs=1" class="newWindow" title="Comments"><i class="fa fa-comments fa-lg"></i></a>
									<?php } else { ?>
										<i class="fa fa-comments fa-lg text-gray"></i>
									<?php } ?>
								</td>
								<td width="5%" class="text-center"><a href="UserOrders?viewId=<?php echo base64_encode($value["userId"]);?>&cs=1" class="newWindow" title="View Orders" ><i class="fa fa-search fa-lg"></i></a></td>
							</tr>
							<?php } //end for ?>	
                           </table>
							<!-- End product List -->						 
						<?php } else { ?>
							<div class="row clear">		
								 <div align="center" class="alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10"><i class="fa fa-warning"></i> <?php echo $errorMessage	;?>	</div>							
							</div>							
						<?php } ?>						
					</div><!-- /.box-body -->
				</div>					
			</div>	
		 </div>
		</section>
		
		<?php footerLogin(); ?>
		<?php commonFooter(); ?>
	<?php } ?>
	
	<script type="text/javascript">
		$(document).ready(function() {
			$(".newWindow").fancybox({
				scrolling: 'auto',			
				type: 'iframe',
				width: '800',
				maxWidth: '100%',
				fitToView: false,
			});
			$(".userWindow").fancybox({
					scrolling: 'none',			
					type: 'iframe',
					width: '350',
					maxWidth: '100%',  // for respossive width set					
					fitToView: false,
					 title: null,
			});
		});
		$('.fancybox').fancybox();
		/*$(".datepicker").datepicker({
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
		 function emptyDates(arg) { 
			var id = arg.getAttribute('name');		
			if(id == 'year' || id == 'month') {
				$('#from_date').val('');
				$('#to_date').val('');		
			}
			else {
				$("#year").children("option[value ='']").attr('selected', true);
				$("#month").children("option[value ='']").attr('selected', true);
			}
		 }*/
	</script>
</html>
