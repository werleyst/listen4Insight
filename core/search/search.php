<?PHP

	
if(!isset($_REQUEST['s']) || !isset($_REQUEST['p']) || strtolower($_GET['p'])!="search"){
	$PG_mainbody .= 'Reached page on error.<br/><br/><a href="?home">Go Back</a>';

}else{

	// ================================================== DATABASE CALL ============================================================

	// ADD NEW CATEGORY TO DATABASE

	// Create connection
	$conn = new mysqli($db_servername, $db_username, $db_password, $db_name);

	// Check connection
	if (!$conn) {
	    die("DB Connection failed: " . mysqli_connect_error());
	}


	// Parse search input
	$pattern = "%".str_replace(" ", "%",$_GET['s'])."%";


	// SQL QUERY
	$sql = "SELECT * FROM Podcasts WHERE Title LIKE '".$pattern."';";
	$result = mysqli_query($conn, $sql);

	if(!$result){

		die("Database Error: SQL Query Failed");
	}


	// Process Results of Query
	if (mysqli_num_rows($result) > 0) {
    // output data of each row
	    while($row = mysqli_fetch_assoc($result)) {
	        $PG_mainbody .=  '<div><a href="?name='.$row['Name'].'">'.$row['Title'].'</a></div>';
	    }
	} else {
	    $PG_mainbody .= 'No results found. <br/><br/><a href="?home">Home</a>';
	}



	mysqli_close($conn);

	// ================================================== END DATABASE CALL ============================================================


}

?>