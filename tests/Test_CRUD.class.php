<?php

/**
 * Test_CRUD
 * 
 * @category Tests
 * 
 * */
class Test_CRUD
{
    // Stores the SQL query
    private $sql;

    /**
     * Main method
     * 
     * */
    public function init()
    {
        // Connection instance started and available
        PDO4You::getInstance();
    }

    /**
     * Returns the SQL query to the appropriate driver
     * 
     * */
    public function getSQL()
    {
        // SQL query
        $this->sql = null;

        // Retrieves the name of the current driver
        $driver = PDO4You::getDriver();
        switch ($driver):
            case 'mysql':
            case 'pgsql': 
            case 'cubrid': $this->sql = 'SELECT * FROM books LIMIT 2;';
                break;
            case 'mssql':
            case 'dblib': 
            case 'sybase': 
            case 'sqlsrv': $this->sql = 'SELECT TOP 2 * FROM books;';
                break;
            case 'oracle': $this->sql = 'SELECT * FROM (SELECT ROW_NUMBER() OVER AS LIMIT FROM books) WHERE LIMIT <= 2;';
                break;
        endswitch;

        return $this->sql;
    }

    /**
     * Default Select
     * Usage: PDO4You::select()
     * 
     * */
    public function select($instance = null)
    {
        // SQL query
        $sql = $this->getSQL();

        // Execute the SQL query in a "pre-defined instance" and store the result
        $result = PDO4You::select($sql, $instance);

        echo '<code>&nbsp;<strong>Test with PDO4You::select()</strong></code>';
        echo '<code class="debug">PDO4You::select(' . $this->formatQuery($sql) . ', ' . $this->formatInstance() . ');' . $this->formatResult($result, false) . '</code>';
    }

    /**
     * All Selects
     * Usage: PDO4You::select(), PDO4You::selectNum(), PDO4You::selectObj(), PDO4You::selectAll()
     * 
     * */
    public function allSelects()
    {
        // SQL query
        $sql = $this->getSQL();

        // Execute the SQL query in a "default instance" and store the result
        $result_1 = PDO4You::select($sql);
        $result_2 = PDO4You::selectNum($sql);
        $result_3 = PDO4You::selectObj($sql);
        $result_4 = PDO4You::selectAll($sql);

        echo '<code>&nbsp;<strong>Test with PDO4You::select()</strong></code>';
        echo '<code class="debug">PDO4You::select(' . $this->formatQuery($sql) . ', ' . $this->formatInstance() . ');' . $this->formatResult($result_1, false) . '</code>';

        echo '<code>&nbsp;<strong>Test with PDO4You::selectNum()</strong></code>';
        echo '<code class="debug">PDO4You::selectNum(' . $this->formatQuery($sql) . ', ' . $this->formatInstance() . ');' . $this->formatResult($result_2, false) . '</code>';

        echo '<code>&nbsp;<strong>Test with PDO4You::selectObj()</strong></code>';
        echo '<code class="debug">PDO4You::selectObj(' . $this->formatQuery($sql) . ', ' . $this->formatInstance() . ');' . $this->formatResult($result_3, false) . '</code>';

        echo '<code>&nbsp;<strong>Test with PDO4You::selectAll()</strong></code>';
        echo '<code class="debug">PDO4You::selectAll(' . $this->formatQuery($sql) . ', ' . $this->formatInstance() . ');' . $this->formatResult($result_4, false) . '</code>';
    }

    /**
     * Multiple Insert
     * Usage: PDO4You::insert()
     * 
     * */
    public function multipleInsert()
    {
        $json = '
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
        $result = PDO4You::insert($json);

        echo '<code>&nbsp;<strong>Test with PDO4You::insert()</strong></code>';
        echo '<code class="debug">PDO4You::insert(' . $this->formatQuery($json) . ', ' . $this->formatInstance() . ');' . $this->formatResult($result, true) . '</code>';
    }

    /**
     * Multiple Update
     * Usage: PDO4You::update()
     * 
     * */
    public function multipleUpdate()
    {
        $json = '
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
        $result = PDO4You::update($json);

        echo '<code>&nbsp;<strong>Test with PDO4You::update()</strong></code>';
        echo '<code class="debug">PDO4You::update(' . $this->formatQuery($json) . ', ' . $this->formatInstance() . ');' . $this->formatResult($result, false) . '</code>';
    }

    /**
     * Multiple Delete
     * Usage: PDO4You::delete()
     * 
     * */
    public function multipleDelete()
    {
        $json = '
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
        $result = PDO4You::delete($json);

        echo '<code>&nbsp;<strong>Test with PDO4You::delete()</strong></code>';
        echo '<code class="debug">PDO4You::delete(' . $this->formatQuery($json) . ', ' . $this->formatInstance() . ');' . $this->formatResult($result, false) . '</code>';
    }

    /**
     * Update Where
     * Usage: PDO4You::update()
     * 
     * */
    public function updateWhere($s, $i)
    {
        $json = '
        { query : [
            {
                table: "books" ,
                values: { description: "' . $s . '" } ,
                where: { id: ' . $i . ' }
            }
        ] }
		';

        // Store the result
        $result = PDO4You::update($json);

        echo '<code>&nbsp;<strong>Test with PDO4You::update()</strong></code>';
        echo '<code class="debug">PDO4You::update(' . $this->formatQuery($json) . ', ' . $this->formatInstance() . ');' . $this->formatResult($result, false) . '</code>';
    }

    /**
     * Format the Query
     * 
     */
    private function formatQuery($sql)
    {
        $s = '"<strong style="color:green;">' . $sql . '</strong>"';

        return $s;
    }

    /**
     * Format the Instance of Connection
     * 
     */
    private function formatInstance()
    {
        $s = '"<strong style="color:red;">' . PDO4You::getConnection() . '</strong>"';

        return $s;
    }

    /**
     * Format the Result
     * 
     */
    private function formatResult($result, $show_lastid = false)
    {
        $s = '<br /><br />- The code above will output: <pre style="color:blue;">' . print_r($result, true) . '</pre>';
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