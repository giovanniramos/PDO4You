<?php

namespace PDO4You;

// Connection class imported
use PDO4You\PDO4You;

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
        PDO4You::getInstance('test', 'sqlite::memory:');
    }

    public function createTables()
    {
        // Creating tables Users and Books
        PDO4You::exec('CREATE TABLE users (id INTEGER PRIMARY KEY, firstname TEXT, lastname TEXT);');
        PDO4You::exec('CREATE TABLE books (id INTEGER PRIMARY KEY, title TEXT, author TEXT, description TEXT);');
    }

    public function testMultipleInsert()
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
        $result = PDO4You::execute($json);

        $this->assertEquals(3, self::getNumRowsAffected($result), 'Test with Insert command');
    }

    public function testMultipleUpdate()
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
        $result = PDO4You::execute($json);

        $this->assertEquals(2, self::getNumRowsAffected($result), 'Test with Update command');
    }

    public function testMultipleDelete()
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
        $result = PDO4You::execute($json);

        $this->assertEquals(1, self::getNumRowsAffected($result), 'Test with Delete command');
    }

    public function testAllSelects()
    {
        // SQL query
        $sql = 'SELECT * FROM users';

        // Executes the SQL and stores the result
        $result = PDO4You::select($sql);

        $this->assertEquals(1, self::getNumRows($result));

        // Executes the SQL and stores the result
        $result_num = PDO4You::selectNum($sql);

        $this->assertEquals(1, self::getNumRows($result_num));

        // Executes the SQL and stores the result
        $result_obj = PDO4You::selectObj($sql);

        $this->assertEquals(1, self::getNumRows($result_obj));

        // Executes the SQL and stores the result
        $result_all = PDO4You::selectAll($sql);

        $this->assertEquals(1, self::getNumRows($result_all));
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
