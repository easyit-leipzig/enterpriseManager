<?php
declare(strict_types=1);

if(session_status()!==PHP_SESSION_ACTIVE)session_start();
require __DIR__.'/system/ui/layout.php';

$kernel=require __DIR__.'/DataForm5-Core/bootstrap/app.php';
$installer=$kernel->container()->get(\DataForm5\Installer\Core\EnterpriseInstaller::class);
if(empty($_SESSION['setup_csrf']))$_SESSION['setup_csrf']=bin2hex(random_bytes(32));
$csrf=(string)$_SESSION['setup_csrf'];
$status=$installer->status();
$message='';$error='';$result=null;
$form=[
 'db_host'=>'127.0.0.1','db_port'=>'3306','db_database'=>'easyit_admin','db_username'=>'root','db_password'=>'',
 'admin_username'=>'admin','admin_email'=>'','admin_password'=>'','admin_password_confirm'=>'',
 'products'=>['dataform'],'environment'=>'production','timezone'=>'Europe/Stockholm',
];
if($_SERVER['REQUEST_METHOD']==='POST'){
 $form=[
  'db_host'=>trim((string)($_POST['db_host']??'127.0.0.1')),'db_port'=>(string)($_POST['db_port']??'3306'),
  'db_database'=>trim((string)($_POST['db_database']??'easyit_admin')),'db_username'=>trim((string)($_POST['db_username']??'root')),
  'db_password'=>(string)($_POST['db_password']??''),'admin_username'=>trim((string)($_POST['admin_username']??'admin')),
  'admin_email'=>trim((string)($_POST['admin_email']??'')),'admin_password'=>(string)($_POST['admin_password']??''),
  'admin_password_confirm'=>(string)($_POST['admin_password_confirm']??''),
  'products'=>array_values(array_intersect(['dataform','dialog','nachhilfe','csv-engine'],(array)($_POST['products']??[]))),
  'environment'=>in_array((string)($_POST['environment']??''),['production','development'],true)?(string)$_POST['environment']:'production',
  'timezone'=>trim((string)($_POST['timezone']??'Europe/Stockholm')),
 ];
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    try{
        if(!hash_equals($csrf,(string)($_POST['csrf_token']??'')))throw new RuntimeException('CSRF-Prüfung fehlgeschlagen.');
        $password=(string)($_POST['admin_password']??'');
        $confirm=(string)($_POST['admin_password_confirm']??'');
        if($password!==$confirm)throw new RuntimeException('Admin-Kennwörter stimmen nicht überein.');
        $result=$installer->install([
            'environment'=>$form['environment'],
            'timezone'=>$form['timezone'],
            'database'=>[
                'host'=>$form['db_host'],
                'port'=>(int)$form['db_port'],
                'database'=>$form['db_database'],
                'username'=>$form['db_username'],
                'password'=>$form['db_password'],
            ],
            'admin'=>[
                'username'=>$form['admin_username'],
                'email'=>$form['admin_email'],
                'password'=>$password,
            ],
            'products'=>$form['products'],
        ]);
        $message='Installation erfolgreich abgeschlossen. Das Health-Gate ist grün und der Install-Lock wurde gesetzt.';
        $status=$installer->status();
    }catch(Throwable $e){$error=$e->getMessage();}
}

ob_start();
?>
<section class="hero"><span class="badge">RC1.8 Installer 2.0 · HF12</span><h1>Installation & Administration</h1>
<p>Neuinstallation, Konfigurationsreparatur und Datenbankprüfung bleiben auch nach einer abgeschlossenen Installation erreichbar.</p>
<div class="actions">
<a class="button" href="installer/database.php">Datenbank-Assistent öffnen</a>
<a class="button secondary" href="recovery.php">Recovery / Reset</a>
<a class="button secondary" href="app/dashboard.php">Enterprise-Dashboard</a>
</div>
</section>

<?php if($message):?><div class="notice success"><?=e($message)?></div><?php endif;?>
<?php if($error):?><div class="notice error"><?=e($error)?></div><?php endif;?>

<section class="card"><h2>1. Systemprüfung</h2>
<table><thead><tr><th>Prüfung</th><th>Status</th></tr></thead><tbody>
<?php foreach((array)($status['inspection']['checks']??$status['inspection']['requirements']??[]) as $index=>$value):
if(is_array($value)){
    $name=(string)($value['name']??$index);
    $ok=(bool)($value['passed']??$value['ok']??false);
    $required=(bool)($value['required']??true);
    $detail=trim((string)($value['message']??''));
}else{
    $name=(string)$index;
    $ok=(bool)$value;
    $required=true;
    $detail='';
}
$statusLabel=$ok?'<strong>OK</strong>':($required?'Fehler':'Optional');?>
<tr><td><?=e($name)?><?php if($detail!==''):?><br><small><?=e($detail)?></small><?php endif;?></td><td><?=$statusLabel?></td></tr>
<?php endforeach;?>
<?php foreach((array)($status['inspection']['required_extensions']??[]) as $name=>$ok):?>
<tr><td>Extension <?=e((string)$name)?></td><td><?=$ok?'<strong>OK</strong>':'Fehlt'?></td></tr>
<?php endforeach;?>
</tbody></table></section>

<?php if($status['installed']??false):?>
<section class="card"><h2>Installation abgeschlossen – Wartungsmodus verfügbar</h2>
<p>Der Install-Lock ist gesetzt. Eine Neuinstallation wird nicht automatisch gestartet. Sie können Konfiguration und Datenbanken trotzdem kontrolliert prüfen oder reparieren.</p>
<div class="actions">
<a class="button" <?= easyit_button_attributes('system_reparieren') ?> href="installer/database.php">Datenbanken / .env prüfen und reparieren</a>
<a class="button secondary" <?= easyit_button_attributes('suchen') ?> href="health.php">Systemprüfung</a>
<a class="button secondary" href="recovery.php">Recovery / Reset</a>
<a class="button secondary" href="login.php">Zur Anmeldung</a>
</div>
<p><strong>Sicherheitsregel:</strong> Bestehende Datenbanken werden vom Datenbank-Assistenten nicht ungefragt überschrieben; registrierte Migrationen werden anhand ihrer Checksums mit <code>SKIP … Checksum OK</code> erkannt.</p>
</section>
<?php else:?>
<form method="post">
<input type="hidden" name="csrf_token" value="<?=e($csrf)?>">
<section class="card"><h2>2. Administrationsdatenbank</h2>
<div class="form-grid">
<label>Host<input name="db_host" value="<?=e($form['db_host'])?>" required></label>
<label>Port<input name="db_port" type="number" value="<?=e($form['db_port'])?>" required></label>
<label>Datenbank<input name="db_database" value="<?=e($form['db_database'])?>" required pattern="[A-Za-z0-9_]+"></label>
<label>DB-Benutzer<input name="db_username" value="<?=e($form['db_username'])?>" required></label>
<label>DB-Kennwort<input name="db_password" type="password" value="<?=e($form['db_password'])?>" autocomplete="new-password" data-password-field></label>
</div></section>

<section class="card"><h2>3. Erstadministrator</h2>
<div class="form-grid">
<label>Benutzername<input name="admin_username" value="<?=e($form['admin_username'])?>" required></label>
<label>E-Mail<input name="admin_email" type="email" value="<?=e($form['admin_email'])?>"></label>
<label>Kennwort<input name="admin_password" type="password" value="<?=e($form['admin_password'])?>" required data-password-field minlength="12" autocomplete="new-password"></label>
<label>Kennwort wiederholen<input name="admin_password_confirm" type="password" value="<?=e($form['admin_password_confirm'])?>" required data-password-field minlength="12" autocomplete="new-password"></label>
</div></section>

<section class="card"><h2>4. Produkte</h2>
<label><input type="checkbox" name="products[]" value="dataform" <?=in_array('dataform',$form['products'],true)?'checked':''?>> DataForm</label><br>
<label><input type="checkbox" name="products[]" value="dialog" <?=in_array('dialog',$form['products'],true)?'checked':''?>> Dialog</label><br>
<label><input type="checkbox" name="products[]" value="nachhilfe" <?=in_array('nachhilfe',$form['products'],true)?'checked':''?>> Nachhilfe</label><br>
<label><input type="checkbox" name="products[]" value="csv-engine" <?=in_array('csv-engine',$form['products'],true)?'checked':''?>> CSV-Engine</label>
</section>

<section class="card"><h2>5. Umgebung & Abschluss</h2>
<div class="form-grid">
<label>Umgebung<select name="environment"><option value="production" <?=$form['environment']==='production'?'selected':''?>>production</option><option value="development" <?=$form['environment']==='development'?'selected':''?>>development</option></select></label>
<label>Zeitzone<input name="timezone" value="<?=e($form['timezone'])?>"></label>
</div>
<p>Der Install-Lock wird erst gesetzt, wenn das abschließende Health-Gate erfolgreich ist.</p>
<button class="button" type="submit">Installation ausführen</button>
</section>
</form>
<script>
document.querySelectorAll('input[data-password-field]').forEach(function(input){
 var b=document.createElement('button'); b.type='button'; b.className='password-toggle'; b.textContent='👁';
 b.title='Kennwort anzeigen'; b.setAttribute('aria-label','Kennwort anzeigen');
 input.insertAdjacentElement('afterend',b);
 b.addEventListener('click',function(){var show=input.type==='password';input.type=show?'text':'password';b.title=show?'Kennwort verbergen':'Kennwort anzeigen';b.setAttribute('aria-label',b.title);});
});
</script>
<style>.password-toggle{margin-left:.35rem;border:1px solid #ccd5e2;border-radius:.4rem;background:#fff;padding:.35rem .55rem;cursor:pointer}</style>
<?php endif;?>
<?php
$content=ob_get_clean();
render_page(['title'=>'Installer 2.0','active'=>'setup','content'=>$content,'help'=>[
'title'=>'Installer 2.0','location'=>'Setup → Installer',
'short'=>'Installiert easyIT Enterprise über einen konsolidierten Ablauf.',
'goal'=>'Einen reproduzierbaren und überprüften Ausgangszustand herstellen.',
'next'=>'Systemprüfung kontrollieren und anschließend Datenbank- und Adminkonto eingeben.',
'steps'=>['Systemprüfung prüfen.','Admin-Datenbank konfigurieren.','Erstadmin anlegen.','Produkte auswählen.','Health-Gate ausführen und Lock setzen.'],
'tips'=>['Das Admin-Kennwort ist nicht das Datenbankkennwort.','Ein bestehender Install-Lock verhindert versehentliche Neuinstallation.','Das Kennwort wird nur als Passwort-Hash gespeichert.']
]]);
