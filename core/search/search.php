<?PHP
/*
	All Code by Matt DePero
*/

	
if(!isset($_REQUEST['s']) || !isset($_REQUEST['p']) || strtolower($_GET['p'])!="search"){
	$PG_mainbody .= 'Reached page on error.<br/><br/><a href="?home">Go Back</a>';

}else{

	// ================================================== DATABASE CALL ============================================================

	// ADD NEW CATEGORY TO DATABASE

	// Create connection
	$conn = new mysqli($db_servername, $db_username, $db_password, $db_name);

	// Check connection
	if (!$conn) {
		$PG_mainbody .= "<p><b><font color=\"red\">ERROR: [Database Connection Error] ".mysqli_connect_error()." <br/>Contact a System Admin immediately to resolve this issue.</font></b></p>";
	}





	// === Database Columns to Search ===
	$search_columns = array("Title", "Long_Description","Short_Description", "Key_Words", "IE_Name", "IE_Bio", "Author");



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