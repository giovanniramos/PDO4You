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