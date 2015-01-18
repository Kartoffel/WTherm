/** drawChart.js
 * WTherm v2
 * Author: Niek Blankers <niek@niekproductions.com>
 *
 * Javascript code for drawing charts
 */

// Get the context of the canvas element we want to select
var LineChart;
var ctx = document.getElementById("tempChart").getContext("2d");

var options = {
	responsive : true,
	annotateDisplay : true,
	legend: true,
	annotateLabel : "<%=v3%> &deg;C",
	yAxisLabel : "Temperature",
	xAxisLabel : "Time",
};

var json = JSONHTTPRequest(1);


LineChart = new Chart(ctx).Line(json, options);

// Used to change the date range
function changeRange(days){
	setActive(days);
	
	json = JSONHTTPRequest(days);
	updateChart(ctx,json,options,true,false);
}

// Set the active property for the button
function setActive(days){
	resetActive();
	var el = document.getElementById(days + 'd');
	el.className += " active";
}

// Reset active property for buttons
function resetActive(){
	var classname = document.getElementById('1d').className;
	document.getElementById('1d').className = classname.replace(" active", "");
	var classname = document.getElementById('7d').className;
	document.getElementById('7d').className = classname.replace(" active", "");
	var classname = document.getElementById('365d').className;
	document.getElementById('365d').className = classname.replace(" active", "");
}

// Request data in JSON format
function JSONHTTPRequest(days){
	var request = new XMLHttpRequest();  
	request.open('GET', 'chartdata.php?days=' + days, false); 
	request.send(null);  
	
	var response = request.responseText;
	if(response == 'LOGIN'){
		window.location.replace('login.php');
		return false;
	}
	
	return JSON.parse(response);  
}