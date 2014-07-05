<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
require_once('controllers/StatisticsController.php');
$StatisticsObj   =   new StatisticsController();
commonHead(); 

$topmerchantlist = $topuserlist = $mechants_array = $user_array = array();
$fields = $condition  = '';
$Totalnoof_merchants = $Totalnoof_users  = $noof_orders = $totalmerchantlogin =  $totaluserlogin = 0; 
$totalprice = $new_count = $accepted_count = $rejected_count = $acceptedprice =   0;
$Orderdoneby_user = $Orderdoneby_merchant = 0;
$condition_filter = $order_filter  = '';
$order_filter 	= " and date(OrderDate) =  '".date('Y-m-d')."'";
$condition_filter = " and date(DateCreated) =  '".date('Y-m-d')."'";
$login_condition = "and  date(LastLoginDate) =  '".date('Y-m-d')."'";

$start_year = 2010;
$end_year   = 2020;
if(isset($_GET['cs']) && $_GET['cs']=='1') {	
	unset($_SESSION['sess_statistics_from_date']);
	unset($_SESSION['sess_statistics_to_date']);
	unset($_SESSION['sess_statistics_search_month']);
	unset($_SESSION['sess_statistics_search_year']);
}

//Search list
if(isset($_POST['Search']) && $_POST['Search'] != ''){	
	$_POST          = unEscapeSpecialCharacters($_POST);
    $_POST          = escapeSpecialCharacters($_POST);	
	$_SESSION['sess_statistics_from_date']    	= $_POST['from_date'];
	$_SESSION['sess_statistics_to_date']    	= $_POST['to_date'];
	$_SESSION['sess_statistics_search_month']   = $_POST['month']; 
	$_SESSION['sess_statistics_search_year']   	= $_POST['year'];	
	$order_filter  = $condition_filter = $login_condition = '';
	
	if(isset($_POST['month']) && isset($_POST['year']) && !empty($_POST['month']) && !empty($_POST['year'])) {
		$order_filter 	    .= " and month(OrderDate) =  '".$_POST['month']."' and  year(OrderDate) =  '".$_POST['year']."'";
		$condition_filter 	.= " and month(DateCreated) =  '".$_POST['month']."' and  year(DateCreated) =  '".$_POST['year']."'";
		$login_condition  	.= "and  month(LastLoginDate) =  '".$_POST['month']."' and  year(LastLoginDate) =  '".$_POST['year']."'";
	}
	else if(isset($_POST['month']) && !empty($_POST['month'])) {
		$order_filter 	    .= " and month(OrderDate) =  '".$_POST['month']."' and  year(OrderDate) =  '".date('Y')."'";
		$condition_filter  	.= " and month(DateCreated) =  '".$_POST['month']."' and  year(DateCreated) =  '".date('Y')."'";
		$login_condition   	.= " and month(LastLoginDate) =  '".$_POST['month']."' and  year(LastLoginDate) =  '".date('Y')."'";
	}
	else if(isset($_POST['year']) && !empty($_POST['year'])) {
		$order_filter 	    .= " and year(OrderDate) =  '".$_POST['year']."'";
		$condition_filter  	.= " and year(DateCreated) =  '".$_POST['year']."'";
		$login_condition   	.= " and year(LastLoginDate) =  '".$_POST['year']."'";
	}
	else if(isset($_POST['from_date']) && !empty($_POST['from_date']) && isset($_POST['to_date']) && !empty($_POST['to_date'])) {
		$order_filter 	    .= " and date(OrderDate) between '".date('Y-m-d',strtotime($_POST['from_date']))."' and '".date('Y-m-d',strtotime($_POST['to_date']))."'";
		$condition_filter  	.= " and date(DateCreated) between '".date('Y-m-d',strtotime($_POST['from_date']))."' and '".date('Y-m-d',strtotime($_POST['to_date']))."'";
		$login_condition   	.= " and date(LastLoginDate) between '".date('Y-m-d',strtotime($_POST['from_date']))."' and '".date('Y-m-d',strtotime($_POST['to_date']))."'";
	}
	else {
		$order_filter 	   .= " and date(OrderDate) =  '".date('Y-m-d')."'";
		$condition_filter  .= " and date(DateCreated) =  '".date('Y-m-d')."'";
		$login_condition   .= " and date(LastLoginDate) =  '".date('Y-m-d')."'";
	}
}

$date = date('m/d/Y');
//Initializing start date and end date on page load 
if(!isset($_SESSION['sess_statistics_from_date'])) 
	$_SESSION['sess_statistics_from_date']	=	$date;
if(!isset($_SESSION['sess_statistics_to_date'])) 
	$_SESSION['sess_statistics_to_date']	=	$date;

//Total number of Merchant login 
$fields = "count(id) as tot_count ";
//$login_condition = " date(LastLoginDate) =  '".date('Y-m-d')."'";
$merchantstatisticslogin	= $StatisticsObj->getRegisteredMerchants($fields,$login_condition);
if(isset($merchantstatisticslogin) && is_array($merchantstatisticslogin) && count($merchantstatisticslogin) >0)
	$totalmerchantlogin = $merchantstatisticslogin['0']->tot_count;

//Total number of users login 
$fields = "count(id) as tot_count ";
$userstatisticslogin	= $StatisticsObj->getRegisteredUsers($fields,$login_condition);
if(isset($userstatisticslogin) && is_array($userstatisticslogin) && count($userstatisticslogin) >0)
	$totaluserlogin = $userstatisticslogin['0']->tot_count;

//Number of registered Merchants
$fields = "count(id) as tot_count";
$condition = $condition_filter." and Status = 1";
$noofmerchants_array = $StatisticsObj->getRegisteredMerchants($fields,$condition);
if(isset($noofmerchants_array) && is_array($noofmerchants_array) && count($noofmerchants_array) > 0){
	$Totalnoof_merchants = $noofmerchants_array[0]->tot_count;
}
//Number of registered users
$fields = "count(id) as tot_count";
$condition = $condition_filter." and Status = 1";
$noofusers_array = $StatisticsObj->getRegisteredUsers($fields,$condition);
if(isset($noofusers_array) && is_array($noofusers_array) && count($noofusers_array) > 0){
	$Totalnoof_users = $noofusers_array[0]->tot_count;
}
// Overall order details
$condition = $order_filter." group by OrderStatus";
$OverallOrderList = $StatisticsObj->getOverallStatOrderList($condition);
if(isset($OverallOrderList) && is_array($OverallOrderList) && count($OverallOrderList) > 0){
	
	foreach($OverallOrderList as $overall_orderkey=>$overall_ordervalue){
		
		if($overall_ordervalue->OrderStatus == 0){
			$new_count = $overall_ordervalue->totalcount;
		}
		else if($overall_ordervalue->OrderStatus == 1){
			$accepted_count = $overall_ordervalue->totalcount;
			$acceptedprice  = $overall_ordervalue->Price;
		}
		else if($overall_ordervalue->OrderStatus == 2){
			$rejected_count = $overall_ordervalue->totalcount;
		}
		
		if(isset($overall_ordervalue->totalcount) && $overall_ordervalue->totalcount != ''){
			$noof_orders = $noof_orders + $overall_ordervalue->totalcount;
		}
		
		if($overall_ordervalue->Price != ''){
			$totalprice = $totalprice + $overall_ordervalue->Price;
		}
	}	
}
//OrderDoneBy list
$condition = $order_filter." group by OrderDoneBy";
$OrderDonebyList = $StatisticsObj->getOverallStatOrderList($condition);
if(isset($OrderDonebyList) && is_array($OrderDonebyList) && count($OrderDonebyList) > 0){	
	foreach($OrderDonebyList as $orderdonekey=>$orderdonevalue){
	
		if($orderdonevalue->OrderDoneBy == 1){
			$Orderdoneby_user = $orderdonevalue->totalcount;
		}
		else if($orderdonevalue->OrderDoneBy == 2){			
			$Orderdoneby_merchant  = $orderdonevalue->totalcount;
		}
		
	}
}

// Top 5 Users
$fields = "o.*,sum(`TotalPrice`) as OrderdPrice,count(o.id) as TotalCount,concat(u.FirstName , ' ',u.LastName) as UserName,u.Photo";
$condition = " Group BY fkUsersId order by OrderdPrice desc limit 5 ";
$topuserlist = $StatisticsObj->getTopUsersList($fields,$condition);
// Top 5 Merchants
$fields = "o.*,sum(`TotalPrice`) as OrderdPrice,count(o.id) as TotalCount,m.CompanyName,m.Icon";
$condition = " Group BY fkMerchantsId order by OrderdPrice desc limit 5 ";
$topmerchantlist = $StatisticsObj->getTopMerchantsList($fields,$condition);
?>
<body class="skin-blue">
<?php top_header(); ?>
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<div class="row col-xs-10">
			<h1><i class="fa fa-list"></i> Statistics </h1>			
		</div>
	</section>
	<!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-xs-12">
				<form name="search_statistics" action="Statistics" method="post">
				<div class="box box-primary">					
					
					<div class="col-sm-3 form-group">
						<label>Start Date</label>
							<input  type="text" id = "from_date" class="form-control datepicker" autocomplete="off" title="Select Date" name="from_date" value="<?php if(isset($_SESSION['sess_statistics_from_date'])) echo $_SESSION['sess_statistics_from_date']; ?>" onchange="return emptyDates(this);">
					</div>
					<div class="col-sm-3 form-group">
						<label>End Date</label>
							<input type="text" id = "to_date" class="form-control datepicker" autocomplete="off"  title="Select Date" name="to_date" value="<?php if(isset($_SESSION['sess_statistics_to_date'])) echo $_SESSION['sess_statistics_to_date']; ?>" onchange="return emptyDates(this);">
					</div>
					<div class="col-sm-3 form-group">
						<label>Month</label>
						<select name="month" id ="month" class="form-control" onchange="return emptyDates(this);">
						<option value="">Select</option>
						<?php if(isset($month_name) && is_array($month_name) && count($month_name) > 0 ) { 
								foreach($month_name as $monthkey=>$monthval){
						?>
							<option value="<?php echo $monthkey; ?>" <?php if(isset($_SESSION['sess_statistics_search_month']) && $_SESSION['sess_statistics_search_month'] == $monthkey) echo "selected"; ?>><?php echo $monthval; ?></option>
						<?php } } ?>
						</select>
					</div>
					<div class="col-sm-3 form-group">
						<label>Year</label>
						<select name="year" id ="year" class="form-control" onchange="return emptyDates(this);">
							<option value="">Select</option>
							<?php for($i = $start_year;$i<=$end_year;$i++){ ?>
								<option value="<?php echo $i; ?>" <?php if(isset($_SESSION['sess_statistics_search_year']) && $_SESSION['sess_statistics_search_year'] == $i) echo "selected"; ?>  ><?php echo $i; ?></option>
							<?php } ?>
						</select>
					</div>
					
					<div class="col-sm-12 box-footer clear" align="center">
						<label>&nbsp;</label>
						<input type="submit" class="btn btn-success" name="Search" id="Search" value="Search">
					</div>
				</div>
				</form>
			</div>			
		</div>
		<div>
			<div><h1></h1></div>
			<div class="box">
			  <div class="box-body table-responsive no-padding no-margin">
				 <table class="table table-hover">
 					<tr><th width="35%">Process </th><th width="65%">Total</th></tr>
					<tr><td>No. of Users Registered</td><td><?php echo $Totalnoof_users; ?></td></tr>
					<tr><td>No. of Merchants Registered</td><td><?php echo $Totalnoof_merchants; ?></td></tr>
					<tr><td>No. of Users login  </td><td><?php echo $totaluserlogin ; ?></td></tr>		
					<tr><td>No. of Merchants login </td><td><?php echo $totalmerchantlogin ; ?></td></tr>	
					<tr><td>No .of Orders done by Users </td><td><?php echo $Orderdoneby_user ; ?></td></tr>
					<tr><td>No .of Orders done by Merchants </td><td><?php echo $Orderdoneby_merchant ; ?></td></tr>								
					<tr><td>No. of Orders</td><td><?php echo $noof_orders."<p class='help-block no-margin'>(".$new_count."- New, ".$rejected_count." - Rejected, ".$accepted_count." - Accepted)</p>"; ?></td></tr>
					<tr><td>Total Order Price</td><td><?php echo price_fomat($totalprice)." <p class='help-block no-margin'>( Accepted - ".price_fomat($acceptedprice).")</p>" ; ?></td></tr>																
				</table>
			</div>	
		  </div>	
		</div>
		
		<div>
		<?php if(isset($topmerchantlist) && is_array($topmerchantlist) && count($topmerchantlist) > 0){?>
		<div><h1>Merchant List</h1></div>
		<div class="box">
                       <div class="box-body table-responsive no-padding no-margin">
                           <table class="table table-hover">
                               <tr> 							
									<th width="25%">Merchant Details</th>
									<th width="10%">Order Price</th>	
									<th width="10%">Order Total</th>	
									<?php
										 $i = 0;
											foreach($topmerchantlist as $merchantkey=>$merchantvalue){	
										?>	
							   <tr>
							  		<td>
										<?
										$icon_image_path = '';
										$merchant_image = $merchantvalue->Icon;
										$icon_image_path = ADMIN_IMAGE_PATH.'no_user.jpeg';
										if(isset($merchant_image) && $merchant_image != ''){
											if(SERVER){
												if(image_exists(6,$merchant_image))
													$icon_image_path = MERCHANT_ICONS_IMAGE_PATH.$merchant_image;
											}
											else{
												if(file_exists(MERCHANT_ICONS_IMAGE_PATH_REL.$merchant_image))
													$icon_image_path = MERCHANT_ICONS_IMAGE_PATH.$merchant_image;
											}
										}
									?>
									<div> 
										 
											<?php if(isset($icon_image_path) && $icon_image_path != ''){ ?>
											<img width="36" height="36"  align="top" class="img_border" src="<?php echo $icon_image_path;?>" >
											<?php }?>
											<?php if(isset($merchantvalue->CompanyName) && $merchantvalue->CompanyName != ''){ ?>
											<span title="Company Name">
												<?php echo "<b>".$merchantvalue->CompanyName."</b>";  ?>
											</span><br>   
											<?php }?>
										
									
									</div>
									</td>
									<td><?php echo price_fomat($merchantvalue->OrderdPrice) ;?></td>
									<td><?php echo $merchantvalue->TotalCount ;?></td>
							    </tr>
									<?php } ?>	
                           </table>
                       </div><!-- /.box-body -->
                   </div><!-- /.box -->		
		<?php }?> 
		</div>	
		
		<div>
		<?php if(isset($topuserlist) && is_array($topuserlist) && count($topuserlist) > 0){?>
		<div><h1>Users List</h1></div>
		<div class="box">
                       <div class="box-body table-responsive no-padding no-margin">
                           <table class="table table-hover">
                               <tr> 							
									<th width="25%">User Details</th>
									<th width="10%">Order Price</th>	
									<th width="10%">Order Total</th>	
									<?php
										 $i = 0;
											foreach($topuserlist as $userkey=>$uservalue){		
										
										$image_path = '';
										$photo = $uservalue->Photo;
										$original_path = $image_path = ADMIN_IMAGE_PATH.'no_user.jpeg';
										
										if(isset($photo) && $photo != ''){
											$user_image = $photo;
											if(SERVER){
												if(image_exists(2,$user_image))
													$image_path = USER_THUMB_IMAGE_PATH.$user_image;
												if(image_exists(1,$user_image))
													$original_path = USER_IMAGE_PATH.$user_image;
												
											}else{
												if(file_exists(USER_IMAGE_PATH_REL.$user_image))
													$original_path = USER_IMAGE_PATH.$user_image;
												if(file_exists(USER_THUMB_IMAGE_PATH_REL.$user_image))
													$image_path = USER_THUMB_IMAGE_PATH.$user_image;
											}
										}
										?>	
							   <tr>
							  		<td>	
									<div>			
										<?php if(isset($image_path) && $image_path != ''){ ?>
												<img id="<?php echo $uservalue->id ;?>"  width="36" height="36" align="top" class="img_border" src="<?php echo $image_path;?>" >	
										<?php } ?>
										<span>					
											<?php if(isset($uservalue->UserName) && $uservalue->UserName != ''){ echo ucfirst($uservalue->UserName); } ?>								
										</span>	
									</td>
									<td><?php echo price_fomat($uservalue->OrderdPrice) ;?></td>
									<td><?php echo $uservalue->TotalCount ;?></td>
							    </tr>
									<?php } ?>	
                           </table>
                       </div><!-- /.box-body -->
                   </div><!-- /.box -->		
		<?php }?> 
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
 }
   </script>
</html>