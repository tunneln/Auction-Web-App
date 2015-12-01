<?php
session_start(); require_once '/u/noel/CS105-PHP/openDatabase.php'; ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	<head>
		<title>Work Product</title>
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
		<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum tristique quam et ant				e tristique, eu luctus purus pharetra. Vestibulum scelerisque mauris sit amet rh		oncus porttitor. Cras vitae fringilla ipsum. Cras non vehicula dui. Praesent 					blandit ex dolor, sed suscipit elit dignissim a. Etiam lobortis mollis felis vel ferment		um. Fusce rutrum placerat tellus sit amet sollicitudin. Proin ut tincidunt odio. Nullam semper purus 			fringilla quam egestas finibus.
		Integer id mauris ut magna egestas gravida in sit amet ipsum. Nam congue mi vitae nisi congue, at convallis orci luctus. Integer sed enim sit amet eros mollis finibus. Aenean tristique lacinia dolor non convallis. Aliquam mattis sollicitudin dictum. Morbi sagittis venenatis erat sed pulvinar. Quisque tristique urna quis imperdiet tempus. Pellentesque consectetur eu elit vitae dictum. In venenatis turpis augue, vitae varius leo faucibus quis. Vestibulum lobortis molestie nibh a commodo.
		Curabitur epost efficitur purus, id condimentum turpis. Mauris consequat urna at pellentesque finibus. Suspendisse epost mi vehicula, accumsan magna non, rhoncus dui. Quisque nec ante a ipsum aliquam placerat eu sit amet nunc. Ut iaculis, mi ac facilisis tempor, ex quam vulputate elit, epost blandit lorem justo et nibh. Nullam fringilla sollicitudin ultrices. Fusce ut commodo diam. Maecenas at mauris molestie tortor pellentesque bibendum vitae vitae orci. Maecenas dignissim efficitur leo ac iaculis. Mauris aliquet scelerisque nulla, in egestas ipsum fermentum quis. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vivamus nec tincidunt nibh, at scelerisque ipsum. Ut tristique lacus id eros imperdiet bibendum. Vivamus viverra tellus sapien, vel euismod dui pretium non. Integer purus ex, pulvinar nec ultrices ut, finibus non urna.
	Interdum et malesuada fames ac ante ipsum primis in faucibus. Duis bibendum mollis luctus. Vestibulum pharetra ex ut justo viverra tempor. Aenean ex quam, gravida epost sagittis sed, ornare epost nisi. Duis ut placerat ex. Integer mollis est eu nisi gravida posuere. Nullam a lacus epost sem eleifend pharetra. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Aliquam erat volutpat. Phasellus id ligula epost nunc finibus ultricies nec at dui. Vestibulum vel tempus odio. Ut est lacus, sagittis feugiat dolor nec, cursus luctus lacus.
	Phasellus elementum, arcu in rutrum pretium, felis neque iaculis mauris, vel ullamcorper est tellus epost justo. Aliquam erat volutpat. Nullam dictum, dui nec dapibus fermentum, neque est ultricies enim, at lacinia est erat sit amet dui. Sed aliquet porttitor nisi epost tempor. Curabitur auctor, orci non consectetur lobortis, leo turpis rutrum est, sed malesuada augue nulla a arcu. Sed varius arcu mauris, a ornare tortor iaculis ac. Maecenas viverra neque lacus, non scelerisque ipsum volutpat et.</p>
			<h3><a href="register.php" style="text-decoration: underline;">Go Back To Registration</a></h3>
		</div>
	</body>
</html>

