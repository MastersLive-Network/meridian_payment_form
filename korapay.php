<?php
@session_start();

error_reporting(E_ALL);
ini_set('display_errors', '1');

require "db/connect.php";

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

?>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="author" content="Meridianbet NG">

        <title>Meridianbet NG - Korapay Payment Processor</title>
        <link href="bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="style.css" rel="stylesheet" type="text/css" />
    </head>
    <body>
        <img src="meridianbetlogo.png" class="logo" alt="">
        <div class="paybox">
            <!-- start live payment -->
            <?php
            if ((isset($_GET['u']) && ($_GET['u'] !== "")) && ($num_ > 0)){
                ?>
                <div class="live-payment">
                    <div class="row">
                        <div class="col-auto">
                            <img src="payment-protection.png" alt="">
                        </div>
                        <div class="col text-right">
                            <div class="float-end">
                                <small class="text-muted smm__"><?= ucwords($record['last_name']) . " " . ucwords($record['first_name']) ?></small>
                                <small class="text-black block smm__">Pay <span class="text-success text-bold">NGN <?= number_format($record['amount'], 2) ?></span></small>
                            </div>
                        </div>
                    </div>

                    <div id="rsp"></div>
                    <small class="bnk">Provide your Recipient's details</small>

                    <div class="transfer">
                        <form action="#" method="post" id="send-money">
                            <input type="hidden" name="id" value="<?= $_GET['u'] ?? '' ?>">

                            <div class="form_">
                                <label for="account_details">Select Bank</label>
                                <select id="banks" name="banks">
                                    <option value="">-- select bank --</option>
                                </select>
                            </div>
                            <div class="form_">
                                <label for="account_details">Account Number</label>
                                <input type="number" name="acc" id="acc" placeholder="Receiver's NUBAN details">
                            </div>
                            <div class="float-end">
                                <img src="check_green.png" id="success_" alt="">
                                <img src="error_red.png" id="error_" alt="">
                            </div>
                            <div class="clearfix"></div>
                            <div class="result"></div>

                            <div id="loader" style="display: none;">Processing...</div>
                            
                            <div class="text-center">
                                <button class="button-84 rounded" role="button" type="submit" name="ttm_" id="ttm_">Transfer Money</button>
                                <a href="javascript:void(0)" class="block cank text-underline text-muted caw">Cancel Withdrawal</a>
                            </div>

                        </form>


                    </div>


                    <div class="text-center">
                        <small class="wpoidj">Powered By:</small>
                        <img src="66a2d8aea3c4ec479b61b664_korapay.png" alt="">
                    </div>
                </div>
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


            <!-- success or error -->
            <div class="fhjs__lo">
                <div class="succ">
                    <div class="text-center">
                        <img src="ico_suc.gif" alt="" class="loader">
                        <small class="bn">Transfer Success!</small>
                        <section class="text-muted" id="tmsg">Transfer of NGN 10000.00 was initiated successfully sjknsskds jksdsjk klnsfsd fkjndsfd.</section>
                        <section class="text-muted dhjs_pso">You can close this window</section>
                    </div>
                </div>
            </div>
            <!-- end of success or error -->


            <div class="overlay none">
                <div class="text-center">
                    <img src="3686971.png" class="close__" alt="">
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

        <script src="jquery-3.2.1.min.js" type="text/javascript"></script>
        <script src="index.js" type="text/javascript"></script>
    </body>
</html>