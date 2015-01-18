<?php
/** base.php
 * WTherm v2
 * Author: Niek Blankers <niek@niekproductions.com>
 *
 * Global settings and functions
 */
 
/**
 * Configuration settings
 */
$CONFIG = array(
	// Database configuration
	"db_server" => "localhost", // MySQL server
	"db_name" => "wtherm", // Database name
	"db_user" => "WTherm", // Database username
	"db_pass" => "", // Database password
	
	// Thermostat configuration
	"min_temp" => 10, // Minimum goal temperature
	"max_temp" => 30, // Maximum goal temperature
	"C" => 8978053, // Heat capacity (J/K)
	"U" => 364, // Heat transfer coefficient (W/K) 
	"P" => 7176, // Heating power (Watts)
	"time_unreliable" => 10, // Number of minutes after which the temperature is considered unreliable
	"heating_pin" => 5, // GPIO pin that switches the heater
	
	// Sensor configuration
	"hw_ip" => "hw-ip", // HomeWizard IP address
	"hw_pw" => "password", // HomeWizard password
	"hw_sid" => 1, // HomeWizard's inside temperature sensor ID
	"hw_osid" => 0, // HomeWizard's outside temperature sensor ID
	
	// Additional website configurations
	"timezone" => "Europe/Amsterdam", // Time zone, full list at http://php.net/manual/en/timezones.php
	"menuitems" => array(
		1 => array(
			"name" => "Home",
			"file" => "index.php",
		),
		2 => array(
			"name" => "Schedule",
			"file" => "schedule.php",
		),
		3 => array(
			"name" => "Stats",
			"file" => "stats.php",
		),
		4 => array(
			"name" => "Settings",
			"file" => "settings.php",
		),
		5 => array(
			"name" => "Logout",
			"file" => "login.php?logout",
		),
	),
);

// Set default time zone
date_default_timezone_set($CONFIG['timezone']);

/**
 * tempCalc class
 * Temperature calculations
 */ 
class tempCalc{
	protected $C,$U,$P;
	public $t = null;
	
	public function __construct() {
		global $CONFIG;
		
		$this->C =& $CONFIG['C'];
		$this->U =& $CONFIG['U'];
		$this->P =& $CONFIG['P'];
	}
	
	/**
	 * Calculate the heating time to a certain temperature
	 *
	 * @param	float	$T	Starting temperature
	 * @param	float	$Target	Target temperature
	 * @param	float	$To	Outside temperature
	 * @param	number	$dt	dt steps (seconds) - optional
	 * @param	boolean	$hours	return in hours - optional
	 * @return	number	time to reach temperature
	 */ 
	public function heatingTime($T, $Target, $To, $dt = 1, $hours = false){
		if($T > $Target) return 0;
		
		$t =& $this->t;
		$t = 0; // Time
		
		while($T < $Target){
			$Qin = $this->P * $dt;
			$Qloss = $this->U * ($T - $To) * $dt;
			$dT = ($Qin - $Qloss) / $this->C;
			$T += $dT;
			$t += $dt;
			
			// The model has an asymptote - stop if the time is greater than 20 hours
			if($t > 20*3600) break;
		}
		
		if($hours) $t /= 3600;
		
		return $t;
	}
	
	/**
	 * Convert the number of seconds from heatingTime() to HH:MM format
	 *
	 * @return	string	time in HH:MM format
	 */
	public function toString(){
		$t =& $this->t;
		$hours = floor($t / 3600);
		$minutes = round($t % 3600 /60);
		return $hours.":".$minutes;
	}
}

/**
 * Checks if user is logged in
 * If not, redirect to login.php
 */ 
function checkLogin(){
	session_start();
	if(!isset($_SESSION['username'])){ // User is not logged in
		header('Location: login.php');
		die();
	}
}

/**
 * Find next target temperature and the number of hours to it
 *
 * @param	array	$schedule	array from schedule database table
 * @return	array($T_target, $t)	target temperature and the number of hours to it
 */ 
function nextTarget($schedule){
	// Day of the week (1-7);
	$DOW = date('N');
	
	$T_target = false;
	$t = false;
	
	foreach($schedule as $setPoint){
		$S_T_target = floatval($setPoint['T_target']);
		$S_time = $setPoint['time'];
		$S_day = $setPoint['day'];

		// Convert the target time to Unix time
		$time_str = date('j F Y', strtotime(($S_day-$DOW)." day"))." ".$S_time;
		$S_time = strtotime($time_str);
		
		$cur_time = time();
		
		// Calculate the number of hours to the target
		$hours_to_target = ($S_time-$cur_time) / 3600;
		if($hours_to_target < 0) $hours_to_target += 7*24;
		
		if($T_target === false){
			$T_target = $S_T_target;
			$t = $hours_to_target;
		}else if($hours_to_target < $t){
			$T_target = $S_T_target;
			$t = $hours_to_target;
		}
	}
	
	return array($T_target, $t);
}

/**
 * Database class
 * Simplifies database actions
 * Slightly modified, from Philip Brown: http://culttt.com/2012/10/01/roll-your-own-pdo-php-class/
 */ 
class Database{
	private $host,$user,$pass,$dbname;
	
	private $dbh;
    private $e;
	
	private $stmt;
 
	// Initialize the class, connect to the database
    public function __construct(){
		global $CONFIG;
        $this->host =& $CONFIG['db_server'];
		$this->user =& $CONFIG['db_user'];
		$this->pass =& $CONFIG['db_pass'];
		$this->dbname =& $CONFIG['db_name'];
		
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname;

        $options = array(
            PDO::ATTR_PERSISTENT    => true,
            PDO::ATTR_ERRMODE       => PDO::ERRMODE_EXCEPTION
        );
		
        try{
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        }catch(PDOException $e){
			// An exception has been caught
            die('PDO Exception: '.$e->getMessage());
        }
    }
	
	// Prepare the query
	public function query($query){
		$this->stmt = $this->dbh->prepare($query);
	}
	
	// Bind parameters to the query
	public function bind($param, $value, $type = null){
		if (is_null($type)) {
			switch (true) {
				case is_int($value):
					$type = PDO::PARAM_INT;
					break;
				case is_bool($value):
					$type = PDO::PARAM_BOOL;
					break;
				case is_null($value):
					$type = PDO::PARAM_NULL;
					break;
				default:
					$type = PDO::PARAM_STR;
			}
		}
		$this->stmt->bindValue($param, $value, $type);
	}
	
	// Execute a query
	public function execute(){
		try {
			return $this->stmt->execute();
		}catch(PDOException $e){
			// An exception has been caught
            die('PDO Exception: '.$e->getMessage());
        }
	}
	
	// Return an array of the resulting rows
	public function resultset(){
		try{
			$this->execute();
			return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
		}catch(PDOException $e){
			// An exception has been caught
            die('PDO Exception: '.$e->getMessage());
        }
	}
	
	// Return a single record from the database
	public function single(){
		try{
			$this->execute();
			return $this->stmt->fetch(PDO::FETCH_ASSOC);
		}catch(PDOException $e){
			// An exception has been caught
            die('PDO Exception: '.$e->getMessage());
        }
	}
	
	// Count the number of effected rows
	public function rowCount(){
		try{
			return $this->stmt->rowCount();
		}catch(PDOException $e){
			// An exception has been caught
            die('PDO Exception: '.$e->getMessage());
        }
	}	
}

/**
 * Hash generation function to salt the password
 *
 * @param  string    $password 
 * @return string 	 Hashed password
 */ 
function generateHash($password) {
    if (defined("CRYPT_BLOWFISH") && CRYPT_BLOWFISH) {
        $salt = '$2y$11$' . substr(md5(uniqid(rand(), true)), 0, 22);
        return crypt($password, $salt);
    }else{
		exit("CRYPT_BLOWFISH unsupported!");
	}
}

/**
 * Verify the salted, hashed password.
 *
 * @param  String    $password    password to compare to $hashedPassword
 * @param  String    $hashedPassword
 * @return boolean   match
 */ 
function verify($password, $hashedPassword) {
	if (defined("CRYPT_BLOWFISH") && CRYPT_BLOWFISH) {
		return crypt($password, $hashedPassword) == $hashedPassword;
	}else{
		exit("CRYPT_BLOWFISH unsupported!");
	}
} 

/**
 * Run the thermostat script
 * Passes an $argv variable to prevent logging
 */
function update(){
	exec('php5 /usr/local/bin/WTherm/thermostat.php noupdate'); //execute thermostat script, but don't log the temperature
}

/**
 * Layout class 
 * Used for parsing a template
 */ 
class Layout{
	// Declaring variables
	var $item,$html,$name,$enablemenu;
	protected $menu;
	
	public function __construct() {
        global $CONFIG;
        $this->menu =& $CONFIG['menuitems'];
		$this->enablemenu = true;
    }	
	
	/**
	 * Indicate that the page HTML is following
	 * Starts output buffer
	 *
	 * @param  integer  $name  Page name
	 */ 
	public function startHTML($name, $enablemenu = true){
		$this->name = $name;
		if(!$enablemenu) $this->enablemenu = false;
		ob_start();
	}
	
	/**
	 * Flushes output buffer
	 */
	public function fetchHTML(){
		return ob_get_clean();
	}
	
	/**
	 * Outputs the page
	 */
	
	public function output($PAGE_BODY){
		include('layout/layout.php');
	}
}
 
 ?>
