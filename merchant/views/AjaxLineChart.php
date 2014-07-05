<?php if(isset($_POST['action']) && $_POST['action']=='GET_CHART') { 
require_once('ajax_chart_filter.php');

if(isset($error_div) && $error_div==0) {
?>
<script type="text/javascript">
$(function () {
    var chart;
	var xlabels = [];
	var dataValue = [];
	<?php if(isset($x_labels_string) && $x_labels_string!='') { ?>
	xlabels = [<?php echo $x_labels_string;?>];
	<?php } else  { ?>
	xlabels = [
					'Jan',
                    'Feb',
                    'Mar',
                    'Apr',
                    'May',
                    'Jun',
                    'Jul',
                    'Aug',
                    'Sep',
                    'Oct',
                    'Nov',
                    'Dec'
                ];
	<?php } ?>
	<?php if(isset($value_string) && $value_string!='') { ?>
	dataValue = [<?php echo $value_string; ?>];
	<?php } else { ?>
	dataValue = [70.0, 60.9, 90.5, 14.5, 78.2, 51.5, 85.2, 56.5, 23.3, 78.3, 43.9, 90.6];
	<?php } ?>
    $(document).ready(function() {
        chart = new Highcharts.Chart({
             colors:['#2b776d'],
			chart: {
                renderTo: 'graph_block_line',
                type: 'line',
				zoomType: 'y'
            },
			title: {
                text: '<?php /* if(isset($filter_type) && $filter_type=='all') 
							 	echo 'Showing All Data';
							else {
								$chart_count = count($filter_type_arr);
								if($chart_count==1)
									echo 'Showing '.ucfirst($filter_type).' Data';
								else 
									echo 'Showing Multiple Data';
							}*/ ?>',
            },
            xAxis: {
				gridLineWidth: 1,
				tickLength: 0,
				categories: xlabels,
				labels: {
                    rotation: -45,
                    align: 'right',
                    style: {
                        fontSize: '10px',
                        fontFamily: 'Verdana, sans-serif'
                    }
                }
            },
            yAxis: {
			    lineWidth: 1,
				title: {
                    text: ''
                }
            },
			tooltip: {
                formatter: function() {
                	var tooltipContent = '<b>'+ this.series.name +'</b><br/>';
					if(this.series.name=='Revenue') {
						tooltipContent = tooltipContent+'$'+this.y ;
					} else {
						tooltipContent = tooltipContent+this.y ;
					}
					return tooltipContent;
                }
            },
			credits : {
  				enabled : false
			},
			 plotOptions: {
		    	series: {
		    		marker: {
		    			enabled: false	
		    		}
		    	}
		    },
			legend: {
                layout: 'vertical',
                backgroundColor: '#FFFFFF',
                align: 'left',
                verticalAlign: 'top',
                x: 100,
                y: 70,
                floating: true,
                shadow: true
            },
           	series: [
               
				<?php } if(isset($all_series['order']) && $all_series['order']!='') { ?>
				{
				   // showInLegend: false, 
					name: 'Revenue',
	                data: [<?php echo $all_series['order']; ?>]
            	}
			
    
            ]
        });
    });
    
});
		</script>
<div id="graph_block_line"></div><!-- style="width: 800px; height: 400px; margin: 0 auto" -->
<?php
} else { ?>
<div id="error_div" style="color:red;margin-top:100px;text-align:center;">
	<img src="<?php echo MERCHANT_IMAGE_PATH;?>no_datas.png" />
</div>
<?php }
 } ?>