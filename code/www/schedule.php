<?php
/** index.php
 * WTherm v2
 * Author: Niek Blankers <niek@niekproductions.com>
 *
 * Main page
 */
include('../base.php');
checkLogin();

// Used for mapping DOW (1-7) to name of day
$dowMap = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');

/*-------------------------------------------------------------------
| A schedule has been submitted, save it to the database
| -------------------------------------------------------------------*/
if(isset($_POST['setPoints'])){
	$setPoints = json_decode($_POST['setPoints'], true);
	$validSetPoints = array_filter($setPoints, 'isValidPoint');
	
	
	// Empty the schedule database
	$database = new Database();
	$database->query('TRUNCATE TABLE schedule');
	$database->execute();
	
	// Prepare the query
	$database->query('INSERT schedule (T_target, time, day) VALUES (:T_target,:time,:day)');
	
	foreach($validSetPoints as $setPoint){	
		$time = $setPoint['time'];
		$temp = $setPoint['temp'];
		$DOW = array_search($setPoint['day'], $dowMap) + 1; 
		
		// Insert setPoint into database
		$database->bind(':T_target', $temp);
		$database->bind(':time', $time);
		$database->bind(':day', $DOW);
		$database->execute();		
	}
	update();
}

/**
 * Validate a temperature setPoint
 *
 * @param	array	$setpoint	setpoint to validate
 * @return	boolean	valid
 */ 
function isValidPoint($setPoint){
	global $dowMap,$CONFIG;
	
	// Check if the setPoint contains the correct data
	if(!isset($setPoint['time']) || !isset($setPoint['temp']) || !isset($setPoint['day'])) return false;
	
	$time = $setPoint['time'];
	$temp = $setPoint['temp'];
	
	// Check if the day of the week is valid
	$DOW = array_search($setPoint['day'], $dowMap) + 1; 
	if($DOW < 1 || $DOW > 7) return false;
	
	// Check if the time is a valid format (hh:mm, 24-hours)
	if(!preg_match("/^([01]?[0-9]|2[0-3]):[0-5][0-9]\z/", $time)) return false;
	
	// Check if $temp is valid
	if(!is_numeric($temp) || $temp < $CONFIG['min_temp'] || $temp > $CONFIG['max_temp']) return false;
	
	// Everything is valid
	return true;
}

// Initialize a new page
$page = new Layout; 
// Indicate that HTML code is following
$page->startHTML("Schedule"); 

// Fetch
$database = new Database();
$database->query('SELECT * FROM schedule ORDER BY day ASC, time ASC');
$database->execute();
$schedule = $database->resultset();

?>

<div class="row control">
<div class="fullwidth">
	<div class="col-lg-2 col-sm-2 col-md-2 col-xs-2" id="optButtons">
		<div id="addPoint" class="mouseover unselectable" onclick="javascript:addSetPoint();"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span><span>Add</span></div>
		<div id="delPoint" class="mouseover unselectable" onclick="javascript:deleteSetPoint();"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span><span>Delete</span></div>
	</div>
	<div id="setPointLst" class="col-lg-10 col-sm-10 col-md-10 col-xs-10">
<? foreach($schedule as $key=>$schedulePoint){ ?>
		<div class="setPoint" id="<?=$key?>" onclick="javascript:changeSetPoint(<?=$key?>)">
		<table>
			<tr>
				<td style="width:80%"><?=$dowMap[$schedulePoint['day']-1]?></td>
				<td style="text-align:right;">Set to</td>
			</tr>
			<tr>
				<td><span class="big" id="time"><?=$schedulePoint['time']?></span></td>
				<td style="text-align:right;">
					<span style="font-weight: bold;" class="big" id="Target"><?=$schedulePoint['T_target']?></span>
				</td>
			</tr>
		</table>
		</div>
<? } ?>
	</div>
</div>
</div>
<div class="row control" id="editPoint" style="display: none;">
	<div class="col-lg-8 col-sm-8" style="padding-bottom: 5px;">
		<h4>Time:</h4>
		<select id="editDay" class="form-control" style="float:left;width:40%;">
			<option value="Monday">Monday</option>
			<option value="Tuesday">Tuesday</option>
			<option value="Wednesday">Wednesday</option>
			<option value="Thursday">Thursday</option>
			<option value="Friday">Friday</option>
			<option value="Saturday">Saturday</option>
			<option value="Sunday">Sunday</option>
		</select>
		<span style="float:right;width:59%;">
			<input id="editHour" type="number" onmouseover="" min="0" max="23" value="00" class="form-control" placeholder="HH" style="width:40%;float:left;" required><span style="font-size:20px;float:left;">:</span><input id="editMinute" type="number" min="0" max="55" step="5" value="00" class="form-control" placeholder="MM" style="width:40%;float:left;" required>   
		</span>
	</div>
	<div class="col-lg-4 col-sm-4">
		<h4>Temperature:</h4>
			<input id="editTemp" type="number" min="<?=$CONFIG['min_temp']?>" max="<?=$CONFIG['max_temp']?>" value="00" step="0.5" class="form-control" style="width:50%;float:left;" required><span style="font-size:20px;width:20%;">&deg;C</span>
			<button type="submit" onclick="javascript:updateSetPoint();" class="btn btn-default" style="float: right;">Set</button>
	</div>
</div>





<script type="text/javascript" src="js/schedule.js"></script>  
<?php
$HTML = $page->fetchHTML(); // Fetch the above HTML
$page->output($HTML); // Output the layout
?>