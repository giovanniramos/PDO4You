<?php

/**
 * User class
 * 
 * @class User
 * 
 * */


class User 
{
	const NAME = 'Name';
	const LAST_NAME = 'Last name';
	const MAIL = 'Mail';
	const ADD_USER = 'Add new user';
	const TOTAL_USERS = 'Total registered users';
	
	private static $message = null;
	private static $has_records = 0;
	
	
	/**
	 * Main method that initiates the connection
	 * */
	public function init()
	{
		// Connection instance started and available
		PDO4You::getInstance();
		
		// Validates the form input submitted, before writing to database
		if($_POST){
			
			if(isset($_POST["firstname"]))
				$firstName = filter_input(INPUT_POST, 'firstname', FILTER_SANITIZE_STRING);
			
			if(isset($_POST["lastname"]))
				$lastName = filter_input(INPUT_POST, 'lastname', FILTER_SANITIZE_STRING);
			
			if(isset($_POST["mail"]))
				$mail = filter_input(INPUT_POST, 'mail', FILTER_VALIDATE_EMAIL);
			
			
			if(isset($firstName) && isset($lastName) && isset($mail)){
			
				// Performing validation
				if(empty($firstName) || empty($lastName)){
					$error = 'Enter your name and last name';
				} else if(empty($mail)) {
					$error = 'Enter your mail'; 
				}
				
				// Displays a message if there are errors, otherwise continue with the registration process user
				if(isset($error)){
					self::$message = '<i>'.$error.'</i><br />';
				} else {
					$sql = '
					{ query : [
						{
							table: "users" ,
							values: { firstname: "'.$firstName.'", lastname: "'.$lastName.'", mail: "'.$mail.'"  }
						}
					] }
					';
					
					// Loading the connection to the database
					$result = PDO4You::insert($sql);
					self::$message = "Register #{$result[0]} added successfully!!<br />";
				}
			}
		}
		
		// Capture records of all registered users
		self::$has_records = PDO4You::select("SELECT * FROM users");
	}
	
	
	/**
	 * Method that displays all registered users
	 * */
	public function showUsers()
	{
		if(self::$has_records):
			$html = null;
			foreach(self::$has_records as $dba):
				$firstname = ucwords($dba["firstname"]);
				$lastname = ucwords($dba["lastname"]);
				$mail = $dba["mail"];
				
				$html.= '<div>- '.$firstname.' '.$lastname.' ['.$mail.']</div>';
			endforeach;
			return $html;
		endif;
		
		return '<div>No registered users at the moment.</div>';
	}
	
	
	/**
	 * Method that returns the total number of registered users
	 * */
	public function getTotalUsers()
	{
		return (is_array(self::$has_records)) ? count(self::$has_records) : 0;
	}
	
	
	/**
	 * Returns a message
	 * */
	public function getMessage()
	{
		return self::$message."<br />";
	}
	
}

?>