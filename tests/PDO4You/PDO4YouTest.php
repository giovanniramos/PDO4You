<?php

namespace PDO4You;

// Connection class imported
use PDO4You\PDO4You as test;

/**
 * Test class for PDO4You
 */
class PDO4YouTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        // Creates and maintains a connection instance with a database
        $this->createDatabase();

        // Creates tables in the database
        $this->createTables();
    }

    public function createDatabase()
    {
        // Connection instance started and available
        test::getInstance('test', 'sqlite::memory:');
    }

    public function createTables()
    {
        // Creating tables Users and Books
        test::exec('CREATE TABLE users (id INTEGER PRIMARY KEY, firstname TEXT, lastname TEXT);');
        test::exec('CREATE TABLE books (id INTEGER PRIMARY KEY, title TEXT, author TEXT, description TEXT);');
    }

    public function testMultipleInsertInJsonFormat()
    {
        // SQL Insert in JSON format
        $json = '
        insert: [
            { table: "users" , values: { firstname: "John", lastname: "Lennon" } } ,
            { table: "users" , values: { firstname: "Paul", lastname: "McCartney" } } ,
            { table: "books" , values: { title: "Lorem ipsum dolor sit amet, consectetur adipiscing elit.", author: "Giovanni Ramos" } }
        ]
        ';

        // Executes the SQL and stores the result
        $result = test::execute($json);

        $this->assertEquals(3, self::getNumRowsAffected($result), 'Test with Insert command');
    }

    public function testMultipleUpdateInJsonFormat()
    {
        // SQL Update in JSON format
        $json = '
        update: [
            { table: "books" , values: { author: "teste" } , where: { id: 1 } } ,
            { table: "users" , values: { lastname: "Doe" } , where: { id: 1 } } ,
            { table: "users" , values: { firstname: "Sparta", lastname: "" } , where: { id: 300 } }
        ]
        ';

        // Executes the SQL and stores the result
        $result = test::execute($json);

        $this->assertEquals(2, self::getNumRowsAffected($result), 'Test with Update command');
    }

    public function testMultipleDeleteInJsonFormat()
    {
        // SQL Delete in JSON format
        $json = '
        delete: [
            { table: "books" , where: { id: 200 } } ,
            { table: "users" , where: { id: 1 } } ,
            { table: "users" , where: { id: 300 } } 
        ]
        ';

        // Executes the SQL and stores the result
        $result = test::execute($json);

        $this->assertEquals(1, self::getNumRowsAffected($result), 'Test with Delete command');
    }

    public function testAllTypesSelects()
    {
        // SQL query
        $sql = 'SELECT * FROM users';

        // Executes the SQL and stores the result
        $result = test::select($sql);

        $this->assertEquals(1, self::getNumRows($result));

        // Executes the SQL and stores the result
        $result_num = test::selectNum($sql);

        $this->assertEquals(1, self::getNumRows($result_num));

        // Executes the SQL and stores the result
        $result_obj = test::selectObj($sql);

        $this->assertEquals(1, self::getNumRows($result_obj));

        // Executes the SQL and stores the result
        $result_all = test::selectAll($sql);

        $this->assertEquals(1, self::getNumRows($result_all));
    }

    public function testMultipleInsertInArrayFormat()
    {
        $array['query'] = array(
            array(
                'table' => 'users', 
                'values' => array('firstname' => "John", 'lastname' => "Lennon")
            ),
            array(
                'table' => 'users',
                'values' => array('firstname' => "Paul", 'lastname' => "McCartney")
            )
        );

        // Executes the SQL and stores the result
        $result = test::insert($array);

        $this->assertEquals(2, self::getNumRowsAffected($result), 'Test with Insert method');
    }

    private function getNumRows($result)
    {
        return count($result);
    }

    private function getNumRowsAffected($result)
    {
        return array_sum($result);
    }

}
