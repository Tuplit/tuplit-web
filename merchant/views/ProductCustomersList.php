<?php
require_once('includes/CommonIncludes.php');
if(isset($_POST['action']) && $_POST['action']=='GET_CUSTOMERS') { 
	if(isset($_POST['dataType']) && $_POST['dataType'] != '') {
		$date_type							=	$_POST['dataType'];
		$_SESSION['TuplitAnalyticsView']	=	$date_type;
	}
	if(isset($_POST['Starts']) && $_POST['Starts'] != '')
		$start			=	$_POST['Starts'];
	if(isset($_POST['SearchText']))
		$SearchText			=	$_POST['SearchText'];	
}
if(!isset($_SESSION['tuplit_ses_from_timeZone']) || $_SESSION['tuplit_ses_from_timeZone'] == ''){
	 $time_zone = getTimeZone();
	 $_SESSION['tuplit_ses_from_timeZone'] = strval($time_zone);	 
}
$start_count = $start;
$tot_rec	 = 0;
//getting customer list
$url					=	WEB_SERVICE.'v1/merchants/productcustomerlist/?DataType='.$date_type.'&Start='.$start.'&SearchText='.$SearchText.'&TimeZone='.$_SESSION['tuplit_ses_from_timeZone'].'';
//echo $url;
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
?>

<div class="col-sm-12 no-padding ">
		   <?php if(isset($customerList) && !empty($customerList)) { ?>
            <div class="box">
				<div class="box-body table-responsive no-padding no-margin">
					<table class="table table-hover customer_list">
						   <tr>
								<th align="center" width="3%" class="text-center">#</th>									
								<th width="10%" class="text-center"><img class="photo_img_border" src="<?php echo MERCHANT_IMAGE_PATH.'no-user.png'; ?>" width="20" height="20"></th>
								<th width="20%">Customer</th>
								<th width="10%">First Order</th>
								<th width="10%">Last Order</th>
								<th nowrap="wrap" width="5%" class="text-center">Total amount</br> by customer(&pound;)</th>
								<th nowrap="wrap" width="10%" class="text-center">Avg. transaction </br> for customer(&pound;)</th>
								<th nowrap="wrap" width="10%" class="text-center word-break">Number </br> of Transactions</th>
								<th nowrap="wrap" width="10%" class="text-center">Avg. days </br> between orders</th>
								<th width="10%" class="text-center">Comments</th>
							</tr>
						  <?php
						  	$count = 0;
							foreach($customerList as $key=>$value){
								$count += 1;
								$start_count += 1;	
								$imagepath 	=	$value['Photo'];
								if(empty($imagepath))
									$imagepath = MERCHANT_IMAGE_PATH.'no-user.png';
							?>
						<tr>
							<td align="center"><?php echo $start_count; ?></td>
							<td class="text-center">
								<?php if(isset($value["TotalOrders"]) && $value["TotalOrders"]!= '') { ?>
									<a href="UserOrders?viewId=<?php echo base64_encode($value["userId"]);?>&cs=1" class="showCustomer" title="View Orders" >
										<img class="photo_img_border" src="<?php echo $imagepath; ?>" width="35" height="35">
									</a>
								<?php } else { ?>
									<img class="photo_img_border" src="<?php echo $imagepath; ?>" width="35" height="35">
								<?php } ?>
							</td>
							<td>
								<div class="col-xs-10 col-md-11 no-padding"> 
									<?php if(isset($value["TotalOrders"]) && $value["TotalOrders"]!= '') { ?>
										<a href="UserOrders?viewId=<?php echo base64_encode($value["userId"]);?>&cs=1" class="showCustomer" title="View Orders" >
											<?php  echo  ucfirst($value["FirstName"]).' '.ucfirst($value["LastName"]);?>
										</a>
									<?php } else { ?>
										<?php  echo  ucfirst($value["FirstName"]).' '.ucfirst($value["LastName"]);?>
									<?php } ?>
								</div>
							</td>
							<td nowrap class="HelveticaNeueBold"><?php if(isset($value["FirstVisit"]) && $value["FirstVisit"] != '0000-00-00 00:00:00'){ echo date('j M Y',strtotime($value["FirstVisit"])); }else echo '-';?></td>
							<td nowrap class="HelveticaNeueBold"><?php if(isset($value["LastVisit"]) && $value["LastVisit"] != '0000-00-00 00:00:00'){ echo date('j M Y',strtotime($value["LastVisit"])); }else echo '-';?></td>
							<td align="center" class="HelveticaNeueBold"><?php if(isset($value["TotalPrice"]) && $value["TotalPrice"]!= ''){ echo number_format((float)$value["TotalPrice"],2,'.',',');}?></td>
							<td align="center" class="HelveticaNeueBold"><?php if(isset($value["AverageSpend"]) && $value["AverageSpend"]!= ''){ echo number_format((float)$value["AverageSpend"],2,'.',',');}?></td>
							<td align="center" class="HelveticaNeueBold"><?php if(isset($value["TotalOrders"]) && $value["TotalOrders"]!= ''){ echo $value["TotalOrders"];}?></td>
							<td align="center" class="HelveticaNeueBold"><?php echo $value["DayDifference"]; ?></td>
							
							<td width="5%" align="center" >
								<?php if(!empty($value["TotalComment"])) { ?>
								<a href="UserComments?viewId=<?php echo base64_encode($value["userId"]);?>&cs=1" class="newWindow showComments comments" 
									title="Comments"><?php echo $value["TotalComment"]; ?></a>
								<?php } else { ?>
									<a href="#" class="comments" 
									title="Comments">0</a>
								<?php } ?>
							</td>
						</tr>
						<?php } //end for ?>	
					   </table>
						<!-- End product List -->						 
					<?php } else { ?>
						<div class="col-sm-12 no-padding ">		
							 <div align="center" class="alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10"><i class="fa fa-warning"></i> <?php echo $errorMessage	;?>	</div>							
						</div>							
					<?php } ?>						
				</div><!-- /.box-body -->
			</div>					
		</div>	
	</div>
	<div class="customer_trans">
	<div class="col-xs-12 next-prev">
		<form method="post" action="ProductCustomer">
			<!--<input type="hidden" name="start" value="<?php echo $start; ?>"/> 
			<input type="hidden" name="totalcount" value="<?php echo $tot_rec; ?>"/>-->
			<?php if($start != 0) { ?> 
				<div id="prevTransaction"  class="col-xs-6 col-sm-6" align="center">	
					<input class="btn bg-olive btn-md prev_button" type="submit" name="Previous" id="Previous" value="Previous" onclick="return getProductCustomers('','',2);"/>
				</div>
			<?php } ?>
			<?php $startcheck = $start + 10; if($startcheck < $tot_rec) { ?> 
				<div id="nextTransaction"  class="col-xs-6 col-sm-6" align="center" style="float:right;">
						<input class="btn bg-olive btn-md next_button" type="submit" name="Next" id="Next" value="Next"  onclick="return getProductCustomers('','',3);"/>
				</div>
			<?php } ?>
		</form>
	</div>
	</div>
<script type="text/javascript">
		$(document).ready(function() {
			$('#totalcounter').val(<?php echo $tot_rec; ?>);
			$('#startcounter').val(<?php echo $start; ?>);
			$(".showComments").fancybox({
				scrolling: 'auto',			
				type: 'iframe',
				width: '800',
				maxWidth: '100%',
				//fitToView: false,
				title: null,				
			});
			$(".showCustomer").fancybox({
				scrolling: 'auto',			
				type: 'iframe',
				width: '500',
				maxWidth: '100%',
				fitToView: false,
				title: null,
			});				
		});
		
	</script>
