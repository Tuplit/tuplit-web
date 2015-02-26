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
$errorMessage 	= 'No Record Found';
$limit 	= $count = 0;
$cond	= $having = $left_join = '';
//$top	= array();
$class_count  = '5';
$image_path = $original_image_path = '';
if(isset($_GET['cs']) && $_GET['cs'] == 1){
	//unset($_SESSION['loc_mer_name']);
	//unset($_SESSION['loc_mer_price']);
	//unset($_SESSION['loc_mer_city']);
	//unset($_SESSION['loc_mer_category']);
}

if(isset($_POST['Search']) && $_POST['Search']!='' ){
	
	$_POST     		= 	unEscapeSpecialCharacters($_POST);
    $_POST     		= 	escapeSpecialCharacters($_POST);
	
	if(isset($_POST['Merchant_Name']) && $_POST['Merchant_Name']!=''){
		$_SESSION['loc_mer_name'] 	 = trim($_POST['Merchant_Name']);
		$cond						.= " and m.id = ".trim($_SESSION['loc_mer_name']);
	}else unset($_SESSION['loc_mer_name']);
	if(isset($_POST['Merchant_Price']) && $_POST['Merchant_Price']!=''){
		$_SESSION['loc_mer_price'] 	 = trim($_POST['Merchant_Price']);
		$having						.= " Having TotalPrice <= '".trim($_SESSION['loc_mer_price'])."'";
	}else unset($_SESSION['loc_mer_price']);
	if(isset($_POST['Merchant_city']) && $_POST['Merchant_city']!=''){
		$_SESSION['loc_mer_city'] 	 = $_POST['Merchant_city'];
		$cond						.= " and m.City LIKE '%".trim($_SESSION['loc_mer_city'])."%'";
	}else unset($_SESSION['loc_mer_city']);
	if(isset($_POST['Merchant_Category']) && $_POST['Merchant_Category']!=''){
		$_SESSION['loc_mer_category'] 	 = $_POST['Merchant_Category'];
		$cond							.= " and c.id = ".trim($_SESSION['loc_mer_category']);
		$left_join						 .=	'left join merchantcategories as mc on (m.id = mc.fkMerchantId) 
											left join categories as c on (c.id = mc.fkCategoriesId)';
	}else unset($_SESSION['loc_mer_category']);
	//echo $cond;
}
$fields 		= '';
$condition 		= '';
$type			= 1;
$analyticsList  = $analyticsObj->getCustomerReport($fields,$cond,$having,$limit,$type,$left_join);
$tot_rec 		= $analyticsObj->getTotalRecordCount();

?>
<body class="skin-blue">
<?php 	top_header();
		$formStatus = 3;
		require_once("ReportSearchBox.php");
?>
	
	<!-- Main content -->
	<section class="content Report-customer">
		<div class="row">
            <div class="col-xs-12">
			<div class="box box-primary">
				<div class="Category-manage sett-menu clear">
					<form action="" class="l_form" name="customerList" id="customerList"  method="post">
						<div id="cust_hist_List" class="custom_report">
						<?php 	
							$i=0 ; 
							$_GET['start'] = 0;
							if(isset($cond) && $cond!=''){
									$_GET['search']	= $cond;
							}
							if(isset($having) && $having != ''){
									$_GET['having'] = $having;
							}
							if(isset($left_join) && $left_join != ''){
									$_GET['left_join'] = $left_join;
							}
							 require_once('CustomerReportDetails.php'); 
						?>
						</div>	
						<div class="manage-transactions">
							<div class="col-xs-12 next-prev">
								<div id="prevCustHist"  class="col-xs-6 col-sm-6" align="center">	
									<a class="" href="javascript:void(0);" onclick="custHistTransaction('prev')"  title="Prev Page">Previous</a>
								</div>
							
								<div id="nextCustHist"  class="col-xs-6 col-sm-6" align="center" style="float:right;">
									<a class="" href="javascript:void(0);" onclick="custHistTransaction('next')"  title="Next Page">Next</a>
								</div>
							</div>
						</div>
						<input type="hidden" id="cust_hist_display_count" value="<?php echo count($analyticsList); ?>">
						<input type="hidden" id="cust_hist_total_count" value="<?php echo $tot_rec;  ?>">
						<input type="hidden" id="count" value="">
					</form>
				</div>
			</div>
			</div>	
		 </div>
	</section><!-- /.content -->	
<?php commonFooter(); ?>
<script type="text/javascript">
	$("#prevCustHist").hide();	
	if($('#cust_hist_total_count').val() == 0){
		$("#nextCustHist").hide();	
	}
	if($('#cust_hist_total_count').val() == $('#cust_hist_display_count').val()){	
		$("#prevCustHist").hide();
		$("#nextCustHist").hide();	
	}
	$('#cust_hist_search').keypress(function(event) {	
	    var keycode = (event.keyCode ? event.keyCode : event.which);
	    if(keycode == '13') {
			 custHistTransaction('search');		
		   return false;
	    }
	});

function custHistTransaction(direction)
{
	var countValue 		= $("#count").val();
	var	startVal 		= ''; 
	var searchVal		= '';
	var having			= '';
	var left_join		= '';
	if(direction == 'search'){
		$('#cust_hist_display_count').val('0');
	}
	var prevStart = $("#prevCount").val();
	if(direction == 'prev'){
		startVal = ($('#cust_hist_display_count').val())-20;
		if(startVal == '0' || prevStart == '0'){
			$('#prevCustHist').hide();
			$("#prevCount").val('0');
		}
	}else if(direction == 'next'){
		startVal 	= $('#cust_hist_display_count').val();
		$('#prevCustHist').show();
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
		having = '<?php if(isset($_SESSION['loc_mer_price']) && $_SESSION['loc_mer_price'] != '') echo " Having TotalPrice <= ".$_SESSION['loc_mer_price'] ?>';	
		$("#count").val('1');
	}
	if(search_city != ''){
		searchVal = searchVal+"<?php if(isset($_SESSION['loc_mer_city']) && $_SESSION['loc_mer_city'] != '') echo " and m.City LIKE '%".$_SESSION['loc_mer_city']."%'";?>";	
		$("#count").val('1');
	}
	if(search_category != ''){
		searchVal = searchVal+'<?php if(isset($_SESSION['loc_mer_category']) && $_SESSION['loc_mer_category'] != '') echo " and c.id = ".$_SESSION['loc_mer_category']." "; ?>';
		left_join = 'left join merchantcategories as mc on (m.id = mc.fkMerchantId)	left join categories as c on (c.id = mc.fkCategoriesId)';	
		$("#count").val('1');
	}
	//alert(searchVal);
	$.ajax({
	        type: "GET",
	        //url: actionPath+"models/AjaxAction.php",
	         url: actionPath+"CustomerReportDetails",
			 global: false,
			data: 'action=TRANSACTION_HISTORY&start='+startVal+'&search='+searchVal+'&type='+countValue+'&having='+having+'&left_join='+left_join,
	        success: function (result){
				$("#cust_hist_List").html(result);
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