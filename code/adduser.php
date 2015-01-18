<?php
/** adduser.php
 * WTherm v2
 * Author: Niek Blankers <niek@niekproductions.com>
 *
 * run 'adduser.php [user] [pass]' to add a user to the database
 */
 
include('base.php');

if(!isset($argv[1]) || !isset($argv[2])){
	exit("usage: adduser.php user pass \n");
}

$database = new Database();
$database->query('SELECT * FROM users WHERE username=:username');
$database->bind(':username', strtolower($argv[1]));
$database->execute();
$user = $database->single();
if($user){
	echo "User already exists!\n";
	$db = null;
	exit;
}

$database->query('INSERT INTO users (`username`, `password`) VALUES (:username, :password)');
$database->bind(':username', strtolower($argv[1]));
$database->bind(':password', generateHash(strtolower($argv[2])));
$database->execute();

echo "User added succesfully!\n";
$db = null;
?>