<?php
declare(strict_types=1);

require dirname(__DIR__) . '/config/bootstrap.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($config['app_name'], ENT_QUOTES, 'UTF-8') ?></title>
<style>
body{font-family:system-ui,-apple-system,sans-serif;margin:0;background:#f5f7fb;color:#172033}.wrap{max-width:1100px;margin:60px auto;padding:24px}.card{background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:28px;box-shadow:0 8px 30px rgba(0,0,0,.05)}h1{margin-top:0}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px}.item{padding:18px;border:1px solid #e5e7eb;border-radius:12px}.ok{font-weight:700}
</style>
</head>
<body><main class="wrap"><section class="card">
<h1>ResellNom Domain Platform</h1>
<p class="ok">Application foundation is online.</p>
<div class="grid">
<div class="item"><strong>Multi-Registrar</strong><br>Wix, Namecheap & future adapters</div>
<div class="item"><strong>Reseller Hierarchy</strong><br>Admin → Reseller → Sub-Reseller → Client</div>
<div class="item"><strong>Domain Lifecycle</strong><br>Register, Transfer, Renewal, Restore</div>
<div class="item"><strong>DNS</strong><br>Records and nameserver management</div>
<div class="item"><strong>WHMCS</strong><br>Unified API/module foundation</div>
<div class="item"><strong>Security</strong><br>PDO prepared queries, CSRF and audit foundation</div>
</div></section></main></body></html>
