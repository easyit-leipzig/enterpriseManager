<?php
declare(strict_types=1);

require dirname(__DIR__).'/system/app/bootstrap.php';
require dirname(__DIR__).'/system/ui/layout.php';

$user=enterprise_require_auth('../');
$error='';$projects=[];$products=[];$counts=['projects'=>0,'users'=>0,'products'=>0,'licenses'=>0];
try{
    $pdo=enterprise_pdo();
    enterprise_upgrade($pdo);
    $projects=$pdo->query('SELECT * FROM projects ORDER BY updated_at DESC LIMIT 8')->fetchAll();
    $products=$pdo->query('SELECT * FROM installed_products ORDER BY id')->fetchAll();
    $counts['projects']=(int)$pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn();
    $counts['users']=(int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $counts['products']=(int)$pdo->query("SELECT COUNT(*) FROM installed_products WHERE status='available'")->fetchColumn();
    $counts['licenses']=(int)$pdo->query("SELECT COUNT(*) FROM enterprise_licenses WHERE enabled=1")->fetchColumn();
}catch(Throwable $e){$error=$e->getMessage();}

$ops=enterprise_dashboard_snapshot();
try{$moduleCards=enterprise_module_ui()->dashboard($user);}catch(Throwable){$moduleCards=[];}

ob_start();
render_breadcrumbs([['label'=>'Enterprise','href'=>'dashboard.php'],['label'=>'Dashboard','href'=>'']]);
?>
<section class="hero">
<span class="badge">RC1.8 · Enterprise Control Center</span>
<h1>Enterprise-Dashboard</h1>
<p>Willkommen, <strong><?=e((string)$user['username'])?></strong>. Projekte, Produkte und Betriebszustand sind hier zusammengeführt.</p>
<div class="actions">
<a class="button" <?= easyit_button_attributes('neu','project') ?> href="projects/create.php">Neues Projekt anlegen</a>
<a class="button secondary" <?= easyit_button_attributes('projekt_registrieren') ?> href="projects/register.php">Vorhandenes Projekt registrieren</a>
<a class="button secondary" <?= easyit_button_attributes('anzeigen','overview') ?> href="operations/index.php">Betriebszentrale</a>
<?php if(enterprise_can($user,'permissions.manage')):?><a class="button secondary" <?= easyit_button_attributes('security_benutzer') ?> href="security/users.php">Benutzer & Rechte</a><?php endif;?><?php if(enterprise_can($user,'permissions.manage')):?><a class="button secondary" <?= easyit_button_attributes('verlauf') ?> href="security/audit.php">Audit-Protokoll</a><?php endif;?>
<a class="button secondary" <?= easyit_button_attributes('setup') ?> href="../setup.php">Installation / Setup</a>
<a class="button secondary" <?= easyit_button_attributes('datenbank_assistent') ?> href="../installer/database.php">Datenbank-Assistent</a>
<a class="button secondary" <?= easyit_button_attributes('restore') ?> href="../recovery.php">Recovery / Reset</a>
</div>
</section>

<?php if($error):?>
<div class="notice error">
<strong>Administrationskonfiguration unvollständig.</strong><br>
<?=e($error)?>
<div class="actions" style="margin-top:1rem">
<a class="button" <?= easyit_button_attributes('setup') ?> href="../setup.php">Installation / Setup öffnen</a>
<a class="button secondary" <?= easyit_button_attributes('datenbank_assistent') ?> href="../installer/database.php">Datenbank-Assistent öffnen</a>
<a class="button secondary" <?= easyit_button_attributes('restore') ?> href="../recovery.php">Recovery / Reset</a>
</div>
</div>
<?php endif;?>

<div class="metric-grid">
<div class="metric"><strong><?=$counts['projects']?></strong><span>Projekte</span></div>
<div class="metric"><strong><?=$counts['products']?></strong><span>Produkte</span></div>
<div class="metric"><strong><?=e((string)$ops['modules']['active'])?></strong><span>aktive Module</span></div>
<div class="metric"><strong><?=e(strtoupper((string)$ops['overall']))?></strong><span>Betriebszustand</span></div>
</div>

<section>
<div class="section-head"><h2>Betriebsstatus</h2><a href="operations/index.php">Alle Betriebsdetails</a></div>
<div class="cards product-grid">
<article class="card"><span class="status-pill"><?=e((string)$ops['monitoring']['status'])?></span><h2>Monitoring</h2><p><?=e((string)$ops['monitoring']['alerts'])?> aktive Warnung(en).</p><a class="button secondary" href="monitoring/index.php">Öffnen</a></article>
<article class="card"><span class="status-pill"><?=e((string)$ops['jobs']['status'])?></span><h2>Jobs</h2><p><?=e((string)$ops['jobs']['pending'])?> wartend · <?=e((string)$ops['jobs']['failed'])?> fehlgeschlagen.</p><a class="button secondary" href="background/index.php">Öffnen</a></article>
<article class="card"><span class="status-pill"><?=e((string)$ops['cluster']['status'])?></span><h2>Cluster</h2><p><?=e((string)$ops['cluster']['online'])?> / <?=e((string)$ops['cluster']['nodes'])?> Nodes online.</p><a class="button secondary" href="cluster/index.php">Öffnen</a></article>
<article class="card"><span class="status-pill"><?=e((string)$ops['storage']['status'])?></span><h2>Storage</h2><p><?=e((string)$ops['storage']['healthy'])?> / <?=e((string)$ops['storage']['total'])?> Provider healthy.</p><a class="button secondary" href="storage/index.php">Öffnen</a></article>
</div>
</section>

<section>
<h2>Produkte</h2>
<div class="cards product-grid">
<?php foreach($products as $p):?>
<article class="card">
<span class="status-pill <?=e((string)$p['status'])?>"><?=e((string)$p['status'])?></span>
<h2><?=e((string)$p['label'])?></h2>
<p><?=$p['status']==='available'?'Für Projekte verfügbar.':'Derzeit nicht für neue Projekte aktiviert.'?></p>
</article>
<?php endforeach;?>
</div>
</section>

<?php if($moduleCards!==[]):?>
<section><h2>Modulfunktionen</h2><div class="cards product-grid">
<?php foreach($moduleCards as $card):$href=(string)$card['href'];if(!preg_match('~^(?:https?://|/)~',$href))$href='../'.ltrim($href,'/');?>
<article class="card"><h2><?=e((string)$card['label'])?></h2><p><?=e((string)($card['description']??'Modulfunktion öffnen.'))?></p><a class="button secondary" href="<?=e($href)?>">Öffnen</a></article>
<?php endforeach;?>
</div></section>
<?php endif;?>

<section>
<div class="section-head"><h2>Letzte Projekte</h2><a href="projects/index.php">Alle anzeigen</a></div>
<?php if(!$projects):?>
<div class="empty-state"><h3>Noch kein Projekt vorhanden</h3><p>Legen Sie jetzt das erste Produktprojekt an.</p><a class="button" href="projects/create.php">Projekt anlegen</a></div>
<?php else:?>
<div class="table-wrap"><table><thead><tr><th>Name</th><th>Produkt</th><th>Datenbank</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach($projects as $p):?><tr><td><strong><?=e((string)$p['name'])?></strong></td><td><?=e((string)($p['product_type']??'dataform'))?></td><td><code><?=e((string)$p['database_name'])?></code></td><td><?=e((string)$p['status'])?></td><td><a href="projects/view.php?id=<?=(int)$p['id']?>">Öffnen</a></td></tr><?php endforeach;?>
</tbody></table></div>
<?php endif;?>
</section>

<?php
$content=ob_get_clean();
render_page([
    'title'=>'Dashboard','active'=>'dashboard','base'=>'../','content'=>$content,'app_nav'=>true,'user'=>$user,
    'help'=>[
        'title'=>'Enterprise Control Center','location'=>'Enterprise → Dashboard',
        'short'=>'Zentrale Übersicht über Projekte, Produkte, Module und Betriebszustand.',
        'goal'=>'Die Plattform aus einer gemeinsamen Übersicht steuern.',
        'next'=>'Bei Warnungen die Betriebszentrale öffnen; andernfalls Projekt oder Produkt auswählen.',
        'steps'=>['Betriebszustand prüfen.','Produkte und Module kontrollieren.','Projekt auswählen oder neu anlegen.'],
        'tips'=>['Technische Detailseiten sind unter Betrieb gebündelt.','Das Dashboard führt nur Status zusammen und verändert nichts automatisch.']
    ]
]);
