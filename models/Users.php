<?php
/**
 *
 * @author Guilherme Mangabeira Gregio<guilherme@gregio.net>
 */
class Users {
    private $conn;
 
    function __construct() {
        require_once dirname(__FILE__) . '/' . '../utils/DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

	public function createUser($username, $password) {
        require_once dirname(__FILE__) . '/' . '../utils/PassHash.php';
        $response = array();
 
        // First check if user already existed in db
        if (!$this->isUserExists($username)) {
            // Generating password hash
            $password_hash = PassHash::hash($password);
 
            // Generating API key
            $api_key = $this->generateApiKey();
 
            // insert query
            $stmt = $this->conn->prepare("INSERT INTO users(username, password, api_key) values(?, ?, ?)");
            $stmt->bind_param("sss", $username, $password_hash, $api_key);
 
            $result = $stmt->execute();
 
            $stmt->close();
 
            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {
                // Failed to create user
                return USER_CREATE_FAILED;
            }
        } else {
            // User with same email already existed in the db
            return USER_ALREADY_EXISTED;
        }
 
        return $response;
    }

	private function isUserExists($username) {
        $stmt = $this->conn->prepare("SELECT id from users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

	private function generateApiKey() {
        return md5(uniqid(rand(), true));
    }

	public function checkLogin($username, $password) {
        require_once dirname(__FILE__) . '/' . '../utils/PassHash.php';
		$stmt = $this->conn->prepare("SELECT password FROM USERS WHERE username = ?");
 
        $stmt->bind_param("s", $username);
 
        $stmt->execute();
 
        $stmt->bind_result($password_hash);
 
        $stmt->store_result();
 
        if ($stmt->num_rows > 0) {
            // Found user with the email
            // Now verify the password
 
            $stmt->fetch();
 
            $stmt->close();
 
            if (PassHash::check_password($password_hash, $password)) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            $stmt->close();
 
            return FALSE;
        }
	}
}
