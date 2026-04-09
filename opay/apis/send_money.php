<?php

header("Access-Control-Allow-Origin: *");

require_once "../../db/connect.php";


/*

This is called from Java springboot
/withdraw request approval

*/

// PRIVATE KEY (MOVE TO ENV IN PRODUCTION)
$privateKey = <<<EOD
-----BEGIN PRIVATE KEY-----
MIIEuwIBADANBgkqhkiG9w0BAQEFAASCBKUwggShAgEAAoIBAQDo5mLZywjWApXi
5G0gRJ9TzIzSvep+X9LvSfd4yZm0Shi8YcZDAlatHLDmLoi2MwTfHdYFrW1e7P3H
q/f0k4WSJWHDkiBzcwmB9eRkOFIhWJbxn3JwbkZXvSoeZTT81IuONwUBVx0hdQqV
Dbcqv7Zms7R1uK5o5U1QvzWZYryWIbtcGNi4o7NrPuM/WJ1TBoWwhg7VoBH5peuy
DIDZUdjdJLqf570vPUBorAU63k1mYnGzy6w54mNigP3Vwo+/b6q1HPEZbsiiVSCk
72S/grdVex1mN/IIjaSJZOj2snfcGuqYQkk9VY1Wp2lM1a5thomKvsTL2YMmZksT
Af+vAz0NAgMBAAECgf8BEVGHMM8MIXXmDhmUIdqnytMml/k/r9z0vRYvEs7mH1oh
M21MytoSzUn3cx+1+sTnNO8OejF0GYAhU99ZaXJZcC+kJD9Lb8pCcw68HiXj1SM6
1+biU9cIwqpu+wCtNcclbjpDLp9+WIOYXaB3+R76bsmGWEi4uoAm40fuAfDfSbW8
nv87uUeIf4ZNayIoF8Wmhu2CKIlzpmyz5v9pvCsfVKk0ztuNTfsfXGoa7lyTarXu
Qztk+ACmWPfohuPa+LDIAp5WlfUcOol9i64n9D2/2iI5wENav6BUVeEd13buQAZb
zgwJLPJDjIPO+qo2EZSjZYiDVkeIeMQuTJUz69UCgYEA+VuTQU27suViaebTLPOF
YJlg1owCQyZ//KrbPE0K9LkU50iU2VP3GYjer5XK8XoE1TmOjoCp69XnrPYRZZ6T
0WBRybIL7hPb4QPblgnxXWwfiOEvEiUmu2a8+LePeCUlb/nr7r0fJjmuYmCTY2HT
AbTaH+/xa2RlCaegajim/DMCgYEA7xqU8CjooPJGNkcfyu5FNhpY8xRNm8ymN6bU
sDEftjnWk0qTxbEgQuBUxg/qNJ3TV+3xxNRJXpccXrkemEF56rQ+HPpB9ww4RNwZ
++DmupMk0dfORLqhiRJgMc+402BBvieZD/g530/K3zzSdqMJ/xKtx2zi6jgcanzZ
FJHrob8CgYEAi92pwz9uwPGZOf2XBeeyMHTXtH/j5PZ7Y6YSQsiUFKCb8P7tPtmy
CEiVX7eNldTzUQZvx86zgO0CfimnqHBCSXbVaWTM/EV3V8dqK8Z39AbpyUVFuc/M
4eDGrluHxcRQM3bjt42tIyvHfLbe9Sexy4s9rhxQNgSiB8BWYj5Uq7ECgYBwnVF2
x53BaDqfh+I2fwDEGaa5Xl+rOLk0zvOvxINOHXGtz9tHqkQqm2PyIT7K52bKLDzJ
2r5vubZX+tKpHXWhkKEMnuYAyJWcARqP4n5pc7JMz1rMTiaU273I2DASBm0QdbAG
sH/5aKiBejEaRXII3DBTFDrP2/uuP/0yTgPwGwKBgG5niyBPETGny4gxN1PrYcmg
IFx+RGPc76LOozWvxC2LqzvolDKOIIOuVEatyFDb76nFa8EmnLsG6PF22pmn+L7K
8e+73XBHhqkoBtuBkcLnLRySoT9vdFAGTTbOBu6ryvn+iV6/RjdDCXe2BeufS91a
NjzGPJuyQ1Nks/mh/4x7
-----END PRIVATE KEY-----
EOD;

// VALIDATE INPUT 
$required = ['payment_id'];

foreach ($required as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        echo json_encode([
            "status" => false,
            "message" => "$field is required"
        ]);
        exit;
    }
}

// SANITIZE INPUT
$payment_id     = trim($_POST['payment_id']);

//find amount
$query = "SELECT * FROM opay_withdrawal WHERE payment_id='$payment_id'";

$result = mysqli_query($con, $query);
$num = mysqli_num_rows($result);
$amount = 0;

if ($num<1){
    exit;
}

$rr = mysqli_fetch_assoc($result);
$amount = (int) $rr['amount'];

$payment_reference = trim($rr['payment_reference']);
$account_number = trim($rr['account_number']);
$bank_code = trim($rr['bank_code']);
$account_name = trim($rr['account_name']);
$account_id = trim($rr['account_id']);

$final_status = '';


// OPTIONAL: amount (default fallback)
$amount = (int) ($amount * 100); // in kobo

// GENERATE REFERENCE
$reference = $payment_reference;

// REQUEST PAYLOAD
$data = [
    "country" => "NG",
    "merchantOrderNo" => $reference,
    "metaData" => [
        "accountNo" => $account_number,
        "accountName" => $account_name,
        "accountBankCode" => $bank_code
    ],
    "amount" => (int) $amount,
    "currency" => "NGN",
    "payoutType" => "BankTransfer",
    "notifyUrl" => "https://korapay.meridianbet.com/processor/meridian_payment_form/opay/apis/withdrawal_webhook.php",
    "language" => "en",
    "remark" => "Withdrawal for payment_reference: " . $payment_reference
];

// MUST MATCH EXACT FORMAT
$body = json_encode($data, JSON_UNESCAPED_SLASHES);

// SIGNATURE (SHA256withRSA)
$signature = '';
openssl_sign($body, $signature, $privateKey, OPENSSL_ALGO_SHA256);

// BASE64 ENCODE
$authorization = base64_encode($signature);
$authorization = trim($authorization);

// SEND REQUEST TO OPAY
$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => 'https://liveapi.opaycheckout.com/api/v1/international/payout/createSingleOrder',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $body,
    CURLOPT_HTTPHEADER => [
        'MerchantId: 256625012791839',
        'Content-Type: application/json',
        'Authorization: Bearer ' . $authorization
    ],
]);

$response = curl_exec($curl);

// ERROR HANDLING
if (curl_errno($curl)) {
    $error = curl_error($curl);
    echo json_encode([
        "status" => false,
        "error" => $error
    ]);

    //notify db
    $query_ = "UPDATE opay_withdrawal SET status='CURL_ERROR', error_msg='$error' WHERE payment_reference='$payment_reference'";
    $result_ = mysqli_query($con, $query_);

    // notify PRIZMA
    $final_status = 'FAILED';
}

curl_close($curl);

// RETURN RESPONSE
// echo $response;

// PARSE RESPONSE
$res = json_decode($response, true);

if (!$res) {
    //notify db
    $query_ = "UPDATE opay_withdrawal SET status='CURL_ERROR', error_msg='Invalid response from payment gateway',account_number='$account_number', bank_code='$bank_code', account_name='$account_name' WHERE payment_reference='$payment_reference'";
    $result_ = mysqli_query($con, $query_);

    // notify PRIZMA
    $final_status = 'FAILED';
}

// HANDLE RESPONSE
if ($res['code'] === "00000") {

    $status = $res['data']['orderStatus'];
    $orderNo = $res['data']['orderNo'];
    $reference = $res['data']['reference'];

    //success
    $query_ = "UPDATE opay_withdrawal SET status='SUCCESS', account_number='$account_number', bank_code='$bank_code', account_name='$account_name',order_no='$orderNo', reference='$reference', order_status='$status' WHERE payment_reference='$payment_reference'";
    $result_ = mysqli_query($con, $query_);

    if ($status === "INITIAL") {
        // "Withdrawal Successful"
        $final_status = 'CREATE_TRANSFER';
    } else {
        // "Withdrawal successful"
        // update for an inconsistent response
        $final_status = 'CREATE_TRANSFER';
    }

} else {
    $err = $res['message'] ?? "Transaction failed";
    $query_ = "UPDATE opay_withdrawal SET status='SUCCESS', account_number='$account_number', bank_code='$bank_code', account_name='$account_name',error_msg='$err' WHERE payment_reference='$payment_reference'";
    $result_ = mysqli_query($con, $query_);

    // "Transaction failed"
    $final_status = 'FAILED';
}




// SEND NOTIFICATION TO PRIZMA FOR FINAL STATUS
/* Notify Prizma */


// Staging: https://payments-stage.meridianbet.com/proxy/notify/{paymentId}

// Live: https://prizma.meridianbet.com/proxy/notify/{paymentId}

$json = file_get_contents("banks.json");
$banks = json_decode($json, true);
$bank_name = $banks[$bank_code] ?? "Unknown Bank";

$paymentId = $payment_id;
$accountId = $rr['account_id'];
$amount__ = $rr['amount'];
$prizma_request = $rr['prizma_request'];
$p_request = json_decode($prizma_request, true);
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
"status": "'.$final_status.'",
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
exit;