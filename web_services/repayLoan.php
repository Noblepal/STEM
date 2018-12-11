<?php

require_once '../includes/DBOperations.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['user_id']) and
        isset($_POST['paid_amount'])) {

        $db = new DBOperations();

        $result = $db->repayLoan(
            $_POST['user_id'],
            $_POST['paid_amount']
        );
        if ($result == 1) {
            $response['error'] = false;
            $response['message'] = "Loan Repayment successful";

        } elseif ($result == 2) {
            $response['error'] = true;
            $response['message'] = "SQL execute() ERROR";

        } elseif ($result == 3) {
            $response['error'] = true;
            $response['message'] = "Loan already fully paid";

        } elseif ($result == 0) {
            $response['error'] = true;
            $response['message'] = "You don't have an existing loan";
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