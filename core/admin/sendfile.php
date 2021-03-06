<?php
############################################################
# PODCAST GENERATOR
#
# Created by Alberto Betella
# http://podcastgen.sourceforge.net
# 
# This is Free Software released under the GNU/GPL License.
############################################################

########### Security code, avoids cross-site scripting (Register Globals ON)
if (isset($_REQUEST['GLOBALS']) OR isset($_REQUEST['absoluteurl']) OR isset($_REQUEST['amilogged']) OR isset($_REQUEST['theme_path'])) { exit; } 
########### End


### Check if user is logged ###
	if (!isUserLogged()) { exit; }
###




if (isset($_FILES['userfile']) AND $_FILES['userfile']!=NULL AND isset($_POST['title']) AND $_POST['title']!=NULL AND isset($_POST['description']) AND $_POST['description']!=NULL){ //001

	$file= $_FILES['userfile'] ['name']; //episode file

//	if (isset($_FILES['image'])) $img= $_FILES['image'] ['name']; // image file

	$title = $_POST['title'];

	$description = $_POST['description'];

	if (isset($_POST['category']) AND $_POST['category'] != NULL) $category = $_POST['category'];

	$transcript = $_POST['transcript'];

	$keywords = $_POST['keywords'];

	$explicit = $_POST['explicit'];

	$auth_name = $_POST['auth_name'];

	$auth_email = $_POST['auth_email'];

	// $errore= $_FILES['userfile']['error'];

	$temporaneo= $_FILES['userfile']['tmp_name'];

	// echo "<br /><br /><br />$file - err $errore - temp: $temporaneo<br /><br /><br />";

	$filesuffix = NULL; // declare variable for duplicated filenames
	$image_new_name = NULL; // declare variable for image name

	####
	## here I check lenght of long description: according to the iTunes technical specifications
	## the itunes:summary field can be up to 4000 characters, while the other fields up to 255

	$transcriptMax = 50000; #set max characters variable.
	$descMax = 4000;

	if (strlen($transcript)<$transcriptMax && strlen($description)<$descMax) { // 002 (if long description IS NOT too long, go on executing...
		####


		#### INPUT DEPURATION
		$title = depurateContent($title); //title
		$description = depurateContent($description); //short desc
		$transcript = depurateContent($transcript); //long desc
		$keywords = depurateContent($keywords); //Keywords
		$auth_name = depurateContent($auth_name); //author's name

		##############
		### processing Long Description

		#$PG_mainbody .= "QUI: $transcript<br>lunghezza:".strlen($transcript)."<br>"; //debug

		if ($transcript == NULL OR $transcript == "        ") { //if user didn't input long description the long description is equal to short description
			$PG_mainbody .= "<p><b><font color=\"red\">"._("Transcript is not present. Please add a transcript in the future by editing this podcast.")."</font></b></p>";
			//$transcript = $description;
		}

		else {
			$PG_mainbody .= "<p>"._("Transcript is present")."</p>";
			$transcript = str_replace("&nbsp;", " ", $transcript); 
		}

	##############
	### processing iTunes KEYWORDS

	## iTunes supports a maximum of 12 keywords for searching: don't know how many keywords u can add in a feed. Anyway it's better to add a few keyword, so we display a warning if user submits more than 12 keywords

	# $PG_mainbody .= "$keywords<br>"; /debug

	if (isset($ituneskeywords) AND $ituneskeywords != NULL) { 
		$PG_mainbody .= "<p>"._("iTunes Keywords:")." $ituneskeywords</p>";

		$singlekeyword=explode(",",$keywords); // divide filename from extension

		if ($singlekeyword[12] != NULL) { //if more than 12 keywords
			$PG_mainbody .= "<p>- "._("You submitted more than 12 keywords for iTunes...")."</p>";

		}
	}

	##############
	### processing Author

	if (isset($auth_name) AND $auth_name != NULL) { //if a different author is specified

		$PG_mainbody .= "<p>"._("Author specified for this episode...")."</p>";

		if (!validate_email($auth_email)) { //if author doesn't have a valid email address, just ignore it and use default author

		$PG_mainbody .= "<p>"._("No")."authemail "._("Author will be IGNORED")."</p>";

		$auth_name = NULL; //ignore author
		$auth_email = NULL; //ignore email

	} 


}
else { //if author's name doesn't exist unset also email field
$auth_email = NULL; //ignore email
}


#show submitted data (debug purposes)
//$PG_mainbody .= "Dati inseriti:</b><br><br>Titolo: <i>$title</i> <br>Descrizione breve: <i>$description</i> <br>Descrizione lunga: <i>$transcript</i>";
###




## start processing podcast

$PG_mainbody .= "<p><b>"._("Processing episode...")."</b></p>";

$PG_mainbody .= "<p>"._("Original filename:")." <i>$file</i></p>";


	$file_parts = divideFilenameFromExtension($file);
	$filenameWithoutExtension = $file_parts[0];
	$fileExtension = $file_parts[1];

// $PG_mainbody .= "<p>"._("File")."_ext <i>$fileExtension</i></p>"; //display file extension

##############
### processing file extension
$fileData = checkFileType(strtolower($fileExtension),$absoluteurl); //lowercase extension to compare with the accepted extensions array

if (isset($fileData[0])){ //avoids php notice if array [0] doesn't exist
$podcast_filetype=$fileData[0];

}else {
	$podcast_filetype=NULL;	
}

if ($fileExtension==strtoupper($podcast_filetype)) $podcast_filetype = strtoupper($podcast_filetype); //accept also uppercase extension

if ($fileExtension==$podcast_filetype) { //003 (if file extension is accepted, go on....


	##############
	##############
	### file name depuration!!!! Important... By default Podcastgen uses a "strict" depuration policy (just characters from a to z and numbers... no accents and other characters).

	if ($strictfilenamepolicy == "yes") {
		#enable this to have a very strict filename policy

		$filenameWithoutExtension = renamefilestrict ($filenameWithoutExtension);

	}

	else {
		# LESS strict renaming policy

		$filenameWithoutExtension = renamefile ($filenameWithoutExtension);

	}

		$fileExtension = strtolower ($fileExtension); //lowercase file extension


	##############
	############## end filename depuration


if ($strictfilenamepolicy == "yes") 	$filenamechanged = date('Y-m-d')."_".$filenameWithoutExtension; //add date, to order files in mp3 players
else $filenamechanged = $filenameWithoutExtension;


	$uploadFile = $upload_dir . $filenamechanged.".".$fileExtension ;


	while (file_exists("$uploadFile")) { //cicle: if file already exists add an incremental suffix
		$filesuffix++;

		# $PG_mainbody .= "$filesuffix"; //debug

		$uploadFile = $absoluteurl . $upload_dir . $filenamechanged . $filesuffix.".".$fileExtension ;

	}


	$PG_mainbody .= _("File Renamed:")." <i>$filenamechanged$filesuffix.$fileExtension</i><br />";

	$uploadFile == NULL ;

	#$PG_mainbody .= "<br>Uploaded file:$uploadFile<br>";

	//move file from the temp directory to the upload directory
	if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadFile))
	{

			############################################
			# START CHANGE DATE

			//print_r($_POST);

			if (isset($_POST['Day']) AND isset($_POST['Month']) AND isset($_POST['Year']) AND isset($_POST['Hour']) AND isset($_POST['Minute'])) { 


			$filefullpath = $absoluteurl.$upload_dir.$filenamechanged.$filesuffix.'.'.$fileExtension;

			//$oradelfile = filemtime($filefullpath);

			$oracambiata = mktime($_POST['Hour'],$_POST['Minute'],0,$_POST['Month'],$_POST['Day'],$_POST['Year']); //seconds are simply 0, no need to handle them

	//	echo $oracambiata;

			if ($oracambiata > time() AND checkdate($_POST['Month'],$_POST['Day'],$_POST['Year']) == TRUE) { 

			touch($filefullpath,$oracambiata);

			$PG_mainbody .= "<p>"._("The episode date has been set to future. This episode won't show up till then.")."</p>";

			}

			} 					

			# END CHANGE DATE						
			############################################


		// ====================== Adding interviewee name and bio to array ==========================

			// ============ START PROFILE PICTURE UPLOAD =================

			$uploadOk = 1;
			$imageFileType = pathinfo(basename($_FILES["ie_photo"]["name"]),PATHINFO_EXTENSION);
			$target_file = $absoluteurl.'images/'.$filenamechanged . '.'.$imageFileType;
			// Check if image file is a actual image or fake image

			    $check = getimagesize($_FILES["ie_photo"]["tmp_name"]);
			    if($check !== false) {
			        //echo "File is an image - " . $check["mime"] . ".";
			        $uploadOk = 1;
			    } else {
			        $PG_mainbody .= "<p><b><font color=\"red\">Warning: Profile image is not an image! Discarding image.</font></b></p>";
			        $uploadOk = 0;
			    }
			    if (file_exists($target_file)) {
				    $PG_mainbody .= "<p><b><font color=\"red\">Warning: Profile image already exists in the media folder</font></b></p>";
				    $uploadOk = 0;
				}
				if ($_FILES["ie_photo"]["size"] > 1900000) {
				    $PG_mainbody .= "<p><b><font color=\"red\">Warning: Profile image too large. Make less than 1.8MB. Discarding image.</font></b></p>";
				    $uploadOk = 0;
				}
				if( $imageFileType != "jpg" && $imageFileType != "png" ) {
				    $PG_mainbody .= "<p><b><font color=\"red\">Warning: Profile image is not a png or a jpg. Discarding</font></b></p>";
				    $uploadOk = 0;
				}
				if (move_uploaded_file($_FILES["ie_photo"]["tmp_name"], $target_file)) {
			        $PG_mainbody .= '<p>Profile picture successfully uploaded: '.$target_file.'</p>';
			    } else {
			        $PG_mainbody .= "<p><b><font color=\"red\">Warning: There was an error uploading the profile pictures.</font></b></p>";

			    }
			
			// ============ END PROFILE PICTURE UPLOAD =================

	    // ============== Added ie_name and ie_bio to episode data
		$thisEpisodeData = array($title,$description,$transcript,$image_new_name,$category,$keywords,$explicit,$auth_name,$auth_email, $_POST['ie_name'], $_POST['ie_bio'], $_POST['ie_title']);
		
		$episodeXMLDBAbsPath = $absoluteurl.$upload_dir.$filenamechanged.$filesuffix.'.xml'; // extension = XML

		//// Creating xml file associated to episode
		writeEpisodeXMLDB($thisEpisodeData,$absoluteurl,$filefullpath,$episodeXMLDBAbsPath,$filenamechanged.$filesuffix,TRUE);




		// =========================================== SQL UPDATE NEEDED ================================================= //
		// Uploading a new podcast, need a new podcast added to the database.
		// Be sure to include the file name in the database, as that is what the backend uses to uniqely identify podcasts, specifically for deleting
		// file name is equal to: $filenamechanged.$filesuffix.'.'.$fileExtension

	// ================================================== DATABASE CALL ============================================================

	// ADD NEW PODCAST TO TABLE

	// Create connection
	$conn = new mysqli($db_servername, $db_username, $db_password, $db_name);

	// Check connection
	if (!$conn) {
	    $PG_mainbody .= "<p><b><font color=\"red\">ERROR: Fatal Error. Failed to connect to database. Error Code: ".mysqli_connect_error()." </font></b></p>";
	}


	// parse categories
	$categoryIDs = array("","","");
	for($i = 0;$i < 3; $i++){
		if(isset($category[$i]) && $category[$i] != NULL && $category[$i] != ""){
			$sql = "SELECT * FROM `listen4_db0`.`Categories` WHERE `Categories`.`uniqueID` = '".$category[$i]."';";
			$result = mysqli_query($conn, $sql);
			if (mysqli_num_rows($result) == 1 ) {
			    $row = mysqli_fetch_assoc($result);
			    $categoryIDs[$i] = $row["ID"];
			}else{
				$PG_mainbody .= "<p><b><font color=\"red\">ERROR: SQL Database contains an incorrect number of cateogry entries for this podcast. Category number ".$i."  (".$category[$i]."). Expected 1 entry, database has ".mysqli_num_rows($result).". Podcast was uploaded but database was not updated properly. <br/>Contact a site admin immediately to correct this issue.</font></b></p>";
			}
		}
	}// end for loop


	// SQL QUERY
	$sql = "INSERT INTO `listen4_db0`.`Podcasts` (`ID`,`Last_Modified`, `Name`, `Title`, `Date`, `Author`, `Transcript`, `Description`, `Category_ID`, `Key_Words`, `IE_Name`, `IE_Bio`, `IE_Title`, `Hook`)
	VALUES (NULL, NOW(), '".$filenamechanged.$filesuffix.".".$fileExtension."', '".str_replace("'", "''", $title)."', '".date('Y-m-d H:i:s',$oracambiata)."', '".str_replace("'", "''", $auth_name)."', '".str_replace("'", "''", $transcript)."', '".str_replace("'", "''", $description)."', '".$categoryIDs[0].", ".$categoryIDs[1].", ".$categoryIDs[2]."', '".str_replace("'", "''", $keywords)."', '".str_replace("'", "''", $_POST['ie_name'])."', '".str_replace("'", "''", $_POST['ie_bio'])."', '".str_replace("'", "''", $_POST['ie_title'])."', '".$_REQUEST['hook']."' );";
	$result = mysqli_query($conn, $sql);

	// Handle Errors
	if(!$result){
		$PG_mainbody .= "<p><b><font color=\"red\">Database Error: SQL Query Failed to update. Error Message: ".mysqli_error($conn)."<br/>Podcast was uploaded, however podcast database table failed to update. <br/>Contact a System Admin immediately to resolve this issue.</font></b></p>";
	}

	mysqli_close($conn);

	// ================================================== END DATABASE CALL ============================================================



		$PG_mainbody .= "<p><b><font color=\"green\">"._("File Uploaded Successfully")."</font></b></p>"; // If upload is successful.

		########## REGENERATE FEED
		//include ("$absoluteurl"."core/admin/feedgenerate.php"); //(re)generate XML feed
		$episodesCounter = generatePodcastFeed(TRUE,NULL,FALSE); //Output in file
		##########
		
		$PG_mainbody .= "<p><a href=\"$url\">"._("Go to the homepage")."</a> - <a href=\"?p=admin&do=upload\">"._("Upload another episode")."</a></p>";

	}
	else //If upload is not successful
	{

		$PG_mainbody .= "<p><b><font color=\"red\">"._("FILE ERROR")." "._("Upload Failed")."</font></b></p>";
		$PG_mainbody .= "<p><b>"._("FILE ERROR")."1</b></p>";
		$PG_mainbody .= "<p> - "._("You didn't assign writing permission to the media folder and the uploaded file can't be saved on the server.")."</p>";
		$PG_mainbody .= "<p> - "._("Your file is bigger than upload max filesize on your server.")."</p>";

		$PG_mainbody .= "<p><b>"._("Useful information for debugging:")."</b> <a href=\"?p=admin&amp;do=serverinfo\">"._("Your server configuration")."</a></p>";

		$PG_mainbody .= "<p>"._("FILE ERROR")." <a href=\"http://podcastgen.sourceforge.net/\" target=\"_blank\">"._("Podcast Generator web page")."</a></p>";

		$PG_mainbody .= '<p><form>
			<input type="button" value="'._("Back").'" class="btn btn-danger btn-small" onClick="history.back()">
			</form></p>';
	}


} // 003 (if file extension is not accepted)
else {
	$PG_mainbody .= "<p><i>$fileExtension</i> "._("is not a supported extension or your filename contains forbidden characters.")."</p>";
	$PG_mainbody .= '<form>
		<input type="button" value="'._("Back").'" class="btn btn-danger btn-small" onClick="history.back()">
		</form>';
}


} // 002
else { //if long description is more than max characters allowed

	if($transcriptMax < strlen($transcript))
		$PG_mainbody .= "<b>"._("Transcript")." too long</b><p>"._("Transcript")." maxchar: $transcriptMax "._("characters")." - "._("Actual Length:")." <font color=red>".strlen($transcript)."</font> "._("characters").".</p>";

	if($descMax < strlen($description))
		$PG_mainbody .= "<b>"._("Description")." too long</b><p>"._("Description")." maxchar: $descMax "._("characters")." - "._("Actual Length:")." <font color=red>".strlen($description)."</font> "._("characters").".</p>";


		$PG_mainbody .= '<form>
		<input type="button" value="'._("Back").'" class="btn btn-danger btn-small" onClick=\"history.back()\">
		</form>';
}
#### end of long desc lenght checking


} //001 
else { //if file, description or title not present...
	$PG_mainbody .= '<p>'._("Error: No file, file too big, no description, or no title").'
		<br />
		<form>
		<input type="button" value="&laquo; '._("Back").'" onClick="history.back()" class="btn btn-danger btn-small" />
		</form>
		</p>
		';
}







?>