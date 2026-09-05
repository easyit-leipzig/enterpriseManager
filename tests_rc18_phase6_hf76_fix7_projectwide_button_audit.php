<?php
declare(strict_types=1);
$root=__DIR__;
require_once $root.'/system/ui/ButtonRegistry.php';
$registry=easyit_button_registry();
$css=(string)file_get_contents($root.'/assets/css/easyit-crud-3d-buttons.css');
$js=(string)file_get_contents($root.'/assets/js/easyit-button-registry.js');
$runtime=(string)file_get_contents($root.'/products/dataform/runtime.php');
$checks=[];$check=function(string $name,bool $ok)use(&$checks){$checks[]=$ok;echo ($ok?'PASS':'FAIL')." - $name\n";};
$check('canonical button registry extended',count($registry)>=56);
$check('every type has central title',count(array_filter($registry,static fn(array $d):bool=>trim((string)($d['title']??''))!==''))===count($registry));
$check('every type has canonical PNG',count(array_filter($registry,static fn(array $d):bool=>is_file(__DIR__.'/'.(string)($d['image']??''))))===count($registry));
$check('canonical CSS contains no gradients',!str_contains($css,'linear-gradient(')&&!str_contains($css,'radial-gradient('));
$check('canonical button backgrounds transparent',str_contains($css,'background-color:transparent!important')&&str_contains($css,'box-shadow:none!important'));
$check('all registry CSS image mappings present',count(array_filter(array_keys($registry),static fn(string $k):bool=>str_contains($css,'data-button="'.$k.'"')))===count($registry));
$check('browser records unresolved actions for diagnostics',str_contains($js,"data-button-unresolved"));
$check('link-button is no longer globally skipped',!str_contains($js,"classList.contains('link-button')"));
$check('password toggle uses central context',str_contains($js,"context='password_toggle'")&&easyit_button_title('anzeigen','password_toggle')==='Kennwort anzeigen oder verbergen');
$check('DataForm open remains formular.png',str_contains($runtime,"easyit_button_attributes('formular','dataform')")&&($registry['formular']['image']??'')==='assets/img/formular.png');
$check('DataForm records remains anzeigen.png',str_contains($runtime,"easyit_button_attributes('anzeigen','dataform_records')")&&($registry['anzeigen']['image']??'')==='assets/img/anzeigen.png');
$check('project switch has explicit central context',str_contains($runtime,"easyit_button_attributes('auswaehlen','project_switch')")&&easyit_button_title('auswaehlen','project_switch')==='Projekt wechseln');
$expected=[
 'Neues Projekt anlegen'=>'neu','Projekt registrieren'=>'projekt_registrieren','Lizenz anlegen'=>'neu','Rolle anlegen'=>'neu','API anlegen'=>'neu','Endpunkt anlegen'=>'neu',
 'Felder verwalten'=>'bearbeiten','Benutzer verwalten'=>'bearbeiten','Workflow'=>'bearbeiten',
 'Löschen'=>'loeschen','Widerrufen'=>'loeschen','Entziehen'=>'loeschen','Zurücknehmen'=>'rueckgaengig',
 'Änderungen speichern'=>'speichern','Konfiguration speichern'=>'speichern','Eigenschaften speichern'=>'speichern','Positionen speichern'=>'speichern',
 'Abbrechen'=>'abbrechen','Zur Projektliste'=>'zurueck','Vorschau verwerfen'=>'abbrechen',
 'Öffnen'=>'anzeigen','Datensätze anzeigen'=>'anzeigen','Details öffnen'=>'anzeigen',
 'System prüfen'=>'suchen','Verbindung testen'=>'suchen','Paket prüfen'=>'suchen','Duplikate prüfen'=>'suchen',
 'Filtern'=>'filter','Zurücksetzen'=>'filter_loeschen','Neu einlesen'=>'aktualisieren','Snapshot aktualisieren'=>'aktualisieren',
 'Formular-Designer'=>'formular','DataForm öffnen'=>'formular','Formular gestalten'=>'formular',
 'Geprüftes Paket importieren'=>'importieren','ZIP installieren'=>'importieren','Update installieren'=>'importieren',
 'CSV'=>'exportieren','Word'=>'exportieren','OpenAPI JSON'=>'exportieren','Build-ZIP erzeugen'=>'exportieren',
 'Druck-/PDF-Vorschau'=>'drucken','Vollbackup jetzt erstellen'=>'backup','Projektsicherung wiederherstellen'=>'restore',
 'Konfiguration'=>'einstellungen','Datenbank-Assistent'=>'datenbank_assistent','Capability-Matrix'=>'security_capabilities',
 'Setup-Tutorial'=>'hilfe','Installation ausführen'=>'bestaetigen','Worker jetzt ausführen'=>'bestaetigen',
 'Zum Dashboard'=>'start','Anmelden'=>'anmelden','Audit-Protokoll'=>'verlauf','Primär'=>'favorit','Aktivieren'=>'entsperren','Deaktivieren'=>'sperren',
];
foreach($expected as $label=>$type){$check('central mapping: '.$label,easyit_button_type_for_text($label)===$type);}
foreach(['erster_ds','vorheriger_ds','aktueller_ds','naechster_ds','letzter_ds','neuer_ds','normaler_ds'] as $key){$check($key.' record-navigation scope',($registry[$key]['scope']??'')==='record_navigation');}
$failed=count(array_filter($checks,static fn(bool $v):bool=>!$v));
echo 'HF76-FIX7 project-wide button audit: '.(count($checks)-$failed).'/'.count($checks).' PASS'.PHP_EOL;
exit($failed?1:0);
