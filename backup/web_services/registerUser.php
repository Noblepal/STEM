<?php

require_once '../includes/DBOperations.php';
$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['firstname']) and
        isset($_POST['lastname']) and
        isset($_POST['phone']) and
        isset($_POST['email']) and
        isset($_POST['nationalID']) and
        isset($_POST['pin'])) {

        $db = new DBOperations();

        $result = $db->createUser(
            $_POST['firstname'],
            $_POST['lastname'],
            $_POST['phone'],
            $_POST['email'],
            $_POST['nationalID'],
            $_POST['pin']
        );

        if ($result == 1) {
            $response['error'] = false;
            $response['message'] = "Registered successfully";

            $user = $db->getUserByEmail($_POST['email']);
            $response['user_id'] = $user['user_id'];
            $response['firstname'] = $user['firstname'];
            $response['lastname'] = $user['lastname'];
            $response['phone'] = $user['phone'];
            $response['email'] = $user['email'];
            $response['nationalID'] = $user['nationalID'];
            $response['pin'] = $user['pin'];

        } elseif ($result == 2) {
            $response['error'] = true;
            $response['message'] = "An error occurred, please try again";
        } elseif ($result == 0) {
            $response['error'] = true;
            $response['message'] = "It seems you are already registered, please log in";

        }

    } else {
        $response['error'] = true;
        $response['message'] = "Some required fields are missing";
    }
} else {
    $response['error'] = true;
    $response['message'] = "Invalid Request";

}

echo json_encode($response);

?>