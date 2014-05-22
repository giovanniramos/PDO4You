<?php

namespace PDO4You;

/**
 * Pagination class
 * 
 * @author Giovanni Ramos <giovannilauro@gmail.com>
 * @copyright 2010-2014, Giovanni Ramos
 * @since 2010-09-07
 * @license http://opensource.org/licenses/MIT
 * @link http://github.com/giovanniramos/PDO4You
 * @package PDO4You
 */
class Pagination
{
    static private $query;
    static private $paging;
    static private $paginator;
    static private $friendly_url;
    static private $limit_per_page = 5;
    static private $total_records = 0;
    static private $total_of_pages = 0;
    static public $buttons = array(
        'first' => 'FIRST',
        'previous' => '&#9668;',
        'next' => '&#9658;',
        'last' => 'LAST',
    );

    /**
     * Sets the activation of paging
     * 
     * @access public static
     * @param boolean $friendly_url OPTIONAL Enables URLs friendlies
     */
    public static function setPagination($friendly_url = false)
    {
        self::setPaging(true);
        self::setFriendlyUrl($friendly_url);
    }

    /**
     * Enables paging of records
     * 
     * @access private static
     * @param boolean $paging Enables pagination
     */
    private static function setPaging($paging)
    {
        self::$paging = $paging;
    }

    /**
     * Gets the activation of pagination
     * 
     * @access public static
     * @see PDO4You::selectRecords()
     * @return boolean
     */
    public static function getPaging()
    {
        return self::$paging;
    }

    /**
     * Enables navigation by URLs friendlies
     * 
     * @access public static
     * @param boolean $friendly_url Enables URLs friendlies
     */
    public static function setFriendlyUrl($friendly_url)
    {
        self::$friendly_url = (boolean) $friendly_url;
    }

    /**
     * Builds a query and sets the number of records per page
     * 
     * @access public static
     * @param string $query SQL query
     * @param array $records Records of the query
     * @see PDO4You::selectRecords()
     * @return string
     */
    public static function buildQuery($query, $records)
    {
        $page = self::getCurrentPage();
        $limit = self::getLimitPerPage();
        $offset = abs(($page - 1) * $limit);

        self::setTotalOfRecords($records);
        self::setTotalOfPages($limit);

        self::$query = $query . ($limit == -1 ? null : ' LIMIT ' . $limit . ' OFFSET ' . $offset);

        return self::$query;
    }

    /**
     * Sets the total number of records
     * 
     * @access private static
     * @param array $records Records of the query
     */
    private static function setTotalOfRecords($records)
    {
        self::$total_records = count($records);
    }

    /**
     * Gets the total number of records
     * 
     * @access public static
     * @return integer
     */
    public static function getTotalOfRecords()
    {
        return self::$total_records;
    }

    /**
     * Sets the total number of pages
     * 
     * @access private static
     * @param integer $limit Limit of records
     */
    private static function setTotalOfPages($limit)
    {
        self::$total_of_pages = ceil(self::$total_records / $limit);
    }

    /**
     * Gets the total number of pages
     * 
     * @access private static
     * @param integer
     */
    private static function getTotalOfPages()
    {
        return self::$total_of_pages;
    }

    /**
     * Sets the number of records per page
     * 
     * @access public static
     * @param integer $limit Maximum of records per page
     */
    public static function setLimitPerPage($limit)
    {
        self::$limit_per_page = (int) $limit;
    }

    /**
     * Gets the number of records per page
     * 
     * @access private static
     * @return integer
     */
    private static function getLimitPerPage()
    {
        return self::$limit_per_page;
    }

    /**
     * Sets the paginator
     * 
     * @access public static
     * @param string $paginator Parameter used as paginator
     */
    public static function setPaginator($paginator)
    {
        self::$paginator = (string) $paginator;
    }

    /**
     * Get the number of the current page
     * 
     * @access private static
     * @return integer
     */
    private static function getCurrentPage()
    {
        return isset($_REQUEST[self::$paginator]) ? (int) $_REQUEST[self::$paginator] : 1;
    }

    /**
     * Builds the paginator
     * 
     * @access private static
     * @param integer $page Page number
     * @return string
     */
    private static function buildPaginator($page)
    {
        $_R = $_REQUEST;
        $_S = $_SERVER;
        $http = isset($_S['HTTPS']) && strcasecmp($_S['HTTPS'], 'off') ? 'https://' : 'http://';
        $host = isset($_S['HTTP_X_FORWARDED_HOST']) ? $_S['HTTP_X_FORWARDED_HOST'] : isset($_S['HTTP_HOST']) ? $_S['HTTP_HOST'] : $_S['SERVER_NAME'];
        $path = pathinfo($_S['SCRIPT_NAME']);
        $path_parts = $path['dirname'] . '/' . (self::$friendly_url ? $path['filename'] : $path['basename']);

        $request = isset($_R[self::$paginator]) ? array_slice($_R, 1) : $_R;
        $paginator = stripslashes($http . $host . $path_parts);

        if (self::$friendly_url) {
            array_walk($request, create_function('&$v,$k', '$v="$k/$v";'));
            $params = implode($request, '/');
            $paginator.= '/' . self::$paginator . '/' . (int) $page . ($params ? '/' . $params : '');
        } else {
            $params = http_build_query($request);
            $paginator.= '?' . self::$paginator . '=' . (int) $page . ($params ? '&' . $params : '');
        }

        return $paginator;
    }

    /**
     * Displays the pagination
     * 
     * @access public static
     * @return null|string
     */
    public static function getPagination()
    {
        if (self::getPaging() == false) {
            return null;
        }

        $page = self::getCurrentPage();
        $total_of_pages = self::getTotalOfPages();

        $nave = '<div class="pagination">';

        if ($page != 1) {
            $nave.= '<a class="first" href="' . self::buildPaginator('1') . '">' . self::$buttons['first'] . '</a>';
        } else {
            $nave.= '<a class="first nolink">' . self::$buttons['first'] . '</a>';
        }

        if ($page != 1 && $total_of_pages > 0) {
            $nave.= '<a class="previous" href="' . self::buildPaginator($page - 1) . '">' . self::$buttons['previous'] . '</a>';
        } else {
            $nave.= '<a class="previous nolink">' . self::$buttons['previous'] . '</a>';
        }

        if ($total_of_pages == 0) {
            $nave.= '<a class="selected">1</a>';
        } else {
            for ($x = 1; $x <= $total_of_pages; $x++) {
                if ($page == $x + 3)
                    $nave.= '<a href="' . self::buildPaginator($x) . '">' . $x . '</a>';
                if ($page == $x + 2)
                    $nave.= '<a href="' . self::buildPaginator($x) . '">' . $x . '</a>';
                if ($page == $x + 1)
                    $nave.= '<a href="' . self::buildPaginator($x) . '">' . $x . '</a>';
                if ($page == $x)
                    $nave.= '<a class="selected">' . $x . '</a>';
                if ($page == $x - 1)
                    $nave.= '<a href="' . self::buildPaginator($x) . '">' . $x . '</a>';
                if ($page == $x - 2)
                    $nave.= '<a href="' . self::buildPaginator($x) . '">' . $x . '</a>';
                if ($page == $x - 3)
                    $nave.= '<a href="' . self::buildPaginator($x) . '">' . $x . '</a>';
            }
        }

        if ($page < $total_of_pages) {
            $nave.= '<a class="next" href="' . self::buildPaginator($page + 1) . '">' . self::$buttons['next'] . '</a>';
        } else {
            $nave.= '<a class="next nolink">' . self::$buttons['next'] . '</a>';
        }

        if ($page != $total_of_pages && $total_of_pages > 0) {
            $nave.= '<a class="last" href="' . self::buildPaginator($total_of_pages) . '">' . self::$buttons['last'] . '</a>';
        } else {
            $nave.= '<a class="last nolink">' . self::$buttons['last'] . '</a>';
        }

        $nave.= '</div>';

        return $nave;
    }

}