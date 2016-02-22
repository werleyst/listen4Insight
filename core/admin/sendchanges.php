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

if (isset($_POST['userfile']) AND $_POST['userfile']!=NULL AND isset($_POST['title']) AND $_POST['title']!=NULL AND isset($_POST['description']) AND $_POST['description']!=NULL){ //001

	$file = $_POST['userfile']; //episode file

	if (isset($_FILES['image'])) $img = $_FILES['image'] ['name']; // image file
	
	if (isset($_POST['existentimage'])) $existentimage = $_POST['existentimage']; else $existentimage = NULL;
	
	$title = $_POST['title'];

	$description = $_POST['description'];

	if (isset($_POST['category']) AND $_POST['category'] != NULL) {
		$category = $_POST['category'];
	} else {
	$category = NULL;
	}

	$transcript = $_POST['transcript'];

	$keywords = $_POST['keywords'];

	$explicit = $_POST['explicit'];

	$auth_name = $_POST['auth_name'];

	$auth_email = $_POST['auth_email'];



	// echo "<br /><br /><br />$file - err $errore - temp: $temporaneo<br /><br /><br />";

	$filesuffix = NULL; // declare variable for duplicated filenames
	$image_new_name = NULL; // declare variable for image name

	####
	## here I check lenght of long description: according to the iTunes technical specifications
	## the itunes:summary field can be up to 4000 characters, while the other fields up to 255

	$transcriptMax = 50000; #set max characters variable.
	$descMax = 4000;

	if (strlen($transcript)<$transcriptMax) { // 002 (if long description IS NOT too long, go on executing...
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

		if ($description == NULL OR $description == "        ") { //if user didn't input long description the long description is equal to short description
			$PG_mainbody .= "<p><b><font color=\"red\">"._("Description is not present. Please add a description in the future by editing this podcast.")."</font></b></p>";
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

		$PG_mainbody .= "<p>"._("Author's email address not present or not valid.")." "._("Author will be IGNORED")."</p>";

		$auth_name = NULL; //ignore author
		$auth_email = NULL; //ignore email

	} 


}
else { //if author's name doesn't exist unset also email field
$auth_email = NULL; //ignore email
}


$file_ext = divideFilenameFromExtension($file); //supports more full stops . in the file name. PHP >= 5.2.0 needed


					
############################################
# START CHANGE DATE

//print_r($_POST);

if (isset($_POST['Day']) AND isset($_POST['Month']) AND isset($_POST['Year']) AND isset($_POST['Hour']) AND isset($_POST['Minute'])) { 


$filefullpath = $absoluteurl.$upload_dir.$file;

$oradelfile = filemtime($filefullpath);

$oracambiata = mktime($_POST['Hour'],$_POST['Minute'],0,$_POST['Month'],$_POST['Day'],$_POST['Year']); //seconds are simply 0, no need to handle them


if ($oradelfile != $oracambiata AND checkdate($_POST['Month'],$_POST['Day'],$_POST['Year']) == TRUE) { //is date posted is different from file date and if php function CHECKDATE == TRUE
	
touch($filefullpath,$oracambiata);

$PG_mainbody .= "<p>"._("Date and time of the episode have been modified (this might change the order of your episodes in the podcast feed).")."</p>";

				}

} 					
						
# END CHANGE DATE						
############################################					
						



$PG_mainbody .= "<p><b>"._("Processing changes...")."</b></p>";


				//// RE-CREATING XML FILE ASSOCIATED TO EPISODE


			// if they uploaded a new profile picture
			// ============ START PROFILE PICTURE UPLOAD =================


			if(!file_exists($_FILES["ie_photo"]["tmp_name"]) || !is_uploaded_file($_FILES["ie_photo"]["tmp_name"]) ){

				$PG_mainbody .= "<p>Didn't upload a new profile picture, skipping</p>";
			
			}else{


			$uploadOk = 1;
			$imageFileType = pathinfo(basename($_FILES["ie_photo"]["name"]),PATHINFO_EXTENSION);
			$target_file = $absoluteurl.'images/'.preg_replace('/\\.[^.\\s]{3,4}$/', '', $file) . '.'.$imageFileType;
			$target_file_without_ext = $absoluteurl.'images/'.preg_replace('/\\.[^.\\s]{3,4}$/', '', $file);
			// Check if image file is a actual image or fake image

			    $check = getimagesize($_FILES["ie_photo"]["tmp_name"]);
			    if($check !== false) {
			        //echo "File is an image - " . $check["mime"] . ".";
			        $uploadOk = 1;
			    } else {
			        $PG_mainbody .= "<p><b><font color=\"red\">Warning: Profile image is not an image! Discarding image.</font></b></p>";
			        $uploadOk = 0;
			    }
			 	//  if (file_exists($target_file)) {
				//     $PG_mainbody .= "<p><b><font color=\"red\">Warning: Profile image already exists in the media folder</font></b></p>";
				//     $uploadOk = 0;
				// }
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
			        if($imageFileType == "jpg" && file_exists($target_file_without_ext.".png")){
			        	unlink($target_file_without_ext.".png");
			        	$PG_mainbody .= "<p><b><font color=\"green\">Deleted old png file: ".$target_file_without_ext.".png"."</font></b></p>";
			        }elseif($imageFileType == "png" && file_exists($target_file_without_ext.".jpg")){
			        	unlink($target_file_without_ext.".jpg");
			        	$PG_mainbody .= "<p><b><font color=\"green\">Deleted old jpg file: ".$target_file_without_ext.".jpg"."</font></b></p>";
			        }else{
			        	$PG_mainbody .= "<p><b><font color=\"green\">Overwrote old image file: ".$target_file."</font></b></p>";
			        }
			    } else {
			        $PG_mainbody .= "<p><b><font color=\"red\">Warning: There was an error uploading the profile pictures.</font></b></p>";

			    }

			}
			
			// ============ END PROFILE PICTURE UPLOAD =================




				// ========== ADDED ie_name and ie_bio to updated episode data
				$thisEpisodeData = array($title,$description,$transcript,$image_new_name,$category,$keywords,$explicit,$auth_name,$auth_email, $_POST['ie_name'], $_POST['ie_bio'], $_POST['ie_title']);
		
		$episodeXMLDBAbsPath = $absoluteurl.$upload_dir.$file_ext[0].'.xml'; // extension = XML

		//// Creating xml file associated to episode
		writeEpisodeXMLDB($thisEpisodeData,$absoluteurl,$filefullpath,$episodeXMLDBAbsPath,$file_ext[0],TRUE);




		// =========================================== SQL UPDATE NEEDED ================================================= //
		// Editing an old podcast, need to update an existing line in the database
		// Use the file name to find which row to update. Update all columns with the variables on this page
		// ******file name is equal to: $file

	// ================================================== DATABASE CALL ============================================================

	// UPDATE EXISITNG PODCAST

	// Create connection
	$conn = new mysqli($db_servername, $db_username, $db_password, $db_name);

	// Check connection
	if (!$conn) {
	    $PG_mainbody .= "<p><b><font color=\"red\">ERROR: Fatal Error. Failed to connect to database. Error Code: ".mysqli_connect_error()." <br/>Contact a System Admin.</font></b></p>";
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
				$PG_mainbody .= "<p><b><font color=\"red\">ERROR: SQL Database contains an incorrect number of category entries for this podcast. Category number ".$i."  (".$category[$i]."). Expected 1 entry, database has ".mysqli_num_rows($result).". Podcast was updated but <b>database was not updated</b>. Contact a site admin immediately to correct this issue.</font></b></p>";
			}
		}
	}// end for loop


	// SQL QUERY
	$sql = "UPDATE `listen4_db0`.`Podcasts` SET `Title` = '".str_replace("'", "''", $title)."', `Date` = '".date('Y-m-d H:i:s',$oracambiata)."', `Author` = '".str_replace("'", "''", $auth_name)."', `Transcript` = '".str_replace("'", "''", $transcript)."', `Description` = '".str_replace("'", "''", $description)."', `Category_ID` = '".$categoryIDs[0].", ".$categoryIDs[1].", ".$categoryIDs[2]."', `Key_Words` = '".str_replace("'", "''", $keywords)."', `IE_Name` = '".str_replace("'", "''", $_POST['ie_name'])."', `IE_Bio` = '".str_replace("'", "''", $_POST['ie_bio'])."', `IE_Title` = '".str_replace("'", "''", $_POST['ie_title'])."', `Hook` = '".str_replace("'", "''", $_POST['hook'])."', `Last_Modified` = NOW() WHERE `Name` = '".$file."';";
	$result = mysqli_query($conn, $sql);

	if(!$result){
		$PG_mainbody .= '<p><b style="color:red;">***WARNING***: An SQL Query failed to execute. Error message: '.mysqli_error($conn).'<br/>Constant System Admin immediately to resolve issue.</b></p>';
	}


	// ** FOR CHANGES ONLY** make sure a row was affected, warn if not.
	$numOfRowsAffect = mysqli_affected_rows($conn);
	if($numOfRowsAffect != 1){
		$PG_mainbody .= '<p><b style="color:red;">***WARNING***: Database updated an incorrect number of rows. Expected to update 1 row, updated '.$numOfRowsAffect.'. A system admin must be alerted to this change for this podcast to properly function on the site.</b></p>';
	}

	mysqli_close($conn);

	// ================================================== END DATABASE CALL ============================================================





						

						#	$PG_mainbody .= "<p><b><font color=\"green\">"._("File")."sent</font></b></p>"; // If upload is successful.

						########## REGENERATE FEED
						//include ("$absoluteurl"."core/admin/feedgenerate.php"); //(re)generate XML feed
						$episodesCounter = generatePodcastFeed(TRUE,NULL,FALSE); //Output in file
						##########


						$PG_mainbody .= "<p><a href=\"$url\">"._("Go to the homepage")."</a> - <a href=\"?p=archive&amp;cat=all\">"._("Edit other episodes")."</a></p>";

						



							} // 002
							else { //if long description is more than max characters allowed

								$PG_mainbody .= "<b>"._("Long Description")."toolong</b><p>"._("Long Description")."maxchar $longdescmax "._("characters")." - "._("Actual Length")." <font color=red>".strlen($transcript)."</font> "._("characters").".</p>
									<form>
									<INPUT TYPE=\"button\" VALUE=\""._("Back")."\" onClick=\"history.back()\">
									</form>";
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