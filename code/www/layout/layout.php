<?php
/** layout.php
 * WTherm v2
 * Author: Niek Blankers <niek@niekproductions.com>
 *
 * Page layout
 */
 ?>
<!DOCTYPE HTML>
<html lang="en">
<head>
	<title>WTherm &bull; <?=$this->name?></title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
	<meta name="description" content="Web controlled thermostat">
	<meta name="author" content="NiekProductions">
	<link href="layout/bootstrap.min.css" rel="stylesheet">
	<link href="layout/layout.css" rel="stylesheet">
	<link rel="icon" sizes="196x196" href="WTherm-icon-196.png">
	<link rel="apple-touch-icon" sizes="196x196" href="WTherm-icon-196.png">
	<link rel="shortcut icon" type="image/x-icon" href="WTherm-icon.ico">
</head>
<body>
<div class="container">
    <div class="header">
		<ul class="nav nav-pills pull-right">
			<?php if($this->enablemenu) foreach($this->menu as $menuitem){ // Menu items ?>
            <li<?=($menuitem['name'] == $this->name)? " class=\"active\"" : ""?>><a href="<?=$menuitem['file']?>"><?=$menuitem['name']?></a></li>
			<?php } ?>
		</ul>
		<h3 class="text-muted">WTherm</h3>
    </div>
	<noscript><div class="alert alert-danger" role="alert"><strong>Alert!</strong><p>Please enable javascript. It is necessary for important page functionality.</p></div></noscript>
	<?=isset($PAGE_BODY)? $PAGE_BODY : "<div class=\"alert alert-danger\" role=\"alert\"><strong>Alert!</strong><p>Something went wrong while loading the page, please check the requested URL.</p></div>"?>
	
	<div class="footer">
		<p style="width:100%;"><span class="text-left">&copy; <a href="http://niekproductions.com/">NiekProductions</a> <?php echo date("Y"); ?></span></p>	
	</div>
    </div>
</body>
</html>