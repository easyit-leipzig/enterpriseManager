<?php
declare(strict_types=1);
require __DIR__ . '/system/ui/layout.php';
$checks=[];
$checks[]=['PHP-Version >= 8.2',version_compare(PHP_VERSION,'8.2.0','>='),PHP_VERSION];
foreach(['pdo','json','mbstring','openssl','filter','session'] as $ext){$checks[]=['PHP-Erweiterung: '.$ext,extension_loaded($ext),extension_loaded($ext)?'geladen':'fehlt'];}
$checks[]=['PHP-Erweiterung: zip',extension_loaded('zip'),extension_loaded('zip')?'geladen':'optional – für ZIP-Pakete erforderlich'];
foreach(['storage','workspace','packages'] as $dir){$path=__DIR__.'/'.$dir;$checks[]=['Schreibrecht: '.$dir,is_dir($path)&&is_writable($path),is_dir($path)?(is_writable($path)?'beschreibbar':'nicht beschreibbar'):'fehlt'];}
$criticalOk=true;foreach($checks as $i=>$c){if($i<7&&!$c[1]){$criticalOk=false;}}
ob_start(); ?>
<section class="hero"><span class="badge">Diagnose</span><h1>Systemprüfung</h1><p>Die Prüfung verändert keine Dateien und keine Datenbanken.</p></section>
<p class="notice"><strong>Gesamtergebnis:</strong> <?= $criticalOk ? 'Grundvoraussetzungen erfüllt.' : 'Mindestens eine Grundvoraussetzung fehlt.' ?></p>
<div class="status-list"><?php foreach($checks as [$label,$ok,$detail]): ?><div class="status"><span><?= e($label) ?><br><small><?= e((string)$detail) ?></small></span><strong class="<?= $ok?'ok':'bad' ?>"><?= $ok?'OK':'PRÜFEN' ?></strong></div><?php endforeach; ?></div>
<nav class="page-actions" aria-label="Seitennavigation"><a class="button secondary" <?= easyit_button_attributes('zurueck') ?> href="index.php">← Zurück zur Startseite</a><a class="button" <?= easyit_button_attributes('weiter') ?> href="setup.php#step-5">Weiter zu Schritt 5 →</a></nav>
<?php $content=ob_get_clean();
render_page(['title'=>'Systemprüfung','active'=>'health','content'=>$content,'help'=>['title'=>'Systemprüfung','location'=>'Setup → Systemprüfung','short'=>'Hier sehen Sie, ob die lokale PHP-Umgebung für die nächsten Phasen vorbereitet ist.','goal'=>'PHP-Version, Erweiterungen und Schreibrechte sicher prüfen.','next'=>'Nach erfolgreicher Prüfung mit Schritt 5 des Setup-Tutorials fortfahren.','steps'=>['Alle Zeilen prüfen.','Fehlende PHP-Erweiterungen in php.ini aktivieren.','Apache nach Änderungen neu starten.','Seite erneut laden.'],'examples'=>['extension=zip','extension=mbstring','extension=pdo_mysql'],'tips'=>['ZIP ist für Paketexporte notwendig.','Die Datenbankverbindung wird in einer späteren Phase separat geprüft.'],'duration'=>'ca. 2 Minuten']]);
