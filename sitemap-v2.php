<?php
include "db.php";
header("Content-Type: application/xml; charset=utf-8");

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

<?php
// ===============================
// 1) STATIC PAGES
// ===============================
$static_pages = [
    "/"                      => "2025-01-01",
    "/phone_info.php"        => "2025-01-01",
    "/news.php"              => "2025-01-01",
    "/tbomb.php"             => "2025-01-01",
    "/#projects"             => "2025-01-01",
    "/#contact"              => "2025-01-01",
];

$base = "https://hackncode.live";

foreach ($static_pages as $page => $lastmod) {
?>
<url>
    <loc><?= $base . $page ?></loc>
    <lastmod><?= $lastmod ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.90</priority>
</url>
<?php } ?>

<?php
// ===============================
// 2) DYNAMIC NEWS PAGES
// ===============================
function slugify($t){
    $t = strtolower(trim($t));
    $t = preg_replace('/[^a-z0-9]+/','-',$t);
    return trim($t,'-');
}

$res = $conn->query("SELECT id, title, created_at FROM news ORDER BY id DESC");

while ($r = $res->fetch_assoc()) {
    $slug = slugify($r['title']);
    $loc  = $base . "/news/" . $r['id'] . "/" . $slug;
?>
<url>
    <loc><?= $loc ?></loc>
    <lastmod><?= date('Y-m-d', strtotime($r['created_at'])) ?></lastmod>
    <changefreq>daily</changefreq>
    <priority>0.80</priority>
</url>
<?php } ?>

<?php
// ===============================
// 3) FUTURE DYNAMIC PAGES (OPTIONAL)
// Uncomment if needed when APIs ready
// ===============================

// PROJECTS
/*
$projects = [
   ["loc" => "/projects/osint-tools", "priority" => "0.70"],
   ["loc" => "/projects/api-services", "priority" => "0.75"],
];
foreach ($projects as $p) {
?>
<url>
   <loc><?= $base . $p["loc"] ?></loc>
   <lastmod><?= date("Y-m-d") ?></lastmod>
   <changefreq>monthly</changefreq>
   <priority><?= $p["priority"] ?></priority>
</url>
<?php } ?>
*/

// TOOLS
/*
$tools = [
   "/phone_info.php",
   "/tbomb.php",
];
foreach ($tools as $t) {
?>
<url>
   <loc><?= $base . $t ?></loc>
   <lastmod><?= date("Y-m-d") ?></lastmod>
   <changefreq>weekly</changefreq>
   <priority>0.70</priority>
</url>
<?php } ?>
*/
?>

</urlset>
