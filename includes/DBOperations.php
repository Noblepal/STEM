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
            $stmt = $this->con->prepare("INSERT INTO users (user_id, firstname, lastname, phone, email, nationalID, reg_date, pin) VALUES (NULL, ?, ?, ?, ?, ?, now(), ?);");
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
                //TODO: Status == PENDING APPROVAL
                $status = "Active";
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

    //Calculate user points
    public function calculatePoints($user_id)
    {
        #VARIABLES:
        $w;//week
        $e;//expected date
        $remd;//remaining days = expected date - paid date
        $ed;//elapsed days = expected date - remaining days
        $pd;//paid date
        $a;//amount
        $pts;//points
        $x;

        //Get user data
        $stmt = $this->con->prepare("SELECT lend_date, status, loan_amount, expected_date, paid_full_date FROM user_loans WHERE user_id = ? AND status = 'Cleared'");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $stmt->bind_result($lend_date, $status, $loan_amount, $expected_date, $paid_date);
        $stmt->fetch();
        $stmt->close();

        $ld = date_create($lend_date);
        $e = date_create($expected_date);
        $pd = date_create($paid_date);

        $ed = date_diff($e, $pd);

        echo $ed->format("%R%a Days");
    }

    private function isUserHasBalance($user_id)
    {
        $status = "Cleared";
        //TODO: Status == PENDING APPROVAL

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

    public function getUserLoanDetails($user_id)
    {
        $status = "Active";
        $stmt = $this->con->prepare("SELECT loan_amount, lend_date, expected_date, paid_amount, balance, status FROM user_loans WHERE user_id = ? and status = ?");
        $stmt->bind_param("ss", $user_id, $status);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function uploadPhoto($user_id, $image_name, $photo)
    {
        $upload_path = "../profile_pictures/$image_name.jpg";
        $stmt = $this->con->prepare("UPDATE users SET photo = ? WHERE user_id = ?");
        $stmt->bind_param("ss", $image_name, $user_id);

        if ($stmt->execute()) {
            if (file_put_contents($upload_path, base64_decode($photo))) {
                return 1;
            } else {
                return 0;
            }
            return 1;
        } else {
            return 2;
        }
    }

}

//ADMIN OPERATIONS ONLY!
class AdminOperations
{
    private $con;

    function __construct()
    {
        require_once dirname(__FILE__) . '/DBConnect.php';
        $db = new DBConnect();

        $this->con = $db->connect();

    }

    public function getUsers()
    {
        $stmt = $this->con->prepare("SELECT * FROM users ORDER BY reg_date DESC");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    }

    public function getLatestUsers()
    {
        $stmt = $this->con->prepare("SELECT * FROM users ORDER BY reg_date DESC LIMIT 5");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    }

    public function getUserForEditting($user_id)
    {
        $stmt = $this->con->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function fetchNewLoanApplications()
    {
        $stmt = $this->con->prepare("SELECT `users`.`firstname`, `users`.`lastname`, `users`.`phone`, `user_loans`.`*` from users LEFT OUTER JOIN user_loans ON `users`.`user_id` = `user_loans`.`user_id` WHERE `user_loans`.`status` = 'Pending'");
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function fetchCRBMembers()
    {
        $stmt = $this->con->prepare("SELECT `users`.`firstname`, `users`.`lastname`, `users`.`phone`, `user_loans`.`*` from users LEFT OUTER JOIN user_loans ON `users`.`user_id` = `user_loans`.`user_id` WHERE `user_loans`.`status` = 'Late'");
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    //Get data to fill chart
    public function getChartData()
    {
        $stmt = $this->con->prepare("SELECT * FROM user_loans WHERE 1 ORDER BY user_id ASC");
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    //Counter Functions
    public function fetchNumUsers()
    {
        $stmt = $this->con->prepare("SELECT * FROM users ORDER BY user_id DESC");
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows;
        $stmt->close;
    }

    public function getCRBUsers()
    {
        $stmt = $this->con->prepare("SELECT * from users LEFT OUTER JOIN user_loans ON `users`.`user_id` = `user_loans`.`user_id` WHERE `user_loans`.`status` = 'Late'");
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows;
        $stmt->close;
    }

    public function fetchNumLoanApplications()
    {
        $stmt = $this->con->prepare("SELECT * FROM user_loans WHERE status = 'pending' ORDER BY user_id DESC");
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows;
        $stmt->close;
    }
}

?>