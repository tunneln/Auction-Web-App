<?php
session_start();

require_once '/u/noel/CS105-PHP/openDatabase.php'; 

$updateBid = $database->prepare(<<<'SQL'
	UPDATE AUCTION 
	SET BID_PRICE = :bidPrice,
	MAX_BIDDER = :buyer
	WHERE AUCTION_ID = :foo;
SQL
);
$updateStatus = $database->prepare(<<<'SQL'
	UPDATE AUCTION SET STATUS = :statusSet
	WHERE AUCTION_ID = :foo;
SQL
);
$thisAuctionQuery = $database->prepare(<<<'SQL'
    SELECT AUCTION_ID, STATUS, SELLER, OPEN_TIME, CLOSE_TIME, ITEM_CATEGORY, ITEM_CAPTION, ITEM_DESCRIPTION,
    ITEM_CONDITION, BID_PRICE, RESERVE_PRICE, ITEM_CONDITION_ID, ITEM_CATEGORY_ID, 
    ITEM_CATEGORY.NAME AS CAT_NAME, ITEM_CONDITION.NAME AS CON_NAME
    FROM AUCTION
    JOIN ITEM_CONDITION ON ITEM_CONDITION.ITEM_CONDITION_ID = AUCTION.ITEM_CONDITION
    JOIN ITEM_CATEGORY ON ITEM_CATEGORY.ITEM_CATEGORY_ID = AUCTION.ITEM_CATEGORY
    WHERE AUCTION.AUCTION_ID = :auctionId;
SQL
);
$sellerDude = $database->prepare(<<<'SQL'
    SELECT SURNAME, FORENAME
    FROM PERSON
    JOIN AUCTION ON AUCTION.SELLER = :sellerId;
SQL
);
$statusQuery = $database->prepare(<<<'SQL'
    SELECT AUCTION_STATUS.NAME 
    FROM AUCTION_STATUS
    JOIN AUCTION ON AUCTION_STATUS.AUCTION_STATUS_ID = AUCTION.STATUS;
    WHERE AUCTION.AUCTION_ID = :statusId;
SQL
);
$check = 0;
$holder = 0;
$check2 = 0;
if (isset($_POST['item'])) {
	$item = $_POST['item'];
	$holder = 1;
	if (isset($_POST['cancel'])) {
		$updateStatus->bindValue(':statusSet', 2, PDO::PARAM_INT);
		$updateStatus->bindValue(':foo', $item, PDO::PARAM_INT);
		$updateStatus->execute();
		$updateStatus->closeCursor();
		$check2 = 1;
		$message = 'You have successully cancelled your auction';
	}
}
if (isset($_GET['item'])) {
	$item = $_GET['item'];
	$holder = 1;
}
if ($holder == 1) {
$thisAuctionQuery->bindValue(':auctionId', $item, PDO::PARAM_INT);
$thisAuctionQuery->execute();
$temp = $thisAuctionQuery->fetch();
$time = new DateTime($temp['CLOSE_TIME']);
$currTime = new DateTime('NOW');
$thisAuctionQuery->closeCursor();
if (isset($_POST['bid'])) {
	if ($currTime > $time) {
		$check = 1;
		$message = htmlspecialchars('Bid was unsuccessful, the auction has already ended');
		$updateStatus->bindValue(':statusSet', 4, PDO::PARAM_INT);
		$updateStatus->bindValue(':foo', $item, PDO::PARAM_INT);
		$updateStatus->execute();
		$updateStatus->closeCursor();
	} else {	
		$bid = $_POST['bid'];
		$updateBid->bindvalue(':bidPrice', $bid, PDO::PARAM_INT);
		$updateBid->bindvalue(':foo', $item, PDO::PARAM_INT);
		$updateBid->bindValue(':buyer', $_SESSION['personId'], PDO::PARAM_INT);
		$updateBid->execute();
		$updateBid->closeCursor();
		$message = htmlspecialchars('Bid was successful!');
	}
}
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	<head>
		<title>Listing Details</title>
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
<?php
	if ($holder == 1) {
		$thisAuctionQuery->bindValue(':auctionId', $item, PDO::PARAM_INT);
		$thisAuctionQuery->execute();
		$auction = $thisAuctionQuery->fetch(); 
?>
		<div class="other">		
			<img style="float:right;margin-right:10%;height:40%;width:25%;" src="http://www.pcgam.com/img/bios/placeholder.jpg" alt=""/>
			<ul>
				<?php if (isset($message)) { echo htmlspecialchars($message); } ?>
				<li><strong><label>Item Name:</label></strong><?= htmlspecialchars($auction['ITEM_CAPTION']) ?><br/><br/></li>
				<li><strong><label>Category:</label></strong><?= htmlspecialchars($auction['CAT_NAME']) ?><br/><br/></li>
				<li><strong><label>Condition:</label></strong><?= htmlspecialchars($auction['CON_NAME']) ?><br/><br/></li>
                <li><strong><label>Seller:</label></strong>
                <?php
		$sellerDude->bindvalue(':sellerId', $auction['SELLER'], PDO::PARAM_INT);	
		$sellerDude->execute();
                $sellerD = $sellerDude->fetch();
                echo htmlspecialchars($sellerD['FORENAME'] . ' ' . $sellerD['SURNAME']);
                ?>
                    <br/><br/>
                </li>
				<li><strong><label>Description:</label></strong> <?= htmlspecialchars($auction['ITEM_DESCRIPTION']) ?><br/><br/></li>
				<li><strong><label>Auction Opened:</label></strong> <?= htmlspecialchars($auction['OPEN_TIME']) ?><br/><br/></li>
				<li><strong><label>Auction Ends:</label></strong> <?= htmlspecialchars($auction['CLOSE_TIME']) ?><br/><br/></li>
				<li><strong><label>Current Max Bid:</label></strong> <?= htmlspecialchars('$' . $auction['BID_PRICE']) ?><br/><br/></li>
				<li><strong><label>Status:</label></strong> 
<?php 
		$statusQuery->bindValue(':statusId', $item, PDO::PARAM_INT);
		$statusQuery->execute();
		$currStatus = $statusQuery->fetch();
		if ($check2 == 1 || $currStatus['NAME'] == 'Cancelled') {
			echo htmlspecialchars('Cancelled');
		}
		else if ($check == 1) { 
			echo htmlspecialchars('Ended');
		} else {
			echo htmlspecialchars($currStatus['NAME']);	
		}
?>	
				</li>

			</ul>
		<form action="bid.php" method="post">
			<input type="submit" value="Place Bid"/>
			<input type="hidden" name="item" value=<?= htmlspecialchars($item) ?>/>
		</form>

		</div>
<?php
		} else {
			echo htmlspecialchars('Sorry, we could not find the item you are looking for');
		}
		$thisAuctionQuery->closeCursor();
		$statusQuery->closeCursor();
?>
	</body>
</html>

