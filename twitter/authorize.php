<?php
//add autoload note:do check your file paths in autoload.php
require "twitteroauth/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;

  // codes
  require 'codes.php';
  $callback = 'http://listen4insight.com/twitter/authorize.php/?success';

//this code will run when returned from twitter after authentication
if(isset($_REQUEST['success'])){

  
  $oauth_token=$_SESSION['oauth_token'];unset($_SESSION['oauth_token']);
  $connection = new TwitterOAuth($consumer_key, $consumer_secret);
 //necessary to get access token other wise u will not have permission to get user info
  $params=array("oauth_verifier" => $_GET['oauth_verifier'],"oauth_token"=>$_GET['oauth_token']);
  $access_token = $connection->oauth("oauth/access_token", $params);
  //now again create new instance using updated return oauth_token and oauth_token_secret because old one expired if u dont u this u will also get token expired error
  $connection = new TwitterOAuth($consumer_key, $consumer_secret,
  $access_token['oauth_token'],$access_token['oauth_token_secret']);


  $access_oauth_token = $access_token['oauth_token'];
  $access_oauth_secret = $access_token['oauth_token_secret'];


  $content = $connection->get("account/verify_credentials");


  if(!isset($content->name)){

    die('Unable to Verify Account');

  }

  // Create connection
  $conn = new mysqli($db_servername, $db_username, $db_password, $db_name);

  // Check connection
  if (!$conn) {
    die("<p><b><font color=\"red\">ERROR: [Database Connection Error] ".mysqli_connect_error()." <br/>Contact a System Admin immediately to resolve this issue.</font></b></p>");
  }




  $sql = "INSERT INTO `listen4_db0`.`TwitterUsers` (`Id`, `Name`, `Handle`, `AccessToken`, `AccessSecret`) VALUES (NULL, '".$content->name."', '".$content->screen_name."', '".$access_oauth_token."', '".$access_oauth_secret."');";

  $result = mysqli_query($conn, $sql);

  if(!$result){

    die("<p><b><font color=\"red\">Database Error: [SQL Query Failed]. Error Message: ".mysqli_error($conn)."<br/>Please contact a System Admin immediately to resolve this issue.</font></b></p>");
  }


  echo '<html><head><title>L4I Twitter Authorization</title></head><body><h1>Thank You '.$content->name.'.</h1><p>You have successfully been authorized with the Listen4Insight Twitter App. You can revoke access at any time by going to the "Apps" section of your Twitter settings.</p><p>Contact Matt DePero &lt;<a href="mailto:deperomm@miamioh.edu">deperomm@miamioh.edu</a>&gt; with questions.</p></body></html>';
}
else{
  //this code will return your valid url which u can use in iframe src to popup or can directly view the page as its happening in this example

  $connection = new TwitterOAuth($consumer_key, $consumer_secret);
  $temporary_credentials = $connection->oauth('oauth/request_token', array("oauth_callback" =>$callback));
  $_SESSION['oauth_token']=$temporary_credentials['oauth_token'];       $_SESSION['oauth_token_secret']=$temporary_credentials['oauth_token_secret'];$url = $connection->url("oauth/authorize", array("oauth_token" => $temporary_credentials['oauth_token']));
// REDIRECTING TO THE URL
  header('Location: ' . $url); 
}
?>