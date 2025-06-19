<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once( "../db/connect.php" );

$code = $_GET['u'] ?? '';

//check if reference exist
$query_ = "SELECT * FROM pocket_deposit WHERE reference='$code'";
$result_ = mysqli_query($con, $query_);
$num_ = $result_->num_rows;
$record = [];

if ($num_ < 1){
    //does not exist, show error

} else{
    $record = mysqli_fetch_assoc($result_);

    if ($record['pocket_status'] !== "PENDING"){
        header('Location: 403.php');
    }
}

//if status is not pending, go to another page, link expired

?>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pocket</title>
    <link href="../bootstrap.min.css" rel="stylesheet" type="text/css" />
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

        body{
            background: #ccc;
            width: 100%;
            height: 100%;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;

            position: relative;
            background:#753ff6  url(../larva-bg.3443d1c4.png) 50% no-repeat;
            background-size: cover;
        }

        .cc__{
            background: #fff;
            width: 400px;
            min-height: 500px;
            border-radius: 20px;
            padding: 40px 25px;
            box-shadow: rgba(17, 12, 46, 0.15) 0px 48px 100px 0px;
        }

        svg{
            max-width: 100px;
            display: block;
            margin: 2px auto
        }

        img.logo{
            max-width: 200px;
            filter: grayscale(100%);
        }

        .mt-20{
            margin-top: 20px;
        }

        .mt-50{
            margin-top: 50px;
        }

        .amsn{
            border-top: 1px solid rgba(235, 235, 235, 0.56);
            padding-top: 40px
        }

        .loader {    
            --r1: 154%;
            --r2: 68.5%;
            width: 60px;
            aspect-ratio: 1;
            border-radius: 50%; 
            background:
                radial-gradient(var(--r1) var(--r2) at top   ,#eaeaea 79.5%,#6c40ec 80%),
                radial-gradient(var(--r1) var(--r2) at bottom,#6c40ec 79.5%,#eaeaea 80%),
                radial-gradient(var(--r1) var(--r2) at top   ,#eaeaea 79.5%,#6c40ec 80%),
                #ccc;
            background-size: 50.5% 220%;
            background-position: -100% 0%,0% 0%,100% 0%;
            background-repeat:no-repeat;
            animation: l9 2s infinite linear;
            margin: 30px auto;
        }
        @keyframes l9 {
            33%  {background-position:    0% 33% ,100% 33% ,200% 33% }
            66%  {background-position: -100%  66%,0%   66% ,100% 66% }
            100% {background-position:    0% 100%,100% 100%,200% 100%}
        }

        .chip_p{
            background: #e5e5e5;
            padding: 5px 12px;
            display: inline-block;
            border-radius: 10px;
            font-weight: bold;
        }

        .cancel, .success, .error{
            display: none;
        }

        img.ww{
            max-width: 50px;
            margin-bottom: -30px
        }
    </style>
</head>
<body>

    <div class="cc__ text-center">
        <img src="../meridianbetlogo.png" class="logo" alt="">
        

        <?php
        if ($num_ > 0){
        ?>
            <div class="text-center mt-50">
                <section class="chip_p">&#8358; <?= number_format($record['amount']) ?></section>
            </div>
            
            <div class="pending" id="pending">
                <h1 class="mt-20">Payment Processing</h1>
                <div class="loader"></div>
            </div>
        <?php
        } else{
            ?>
            <div class="invalid mt-20 p-4" id="invalid">
                <img src="../error-512.png" class="ww" alt="">
                <h1 class="mt-20 p-4">PAYMENT DOES NOT EXIST</h1>
                <small class="text-muted">Payment link does not exist. Please close this window to reinitiate another payment if this was a mistake.</small>
            </div>
            <?php
        }
        ?>


        <div class="cancel mt-20 p-4" id="cancel">
            <img src="../error_red.png" class="ww" alt="">
            <h1 class="mt-20 p-4">PAYMENT CANCELED</h1>
            <small class="text-muted">Payment was canceled. Please close this window to reinitiate another payment if this was a mistake.</small>
        </div>


        



        <div class="error mt-20 p-4" id="error">
            <img src="../error_red.png" class="ww" alt="">
            <h1 class="mt-20 p-4">ERROR OCCURED</h1>
            <small class="text-muted">An error occured while processing your payment or with your inputs, please try again later.</small>
        </div>

        <div class="success mt-20 p-4" id="success">
            <img src="../check_green.png" class="ww" alt="">
            <h1 class="mt-20 p-4">PAYMENT SUCCESSFUL</h1>
            <small class="text-muted">Your account has been credited with your payment.</small>
        </div>


        <div class="text-center amsn mt-50">
            <small class="wpoidj">Powered By:</small>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 696 263" class="Footer_logo__xLcg0"><path class="logo_svg__cls-1" d="M552.1 234.1c-.5 4.3.7 7.8 3.8 10.4 3.1 2.7 7.5 4 13.1 4h1.8c12.7 0 19.9-4.5 22-15.9l2.8-15.8c.9-4.9-.2-9-3.1-12.2-2.9-3.2-7.3-4.8-13.1-4.8h-1.5c-13.4 0-19.7 5-21.2 15.6h11.1c.7-3.8 3.6-5.7 9-5.7s7.8 2.9 6.9 7.5l-.7 4.1c-2.1-1.2-5.6-1.8-10.7-1.8-12.3 0-19 4.5-20.2 13.4l-.2 1.1Zm28.9-.2c-.7 3.8-4.3 6.3-9.7 6.3s-8.3-2-7.4-6.3l.2-.8c.9-4.3 3.5-6.2 9.6-6.2s8.3 1.9 7.5 6.2v.7Zm50.7-3.4c-.9 5.3-4.3 8-10.1 8s-8.2-2.7-7.3-8l2.2-12.2c.9-5.3 4.3-8 10.1-8s8.2 2.7 7.3 8l-2.1 12.2Zm13.9-12.8c.9-5.2-.2-9.4-3.5-12.8-3.2-3.3-8.1-5-14.6-5-13.8 0-20.6 5.5-22.5 16.9l-8.1 46.1h11.7l3.1-17.7c1.9 1.9 6.3 3.3 11.3 3.3 12.1 0 18.1-5.3 20.2-16.6l2.5-14.2Zm36.2 12.8c-.9 5.3-4.3 8-10.1 8s-8.2-2.7-7.3-8l2.2-12.2c.9-5.3 4.3-8 10.1-8s8.2 2.7 7.3 8l-2.1 12.2Zm13.9-12.8c.9-5.2-.2-9.4-3.5-12.8-3.2-3.3-8.1-5-14.6-5-13.8 0-20.6 5.5-22.5 16.9l-8.1 46.1h11.7l3.1-17.7c1.9 1.9 6.3 3.3 11.3 3.3 12.1 0 18.1-5.3 20.2-16.6l2.5-14.2Z" fill="#9E9E9E"></path><g class="logo_svg__cls-2" fill="#753FF6"><path class="logo_svg__cls-1" d="M56 32.4c-37.5 0-56 17.4-56 46.4v126.9h32.2v-48.8c6.6 6 17.7 8.9 33.2 8.9 31.7 0 46.9-16.6 46.9-45.6V78.7c0-29-18.5-46.4-56.2-46.4Zm24.1 83.9c0 14.7-8.1 22.2-24.1 22.2s-23.8-7.5-23.8-22.2V82.8c0-14.7 7.9-22.1 23.8-22.1s24.1 7.5 24.1 22.1v33.5ZM312.1 32.4c-35.8 0-54.3 18.7-54.3 46v41.7c0 27.3 18.5 45.8 54.1 45.8s53.5-18.5 53.5-45.8v-7.2h-30.9v3.8c0 13.8-7.5 20.9-22.5 20.9s-22.6-7-22.6-21.3V82c0-14.3 7.7-21.3 22.8-21.3s22.4 7 22.4 20.9v4.3h30.9v-7.4c0-27.3-18.6-46-53.3-46Z"></path><path data-name="Path" class="logo_svg__cls-1" d="M492.4 34.5h-36l-27.3 47.7h-16.2V0h-32.1v164h32.1v-54.5h15.2l29.1 54.5h37.1l-38.1-66.9 36.2-62.6z"></path><path data-name="Shape" class="logo_svg__cls-1" d="M554.9 32.4c-37.3 0-56.7 17.7-56.7 46v42c0 28.3 18.9 45.6 56.7 45.6s56.4-16 56.4-38.8v-4.3h-31.1v1.1c0 10.4-8.3 15.8-25.1 15.8s-25.8-7.2-25.8-21.5v-10.2h82V78.5c0-28.8-19.2-46-56.4-46Zm25.3 54.7h-50.9v-6.6c0-14.7 8.5-21.9 25.6-21.9s25.3 7.2 25.3 21.7v6.9Z"></path><path data-name="Path" class="logo_svg__cls-1" d="M681.9 136.1c-16.8 0-22.5-5.3-22.5-24.9V60.3h35.1V34.5h-35.1V7.9h-32.2v105.4c0 36.4 13.2 50.9 47.3 50.9h20.9v-28.1H682Z"></path><path data-name="Shape" class="logo_svg__cls-1" d="M217.4 34.6c-11.1-1.4-22.3-2.1-33.6-2.1-4.8-.2-11.1.3-17.5.8-5.5.4-11.1.8-16.5 1.8-9.8 1.9-19.1 13.2-20.3 24.6-1.8 17.5-2.3 35-1.7 52.6.4 12.8 4.9 23.2 13.3 31.5 3.4 3.3 7.1 6.2 11.1 8.7 7.4 4.7 15.3 8.2 23.2 11.4 4.9 2 10.4 2.4 15.5 1 8-2.1 15.3-6.1 22.7-10 5.1-2.7 9.9-6.1 14.1-10.1 7.4-7 12.5-15.7 13.8-26.8.5-4.3.6-8.7.8-13.1.3-14.6-.4-29.1-1.8-43.7-1.3-14.1-10.9-25.2-23.2-26.7Zm-66.8 17.1c.4-.5.9-1 1.4-1.3.4-.2.8-.5 1.2-.6l.6-.3c.4-.2 1-.3 1.5-.5.3 0 .6-.2.9-.2.5 0 1.2-.1 1.8-.2h2.1c8.5.2 16.8.4 24.9.7 8.1-.3 16.5-.5 24.9-.7h2.1c.6 0 1.2.2 1.8.2.3 0 .6.2.9.2.5.1 1 .3 1.5.5.2 0 .4.2.6.3l1.2.6c.5.4 1 .8 1.4 1.3 1.8 2.3 2 5.6.8 9.5-.2.7-.3 1.3-.5 2-.3 1.1-.5 2.3-.6 3.5-.2 1.1-.3 2.3-.3 3.4v4.8c-1.2.3-2.5.6-3.9.9-.2-2.2-.3-4.3-.2-6.5 0-1.1.1-2.3.3-3.4.1-1.2.4-2.3.6-3.5 0-.3.1-.6.2-.8.7-2.7.4-5.1-1-6.8-1.4-1.9-3.9-2.8-7.2-2.7-7.5.2-15 .5-22.5.8-7.5-.3-15-.6-22.5-.8-3.3 0-5.8.8-7.2 2.7-1.4 1.7-1.7 4.1-1 6.8 0 .3 0 .5.2.8.3 1.1.5 2.3.6 3.5.2 1.1.3 2.3.3 3.4 0 2.2 0 4.3-.2 6.5-1.4-.3-2.7-.6-3.9-.9v-4.8c0-1.1-.1-2.3-.3-3.4-.1-1.2-.3-2.3-.6-3.5-.2-.7-.3-1.3-.5-2-1.3-3.9-1.1-7.2.7-9.5Zm8.4 17.2c4.3 0 7.6-.8 10-2.4 1.6-.9 2.9-2.4 3.5-4.2.5-1.2.6-2.6.4-3.9 0-.5-.2-.9-.3-1.4l-.6-1.5c4.1.2 8.5.3 12.9.5 4.4-.2 8.8-.3 12.9-.5l-.6 1.5c-.1.5-.2.9-.3 1.4-.2 1.3 0 2.6.4 3.9.6 1.8 1.9 3.2 3.5 4.2 2.4 1.6 5.8 2.4 10 2.4 0 2.5 0 4.9.3 7.4-8.7 1.6-17.5 2.2-26.3 1.9-8.8.3-17.6-.4-26.3-1.9.2-2.4.3-4.9.2-7.4Zm-.9-7.4v-.4c-.4-1.7-.2-3.1.6-4.2.9-1.1 2.5-1.7 4.6-1.6h4.9c.7 1.3 1.1 2.6 1.1 4 0 1-.3 2-.9 2.8-.3.5-.8 1-1.3 1.3-1.8 1.3-4.6 2-8.3 2v-.3c-.2-1.2-.4-2.4-.7-3.6Zm53.3 3.6v.3c-3.7 0-6.6-.7-8.3-2-.5-.3-.9-.8-1.3-1.3-.6-.8-.9-1.8-.9-2.8 0-1.4.5-2.7 1.1-3.8h4.9c2.1-.2 3.7.4 4.6 1.5.9 1 1 2.5.6 4.2v.4c-.3 1.2-.6 2.4-.7 3.6Zm18.3 20.7c-.1 2.4-1.6 4.4-3.9 5.2-26.6 7.8-54.9 7.8-81.5 0-2.2-.8-3.8-2.8-3.9-5.2v-5.9c0-2.2 1.7-3.4 3.9-2.8 26.6 7.8 54.9 7.8 81.5 0 2.2-.6 3.9.6 3.9 2.8v5.9Z"></path></g></svg>
        </div>
        
    </div>
    
    <script src="https://pocket-checkout-sdk.netlify.app/checkout-sdk.js"></script>
    <script type="text/javascript">
        const payWithPocket = new Pocket({
            key:'test_public_key_QuZqGF8dFwN2YTrl2wBTIW2d',
            amount: <?= $record['amount'] * 100 ?>,// amount to charge (in kobo)
            reference: "<?= $record['reference'] ?>",
            narration: "topping up my meridianbet wallet",
            view: "popup",
            redirect_url: "success.php?u=<?= $code ?>",
            onSuccess: success, // when we have confirmed payment transaction, it returns reference of the transaction 
            onClose: cancel, // when the checkout has been closed
            onPending: Function, // when the user claims they have made payment
            onOpen: Function, // when the checkout pop-up or redirection has been initialized
            onError: error, // if any error happens
        });


        <?php
        if ($num_ > 0){
        ?>
        setTimeout(() => {
            payWithPocket.show();
        }, 1000);
        <?php
        }
        ?>



        function error(){
            document.getElementById("pending").style.display = "none";
            document.getElementById("error").style.display = "block";
            
            // console.log("error", err);
        }



        
        function success(){
            document.getElementById("pending").style.display = "none";
            document.getElementById("error").style.display = "none";
            document.getElementById("cancel").style.display = "none";
            document.getElementById("success").style.display = "block";

            location.href = "success.php?u=<?= $code ?>";
        }



        function cancel(){
            document.getElementById("pending").style.display = "none";
            document.getElementById("cancel").style.display = "block";

            // setTimeout(() => {
            //     location.href = "success.php?u=<?= $code ?>"
            // }, 500);
        }


        function pocket_close(){
            
        }
    </script>
</body>
</html>