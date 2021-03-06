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

if (isset($_GET['file']) AND $_GET['file']!=NULL) {

	$file = $_GET['file']; 
	
		$file = str_replace("/", "", $file); // Replace / in the filename.. avoid deleting of file outside media directory - AVOID EXPLOIT with register globals set to ON

	$ext = $_GET['ext'];



	if (file_exists("$absoluteurl$upload_dir$file.$ext")) {
		unlink ("$upload_dir$file.$ext");
		$PG_mainbody .="<p><b>$file.$ext</b> "._("has been deleted")."</p>";

	}

	if (file_exists("$absoluteurl$upload_dir$file.xml")) {

		unlink ("$absoluteurl$upload_dir$file.xml"); // DELETE THE FILE

	}
	
	//Delete associated image
	if (file_exists("$absoluteurl$img_dir$file.jpg")) {
		unlink ("$absoluteurl$img_dir$file.jpg"); 
	} else if (file_exists("$absoluteurl$img_dir$file.png")) {
		unlink ("$absoluteurl$img_dir$file.png"); 
	}



	// =========================================== SQL UPDATE NEEDED ================================================= //
	// Deleted a podcast. Use the filename above as the unique identifier [  unlink ("$upload_dir$file.$ext"); ] to delete from database


	// ================================================== DATABASE CALL ============================================================

	// DELETE A PODCAST FROM THE TABLE

	// Create connection
	$conn = new mysqli($db_servername, $db_username, $db_password, $db_name);

	// Check connection
	if (!$conn) {
	    die("DB Connection failed: " . mysqli_connect_error());
	}


	// SQL QUERY
	$sql = "DELETE FROM `listen4_db0`.`Podcasts` WHERE `Name` = '".$file.".".$ext."';";
	$result = mysqli_query($conn, $sql);

	if(!$result){

		$PG_mainbody .= '<p><b style="color:red;">***WARNING***: SQL query failed to delete entry in database. Error Message: '.mysqli_error($conn).'<br/>Constant a system admin immediately to resolve this issue.</b></p>';
	}

	// ** FOR CHANGES ONLY** make sure a row was affected, warn if not.
	$numOfRowsAffect = mysqli_affected_rows($conn);
	if($numOfRowsAffect != 1){
		$PG_mainbody .= '<p><b style="color:red;">***WARNING***: Database updated an incorrect number of rows. Expected to update 1 row, updated '.$numOfRowsAffect.'. A system admin must be alerted to this change for this podcast to properly function on the site.</b></p>';
	}

	mysqli_close($conn);

	// ================================================== END DATABASE CALL ============================================================


	########## REGENERATE FEED
	//include ("$absoluteurl"."core/admin/feedgenerate.php"); //(re)generate XML feed
	generatePodcastFeed(TRUE,NULL,FALSE); //Output in file
	##########

	$PG_mainbody .= '<p><a href=?p=archive&amp;cat=all>'._("Delete other episodes").'</a></p>';

} else { 
	$PG_mainbody .= _("No file to delete...");
}
?>