<?php
declare(strict_types=1);

// === Includes ===
require_once __DIR__ . '/config/config.php';
require_once LIB_DIR . '/security.php';
require_once LIB_DIR . '/csrf.php';

// Seguridad + nonce para JS inline
send_security_headers();
$nonce = csp_nonce();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Latest Cyber-Phishing News</title>
  <link rel="stylesheet" href="<?= app_url('../css/dashboard.css') ?>">
<link rel="stylesheet" href="<?= app_url('../css/news.css') ?>">
</head>
<body>

<?php include 'sidebar-and-topbar.inc.php'; ?>

<main class="news-wrap">
  <h1>Latest Cyber-Phishing News</h1>

  <!-- Board centrado con borde rojo -->
  <div class="news-board">
    <div id="news-container">
      <div class="loader">Fetching live headlines …</div>
    </div>
  </div>
</main>

<script nonce="<?= $nonce ?>">
// Usamos el proxy local para evitar problemas de CSP (connect-src)
const fallback = [
  { title:'FTC warns of surge in QR-code phishing texts', link:'#', src:'KrebsOnSecurity',  date:'2025-06-18' },
  { title:'University loses 300k records to credential-phish', link:'#', src:'BBC',        date:'2025-06-15' },
  { title:'Ransomware gang uses ads to steal Outlook creds',    link:'#', src:'BleepingComputer', date:'2025-06-14' },
];

async function loadNews(){
  try {
    const res = await fetch('<?= app_url('php/news-proxy.php') ?>', { credentials:'same-origin' });
    if (!res.ok) throw new Error('Proxy error');
    const data = await res.json();
    if (!data || !Array.isArray(data.results)) throw new Error('Bad payload');

    const items = data.results.map(n => ({
      title: n.title,
      link : n.link,
      src  : n.source_id ?? 'news',
      date : (n.pubDate || '').slice(0,10)
    }));
    buildList(items);
  } catch (e) {
    console.warn('News proxy failed → using fallback', e);
    buildList(fallback);
  }
}

function buildList(items){
  const box   = document.getElementById('news-container');
  const cards = document.createElement('div');
  cards.className = 'news-cards';

  items.slice(0, 10).forEach(n => {
    const card  = document.createElement('article');
    card.className = 'news-card';

    const a = document.createElement('a');
    a.className   = 'news-title';
    a.href        = n.link;
    a.target      = '_blank';
    a.rel         = 'noopener noreferrer';
    a.textContent = n.title;

    const meta = document.createElement('div');
    meta.className = 'news-meta';

    const src = document.createElement('span');
    src.className   = 'src';
    src.textContent = n.src;

    const date = document.createElement('span');
    date.className   = 'date';
    date.textContent = n.date;

    meta.append(src, date);
    card.append(a, meta);
    cards.append(card);
  });

  box.innerHTML = '';
  box.append(cards);
}

loadNews();
</script>
</body>
</html>
