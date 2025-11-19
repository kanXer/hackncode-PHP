<?php
$success_message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $name = trim(substr(filter_var($_POST['name'] ?? '', FILTER_SANITIZE_STRING), 0, 200));
    $email = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
    $message = trim(filter_var($_POST['message'] ?? '', FILTER_SANITIZE_STRING));

    if (!$name) $errors[] = 'Name is required.';
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (!$message) $errors[] = 'Message required.';

    if (empty($errors)) {
        $text = "📩 New Contact Message\nName: " . htmlspecialchars($name) . "\nEmail: " . htmlspecialchars($email) . "\nMessage: " . htmlspecialchars($message);
        $msg = urlencode($text);
        $vercel_url = "https://tele-bridge.vercel.app/api/send?msg=$msg";

        $response = file_get_contents($vercel_url);
        if ($response === FALSE) {
          echo "<script>alert('❌ ERROR: Could not connect to Server.');</script>";
        } else {  
                  $json_response = json_decode($response, true);
                  if (!empty($json_response['success'])) {
                    echo "<script>alert('✅ Thank you! Your message was sent.');</script>";
                    // Optional: page reload after success
                    echo "<script>window.location.href = '" . $_SERVER['PHP_SELF'] . "';</script>";
                    exit;
                  } else  {
                          $error_description = $json_response['error'] ?? 'Unknown API Error';
                          echo "<script>alert('❌ ERROR: API rejected the message. Reason: " . addslashes($error_description) . "');</script>";
                          }
                }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Hi, I'm Sahil Srivastava (KanXer) – Full-stack developer & designer building clean, modern web experiences.">
<meta name="keywords" content="Sahil Srivastava, KanXer, Full-stack Developer, Web Designer, Portfolio, HTML, CSS, JavaScript, PHP, React, Next.js">
<meta name="author" content="Sahil Srivastava">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:title" content="Sahil Srivastava (KanXer) | Portfolio">
<meta property="og:description" content="Full-stack developer & designer building clean, modern web experiences.">
<meta property="og:image" content="https://hackncode.live/logo.jpeg">
<meta property="og:url" content="https://hackncode.live/">
<meta property="og:site_name" content="Sahil Srivastava | KanXer">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Sahil Srivastava (KanXer) | Portfolio">
<meta name="twitter:description" content="Full-stack developer & designer building clean, modern web experiences.">
<meta name="twitter:image" content="https://hackncode.live/logo.jpeg">

<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' rx='20' ry='20' fill='%23050705'/%3E%3Ctext x='50' y='63' font-size='42' font-family='Inter' text-anchor='middle' fill='%2314b866' font-weight='700'%3ESKS%3C/text%3E%3C/svg%3E">
<title>Sahil Srivastava (KanXer) | Portfolio & Contact</title>
<script async custom-element="amp-auto-ads"
        src="https://cdn.ampproject.org/v0/amp-auto-ads-0.1.js">
</script>
<link rel="stylesheet" href="assets/style.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="assets/header.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="footer.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="dark">
<?php include 'components/header.php'; ?>
<div class="container">
  

  <!-- HERO -->
  <section class="hero">
    <div class="card intro">
      <h2 id="about">Hi, I'm <span style="color:var(--accent)">Sahil Srivastava (KanXer)</span></h2>
      <p>I'm a full-stack developer and designer building clean, modern web experiences.</p>
      <div class="skills">
        <div class="skill">PHP</div>
        <div class="skill">HTML/CSS</div>
        <div class="skill">JavaScript</div>
        <div class="skill">Next.js</div>
        <div class="skill">Python</div>
        <div class="skill">MySQL</div>
        <div class="skill">Nmap</div>
        <div class="skill">Metasploit</div>
        <div class="skill">Burp Suite</div>
        <div class="skill">Bug Hunting </div>
      </div>
      <div class="cta"><a class="btn" href="res_preview.php">Resume</a></div>
    </div>

    <aside class="card profile">
      <div class="avatar"><img src="logo.jpeg" alt="Sahil Srivastava"></div>
      <div class="meta">
        <p><strong>Sahil Srivastava (KanXer)</strong></p>
        <p>India • Black Hat • Open to work</p>
      </div>
      <a class="btn" href="#projects">View Projects</a>
    </aside>
  </section>
  <amp-auto-ads type="adsense"
        data-ad-client="ca-pub-8088557937249669">
  </amp-auto-ads>
  <!-- PROJECTS -->
  <section id="projects" class="projects">
    <h1>Projects</h1>
    
    <a href="/phone_info.php" class="project">
      <h3>Phone & Email OSINT</h3>
      <p>Search public OSINT data (via LeakOSINT API). Tech: PHP.</p>
    </a>
    
    <a href="https://github.com/kanXer/The-Ultimate-Weapon" target="_blank" class="project">
      <h3>The-Ultimate-Weapon</h3>
      <p>Python-based tool to create EXE or binary using Telegram bot.</p>
    </a>
    
    <a href="https://github.com/kanXer/" target="_blank" class="project">
      <h3>My Github</h3>
      <p>Hi, I’m @Sahil Srivastav. Open to collaboration.</p>
    </a>
    <a href="/instagram" target="_blank" class="project">
      <h3>Instagram Followers Increaser</h3>
      <p>🚀Boost Your Instagram Reach instantly- get real, organic Followers in no time</p>
    </a>
  </section>


  <!-- CONTACT FORM COMPONENT -->
  <?php include 'components/contact_form.php'; ?>
  <amp-auto-ads type="adsense"
        data-ad-client="ca-pub-8088557937249669">
  </amp-auto-ads>
  <?php include 'components/footer.php'; ?>

</div>
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8088557937249669"
     crossorigin="anonymous"></script>
</body>
</html>
