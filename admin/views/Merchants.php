<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();

require_once('controllers/AdminController.php');
$adminLoginObj  =   new AdminController();
require_once('controllers/MerchantController.php');
$MerchantObj   	=   new MerchantController();
require_once('controllers/ManagementController.php');
$managementObj  =   new ManagementController();

require_once('controllers/CommentController.php');
$commentObj   	=   new CommentController();
require_once('controllers/OrderController.php');
$orderObj   	=   new OrderController();

$limit			= "0";
$curr_date 		= date('m/d/Y');
$condition 	= $condition_week	= $condition_day = '';
/*if(!isset($_SESSION['tuplit_ses_from_timeZone']) || $_SESSION['tuplit_ses_from_timeZone'] == ''){
	$time_zone 	= 	getTimeZone();
	$_SESSION['tuplit_ses_from_timeZone'] = strval($time_zone);
} else {
	$time_zone 	= 	$_SESSION['tuplit_ses_from_timeZone'];
}*/
$time_zone		= getTimeZone();
$time_zone_val	= strval($time_zone);
if(isset($_GET['cs']) && $_GET['cs']=='1') {
	destroyPagingControlsVariables();
	unset($_SESSION['startlimit']);
	unset($_SESSION['pdtStartLimit']);
	unset($_SESSION['merchantSearch']);
}
if(isset($_POST['Search']) && $_POST['Search'] != ''){
	destroyPagingControlsVariables();
	$_POST          = unEscapeSpecialCharacters($_POST);
    $_POST          = escapeSpecialCharacters($_POST);
	if(isset($_POST['merchantsearch'])){
		$_SESSION['merchantSearch'] = $_POST['merchantsearch'];
	}
}
/*-------Merchant List----------*/
setPagingControlValues('id',ADMIN_PER_PAGE_LIMIT);
$fields    = " m.* ";
$_SESSION['approve'] = 0;
if(isset($_GET['status']) && $_GET['status'] == '0'){
	$condition	= " and m.Status in (0)";
	$_SESSION['approve'] = 1;
	$del	=	1;
}
else{
	$condition	= " and m.Status in (0,1,2)";
	$del		= 2;
}

$merchantListResult  	= $MerchantObj->getMerchantImagesList($fields,$condition,'');
$tot_rec 		 		= $MerchantObj->getTotalRecordCount();
//echo $merchantListResult[0]->id;

if($tot_rec!=0 && !is_array($merchantListResult)) {
	$_SESSION['curpage'] = 1;
	$merchantListResult  = $MerchantObj->getMerchantList($fields,$condition);
}


/*-------Comments List----------*/
$fields 		= "com.*,u.FirstName,u.LastName,u.Photo";
$condition 		= ' and u.Status = 1 and com.Status = 1';
$commentlist 	= $commentObj->getCommentList($fields,$condition,$limit);
$total_record 	= $commentObj->getTotalRecordCount();

/*-------Transaction List----------*/
$fields				= "";
$condition_total	= "";
$condition_week 	.= 	" and (DATE_FORMAT(OrderDate,'%Y-%m-%d') <= '".date('Y-m-d',strtotime($curr_date))."' and DATE_FORMAT(OrderDate,'%Y-%m-%d') > '".date('Y-m-d',strtotime("-7 days"))."') ";
$condition_day		.= 	" and date(DATE_ADD(OrderDate,INTERVAL '".$time_zone_val."' HOUR_MINUTE))='".date('Y-m-d',strtotime($curr_date))."' ";
$transactionTotal 	= $orderObj->getTotalRevenue($fields,$condition_total);
$transactionWeekly 	= $orderObj->getTotalRevenue($fields,$condition_week);
$transactiondays 	= $orderObj->getTotalRevenue($fields,$condition_day);

/*-------Delete  Merchant----------*/
if(isset($_GET['delId']) && $_GET['delId']!=''){
	$delete_id  = $_GET['delId'];
}
if(isset($delete_id) && $delete_id != ''){
	$MerchantObj->deleteMerchantEntries($delete_id);
	$delete = explode(',',$delete_id);
	if(isset($delete) && is_array($delete) && count($delete) >0 )
	{
		foreach($delete as $key=>$value){
			$merchantListResult  = $MerchantObj->selectMerchantDetail($value);
			if(isset($merchantListResult) && is_array($merchantListResult) && count($merchantListResult) > 0){
				$mer_image = $merchantListResult[0]->Icon;
				$com_image = $merchantListResult[0]->Image;
				if(SERVER){
					if(image_exists(6,$mer_image))
						deleteImages(6,$mer_image);
					if(image_exists(7,$com_image))
						deleteImages(7,$com_image);
				}
				else{
					if(file_exists(MERCHANT_ICONS_IMAGE_PATH_REL.$mer_image))
						unlink(MERCHANT_ICONS_IMAGE_PATH_REL . $mer_image);
					if(file_exists(MERCHANT_IMAGE_PATH_REL.$com_image))
						unlink(MERCHANT_IMAGE_PATH_REL . $com_image);
				}
			}
		}
	}	//die();
	if(isset($_GET['redirect']) && $_GET['redirect']== '2'){
		header("location:Merchants?msg=3");
	}else if(isset($_GET['redirect']) && $_GET['redirect']== '1'){
		header("location:Merchants?cs=1&status=0&msg=3");
	}
}

/*-------Success msg for merchant add/edit----------*/
if(isset($_GET['msg']) && $_GET['msg'] == 1){
	$msg 		= 	"Merchant detail added successfully";
	$display	=	"block";
	$class 		= 	"alert-success";
	$class_icon = 	"fa-check";
}
else if(isset($_GET['msg']) && $_GET['msg'] == 2){
	$msg 		= 	"Merchant detail updated successfully";
	$display	=	"block";
	$class 		= 	"alert-success";
	$class_icon = "fa-check";
}
else if(isset($_GET['msg']) && $_GET['msg'] == 3){
	$msg 		= 	"Merchant deleted successfully";
	$display	=	"block";
	$class 		= 	"alert-success";
	$class_icon    = "fa-check";
} 


commonHead();
?>
<body class="skin-blue" onload="">
	<?php top_header(); ?>
	<!--<div id="loading">
  		<img id="loading-image" src="<?php echo ADMIN_IMAGE_PATH;?>/bx_loader.gif" alt="Loading..." />
	</div>-->
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<div class="col-sm-5 col-xs-12 col-lg-7">
			<h1>Merchant List</h1>
		</div>
		<div class="col-sm-7 col-xs-12 col-lg-5 header-margin align">
			<h3><a href="MerchantManage" target="_blank" title="Add Merchant"><i class="fa fa-plus-circle"></i> Add Merchant</a></h3>
			<div class="search-box">
				<input type="text" placeholder="Search" value="<?php if(!empty($_SESSION['merchantSearch'])) echo $_SESSION['merchantSearch']; ?>" name="merchantsearch" id="merchantsearch">
				<input type="submit" name="Search" id="merchant_search" value="Search" class="search_icon" title="Search">  
			</div>
		</div>
	</section>	
	 <!-- Main content -->
	<section class="content">
		<?php if(isset($msg) && $msg != '') { ?>
		 <div class="row">
               <div class="alert <?php  echo $class;  ?> alert-dismissable col-xs-11  col-sm-5  col-lg-3"><i class="fa fa-check"></i>  <?php echo $msg; ?></div>
		 </div>	
		<?php } ?>
		<div class="row">
            <div class="col-xs-12" id="myImages">
				<div id="container">
					<!--MERCHANTS IMAGES-->
				   <form action="Merchants" class="l_form " name="Merchants" id="Merchants"  method="post">
				  
					<?php if(isset($merchantListResult) && is_array($merchantListResult) && count($merchantListResult) > 0 ) { ?>
						<div class="box padd-space <?php if(isset($_GET['status']) && $_GET['status'] == 0) echo ''.'approve_merchant'; ?>">
							<div id="merchantImages" class="clear">
							<div  id="slider" class="products">
								<ul>
									<?php 
								$i=0 ; ?>
								 <?php require_once('MerchantsImages.php');?>
								</ul>
							</div>
							
							 
							   <div id="merchant_no_results" style="display:none;">
									<div class="alert alert-danger alert-dismissable col-sm-5 col-xs-11 merchant-margintop"><i class="fa fa-warning"></i> No Result Found</div> 
							   </div>
							</div>
								<input type="hidden" id="image_display_count" value="<?php echo count($merchantListResult); ?>">
								<input type="hidden" id="image_total_count" value="<?php echo $tot_rec;  ?>">
							
						</div>
				 
					   
				   <?php } else { ?>	
						<div class="alert alert-danger alert-dismissable col-sm-5 col-xs-11"><i class="fa fa-warning"></i> No Merchants found</div> 
					<?php } ?>	
				
				<?php if(isset($_GET['status']) && $_GET['status'] == '0' && $tot_rec > 0){ //cancel membership in approve merchant page?>
				<div class="col-xs-12 col-sm-12  col-md-6 col-lg-4"> 
				</div>
				<div class="col-xs-12 col-sm-12 col-md-6 col-lg-8 align align-center">
						<a class="add-button" href="javascript:void(0);" id="editMerchant" title="Edit Merchants">Edit Merchants </a>&nbsp;&nbsp;
						<a class="cancel-button" href="javascript:void(0);" id="deleteMerchant" title="Cancel Merchant Membership">Cancel Merchant Membership</a>
				</div>
				<?php } ?>
				<!--MERCHANTS IMAGES END-->
				<div id="merchant-details" style="<?php if(isset($_GET['status']) && $_GET['status'] == '0'){ echo 'display:none'; }?>">
					<div class="col-xs-12 col-sm-12  col-md-6 col-lg-4"> 
						<div class="h1">Merchant Details</div>
					</div>
					<div class="col-xs-12 col-sm-12 col-md-6 col-lg-8 align align-center"> 
						<a class="add-button" href="javascript:void(0);" id="editMerchant" title="Edit Merchants">Edit Merchants </a>&nbsp;&nbsp;
						<a class="cancel-button" href="javascript:void(0);" id="deleteMerchant" title="Cancel Merchant Membership">Cancel Merchant Membership</a>
					</div>
					<div class="box products merchants_list col-xs-12">
						<h2 class="col-xs-6  col-sm-6" style="padding-top: 4px;padding-bottom: 4px">Products</h2>
						<div class="search-box">
							<input type="text" style="border: 1px solid #ccc"	placeholder="Search" value="" name="searchproduct" id="searchproduct">
							<input type="submit" name="product_search" id="product_search" value="Search" class="search_icon" title="Search">  
						</div>
						<div id="productImages" class="clear">
								<div id="productSlider">
									<ul>
										<?php 
										$j=0 ;$defaultMerchantId ='';
										if(is_array($merchantListResult)){
											$_GET['merchantId'] = $defaultMerchantId = $merchantListResult[0]->id;
											$_GET['gettype']	=	1;
										}
										require_once('ProductImages.php'); ?>
									</ul>
								</div>
								<div style="display:none;" id="products_no_results" class="alert alert-danger alert-dismissable col-sm-5 col-xs-11"><i class="fa fa-warning"></i> No Products found</div> 
						</div>
						<input type="hidden" id="product_display_count" value="<?php echo count($productListResult); ?>">
						<input type="hidden" id="product_total_count" value="<?php echo $total_rec;  ?>">
						<input type="hidden" id="merchantId" value="<?php echo $defaultMerchantId;?>">
					</div>
					<!-- Transactions list start-->
					<div class="box transactions merchants_list col-xs-12">
						<h2>Transactions</h2>
						<div id="merchantTransactions">
							<?php 
							$defaultMerchantId ='';
							if(is_array($merchantListResult)){
								$_GET['merchantId'] = $defaultMerchantId = $merchantListResult[0]->id;
							}
							require_once('MerchantTransactionList.php'); ?>
						</div>
					</div>
					<!-- Transactions list end-->
					<!-- Comments list start -->	
					<div class="box comments col-xs-12 no-padding">
						<div class="  merchants_list col-xs-12" style="padding-bottom: 0;margin-bottom: 0">
							<h2>Comments for merchant</h2>
							<div id="commentsList">
								<?php $i=0;
								if(is_array($merchantListResult)){
									$_GET['merchantId'] = $defaultMerchantId = $merchantListResult[0]->id;
								}
								require_once('CommentList.php');?>
							</div>
						</div>
						<div class="clear"></div>
						<input type="hidden" id="comments_result_count" value="<?php echo $i;  ?>">
						<input type="hidden" id="comments_total_count" value="<?php echo $total_record;  ?>">
						<input type="hidden" id="delStatus" value="<?php echo $del;?>">
						<?php  
						if($i>2 && $i<$total_record){ ?>
							<div class="seemore-link" style="text-align:center;padding-bottom: 15px" id="seeMoreCommentsLink"><a href="javascript:void(0);" title="see more" onclick="seeMoreComments('2')" >See more</a></div>	
						<?php } ?>
					</div>
				</div><!-- #merchant-details -->
				</form>
			</div><!-- container -->
		</div><!-- /.box -->
	</div><!--col-xs-12-->
</div><!--row-->

<!-- <div id="sub" class="load-icon-bg"><div><i class="fa fa-spinner fa-spin fa-lg"></i></div></div>
<div class="fancybox-loading" style="display:none;">
<div class="loader">
   		<div style="padding-top:6px;"><i class="fa fa-spinner fa-spin fa-lg"></i></div>
       <!-- <img class="loading-image" src="<?php echo ADMIN_IMAGE_PATH?>bx_loader.gif" alt="loading.."> 
</div> -->
</div>

</section><!-- /.content -->	

<?php commonFooter(); ?>

<script src="<?php echo SITE_PATH;?>/admin/webresources/js/jquery.sudoSlider.js" type="text/javascript"></script>
<script src="<?php echo ADMIN_SCRIPT_PATH; ?>theme/plugins/morris/raphel.min.js" type="text/javascript"></script>

<script type="text/javascript">
/*function getMerchantItems1(merchantId){
	$("#merchantId").val(merchantId);
	getProductsList(merchantId);
	seeMoreComments('1');
	getTransactionList();
	loadTransactionChart();
}*/
//hide merchant details on search in approve page
/*alert($("#delStatus").val());
if($("#delStatus").val() == 1){
	$("#merchant-details").hide();
}*/

$(document).ready(function(){	
	 var sudoSlider 	= callSlider('load');
	 var productSlider  = callProductSlider('load');
	 // seeMoreComments('1');
	 //loadTransactionChart();
	 var searchVal	= $("#merchantsearch").val(); 
	if(searchVal != ''){
		searchMerchants('search',1);
		//return false;
	}
	$('#merchant_search').click(function() {
			var clearPrdt = $("#merchantsearch").val();
			if(clearPrdt == ''){
				$('#searchproduct').val('');
			}
		 searchMerchants('search',1);
         return false;
      });
	$('#merchantsearch').keypress(function(event) {	
	    var keycode = (event.keyCode ? event.keyCode : event.which);
	    if(keycode == '13') {
			var clearPrdt = $("#merchantsearch").val();
			if(clearPrdt == ''){
				$('#searchproduct').val('');
			}
			 searchMerchants('search',1);
	         return false;
	    }
	});	
	$('#product_search').click(function() {
		/*var id_val		=  	$('.slidesContainer li.select img').attr('id');
		var pro_search 	= 	$('#searchproduct').val();
		getProductsList(id_val,pro_search);*/
		searchMerchants('search',2);
         return false;
      });
	$('#productSearch').keypress(function(event) {	
	    var keycode = (event.keyCode ? event.keyCode : event.which);
	    if(keycode == '13') {
			searchMerchants('search',2);
	         return false;
	    }
	});	
	
	$('.merchantImage').click(function() {
		$('.slide').removeClass('select');
		$(this).closest("li").addClass("select");
	    var id_val =  $(this).attr('id');
		$("#merchantId").val(id_val);
		var pro_search = $('#searchproduct').val();		
		getProductsList(id_val,pro_search,1);
		//seeMoreComments('1');
		//getTransactionList(id_val);
		return false;
	});
	$( ".merchantImage" ).live( "click", function() {
		$('.slide').removeClass('select');
		$(this).closest("li").addClass("select");
	    var id_val		=  	$(this).attr('id');
		var pro_search 	= 	$('#searchproduct').val();
		getProductsList(id_val,pro_search,1);
		//alert('--');
		//seeMoreComments('1');
		//getTransactionList(id_val);
		return false;
	});
	//delStatus  = 1 --> merchant approve page
	//delStatus  = 2 --> merchants page
	function searchMerchants(type,searchtype){
			var redirect = $("#delStatus").val(); 
			$('#merchant_no_results').hide();
			if(redirect == 1){
				$('#merchant-details').hide();
			}else{
				$('#merchant-details').show();
			}
			var search 		= 	$.trim($('#merchantsearch').val());
			var	pro_search 	= 	$.trim($('#searchproduct').val());	
			$('#image_display_count').val(0);
			var countValue 	= 	1;
			if(type == 'search'){
				var dataVar = new Array();
				$.ajax({
			        type	: "GET",
			        url		: actionPath+"MerchantsImages",
			        global	: false,
					data	: 'action=GET_MORE_MERCHANTS&start=0&search='+search+'&type='+countValue+'&prosearch='+pro_search,
			        success	: function (result){
						//alert(result);
						if($.trim(result) != 'fails'){
							var totalSlides = sudoSlider.getValue('totalSlides');
							$('#image_display_count').val(30);
							for(var i=1;i<=totalSlides;i++){
								sudoSlider.removeSlide(i);
							}
							totalSlides = 0;
							var obj = jQuery.parseJSON(result);
							$.each(obj, function(i, objects) {
								sudoSlider.insertSlide(objects, totalSlides, '');
								totalSlides =  (+totalSlides) + (+1);
							});
							sudoSlider.init();
							$('#slider .slidesContainer').closest("li").addClass("select");
	   						/*var id_val =  $('#slider .slidesContainer').closest("li").attr('id');
							alert('--------id_val-------------'+id_val);*/
							var idVal = $('#current_slide').html();
							$('#current_slide').closest("li").addClass("select");
							$("#merchantId").val(idVal);							
							getProductsList(idVal,pro_search,searchtype);
							//seeMoreComments('1');
							//getTransactionList(idVal);
						}
						else{
							var totalSlides = sudoSlider.getValue('totalSlides');
							for(var i=1;i<=totalSlides;i++){
								sudoSlider.removeSlide(i);
							}
							totalSlides = 0;
							$('#merchant_no_results').show();
							$('#merchant-details').hide();
							$('#merchantId').val('0');
						}
			        },
					beforeSend: function(){
						// Code to display spinner
						$('.loader-merchant').show();
					},
					complete: function(){
						// Code to hide spinner.
						var idValue	=	$('#merchantId').val();
						if(idValue != 0){
							//getTransactionList(idValue);
						}else{	
							$('.loader-merchant').hide();
						}
					}	
			    });
				
			}
		} 
		function getProductsList(merchantId,pro_search,gettype){
			$("#merchantId").val(merchantId);
			$('#products_no_results').hide();
			$('#products_display_count').val(0);
			var countValue = 1;
			var dataVar = new Array();
			var mersearch 		= 	$('#merchantsearch').val();
			$.ajax({
		        type: "GET",
		        url: actionPath+"ProductImages",
				global: false,
		        data: 'action=GET_MORE_PRODUCTS&start=0&merchantId='+merchantId+'&prosearch='+pro_search+'&gettype='+gettype+'&mersearch='+mersearch,
		        success: function (result){
					if($.trim(result) != 'fails'){						
						var totalSlides = productSlider.getValue('totalSlides');
						$('#product_display_count').val(30);
						for(var i=1;i<=totalSlides;i++){
							productSlider.removeSlide(i);
						}
						totalSlides = 0;
						var obj = jQuery.parseJSON(result);
						$.each(obj, function(i, objects) {
							productSlider.insertSlide(objects, totalSlides, '');
							totalSlides =  (+totalSlides) + (+1);
						});
						productSlider.init();
						
					}
					else{
						var totalSlides = productSlider.getValue('totalSlides');
						for(var i=1;i<=totalSlides;i++){
							productSlider.removeSlide(i);
						}
						totalSlides = 0;
						$('#products_no_results').show();
						
					}
					
		        },
				beforeSend: function(){
					// Code to display spinner
					$('.loader-merchant').show();
				},
				complete: function(){
					getTransactionList(merchantId);
				}		
		    });
			//return false; 
		}
		
 		$(window).on("resize focus load", function () {
			var width = $(window).width();
			var orgSlideCount = sudoSlider.getOption("slideCount");
			var prodSlideCount = productSlider.getOption("slideCount");
			var slideCount;
			if (width >= 1200) {
				slideCount = 6;
			} else if (width > 900) {
				slideCount = 5;
			} else if (width > 640) {
				slideCount = 3;
			}else if (width > 360) {
				slideCount = 2;
			} else {
				slideCount = 1;
			}
			if (slideCount != orgSlideCount) {
				sudoSlider.setOption("slideCount", slideCount);
				sudoSlider.setOption("moveCount", slideCount);
				productSlider.setOption("slideCount", slideCount);
				productSlider.setOption("moveCount", slideCount);
			}
		}).resize();
  });
	
/*$(document).ready(function() {
	$('.fancybox').fancybox();	
	 $("table#myTable tr").not(':first').hover(
	     function () {
	      jQuery(this).find("div.row-actions a").css("visibility","visible");
	     }, 
	     function () {
	      jQuery(this).find("div.row-actions a").css("visibility","hidden");
	     }
	  );
});*/

/*$("#myImages").on('click','.merchantImage',function(){
		$("#slider").find("li").removeClass("select");
		$(".merchantImage").removeClass("merImage");
		$(this).addClass("merImage");
		$(this).closest("li").addClass("select");
});	*/
		
</script>
</html>
