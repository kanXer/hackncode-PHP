<?php
// ==================== CONFIG ====================
define('API_TOKEN', '8567607096:YfufQS9F');
define('LANG', 'ru');
define('LIMIT', 600);

// Use original endpoint (works)
define('API_BASE_URL', 'https://leakosintapi.com/');

function e($s) { 
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); 
}

function call_phone_api($query) {
    $payload = [
        'token'   => API_TOKEN,
        'request' => $query,
        'limit'   => LIMIT,
        'lang'    => LANG
    ];

    $ch = curl_init(API_BASE_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
        CURLOPT_TIMEOUT        => 25
    ]);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false)
        return ['error' => "cURL Error: $err"];

    if ($code !== 200)
        return ['error' => "Invalid response (HTTP $code)", 'raw' => $response];

    $data = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE)
        return ['error' => "Invalid JSON format", 'raw' => $response];

    return $data;
}

$query   = '';
$error   = '';
$results = null;
$consent = false;
$mode    = $_POST['mode'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $query   = trim($_POST['query'] ?? '');
    $consent = isset($_POST['consent']) && $_POST['consent'] === '1';

    // basic validations
    if (!$consent) {
        $error = '⚠️ Please confirm legal consent before searching.';
    } elseif ($query === '') {
        $error = '📱 Enter a value to lookup.';
    } else {

        if ($mode === 'phone') {
            if (!preg_match('/^\+91\d{10}$/', $query)) {
                $error = '📵 Invalid phone number. Use +91 followed by 10 digits.';
            }
        } elseif ($mode === 'email') {
            if (!filter_var($query, FILTER_VALIDATE_EMAIL)) {
                $error = '📧 Invalid email address.';
            }
        } elseif ($mode === 'id') {
            if (strlen($query) < 6) {
                $error = '🆔 ID number must be at least 6 characters.';
            }
        }

        if ($error === '') {
            $api = call_phone_api($query);

            if (isset($api['error'])) {
                $error   = $api['error'];
                $results = $api['raw'] ?? null;
            } else {
                $results = $api;
            }
        }
    }

    // 🔥 IMPORTANT: No auto-refresh!
    // (Removed header redirect completely)
}
?>
<!doctype html>
<html lang="hi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Phone, Email & ID Lookup — KanXer OSINT</title>
<script async custom-element="amp-auto-ads"
        src="https://cdn.ampproject.org/v0/amp-auto-ads-0.1.js">
</script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="/assets/footer.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="/assets/style.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="/assets/header.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="/assets/phoneinfo.css?v=<?php echo time(); ?>">

<link rel="icon" type="image/svg+xml"
href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' rx='20' ry='20' fill='%23050705'/%3E%3Ctext x='50' y='63' font-size='42' font-family='Inter' text-anchor='middle' fill='%2314b866' font-weight='700'%3ESKS%3C/text%3E%3C/svg%3E">

</head>

<body class="dark">

<?php include 'components/header.php'; ?>

<div class="phone-info-page">

    <h2 class="osint-title" style="text-align:center;margin-bottom:10px;font-weight:700;">
        📞 Phone & Email Lookup
        <p style="text-align:center;color:#a8b3a6;">
            Search OSINT public data from multiple APIs.
        </p>
    </h2>
  <amp-auto-ads type="adsense"
        data-ad-client="ca-pub-8088557937249669">
  </amp-auto-ads>
    <div class="lookup-card container">
        <form method="post" class="lookup-form">

            <div class="lookup-radio">
                <label><input type="radio" name="mode" value="phone" <?= $mode === 'phone' ? 'checked' : '' ?>> Phone</label>
                <label><input type="radio" name="mode" value="email" <?= $mode === 'email' ? 'checked' : '' ?>> Email</label>
                <label><input type="radio" name="mode" value="id" <?= $mode === 'id' ? 'checked' : '' ?>> ID</label>
            </div>

            <input type="text" name="query" class="lookup-input"
                placeholder="Select Phone, Email or ID"
                value="<?= e($query) ?>">

            <label class="lookup-checkbox">
                <input type="checkbox" name="consent" value="1" <?= $consent ? 'checked' : '' ?>>
                I confirm this search is for legal and educational purposes only.
            </label>

            <button class="btn lookup-btn" type="submit">🔍 Search</button>
        </form>
        <amp-auto-ads type="adsense" data-ad-client="ca-pub-8088557937249669"></amp-auto-ads>
    </div>

    <?php if ($error): ?>
        <div class="api-error-box"><?= e($error) ?></div>
    <?php endif; ?>

    <!-- ⭐ Result Output -->
    <?php if ($results && isset($results['List'])): ?>
        <?php foreach ($results['List'] as $db => $content): ?>
            <div class="card project lookup-result-card">
                <h3>📁 Database: <?= e($db) ?></h3>

                <?php foreach ($content['Data'] as $entry): ?>
                    <div class="lookup-table-container">
                        <?php if (is_array($entry)): ?>
                            <table class="lookup-table">
                                <?php foreach ($entry as $key => $value): ?>
                                    <tr>
                                        <th><?= e($key) ?></th>
                                        <td><?= e(is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<?php include 'components/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {

    const radios = document.querySelectorAll('input[name="mode"]');
    const input  = document.querySelector('input[name="query"]');

    radios.forEach(r => {
        r.addEventListener('change', () => {
            input.value = "";
            document.querySelectorAll(".lookup-result-card").forEach(card => card.remove());

            let err = document.querySelector(".api-error-box");
            if (err) err.remove();

            if (r.value === 'phone') {
                input.value = '+91';
            } else if (r.value === 'email') {
                input.placeholder = "email@example.com";
            } else {
                input.placeholder = "Enter ID number (Aadhaar / PAN / DL)";
            }
        });
    });
});
</script>
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8088557937249669" crossorigin="anonymous"></script>
</body>
</html>
