<?php
require_once('includes/CommonIncludes.php');
$date_type	= 'day';
if(isset($_POST['action']) && $_POST['action']=='GET_CHART') { 
	$timeDay		=	1;
	if(isset($_POST['dataType']) && $_POST['dataType'] != '')
		$date_type			=	$_POST['dataType'];
	if(isset($_POST['timeOfDay']) && $_POST['timeOfDay'] != ''){
		$timeDay			=	$_POST['timeOfDay'];
	}
	//$date_type  = 'day';
	$url					=	WEB_SERVICE.'v1/merchants/topProducts/?DataType='.$date_type.'';
	$curlProductResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlProductResponse) && is_array($curlProductResponse) && $curlProductResponse['meta']['code'] == 201 && is_array($curlProductResponse['TopProducts']) ) {
		if(isset($curlProductResponse['TopProducts']['result'])){
			$product_array	= 	$curlProductResponse['TopProducts']['result'];	
		}
		if(isset($curlProductResponse['TopProducts']['pieChart'])){
			$piechart_array	= 	$curlProductResponse['TopProducts']['pieChart'];	
		}
	} else if(isset($curlProductResponse['meta']['errorMessage']) && $curlProductResponse['meta']['errorMessage'] != '') {
			$errorMessage	=	$curlProductResponse['meta']['errorMessage'];
	} else {
			$errorMessage	= 	"Bad Request";
	} 
	//print_r($product_array);
?>

<?php 

if(isset($product_array) && is_array($product_array) && count($product_array)>0) {
$product_count = count($product_array);
if($product_count == 1)
	$ht = 100;
else if($product_count > 4)
	$ht = 60;
else
	$ht = 100;
//$ht = (((count($product_array))>4)?((count($product_array))*60):((count($product_array))*85));
?>
<div class="col-xs-12 col-sm-12 no-padding" style="padding-top:0px;" >
	
	<div id="bar-canvas" style="height:<?php  echo  $ht*$product_count; ?>px;"></div>
</div>

<?php } else { ?>
	<div class="row clear">		
		 <div align="center" class="alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10">
		 	<i class="fa fa-fw fa-warning"></i>No results found</div>							
	</div>							
<?php } ?>
<style>
	.morris-hover{position:absolute;z-index:300;}
	.morris-hover.morris-default-style{border-radius:10px;padding:6px;color:#666;background:rgba(255, 255, 255, 0.8);border:solid 2px rgba(230, 230, 230, 0.8);font-family:sans-serif;font-size:12px;text-align:center;}
	.morris-hover.morris-default-style .morris-hover-row-label{font-weight:bold;margin:0.25em 0;}
	.morris-hover.morris-default-style .morris-hover-point{white-space:nowrap;margin:0.1em 0;}
	
</style>
<script type="text/javascript">
	<?php if(isset($product_array) && is_array($product_array) && count($product_array)>0) {?>
	window.m = Morris.Bar({
		element: 'bar-canvas',
		data: [
			 <?php  if(isset($product_array) && is_array($product_array)) {
				  			foreach($product_array as $key=>$val){
								$aVal		=	trim(unEscapeSpecialCharacters($val["TotalPrice"]));
								//$yVal		=	trim(unEscapeSpecialCharacters($val["Name"]));
								$yVal		=	ucfirst($val["Name"]);
								
								?>
								
	                  	{y: decodeURI("<?php echo $yVal;?>"),a: '<?php echo $aVal;?>' },
				<?php  }
			}
			
			?>
		],
		xkey		: 'y',
		ykeys		: ['a'],
		labels		: ['&pound'],
		barColors	: ['#01a99a'],
		grid		: false,
		hideHover	: true,
		//axes		: false,
		horizontal 	: true,
		//barGap		: '1',
		barSizeRatio: '0.99',
		stacked		: true,
		redraw		: false,
		resize		: false,
		hoverCallback: function (index, options, content, row) {
			if(row.a != ''){
				var labelVal  	= parseFloat(row.a);
				labelVal		=	labelVal.toFixed(2);
			}else{
				labelVal		= 0;
			}
		  return ""+row.y+"<br>"+"&pound"+labelVal;
		}
	});
	
	<?php } ?>
</script>
<?php }?>
