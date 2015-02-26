<?php 
//ob_start();
require_once('../includes/AdminCommonIncludes.php');
if(isset($_GET['action']) && $_GET['action'] == 'SET_ORDERING_WEBSERVICE'){
	$order_value = 0;
	require_once('../controllers/ServiceController.php');
	$serviceObj   =   new ServiceController();
	$ExistCondition = '';
	$service_exists = '0';
	if(isset($_GET['orderValue']) && $_GET['orderValue'] != '')
		$order_value = $_GET['orderValue'];
	if(isset($_GET['serviceId']) && $_GET['serviceId'] != '')
		$service_id = $_GET['serviceId'];
	if($order_value != '' && $service_id != '' )
		$ExistCondition = " and Ordering = ".$order_value." and id != ".$service_id." and Ordering!='0' ";		
	$field = " Ordering ";	
	$alreadyExist   = $serviceObj->selectServiceDetails($field,$ExistCondition);	
	if(isset($alreadyExist) && is_array($alreadyExist) && count($alreadyExist) > 0){
			$service_exists = 1;
	}	
	if($service_exists != '1'){
		if($order_value != '' && $service_id != '' ){
			$update_string 	    = " Ordering = ".$order_value;
			$condition 		    = " id = ".$service_id;
			$OrderingResult     = $serviceObj->updateServiceDetails($update_string,$condition);
		}
	}
	echo $service_exists;
}
if(isset($_POST['action']) && $_POST['action'] == 'DELETE_SLIDER'){
	require_once('../controllers/UserController.php');
	$userObj   =   new UserController();
	if(isset($_POST['idValue']) && $_POST['idValue'] != '')
	$delete_id		=	$_POST['idValue'];
	$deleteSlider   = 	$userObj->deleteSlider($delete_id);	
	echo 1;
}
if(isset($_GET['action']) && $_GET['action'] == 'SET_ORDERING_SLIDER'){
	$order_value = 0;
	require_once('../controllers/UserController.php');
	$userObj   =   new UserController();
	$ExistCondition = $slider_id = '';
	$service_exists = 0;
	if(isset($_GET['orderValue']) && $_GET['orderValue'] != '')
		$order_value = $_GET['orderValue'];
	if(isset($_GET['SliderId']) && $_GET['SliderId'] != '')
		$slider_id = $_GET['SliderId'];
	if(isset($_GET['slide_type']) && $_GET['slide_type'] != '')
		$slider_type = $_GET['slide_type'];
	if($order_value != '' && $slider_id != '' && $slider_type != '')
		$ExistCondition = "  `Order`  = ".$order_value." and id != '".$slider_id."' and `Order` !='0' and Status = 1 and SliderType = ".$slider_type." ";		
	$field = " `Order`,id ";	
	$alreadyExist   = $userObj->getSliderImageDetails($field,$ExistCondition);	
	if(isset($alreadyExist) && is_array($alreadyExist) && count($alreadyExist) > 0){
			$service_exists = 1;
	}	
	if($service_exists != '1'){
		if($order_value != '' && $slider_id != '' ){
			$update_string 	    = " `Order`= ".$order_value;
			$condition 		    = " id = ".$slider_id;
			$OrderingResult     = $userObj->updateSliderDetails($update_string,$condition);
		}
	}
	echo $service_exists;
}
if(isset($_POST['action']) && ($_POST['action']=='GET_PRODUCT_CATEGORY')){
	$sub_val	 = '';
	require_once('../controllers/ManagementController.php');
	$managementObj   =   new ManagementController();
	$merchant_id		 	 	= 	$_POST['m_id'];
	if($merchant_id != ''){
		$condition       	= " fkMerchantId IN( ".$merchant_id.",0) and Status =1 ORDER BY fkMerchantId,CategoryName asc";
		$field				=	' id,CategoryName';
		$productCategories  = $managementObj->selectProductCategoryDetails($field,$condition);
	}
	if(isset($productCategories) && is_array($productCategories) && count($productCategories) > 0)
	{
?>
	<select class="form-control " name="Category">
		<option value="" >Select</option>								
		<?php if(isset($productCategories) && !empty($productCategories)) {
			foreach($productCategories as $key=>$val) {								
		?>
		<option value="<?php echo $val->id;?>" <?php //if(isset($CategoryId) && $val->id == $CategoryId) echo "selected"; ?>><?php echo ucfirst($val->CategoryName);?></option>
		<?php } } ?>								
	</select>
<?php } 
}

if(isset($_GET['action']) && ($_GET['action']=='DRAW_CHART')){
	require_once('../controllers/StatisticsController.php');
	$StatisticsObj   =   new StatisticsController();
	$start_date = (isset($_GET['start_date']) && $_GET['start_date'] != '') ? $_GET['start_date'] : '';
	$end_date = (isset($_GET['end_date']) && $_GET['end_date'] != '') ? $_GET['end_date'] : '';	
	
	/*if(isset($orderlist) && is_array($orderlist) && count($orderlist) > 0){		
		$prefix = '';
		echo "[\n";
		foreach($orderlist as $orderkey=>$ordervalue){
		  echo $prefix . " {\n";
		  echo '  "Orderdate": "' . date('d/m/y',strtotime($ordervalue->OrderDate)). '",' . "\n";
		  echo '  "Orders": ' . $ordervalue->total_count . ',' . "\n";	 
		  echo " }";
		  $prefix = ",\n";
		}
		echo "\n]";
	}*/
	$order_filter 	= " and date(OrderDate) =  '".date('Y-m-d')."'";
	if(isset($start_date) && !empty($start_date) && isset($end_date) && !empty($end_date)) {
		$order_filter = " and date(OrderDate) between '".date('Y-m-d',strtotime($start_date))."' and '".date('Y-m-d',strtotime($end_date))."'";
	}
	
	$fields = "OrderDate,count(id) as total_count";
	$condition = $order_filter." group by DATE(OrderDate),Hour(OrderDate) order by OrderDate desc";
	//$condition = $order_filter." group by DATE(OrderDate)";
	
	$orderlist = $StatisticsObj->getorderlistbydate($fields,$condition);
	
	foreach($orderlist as $orderkey=>$ordervalue){
		//$order[]=array('Orderdate'=>date('d/m/y',strtotime($ordervalue->OrderDate)),'Orders'=>$ordervalue->total_count);
		$order[]=array('Orderdate'=>date('d M Y H:i',strtotime($ordervalue->OrderDate)),'Orders'=>$ordervalue->total_count);
		//$order[]=array('Orderdate'=>$ordervalue->OrderDate,'Orders'=>$ordervalue->total_count);
	}
	echo json_encode($order);
	//echo "[{y: 'July 24', a: 100},{y: 'July 25', a: 400},{y: 'July 26', a: 100},{y: 'July 27', a: 100},{y: 'July 28', a: 900},{y: 'July 29', a: 100}]";
						
	//echo "<pre>"; print_r($orderlist); echo "</pre>";
	die();
}
if( isset( $_GET['action'] )  && $_GET['action'] == 'GET_CURRENCY_FROM_COUNTRY') {
	if(isset($_GET['country_name']) && $_GET['country_name']!=''){
		$country_name	= strtolower($_GET['country_name']);
		$currency_array = array_change_key_case($country_currency_array);
		if(isset($currency_array[$country_name]))
			echo $currency_array[$country_name];
		else
			echo 0;
	}
}

/*---------------See More category-------------------*/
if(isset($_GET['action']) && $_GET['action'] == 'GET_CATEGORY_LIST'){
	require_once('../controllers/ManagementController.php');
	$managementObj   =   new ManagementController();
	$fields    		= " c.CategoryName,c.CategoryIcon,count(p.id) as ProductsCount ";
	$condition 		= " and c.Status in (1) ";
	$i = $_GET['start'];
	$_SESSION['startlimit'] = $_GET['start'];
	$CategoryListResult  	= $managementObj->getCategoriesList($fields,$condition);
	$tot_rec 		 		= $managementObj->getTotalRecordCount();
	require_once('../views/CategoryListing.php');
	if(1){
		?>
		<script>
				$('#result_count').val('<?php echo $_GET['start']+3;?>');
				var total = $('#total_count').val();
				var resultCount = ($('#result_count').val())-1;
				if(resultCount >= total || Number(resultCount)+1 == total) {
					$('#seeMoreLink').hide();
				}
		</script>
		<?php
	}
}
/*---------------Next/Prev merchant list-------------------*/
if(isset($_GET['action']) && $_GET['action'] == 'GET_IMAGES_LIST'){
	require_once('../controllers/MerchantController.php');
	$condition = "";
	$managementObj   =   new MerchantController();
	$fields    		= " m.* ";
	if(isset($_GET['search']) && !empty($_GET['search'])){
		
		$_SESSION['merchantSearch'] = $_GET['search'];
		$condition .= " and ( m.CompanyName LIKE '%".$_SESSION['merchantSearch']."%')";
		if(isset($_GET['type']) && $_GET['type'] == 0){
			$startValue		= 0;
		}else {$startValue	= $_GET['start']; }
		$_SESSION['startlimit'] = $startValue;
	}else { 
		$_SESSION['startlimit'] = $_GET['start'];
		$startValue		= $_GET['start'];
	}/*
	if(isset($_SESSION['merchantSearch'])){
		$condition .= " and ( m.CompanyName LIKE '%".$_SESSION['merchantSearch']."%')";
	}*/
	$condition 		.= " and m.Status in (0,1,2)";
	$i = $_GET['start'];
	
	$merchantListResult  	= $managementObj->getMerchantImagesList($fields,$condition);
	$tot_rec 		 		= $managementObj->getTotalRecordCount();
	require_once('../views/MerchantsImages_kal.php');
	if(1){
		?>
		<script>
				//alert($('#image_display_count').val());
				if($('#image_display_count').val() == '0'){
					$('#seePrevImages').hide();
				}
				$("#image_total_count").val('<?php echo $tot_rec;?>');		
				$('#image_display_count').val('<?php echo $startValue+10;?>');
				var total = $('#image_total_count').val();
				var resultCount = ($('#image_display_count').val())-1;
				if(resultCount >= total){ 
					$('#seeMoreImages').hide();
					//$('#seePrevImages').hide();
				}else if(total > resultCount){
					$('#seeMoreImages').show();
				}else if(total < resultCount){
					$('#seeMoreImages').hide();
					$('#seePrevImages').hide();
				}
				if($('#image_display_count').val() == '10' || total == '0'){
					$('#seePrevImages').hide();
				}
		</script>
		<?php
	}
}
/*---------------Product list-------------------*/
if(isset($_GET['action']) && $_GET['action'] == 'GET_PRODUCTS_LIST'){
	$condition				= '';
	require_once('../controllers/ProductController.php');
	$ProductObj   	=   new ProductController();
	$fields    				= 	" p.*,m.CompanyName,m.Icon,pc.CategoryName,m.DiscountTier as Discount ";
	if(isset($_GET['merchantId']) && !empty($_GET['merchantId'])){
		$condition			.= " and m.id = ".$_GET['merchantId'];
	}
	$condition 				.= 	" and p.Status in (1,2)";
	if(isset($_GET['start']) && !empty($_GET['start'])){
		$startLimit = $_GET['start'];
	}else{
		$startLimit = 0;
	}
	$productListResult  	= 	$ProductObj->getProductList($fields,$condition,$startLimit);
	$total_rec 		 		= 	$ProductObj->getTotalRecordCount();
	//$_SESSION['pdtStartLimit'] = $_GET['start'];
	require_once('../views/ProductImages.php');
	if(1){
		?>
		<script>
				if($('#product_display_count').val() == 0){
					$('#prevProduct').hide();
				}
				$("#product_total_count").val('<?php echo $total_rec;?>');		
				$('#product_display_count').val('<?php echo $_GET['start']+5;?>');
				var total 		= $('#product_total_count').val();
				var resultCount = ($('#product_display_count').val())-1;
				//alert(total); alert(resultCount);
				if(resultCount >= total){ 
					$('#nextProduct').hide();
				}else if(total > resultCount){
					$('#nextProduct').show();
				}else if(total < resultCount){
					$('#nextProduct').hide();
					$('#prevProduct').hide();
				}
				if($('#product_display_count').val() == '<?php echo count($productListResult);?>' || total == '0'){
					$('#prevProduct').hide();
				}
		</script>
		<?php
	}
}
/*---------------See More comments-------------------*/
if(isset($_GET['action']) && $_GET['action'] == 'GET_COMMENTS_LIST'){
	require_once('../controllers/CommentController.php');
	$condition		= "";
	$commentObj   	=   new CommentController();
	$fields    		= " com.*,u.FirstName,u.LastName,u.Photo ";
	if(isset($_GET['merchantId']) && !empty($_GET['merchantId'])){
		$condition .= " and m.id = ".$_GET['merchantId'];
	}
	$condition 		.= " and com.Status = 1";
	$i = $limit = $_GET['start'];
	$commentlist 	= $commentObj->getCommentList($fields,$condition,$limit);
	$total_record 	= $commentObj->getTotalRecordCount();
	require_once('../views/CommentList.php');
	if(1){
		?>
		<script>
				$("#comments_total_count").val('<?php echo $total_record;?>');	
				$('#comments_result_count').val('<?php echo $_GET['start']+5;?>');
				var total = $('#comments_total_count').val();
				var resultCount = ($('#comments_result_count').val())-1;
				if(resultCount >= total ||  (resultCount)+1 == total ) {
					$('#seeMoreCommentsLink').hide();
				}
		</script>
		<?php
	}
}
/*---------------Merchant Transaction list-------------------*/
if(isset($_GET['action']) && $_GET['action'] == 'GET_TRANSACTION_LIST'){
	require_once('../controllers/OrderController.php');
	$time_zone			= getTimeZone();
	$time_zone_val		= strval($time_zone);
	$curr_date 			= date('m/d/Y');
	$condition_week		= $condition_day	= $condition = '';
	$orderObj   		=   new OrderController();
	if(isset($_GET['merchantId']) && !empty($_GET['merchantId'])){
		$condition 		= " and o.fkMerchantsId 	= ".$_GET['merchantId'];
		$condition_week .= " and o.fkMerchantsId 	= ".$_GET['merchantId'];
		$condition_day 	.= " and o.fkMerchantsId 	= ".$_GET['merchantId'];
	}
	$fields 			= " ";
	$transactionTotal 	= $orderObj->getTotalRevenue($fields,$condition);
	
	$condition_week 	.= 	" and (DATE_FORMAT(OrderDate,'%Y-%m-%d') <= '".date('Y-m-d',strtotime($curr_date))."' and DATE_FORMAT(OrderDate,'%Y-%m-%d') > '".date('Y-m-d',strtotime("-7 days"))."')";
	$transactionWeekly 	= $orderObj->getTotalRevenue($fields,$condition_week);
	
	$condition_day		.= 	" and date(DATE_ADD(OrderDate,INTERVAL '".$time_zone_val."' HOUR_MINUTE))='".date('Y-m-d',strtotime($curr_date))."'";
	$transactiondays 	= $orderObj->getTotalRevenue($fields,$condition_day);
	
	require_once('../views/MerchantTransactionList.php');
}
/*---------------Next/Prev customer list-------------------*/
if(isset($_GET['action']) && $_GET['action'] == 'GET_CUSTOMERS_LIST'){
	require_once('../controllers/UserController.php');
	$userObj   		=   new UserController();
	$condition		= " ";
	$fields    		= " u.* ";
	if(isset($_GET['search']) && !empty($_GET['search'])){
		$_SESSION['customerSearch'] = $_GET['search'];
		$condition .= " and ( u.FirstName LIKE '%".$_GET['search']."%' || u.LastName LIKE '%".$_GET['search']."%' )";
		if(isset($_GET['type']) && $_GET['type'] == 0){
			$startValue		= 0;
		}else{
			$startValue	= $_GET['start']; }
			//$_SESSION['startlimit'] = $startValue;
	}else{ 
		//$_SESSION['startlimit'] = $_GET['start'];
		$startValue		= $_GET['start'];
	}
	$condition 		.= " and u.Status in (1,2)";
	$limit			= 	$startValue;
	$i = $_GET['start'];
	
	$userListResult = $userObj->getCustomerList($fields,$condition,$limit);
	$tot_rec 		= $userObj->getTotalRecordCount();
	require_once('../views/CustomerList.php');
	if(1){
		?>
		<script>
				if($('#customer_display_count').val() == '0'){
					$('#seePrevCustomers').hide();
				}
				$("#customer_total_count").val('<?php echo $tot_rec;?>');		
				$('#customer_display_count').val('<?php echo $startValue+5;?>');
				var total = $('#customer_total_count').val();
				var resultCount = ($('#customer_display_count').val())-1;
				if(resultCount >= total){ 
					$('#seeMoreCustomers').hide();
				}else if(total > resultCount){
					$('#seeMoreCustomers').show();
				}else if(total < resultCount){
					$('#seeMoreCustomers').hide();
					$('#seePrevCustomers').hide();
				}
				if($('#customer_display_count').val() == '5' || total == '0'){
					$('#seePrevCustomers').hide();
				}
		</script>
		<?php
	}
}
/*---------------Customer Transaction list-------------------*/
if(isset($_GET['action']) && $_GET['action'] == 'GET_CUSTOMER_TRANSACTION'){
	require_once('../controllers/UserController.php');
	$time_zone			= getTimeZone();
	$time_zone_val		= strval($time_zone);
	$curr_date 			= date('m/d/Y');
	$condition_week		= $condition_day	= $condition = $condition_details ='';
	$userObj   			=  new UserController();
	$fields    			= ", u.FirstName,u.LastName,u.UniqueId,u.Photo";
	$condition 			.= " and u.Status in (1,2) ";
	if(isset($_GET['customerId']) && !empty($_GET['customerId'])){
		$condition 		.= " and o.fkUsersId 		= ".$_GET['customerId'];
		$condition_week .= " and o.fkUsersId 		= ".$_GET['customerId'];
		$condition_day 	.= " and o.fkUsersId 		= ".$_GET['customerId'];
		$condition_details 	.= " and o.fkUsersId =".$_GET['customerId'];
	}
	$totalTransaction 	= $userObj->getCustomerTransaction($fields,$condition);
	
	$condition_week 	.= 	" and (DATE_FORMAT(OrderDate,'%Y-%m-%d') <= '".date('Y-m-d',strtotime($curr_date))."' and DATE_FORMAT(OrderDate,'%Y-%m-%d') > '".date('Y-m-d',strtotime("-7 days"))."')";
	$weeklyTransaction 	= $userObj->getCustomerTransaction($fields,$condition_week);
	
	$condition_day		.= 	" and date(DATE_ADD(OrderDate,INTERVAL '".$time_zone_val."' HOUR_MINUTE))='".date('Y-m-d',strtotime($curr_date))."'";
	$dayTransaction 	= $userObj->getCustomerTransaction($fields,$condition_day);
	
	$fields				= "sum(TotalPrice) as Total,o.OrderDate,m.FirstName,m.LastName,m.CompanyName";
	$transactionDetails	= $userObj->getTransactionDetails($fields,$condition_details);
	require_once('../views/CustomerTransactionList.php');
}

/*----------------customer list end---------*/
/*---------------Next/Prev Transaction list - transactions/merchant/customer-------------------*/
if(isset($_GET['action']) && $_GET['action'] == 'TRANSACTION_LIST'){
	require_once('../controllers/OrderController.php');
	$OrderObj   		=   new OrderController();
	$IdArray			= $CountArray = array();
	$condition			= '';
	if(isset($_GET['searchType']) && $_GET['searchType'] == 1){
		$fields    			= " o.TotalPrice,o.TransactionId,o.Status,o.TotalItems,o.OrderDate,m.CompanyName,u.FirstName,u.LastName,o.fkCartId,o.Commision  ";
	}else if(isset($_GET['searchType']) && $_GET['searchType'] == 2){
		$fields    				= " o1.TotalPrice,o1.TransactionId,o1.TotalItems,o1.OrderDate,o1.Status,o1.fkCartId,m.CompanyName,m.Icon,u.FirstName,u.LastName,o1.fkUsersId,o1.fkMerchantsId,o1.Commision";
		$cond					=	" o1.fkMerchantsId = o2.fkMerchantsId ";
		$_GET['displayType'] 	= 'Merchant';
	}else if(isset($_GET['searchType']) && $_GET['searchType'] == 3){
		$fields    				= " o1.TotalPrice,o1.TransactionId,o1.TotalItems,o1.OrderDate,o1.Status,o1.fkCartId,u.FirstName,u.LastName,u.Photo,m.CompanyName,m.Icon,o1.fkUsersId,o1.fkMerchantsId,o1.Commision";
		$cond					=	" o1.fkUsersId = o2.fkUsersId ";
		$_GET['displayType'] 	= 'Customer';
	}
	if(isset($_GET['search']) && $_GET['search']!=''){
		$_SESSION['transactionsearch'] = $_GET['search'];
			if($_GET['searchType'] == 3){
				$condition = " and ( u.FirstName LIKE '%".$_GET['search']."%' || u.LastName LIKE '%".$_GET['search']."%' )";
			}else {
				$condition = " and ( m.CompanyName LIKE '%".$_GET['search']."%')";
			}
		if(isset($_GET['type']) && $_GET['type'] == 0){
			$limit			= 0;
		}else {
			$limit 		= $_GET['start']; }
	}else{
		$limit			= $_GET['start'];
	}
	$leftjoin			= " left join users as u on  (u.id	= o.fkUsersId ) left join merchants as m on (m.id = o.fkMerchantsId)";
	//$condition 			.= " and o.Status in (1,2)";
	$i = $_GET['start'];
	$sort		=	" o.id desc ";
	if(isset($_GET['searchType']) && $_GET['searchType'] == 1){
		$OrderListResult  = $OrderObj->getOrderList($fields,$leftjoin,$condition,$sort,$limit);
		$_GET['displayType'] = 'Transaction';
	}else {
		$OrderListResult  = $OrderObj->MerchantTransactionList($fields,$cond,$condition,$limit);
		if(isset($OrderListResult) && $OrderListResult != ''){
			foreach($OrderListResult as $k=>$v)
			{
				if(isset($_GET['searchType']) && $_GET['searchType'] == 2){
					array_push($IdArray,$v->fkMerchantsId);
				}else if(isset($_GET['searchType']) && $_GET['searchType'] == 3){
					array_push($IdArray,$v->fkUsersId);
				}
			}
			//print_r($IdArray);
			//echo $ids	= implode(',',$IdArray);
			if(isset($_GET['searchType']) && $_GET['searchType'] == 2){
					$mer_field	= " fkMerchantsId as commId,count(id) as OrderCount ";
					$mer_cond	= " and fkMerchantsId in (".implode(',',$IdArray).")";
					$group		= " group by fkMerchantsId ";
			}else if(isset($_GET['searchType']) && $_GET['searchType'] == 3){
					$mer_field	= " fkUsersId as commId,count(id) as OrderCount ";
					$mer_cond	= " and fkUsersId in (".implode(',',$IdArray).")";
					$group		= " group by fkUsersId ";
			}
			$countResult		= $OrderObj->merchantCustomerList($mer_field,$mer_cond,$group);
			foreach($countResult as $k=>$v){
				$countArray[$v->commId] = $v->OrderCount;
			}
			//print_r($countResult);
			//print_r($countArray);
		}
	}
	$tot_rec 		 	= $OrderObj->getTotalRecordCount();
	require_once('../views/CommonTransactionList.php');
	if(1){
		?>
		<script>
				//alert(<?php echo $tot_rec;?>);
				if($('#'+'<?php echo $_GET['displayType']?>'+'_display_count').val() == '0'){
					$('#prevTransaction').hide();
				}
				$('#'+'<?php echo $_GET['displayType']?>'+'_total_count').val('<?php echo $tot_rec;?>');		
				$('#'+'<?php echo $_GET['displayType']?>'+'_display_count').val('<?php echo $_GET['start']+10;?>');
				var total 		= $('#'+'<?php echo $_GET['displayType']?>'+'_total_count').val();
				var resultCount = ($('#'+'<?php echo $_GET['displayType']?>'+'_display_count').val())-1;
				//alert('res'+resultCount);
				//alert('tot'+total);
				if(resultCount >= total){ 
					$('#nextTransaction').hide();
				}else if(total > resultCount){
					$('#nextTransaction').show();
				}else if(total < resultCount){
					$('#nextTransaction').hide();
					$('#prevTransaction').hide();
				}
				if($('#'+'<?php echo $_GET['displayType']?>'+'_display_count').val() == '10' || total == '0'){
					$('#prevTransaction').hide();
				}
		</script>
		<?php
	}
}

/*---------------See More Merchants/Customers(transaction list)-------------------*/
if(isset($_GET['action']) && $_GET['action'] == 'MERCHANT_CUSTOMER_TRANSACTION'){
	require_once('../controllers/OrderController.php');
	$OrderObj   =   new OrderController();
	$condition			= '';
	$fields    			= " o.TotalPrice,o.TransactionId,o.TotalItems,o.OrderDate,o.Status,o.fkCartId,m.CompanyName,u.FirstName,u.LastName,o.Commision";
	if(isset($_GET['start']) && $_GET['start']!= '' ){
		$limit			= $_GET['start'];
	}
	if(isset($_GET['idVal']) && $_GET['idVal']!= '' ){
		$searchId		= $_GET['idVal'];
	}
	if(isset($_GET['type']) && $_GET['type']== '2' ){
		$fields    		=  " o.TotalPrice,o.TransactionId,o.TotalItems,o.OrderDate,o.Status,o.fkCartId,u.FirstName,u.LastName,o.Commision";
		$condition		.= " and fkMerchantsId= ".$searchId;
		$leftjoin		= " left join users as u on  (u.id	= o.fkUsersId ) ";
		$displayCount	= 'Merchant';
	}else{
		$fields    		= " o.TotalPrice,o.TransactionId,o.TotalItems,o.OrderDate,o.Status,o.fkCartId,m.CompanyName,o.Commision";
		$condition		.= " and fkUsersId= ".$searchId;
		$leftjoin			= "left join merchants as m on (m.id = o.fkMerchantsId)";
		$displayCount	= 'Customer';
	}
	$sort				= 'o.OrderDate desc';
	//$leftjoin			= " left join users as u on  (u.id	= o.fkUsersId ) left join merchants as m on (m.id = o.fkMerchantsId)";
	$transactionList  	= $OrderObj->getOrderList($fields,$leftjoin,$condition,$sort,$limit);
	$tot_rec 			= $OrderObj->getTotalRecordCount();
	$i = $limit = $_GET['start'];
	require_once('../views/MerchantCustomerList.php');
	if(1){
		?>
		<script>
				$("#"+"<?php echo $displayCount;?>"+"_total_count_"+"<?php echo $_GET['idVal'];?>").val('<?php echo $tot_rec;?>');	
				$("#"+"<?php echo $displayCount;?>"+"_result_count_"+"<?php echo $_GET['idVal'];?>").val('<?php echo $_GET['start']+10;?>');
				var total = $("#"+"<?php echo $displayCount;?>"+"_total_count_"+"<?php echo $_GET['idVal'];?>").val();
				var resultCount = ($("#"+"<?php echo $displayCount;?>"+"_result_count_"+"<?php echo $_GET['idVal'];?>").val())-1;
				if(resultCount >= total ||  Number(resultCount)+1 == total ) {
					$('#showMore_'+'<?php echo $_GET['idVal'];?>').hide();
				}
		</script>
		<?php
	}
}

/*---------------Next/Prev Transaction list - transactions end-------------------*/
/*---------------TRANSACTION HISTORY-------------------*/
if(isset($_GET['action']) && $_GET['action'] == 'TRANSACTION_HISTORY'){
	require_once('../controllers/OrderController.php');
	$OrderObj   		=   new OrderController();
	$condition			= '';
	$fields    			= " o.TotalPrice,o.TransactionId,o.Status,o.OrderDate,m.CompanyName,u.FirstName,u.LastName,o.fkCartId,o.Commision ";
	if(isset($_GET['search']) && $_GET['search']!=''){
		$_SESSION['trans_hist_search'] = $_GET['search'];
		$condition .= $_GET['search'];
		if(isset($_GET['type']) && $_GET['type'] == 0){
			$limit			= 0;
		}else {
			$limit 		= $_GET['start']; }
	}else{
		$limit			= $_GET['start'];
	}
	$leftjoin			= " left join users as u on  (u.id	= o.fkUsersId ) left join merchants as m on (m.id = o.fkMerchantsId)";
	$i 					= $_GET['start'];
	$sort				= " o.id desc ";
	
	require_once('../views/TransactionHistoryDetails.php');
	if(1){
		?>
		<script>
				if($('#Trans_hist_display_count').val() == '0'){
					$('#prevTransHist').hide();
				}
				$('#Trans_hist_total_count').val('<?php echo $tot_rec;?>');		
				$('#Trans_hist_display_count').val('<?php echo $_GET['start']+10;?>');
				var total 		= $('#Trans_hist_total_count').val();
				var resultCount = ($('#Trans_hist_display_count').val())-1;
				if(resultCount >= total){ 
					$('#nextTransHist').hide();
				}else if(total > resultCount){
					$('#nextTransHist').show();
				}else if(total < resultCount){
					$('#nextTransHist').hide();
					$('#prevTransHist').hide();
				}
				if($('#Trans_hist_display_count').val() == '10' || total == '0'){
					$('#prevTransHist').hide();
				}
		</script>
		<?php
	}
}
if(isset($_POST['my_order']) && $_POST['my_order']!=''){
require_once('../controllers/UserController.php');
$userObj   =   new UserController();
		//print_r($_POST['my_order']);
	foreach(explode("&",$_POST['my_order']) as $key=>$value){
		$sort_element[] =explode("=",$value);
	}
	//print_r($sort_element);
	//die();	
	foreach($sort_element as $key=>$value){
		++$key;
		//echo $key;echo"===========>";print_r($value);
		//die();
		$photoUpdateString	= " `Order` = '" .$key. "' ";
		$condition 			= " `id` = '".$value[1]."'";
		$userObj->updateSliderDetails($photoUpdateString,$condition);		
	}
	
}
if(isset($_POST['action']) && $_POST['action']!='' && $_POST['action']=='Save_Image'){
require_once('../controllers/UserController.php');
	$userObj   =   new UserController();
	$fileElementName = $_POST['save_image_name'];
	$imgorder 		= $_POST['imgorder'];
	$image			= $_POST['image'];
	$fileOldName	= $_POST['old_img'];
	if(isset($fileElementName)){
		if(strstr($fileElementName,"Tutorial_Image"))
			$insert_id   		    = $userObj->insertTutorialSlide($imgorder);
		else if(strstr($fileElementName,"Slider_Image"))
			$insert_id   		    = $userObj->insertHomeSlide($imgorder);
		$userObj->updateSliderDetails('`Order` = "'.$imgorder.'"','id = "'.$insert_id.'"');
		$date_now = date('Y-m-d H:i:s');
		if(isset($insert_id) && $insert_id != '' )					{
			$imageName 				= $image;
			$imagetemp 				= TEMP_USER_IMAGE_PATH_REL.$fileElementName;
			$imagepath 				= UPLOAD_SLIDER_PATH_REL.$imageName;
			$oldSliderName			= $image;
			if ( !file_exists(UPLOAD_SLIDER_PATH_REL) ){
				mkdir (UPLOAD_SLIDER_PATH_REL, 0777);
			}
			copy($imagetemp,$imagepath);
			if (SERVER){
				if($oldSliderName!='') {
					if(image_exists(4,$oldSliderName)) {
						deleteImages(4,$oldSliderName);
					}
				}
				uploadImageToS3($imagepath,4,$imageName);
				unlink($imagepath);
			}
			$photoUpdateString	= " SliderImages = '" . $imageName . "'";
			unlink(TEMP_USER_IMAGE_PATH_REL.$fileElementName);
			if($photoUpdateString!=''){
				$condition 			= "id = ".$insert_id;
				$userObj->updateSliderDetails($photoUpdateString,$condition);
			}
		}
		$result['insertid'] = $insert_id;
		$result['url'] = SLIDER_IMAGE_PATH.$imageName;
		echo json_encode($result);
	}
}
if(isset($_GET['action']) && $_GET['action']!='' && $_GET['action']=='Delete_Image'){
require_once('../controllers/UserController.php');
	$userObj   =   new UserController();
	//$fileElementName = $_POST['delete_image_name'];
	//$imgorder 	= $_POST['image_order'];
	$image		= $_POST['image'];
	$msg = '';
	//echo $imgorder;echo"<br>";echo $image;
	if(isset($image)){
		$userObj->updateSliderDetails('`Status` = 2 ',' `id` = "'.$image.'" ');
		$imagepath 				= UPLOAD_SLIDER_PATH_REL.$image;
		if (SERVER){
			if($image!='') {
				if(image_exists(4,$image)) {
					deleteImages(4,$image);
				}
			}
		}
		if(file_exists($imagepath))
			unlink($imagepath);
		$msg	= "Deleted";
	}
	echo $msg;
}
if(isset($_GET['action']) && !empty($_GET['action']) && $_GET['action'] == 'Save-Content') {
	require_once('../controllers/ContentController.php');
	$contentObj   =   new ContentController();
	$_POST     		= 	unEscapeSpecialCharacters($_POST);
	$_POST     		= 	escapeSpecialCharacters($_POST);
	//echo "<pre>"; echo print_r($_POST['Content']); echo "</pre>";	
	//Edit Content
	$contentObj->updateContentDetail($_POST);	
}
if(isset($_GET['action']) && !empty($_GET['action']) && $_GET['action'] == 'APPROVE_MERCHANT') {
	require_once('../controllers/MerchantController.php');
	$managementObj   =   new MerchantController();
	if(isset($_POST['id']) && $_POST['id']!='')
		$managementObj->approveMerchant($_POST['id'],"1");
}

/*if(isset($_GET['action']) && !empty($_GET['action']) && $_GET['action'] == 'MAP_LOAD') {
	if(isset($_POST['merchant_id']) && $_POST['merchant_id']!=''){
		require_once('../controllers/MerchantController.php');
		$merchantObj   	=   new MerchantController();
		$condition       	= " id ='".$_POST['merchant_id']."' and  Status =1 and Latitude <>'' and Longitude <>'' order by CompanyName asc";
		$field				= ' Latitude,Longitude,CompanyName,Address';
		$result				= $merchantObj->selectMerchantDetails($field,$condition);
		//echo "this is merchant id".$_POST['merchant_id'];
	}else if(isset($_POST['category_id']) && $_POST['category_id']!=''){
		require_once('../controllers/MerchantController.php');
		$merchantObj   =   new MerchantController();
		$condition       	= " and c.id ='".$_POST['category_id']."' and  m.Status =1 and m.Latitude <> '' and m.Longitude <> ''";
		$field				= 'm.Latitude,m.Longitude,m.CompanyName,m.Address';
		$result				= $merchantObj->getMerchantList($field,$condition);
	}
	if(isset($result)){
		foreach($result as $key=>$value){
			$array['Latitude']=$value->Latitude;
			$array['Longitude']=$value->Longitude;
			$array['CompanyName']=$value->CompanyName;
			$array['Address']=$value->Address;
		}
	echo json_encode($array);
	}else{
		$array['Latitude']	 	= 13.082540019708;
		$array['Longitude'] 	= 80.271348980292	;
		$array['CompanyName'] 	= 'Testing';
		$array['Address']		= 'Chennai';
		echo json_encode($array);
	}
		
	
}*/
?>
