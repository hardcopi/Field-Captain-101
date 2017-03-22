<?php

$team = $_COOKIE["team"];

if ($_GET['team']) {
	$team = $_GET['team'];
	setcookie("team", $team);
} else {
	if (!$team) $team = 5484;
}

if ($_GET['events']) {
	$event = $_GET['events'];
	setcookie("events", $event);
} else {
	if (!$event) $event = '2017inmis';
}

$appHeader = "frc5484:alliance-selection:v01";
$oprData = "https://www.thebluealliance.com/api/v2/event/{$event}/stats?X-TBA-App-Id=" . $appHeader;
$eventList = "https://www.thebluealliance.com/api/v2/events/2017?X-TBA-App-Id=" . $appHeader;
$events = json_decode(file_get_contents($eventList));

$eventSelect = "<select name='events'>";
foreach ($events AS $key => $value) {
	$opening_date = new DateTime($value->start_date);
	$current_date = new DateTime();
	if ($opening_date < $current_date) {
		$eventSelect .= "<option value='{$value->key}'>{$value->name}</option>";
	}
}
$eventSelect .= "</select>";

$output = json_decode(file_get_contents($oprData));
$oprs = $output->oprs;
$dprs = $output->dprs;
$removeKeys = array();
$removeKeys = json_decode($_GET['remove']);
$removeKeys = array($removeKeys);

$teamOpr = round($oprs->$team, 2);
$oprs = (array) $oprs;

$output =  "<ul class=\"list-group\">";
$output .=  "<li class=\"list-group-item\">{$team}<span class=\"badge\">$teamOpr</span></li>";

unset($oprs[$team]);
arsort($oprs);

$count = 0;
$score = $teamOpr;

$removeKeys = explode(",", $_GET['remove']);
foreach($oprs AS $key => $value) {
	if ($key != $team && !in_array($key, $removeKeys)) {
		$count++;
		if ($count < 3) { 
			$score = $score + $value;
		}
		$newRemoveKeys = $_GET['remove'];
		$newRemoveKeys = $newRemoveKeys . "," . $key;
		$value = round($value, 2);
		$output .=  "<li id='row-$key' class='list-group-item team-row' data-team='$key' data-value='$value'>{$key} <span class=\"badge\">$value</span></li>";
	};
	
}
$output .=  "</ul>";
?>

<!DOCTYPE html>
<html>
<head>
	<title>Memento Vitam 5484 - Field Captain 101</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<link rel="stylesheet" href="style.css">
	<script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>	
	<style>
		body {  }
		.team-row { cursor: pointer; }
		table { border: 1px solid #000; margin: 0 auto; }
		.team-row { background-color: green; color: #FFF; }
		.red { background-color: #FF0000; color: #FFF; }
		.score-table { padding: 0px; }
	</style>
</head>
<body>
	<div id="bootstrap-menu" class="navbar navbar-default navbar-fixed-top" role="navigation">
		<div class="container-fluid">
			<div class="navbar-header"><a class="navbar-brand" href="#">5484 Memento Vitam</a>
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-menubuilder"><span class="sr-only">Toggle navigation</span><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
				</button>
			</div>
			<div class="collapse navbar-collapse navbar-menubuilder">
				<ul class="nav navbar-nav navbar-right">
					<li>
						<a data-toggle="modal" data-target="#setupModal">Setup <span class="glyphicon glyphicon-cog"></span></a>
					</li>
				</ul>
			</div>
		</div>
	</div>
	<div class="container">
		<center>
			<img src="logo.jpg" id="logo"><br>
			<span class="total">Score Prediction: <span id="total"></span></span><br>
			<input type="submit" value="Clear All" class="btn btn-primary" id="reset"> <input type="submit" value="Select All" class="btn btn-primary" id="select-all"> <br>			
			<table id="score-table" class="table">
				<thead>
					<tr>
						<th>Team #1</th>
						<th>Team #2</th>
						<th>Team #3</th>
					</tr>
				</thead>
				<tr>
					<td id="team1"><?= $team ?></td>
					<td id="team2"></td>
					<td id="team3"></td>
				</tr>
			</table>
			<br>
		</center>

		<?= $output ?>
	</div>


	<div id="setupModal" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">App Setup</h4>
				</div>
				<div class="modal-body">
					<form action="index.php">
						Team: <input type="text" name="team" placeholder="Enter your team Number" value="<?= $team ?>" class="form-control">
						<?= $eventSelect ?>
					</div>
					<div class="modal-footer">
						<input type="submit"  class="btn btn-default" value="Save">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>

		</div>
	</div>

	<script>
		$(document).ready(function () {
			var count = 1;
			var total = <?= $teamOpr ?>;
			$('.team-row').each(function(i, obj) {
				var id = $(this)[0].id;
				var value = $('#' + id).data('value');
				if (count < 3 && !$('#' + id).hasClass('red')) { 
					total = total + value;
					count++;
					$('#team' + count).html($('#' + id).data('team'));
				}					
			});
			total = Math.round(total * 100) / 100
			$('#total').html(total);


			$('.team-row').click(function(e) {
				$('#' + e.currentTarget.id).toggleClass('red');
				var count = 1;
				var total = <?= $teamOpr ?>;
				$('.team-row').each(function(i, obj) {
					var id = $(this)[0].id;
					var value = $('#' + id).data('value');
					if (count < 3 && !$('#' + id).hasClass('red')) { 
						total = total + value;
						count++;
						$('#team' + count).html($('#' + id).data('team'));
					}					
				});
				$('#total').html(total);
			});

			$('#reset').click(function() {
				$('.team-row').removeClass('red');
			});
			$('#select-all').click(function() {
				$('.team-row').addClass('red');
			});
		});
	</script>

</body>
</html>
