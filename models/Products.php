<?php
/**
 *
 * @author Guilherme Mangabeira Gregio<guilherme@gregio.net>
 */
class Products {
    private $conn;
 
    function __construct() {
		require_once dirname(__FILE__) . '/' . '../utils/DbConnect.php';
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

	public function getAll() {
		$stmt = $this->conn->prepare("SELECT p.id, p.name, p.shortDescription, p.longDescription, p.price, p.data from PRODUCTS t");
		$stmt->execute();
		$products = $stmt->get_result();
		$stmt->close();

		return $products;
	}

	public function remove() {
	}

	public function update() {
	}

	public function add() {
	}
}
