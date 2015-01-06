<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
require_once('controllers/AdminController.php');
$msg = '';
require_once('controllers/MerchantController.php');
$merchantObj   	=   new MerchantController();
require_once('controllers/ManagementController.php');
$managementObj  =   new ManagementController();
require_once('controllers/AnalyticsController.php');
$analyticsObj   =   new AnalyticsController();
$cond 		=	$field  = $loc_cond =  $having ='';
function address_formation($val1,$val2,$val3,$val4,$val5){
	$address  = '';
	if($val1!=''){
		if($val2=='' && $val3=='')
			$address .= $val1.'<br>';
		else
			$address .= $val1.', ';
	}
	if($val2!=''){
		if($val3=='')
			$address .= $val2.'<br>';
		else	
			$address .= $val2.', ';
	}
	if($val3!='')
		$address .= $val3.'<br>';
	if($val4!='' && $val5!='')
		$address .= $val4.'- ';
	else
		$address .= $val4;
	if($val5!='')
		$address .= $val5;
	return $address;
}
if(isset($_GET['cs']) && $_GET['cs'] == 1){
 	//unset($_SESSION['loc_mer_name']);
	//unset($_SESSION['mer_sess_Category']);
	//unset($_SESSION['merchant_sess_city']);
	//unset($_SESSION['merchant_sess_price']);
	//unset($_SESSION['mer_sess_name']);
	unset($_SESSION['ordertype']);
	unset($_SESSION['sortBy']);
	//unset($_SESSION['loc_mer_price']);
	//unset($_SESSION['loc_mer_city']);
	//unset($_SESSION['loc_mer_category']);
}

/*----Merchant search----------------------*/
$condition       	= "  Status =1 order by CompanyName asc";
$field				=	' id,CompanyName';
$MerchantList		= 	$merchantObj->selectMerchantDetails($field,$condition);

/*Category search--------------------------*/
$condition       	= 1;
$field				='id,CategoryName';
$CategoryList  = $managementObj->selectCategoryDetails($field,$condition);

/*City search-----------------------------*/
$condition       	= " City<>''  group by City order by City asc";
$field				=	' City';
$CityList		= 	$merchantObj->selectMerchantDetails($field,$condition);

if(isset($_POST) && !empty($_POST)){
	
	if(isset($_POST['Merchant_Name']) && trim($_POST['Merchant_Name'])!='')
		$_SESSION['loc_mer_name'] = $_POST['Merchant_Name'];
	else
		unset($_SESSION['loc_mer_name']);
		
	if(isset($_POST['Merchant_Category']) && trim($_POST['Merchant_Category'])!='')
		$_SESSION['loc_mer_category'] = $_POST['Merchant_Category'];
	else
		unset($_SESSION['loc_mer_category']);
		
	if(isset($_POST['Merchant_city']) && trim($_POST['Merchant_city'])!='')
		$_SESSION['loc_mer_city'] = $_POST['Merchant_city'];
	else
		unset($_SESSION['loc_mer_city']);
		
	if(isset($_POST['Merchant_Price']) && trim($_POST['Merchant_Price'])!='')
		$_SESSION['loc_mer_price'] = $_POST['Merchant_Price'];
	else
		unset($_SESSION['loc_mer_price']);
 }
 if(isset($_POST['Search']) && is_array($_POST) && !empty($_POST)){
 	
	$fields 		= 'm.Latitude,m.Longitude,m.CompanyName,m.Street,m.City,m.State,m.Country,m.PostCode';
	$cond			= '';
	$join			= '';
	if(isset($_SESSION['loc_mer_name']) && trim($_SESSION['loc_mer_name'])!=''){
		$cond .= " and m.id = ".$_SESSION['loc_mer_name'];
		$loc_cond .= " and m.id = ".$_SESSION['loc_mer_name'];
	}
	if(isset($_SESSION['loc_mer_category']) && trim($_SESSION['loc_mer_category'])!=''){
		$cond .= " and mca.fkCategoriesId =".$_SESSION['loc_mer_category'];
		$loc_cond .=  " and c.id = ".trim($_SESSION['loc_mer_category']);
		$join  = "category";
	}
	if(isset($_SESSION['loc_mer_city']) && trim($_SESSION['loc_mer_city'])!=''){
		$cond .= $loc_cond .= " and m.City like '%".$_SESSION['loc_mer_city']."%'";
		
	}	
	if(isset($_SESSION['loc_mer_price']) && trim($_SESSION['loc_mer_price'])!=''){
		$cond .= " and '".$_SESSION['loc_mer_price']."' between substring_index(m.PriceRange,',',1) and substring_index(m.PriceRange,',',-1)"; 
		$having	.= " Having TotalPrice <= '".trim($_SESSION['loc_mer_price'])."'";
	}
	if($cond!='')
		$result	= $merchantObj->getMerchantLocation($fields,$cond,$join);	
}
if(isset($result) && is_array($result)){
	//echo "<br>";print_r($result);echo "</br>";
	$Address = '';
	foreach($result as $key=>$value){
		$Address .= '- '.$value->PostCode;
		$latlng[$key]['Latitude']	= $value->Latitude;
		$latlng[$key]['Longitude']	= $value->Longitude;
		$latlng[$key]['CompanyName']= $value->CompanyName;
		$latlng[$key]['Address']	= address_formation($value->Street,$value->City,$value->State,$value->Country,$value->PostCode);
	}		
	//echo "<br>";print_r($latlng);echo "</br>";
}
$fields				= "group_concat(fkUsersId) as userArray,c.CategoryName,mc.fkCategoriesId,count(o.id) as transCount,count(distinct fkUsersId) as uniqueCustomers,fkMerchantsId,sum(TotalPrice) as TotalPrice,Min(OrderDate) as FirstTrans,m.City";
$locationResult		=  $analyticsObj->getLocationReport($fields,$loc_cond,$having);	 
commonHead(); 
?>

<?php top_header(); ?>
<body class="skin-blue">
<?php 
$formStatus = 1;
require_once("ReportSearchBox.php");
?>
<section class="content">
	<div class="row">
		<div class=" col-xs-12">
			<div class="box box-primary no-padding">
				<div class="box-body" id="map" style="padding-top: 15px">
					<div id="map-canvas" style="height:550px;width:100%;" class="no-padding box-body"></div>
				</div>
			</div>			
		</div>
	</div>
	<div class="row">
		<div class="Category-manage sett-menu clear col-md-12"><!-- LOCATION BREAKDOWN START-->
			<div class="box box-primary">
				<?php $displayList = 1;require_once('LocationBreakdownList.php');?>
			</div>
		</div>
	</div>
</section>
<?php commonFooter(); ?>
<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=places"></script>
<script>
var map;
var infowindow = new google.maps.InfoWindow();
var div = document.getElementById('map-canvas');  
/*
	var map_prop = [{    
		Latitude 	: 13.082540019708,    
		Longitude 	: 80.271348980292,    
		CompanyName	: 'Testing',    
		Address		: 'Chennai'
	}];
*/	
var styles 	= [{
		featureType : 'water',		
		elementType : 'all',    
		stylers : [{hue : '#D8E8E7'},{saturation : -30},{lightness :10}, {visibility : 'simplified'}]	
	},{			
		featureType : 'landscape',		
		elementType : 'all',		
		stylers : [{hue : '#E3EDED'}, {saturation : -30}, {lightness : 10}, {visibility : 'simplified'}]		
	},{			
		featureType : 'road.highway',		
		elementType : 'all',		
		stylers : [{hue : '#D8E8E7'}, {saturation : 66}, {lightness : 10}, {visibility : 'simplified'}]		
	},{			
		featureType : 'poi',		
		elementType : 'all',		
		stylers : [{hue : '#D8E8E7'}, {saturation : -30}, {lightness : 10}, {visibility : 'simplified'}]		
	},{		
		featureType : 'administrative',		
		elementType : 'all',		
		stylers : [{visibility : 'on'}]		
	},{		
	  featureType : 'administrative.locality',			
	  elementType : 'all',			
	  stylers : [{visibility : 'on'}]		
	}];   
function initialize(map_prop){
	if(map_prop!='')
		var pyrmont = new google.maps.LatLng(map_prop[0].Latitude,map_prop[0].Longitude);
	else	
		var pyrmont = new google.maps.LatLng(13.082540019708,80.271348980292);
	var options = {    
		mapTypeControlOptions : {mapTypeIds : ['Styled']},    
		center : pyrmont,
		zoom	:5,
		mapTypeId : 'Styled'
	};	

	var map = new google.maps.Map(div, options);    
	var styledMapType = new google.maps.StyledMapType(styles, {name : 'Styled'});    
	map.mapTypes.set('Styled', styledMapType);
	var bounds = new google.maps.LatLngBounds();
	
	if(map_prop!='' && $.isArray(map_prop) && map_prop.length>0){
		for(var i=0;i < map_prop.length;i++){
			var marker = new google.maps.Marker({
				position: new google.maps.LatLng(map_prop[i].Latitude,map_prop[i].Longitude),
				map		: map,
				html	: "<div id='content' style='width:250px;height:85px;line-height:12px;'><p style='font-size:12;font-weight:bold;color:#000;'>"+map_prop[i].CompanyName+"<br>"+map_prop[i].Address+"</div>"
			});
			bounds.extend(marker.position);
			google.maps.event.addListener(marker, 'click', function() {	
				infowindow.setContent(this.html);
				infowindow.open(map, this);
			});
		}
		map.fitBounds(bounds);	
	}
}
<?php if(isset($latlng) && is_array($latlng)){?>
		var latlng = <?php echo json_encode($latlng) ?>; 
		initialize(latlng);
<?php }else{?>
google.maps.event.addDomListener(window, 'load', initialize(map_prop=''));
<?php }?>
</script>