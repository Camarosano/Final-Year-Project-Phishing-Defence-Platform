<?php
// Este include asume que la página padre ya ha cargado:
//   - phishing-defence-platform/config/config.php (define app_url() / url())
//   - libs de seguridad y send_security_headers()
//   - $nonce = csp_nonce()
// Si el padre no definió $nonce, generamos uno simple para evitar notices.
if (!isset($nonce)) {
  try {
    $nonce = bin2hex(random_bytes(16));
  } catch (Throwable $e) {
    $nonce = bin2hex(openssl_random_pseudo_bytes(16));
  }
}
?>

<!-- Sidebar navigation menu -->
<nav id="sidebar" class="sidebar">
  <ul>
    <!-- Todas las rutas usan helpers para respetar APP_BASE -->
    <li><a href="<?= app_url('dashboard.php') ?>">Dashboard</a></li>
    <li><a href="<?= app_url('news.php') ?>">News</a></li>
    <li><a href="<?= app_url('faq.php') ?>">F&nbsp;&amp;&nbsp;Q</a></li>
    <li><a href="<?= app_url('payment.php') ?>">Payment Method</a></li>
    <li><a href="<?= app_url('about.php') ?>">About Us</a></li>
    <li><a href="<?= app_url('performance.php') ?>">Performance</a></li>
    <!-- FIX: antes era ../php/logout.php (apuntaba fuera de la base). -->
    <li><a href="<?= app_url('php/logout.php') ?>">Logout</a></li>
    <!-- También podrías usar url('php/logout.php') si prefieres absoluta -->
    <!-- <li><a href="<?= url('php/logout.php') ?>">Logout</a></li> -->
  </ul>
</nav>

<!-- Top bar header with logo and toggle button (mobile menu) -->
<header class="top-bar">
  <div class="logo-small">PHISHING&nbsp;DEFENCE</div>
  <button class="menu-toggle" aria-label="Toggle menu" id="menuToggleBtn">☰</button>
</header>

<style>
/* ===== TOP BAR ================================================ */
header.top-bar{
  position:fixed; top:0; left:0; width:100%;
  height:56px; background:#000; border-bottom:2px solid #e11d48;
  display:flex; align-items:center; justify-content:space-between;
  padding:0 16px; z-index:9000;
}
header.top-bar .logo-small{
  display:block; font-size:1.4rem; font-weight:900;
  color:#e11d48; letter-spacing:1px;
}

/* ===== SIDEBAR ================================================= */
.sidebar{
  position:fixed; top:56px; left:0;
  width:260px; height:calc(100vh - 56px);
  background:#0b0b0b; border-right:2px solid #e11d48;
  transform:translateX(-100%); transition:transform .2s ease-in-out;
  z-index:8000; overflow-y:auto;
}
.sidebar.active{ transform:translateX(0); }
.sidebar ul{ list-style:none; margin:0; padding:12px; }
.sidebar li{ margin:6px 0; }
.sidebar a{
  color:#e5e7eb; text-decoration:none; display:block;
  padding:10px 12px; border-radius:8px;
}
.sidebar a:hover{ background:#111; color:#22c55e; }
</style>

<!-- Attach the event handler using CSP nonce (no inline JS attributes) -->
<script nonce="<?= $nonce ?>">
  (function () {
    var btn = document.getElementById('menuToggleBtn');
    var sidebar = document.getElementById('sidebar');
    if (btn && sidebar) {
      btn.addEventListener('click', function () {
        sidebar.classList.toggle('active');
      });
    }
  })();
</script>
