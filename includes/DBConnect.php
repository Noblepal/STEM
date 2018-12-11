<?php

class DBConnect
{
    private $con;

    function __construct()
    {

    }

    function connect()
    {
        include_once dirname(__FILE__) . '/constraints.php';
        $this->con = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        if (mysqli_connect_errno()) {
            printf("Failed to connect %s\n", mysqli_connect_error());
        }

        return $this->con;
    }
}

?>