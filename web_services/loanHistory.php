<?php

require_once '../includes/DBOperations.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['user_id'])) {
        $db = new DBOperations();

        $result = $db->loanHistory(
            $_POST['user_id']
        );

        if ($result) {
            $decoded_json = json_decode($result);
            if (count($decoded_json) > 0) {
                $response['error'] = "false";
                $response['message'] = "Data retrieved successfully";
                echo $result;
            } else {
                $response['error'] = "true";
                $response['message'] = "No data found";
                echo json_encode($response);
            }
        }
    } else {
        $response['error'] = "true";
        $response['message'] = "Some fields are missing";
        echo json_encode($response);
    }
} else {
    $response['error'] = "true";
    $response['message'] = "Invalid request";
    echo json_encode($response);
}
?>