<?php

/**
 * Teste class
 * 
 * @classe Teste
 * 
 * */


class Teste 
{
	function __construct()
	{
		PDO4You::getInstance();
		PDO4You::getInstance('bookstore', 'mysql:host=localhost;port=3306;');	
	}


	/**
	 * Usando PDO4You::select()
	 * Teste de consulta padrão
	 * 
	 * */
	static function executeSelect1()
	{
		return PDO4You::select('SELECT * FROM books LIMIT 2');
	}


	/**
	 * Usando PDO4You::select()
	 * Teste de consulta apontando para outro banco de dados, definindo uma outra instância de conexão
	 * 
	 * */
	static function executeSelect2()
	{
		//PDO4You::setInstance('bookstore');
		return PDO4You::select('SELECT * FROM books LIMIT 2', 'bookstore');
	}


	/**
	 * Métodos PDO4You de consulta sendo usados: select, selectNum, selectObj, selectAll, rowCount
	 * Teste de consulta, alternando entre instâncias de conexão
	 * 
	 * */
	static function executeSelect3()
	{
		$sql = 'SELECT * FROM books LIMIT 2';
		
		PDO4You::setInstance('bookstore');
		$result1 = PDO4You::select($sql);
		$result2 = PDO4You::selectNum($sql);
		$result3 = PDO4You::selectObj($sql);
		$result4 = PDO4You::selectAll($sql);
		$result5 = PDO4You::rowCount($sql);
		
		echo "<pre><h3>select() = FETCH_ASSOC - DEFAULT:</h3> ",print_r($result1, true),"</pre>";
		echo "<pre><h3>selectNum() = FETCH_NUM:</h3> ",print_r($result2, true),"</pre>";
		echo "<pre><h3>selectObj() = FETCH_OBJ:</h3> ",print_r($result3, true),"</pre>";
		echo "<pre><h3>selectAll() = FETCH_BOTH:</h3> ",print_r($result4, true),"</pre>";
		echo "<pre><h3>rowCount():</h3> ",print_r($result5, true),"</pre>";
		echo "<br /><br />";
		
		
		$sql = 'SELECT * FROM users;';
		
		PDO4You::setInstance('pdo4you');
		$result = PDO4You::select($sql);
		
		// Retornando apenas a primeira posição do resultado
		return $result[0];
	}


	/**
	 * Usando PDO4You::insert()
	 * 
	 * */
	static function executeInsert()
	{
		$hash = time();
		$sql = '
		{
			query : [
				{
					table: "users" ,
					values: { mail: "email1_'.$hash.'@gmail.com" }
				},{
					table: "users" ,
					values: { mail: "email2_'.$hash.'@gmail.com" }
				},{
					table: "users" ,
					values: { mail: "email3_'.$hash.'@gmail.com" }
				}
			] 
		}
		';
		
		$result = PDO4You::insert($sql);
		return $result;
	}


	/**
	 * Usando PDO4You::update()
	 * 
	 * */
	static function executeUpdate()
	{
		$hash = time();
		$sql = '
		{
			query : [
				{
					table: "users" ,
					values: { mail: "novo-email1_'.$hash.'@gmail.com" } ,
					where: { id: 2 }
				},{
					table: "users" ,
					values: { mail: "novo-email2_'.$hash.'@gmail.com" } ,
					where: { id: 3 }
				},{
					table: "books" ,
					values: { title: "novo-titulo_'.$hash.'", author: "novo-autor_'.$hash.'" } ,
					where: { id: 1 }
				}
			] 
		}
		';
		
		$result = PDO4You::update($sql);
		return $result;
	}


	/**
	 * Usando PDO4You::delete()
	 * 
	 * */
	static function executeDelete()
	{
		$sql = '
		{
			query : [
				{
					table: "users" ,
					where: { id: 2 }
				},{
					table: "users" ,
					where: { id: 5 }
				},{
					table: "users" ,
					where: { id: 10 }
				},{
					table: "books" ,
					where: { id: 10 }
				}
			] 
		}
		';
		$result = PDO4You::delete($sql);
		return $result;
	}

}
