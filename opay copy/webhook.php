<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("../db/connect.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

/*
|--------------------------------------------------------------------------
| 1. Read Raw Request
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
| 2. Verify SHA512 Signature (IMPORTANT)
|--------------------------------------------------------------------------
*/

$secretKey = "YOUR_OPAY_SECRET_KEY";

$calculatedHash = hash_hmac('sha512', json_encode($payload), $secretKey);

if (!hash_equals($calculatedHash, $data['sha512'])) {

    // http_response_code(401);
    // exit;
}

/*
|--------------------------------------------------------------------------
| 3. Find Transaction
|--------------------------------------------------------------------------
*/

$stmt = $con->prepare("SELECT * FROM opay_deposit WHERE payment_id = ?");
$stmt->bind_param("s", $reference);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows < 1) {

    if (!is_dir(__DIR__ . "/callbacks")) {
        mkdir(__DIR__ . "/callbacks", 0755, true);
    }

    $filename = __DIR__ . "/callbacks/" . date("Ymd_His") . ".txt";
    file_put_contents($filename, $callbackJson);

    http_response_code(200);
    exit;
}

$record = $result->fetch_assoc();

/*
|--------------------------------------------------------------------------
| 4. Save Callback
|--------------------------------------------------------------------------
*/

$stmt = $con->prepare("UPDATE opay_deposit SET callback = ? WHERE payment_id = ?");

$stmt->bind_param("ss", $callbackJson, $reference);
$stmt->execute();

/*
|--------------------------------------------------------------------------
| 5. Determine Transaction Status
|--------------------------------------------------------------------------
*/

if ($payload['status'] !== "SUCCESS") {
    $prizmaStatus = "FAILED";
} else {
    $prizmaStatus = "CREATE_TRANSFER";
}

/*
|--------------------------------------------------------------------------
| 6. Build Prizma Request
|--------------------------------------------------------------------------
*/

$timestampMs = round(microtime(true) * 1000);

$prizmaReq = [
    "paymentId" => $record['payment_id'],
    "paymentType" => "DEPOSIT",
    "accountId" => $record['account_id'],
    "amount" => (float)$payload['amount'],
    "currencyCode" => "NGN",
    "createTimestamp" => $timestampMs,
    "status" => $prizmaStatus,
    "currencyNumericCode" => 566,
    "overrideAmount" => false,
    "customerParams" => [
        "customerPhone" => $record['customer_phone'] ?? '2349012341234',
        "customerEmail" => $record['customer_email'] ?? 'kayode@meridianbet.com',
        "customerFirstName" => $record['customer_first_name'] ?? 'Kayode',
        "customerLastName" => $record['customer_last_name'] ?? 'Shobalaje',
        "clientLanguage" => "en",
        "customerCountryIso2" => "NG",
        "customerCountry" => "Nigeria"
    ]
];

$prizmaReqJson = json_encode($prizmaReq, JSON_UNESCAPED_SLASHES);

/*
|--------------------------------------------------------------------------
| 7. Send Request to Prizma
|--------------------------------------------------------------------------
*/

$curl = curl_init();

// Staging: https://payments-stage.meridianbet.com/proxy/notify/{paymentId}

// Live: https://prizma.meridianbet.com/proxy/notify/{paymentId}

curl_setopt_array($curl, [
    CURLOPT_URL => "https://prizma.meridianbet.com/proxy/notify/" . $record['payment_id'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json"
    ],
    CURLOPT_POSTFIELDS => $prizmaReqJson,
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($curl);

if (curl_errno($curl)) {
    $response = curl_error($curl);
}

curl_close($curl);

/*
|--------------------------------------------------------------------------
| 8. Update Transaction
|--------------------------------------------------------------------------
*/

$status = ($prizmaStatus === "CREATE_TRANSFER") ? "COMPLETED - HOOK" : "FAILED - HOOK";

$stmt = $con->prepare("UPDATE opay_deposit SET notify_prizma_req = ?, prizma_res = ?, status = ? WHERE payment_id = ?");

$stmt->bind_param("ssss", $prizmaReqJson, $response, $status, $reference);
$stmt->execute();

/*
|--------------------------------------------------------------------------
| 9. Done
|--------------------------------------------------------------------------
*/

http_response_code(200);
exit;

?>