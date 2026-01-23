<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once("../db/connect.php");

$merchantId = '256624093066025';
$secretkey = 'OPAYPRV17277085871070.5999657476412334';
$url = 'https://testapi.opaycheckout.com/api/v1/international/cashier/status';

$reference = trim($_GET['ref']);

function auth ( $data, $secretKey ) {
    $secretKey = $secretkey;
    $auth = hash_hmac('sha512', $data, $secretKey);
    return $auth;
}

function http_post ($url, $header, $data) {
    if (!function_exists('curl_init')) {
        throw new Exception('php not found curl', 500);
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    $response = curl_exec($ch);
    $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error=curl_error($ch);
    curl_close($ch);
    if (200 != $httpStatusCode) {
        print_r("invalid httpstatus:{$httpStatusCode} ,response:$response,detail_error:" . $error, $httpStatusCode);
    }
    return $response;
}

if ($reference !== ''){
    // 1. Check DB
    $stmt = $con->prepare("SELECT * FROM opay_deposit WHERE payment_id = ?");
    $stmt->bind_param("s", $reference);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows < 1) {
        // log the error
         $timestamp = date("Ymd_His");
        $filename  = __DIR__ . "/callbacks/{$timestamp}.txt";

        if (!is_dir(__DIR__ . "/callbacks")) {
            mkdir(__DIR__ . "/callbacks", 0777, true);
        }

        file_put_contents($filename, $callbackJson);
    } else{
        // ref exist
        $record = $result->fetch_assoc();

        $data = [
            'country' => 'NG',
            'reference' => $reference
        ];
        $data2 = (string) json_encode($data,JSON_UNESCAPED_SLASHES);
        $auth = auth($data2, $secretKey);
        $header = ['Content-Type:application/json', 'Authorization:Bearer '. $auth, 'MerchantId:'.$merchantId];
        $response = http_post($url, $header, json_encode($data));
        $result = $response?$response:null;

        // decode result
        if ($result) {
            $callbackJson = json_encode($result, JSON_UNESCAPED_SLASHES);
            // Save callback data
            $stmt = $con->prepare(
                "UPDATE opay_deposit SET query_status = ? WHERE payment_id = ?"
            );
            $stmt->bind_param("ss", $callbackJson, $reference);
            $stmt->execute();

            // filter data to notify MERIDIANBET
            $decoded = json_decode($result, true); // decode to associative array
            $prizmaStatus = 'FAILED';
            if ($decoded['code'] === '00000' && $decoded['message'] === 'SUCCESSFUL') {
                // success
                $prizmaStatus = 'CREATE_TRANSFER';
            }

            // Timestamp in milliseconds
            $timestampMs = round(microtime(true) * 1000);

            // Compose Prizma request
            $prizma_req = [
                "paymentId"            => $record['payment_id'],
                "paymentType"          => "DEPOSIT",
                "accountId"            => $record['account_id'],
                "amount"               => (float)$payload['amount'],
                "currencyCode"         => "NGN",
                "createTimestamp"      => $timestampMs,
                "status"               => $prizmaStatus,
                "currencyNumericCode"  => 566,
                "overrideAmount"       => false,
                "customerParams"       => [
                    "customerPhone"      => $record['customer_phone'] ?? '2349012341234',
                    "customerEmail"      => $record['customer_email'] ?? 'kayode@meridianbet.com',
                    "customerFirstName"  => $record['customer_first_name'] ?? 'Kayode',
                    "customerLastName"   => $record['customer_last_name'] ?? 'shobalaje',
                    "clientLanguage"     => "en",
                    "customerCountryIso2"=> "NG",
                    "customerCountry"    => "Nigeria"
                ]
            ];

            $prizmaReqJson = json_encode($prizma_req, JSON_UNESCAPED_SLASHES);

            // Send notification to MeridianBet
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL            => "https://payments-stage.meridianbet.com/proxy/notify/" . $record['payment_id'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST  => "POST",
                CURLOPT_HTTPHEADER     => ["Content-Type: application/json"],
                CURLOPT_POSTFIELDS     => $prizmaReqJson,
                CURLOPT_TIMEOUT        => 30
            ]);

            $response = curl_exec($curl);
            curl_close($curl);

            // 6. Save Prizma request
            $stmt = $con->prepare(
                "UPDATE opay_deposit 
                SET notify_prizma_req = ?, status = 'COMPLETED' 
                WHERE payment_id = ?"
            );
            $stmt->bind_param("ss", $prizmaReqJson, $reference);
            $stmt->execute();
        }
    }

    
}

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