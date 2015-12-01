<?php
session_start(); 
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== "on") {
	header('HTTP/1.1 403 Forbidden: TLS Required');
	exit(1);
}
if (!isset($_SESSION['userName']) || $_SESSION['userName'] == ' ') {
	header('Location: https://fall-2015.cs.utexas.edu/cs105/noel/app-iter-2/sign_in.php?redir=user_listings');
}

require_once '/u/noel/CS105-PHP/openDatabase.php'; 
$thisAuctionQuery = $database->prepare(<<<'SQL'
	SELECT * FROM AUCTION
	JOIN AUCTION_STATUS ON AUCTION_STATUS.AUCTION_STATUS_ID = AUCTION.STATUS
	WHERE AUCTION.SELLER = :userID;
SQL
);
$thisAuctionQuery->bindValue(':userID', $_SESSION['personId'], PDO::PARAM_INT);
$thisAuctionQuery->execute();
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	<head>
		<title>User Listings</title>
		<link rel="stylesheet" href="style.css" type="text/css"/>
		<meta charset="utf-8" /> </head> <body>
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
		<h2> <?= htmlspecialchars($_SESSION['userName']) ?>'s Listings </h2>
			<br/>
			<?php
			foreach ($thisAuctionQuery->fetchAll() as $auction) {
			?>	
			<ul style="border: none;">	
				<li>
				<form action="list_new.php" method="post" enctype="multipart/form-data">	
				<label>Item Name:</label> <?= htmlspecialchars($auction['ITEM_CAPTION']) ?><br/>			    <label>Auction Ends:</label> <?= htmlspecialchars($auction['CLOSE_TIME']) ?><br/>
				<label>Status:</label> <?= htmlspecialchars($auction['NAME']) ?><br/>
				<input type="submit" value="Update" <?php if ($auction['NAME'] != 'Open') { ?> disabled="disabled" <?php } ?> />
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
			$thisAuctionQuery->closeCursor();
			?>
		</div>
	</body>
</html>

