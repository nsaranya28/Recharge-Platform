<?php

/**
 * Sends a recharge confirmation SMS using Fast2SMS API
 * 
 * @param string $mobile   10-digit mobile number
 * @param string $amount   Recharge amount
 * @param string $operator Mobile operator name
 * @return array           API response
 */
function sendFast2SMS($mobile, $amount, $operator) {
    // REPLACE THIS WITH YOUR ACTUAL FAST2SMS API KEY
    $apiKey = "3slq********************";
    
    $message = "Success! Your recharge of Rs.$amount for $operator is successful. Thank you for using Smart Recharge.";
    
    $fields = array(
        "route" => "q",
        "message" => $message,
        "language" => "english",
        "numbers" => $mobile,
    );

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://www.fast2sms.com/dev/bulkV2",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($fields),
        CURLOPT_HTTPHEADER => array(
            "authorization: $apiKey",
            "accept: */*",
            "cache-control: no-cache",
            "content-type: application/json"
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        return array("status" => "error", "message" => "cURL Error: " . $err);
    } else {
        return json_decode($response, true);
    }
}
