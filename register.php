<?php
session_start(); 
require_once '/u/noel/CS105-PHP/openDatabase.php'; 
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== "on") {
	    header('Location: https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
	        exit(1);
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
		<div class="corner2"> Welcome, <a href="user_profile.php"><?= htmlspecialchars($_SESSION['userName']) ?></a>! | <a href="index.html?logout=1">LOGOUT?</a></div>
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
<?php
	if (isset($_SESSION['userName']) && $_SESSION['userName'] != ' ') {
		echo htmlspecialchars('Please log off before registering again');
	} else {
?>
		<div class="other">
		<form action="index.php" method="post">
			<br/>
			<label>First Name:</label><input type="text" name="forename" required="required"/>
			<label>Last Name:</label><input type="text" name="surname" required="required"/><br/>
			<label>Email/Username: </label><input type="email" name="email_address" required="required"/>
			<label>Password: </label><input name="password" style="color:#c1c1c1;" type="text" value="At least 5 characters" onclick="this.type='password'; this.value=''; this.style.color='#323232'" pattern=".{5,}" required="required"/><br/>
			<input type="radio" required="required"/> I Agree to the <a href="terms.php">Terms &amp; Conditions</a><br/><br/>
			<input type="submit" value="Register!"/>
			</form>
		</div>
<?php } ?>
	</body>
</html>

