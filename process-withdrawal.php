<?php
// Set the content type to JSON
header('Content-Type: application/json');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

require "db/connect.php";

$payment_ref = $_POST['id'];
$bank_code = $_POST['banks'];
$account_number = $_POST['acc'];
$PRIZMA_STAGING_NOTIFY_URL = "https://payments-stage.meridianbet.com/proxy/notify/";

//fetch amount from DB
$query_ = "SELECT * FROM korapay_withdrawal WHERE payment_reference='$payment_ref'";
$result_ = mysqli_query($con, $query_);
$num_ = $result_->num_rows;
$record = [];

if ($num_ < 1){
    //does not exist, show error

    $payload = [
        "status" => "error",
        "message" => "Payment Reference does not exist".$paymemt_ref
    ];

    // Convert to JSON format
    $jsonPayload = json_encode($payload, JSON_PRETTY_PRINT);

    // Output JSON
    echo $jsonPayload;

} else{
    // start of else
    $record = mysqli_fetch_assoc($result_);

    $prizma_req = json_decode($record['prizma_request'], true);

    $url = "https://korapay.meridianbet.com/korapay/disburse";
    $name = ucwords($record['last_name']) . " " . ucwords($record['first_name']);
    $first_name = strtolower($record['first_name']);
    $payload = '{
        "reference": "'.$payment_ref.'",
        "destination": {
            "type": "bank_account",
            "amount": "'.$record['amount'].'",
            "currency": "NGN",
            "narration": "MERIDIANBET: payout",
            "bank_account": {
                "bank": "'.$bank_code.'",
                "account": "'.$account_number.'"
            },
            "customer": {
                "name": "'.$name.'",
                "email": "'.$first_name.'@meridianbet.ng"
            }
        }
    }';

    $data = json_decode($payload, true);// Decode JSON string into a PHP array

    $destinationJson = json_encode($data); // Convert PHP array to JSON


    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
    ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    echo $response;


    //decode response
    // Decode JSON into PHP associative array
    $responseArray = json_decode($response, true);

    // Check if JSON decoding was successful
    if ($responseArray === null) {
        die("Error: Invalid JSON response from KORAPAY disburse endpoint");
    }


    // Get status from response
    $mainStatus = $responseArray["status"]; // "true"


    //compose notify payload
    // Create the payload array for response
    $res_payload = [
        "paymentId" => $prizma_req['paymentId'],
        "paymentType" => "WITHDRAW",
        "accountId" => $prizma_req['accountId'],
        "amount" => $prizma_req['amount'],
        "currencyCode" => "NGN",
        "createTimestamp" => $prizma_req['createTimestamp'],
        "status" => "CREATE_TRANSFER",
        "customerParams" => [
            "customerBirthdate" => "",
            "customerPhone" => $prizma_req['customerParams']['customerPhone'],
            "clientLanguage" => "en",
            "customerPersonalId" => "",
            "customerEmail" => strtolower($prizma_req['customerParams']['customerFirstName'])."@meridianbet.ng",
            "customerFirstName" => $prizma_req['customerParams']['customerFirstName'],
            "currency" => $prizma_req['customerParams']['currency'],
            "customerLastName" => $prizma_req['customerParams']['customerLastName'],
            "bankId" => $bank_code,
            "documentType" => "",
            "documentValue" => " ",
            "accountType" => "SAVING",
            "accountNumber" => $account_number,
            "cci" => ""
        ],
        "currencyNumericCode" => 604,
        "errorMessage" => "",
        "overrideAmount" => false
    ];

    if ($mainStatus != "true"){
        //FAILED
        $res_payload['status'] = "FAILED";
    }

    $priz_response = json_encode($res_payload);

    //update request and response to db
    $query_ = "UPDATE korapay_withdrawal SET disburse_request = '$destinationJson', disburse_response='$response', prizma_response = '$priz_response' WHERE payment_reference='$payment_ref'";
    $result_ = mysqli_query($con, $query_);



    //call notify endpoint
    // Convert the PHP array to JSON
    $json_payload = json_encode($res_payload);


    //return response
    // echo json_encode($json_payload, JSON_PRETTY_PRINT);

    // end of else
}