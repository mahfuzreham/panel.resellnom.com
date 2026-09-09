<?php
declare(strict_types=1);

require dirname(__DIR__) . '/config/bootstrap.php';
require dirname(__DIR__) . '/app/Auth/Auth.php';

use ResellNom\Auth\Auth;

$auth = new Auth(db());
if ($auth->user()) { header('Location: /'); exit; }
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf($_POST['csrf'] ?? '');
        if ($auth->attempt((string)($_POST['email'] ?? ''), (string)($_POST['password'] ?? ''))) {
            header('Location: /'); exit;
        }
        $error = 'Invalid email or password.';
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Login · ResellNom</title>
<style>body{font-family:system-ui;margin:0;background:#f5f7fb;display:grid;place-items:center;min-height:100vh}.card{width:min(420px,90vw);background:#fff;padding:32px;border-radius:16px;box-shadow:0 10px 35px #0001}input{width:100%;box-sizing:border-box;padding:12px;margin:7px 0 15px;border:1px solid #ddd;border-radius:8px}button{width:100%;padding:12px;border:0;border-radius:8px;background:#111827;color:white;font-weight:700}.err{color:#b91c1c;background:#fee2e2;padding:10px;border-radius:8px;margin-bottom:15px}</style></head>
<body><main class="card"><h1>ResellNom</h1><p>Domain Platform Login</p><?php if($error): ?><div class="err"><?=htmlspecialchars($error, ENT_QUOTES, 'UTF-8')?></div><?php endif; ?><form method="post" autocomplete="off"><input type="hidden" name="csrf" value="<?=htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8')?>"><label>Email</label><input type="email" name="email" required autocomplete="username"><label>Password</label><input type="password" name="password" required autocomplete="current-password"><button type="submit">Sign in</button></form></main></body></html>
