<?php
require '../../vendor/autoload.php'; // Include the autoload file for Composer dependencies

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

function get_openai_response($message) {
    $api_key = getenv('OPENAI_API_KEY');
    error_log('OpenAI key is available: ' . $api_key);

    if (!$api_key) {
        error_log('OpenAI key is not available');
        // return null;
    }

    $url = 'https://api.openai.com/v1/chat/completions';
    $data = [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            [
                'role' => 'user',
                'content' => $message
            ]
        ],
        'max_tokens' => 250,
        'temperature' => 0.7
    ];

    error_log('OpenAI request data: ' . json_encode($data));
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: Bearer " . $api_key
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data)
    ];

    $ch = curl_init();
    curl_setopt_array($ch, $options);
    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        error_log('cURL error: ' . curl_error($ch));
        curl_close($ch);
        return null;
    }

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_code !== 200) {
        error_log('HTTP error code: ' . $http_code);
        error_log('cURL response: ' . $result);
        curl_close($ch);
        return null;
    }

    $response = json_decode($result, true);
    error_log('OpenAI response: ' . json_encode($response));

    curl_close($ch);

    return $response['choices'][0]['message']['content'] ?? null;
}
