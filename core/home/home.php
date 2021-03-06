<?PHP


//picture grid and text
$PG_mainbody .= '<div class="row homeText"><div class="col-md-8 pictureGrid"><img src="images/pictureGrid.jpg" class="img-responsive" alt="Picture Grid"></div><br/>';
$PG_mainbody .= '<div class="col-md-4 homeText"><p>Welcome to Listen4Insight---a compilation of podcasts on creativity, innovation, and leadership. These podcasts are conducted by the Lockheed Martin Leadership Institute engineering students at Miami University in Oxford, Ohio.  Listen4Insight was developed in conjunction with Miamideas, a campus-wide initiative inspiring creativity and innovation. We know there are many people that use innovative thinking every day. Listen4Insight creates a home for these ideas to live, grow, and be shared.</p></div></div>';


//// This is the part of the homepage that shows the tiles
$PG_mainbody .= showRecentTiles();











// This function generates the recent pod cast tiles
function showRecentTiles(){
	//show recent episodes for each category

	$existingCategories = readPodcastCategories ($absoluteurl);

	for ($i = 0; $i <  count($existingCategories); $i++) {
    $key=key($existingCategories);
    $val=$existingCategories[$key];
		if ($val<> ' ') {
			$ret .= '<div class="clearfix"></div>';
			$ret .= showPodcastEpisodes(0,$key); //parameter, is bool yes or not (all episodes?), the second parameter is the category
			$ret .= '<div class="clearfix"></div>';// was after div: <a href="?p=archive&amp;cat='.$key.'">'.('View All Episodes in this Category').'</a>'
		}
     next($existingCategories);
    }

	$ret .= '<div style="clear:both;"><p><a href="'.$url.'?p=archive&amp;cat=all"><i class="fa fa-archive"></i> '._("View All Episodes").'</a></p></div>';

	return $ret;
}




?>
