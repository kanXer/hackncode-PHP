<!-- ==============================
     KANXER HEADER COMPONENT (Safe)
============================== -->
<header class="kx-header">
  <div class="kx-logo">
    <div class="kx-dot">SKS</div>
    <a href="/" style="text-decoration:none;color:inherit">
      <div>
        <h1>Sahil Srivastava</h1>
        <div class="kx-small">Black Hat Hacker & Developer</div>
      </div>
    </a>
  </div>

  <nav id="kxMainNav" class="kx-nav">
    <a href="/">Home</a>
    <a href="/phone_info.php">OSINT</a>
    <a href="/news.php">News</a>
    <a href="/tbomb.php">T-Bomb</a>
    <a href="/#projects">Projects</a>
    <a href="/#contact">Contact</a>
  </nav>

  <div class="kx-right">
    <button id="kxThemeToggle" aria-label="Toggle Theme" class="kx-theme-btn">💡</button>
    <button class="kx-hamburger" id="kxHamburgerBtn" aria-label="Menu">
      <span></span>
      <span></span>
      <span></span>
    </button>
  </div>
</header>

<!-- ==========================
     Script for Theme & Navbar
     ========================== -->
<script>
const body = document.body;
const themeToggle = document.getElementById('kxThemeToggle');
const hamburger = document.getElementById('kxHamburgerBtn');
const nav = document.getElementById('kxMainNav');

// Initialize theme
const savedTheme = localStorage.getItem('theme') || 'dark';
body.classList.add(savedTheme);
updateIcon(savedTheme);

// Theme toggle
themeToggle.addEventListener('click', () => {
  const isDark = body.classList.contains('dark');
  body.classList.remove('dark', 'light');
  body.classList.add(isDark ? 'light' : 'dark');
  localStorage.setItem('theme', isDark ? 'light' : 'dark');
  updateIcon(isDark ? 'light' : 'dark');
});

// Update emoji based on theme
function updateIcon(theme) {
  themeToggle.textContent = theme === 'dark' ? '💡' : '🌙';
}

// Hamburger toggle
hamburger.addEventListener('click', () => {
  nav.classList.toggle('active');
  hamburger.classList.toggle('open');
});
</script>