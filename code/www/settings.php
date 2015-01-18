<?php
/** settings.php
 * WTherm v2
 * Author: Niek Blankers <niek@niekproductions.com>
 *
 * Settings page - used for changing a password
 */
include('../base.php');
checkLogin();

$page = new Layout; // Start new layout
$page->startHTML("Settings"); // Indicate that HTML code is following

if(isset($_POST['password']) && isset($_POST['password2'])){
	if(is_null($_POST['password'])){
	
		$error = "Please enter a new password!";
	}else if($_POST['password'] != $_POST['password2']){
	
		$error = "Passwords don't match!";
	}else{
		// Input is valid, update the password
		$newpass = $_POST['password'];
		$hashedPass = generateHash($newpass);	

		// Update password in the database
		$database = new Database();
		$database->query('UPDATE users SET password=:newpass WHERE username=:username');
		$database->bind(':username', $_SESSION['username']);
		$database->bind(':newpass', $hashedPass);
		$database->execute();
		
		$success = true;
	}
}


?>
<div class="row control">
	<?=isset($error)? "<div class=\"alert alert-danger\" role=\"alert\"><strong>Error</strong><p>".$error."</p></div>" : ""?>
	<?=isset($success)? "<div class=\"alert alert-success\" role=\"alert\"><strong>Success</strong><p>Successfully changed password.</p></div>" : ""?>
	<div class="col-lg-8 col-sm-8">
		<h3>Change password</h3>
		<form method="POST">
			<div class="form-group">
				<input type="password" name="password" class="form-control" placeholder="New password" required>
			</div>
			<div class="form-group input-group">
				<input type="password" name="password2" class="form-control" placeholder="Confirm new password" required>
				<span class="input-group-btn">
				<button class="btn btn-default" type="submit">Change</button>
				</span>
			</div>
		</form>
	</div>
</div>
<?php
$HTML = $page->fetchHTML(); // Fetch the above HTML
$page->output($HTML); // Output the layout
?>