<?php

header("Access-Control-Allow-Origin: *");

require_once "../../db/connect.php";

/*

! ------------ !

This is the processor of the OPAY withdrawal form 
and it is meant to notify PRIZMA of NEW_RESERVATION status 
and exit if all fields were entered by user.

! ------------ !

*/

// VALIDATE INPUT
$required = ['payment_reference', 'account_number', 'bank_code', 'account_name'];

foreach ($required as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        echo json_encode([
            "status" => false,
            "message" => "$field is required"
        ]);
        exit;
    }
}

$new_status = '';

// SANITIZE INPUT
$payment_reference = trim($_POST['payment_reference'] ?? '');
$account_number    = trim($_POST['account_number'] ?? '');
$bank_code         = trim($_POST['bank_code'] ?? '');
$account_name      = trim($_POST['account_name'] ?? '');


if (
    !empty($payment_reference) &&
    !empty($account_number) &&
    !empty($bank_code) &&
    !empty($account_name)
) {
    $new_status = 'SUCCESS';
} else {
    $new_status = 'FAILED';
}

//find amount
$query = "SELECT * FROM opay_withdrawal WHERE payment_reference='$payment_reference'";

$result = mysqli_query($con, $query);
$num = mysqli_num_rows($result);
$amount = 0;

if ($num<1){
    exit;
}

$rr = mysqli_fetch_assoc($result);
$amount = (int) $rr['amount'];

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


//
//
//
// Remove sending to Opay
//
//
//

$res = [
    'code' => "99999", // default (failed)
    'message' => "Some fields are not correct",
    'data' => [
        'orderStatus' => "INITIAL",
        'orderNo' => strtoupper(substr(md5(uniqid()), 0, 7)),
        'reference' => $payment_reference,
    ]
];

if ($new_status === 'SUCCESS') {
    $res['code'] = "00000";
    $res['message'] = "";
}

// HANDLE RESPONSE
if ($res['code'] === "00000") {

    $status = $res['data']['orderStatus'];
    $orderNo = $res['data']['orderNo'];
    $reference = $res['data']['reference'];

    //success
    $query_ = "UPDATE opay_withdrawal SET status='NEW_RESERVATION', account_number='$account_number', bank_code='$bank_code', account_name='$account_name',order_no='$orderNo', reference='$reference', order_status='$status' WHERE payment_reference='$payment_reference'";
    $result_ = mysqli_query($con, $query_);

    if ($status === "INITIAL") {
        echo showUI("pending", "Withdrawal Successful", $rr['amount'], $res['data']);
        //Your withdrawal is being processed
    } else {
        echo showUI("success", "Withdrawal successful", $rr['amount'], $res['data']);
    }

} else {
    $err = $res['message'] ?? "Transaction failed";
    $query_ = "UPDATE opay_withdrawal SET status='FAILED', account_number='$account_number', bank_code='$bank_code', account_name='$account_name',error_msg='$err' WHERE payment_reference='$payment_reference'";
    $result_ = mysqli_query($con, $query_);

    echo showUI("error", $res['message'] ?? "Transaction failed", $rr['amount']);
}



/* Notify Prizma */


// Staging: https://payments-stage.meridianbet.com/proxy/notify/{paymentId}

// Live: https://prizma.meridianbet.com/proxy/notify/{paymentId}

$json = file_get_contents("banks.json");
$banks = json_decode($json, true);
$bank_name = $banks[$bank_code] ?? "Unknown Bank";

$paymentId = $rr['payment_id'];
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
exit;


// UI FUNCTION
function showUI($type, $message, $amount, $data = [])
{
    $color = "#333";
    $bg = "#f5f5f5";
    $img_type = "<img src='../succ.png' />";

    if ($type === "success") {
        $color = "#155724";
        $bg = "#d4edda";
    } elseif ($type === "error") {
        $color = "#721c24";
        $bg = "#f8d7da";
        $img_type = "<img src='../err.png' />";
    } elseif ($type === "pending") {
        $color = "#856404";
        $bg = "#fff3cd";
    }

    $details = "";

    if (!empty($data)) {
        $details .= '<table class="table table-striped table-hover"><tbody>';
        $details .= "<tr><th>Amount</th><td>₦".number_format($amount, 2)."</td></tr>";
        $details .= "<tr><th>Order No</th><td>{$data['orderNo']}</td></tr>";
        $details .= "<tr><th>Reference</th><td>{$data['reference']}</td></tr>";
        $details .= "<tr><th>Status</th><td>{$data['orderStatus']}</td></tr>";
        $details .= "<tr><th>Transaction Date</th><td>".Date('Y-m-d h:iA')."</td></tr>";
        $details .= "</tbody></table>";
    }

    echo "
    <!DOCTYPE html>
    <html>
    <head>
        <title>Withdrawal Status</title>
        <style>
            @font-face {
                font-family: 'koo-light';
                src: url(../../fonts/Montserrat-Light.ttf);
            }
            @font-face {
                font-family: 'koo-bold';
                src: url(../../fonts/Montserrat-Bold.ttf);
            }
            @font-face {
                font-family: 'koo-semibold';
                src: url(../../fonts/Montserrat-SemiBold.ttf);
            }
            *{
                font-family: 'koo-light' !important;
                font-size: 14px !important;
                box-sizing: border-box;
            }
            body {
                font-family: Arial;
                background: #f0f2f5;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
            }
            .table {
                width: 100%;
                border-collapse: collapse;
                font-family: Arial, sans-serif;
            }

            .table th,
            .table td {
                padding: 12px;
                border: 1px solid #dee2e6;
                text-align: left;
            }

            .table thead {
                background-color: #f8f9fa;
                font-weight: bold;
            }

            /* striped rows */
            .table-striped tbody tr:nth-child(odd) {
                background-color: #f2f2f2;
            }

            /* hover effect */
            .table-hover tbody tr:hover {
                background-color: #e9ecef;
                cursor: pointer;
            }
            .card {
                /* background: $bg; */
                /* color: $color; */
                background: '#fff';
                color: '#333';
                padding: 30px;
                border-radius: 5px;
                width: 450px;
                box-shadow: rgba(0, 0, 0, 0.16) 0px 10px 36px 0px, rgba(0, 0, 0, 0.06) 0px 0px 0px 1px;
            }
            h2 {
                margin-bottom: 10px;
                font-weight: bold;
                font-size: 18px !important;
                text-align: center;
                padding: 10px;
                margin-top: -15px;
                font-family: 'koo-bold' !important;
            }
            p {
                margin: 5px 0;
            }
            .img {
                padding: 30px;
                text-align: center;
            }
            th{
                font-family: 'koo-semibold' !important;
            }
            img {
                max-width: 30px;
                margin: 5px !important;
            }
            .logo__{
                max-width: 100px;
                height: auto;
            }
            .text-center{
                text-align: center;
            }
            .p-4{
                padding: 20px
            }
        </style>
    </head>
    <body>
        <div class='card'>
            <div class='text-center p-4'><img src='../opay_transparent.png' class='logo__' alt=''></div>
            <div class='img'>$img_type</div>
            <h2>$message</h2>
            $details
        </div>
    </body>
    </html>
    ";
}