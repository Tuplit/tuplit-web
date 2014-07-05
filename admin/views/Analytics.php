<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
require_once('controllers/AdminController.php');
$msg = '';

commonHead(); ?>
<body class="skin-blue">
<?php top_header(); ?>
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<div class="row col-xs-10">
			<h1><i class="fa fa-list"></i> Analytics </h1>			
		</div>
	</section>
	<!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-xs-12">
				<form name="search_Analytics" action="Analytics" method="post">
				<div class="box box-primary">	
					<div class="col-sm-3 form-group">
						<label>Start Date</label>
						<div class="col-lg-12 no-padding">
							<input  type="text" id = "from_date" class="form-control datepicker" autocomplete="off" title="Select Date" name="from_date" value="" onchange="return emptyDates(this);">
						</div>
					</div>
					<div class="col-sm-3 form-group">
						<label>End Date</label>
						<div class="col-lg-12 no-padding">
							<input type="text" id = "to_date" class="form-control datepicker" autocomplete="off"  title="Select Date" name="to_date" value="" onchange="return emptyDates(this);">
						</div>
					</div>	
					<div class="col-sm-12 box-footer clear" align="center">
						<label>&nbsp;</label>
						<input type="button" class="btn btn-success" name="Search" id="Search" value="Search" >
					</div>
				</div>
				</form>
			</div>			
		</div>
		<!-- BAR CHART -->
          <div class="box box-success">
              <div class="box-header">
                  <h3 class="box-title">Bar Chart</h3>
              </div>			  
              <div class="box-body chart-responsive">
                  <div class="chart" id="bar-chart" style="height: 300px;"></div>
              	   </div><!-- /.box-body -->
          </div><!-- /.box -->
		 <div class="box-body chart-responsive">
                  <div class="chart" id="line-chart" style="height: 300px;"></div>
              	  </div><!-- /.box-body -->
          </div><!-- /.box -->
		   <div class="box-body chart-responsive">
                  <div class="chart" id="donut-chart" style="height: 300px;"></div>
              	  </div><!-- /.box-body -->
           </div><!-- /.box -->
	</section><!-- /.content -->	
<?php commonFooter(); ?>
 <!-- Morris charts -->
        <link href="<?php echo ADMIN_STYLE_PATH; ?>theme/morris/morris.css" rel="stylesheet" type="text/css" />
<!-- Morris.js charts -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
    <script src="<?php echo ADMIN_SCRIPT_PATH; ?>theme/plugins/morris/morris.min.js" type="text/javascript"></script>
	
 <script type="text/javascript">
         $(function() {               
              	//BAR CHART
               /*	var bar = new Morris.Bar({
                    element: 'bar-chart',
                    resize: true,
                    data: [
                        {y: '2006', a: 100, b: 90},
                        {y: '2007', a: 75, b: 65},
                        {y: '2008', a: 50, b: 40},
                        {y: '2009', a: 75, b: 65},
                        {y: '2010', a: 50, b: 40},
                        {y: '2011', a: 75, b: 65},
                        {y: '2012', a: 100, b: 90}
                    ],
                    barColors: ['#00a65a', '#f56954'],
                    xkey: 'y',
                    ykeys: ['a', 'b'],
                    labels: ['Users', 'Merchants'],
                    hideHover: 'auto'
                });*/
				
				//DONUT CHART
                var donut = new Morris.Donut({
                    element: 'donut-chart',
                    resize: true,
                    colors: ["#3c8dbc", "#f56954", "#00a65a"],
                    data: [
                        {label: "Download Sales", value: 50},
                        {label: "In-Store Sales", value: 30},
                        {label: "Mail-Order Sales", value: 20}
                    ],
                    hideHover: 'auto'
                });
				
				
				/*
				
				var barchart2 = new Morris.Bar({
                    element: 'bar-chart1',
                    resize: true,
                    data: [
                        {y: '2006', a: 100},
                        {y: '2007', a: 75},
                        {y: '2008', a: 50},
                        {y: '2009', a: 75},
                        {y: '2010', a: 50},
                        {y: '2011', a: 75},
                        {y: '2012', a: 1000}
                    ],
                    barColors: ['#01B3A5'],
                    xkey: 'y',
                    ykeys: ['a'],
                    labels: ['Orders'],
					//grid : false, //set to hide horizontal grids
					//gridTextColor : '#000000',gridTextSize : '14',gridTextFamily : 'sans-serif',gridTextWeight : 'bold', 
                    hideHover: 'auto'
					
                });
				*/
				
				
				//DONUT CHART
             
				
				$(".datepicker").datepicker({
					showButtonPanel	:	true,        
				    buttonText		:	'<i class="fa fa-calendar"></i>',
				    buttonImageOnly	:	true,
				    buttonImage		:	path+'webresources/images/calender.png',
				    dateFormat		:	'mm/dd/yy',
					changeMonth		:	true,
					changeYear		:	true,
					hideIfNoPrevNext:	true,
					showWeek		:	true,
					yearRange		:	"c-30:c",
					closeText		:   "Close"
				 });
            });
</script>
<script>
$(function() {  
 
 var bar = new Morris.Bar({
                    element: 'bar-chart',
                    resize: true,
                    data: [0,0],
                    barColors: ['#01B3A5'],
                    xkey: 'Orderdate',
                    ykeys: ['Orders'],
                    labels: ['Orders'],
					//grid : false, //set to hide horizontal grids
					//gridTextColor : '#000000',gridTextSize : '14',gridTextFamily : 'sans-serif',gridTextWeight : 'bold', 
                    hideHover: 'auto'					
                });
 
 		
 		
		
		$.ajax({
		      type: "GET",
		      dataType: 'json',		    
			  url: actionPath+"models/AjaxAction.php",
			  data: 'action=DRAW_CHART&start_date=',		    
			  success: function (result){								
					bar.setData(result);	
					
					
					/*Morris.Line({ element: 'line-chart', data: result,xkey: 'Orderdate',
                    ykeys: ['Orders'],
                    labels: ['Orders'],
					hideHover: 'auto',
					resize: true });	*/
					
					/*Morris.Line({ element: 'donut-chart', data: result,xkey: 'Orderdate',
                    ykeys: ['Orders'],
                    labels: ['Orders'],
					hideHover: 'auto',
					xLabelAngle: 70, xLabelFormat: function (x) { var IndexToMonth = [ "Jan", "Feb", "Mär", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dez" ]; var month = IndexToMonth[ x.getMonth() ]; var year = x.getFullYear(); return year + ' ' + month; }, dateFormat: function (x) { var IndexToMonth = [ "Jan", "Feb", "Mär", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dez" ]; var month = IndexToMonth[ new Date(x).getMonth() ]; var year = new Date(x).getFullYear(); return year + ' ' + month; }, resize: true });				*/
		       }
		 });
		
			
			
		/*var donut = new Morris.Donut({
                    element: 'donut-chart',
                    resize: true,
                    colors: ["#01B3A5", "#f56954", "#00a65a"],
                    data: [
                        {label: "2006", value: 90},
                        {label: "2007", value: 30},
                        {label: "2008", value: 80},
						{label: "2009", value: 10},
						{label: "2010", value: 30},
						{label: "2011", value: 20},
						{label: "2012", value: 50},
                    ],
                    hideHover: 'auto'
                });*/
						   
		
		/*var json = (function () {
            var json = null;
            $.ajax({               
			   type: "GET",
                url: actionPath+"models/AjaxAction.php",
				data: 'action=DRAW_CHART&start_date=',
                'dataType': "json",
                'success': function (data1) {
					alert(data1);
                    //json = data;
						$('#donut-chart').html('');		
				
					var donut = new Morris.Donut({
                    element: 'donut-chart',
                    resize: true,
                    colors: ["#01B3A5", "#f56954", "#00a65a"],
                    data: data1,
                    hideHover: 'auto'
                	});
                }
            });
            return json;
        })();*/
		
		
		
				
		//alert(json);
		
   });	
 
 
 
 $('#Search').click(function(){ 
 	var start_date = $("#from_date").val();
	var end_date   = $("#to_date").val();	
 	//requestchartData(start_date,end_date);
	
	var bar = new Morris.Bar({
                    element: 'bar-chart',
                    resize: true,
                    data: [0,0],
                    barColors: ['#01B3A5'],
                    xkey: 'Orderdate',
                    ykeys: ['Orders'],
                    labels: ['Orders'],
					//grid : false, //set to hide horizontal grids
					//gridTextColor : '#000000',gridTextSize : '14',gridTextFamily : 'sans-serif',gridTextWeight : 'bold', 
                    hideHover: 'auto'					
                });
				
						
			
				
			$.ajax({
		      type: "GET",
		      dataType: 'json',
		     // url: sitePath+"./api", // This is the URL to the API
			  url: actionPath+"models/AjaxAction.php",
			  data: 'action=DRAW_CHART&start_date='+start_date+'&end_date='+end_date,
		      //data: { action:chart }
			  success: function (result){
					//alert(result);
					//console.log(result);
					bar.setData(result);
					//donut.setData(result);
					
					/*Morris.Line({ element: 'line-chart', data: result,xkey: 'Orderdate',
                    ykeys: ['Orders'],
                    labels: ['Orders'],
					hideHover: 'auto',
					resize: true });	*/
					
		        }
		});
			
	});	
 

</script>
</html>