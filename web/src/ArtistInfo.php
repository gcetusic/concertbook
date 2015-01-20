<html> 
<head> 
<style>
      #map-canvas {
        width: 70%;
        height: 60%;
      }
    </style>
<script src="https://maps.googleapis.com/maps/api/js"></script>
<title>ConcertBook</title>
<meta content="text/html; charset=UTF-8" http-equiv="content-type">
<link rel="stylesheet" type="gn/css" href="design.css">
</head>
<body>
<?php
#prikaz svih informacija o glazbeniku i lokacija nadolazecih koncerata
error_reporting(E_ALL ^ E_WARNING); 
require_once( 'YoutubeFetching.php' );
require_once( 'ProcessData.php' );
require_once( 'SendingRequests.php' );
session_start();
	
if( $_GET['maxResults'] && $_GET['q']){
	$youtubeFetch = new YoutubeFetching();
	$processData = new ProcessData();
	$sendingRequests = new SendingRequests();
	
	#dohvaćanje top 3 videa s YouTubea
	$searchedVideos = $youtubeFetch -> getMusicVideos($_GET['q'], $_GET['maxResults']);
	$artistName = $_GET['q'];
	
	#provjerava se ima li glazbenik vec spremljene dodatne informacije, ako nema one se dohvacaju sa servera
	$hasInfo = $processData -> checkArtistInfo($artistName);
	if($hasInfo== false){
		$response = $sendingRequests ->getAdditionalInfo($artistName);
		$processData -> storeAdditionalArtistInfo($artistName, $response);
	}
	$artistInfo = $processData -> getArtistInfo($artistName);
	
	
	echo '<div id="artist">';
	echo '<img style="width:30%;display: block;margin-left: auto;margin-right: auto" src='.$artistInfo['image'].' border="0" alt='.$artistInfo["image"].'><br>';
	echo '<p style="font-size:200%"><b>'. $artistInfo['name'] . '</b></p>';
	echo '</div>';
	echo '<div id="about"><p style="font-size:150%"><b>O izvođaču:</b></p>';
	
	#ispis zivotopisa glazbenika
	echo $artistInfo['about'].'<br></div>';
	
	#ispis tagova glazbenika
	echo '<div id= "tags"><p style="font-size:120%"><b>Tagovi:</b></p>';
	$tags = '';
	foreach($artistInfo['tags'] as $tag){
		$tags = $tags . $tag. '; ';
		}
	$tags = substr($tags, 0, strlen($tags)-2);
	echo $tags;
	echo '</div><br>';
	
	#ispis glazbenikovih albuma
	echo '<div id= "albums"><p style="font-size:120%"><b>Albumi:</b></p>';
	echo '<table style="width:100%;"><tr style="width:100%">';
	$number = 0;
	foreach($artistInfo['albums'] as $album){
		$number++;
		echo '<td style="width:12.5%"><img style="width:100%" src='.$album["cover_image"].' border="0" alt='.$album["cover_image"].'>';
		echo '<p style="text-align:center">'.$album["name"].'</p><br></td>';
		if ($number%8 == 0)
				echo '</tr><tr>';
	}
	echo '</tr></table>';
	echo '</div>';
	
	#ispis top 3 videa
	echo '<div id= "videos"><p style="font-size:120%"><b>Top 3 YouTube videa:</b></p>';
	echo '<div id="centerVideos">';
	foreach ($searchedVideos['items'] as $searchResult){
		if ($searchResult['id']['kind'] == 'youtube#video'){
			$data = $searchResult['id']['videoId'];
			echo '<iframe width="350" height="197" src="//www.youtube.com/embed/'.$data,'" frameborder="0"></iframe>';
		}
	} 
	echo '</div>';
	echo '</div>';
	
	#ukoliko postoji varjabla sjednice 'already', zaci da su vec dohvaceni podaci o nadolazecim nastupima glazbenika pa se ne salje ponovno zahtjev na servera
	#ako ne postoji, salje se zahtjev za dohvacanje nadolazecih dogadjaja
	if ($_SESSION['already'] ==0){
		$response = $sendingRequests ->getArtistEvents($artistName);
		$events = array();
		foreach ($response as $event){
			$event = get_object_vars($event);
			$time = strtotime($event['start']);
			$venue = $event['venue'];
			$venue = get_object_vars($venue);
			$location = get_object_vars($venue['location']);
			$eventToArray= array("start"=>date('Y-m-d',$time), "url"=>$event['url'], "city"=>$venue['city'], "name"=>$event['title'], "latitude"=>$location['geo:lat'], "longitude"=>$location['geo:long'] );
			array_push($events, $eventToArray); 
		}
		$_SESSION['already'] =1;
		$_SESSION['events'] =$events;
	}
	else	
		$events = $_SESSION['events'];
	echo '</div>';
	
	#omogucavanje pretrazivanja dogadjaja po datumima
	echo '<div id= "searching"><p style="font-size:120%"><b>Pretraži događaje po datumima</b></p>';
	echo '<form>OD <input type="date" id="from" value = "'. date('Y-m-d').'"> ';
	echo '<form>DO <input type="date" id="to" value = "'. date('Y-m-d').'"></form>';
	echo '<div id ="submit" onclick="initializeTwo()"><b>Pretraži!</b></div>';
	echo '</div>';
	echo '<div id="map-canvas"></div>';
	
	#dohvacanje korisnikovih lokacija iz baze podataka
	$locations = $processData ->getUserLocations($_SESSION['userId']);
	$locations = $locations['locations'];
	echo '<div id="return"><a href="choosingArtist.php"><b>Povratak</b></a></div>'; 
}
else {
		header("Location: Youtube");
	}
?>
<script type="text/javascript">

/* osnovna funkcija za iscrtavanje mape na kojoj se na početku nalaze samo korisnikova posjećena mjesta, označena zelenim markerom */
function initialize() {
	var markers =<?php echo json_encode($locations ); ?>;
    var latlng = new google.maps.LatLng(45.80112099999999, 15.970841000000064);
    var myOptions = {
        zoom: 5,
        center: latlng,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        mapTypeControl: false
    };
    var map = new google.maps.Map(document.getElementById("map-canvas"),myOptions);
	
    var infowindow = new google.maps.InfoWindow(), marker, i;
	
	/* iscrtavanje markera događaja s inf. o lokaciji */
    for (i = 0; i < Object.keys(markers).length; i++) {  
        marker = new google.maps.Marker({
            position: new google.maps.LatLng(markers[i]["latitude"], markers[i]["longitude"]),
            map: map,
			icon:'http://maps.google.com/mapfiles/ms/icons/green-dot.png'
        });
        google.maps.event.addListener(marker, 'click', (function(marker, i) {
            return function() {
                infowindow.setContent('<div style="width:200px"><p>' + markers[i]["name"] + '</p></div>');
                infowindow.open(map, marker);
            }
        })(marker, i));
    }
}
/* funkcija uz korisnikova mjesta, iscrtava i lokacije koncerata koji zadovoljavaju uvjete, označeni crvenom bojom */	
function initializeTwo() {
	var fromDate = document.getElementById('from').value;
	var toDate = document.getElementById('to').value;
	var from = new Date(fromDate);
	var to = new Date(toDate);
	var artistName = <?php echo json_encode($artistName); ?>;
	
	/* provjerava se ispravnost od - do datuma */	
	if (to<from){
		alert ('Unesite ispravne datume');
	}
	else{
		var markers =<?php echo json_encode($events ); ?>;
		var latlng = new google.maps.LatLng(45.80112099999999, 15.970841000000064);
		var myOptions = {
			zoom: 5,
			center: latlng,
			mapTypeId: google.maps.MapTypeId.ROADMAP,
			mapTypeControl: false
		};
		var map = new google.maps.Map(document.getElementById("map-canvas"),myOptions);
		
		var infowindow = new google.maps.InfoWindow(), marker, i;
		
		/* iscrtavanje markera događaja s inf. o lokaciji */	
		for (i = 0; i < Object.keys(markers).length; i++) {  
			var dateConcert = new Date(markers[i]['start']);
			if (dateConcert >= from && dateConcert <= to){
				marker = new google.maps.Marker({
					position: new google.maps.LatLng(markers[i]["latitude"], markers[i]["longitude"]),
					map: map
				});
				google.maps.event.addListener(marker, 'click', (function(marker, i) {
					return function() {
						var linkTo = 'ShowMap.php?lat=' +markers[i]["latitude"] + '&long='+markers[i]["longitude"] + '&name='  + markers[i]["name"] + '&start=' + markers[i]["start"] + '&city=' + markers[i]["city"] + '&artistName=' + artistName;
						infowindow.setContent('<div style="width:200px"><p><b><a href ="' + linkTo + '">' + markers[i]["name"] + '</a></b></p><br>'+ markers[i]["start"] + '<br>' + markers[i]["city"]+ '</div>');
						infowindow.open(map, marker);
					}
				})(marker, i));
			}
		}
		
		/* iscrtavanje markera posjećenih mjesta s inf. o lokaciji */
		var markersLocations =<?php echo json_encode($locations ); ?>;
		for (var j = 0; j < Object.keys(markersLocations).length; j++) {  
			marker = new google.maps.Marker({
				position: new google.maps.LatLng(markersLocations[j]["latitude"], markersLocations[j]["longitude"]),
				map: map,
				icon:'http://maps.google.com/mapfiles/ms/icons/green-dot.png'
			});
			google.maps.event.addListener(marker, 'click', (function(marker, j) {
				return function() {
					infowindow.setContent('<div style="width:200px"><p><b>' + markersLocations[j]["name"] + '</b></p></div>');
					infowindow.open(map, marker);
				}
			})(marker, j));
		} 
	}	
}

	google.maps.event.addDomListener(window, 'load', initialize);
    </script>
</body> </html>