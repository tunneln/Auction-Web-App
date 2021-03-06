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
		header('Location: https://fall-2015.cs.utexas.edu/cs105/noel/app-iter-2/sign_in.php?redir=update&fail=1');
	}
	if (!password_verify($pass, $hash)) {
		header('Location: https://fall-2015.cs.utexas.edu/cs105/noel/app-iter-2/sign_in.php?redir=update&fail=1');
	}
	$userDetails->bindValue(':usrname', $uname, PDO::PARAM_STR);
	$userDetails->execute();
	$details = $userDetails->fetch();
	$userDetails->closeCursor();
	$_SESSION['userName'] = $details['FORENAME'] . ' ' . $details['SURNAME'];
	$_SESSION['emailAddress'] = $details['EMAIL_ADDRESS'];
	$_SESSION['personId'] = $details['PERSON_ID'];		
}

if (!isset($_SESSION['userName']) || $_SESSION['userName'] == ' ') {
	header('Location: https://fall-2015.cs.utexas.edu/cs105/noel/app-iter-2/sign_in.php?redir=update');
}
$notice = $database->prepare(<<<'SQL'
	SELECT NOTIFICATIONS FROM PERSON
	WHERE PERSON_ID = :personId;
SQL
);
$thisAuctionQuery = $database->prepare(<<<'SQL'
	SELECT * FROM AUCTION
	JOIN AUCTION_STATUS ON AUCTION_STATUS.AUCTION_STATUS_ID = AUCTION.STATUS
	WHERE AUCTION.STATUS = 3;
SQL
);
$thisAuctionQuery->execute();
$thisAuctionQuery2 = $database->prepare(<<<'SQL'
	SELECT * FROM AUCTION
	JOIN AUCTION_STATUS ON AUCTION_STATUS.AUCTION_STATUS_ID = AUCTION.STATUS
	WHERE AUCTION.SELLER = :userID;
SQL
);
$thisAuctionQuery2->bindValue(':userID', $_SESSION['personId'], PDO::PARAM_INT);
$thisAuctionQuery2->execute();
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	<head>
		<title>User Profile</title>
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
			<h2> Your Listings </h2>
			<br/>
			<?php
				foreach ($thisAuctionQuery2->fetchAll() as $auction) {
			?>	
			<ul style="border: none;">	
				<li>
				<form action="list_new.php" method="post" enctype="multipart/form-data">	
				<label>Item Name:</label> <?= htmlspecialchars($auction['ITEM_CAPTION']) ?><br/>			    <label>Auction Ends:</label> <?= htmlspecialchars($auction['CLOSE_TIME']) ?><br/>
				<label>Status:</label> <?= htmlspecialchars($auction['NAME']) ?><br/>
				<input type="submit" value="Update"/>
				<input type="hidden" name="update" value="<?= htmlspecialchars($auction['AUCTION_ID'])?>" />
				<input type="hidden" name="item" value="<?= htmlspecialchars($auction['AUCTION_ID'])?>" />
				</form>
				<form action="listing_details.php" method="post" enctype="multipart/form-data">
					<input type="submit" value="Cancel" <?php if ($auction['NAME'] != 'Open') { ?> disabled="disabled" <?php } ?> />
					<input type="hidden" name="item" value="<?= htmlspecialchars($auction['AUCTION_ID']) ?>" />
					<input type="hidden" name="cancel" value="<?= htmlspecialchars($auction['AUCTION_ID'])?>" />
				</form>
				</li>		
			</ul>
		<br/>
			<?php
				}
				$thisAuctionQuery2->closeCursor();
			?>

			<h2> Won Auctions </h2>
			<br/>
			<?php
				foreach ($thisAuctionQuery->fetchAll() as $auction) {
			?>	
			<ul style="border: none;">	
				<li>
				<form action="pay.php" method="post" enctype="multipart/form-data">	
				<label>Item Name:</label> <?= htmlspecialchars($auction['ITEM_CAPTION']) ?><br/>			    <label>Auction Ends:</label> <?= htmlspecialchars($auction['CLOSE_TIME']) ?><br/>
				<label>Status:</label> <?= htmlspecialchars($auction['NAME']) ?><br/>
				<input type="submit" value="Pay"/>
				<input type="hidden" name="update" value="<?= htmlspecialchars($auction['AUCTION_ID'])?>"/>
				<input type="hidden" name="item" value="<?= htmlspecialchars($auction['AUCTION_ID'])?>"/>
				</form>
				</li>		
			</ul>
		<br/>
			<?php
				}
				$thisAuctionQuery->closeCursor();
			?>
			<h2> Notifications </h2>
			<?php 
				$notice->bindValue(':personId', $_SESSION['personId'], PDO::PARAM_INT);
				$notice->execute();
				$notifications = $notice->fetchColumn(0);
				echo htmlspecialchars($notifications);
			?>
		</div>
	</body>
</html>

