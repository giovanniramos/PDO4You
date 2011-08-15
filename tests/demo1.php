<?php

// Load of all necessary classes
require_once("../PDO4You.load.php");


// Creating an instance
$demo = new User(); //--> English Class
#$demo = new Usuario(); //--> Classe em Português
$demo->init();
PDO4You::setStyle();

?>
<!DOCTYPE html>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<title>PDO4You</title>
<html>
<body class="margin">

	<?=$demo::TOTAL_USERS;?>: <?=$demo->getTotalUsers();?>
	
	<form method="post">
	<h2><?=$demo::ADD_USER;?></h2>
	
	<div><?=$demo::NAME;?>: <input type="text" name="firstname" /></div>
	<div><?=$demo::LAST_NAME;?>: <input type="text" name="lastname" /></div>
	<div><?=$demo::MAIL;?>: <input type="text" name="mail" /></div>
	<div><input type="submit" value="Register" /></div>
	</form>
	<br />
	
	<?=$demo->getMessage();?>

	<?=$demo->showUsers();?>
	
</body>
</html>