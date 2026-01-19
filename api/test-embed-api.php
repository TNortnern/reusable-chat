<?php

echo "Testing Embed API Endpoints\n";
echo "============================\n\n";

// Test 1: Init endpoint
echo "1. Testing /api/embed/init...\n";
$initData = json_encode(['key' => 'sk_demo_reusable_chat_demo_key_2026']);
$initCh = curl_init('https://api-production-de24c.up.railway.app/api/embed/init');
curl_setopt($initCh, CURLOPT_RETURNTRANSFER, true);
curl_setopt($initCh, CURLOPT_POST, true);
curl_setopt($initCh, CURLOPT_POSTFIELDS, $initData);
curl_setopt($initCh, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$initResponse = curl_exec($initCh);
$initStatus = curl_getinfo($initCh, CURLINFO_HTTP_CODE);
curl_close($initCh);

echo "Status: {$initStatus}\n";
echo "Response: {$initResponse}\n\n";

if ($initStatus === 200) {
    echo "✅ Init endpoint working!\n\n";

    // Test 2: Session endpoint
    echo "2. Testing /api/embed/session...\n";
    $sessionData = json_encode([
        'key' => 'sk_demo_reusable_chat_demo_key_2026',
        'user_id' => 'test_script_user',
        'user_name' => 'Test Script User',
        'user_email' => 'test@script.com'
    ]);

    $sessionCh = curl_init('https://api-production-de24c.up.railway.app/api/embed/session');
    curl_setopt($sessionCh, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($sessionCh, CURLOPT_POST, true);
    curl_setopt($sessionCh, CURLOPT_POSTFIELDS, $sessionData);
    curl_setopt($sessionCh, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $sessionResponse = curl_exec($sessionCh);
    $sessionStatus = curl_getinfo($sessionCh, CURLINFO_HTTP_CODE);
    curl_close($sessionCh);

    echo "Status: {$sessionStatus}\n";
    echo "Response: {$sessionResponse}\n\n";

    if ($sessionStatus === 200) {
        echo "✅ Session endpoint working!\n";
        $session = json_decode($sessionResponse, true);
        if (isset($session['token'])) {
            echo "Token created: " . substr($session['token'], 0, 20) . "...\n";
            echo "Expires: " . ($session['expires_at'] ?? 'Not set') . "\n";
        }
    } else {
        echo "❌ Session endpoint failed with status {$sessionStatus}\n";
    }
} else {
    echo "❌ Init endpoint failed with status {$initStatus}\n";
}
