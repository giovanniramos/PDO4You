<?php

/**
 * Test class
 * 
 * @classe Test
 * 
 * */

class Test
{
	private $sql;
	
	public function __construct()
	{
		// Iniciando a instância de conexão com a base de dados
		PDO4You::getInstance();
		
		// Exibindo detalhes sobre a meta do servidor da base de dados conectada
		PDO4You::getServerInfo();
		
		// Query de consulta
		$this->sql = "SELECT * FROM books LIMIT 2";
		
		
		echo '<div style="margin: 20px 10px;">';
	}
	
		
	/**
	 * Métodos PDO4You em uso: select()
	 * */
	public function showMethodSelect($db = null)
	{
		$rs = PDO4You::select($this->sql, $db);
		
		echo "<div class=\"source\">&nbsp;<strong>M&eacute;todo de consulta (FETCH_ASSOC):</strong> PDO4You::select() </div>";
		echo "<div class=\"source bottom margin\">";
		echo "<strong>Cl&aacute;usula de consulta:</strong> ". $this->sql ."</br>";
		echo "<strong>Total de registros afetados:</strong> ". PDO4You::rowCount() ."</br>";
		echo "<strong>Resultado da consulta em:</strong> ". PDO4You::getDatabase() ."</br>";
		echo "<pre>". print_r($rs, true) ."</pre>";
		echo "</div>";
	}
	
	
	/**
	 * Métodos PDO4You em uso: select(), selectNum() , selectObj() , selectAll()
	 * */
	public function showAllMethodsOfSelect()
	{
		PDO4You::setInstance('bookstore');
		$rs_1 = PDO4You::select($this->sql);
		$rs_2 = PDO4You::selectNum($this->sql);
		$rs_3 = PDO4You::selectObj($this->sql);
		$rs_4 = PDO4You::selectAll($this->sql);
		
		PDO4You::setInstance('pdo4you');
		$rs_5 = PDO4You::select($this->sql);
		
		
		echo "<div class=\"source\">&nbsp;<strong>M&eacute;todo de consulta (FETCH_ASSOC):</strong> PDO4You::select() </div>";
		echo "<div class=\"source bottom margin\">";
		echo "<pre>". print_r($rs_1, true) ."</pre>";
		echo "</div>";
		
		echo "<div class=\"source\">&nbsp;<strong>M&eacute;todo de consulta (FETCH_NUM):</strong> PDO4You::selectNum() </div>";
		echo "<div class=\"source bottom margin\">";
		echo "<pre>". print_r($rs_2, true) ."</pre>";
		echo "</div>";
		
		echo "<div class=\"source\">&nbsp;<strong>M&eacute;todo de consulta (FETCH_OBJ):</strong> PDO4You::selectObj() </div>";
		echo "<div class=\"source bottom margin\">";
		echo "<pre>". print_r($rs_3, true) ."</pre>";
		echo "</div>";
		
		echo "<div class=\"source\">&nbsp;<strong>M&eacute;todo de consulta (FETCH_BOTH):</strong> PDO4You::selectAll() </div>";
		echo "<div class=\"source bottom margin\">";
		echo "<pre>". print_r($rs_4, true) ."</pre>";
		echo "</div>";
		
		echo "<div class=\"source\">&nbsp;<strong>M&eacute;todo de consulta (FETCH_ASSOC):</strong> PDO4You::select() </div>";
		echo "<div class=\"source bottom margin\">";
		echo "<pre>". print_r($rs_5, true) ."</pre>";
		echo "</div>";
	}
	
	
	/**
	 * Usando PDO4You::insert()
	 * */
	public function executeInsert()
	{
		$hash = time();
		
		$sql = '
		{ query : [
			{
				table: "users" ,
				values: { firstname: "'.self::getFakeName().'", lastname: "'.self::getFakeName().'", mail: "mail_1_'.$hash.'@domain.com" }
			},{
				table: "users" ,
				values: { firstname: "'.self::getFakeName().'", lastname: "'.self::getFakeName().'", mail: "mail_2_'.$hash.'@domain.com" }
			}
		] }
		';
		
		$rs = PDO4You::insert($sql, 'pdo4you');
		
		echo "<div class=\"source\">&nbsp;<strong>M&eacute;todo de inserção (INSERT):</strong> PDO4You::insert() </div>";
		echo "<div class=\"source bottom margin\">";
		echo "<strong>ID do &uacute;ltimo registro inserido:</strong> ". PDO4You::lastId() ."</br>";
		echo "<strong>Total de registros inseridos:</strong> ". PDO4You::rowCount() ."</br>";
		echo "<strong>Resultado da opera&ccedil;&atilde;o em:</strong> ". PDO4You::getDatabase() ."</br>";
		echo "<span><pre>". print_r($rs, true) ."</pre></span></br></br>";
		echo "</div>";
	}
	
	
	/**
	 * Usando PDO4You::update()
	 * */
	static function executeUpdate()
	{
		$hash = time();
		
		$sql = '
		{ query : [
			{
				table: "users" ,
				values: { mail: "new_1_'.$hash.'@domain.com" } ,
				where: { id: 2 }
			},{
				table: "users" ,
				values: { mail: "new_2_'.$hash.'@domain.com" } ,
				where: { id: 12 }
			},{
				table: "users" ,
				values: { mail: "new_3_'.$hash.'@domain.com" } ,
				where: { id: 30 }
			},{
				table: "books" ,
				values: { title: "new_title_'.$hash.'", author: "new_author_'.$hash.'" } ,
				where: { id: 1 }
			}
		] }
		';
		
		$rs = PDO4You::update($sql, 'pdo4you');
		
		echo "<div class=\"source\">&nbsp;<strong>M&eacute;todo de atualização (UPDATE):</strong> PDO4You::update() </div>";
		echo "<div class=\"source bottom margin\">";
		echo "<strong>Total de registros atualizados:</strong> ". PDO4You::rowCount() ."</br>";
		echo "<strong>Resultado da opera&ccedil;&atilde;o em:</strong> ". PDO4You::getDatabase() ."</br>";
		echo "<span><pre>". print_r($rs, true) ."</pre></span></br></br>";
		echo "</div>";
	}
	
	
	/**
	 * Usando PDO4You::delete()
	 * */
	static function executeDelete()
	{
		$sql = '
		{ query : [
			{
				table: "users" , 
				where: { id: 4 }
			},{
				table: "users" ,
				where: { id: 20 }
			},{
				table: "users" ,
				where: { id: 30 }
			},{
				table: "books" ,
				where: { id: 10 }
			}
		] }
		';
		
		$rs = PDO4You::delete($sql, 'pdo4you');
		
		echo "<div class=\"source\">&nbsp;<strong>M&eacute;todo de exclusão (DELETE):</strong> PDO4You::delete() </div>";
		echo "<div class=\"source bottom margin\">";
		echo "<strong>Total de registros excluídos:</strong> ". PDO4You::rowCount() ."</br>";
		echo "<strong>Resultado da opera&ccedil;&atilde;o em:</strong> ". PDO4You::getDatabase() ."</br>";
		echo "<span><pre>". print_r($rs, true) ."</pre></span></br></br>";
		echo "</div>";
	}
	
	
	/**
	 * Método que gera um nome falso 
	 * */
	public function getFakeName()
	{
		$v = array("a", "e", "i", "o", "u");
		$c = array("b", "c", "d", "f", "g", "h", "j", "l", "m", "n", "p", "q", "r", "s", "t", "v", "x", "z");
		
		return ucfirst($c[array_rand($c,1)].$v[array_rand($v,1)].$c[array_rand($c, 1)].$v[array_rand($v,1)].$v[array_rand($v,1)]);
	}
	
	
	public function __destruct()
	{
		echo '</div>';
	}
	
}
