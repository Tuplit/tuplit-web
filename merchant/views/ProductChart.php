<?php
require_once('includes/CommonIncludes.php');
if(isset($_POST['action']) && $_POST['action']=='GET_CHART') { 
	require_once('ProductChartFilter.php');
	$xarrays = $new_array = $value_arrays = array();
	$count = 0;	
	if(isset($x_labels_string) && $x_labels_string!='') { 
		$xarrays = explode(',',$x_labels_string);
	}
	if(isset($all_series['order']) && $all_series['order'] != ''){
		$value_arrays = explode(',',$all_series['order']);
	}
	

?>

<?php if(isset($error_div) && $error_div==0 && !empty($product_array)) {?>
<div class="box box-success">
	<div  class="col-xs-6  pull-right text-right space_top"><a id="view_list" href="#view_list"><strong><i class="fa fa-list"></i> View Product List</strong></a></div>
	 <div class="box-header clear">
	     <h3 class="box-title pull-left">Bar Chart</h3>
	 </div>			  
	<div class="box-body chart-responsive">
	     <div class="chart" id="bar-chart1" style="height:300px;"></div>
	 </div>
	  <div class="box-header">
	     <h3 class="box-title">Pie Chart</h3>
	 </div>		
	 <div class="box-body chart-responsive">
	     <div class="chart" id="bar-chart3" style="height: 300px;"></div>
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
			  xLabelMargin: 20,
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
              labels: ['Order(s)'],
              hideHover: 'auto'
			 
          });
	 var orderby	=	$("#orderby").val();
	 var ordertype	=	$("#ordertype").val();
	 if(orderby == 'Name' && ordertype == 'desc')
		$("#name_bg").attr('src', '../merchant/webresources/images/desc.gif');
	 else if(orderby == 'Name' && ordertype == 'asc')
		$("#name_bg").attr('src', '../merchant/webresources/images/asc.gif');
	 if(orderby == 'TotalPrice' && ordertype == 'desc')
		$("#sales_bg").attr('src', '../merchant/webresources/images/desc.gif');
	 else if(orderby == 'TotalPrice' && ordertype == 'asc')
		$("#sales_bg").attr('src', '../merchant/webresources/images/asc.gif');
	 if(orderby == 'TotalQuantity' && ordertype == 'desc')
		$("#quantity_bg").attr('src', '../merchant/webresources/images/desc.gif');
	 else if(orderby == 'TotalQuantity' && ordertype == 'asc')
		$("#quantity_bg").attr('src', '../merchant/webresources/images/asc.gif');
  });

<?php } ?>
<?php if(isset($error_div) && $error_div==0 && !empty($pie_chart_array)) {
	
?>

$(document).ready(function() {
	var barchart3 = new Morris.Donut({
		      element: 'bar-chart3',
		    data: [
			<?php  if(isset($pie_chart_array) && is_array($pie_chart_array)) {
					$specials = $normal = $total = $s_percentage = $n_percentage = 0;
					if($pie_chart_array[0]['specialProducts'] != '')
						$specials	=	$pie_chart_array[0]['specialProducts'];
					if($pie_chart_array[0]['normalProducts'] != '')
						$normal		=	$pie_chart_array[0]['normalProducts'];
					$total	=	$specials + $normal	;
					if($total > 0){
						$s_percentage	=	number_format((($specials/$total)*100),2,'.','');
						$n_percentage	=	number_format((($normal/$total)*100),2,'.','');
					}?>
		    {value:<?php echo $s_percentage;?>, label: 'Discounted'},
			{value:<?php  echo $n_percentage;?>, label: 'Non Discounted'},
			<?php } ?>
		    ],
			resize: true,
            colors: ["#01b3a5", "#f56954"],
			hideHover: 'auto',
		   	formatter: function (x) { return x + "%"}
		    }).on('click', function(i, row){
		    console.log(i, row);
		    });
  });

<?php } ?>
</script> 
<?php

 } ?>
 <div class="col-lg-8 box-center ">
 <div class="row table-responsive no-margin" id="pro_list">
 	
	<?php if(isset($product_array) && !empty($product_array)) { ?>
	<form  id="productAnalytics" name="productAnalytics" action="#" method="post">
		<input type="Hidden" name="orderby" id="orderby" value="<?php if(isset($sort_field) && $sort_field !='') echo $sort_field;?>">
		<input type="Hidden" name="ordertype" id="ordertype" value="<?php if(isset($sort_type) && $sort_type !='') echo $sort_type;?>">
		<table class="table table-bordered" border="0" >
	         <tr>
				<th rowspan="2"><a onclick="sortTable('Name');" id="Name" name="Name" style="cursor:pointer;">Name<img id="name_bg" src="<?php echo  MERCHANT_IMAGE_PATH.'no_sort.gif'?>"></a></th>
				<th colspan="2" class="text-center"><a onclick="sortTable('TotalPrice');" id="TotalPrice" name="TotalPrice" style="cursor:pointer;">Sales<img id="sales_bg" src="<?php echo  MERCHANT_IMAGE_PATH.'no_sort.gif'?>"></a></th>
				<th rowspan="2" class="text-center"><a onclick="sortTable('TotalQuantity');" id="TotalQuantity" name="TotalQuantity" style="cursor:pointer;">Quantity<img id="quantity_bg" src="<?php echo  MERCHANT_IMAGE_PATH.'no_sort.gif'?>"></a></th>
			</tr>
			<tr>
				<th class="text-center">Total Price</th>
				<th class="text-center">Average</th>
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
</div>


	<?php if(isset($product_array) && !empty($product_array)) { ?>
		<div align="right" class="col-xs-12"><span class=""><a id="charts" href="#charts"> <strong><i class="fa fa-bar-chart-o"></i> View Charts</strong></a></span><br><br></div>					
	<?php } ?>		
</div>
<script type="text/javascript">
	$("a[href='#view_list']").click(function() {
		  var pos = $("#pro_list").position().top;
		  var ht = $(document).height() - pos;
		 $("html, body").animate({ scrollTop: $("#pro_list").offset().top - $(".navbar").height()  }, {duration: $("#pro_list").offset().top});
		  return false; 
    });
	$("a[href='#charts']").click(function() {
		  var pos = $("#view_list").position().top;
		  var ht = $(document).height() - pos;
		 $("html, body").animate({ scrollTop: $("#view_list").offset().top - $(".navbar").height()  }, {duration: $("#view_list").offset().top});
		  return false; 
    });
</script>