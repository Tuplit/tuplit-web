<?php 
unset($_SESSION['CategoryListArray']);
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

	$url					=	WEB_SERVICE.'v1/categories/analytics/?DataType='.$date_type.'&Start='.$Start."&TimeZone=".$_SESSION['tuplit_ses_from_timeZone'];
	
	$curlOrderResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlOrderResponse) && is_array($curlOrderResponse) && $curlOrderResponse['meta']['code'] == 201 && isset($curlOrderResponse['CategoryAnalytics']) ) {
		if(isset($curlOrderResponse['CategoryAnalytics'])){
			$CategoryList 	= $curlOrderResponse['CategoryAnalytics'];	
			$TotalCategory 	= $curlOrderResponse['meta']['TotalCategory'];	
		}
	} else if(isset($curlOrderResponse['meta']['errorMessage']) && $curlOrderResponse['meta']['errorMessage'] != '') {
			$errorMessage	=	$curlOrderResponse['meta']['errorMessage'];
	} else {
			$errorMessage	= 	"Bad Request";
	} 
}
?>
<?php
if(isset($CategoryList) && is_array($CategoryList)){
	foreach($CategoryList as $key=>$value){
		$day_arr[$value['CategoryId']]['Morning'] = (isset($value['Morning']['Percentage']) && $value['Morning']['Percentage'] != '' ? $value['Morning']['Percentage'] : 0.3) ; 
		$day_arr[$value['CategoryId']]['Noon'] = (isset($value['Noon']['Percentage']) && $value['Noon']['Percentage'] != '' ? $value['Noon']['Percentage'] : 0.3) ; 
		$day_arr[$value['CategoryId']]['Evening'] = (isset($value['Evening']['Percentage']) && $value['Evening']['Percentage'] != '' ? $value['Evening']['Percentage'] : 0.3) ;
	}	
}

if(isset($CategoryList) && is_array($CategoryList)){
		$_SESSION['CategoryListArray'] =  $CategoryList ;
		foreach($CategoryList as $key=>$value){
			/*$weekList	=	$value['WeekList'];
			if(isset($value['WeekList']) && is_array($value['WeekList']) ) {
				if(count($weekList) > 1){
					$widthVal =  count($weekList) * 50;
				}else{
					$widthVal =  count($weekList) * 80;
				}
			}*/
			$productName	= ((isset($value["CategoryName"]) && $value["CategoryName"] != "")?ucfirst($value["CategoryName"]):'');
			$pdt			= unEscapeSpecialCharacters($productName);
			$pdtName		= escapeSpecialCharacters($pdt);
			$mrng_price		= (isset($value['Morning']['Amount']) && $value['Morning']['Amount'] != '' ? price_fomat($value['Morning']['Amount']) :'') ;
			$noon_price		= (isset($value['Noon']['Amount']) && $value['Noon']['Amount'] != '' ? price_fomat($value['Noon']['Amount']) :'') ;	
			$eve_price		= (isset($value['Evening']['Amount']) && $value['Evening']['Amount'] != '' ? price_fomat($value['Evening']['Amount']) :'') ;
		?>
		<?php if(isset($_GET['action']) && $_GET['action'] == 'GET_MORE_CATEGORY'){ ?>
		
			<?php  
			
			$output[] = 
'<div><h3 class="prod_analy slide">'.$value['CategoryName'].'</h3><img id='.$value['CategoryName'].'  title="'.$value['CategoryName'].'" class="productImage" width="115" height="110" align="top" src="'.$value["ProductImage"].'">
<p class="HelveticaNeueBold">Orders per time of the day</p><div class="col-xs-12 products_canvas"><div id="day_canvas_'.$value['CategoryId'].'" style="width:240px;height:100px;"></div>
<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 no-padding"><span><div style="font-size:9px;">Morning</div><br><div class="morning_per">'.floor($day_arr[$value["CategoryId"]]["Morning"]).'%</div>
<div class="morning_per">'.$mrng_price.'</div></span></div><div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 no-right-pad"><span><div style="font-size:9px;">Noon</div><br><div class="noon_per">'.floor($day_arr[$value["CategoryId"]]["Noon"]).'%</div>
<div class="morning_per">'.$noon_price.'</div></span></div><div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 no-right-pad"><span><div style="font-size:9px;">Evening</div><br><div class="evening_per">'.floor($day_arr[$value["CategoryId"]]["Evening"]).'%</div>
<div class="morning_per">'.$eve_price.'</div></span></div></div><div class="col-xs-12 no-padding"><p class="HelveticaNeueBold">Orders per day in the week</p>
<input type="hidden" id="day_color_'.$value["CategoryId"].'" value="1"><div id="week_canvas_'.$value["CategoryId"].'" style="width:250px;height:100px;"></div></div></div>';
			 ?>
		
		<?php } else{ ?>
			<li class="prod_analy">
				<div class="col-xs-12">
					<h3 class="prod_analy slide"><?php if(isset($value["CategoryName"]) && $value["CategoryName"] != ""){ echo ucfirst($value["CategoryName"]); } ?></h3>
					<img id="<?php echo $value["ProductName"] ;?>"  title="<?php if(isset($value["CategoryName"]) && $value["CategoryName"] != ""){ echo ucfirst($value["CategoryName"]); } ?>" class="merchantImage " width="115" height="110" align="top" src="<?php echo $value["ProductImage"];?>" >
					<div class="line_bottom">
					<p class="HelveticaNeueBold">Orders per time of the day</p>
					<div class="col-xs-12 no-padding">
						<div id="day_canvas_<?php echo $value["CategoryId"]?>" style="width:315px;height:100px;"></div>
					</div>
					<div class="col-xs-12">
					<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
						<?php if($day_arr[$value["CategoryId"]]["Morning"] != ""){ ?>
						<span>Morning<br>
							<div class="morning_per"><?php echo floor($day_arr[$value["CategoryId"]]["Morning"])."%";?></div>
							<div class="dis_amount"><?php echo (isset($value['Morning']['Amount']) && $value['Morning']['Amount'] != '' ? price_fomat($value['Morning']['Amount']) :'') ;?></div>
						</span>
						<?php } ?>
					</div>
					<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
						<?php if($day_arr[$value["CategoryId"]]["Noon"] != ""){ ?>
						<span>Noon<br>
							<div class="noon_per"><?php echo floor($day_arr[$value["CategoryId"]]["Noon"])."%";?></div>
							<div class="dis_amount"><?php echo (isset($value['Noon']['Amount']) && $value['Noon']['Amount'] != '' ? price_fomat($value['Noon']['Amount']) :'') ;?></div>
						</span>
						<?php } ?>
	
					</div>
					<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
						<?php if($day_arr[$value["CategoryId"]]["Evening"] != ""){ ?>
						<span>Evening<br>
							<div class="evening_per"><?php echo floor($day_arr[$value["CategoryId"]]["Evening"])."%";?></div>
							<div class="dis_amount"><?php echo (isset($value['Evening']['Amount']) && $value['Evening']['Amount'] != '' ? price_fomat($value['Evening']['Amount']) :'') ;?></div>
						</span>
						<?php } ?>
					</div>
					</div>
					</div>
					<div class="col-xs-12 no-padding">
						<p class="HelveticaNeueBold">Orders per day in the week</p>
						<input type="hidden" id="day_color_<?php echo $value["CategoryId"];?>" value="1">
						<div id="week_canvas_<?php echo $value["CategoryId"]?>" style="width:<?php echo '250';//$widthVal; ?>px;height:100px;"></div>  
					</div>				
				</div>
			</li>
		
		<?php } ?>
		 <?php 
		  } $i++;
	if(isset($output) &&  count($output) > 0){
		$resultArray['result'] 	= $output;
		$resultArray['total'] 	= $TotalCategory;
		echo trim(json_encode($resultArray));
	}	 
}else{
	echo 'fails';
}
?>
