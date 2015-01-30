<?php
/** stats.php
 * WTherm v2
 * Author: Niek Blankers <niek@niekproductions.com>
 *
 * Temperature charts
 * Uses ChartNew.js - https://github.com/FVANCOP/ChartNew.js
 */
include('../base.php');
checkLogin();

$page = new Layout; // Start new layout
$page->startHTML("Stats"); // Indicate that HTML code is following
?>
<div id="row control">
		<div class="btn-group" style="margin: 9px 0;">
			<a id="1d" onclick="javascript:changeRange(1);" class="btn btn-default active">Last day</a>
			<a id="7d" onclick="javascript:changeRange(7);" class="btn btn-default">Last week</a>
			<a id="365d" onclick="javascript:changeRange(365);" class="btn btn-default">Last year</a>
		</div>
		<canvas id="tempChart" width="400" height="250"></canvas>
		<div style="width:100%;"></div>
</div>

<script src="js/ChartNew.min.js"></script>
<script src="js/format.min.js"></script>
<script type="text/javascript" src="js/drawChart.js"></script>  
<?php
$HTML = $page->fetchHTML(); // Fetch the above HTML
$page->output($HTML); // Output the layout
?>
