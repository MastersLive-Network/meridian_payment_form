<?php
//receive the reference and update it to success

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once( "../db/connect.php" );




$full_url = $_SERVER['REQUEST_URI'];
$parsed_url = parse_url($full_url);


// Parse the first query string
parse_str($parsed_url['query'], $params);

// Check if 'u' contains extra query string
if (isset($params['u']) && strpos($params['u'], '?') !== false) {
    $u_parts = explode('?', $params['u'], 2);
    $params['u'] = $u_parts[0];
    parse_str($u_parts[1], $extra_params);
    $params = array_merge($params, $extra_params);
}

$code = $_GET['u'] ?? $params['u'];
$code = strtok($code, '?');

//
// http://localhost/meridian_payment_form/pocket/success.php?u=6a7df10f-f22a-46cb-85fd-a667647ece87?reference=PVB01JXWBM97JHM95H18WB3JDAZ8K&status=success
//



//check if reference exist
$query_ = "SELECT * FROM pocket_deposit WHERE reference='$code'";
$result_ = mysqli_query($con, $query_);
$num_ = $result_->num_rows;
$record = [];

$error = '';

if ($num_ < 1){
    //does not exist, show error
    $error = 'Unable to locate the payment reference';

} else{
    $record = mysqli_fetch_assoc($result_);

    $query_ = "UPDATE pocket_deposit SET pocket_status='COMPLETED' WHERE reference='$code'";
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
    print_r($res);

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

    $query_d = "UPDATE pocket_deposit SET notify_prizma_req='$minified', notify_prizma_res='$jsonData' WHERE reference='$code'";
    $result_d = mysqli_query($con, $query_d);
}
?>


<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

<center>

<br><br>
<h1>Deposit successful</h1>
<small class="text-muted">Please close this window</small><br><br><br><br>
<div class="text-center">
    <small class="wpoidj">Powered By:</small>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 696 263" class="Footer_logo__xLcg0"><path class="logo_svg__cls-1" d="M552.1 234.1c-.5 4.3.7 7.8 3.8 10.4 3.1 2.7 7.5 4 13.1 4h1.8c12.7 0 19.9-4.5 22-15.9l2.8-15.8c.9-4.9-.2-9-3.1-12.2-2.9-3.2-7.3-4.8-13.1-4.8h-1.5c-13.4 0-19.7 5-21.2 15.6h11.1c.7-3.8 3.6-5.7 9-5.7s7.8 2.9 6.9 7.5l-.7 4.1c-2.1-1.2-5.6-1.8-10.7-1.8-12.3 0-19 4.5-20.2 13.4l-.2 1.1Zm28.9-.2c-.7 3.8-4.3 6.3-9.7 6.3s-8.3-2-7.4-6.3l.2-.8c.9-4.3 3.5-6.2 9.6-6.2s8.3 1.9 7.5 6.2v.7Zm50.7-3.4c-.9 5.3-4.3 8-10.1 8s-8.2-2.7-7.3-8l2.2-12.2c.9-5.3 4.3-8 10.1-8s8.2 2.7 7.3 8l-2.1 12.2Zm13.9-12.8c.9-5.2-.2-9.4-3.5-12.8-3.2-3.3-8.1-5-14.6-5-13.8 0-20.6 5.5-22.5 16.9l-8.1 46.1h11.7l3.1-17.7c1.9 1.9 6.3 3.3 11.3 3.3 12.1 0 18.1-5.3 20.2-16.6l2.5-14.2Zm36.2 12.8c-.9 5.3-4.3 8-10.1 8s-8.2-2.7-7.3-8l2.2-12.2c.9-5.3 4.3-8 10.1-8s8.2 2.7 7.3 8l-2.1 12.2Zm13.9-12.8c.9-5.2-.2-9.4-3.5-12.8-3.2-3.3-8.1-5-14.6-5-13.8 0-20.6 5.5-22.5 16.9l-8.1 46.1h11.7l3.1-17.7c1.9 1.9 6.3 3.3 11.3 3.3 12.1 0 18.1-5.3 20.2-16.6l2.5-14.2Z" fill="#9E9E9E"></path><g class="logo_svg__cls-2" fill="#753FF6"><path class="logo_svg__cls-1" d="M56 32.4c-37.5 0-56 17.4-56 46.4v126.9h32.2v-48.8c6.6 6 17.7 8.9 33.2 8.9 31.7 0 46.9-16.6 46.9-45.6V78.7c0-29-18.5-46.4-56.2-46.4Zm24.1 83.9c0 14.7-8.1 22.2-24.1 22.2s-23.8-7.5-23.8-22.2V82.8c0-14.7 7.9-22.1 23.8-22.1s24.1 7.5 24.1 22.1v33.5ZM312.1 32.4c-35.8 0-54.3 18.7-54.3 46v41.7c0 27.3 18.5 45.8 54.1 45.8s53.5-18.5 53.5-45.8v-7.2h-30.9v3.8c0 13.8-7.5 20.9-22.5 20.9s-22.6-7-22.6-21.3V82c0-14.3 7.7-21.3 22.8-21.3s22.4 7 22.4 20.9v4.3h30.9v-7.4c0-27.3-18.6-46-53.3-46Z"></path><path data-name="Path" class="logo_svg__cls-1" d="M492.4 34.5h-36l-27.3 47.7h-16.2V0h-32.1v164h32.1v-54.5h15.2l29.1 54.5h37.1l-38.1-66.9 36.2-62.6z"></path><path data-name="Shape" class="logo_svg__cls-1" d="M554.9 32.4c-37.3 0-56.7 17.7-56.7 46v42c0 28.3 18.9 45.6 56.7 45.6s56.4-16 56.4-38.8v-4.3h-31.1v1.1c0 10.4-8.3 15.8-25.1 15.8s-25.8-7.2-25.8-21.5v-10.2h82V78.5c0-28.8-19.2-46-56.4-46Zm25.3 54.7h-50.9v-6.6c0-14.7 8.5-21.9 25.6-21.9s25.3 7.2 25.3 21.7v6.9Z"></path><path data-name="Path" class="logo_svg__cls-1" d="M681.9 136.1c-16.8 0-22.5-5.3-22.5-24.9V60.3h35.1V34.5h-35.1V7.9h-32.2v105.4c0 36.4 13.2 50.9 47.3 50.9h20.9v-28.1H682Z"></path><path data-name="Shape" class="logo_svg__cls-1" d="M217.4 34.6c-11.1-1.4-22.3-2.1-33.6-2.1-4.8-.2-11.1.3-17.5.8-5.5.4-11.1.8-16.5 1.8-9.8 1.9-19.1 13.2-20.3 24.6-1.8 17.5-2.3 35-1.7 52.6.4 12.8 4.9 23.2 13.3 31.5 3.4 3.3 7.1 6.2 11.1 8.7 7.4 4.7 15.3 8.2 23.2 11.4 4.9 2 10.4 2.4 15.5 1 8-2.1 15.3-6.1 22.7-10 5.1-2.7 9.9-6.1 14.1-10.1 7.4-7 12.5-15.7 13.8-26.8.5-4.3.6-8.7.8-13.1.3-14.6-.4-29.1-1.8-43.7-1.3-14.1-10.9-25.2-23.2-26.7Zm-66.8 17.1c.4-.5.9-1 1.4-1.3.4-.2.8-.5 1.2-.6l.6-.3c.4-.2 1-.3 1.5-.5.3 0 .6-.2.9-.2.5 0 1.2-.1 1.8-.2h2.1c8.5.2 16.8.4 24.9.7 8.1-.3 16.5-.5 24.9-.7h2.1c.6 0 1.2.2 1.8.2.3 0 .6.2.9.2.5.1 1 .3 1.5.5.2 0 .4.2.6.3l1.2.6c.5.4 1 .8 1.4 1.3 1.8 2.3 2 5.6.8 9.5-.2.7-.3 1.3-.5 2-.3 1.1-.5 2.3-.6 3.5-.2 1.1-.3 2.3-.3 3.4v4.8c-1.2.3-2.5.6-3.9.9-.2-2.2-.3-4.3-.2-6.5 0-1.1.1-2.3.3-3.4.1-1.2.4-2.3.6-3.5 0-.3.1-.6.2-.8.7-2.7.4-5.1-1-6.8-1.4-1.9-3.9-2.8-7.2-2.7-7.5.2-15 .5-22.5.8-7.5-.3-15-.6-22.5-.8-3.3 0-5.8.8-7.2 2.7-1.4 1.7-1.7 4.1-1 6.8 0 .3 0 .5.2.8.3 1.1.5 2.3.6 3.5.2 1.1.3 2.3.3 3.4 0 2.2 0 4.3-.2 6.5-1.4-.3-2.7-.6-3.9-.9v-4.8c0-1.1-.1-2.3-.3-3.4-.1-1.2-.3-2.3-.6-3.5-.2-.7-.3-1.3-.5-2-1.3-3.9-1.1-7.2.7-9.5Zm8.4 17.2c4.3 0 7.6-.8 10-2.4 1.6-.9 2.9-2.4 3.5-4.2.5-1.2.6-2.6.4-3.9 0-.5-.2-.9-.3-1.4l-.6-1.5c4.1.2 8.5.3 12.9.5 4.4-.2 8.8-.3 12.9-.5l-.6 1.5c-.1.5-.2.9-.3 1.4-.2 1.3 0 2.6.4 3.9.6 1.8 1.9 3.2 3.5 4.2 2.4 1.6 5.8 2.4 10 2.4 0 2.5 0 4.9.3 7.4-8.7 1.6-17.5 2.2-26.3 1.9-8.8.3-17.6-.4-26.3-1.9.2-2.4.3-4.9.2-7.4Zm-.9-7.4v-.4c-.4-1.7-.2-3.1.6-4.2.9-1.1 2.5-1.7 4.6-1.6h4.9c.7 1.3 1.1 2.6 1.1 4 0 1-.3 2-.9 2.8-.3.5-.8 1-1.3 1.3-1.8 1.3-4.6 2-8.3 2v-.3c-.2-1.2-.4-2.4-.7-3.6Zm53.3 3.6v.3c-3.7 0-6.6-.7-8.3-2-.5-.3-.9-.8-1.3-1.3-.6-.8-.9-1.8-.9-2.8 0-1.4.5-2.7 1.1-3.8h4.9c2.1-.2 3.7.4 4.6 1.5.9 1 1 2.5.6 4.2v.4c-.3 1.2-.6 2.4-.7 3.6Zm18.3 20.7c-.1 2.4-1.6 4.4-3.9 5.2-26.6 7.8-54.9 7.8-81.5 0-2.2-.8-3.8-2.8-3.9-5.2v-5.9c0-2.2 1.7-3.4 3.9-2.8 26.6 7.8 54.9 7.8 81.5 0 2.2-.6 3.9.6 3.9 2.8v5.9Z"></path></g></svg>
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