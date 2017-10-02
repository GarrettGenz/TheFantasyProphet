<?php

$cfldb_conn = pg_connect("host=" . $endpoint . " dbname=" .
    $cfl_database . " user=" . $cfl_user . " password=" . $cfl_password);

// Grab CFL posts
// Returns array with Key: Title, Values: Post Date, URL
function getCFLPosts() {
    $result = pg_query($GLOBALS['cfldb_conn'], "SELECT	(SELECT MIN(date_start) FROM games WHERE games.season = ppp.season AND games.week = ppp.week) AS \"post_date\",
										CAST(season AS text) || ' Week ' || CAST(week AS text) || ' CFL  Player Projections' AS \"title\",
										ppp.season,
										ppp.week
									FROM player_proj_points ppp
									GROUP BY ppp.season, ppp.week");

    while ($row = pg_fetch_row($result)) {
        // Key: Title, Values: post_date, URL params
        $recent_posts[$row[1]] = array($row[0], "projections.php?league=CFL&season=$row[2]&week=$row[3]");
    }

    return $recent_posts;
}

// Grab CFL projections into a table
function getCFLProjs($season, $week)
{
    echo "<table class=\"proj-table\"><tr><td>";
    getCFLCell('QB', $season, $week);
    echo "</td><td>";
    getCFLCell('RB', $season, $week);
    echo "</td></tr><tr><td>";
    getCFLCell('WR', $season, $week);
    echo "</td><td>";
    getCFLCell('DST', $season, $week);
    echo "</td></tr></table>";
}

// Table partial to show projections
function getCFLCell($player_type, $season, $week)
{
    if ($player_type != 'DST') {
        $result = pg_query_params($GLOBALS['cfldb_conn'],
                            'SELECT row_number() OVER (ORDER BY proj_points DESC),
                                           first_name || \' \' || initcap(last_name),
                                           abbreviation,
                                           round(proj_points, 1) AS "proj_points"
                                    FROM  player_proj_points ppp JOIN players p ON ppp.cfl_central_id = p.cfl_central_id
                                    WHERE p.position_abbreviation = $1
                                    AND   ppp.season = $2
                                    AND   ppp.week = $3
                                    LIMIT 5',
                                    array($player_type, $season, $week));

        echo "<div class=\"proj-cell\">
                        <span class=\"proj-header\">";

        if ($player_type == 'QB') {
            echo "Quarterbacks";
        } else if ($player_type == 'RB') {
            echo "Runningbacks";
        } else if ($player_type == 'WR') {
            echo "Wide Receivers";
        }

        echo "</span>
                        <table class=\"table table-condensed\">
                            <thead>
                                <tr><th>Rank</th><th>Name</th><th>Team</th><th>Proj</th></tr>
                            </thead>
                            <tbody>";

        // Print out each player as a row
        while ($row = pg_fetch_row($result)) {
            echo "<tr><td>$row[0]</td><td>$row[1]</td><td>$row[2]</td><td>$row[3]</td></tr>";
        }

        echo "</tbody></table></div>";
    } else {
        $result = pg_query_params($GLOBALS['cfldb_conn'],
            'SELECT row_number() OVER (ORDER BY proj_points DESC),
                           t.full_name,
                           round(proj_points, 2) AS "proj_points"
                    FROM defense_projections dp JOIN teams t ON dp.team_id = t.team_id
                                                JOIN games g ON dp.game_id = g.game_id
                                    WHERE season = $1
                                    AND   week = $2
                                    LIMIT 5',
            array($season, $week));

        echo "<div class=\"proj-cell\">
                        <span class=\"proj-header\">Defenses</span>
                        <table class=\"table table-condensed\">
                            <thead>
                                <tr><th>Rank</th><th>Name</th><th>Proj</th></tr>
                            </thead>
                            <tbody>";

        // Print out each team as a row
        while ($row = pg_fetch_row($result)) {
            echo "<tr><td>$row[0]</td><td>$row[1]</td><td>$row[2]</td></tr>";
        }

        echo "</tbody></table></div>";
    }
}