<?php if(isset($CategoryList)){ ?>
	<script src="<?php echo MERCHANT_SCRIPT_PATH;?>jquery-latest.js" type="text/javascript"></script>
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>raphel.min.js" type="text/javascript"></script>
	<script src="<?php echo MERCHANT_SCRIPT_PATH; ?>morris_custom.js" type="text/javascript"></script>
<?php } else { 
	if(isset($_SESSION['CategoryListArray'])){ 
		$CategoryList = $_SESSION['CategoryListArray'];
		unset($_SESSION['CategoryListArray']);
	}
 } ?>
<?php 
if(isset($CategoryList) && is_array($CategoryList)){
foreach($CategoryList as $k=>$v){
		for($i=6;$i>0;$i--){
			$date 			= date('D',strtotime('- '.$i.' days'));	
			$newArr[$v['CategoryId']][$date] 	= '0'; 
	}
}
foreach($CategoryList as $k=>$v){
		foreach($v['WeekList'] as $k1=>$v1){
			$newArr[$v['CategoryId']][$v1['OrderDay']] = $v1['TotalPrice'];	
		}	
}

?>
<script type="text/javascript">
<?php 
foreach($CategoryList as $key=>$value){ ?>
		window.m = Morris.Bar({
		element: "week_canvas_<?php echo $value['CategoryId'];?>",
		 data: [
			<?php
				foreach($newArr[$value['CategoryId']] as $k1=>$v1){
				?>
			{y:'<?php echo $k1; ?>',a:'<?php echo $v1;?>'},
			<?php }  ?>
		],
		xkey		: 'y',
		ykeys		: ['a'],
		labels		: ['&pound'],
		barColors	: ['#01a99a'],
		//grid		: false,
		hideHover	: 'true',
		gridLineColor: '#01a99a',
		numLines: 1,
		axes		: false,
		barGap		: '0',
		barSizeRatio: '0.95',
		stacked		: true,
		redraw		: true,
		resize		: true,
		hoverCallback: function(index, options, content,row) {
			//return '&pound'+Math.floor(row.a)+'<br>'+row.y;
			return '&pound'+row.a+'<br>'+row.y;
		}
	});
	<?php 
	$day_arr[$value['CategoryId']]['Morning'] = (isset($value['Morning']['Percentage']) && $value['Morning']['Percentage'] != '' ? $value['Morning']['Percentage'] : 0.3) ; 
	$day_arr[$value['CategoryId']]['Noon'] = (isset($value['Noon']['Percentage']) && $value['Noon']['Percentage'] != '' ? $value['Noon']['Percentage'] : 0.3) ; 
	$day_arr[$value['CategoryId']]['Evening'] = (isset($value['Evening']['Percentage']) && $value['Evening']['Percentage'] != '' ? $value['Evening']['Percentage'] : 0.3) ;
	?>
	window.m = Morris.Bar({
		element: 'day_canvas_<?php echo $value['CategoryId'];?>',
		 data: [
			<?php foreach($day_arr[$value['CategoryId']] as $k=>$v){ ?>
			{y:'',a:'<?php echo $v;?>'},
			<?php } ?>
		],
		xkey		: 'y',
		ykeys		: ['a'],
		labels		: ['&pound'],
		//barColors	: ['#01a99a'],
		grid		: false,
		hideHover	: 'always',
		axes		: false,
		//horizontal 	: true,
		barGap		: '0',
		barSizeRatio: '0.95',
		stacked		: true,
		redraw		: true,
		resize		: true,
		//barColors	: colors_array,
		barColors: function (row, series, type) {
			//var index = 1;
			if (type === 'bar') {
				if( $("#day_color_<?php echo $value['CategoryId'];?>").val() == 1){
					$('#day_color_<?php echo $value['CategoryId']?>').val('2');
					return '#000';
				}
				if( $("#day_color_<?php echo $value['CategoryId'];?>").val() == 2){
					$('#day_color_<?php echo $value['CategoryId'];?>').val('3');
					return '#fc7f09';
				}
				if($("#day_color_<?php echo $value['CategoryId'];?>").val() == 3){
					$('#day_color_<?php echo $value['CategoryId'];?>').val('1');
					return '#01B3A5';
				}
			}
			else {
			return '#000';
			}
		}
	});
	//var red = Math.ceil( 255 * row.y / this.ymax);
<?php } ?>
</script>
<?php } ?>