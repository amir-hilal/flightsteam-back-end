<?php
// require '../../vendor/autoload.php'; // Include the autoload file for Composer dependencies


// function get_openai_response($message) {
//     // URL for the local proxy
//     $url = 'http://localhost:3000/api/openai/chat';
//     $data = [
//         'message' => $message
//     ];

//     error_log('OpenAI request data: ' . json_encode($data));
//     $options = [
//         CURLOPT_URL => $url,
//         CURLOPT_RETURNTRANSFER => true,
//         CURLOPT_HTTPHEADER => [
//             "Content-Type: application/json"
//         ],
//         CURLOPT_POST => true,
//         CURLOPT_POSTFIELDS => json_encode($data)
//     ];

//     $ch = curl_init();
//     curl_setopt_array($ch, $options);
//     $result = curl_exec($ch);

//     if (curl_errno($ch)) {
//         error_log('cURL error: ' . curl_error($ch));
//         curl_close($ch);
//         return null;
//     }

//     $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//     if ($http_code !== 200) {
//         error_log('HTTP error code: ' . $http_code);
//         error_log('cURL response: ' . $result);
//         curl_close($ch);
//         return null;
//     }

//     $response = json_decode($result, true);
//     error_log('OpenAI response: ' . json_encode($response));

//     curl_close($ch);

//     return $response['response'] ?? null;
// }
