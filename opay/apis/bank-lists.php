<?php

header('Content-Type: application/json'); // <-- JSON response header
header("Access-Control-Allow-Origin: *");

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

$data = [
    "countryCode" => "NG"
];

// Minified JSON
$body = json_encode($data, JSON_UNESCAPED_SLASHES);

// Sign with SHA256withRSA
openssl_sign($body, $signature, $privateKey, OPENSSL_ALGO_SHA256);

// Base64 encode without newlines
$authorization = base64_encode($signature);
$authorization = str_replace("\n", '', $authorization);

// cURL request
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => 'https://liveapi.opaycheckout.com/api/v1/international/banks',
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

if (curl_errno($curl)) {
    echo 'Error:' . curl_error($curl);
}

curl_close($curl);

// Return Opay response as JSON
echo $response;