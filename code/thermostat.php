<?php
/** thermostat.php
 * WTherm v2
 * Author: Niek Blankers <niek@niekproductions.com>
 *
 * The actual thermostat function
 * Controlled by the schedule and status in the database
 * Run this script with an $argv variable to prevent logging
 */
include('base.php');
$heater = false;

/*-------------------------------------------------------------------
| Connect to the database, fetch the status
| -------------------------------------------------------------------*/
$database = new Database();
$database->query('SELECT * FROM status');
$database->execute();
$status = $database->single();

/*-------------------------------------------------------------------
| Fetch the schedule
| -------------------------------------------------------------------*/
$database->query('SELECT * FROM schedule ORDER BY day ASC, time ASC');
$database->execute();
$schedule = $database->resultset();

/*-------------------------------------------------------------------
| Get temperature measurements
| -------------------------------------------------------------------*/
// Fetch the inside and outside temperature
list($T, $T_o) = HW_temp();

// If unable to fetch the temperature, check if the measurements are still valid
if($T === false || $T_o === false){
	$measurementFailed = true;
	validateTemp($status);
}

// Set the target
$target = floatval($status['T_target']);

/*-------------------------------------------------------------------
| Temperature / time prediction
| -------------------------------------------------------------------*/
// Get the next target temperature and the available time to reach it
list($T_target, $target_t) = nextTarget($schedule);

// Check if a target is available
if($T_target !== false && $target_t !== false){
	// Use the thermal model to calculate the number of hours it would take to reach the desired temperature
	$tmpCalc = new tempCalc;
	$heatingTime = $tmpCalc->heatingTime($T,$T_target,$T_o,1,true);
	
	// Check if the available amount of time is less than the required amount of time
	// The room should still be heated in time, since the thermal model is pessimistic as it was made when all of the furniture in the room had cooled down to a very low temperature
	if($target_t < $heatingTime){
		// The heater should be switched on
		$target = $T_target;
		$heater = true;
	}else{
		$heater = false;
	}
	
	// Linearly extrapolate the current temperature and that of 15 minutes ago 
	$database->query('SELECT Time, T FROM `log` WHERE Time < (NOW() - INTERVAL 14 MINUTE) ORDER BY TIME DESC LIMIT 1');
	$database->execute();
	$previousRecord = $database->single();
	
	$lastTime = strtotime($previousRecord['Time']);
	$lastTemp = $previousRecord['T'];
	
	if($T > $lastTemp){
		// Calculate the temperature rise per hour
		$elapsedTime = time() - $lastTime;
		$degPerHour = ($T - $lastTemp) / ($elapsedTime / 3600); 
		
		// Use $degPerHour to extrapolate the temperature at the target time
		$extrapolatedTemp = $T + $degPerHour * $target_t;
		
		// If the temperature appears to be rising too quickly, turn off the heater
		if($extrapolatedTemp > $T_target) $heater = false;
	}
}

/*-------------------------------------------------------------------
| Manual operation
| -------------------------------------------------------------------*/
if($status['Override']){
	$target = $status['T_target'];
	if($target > $CONFIG['max_temp'] || $target < $CONFIG['min_temp']) $target = $CONFIG['min_temp'];
	
	if($target > $T ) $heater = true; //switch on heater
	else $heater = false; //switch off heater	
}


/*-------------------------------------------------------------------
| Actually controlling the GPIO that switches the relay to open/close the heat valve
| -------------------------------------------------------------------*/
heat($heater);

/*-------------------------------------------------------------------
| Logging & Updating the status table
| -------------------------------------------------------------------*/

$database->query('UPDATE status SET T=:T, T_o=:T_o, T_target=:T_target, Heating=:Heating, Last_update=now()');
$database->bind(':T', $T);
$database->bind(':T_o', $T_o);
$database->bind(':T_target', $target);
$database->bind(':Heating', $heater);
$database->execute();

// Only log if an argv variable hasn't been passed to the script
if(!isset($argv[1])){
	if(isset($measurementFailed)) $database->query('INSERT INTO log (T, T_target, T_o, Heating) VALUES (:T, :T_target, :T_o, :Heating)');
	else $database->query('INSERT INTO log (Time, T, T_target, T_o, Heating) VALUES (now(), :T, :T_target, :T_o, :Heating)');
		
	$database->bind(':T', $T);
	$database->bind(':T_o', $T_o);
	$database->bind(':T_target', $target);
	$database->bind(':Heating', $heater);
	$database->execute();
}

// Close the database connection
$database = null;


/*--------------------------------------------------------------------------------------------------------------------------------------
| Functions
| -------------------------------------------------------------------*/

/**
 * Control the heater
 *
 * @param boolean	$switch	enable or disable the heater
 */ 
function heat($switch){
	global $CONFIG;
	if($switch){
		// Turn on heater
		shell_exec('/usr/local/bin/gpio write '.$CONFIG['heating_pin'].' 0');
	}else{
		// Turn off heater
		shell_exec('/usr/local/bin/gpio write '.$CONFIG['heating_pin'].' 1');
	}
}

/**
 * Fetch temperature and humidity from a HomeWizard (http://www.homewizard.nl/)
 * Read more about the HomeWizard API on http://wiki.td-er.nl/index.php?title=Homewizard
 *
 * @return	array($T_i, $T_o)
 */ 
function HW_temp(){
	global $CONFIG;
	
	$hw_ip = $CONFIG['hw_ip'];
	$hw_pw = $CONFIG['hw_pw'];
	$hw_sid = $CONFIG['hw_sid'];
	$hw_osid = $CONFIG['hw_osid'];
	
	// Fetch the temperature sensor list
	$url = $hw_ip."/".$hw_pw."/telist.php";
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	$response = curl_exec($ch);
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	
	// Handle errors
	if(curl_errno($ch)){
		$errNo = curl_errno($ch);
		handleError('cURL error: '.$errNo.' : '.curl_strerror($errNo));
		return array(false,false);
	}	
	
	curl_close($ch);
	
	// Check if the response code is valid
	if($httpcode < 200 || $httpcode >= 300){
		handleError('Invalid response code from the HomeWizard');
		return array(false,false);
	}
	
	// Convert the JSON response into an array
	$data = json_decode($response, true);
	
	// Extract the inside and outside temperature from the response
	$T_i = (isset($data['response'][$hw_sid]['te']) && is_numeric($data['response'][$hw_sid]['te'])) ? $data['response'][$hw_sid]['te'] : false;
	$T_o = (isset($data['response'][$hw_osid]['te']) && is_numeric($data['response'][$hw_osid]['te'])) ? $data['response'][$hw_osid]['te'] : false;

	return array($T_i, $T_o);
}

/**
 * Check if temperature measurements are valid, stop if they're invalid.
 *
 * @param	MySQL date format	$last_update	date/time at last update
 */ 
function validateTemp($status){
	global $T, $T_o;
	handleError("Unable to fetch the temperature");
	
	if(strtotime($status['last_update']) < strtotime("-".$CONFIG['time_unreliable']." minutes")){
		// Temperature measurements are invalid
		handleError("Temperature measurement is unreliable, turning off heater");
		heat(false);
		die();
	}else{
		$T = ($T === false) ? $status['T'] : $T;
		$T_o = ($T_o === false) ? $status['T_o'] : $T_o;
	}
}

/**
 * Add a timestamp to an error message
 *
 * @param	string	$err	error message
 */ 
function handleError($err){
	// Print MySQL format timestamp
	echo "[".date("Y-m-d H:i:s")."] ";
	// Print error message
	echo $err;
	// Print newline
	echo "\n";
}

?>
