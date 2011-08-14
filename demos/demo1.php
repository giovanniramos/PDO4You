<?php

/**
 * Configuração de testes
 * 
 * @author Giovanni Ramos <giovannilauro@gmail.com>
 * @copyright Giovanni Ramos
 * @since 2011-07-27
 * @version 1.0
 * 
 * */
          

// Load of all necessary classes
require_once('../classes/PDOYou.load.php');
                                

// Load the User test case
require_once('tests/Users.class.php');

// Instânciando a classe Users
$users = new Users();
$users->init();


?>
<!DOCTYPE html>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<title>PDO4You</title>
<html>
<body>
	
	Total de usuários cadastrados: <?=$users->getTotalUsers();?>
	

	<h1>Usuários</h1>
	
	<?=$users->showUsers();?>
	
	
	<br />
	<form method="post">
	<h2>Adicionar novo usuário</h2>
	
	<div>Nome: <input type="text" name="firstname" /></div>
	<div>Sobrenome: <input type="text" name="lastname" /></div>
	<div>E-mail: <input type="text" name="mail" /></div>
	<div><input type="submit" value="Cadastrar" /></div>
	</form>
	<br />
	
	<?=$users->getMessage();?>

</body>
</html>