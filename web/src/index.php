<?php

error_reporting(E_ALL ^ E_WARNING); 
require_once( 'FacebookFetching.php' );
require_once( 'ProcessData.php' );
$facebookFetch = new FacebookFetching();
$processData = new ProcessData();
list($userMusic, $artistImages, $userPlaces, $userInfo) = $facebookFetch -> getUserData();
if ($userInfo != 0){
	session_start();
	#prikupljanje podataka o korisniku i njihova pohrana
	$_SESSION['userId'] = $userInfo['id'];
	$processData -> storeUserInfo($userInfo);
	$processData -> storeUserMusic($userMusic, $userInfo['id']);
	$processData -> storeArtistInfo($userMusic, $artistImages);
	$processData -> storeUserLocations($userPlaces, $userInfo['id']);
	
	header("Location: choosingArtist.php"); /* Redirect browser */
	exit();
} 
?>