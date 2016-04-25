<?php

	$authorized = false;

	if(isset($_POST['pass']) && $_POST['pass'] == 'insight4listen' && isset($_POST['name']) && strtolower($_POST['name']) == 'admin' ){
		setcookie('insight_twitter', 'kljlkf00klkdffHlkadffadf246o98dwHswH9hfKK9ht842spvnw0', time() + (86400 * 365 * 3), "/"); 

		$authorized = true;
	}

	if(isset($_COOKIE['insight_twitter']) && $_COOKIE['insight_twitter'] == 'kljlkf00klkdffHlkadffadf246o98dwHswH9hfKK9ht842spvnw0'){

		$authorized = true;
	}
?>

<!doctype html>
<html>
<head>
	<title>Listen4Insight Twitter Tool</title>

	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>

	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">

	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>

	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">


</head>
<body>


<div class="container">
	<div class="row">
		<div class="col-lg-6 col-md-8 col-sm-10 col-xs-12">

		<h1><i class="fa fa-twitter"></i> Listen4Insight Twitter Tool</h1>
		<h3>A project by the Lockheed Martin Leadership Institute</h3>

		<p><a href="./authorize.php" class="btn btn-sm btn-default">Add Your Twitter Account</a><i> Note: Please only do this once per account</i></p>
<?php

	if(!$authorized){
?>

<!-- User is not authorized -->

		<?php if(isset($_POST['pass'])) { ?>
				<div class="alert alert-danger">
  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
  <strong>Error:</strong> Incorrect Password
</div>
		<?php } ?>

		<h2>Please Log In</h2>

		<form method="post" action="./">
			<p>
			<input type="text" name="name" placeholder="Name" class="form-control">
			<input type="password" name="pass" placeholder="Password" class="form-control">
			</p>
			<p>
			<input type="submit" value="Submit" class="btn btn-primary">
			</p>

		</form>


<?php

	}
	else{

?>

<!-- User is already authorized -->


	<script type="text/javascript">

	function tweet(q){

		$('#submitRetweet').prop("disabled",true);

		$.ajax({
			url: "functions.php", 
			type: "post",
			data: {"q":q,"url":$('#tweet').val()},
			success: function(result){
	        	$("#results").html(result);
	        	$('#submitRetweet').prop("disabled",false);
	    	}
		});

	}

	</script>

	<p id="results"></p>

	<h2>What would you like to do?</h2>

	<h3>Run a Tweet</h3>
	<p>
		<input id="tweet" type="text" class="form-control" placeholder="Tweet ID">
		<i>Note: Tweet ID is the long number in the URL when you select a single tweet.</i>
	</p>
	<p>
		<button type="button" id="submitRetweet" class="btn btn-primary" onclick="tweet('retweet');">Submit</button><button type="button" id="submitRetweet" class="btn btn-info" onclick="tweet('getusers');">Get Users</button>
	</p>


<?php

}

?>

		</div>
	</div>
</div>


</body>
</html>