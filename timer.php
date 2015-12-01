<?php
error_reporting(-1);
ini_set("display_errors", 1);
session_start();
require_once '/u/noel/CS105-PHP/openDatabase.php';

$thisAuctionQuery = $database->prepare(<<<'SQL'
	SELECT * FROM AUCTION;
SQL
);
$updateStatus = $database->prepare(<<<'SQL'
	UPDATE AUCTION
	SET AUCTION.STATUS = :statusSet
	WHERE AUCTION_ID = :foo;
SQL
);
$updateSNote = $database->prepare(<<<'SQL'
	UPDATE PERSON
	SET PERSON.NOTIFICATIONS = :snote
	WHERE PERSON_ID = :sid;
SQL
);
$updateBNote = $database->prepare(<<<'SQL'
	UPDATE PERSON
	SET PERSON.NOTIFICATIONS = :bnote
	WHERE PERSON_ID = :bid;
SQL
);

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	<head>
		<title>Timer</title>
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
			<h2> TIMER FIRED! </h2>
			<h3> Upadated Auction Listings </h3>
<?php
			$thisAuctionQuery->execute();
			foreach ($thisAuctionQuery->fetchAll() as $auction) {

 				$time = new DateTime($auction['CLOSE_TIME']);
				$currentTime = new DateTime('NOW');
				if ($auction['STATUS'] != 2) {
				if ($time < $currentTime) {
					$updateStatus->bindValue(':foo', $auction['AUCTION_ID'], PDO::PARAM_INT);
					if ($auction['MAX_BIDDER'] == $_SESSION['personId']) {
						$updateStatus->bindValue(':statusSet', 3, PDO::PARAM_INT);
					} else {
						$updateStatus->bindValue(':statusSet', 4, PDO::PARAM_INT);
					}
					
					$updateStatus->execute();
					$updateStatus->closeCursor();	
					if ($auction['BID_PRICE'] < $auction['RESERVE_PRICE']) {
						$updateSNote->bindValue(':sid', $auction['SELLER'], PDO::PARAM_INT);
						$updateSNote->bindValue(':snote', "+ One of your posted auctions has ended, unfortunately the bid did not meet your reserve price; there is no winning bid - TIME: " . $currentTime->format('Y-m-d H:i:s'), PDO::PARAM_STR);
						$updateSNote->execute();
						$updateSNote->closeCursor();

					} else {
						$updateBNote->bindValue(':bid', $auction['MAX_BIDDER'], PDO::PARAM_INT);
						$updateBNote->bindValue(':bnote', "+ Congratulations! You have just won an auction for the listing: (" . $auction['ITEM_CAPTION'] . "). Go to the (Pay) tab on the top right corner so can recieve your new prize - TIME: " . $currentTime->format('Y-m-d H:i:s'), PDO::PARAM_STR);
						$updateBNote->execute();
						$updateBNote->closeCursor();
						$updateSNote->bindValue(':sid', $auction['SELLER'], PDO::PARAM_INT);
						$updateSNote->bindValue(':snote', "+ Congratulations! Your lising for (" . $auction['ITEM_CAPTION'] . ") has been sold - TIME: " . $currentTime->format('Y-m-d H:i:s'), PDO::PARAM_STR);
						$updateSNote->execute();
						$updateSNote->closeCursor();
					}
				}
				}
			} 
			$thisAuctionQuery->closeCursor();
?>

		</div>
	</body>
</html>

