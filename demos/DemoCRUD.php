<?php

// Importing classes
use PDO4You\PDO4You;

/**
 * DemoCRUD
 * 
 * */
class DemoCRUD
{

    /**
     * Checks and returns the SQL query to the appropriate driver
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
     * 
     * */
    public function select($instance = null)
    {
        // SQL query
        $sql = self::sql();

        // Execute the SQL query in a "pre-defined instance" and store the result
        $result = PDO4You::select($sql, $instance);

        echo '<div class="code title">Demo with the method PDO4You::select()</div>';
        echo '<div class="code debug">PDO4You::select(' . $this->getQuery($sql) . '); ' . $this->getResult($result) . '</div>';
    }

    /**
     * All Selects
     * 
     * */
    public function allSelects()
    {
        // SQL query
        $sql = self::sql();

        // Executes the SQL and stores the result
        $result = PDO4You::select($sql);
        $result_num = PDO4You::selectNum($sql);
        $result_obj = PDO4You::selectObj($sql);
        $result_all = PDO4You::selectAll($sql);

        echo '<div class="code title">Demo with the method PDO4You::select()</div>';
        echo '<div class="code debug">PDO4You::select(' . $this->getQuery($sql) . '); ' . $this->getResult($result) . '</div>';

        echo '<div class="code title">Demo with the method PDO4You::selectNum()</div>';
        echo '<div class="code debug">PDO4You::selectNum(' . $this->getQuery($sql) . '); ' . $this->getResult($result_num) . '</div>';

        echo '<div class="code title">Demo with the method PDO4You::selectObj()</div>';
        echo '<div class="code debug">PDO4You::selectObj(' . $this->getQuery($sql) . '); ' . $this->getResult($result_obj) . '</div>';

        echo '<div class="code title">Demo with the method PDO4You::selectAll()</div>';
        echo '<div class="code debug">PDO4You::selectAll(' . $this->getQuery($sql) . '); ' . $this->getResult($result_all) . '</div>';
    }

    /**
     * Multiple Insert
     * 
     * */
    public function multipleInsert()
    {
        // SQL Insert in JSON format
        $json = '
        insert: [
            {
                table: "users" ,
                values: { firstname: "' . $this->fakeName(true) . '", lastname: "' . $this->fakeName(true) . '" }
            },{
                table: "users" ,
                values: { firstname: "' . $this->fakeName(true) . '", lastname: "' . $this->fakeName(true) . '" }
            } 
        ]
        ';

        // Executes the SQL and stores the result
        $result = PDO4You::execute($json);

        echo '<div class="code title">Demo with Insert command</div>';
        echo '<div class="code debug">PDO4You::execute(' . $this->getQuery($json) . '); ' . $this->getResult($result, true) . '</div>';
    }

    /**
     * Multiple Update
     * 
     * */
    public function multipleUpdate()
    {
        // SQL Update in JSON format
        $json = '
        update: [
            {
                table: "users" ,
                values: { mail: "' . $this->fakeName() . '@gmail.com" } ,
                where: { id: 2 }
            },{
                table: "users" ,
                values: { mail: "' . $this->fakeName() . '@gmail.com" } ,
                where: { id: 12 }
            },{
                table: "users" ,
                values: { mail: "' . $this->fakeName() . '@gmail.com" } ,
                where: { id: 30 }
            },{
                table: "users" ,
                values: { mail: "' . $this->fakeName() . '@gmail.com" } ,
                where: { id: 1 }
            } 
        ]
        ';

        // Executes the SQL and stores the result
        $result = PDO4You::execute($json);

        echo '<div class="code title">Demo with Update command</div>';
        echo '<div class="code debug">PDO4You::execute(' . $this->getQuery($json) . '); ' . $this->getResult($result) . '</div>';
    }

    /**
     * Multiple Delete
     * 
     * */
    public function multipleDelete()
    {
        // SQL Delete in JSON format
        $json = '
        delete: [
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
        ]
        ';

        // Executes the SQL and stores the result
        $result = PDO4You::execute($json);

        echo '<div class="code title">Demo with Delete command</div>';
        echo '<div class="code debug">PDO4You::execute(' . $this->getQuery($json) . '); ' . $this->getResult($result) . '</div>';
    }

    /**
     * Update Where
     * 
     * */
    public function updateWhere($desc, $id)
    {
        // SQL update in JSON format
        $json = ' query: [ { table: "books" , values: { description: "' . $desc . '" } , where: { id: ' . $id . ' } } ] ';

        // Executes the SQL and stores the result
        $result = PDO4You::update($json);

        echo '<div class="code title">Demo with the old method PDO4You::update()</div>';
        echo '<div class="code debug">PDO4You::update(' . $this->getQuery($json) . '); ' . $this->getResult($result) . '</div>';
    }

    /**
     * Format the Query
     * 
     */
    private function getQuery($sql)
    {
        $sql = '<strong style="color:green;">' . htmlspecialchars($sql) . '</strong>';
        $conn = '<strong style="color:red;">' . PDO4You::getConnection() . '</strong>';

        return '"' . $sql . '", "' . $conn . '"';
    }

    /**
     * Format the Result
     * 
     */
    private function getResult($result, $show_lastid = false)
    {
        $result = '<br /><br /> - The code above will output: <pre style="color:blue;">' . print_r($this->sanitize($result), true) . '</pre>';
        $result.= 'Total records affected: <strong style="color:red;">' . PDO4You::rowCount() . '</strong>';
        $result.= ($show_lastid) ? '&nbsp;&nbsp; Id of the last iteration: <strong style="color:red;">' . PDO4You::lastId() . '</strong>' : null;

        return $result;
    }

    /**
     * Sanitizes the result
     * 
     */
    private function sanitize($data)
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
    private function fakeName($ucfirst = false)
    {
        $v = array("a", "e", "i", "o", "u");
        $c = array("b", "c", "d", "f", "g", "h", "j", "l", "m", "n", "p", "q", "r", "s", "t", "v", "x", "z");

        $fakename = $c[array_rand($c, 1)] . $v[array_rand($v, 1)] . $c[array_rand($c, 1)] . $v[array_rand($v, 1)] . $v[array_rand($v, 1)];

        return ($ucfirst) ? ucfirst($fakename) : $fakename;
    }

}