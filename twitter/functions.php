<?php




if(!isset($_REQUEST['q'])){
	die('<p>No Parameters Sent</p>');
}


require 'codes.php';
require "twitteroauth/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;


  // Create connection
  $conn = new mysqli($db_servername, $db_username, $db_password, $db_name);

  // Check connection
  if (!$conn) {
  die("<p><b><font color=\"red\">ERROR: [Database Connection Error] ".mysqli_connect_error()." <br/>Contact a System Admin immediately to resolve this issue.</font></b></p>");
}


if($_REQUEST['q'] == 'getusers'){

	$sql = "SELECT * FROM TwitterUsers ORDER BY Name;";

	$result = mysqli_query($conn, $sql);

	if(!$result){

		die("<p><b><font color=\"red\">Database Error: [SQL Query Failed]. Error Message: ".mysqli_error($conn)."<br/>Please contact a System Admin immediately to resolve this issue.</font></b></p>");
	}

	print '<h3>Currently Active Accounts...</h3>';

	$ret = '<table class="table table-striped"><tr><th>Name</th><th>Handle</th></tr>';

	while($row = mysqli_fetch_assoc($result)) {

		$ret .= '<tr><td>'.$row['Name'].'</td><td>'.$row['Handle'].'</td></tr>';
	}

	$ret .= '</table>';

	print $ret;

}



if($_REQUEST['q'] == 'retweet' && isset($_REQUEST['url'])){


	$sql = "SELECT * FROM TwitterUsers ORDER BY Name;";

	$result = mysqli_query($conn, $sql);

	if(!$result){

		die("<p><b><font color=\"red\">Database Error: [SQL Query Failed]. Error Message: ".mysqli_error($conn)."<br/>Please contact a System Admin immediately to resolve this issue.</font></b></p>");
	}

	print '<h3>Retweeting a Tweet...</h3>';


	$tweet_id = $_REQUEST['url'];


	$connection = new TwitterOAuth($consumer_key, $consumer_secret);

	$content = $connection->get('statuses/show/'.$tweet_id);

	// check if tweet exists
	if ($connection->getLastHttpCode() != 200) {
		die('<div class="alert alert-danger">
		  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
		  <strong>Error</strong> Could not find tweet. Bad tweet ID?
		</div>');
	}

	$ret = '<table class="table table-striped"><tr><th>Name</th><th>Handle</th><th>Retweet</th><th>Favorite</td></tr>';

	while($row = mysqli_fetch_assoc($result)) {

		$connection = new TwitterOAuth($consumer_key, $consumer_secret,
		$row['AccessToken'],$row['AccessSecret']);

		$content = $connection->post('statuses/retweet/'.$tweet_id);

		if ($connection->getLastHttpCode() == 200) {
		    $ret .= '<tr><td>'.$content->user->name.'</td><td>'.$content->user->screen_name.'</td><td style="color:green;">success</td>';
		} else {
		    // Handle error case
		    $ret .= '<tr><td>'.$row['Name'].'</td><td>'.$row['Handle'].'</td><td style="color:red;">error</td>';
		}

		$content = $connection->post('favorites/create', ["id" => $tweet_id]);

		if ($connection->getLastHttpCode() == 200) {
		    $ret .= '<td style="color:green;">success</td></tr>';
		} else {
		    // Handle error case
		    $ret .= '<td style="color:red;">error</td></tr>';
		}
    }

    $ret .= '</table>';
    print $ret;


}

















?>