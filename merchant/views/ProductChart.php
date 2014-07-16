<?php if(isset($_POST['action']) && $_POST['action']=='GET_CHART') { 
	require_once('ProductChartFilter.php');
	merchant_login_check();
	$xarrays = $new_array = $value_arrays = array();
	$count = 0;	
	if(isset($x_labels_string) && $x_labels_string!='') { 
		$xarrays = explode(',',$x_labels_string);
	}
	if(isset($all_series['order']) && $all_series['order'] != ''){
		$value_arrays = explode(',',$all_series['order']);
	}

?>
<div class="row table-responsive no-margin">
	<?php if(isset($product_array) && !empty($product_array)) { ?>
	<form  id="productAnalytics" name="productAnalytics" action="#" method="post">
		<input type="Hidden" name="orderby" id="orderby" value="<?php if(isset($sort_field) && $sort_field !='') echo $sort_field;?>">
		<input type="Hidden" name="ordertype" id="ordertype" value="<?php if(isset($sort_type) && $sort_type !='') echo $sort_type;?>">
		<table class="table table-hover" border="0" >
	         <tr>
				<th style="text-align:left"><a onclick="sortTable('Name');" id="Name" name="Name" style="cursor:pointer;">Name</a></th>
				<th style="text-align:center" colspan="2"><a onclick="sortTable('TotalPrice');" id="TotalPrice" name="TotalPrice" style="cursor:pointer;">Sales</a></th>
				<th style="text-align:center"><a onclick="sortTable('TotalQuantity');" id="TotalQuantity" name="TotalQuantity" style="cursor:pointer;">Quantity</a></th>
			</tr>
	        <?php
			$total_price = $total_quantity = $average	= 0;
		  	foreach($product_array as $key=>$value){
				$total_price	=	$value["TotalPrice"];
				$total_quantity	=	$value["TotalQuantity"];
				$average		=	$value["Average"];
				$count += 1;?>
			
			<tr>
				<td align="left"><?php if(isset($value["Name"]) && $value["Name"] != '' ){ echo $value["Name"];} else echo '-';?></td>
				<td align="center"><?php if(isset($total_price) && $total_price>0){ echo '$'.$total_price;}?></td>
				<td align="center"><?php if(isset($average) && $average>0){ echo '$'.$average;}?></td>
				<td align="center"><?php if(isset($total_quantity) && $total_quantity> 0){ echo $total_quantity;}?></td>
				
			</tr>
		<?php } //end for ?>	
	  </table>
  </form>
<?php } else { ?>
	<div class="row clear">		
		 <div align="center" class="alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10">
		 	<i class="fa fa-fw fa-warning"></i><?php echo $errorMessage;?></div>							
	</div>							
<?php } ?>						
</div>
<?php if(isset($error_div) && $error_div==0 && !empty($product_array)) {?>
 <div class="box box-success">
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
<?php if(isset($error_div) && $error_div==0 && !empty($product_array)) {
?>

$(document).ready(function() {
	var barchart2 = new Morris.Bar({
              element: 'bar-chart1',
              resize: true,
              data: [
			  <?php 
			  if(isset($xarrays) && is_array($xarrays)) {
			  			foreach($xarrays as $key=>$val){
							?>
                  	{y: '<?php echo $val?>',a: <?php echo $value_arrays[$key];?>},
					<?php } 
				}?>
              ],
              barColors: ['#01B3A5'],
              xkey: 'y',
              ykeys: ['a'],
              labels: ['Orders'],
              hideHover: 'auto'
			 
          });
  });

<?php } ?>
</script> 
<?php

 } ?>
 