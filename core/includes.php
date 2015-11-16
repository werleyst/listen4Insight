<?php
############################################################
# PODCAST GENERATOR
#
# Created by Alberto Betella
# http://podcastgen.sourceforge.net
# 
# This is Free Software released under the GNU/GPL License.
############################################################

//// Security code, avoids cross-site scripting (Register Globals ON)
if (isset($_REQUEST['GLOBALS']) OR isset($_REQUEST['absoluteurl']) OR isset($_REQUEST['theme_path'])) { exit; } 



/*
	+-------------------------------------+
	|        Database Credentials         |
	+-------------------------------------+
*/

$db_servername = "localhost";
$db_name = "listen4_db0";
$db_username = "listen4_user0";
$db_password = "AHtQof9TWMwn";



//if config.php doesn't exist, stop
if (!file_exists("config.php")) {
header("HTTP/1.1 301 Moved Permanently");
header("Location: setup/"); // redirect to setup
} 

include("config.php"); 

include_once($absoluteurl."core/functions.php"); //LOAD ONCE

include($absoluteurl."core/language.php");


?>