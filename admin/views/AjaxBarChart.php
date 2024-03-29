<?php 
if(isset($_POST['action']) && $_POST['action']=='GET_CHART') { 
	require_once('AjaxChartFilter.php');
	$xarrays = $new_array = array();
	$count = 0;	
	/*if(isset($_POST['filter_dashboard_date']) && $_POST['filter_dashboard_date'] =='month') {
		$default_label = 'Jan,Feb,Mar,Apr,May,Jun,Jul,Aug,Sep,Oct,Nov,Dec';
		$xarrays = explode(',',$default_label);
	 }
	else{*/
		if(isset($x_labels_string) && $x_labels_string!='') { 
			$xarrays = explode(',',$x_labels_string);
		}
		else{
			$default_label = 'Jan,Feb,Mar,Apr,May,Jun,Jul,Aug,Sep,Oct,Nov,Dec';
			$xarrays = explode(',',$default_label);
		}
	//}
	
	$value_arrays = array();
	if(isset($all_series['order']) && $all_series['order'] != ''){
		$value_arrays = explode(',',$all_series['order']);
	}
?>
<div class="row table-responsive no-margin">
	<?php if(isset($order_array) && !empty($order_array)) { ?>
	<table class="table table-hover" >
                         <tr>
			<th style="text-align:center">Total Revenue</th>
			<th style="text-align:center">Average Transaction</th>
			<th style="text-align:center">Total No.of Transactions</th>
		</tr>
                    <?php
		$total_price = $total_orders = 0;
	  	foreach($order_array as $key=>$value){
			$total_price	+=	$value->TotalPrice;
			$total_orders	+=	$value->TotalOrders;
			$average		=	$total_price/$total_orders;
			$count += 1;
		}?>
		
		<tr>
			<td align="center"><?php if(isset($total_price) && $total_price>0){ echo '&pound;'.number_format((float)$total_price,2,'.',',');}?></td>
			<td align="center"><?php if(isset($average) && $average>0){ echo '&pound;'.number_format((float)$average, 2, '.', ',');}?></td>
			<td align="center"><?php if(isset($total_orders) && $total_orders> 0){ echo $total_orders;}?></td>
		</tr>
	<?php //} //end for ?>	
                     </table>
<?php } else { ?>
	<div class="row clear">		
		 <div align="center" class="alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10">
		 	<i class="fa fa-fw fa-warning"></i> No results found</div>							
	</div>							
<?php } ?>						
<?php if(isset($error_div) && $error_div==0 && !empty($order_array)) {?>
<div>
	 <div class="box-header">
	     <h3 class="box-title">Bar Chart</h3>
	 </div>			  
	<div class="box-body chart-responsive">
	     <div class="chart" id="bar-chart1" style="height: 300px;"></div>
	 </div>
</div> 
<?php } ?>				

<script type="text/javascript">

$('.chart').html('');
<?php if(isset($error_div) && $error_div==0 && !empty($order_array)) {?>
$(document).ready(function() {
	var barchart2 = new Morris.Bar({
              element: 'bar-chart1',
              resize: true,
			  xLabelMargin: 10,
              data: [
			  <?php 
			  $extra = '';
			  if(isset($xarrays) && is_array($xarrays)) {
			  			foreach($xarrays as $key=>$val){
						if($_POST['filter_dashboard_date'] == 'timeofday'){
							if($val == 'Morning')
								$extra =  ' (less than 12)';
							else if($val == 'Afternoon')
								$extra =  ' (12 to 16)';
							else if($val == 'Evening')
								$extra =  ' (16 to 22)';
 							else 
								$extra =  ' (greater than 22)';
						}
						if(isset($value_arrays[$key]) && $value_arrays[$key] != ''){
							?>
                  	{y: '<?php echo $val.$extra?>',a: <?php echo number_format((float)$value_arrays[$key],2,'.','');?>},
					<?php } }
				}?>
              ],
              barColors: ['#01B3A5'],
              xkey: 'y',
              ykeys: ['a'],
              labels: ['Revenue(&pound;)'],
              hideHover: 'auto',
			 // xLabelAngle: 60
			 
          });
  });

<?php } ?>
</script>
<?php
 } ?>