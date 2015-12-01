<?php
session_start();
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== "on") {
	header('HTTP/1.1 403 Forbidden: TLS Required');
	exit(1);
}
if (!isset($_SESSION['userName']) || $_SESSION['userName'] == ' ') {
	header('Location: https://fall-2015.cs.utexas.edu/cs105/noel/app-iter-2/sign_in.php?redir=index.php');
}

require_once '/u/noel/CS105-PHP/openDatabase.php';
if (isset($_POST['item'])) {
	$item = $_POST['item'];
}

$updateAll = $database->prepare(<<<'SQL'
	UPDATE AUCTION 
	SET ITEM_CATEGORY = :ucat, 
	ITEM_CAPTION = :ucap,
	ITEM_DESCRIPTION = :udes,
	ITEM_CONDITION = :ucon,
	BID_PRICE = :ubid,
	RESERVE_PRICE = :ures
	WHERE AUCTION_ID = :itemID;
SQL
);	

$updateStatus = $database->prepare(<<<'SQL'
	UPDATE AUCTION
	SET STATUS = :st;
	WHERE AUCTION_ID = :iid;
SQL
);
if (isset($_POST['update'])) {
	$updateAll->bindValue(':ucat', $_POST['category'], PDO::PARAM_INT);
	$updateAll->bindValue(':ucap', $_POST['item_name'], PDO::PARAM_STR);
	$updateAll->bindValue(':udes', $_POST['description'], PDO::PARAM_STR);
	$updateAll->bindValue(':ucon', $_POST['condition'], PDO::PARAM_INT);
	$updateAll->bindValue(':ubid', $_POST['openingPrice'], PDO::PARAM_INT);
	$updateAll->bindValue(':ures', $_POST['reservePrice'], PDO::PARAM_INT);
	$updateAll->bindValue(':itemID', $_POST['item'], PDO::PARAM_INT);
	$updateAll->execute();
	$updateAll->closeCursor();
	$postNewId = $item;
} else {
$listingPost = $database->prepare(<<<'SQL'
    INSERT INTO AUCTION
      (AUCTION_ID, STATUS, SELLER, OPEN_TIME, CLOSE_TIME, ITEM_CATEGORY, ITEM_CAPTION, ITEM_DESCRIPTION, ITEM_CONDITION, BID_PRICE, RESERVE_PRICE)
      VALUES (:auctionId, :status, :seller, :openTime, :closeTime, :itemCategory, :itemCaption, :itemDescription, :itemCondition, :bidPrice, :reservePrice);
SQL
);
$newIdQuery = $database->prepare('SELECT NEXT_SEQ_VALUE(:seqGenName);');
$newIdQuery->bindValue(':seqGenName', 'AUCTION', PDO::PARAM_STR);
$newIdQuery->execute();
$postNewId = $newIdQuery->fetchColumn(0);
$newIdQuery->closeCursor();

$listingPost->bindValue(':auctionId', $postNewId, PDO::PARAM_INT);
$listingPost->bindValue(':status', 1, PDO::PARAM_INT);
$listingPost->bindValue(':seller', $_SESSION['personId'], PDO::PARAM_INT);

$time = new DateTime();
$openDate = $time->format("Y-m-d H:i:s");
$exitDate = $_POST['year'] . '-' . $_POST['month'] . '-' . $_POST['day'] . '  ' . $_POST['hour'] . ':' . $_POST['min'] . ':' . $_POST['sec'];

$listingPost->bindValue(':openTime', $openDate, PDO::PARAM_STR);
$listingPost->bindValue(':closeTime', $exitDate, PDO::PARAM_STR);
$listingPost->bindValue(':itemCategory', $_POST['category'], PDO::PARAM_INT);
$listingPost->bindValue(':itemCaption', $_POST['item_name'], PDO::PARAM_STR);
$listingPost->bindValue(':itemDescription', $_POST['description'], PDO::PARAM_STR);
$listingPost->bindValue(':itemCondition', $_POST['condition'], PDO::PARAM_INT);
$listingPost->bindValue(':bidPrice', $_POST['openingPrice'], PDO::PARAM_INT);
$listingPost->bindValue(':reservePrice', $_POST['reservePrice'], PDO::PARAM_INT);
$listingPost->execute();
$listingPost->closeCursor();
} ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <title>New Listing Processing</title>
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
$thisAuctionQuery = $database->prepare(<<<'SQL'
    SELECT AUCTION.ITEM_PHOTO, AUCTION.ITEM_CAPTION, ITEM_CATEGORY.NAME AS CATEGORY_NAME, ITEM_CONDITION.NAME AS CONDITION_NAME, AUCTION.ITEM_DESCRIPTION, AUCTION.CLOSE_TIME, AUCTION.BID_PRICE, AUCTION_STATUS.NAME AS STATUS_NAME, AUCTION.AUCTION_ID
    FROM AUCTION
    JOIN ITEM_CATEGORY ON AUCTION.ITEM_CATEGORY = ITEM_CATEGORY.ITEM_CATEGORY_ID
    JOIN ITEM_CONDITION ON AUCTION.ITEM_CONDITION = ITEM_CONDITION.ITEM_CONDITION_ID
    JOIN AUCTION_STATUS ON AUCTION.STATUS = AUCTION_STATUS.AUCTION_STATUS_ID
    WHERE AUCTION.AUCTION_ID = :ID;
SQL
);
$thisAuctionQuery->bindValue(':ID', $postNewId, PDO::PARAM_INT);
if ($thisAuctionQuery->execute()) {
    $item = $thisAuctionQuery->fetch();
    $thisAuctionQuery->closeCursor();
?>
    <div class="other">
        <h3>Your new item posting was a success!</h3>
        <img style="float:right;margin-right:10%;height:40%;width:25%;" src="http://www.pcgam.com/img/bios/placeholder.jpg" alt=""/>
        <ul>
            <li><strong><label>Item Name:</label> </strong> <?= htmlspecialchars($item['ITEM_CAPTION']) ?><br/><br/></li>
            <li><strong><label>Category:</label></strong> <?= htmlspecialchars($item['CATEGORY_NAME']) ?><br/><br/></li>
            <li><strong><label>Description:</label></strong> <?= htmlspecialchars($item['ITEM_DESCRIPTION']) ?><br/><br/></li>
	    <li><strong><label>Condition: </label></strong> <?= htmlspecialchars($item['CONDITION_NAME']) ?><br/><br/></li>
	    <li><strong><label>Starting Bid: </label></strong> <?= htmlspecialchars('$' . $item['BID_PRICE']) ?><br/><br/></li>
            <li><strong><label>Auction Ends:</label></strong> <?php
                $endTime= new DateTime($item['CLOSE_TIME']);
                echo $endTime->format("l, F d, Y, g:i:s a");
                ?><br/><br/>
            </li>
            <?php
            $currentTime = new DateTime();
            $timeLeft = $currentTime->diff($endTime);
            ?>
	    <li><strong><label>Status:</label></strong><?php
	    if ($endTime >= $currentTime) {  
		    echo htmlspecialchars($item['STATUS_NAME']);
	    } else {
		    echo htmlspecialchars('Ended');
	    }
		?><br/><br/></li>
	    <li>
            <label>
                <?php
	    if ($endTime < $currentTime) { 
		$updateStatus->bindValue(':st', 4, PDO::PARAM_INT);
		$updateStatus->bindValue(':iid', $item, PDO::PARAM_INT);
		$updateStatus->execute();
		$updateStatus->closeCursor();
		?>
                <h3>This auction has already ended - did you make a mistake on the time?</h3>
                <?php } ?>
	    </label>
	    </li>
	</ul>
	<h6> Note: You cannot Update/Cancel Listings that have already ended </h6>
	<form action="list_new.php" method="post" enctype="multipart/form-data">
	    <input type="submit" value="Update Listing" <?php if ($item['STATUS_NAME'] != 'Open') { ?> disabled="disabled" <?php } ?> />
	    <input type="hidden" name="update" value="1"/>
 	    <input type="hidden" name="item" value="<?= htmlspecialchars($postNewId)?>"/>
	</form>
	<form action="listing_details.php" method="post" enctype="multipart/form-data" >
	    <input type="submit" value="Cancel Listing" <?php if ($item['STATUS_NAME'] != 'Open') { ?> disabled="disabled" <?php } ?>/>
	    <input type="hidden" name="cancel" value="1"/>
            <input type="hidden" name="item" value="<?= htmlspecialchars($postNewId)?>"/>
	</form>
    </div>
    <?php
} else {
    echo htmlspecialchars('sorry, we could not find the item you are looking for, please try again later.');
}
$thisAuctionQuery->closeCursor();
?>
</body>
</html>

