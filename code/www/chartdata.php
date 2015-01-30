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
	$database->bind(':interval', 2*$days);
	$database->bind(':hours', $days*24);
}else{
	$database->query('SELECT DATE(Time) AS Time, FORMAT(AVG(T),1) AS T, FORMAT(AVG(T_o), 1) AS T_o FROM log WHERE Time >= NOW() - INTERVAL :hours HOUR GROUP BY DATE(Time) ORDER BY Time ASC');
	$database->bind(':hours', $days*24);
}
$database->execute();
$log = $database->resultset();

$beginTime = $log[0]['Time'];
$endTime = $log[count($log)-1]['Time'];

// Set up data array
$data = array(
	"labels" => array(),
	"xBegin" => timestampToJSDate($beginTime),
	"xEnd" => timestampToJSDate($endTime),
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
			"xPos" => array(),
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
			"xPos" => array(),
		),
	),
);

$lastMonth = 0;
$lastDay = 0;
$lastHour = 25;


// Fill data array
foreach($log as $key=>$logPoint){
	$pointTime = $logPoint['Time'];
	$time = timestampToJSDate($pointTime);

	switch($days){
		case 1:
			if($lastHour != date('G',strtotime($pointTime))){
				$lastHour = date('G',strtotime($pointTime));
				array_push($data['labels'], $time);
			}
			break;
		case 7:
			if($lastDay != date('j',strtotime($pointTime))){
                        	$lastDay = date('j',strtotime($pointTime));
                        	array_push($data['labels'], $time);
			}
			break;
		default:
			if($lastMonth != date('m',strtotime($pointTime))){
				$lastMonth = date('m',strtotime($pointTime));
				array_push($data['labels'], $time);
                        }
	}
	
	if($key % (2*days)) continue;
	array_push($data['datasets'][0]['data'], $logPoint['T']);
	array_push($data['datasets'][0]['xPos'], $time);
	
	array_push($data['datasets'][1]['data'], $logPoint['T_o']);
	array_push($data['datasets'][1]['xPos'], $time);
}

// Convert array into JSON
$jsonData = json_encode($data, JSON_NUMERIC_CHECK);
//$jsonData = preg_replace("/(('|\")%%|%%(\"|'))/",'', $jsonData);
echo($jsonData);

function timestampToJSDate($timestamp){
	return date('c',strtotime($timestamp));
}
?>
