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

?>