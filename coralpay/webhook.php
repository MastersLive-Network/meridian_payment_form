<?php
//receive the reference and update it to success

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once( "../db/connect.php" );


$status = $_GET['status'] ?? null;
$id     = $_GET['id'] ?? null;

if ($status === 'success' && !empty($id)) {
    // Success
} else {
    // Redirect to error page
    header("Location: error.php");
    exit;
}




$full_url = $_SERVER['REQUEST_URI'];
$parsed_url = parse_url($full_url);


// Parse the first query string
parse_str($parsed_url['query'], $params);

// Check if 'id' contains extra query string
if (isset($params['id']) && strpos($params['id'], '?') !== false) {
    $u_parts = explode('?', $params['id'], 2);
    $params['id'] = $u_parts[0];
    parse_str($u_parts[1], $extra_params);
    $params = array_merge($params, $extra_params);
}

$code = $_GET['id'] ?? $params['id'];
$code = strtok($code, '?');

//
// http://localhost/meridian_payment_form/pocket/success.php?u=6a7df10f-f22a-46cb-85fd-a667647ece87?reference=PVB01JXWBM97JHM95H18WB3JDAZ8K&status=success
// https://korapay.meridianbet.com/processor/coralpay/webhook?status=success&id=138569898
//

//paymentId

//check if reference exist
$query_ = "SELECT * FROM coralpay_deposit WHERE payment_id='$code'";
$result_ = mysqli_query($con, $query_);
$num_ = $result_->num_rows;
$record = [];

$error = '';

if ($num_ < 1){
    //does not exist, show error
    $error = 'Unable to locate the payment reference';

} else{
    $record = mysqli_fetch_assoc($result_);

    $query_ = "UPDATE coralpay_deposit SET status='COMPLETED' WHERE payment_id='$code'";
    $result_ = mysqli_query($con, $query_);



    $request = json_decode($record['payload'], true);
    $timestamp = round(microtime(true) * 1000);



    // Staging: https://payments-stage.meridianbet.com/proxy/notify/{paymentId}

    // Live: https://prizma.meridianbet.com/proxy/notify/{paymentId}


    //send notification to MERIDIANBET
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://payments-stage.meridianbet.com/proxy/notify/'.$record['payment_id'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'{
        "paymentId": "'.$record['payment_id'].'",
        "paymentType": "DEPOSIT",
        "accountId": "'.$record['account_id'].'",
        "amount": '.$record['amount'].',
        "currencyCode": "NGN",
        "createTimestamp": '.$timestamp.',
        "status": "CREATE_TRANSFER",
        "currencyNumericCode": 566,
        "overrideAmount": false,
        "customerParams": {
            "customerBirthdate": "1993-02-08",
            "customerPhone": "'.$request['customerParams']['customerPhone'].'",
            "clientLanguage": "en",
            "customerPersonalId": "",
            "customerEmail": "'.$request['customerParams']['customerEmail'].'",
            "customerFirstName": "'.$request['customerParams']['customerFirstName'].'",
            "currency": "'.$request['customerParams']['currency'].'",
            "customerLastName": "'.$request['customerParams']['customerLastName'].'",
            "paymentClientApp": "'.($request['customerParams']['paymentClientApp'] ?? '').'",
            "customerCountryIso2": "NG",
            "customerCountry": "Nigeria"
        }
    }',
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
    ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    // echo $response;


    //decode response
    $res = json_decode($response, true);
    // print_r($res);

    // Convert to JSON
    $jsonData = json_encode($res);

    $prizma_req= '{
        "paymentId": "'.$record['payment_id'].'",
        "paymentType": "DEPOSIT",
        "accountId": "'.$record['account_id'].'",
        "amount": '.$record['amount'].',
        "currencyCode": "NGN",
        "createTimestamp": '.$timestamp.',
        "status": "CREATE_TRANSFER",
        "currencyNumericCode": 566,
        "overrideAmount": false,
        "customerParams": {
            "customerBirthdate": "1993-02-08",
            "customerPhone": "'.$request['customerParams']['customerPhone'].'",
            "clientLanguage": "en",
            "customerPersonalId": "",
            "customerEmail": "'.$request['customerParams']['customerEmail'].'",
            "customerFirstName": "'.$request['customerParams']['customerFirstName'].'",
            "currency": "'.$request['customerParams']['currency'].'",
            "customerLastName": "'.$request['customerParams']['customerLastName'].'",
            "paymentClientApp": "'.($request['customerParams']['paymentClientApp'] ?? '').'",
            "customerCountryIso2": "NG",
            "customerCountry": "Nigeria"
        }
    }';

    $minified = json_encode(json_decode($prizma_req));

    $query_d = "UPDATE coralpay_deposit SET notify_prizma_req='$minified', notify_prizma_res_notif='$jsonData' WHERE payment_id='$code'";
    $result_d = mysqli_query($con, $query_d);
}
?>




<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

<center>

<br><br>
<img src="3459959.png" style="max-width: 60px;margin-top: 20px" alt="">
<h1>Deposit successful</h1>
<small class="text-muted">Please close this window</small><br><br><br><br>
<div class="text-center">
    <small class="wpoidj">Powered By:</small><br>
    <img src="coralpay_logo.jpeg" style="max-width: 60px;margin-top: 20px" alt="">
</div>
<br><br>

</center> 

<style>
    *{
        font-family: "Montserrat";
    }

    body{
        background: #fff;
    }

    svg{
        display: block;
        margin-top: 10px;
        max-width: 100px;
    }

    .text-muted{
        opacity: .4;
    }
</style>