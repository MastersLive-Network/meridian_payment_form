<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

?>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OPAY - Pending Payment</title>
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
            background: #d3d3d3ff;
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
            max-width: 70px;
            margin-bottom: -30px
        }

        .font-bold{
            font-weight: bold;
            font-size: 25px !important;
        }

        .wpoidj{
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="cc__ text-center">
        <img src="../meridianbetlogo.png" class="logo" alt="">
        

        
        <div class="invalid mt-20 p-4" id="invalid">
            <img src="success__.png" class="ww" alt="">
            <h1 class="mt-20 p-4 font-bold">Payment<br>Completed</h1>
            <small class="text-muted">Your payment was completed successfully. You may close this window. To proceed with another payment, please begin a new transaction.</small>
        </div>
            

        <div class="text-center amsn mt-50">
            <small class="wpoidj">Powered By:</small>
            <img src="opay.png" alt="OPAY">
        </div>
        
    </div>
</body>
</html>