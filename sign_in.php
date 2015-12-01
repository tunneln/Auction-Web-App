<?php 
session_start();
require_once '/u/noel/CS105-PHP/openDatabase.php'; 
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== "on") {
    header('Location: https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
    exit(1);
}
if (isset($_GET['fail'])) {
	$message = htmlspecialchars('Sign in Failed: Username or Password is incorrect');
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	<head>
		<title>Work Product</title>
		<link rel="stylesheet" href="style.css" type="text/css"/>
		<meta charset="utf-8" />
	</head>
	<body>
		<?php if (isset($_SESSION['userName']) && $_SESSION['userName'] != ' ') { ?>
		<div class="corner2"> Welcome, <a href="user_profile.php"><?= htmlspecialchars($_SESSION['userName']) ?></a>! | <a href="index.php?logout=1">LOGOUT?</a></div>
		<?php } ?>
		<div class="corner"><a href="update.php">Update</a> &#x2022; <a href="cancel.php">Cancel</a> &#x2022; <a href="pay_list.php">Pay</a></div>
		<h1 class="title"> Auction Web Application </h1>
		<div class="navi">
			<ul>
				<li> <a href="index.php">Home</a> </li>
				<?php if (isset($_SESSION['userName']) && $_SESSION['userName'] != ' ') { ?>
				<li> <a href="user_listings.php">My Listings</a> </li> 
				<?php } else { ?>
				<li> <a href="sign_in.php?redir=index">Sign In!</a> </li>
				<?php } ?>
				<li> <a href="listings.php">Browse Listings</a> </li>
				<li> <a href="list_new.php">List New Item</a> </li>
			</ul>
		</div>	
		<div class="other">
<?php
	if (isset($_SESSION['userName']) && $_SESSION['userName'] != ' ') {
		echo htmlspecialchars('You have to log off before you can sign in again');
	} else {
?>
		<?php if (isset($message)) { echo htmlspecialchars($message); }	?>
		<form action="<?php if (isset($_GET['redir'])) { 
			echo htmlspecialchars($_GET['redir'] . '.php');
		} else {
			echo htmlspecialchars('index.php'); 
		} ?>" method="post">
			<br/>
			<label>Email/Username:</label><input name="uname" type="email"/><br/>
			<label>Password:</label><input name="pass" type="password"/><br/><br/>
			<input type="submit" value="Sign In!"/> New? <a href="register.php">Register Here!</a>
		</form>
		</div>
<?php } ?>
	</body>
</html>

