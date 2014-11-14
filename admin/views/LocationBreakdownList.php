
	<!--<h2 class="location-title">Location breakdown</h2>-->
	<h2 class="location-title tabsection" id="location">Location breakdown</h2>
	<form action="" class="l_form" name="LocationBreakdown" id="LocationBreakdown"  method="post">
		<div class="box-body table-responsive no-padding no-margin location_break" id="location_block" style="display:<?php if(isset($displayList) && $displayList == 1){ echo 'block';} else {echo 'none';} ?>" >
		<table class="table table-hover">
			<tr>
				<th>Address</th>
				<th>Total sales volume</th>
				<th>Transaction count</th>
				<th>Unique customers</th>
				<th>Repeat customers</th>
				<th class="text-right">Avg. spend transaction</th>
				<th>First transaction</th>
			</tr>
			 <?php
				if(isset($locationResult) && !empty($locationResult) && count($locationResult)>0) { 
					foreach($locationResult as $key=>$value){ 
						$count 	= 0;
						$keyy 	= explode(',',$value->userArray);
						$key1	=  array_count_values($keyy);
						//print_r($key1);
						foreach($key1 as $k=>$v){
							if($v > 1){
								$count++;
							}
						}
						if($count != '0' && isset($value->uniqueCustomers) && $value->uniqueCustomers != '0'){
							$percent = ($count/$value->uniqueCustomers)*100;
						}
						?>
					<tr>
						<td><?php if(isset($value->City) && !empty($value->City)) echo $value->City; else echo "-";?></td>
						<td><?php if(isset($value->TotalPrice) && !empty($value->TotalPrice)) echo price_fomat($value->TotalPrice); else echo "0";?></td>
						<td><?php if(isset($value->transCount) && !empty($value->transCount)) echo $value->transCount; else echo "0";?></td>
						<td><?php if(isset($value->uniqueCustomers) && !empty($value->uniqueCustomers)) echo $value->uniqueCustomers; else echo "0";?></td>
						<td><?php if(!empty($count)) {echo $count.' ('.number_format($percent,2).'%)';} else echo '0'.' (0.00%)';?></td>
						<td><?php if(isset($value->TotalPrice) && !empty($value->TotalPrice) && !empty($value->transCount)) echo price_fomat($value->TotalPrice/$value->transCount); else echo "0";?></td>
						<td><?php if(isset($value->FirstTrans) && !empty($value->FirstTrans)) echo date('d M Y',strtotime($value->FirstTrans)); else echo "0";?></td>
					</tr>
			<?php } }else { ?>
					<tr><td style="text-align:center;color:red;" colspan="10" ><?php echo "No result found";?></td></tr>
			<?php } ?>	
		</table>		
		</div>
	</form>
</div>
