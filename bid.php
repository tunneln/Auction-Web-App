<?php
session_start();
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== "on") {
	header('HTTP/1.1 403 Forbidden: TLS Required');
	exit(1);
}
require_once '/u/noel/CS105-PHP/openDatabase.php'; 
$verifyLogin = $database->prepare(<<<'SQL'
	SELECT PASSWORD FROM PERSON
	WHERE EMAIL_ADDRESS = :uname;
SQL
);
$userDetails = $database->prepare(<<<'SQL'
	SELECT * FROM PERSON
	WHERE EMAIL_ADDRESS = :usrname;
SQL
);

if (isset($_POST['uname']) && isset($_POST['pass'])) {
	$uname = $_POST['uname'];
	$pass = $_POST['pass'];
	$verifyLogin->bindValue(':uname', $uname, PDO::PARAM_STR);
	$verifyLogin->execute();
	$hash = $verifyLogin->fetchColumn();
	$verifyLogin->closeCursor();
	if (!$hash) {
		header('Location: https://fall-2015.cs.utexas.edu/cs105/noel/app-iter-2/sign_in.php?redir=bid&fail=1');
	}
	if (!password_verify($pass, $hash)) {
		header('Location: https://fall-2015.cs.utexas.edu/cs105/noel/app-iter-2/sign_in.php?redir=bid&fail=1');
	}
	$userDetails->bindValue(':usrname', $uname, PDO::PARAM_STR);
	$userDetails->execute();
	$details = $userDetails->fetch();
	$userDetails->closeCursor();
	session_regenerate_id(true);
	$_SESSION['userName'] = $details['FORENAME'] . ' ' . $details['SURNAME'];
	$_SESSION['emailAddress'] = $details['EMAIL_ADDRESS'];
	$_SESSION['personId'] = $details['PERSON_ID'];		
}

if (!isset($_SESSION['userName']) || $_SESSION['userName'] == ' ') {
	header('Location: https://fall-2015.cs.utexas.edu/cs105/noel/app-iter-2/sign_in.php?redir=bid');
}

$thisAuctionQuery = $database->prepare(<<<'SQL'
	SELECT * FROM AUCTION
	WHERE AUCTION_ID = :auctionId;
SQL
);
$thisAuctionQuery->bindValue(':auctionId', $_POST['item'], PDO::PARAM_INT);
$thisAuctionQuery->execute();
$auction = $thisAuctionQuery->fetch();
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	<head>
		<title>Bid</title>
		<link rel="stylesheet" href="style.css" type="text/css"/>
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
				<li> <a href="update.php">List New Item</a> </li>
			</ul>
		</div>
		<div class="other">
		<form action="listing_details.php" method="post" enctype="multipart/form-data">
		<h3> Minimum Bid: <?= htmlspecialchars($auction['BID_PRICE']) ?> </h3>
			<br/>
			<label>Enter Bid Amount:</label> $<input type="number" min="<?= htmlspecialchars(++$auction['BID_PRICE']) ?>" name="bid" required="required"/><br/>
			<input type="hidden" name="item" value="<?= htmlspecialchars($auction['AUCTION_ID']) ?>" />
			<input type="submit" value="Confirm Bid"/>
		</form>
		</div>
	</body>
</html>

