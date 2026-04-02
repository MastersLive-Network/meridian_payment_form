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
}

?>

<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="index.css?v=<?php echo time(); ?>">
</head>
<body>
    
    <div><br />
        <div class="text-center p-4 mt-100 mb-40">
            <img src="opay_transparent.png" class="logo__" alt="">
        </div>

        <div class="text-center mb-40" style="margin-top: -25px !important">
            <section class="opacity5">You are withdrawing:</section>
            <h1 class="amount"><span>&#8358;</span><?= number_format($record['amount'], 2) ?></section>
        </div>


        <div class="notice">
            <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="40" height="40" viewBox="0 0 64 64" fill="#856404">
            <path d="M32,6C17.641,6,6,17.641,6,32c0,14.359,11.641,26,26,26s26-11.641,26-26C58,17.641,46.359,6,32,6z M32.021,16 C33.555,16,35,17.346,35,18.981C35,20.727,33.555,22,32.021,22C30.225,22,29,20.727,29,18.981C29,17.346,30.225,16,32.021,16z M39,47h-5h-4h-5v-3l5-1V30h-4v-3l8-1v17l5,1V47z"></path>
            </svg>
            <section>Temporary unable to process OPay withdrawals. Please try again shortly or use another withdrawal option.</section>
        </div>
        <div class="tabs">
            <input class="input" name="tabs" type="radio" id="tab-1" checked="checked" />
            <label class="label" for="tab-1">Other Banks</label>
            <div class="panel">
                <h1>Send Money to Other Banks</h1>

                <form action="apis/withdraw_money.php" method="post">
                    <section class="opacity5 mt-30 mb-10">Select Destination Bank</section>
                    <select id="mySelect" style="width: 100%;" name="bank_code"></select>
                    <small id="load_banks">Loading Banks...</small>


                    <div class="dark_box mt-30">
                        <section class="opacity5 mb-10">Recipient Account</section>
                        <input autocomplete="off" type="text" placeholder="Enter Bank Account No." class="formc recipient_bank_account" name="account_number" maxlength="10">
                    </div>

                    <input type="hidden" name="payment_reference" value="<?= $code ?>">
                    <input type="hidden" name="account_name" id="account_name">

                    <div class="bank_validation"></div>

                    <div class="text-center">
                        <button class="rrd" type="submit" name="withdraw_bank" id="withdraw_bank">Withdraw NGN<?= number_format($record['amount'], 2) ?></button>
                    </div>
                </form>
            </div>
            <input class="input" name="tabs" type="radio" id="tab-2" />
            <label class="label" for="tab-2">Opay Wallet</label>
            <div class="panel">
                <h1>Send Money to Opay Wallet</h1>

                <form action="apis/withdraw_money.php" method="post">
                    <div class="dark_box mt-30">
                        <section class="opacity5 mb-10">Recipient Account</section>
                        <input autocomplete="off" type="text" placeholder="Phone No./Opay Account No./Name" class="formc recipient_opay_account" name="account_number">
                    </div>

                    <input type="hidden" name="bank_code" value="305">
                    <input type="hidden" name="payment_reference" value="<?= $code ?>">
                    <input type="hidden" name="account_name" id="wallet_name">
                    <div class="opay_validation"></div>

                    <div class="text-center">
                        <button class="rrd" type="submit" name="withdraw_wallet" id="withdraw_wallet">Withdraw NGN<?= number_format($record['amount'], 2) ?></button>
                    </div>
                </form>

            </div>
        </div>

        <div class="p-4 text-center">
            <section class="opacity5">
                Wrong amount entered for withdrawal?
            </section>

            <a href="opay_withdrawal_cancel.php?u=<?= $_GET['u'] ?>" class="darkrrd">Cancel Withdrawal</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="index-v1.1.js" type="text/javascript"></script>

    <script>
        $(document).ready(function () {
            $('#mySelect').select2({
                width: '100%'
            });

            $('#mySelect').select2({
                minimumResultsForSearch: 0,
                dropdownCssClass: "antd-dropdown",
                selectionCssClass: "antd-selection",
                templateResult: formatOption,
                templateSelection: formatOption,
                placeholder: "Select a bank"
            });

            $('.formc').on('input', function () {
                this.value = this.value.replace(/\D/g, '');
            });

            // Fetch bank list
            $.ajax({
                url: "https://korapay.meridianbet.com/processor/meridian_payment_form/opay/apis/bank-lists.php",
                type: "GET",
                dataType: "json",
                success: function (response) {

                    $("#load_banks").html("");

                    if (response.code === "00000") {

                        let banks = response.data;

                        // Clear existing options
                        $('#mySelect').empty();

                        // Add placeholder
                        $('#mySelect').append(`<option></option>`);

                        // Loop and append
                        banks.forEach(function (bank) {
                            $('#mySelect').append(
                                `<option value="${bank.bankCode}">${bank.bankName}</option>`
                            );
                        });

                        // Refresh Select2
                        $('#mySelect').trigger('change');
                    } else {
                        console.error("API Error:", response.message);
                    }
                },
                error: function (xhr, status, error) {
                    $("#load_banks").html("");
                    console.error("Request failed:", error);
                }
            });

            function formatOption(option) {
                if (!option.id) return option.text;

                const img = $(option.element).data('image');

                return $(`
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span>${option.text}</span>
                    </div>
                `);
            }


            let typingTimer;
            const delay = 500; // debounce delay

            $('.recipient_opay_account').on('input', function () {
                let value = $(this).val().trim();

                clearTimeout(typingTimer);

                // Only trigger when length is exactly 10
                if (value.length === 10) {

                    typingTimer = setTimeout(function () {

                        // Show loading
                        $('.opay_validation').html(`
                            <div style="display:flex; align-items:center; gap:8px;">
                                <div class="spinner"></div>
                                <span>Validating account...</span>
                            </div>
                        `);

                        $("#withdraw_wallet").hide();


                        // API Call
                        $.ajax({
                            url: "https://korapay.meridianbet.com/processor/meridian_payment_form/opay/apis/opay-wallet-validate.php",
                            type: "POST",
                            dataType: "json",
                            data: {
                                phone_account: value
                            },
                            success: function (res) {

                                if (res.code === "00000") {
                                    $('.opay_validation').html(`
                                        <div class="alert-success">
                                            ${res.data.firstName + ' ' + res.data.lastName || 'Account Verified'}
                                        </div>
                                    `);

                                    $("#wallet_name").val(res.data.firstName + ' ' + res.data.lastName);
                                    $("#withdraw_wallet").show();

                                } else {
                                    $('.opay_validation').html(`
                                        <div class="alert-error">
                                            ${res.message}
                                        </div>
                                    `);

                                    $("#withdraw_wallet").hide();

                                }
                            },
                            error: function () {
                                $('.opay_validation').html(`
                                    <div class="alert-error">
                                        Failed to validate account
                                    </div>
                                `);
                                $("#withdraw_wallet").hide();

                            }
                        });

                    }, delay);

                } else {
                    // Clear if not 10 digits
                    $('.opay_validation').html('');
                }
            });


            let lastRequest = "";

            function validateBankAccount() {
                let accountNo = $('.recipient_bank_account').val().trim();
                let bankCode = $('#mySelect').val();

                // Must have both
                if (accountNo.length !== 10 || !bankCode) {
                    $('.bank_validation').html('');
                    return;
                }

                let requestKey = bankCode + accountNo;
                if (requestKey === lastRequest) return;
                lastRequest = requestKey;

                // Show loading
                $('.bank_validation').html(`
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div class="spinner"></div>
                        <span>Validating account...</span>
                    </div>
                `);

                $("#withdraw_bank").hide();

                // API Call
                $.ajax({
                    url: "https://korapay.meridianbet.com/processor/meridian_payment_form/opay/apis/bank-account-validate.php",
                    type: "POST",
                    dataType: "json",
                    data: {
                        accountBankCode: bankCode,
                        accountNo: accountNo
                    },
                    success: function (res) {

                        if (res.code === "00000") {
                            $('.bank_validation').html(`
                                <div class="alert-success">
                                    ${res.data.accountName || 'Account Verified'}
                                </div>
                            `);
                            $('#account_name').val(res.data.accountName);
                            $("#withdraw_bank").show();

                        } else {
                            $('.bank_validation').html(`
                                <div class="alert-error">
                                    ${res.message}
                                </div>
                            `);
                        }

                    },
                    error: function () {
                        $('.bank_validation').html(`
                            <div class="alert-error">
                                Failed to validate account
                            </div>
                        `);

                        $("#withdraw_bank").hide();

                    }
                });
            }

            //  Trigger on account input (debounced)
            $('.recipient_bank_account').on('input', function () {

                // Only numbers
                this.value = this.value.replace(/\D/g, '');

                clearTimeout(typingTimer);

                typingTimer = setTimeout(function () {
                    validateBankAccount();
                }, 500);
            });

            //  Trigger when bank changes
            $('#mySelect').on('change', function () {
                validateBankAccount();
            });
        });
    </script>
</body>
</html>