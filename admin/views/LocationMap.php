
<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=places"></script>
<script>
var map;
var infowindow;
function initialize() {
<?php if(isset($latlng) && $latlng!=''){ ?>
var lat 	= <?php echo $latlng[0]->Latitude;?>,
	lng 	= <?php echo $latlng[0]->Longitude;?>;
	alert(lat);
	alert(lng);
<?php }else{ ?>
	lat		= 13.082540019708,
	lng 	= 80.271348980292;
<?php }?>	
var styles = [{
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
	}, {		
	  featureType : 'administrative.locality',			
	  elementType : 'all',			
	  stylers : [{visibility : 'on'}]		
	}];   
	pyrmont = new google.maps.LatLng(lat,lng);	
var options = {    
	  mapTypeControlOptions : {mapTypeIds : ['Styled']},    
	  center : pyrmont,    
	  zoom : 15,    
	  mapTypeId : 'Styled'
	};	
	var div = document.getElementById('map-canvas');    
		var map = new google.maps.Map(div, options);    
		var styledMapType = new google.maps.StyledMapType(styles, {name : 'Styled'});    
	map.mapTypes.set('Styled', styledMapType);
	//infowindow = new google.maps.InfoWindow();
<?php  if(isset($latlng) && is_array($latlng) && $latlng!=''){
			foreach($latlng as $key=>$value){?>
				var marker = new google.maps.Marker({
				position: new google.maps.LatLng(<?php echo $value->Latitude;?>,<?php echo $value->Longitude;?>),
				map: map,
			});
			google.maps.event.addListener(marker, 'click', function() {
			var contentString = "<div id='content' style='width:200px;height:50px;'><h1 style='font-size:12;font-weight:bold;color:#000;'><?php echo $value->CompanyName;?></h1><h1 style='font-size:11;color:#000;'>Address: <?php echo $value->Address;?></h1></div>";
			var infowindow = new google.maps.InfoWindow({content: contentString });
			infowindow.open(map, this);
		});
<?php 		}
		}
?>

  var request = {
    location: pyrmont,
    radius: 500,
    types: ['store'] 
  };
  /*var service = new google.maps.places.PlacesService(map);
  service.nearbySearch(request);*/
}
google.maps.event.addDomListener(window, 'load', initialize);

</script>