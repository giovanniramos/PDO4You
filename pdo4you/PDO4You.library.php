<?php

/**
 * Function Library
 * 
 * @author Giovanni Ramos <giovannilauro@gmail.com>
 * @copyright 2010-2013, Giovanni Ramos
 * @since 2010-09-07
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @link https://github.com/giovanniramos/PDO4You
 * 
 * */

/**
 * The Autoloader allows the automatic loading of classes
 * 
 * @param string $classname Class name called during the proceedings
 * @throws Throws an exception if it can not find the class
 * 
 * */
function pdo4you_autoload($classname)
{
    try {
        try {
            spl_autoload_extensions('.class.php');
            spl_autoload($classname);
        } catch (Exception $e) {
            throw new Exception("The Autoloader could not find class '<i>" . $classname . ".class.php</i>', preventing its automatic instance.");
        }
    } catch (Exception $e) {
        PDO4You::stackTrace($e);
    }
}

spl_autoload_register("pdo4you_autoload");

/**
 * Interprets an INI file with heritage section
 * 
 * @param string $filename Filename
 * @return array
 * @link https://gist.github.com/4217717
 * 
 */
function parse_ini_file_advanced($filename)
{
    $nArr = array();
    $oArr = parse_ini_file($filename, true);

    if (is_array($oArr)) {
        foreach ($oArr as $k => $v) {
            $k = preg_split('~[:]~', preg_replace('~[\s]{1,}~', null, $k));
            $t = &$nArr;
            foreach ($k as $x) {
                $t = &$t[$x];
            }
            $t = $v;
        }
    }

    return $nArr;
}

/**
 * Returns the sum of occurrences, in an array of a given condition satisfied
 * 
 * @param mixed $value The value or array to be evaluated
 * @param string $operator Operator of evaluation
 * @param string $conditional Conditional assignment
 * @return integer
 * @link https://gist.github.com/3100679
 * 
 * */
function countWhere($value = 1, $operator = '==', $conditional = 1)
{
    $array = is_array($value) ? $value : (array) $value;
    $operator = !in_array($operator, array('<', '>', '<=', '>=', '==', '!=')) ? '==' : $operator;

    $i = 0;
    foreach ($array as $current) {
        $match = null;

        eval('$match = (bool)("' . $current . '"' . $operator . '"' . $conditional . '");');

        $i = $match ? ++$i : $i;
    }

    return $i;
}

/**
 * Removes the style markup in html tags, derived from a text editor
 * 
 * @param string $value The input string
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
 * Converts a character from a colon to HTML
 * 
 * @param string $value The input string
 * @return string
 * @link https://gist.github.com/5716880
 * 
 * */
function htmlColon($value)
{
    $value = preg_replace('~[:]~', '&#58;', htmlentities($value));

    return $value;
}
