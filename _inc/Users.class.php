<?php

/**
 * Users - Sample class
 * 
 * */


class Users {


	/**
	 * Inicializa o módulo e armazena os dados selecionados
	 * 
	 * @access public
	 * @return void
	 * 
	 * */
	public function init() {
		$pdo = PDO4You::getInstance();
		try {
			$sql = '
				SELECT
					u.* 
				FROM
					users u
			';
			$pre = $pdo->prepare($sql);
			$pre->execute();
			$this->dba_records = $pre->fetchAll(PDO::FETCH_ASSOC);
			$this->has_records = $pre->rowCount();
		} catch (PDOException $e) {
			echo 'Erro: '.$e->getMessage().'<br /><br />';
		}
		$pdo = null;
	}


	/**
	 * Lista todos os registros cadastrados
	 * 
	 * @access public
	 * @return string
	 * 
	 * */
	public function getRecords() {
		$html = null;
		if($this->has_records):
			foreach($this->dba_records as $dba):
				$name = ucwords($dba["name"]);
				$lastname = ucwords($dba["lastname"]);
				
				$html.= '
					<div>
						<div><strong>Usuário:</strong> <span>'.$name.' '.$lastname.'</span></div>
						<div><strong>Email:</strong> <span>'.$dba["mail"].'</span></div>
						
					</div>
					<br />
				';
			endforeach;
		else:
			$html.= '
				<div class="relatorio_evento">
					Não há usuários cadastrados no momento.
				</div>
			';
		endif;
		return $html;
	}


	/**
	 * Retorna o total de registros cadastrados
	 * 
	 * @access public
	 * @return string
	 * 
	 * */
	public function getTotalRecords() {
		return $this->has_records;
	}


}

?>