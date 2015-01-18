<?php
/** index.php
 * WTherm v2
 * Author: Niek Blankers <niek@niekproductions.com>
 *
 * Main page
 */
include('../base.php');
checkLogin();

$page = new Layout; // Start new layout
$page->startHTML("Home"); // Indicate that HTML code is following
?>
<div id="error" style="display: none;" class="alert alert-danger alert-dismissable">
	<button type="button" onclick="document.getElementById('error').style.display='none';" class="close" aria-hidden="true">&times;</button>
	<strong>Error!</strong> <span id="errormsg">Unknown.</span>
</div>	
<div class="row control">
	<div class="col-lg-8 col-lg-offset-2 col-md-8 col-md-offset-2 col-sm-8 col-sm-offset-2">
		<div id="controlBox">
		<table>
			<tr style="height: 20%; line-height: 100px;">
				<td id="paddingTd"></td>
				<td style="width: 40%; min-width: 40%;">
				<span onclick="javascript:incrtemp();" id="temp-up" class="big" ><span class="glyphicon glyphicon-chevron-up" aria-hidden="true"></span></span>
				</td>
				<td style="width: 30%;"></td>
			</tr>
			<tr style="height: 60%; position:relative;">
				<td></td>
				<td>
					<span class="big" onclick="disable_override();" id="tempDiv"><span id="temp" style="padding-left: 35px; font-weight: bold;">15</span>&deg;</span>
				</td>
				<td style="position: relative;">
					<span id="timeRemaining" style="display: none; position: absolute; left:0; bottom: 16px; font-size: 23px;"></span>
				</td>
			</tr>
			<tr style="height: 20%; line-height: 100px;">
				<td></td>
				<td>
				<span onclick="javascript:decrtemp();" id="temp-down" class="big" ><span class="glyphicon glyphicon-chevron-down" aria-hidden="true"></span></span>
				</td>
				<td></td>
			</tr>
		</table>
		</div>
	</div>
</div>

<script type="text/javascript" src="js/control.js"></script>  
<?php
$HTML = $page->fetchHTML(); // Fetch the above HTML
$page->output($HTML); // Output the layout
?>