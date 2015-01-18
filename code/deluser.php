<?php
/** deluser.php
 * WTherm v2
 * Author: Niek Blankers <niek@niekproductions.com>
 *
 * run 'deluser.php [user]' to delete a user from the database
 */

include('base.php');

if(!isset($argv[1])){
	exit("usage: deluser.php user\n");
}

$database = new Database();
$database->query('SELECT * FROM users WHERE username=:username');
$database->bind(':username', strtolower($argv[1]));
$database->execute();
$user = $database->single();
if(!$user){
	echo "No such user!\n";
	$db = null;
	exit;
}

$database = new Database();
$database->query('DELETE FROM users WHERE username=:username');
$database->bind(':username', strtolower($argv[1]));
$database->execute();
echo "User deleted successfully!\n";

$db = null;
?>