<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
require_once('controllers/AdminController.php');
$msg = '';
require_once('controllers/AnalyticsController.php');
$analyticsObj   =   new AnalyticsController();
require_once('controllers/MerchantController.php');
$merchantObj   	=   new MerchantController();
commonHead(); 
$errorMessage = 'No Record Found';
$show = $count = 0;
$image_path = $original_image_path = '';
if(isset($_GET['cs']) && $_GET['cs']=='1') { 
	destroyPagingControlsVariables();	
	$_SESSION['tuplit_sess_order_user_name']	= 	'';
	$_SESSION['tuplit_sess_order_visit']		=	'';
	$_SESSION['tuplit_sess_order_total_spend']	=	'';
	$_SESSION['tuplit_sess_merchant_id']		=	'';
	$_SESSION['tuplit_sess_DateFrom']			=	'';
}

if(isset($_GET['Show']) && $_GET['Show'] == 1){
	$show = 1;
}

if(isset($_POST['Search']) && $_POST['Search'] != ''){
    if(isset($_POST['UserName']) && $_POST['UserName'] != ''){
		$_SESSION['tuplit_sess_order_user_name'] 	=	$_POST['UserName'];
	}
	 if(isset($_POST['Vistit']) && $_POST['Vistit'] != ''){
		$_SESSION['tuplit_sess_order_visit'] 		=	trim($_POST['Vistit']);
	}
	 if(isset($_POST['TotalSpend']) && $_POST['TotalSpend'] != ''){
		$_SESSION['tuplit_sess_order_total_spend']	=	trim($_POST['TotalSpend']);
	}
	 if(isset($_POST['DateFrom']) && $_POST['DateFrom'] != ''){
		$_SESSION['tuplit_sess_DateFrom']		=	$_POST['DateFrom'];
	}
	 if(isset($_POST['Merchant']) && $_POST['Merchant'] != ''){
		$_SESSION['tuplit_sess_merchant_id']		=	$_POST['Merchant'];
	}
}
setPagingControlValues('o.id',ADMIN_PER_PAGE_LIMIT);
$fields 		= '';
$condition 		= '';
$analyticsList  = $analyticsObj->getAnalyticsList($fields,$condition,$show);
$tot_rec 		= $analyticsObj->getTotalRecordCount();
if($tot_rec!=0 && !is_array($analyticsList)) {
	$_SESSION['curpage'] = 1;
	$analyticsList  = $analyticsObj->getAnalyticsList($fields,$condition,$show);
}
$condition       	= "  Status =1 order by CompanyName asc";
$field				=	' id,CompanyName';
$merchantList		= 	$merchantObj->selectMerchantDetails($field,$condition);
?>
<body class="skin-blue">
<?php if($show ==1){?>
	
		<div class="col-xs-12 no-padding">	 
			<section class="content-header"> 
                <h1 class="no-margin space_bottom">Top 5 Customers</h1>
            </section>
			<div class="product_list">
				<div class="box box-primary no-padding no-margin">
					<div class="box-body table-responsive no-padding no-margin">
						
						<?php if(isset($analyticsList) && !empty($analyticsList)) { ?>
						<table class="table table-hover" border="0">
                               <tr>
									<th class="text-center" width="3%">#</th>									
									<th width="55%">Name</th>
									<th width="10%" class="text-right">Total Amount</th>
									<th width="" class="text-center">No.of Transactions</th>
								</tr>
                              <?php
							  	foreach($analyticsList as $key=>$value){
									if(isset($value->Photo) && $value->Photo != ''){
									$user_image = $value->Photo;
									if (!SERVER){
										if(file_exists(USER_THUMB_IMAGE_PATH_REL.$user_image))
											$image_path = USER_THUMB_IMAGE_PATH.$user_image;
										if(file_exists(USER_IMAGE_PATH_REL.$user_image))
											$original_image_path = USER_IMAGE_PATH.$user_image;
									}
									else{
										if(image_exists(2,$user_image))
											$image_path = USER_THUMB_IMAGE_PATH.$user_image;
										if(image_exists(1,$user_image))
											$original_image_path = USER_IMAGE_PATH.$user_image;
									}
								}
									$count += 1;
								?>
							<tr>
								<td align="center"><?php echo $key+1;?></td>												
								<td>
									<div class="col-xs-3 col-sm-1 col-md-1 no-padding">
									<?php if(isset($image_path) && $image_path != ''){ 
											?>
									
										<a <?php if(isset($original_image_path) ) { ?>href="<?php echo $original_image_path; ?>" class="fancybox" title="<?php  echo  ucfirst($value->FirstName).' '.ucfirst($value->LastName);?>" <?php } ?> > 
											<img  width="36" height="36" align="top" class="img_border" src="<?php echo  $image_path;?>" >
										</a>
									
								<?php } else {?> <img  width="36" height="36" align="top" class="img_border" src="<?php echo ADMIN_IMAGE_PATH.'no_user.jpeg';?>" > <?php  } ?>
								</div>
								<div class="col-xs-9 col-sm-5 col-md-11 "> 										
										<?php  echo  ucfirst($value->FirstName).' '.ucfirst($value->LastName);?>					
								</div>
								</td>
								<td align="right"><?php if(isset($value->TotalPrice) && $value->TotalPrice != ''){ echo '$'.number_format((float)$value->TotalPrice,2,'.',',');}?></td>
								<td align="center"><?php if(isset($value->TotalOrders) && $value->TotalOrders != ''){ echo $value->TotalOrders;}?></td>
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
	<?php top_header(); ?>
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<div class="row col-xs-10">
			<h1><i class="fa fa-list"></i> Customer Analytics </h1>			
		</div>
	</section>
	<!-- Main content -->
	<section class="content">
		<div class="col-sm-3 btn-inline space_top pull-right no-margin" style="padding-bottom:20px;">
			<?php if(isset($analyticsList) && !empty($analyticsList)) { ?>
				<a href="CustomerAnalytics?Show=1" class="newWindow col-xs-12 btn btn-success"><i class="fa fa-users"></i> View top 5 customers</a>
			<?php } ?>
		</div>
		<div class="row">
			<div class="col-xs-12">
				<form name="search_Analytics" action="CustomerAnalytics?cs=1" method="post">
				<div class="box box-primary">	
						<div class="box-body no-padding" >		
							<div class="form-group col-sm-4 col-xs-6">
							<label>Merchant</label>
							<select class="form-control " name="Merchant" id="Merchant" onchange="getProductCategory(this.value);">
								<option value="" >Select</option>								
								<?php if(isset($merchantList) && !empty($merchantList)) {
									foreach($merchantList as $m_key=>$m_val) {								
								?>
								<option value="<?php echo $m_val->id;?>" <?php if(isset($_SESSION['tuplit_sess_merchant_id']) && $_SESSION['tuplit_sess_merchant_id'] == $m_val->id) echo "selected"; ?>><?php echo ucfirst($m_val->CompanyName);?></option>
								<?php } } ?>								
							</select>
							</div>
						</div>
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
								<input type="text" class="form-control" name="TotalSpend" id="TotalSpend"  value="<?php if(isset($_SESSION['tuplit_sess_order_total_spend']) && $_SESSION['tuplit_sess_order_total_spend'] != '') echo $_SESSION['tuplit_sess_order_total_spend'];  ?>" >
							</div>
						</div>
						<div class="box-body no-padding" >				
							<div class="col-sm-3 form-group">
								<label>Date</label>
							<input type="text" class="form-control datepicker" name="DateFrom" id="DateFrom"  value="<?php  if(isset($_SESSION['tuplit_sess_DateFrom']) && $_SESSION['tuplit_sess_DateFrom'] != '') echo $_SESSION['tuplit_sess_DateFrom'];  ?>" >
							</div>
						</div>
						<div class="col-sm-12 box-footer clear" align="center">
							<label>&nbsp;</label>
							<input type="submit" class="btn btn-success" name="Search" id="Search" value="Search" >
						</div>
				</div>
				</form>				
			</div>	
		</div>	
		
		<div class="row product_list paging">
					<div class="col-xs-12 col-sm-2">
					<?php if(isset($analyticsList) && is_array($analyticsList) && count($analyticsList) > 0){ ?>
						<div class="dataTables_info">Total Customer(s)&nbsp:&nbsp;<strong><?php echo $tot_rec; ?></strong></div>
					</div>
					<div class="col-xs-12 col-sm-10">
						<div class="dataTables_paginate paging_bootstrap row">
								<?php pagingControlLatest($tot_rec,'Analytics'); ?>
						</div>
					</div>
					<?php } ?>
		</div>
		<div class="row">
            	<div class="col-xs-12">
				   <?php if(isset($analyticsList) && !empty($analyticsList)) { ?>
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
									<th width="10%" class="text-center word-break">Days Between Orders</th>
									<th colspan="2" width="10%" class="text-center">Actions</th>
								</tr>
                              <?php
							  	foreach($analyticsList as $key=>$value){ 
								$averagePrice = $averageSpend = 0;
								$diff 							= 	abs(strtotime($value->LastVisit)-strtotime($value->FirstVisit));
								$year							=	round($diff/(365*24*60*60));
								$month							=	round(($diff-($year*365*24*60*60))/(30*24*60*60));
								$dates							=	round(($diff-($year*365*24*60*60)-($month*30*24*60*60))/(24*60*60));
								$dayDifference					=	abs($dates);
								if($value->TotalOrders > 0){
									$averagePrice				=	$value->TotalPrice/$value->TotalOrders;
									$averageSpend				=	round($averagePrice,2);
								}
							  ?>
									<tr>
								<td align="center"><?php echo (($_SESSION['curpage'] - 1) * ($_SESSION['perpage']))+$key+1;?></td>												
								<td>
									<div class="col-xs-10 col-md-11 no-padding"> 										
										<a href="UserDetail?viewId=<?php echo $value->userId;?>&cs=1&bk=1" class="userWindow white-space" title="View Customer Details" ><?php  echo  ucfirst($value->FirstName).' '.ucfirst($value->LastName);?></a>								
									</div>
								</td>
								<td nowrap><?php if(isset($value->FirstVisit) && $value->FirstVisit != '0000-00-00 00:00:00'){ echo date('m/d/Y',strtotime($value->FirstVisit)); }else echo '-';?></td>
								<td nowrap><?php if(isset($value->LastVisit) && $value->LastVisit != '0000-00-00 00:00:00'){ echo date('m/d/Y',strtotime($value->LastVisit)); }else echo '-';?></td>
								<td align="right"><?php if(isset($value->TotalPrice) && $value->TotalPrice != ''){ echo '$'.number_format((float)$value->TotalPrice,2,'.',',');}?></td>
								<td align="right"><?php if(isset($averageSpend) && $averageSpend != ''){ echo '$'.number_format((float)$averageSpend,2,'.',',');}?></td>
								<td align="center"><?php if(isset($value->TotalOrders) && $value->TotalOrders != ''){ echo $value->TotalOrders;}?></td>
								<td align="center"><?php echo $dayDifference; ?></td>
								
								<td width="5%" class="text-center">
									<?php if(!empty($value->Comments)) { ?>
										<a href="CommentList?user_id=<?php echo $value->userId;?>&cs=1" class="view newWindow" title="Comments"><i class="fa fa-comments fa-lg"></i></a>
									<?php } else { ?>
										<i class="fa fa-comments fa-lg text-gray"></i>
									<?php } ?>
								</td>
								<td width="5%" class="text-center"><a href="OrderList?user_id=<?php echo $value->userId;?>&cs=1" class="newWindow" title="View Orders" ><i class="fa fa-search fa-lg"></i></a></td>
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
	<?php } ?>
<?php commonFooter(); ?>
<script type="text/javascript">
$(document).ready(function() {
	$(".newWindow").fancybox({
		scrolling: 'auto',			
		type: 'iframe',
		width: '800',
		maxWidth: '100%',
		
		fitToView: false,
	});
});
$('.fancybox').fancybox();
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