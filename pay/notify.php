<?php
@session_start();

header('Content-Type: application/json; charset=utf-8');

require "../db/connect.php";

/**
 * 
 * PROCESS
 * 
 * 1. receive data
 * 
 * 2. build data
 * 
 * 3. send data to notify
 * 
 * 4. receive response
 * 
 * 
 */

 


//receive data
$ref = $_POST['id'];
$bank_code = $_POST['bank_code'];
$bank_name = $_POST['bank_name'];
$customer_name = $_POST['customer_name'];
$account_number = $_POST['account_number'];
$acc_name = $_POST['acc_name'];


//get details of transaction
$code = $ref ?? '';
$query_ = "SELECT * FROM korapay_withdrawal WHERE payment_reference='$code'";
$result_ = mysqli_query($con, $query_);
$num_ = $result_->num_rows;
$record = [];

if ($num_ < 1){
    //does not exist, show error

} else{
    $record = mysqli_fetch_assoc($result_);
}


$request = json_decode($record['prizma_request'], true);
$timestamp = round(microtime(true) * 1000);



// Staging: https://payments-stage.meridianbet.com/proxy/notify/{paymentId}

// Live: https://prizma.meridianbet.com/proxy/notify/{paymentId}


//send notification to MERIDIANBET
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://prizma.meridianbet.com/proxy/notify/'.$request['paymentId'],
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
  "paymentId": "'.$request['paymentId'].'",
  "paymentType": "WITHDRAW",
  "accountId": "'.$request['accountId'].'",
  "amount": '.$request['amount'].',
  "currencyCode": "'.$request['currencyCode'].'",
  "createTimestamp": '.$timestamp.',
  "status": "NEW_RESERVATION",
  "customerParams": {
    "customerBirthdate": "1993-02-08",
    "customerPhone": "'.$request['customerParams']['customerPhone'].'",
    "clientLanguage": "en",
    "customerPersonalId": "",
    "customerEmail": "vladapen1@test.com",
    "customerFirstName": "'.$request['customerParams']['customerFirstName'].'",
    "currency": "'.$request['customerParams']['currency'].'",
    "customerLastName": "'.$request['customerParams']['customerLastName'].'",
    "bankId": "",
    "documentType": "",
    "documentValue": "",
    "accountType": "SAVING",
    "accountNumber": "'.$account_number.'",
    "cci": "'.$bank_code.'"
  },
  "processorParams": {
    "plutusSubmitUrl": "'.$request['processorParams']['plutusSubmitUrl'].'",
    "accountNumberRegex": ".*",
    "cciRegex": ".*",
    "test": "test",
    "withdrawUrl": "'.$request['processorParams']['withdrawUrl'].'",
    "banksUrl": "'.$request['processorParams']['banksUrl'].'",
    "plutusBanksUrl": "'.$request['processorParams']['plutusBanksUrl'].'",
    "documentNumberRegex": ".*",
    "clientExternalId": "'.$request['processorParams']['clientExternalId'].'"
  },
  "inputParams": {
    "DOCUMENT_TYPE": "",
    "DOCUMENT_VALUE": "",
    "ACCOUNT_TYPE": "SAVING",
    "ACCOUNT_NUMBER": "'.$account_number.'",
    "NAME_OF_BANK": "'.$bank_name.'",
    "CCI": "'.$bank_code.'"
  },
  "currencyNumericCode": '.$request['currencyNumericCode'].'
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

// Check if 'errorCode' exists and is not empty
if (isset($res['errorCode']) && !empty($res['errorCode'])) {

    $response_ = array(
        "status"=> false,
        "message"=> $res['errorMessage']
    );

    echo json_encode($response_);

} else {
    // NO ERROR, send success

    $response_ = ["status"=> true];
    echo json_encode($response_);

}