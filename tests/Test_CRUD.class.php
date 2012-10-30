<?php

/**
 * Test_CRUD
 * 
 * @category Tests
 * 
 * */
class Test_CRUD
{
    protected $sql;

    /**
     * Main method
     * 
     * */
    public function init()
    {
        // Connection instance started and available
        PDO4You::getInstance();

        // SQL query
        $this->sql = 'SELECT * FROM books LIMIT 2';
    }

    /**
     * Default Select
     * Usage: PDO4You::select()
     * 
     * */
    public function select($instance = null)
    {
        // Execute SQL query and Store the result
        $rs = PDO4You::select($this->sql, $instance);

        echo '<code>&nbsp;<strong>Test with PDO4You::select()</strong></code>';
        echo '<code class="debug">PDO4You::select(' . $this->formatSql() . ', ' . $this->formatDB() . ');' . $this->formatRS($rs, false) . '</code>';
    }

    /**
     * All Selects with default instance
     * Usage: PDO4You::select(), PDO4You::selectNum(), PDO4You::selectObj(), PDO4You::selectAll()
     * 
     * */
    public function allSelects()
    {
        // Execute SQL query and Store the result
        $rs = PDO4You::select($this->sql);
        $rs_num = PDO4You::selectNum($this->sql);
        $rs_obj = PDO4You::selectObj($this->sql);
        $rs_all = PDO4You::selectAll($this->sql);

        echo '<code>&nbsp;<strong>Test with PDO4You::select()</strong></code>';
        echo '<code class="debug">PDO4You::select(' . $this->formatSql() . ', ' . $this->formatDB() . ');' . $this->formatRS($rs, false) . '</code>';

        echo '<code>&nbsp;<strong>Test with PDO4You::selectNum()</strong></code>';
        echo '<code class="debug">PDO4You::selectNum(' . $this->formatSql() . ', ' . $this->formatDB() . ');' . $this->formatRS($rs_num, false) . '</code>';

        echo '<code>&nbsp;<strong>Test with PDO4You::selectObj()</strong></code>';
        echo '<code class="debug">PDO4You::selectObj(' . $this->formatSql() . ', ' . $this->formatDB() . ');' . $this->formatRS($rs_obj, false) . '</code>';

        echo '<code>&nbsp;<strong>Test with PDO4You::selectAll()</strong></code>';
        echo '<code class="debug">PDO4You::selectAll(' . $this->formatSql() . ', ' . $this->formatDB() . ');' . $this->formatRS($rs_all, false) . '</code>';
    }

    /**
     * Multiple Insert
     * Usage: PDO4You::insert()
     * 
     * */
    public function multipleInsert()
    {
        $sql = '
        { query : [
            {
                table: "users" ,
                values: { firstname: "' . $this->genFakeName() . '", lastname: "' . $this->genFakeName() . '" }
            },{
                table: "users" ,
                values: { firstname: "' . $this->genFakeName() . '", lastname: "' . $this->genFakeName() . '" }
            }
        ] }
		';

        // Store the result
        $rs = PDO4You::insert($sql);

        echo '<code>&nbsp;<strong>Test with PDO4You::insert()</strong></code>';
        echo '<code class="debug">PDO4You::insert(' . $this->formatSql($sql) . ', ' . $this->formatDB() . ');' . $this->formatRS($rs, true) . '</code>';
    }

    /**
     * Multiple Update
     * Usage: PDO4You::update()
     * 
     * */
    public function multipleUpdate()
    {
        $sql = '
        { query : [
            {
                table: "users" ,
                values: { mail: "' . strtolower($this->genFakeName()) . '@gmail.com" } ,
                where: { id: 2 }
            },{
                table: "users" ,
                values: { mail: "' . strtolower($this->genFakeName()) . '@gmail.com" } ,
                where: { id: 12 }
            },{
                table: "users" ,
                values: { mail: "' . strtolower($this->genFakeName()) . '@gmail.com" } ,
                where: { id: 30 }
            },{
                table: "users" ,
                values: { mail: "' . strtolower($this->genFakeName()) . '@gmail.com" } ,
                where: { id: 1 }
            }
        ] }
		';

        // Store the result
        $rs = PDO4You::update($sql);

        echo '<code>&nbsp;<strong>Test with PDO4You::update()</strong></code>';
        echo '<code class="debug">PDO4You::update(' . $this->formatSql($sql) . ', ' . $this->formatDB() . ');' . $this->formatRS($rs, false) . '</code>';
    }

    /**
     * Multiple Delete
     * Usage: PDO4You::delete()
     * 
     * */
    public function multipleDelete()
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

        // Store the result
        $rs = PDO4You::delete($sql);

        echo '<code>&nbsp;<strong>Test with PDO4You::delete()</strong></code>';
        echo '<code class="debug">PDO4You::delete(' . $this->formatSql($sql) . ', ' . $this->formatDB() . ');' . $this->formatRS($rs, false) . '</code>';
    }

    /**
     * Update Where
     * Usage: PDO4You::update()
     * 
     * */
    public function updateWhere($s, $i)
    {
        $sql = '
        { query : [
            {
                table: "books" ,
                values: { description: "' . $s . '" } ,
                where: { id: ' . $i . ' }
            }
        ] }
		';

        // Store the result
        $rs = PDO4You::update($sql);

        echo '<code>&nbsp;<strong>Test with PDO4You::update()</strong></code>';
        echo '<code class="debug">PDO4You::update(' . $this->formatSql($sql) . ', ' . $this->formatDB() . ');' . $this->formatRS($rs, false) . '</code>';
    }

    /**
     * Format the SQL
     * 
     */
    private function formatSql($sql = null)
    {
        $s = '"<strong style="color:green;">' . (!is_null($sql) ? trim($sql) : $this->sql) . '</strong>"';

        return $s;
    }

    /**
     * Format the Database
     * 
     */
    private function formatDB()
    {
        $s = '"<strong style="color:red;">' . PDO4You::getDatabase() . '</strong>"';

        return $s;
    }

    /**
     * Format the Result
     * 
     */
    private function formatRS($rs, $show_lastid = false)
    {
        $s = '<br /><br />- The code above will output: <pre style="color:blue;">' . print_r($rs, true) . '</pre>';
        $s.= 'Total records affected: <strong style="color:red;">' . PDO4You::rowCount() . '</strong>';
        $s.= ($show_lastid) ? '&nbsp;&nbsp;&nbsp;&nbsp;Id of the last iteration: <strong style="color:red;">' . PDO4You::lastId() . '</strong>' : null;

        return $s;
    }

    /**
     * Random name generator
     * 
     * */
    public function genFakeName()
    {
        $v = array("a", "e", "i", "o", "u");
        $c = array("b", "c", "d", "f", "g", "h", "j", "l", "m", "n", "p", "q", "r", "s", "t", "v", "x", "z");

        return ucfirst($c[array_rand($c, 1)] . $v[array_rand($v, 1)] . $c[array_rand($c, 1)] . $v[array_rand($v, 1)] . $v[array_rand($v, 1)]);
    }

}