<?php

/**
 * DemoRegister
 * 
 * @category Demo
 * 
 * */
class DemoRegister
{
    const FIRST_NAME = 'Name';
    const LAST_NAME = 'Last name';
    const MAIL = 'Mail';
    const TOTAL_USERS = 'Total registered users';
    const ADD_NEW_USER = 'Add new user';

    private static $message;
    private static $hasRecords = 0;

    /**
     * Main method
     * 
     * */
    public function init()
    {
        // Connection instance started and available
        PDO4You::getInstance();

        // Validates the form input submitted, before writing to database
        if ($_POST) {
            $firstName = filter_input(INPUT_POST, 'firstname', FILTER_SANITIZE_STRING);
            $lastName = filter_input(INPUT_POST, 'lastname', FILTER_SANITIZE_STRING);
            $mail = filter_input(INPUT_POST, 'mail', FILTER_VALIDATE_EMAIL);

            // Performing validation
            if (empty($firstName) || empty($lastName)) {
                $error = 'Enter your name and last name';
            } else if (empty($mail)) {
                $error = 'Enter a valid email address';
            }

            // Displays a message in case of errors
            if (isset($error)) {
                self::$message = '<i style="color: #f50;">ERROR: ' . $error . '</i><br />';
            } else {
                $sql = '
                    { query : [
                        {
                            table: "users" ,
                            values: { firstname: "' . $firstName . '", lastname: "' . $lastName . '", mail: "' . $mail . '"  }
                        }
                    ] }
					';

                // Performs the new record
                $result = PDO4You::insert($sql);

                self::$message = 'Register #' . $result[0] . ' added successfully!!<br />';
            }
        }

        // Capture records of all registered users
        self::$hasRecords = PDO4You::select('SELECT * FROM users ORDER BY id DESC');
    }

    /**
     * Displays a message
     * 
     * */
    public function getMessage()
    {
        return self::$message;
    }

    /**
     * Displays the total number of records
     * 
     * */
    public function getTotalRecords()
    {
        return is_array(self::$hasRecords) ? count(self::$hasRecords) : 0;
    }

    /**
     * Displays all records
     * 
     * */
    public function getRecords()
    {
        if (self::$hasRecords) {
            $html = null;

            foreach (self::$hasRecords as $row) {
                $html.= '<div>- #' . $row['id'] . ' ' . ucwords($row['firstname']) . ' ' . ucwords($row['lastname']) . ' [' . $row['mail'] . ']</div>';
            }

            return $html;
        }

        return '<div>No record at the time.</div>';
    }

}
