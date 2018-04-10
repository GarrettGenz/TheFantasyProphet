<?php
include 'config.php';
include 'CFL.php';

// Separate database connections are required for each database in PostgreSQL
$web_db_conn = pg_connect("host=" . $endpoint . " dbname=" .
                            $web_database . " user=" . $web_user . " password=" . $web_password);

function getRecentPosts() {
    $recent_posts = array();

    echo "<h3>Recent Posts</h3>
		  <hr>";

    $web_posts = getWebPosts($GLOBALS['web_db_conn']);

    foreach ($web_posts as $title => $values) {
        // Pass date and partial URL:
        // posts.php?post_id=1
        $recent_posts[$title] = array($values[0], $values[1]);
    }
	
    $cfl_posts = getCFLPosts();

    foreach ($cfl_posts as $title => $values) {
        // Pass date and partial URL:
        // player_projections.php?league=CFL&season=2017&week=3
        $recent_posts[$title] = array($values[0], $values[1]);
    }
	
	// Get array of post dates for sorting
	foreach ($recent_posts as $title => $values) {
		$post_dates[$title] = $values[0];
	}

    // Sort array in descending order of when post was made
    array_multisort($post_dates, SORT_DESC, $recent_posts);

	// Only return the top 10 recent posts
    $returned_posts = array_slice($recent_posts, 0, 10, true);

	// Build each post in HTML
    foreach ($returned_posts as $title => $values) {
         echo "<p> <a href=\"$values[1]\">$title</a> </p>";
    }
}

// Grab posts that are manually created
// Returns array with Key: Title, Values: Post Date, URL
function getWebPosts() {
    $result = pg_query($GLOBALS['web_db_conn'], "SELECT * FROM posts LIMIT 10");

    while ($row = pg_fetch_row($result)) {
        // Key: Title, Value: post_date, URL params 
        $recent_posts[$row[2]] = array($row[1], "posts.php?post_id=$row[0]");
    }

    return $recent_posts;
}

// Grab title and content for specific post
function getPost($post_id) {
    $result = pg_query_params($GLOBALS['web_db_conn'], "SELECT title, content FROM posts WHERE post_id = $1", array($post_id));

    $row = pg_fetch_row($result);

    $title = $row[0];
    $content = $row[1];

    return array($title, $content);
}

// Function to convert position abbreviation to position name
function getPositionName($position_abbr) {
	if ($position_abbr == 'QB') {
		return 'Quarterbacks';
	}
	else if ($position_abbr == 'RB') {
		return 'Runningbacks';
	}
	else if ($position_abbr == 'WR') {
		return 'Wide Receivers';
	}
	else if ($position_abbr == 'TE') {
		return 'Tight Ends';
	}
	else if ($position_abbr == 'DST') {
		return 'Defenses';
	}
	else {
		return $position_abbr;
	}
}
