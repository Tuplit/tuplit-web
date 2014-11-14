<?php 
	if((isset($_GET['displayType']) && $_GET['displayType'] == 'Transaction') || !isset($_GET['displayType'])){
?>
<div class="table-responsive">
<table class="table" id="transaction_list" >
<tr>
	<th align="center" width="5%" style="text-align:center">#</th>												
	<th width="10%">Date</th>
	<th width="15%">Customer</th>
	<th width="15%">Merchant</th>
	<th width="6%">Product Count</th>
	<th width="8%">ID</th>
	<th width="10%">Amount</th>
	<th width="15%">Commission Fee</th>
	<th width="15%">Status</th>
</tr>
<?php 	
if(isset($OrderListResult) && is_array($OrderListResult) && count($OrderListResult)>0 ){
	foreach($OrderListResult as $key=>$value){ ?>									
	<tr >
		<td align="center"><?php echo $i+1;?></td>	
		<td><?php if(isset($value->OrderDate) && $value->OrderDate != '' ) echo date('d M Y',strtotime($value->OrderDate));?></td>
		<td><?php  echo  (!empty($value->FirstName)?ucfirst($value->FirstName):'').' '.(!empty($value->LastName)?ucfirst($value->LastName):'');?></td>
		<td><?php  echo  (!empty($value->CompanyName)?ucfirst($value->CompanyName):'');?></td>
		<td><?php if(isset($value->TotalItems) && $value->TotalItems != '' ) { ?>
			<a  onclick="return checkLoader();" href='ProductPopup?cart_id=<?php echo base64_encode($value->fkCartId);?>&cs=1' class='newWindow' title='View Orders'><?php echo $value->TotalItems; ?></a><!--  showLoaderPopup(); -->
			<?php }  else echo '-'?></td>
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
<?php } else {
	if(isset($OrderListResult) && is_array($OrderListResult) && count($OrderListResult)>0 ){ 
			foreach($OrderListResult as $key=>$value){ 									
				if($_GET['displayType'] == 'Merchant'){ 
					$image_path = '';
					$photo = $value->Icon;
					$image_path = ADMIN_IMAGE_PATH.'no_user.jpeg';
					if(isset($photo) && $photo != ''){
						if(SERVER){
							if(image_exists(6,$photo))
								$image_path = MERCHANT_ICONS_IMAGE_PATH.$photo;
						}else{
							if(file_exists(MERCHANT_ICONS_IMAGE_PATH_REL.$photo))
								$image_path = MERCHANT_ICONS_IMAGE_PATH.$photo;
						}
					}
					$displayName	= (!empty($value->CompanyName)?ucfirst($value->CompanyName):'Merchant Name');
					$idVal			= ((isset($value->fkMerchantsId) && !empty($value->fkMerchantsId))? $value->fkMerchantsId :'');
				}else{ 
					$image_path = '';
					$photo = $value->Photo;
					$original_path = $image_path = ADMIN_IMAGE_PATH.'no_user.jpeg';
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
					$displayName	= (!empty($value->FirstName)?ucfirst($value->FirstName):'').' '.(!empty($value->LastName)?ucfirst($value->LastName):'');
					$idVal			= ((isset($value->fkUsersId) && !empty($value->fkUsersId))? $value->fkUsersId :'');
				}	?>
				<div class="list-comment">
						<?php  if($photo != '' ){ ?>
							<img class="customerImage" width="50" height="50" align="top" title="<?php echo $idVal;?>" src="<?php echo $image_path;?>">
						<?php }else { ?><img class="customerImage" width="50" height="50" align="top" src="<?php echo ADMIN_IMAGE_PATH.'no_user.jpeg';?>"><?php } ?> 
						<span><?php  echo $displayName; ?></span>
				</div>
				<div class="table-responsive clear">
					<table class="table" id="transaction_list_<?php echo $idVal;?>">
						<tr>
							<th width="5%" align="center" style="text-align:center">#</th>												
							<th  width="10%" >Date</th>
							<th width="25%" ><?php if($_GET['displayType'] == 'Merchant') echo "Customer"; else echo 'Merchant';?></th>
							<th width="5%" >Product Count</th>
							<th width="20%" >ID</th>
							<th width="10%" >Amount</th>
							<th width="15%" >Commission Fee</th>
							<th>Status</th>
						</tr>
						<tr>
							<td align="center"><?php echo '1';?></td>	
							<td ><?php if(isset($value->OrderDate) && $value->OrderDate != '' ) echo date('d M Y',strtotime($value->OrderDate));?></td>
							<?php if($_GET['displayType'] == 'Merchant'){ ?>
								<td><?php  echo  (!empty($value->FirstName)?ucfirst($value->FirstName):'').' '.(!empty($value->LastName)?ucfirst($value->LastName):'');?></td>
							<?php } else { ?>
								<td><?php  echo  (!empty($value->CompanyName)?ucfirst($value->CompanyName):'');?></td>
							<?php } ?>
							<td><?php if(isset($value->TotalItems) && $value->TotalItems != '' ) { ?>
								<a  onclick="return checkLoader();" href='ProductPopup?cart_id=<?php echo base64_encode($value->fkCartId);?>&cs=1' class='newWindow newWindow-new' title='View Products'><?php echo $value->TotalItems; ?></a>
								<?php }  else echo '-'?></td>
							<td>
								<?php if(isset($value->TransactionId) && $value->TransactionId != '' ) echo $value->TransactionId; else echo '-';?></td>
							<td><?php if(isset($value->TotalPrice) && $value->TotalPrice != '' ) echo price_fomat($value->TotalPrice);?></td>
							<td><?php if($value->Commision != '') echo price_fomat($value->Commision); else echo price_fomat('0');?></td>
							<td><?php  if(isset($value->OrderStatus) && $value->OrderStatus != '') echo $order_status_array[$value->OrderStatus];?></td>
						</tr>
					</table>
				</div>
				
				<div class="seemore-link" align="center" style="height: 50px">
				<?php if(isset($countArray) && $countArray[$idVal] != '' && $countArray[$idVal] > 1 ){?> 
					<div id="showMore_<?php echo $idVal;?>">
					<a href="javascript:void(0);" id="showMore" onclick="showMoreMerchantCustomer('<?php echo $idVal?>')">SEE MORE</a>
					<?php $i = 0;?>
						<input type="hidden" id="<?php echo $_GET['displayType'];  ?>_result_count_<?php echo $idVal;  ?>" value="1">
						<input type="hidden" id="<?php echo $_GET['displayType'];  ?>_total_count_<?php echo $idVal;  ?>" value="<?php echo $tot_rec;  ?>">
					</a>
					</div>
				<?php  } ?>
				</div>
				
					
			<?php }
} else { ?>
	<div style="text-align:center;color:red" class="error-space"><?php echo "No result found";?></div>
	<?php } } ?>
<script type="text/javascript">
	function checkLoader(){
		if(loaded == true){
			showLoaderPopup();
			$(".newWindow").fancybox({
				scrolling	: 'none',			
				type		: 'iframe',
				width		: '600',
				position	:'fixed',
				maxWidth	: '100%',  // for responsive width set					
				fitToView	: false,
				//wrapCSS 	: 'photo-lightbox-class',		
				afterShow 	: function() {
				//location.reload();
				$('.loader-merchant').hide();
			}
			});
		}
		else{
			return false;
		}
	}


</script>