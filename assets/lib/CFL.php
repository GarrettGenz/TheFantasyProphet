<?php

$cfldb_conn = pg_connect("host=" . $endpoint . " dbname=" .
    $cfl_database . " user=" . $cfl_user . " password=" . $cfl_password);

// Grab CFL posts. Used for recent posts section
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

// Returns a different header based on what type of projections are being displayed
function getCFLHeader($season, $week, $position, $player_id) {
    if ($player_id) {
        $player_name = pg_query_params($GLOBALS['cfldb_conn'],
            'SELECT  first_name || \' \' || initcap(last_name) AS "name",
                        position_abbreviation AS "position"
                FROM  players
                WHERE cfl_central_id = $1
                LIMIT 1;',
            array($player_id));

        // Print player name and position
        while ($row = pg_fetch_array($player_name)) {
            echo $row['name'] . ' | ' . $row['position'];
        }
    }
    else {
        echo "CFL $season Week $week Projections";
    }
}

// Generic function that returns one of three things:
// 1) Summary of all projections for the week
// 2) All projections for the week of a certain position
// 3) All projections for the season of a current player
function getCFLProjs($season, $week, $position, $player_id) {
    // If player_id is provided, return player projections for season
    if ($player_id) {
        getCFLProjsPlayer($season, $player_id);
    }
    // If position is set return projections for that position
    else if ($position) {
        getCFLProjsPosition($position, $season, $week);
    }
    else {
        // Return a summary of the top five players at each position
        getCFLProjsSummary($season, $week);
    }
}

function getCFLProjsPlayer($season, $player_id) {
    echo "<table class=\"proj-table\"><tr><td>";
    getCFLCellPlayer($season, $player_id);
    echo "</td></tr></table>";
}

// Grab CFL projections into a table
// Show top five for each position
function getCFLProjsSummary($season, $week)
{
    echo "<table class=\"proj-table\"><tr><td>";
    getCFLCellPosition('QB', $season, $week, 5);
    echo "</td><td>";
    getCFLCellPosition('RB', $season, $week, 5);
    echo "</td></tr><tr><td>";
    getCFLCellPosition('WR', $season, $week, 5);
    echo "</td><td>";
    getCFLCellPosition('DST', $season, $week, 5);
    echo "</td></tr></table>";
}

// Grab CFL projections into a table
// Show top 25 for a specific position
function getCFLProjsPosition($position, $season, $week)
{
    echo "<table class=\"proj-table\"><tr><td>";
    getCFLCellPosition($position, $season, $week, 25);
    echo "</td></tr></table>";
}

// Table partial to show projections for a specifid position
function getCFLCellPlayer($season, $player_id)
{
    $result = pg_query_params($GLOBALS['cfldb_conn'],
        'SELECT  season, week,
                         abbreviation,
                         round(proj_points, 1) AS "proj_points"
                FROM  player_proj_points ppp JOIN players p ON ppp.cfl_central_id = p.cfl_central_id
                WHERE season = $1
                AND   ppp.cfl_central_id = $2
                ORDER BY week DESC',
        array($season, $player_id));

    echo "<div class=\"proj-cell\">
                    <span class=\"proj-header\">";

    echo "</span>
                    <table class=\"table table-condensed\">
                        <thead>
                            <tr><th>Season</th><th>Week</th><th>Team</th><th>Proj</th></tr>
                        </thead>
                        <tbody>";

    // Print out each player as a row
    while ($row = pg_fetch_row($result)) {
        echo "<tr><td>$row[0]</td><td>$row[1]</td><td>$row[2]</td><td>$row[3]</td></tr>";
    }

    echo "</tbody></table></div>";
}

// Table partial to show projections for a specifid position
function getCFLCellPosition($player_type, $season, $week, $num_rows)
{
    if ($player_type != 'DST') {
        $result = pg_query_params($GLOBALS['cfldb_conn'],
                            'SELECT row_number() OVER (ORDER BY proj_points DESC),
                                           first_name || \' \' || initcap(last_name),
                                           abbreviation,
                                           round(proj_points, 1) AS "proj_points",
                                           p.cfl_central_id AS "player_id"
                                    FROM  player_proj_points ppp JOIN players p ON ppp.cfl_central_id = p.cfl_central_id
                                    WHERE p.position_abbreviation = $1
                                    AND   ppp.season = $2
                                    AND   ppp.week = $3
                                    LIMIT $4',
                                    array($player_type, $season, $week, $num_rows));

        echo "<div class=\"proj-cell\">
                        <span class=\"proj-header\">";

        if ($player_type == 'QB') {
            echo "<a href=\"projections.php?league=CFL&position=$player_type&season=$season&week=$week\">Quarterbacks</a>";
        } else if ($player_type == 'RB') {
            echo "<a href=\"projections.php?league=CFL&position=$player_type&season=$season&week=$week\">Runningbacks</a>";
        } else if ($player_type == 'WR') {
            echo "<a href=\"projections.php?league=CFL&position=$player_type&season=$season&week=$week\">Wide Receivers</a>";
        }

        echo "</span>
                        <table class=\"table table-condensed\">
                            <thead>
                                <tr><th>Rank</th><th>Name</th><th>Team</th><th>Proj</th></tr>
                            </thead>
                            <tbody>";

        // Print out each player as a row
        while ($row = pg_fetch_row($result)) {
            echo "<tr><td>$row[0]</td><td><a href=\"projections.php?league=CFL&season=$season&player_id=$row[4]\">$row[1]</a></td><td>$row[2]</td><td>$row[3]</td></tr>";
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
                                    LIMIT $3',
            array($season, $week, $num_rows));

        echo "<div class=\"proj-cell\">
                        <span class=\"proj-header\"><a href=\"projections.php?league=CFL&position=$player_type&season=$season&week=$week\">Defenses</a></span>
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

