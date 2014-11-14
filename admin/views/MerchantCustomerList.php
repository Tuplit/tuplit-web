
<?php 	
		if(isset($transactionList) && is_array($transactionList) && count($transactionList)>0 ){ ?>
<?php
		foreach($transactionList as $key=>$value){ 
		?>									
		<tr>
			<td align="center" ><?php echo $i+1;?></td>	
			<td><?php if(isset($value->OrderDate) && $value->OrderDate != '' ) echo date('d M Y',strtotime($value->OrderDate));?></td>
			<?php if($displayCount == 'Merchant'){ ?>
				<td ><?php  echo  (!empty($value->FirstName)?ucfirst($value->FirstName):'').' '.(!empty($value->LastName)?ucfirst($value->LastName):'');?></td>
			<?php } else { ?>
				<td ><?php  echo  (!empty($value->CompanyName)?ucfirst($value->CompanyName):'');?></td>
			<?php } ?>
			<td><?php if(isset($value->TotalItems) && $value->TotalItems != '' ) { ?>
				<a href='ProductPopup?cart_id=<?php echo base64_encode($value->fkCartId);?>&cs=1' class='newWindow' title='View Products'><?php echo $value->TotalItems; ?></a>
				<?php }  else echo '-'?></td>
			<td >
				<?php if(isset($value->TransactionId) && $value->TransactionId != '' ) echo $value->TransactionId; else echo '-';?></td>
			<td ><?php if(isset($value->TotalPrice) && $value->TotalPrice != '' ) echo price_fomat($value->TotalPrice);?></td>
			<td ><?php if($value->Commision != '') echo price_fomat($value->Commision); else echo price_fomat('0');?></td>
			<td ><?php  if(isset($value->Status) && $value->Status != '' ) echo ($value->Status == 1?'Accepted':'Rejected');?></td>
		</tr>
<?php $i++; }  ?>
<?php
}  ?>
