<?php

header("Access-Control-Allow-Origin: *");

require_once "../../db/connect.php";

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

// SANITIZE INPUT
$payment_reference     = trim($_POST['payment_reference']);
$account_number = trim($_POST['account_number']);
$bank_code      = trim($_POST['bank_code']);
$account_name   = trim($_POST['account_name']);

//find amount
$query = "SELECT * FROM opay_withdrawal WHERE payment_reference='$payment_reference'";

$result = mysqli_query($con, $query);
$num = mysqli_num_rows($result);
$amount = 0;

if ($num>0){
    $rr = mysqli_fetch_assoc($result);
    $amount = (int) $rr['amount'];

}

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

    die(showUI("error", curl_error($curl)));
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

    die(showUI("error", "Invalid response from payment gateway"));
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
        echo showUI("pending", "Your withdrawal is being processed", $res['data']);
    } else {
        echo showUI("success", "Withdrawal successful", $res['data']);
    }

} else {
    $err = $res['message'] ?? "Transaction failed";
    $query_ = "UPDATE opay_withdrawal SET status='SUCCESS', account_number='$account_number', bank_code='$bank_code', account_name='$account_name',error_msg='$err' WHERE payment_reference='$payment_reference'";
    $result_ = mysqli_query($con, $query_);

    echo showUI("error", $res['message'] ?? "Transaction failed");
}


// UI FUNCTION
function showUI($type, $message, $data = [])
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
        $details .= "<tr><th>Order No</th><td>{$data['orderNo']}</td></tr>";
        $details .= "<tr><th>Reference</th><td>{$data['reference']}</td></tr>";
        $details .= "<tr><th>Status</th><td>{$data['orderStatus']}</td></tr>";
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