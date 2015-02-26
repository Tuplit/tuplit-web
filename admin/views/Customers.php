<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
require_once('controllers/UserController.php');
$userObj   		=   new UserController();
$time_zone		= getTimeZone();
$time_zone_val	= strval($time_zone);
$curr_date 		= date('m/d/Y');
$condition		=  $condition_week = $condition_day = '';
if(isset($_GET['cs']) && $_GET['cs'] == 1){
	unset($_SESSION['customerSearch']);
}
//echo $_SESSION['customerSearch'];
/*-------Customer list----------*/
$limit			= 0;
$fields    		= " u.* ";
$condition 		= " and u.Status in (1,2)";
$userListResult = $userObj->getCustomerList($fields,$condition,$limit);
$tot_rec 		= $userObj->getTotalRecordCount();

/*-------Customes transaction----------*/
$fields    			= ", u.FirstName,u.LastName,u.UniqueId,u.Photo";
$condition 			= " and u.Status in (1,2) and o.fkUsersId =".$userListResult[0]->id;
$totalTransaction 	= $userObj->getCustomerTransaction($fields,$condition);  // total transaction

$condition_week 	.= 	" and o.fkUsersId =".$userListResult[0]->id." and (DATE_FORMAT(OrderDate,'%Y-%m-%d') <= '".date('Y-m-d',strtotime($curr_date))."' and DATE_FORMAT(OrderDate,'%Y-%m-%d') > '".date('Y-m-d',strtotime("-7 days"))."')";
$weeklyTransaction 	= $userObj->getCustomerTransaction($fields,$condition_week); // weekly transaction 
	
$condition_day		.= 	" and o.fkUsersId =".$userListResult[0]->id." and date(DATE_ADD(OrderDate,INTERVAL '".$time_zone_val."' HOUR_MINUTE))='".date('Y-m-d',strtotime($curr_date))."'";
$dayTransaction 	= $userObj->getCustomerTransaction($fields,$condition_day); // day transaction

$fields				= "sum(TotalPrice) as Total,o.OrderDate,m.FirstName,m.LastName,m.CompanyName";
$condition 			= " and o.fkUsersId =".$userListResult[0]->id;
$transactionDetails	= $userObj->getTransactionDetails($fields,$condition);
//echo $userListResult[0]->id;

if(isset($_GET['delId']) && $_GET['delId']!=''){
	$delete_id      = $_GET['delId'];
}

if(isset($delete_id) && $delete_id != ''){	
	$userObj->deleteUserReleatedEntries($delete_id);
	$field			 = " Photo ";
	$delete          = explode(',',$delete_id);
	if(isset($delete) && is_array($delete) && count($delete) >0 )
	{
		foreach($delete as $key=>$value){
			$unlink_comdition   = " id = ".$value;
			$userListResult     = $userObj->selectUserDetails($field,$unlink_comdition);
			if(isset($userListResult) && is_array($userListResult) && count($userListResult) > 0){
				if(isset($userListResult[0]->Photo) && $userListResult[0]->Photo != ''){
					$user_image = $userListResult[0]->Photo;	
					if(SERVER){
						deleteImages(1,$user_image);
						deleteImages(2,$user_image);
					}
					else{
						if(file_exists(USER_THUMB_IMAGE_PATH_REL . $user_image))
							unlink(USER_THUMB_IMAGE_PATH_REL . $user_image);
						if(file_exists(USER_IMAGE_PATH_REL . $user_image))
							unlink(USER_IMAGE_PATH_REL . $user_image);
					}
				}
			}
		}
	}
	//header("location:UserList?msg=3");	
	header("location:Customers?msg=3");
}


/*-------Customes success/error msg----------*/
if(isset($_GET['msg']) && $_GET['msg'] == 1){
	$msg 		= 	"Customer added successfully";
	$display	=	"block";
	$class 		= 	"alert-success";
	$class_icon = "fa-check";
}
else if(isset($_GET['msg']) && $_GET['msg'] == 2){
	$msg 		= 	"Customer updated successfully";
	$display	=	"block";
	$class 		= 	"alert-success";
	$class_icon = "fa-check";
}
else if(isset($_GET['msg']) && $_GET['msg'] == 3){
	$msg 		= 	"Customer deleted successfully";
	$display	=	"block";
	$class 		= 	"alert-success";
	$class_icon = "fa-check";
}

commonHead();
?>
<body class="skin-blue" onload="">
	<?php top_header(); ?>
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<div class="col-sm-5 col-xs-12 col-lg-5">
			<h1><i class="fa fa-list"></i>Tuplit Customers</h1>
		</div>
		<div class="col-sm-7 col-xs-12 col-lg-7 align">
			<h3 class="send_push"><a href="UserList?cs=1" id="sendPushNotification" title="Send Push Notification"><i class="fa fa-plus-circle"></i> SEND PUSH NOTIFICATION </a></h3>
			<h3><a href="CustomerManage" target="_blank" title="Add Customer"><i class="fa fa-plus-circle"></i> Add Customer</a></h3>
			<!--<div class="search-box col-sm-7 col-lg-7 no-padding align">
				<input type="text" placeholder="Search Customers" value="<?php if(!empty($_SESSION['customersearch'])) echo $_SESSION['customersearch']; ?>" name="customersearch" id="customersearch">
				<input type="submit" name="Search" value="Search" onclick="seeMoreCustomers('search')" class="search_icon" title="Search">  
			</div> -->
			<div class="search-box">
				<input type="text" placeholder="Search Customers" value="<?php if(isset($_SESSION['customerSearch']) && $_SESSION['customerSearch']!='') echo $_SESSION['customerSearch']; ?>" name="customersearch" id="customersearch">
				<input type="submit" name="Search" id="customer_search" value="Search" class="search_icon" title="Search"> 
			</div>
		</div>
	</section>	
	<!--CUSTOMERS LIST START-->
	 <!-- Main content -->
	<section class="content">
		<?php if(isset($msg) && $msg != '') { ?>
		 <div class="row">
               <div class="alert <?php  echo $class;  ?> alert-dismissable col-xs-11  col-sm-5  col-lg-3"><i class="fa fa-check"></i>  <?php echo $msg; ?></div>
		 </div>	
		<?php } ?>
		<!--CUSTOMERS IMAGES-->
		<div class="row">
            <div class="col-xs-12" id="customersList">
			   <div id="container">
			   <form action="Customers" class="l_form" name="Customers" id="Customers"  method="post">
				<?php if(isset($userListResult) && is_array($userListResult) && count($userListResult) > 0 ) { ?>
				 <div class="box padd-space">
							<div  id="sliderC">
								<ul>
						<?php 
						$i=0 ; ?>
						 <?php require_once('CustomerList.php');?>
					</ul>
				  </div>
				   <input type="hidden" id="customer_display_count" value="<?php echo count($userListResult); ?>">
				   <input type="hidden" id="customer_total_count" value="<?php echo $tot_rec;  ?>">
				   <input type="hidden" id="count" value="0">
				   <div id="customer_no_results" style="display:none;">
						<div class="alert alert-danger alert-dismissable col-sm-5 col-xs-11  merchant-margintop"><i class="fa fa-warning"></i> No Result Found</div> 
					</div>
				   <div>
					<input type="hidden" id="prevCount" value="0">
				  </div>
			   <?php } else { ?>	
					<div class="alert alert-danger alert-dismissable col-sm-5 col-xs-11"><i class="fa fa-warning"></i> No Customers found</div> 
				<?php } ?>	
			</form>
			
			</div><!--col-xs-12-->
		</div><!--row-->
		<!--CUSTOMERS IMAGES-->
		<div id="customer-details" style="<?php if(isset($_GET['status']) && $_GET['status'] == '0'){ echo 'display:none'; }?>">
		<div class="col-xs-12 col-sm-6"> 
				<div class="h1">Customer</div>
		</div>
		<div class="col-xs-12 col-sm-6 mtop text-right"> 
				<a class="add-button" href="javascript:void(0);" id="editCustomer" title="Edit Customer">EDIT CUSTOMER </a>&nbsp;&nbsp;
				<a class="cancel-button" href="javascript:void(0);" id="deleteCustomer" title="Suspend User">SUSPEND USERS</a>
		</div>
		<!--CUSTOMERS LIST END-->
		<!-- Transactions list start-->
			<div class="box transactions  col-xs-12">
				<div id="customerTransactions"><?php 
							$defaultCustomerId ='';
							if(is_array($userListResult)){
								$_GET['customerId'] = $defaultCustomerId = $userListResult[0]->id;
							}
				require_once('CustomerTransactionList.php'); ?></div>
			</div>
			<input type="hidden" id="customerId" value="<?php echo $defaultCustomerId;?>">
			<!-- Transactions list end-->
		</div><!-- container -->
		</div><!-- /.box -->
	</div><!--col-xs-12-->
</div><!--row-->
</section><!-- /.content -->	
<?php commonFooter(); ?>
<script src="<?php echo SITE_PATH;?>/admin/webresources/js/jquery.sudoSlider.js" type="text/javascript"></script>
<script src="<?php echo ADMIN_SCRIPT_PATH; ?>theme/plugins/morris/raphel.min.js" type="text/javascript"></script>
<script>
$(document).ready(function(){	
	var sudoSlider 	= callCustomerSlider('load');
    var searchVal	= $("#customersearch").val(); 
	if(searchVal != ''){
		searchCustomers('search');
		//return false;
	}
	 $('#customer_search').click(function() {
         searchCustomers('search');
         return false;
      });
	
	 $('#customersearch').keypress(function(event) {	
	    var keycode = (event.keyCode ? event.keyCode : event.which);
	    if(keycode == '13') {
			searchCustomers('search');
		   	return false;
	    }
	});
			
	$('.customerImage').click(function() {
		$('.slide').removeClass('select');
		$(this).closest("li").addClass("select");
	    var id_val =  $(this).attr('id');
		$("#customerId").val(id_val);
		getCustomerTransaction(id_val);
		return false;
	});
	$( ".customerImage" ).live( "click", function() {
		$('.slide').removeClass('select');
		$(this).closest("li").addClass("select");
	    var id_val =  $(this).attr('id');
		$("#customerId").val(id_val);
		getCustomerTransaction(id_val);
		return false;
	});
	
	function searchCustomers(type){
			$('#customer_no_results').hide();
			$('#customer-details').show();
			var search = $.trim($('#customersearch').val());
			$('#customer_display_count').val(0);
			var countValue = 1;
			if(type == 'search'){
				var dataVar = new Array();
				$.ajax({
			        type: "GET",
			        url: actionPath+"CustomerList",
			        data: 'action=GET_MORE_CUSTOMERS&start=0&search='+search+'&type='+countValue,
			        success: function (result){
						if($.trim(result) != 'fails'){
							var totalSlides = sudoSlider.getValue('totalSlides');
							$('#customer_display_count').val(30);
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
							$('#sliderC .slidesContainer').closest("li").addClass("select");
							var idVal = $('#current_slide').html();
							$('#current_slide').closest("li").addClass("select");
							$("#customerId").val(idVal);
							getCustomerTransaction(idVal);
						}
						else{
							var totalSlides = sudoSlider.getValue('totalSlides');
							for(var i=1;i<=totalSlides;i++){
								sudoSlider.removeSlide(i);
							}
							totalSlides = 0;
							$('#customer_no_results').show();
							$('#customer-details').hide();
						}
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
		} 
	
		$(window).on("resize focus load", function () {
			var width = $(window).width();
			var orgSlideCount = sudoSlider.getOption("slideCount");
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
			}
		}).resize();
});
</script>