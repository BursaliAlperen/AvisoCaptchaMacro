<?php
/**
 * Waryono hCaptcha Solver API
 * Deploy: Render.com (Web Service)
 */

// CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$API_KEY = "1a3d255d8621b16df5ccbe6bcd4a03f5";
$API_BASE = "https://api.waryono.my.id";

function apiRequest($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    if ($method === 'POST' && $data) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    }
    
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

$action = $_GET['action'] ?? 'status';
$sitekey = $_GET['sitekey'] ?? '20d9dd48-12db-4e63-8fdf-fec11eab6f85';
$domain = $_GET['domain'] ?? 'https://aviso.bz';

if ($action === 'solve') {
    // Submit
    $submit = apiRequest("$API_BASE/in.php", 'POST', [
        'apikey' => $API_KEY,
        'methods' => 'hcaptcha',
        'domain' => $domain,
        'sitekey' => $sitekey,
        'json' => 1
    ]);
    
    if (!isset($submit['status']) || $submit['status'] != 1) {
        echo json_encode(['status' => 'error', 'message' => 'Submit failed']);
        exit;
    }
    
    $captchaId = $submit['request'];
    
    // Poll (max 5 min)
    for ($i = 0; $i < 60; $i++) {
        sleep(5);
        $result = apiRequest("$API_BASE/res.php?apikey=" . urlencode($API_KEY) . "&action=get&id=" . urlencode($captchaId) . "&json=1");
        
        if (isset($result['status']) && $result['status'] == 1) {
            echo json_encode(['status' => 'success', 'token' => $result['request']]);
            exit;
        }
    }
    
    echo json_encode(['status' => 'timeout']);
    exit;
}

if ($action === 'balance') {
    $result = apiRequest("$API_BASE/balance.php?apikey=" . urlencode($API_KEY) . "&json=1");
    echo json_encode($result);
    exit;
}

echo json_encode(['status' => 'ok', 'actions' => ['solve', 'balance']]);
