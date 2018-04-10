<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	
	<!-- Required Javascript -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	
	<!-- Custom CSS -->
	<link href="assets/css/main.css" rel="stylesheet">

    <!-- Custom PHP functions to populate page -->
    <?php include 'assets/lib/functions.php'; ?>

    <?php
        // Set variables if provided by URL
        if (isset($_GET["league"])) {
            $league = $_GET["league"];
        }
        else {
            $league = NULL;
        }
        if (isset($_GET["week"])) {
            $week = $_GET["week"];
        }
        else {
            $week = NULL;
        }
        if (isset($_GET["season"])) {
            $season = $_GET["season"];
        }
        else {
            $season = NULL;
        }
		if (isset($_GET["position"])) {
		    $position = $_GET["position"];
        }
        else {
		    $position = NULL;
        }
        if (isset($_GET["player_id"])) {
            $player_id = $_GET["player_id"];
        }
        else {
            $player_id = NULL;
        }
    ?>

    <meta charset="UTF-8">
    <title>The Fantasy Prophet</title>
</head>
<body>

<?php include 'assets/partials/header.php'; ?>
  
<div class="container-fluid text-center">    
  <div class="row content main-content">
    <div class="col-sm-9 text-left"> 
		<div class="content-pane">
              <?php
                if ($league == 'CFL') {
                    echo '<h1>';
                    getCFLHeader($season, $week, $position, $player_id);
                    echo '</h1>';
                    getCFLProjs($season, $week, $position, $player_id);
                }
              ?>
		</div>	
    </div>
    <div class="col-sm-3 sidenav text-left">
		<div class="sidenav-content">
        <?php getRecentPosts($web_db_conn); ?>
		</div>
	</div>
  </div>
</div>

<?php include 'assets/partials/footer.php'; ?>

</body>
</html>