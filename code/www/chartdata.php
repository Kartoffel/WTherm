<?php
/** chartdata.php
 * WTherm v2
 * Author: Niek Blankers <niek@niekproductions.com>
 *
 * Used to generate JSON code for the charts
 */
session_start();
include('../base.php');

// Prevent caching
header("Expires: Friday, 16 Jan 2015 17:30 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check if the user is logged in
if(!isset( $_SESSION['username'])){
	exit("LOGIN");
}

$days = isset($_GET['days'])? $_GET['days'] : 1;
if($days != 7 && $days != 365) $days = 1;


// Fetch chart data
$database = new Database();
if($days < 8){
	$database->query('SELECT * FROM ( SELECT @row := @row +1 AS rownum, Time, T, T_target, T_o, Heating FROM ( SELECT @row :=0) r, log ) ranked WHERE rownum % :interval = 1 AND Time >= from_unixtime(300 * round(unix_timestamp(now())/300)) - INTERVAL :hours HOUR ORDER BY Time ASC');
	$database->bind(':interval', $days*12);
	$database->bind(':hours', $days*24);
}else{
	$database->query('SELECT DATE(Time) AS Time, AVG(T) AS T, AVG(T_o) AS T_o FROM log WHERE Time >= NOW() - INTERVAL :hours HOUR GROUP BY DATE(Time) ORDER BY Time ASC');
	$database->bind(':hours', $days*24);
}
$database->execute();
$log = $database->resultset();

// Set up data array
$data = array(
	"labels" => array(),
	"datasets" => array(
		array(
			"title" => "Inside",
			"fillColor" => "rgba(220,220,220,0.2)",
            "strokeColor" => "rgba(220,220,220,1)",
            "pointColor" => "rgba(220,220,220,1)",
            "pointStrokeColor" => "#fff",
            "pointHighlightFill" => "#fff",
            "pointHighlightStroke" => "rgba(220,220,220,1)",	
			"data" => array(),
		),
		array(
			"title" => "Outside",
			"fillColor" => "rgba(151,187,205,0.2)",
            "strokeColor" => "rgba(151,187,205,1)",
            "pointColor" => "rgba(151,187,205,1)",
            "pointStrokeColor" => "#fff",
            "pointHighlightFill" => "#fff",
            "pointHighlightStroke" => "rgba(151,187,205,1)",	
			"data" => array(),
		),
	),
);

// Fill data array
foreach($log as $logPoint){
	switch($days){
		case 1:
			$time = date('H:i', strtotime($logPoint['Time']));
			break;
		case 7:
			$time = date('D H:i', strtotime($logPoint['Time']));
			break;
		case 365:
			$time = date('d-m-Y H:i', strtotime($logPoint['Time']));
			break;
		default:
			$time = "invalid";
	}
	
	array_push($data['labels'], $time);
	array_push($data['datasets'][0]['data'], $logPoint['T']);
	array_push($data['datasets'][1]['data'], $logPoint['T_o']);
}

// Convert array into JSON
$jsonData = json_encode($data, JSON_NUMERIC_CHECK);
print_r($jsonData);
?>