<?php 
require_once('includes/CommonIncludes.php');
	$transactionArray	=	array('120','150','170','270','540');
	$colorArray			=	array('#A0F7EF','#60F0E6','#02D2C2','#01B3A5','#008C81');
 ?>
<?php if(isset($transactionArray) &&  !empty($transactionArray)) {?>
 <div>
	 <div class="box-body chart-responsive">
	     <div class="chart" id="bar-chart3" style="height: 300px;"></div>
	 </div>
</div> 
<?php } ?>
<script type="text/javascript">
<?php if(isset($transactionArray) && !empty($transactionArray)) {
?>
	$(document).ready(function() {
		var barchart3 = new Morris.Donut({
			    element: 'bar-chart3',
			    data: [
				<?php  
					if(isset($transactionArray) && is_array($transactionArray)) {
						$total 	= array_sum($transactionArray);//240;
						foreach($transactionArray as $key=>$val){
							$percentage	= ($val/$total)*100;
					 ?>
					{value:<?php echo $percentage;?>, label:<?php echo $val;?> }<?php echo ",";?>
					<?php } }?>
			    ],
				legend: { show:true, location: 'e' },
				resize: true,
	            colors: ["<?php echo $colorArray[0];?>","<?php echo $colorArray[1];?>","<?php echo $colorArray[2];?>","<?php echo $colorArray[3];?>","<?php echo $colorArray[4];?>"],
				hideHover: 'auto',
			   	formatter: function (x) { return  x + "%"}
			    }).on('click', function(i, row){
			    console.log(i, row);
			    });
	  });
	<?php  } ?>
</script> 
