<?php
include "db.php";

// ========= GET NEWS ID ========= //
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ========= FETCH DATA ========= //
if ($id > 0) {

    $stmt = $conn->prepare("SELECT * FROM news WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $news = $res->fetch_assoc();

} else {
    $res = $conn->query("SELECT id, title, content, created_at FROM news ORDER BY id DESC");
}

// ========= SLUG MAKER ========= //
function slugify($title) {
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    return trim($slug, '-');
}

// ========= IMAGE URL ========= //
function uploads_url($file) {
    return '/uploads/' . rawurlencode($file);
}

// ========= CLEAN SEO DESCRIPTION ========= //
function clean_excerpt($text, $limit = 180) {
    $text = strip_tags($text);
    $text = preg_replace('/\s+/', ' ', $text);

    if (strlen($text) <= $limit) return htmlspecialchars($text);

    $cut = substr($text, 0, $limit);
    $cut = substr($cut, 0, strrpos($cut, ' '));

    return htmlspecialchars($cut . '...');
}

// ========= SEO BLOCK ========= //
if ($id > 0 && $news) {

    $seo_title = htmlspecialchars($news['title']) . " — KanXer News";
    $seo_desc = clean_excerpt($news['content'], 180);

    $slug = slugify($news['title']);
    $canonical = "https://hackncode.live/news/$id/$slug";

} else {

    $seo_title = "Latest News — KanXer";
    $seo_desc = "Stay updated with OSINT, Cybersecurity, Forensics & Tech news curated by KanXer.";
    $canonical = "https://hackncode.live/news.php";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">

<title><?= $seo_title ?></title>
<meta name="description" content="<?= $seo_desc ?>">
<link rel="canonical" href="<?= $canonical ?>">

<!-- OG -->
<meta property="og:title" content="<?= $seo_title ?>">
<meta property="og:description" content="<?= $seo_desc ?>">
<meta property="og:image" content="https://hackncode.live/assets/kanxer-banner.jpg">
<meta property="og:url" content="<?= $canonical ?>">
<meta property="og:type" content="article">

<!-- TWITTER -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= $seo_title ?>">
<meta name="twitter:description" content="<?= $seo_desc ?>">

<link rel="stylesheet" href="/assets/header.css?v=<?= time(); ?>">
<link rel="stylesheet" href="/assets/style.css?v=<?= time(); ?>">
<link rel="stylesheet" href="/assets/footer.css?v=<?= time(); ?>">
<link rel="stylesheet" href="/assets/news.css?v=<?= time(); ?>">
<style>
/* MOBILE FIX */
@media(max-width:768px){
    .news-wrapper { padding:15px; }
    .news-card-kanxer { padding:18px; }
    .news-title { font-size:24px; }
    .news-content { font-size:16px; }
    .news-excerpt { font-size:16px; }
    .news-list-item a { font-size:18px; }
    .back-btn {
        padding:8px 14px;
        font-size:14px;
    }
}
</style>
<link rel="icon" type="image/svg+xml"
href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' rx='20' ry='20' fill='%23050705'/%3E%3Ctext x='50' y='63' font-size='42' font-family='Inter' text-anchor='middle' fill='%2314b866' font-weight='700'%3ESKS%3C/text%3E%3C/svg%3E">
<script async custom-element="amp-auto-ads"
        src="https://cdn.ampproject.org/v0/amp-auto-ads-0.1.js">
</script>
</head>

<body class="dark">

<?php include $_SERVER['DOCUMENT_ROOT']."/components/header.php"; ?>

<div class="news-wrapper">

<?php if ($id == 0): ?>

    <h2 style="color:var(--accent);font-size:30px;text-align:center;">🔔 Latest News</h2>
    <amp-auto-ads type="adsense" data-ad-client="ca-pub-8088557937249669"></amp-auto-ads>

    <?php while($row = $res->fetch_assoc()): ?>
        <?php $slug = slugify($row['title']); ?>
        <?php $excerpt = clean_excerpt($row['content'], 120); ?>

        <div class="news-list-item">
            <a href="/news/<?= $row['id'] ?>/<?= $slug ?>">
                <?= htmlspecialchars($row['title']) ?>
            </a>
            <p style="color:var(--muted);font-size:14px;margin-top:6px;">
                <?= $excerpt ?>
            </p>
        </div>
    	<amp-auto-ads type="adsense" data-ad-client="ca-pub-8088557937249669"></amp-auto-ads>

    <?php endwhile; ?>

<?php else: ?>

    <?php if (!$news): ?>

        <p style="color:#fff;">News Not Found.</p>

    <?php else: ?>

        <div class="news-card-kanxer">

            <!-- BACK BUTTON -->
            <a href="/news.php" class="back-btn">← Back to Posts</a>
			<amp-auto-ads type="adsense" data-ad-client="ca-pub-8088557937249669"></amp-auto-ads>
            <!-- TITLE -->
            <h1 class="news-title"><?= htmlspecialchars($news['title']) ?></h1>

            <!-- SHORT EXCERPT -->
            <p class="news-excerpt"><?= clean_excerpt($news['content'], 160) ?></p>
			<amp-auto-ads type="adsense" data-ad-client="ca-pub-8088557937249669"></amp-auto-ads>
            <!-- YOUTUBE -->
            <?php if (!empty($news['youtube_url'])): ?>
                <div class="yt-embed">
                    <iframe width="100%" height="420"
                        src="https://www.youtube.com/embed/<?= htmlspecialchars($news['youtube_url']) ?>"
                        allowfullscreen></iframe>
                </div>
            <?php endif; ?>
			<amp-auto-ads type="adsense" data-ad-client="ca-pub-8088557937249669"></amp-auto-ads>
            <!-- CONTENT + IMAGES -->
            <?php
            $images = [];
            if (!empty($news['images'])) $images = json_decode($news['images'], true);

            $content = str_replace(["\r\n","\r"], "\n", $news['content']);
            $paras = preg_split("/\n{2,}/", $content);
            if (count($paras) == 1) $paras = explode("\n", $content);

            $p = count($paras);
            $i = count($images);
            $slot = $p + 1;
            $per = $i / $slot;
            $x = 0;

            for ($k=0; $k<$p; $k++) {
                echo "<div class='news-content'>" . nl2br(htmlspecialchars($paras[$k])) . "</div>";

                $need = round($per * ($k+1));
                while ($x < $need && $x < $i) {
                    echo "<img class='img-mid' src='".uploads_url($images[$x])."'>";
                    $x++;
                }
            }

            while ($x < $i) {
                echo "<img class='img-mid' src='".uploads_url($images[$x])."'>";
                $x++;
            }
            ?>

            <p style="font-size:14px;color:var(--muted);margin-top:10px;text-align:right;">
                Posted on: <?= $news['created_at'] ?>
            </p>
            <amp-auto-ads type="adsense" data-ad-client="ca-pub-8088557937249669"></amp-auto-ads>

        </div>
		<amp-auto-ads type="adsense" data-ad-client="ca-pub-8088557937249669"></amp-auto-ads>
    <?php endif; ?>

<?php endif; ?>

</div>

<?php include $_SERVER['DOCUMENT_ROOT']."/components/footer.php"; ?>
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8088557937249669" crossorigin="anonymous"></script>
</body>
</html>
