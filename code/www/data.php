<?php 
/** data.php
 * WTherm v2
 * Author: Niek Blankers <niek@niekproductions.com>
 *
 * This file handles javascript communication to the database.
 * It outputs responses to the queries.
 * If the user is not logged in, output "LOGIN"
 * If something goes wrong, output "FAIL"
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

// No action has been specified
if(!isset($_GET['func']))	exit("FAIL");

/*-------------------------------------------------------------------
| Get contents of the status table from the database
| -------------------------------------------------------------------*/
$database = new Database();
$database->query('SELECT * FROM status');
$database->execute();
$status = $database->single();

// Handle the requests
switch ($_GET['func']) { 
	// Uptime
	case 'UPTIME':
		$tmp = explode(' ', file_get_contents('/proc/uptime'));
		exit(secondsToTime(floor($tmp[0])));
		break;
	// Override state
	case 'OVERRIDE-STATUS':
		exit($status['Override']);
		break;
	// Heating state
	case 'HEATING-STATUS':
		exit($status['Heating']);
		break;
	// Requests the current temperature
	case 'CURTEMP': 
		exit($status['T']);
		break;
	// Requests the target temperature
	case 'TARGETTEMP': 
		exit($status['T_target']);
		break;
	// Time to next setPoint
	case 'TIME-POINT':
		$database->query('SELECT * FROM schedule ORDER BY day ASC, time ASC');
		$database->execute();
		$schedule = $database->resultset();
		
		list($T_target, $target_t) = nextTarget($schedule);
		if($T_target == $status['T_target'])
			exit("In ".round_up($target_t,0)." hours");
		else
			exit("For ".round_up($target_t,0)." hours");
		break;
	// Enables the override
	case 'ENA-OVERRIDE': 
		$database->query('UPDATE status SET Override=1');
		$database->execute();
		update();
		exit("OK");
		break;
	// Disables the override
	case 'DIS-OVERRIDE': 
		$database->query('UPDATE status SET Override=0');
		$database->execute();
		update();
		exit("OK");
		break;
	case 'TEMP': // Sets the target temp
	if(isset($_GET['value']) && floatval($_GET['value'])){
			$temp = floatval($_GET['value']);
			if($temp >= $CONFIG['min_temp'] && $temp <= $CONFIG['max_temp']){
				$database->query('UPDATE status SET T_target=:targettemp');
				$database->bind(':targettemp', $temp);
				$database->execute();
				$database->query('UPDATE status SET Override=1');
				$database->execute();
				update();
				exit("OK");
			}else{
				exit("FAIL");
			}
		}else{
			exit("FAIL");
		}
		break;
	default:
		exit("FAIL");
		break;
}



/**
 * Convert uptime in seconds to a readable string
 *
 * @param  integer  $seconds  Uptime (in seconds)
 * @return String   Readable uptime
 */ 
function secondsToTime($seconds){
    $dtF = new DateTime("@0");
    $dtT = new DateTime("@$seconds");
    return $dtF->diff($dtT)->format('%ad %hh %im %ss');
}

/**
 * Round up
 *
 * @param  number  $number  Number to round
 * @param  number  $precision  Decimals to round to
 * @return number   Rounded number
 */ 
function round_up($number, $precision = 2){
    $fig = (int) str_pad('1', $precision, '0');
    return (ceil($number * $fig) / $fig);
}

$db = null;
?>
