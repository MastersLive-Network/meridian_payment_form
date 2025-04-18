<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '', // optional if same domain
    'secure' => true, // Required for SameSite=None
    'httponly' => true,
    'samesite' => 'None'
]);

@session_start();

header('Content-Type: application/json; charset=utf-8');

// PROCESS

/**
 * 
 * 1. receive form data
 * 
 * 2. ensure that form data is complete
 * 
 * 3. encrypt data and send to next page (kora-verify.php)
 * 
*/

$payid = $_POST['payid'];
$bank_code = $_POST['bank_code'];
$bank_name = $_POST['bank_name'];
$customer_name = $_POST['customer_name'];
$account_number = $_POST['acc'];
$resolved_name = $_POST['acc_name'];



if (
    isset($_POST['payid'], $_POST['bank_code'], $_POST['bank_name'], $_POST['customer_name'], $_POST['acc'], $_POST['acc_name']) &&
    !empty($_POST['payid']) &&
    !empty($_POST['bank_code']) &&
    !empty($_POST['bank_name']) &&
    !empty($_POST['customer_name']) &&
    !empty($_POST['acc']) &&
    !empty($_POST['acc_name'])
) {
    // All required fields are present and not empty
    // Proceed with processing

    $data = [
        "payid"=> $payid,
        "bank_code"=> $bank_code,
        "bank_name"=> $bank_name,
        "customer_name"=> $customer_name,
        "account_number"=> $account_number,
        "acc_name"=> $resolved_name,
    ];
    
    
    // Your secret key and initialization vector
    $key = 'kay-meridian-123'; // Must be 16, 24, or 32 bytes
    $iv = openssl_random_pseudo_bytes(16); // Must be 16 bytes for AES-256-CBC
    
    
    
    // Convert array to JSON string
    $plainText = json_encode($data);
    
    
    
    // Encrypt the data
    $encrypted = openssl_encrypt($plainText, 'AES-256-CBC', $key, 0, $iv);
    
    
    
    // Store the IV along with the encrypted data (encode to safely transmit/store)
    $encryptedPayload = base64_encode($iv . $encrypted);
    

    //put data into SESSION
    $_SESSION['xui'] = $encryptedPayload;
    

    // Redirect to another page
    $response = ["status"=> true];
    echo json_encode($response);




} else {
    // Handle missing or empty fields
    // echo "Error: All fields are required.";
    $response = ["status"=> false];
    echo json_encode($response);
}