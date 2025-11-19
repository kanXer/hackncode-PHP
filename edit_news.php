<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

include "db.php";

// ==================== VALIDATE ID ==================== //
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: admin_dashboard.php");
    exit;
}

// ==================== FETCH NEWS ==================== //
$stmt = $conn->prepare("SELECT * FROM news WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$news = $res->fetch_assoc();

if (!$news) {
    header("Location: admin_dashboard.php");
    exit;
}

$msg = "";
$err = "";

// ==================== UPDATE NEWS ==================== //
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');
    $short_desc = trim($_POST['short_desc'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $youtube = trim($_POST['youtube'] ?? '');

    if ($title === '' || $content === '') {
        $err = "Title and Content are required.";
    } else {

        // Old images
        $existing = [];
        if (!empty($news['images'])) {
            $tmp = json_decode($news['images'], true);
            if (is_array($tmp)) $existing = $tmp;
        }

        // ================= DELETE SELECTED IMAGES ================= //
        $final_images = [];
        $delete_images = $_POST['delete_img'] ?? [];

        foreach ($existing as $img) {
            if (!in_array($img, $delete_images)) {
                $final_images[] = $img;
            } else {
                // delete from folder
                $file = __DIR__ . "/uploads/" . $img;
                if (file_exists($file)) unlink($file);
            }
        }

        // ================= ADD NEW UPLOADED IMAGES ================= //
        if (!empty($_FILES['images']) && is_array($_FILES['images']['name'])) {

            $upload_dir = __DIR__ . '/uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            $count = count($_FILES['images']['name']);

            for ($i = 0; $i < $count; $i++) {

                if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) continue;

                $tmpf = $_FILES['images']['tmp_name'][$i];
                $orig = basename($_FILES['images']['name'][$i]);
                $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));

                $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                if (!in_array($ext, $allowed)) continue;
                if (filesize($tmpf) > 5 * 1024 * 1024) continue;

                $fname = time() . '_' . rand(1000, 9999) . '_' . preg_replace('/[^A-Za-z0-9\-_\.]/', '_', $orig);

                if (move_uploaded_file($tmpf, $upload_dir . $fname)) {
                    $final_images[] = $fname;
                }
            }
        }

        $images_json = !empty($final_images) ? json_encode($final_images, JSON_UNESCAPED_UNICODE) : null;

        // =============== Extract YouTube ID =============== //
        $yt = null;
        if ($youtube) {
            if (preg_match('/(?:v=|\/embed\/|youtu\.be\/|\/v\/)([A-Za-z0-9_\-]{11})/', $youtube, $m)) {
                $yt = $m[1];
            }
        }

        // =============== UPDATE DB =============== //
        $u = $conn->prepare("
            UPDATE news 
            SET title = ?, short_desc = ?, content = ?, images = ?, youtube_url = ? 
            WHERE id = ?
        ");

        $u->bind_param("sssssi", $title, $short_desc, $content, $images_json, $yt, $id);

        if ($u->execute()) {
            header("Location: admin_dashboard.php?updated=1");
            exit;
        } else {
            $err = "DB Error: " . $conn->error;
        }
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Edit News #<?= $id ?></title>
<link rel="stylesheet" href="/assets/dash.css">
<link rel="icon" type="image/svg+xml"
href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' rx='20' ry='20' fill='%23050705'/%3E%3Ctext x='50' y='63' font-size='42' font-family='Inter' text-anchor='middle' fill='%2314b866' font-weight='700'%3ESKS%3C/text%3E%3C/svg%3E">

</head>

<body>

<div class="dash-box" style="max-width:900px;margin:30px auto;">

    <h2>Edit News</h2>
    <a href="admin_dashboard.php" class="back-btn">← Back</a>

    <?php if($err): ?><p class="err"><?= $err ?></p><?php endif; ?>

    <form method="post" enctype="multipart/form-data">

        <input type="text" name="title" value="<?= htmlspecialchars($news['title']) ?>" required><br><br>

        <textarea name="short_desc" rows="3" placeholder="Short description (optional)">
<?= htmlspecialchars($news['short_desc']) ?>
</textarea><br><br>

        <textarea name="content" rows="8" required><?= htmlspecialchars($news['content']) ?></textarea><br><br>

        <!-- ================= SHOW EXISTING IMAGES ================= -->
        <?php
        $imgs = [];
        if (!empty($news['images'])) {
            $tmp = json_decode($news['images'], true);
            if (is_array($tmp)) $imgs = $tmp;
        }

        if ($imgs):
        ?>
            <strong>Existing Images:</strong><br>

            <?php foreach($imgs as $im): ?>
                <div class="img-box">
                    <img src="uploads/<?= rawurlencode($im) ?>">
                    <div class="delete-check">
                        <label>
                            <input type="checkbox" name="delete_img[]" value="<?= $im ?>"> Delete
                        </label>
                    </div>
                </div>
            <?php endforeach; ?>

            <br><br>
        <?php endif; ?>


        <label>Upload More Images:</label><br>
        <input type="file" name="images[]" multiple accept="image/*"><br><br>

        <label>YouTube URL (optional)</label><br>
        <input type="text" name="youtube" value="<?= htmlspecialchars($news['youtube_url']) ?>"><br><br>

        <button type="submit">Save Changes</button>

    </form>

</div>
</body>
</html>
