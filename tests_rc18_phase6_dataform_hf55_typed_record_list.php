<?php
declare(strict_types=1);
$root=__DIR__;
$records=(string)file_get_contents($root.'/products/dataform/records.php');
$css=(string)file_get_contents($root.'/assets/css/enterprise.css');
$checks=[];
$check=function(string $label,bool $ok) use (&$checks): void {$checks[]=['label'=>$label,'ok'=>$ok];};
$check('HF55 typed renderer marker present',str_contains($records,'HF55: Render values in the tabular record list'));
$check('typed list renderer exists',str_contains($records,'function df_record_list_control'));
$check('lookup relation detector exists',str_contains($records,'function df_record_list_lookup_relation'));
$check('text input output supported',str_contains($records,"default=>'text'"));
$check('number input output supported',str_contains($records,"'number'=>'number'"));
$check('date input output supported',str_contains($records,"'date'=>'date'"));
$check('datetime-local output supported',str_contains($records,"'datetime'=>'datetime-local'"));
$check('email output supported',str_contains($records,"'email'=>'email'"));
$check('url output supported',str_contains($records,"'url'=>'url'"));
$check('textarea output supported',str_contains($records,'df-list-output-textarea'));
$check('checkbox output supported',str_contains($records,'df-list-output-checkbox'));
$check('select/lookup output supported',substr_count($records,'df-list-output-select')>=2);
$check('table uses typed controls or newer inline editor',str_contains($records,'df_record_list_control($pdo,$field,$v)') || str_contains($records,'df-record-inline-edit-cell'));
$check('old flattened cell renderer removed',!str_contains($records,'mb_strimwidth(df_display_record_value($pdo,$field,$v),0,80,\'…\')'));
$check('controls have no record value name attribute',!str_contains($records,'df-list-output-control" name='));
$check('typed list CSS exists',str_contains($css,'HF55: field-type-aware'));
$failed=array_values(array_filter($checks,static fn(array $c): bool => !$c['ok']));
foreach($checks as $c){echo ($c['ok']?'PASS':'FAIL').' '.$c['label'].PHP_EOL;}
exit($failed?1:0);
