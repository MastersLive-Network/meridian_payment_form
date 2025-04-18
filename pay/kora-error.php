<?php
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
$query_ = "SELECT * FROM korapay_withdrawal WHERE payment_reference='$code'";
$result_ = mysqli_query($con, $query_);
$num_ = $result_->num_rows;
$record = [];

if ($num_ < 1){
    //does not exist, show error

} else{
    $record = mysqli_fetch_assoc($result_);
}


$query_ = "UPDATE korapay_withdrawal SET status='INVALID_ON_PRIZMA' WHERE payment_reference='$code'";
$result_ = mysqli_query($con, $query_);

?>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="author" content="Meridianbet NG">

        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
        <meta http-equiv="Pragma" content="no-cache" />
        <meta http-equiv="Expires" content="0" />

        <title>Meridianbet NG - Korapay Payment Processor</title>
        <link href="../bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="../style.css" rel="stylesheet" type="text/css" />
    </head>
    <body>
        <img src="../meridianbetlogo.png" class="logo" alt="">
        <div class="paybox">
            <!-- start live payment -->
            <?php
            if ((isset($_GET['u']) && ($_GET['u'] !== "")) && ($num_ > 0)){
                ?>
                <!-- success or error -->
                <div class="">
                    <div class="su__">
                        <div class="text-center">
                            <img src="../error-512.png" alt="" class="loader_"><br>
                            <small class="bn">Invalid Withdrawal</small>
                            <section class="text-muted" id="tmsg">You cannot access withdrawal because it is invalid and/or does not exist. Please contact administrator if you think this is an error.</section>
                            <section class="text-muted dhjs_pso">You can close this window</section>
                        </div>
                    </div>
                </div>
                <!-- end of success or error -->
                <?php
            }
            ?>
            <!-- end live payment -->

            <!-- start broken link -->
            <?php
            if (!isset($_GET['u']) || (trim($_GET['u']) === "") || ($num_ < 1)){
                ?>
                <div class="broken">
                    <div>
                        <img src="broken.png" class="brklnk constant-tilt-shake" alt="">
                        <small class="bn">403 - Forbidden</small>
                        <div class="mt-2">
                            <small class="bnk text-muted">This payment link is unavailable, may not exist, or may have been entered incorrectly.</small>
                        </div>
                    </div>

                </div>
                <?php
            }
            ?>
            <!-- end of broken link -->


        </div>

        <script src="../jquery-3.2.1.min.js" type="text/javascript"></script>
        <script src="index-v1.1.js" type="text/javascript"></script>
    </body>
</html>