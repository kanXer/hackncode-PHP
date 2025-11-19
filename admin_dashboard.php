<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}
include "db.php";

$msg = "";
$err = "";

// =================== ADD NEWS ===================== //
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {

    $title = trim($_POST['title'] ?? '');
    $short_desc = trim($_POST['short_desc'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $youtube = trim($_POST['youtube'] ?? '');

    if ($title === '' || $content === '') {
        $err = "Title and content required.";
    } else {

        // ======== IMAGE UPLOAD ======== //
        $saved = [];
        if (!empty($_FILES['images']) && is_array($_FILES['images']['name'])) {
            $upload_dir = __DIR__ . '/uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            $count = count($_FILES['images']['name']);
            for ($i=0; $i < $count; $i++) {

                if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) continue;

                $tmp = $_FILES['images']['tmp_name'][$i];
                $orig = basename($_FILES['images']['name'][$i]);
                $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));

                $allowed = ['jpg','jpeg','png','webp','gif'];
                if (!in_array($ext, $allowed)) continue;
                if (filesize($tmp) > 5*1024*1024) continue;

                $fname = time().'_'.rand(1000,9999).'_'.preg_replace('/[^A-Za-z0-9\-_\.]/','_',$orig);

                if (move_uploaded_file($tmp, $upload_dir.$fname)) {
                    $saved[] = $fname;
                }
            }
        }

        $images_json = !empty($saved) ? json_encode($saved, JSON_UNESCAPED_UNICODE) : null;

        // ======== YOUTUBE ID EXTRACTION ======== //
        $yt = null;
        if ($youtube) {
            if (preg_match('/(?:v=|\/embed\/|youtu\.be\/|\/v\/)([A-Za-z0-9_\-]{11})/i', $youtube, $m)) {
                $yt = $m[1];
            }
        }

        // ========== INSERT ========== //
        $stmt = $conn->prepare("
            INSERT INTO news (title, short_desc, content, images, youtube_url)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssss", $title, $short_desc, $content, $images_json, $yt);

        if ($stmt->execute()) {
            header("Location: admin_dashboard.php?added=1");
            exit;
        } else {
            $err = "DB error: " . $conn->error;
        }
    }
}

// =================== FETCH ALL NEWS ===================== //
$list = $conn->query("SELECT id, title, created_at FROM news ORDER BY id DESC");
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Admin Dashboard — KanXer</title>

<link rel="icon" type="image/svg+xml"
href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' rx='20' ry='20' fill='%23050705'/%3E%3Ctext x='50' y='63' font-size='42' font-family='Inter' text-anchor='middle' fill='%2314b866' font-weight='700'%3ESKS%3C/text%3E%3C/svg%3E">
<link rel="stylesheet" href="/assets/dash.css">

</head>
<body>

<div class="dash-box" style="max-width:900px;margin:30px auto;">

    <h2>Admin Dashboard</h2>
    <a href="logout.php" class="logout">Logout</a>

    <?php if(isset($_GET['added'])): ?>
        <p class="success">News added successfully!</p>
    <?php endif; ?>

    <?php if($msg): ?><p class="success"><?=$msg?></p><?php endif; ?>
    <?php if($err): ?><p class="err"><?=$err?></p><?php endif; ?>


    <!-- ================= LIST OF ALL NEWS ================= -->
    <h3>All News</h3>
    <?php while($r = $list->fetch_assoc()): ?>
        <div class="news-row">

            <div>
                <strong><?= htmlspecialchars($r['title']) ?></strong><br>
                <span class="small">ID: <?= $r['id'] ?> • <?= $r['created_at'] ?></span>
            </div>

            <div class="actions">
                <a class="a-edit" href="edit_news.php?id=<?= $r['id'] ?>">Edit</a>
                <a class="a-delete" href="delete_news.php?id=<?= $r['id'] ?>" onclick="return confirm('Delete this news?')">Delete</a>
            </div>

        </div>
    <?php endwhile; ?>


    <!-- ================= ADD NEWS FORM ================= -->
    <div class="add-section">
        <h3>Add New News</h3>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">

            <input type="text" name="title" placeholder="News Title" required><br><br>

            <textarea name="short_desc" placeholder="Short Description (optional)" rows="3"></textarea><br><br>
			<textarea name="content" id="content "required></textarea><br><br>

            <label>Images (multiple)</label><br>
            <input type="file" name="images[]" multiple accept="image/*"><br><br>

            <label>YouTube URL (optional)</label><br>
            <input type="text" name="youtube" placeholder="https://www.youtube.com/watch?v=..."><br><br>

            <button type="submit">Add News</button>
        </form>
    </div>

</div>

</body>
</html>
