<?php
session_start(); 
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== "on") {
	header('HTTP/1.1 403 Forbidden: TLS Required');
	exit(1);
}
if (!isset($_SESSION['userName']) || $_SESSION['userName'] == ' ') {
	header('Location: https://fall-2015.cs.utexas.edu/cs105/noel/app-iter-2/index.php');
}

require_once '/u/noel/CS105-PHP/openDatabase.php'; 
$payment = $database->prepare(<<<'SQL'
	SELECT BID_PRICE, SELLER, ITEM_CAPTION
	FROM AUCTION
	WHERE AUCTION_ID = :auctionId;
SQL
);
if (isset($_POST['item'])) {
	$item = $_POST['item'];
}
$payment->bindValue(':auctionId', $item, PDO::PARAM_INT);
$payment->execute();
$pay = $payment->fetch();
$payment->closeCursor();
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	<head>
		<title>Pay</title>
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
				<li> <a href="list_new.php">List New Item</a> </li>
			</ul>
		</div>
		<div class="other">
		<form action="index.php" method="post">
		<h4> You owe a total of $<?= htmlspecialchars($pay['BID_PRICE']) ?> for the item: <em><?= htmlspecialchars($pay['ITEM_CAPTION']) ?></em>.<br/>Please enter your shipping and payment information below</h4>
			<label>Full Legal Name:</label><input type="text" required="required" value=""/>
			<br/>
			<label>Credit Card #:</label><input pattern=".{19,19}" type="text" required="required" value=""/>
			<label>CVV: </label><input type="text" pattern=".{3,3}" required="required" value=""/><br/>
			<label>Billing Address:</label><input type="text" required="required" value=""/>
			<label>Billing State/ZIP:</label><input type="text" required="required" value=""/><br/>
			<label>Shipping Address:</label><input type="text" required="required" value=""/>
			<label>Shipping State/ZIP:</label><input type="text" required="required" value=""/><br/><br/>
			<input type="hidden" name="item" value="<?= htmlspecialchars($item) ?>" />
			<input type="hidden" name="pay" value="<?= htmlspecialchars($pay['SELLER']) ?>" />
			<input type="submit" value="Confirm Payment"/>
		</form>
		</div>
	</body>
</html>

