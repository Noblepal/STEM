<?php

require_once '../includes/DBOperations.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['user_id'])) {
        $db = new DBOperations();
        $db->calculatePoints($_POST['user_id']);
    }

}



?>