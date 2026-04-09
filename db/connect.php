<?php
//connection to MYSQL
//local
define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PWD", "");
define("DB_NAME", "meridianbet");

//production
// define("DB_HOST", "localhost");
// define("DB_USER", "kaymeri");
// define("DB_PWD", "Passwords12!!");
// define("DB_NAME", "meridianbet");


$con=mysqli_connect(DB_HOST, DB_USER, DB_PWD, DB_NAME);// Check connection


if (mysqli_connect_errno()){  
    echo "Failed to connect to MySQL: ".mysqli_connect_error();
}