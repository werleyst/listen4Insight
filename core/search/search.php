<?PHP
/*
	All Code by Matt DePero
*/

	
if(!isset($_REQUEST['s']) || !isset($_REQUEST['p']) || strtolower($_GET['p'])!="search"){
	$PG_mainbody .= 'Reached page on error.<br/><br/><a href="?home">Go Back</a>';

}else{



	$PG_mainbody .= '<h1>Search Results</h1>';

	$PG_mainbody .= '<div id="search_parameters">Finding results for <i>'.$_REQUEST['s'].'</i></div><br/><br/>';


	// ================================================== DATABASE CALL ============================================================

	// ADD NEW CATEGORY TO DATABASE

	// Create connection
	$conn = new mysqli($db_servername, $db_username, $db_password, $db_name);

	// Check connection
	if (!$conn) {
		$PG_mainbody .= "<p><b><font color=\"red\">ERROR: [Database Connection Error] ".mysqli_connect_error()." <br/>Contact a System Admin immediately to resolve this issue.</font></b></p>";
	}





	// === Database Columns to Search ===
	$search_columns = array("Title", "Hook", "Transcript","Description", "Key_Words", "IE_Name", "IE_Bio", "Author");



	// === Generate Search SQL ===

	// Parse search input
	$search_elements = explode(" ", $_GET['s']);

	$search_patterns = array();

	// create a search pattern for each search element
	foreach ($search_elements as $element){

		$search_pattern = "";
		$first = true;
		// go through each column to be included in search
		foreach ($search_columns as $column){
			if($first == true){
				$first = false;
			}else{
				$search_pattern .= " OR";
			}

			$search_pattern .= " ".$column." LIKE '%".str_replace("'","''",$element)."%'";
		}
		array_push($search_patterns, $search_pattern);

	}

	$search_query = "";
	$first = true;
	foreach($search_patterns as $pattern){
		if($first){
			$first = false;
		}else{
			$search_query .= " AND";
		}

		$search_query .= " (".$pattern.")";
	}


	// SQL QUERY
	$sql = "SELECT * FROM Podcasts WHERE ".$search_query.";";
	$PG_mainbody .= '<script> console.log("SQL Search Query: '.$sql.'")</script>';
	$result = mysqli_query($conn, $sql);

	if(!$result){

		$PG_mainbody .= "<p><b><font color=\"red\">Database Error: [SQL Query Failed]. Error Message: ".mysqli_error($conn)."<br/>Please contact a System Admin immediately to resolve this issue.</font></b></p>";
	}



	// Process Results of Query
	if (mysqli_num_rows($result) > 0) {
    // output data of each row

		$resulting_episodes = '<div class="podcast_list">';


	    while($row = mysqli_fetch_assoc($result)) {


	    		$singleFileName = $row['Name'];

	    	////Validate the current episode
				//NB. validateSingleEpisode returns [0] episode is supported (bool), [1] Episode Absolute path, [2] Episode XML DB absolute path,[3] File Extension (Type), [4] File MimeType, [5] File name without extension, [6] episode file supported but to XML present
				$thisPodcastEpisode = validateSingleEpisode($singleFileName);

			
				////If episode is supported and has a related xml db, and if it's not set to a future date OR if it's set for a future date but you are logged in as admin
				if (($thisPodcastEpisode[0]==TRUE AND !publishInFuture($thisPodcastEpisode[1])) OR ($thisPodcastEpisode[0]==TRUE AND publishInFuture($thisPodcastEpisode[1]) AND isUserLogged())) { 

					////Parse XML data related to the episode 
					// NB. Function parseXMLepisodeData returns: [0] episode title, [1] short description, [2] long description, [3] image associated, [4] iTunes keywords, [5] Explicit language,[6] Author's name,[7] Author's email,[8] PG category 1, [9] PG category 2, [10] PG category 3, [11] file_info_size, [12] file_info_duration, [13] file_info_bitrate, [14] file_info_frequency, [15] interviewee name, [16] interviewee bio
					$thisPodcastEpisodeData = parseXMLepisodeData($thisPodcastEpisode[2]);

					////if category is specified as a parameter of this function
					if (isset($category) AND $category != NULL) { 
						//if category is not associated to the current episode
						if ($category != $thisPodcastEpisodeData[8] AND $category != $thisPodcastEpisodeData[9] AND $category != $thisPodcastEpisodeData[10]) {
							continue; //STOP this cycle and start a new one
						} else {
							$CounterEpisodesInCategory++; // Incremente episodes counter
						}
					}

					//// Start constructing episode HTML output

					// SQL CALL TO GET HOOK OUT OF DATABASE

					// Create connection
					$conn2 = new mysqli($db_servername, $db_username, $db_password, $db_name);

					// Check connection
					// if (!$conn) {
					//     $PG_mainbody .= "<p><b><font color=\"red\">ERROR: Fatal Error. Failed to connect to database. Error Code: ".mysqli_connect_error()." Contact System Admin.</font></b></p>";
					// }

					$sql2 = "SELECT * FROM Podcasts WHERE Name = '".urlencode($thisPodcastEpisode[5]).'.'.$thisPodcastEpisode[3]."'";

					$result2 = mysqli_query($conn2, $sql2);

					// if(!$result || mysqli_num_rows($result) != 1) {
					// 	$PG_mainbody .= "<p><b><font color=\"red\">ERROR: SQL Query error on hook statement. Contact System Admin. </font></b></p>";
					// }

					$row2 = mysqli_fetch_assoc($result2);
					$hook = $row2['Hook'];

					mysqli_close($conn2);


					
					//Theme engine PG version >= 2.0
					if (useNewThemeEngine($theme_path)) {
						//episodes per line in some themes (e.g. bootstrap)
						$numberOfEpisodesPerLine = 2; 
						//If the current episode number is multiple of $numberOfEpisodesPerLine
						if ($episodesCounter % $numberOfEpisodesPerLine != 0 OR $episodesCounter == count($fileNamesList)) {
							//open div with class row-fluid (theme based on bootstrap)
							//N.B. row-fluid is a CSS class for a div containing 1 or more episodes
							//$resulting_episodes .= '<div class="row-fluid">';
							$resulting_episodes .= '<div class="episode">';
						}
						$resulting_episodes .= '<div class="col-lg-4 col-md-6 episodebox">'; //open the single episode DIV
					}

					$resulting_episodes .= '<div class="top-half-of-episode">';

					//// Edit/Delete button for logged user (i.e. admin)
					if (isUserLogged()) { 
						$resulting_episodes .= '<p><a class="btn btn-inverse btn-xs btn-mini" href="?p=admin&amp;do=edit&amp;=episode&amp;name='.urlencode($thisPodcastEpisode[5]).'.'.$thisPodcastEpisode[3].'">'._("Edit / Delete").'</a></p>';
					}
					
					//Show Image embedded in the mp3 file or image associated in the images/ folder from previous versions of PG (i.e. 1.4-) - Just jpg and png extension supported
					$resulting_episodes .= '<div class="row episode_header"><div class="col-xs-4 episode-image-container"><a href="?name='.$thisPodcastEpisode[5].'.'.$thisPodcastEpisode[3].'">';
					if (file_exists($absoluteurl.$img_dir.$thisPodcastEpisode[5].'.jpg')) {
						$resulting_episodes .= '<img class="episode_image img-responsive" src="'.$url.$img_dir.$thisPodcastEpisode[5].'.jpg" alt="'.$thisPodcastEpisodeData[0].'" />';
					} 
					else if (file_exists($absoluteurl.$img_dir.$thisPodcastEpisode[5].'.png')) {
						$resulting_episodes .= '<img class="episode_image img-responsive" src="'.$url.$img_dir.$thisPodcastEpisode[5].'.png" alt="'.$thisPodcastEpisodeData[0].'" />';
					}
					$resulting_episodes .= '</a>';

					$resulting_episodes .= '</div><div class="col-xs-8 person-title-container">';

					$resulting_episodes .= '<a href="?name='.$thisPodcastEpisode[5].'.'.$thisPodcastEpisode[3].'">';

					////Interviewee
					$resulting_episodes .= '<h3 class="person_title">'.$thisPodcastEpisodeData[15].'</h3>';

					$resulting_episodes .= '</a>';

					////Hook
					$resulting_episodes .= '<h4 class="hook">'.$hook.'</h4>';

					$resulting_episodes .= '</div></div>';

					$resulting_episodes .= '<div class="podcast-title-container">';

					////Title/Hook
					$resulting_episodes .= '<h4 class="episode-title-hook">'.$thisPodcastEpisodeData[0].'</h4>';

					$resulting_episodes .= '</div>';



					////Date
					// $resulting_episodes .= '<p class="episode_date">';
					// $thisEpisodeDate = filemtime($thisPodcastEpisode[1]);
					// if ($thisEpisodeDate > time()) { //if future date
					// $resulting_episodes .= '<i class="fa fa-clock-o fa-2x"></i>  ';	//show watch icon
					// }
					// $episodeDate = date ($dateformat, $thisEpisodeDate);
					// $resulting_episodes .= $episodeDate.'</p>';
					


					////Buttons (More, Download, Watch)
					// $resulting_episodes .= showButtons($thisPodcastEpisode[5],$thisPodcastEpisode[3],$url,$upload_dir,$episodesCounter,$thisPodcastEpisode[1],$enablestreaming);

					
					////Other details (file type, duration, bitrate, frequency)					
					//NB. read from XML DB (except file extension = $thisPodcastEpisode[3]).
					// $episodeDetails = _('Filetype:')." ".strtoupper($thisPodcastEpisode[3]);
					// if ($thisPodcastEpisodeData[11] != NULL) $episodeDetails .= ' - '._('Size:')." ".$thisPodcastEpisodeData[11]._("MB");
					
					// if($thisPodcastEpisodeData[12]!=NULL) { // display file duration
					// $episodeDetails .= " - "._("Duration:")." ".$thisPodcastEpisodeData[12]." "._("m");
					// }
					// if($thisPodcastEpisode[3]=="mp3" AND $thisPodcastEpisodeData[13] != NULL AND $thisPodcastEpisodeData[14] != NULL) { //if mp3 show bitrate and frequency
					// 	$episodeDetails .= " (".$thisPodcastEpisodeData[13]." "._("kbps")." ".$thisPodcastEpisodeData[14]." "._("Hz").")";
					// }
					// $resulting_episodes .= '<p class="episode_info">'.$episodeDetails.'</p>';


					// end top half of episode
					$resulting_episodes .= '</div><div class="bottom-half-of-episode">';

					////Playes: audio (flash/html5) and video (html5), for supported files and browsers
					//if audio and video streaming is enabled in PG options
					if ($enablestreaming=="yes" AND !detectMobileDevice()) { 
						$resulting_episodes .= showStreamingPlayers ($thisPodcastEpisode[5],$thisPodcastEpisode[3],$url,$upload_dir,$episodesCounter);
					}
					$isvideo = FALSE; //RESET isvideo for next episode


					//// Long Description
					//$resulting_episodes .= '<p>'.$thisPodcastEpisodeData[1].'</p>';
					
					////Social networks and (eventual) embedded code (very slow if many podcasts on the same screen)
					//$resulting_episodes .= attachToEpisode($thisPodcastEpisode[5],$thisPodcastEpisode[3],$thisPodcastEpisodeData[0]);

					
					
					// end bottom half of episode
					$resulting_episodes .= '</div>';


					//Close the single episode DIV
					$resulting_episodes .= "</div>";
					//Close div with class row-fluid (theme based on bootstrap). Theme engine >= 2.0
					if (useNewThemeEngine($theme_path) AND $episodesCounter % $numberOfEpisodesPerLine != 0 OR 		$episodesCounter == count($fileNamesList)) { 
						$resulting_episodes .= "</div>"; //close class row-fluid (bootstrap)
					}

					//$episodesCounter++; //increment counter

				}

	        	//$PG_mainbody .=  '<div><a href="?name='.$row['Name'].'">'.$row['Title'].'</a></div>';
	    }

	    $resulting_episodes .= "</div>";

	    $PG_mainbody .= $resulting_episodes;


	} else {
	    $PG_mainbody .= 'No results found. <br/><br/><a class="btn btn-primary" href="?home">Home</a>';
	}



	mysqli_close($conn);

	// ================================================== END DATABASE CALL ============================================================


}

?>