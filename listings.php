<?php
session_start();

require_once '/u/noel/CS105-PHP/openDatabase.php';
$thisAuctionQuery = $database->prepare(<<<'SQL'
	SELECT * FROM AUCTION;
SQL
);

$sellerDude = $database->prepare(<<<'SQL'
    SELECT PERSON.SURNAME, PERSON.FORENAME, AUCTION_STATUS.NAME
    FROM PERSON
    JOIN AUCTION ON AUCTION.SELLER = PERSON.PERSON_ID
    JOIN AUCTION_STATUS ON AUCTION_STATUS.AUCTION_STATUS_ID = AUCTION.STATUS
    WHERE AUCTION.SELLER = :sellerId AND AUCTION_STATUS.AUCTION_STATUS_ID = :statusId;
SQL
);
$thisAuctionQuery->execute();
$updateStatus = $database->prepare(<<<'SQL'
	UPDATE AUCTION SET STATUS = :statusSet
	WHERE AUCTION_ID = :foo;
SQL
);

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	<head>
		<title>Browse Listings</title>
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
			<h3 style="font-size:130%;">Browse Listings &amp; Filter Results</h3>
			
			<h3> View Only: </h3>
			<form action="listings.php" method="post" enctype="multipart/form-data">	
				<input type="radio" name="limit" value="Open"/>Open<br/>
				<input type="radio" name="limit" value="Cancelled"/>Cancelled<br/>	
				<input type="radio" name="limit" value="Won"/>Won<br/>
				<input type="radio" name="limit" value="Ended"/>Ended<br/>
				<input type="submit" value="Filter"/><br/><br/>
			</form>
			<h3> Auction Listings </h3>
			<?php
			foreach ($thisAuctionQuery->fetchAll() as $auction) {
?>	
		<?php 	if (isset($_POST['limit'])) {
				$limit = $_POST['limit'];
				if ($limit == 'Open') { $limit = 1; }
				if ($limit == 'Won') { $limit = 3; } 
				if ($limit == 'Cancelled') { $limit = 2; }
				if ($limit == 'Ended') { $limit = 4; }
				if ($auction['STATUS'] == $limit) {
				?>
				<form action="bid.php" method="post" enctype="multipart/form-data">	
				<label>Item Name:</label> <?= htmlspecialchars($auction['ITEM_CAPTION']) ?><br/>
                		<label>Seller:</label>
                    		<?php
				$sellerDude->bindvalue(':sellerId', $auction['SELLER'], PDO::PARAM_INT);
				$sellerDude->bindValue(':statusId', $auction['STATUS'], PDO::PARAM_INT);
		    		$sellerDude->execute();
		    		$sellerD = $sellerDude->fetch();
            	    		$sellerDude->closeCursor();
                    		echo htmlspecialchars($sellerD['FORENAME'] . ' ' . $sellerD['SURNAME']);
				?><br/>
				
			    	<label>Status:</label> <?= htmlspecialchars($sellerD['NAME']) ?>
				<br/>	
				<label>Auction Ends:</label> <?= htmlspecialchars($auction['CLOSE_TIME']) ?><br/>
				<a href="listing_details.php?item=<?= urlencode($auction['AUCTION_ID']) ?>">Read More (further description)</a><br/>
				<input type="hidden" name="item" value="<?=htmlspecialchars($auction['AUCTION_ID'])?>"/>
			<input type="submit" name="bid" value="Place Bid" <?php if ($auction['STATUS'] != 1) { ?> disabled="disabled" <?php } ?>/><br/><br/>
				</form>
<?php				} 
	} else { ?> 
				<form action="bid.php" method="post" enctype="multipart/form-data">	
				<label>Item Name:</label> <?= htmlspecialchars($auction['ITEM_CAPTION']) ?><br/>
                		<label>Seller:</label>
                    		<?php
				$sellerDude->bindvalue(':sellerId', $auction['SELLER'], PDO::PARAM_INT);
				$sellerDude->bindValue(':statusId', $auction['STATUS'], PDO::PARAM_INT);
		    		$sellerDude->execute();
		    		$sellerD = $sellerDude->fetch();
            	    		$sellerDude->closeCursor();
                    		echo htmlspecialchars($sellerD['FORENAME'] . ' ' . $sellerD['SURNAME']);
				?><br/>
				
			    	<label>Status:</label> <?= htmlspecialchars($sellerD['NAME']) ?>
				<br/>	
				<label>Auction Ends:</label> <?= htmlspecialchars($auction['CLOSE_TIME']) ?><br/>
				<a href="listing_details.php?item=<?= urlencode($auction['AUCTION_ID']) ?>">Read More (further description)</a><br/>
				<input type="hidden" name="item" value="<?=htmlspecialchars($auction['AUCTION_ID'])?>"/>
				<input type="submit" name="bid" value="Place Bid" <?php if ($auction['STATUS'] != 1) { ?> disabled="disabled" <?php } ?>/><br/><br/>
				</form>
			<?php }
		} 
			$thisAuctionQuery->closeCursor();
?>

		</div>
	</body>
</html>

