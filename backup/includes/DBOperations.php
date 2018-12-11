<?php

class DBOperations
{
    private $con;

    function __construct()
    {
        require_once dirname(__FILE__) . '/DBConnect.php';
        $db = new DBConnect();

        $this->con = $db->connect();

    }

    public function createUser($fname, $lname, $phone, $email, $nationalID, $pin)
    {
        if ($this->isUserExist($phone, $email, $nationalID)) {
            return 0;
        } else {

            $pin = md5($pin);
            $stmt = $this->con->prepare("INSERT INTO users (user_id, firstname, lastname, phone, email, nationalID, pin) VALUES (NULL, ?, ?, ?, ?, ?, ?);");
            $stmt->bind_param('ssssss', $fname, $lname, $phone, $email, $nationalID, $pin);

            if ($stmt->execute()) {
                return 1;
            } else {
                return 2;
            }
        }

        $stmt->close();
    }

    public function userLogin($email, $pin)
    {
        $pin = md5($pin);
        $stmt = $this->con->prepare("SELECT user_id FROM users WHERE email = ? AND pin = ?");
        $stmt->bind_param("ss", $email, $pin);
        $stmt->execute();
        $stmt->store_result();

        return $stmt->num_rows > 0;
    }

    public function getUserByEmail($email)
    {
        $stmt = $this->con->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    private function isUserExist($phone, $email, $nationalID)
    {
        //TODO: Changed ID to user_id
        $stmt = $this->con->prepare("SELECT user_id FROM users WHERE phone = ? OR email = ? OR nationalID = ?");
        $stmt->bind_param("sss", $phone, $email, $nationalID);
        $stmt->execute();
        $stmt->store_result();

        return $stmt->num_rows > 0;
    }

    public function applyLoan($user_id, $loan_amount, $lend_date, $expected_date)
    {
        //Check if user already has existing loan
        if ($this->isUserLoanActive($user_id)) {
            return 0;
        } else {
            if ($this->checkUserExistence($user_id)) {
                return 3;
            } else {
                $status = "Active";
                //$lend_date = now();
                $stmt = $this->con->prepare("INSERT INTO user_loans (loan_id, user_id, loan_amount, lend_date, expected_date, balance, status) VALUES (NULL, ?,?,?,?,?,?)");
                $stmt->bind_param("ssssss", $user_id, $loan_amount, $lend_date, $expected_date, $loan_amount, $status);
                $stmt->store_result();
                if ($stmt->execute()) {
                    return 1;
                } else {
                    return 2;
                }
            }
        }

        $stmt->close();
    }

    private function isUserLoanActive($user_id)
    {
        $stmt = $this->con->prepare("SELECT * FROM user_loans WHERE user_id = ? and status = 'Active'");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $stmt->store_result();
        
        return $stmt->num_rows > 0;
        $stmt->close();

    }

    private function checkUserExistence($user_id)
    {
        $stmt = $this->con->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows <= 0) {
            return true;
        } else {
            return false;
        }

    }

    public function repayLoan($user_id, $paid_amount)
    {
        $status = "Cleared";
        //Check if user loan status is cleared
        if ($this->checkUserLoanStatus($user_id) == 'Cleared') {
            return 0;
        } else {
            //Retrieve initial loan amount and balance first
            $stmt = $this->con->prepare("SELECT loan_amount, balance FROM user_loans WHERE user_id = ? and status='Active'");
            $stmt->bind_param("s", $user_id);
            $stmt->execute();
            $stmt->bind_result($loan_amount, $balance);
            $stmt->fetch();
            $stmt->close();

            //Check whether loan_amount > balance and balance > 0
            if ($loan_amount >= $balance and $balance > 0) {
                
                //Update user loan details
                $stmt = $this->con->prepare("UPDATE user_loans SET paid_amount = paid_amount + ?, paid_weekly = now() WHERE user_id = ? and status = 'Active'");
                $stmt->bind_param("ds", $paid_amount, $user_id);
                
                if ($stmt->execute()) {
                    $stmt->close();

                    //Update balance
                    $stmt = $this->con->prepare("UPDATE user_loans SET balance = loan_amount - paid_amount WHERE user_id = ? and status = 'Active'");
                    $stmt->bind_param("s", $user_id);
                    $stmt->execute();
                    $stmt->close();

                    //Check whether balance is = 0
                    if ($this->isUserHasBalance($user_id) == false) {
                        $stmt = $this->con->prepare("UPDATE user_loans SET status = ?, paid_full_date = now() WHERE user_id = ? and status = 'Active'");
                        $stmt->bind_param("ss", $status, $user_id);
                        $stmt->execute();
                        $stmt->close();
                    }
                    return 1;
                } else {
                    return 2;
                }
            } else {
                $stmt = $this->con->prepare("UPDATE user_loans SET status = ?, paid_full_date = now() WHERE user_id = ? and status = 'Active'");
                $stmt->bind_param("ss", $status, $user_id);
                $stmt->execute();
                return 0;
            }
        }

    }

    private function isUserHasBalance($user_id)
    {
        $status = "Cleared";

        $stmt = $this->con->prepare("SELECT balance FROM user_loans WHERE user_id = ? and status = 'Active'");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $stmt->bind_result($bal);
        $stmt->fetch();

        if ($bal > 0) {
            return true;
        } else {
            return false;
        }

    }

    private function checkUserLoanStatus($user_id)
    {
        $stmt = $this->con->prepare("SELECT status FROM user_loans WHERE user_id = ? and status = 'Active'");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();

        $stmt->bind_result($status);
        $stmt->fetch();

        return $status;
    }
    
    public function loanHistory($user_id)
    {
        $stmt = $this->con->prepare("SELECT lend_date, status, loan_amount, expected_date, paid_full_date FROM user_loans WHERE user_id = ?");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return json_encode($res);
    }

}
?>