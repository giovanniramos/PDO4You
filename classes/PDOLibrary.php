<?php

/**
 * Biblioteca de funções
 * 
 * @category Library
 * @package PDO4You
 * @author Giovanni Ramos <giovannilauro@gmail.com>
 * @copyright 2010-2012, Giovanni Ramos
 * @since 2010-09-07
 * @version 2.1
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @link https://github.com/giovanniramos/PDO4You
 *
 * */

/**
 * Autoloading de classes
 * 
 * @param string $class Nome da classe invocada durante a instância
 * @throws Dispara uma exceção em caso de erro
 * 
 * */
function __autoload($class)
{
    try {
        try {
            spl_autoload_extensions('.class.php');
            spl_autoload($class);
        } catch (Exception $e) {
            throw new Exception("O Autoloader n&atilde;o conseguiu localizar a classe '<i>Test.class.php</i>', impedindo a inst&acirc;ncia autom&aacute;tica.");
        }
    } catch (Exception $e) {
        PDO4You::stackTrace($e);
    }
}

/**
 * Soma o total de ocorrências em um array de uma determinada condição satisfeita 
 * 
 * @param mixed $expr1 Valor que será avaliado
 * @param string $expr2 Operador de avaliação
 * @param string $expr3 Atribuição condicional
 * @return integer Retorna o total de ocorrências
 * @link https://gist.github.com/3100679
 * @see PDO4You::rowCount()
 * 
 * */
function countWhere($expr1 = 1, $expr2 = '==', $expr3 = 1)
{
    $expr1 = is_array($expr1) ? $expr1 : (array) $expr1;
    $expr2 = !in_array($expr2, array('<', '>', '<=', '>=', '==', '!=')) ? '==' : $expr2;

    $i = 0;
    foreach ($expr1 as $current):
        $match = null;
        eval('$match = (bool)("' . $current . '"' . $expr2 . '"' . $expr3 . '");');

        $i = $match ? $i + 1 : $i;
    endforeach;

    return $i;
}

/**
 * Remove a marcação de estilo em tags html, oriundas de um editor de texto por exemplo
 * 
 * @param string $str A string de entrada
 * @return string
 * @link https://gist.github.com/3078188
 * 
 * */
function clearStyle($str)
{
    $str = preg_replace("~<(a|ol|ul|li|h[1-6r]|d[dlt]|em|p|i|b|s|strong|span|div|table|t[dhr])\s?(style.*)?/>~i", "<$1>", $str);

    return $str;
}

/**
 * Converte o caracter de 'Dois Pontos' em uma string
 * 
 * @param string $str A string de entrada
 * @return string
 * 
 * */
function htmlColon($str)
{
    $str = htmlentities($str);
    $str = preg_replace('~[:]~', '&#58;', $str);

    return $str;
}
