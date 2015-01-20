

<?php
error_reporting(E_ALL ^ E_WARNING); 
require_once( 'Facebook/HttpClients/FacebookHttpable.php' );
require_once( 'Facebook/HttpClients/FacebookCurl.php' );
require_once( 'Facebook/HttpClients/FacebookCurlHttpClient.php' );
require_once( 'Facebook/Entities/AccessToken.php' );
require_once( 'Facebook/Entities/SignedRequest.php' );
require_once( 'Facebook/FacebookSession.php' );
require_once( 'Facebook/FacebookRedirectLoginHelper.php' );
require_once( 'Facebook/FacebookRequest.php' );
require_once( 'Facebook/FacebookResponse.php' );
require_once( 'Facebook/FacebookSDKException.php' );
require_once( 'Facebook/FacebookRequestException.php' );
require_once( 'Facebook/FacebookOtherException.php' );
require_once( 'Facebook/FacebookAuthorizationException.php' );
require_once( 'Facebook/GraphObject.php' );
require_once( 'Facebook/GraphSessionInfo.php' );

use Facebook\HttpClients\FacebookHttpable;
use Facebook\HttpClients\FacebookCurl;
use Facebook\HttpClients\FacebookCurlHttpClient;
use Facebook\Entities\AccessToken;
use Facebook\Entities\SignedRequest;
use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use Facebook\FacebookSDKException;
use Facebook\FacebookRequestException;
use Facebook\FacebookOtherException;
use Facebook\FacebookAuthorizationException;
use Facebook\GraphObject;
use Facebook\GraphSessionInfo;

?>
<?php

class FacebookFetching{
	
	#funkcija za sakupljanje podataka o korisniku s fejsa i o njegovim glazbenicima
	public function getUserData (){
		session_start();

		FacebookSession::setDefaultApplication( '356435471204181','177ad8b444c6a4d12d7f461733d3b8d1' );
		$helper = new FacebookRedirectLoginHelper( 'http://localhost/Lab/src/index.php' );
		if ( isset( $_SESSION ) && isset( $_SESSION['fb_token'] ) ) {
		  $session = new FacebookSession( $_SESSION['fb_token'] );
		  try {
			if ( !$session->validate() ) {
			  $session = null;
			}
		  } catch ( Exception $e ) {
			$session = null;
		  }
		}  
		if ( !isset( $session ) || $session === null ) {
		  try {
			$session = $helper->getSessionFromRedirect();
		  } catch( FacebookRequestException $ex ) {
			print_r( $ex );
		  } catch( Exception $ex ) {
			print_r( $ex );
		  }
		}
		if ( isset( $session ) ) {
			$limit = 25;
			$music = array();
			$offset=0;
			$_SESSION['fb_token'] =  $session->getToken();
			$session = new FacebookSession( $session->getToken() );
			
			#dohvaca lajkane artiste
			$request = new FacebookRequest( $session, 'GET', '/me/music?limit=25' );
			$response = $request->execute();
			$graphObject = $response->getGraphObject()->asArray();
			while ($graphObject ['data']){
				$music = array_merge( $music, $graphObject['data'] );
				$offset += $limit;
				$offsetString= (string)$offset;
				$request = new FacebookRequest( $session, 'GET', '/me/music?limit=25&offset='.$offsetString);
				$response = $request->execute();
				$graphObject = $response->getGraphObject()->asArray();
			}
			
			#dohvaca slike glazbenika
			$array = json_decode(json_encode($music), true);
			$artistUrls = array();
			foreach ($array as $musicLike){
				if ($musicLike['category'] == "Musician/band"){
					$url = '/'. $musicLike['id'] .'/picture?redirect=false&height=300&type=normal&width=300';
					$request = new FacebookRequest( $session, 'GET', $url );
					$response = $request->execute();
					$graphObject = $response->getGraphObject()->asArray();
					array_push($artistUrls, $graphObject['url']);
				}	
			}
			
			#dohvaca taggana mjesta
			$request = new FacebookRequest( $session, 'GET', '/me/tagged_places?limit=25' );
			$response = $request->execute();
			$graphObject = $response->getGraphObject()->asArray();
			$places = array();
			while ($graphObject ['data']){
				$places = array_merge( $places, $graphObject['data'] );
				$after = $graphObject['paging'];
				$after = $graphObject['paging']->cursors->after;
				$request = new FacebookRequest( $session, 'GET', '/me/tagged_places?limit=25&after='.$after);
				$response = $request->execute();
				$graphObject = $response->getGraphObject()->asArray(); 
			} 
			
			$request = new FacebookRequest( $session, 'GET', '/me?fields=id,name' );
			$response = $request->execute();
			$userInfo = $response->getGraphObject()->asArray();
			#vraca podatke korisnka
			return array($music, $artistUrls, $places,$userInfo);
		  
		} 
		else {
		  // show login url
		  echo '<a id = "login" href="' . $helper->getLoginUrl( array( 'email', 'user_friends' ) ) . '">Login</a>';
		  return array(0, 0, 0, 0);
		}
	}
}

?>
