<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
require_once('controllers/AdminController.php');
$msg = '';
require_once('controllers/AnalyticsController.php');
$analyticsObj   =   new AnalyticsController();
commonHead(); 
$errorMessage = 'No Record Found';
if(isset($_GET['cs']) && $_GET['cs']=='1') { 
	destroyPagingControlsVariables();	
	$_SESSION['tuplit_sess_order_user_name']	= 	'';
	$_SESSION['tuplit_sess_order_visit']		=	'';
	$_SESSION['tuplit_sess_order_total_spend']	=	'';
}

//echo "==>".__line__."<====<pre>";print_r($_POST);echo "</pre>=====";

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
}
setPagingControlValues('o.OrderDate',ADMIN_PER_PAGE_LIMIT);
$fields = ' u.FirstName, u.LastName, u.DateModified, u.LastLoginDate, count(o.id) AS no_of_order, SUM(o.TotalPrice) as total_price, o.OrderDate, o.id as order_id';
$condition = '';
$analyticsList  = $analyticsObj->getAnalyticsList($fields,$condition);
$tot_rec 		= $analyticsObj->getTotalRecordCount();
if($tot_rec!=0 && !is_array($analyticsList)) {
	$_SESSION['curpage'] = 1;
	$analyticsList  = $analyticsObj->getAnalyticsList($fields,$condition);
}
//echo "==>".__line__."<====<pre>";print_r($analyticsList);echo "</pre>=====";
$user_name = $firstorder = $lastorder = $date_diff = array();
if(isset($analyticsList) && is_array($analyticsList) && count($analyticsList)){
	foreach($analyticsList as $key=>$val){
		$date_diff_val = '';
		$user_name[$key]  = $val->FirstName.' '.$val->LastName;
		$firstorder[$key] = $analyticsObj->getOrdersDetail("OrderDate",$val->user_id,'asc','limit 0,1'); // get the first order date for the user
		$lastorder[$key]  = $analyticsObj->getOrdersDetail("OrderDate",$val->user_id,'desc','limit 0,1'); // get the last order date for the user
		if(isset($lastorder[$key][0]->OrderDate)  && isset($firstorder[$key][0]->OrderDate) ){		
			$date_diff_val    = date_diff(date_create(date('Y-m-d',strtotime($firstorder[$key][0]->OrderDate))), date_create(date('Y-m-d',strtotime($lastorder[$key][0]->OrderDate))));
			$date_diff[$key]  = $date_diff_val->format("%a");
		}
		//$order_prix[$key] = $analyticsObj->getOrdersDetail(" count( id ) AS no_of_order, SUM( TotalPrice ) AS ttl_price ",$val->user_id,'asc',''); // get the total order price of the single user
	}
}

?>
<body class="skin-blue">
<?php top_header(); ?>
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<div class="row col-xs-10">
			<h1><i class="fa fa-list"></i> Analytics </h1>			
		</div>
	</section>
	<!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-xs-12">
				<form name="search_Analytics" action="Analytics?cs=1" method="post">
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
								<input type="text" class="form-control" name="TotalSpend" id="TotalSpend"  value="<?php if(isset($_SESSION['tuplit_sess_order_total_spend']) && $_SESSION['tuplit_sess_order_total_spend'] != '') echo $_SESSION['tuplit_sess_order_total_spend'];  ?>" >
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
					<?php if(isset($analyticsList) && is_array($analyticsList) && count($analyticsList) > 0){ ?>
					<div class="col-xs-12 col-sm-3 no-padding">
						<span class="totl_txt">Total Customer(s) : <b><?php echo $tot_rec; ?></b></span>
					</div>
					<div class="col-xs-12 col-sm-9 no-padding">
						<div class="dataTables_paginate paging_bootstrap row no-margin">
								<?php pagingControlLatest($tot_rec,'Analytics'); ?>
						</div>
					</div>
					<?php } ?>
		</div>
		<div class="row">
            	<div class="col-xs-12 no-padding">
				   <?php if(isset($analyticsList) && !empty($analyticsList)) { ?>
		              <div class="box">
		               <div class="box-body table-responsive no-padding no-margin">
						<table class="table table-hover">
                               <tr>
									<th align="center" width="5%" class="text-center">#</th>									
									<th width="16%">Name</th>
									<th width="10%">First Order</th>
									<th width="10%">Last Order</th>
									<th width="10%" class="text-right">Total Amount</th>
									<th width="10%" class="text-right">Avg. Transaction</th>
									<th width="10%" class="text-center word-break">No.of Transactions</th>
									<th width="10%" class="text-center word-break">Days Between Orders</th>									
								</tr>
                              <?php
							  	foreach($analyticsList as $key=>$value){ 
									$ttl_order_prix = $no_of_order = 0;
							  ?>
									<tr>
										<td align="center"><?php echo (($_SESSION['curpage'] - 1) * ($_SESSION['perpage']))+$key+1;?></td>												
										<td>
											<div class="col-xs-10 col-md-11 no-padding"> 										
												<!-- UserDetail?viewId=<?php //echo base64_encode($value->user_id);?>&cs=1 -->
												<a href="javascript:void(0);" class="userWindow white-space" title="View user Details" ><?php if(isset($user_name[$key]) && $user_name[$key] != ''){ echo ucfirst($user_name[$key]); } ?></a>								
											</div>
										</td>
										<td><?php if(isset($firstorder[$key][0]->OrderDate) && $firstorder[$key][0]->OrderDate != '0000-00-00 00:00:00')
													 echo date('m/d/Y',strtotime($firstorder[$key][0]->OrderDate)); else echo "-";
										?></td>
										<td><?php if(isset($lastorder[$key][0]->OrderDate) && $lastorder[$key][0]->OrderDate != '0000-00-00 00:00:00')
													 echo date('m/d/Y',strtotime($lastorder[$key][0]->OrderDate)); else echo "-";
										?></td>
										<td align="right">
										<?php 
											//$ttl_order_prix = $order_prix[$key][0]->ttl_price;											
											//$no_of_order = $order_prix[$key][0]->no_of_order;											
											
											$ttl_order_prix = $value->total_price;											
											$no_of_order = $value->no_of_order;											
											
											if(isset($ttl_order_prix) && $ttl_order_prix!= ''){ echo '$'.number_format((float)$ttl_order_prix,2,'.',',');} else echo "-"; ?>
										</td>
										<td align="right">
											<?php 
												if(isset($ttl_order_prix) && $ttl_order_prix !='' && isset($no_of_order) && $no_of_order !=''){
													$avg_trans = ($ttl_order_prix / $no_of_order); 
													if(isset($avg_trans) && $avg_trans !='')
														echo '$'.number_format((float)$avg_trans,2,'.',',');
													else
														echo '-';
												}else{
													echo '-';
												}
											?>
										</td>
										<td align="center"><?php if(isset($no_of_order) && $no_of_order !='') echo $no_of_order; else echo '-'; ?></td>
										<td align="center"><?php if(isset($date_diff[$key]) && $date_diff[$key] !='') echo $date_diff[$key]; else echo '-';?></td>
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
<script type="text/javascript"></script>
</html>