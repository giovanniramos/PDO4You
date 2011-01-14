<?php

error_reporting(E_ALL|E_STRICT);
date_default_timezone_set('America/Halifax');


// Carregando interface
require '_inc/PDOConfig.class.php';
require '_inc/PDO4You.class.php';
require '_inc/Users.class.php';


// Conexão direta via driver dsn (OPCIONAL)
//PDO4You::singleton('mysql:host=localhost;port=3306;dbname=pdo4you', 'root', '1234');


$cod = (isset($_GET['cod'])) ? $_GET['cod'] : NULL;
$cmd = (isset($_GET['cmd'])) ? $_GET['cmd'] : NULL;

// Ação para adicionar novo registro no banco de dados
if($cmd == 'add'):

	$error = null;

	if(isset($_POST["name"])) $P_Name = trim($_POST["name"]);
	if(isset($_POST["lastname"])) $P_LastName = trim($_POST["lastname"]);
	if(isset($_POST["mail"])) $P_Mail = trim($_POST["mail"]);


	if(isset($P_Name) && isset($P_LastName) && isset($P_Mail)){

		// Fazendo as validações
		if(empty($P_Name) || empty($P_LastName)){
			$error = "Informe seu Nome e Sobrenome";
		}else{
			if(empty($P_Mail)){
				$error = "Informe seu Email"; 
			}else if(!preg_match("/^\S+@[\w\d.-]{2,}\.[\w]{2,6}$/iU", $P_Mail)){
				$error = "Email inválido"; 
			}
		}

		// Se não houver erros, executar o método de cadastro de usuários (INSERT)
		if(is_null($error)){

			$retrieveId = PDO4You::insert( 'users', 
				array(
					array(
						'name' 		=> $P_Name, 
						'lastname' 	=> $P_LastName, 
						'mail' 		=> $P_Mail
					)
				)
			);

		}

	}

endif;
?>
<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
<html>

<body>
<div>

	<?
	// Instânciando a classe
	$users = new Users();
	$users->init();
	?>


	<h1>Usuários</h1>

	<!-- Bloco com total de usuários cadastrados -->
	<div>
		<p>Total de Usuários: <?=$users->getTotalRecords();?></p> 
	</div>


	<!-- Bloco listando todos os usuários cadastrados -->
	<div>
		<?=$users->getRecords();?>
	</div>


	<!-- Formulário de cadastro -->
	<form action="?cmd=add" method="post">
		<div>
			<h2>Adicionar novo usuário</h2>
			<div><strong>Nome:</strong> <span><input type="text" name="name" /></span></div>
			<div><strong>Sobrenome:</strong> <span><input type="text" name="lastname" /></span></div>
			<div><strong>Email:</strong> <span><input type="text" name="mail" /></span></div>
			<div><input type="submit" value="Cadastrar" /></div>
		</div>
	</form>


	<!-- Este bloco exibe o id do último registro inserido, ao cadastrar um novo usuário no banco de dados -->
	<div>
		<?php
		if($cmd == 'add'):

			echo (!isset($ultimoId)) ? NULL : 'Registro #'.$ultimoId.', adicionado com sucesso!';
			
			if(!is_null($error)) echo '<i>'.$error.'</i>';

		endif;
		?>
	</div>



</div>
</body>
</html>