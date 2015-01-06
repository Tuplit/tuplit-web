<?php 
unset($_SESSION['ProductListArray']);
if(isset($_GET['action']) && $_GET['action'] == 'GET_MORE_CATEGORY'){ 
	$Start 	= $TotalProducts = $i =  0;
	$ProductList =  $resultArray = Array();
	if(isset($_GET['Start']) && !empty($_GET['Start'])) {
		$Start	=	$_GET['Start'];
	}
	if(isset($_GET['datetype']) && !empty($_GET['datetype'])) {
		$date_type							=	$_GET['datetype'];
		$_SESSION['TuplitAnalyticsView']	=	$date_type;
	} else 	if(isset($_SESSION['TuplitAnalyticsView']) && !empty($_SESSION['TuplitAnalyticsView']))
		$date_type							=	$_SESSION['TuplitAnalyticsView'];
	else {
		$date_type							=	'month';
		$_SESSION['TuplitAnalyticsView']	=	'month';
	}
	
	//getting merchant details
	if(isset($_SESSION['merchantDetailsInfo']) && is_array($_SESSION['merchantDetailsInfo'])){
		$merchantInfo  =	$_SESSION['merchantDetailsInfo'];	
	}
	else{
		$merchantId					= 	$_SESSION['merchantInfo']['MerchantId'];
		$url						=	WEB_SERVICE.'v1/merchants/'.$merchantId;
		$curlMerchantResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);	
		if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201 && $curlMerchantResponse['merchant']['MerchantId'] != '' ) 
		 {
			$_SESSION['merchantDetailsInfo']   =	$curlMerchantResponse['merchant'];
			$merchantInfo  =	$_SESSION['merchantDetailsInfo'];
		}
	}
	if(!isset($_SESSION['tuplit_ses_from_timeZone']) || $_SESSION['tuplit_ses_from_timeZone'] == ''){
		 $time_zone = getTimeZone();
		 $_SESSION['tuplit_ses_from_timeZone'] = strval($time_zone);	 
	}
	$url					=	WEB_SERVICE.'v1/products/analytics/?DataType='.$date_type.'&Start='.$Start."&TimeZone=".$_SESSION['tuplit_ses_from_timeZone'];
	$curlOrderResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlOrderResponse) && is_array($curlOrderResponse) && $curlOrderResponse['meta']['code'] == 201 && isset($curlOrderResponse['ProductAnalytics']) ) {
		if(isset($curlOrderResponse['ProductAnalytics'])){
			$ProductList 	= $curlOrderResponse['ProductAnalytics'];	
			$TotalProducts 	= $curlOrderResponse['meta']['TotalProducts'];	
		}
	} else if(isset($curlOrderResponse['meta']['errorMessage']) && $curlOrderResponse['meta']['errorMessage'] != '') {
			$errorMessage	=	$curlOrderResponse['meta']['errorMessage'];
	} else {
			$errorMessage	= 	"Bad Request";
	} 
}
	foreach($ProductList as $key=>$value){
		$day_arr[$value['ProductID']]['Morning'] = (isset($value['Morning']['Percentage']) && $value['Morning']['Percentage'] != '' ? $value['Morning']['Percentage'] : 0.3) ; 
		$day_arr[$value['ProductID']]['Noon'] = (isset($value['Noon']['Percentage']) && $value['Noon']['Percentage'] != '' ? $value['Noon']['Percentage'] : 0.3) ; 
		$day_arr[$value['ProductID']]['Evening'] = (isset($value['Evening']['Percentage']) && $value['Evening']['Percentage'] != '' ? $value['Evening']['Percentage'] : 0.3) ;
		
	}	


if(is_array($ProductList)){
		$_SESSION['ProductListArray'] =  $ProductList ;
		foreach($ProductList as $key=>$value){
			/*$weekList	=	$value['WeekList'];
			if(isset($value['WeekList']) && is_array($value['WeekList']) ) {
				if(count($weekList) > 1){
					$widthVal =  count($weekList) * 50;
				}else{
					$widthVal =  count($weekList) * 80;
				}
			}*/
			$productName	= ((isset($value["ProductName"]) && $value["ProductName"] != "")?ucfirst($value["ProductName"]):'');
			$pdt			= unEscapeSpecialCharacters($productName);
			$pdtName		= escapeSpecialCharacters($pdt);
			$mrng_price		= (isset($value['Morning']['DisAmount']) && $value['Morning']['DisAmount'] != '' ? price_fomat($value['Morning']['DisAmount']) :'') ;
			$noon_price		= (isset($value['Noon']['DisAmount']) && $value['Noon']['DisAmount'] != '' ? price_fomat($value['Noon']['DisAmount']) :'') ;	
			$eve_price		= (isset($value['Evening']['DisAmount']) && $value['Evening']['DisAmount'] != '' ? price_fomat($value['Evening']['DisAmount']) :'') ;
		?>
		<?php if(isset($_GET['action']) && $_GET['action'] == 'GET_MORE_CATEGORY'){ ?>
			<?php  
			$output[] = 
'<div class="col-xs-12"><h3 class="col-xs-12 prod_analy_name">'.$value['ProductName'].'</h3><img id='.$value['ProductName'].'  title="'.$value['ProductName'].'" class="productImage" width="115" height="110" align="top" src="'.$value["ProductImage"].'">
<div class="line_bottom"><p class="HelveticaNeueBold">Orders per time of the day</p><div class="col-xs-12 no-padding"><div id="day_canvas_'.$value['ProductID'].'" class="chart_canvas"></div></div>
<div class="col-xs-12">
<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 no-padding"><span><div style="font-size:9px;">Morning</div><div class="morning_per">'.floor($day_arr[$value["ProductID"]]["Morning"]).'%</div>
<div class="dis_amount">'.$mrng_price.'</div></span></div><div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 no-padding"><span><div style="font-size:9px;">Noon</div><div class="noon_per">'.floor($day_arr[$value["ProductID"]]["Noon"]).'%</div>
<div class="dis_amount">'.$noon_price.'</div></span></div><div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 no-padding"><span><div style="font-size:9px;">Evening</div><div class="evening_per">'.floor($day_arr[$value["ProductID"]]["Evening"]).'%</div>
<div class="dis_amount">'.$eve_price.'</div></span></div></div></div><div class="col-xs-12 no-padding"><p class="HelveticaNeueBold">Orders per day of the week</p>
<input type="hidden" id="day_color_'.$value["ProductID"].'" value="1"><div id="week_canvas_'.$value["ProductID"].'" style="width:250px;height:100px;"></div></div></div>';
			?>
		<?php } else{ ?>
			<li>
				<div class="col-xs-12">
					<h3 class="col-xs-12 prod_analy_name"><?php if(isset($value["ProductName"]) && $value["ProductName"] != ""){ echo ucfirst($value["ProductName"]); } ?></h3>
					<img id="<?php echo $value["ProductName"] ;?>"  title="<?php if(isset($value["ProductName"]) && $value["ProductName"] != ""){ echo ucfirst($value["ProductName"]); } ?>" class="productImage " width="115" height="110" align="top" src="<?php echo $value["ProductImage"];?>" >
					<div class="line_bottom">
						<p class="HelveticaNeueBold">Orders per time of the day</p>
						<div class="col-xs-12 no-padding">
						<div id="day_canvas_<?php echo $value["ProductID"]?>" class="chart_canvas"></div>
						</div>

						<div class="col-xs-12">
							<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 no-padding">
								<?php if($day_arr[$value["ProductID"]]["Morning"] != ""){ ?>
								<span><div style="font-size:9px;">Morning</div>
									<div class="morning_per"><?php echo floor($day_arr[$value["ProductID"]]["Morning"])."%";?></div>
									<div class="dis_amount"><?php echo (isset($value['Morning']['DisAmount']) && $value['Morning']['DisAmount'] != '' ? price_fomat($value['Morning']['DisAmount']) :'') ;?></div>
								</span>
								<?php } ?>
							</div>
							<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 no-padding">
								<?php if($day_arr[$value["ProductID"]]["Noon"] != ""){ ?>
								<span><div style="font-size:9px;">Noon</div>
									<div class="noon_per"><?php echo floor($day_arr[$value["ProductID"]]["Noon"])."%";?></div>
									<div class="dis_amount"><?php echo (isset($value['Noon']['DisAmount']) && $value['Noon']['DisAmount'] != '' ? price_fomat($value['Noon']['DisAmount']) :'') ;?></div>
								</span>
								<?php } ?>
			
							</div>
							<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 no-padding">
								<?php if($day_arr[$value["ProductID"]]["Evening"] != ""){ ?>
								<span><div style="font-size:9px;">Evening</div>
									<div class="evening_per"><?php echo floor($day_arr[$value["ProductID"]]["Evening"])."%";?></div>
									<div class="dis_amount"><?php echo (isset($value['Evening']['DisAmount']) && $value['Evening']['DisAmount'] != '' ? price_fomat($value['Evening']['DisAmount']) :'') ;?></div>
								</span>
								<?php } ?>
							</div>
						</div>
					</div>
					<div class="col-xs-12 no-padding">
						<p class="HelveticaNeueBold">Orders per day in the week</p>
						<input type="hidden" id="day_color_<?php echo $value["ProductID"];?>" value="1">
						<div id="week_canvas_<?php echo $value["ProductID"]?>" style="width:<?php echo '250'//$widthVal; ?>px;height:100px;"></div>  
					</div>				
				</div>
			</li>
		
		<?php } ?>
		
		 <?php 
		  } $i++; 
		 
	if(isset($output) &&  count($output) > 0){
		$resultArray['result'] 	= $output;
		$resultArray['total'] 	= $TotalProducts;
		echo trim(json_encode($resultArray));
	} ?>
<?php 
}else{
	echo 'fails';
}
?>
