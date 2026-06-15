<?php
/**
 * Waryono hCaptcha Solver API
 * URL: https://avisocaptchamacro.onrender.com
 */

// CORS Headers
header("Access-Control-Allow-Origin: *");
header("Access-Type: application/json");
header("Content-Type: application/json");

// API Bilgileri
$API_KEY = "1a3d255d8621b16df5ccbe6bcd4a03f5";
$API_BASE = "https://api.waryono.my.id";

function apiRequest($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    if ($method === 'POST' && $data) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        return ['status' => 'error', 'message' => 'CURL Error: ' . $error];
    }

    curl_close($ch);

    $decoded = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['status' => 'error', 'message' => 'JSON Parse Error', 'raw' => $response];
    }

    return $decoded;
}

// Parametreleri al
$action = $_GET['action'] ?? 'status';
$sitekey = $_GET['sitekey'] ?? '20d9dd48-12db-4e63-8fdf-fec11eab6f85';
$domain = $_GET['domain'] ?? 'https://aviso.bz';

// Action: solve
if ($action === 'solve') {
    // 1. Captcha submit et
    $submit = apiRequest("$API_BASE/in.php", 'POST', [
        'apikey' => $API_KEY,
        'methods' => 'hcaptcha',
        'domain' => $domain,
        'sitekey' => $sitekey,
        'json' => 1
    ]);

    if (!isset($submit['status']) || $submit['status'] != 1) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Submit failed', 
            'details' => $submit
        ]);
        exit;
    }

    $captchaId = $submit['request'];

    // 2. Sonucu bekle (max 5 dakika = 60 x 5 saniye)
    for ($i = 0; $i < 60; $i++) {
        sleep(5);

        $result = apiRequest(
            "$API_BASE/res.php?apikey=" . urlencode($API_KEY) . 
            "&action=get&id=" . urlencode($captchaId) . "&json=1"
        );

        if (isset($result['status']) && $result['status'] == 1) {
            echo json_encode([
                'status' => 'success', 
                'token' => $result['request'],
                'captcha_id' => $captchaId
            ]);
            exit;
        }

        if (isset($result['request']) && $result['request'] === 'CAPCHA_NOT_READY') {
            continue; // Beklemeye devam et
        }
    }

    echo json_encode([
        'status' => 'timeout',
        'message' => 'Captcha cozumu zaman asimina ugradi'
    ]);
    exit;
}

// Action: balance
if ($action === 'balance') {
    $result = apiRequest("$API_BASE/balance.php?apikey=" . urlencode($API_KEY) . "&json=1");
    echo json_encode($result);
    exit;
}

// Default: status
echo json_encode([
    'status' => 'ok',
    'service' => 'Waryono hCaptcha Solver',
    'version' => '2.0',
    'actions' => ['solve', 'balance'],
    'usage' => [
        'solve' => '/captcha_solver.php?action=solve&sitekey=XXX&domain=XXX',
        'balance' => '/captcha_solver.php?action=balance'
    ]
]);
