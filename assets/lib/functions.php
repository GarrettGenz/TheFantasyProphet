<?php
include 'config.php';

// Separate database connections are required for each database in PostgreSQL
$web_db_conn = pg_connect("host=" . $endpoint . " dbname=" .
                            $web_database . " user=" . $web_user . " password=" . $web_password);

$cfldb_conn = pg_connect("host=" . $endpoint . " dbname=" .
                            $cfl_database . " user=" . $cfl_user . " password=" . $cfl_password);

$nfldb_conn = pg_connect("host=" . $endpoint . " dbname=" .
                            $nfl_database . " user=" . $nfl_user . " password=" . $nfl_password);

function getRecentPosts($web_conn, $cfl_conn, $nfl_conn) {
    $recent_posts = array();

    echo "<h3>Recent Posts</h3>
		  <hr>";

    $web_posts = getWebPosts($web_conn);

    foreach ($web_posts as $title => $values) {
        $recent_posts[$title] = $values[1];
    }
	
    $cfl_posts = getCFLPosts($cfl_conn);

    foreach ($cfl_posts as $title => $values) {
        $recent_posts[$title] = $values[1];
    }

    // Sort array in descending order of when post was made
    arsort($recent_posts);

	// Only return the top 10 recent posts
    $returned_posts = array_slice($recent_posts, 0, 10, true);

    foreach ($returned_posts as $title => $date) {
         echo "<p> $title </p>";
    }
}

// Grab posts that are manually created
function getWebPosts($web_conn) {
    $result = pg_query($web_conn, "SELECT * FROM posts LIMIT 10");

    while ($row = pg_fetch_row($result)) {
        // Key: Title, Values: 'WEB', post_date, post_id
        $recent_posts[$row[2]] = array('WEB', $row[1], $row[0]);
    }

    return $recent_posts;
}

// Grab CFL posts
function getCFLPosts($cfl_conn) {
    $result = pg_query($cfl_conn, "SELECT	(SELECT MIN(date_start) FROM games WHERE games.season = ppp.season AND games.week = ppp.week) AS \"post_date\",
										CAST(season AS text) || ' Week ' || 
										CAST(week AS text) || ' CFL  Player Projections' AS \"title\",
										ppp.season,
										ppp.week
									FROM player_proj_points ppp
									GROUP BY ppp.season, ppp.week");

    while ($row = pg_fetch_row($result)) {
        // Key: Title, Values: 'CFL', post_date, season, week
        $recent_posts[$row[1]] = array('CFL', $row[0], $row[2], $row[3]);
    }

    return $recent_posts;
}



