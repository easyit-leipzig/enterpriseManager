<?php
declare(strict_types=1);
require __DIR__.'/system/app/bootstrap.php';
if ($u=enterprise_user()) { try { enterprise_audit(enterprise_pdo(),(int)$u['id'],'auth.logout','user',(string)$u['id']); enterprise_event_dispatch('auth.user.logged_out',['user_id'=>(int)$u['id'],'username'=>(string)$u['username']],['source'=>'logout']); } catch(Throwable $e) {} }
$_SESSION=[]; if (ini_get('session.use_cookies')) { $p=session_get_cookie_params(); setcookie(session_name(),'',time()-42000,$p['path'],$p['domain'],$p['secure'],$p['httponly']); } session_destroy(); header('Location: login.php'); exit;
