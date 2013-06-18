<?php

/**
 * DemoCRUD
 * 
 * @category Demo
 * 
 * */
class DemoCRUD
{

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
    private function sql()
    {
        // Retrieves the name of the current driver
        $driver = PDO4You::getDriver();

        switch ($driver) {
            case 'mysql':
            case 'pgsql':
            case 'cubrid':
            case 'sqlite': $sql = 'SELECT * FROM books LIMIT 2;';
                break;
            case 'mssql':
            case 'dblib':
            case 'sybase':
            case 'sqlsrv': $sql = 'SELECT TOP 2 * FROM books;';
                break;
            case 'oracle': $sql = 'SELECT * FROM (SELECT ROW_NUMBER() OVER AS LIMIT FROM books) WHERE LIMIT <= 2;';
                break;
            default: $sql = null;
        }

        return $sql;
    }

    /**
     * Default Select
     * Usage: PDO4You::select()
     * 
     * */
    public function select($instance = null)
    {
        // SQL query
        $sql = $this->sql();

        // Execute the SQL query in a "pre-defined instance" and store the result
        $result = PDO4You::select($sql, $instance);

        echo '<div class="code">&nbsp;<strong>Demo with PDO4You::select()</strong></div>';
        echo '<div class="code debug">PDO4You::select(' . $this->getSQL($sql) . ', ' . $this->getInstance() . ');' . $this->getResult($result) . '</div>';
    }

    /**
     * All Selects
     * Usage: PDO4You::select(), PDO4You::selectNum(), PDO4You::selectObj(), PDO4You::selectAll()
     * 
     * */
    public function allSelects()
    {
        // SQL query
        $sql = $this->sql();

        // Execute the SQL query in a instance and store the result
        $result_1 = PDO4You::select($sql);
        $result_2 = PDO4You::selectNum($sql);
        $result_3 = PDO4You::selectObj($sql);
        $result_4 = PDO4You::selectAll($sql);

        echo '<div class="code">&nbsp;<strong>Demo with PDO4You::select()</strong></div>';
        echo '<div class="code debug">PDO4You::select(' . $this->getSQL($sql) . ', ' . $this->getInstance() . ');' . $this->getResult($result_1) . '</div>';

        echo '<div class="code">&nbsp;<strong>Demo with PDO4You::selectNum()</strong></div>';
        echo '<div class="code debug">PDO4You::selectNum(' . $this->getSQL($sql) . ', ' . $this->getInstance() . ');' . $this->getResult($result_2) . '</div>';

        echo '<div class="code">&nbsp;<strong>Demo with PDO4You::selectObj()</strong></div>';
        echo '<div class="code debug">PDO4You::selectObj(' . $this->getSQL($sql) . ', ' . $this->getInstance() . ');' . $this->getResult($result_3) . '</div>';

        echo '<div class="code">&nbsp;<strong>Demo with PDO4You::selectAll()</strong></div>';
        echo '<div class="code debug">PDO4You::selectAll(' . $this->getSQL($sql) . ', ' . $this->getInstance() . ');' . $this->getResult($result_4) . '</div>';
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

        echo '<div class="code">&nbsp;<strong>Demo with PDO4You::insert()</strong></div>';
        echo '<div class="code debug">PDO4You::insert(' . $this->getSQL($json) . ', ' . $this->getInstance() . ');' . $this->getResult($result, true) . '</div>';
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

        echo '<div class="code">&nbsp;<strong>Demo with PDO4You::update()</strong></div>';
        echo '<div class="code debug">PDO4You::update(' . $this->getSQL($json) . ', ' . $this->getInstance() . ');' . $this->getResult($result) . '</div>';
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

        echo '<div class="code">&nbsp;<strong>Demo with PDO4You::delete()</strong></div>';
        echo '<div class="code debug">PDO4You::delete(' . $this->getSQL($json) . ', ' . $this->getInstance() . ');' . $this->getResult($result) . '</div>';
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

        echo '<div class="code">&nbsp;<strong>Demo with PDO4You::update()</strong></div>';
        echo '<div class="code debug">PDO4You::update(' . $this->getSQL($json) . ', ' . $this->getInstance() . ');' . $this->getResult($result) . '</div>';
    }

    /**
     * Format the Query
     * 
     */
    private function getSQL($sql)
    {
        return '"<strong style="color:green;">' . htmlspecialchars($sql) . '</strong>"';
    }

    /**
     * Format the Instance of Connection
     * 
     */
    private function getInstance()
    {
        return '"<strong style="color:red;">' . PDO4You::getConnection() . '</strong>"';
    }

    /**
     * Format the Result
     * 
     */
    private function getResult($result, $show_lastid = false)
    {
        $s = '<br /><br />- The code above will output: <pre style="color:blue;">' . print_r($this->sanitize($result), true) . '</pre>';
        $s.= 'Total records affected: <strong style="color:red;">' . PDO4You::rowCount() . '</strong>';
        $s.= ($show_lastid) ? '&nbsp;&nbsp;&nbsp;&nbsp;Id of the last iteration: <strong style="color:red;">' . PDO4You::lastId() . '</strong>' : null;

        return $s;
    }

    /**
     * Sanitizes the result
     * 
     */
    function sanitize($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->sanitize($value);
            }
        } elseif (is_object($data)) {
            foreach ($data as $key => $value) {
                $data->$key = $this->sanitize($value);
            }
        } elseif (is_string($data)) {
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        }

        return $data;
    }

    /**
     * Random name generator
     * 
     * */
    private function genFakeName()
    {
        $v = array("a", "e", "i", "o", "u");
        $c = array("b", "c", "d", "f", "g", "h", "j", "l", "m", "n", "p", "q", "r", "s", "t", "v", "x", "z");

        return ucfirst($c[array_rand($c, 1)] . $v[array_rand($v, 1)] . $c[array_rand($c, 1)] . $v[array_rand($v, 1)] . $v[array_rand($v, 1)]);
    }

}