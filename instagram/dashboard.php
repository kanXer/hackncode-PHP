<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: index.php');
    exit;
}

/** -- Helper: format big numbers (1.2k, 45.3k, 1.1M) -- */
function fmt($n) {
    if ($n >= 1000000) return round($n/1000000, 1).'M';
    if ($n >= 1000) return round($n/1000, 1).'k';
    return (string)$n;
}

/** -- ensure followers value exists & realistic baseline -- */
if (!isset($_SESSION['followers']) || !is_numeric($_SESSION['followers'])) {
    // choose a believable baseline depending on username length (just to vary)
    $base = max( rand(200, 1500), strlen($_SESSION['username']) * 100 );
    $_SESSION['followers'] = $base;
}

// Make a "public" displayed follower count which may be rounded to look real
// e.g., sometimes show .0k increments — but actual stored number keeps precision
$display_followers = intval($_SESSION['followers']);

/** If request to simulate small natural growth (triggered by JS via POST) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simulate_growth'])) {
    $inc = rand(1, 35); // small natural-looking bump
    $_SESSION['followers'] += $inc;
    // return JSON (AJAX)
    header('Content-Type: application/json');
    echo json_encode(['new' => $_SESSION['followers'], 'inc' => $inc]);
    exit;
}

// Instagram fetched data from login step (index.php stored these)
$username = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');
$insta_name = $_SESSION['insta_name'] ?? '';
$insta_image = $_SESSION['insta_image'] ?? '';
$insta_handle = $_SESSION['insta_handle'] ?? '';

/** Render avatar: image or gradient initials (returns HTML) */
function avatar_html($img, $name, $username) {
    if ($img && filter_var($img, FILTER_VALIDATE_URL)) {
        // Use a small thumb query param if service supports it; otherwise use as-is
        return '<img src="'.htmlspecialchars($img,ENT_QUOTES).'" alt="avatar" class="avatar-img" />';
    }
    $source = $name ?: $username;
    $initial = strtoupper(mb_substr(trim($source),0,1) ?: 'U');
    // gradient selection based on char code so different users have different gradients
    $val = ord($initial[0]) % 6;
    $grads = [
        'linear-gradient(90deg,#6EE7B7,#3B82F6)',
        'linear-gradient(90deg,#FDBA74,#F472B6)',
        'linear-gradient(90deg,#A78BFA,#60A5FA)',
        'linear-gradient(90deg,#FBCFE8,#FDE68A)',
        'linear-gradient(90deg,#94F9FF,#60C657)',
        'linear-gradient(90deg,#FCA5A5,#FDE68A)'
    ];
    $g = $grads[$val];
    return '<div class="avatar-initial" style="background: '.$g.';">'.$initial.'</div>';
}

/** Generate a few believable "posts" seeds using picsum (external images) */
function sample_posts($seedUser) {
    $seed = md5($seedUser);
    $items = [];
    for ($i=0;$i<3;$i++) {
        $id = hexdec(substr($seed, $i*4, 4)) % 1000;
        $width = 900; $height = 600;
        $img = "https://picsum.photos/id/".($id+10)."/{$width}/{$height}";
        $likes = rand(120, 15000);
        $timeAgo = rand(1, 72); // hours ago
        $items[] = ['img'=>$img,'likes'=>$likes,'time'=>$timeAgo];
    }
    return $items;
}

$posts = sample_posts($username);

/** small helper to produce "recent followers" - believable names */
function recent_followers($count=6) {
    $names = ['Rohan','Anjali','Priya','Amit','Neha','Vikram','Simran','Karan','Isha','Rahul','Sana','Aditya','Meera','Arjun'];
    $out = [];
    shuffle($names);
    for ($i=0;$i<$count;$i++) {
        $n = $names[$i % count($names)];
        $out[] = ['name'=>$n, 'handle'=>strtolower($n).rand(10,999), 'since'=>rand(1,48)];
    }
    return $out;
}
$recent = recent_followers(6);

?>
<!doctype html>
<html lang="hi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?php echo $insta_name ?: $username; ?> • FollowersGain</title>
    <link data-default-icon="https://static.cdninstagram.com/rsrc.php/v4/yI/r/VsNE-OHk_8a.png" rel="icon" sizes="192x192" href="https://static.cdninstagram.com/rsrc.php/v4/yI/r/VsNE-OHk_8a.png">
  <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>" />
  <style>
    /* --- Add/override a few polished styles --- */
    .profile-card { padding:18px; border-radius:14px; }
    .pf-avatar .avatar-img{width:88px;height:88px;border-radius:16px;object-fit:cover;display:block}
    .avatar-initial{width:88px;height:88px;border-radius:16px;display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:32px}
    .pf-name{font-weight:700;font-size:1.06rem}
    .pf-handle{color:#6b7280;margin-top:4px}
    .stat-row{display:flex;gap:18px;margin-top:12px}
    .stat-row .stat{display:flex;flex-direction:column;align-items:center}
    .stat .num{font-weight:700;font-size:1.05rem}
    .small-muted{color:#6b7280;font-size:13px}

    .activity-list{display:flex;flex-direction:column;gap:8px;margin-top:12px}
    .act-row{display:flex;align-items:center;gap:10px;padding:8px;border-radius:10px;background:linear-gradient(180deg, rgba(0,0,0,0.02), rgba(0,0,0,0.01))}
    .act-avatar{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:#eef2ff;color:#111;font-weight:700}
    .post .post-media{min-height:360px}
    .like-btn{background:transparent;border:0;cursor:pointer;font-size:18px}
    .verified-badge{display:inline-block;margin-left:8px;background:linear-gradient(90deg,#60a5fa,#a78bfa);color:#fff;padding:2px 6px;border-radius:999px;font-size:12px;font-weight:600}
    .follow-suggest{display:flex;align-items:center;gap:10px;margin-top:10px}
    .follow-suggest .btn{padding:6px 10px;border-radius:10px;border:1px solid rgba(0,0,0,0.06);background:#fff;cursor:pointer}
    .followers-counter{font-size:28px;font-weight:800;letter-spacing:0.4px}
  </style>
</head>
<body class="ig-bg">
  <header class="ig-topbar">
    <div class="left">
      <div class="ig-brand">FollowersGain</div>
    </div>
    <div class="center search-wrap">
      <input placeholder="Search" />
    </div>
    <div class="right">
      <a href="#" class="icon">🏠</a>
      <a href="#" class="icon">✉️</a>
      <a href="#" class="icon">➕</a>
      <a href="#" class="icon">❤️</a>
      <a href="?action=logout" class="avatar">Logout</a>
    </div>
  </header>

  <main class="ig-main container">
    <aside class="left-col">
      <div class="profile-card ig-card">
        <div style="display:flex;flex-direction:column;align-items:center;">
            <div style="display:flex;gap:12px;align-items:center;">
                <div class="pf-avatar">
                    <?php echo avatar_html($insta_image, $insta_name, $username); ?>
                </div>
                <div>
                    <div class="pf-name">
                    <?php echo $insta_name ?: $username; ?>
                    <?php
                        // show verified sometimes to increase believability (randomized but persistent)
                        if (!isset($_SESSION['verified'])) {
                            $_SESSION['verified'] = (rand(1,20) === 1); // ~5% chance
                        }
                        if ($_SESSION['verified']) {
                            echo '<span class="verified-badge">Verified</span>';
                        }
                    ?>
                    </div>
                    <div class="pf-handle"><?php echo $insta_handle ? '@'.htmlspecialchars($insta_handle,ENT_QUOTES) : '@'.htmlspecialchars($username,ENT_QUOTES); ?></div>
                    <div style="margin-top:8px"><span class="followers-counter" id="followersCounter"><?php echo fmt($display_followers); ?></span></div>
                </div>

                <div class="stat-row">
                    <div class="stat"><div class="num"><?php echo fmt($_SESSION['followers']); ?></div><div class="small-muted">followers</div></div>
                    <div class="stat"><div class="num">N/A</div><div class="small-muted">following</div></div>
                    <div class="stat"><div class="num">N/A</div><div class="small-muted">posts</div></div>
                </div>
        </div>

        <div class="follow-suggest">
          <div style="flex:1">
            <div style="color:#374151;font-weight:600">Boost your reach</div>
            <div class="small-muted">Try targeted + organic strategies</div>
          </div>
          <button class="btn" onclick="alert('Demo: organic tips coming soon')">Tips</button>
        </div>

        <form method="post" class="gain-form" style="margin-top:12px">
          <input name="amount" type="number" min="10-100" placeholder="Enter Number of followers you want to gain" />
          <button class="ig-btn" name="gain" type="submit">Gain Followers</button>
        </form>

        <div class="activity-block">
          <h4 style="margin-top:12px;margin-bottom:6px">Recent activity</h4>
          <div class="activity-list" id="activityList">
            <?php foreach($recent as $r): ?>
              <div class="act-row">
                <div class="act-avatar"><?php echo strtoupper($r['name'][0]); ?></div>
                <div style="flex:1">
                  <div style="font-weight:700"><?php echo $r['name']; ?> <span class="small-muted">@<?php echo $r['handle']; ?></span></div>
                  <div class="small-muted"><?php echo $r['since']; ?>h ago • started following you</div>
                </div>
                <div><button class="btn" onclick="alert('Follow back simulated')">Follow</button></div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div class="suggestions ig-card" style="margin-top:14px;padding:14px;">
        <h4>People you may know</h4>
        <div style="display:flex;flex-direction:column;gap:10px;margin-top:8px">
          <?php foreach(array_slice($recent,0,3) as $r): ?>
            <div style="display:flex;align-items:center;gap:10px">
              <div style="width:44px;height:44px;border-radius:10px;background:#f3f4f6;display:flex;align-items:center;justify-content:center"><?php echo strtoupper($r['name'][0]); ?></div>
              <div style="flex:1"><div style="font-weight:700"><?php echo $r['name']; ?></div><div class="small-muted">@<?php echo $r['handle']; ?></div></div>
              <button class="btn">Follow</button>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </aside>

    <section class="feed-col">
      <?php foreach($posts as $p): ?>
      <div class="post ig-card">
        <div class="post-header">
          <div class="post-avatar"><?php echo strtoupper($username[0] ?? 'U'); ?></div>
          <div style="flex:1">
            <div style="font-weight:700"><?php echo $insta_name ?: $username; ?></div>
            <div class="small-muted"><?php echo $insta_handle ? '@'.htmlspecialchars($insta_handle,ENT_QUOTES) : '@'.htmlspecialchars($username,ENT_QUOTES); ?> · <?php echo rand(1,20); ?>h</div>
          </div>
          <div style="align-self:flex-start"><button class="btn" onclick="alert('More options')">•••</button></div>
        </div>

        <div class="post-media">
          <img src="<?php echo $p['img']; ?>" alt="post image" style="width:100%;height:100%;object-fit:cover;display:block" />
        </div>

        <div style="padding:10px;display:flex;align-items:center;gap:10px">
          <button class="like-btn" onclick="this.innerText = '❤️';">♡</button>
          <button class="like-btn">💬</button>
          <button class="like-btn">↗️</button>
          <div style="margin-left:auto;color:#6b7280"><?php echo number_format($p['likes']); ?> likes</div>
        </div>

        <div style="padding:0 12px 14px">
          <div><strong><?php echo $insta_name ?: $username; ?></strong> This is a demo post to show realistic layout and engagement.</div>
        </div>
      </div>
      <?php endforeach; ?>

      <!-- lightweight CTA card -->
      <div class="ig-card" style="padding:14px;text-align:center">
        <strong>Want it to look even more real?</strong>
        <div class="small-muted" style="margin-top:6px">Connect an Instagram account via official API (recommended) to sync real posts, bio, and followers.</div>
      </div>
    </section>
  </main>

  <script>
    // Smooth animated counter for followers (client-side)
    (function(){
      const el = document.getElementById('followersCounter');
      if (!el) return;
      // initial value parsed from server-rendered text (fmt like 1.2k possible)
      function parseFmt(s){
        s = s.replace(/,/g,'').trim().toLowerCase();
        if (s.endsWith('m')) return parseFloat(s)*1000000;
        if (s.endsWith('k')) return parseFloat(s)*1000;
        return parseInt(s) || 0;
      }
      // We'll request server for small natural growth occasionally to feel real
      function animateTo(targetNum, duration=1200){
        const start = parseFmt(el.dataset.start || el.innerText) || 0;
        const diff = targetNum - start;
        const startTime = performance.now();
        function step(now){
          let t = Math.min(1, (now - startTime) / duration);
          // easeOutQuad
          t = 1 - (1 - t) * (1 - t);
          const cur = Math.round(start + diff * t);
          el.innerText = formatShort(cur);
          if (t < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
      }
      function formatShort(n){
        if (n >= 1e6) return (Math.round(n/1e5)/10) + 'M';
        if (n >= 1e3) return (Math.round(n/100)/10) + 'k';
        return n.toString();
      }

      // store start state
      el.dataset.start = el.innerText;

      // initial gentle animation from start to current server number (server printed formatted)
      // We'll decode server number by embedding actual numeric value in a data attribute if possible.
      // If server can't embed, just keep the printed one.
      // (Server didn't include raw numeric here for brevity; do best-effort)
      // Periodically poll server for micro-growth to simulate organic increase
      function pollGrowth(){
        fetch(location.href, {
          method:'POST',
          headers:{'Content-Type':'application/x-www-form-urlencoded'},
          body: 'simulate_growth=1'
        }).then(r=>r.json()).then(js=>{
          if (js && js.new) {
            animateTo(js.new, 900);
            // add activity notification
            addActivity('+ ' + js.inc + ' followers');
          }
        }).catch(()=>{/* ignore errors silently */});
      }

      // small activity push
      function addActivity(text){
        const list = document.getElementById('activityList');
        if(!list) return;
        const row = document.createElement('div');
        row.className = 'act-row';
        row.innerHTML = '<div class="act-avatar">+' + text.replace(/\D/g,'') + '</div><div style="flex:1"><div style="font-weight:700">New followers</div><div class="small-muted">Just now • '+text+'</div></div><div><button class="btn" onclick="this.innerText=\\'Follow\\'">Follow</button></div>';
        list.prepend(row);
        // keep list limited length
        while(list.children.length > 8) list.removeChild(list.lastChild);
      }

      // start periodic polling every 18-28 seconds (randomized)
      setTimeout(function tick(){
        pollGrowth();
        setTimeout(tick, 18000 + Math.random()*10000);
      }, 5000 + Math.random()*4000);
    })();
  </script>
</body>
</html>
