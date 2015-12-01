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

// verify login
if (isset($_POST['uname']) && isset($_POST['pass'])) {
	$uname = $_POST['uname'];
	$pass = $_POST['pass'];
	$verifyLogin->bindValue(':uname', $uname, PDO::PARAM_STR);
	$verifyLogin->execute();
	$hash = $verifyLogin->fetchColumn(0);
	$verifyLogin->closeCursor();
	if (!$hash) {
		header('Location: https://fall-2015.cs.utexas.edu/cs105/noel/app-iter-2/sign_in.php?redir=index&fail=1');
	}
	if (!password_verify($pass, $hash)) {
		header('Location: https://fall-2015.cs.utexas.edu/cs105/noel/app-iter-2/sign_in.php?redir=index&fail=1');
	}
	$userDetails->bindValue(':usrname', $uname, PDO::PARAM_STR);
	$userDetails->execute();
	$details = $userDetails->fetch();
	$userDetails->closeCursor();
	session_start();
	session_regenerate_id(true);
	$_SESSION['userName'] = $details['FORENAME'] . ' ' . $details['SURNAME'];
	$_SESSION['emailAddress'] = $details['EMAIL_ADDRESS'];
	$_SESSION['personId'] = $details['PERSON_ID'];	
}

$createUser = $database->prepare(<<<'SQL'
	INSERT INTO PERSON
		(PERSON_ID, SURNAME, FORENAME, EMAIL_ADDRESS, PASSWORD)
		VALUES (:personId, :surname, :forename, :email_address, :password);
SQL
);
$testUname = $database->prepare(<<<'SQL'
	SELECT COUNT(*) FROM PERSON
	WHERE EMAIL_ADDRESS = :test;
SQL
);

// if registering
if (isset($_POST['forename']) && isset($_POST['surname']) && isset($_POST['email_address']) && isset($_POST['password'])) {
	$forename = $_POST['forename']; 
	$surname = $_POST['surname'];
	$email_address = $_POST['email_address'];
	$password = $_POST['password'];

	$testUname->bindValue(':test', $email_address, PDO::PARAM_STR);
	$testUname->execute();
	$duplicates = $testUname->fetchColumn(0);
	$testUname->closeCursor();
	if ($duplicates >= 1) {
		$message = htmlspecialchars('Registration Unsuccessful: Email/Username already in use');
		goto rest;
	}
	$newIdQuery = $database->prepare('SELECT NEXT_SEQ_VALUE(:seqGenName);');
	$newIdQuery->bindValue(':seqGenName', 'PERSON', PDO::PARAM_STR);
	$newIdQuery->execute();
	$newPersonId = $newIdQuery->fetchColumn(0);
	$newIdQuery->closeCursor();
	$newPassword = password_hash($password, PASSWORD_DEFAULT);
	if (!$newPassword) {
		$message = htmlspecialchars('Registration Unsuccessful: Please choose a different password');
		goto rest;
	}

	$createUser->bindValue(':personId', $newPersonId, PDO::PARAM_INT);
	$createUser->bindValue(':surname', $surname, PDO::PARAM_STR);
	$createUser->bindValue(':forename', $forename, PDO::PARAM_STR);
	$createUser->bindValue(':email_address', $email_address, PDO::PARAM_STR);
	$createUser->bindValue(':password', $newPassword, PDO::PARAM_STR);
	$createUser->execute();
	$createUser->closeCursor();
	$message = htmlspecialchars('Registration Successful: Please sign in to start bidding and selling!');
}
rest:

$confirmPayment = $database->prepare(<<<'SQL'
 	UPDATE PERSON 
	SET NOTIFICATIONS = :note
	WHERE PERSON_ID = :seller OR PERSON_ID = :buyer;
SQL
);
$deleteWin = $database->prepare(<<<'SQL'
 	DELETE FROM AUCTION
	WHERE AUCTION_ID = :death;
SQL
);

if (isset($_POST['pay'])) {
	$newline = 13;
	$currentTime = new DateTime('NOW');
	$item = $_POST['item'];
	$pay = $_POST['pay'];
	$confirmPayment->bindValue(':seller', $pay, PDO::PARAM_INT);
	$confirmPayment->bindValue(':buyer', $_SESSION['personId'], PDO::PARAM_INT);
	$confirmPayment->bindValue(':note', "+ Payment for the newly won item has been recieved and is now processing! - TIME: " .  $currentTime->format('Y-m-d H:i:s'), PDO::PARAM_STR);
	$confirmPayment->execute();
	$confirmPayment->closeCursor();
	$deleteWin->bindValue(':death', $item, PDO::PARAM_INT);
	$deleteWin->execute();
	$deleteWin->closeCursor();
}

// if logging out
if (isset($_GET['logout'])) {
	session_destroy();
	$_SESSION = array();
}

$notice = $database->prepare(<<<'SQL'
	SELECT NOTIFICATIONS FROM PERSON
	WHERE PERSON_ID = :personId;
SQL
);

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	<head>
		<title>Work Product</title>
		<link rel="stylesheet" href="style.css" type="text/css"/>
		<meta charset="utf-8" />
	</head>
	<body>
		<?php if (isset($_SESSION['userName']) && $_SESSION['userName'] != ' ' && $_SESSION['userName'] != ' ') { ?>
		<div class="corner2"> Welcome, <a href="user_profile.php"><?= htmlspecialchars($_SESSION['userName']) ?></a>! | <a href="index.php?logout=1">LOGOUT?</a></div>
		<?php } ?> 
		<div class="corner"><a href="update.php">Update</a> &#x2022; <a href="cancel.php">Cancel</a> &#x2022; <a href="pay_list.php">Pay</a></div>
		<h1 class="title"> Auction Web Application </h1>
		<div class="navi">
			<ul>
				<li> <a href="index.php">Home</a> </li>
				<?php if (isset($_SESSION['userName']) && $_SESSION['userName'] != ' ' && $_SESSION['userName'] != ' ') { ?>
				<li> <a href="user_listings.php">My Listings</a> </li> 
				<?php } else { ?>
				<li> <a href="sign_in.php?redir=index">Sign In!</a> </li>
				<?php } ?>
				<li> <a href="listings.php">Browse Listings</a> </li> <li> <a href="list_new.php">List New Item</a> </li> </ul>
		</div>
		<div class="other">
		<?php if (isset($message)) { echo htmlspecialchars($message); } ?>
		<?php if (!isset($_SESSION['userName']) || $_SESSION['userName'] == ' ') { ?>
			<h3> Welcome to the premier auction web application! </h3>
		<?php } else { ?>
			<h1 style="text-decoration:underline;position:relative;left:100px;"> Notifications </h1>
		<?php 				
			$notice->bindValue(':personId', $_SESSION['personId'], PDO::PARAM_INT);
			$notice->execute();
			$notifications = $notice->fetchColumn(0);
			$notice->closeCursor();
			echo htmlspecialchars($notifications);
		?>

			<br/>
			<h3><strong><span style="position:relative; left:90px;"><a href="timer.php">Click Here To Fire Time</a>r</span></strong></h3>
		<?php } ?>
			<?php if (!isset($_SESSION['userName']) || $_SESSION['userName'] == ' ') { ?>
			<h4 style="position:relative; left:150px;"><a id="show" href="register.php">Register Here!</a></h4>
			<?php	} ?>
			<br/>
		</div>
	</body>
</html>

