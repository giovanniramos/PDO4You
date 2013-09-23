<?php

// Defining namespaces
namespace PDO4You;

/**
 * Pagination class
 * 
 * @author Giovanni Ramos <giovannilauro@gmail.com>
 * @copyright 2010-2013, Giovanni Ramos
 * @since 2010-09-07
 * @license http://opensource.org/licenses/MIT
 * @link http://github.com/giovanniramos/PDO4You
 * @package PDO4You
 * 
 * */
class Pagination
{
    static private $link;
    static private $slug;
    static private $limit = -1;
    static private $query;
    static private $paging = false;
    static private $page;
    static private $page_nave;
    static private $total_records = 0;
    static private $total_per_page = 0;

    /**
     * Enables and sets the number of records in paging
     * 
     * @param integer $limit Maximum of records per page
     */
    public static function setPagination($limit = 5)
    {
        self::setPaging(true);
        self::$limit = $limit;
    }

    /**
     * Sets the activation of paging
     * 
     * @param boolean $paging Enables paging
     */
    public static function setPaging($paging)
    {
        self::$paging = $paging;
    }

    /**
     * Gets the activation of paging
     * 
     */
    public static function getPaging()
    {
        return self::$paging;
    }

    /**
     * Sets the total records in paging
     * 
     * @param int $records Total records in paging
     */
    public static function setTotalPagingRecords($records)
    {
        self::$total_records = count($records);
        self::$total_per_page = ceil(self::$total_records / self::$limit);
    }

    /**
     * Gets the total records in paging
     * 
     */
    public static function getTotalPagingRecords()
    {
        return self::$total_records;
    }

    /**
     * Sets the limit of records in the query
     * 
     * @param string $query SQL query
     */
    public static function setLimit($query)
    {
        $limit = self::$limit;
        $offset = abs((self::$page - 1) * $limit);

        $query = $query . ($limit == -1 ? null : ' LIMIT ' . $limit . ' OFFSET ' . $offset);

        self::setQuery($query);
    }

    /**
     * Sets the query
     * 
     * @param string $query SQL query
     */
    protected static function setQuery($query)
    {
        self::$query = $query;
    }

    /**
     * Gets the query 
     * 
     */
    public static function getQuery()
    {
        return self::$query;
    }

    /**
     * Sets the page link
     * 
     * @param string $link Page link
     */
    public static function setPageLink($link)
    {
        self::$link = $link;
    }

    /**
     * Sets a slug for the page link
     * 
     * @param string $slug Slug for the page link
     */
    public static function setSlug($slug)
    {
        self::$slug = $slug;
    }

    /**
     * Sets the current page navigation
     * 
     * @param integer $page Current page navigation
     */
    public static function setPage($page)
    {
        self::$page = $page;
    }

    /**
     * Displays the pagination
     * 
     * @param string $link Page link
     * @param string $slug Slug for the page link
     * @return null|string
     */
    public static function getPagination($link = null, $slug = null)
    {
        if (self::$paging == false || self::$page == 0) {
            return null;
        }

        $link = !is_null($link) ? $link : self::$link;
        $slug = !is_null($slug) ? $slug : self::$slug;
        $page = self::$page;
        $total_per_page = self::$total_per_page;
        $url = self::$page_nave . $link;

        $nave = '<div class="pagination">';

        if ($page != 1) {
            $nave.= '<a href="' . $url . '1' . $slug . '">FIRST</a>';
        } else {
            $nave.= '<a class="nolink">FIRST</a>';
        }

        if ($page != 1 && $total_per_page > 0) {
            $nave.= '<a href="' . $url . ($page - 1) . $slug . '">&#9668;</a>';
        } else {
            $nave.= '<a class="nolink">&#9668;</a>';
        }

        if ($total_per_page == 0) {
            $nave.= '<a class="selected">1</a>';
        } else {
            for ($i = 1; $i <= $total_per_page; $i++) {
                if ($page == $i + 3)
                    $nave.= '<a href="' . $url . $i . $slug . '">' . $i . '</a>';
                if ($page == $i + 2)
                    $nave.= '<a href="' . $url . $i . $slug . '">' . $i . '</a>';
                if ($page == $i + 1)
                    $nave.= '<a href="' . $url . $i . $slug . '">' . $i . '</a>';
                if ($page == $i)
                    $nave.= '<a class="selected">' . $i . '</a>';
                if ($page == $i - 1)
                    $nave.= '<a href="' . $url . $i . $slug . '">' . $i . '</a>';
                if ($page == $i - 2)
                    $nave.= '<a href="' . $url . $i . $slug . '">' . $i . '</a>';
                if ($page == $i - 3)
                    $nave.= '<a href="' . $url . $i . $slug . '">' . $i . '</a>';
            }
        }

        if ($page < $total_per_page) {
            $nave.= '<a href="' . $url . ($page + 1) . $slug . '">&#9658;</a>';
        } else {
            $nave.= '<a class="nolink">&#9658;</a>';
        }

        if ($page != $total_per_page && $total_per_page > 0) {
            $nave.= '<a href="' . $url . ($total_per_page) . $slug . '">LAST</a>';
        } else {
            $nave.= '<a class="nolink">LAST</a>';
        }

        $nave.= '</div>';

        return $nave;
    }

}