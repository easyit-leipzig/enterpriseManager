<?php
declare(strict_types=1);

$root=__DIR__;
$relations=(string)file_get_contents($root.'/products/dataform/relations.php');
$css=(string)file_get_contents($root.'/products/dataform/assets/workspace.css');

$tests=[];
function check_hf42(string $name,bool $ok): void {
    global $tests;
    $tests[] = [$name,$ok];
    echo ($ok?'[PASS] ':'[FAIL] ').$name.PHP_EOL;
}

check_hf42('HF42 marker', preg_match('/DataForm Workspace · HF(?:42|4[3-9]|[5-9][0-9])/', $relations)===1);
check_hf42('relation type explained', str_contains($relations,'Beziehungstyp / Kardinalität'));
check_hf42('1:n parent label precise', str_contains($relations,'Eltern-DataForm – besitzt die Kinddatensätze'));
check_hf42('1:n child label precise', str_contains($relations,'Kind-DataForm – speichert die Eltern-ID'));
check_hf42('n:1 source label precise', str_contains($relations,'Ausgangs-DataForm – hier erfolgt die Auswahl'));
check_hf42('n:1 lookup label precise', str_contains($relations,'Lookup-/Referenz-DataForm – liefert die auswählbaren Datensätze'));
check_hf42('lookup source field explained', str_contains($relations,'Zuordnungsfeld im Ausgangs-DataForm – speichert die Referenz-ID'));
check_hf42('lookup display field explained', str_contains($relations,'Anzeigefeld des Referenzdatensatzes – sichtbarer Wert im Auswahlfeld'));
check_hf42('fixed lookup id explained', str_contains($relations,'Technischer Referenzschlüssel:'));
check_hf42('1:n display field explained', str_contains($relations,'Anzeigefeld des Eltern-Datensatzes im Kindformular'));
check_hf42('1:n fk field explained', str_contains($relations,'Fremdschlüsselfeld im Kind-DataForm – speichert die Eltern-ID'));
check_hf42('required label is context-sensitive', str_contains($relations,"requiredLabel.textContent=relationType==='1:n'"));
check_hf42('irrelevant controls are forcibly hidden', str_contains($css,'#relation-form [hidden]{display:none!important}'));

$failed=array_filter($tests,fn(array $t): bool => !$t[1]);
echo PHP_EOL.count($tests).' Tests, '.count($failed).' Fehler'.PHP_EOL;
exit($failed?1:0);
