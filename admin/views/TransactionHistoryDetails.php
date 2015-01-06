<?php
require_once('includes/CommonIncludes.php');
admin_login_check();
require_once('controllers/OrderController.php');
	$OrderObj   		=   new OrderController();
	$condition			= '';
	$fields    			= " o.TotalPrice,o.TransactionId,o.Status,o.OrderStatus,o.OrderDate,m.CompanyName,u.FirstName,u.LastName,o.fkCartId,o.Commision ";
	if(isset($_GET['search']) && $_GET['search']!=''){
		$condition .= $_GET['search'];
	}
	$leftjoin			= " left join users as u on  (u.id	= o.fkUsersId ) left join merchants as m on (m.id = o.fkMerchantsId)
							left join merchantcategories as mc on (m.id = mc.fkMerchantId) left join categories as c on (c.id = mc.fkCategoriesId)";
	$i 					= $_GET['start'];
	$sort				= " o.id desc ";
	$limit 				= $_GET['start'];
	//$type				= 1;
	//echo $limit	;
	$type = (!empty($type)?$type:'1');
	$OrderListResult  	= $OrderObj->getTransactionDetails($fields,$leftjoin,$condition,$sort,$limit,$type);
	$tot_rec 		  	= $OrderObj->getTotalRecordCount();
?>

<div class="table-responsive">
<table class="table" id="transaction_list" >
<tr>
	<th align="center" style="text-align:center">#</th>												
	<th valign="middle">Date</th>
	<th  valign="middle">Payment To</th>
	<th  valign="middle">Payment From</th>
	<th  valign="middle">Item ID</th>
	<th valign="middle">Amount</th>
	<th valign="middle">Commission <br />Fee</th>
	<th valign="middle">Status</th>
</tr>
<?php 	
if(isset($OrderListResult) && is_array($OrderListResult) && count($OrderListResult)>0 ){
	foreach($OrderListResult as $key=>$value){ ?>									
	<tr >
		<td align="center"><?php echo $i+1;?></td>	
		<td><?php if(isset($value->OrderDate) && $value->OrderDate != '' ) echo date('d M Y',strtotime($value->OrderDate));?></td>
		<td align="center"><?php  echo  (!empty($value->CompanyName)?ucfirst($value->CompanyName):'');?></td>
		<td align="center"><?php  echo  (!empty($value->FirstName)?ucfirst($value->FirstName):'').' '.(!empty($value->LastName)?ucfirst($value->LastName):'');?></td>
		<td>
			<?php if(isset($value->TransactionId) && $value->TransactionId != '' ) echo $value->TransactionId; else echo '-';?></td>
		<td ><?php if(isset($value->TotalPrice) && $value->TotalPrice != '' ) echo price_fomat($value->TotalPrice);?></td>
		<td ><?php if($value->Commision != '') echo price_fomat($value->Commision); else echo price_fomat('0');?></td>
		<td  ><?php  if(isset($value->OrderStatus) && $value->OrderStatus != '') echo $order_status_array[$value->OrderStatus];?></td>
	</tr>
	<?php $i++; } ?>
<?php } else { ?>
	<tr><td align="center" colspan="10" style="color:red;"><?php echo "No result found";?></td></tr>
	<?php } ?>
</table>
</div>
<script src="<?php echo SITE_PATH;?>/admin/webresources/js/jquery-latest.js" type="text/javascript"></script>
<script type="text/javascript">
		if($('#Trans_hist_display_count').val() == '0'){
					$('#prevTransHist').hide();
				}
				$('#Trans_hist_total_count').val('<?php echo $tot_rec;?>');		
				$('#Trans_hist_display_count').val('<?php if(isset($_GET['start']) && $_GET['start'] !='')  echo $limit+10;?>');
				var total 		= $('#Trans_hist_total_count').val();
				var resultCount = ($('#Trans_hist_display_count').val())-1;
				if( total == '0' ){
					$('#nextTransHist').hide();
					$('#prevTransHist').hide();
				}
				if(resultCount >= total || Number(resultCount)+1 == total){ 
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
