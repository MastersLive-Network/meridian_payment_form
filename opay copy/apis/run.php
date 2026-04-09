<?php
require_once "../../db/connect.php";

// List of ALTER TABLE queries
$queries = [
    "ALTER TABLE opay_withdrawal 
        ADD account_number VARCHAR(20) NULL DEFAULT NULL AFTER amount,
        ADD bank_code VARCHAR(10) NULL DEFAULT NULL AFTER account_number,
        ADD account_name VARCHAR(100) NULL DEFAULT NULL AFTER bank_code,
        ADD order_no VARCHAR(100) NULL DEFAULT NULL AFTER account_name,
        ADD reference VARCHAR(200) NULL DEFAULT NULL AFTER order_no",
    
    "ALTER TABLE opay_withdrawal ADD order_status VARCHAR(60) NULL DEFAULT NULL AFTER reference",
    
    "ALTER TABLE opay_withdrawal ADD error_msg TEXT NULL DEFAULT NULL AFTER status"
];

// Execute each query
foreach ($queries as $query) {
    $result = mysqli_query($con, $query);
    if ($result) {
        echo "Query executed successfully: $query<br>";
    } else {
        echo "Error executing query: $query<br>Error: " . mysqli_error($con) . "<br>";
    }
}

// Close connection
mysqli_close($con);
?>