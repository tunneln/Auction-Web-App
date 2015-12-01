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
		header('Location: https://fall-2015.cs.utexas.edu/cs105/noel/app-iter-2/sign_in.php?redir=list_new&fail=1');
	}
	if (!password_verify($pass, $hash)) {
		header('Location: https://fall-2015.cs.utexas.edu/cs105/noel/app-iter-2/sign_in.php?redir=list_new&fail=1');
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
	header('Location: https://fall-2015.cs.utexas.edu/cs105/noel/app-iter-2/sign_in.php?redir=list_new');
}

$thisAuctionQuery = $database->prepare(<<<'SQL'
	SELECT * FROM AUCTION
	WHERE AUCTION_ID = :fooId;
SQL
);

if (isset($_POST['item'])) {
	$item = $_POST['item'];
	$thisAuctionQuery->bindValue(':fooId', $item, PDO::PARAM_INT);
	$thisAuctionQuery->execute();
	$auction = $thisAuctionQuery->fetch();
	$thisAuctionQuery->closeCursor();
}

$categoryQuery = $database->prepare(<<<'SQL'
	SELECT * FROM ITEM_CATEGORY;
SQL
);
$conditionQuery = $database->prepare(<<<'SQL'
	SELECT * FROM ITEM_CONDITION;
SQL
);
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	<head>
		<title>List New Item</title>
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
		if (isset($_POST['update'])) {
?>
		<h3>- Update Item -</h3>
		<h6> Note: Only change fields you wish to update </h6>	
<?php
		} else {
?>
			<h3>- List New Item -</h3>
<?php
		}
?>
		
		<form action="listing_success.php" method="post" enctype="multipart/form-data">
<?php
		if (isset($_POST['update'])) {
?>
			<input type="hidden" name="update" value="1" />
<?php
		}
?>
			<label>Category:</label>
                    <select name="category"  required="required">
                    <option value="" disabled="disabled" selected="selected">Select Category</option>
                    <?php
                    $categoryQuery->execute();
                    foreach ($categoryQuery->fetchAll() as $category) {
                        ?>
			<option value="<?= htmlspecialchars($category['ITEM_CATEGORY_ID']) ?>" <?php
if (isset($_POST['update'])) {
	if ($category['ITEM_CATEGORY_ID'] == $auction['ITEM_CATEGORY']) {
		?> selected="<?php
		echo htmlspecialchars('selected');
		?>"<?php
	}
}
?>
><?= htmlspecialchars($category['NAME']) ?></option>
                        <?php
                    }
                    $categoryQuery->closeCursor();
                    ?>
                </select><br/><br/>
		<label>Name:</label> <input type="text" name="item_name" value="<?php
if (isset($_POST['update'])) {
	echo htmlspecialchars($auction['ITEM_CAPTION']); 
} else {
	echo htmlspecialchars('');
}
?>"
 required="required"/><br/><br/>
                <label>Description:</label> <input type="text" name="description" value="<?php
if (isset($_POST['update'])) {
	echo htmlspecialchars($auction['ITEM_DESCRIPTION']); 
} else {
	echo htmlspecialchars('');
}
?>" required="required"/><br/><br/>
                <label>Condition:</label>
                <select name="condition" required="required">
                    <option value="" disabled="disabled" selected="selected">Select Condition</option>
                    <?php
                    $conditionQuery->execute();
                    foreach ($conditionQuery->fetchAll() as $condition) {
                        ?>
				<option value="<?= htmlspecialchars($condition['ITEM_CONDITION_ID']) ?>" <?php
if (isset($_POST['update'])) {
	if ($condition['ITEM_CONDITION_ID'] == $auction['ITEM_CONDITION']) {
		?>selected="<?php
		echo htmlspecialchars('selected');
		?>"<?php
	}
}
?> ><?= htmlspecialchars($condition['NAME']) ?></option>
                        <?php
                    }
                    $conditionQuery->closeCursor();
                    ?>
                </select><br/><br/>
                <label>Photo:</label> <input type="file" name="photo" accept="image/jpeg"/><br/>
				<br/><br/>
<?php
		if (isset($_POST['update'])) {
?>
		<h6> Note: The Opening/Reserve Prices can only be changed if no bids have been made </h6>
<?php } ?>
               <label>Opening Price:</label><input type="number" step="0.01" min="<?php
if (isset($_POST['update'])) {
	echo htmlspecialchars($auction['BID_PRICE']); 
} else {
	echo htmlspecialchars('0.1');
}
?>" max="999999999.99" name="openingPrice" value="<?php
if (isset($_POST['update'])) {
	echo htmlspecialchars($auction['BID_PRICE']); 
} else {
	echo htmlspecialchars('');
}
?>" <?php if (isset($_POST['update']) && $auction['MAX_BIDDER'] != NULL) { ?> disabled="disabled"<?php } ?> required="required"/><br/><br/>
	<label>Reserve Price:</label><input type="number" step="0.01" min="0.01" max="999999999.99" name="reservePrice" value="<?php
if (isset($_POST['update'])) {
	echo htmlspecialchars($auction['RESERVE_PRICE']); 
} else {
	echo htmlspecialchars('');
}
?>" <?php if (isset($_POST['update']) && $auction['MAX_BIDDER'] != NULL) { ?> disabled="disabled"<?php } ?> required="required"/><br/><br/>
<?php
	if (!isset($_POST['update'])) {
?>
                <label>Closing Deadline:</label>
                <select name="month">
                    <option value="" disabled="disabled" selected="selected">Select Month</option>
                    <?php foreach(range(1,12) as $month) { ?>
                        <option value="<?= htmlspecialchars($month) ?>">
                            <?= htmlspecialchars(date("F",strtotime("0000-$month"))) ?>
                        </option>
                    <?php } ?>
                </select>
                <select name="day">
                    <option value="" disabled="disabled" selected="selected">Day</option>
                    <?php foreach(range(1,31) as $day) { ?>
                    <option value="<?= htmlspecialchars($day) ?>">
                        <?= htmlspecialchars($day) ?>
                    </option>
                    <?php } ?>
                </select>
                <select name="year">
                    <option value="" disabled="disabled" selected="selected">Year</option>
                    <?php foreach(range(2015,2069) as $year) { ?>
                    <option value="<?= htmlspecialchars($year) ?>">
                        <?= htmlspecialchars($year) ?>
                    </option>
                    <?php } ?>
                </select> @
                <select name="hour">
                    <option value="" disabled="disabled" selected="selected">Hr</option>
                    <?php foreach(range(0,23) as $hour) { ?>
                        <option value="<?= htmlspecialchars($hour) ?>">
                            <?= htmlspecialchars($hour) ?>
                        </option>
                    <?php } ?>
                </select>
                <select name="min">
                    <option value="" disabled="disabled" selected="selected">Mm</option>
                    <?php foreach(range(0,59) as $minute) { ?>
                        <option value="<?= htmlspecialchars($minute) ?>">
                            <?= htmlspecialchars($minute) ?>
                        </option>
                    <?php } ?>
                </select>
                <select name="sec">
                    <option value="" disabled="disabled" selected="selected">Ss</option>
                    <?php foreach(range(0,59) as $sec) { ?>
                        <option value="<?= htmlspecialchars($sec) ?>">
                            <?= htmlspecialchars($sec) ?>
                        </option>
                    <?php } ?>
		</select>
<?php } ?>
                <br/><br/><br/>
<?php
		    if (isset($_POST['update'])) {
?>
			<input type="submit" value="Update Item"/>
			<input type="hidden" name="update" value="1" />
			<input type="hidden" name="item" value=<?= htmlspecialchars($auction['AUCTION_ID']) ?>/>
<?php
		} else {
?>
				<input type="submit" value="List Item"/>
<?php
		}
?>
			</form>
		</div>
	</body>
</html>

