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


// check if payment id exist


?>

<html>
    <head></head>
    <body>

        <div class="box">
            <img src="../meridianbetlogo.png" alt="" class="logo">
            <br><br>
            <h3>Payment Error</h3>
            <small class="text-muted">This payment link does not exist.</small>
        </div>
        

        <style>
            @font-face {
                font-family: "koo-light";
                src: url(../fonts/Montserrat-Light.ttf);
            }

            *{
                font-family: "koo-light" !important;
                font-size: 14px !important;
                box-sizing: border-box;
            }

            body {
                background: #eaeaea;
                margin: 0;
                padding: 0;
                font-family: Arial, sans-serif;

                /* center the box */
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
            }

            .box {
                background: #ffffff;
                padding: 40px 30px;
                border-radius: 10px;
                text-align: center;
                /* box-shadow: 0 4px 15px rgba(0,0,0,0.1); */
                box-shadow: rgba(0, 0, 0, 0.04) 0px 10px 36px 0px, rgba(0, 0, 0, 0.06) 0px 0px 0px 1px;
                width: 550px;
            }

            .logo {
                max-width: 200px;
            }

            h3 {
                margin: 35px 0 10px 0;
                font-size: 20px !important;
                font-weight: bold
            }

            .text-muted{
                color: #666666c8;
                display: block;
                margin-bottom: 40px;
            }
        </style>
        <script src="../jquery-3.2.1.min.js" type="text/javascript"></script>
        <script src="https://newwebpay.qa.interswitchng.com/inline-checkout.js"></script>
        <script>
            try {
                (function(c,l,a,r,i,t,y){
                    c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
                    t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/XXXXXX";
                    y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
                })(window, document, "clarity", "script");
            } catch (e) {
                console.warn("Clarity load skipped:", e);
            }
        </script>
        <script>
            //declare callback function
            function paymentCallback(response) {
                console.log(response);
            }

            //sample payment request
            var samplePaymentRequest = {
                merchant_code: "MX6072",          
                pay_item_id: "9405967",
                pay_item_name: "load wallet",
                txn_ref: "2320c797-d2ce-4ed4-a691-e775cb8c6a5a",
                site_redirect_url: "https://google.com/",
                amount: 10000, 
                currency: 566, // ISO 4217 numeric code of the currency used
                onComplete: paymentCallback,
                mode: 'TEST',
                cust_email: 'test@meridianbet.com'
            };

            //call webpayCheckout to initiate the payment
            window.webpayCheckout(samplePaymentRequest);
        </script>
    </body>
</html>