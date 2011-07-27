<?php

/**
 * Users class
 * 
 * @classe Users
 * 
 * */


class Users 
{
	private static $message = null;
	private static $has_records = 0;
	

	/**
	 * Método principal que inicializa uma instância global de conexão
	 * 
	 * */ 
	public static function init()
	{
		// Instância de conexão iniciada e disponível
		PDO4You::getInstance();
		
		// Valida a entrada de dados enviados pelo formulário via POST, antes de gravar no banco 
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
				} else 
				if(empty($mail)){
					$error = 'Informe seu E-mail'; 
				}
				
				// Exibe uma mensagem se houver erros, caso contrário realiza o cadastro do usuário
				if(isset($error)){
					self::$message = '<i>'.$error.'</i><br />';
				} else {
					$sql = '
					{
						query : [
							{
								table: "users" ,
								values: { firstname: "'.$firstName.'", lastname: "'.$lastName.'", mail: "'.$mail.'"  }
							}
						] 
					}
					';
					
					// Carregando a conexão com banco de dados
					//PDO4You::getInstance();
					$retrieveId = PDO4You::insert($sql);
					self::$message = 'Registro #'.$retrieveId[0].' inserido com sucesso!<br />';
				}
			}
		}
		
		
		// Seleciona todos os registros de usuários cadastrados
		$sql = 'SELECT * FROM users';
		self::$has_records = PDO4You::select($sql);
	
	}


	/**
	 * Método que exibe todos os usuários cadastrados
	 * 
	 * */
	public function showUsers()
	{
		$html = null;
		if(self::$has_records):
			foreach(self::$has_records as $dba):
				$firstname = ucwords($dba["firstname"]);
				$lastname = ucwords($dba["lastname"]);
				$mail = $dba["mail"];
				
				$html.= '<div><strong>usuário:</strong> <span>'.$firstname.' '.$lastname.' ['.$mail.']</span></div>';
			endforeach;
		else:
			$html.= '<div class="relatorio_evento">Não há usuários cadastrados no momento.</div>';
		endif;
		
		return $html;
	}


	/**
	 * Método que checa e retorna o total de usuários cadastrados
	 * 
	 * */
	public function getTotalUsers()
	{
		return (is_array(self::$has_records)) ? count(self::$has_records) : 0;
	}


	/**
	 * Retorna uma mensagem
	 * 
	 * */
	public function getMessage()
	{
		return self::$message;
	}

}

?>