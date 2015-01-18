<?php
/** startup.php
 * WTherm v2
 * Author: Niek Blankers <niek@niekproductions.com>
 *
 * Switch off the thermostat after a reboot
 */
include('base.php');
echo "[".date("Y-m-d H:i:s")."] WTherm has booted\n";
// Set the pin mode
echo shell_exec('/usr/local/bin/gpio gpio mode '.$CONFIG['heating_pin'].' out');
// Switch off the relay
echo shell_exec('/usr/local/bin/gpio gpio write '.$CONFIG['heating_pin'].' 1');
?>
