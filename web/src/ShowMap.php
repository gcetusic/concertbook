<html> 
<head> 
<style>
      #map-canvasa {
        width: 100%;
        height: 100%;
      }
    </style>
<script src="https://maps.googleapis.com/maps/api/js"></script>
<title>ConcertBook</title>
<meta content="text/html; charset=UTF-8" http-equiv="content-type">
<link rel="stylesheet" type="gn/css" href="design.css">
</head>
<body>

<?php
#prikaz lokacije koncerta, obližnih barova i restorana te preporučenih mjesta
error_reporting(E_ALL ^ E_WARNING); 
require_once( 'SendingRequests.php' );
require_once( 'ProcessData.php' );
session_start();

if( $_GET['lat'] && $_GET['long']){
	$latitude = $_GET['lat'];
	$longitude = $_GET['long'];
	$name = $_GET['name'];
	$start = $_GET['start'];
	$city = $_GET['city'];
	echo '<a href="ArtistInfo.php?q='.$_GET['artistName'].'&maxResults=3">Povratak</a>';
	echo '<div id="map-canvasa"></div>';
	$processData = new ProcessData();
	$sendingRequests = new SendingRequests();
	
	#dohvaćanje barova i restorana
	$response = $sendingRequests ->getBarsAndRestaurants($latitude, $longitude);
	$barsAndRestaurants = array();
	foreach($response as $barAndRestaurant){
		$barAndRestaurant = get_object_vars($barAndRestaurant);
		$barAndRestaurant = array("name"=>$barAndRestaurant['name'], "lat"=>$barAndRestaurant['latitude'], "long"=>$barAndRestaurant['longitude'], "street"=>$barAndRestaurant['location'][0]);
		array_push($barsAndRestaurants, $barAndRestaurant); 
	}
	
	#dohvaćanje korisnikovih lokacija iz baze podataka
	$locations = $processData ->getUserLocations($_SESSION['userId']);
	$locations = $locations['locations'];
	$similar = 'event='. $latitude . ',' .$longitude . '&locations=';
	foreach ($locations as $location){
		$similar = $similar. $location['latitude'].','.$location['longitude'] . ':';
	}
	#dohvaćanje preporučenih mjesta
	$similar = rtrim($similar, ":");
	$response = $sendingRequests ->getReccomendedPlaces($similar);
	$recommendedPlaces = array();
	foreach($response as $recommended){
		$recommended = get_object_vars($recommended);
		$recommended = array("name"=>$recommended['name'], "lat"=>$recommended['latitude'], "long"=>$recommended['longitude'], "street"=>$recommended['location'][0]);
		array_push($recommendedPlaces, $recommended); 
	}
}
?>
<script type="text/javascript">
	/* funkcija uzima podatke iz php dijela koda o lokaciji nastupa, preporučenim mjestima i obližnjim barovima i restoranima i iscrtava ih na mapi */
	function initialize() {
		var markersBar =<?php echo json_encode($barsAndRestaurants ); ?>;
		var lat =<?php echo json_encode($latitude ); ?>;
		var lon =<?php echo json_encode($longitude ); ?>;
		var name =<?php echo json_encode($name ); ?>;
		var startDate =<?php echo json_encode($start ); ?>;
		var city =<?php echo json_encode($city ); ?>;
		var latlng = new google.maps.LatLng(lat, lon);
		var myOptions = {
			zoom: 15,
			center: latlng,
			mapTypeId: google.maps.MapTypeId.ROADMAP,
			mapTypeControl: false
		};
		var map = new google.maps.Map(document.getElementById("map-canvasa"),myOptions);
		
		var infowindow = new google.maps.InfoWindow(), marker, i;
		
		/* iscrtavanje barova i restorana s info o lokaciji */
		for (i = 0; i < Object.keys(markersBar).length; i++) {  
			marker = new google.maps.Marker({
				position: new google.maps.LatLng(markersBar[i]["lat"], markersBar[i]["long"]),
				map: map,
					icon:'http://maps.google.com/mapfiles/ms/icons/blue-dot.png'
			});
			google.maps.event.addListener(marker, 'click', (function(marker, i) {
				return function() {
					infowindow.setContent('<div style="width:200px"><p><b>' + markersBar[i]["name"] + '</b></p>' +  markersBar[i]["street"] + '</div>');
					infowindow.open(map, marker);
				}
			})(marker, i));
			}
			
		/* iscrtavanje preporučenih mjesta s info o lokaciji */	
		var markersLocations =<?php echo json_encode($recommendedPlaces); ?>;
		
		for (var j = 0; j < Object.keys(markersLocations).length; j++) {  
			marker = new google.maps.Marker({
				position: new google.maps.LatLng(markersLocations[j]["lat"], markersLocations[j]["long"]),
				map: map,
				icon:'http://maps.google.com/mapfiles/ms/icons/green-dot.png'
			});
			google.maps.event.addListener(marker, 'click', (function(marker, j) {
				return function() {
					infowindow.setContent('<div style="width:200px"><p><b>' + markersLocations[j]["name"] + '</b></p>' +  markersLocations[j]["street"] + '</div>');
					infowindow.open(map, marker);
				}
			})(marker, j));
		} 
		
		/* iscrtavanje lokacije koncerta s info o lokaciji */	
		var myLatlng = new google.maps.LatLng(lat, lon);
		var contentString = '<div style="width:200px"><p><b>' + name + '</b></p><br>'+ startDate + '<br>' + city + '</div>';
		var infowindow = new google.maps.InfoWindow({
			content: contentString
		});
		var marker = new google.maps.Marker({
			position: myLatlng,
			map: map,
			title: contentString
		});
		google.maps.event.addListener(marker, 'click', function() {
		infowindow.open(map,marker);
		});	
	}	
	google.maps.event.addDomListener(window, 'load', initialize);
</script>
</body> 
</html>


