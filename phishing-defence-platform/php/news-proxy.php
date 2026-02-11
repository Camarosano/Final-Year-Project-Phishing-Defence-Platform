<?php
declare(strict_types=1);

// Misma origin → sin problemas de CSP (connect-src 'self')
require_once __DIR__ . '/../config/config.php';
require_once LIB_DIR . '/security.php';

if (function_exists('send_security_headers')) send_security_headers();

// Solo GET
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
  http_response_code(405);
  header('Allow: GET');
  exit;
}

// Configura tu API key (mejor por env var)
$NEWS_KEY = getenv('NEWSDATA_API_KEY') ?: 'pub_ec0a59392e5d42aa8f6a36d27c5da633';

// Parámetros “seguros” (nada de proxyear cualquier URL)
$q = 'phishing OR credential theft OR ransomware';
$lang = 'en';
$cat  = 'technology,top';

$api = 'https://newsdata.io/api/1/news';
$query = http_build_query([
  'apikey'   => $NEWS_KEY,
  'q'        => $q,
  'language' => $lang,
  'category' => $cat,
]);

$url = $api . '?' . $query;

// Llamada con cURL (mejor que file_get_contents para manejar errores)
$ch = curl_init($url);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_TIMEOUT        => 10,
]);
$body = curl_exec($ch);
$err  = curl_error($ch);
$code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

header('Content-Type: application/json; charset=utf-8');

if ($body === false || $code < 200 || $code >= 300) {
  echo json_encode(['error' => 'news_api_error', 'http' => $code, 'detail' => $err], JSON_UNESCAPED_SLASHES);
  exit;
}

echo $body;
