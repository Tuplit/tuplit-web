<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
commonHead();
require_once('controllers/OrderController.php');
$OrderObj   	=   new OrderController();
require_once('controllers/MerchantController.php');
$merchantObj   	=   new MerchantController();
require_once('controllers/ManagementController.php');
$managementObj  =   new ManagementController();
$searchCond		=	$cond 	= $search_merchant = '';
$search_city 	=  $search_category = $search_price = '';
if(isset($_GET['cs']) && $_GET['cs'] == 1){
	unset($_SESSION['loc_mer_name']);
	unset($_SESSION['loc_mer_price']);
	unset($_SESSION['loc_mer_city']);
	unset($_SESSION['loc_mer_category']);
}
if(isset($_POST['Search'])){
	if(isset($_POST['Merchant_Name']) && $_POST['Merchant_Name']!=''){
		$_SESSION['loc_mer_name'] 	 = $_POST['Merchant_Name'];
		$cond						.= " and m.id = ".trim($_SESSION['loc_mer_name']);
	}else unset($_SESSION['loc_mer_name']);
	if(isset($_POST['Merchant_Price']) && $_POST['Merchant_Price']!=''){
		$_SESSION['loc_mer_price'] 	 = $_POST['Merchant_Price'];
		//$cond						.= " and o.TotalPrice = ".trim($_SESSION['loc_mer_price']);
		$cond 						.= " and ".$_SESSION['loc_mer_price']." between substring_index(m.PriceRange,',',1) and substring_index(m.PriceRange,',',-1)"; 
	}else unset($_SESSION['loc_mer_price']);
	if(isset($_POST['Merchant_city']) && $_POST['Merchant_city']!=''){
		$_SESSION['loc_mer_city'] 	 = $_POST['Merchant_city'];
		$cond						.= " and m.City LIKE '%".trim($_SESSION['loc_mer_city'])."%'";
	}else unset($_SESSION['loc_mer_city']);
	if(isset($_POST['Merchant_Category']) && $_POST['Merchant_Category']!=''){
		$_SESSION['loc_mer_category'] 	 = $_POST['Merchant_Category'];
		$cond							.= " and c.id = ".trim($_SESSION['loc_mer_category']);
	}else unset($_SESSION['loc_mer_category']);
}

/*-----------------Common Transaction List---------------------*/
$limit				= 0;
$fields    			= " o.TotalPrice,o.TransactionId,o.OrderDate,o.Status,m.CompanyName,u.FirstName,u.LastName,o.Commision ";
//$cond 				.= " and o.Status in (1,2)";
$sort				= " o.id desc ";
$leftjoin			= " left join users as u on  (u.id	= o.fkUsersId ) left join merchants as m on (m.id = o.fkMerchantsId)";
if(isset($_SESSION['loc_mer_category']) && $_SESSION['loc_mer_category']!=''){
	$leftjoin		.= "left join merchantcategories as mc on (m.id = mc.fkMerchantId) left join categories as c on (c.id = mc.fkCategoriesId)";
}
$type				= 1;
$OrderListResult  	= $OrderObj->getTransactionDetails($fields,$leftjoin,$cond,$sort,$limit,$type);
$tot_rec 		 	= $OrderObj->getTotalRecordCount();
?>
<body class="skin-blue" onload="">
<?php top_header(); ?>
<?php 
	$formStatus = 2;
	require_once("ReportSearchBox.php");
?>
	
	<!-- Main content -->
	<section class="content Report-Tranhis">
		<div class="row">
		<!-- left column -->
			<div class="col-md-12 manage-transactions"><!--  col-lg-6 -->
				
				<div class="box box-primary">
				<!-- TRANSACTIONS LIST -->
				<div class="Category-manage sett-menu clear">
					<form action="OrderList" class="l_form" name="OrderListForm" id="OrderListForm"  method="post">
						<div id="trans_hist_List" class="transaction_list">
						<?php 	
							$i=0 ; 
							$_GET['start'] = 0;
							require_once("TransactionHistoryDetails.php");
							?>
						</div>	
						<div class="col-xs-12 next-prev">
							<div id="prevTransHist"  class="col-xs-6 col-sm-6" align="center">	
								<a class="" href="javascript:void(0);" onclick="transHistTransaction('prev')"  title="Prev Page">Back</a>
							</div>
						
							<div id="nextTransHist"  class="col-xs-6 col-sm-6" align="center">
								<a class="" href="javascript:void(0);" onclick="transHistTransaction('next')"  title="Next Page">Next</a>
							</div>
						</div>
						<input type="hidden" id="Trans_hist_display_count" value="<?php echo count($OrderListResult); ?>">
						<input type="hidden" id="Trans_hist_total_count" value="<?php echo $tot_rec;  ?>">
						<input type="hidden" id="count" value="">
					</form>
				</div>
		<!-- CATEGORY LIST END -->
		</div><!-- /.box -->
	</div>
</div><!-- /.row -->
<div class="loader-merchant"><!--loader-->
	<div class="mercha-loader">
		<!-- <img  src="<?php // echo ADMIN_IMAGE_PATH?>bx_loader.gif" alt="loading.."> -->
		<i class="fa fa-spinner fa-spin fa-lg"></i>

	</div>
</div>
</section><!-- /.content -->
<?php commonFooter(); ?>
<style>
.fa fa-spinner fa-spin fa-lg
.fa-spin,.small-box,.photo_load  {display: none;}
.fancybox-loading,#fancybox-loading div{display: none;}
#fancybox-loading{display: none;}
.mercha-loader{
	 background-color: #000;
	left: 50%;
    margin-left: -22px;
    margin-top: -22px;
    position: fixed;
    text-align: center;
    top: 50%;
    z-index: 0;
	padding: 15px;
	border-radius: 5px
}
.loader-merchant{    
	background-color: rgba(0, 0, 0, 0.5);
	left: 0;
    position: fixed;
    top: 0;
    z-index: 0;
	display: none;
	width: 100%;
	height: 100%;
	font-size:15px;
	color:#ccc;

}

</style>
<script type="text/javascript">
	$("#prevTransHist").hide();	
	if($('#Trans_hist_total_count').val() == 0){
		$("#nextTransHist").hide();	
	}
	if($('#Trans_hist_total_count').val() == $('#Trans_hist_display_count').val()){	
		$("#prevTransHist").hide();
		$("#nextTransHist").hide();	
	}
	$('#trans_hist_search').keypress(function(event) {	
	    var keycode = (event.keyCode ? event.keyCode : event.which);
	    if(keycode == '13') {
			 transHistTransaction('search');		
		   return false;
	    }
	});

function transHistTransaction(direction)
{
	var countValue 		= $("#count").val();
	var	startVal 		= ''; 
	var searchVal		= '';
	
	if(direction == 'search'){
		$('#Trans_hist_display_count').val('0');
	}
	var prevStart = $("#prevCount").val();
	if(direction == 'prev'){
		startVal = ($('#Trans_hist_display_count').val())-20;
		if(startVal == '0' || prevStart == '0'){
			$('#prevTransHist').hide();
			$("#prevCount").val('0');
		}
	}else if(direction == 'next'){
		startVal 	= $('#Trans_hist_display_count').val();
		$('#prevTransHist').show();
		$("#prevCount").val('1');
	}
	search_merchant		= '<?php if(isset($_SESSION['loc_mer_name']) && $_SESSION['loc_mer_name'] != '') echo $_SESSION['loc_mer_name'];?>';
	search_price		= '<?php if(isset($_SESSION['loc_mer_price']) && $_SESSION['loc_mer_price'] != '') echo $_SESSION['loc_mer_price'];?>';
	search_city			= '<?php if(isset($_SESSION['loc_mer_city']) && $_SESSION['loc_mer_city'] != '') echo $_SESSION['loc_mer_city'];?>';
	search_category		= '<?php if(isset($_SESSION['loc_mer_category']) && $_SESSION['loc_mer_category'] != '') echo $_SESSION['loc_mer_category'];?>';
	if(search_merchant != ''){
		searchVal = searchVal+'<?php if(isset($_SESSION['loc_mer_name']) && $_SESSION['loc_mer_name'] != '') echo " and m.id=".$_SESSION['loc_mer_name'];  ?>';
		$("#count").val('1');
	}
	if(search_price != ''){
		searchVal = searchVal+'<?php if(isset($_SESSION['loc_mer_price']) && $_SESSION['loc_mer_price'] != '') " and ".$_SESSION['loc_mer_price']." between substring_index(m.PriceRange,',',1) and substring_index(m.PriceRange,',',-1)" //echo " and o.TotalPrice=".$_SESSION['loc_mer_price']; ?>';	
		$("#count").val('1');
	}
	if(search_city != ''){
		searchVal = searchVal+"<?php if(isset($_SESSION['loc_mer_city']) && $_SESSION['loc_mer_city'] != '') echo " and m.City LIKE '%".$_SESSION['loc_mer_city']."%'";?>";	
		$("#count").val('1');
	}
	if(search_category != ''){
		searchVal = searchVal+'<?php if(isset($_SESSION['loc_mer_category']) && $_SESSION['loc_mer_category'] != '') echo " and c.id = ".$_SESSION['loc_mer_category']." "; ?>';	
		$("#count").val('1');
	}
	//alert(searchVal);
	$.ajax({
	        type: "GET",
	        //url: actionPath+"models/AjaxAction.php",
	        url: actionPath+"TransactionHistoryDetails",
			data: 'action=TRANSACTION_HISTORY&start='+startVal+'&search='+searchVal+'&type='+countValue,
	        success: function (result){
				$("#trans_hist_List").html(result);
	        }, 
			beforeSend: function(){
				// Code to display spinner
				$('.loader-merchant').show();
			},
			complete: function(){
			// Code to hide spinner.
				$('.loader-merchant').hide();
			}			
	    });
}

</script>
</html>