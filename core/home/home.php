<?PHP






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
			$ret .= '<div class="clearfix"></div><a href="?p=archive&amp;cat='.$key.'">'.('View All Episodes in this Category').'</a>';
		}
     next($existingCategories);
    }
		
	$ret .= '<div style="clear:both;"><p><a href="'.$url.'?p=archive&amp;cat=all"><i class="fa fa-archive"></i> '._("View All Episodes").'</a></p></div>';

	return $ret;
}




?>