<?php

require_once '../includes/DBOperations.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['user_id']) and
        isset($_POST['loan_amount']) and
        isset($_POST['lend_date']) and
        isset($_POST['expected_date'])) {

        $db = new DBOperations();

        $result = $db->applyLoan(
            $_POST['user_id'],
            $_POST['loan_amount'],
            $_POST['lend_date'],
            $_POST['expected_date']
        );

        if ($result == 1) {
            $response['error'] = false;
            $response['message'] = "Loan application successful";

        } elseif ($result == 2) {
            $response['error'] = true;
            $response['message'] = "An error occurred, please try again";

        } elseif ($result == 3) {
            $response['error'] = true;
            $response['message'] = "We have no records of your existence, please sign up";

        } elseif ($result == 0) {
            $response['error'] = true;
            $response['message'] = "Please clear your existing loan before applying for another one";
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