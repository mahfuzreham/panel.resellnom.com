<?php
declare(strict_types=1);
require dirname(__DIR__) . '/config/bootstrap.php';
require dirname(__DIR__) . '/app/Auth/Auth.php';

use ResellNom\Auth\Auth;

$auth = new Auth(db());
$user = $auth->user();
if (!$user) {
    header('Location: /login.php');
    exit;
}

$role = (string)$user['role'];
$isAdmin = $role === 'admin';
$isReseller = in_array($role, ['reseller', 'sub_reseller'], true);
$displayName = trim((string)($user['name'] ?? '')) ?: (string)$user['email'];
$balance = number_format((float)($user['balance'] ?? 0), 2);
$roleLabel = ucwords(str_replace('_', ' ', $role));

$groups = $isAdmin ? [
    ['icon'=>'◈','title'=>'Users & Roles','items'=>[['/admin/users.php','User Management'],['#','Resellers'],['#','Sub-Resellers'],['#','Clients']]],
    ['icon'=>'◎','title'=>'Registrars','items'=>[['#','Registrar Companies'],['#','API Credentials'],['#','Registrar Priority'],['#','TLD Configuration']]],
    ['icon'=>'৳','title'=>'Billing & Pricing','items'=>[['/admin/billing.php','Wallet & Pricing'],['/admin/billing.php','Promotions'],['#','Invoices']]],
    ['icon'=>'▣','title'=>'Operations','items'=>[['#','Orders'],['#','All Domains'],['#','Transfer Jobs'],['#','Audit Logs']]],
] : ($isReseller ? [
    ['icon'=>'♙','title'=>'Customers','items'=>[['#','Customer Management'],['#','Create Client'],['#','Customer Pricing']]],
    ['icon'=>'৳','title'=>'Reseller Billing','items'=>[['#','Wallet'],['#','Transactions'],['#','Invoices']]],
    ['icon'=>'⌁','title'=>'Domain Sales','items'=>[['#','Search Domains'],['#','Register Domain'],['#','Transfers'],['#','Renewals']]],
    ['icon'=>'⚙','title'=>'Integration','items'=>[['#','WHMCS API'],['#','API Keys'],['#','Webhooks']]],
] : [
    ['icon'=>'◎','title'=>'My Domains','items'=>[['#','Domain Search'],['#','My Domains'],['#','Register Domain'],['#','Transfer Domain'],['#','Renew Domain']]],
    ['icon'=>'⌁','title'=>'Domain Management','items'=>[['#','DNS Manager'],['#','Nameservers'],['#','EPP / Auth Code'],['#','Auto-Renewal']]],
    ['icon'=>'৳','title'=>'Billing','items'=>[['#','My Wallet'],['#','Invoices'],['#','Transactions']]],
    ['icon'=>'?','title'=>'Support','items'=>[['#','Tickets'],['#','Notifications'],['#','Account Settings']]],
]);
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=htmlspecialchars($roleLabel)?> · ResellNom</title>
<style>
:root{--bg:#f5f7fb;--panel:#fff;--text:#172033;--muted:#6b7280;--line:#e8ebf2;--brand:#111827;--accent:#2563eb;--accent2:#0ea5e9;--shadow:0 12px 35px rgba(15,23,42,.07)}
*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--text);font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}.top{height:70px;background:var(--brand);color:#fff;display:flex;align-items:center;justify-content:space-between;padding:0 5%;gap:20px}.logo{font-size:22px;font-weight:900;letter-spacing:-.5px}.logo span{color:#60a5fa}.account{display:flex;align-items:center;gap:12px;font-size:14px}.avatar{width:36px;height:36px;border-radius:50%;display:grid;place-items:center;background:#374151;font-weight:800}.wrap{max-width:1320px;margin:0 auto;padding:32px 22px 60px}.welcome{display:flex;justify-content:space-between;align-items:end;gap:20px;margin-bottom:25px}.welcome h1{font-size:30px;line-height:1.15;margin:0 0 8px;letter-spacing:-1px}.muted{color:var(--muted)}.pill{display:inline-flex;align-items:center;padding:6px 10px;border-radius:999px;background:#eaf2ff;color:#1d4ed8;font-size:12px;font-weight:800}.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px}.stat,.card{background:var(--panel);border:1px solid var(--line);border-radius:18px;box-shadow:var(--shadow)}.stat{padding:20px}.stat-label{color:var(--muted);font-size:13px;font-weight:700}.stat-value{font-size:25px;font-weight:900;margin-top:8px}.stat-link{font-size:12px;color:var(--accent);text-decoration:none}.section-title{font-size:18px;margin:0 0 14px}.grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}.card{padding:20px}.card-head{display:flex;align-items:center;gap:10px;margin-bottom:8px}.icon{width:38px;height:38px;border-radius:11px;background:#eff6ff;color:#2563eb;display:grid;place-items:center;font-weight:900}.card h3{font-size:16px;margin:0}.card p{font-size:13px;line-height:1.5;margin:0 0 10px;color:var(--muted)}.links{border-top:1px solid var(--line);margin-top:12px;padding-top:7px}.links a{display:flex;justify-content:space-between;padding:9px 0;color:#334155;text-decoration:none;font-size:13px;font-weight:700}.links a:hover{color:var(--accent)}.links a:after{content:'›';color:#94a3b8}.quick{margin:28px 0}.quickbar{display:flex;flex-wrap:wrap;gap:10px}.quickbar a{background:#fff;border:1px solid var(--line);padding:11px 15px;border-radius:11px;text-decoration:none;color:#334155;font-size:13px;font-weight:800}.quickbar a:hover{border-color:#bfdbfe;color:var(--accent)}footer{margin-top:35px;color:#94a3b8;font-size:12px;text-align:center}@media(max-width:950px){.stats,.grid{grid-template-columns:repeat(2,1fr)}}@media(max-width:600px){.top{padding:0 18px}.account .email{display:none}.wrap{padding:25px 15px}.welcome{display:block}.welcome h1{font-size:25px}.stats,.grid{grid-template-columns:1fr}.stat{padding:17px}}
</style></head>
<body>
<header class="top"><div class="logo">Resell<span>Nom</span></div><div class="account"><span class="email"><?=htmlspecialchars($displayName,ENT_QUOTES,'UTF-8')?></span><div class="avatar"><?=htmlspecialchars(strtoupper(substr($displayName,0,1)),ENT_QUOTES,'UTF-8')?></div></div></header>
<main class="wrap">
<section class="welcome"><div><h1>Welcome back, <?=htmlspecialchars($displayName,ENT_QUOTES,'UTF-8')?> 👋</h1><div class="muted">Your domain business, billing and reseller tools in one place.</div></div><span class="pill"><?=htmlspecialchars($roleLabel,ENT_QUOTES,'UTF-8')?></span></section>
<section class="stats">
<div class="stat"><div class="stat-label">Wallet Balance</div><div class="stat-value">৳ <?=$balance?></div><a class="stat-link" href="#">View transactions →</a></div>
<div class="stat"><div class="stat-label">Active Domains</div><div class="stat-value">—</div><a class="stat-link" href="#">Manage domains →</a></div>
<div class="stat"><div class="stat-label">Pending Orders</div><div class="stat-value">—</div><a class="stat-link" href="#">View orders →</a></div>
<div class="stat"><div class="stat-label">Quick Search</div><div class="stat-value">Domain</div><a class="stat-link" href="#">Search a domain →</a></div>
</section>
<section class="quick"><h2 class="section-title">Quick Actions</h2><div class="quickbar"><a href="#">🔎 Search Domain</a><a href="#">＋ Register Domain</a><a href="#">↔ Transfer Domain</a><a href="#">↻ Renew Domain</a></div></section>
<section><h2 class="section-title"><?= $isAdmin ? 'Administration' : ($isReseller ? 'Reseller Tools' : 'Domain Services') ?></h2><div class="grid">
<?php foreach($groups as $group): ?><article class="card"><div class="card-head"><div class="icon"><?=htmlspecialchars($group['icon'])?></div><h3><?=htmlspecialchars($group['title'])?></h3></div><p>Manage <?=htmlspecialchars(strtolower($group['title']))?> securely from your dashboard.</p><div class="links"><?php foreach($group['items'] as $item): ?><a href="<?=htmlspecialchars($item[0],ENT_QUOTES,'UTF-8')?>"><?=htmlspecialchars($item[1])?></a><?php endforeach; ?></div></article><?php endforeach; ?>
</div></section>
<footer>ResellNom Domain Platform · Secure reseller infrastructure</footer>
</main></body></html>
