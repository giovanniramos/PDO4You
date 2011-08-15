<?php

/**
 * Biblioteca de funções
 * 
 * @author Giovanni Ramos <giovannilauro@gmail.com>
 * @copyright Giovanni Ramos
 * @twitter giovanni_ramos
 * @since 2010-09-07 
 * @version 2.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package PDO4You
 *
 * */


/**
 * Autoloading classes
 * 
 * @param string $className Nome da classe sendo instânciada
 * @throws Emite uma exceção em caso de erro
 * 
 * */
function __autoload($className) 
{
	try {
		try {
			spl_autoload_extensions('.class.php');
			spl_autoload($className);
		} catch (Exception $e) {
			throw new Exception ("A classe '<i>$className.class.php</b>' n&atilde;o p&ocirc;de ser localizada. Imposs&iacute;vel carregar automaticamente sua inst&acirc;ncia.");
		}
	} catch (Exception $e) {
		PDO4You::stackTrace($e);
	}
}


/**
 * Método que conta o total de ocorrências em um array, com base em determinada condição satisfeita 
 * 
 * @param mixed $expr1 Valor que será avaliado
 * @param string $expr2 Operador de avaliação
 * @param string $expr3 Atribuição condicional
 * @return integer Retorna o total de ocorrências
 * 
 * */
function countWhere($expr1 = 1, $expr2 = '==', $expr3 = 1)
{
	$expr1 = is_array($expr1) ? $expr1 : (array)$expr1;
	$expr2 = !in_array($expr2, array('<','>','<=', '>=','==', '!=')) ? '==' : $expr2;
	
	$i = 0;
	foreach($expr1 as $current):
		$match = null;
		eval('$match = (bool)("'.$current.'"'.$expr2.'"'.$expr3.'");');
		
		$i = $match ? $i+1 : $i;
	endforeach;
	
	return $i;
}

?>