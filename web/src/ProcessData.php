<?php
error_reporting(E_ALL ^ E_WARNING); 

#klasa za dohavćanje s spremanje podataka u bazu podataka
class ProcessData{
	
	#funkcija provjerava postoji li korisnik u bazi, ako ne postoji dodaje ga
	public function storeUserInfo($userInfo){
		$conn = new MongoClient(); 
		$db = $conn->Lab1;
		$collection = $db->users;
		$cursor = $collection->find();
		$inDB = false;
		foreach ($cursor as $user){
			if ($user['id'] == $userInfo['id']){
				$inDB = true;
			}
		}
		
		if ($inDB == false){
			$collection->insert($userInfo);
		}
	}
	
	#funkcija za svakog korisnika provjerava je li neki njegov glazbenik s Fcaebooka vec sporemljen za njega u bazu
	#ako nije, sprema se
	public function storeUserMusic($userMusic, $userId){
		$conn = new MongoClient(); 
		$db = $conn->Lab1;
		$collection = $db->userMusic;
		$cursor = $collection->find();
		$userMusic = json_decode(json_encode($userMusic), true);
		$inDB = false;
		foreach ($cursor as $userStoredMusic){
			if ($userStoredMusic['id'] == $userId){
				$inDB = true;
				$newArtists = array();
				$hasNew = false;
				foreach ($userMusic as $musicLike){
					if ($musicLike['category'] == "Musician/band"){
						if (!(in_array($musicLike['name'], $userStoredMusic["music"]))){
							array_push($newArtists, $musicLike['name']);
							$hasNew = true;
						}
					}
				}
				if ($hasNew == true){
					$musicArray = array_merge($userStoredMusic["music"], $newArtists);
					$idRemove = array('id' => $userId);
					$collection->remove($idRemove);
					$data = array("id"=>$userId, "music"=>$musicArray );
					$collection->insert($data);
				}
			}
		}
		if ($inDB == false){
			$musicArray = array();
			foreach ($userMusic as $musicLike){
				if ($musicLike['category'] == "Musician/band")
					array_push($musicArray, $musicLike['name']);
			}
			$data = array("id"=>$userId, "music"=>$musicArray );
			$collection->insert($data);
		}
	}
	
	#funkcija provjerava je li glazbenik već spremljen u bazi
	#ukoliko nije, sprema se, bez dodatnih informacija
	public function storeArtistInfo($userMusic, $artistImages){
		$conn = new MongoClient(); 
		$db = $conn->Lab1;
		$collection = $db->musicians;
		$cursor = $collection->find();
		$inDB = false;
		$userMusic = json_decode(json_encode($userMusic), true);
		$usersMusicians = array();
		foreach ($userMusic as $musicLike){
			if ($musicLike['category'] == "Musician/band")
				array_push($usersMusicians, $musicLike['name']);
		}
		$existingArtists = array();
		foreach ($usersMusicians as $usersMusician){
			foreach ($cursor as $userArtist){
				if ($usersMusician == $userArtist["name"])
					array_push($existingArtists, $usersMusician);
			}
		}
		foreach($usersMusicians as $usersMusician){
			if (!(in_array($usersMusician, $existingArtists))){
				$key = array_search($usersMusician, $usersMusicians);
				$artistImage = $artistImages[$key];
				$data = array("name"=>$usersMusician, "image"=>$artistImage, "info"=>false );
				$collection->insert($data);
			}
		}
	}
	
	#funkcija provjerava postoje li zapisi o korisnikovim lokacijama
	#ukoliko neki dohvaćeni zapis s Facebooka ne postoji, on se dodaje u bazu
	public function storeUserLocations($userPlaces, $userId){
		$conn = new MongoClient(); 
		$db = $conn->Lab1;
		$collection = $db->locations;
		$cursor = $collection->find();
		$inDB = false;
		$userPlaces = json_decode(json_encode($userPlaces), true);
		#var_dump($userPlaces);
		$existingPlaces = array();
		foreach ($cursor as $userLocations){
			if ($userLocations['id'] == $userId){
				$inDB = true;
				$storedPlaces = $userLocations['locations'];
				foreach($userLocations['locations'] as $location){
					foreach($userPlaces as $userPlace)
						if($location['id'] == $userPlace['id'])
							array_push($existingPlaces, $userPlace['id']);
				} 
			}
		}
		if($inDB == false){///ne postoji uopce, dodaje se novi zapis
			$placesForAdding = array();
			foreach($userPlaces as $userPlace){
				$placeInfo = array("id"=>$userPlace['id'], "latitude"=>$userPlace['place']['location']['latitude'], "longitude"=>$userPlace['place']['location']['longitude'], "city"=>$userPlace['place']['location']['city'], "name"=>$userPlace['place']['name']);
				array_push($placesForAdding, $placeInfo);
			}
			$data = array("id"=>$userId, "locations"=>$placesForAdding);
			$collection->insert($data);
		}
		
		else{
			$newToAdd = false;
			foreach($userPlaces as $userPlace){
				if(!(in_array($userPlace['id'], $existingPlaces))){
					$placeInfo = array("id"=>$userPlace['id'], "latitude"=>$userPlace['place']['location']['latitude'], "longitude"=>$userPlace['place']['location']['longitude'], "city"=>$userPlace['place']['location']['city'], "name"=>$userPlace['place']['name']);
					array_push($storedPlaces, $placeInfo);
					$newToAdd = true;
				}	
			}
			if ($newToAdd == true){
				$idRemove = array('id' => $userId);
				$collection->remove($idRemove);
				$data = array("id"=>$userId, "locations"=>$storedPlaces);
				$collection->insert($data);
				echo 'umetnio novo';
			}
		}	
	}
	
	#dohvaćanje korisnikovih glazbenika iz baze
	public function getUserMusic($userId){
		$conn = new MongoClient(); 
		$db = $conn->Lab1;
		$collection = $db->userMusic;
		$cursor = $collection->findOne(array('id' => $userId));
		return $cursor;
	}
	
	
	#dohaćanje glazbenikove slike iz baze
	public function getArtistImage($usersArtists){
		$conn = new MongoClient(); 
		$db = $conn->Lab1;
		$collection = $db->musicians;
		$cursor = $collection->find();
		$images = array();
		foreach($usersArtists as $userArtist){
			$cursor = $collection->findOne(array('name' => $userArtist));
			array_push($images, $cursor);
		}
		return($images);
	}
	
	#provjerava se ima li glazbenik spremljene dodatne informacije
	public function checkArtistInfo($artistName){
		$conn = new MongoClient(); 
		$db = $conn->Lab1;
		$collection = $db->musicians;
		$cursor = $collection->findOne(array('name' => $artistName));
		return $cursor["info"];
		
	}
	
	#spremanje dodatnih informacija o glazbeniku
	public function storeAdditionalArtistInfo($artistName, $response){
		$conn = new MongoClient(); 
		$db = $conn->Lab1;
		$collection = $db->musicians;
		$cursor = $collection->findOne(array('name' => $artistName));
		$artistImage = $cursor["image"];
		$idRemove = array('name' => $artistName);
		$collection->remove($idRemove);
		$data = array("name"=>$artistName, "image"=>$artistImage, "info"=>true, "about"=>$response["about"], "albums"=>$response["albums"], "tags"=>$response["tags"]);
		$collection->insert($data);
	}
	
	#dohvaćanje informacija o glazbeniku iz baze
	public function getArtistInfo($artistName){
		$conn = new MongoClient(); 
		$db = $conn->Lab1;
		$collection = $db->musicians;
		$cursor = $collection->findOne(array('name' => $artistName));
		return $cursor;
		
	}
	
	#dohvaćanje podataka o korisniku iz baze
	public function getUserLocations($userId){
		$conn = new MongoClient(); 
		$db = $conn->Lab1;
		$collection = $db->locations;
		$cursor = $collection->findOne(array("id"=>$userId));
		return $cursor;
	}
}
?>