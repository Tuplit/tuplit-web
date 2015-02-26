<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
commonHead();
require_once('controllers/OrderController.php');
$OrderObj   =   new OrderController();
$searchCond	=	'';
if(isset($_GET['cs']) && $_GET['cs'] == '1'){
	unset($_SESSION['transactionsearch']);
}
/*-----------------Common Transaction List---------------------*/
$limit				= 0;
$fields    			= " o.OrderStatus,o.TotalPrice,o.TransactionId,o.TotalItems,o.OrderDate,o.Status,m.CompanyName,u.FirstName,u.LastName,o.fkCartId,o.Commision ";
$condition 			= " and o.Status in (1,2) and u.Status = 1 and m.Status = 1";
$sort				= " o.id desc ";
$leftjoin			= " left join users as u on  (u.id	= o.fkUsersId ) left join merchants as m on (m.id = o.fkMerchantsId)";
$OrderListResult  	= $OrderObj->getOrderList($fields,$leftjoin,$condition,$sort,$limit);
$tot_rec 		 	= $OrderObj->getTotalRecordCount();

/*-----------------Merchant/Customer Transaction List---------------------*/
$limit				= 0;
$fields    			= " o1.TotalPrice,o1.TransactionId,o1.TotalItems,o1.OrderDate,o1.Status,o1.fkCartId,m.CompanyName,m.Icon,o1.fkUsersId,o1.fkMerchantsId,o1.Commision";
$condition_mer 		= " o1.fkMerchantsId = o2.fkMerchantsId ";
$Transaction_mer  	= $OrderObj->MerchantTransactionList($fields,$condition_mer,$searchCond,$limit);
$tot_rec_mer 		= $OrderObj->getTotalRecordCount();
$fields    			= " o1.TotalPrice,o1.TransactionId,o1.TotalItems,o1.OrderDate,o1.Status,o1.fkCartId,u.FirstName,u.LastName,o1.fkUsersId,o1.fkMerchantsId,o1.Commision ";
$condition_cust 	= " o1.fkUsersId = o2.fkUsersId ";
$Transaction_cust  	= $OrderObj->MerchantTransactionList($fields,$condition_cust,$searchCond,$limit);
$tot_rec_cust 		= $OrderObj->getTotalRecordCount();
?>
<body class="skin-blue" onload="">
	<?php top_header(); ?>
	<!-- Content Header (Page header) -->
	<section class="content-header no-padding">
		<div class="col-sm-6 col-xs-12">
			<h1><i class="fa fa-list"></i>Transactions</h1>
		</div>
		<div class="col-sm-6 col-xs-12">
			<div class="search-box" align="right">
				<input type="text" placeholder="Search Merchant" value="<?php if(!empty($_SESSION['transactionsearch'])) echo $_SESSION['transactionsearch']; ?>" name="customersearch" id="transactionsearch">
				<input type="submit" name="Search" value="Search" onclick="prevNextTransaction('search')" class="search_icon" title="Search">  
			</div>
		</div>
	</section>	
	<!-- Main content -->
	<section class="content">
		<div class="row">
		<!-- left column -->
			<div class="col-md-12 manage-transactions"><!--  col-lg-6 -->
				
				<div class="box box-primary">
				<div  class="col-xs-12 topbutton"> 
					<a class="trans-button mR-button" href="javascript:void(0);"  id="Merchant_transaction_button" title="Merchant">MERCHANT</a>
					<a class="trans-button" href="javascript:void(0);"  id="Customer_transaction_button" title="Customer">CUSTOMER</a>
				</div>	
				<!-- TRANSACTIONS LIST -->
				<div class="Category-manage sett-menu clear">
					<form action="OrderList" class="l_form" name="OrderListForm" id="OrderListForm"  method="post">
						<div id="transaction_List">
						<?php 	
							$i=0 ; 
							require_once("CommonTransactionList.php");
							?>
						</div>	
						<div class="col-xs-12 next-prev">
							<div id="prevTransaction"  class="col-xs-6 col-sm-6" align="center">	
								<a class="" href="javascript:void(0);" onclick="prevNextTransaction('prev')"  title="PREVIOUS">PREVIOUS</a>
							</div>
						
							<div id="nextTransaction"  class="col-xs-6 col-sm-6" align="center" style="float:right;">
								<a class="" href="javascript:void(0);" onclick="prevNextTransaction('next')"  title="NEXT">NEXT</a>
							</div>
						</div>
						<input type="hidden" id="Transaction_display_count" value="<?php echo count($OrderListResult); ?>">
						<input type="hidden" id="Transaction_total_count" value="<?php echo $tot_rec;  ?>">
						
						<input type="hidden" id="Merchant_display_count" value="<?php echo count($Transaction_mer); ?>">
						<input type="hidden" id="Merchant_total_count" value="<?php echo $tot_rec_mer;  ?>">
						
						<input type="hidden" id="Customer_display_count" value="<?php echo count($Transaction_cust); ?>">
						<input type="hidden" id="Customer_total_count" value="<?php echo $tot_rec_cust;  ?>">
						
						<input type="hidden" id="define_search_type" value="1">
						<input type="hidden" id="count" value="">
					</form>
				</div>
		<!-- CATEGORY LIST END -->
		</div><!-- /.box -->
	</div>
</div><!-- /.row -->
</section><!-- /.content -->
<?php commonFooter(); ?>
<style>
	.focusButton{color:#01A99A;outline:0;text-decoration:none}
	.loader-merchant{    
	background-color: rgba(0, 0, 0, 0.3);
	left: 0;
    position: fixed;
    top: 0;
    z-index: 1000;
	display: none;
	width: 100%;
	height: 100%;
	font-size:15px;
	color:#ccc;
}
</style>
<script type="text/javascript">
	function showLoaderPopup(){
		$('.loader-merchant').show();
	}
	$("#prevTransaction").hide();	
	$('#transactionsearch').keypress(function(event) {	
	    var keycode = (event.keyCode ? event.keyCode : event.which);
	    if(keycode == '13') {
			 prevNextTransaction('search');		
		   return false;
	    }
	});
	/*$(document).ready(function() {
		

	});
*/
$("#Merchant_transaction_button").click(function(){
	$('#define_search_type').val('2');
	$('#transactionsearch').attr('placeholder','Search Merchant');
	prevNextTransaction('search');
});
$("#Customer_transaction_button").click(function(){
	$('#define_search_type').val('3');
	$('#transactionsearch').attr('placeholder','Search Customer');
	prevNextTransaction('search');
});
// searchType -->1- transactions, 2 -  merchant, 3 - customer
function prevNextTransaction(direction)
{
	//alert('----');
	var searchType		= $('#define_search_type').val();
	var countValue = $("#count").val();
	var	startVal = ''; 
	var searchVal	= " ";
	var displayType		= '';
	if(searchType == 1){
		displayType	= 'Transaction';
	}else if(searchType == 2){
		displayType	= 'Merchant';
		$("#Merchant_transaction_button").addClass('focusButton');
		$("#Customer_transaction_button").removeClass('focusButton');
	}else if(searchType == 3){
		displayType	= 'Customer';
		$("#Customer_transaction_button").addClass('focusButton');
		$("#Merchant_transaction_button").removeClass('focusButton');
	}
	//alert(displayType);
	if(direction == 'search'){
		$('#'+displayType+'_display_count').val('0');
	}
	var prevStart = $("#prevCount").val();
	if(direction == 'prev'){
		startVal = ($('#'+displayType+'_display_count').val())-20;
		if(startVal == '0' || prevStart == '0'){
			$('#prevTransaction').hide();
			$("#prevCount").val('0');
		}
	}else if(direction == 'next'){
		startVal 	= $('#'+displayType+'_display_count').val();
		$('#prevTransaction').show();
		$("#prevCount").val('1');
	}
	if($("#transactionsearch").val() != ''){	
		searchVal 	= $.trim($("#transactionsearch").val());
		$("#count").val('1');
	}else{
		searchVal	= ""; 
	}
	//alert(startVal);
	$.ajax({
	        type: "GET",
	        url: actionPath+"models/AjaxAction.php",
	        data: 'action=TRANSACTION_LIST&start='+startVal+'&search='+searchVal+'&type='+countValue+'&searchType='+searchType,
	        success: function (result){
				//alert(result);
				$("#transaction_List").html(result);
	        },
			beforeSend: function(){
				// Code to display spinner
				$('.loader').show();
			},
			complete: function(){
			// Code to hide spinner.
				$('.loader').hide();
			}				
	    });
}

</script>
</html>