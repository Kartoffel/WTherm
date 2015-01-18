<?php
/** login.php
 * WTherm v2
 * Author: Niek Blankers <niek@niekproductions.com>
 *
 * Login page
 */
include('../base.php');
session_start();

$page = new Layout; // Start new layout
$page->startHTML("Login", false); // Indicate that HTML code is following

/*-------------------------------------------------------------------
| Log out  
| -------------------------------------------------------------------*/
if(isset($_GET['logout'])){
	// Destroy the session, logging out the user
	session_destroy(); 
	
	// Unset all of the session variables.
	$_SESSION = array();
	
	// Delete the session cookie.
	if (ini_get("session.use_cookies")) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
			$params["path"], $params["domain"],
			$params["secure"], $params["httponly"]
		);
	}
	
	header("Location:login.php");
	exit;
}

/*-------------------------------------------------------------------
| Check if the user is already logged in
| -------------------------------------------------------------------*/
if(isset($_SESSION['username'])){
	// The user is already logged in
	header('Location: index.php');
	exit;
}

/*-------------------------------------------------------------------
| Validate / Log in
| -------------------------------------------------------------------*/
if(isset($_POST['username']) && isset($_POST['password'])){
	$username = $_POST['username'];
	$password = $_POST['password'];
	
	// Connect to the database, fetch user
	$database = new Database();
	$database->query('SELECT * FROM users WHERE username=:username');
	$database->bind(':username', $username);
	$database->execute();
	$row = $database->single();
	
	if(!$row){
		// No user was found
		$error = true;
	}else{
		// Verify login credentials
		if(verify($password, $row['password'])){	
			// Successful login, register user session
			$_SESSION['username'] = $username; // Successfully logged in
			header("Location:index.php");
			exit;
		}else{
			// Incorrect login
			$error = true; 
		}
	}
}

/*-------------------------------------------------------------------
| Show a message if an error has occured
| -------------------------------------------------------------------*/
if(isset($error))
	echo('<div id="error" style="display: block;" class="alert alert-danger"><h3>Incorrect username/password combination!</h3></div>');
?>
	  
      <div class="row control">
        <div class="col-lg-12 unselectable">
			<form role="form" name="login-form" action="login.php" method="POST">
				<h2>Login</h2>
				<div class="form-group">
					<input type="text" class="form-control input-lg" name="username" placeholder="Username">
				</div>
				<div class="form-group">
					<div class="input-group input-group-lg">
						<input type="password" class="form-control" name="password" placeholder="Password">
						<span class="input-group-btn">
							<button class="btn btn-default" type="submit">Submit</button>
						</span>
					</div>
				</div>
			</form>
        </div>
      </div>
<?php
$HTML = $page->fetchHTML(); // Fetch the above HTML
$page->output($HTML); // Output the layout
?>