 <?php
error_reporting(E_ALL ^ E_WARNING); 
require_once 'Google/Client.php';
require_once 'Google/Service/YouTube.php';

class YoutubeFetching{
	
	#služi za dohvaćanje videa korištenjem Youtube API-ja
	#dohvaća TOP 3 videa u kategoriji musc video za upisani pojam, odnosno ime glazbenika
	public function getMusicVideos ($searchTerm, $numberOfVideos){
		$DEVELOPER_KEY = 'AIzaSyD8GgmupvoaFR9ev0RIGYMe-SVwXmfyDWo';
		$client = new Google_Client();
		$client->setDeveloperKey($DEVELOPER_KEY);
		$youtube = new Google_Service_YouTube($client);
		try {
			$searchResponse = $youtube->search->listSearch('id,snippet', array(
			'q' => $searchTerm,
			'maxResults' => $numberOfVideos,
			'type' => "video",
			'order' => "viewCount",
			'videoDefinition' => "high",
			'videoEmbeddable' => "true",
			'videoCategoryId' => '10',
			));
			return $searchResponse;
			}
			catch (Google_ServiceException $e) {
				$htmlBody .= sprintf('<p>A service error occurred: <code>%s</code></p>',
				htmlspecialchars($e->getMessage()));
			} catch (Google_Exception $e) {
				$htmlBody .= sprintf('<p>An client error occurred: <code>%s</code></p>',
				htmlspecialchars($e->getMessage()));
			}
		}
}

?>