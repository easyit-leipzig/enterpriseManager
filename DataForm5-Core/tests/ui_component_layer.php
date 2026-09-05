<?php
declare(strict_types=1);
use DataForm5\UI\Core\UiManager;
$kernel=require dirname(__DIR__).'/bootstrap/app.php';
$ui=$kernel->container()->get(UiManager::class);
$button=$ui->button('Speichern','/save')->render();assert(str_contains($button,'df5-btn--primary'));assert(str_contains($button,'Speichern'));
$alert=$ui->alert('<script>x</script>','warning','Achtung')->render();assert(str_contains($alert,'&lt;script&gt;x&lt;/script&gt;'));assert(!str_contains($alert,'<script>'));
$table=$ui->table(['id'=>'ID','name'=>'Name'],[['id'=>1,'name'=>'Demo']])->render();assert(str_contains($table,'<th scope="col">Name</th>'));assert(str_contains($table,'Demo'));
$dialog=$ui->dialog('project-dialog','Projekt','Inhalt',['open'=>true])->render();assert(str_contains($dialog,'<dialog'));assert(str_contains($dialog,'open'));
$nav=$ui->navigation([['label'=>'Projekte','href'=>'/projects','key'=>'projects']], 'projects')->render();assert(str_contains($nav,'aria-current="page"'));
$help=$ui->helpPanel('Hilfe',['short'=>'Kurz','steps'=>['Eins','Zwei']],'steps')->render();assert(str_contains($help,'data-help-panel="steps"'));assert(str_contains($help,'<ol>'));
echo "PASS: UI Component Library\n";
