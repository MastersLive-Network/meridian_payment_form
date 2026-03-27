<?php
session_set_cookie_params([
    'samesite' => 'None',
    'secure' => true, // required for SameSite=None
]);

@session_start();

// Disable browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");

error_reporting(E_ALL);
ini_set('display_errors', '1');

require "../db/connect.php";

//get details of transaction
$code = $_GET['u'] ?? '';
$query_ = "SELECT * FROM opay_withdrawal WHERE payment_reference='$code'";
$result_ = mysqli_query($con, $query_);
$num_ = $result_->num_rows;
$record = [];

if ($num_ < 1){
    //does not exist, show error

} else{
    $record = mysqli_fetch_assoc($result_);

    $query_ = "UPDATE opay_withdrawal SET status = 'USER_CANCELED' WHERE payment_reference='$code'";
    $result_ = mysqli_query($con, $query_);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Withdrawal Canceled</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      background-color: #f0f4f8;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .message-box {
      background: #fff;
      padding: 40px 50px;
      border-radius: 12px;
      text-align: center;
      box-shadow: 0 6px 20px rgba(0,0,0,0.12);
      animation: fadeIn 1s ease forwards;
      opacity: 0;
    }

    .message-box svg {
      width: 60px;
      height: 60px;
      margin-bottom: 20px;
      fill: #28a745;
    }

    .message-box h1 {
      font-size: 1.6rem;
      color: #333;
      margin-bottom: 10px;
    }

    .message-box p {
      font-size: 1rem;
      color: #555;
    }

    @keyframes fadeIn {
      to { opacity: 1; }
    }
  </style>
</head>
<body>
  <div class="message-box">
    <!-- Green check icon -->
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
      <path d="M20.285 6.709a1 1 0 0 0-1.414-1.418l-9.192 9.184-4.192-4.192a1 1 0 1 0-1.414 1.414l4.899 4.899a1 1 0 0 0 1.414 0l9.899-9.887z"/>
    </svg>
    <h1>Withdrawal Canceled</h1>
    <p>Your withdrawal request has been successfully canceled.</p>
  </div>
</body>
</html>