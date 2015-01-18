/** schedule.js
 * WTherm v2
 * Author: Niek Blankers <niek@niekproductions.com>
 *
 * This script is used to change and save the schedule
 */

var scheduleItems;
var itemCount;
var selectedItem;

updateNumber();

/**
 * Goes into edit mode of the selected setPoint
 *
 * @param	Identifier	id	The setpoint's div identifier
 */
function changeSetPoint(id){
	// Reset previous element's background color
	if(selectedItem != null){
		var oldelement = document.getElementById(selectedItem);
		if(oldelement)
			oldelement.style.backgroundColor = 'lightgray';
	}
	
	selectedItem = id;
	var element = document.getElementById(id);
	element.style.backgroundColor = '#5bc0de';
	var timeTemp = element.getElementsByClassName('big');
	var timeEl = timeTemp[0];
	var tempEl = timeTemp[1];
	var dateEl = element.getElementsByTagName('td')[0];
	
	var editHour = document.getElementById('editHour');
	var editMinute = document.getElementById('editMinute');
	var editTemp = document.getElementById('editTemp');
	var editDay = document.getElementById('editDay');
	
	var hour = timeEl.innerHTML.split(":")[0];
	var minute = timeEl.innerHTML.split(":")[1];
	
	editHour.value = hour;
	editMinute.value = minute;
	editTemp.value = tempEl.innerHTML;
	editDay.value = dateEl.innerHTML;
	
	document.getElementById('editPoint').style.display = 'block';
	editTemp.focus();
}

/**
 * Update a setpoint 
 */
function updateSetPoint(){
	id = selectedItem;
	selectedItem = null;
	if(id != null){
		var element = document.getElementById(id);
		element.style.backgroundColor = 'lightgray';
		
		var timeTemp = element.getElementsByClassName('big');
		var timeEl = timeTemp[0];
		var tempEl = timeTemp[1];
		var dateEl = element.getElementsByTagName('td')[0];
		
		var editHour = document.getElementById('editHour');
		var editMinute = document.getElementById('editMinute');
		var editTemp = document.getElementById('editTemp');
		var editDay = document.getElementById('editDay');
		
		var day = editDay.value;
		var hour = pad(Math.floor(editHour.value),2);
		var minute = pad(Math.floor(editMinute.value),2);
		var time = hour + ":" + minute;
		
		var temp = round(editTemp.value,1);
		
		dateEl.innerHTML = day;
		timeEl.innerHTML = time;
		tempEl.innerHTML = temp;	
		document.getElementById('editPoint').style.display = 'none';
		saveSetPoints();
	}
}

/**
 * Submit the setpoints to PHP to be saved to the database
 */
function saveSetPoints(){
	var setPoints = new Array();
	
	for (index = 0; index < scheduleItems.length; ++index) {
		day = scheduleItems[index].getElementsByTagName('td')[0].innerHTML;
		time = scheduleItems[index].getElementsByTagName('span')[0].innerHTML;
		temp = scheduleItems[index].getElementsByTagName('span')[1].innerHTML;
		
		setPoints[index] = {day:day,time:time,temp:temp};
	}
	
	setPointString = JSON.stringify(setPoints);
	post('schedule.php', {setPoints: setPointString});
	
}

/**
 * Add a new setpoint
 */
function addSetPoint(){
	var container = document.getElementById('setPointLst');
	var newSetPoint = document.createElement('div');
	
	var newID = 0;
	while(document.getElementById(newID) != null){
		newID += 1;
	}
	
	newSetPoint.setAttribute('id',newID);
	newSetPoint.setAttribute('class',"setPoint");
	newSetPoint.setAttribute('onclick',"javascript:changeSetPoint(" + newID +")");
	newSetPoint.innerHTML = '<table><tr><td style="width:80%">Monday</td><td style="text-align:right;">Set to</td></tr><tr><td><span class="big" id="time">00:00</span></td><td style="text-align:right;"><span style="font-weight: bold;" class="big" id="Target">15.0</span></td></tr></table>';
	container.appendChild(newSetPoint);
	
	updateNumber();
	changeSetPoint(newID);
	document.getElementById(newID).focus();
}

/**
 * Delete the selected set point
 */
function deleteSetPoint(){
	element = document.getElementById(selectedItem);
	if(element != null){
		element.parentNode.removeChild(element);
		selectedItem = null;
		updateNumber();
	}
	document.getElementById('editPoint').style.display = 'none';
	saveSetPoints();
}

/**
 * Update the scheduleItems array and the number of schedule items
 */
function updateNumber(){
	scheduleItems = document.getElementsByClassName('setPoint');
	if(scheduleItems.length != null)
		itemCount = scheduleItems.length;
	else
		itemCount = 0;
}

/**
 * Pad a number by adding zeroes to it until it hits a certain length
 *
 * @param	String	str	The original string
 * @param	Integer	max	The maximum length of the string
 * return	String	The padded string
 */
function pad (str, max) {
  str = str.toString();
  return str.length < max ? pad("0" + str, max) : str;
}

/**
 * Round a number
 *
 * @param	Number	value	The number to round
 * @param	Integer	decimals	The number of decimals to round to
 * return	Number	Rounded value
 */
function round(value, decimals) {
    return Number(Math.round(value+'e'+decimals)+'e-'+decimals).toFixed(1);
}

/**
 * Post a form
 *
 * @param	String	path	where to submit the form
 * @param	String	params	parameters to send
 * @param	String	method	the method to use (POST or GET)
 */
function post(path, params, method) {
    method = method || "POST"; // Set method to post by default if not specified.

    // The rest of this code assumes you are not using a library.
    // It can be made less wordy if you use one.
    var form = document.createElement("form");
    form.setAttribute("method", method);
    form.setAttribute("action", path);

    for(var key in params) {
        if(params.hasOwnProperty(key)) {
            var hiddenField = document.createElement("input");
            hiddenField.setAttribute("type", "hidden");
            hiddenField.setAttribute("name", key);
            hiddenField.setAttribute("value", params[key]);

            form.appendChild(hiddenField);
         }
    }

    document.body.appendChild(form);
    form.submit();
}