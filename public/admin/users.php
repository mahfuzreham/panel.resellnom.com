<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/config/bootstrap.php';
require dirname(__DIR__, 2) . '/app/Auth/Auth.php';

use ResellNom\Auth\Auth;

$auth = new Auth(db());
$admin = $auth->requireRole(['admin']);
$pdo = db();
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf((string)($_POST['csrf'] ?? ''));
        $action = (string)($_POST['action'] ?? '');
        if ($action === 'create') {
            $name = trim((string)($_POST['name'] ?? ''));
            $email = strtolower(trim((string)($_POST['email'] ?? '')));
            $role = (string)($_POST['role'] ?? 'reseller');
            $password = (string)($_POST['password'] ?? '');
            if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($role, ['reseller','sub_reseller','client'], true) || strlen($password) < 10) {
                throw new RuntimeException('Name, valid email, allowed role and password (10+ characters) are required.');
            }
            if ($role === 'sub_reseller') {
                $parentId = (int)($_POST['parent_id'] ?? 0);
                $check = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role IN ('reseller','admin') AND status = 'active'");
                $check->execute([$parentId]);
                if (!$check->fetchColumn()) throw new RuntimeException('Select a valid active reseller/admin parent.');
            } else {
                $parentId = null;
            }
            $stmt = $pdo->prepare('INSERT INTO users (parent_id, role, name, email, password_hash, status) VALUES (?, ?, ?, ?, ?, \'active\')');
            $stmt->execute([$parentId, $role, $name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            $message = 'User created successfully.';
        } elseif ($action === 'status') {
            $id = (int)($_POST['id'] ?? 0);
            $status = (string)($_POST['status'] ?? '');
            if ($id === (int)$admin['id'] || !in_array($status, ['active','suspended'], true)) throw new RuntimeException('Invalid user status change.');
            $stmt = $pdo->prepare('UPDATE users SET status = ? WHERE id = ? AND role <> \'admin\'');
            $stmt->execute([$status, $id]);
            $message = 'User status updated.';
        }
    } catch (Throwable $e) { $error = $e->getMessage(); }
}

$users = $pdo->query("SELECT u.id,u.name,u.email,u.role,u.status,u.balance,u.currency,u.created_at,p.name parent_name FROM users u LEFT JOIN users p ON p.id=u.parent_id ORDER BY u.id DESC LIMIT 200")->fetchAll();
$resellers = $pdo->query("SELECT id,name FROM users WHERE role IN ('admin','reseller') AND status='active' ORDER BY name")->fetchAll();
?><!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Users · ResellNom</title><style>body{font-family:system-ui;margin:0;background:#f5f7fb;color:#111827}.wrap{max-width:1200px;margin:30px auto;padding:20px}.card{background:#fff;padding:22px;border-radius:14px;margin-bottom:20px;overflow:auto}input,select{padding:10px;border:1px solid #ddd;border-radius:8px;margin:5px}button{padding:9px 12px;border:0;border-radius:7px;background:#111827;color:#fff}.err{color:#991b1b}.ok{color:#166534}table{width:100%;border-collapse:collapse}td,th{text-align:left;padding:10px;border-bottom:1px solid #eee;white-space:nowrap}</style></head><body><main class="wrap"><p><a href="/">← Dashboard</a></p><h1>Admin · Users</h1><?php if($message):?><p class="ok"><?=htmlspecialchars($message)?></p><?php endif;?><?php if($error):?><p class="err"><?=htmlspecialchars($error)?></p><?php endif;?><section class="card"><h2>Create reseller / client</h2><form method="post"><input type="hidden" name="csrf" value="<?=htmlspecialchars(csrf_token())?>"><input type="hidden" name="action" value="create"><input name="name" placeholder="Name" required><input name="email" type="email" placeholder="Email" required><select name="role"><option value="reseller">Reseller</option><option value="sub_reseller">Sub-Reseller</option><option value="client">Client</option></select><select name="parent_id"><option value="0">Parent (for Sub-Reseller)</option><?php foreach($resellers as $r):?><option value="<?=$r['id']?>"><?=htmlspecialchars($r['name'])?> (#<?=$r['id']?>)</option><?php endforeach;?></select><input name="password" type="password" minlength="10" placeholder="Temporary password" required><button>Create User</button></form></section><section class="card"><h2>Accounts</h2><table><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Balance</th><th>Parent</th><th>Action</th></tr><?php foreach($users as $u):?><tr><td><?=$u['id']?></td><td><?=htmlspecialchars($u['name'])?></td><td><?=htmlspecialchars($u['email'])?></td><td><?=htmlspecialchars($u['role'])?></td><td><?=htmlspecialchars($u['status'])?></td><td><?=number_format((float)$u['balance'],2).' '.htmlspecialchars($u['currency'])?></td><td><?=htmlspecialchars($u['parent_name'] ?? '-')?></td><td><?php if($u['role']!=='admin'):?><form method="post"><input type="hidden" name="csrf" value="<?=htmlspecialchars(csrf_token())?>"><input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?=$u['id']?>"><input type="hidden" name="status" value="<?=$u['status']==='active'?'suspended':'active'?>"><button><?=$u['status']==='active'?'Suspend':'Activate'?></button></form><?php endif;?></td></tr><?php endforeach;?></table></section></main></body></html>
