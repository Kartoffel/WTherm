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
	pointDot: false,
	annotateLabel : "<%=v2%> &bull; <%=v3%> &deg;C",
	fmtV2: "fmtdatetime WD HH:mm",
	yAxisLabel : "Temperature",
	yAxisMinimumInterval : 0.1,
	xAxisLabel : "Time",
	drawXScaleLine: [{position:"bottom"},{position:"0",lineWidth:0.5,lineColor:"gray"}],
	fmtXLabel : "fmttime hh:mm",
};

changeChart(1);

// Used to change the date range
function changeRange(days){
	options.fmtV2 = "fmtdatetime WD HH:mm";
	if(days == 1)
		options.fmtXLabel = "fmttime hh:mm";
	else if(days == 7)
		options.fmtXLabel = "fmtdatetime WD DD/MM";
	else{
		options.fmtXLabel = "fmtdate mm/yyyy";
		options.fmtV2 = "fmtdate dd/mm/yyyy";
	}
	setActive(days);
	changeChart(days);
}

// Set the active property for the button
function setActive(days){
	resetActive();
	var el = document.getElementById(days + 'd');
	el.className += " active";
}

// Reset active property for buttons
function resetActive(){
	var i = ["1d", "7d", "365d"];
	i.forEach(function(i) {
		var classname = document.getElementById(i).className;
		document.getElementById(i).className = classname.replace(" active", "");
	});
}

// Update the chart using json data
function setChart(json){
	if(LineChart) updateChart(ctx,json,options,true,false);
	else LineChart = new Chart(ctx).Line(json, options);
}

// Change the chart date range between one, seven and 365 days
function changeChart(days){
	var request = new XMLHttpRequest();  
	request.open('GET', 'chartdata.php?days=' + days, true); 
	request.onload = function (e) {
		if (request.readyState === 4) {
			if (request.status === 200) {
				var response = request.responseText;
					if(response == 'LOGIN'){
						window.location.replace('login.php');
					}else{
						setChart(JSON.parse(response,JSON.dateParser));
					}
			} else {
				console.error(request.statusText);
			}
		}
	}
	request.send(null);
}

// JSON date parser
// Credit to http://weblog.west-wind.com/posts/2014/Jan/06/JavaScript-JSON-Date-Parsing-and-real-Dates
if (window.JSON && !window.JSON.dateParser) {
    var reISO = /^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2}(?:\.\d*|\d*))(?:Z|(\+|-)([\d|:]*))?$/;  
    JSON.dateParser = function (key, value) {
        if (typeof value === 'string') {
            var a = reISO.exec(value);
            if (a)
                return new Date(value);
        }
        return value;
    };

}
