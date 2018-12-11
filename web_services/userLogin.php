<?php

require_once '../includes/DBOperations.php';
$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['email']) and isset($_POST['pin'])) {
        $db = new DBOperations();

        if ($db->userLogin($_POST['email'], $_POST['pin'])) {
            $user = $db->getUserByEmail($_POST['email']);
            $response['error'] = false;
            $response['message'] = "Welcome back, " . $user['firstname'];

            //Retrieve logged in user's data
            $response['user_id'] = $user['user_id'];
            $response['firstname'] = $user['firstname'];
            $response['lastname'] = $user['lastname'];
            $response['phone'] = $user['phone'];
            $response['email'] = $user['email'];
            $response['nationalID'] = $user['nationalID'];
            $response['pin'] = $user['pin'];
        } else {
            $response['error'] = true;
            $response['message'] = "Invalid username or pin";
        }

    } else {
        $response['error'] = true;
        $response['message'] = "Invalid Request";
    }
}

echo json_encode($response);
?>