<html>
	<head>
		<title>ConcertBook</title>
		<meta content="text/html; charset=UTF-8" http-equiv="content-type">
		<link rel="stylesheet" type="gn/css" href="design.css">
	</head>
	<body>

		<?php
		#na zaslonu se prikazuju imena i profilne slike svih glazbenika koje je korisnik lajkao
		#ti podaci su dohvaćeni iz baze
		#klikom na njih prikazuje se stranica glazbenika s dodatnim sadržajem
		error_reporting(E_ALL ^ E_WARNING); 
		require_once( 'ProcessData.php' );
		session_start();
		$_SESSION['already'] = 0;
		$processData = new ProcessData();
		$usersArtists = $processData -> getUserMusic($_SESSION['userId']);
		$artistsProfileImages = $processData -> getArtistImage($usersArtists['music']);
		echo '<table style="width:100%;"><tr style="width:100%;height:100%">';
		$number = 0;
		
		foreach($artistsProfileImages as $artistsProfileImage){
			$number++;
			echo '<td style="width:12.5%; height:100%"><a href="ArtistInfo.php?q='.$artistsProfileImage['name'].'&maxResults=3" title='.$artistsProfileImage["name"].' ><img style="width:100%" src='.$artistsProfileImage['image'].' border="0" alt='.$artistsProfileImage["image"].'>';
			echo '<p style="text-align:center"><b>'.$artistsProfileImage["name"].'</b></p></a><br></td>';
			if ($number%8 == 0)
				echo '</tr><tr>';
			}
		echo '</tr></table>';
		?>
	</body>
</html>