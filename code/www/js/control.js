/** control.js
 * WTherm v2
 * Author: Niek Blankers <niek@niekproductions.com>
 *
 * This script is used to communicate with the WTherm database, through data.php
 */

var updateTemp;
var updateInterval = 60; //update interval in seconds

var override = override_status();
//var update=setInterval(function(){updateall()}, updateInterval*1000);
updateall();

function updateall(){
	heat_status();
	override_status();
	time_remain();
}

function incrtemp(){
	var element = document.getElementById('temp');
	var temp = parseFloat(element.innerHTML);
	if(!isNaN(temp)){
		temp += 0.5;
		element.innerHTML = temp;
		if(!override) enable_override();
		clearTimeout(updateTemp);
		updateTemp = setTimeout(function(){ HTTPrequest('TEMP&value=' + temp); updateall()},2000);
	}
}

function decrtemp(){
	var element = document.getElementById('temp');
	var temp = parseFloat(element.innerHTML);
	if(!isNaN(temp)){
		temp -= 0.5;
		element.innerHTML = temp;
		if(!override) enable_override();
		clearTimeout(updateTemp);
		updateTemp = setTimeout(function(){ HTTPrequest('TEMP&value=' + temp); updateall()},2000);
	}
}

function override_status(){
	var status = HTTPrequest('OVERRIDE-STATUS');
	if(status == '1'){
		override = 1;
		document.getElementById('paddingTd').style.width="30%";
		document.getElementById('temp-up').style.opacity = 1.0;
		document.getElementById('temp-down').style.opacity = 1.0;
		document.getElementById('timeRemaining').style.display = "none";
		return true;
	}else if(status == '0'){
		override = 0;
		document.getElementById('paddingTd').style.width="auto";
		document.getElementById('timeRemaining').style.display = "block";
		return false;
	}
}

function time_remain(){
	var element = document.getElementById('timeRemaining');
	if(!override)
		element.style.display = "block";
	element.innerHTML = HTTPrequest('TIME-POINT');
}

function enable_override(){
	document.getElementById('paddingTd').style.width="30%";
	document.getElementById('temp-up').style.opacity = 1.0;
	document.getElementById('temp-down').style.opacity = 1.0;
	document.getElementById('timeRemaining').style.display = "none";
	HTTPrequest('ENA-OVERRIDE');
}

function disable_override(){
	document.getElementById('paddingTd').style.width="auto";
	document.getElementById('temp-up').style.opacity = 0.4;
	document.getElementById('temp-down').style.opacity = 0.4;
	document.getElementById('timeRemaining').style.display = "block";	
	HTTPrequest('DIS-OVERRIDE');
}

function heat_status(){
	var element = document.getElementById('controlBox');
	var status = HTTPrequest('HEATING-STATUS');
	if(status == '1'){
		element.style.backgroundColor = "rgb(169,68,66)";
	}else if(status == '0'){
		element.style.backgroundColor = "rgb(91,192,222)";
	}
	document.getElementById('temp').innerHTML = parseFloat(HTTPrequest('TARGETTEMP'));
}

function error(msg){
	var errorEl = document.getElementById('error');
	var errormsgEl = document.getElementById('errormsg');
	errormsgEl.innerHTML = msg;
	errorEl.style.display = 'block';
}

function HTTPrequest(func){
	var request = new XMLHttpRequest();  
	request.open('GET', 'data.php?func=' + func, false); 
	request.send(null);  
	
	if (request.status === 200) {  
	  var response = request.responseText;
	  if(response == 'LOGIN'){
		window.location.replace('login.php');
		return false;
	  }else if(response == 'FAIL'){
		error('An error was encountered while processing the request');
		return false;
	  }
	  return response;  
	}else{
	  error('WTherm is unreachable');
	  return false;
	}
}