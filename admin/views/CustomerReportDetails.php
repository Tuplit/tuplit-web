<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
require_once('controllers/AnalyticsController.php');
$analyticsObj   =   new AnalyticsController();
$errorMessage 	= 'No Record Found';
$fields 		= '';
$condition 		= $having = $left_join = '';
if(isset($_GET['search']) && $_GET['search']!=''){
		$condition .= $_GET['search'];
}
if(isset($_GET['having']) && $_GET['having'] != ''){
		$having		= $_GET['having'];
}
if(isset($_GET['left_join']) && $_GET['left_join'] != ''){
		$left_join		= $_GET['left_join'];
}
$i 				= $_GET['start'];
$limit 			= $_GET['start'];
$type 			= (!empty($type)?$type:'1');
$analyticsList  = $analyticsObj->getCustomerReport($fields,$condition,$having,$limit,$type,$left_join);
$tot_rec 		= $analyticsObj->getTotalRecordCount();
?>
 <div class="box ">
 	<div class="box-body table-responsive no-padding no-margin">
		<table class="table table-hover">
         	<tr>
				<th align="center"  class="text-center">#</th>
				<th  align="center"><img width="36" height="36" style="border-radius:36px;" src="<?php echo ADMIN_IMAGE_PATH?>no-images.jpg"></th>
				<th  align="left">Customer</th>
				<th>First Order</th>
				<th>Last Order</th>
				<th class="text-right">Total amount <br>by customer (&pound)</th>
				<th class="text-right">Avg. transaction<br> for customer (&pound)</th>
				<th class="text-center word-break">Number <br>of Transactions</th>
				<th class="text-center  word-break">Avg. days <br> between orders</th>
			</tr>
            <?php
				if(isset($analyticsList) && !empty($analyticsList)) { 
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
			  ?>
			<tr>
				<td align="center"><?php echo $i+1;?></td>	
				<td>
					<div class="col-xs-2 no-padding">
						<?php if(isset($image_path) && $image_path != ''){ ?>
							<a onclick="return loaded;" <?php if(isset($original_image_path) ) { ?>href="<?php echo $original_image_path; ?>" class="fancybox" title="<?php  echo  ucfirst($value->FirstName).' '.ucfirst($value->LastName);?>" <?php } ?> > 
								<img  width="46" height="46" align="top" class="img_border" src="<?php echo  $image_path;?>" style="border-radius:46px;" >
							</a>
						<?php } else {?> 
						<img  width="46" height="46" align="top" class="img_border" src="<?php echo ADMIN_IMAGE_PATH.'no-images.jpg';?>" style="border-radius:46px;" > 
						<?php  } ?>
					</div>
				</td>
				<td align="left"><?php  echo  ucfirst($value->FirstName).' '.ucfirst($value->LastName);?></td>
				<td nowrap><?php if(isset($value->FirstVisit) && $value->FirstVisit != '0000-00-00 00:00:00'){ echo date('d M Y',strtotime($value->FirstVisit)); }else echo '-';?></td>
				<td nowrap><?php if(isset($value->LastVisit) && $value->LastVisit != '0000-00-00 00:00:00'){ echo date('d M Y',strtotime($value->LastVisit)); }else echo '-';?></td>
				<td align="right"><?php if(isset($value->TotalPrice) && $value->TotalPrice != ''){ echo number_format((float)$value->TotalPrice,2,'.',',');}else echo "0";?></td>
				<td align="right"><?php if(isset($averageSpend) && $averageSpend != ''){ echo number_format((float)$averageSpend,2,'.',',');}else echo "0";?></td>
				<td align="center"><?php if(isset($value->TotalOrders) && $value->TotalOrders != ''){ echo $value->TotalOrders;}else echo "0";?></td>
				<td align="center"><?php echo $dayDifference; ?></td>
			</tr>
			<?php $i++;} } else{ ?>	
				<tr><td align="center" colspan="10" style="color:red;"><?php echo "No result found";?></td></tr>
			<?php } ?>
	</table>
			<!-- End product List -->						 
			
	</div><!-- /.box-body -->
</div>	
<script src="<?php echo SITE_PATH;?>/admin/webresources/js/jquery-latest.js" type="text/javascript"></script>
<script type="text/javascript">
		if($('#cust_hist_display_count').val() == '0'){
			$('#prevCustHist').hide();
		}
		$('#cust_hist_total_count').val('<?php echo $tot_rec;?>');		
		$('#cust_hist_display_count').val('<?php if(isset($_GET['start']) && $_GET['start'] !='')  echo $limit+10;?>');
		var total 		= $('#cust_hist_total_count').val();
		var resultCount = ($('#cust_hist_display_count').val())-1;
		if( total == '0' ){
			$('#nextCustHist').hide();
			$('#prevCustHist').hide();
		}
		if(resultCount >= total || Number(resultCount)+1 == total){ 
			$('#nextCustHist').hide();
		}else if(total > resultCount){
			$('#nextCustHist').show();
		}else if(total < resultCount){
			$('#nextCustHist').hide();
			$('#prevCustHist').hide();
		}
		if($('#cust_hist_display_count').val() == '10' || total == '0'){
			$('#prevCustHist').hide();
		}
</script>