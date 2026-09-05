<?php
declare(strict_types=1);
require __DIR__.'/system/app/bootstrap.php';
require __DIR__.'/system/ui/layout.php';
if (enterprise_user()) { header('Location: app/dashboard.php'); exit; }
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    try {
        enterprise_check_csrf((string)($_POST['csrf_token']??''));
        $pdo=enterprise_pdo(); enterprise_upgrade($pdo);
        $stmt=$pdo->prepare("SELECT u.id,u.username,u.email,u.password_hash,u.is_active,GROUP_CONCAT(r.name) roles FROM users u LEFT JOIN user_roles ur ON ur.user_id=u.id LEFT JOIN roles r ON r.id=ur.role_id WHERE u.username=? OR u.email=? GROUP BY u.id LIMIT 1");
        $login=trim((string)($_POST['login']??'')); $stmt->execute([$login,$login]); $row=$stmt->fetch();
        if (!$row || !(bool)$row['is_active'] || !password_verify((string)($_POST['password']??''),(string)$row['password_hash'])) throw new RuntimeException('Benutzername/E-Mail oder Kennwort ist falsch.');
        session_regenerate_id(true);
        $_SESSION['enterprise_user']=['id'=>(int)$row['id'],'username'=>$row['username'],'email'=>$row['email'],'roles'=>$row['roles']?explode(',',$row['roles']):[],'permissions'=>enterprise_permissions($pdo,(int)$row['id'])];
        enterprise_audit($pdo,(int)$row['id'],'auth.login','user',(string)$row['id']);
        enterprise_event_dispatch('auth.user.logged_in', ['user_id'=>(int)$row['id'],'username'=>(string)$row['username']], ['source'=>'login']);
        $next=(string)($_GET['next']??'');
        header('Location: '.($next!=='' && str_starts_with($next,'/') ? $next : 'app/dashboard.php')); exit;
    } catch(Throwable $e) { $error=$e->getMessage(); }
}
ob_start(); ?>
<section class="hero"><span class="badge">Enterprise-Anmeldung</span><h1>Bei easyIT Enterprise anmelden</h1><p>Verwenden Sie das in Schritt 7 angelegte Administratorkonto.</p></section>
<?php if($error): ?><div class="notice error" role="alert"><?=e($error)?></div><?php endif; ?>
<section class="card login-card"><form method="post" class="form-grid single-column"><input type="hidden" name="csrf_token" value="<?=e(enterprise_csrf())?>"><label>Benutzername oder E-Mail<input name="login" required autofocus autocomplete="username"></label><label>Kennwort<input type="password" name="password" required autocomplete="current-password"></label><div><button class="button" type="submit">Anmelden</button></div></form></section>
<?php $content=ob_get_clean(); render_page(['title'=>'Anmeldung','active'=>'login','content'=>$content,'help'=>['title'=>'Anmeldung','location'=>'Enterprise → Anmeldung','short'=>'Melden Sie sich mit dem während der Installation angelegten Administratorkonto an.','goal'=>'Geschützten Zugang zum Enterprise-Dashboard erhalten.','tips'=>['Das Datenbankkennwort ist nicht das Administratorkennwort.','Bei Problemen kann im Installer geprüft werden, ob das Konto vorhanden ist.']]]);
