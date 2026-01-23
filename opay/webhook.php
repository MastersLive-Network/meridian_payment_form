<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("../db/connect.php");

// Ensure request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

// Read raw POST body
$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);

// 1. Confirm payload structure
if (
    !isset($data['payload']) ||
    !isset($data['payload']['reference'])
) {
    http_response_code(400);
    exit;
}

$payload    = $data['payload'];
$reference  = $payload['reference'];

// Encode callback JSON for storage
$callbackJson = json_encode($data, JSON_UNESCAPED_SLASHES);

// 2. Check DB
$stmt = $con->prepare("SELECT * FROM opay_deposit WHERE payment_id = ?");
$stmt->bind_param("s", $reference);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows < 1) {

    $timestamp = date("Ymd_His");
    $filename  = __DIR__ . "/callbacks/{$timestamp}.txt";

    if (!is_dir(__DIR__ . "/callbacks")) {
        mkdir(__DIR__ . "/callbacks", 0777, true);
    }

    file_put_contents($filename, $callbackJson);

    // 4. If it does NOT exist
    http_response_code(200);
    exit;
}

// 3. If it exists
$record = $result->fetch_assoc();

// Save callback data
$stmt = $con->prepare(
    "UPDATE opay_deposit SET callback = ? WHERE payment_id = ?"
);
$stmt->bind_param("ss", $callbackJson, $reference);
$stmt->execute();

// 5. Decide Prizma status
$prizmaStatus = ($payload['refunded'] === true)
    ? "FAILED"
    : "CREATE_TRANSFER";

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
     SET notify_prizma_req = ? 
     WHERE payment_id = ?"
);
$stmt->bind_param("ss", $prizmaReqJson, $reference);
$stmt->execute();

// 7. END
http_response_code(200);
exit;
