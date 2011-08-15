<?php

/**
 * Usuario class
 * 
 * @class Usuario
 * 
 * */


class Usuario 
{
	const NAME = 'Nome';
	const LAST_NAME = 'Sobrenome';
	const MAIL = 'E-mail';
	const ADD_USER = 'Adicionar novo usuário';
	const TOTAL_USERS = 'Total de usuários cadastrados';
	
	private static $message = null;
	private static $has_records = 0;
	
	
	/**
	 * Método principal que inicia a conexão
	 * */
	public function init()
	{
		// Instância de conexão iniciada e disponível
		PDO4You::getInstance();
		
		// Valida a entrada do formulário submetido, antes de gravar na base de dados
		if($_POST){
			
			if(isset($_POST["firstname"]))
				$firstName = filter_input(INPUT_POST, 'firstname', FILTER_SANITIZE_STRING);
			
			if(isset($_POST["lastname"]))
				$lastName = filter_input(INPUT_POST, 'lastname', FILTER_SANITIZE_STRING);
			
			if(isset($_POST["mail"]))
				$mail = filter_input(INPUT_POST, 'mail', FILTER_VALIDATE_EMAIL);
			
			
			if(isset($firstName) && isset($lastName) && isset($mail)){
			
				// Realizando validações
				if(empty($firstName) || empty($lastName)){
					$error = 'Informe seu Nome e Sobrenome';
				} else if(empty($mail)) {
					$error = 'Informe seu E-mail'; 
				}
				
				// Exibe uma mensagem se houver erros, caso contrário continua com o processo de cadastro de usuário
				if(isset($error)){
					self::$message = '<i>'.$error.'</i><br />';
				} else {
					$sql = '
					{ query : [
						{
							table: "users" ,
							values: { firstname: "'.$firstName.'", lastname: "'.$lastName.'", mail: "'.$mail.'"  }
						}
					] }
					';
					
					// Carregando a conexão com banco de dados
					$result = PDO4You::insert($sql);
					self::$message = "Registro #{$result[0]} inserido com sucesso!<br />";
				}
			}
		}
		
		// Capturo os registros de todos os usuários cadastrados
		self::$has_records = PDO4You::select("SELECT * FROM users");
	}
	
	
	/**
	 * Método que exibe todos os usuários cadastrados
	 * */
	public function showUsers()
	{
		if(self::$has_records):
			$html = null;
			foreach(self::$has_records as $dba):
				$firstname = ucwords($dba["firstname"]);
				$lastname = ucwords($dba["lastname"]);
				$mail = $dba["mail"];
				
				$html.= '<div>- '.$firstname.' '.$lastname.' ['.$mail.']</div>';
			endforeach;
			return $html;
		endif;
		
		return '<div>Não há usuários cadastrados no momento.</div>';
	}
	
	
	/**
	 * Método que retorna o total de usuários cadastrados
	 * */
	public function getTotalUsers()
	{
		return (is_array(self::$has_records)) ? count(self::$has_records) : 0;
	}
	
	
	/**
	 * Retorna uma mensagem
	 * */
	public function getMessage()
	{
		return self::$message."<br />";
	}
	
}

?>