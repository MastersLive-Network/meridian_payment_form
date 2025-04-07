<html>
    <head>
        <title>Paystcak Payment Processor</title>
        <link href="style.css" rel="stylesheet" type="text/css" />
    </head>
    <body>
        <div class="paybox">
            <img src="payment-protection.png" alt="">
            <small class="bnk">Choose your<br>Payment Option</small>

            <div class="error">You cannot initiate third party payouts as a starter business</div>

            <div class="radio-wrapper-19">
                <div class="radio-inputs-19">
                    <label for="example-19-1">
                    <input id="example-19-1" type="radio" name="radio-examples">
                    <span class="name">Withdraw</span>
                    </label>
                    <label for="example-19-2">
                    <input id="example-19-2" type="radio" name="radio-examples" checked>
                    <span class="name">Transfer</span>
                    </label>
                </div>
            </div>

            <div class="withdraw">
                
            </div>

            <div class="transfer">
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
                <button class="button-84" role="button">Transfer Money</button>



            </div>

        </div>

        <script src="jquery-3.2.1.min.js" type="text/javascript"></script>
        <script src="index.js" type="text/javascript"></script>
    </body>
</html>