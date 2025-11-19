<?php
// portfolio_pdf_preview_modern.php
// KanXer Dark Theme — Full black gradient background + clean green accent
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Hi, I'm Sahil Srivastava (KanXer) This is my professional CV showcasing my skills, projects, and experience in web development and programming.">
  <meta name="author" content="Sahil Srivastava">
  <!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:title" content="Sahil Srivastava (KanXer) | Resume Preview">
<meta property="og:description" content="Hi, I'm Sahil Srivastava (KanXer) This is my professional CV showcasing my skills, projects, and experience in web development and programming.">
<meta property="og:image" content="https://hackncode.live/logo.jpeg">
<meta property="og:url" content="https://hackncode.live/res_preview.php">
<meta property="og:site_name" content="Sahil Srivastava | KanXer">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Sahil Srivastava (KanXer) | Resume Preview">
<meta name="twitter:description" content="Hi, I'm Sahil Srivastava (KanXer) This is my professional CV showcasing my skills, projects, and experience in web development and programming.">
<meta name="twitter:image" content="https://hackncode.live/logo.jpeg">
  <title>Sahil Srivastava (KanXer) | PDF Preview</title>
  <link rel="stylesheet" href="assets/resumepreview.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="assets/header.css?v=<?php echo time(); ?>">
  <link rel="icon" type="image/svg+xml"
        href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' rx='20' ry='20' fill='%23050705'/%3E%3Ctext x='50' y='63' font-size='42' font-family='Inter' text-anchor='middle' fill='%2314b866' font-weight='700'%3ESKS%3C/text%3E%3C/svg%3E">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="assets/footer.css?v=<?php echo time(); ?>">
</head>
<body class="dark">
  <?php include 'components/header.php'; ?>
  <div class="container">
    <section class="pdf-preview" id="pdf">
      <h2>Portfolio PDF Preview</h2>
      <p class="small">Preview my full portfolio below</p>
      <a href="Sahil.pdf" target="_blank" class="btn"><i class="fa-solid fa-download"></i> Download PDF</a>
      <iframe src="Sahil.pdf" style="height: 1200px;"></iframe>
    </section>

  <?php include 'components/footer.php'; ?>
  </div>
</body>
</html>
