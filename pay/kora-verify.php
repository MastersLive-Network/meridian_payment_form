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




//get session data
$encryptedPayload = $_SESSION['xui'];

// -------------- DECRYPTING ------------------

$key = 'kay-meridian-123';
$decoded = base64_decode($encryptedPayload);

// Extract IV and encrypted part
$iv_dec = substr($decoded, 0, 16);
$encryptedText = substr($decoded, 16);

// Decrypt
$decrypted = openssl_decrypt($encryptedText, 'AES-256-CBC', $key, 0, $iv_dec);

// Convert JSON back to array
$session = json_decode($decrypted, true);


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
                <div class="live-payment">
                    <!-- <div class="row">
                        <div class="col-auto">
                            <img src="payment-protection.png" alt="">
                        </div>
                        <div class="col text-right">
                            <div class="float-end">
                                <small class="text-muted smm__"><?= ucwords($record['last_name']) . " " . ucwords($record['first_name']) ?></small>
                                <small class="text-black block smm__">Pay <span class="text-success text-bold">NGN <?= number_format($record['amount'], 2) ?></span></small>
                            </div>
                        </div>
                    </div> -->

                    <a href="korapay.php?u=<?= $_GET['u'] ?>" class="back"><img src="../left.png" alt=""> Account Details</a>

                    <div id="rsp"></div>
                    <div class="succcess">Recipient's account verified successfully</div>

                    <div class="form_">
                        <div class="chp__">Withdrawal Details</div>
                        <div class="row mb-010">
                            <div class="col">
                                <div class="segment">
                                    <span class="text-muted">Customer Name</span>
                                    <section class="details"><?= $session['customer_name'] ?></section>
                                </div>
                            </div>
                            <div class="col">
                                <div class="segment mt-30">
                                    <span class="text-muted">Amount</span>
                                    <section class="details">NGN <?= number_format($record['amount'], 2) ?></section>
                                </div>
                            </div>
                        </div>
                        
                        
                    </div>

                    <small class="bnk">Ensure you confirm Recipient's details</small>
                    
                    <div class="form_">
                        <div class="chp__">Recipient's Details</div>
                        <div class="segment">
                            <span class="text-muted">Bank Name</span>
                            <section class="details"><?= $session['bank_name'] ?></section>
                        </div>
                        <div class="segment mt-30">
                            <span class="text-muted">Account Number</span>
                            <section class="details"><?= $session['account_number'] ?></section>
                        </div>
                        <div class="segment mt-30 mb-010">
                            <span class="text-muted">Account Name (Resolved)</span>
                            <section class="details"><?= $session['acc_name'] ?></section>
                        </div>
                    </div>



                    <div class="transfer">
                        <form action="#" method="post" id="notify_prizma">
                            <input type="hidden" name="id" id="id" value="<?= $_GET['u'] ?? '' ?>">
                            <input type="hidden" name="bank_code" value="<?= $_GET['bank_code'] ?? '' ?>">
                            <input type="hidden" name="bank_name" value="<?= $_GET['bank_name'] ?? '' ?>">
                            <input type="hidden" name="customer_name" value="<?= $_GET['customer_name'] ?? '' ?>">
                            <input type="hidden" name="account_number" value="<?= $_GET['account_number'] ?? '' ?>">
                            <input type="hidden" name="account_name" value="<?= $_GET['acc_name'] ?? '' ?>">

                            <div class="dpsla"></div>
                            <div id="loader" style="display: none;">Processing...</div>

                            <div class="row">
                                <div class="col">
                                    <div class="text-center mt002">
                                        <a href="javascript:void(0)" class="border-btn rounded caw">Cancel</a>
                                    </div>
                                </div>
                                <div class="col">
                                    <button class="button-84 rounded" role="button" type="submit" name="ttm_" id="ttm_">Send Money</button>
                                    
                                </div>
                            </div>

                        </form>


                    </div>

                </div>
                <?php
            }
            ?>
            <!-- end live payment -->

            <!-- start broken link -->
            <?php
            if (!isset($_GET['u']) || (trim($_GET['u']) === "") || ($num_ < 1) || (!isset($_SESSION['xui']))){
                ?>
                <div class="broken">
                    <div>
                        <img src="../broken.png" class="brklnk constant-tilt-shake" alt="">
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



            <div class="overlay none">
                <div class="text-center">
                    <img src="../3686971.png" class="close__" alt="">
                    <div class="mb-3">
                        <span class="text-white ekjsoa">Cancel</span>
                    </div>
                    <div>
                        <span class="text-white">This will terminate/close this withdrawal request and it will no longer be valid.</span>
                    </div>
                    <button class="button-84 whitebg rounded" role="button" type="button" name="ttm">Cancel Transfer</button>
                </div>
            </div>

        </div>

        <script src="../jquery-3.2.1.min.js" type="text/javascript"></script>
        <script src="index-v1.1.js" type="text/javascript"></script>
    </body>
</html>