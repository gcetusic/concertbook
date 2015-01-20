<?php
error_reporting(E_ALL ^ E_WARNING); 
#klasa za dohvaćanje podataha sa servera
class SendingRequests{
	
	#dohvaćanje podatnih informacija o glazbeniku (about, tagovi, albumi..)
	public function getAdditionalInfo($artistName){
		$request = $artistName;
		$request = urlencode ($request);
		$request = "http://188.166.13.174:8000/artists?artists=".$request;
		$response = json_decode(file_get_contents($request));
		$response = $response[0];
		$response = get_object_vars($response);
		return $response;
	}
	
	#dohvaćanje nadolazecih nastupa glazbenika
	public function getArtistEvents($artistName){
		$request = $artistName;
		$request = urlencode ($request);
		$request = "http://188.166.13.174:8000/events?artists=".$request;
		$response = json_decode(file_get_contents($request));
		$response = $response[0];
		$response = get_object_vars($response);
		$response = $response["events"];
		return $response;
	}
	
	#dohvacanje barova i restorana u blizini lokacije koncerta
	public function getBarsAndRestaurants($lat, $long){
		$request = $lat . ',' . $long;
		$request = "http://188.166.13.174:8000/venues?events=".$request;
		$response = json_decode(file_get_contents($request));
		return $response;
	}
	
	#dohvaćanje preporučenih mjesta u blizini lokacije koncerta
	public function getReccomendedPlaces($similar){
		$request = $similar;
		$request = "http://188.166.13.174:8000/similar?".$request;
		var_dump($request);
		$response = json_decode(file_get_contents($request));
		return $response;
	}
}
?>