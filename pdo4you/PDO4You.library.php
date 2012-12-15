<?php

/**
 * Biblioteca de funções
 * 
 * @category Library
 * @package PDO4You
 * @author Giovanni Ramos <giovannilauro@gmail.com>
 * @copyright 2010-2013, Giovanni Ramos
 * @since 2010-09-07
 * @version 2.7+
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @link https://github.com/giovanniramos/PDO4You
 *
 * */

/**
 * O Autoloader permite o carregamento automático de classes
 * 
 * @param string $classname Nome da classe invocada durante a instância
 * @throws Gera uma exceção caso não consiga localizar a classe
 * 
 * */
function pdo4you_autoload($classname)
{
    try {
        try {
            spl_autoload_extensions('.class.php');
            spl_autoload($classname);
        } catch (Exception $e) {
            throw new Exception("O Autoloader n&atilde;o conseguiu localizar a classe '<i>" . $classname . ".class.php</i>', impedindo a sua inst&acirc;ncia autom&aacute;tica.");
        }
    } catch (Exception $e) {
        PDO4You::stackTrace($e);
    }
}
// Possibilita declarar deversas funções de autoload, ideal p/ bibliotecas
spl_autoload_register("pdo4you_autoload");


/**
 * Interpreta um arquivo INI com herança de seção
 * 
 * @param string $filename Nome do arquivo
 * @return array
 * @link https://gist.github.com/4217717
 * 
 */
function pdo4you_parse_ini_file_advanced($filename)
{
    $nArr = array();
    $oArr = parse_ini_file($filename, true);

    if (is_array($oArr)):
        foreach ($oArr as $k => $v):
            $k = preg_split('~[:]~', preg_replace('~[\s]{1,}~', null, $k));
            $t = &$nArr;
            foreach ($k as $x)
                $t = &$t[$x];
            $t = $v;
        endforeach;
    endif;

    return $nArr;
}

/**
 * Conta o número de vezes em um Array que um valor esteja vazio
 * 
 * @param array $array Vetor que será avaliado
 * @return integer
 * 
 * */
function countValues(array $array)
{
    $i = 0;
    foreach ($array as $k => $v)
        if (!empty($v))
            $i++;

    return $i;
}

/**
 * Conta o número de ocorrências em um Array de uma determinada condição satisfeita
 * 
 * @param mixed $value Valor que será avaliado
 * @param string $operator Operador de avaliação
 * @param string $conditional Atribuição condicional
 * @return integer
 * @link https://gist.github.com/3100679
 * @see PDO4You::rowCount()
 * 
 * */
function pdo4you_countWhere($value = 1, $operator = '==', $conditional = 1)
{
    $array = is_array($value) ? $value : (array) $value;
    $operator = !in_array($operator, array('<', '>', '<=', '>=', '==', '!=')) ? '==' : $operator;

    $i = 0;
    foreach ($array as $k):
        $match = null;

        eval('$match = (bool)("' . $k . '"' . $operator . '"' . $conditional . '");');

        $i = $match ? ++$i : $i;
    endforeach;

    return $i;
}

/**
 * Remove a marcação de 'estilo' em tags HTML
 * Apropriado em entradas oriundas de um editor de texto
 * 
 * @param string $value O valor de entrada
 * @return string
 * @link https://gist.github.com/3078188
 * 
 * */
function clearStyle($value)
{
    $value = preg_replace("~<(a|ol|ul|li|h[1-6r]|d[dlt]|em|p|i|b|s|strong|span|div|table|t[dhr])\s?(style.*)?/>~i", "<$1>", $value);

    return $value;
}

/**
 * Converte o caracter de 'dois pontos'
 * 
 * @param string $value O valor de entrada
 * @return string
 * 
 * */
function htmlColon($value)
{
    $value = htmlentities($value);
    $value = preg_replace('~[:]~', '&#58;', $value);

    return $value;
}
