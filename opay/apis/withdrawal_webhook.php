<?php
require_once("../../db/connect.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

/*
|--------------------------------------------------------------------------
| Read Raw Request
|--------------------------------------------------------------------------
*/

$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);

if (!$data || !isset($data['payload']['reference'])) {
    http_response_code(400);
    exit;
}

$payload = $data['payload'];
$reference = $payload['reference'];

// Encode callback JSON for storage
$callbackJson = json_encode($data, JSON_UNESCAPED_SLASHES);



/*
|--------------------------------------------------------------------------
| Find Transaction
|--------------------------------------------------------------------------
*/

$stmt = $con->prepare("SELECT * FROM opay_withdrawal WHERE payment_reference = ?");
$stmt->bind_param("s", $reference);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0){
    $record = $result->fetch_assoc();
    $p_request = json_decode($record['prizma_request'], true);//string json
    $paymentId = $p_request['paymentId'];
    $accountId = $p_request['accountId'];
    $amount__ = $p_request['amount'];

    
    $stmt = $con->prepare("UPDATE opay_withdrawal SET disburse_response = ? WHERE payment_reference = ?");

    $stmt->bind_param("ss", $callbackJson, $reference);
    $stmt->execute();


    if ($payload['status'] == "successful"){
        // Staging: https://payments-stage.meridianbet.com/proxy/notify/{paymentId}

        // Live: https://prizma.meridianbet.com/proxy/notify/{paymentId}

        $json = file_get_contents("banks.json");
        $banks = json_decode($json, true);
        $bank_code = $record['bank_code'];
        $bank_name = $banks[$bank_code] ?? "Unknown Bank";

        $timestamp = round(microtime(true) * 1000);


        //send notification to MERIDIANBET
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://prizma.meridianbet.com/proxy/notify/'.$paymentId,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
        "paymentId": "'.$paymentId.'",
        "paymentType": "WITHDRAW",
        "accountId": "'.$accountId.'",
        "amount": '.$amount__.',
        "currencyCode": "'.$p_request['currencyCode'].'",
        "createTimestamp": '.$timestamp.',
        "status": "NEW_RESERVATION",
        "customerParams": {
            "customerBirthdate": "1993-02-08",
            "customerPhone": "'.$p_request['customerParams']['customerPhone'].'",
            "clientLanguage": "en",
            "customerPersonalId": "",
            "customerEmail": "vladapen1@test.com",
            "customerFirstName": "'.$p_request['customerParams']['customerFirstName'].'",
            "currency": "'.$p_request['customerParams']['currency'].'",
            "customerLastName": "'.$p_request['customerParams']['customerLastName'].'",
            "bankId": "",
            "documentType": "",
            "documentValue": "",
            "accountType": "SAVING",
            "accountNumber": "'.$record['account_number'].'",
            "cci": "'.$record['bank_code'].'"
        },
        "processorParams": {
            "plutusSubmitUrl": "'.$p_request['processorParams']['plutusSubmitUrl'].'",
            "accountNumberRegex": ".*",
            "cciRegex": ".*",
            "test": "test",
            "withdrawUrl": "'.$p_request['processorParams']['withdrawUrl'].'",
            "banksUrl": "'.$p_request['processorParams']['banksUrl'].'",
            "plutusBanksUrl": "'.$p_request['processorParams']['plutusBanksUrl'].'",
            "documentNumberRegex": ".*",
            "clientExternalId": "'.$p_request['processorParams']['clientExternalId'].'"
        },
        "inputParams": {
            "DOCUMENT_TYPE": "",
            "DOCUMENT_VALUE": "",
            "ACCOUNT_TYPE": "SAVING",
            "ACCOUNT_NUMBER": "'.$record['account_number'].'",
            "NAME_OF_BANK": "'.$bank_name.'",
            "CCI": "'.$record['bank_code'].'"
        },
        "currencyNumericCode": '.$p_request['currencyNumericCode'].'
        }',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);


        // update db
        $stmt = $con->prepare("UPDATE opay_withdrawal SET prizma_response = ? WHERE payment_reference = ?");

        $stmt->bind_param("ss", $response, $reference);
        $stmt->execute();


        // return OK
        http_response_code(200);
        echo json_encode([
            "status" => true,
            "message" => "Webhook received"
        ]);
        exit;
    }

}