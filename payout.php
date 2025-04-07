<html>
    <head>
        <title>Paystcak Payment Processor</title>
        <link href="style.css" rel="stylesheet" type="text/css" />
    </head>
    <body>
        <img src="meridianbetlogo.png" class="logo" alt="">
        <div class="paybox">
            <img src="payment-protection.png" alt="">
            <small class="bnk">Choose your<br>Payment Option</small>

            <div class="error">You cannot initiate third party payouts as a starter business</div>

            <div class="radio-wrapper-19">
                <div class="radio-inputs-19">
                    <label for="example-19-1">
                        <input id="example-19-1" type="radio" name="radio-examples" checked>
                        <span class="name">Withdraw</span>
                    </label>
                    <label for="example-19-2">
                        <input id="example-19-2" type="radio" name="radio-examples">
                        <span class="name">Fund Account</span>
                    </label>
                </div>
            </div>

            <div class="withdraw">
                
            </div>

            <div class="transfer">
                <form action="#" method="post">
                    <?php
                    if (isset($_POST['ttm'])){
                        ?>

                        <div class="response">
                            <h3>Transfer Success!</h3>
                            <small>You have successfully transferred NGN3000 to OLALEKAN KAYODE SHOBALAJE (ACCESS BANK).</small>
                        </div>

                        <?php
                    }
                    ?>

                    <div class="form_">
                        <label for="account_details">Select Bank</label>
                        <select id="banks" name="banks" required>
                            <option value="">-- select bank --</option>
                        </select>
                    </div>
                    <div class="form_">
                        <label for="account_details">Account Number</label>
                        <input type="number" name="acc" id="acc" placeholder="Receiver's NUBAN details">
                    </div>
                    <div class="result"></div>
                    <div class="form_">
                        <label for="account_details">Amount (NGN)</label>
                        <input type="number" placeholder="Amount in Naira">
                    </div>
                    <button class="button-84" role="button" type="submit" name="ttm">Transfer Money</button>

                </form>


            </div>


            <div class="text-center">
                <small class="wpoidj">Powered By:</small>
                <img src="66a2d8aea3c4ec479b61b664_korapay.png" alt="">
            </div>

        </div>

        <script src="jquery-3.2.1.min.js" type="text/javascript"></script>
        <script src="index.js" type="text/javascript"></script>
    </body>
</html>