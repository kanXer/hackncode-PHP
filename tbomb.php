<?php
// ============================
//  KanXer TBomb | Live Runner
//  Developed by Lala 💚
// ============================

ini_set('display_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="A Massage,Call and Email Bomber Tool">
<meta name="keywords" content="Sahil Srivastava, KanXer, A, Massage,Call, and, Email, Bomber, Tool">
<meta name="author" content="Sahil Srivastava(kanXer)">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:title" content="Sahil Srivastava (KanXer) | TY-Bomb">
<meta property="og:description" content="A Massage,Call and Email Bomber Tool">
<meta property="og:image" content="https://hackncode.live/preview.jpeg">
<meta property="og:url" content="https://hackncode.live/tbomb.php">
<meta property="og:site_name" content="Sahil Srivastava | KanXer">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Sahil Srivastava (KanXer) | T-Bomb">
<meta name="twitter:description" content="A Massage,Call and Email Bomber Tool">
<meta name="twitter:image" content="https://hackncode.live/preview.jpeg">
<title>(KanXer) | T-Bomb</title>
<link rel="stylesheet" href="assets/header.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="assets/footer.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="assets/tbmb.css?v=<?php echo time(); ?>">
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' rx='20' ry='20' fill='%23050705'/%3E%3Ctext x='50' y='63' font-size='42' font-family='Inter' text-anchor='middle' fill='%2314b866' font-weight='700'%3ESKS%3C/text%3E%3C/svg%3E">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php include 'components/header.php'; ?>

<div class="tbomb-container">
  <h1><i class="fa-solid fa-bomb"></i> KanXer TBomb (Under Development)</h1>

  <form id="tbombForm" onsubmit="return runBomb(event)" style="max-width:600px;margin:auto;text-align:center;">
    <label>Mode:</label><br>
    <select id="type" name="type" class="tbomb-input">
      <option value="sms">SMS</option>
      <option value="call">CALL</option>
      <option value="mail">MAIL</option>
    </select><br><br>

    <label>Country Code:</label><br>
    <input id="cc" type="text" name="cc" value="91" class="tbomb-input"><br><br>

    <label>Phone / Email:</label><br>
    <input id="target" type="text" name="target" placeholder="9876543210" class="tbomb-input"><br><br>

    <label>Requests Count:</label><br>
    <input id="count" type="number" name="count" value="10" class="tbomb-input" min="1"><br><br>

    <label>Delay (sec):</label><br>
    <input id="delay" type="number" name="delay" value="1" class="tbomb-input" min="0" step="0.1"><br><br>

    <button id="runBtn" class="btn"><i class="fa-solid fa-play"></i> Start Test</button>
  </form>

  <div style="max-width:700px;margin:20px auto;">
    <div style="display:flex;justify-content:space-between;align-items:center;">
      <div class="small">Progress: <span id="progressText">0 / 0</span></div>
      <div class="small" id="statusBadge" style="color:var(--muted)">Idle</div>
    </div>
    <div class="progress" style="margin-top:8px;height:10px;border-radius:6px;background:rgba(255,255,255,0.03)">
      <div id="progressBar" class="progress-bar" style="height:100%;width:0%;background:var(--accent)"></div>
    </div>
  </div>

  <div id="logsContainer" class="tbomb-logbox">
    <div class="small">No attempts yet.</div>
  </div>
</div>

<?php include 'components/footer.php'; ?>

<script>
async function runBomb(e) {
  e.preventDefault();
  const btn = document.getElementById('runBtn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Running...';
  document.getElementById('logsContainer').innerHTML = '<div class="small">Fetching results...</div>';
  document.getElementById('statusBadge').textContent = 'Running';
  updateProgress(0,0);

  const params = new URLSearchParams({
    type: document.getElementById('type').value,
    cc: document.getElementById('cc').value.trim(),
    target: document.getElementById('target').value.trim()
  });

  try {
    const res = await fetch('api/tbomb_run.php?' + params.toString());
    const data = await res.json();

    if (data.error) {
      document.getElementById('logsContainer').innerHTML = '<div class="api-error-box" style="color:#ffb2b2;">⚠ ' + data.error + '</div>';
      setStatus('Error');
    } else {
      const logs = data.results || [];
      const total = logs.length;
      let successCount = 0;
      document.getElementById('logsContainer').innerHTML = '';

      logs.forEach((item, i) => {
        setTimeout(() => {
          appendLog(item);
          if (item.success) successCount++;
          updateProgress(i + 1, total);
          if (i + 1 === total) {
            appendLog({name:'[SYSTEM]', success:true, statusText:`Completed — ${successCount}/${total} OK`});
            setStatus('Completed');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-play"></i> Start Test';
          }
        }, i * 150);
      });
    }
  } catch (err) {
    document.getElementById('logsContainer').innerHTML = '<div style="color:#ffb2b2;">❌ Connection failed</div>';
    setStatus('Error');
  }

  return false;
}

function appendLog(item) {
  const container = document.getElementById('logsContainer');
  const placeholder = container.querySelector('.small');
  if (placeholder) placeholder.remove();

  const div = document.createElement('div');
  div.className = 'tbomb-log ' + (item.success ? 'ok' : 'fail');
  div.style.display = 'flex';
  div.style.justifyContent = 'space-between';
  div.style.alignItems = 'center';
  div.innerHTML = `
    <span>[${sanitize(item.name || 'Unknown')}]</span>
    <span style="color:${item.success ? '#14b866' : '#ff5757'}">
      ${item.success ? '✅ OK' : '❌ FAIL'}
    </span>
  `;
  container.prepend(div);
}

function updateProgress(done, total){
  const pct = total === 0 ? 0 : Math.round((done/total)*100);
  document.getElementById('progressBar').style.width = pct + '%';
  document.getElementById('progressText').textContent = `${done} / ${total}`;
}

function setStatus(t){
  document.getElementById('statusBadge').textContent = t;
}

function sanitize(s){
  const d = document.createElement('div');
  d.textContent = s;
  return d.innerHTML;
}
</script>

</body>
</html>
