<?php
declare(strict_types=1);

$root=__DIR__;
$checks=[];
$check=static function(string $name,bool $ok,string $detail='') use (&$checks): void {
    $checks[]=['name'=>$name,'ok'=>$ok,'detail'=>$detail];
};

// Reproduce the dependency order that caused the HF76 browser fatal error.
require_once $root.'/products/dataform/system/DataFormManager.php';
$check('DataFormManager loads registry', class_exists('DataFormFieldTypeRegistry', false));
require_once $root.'/products/dataform/system/DataFormFieldTypes.php';
$check('Second registry include is idempotent', class_exists('DataFormFieldTypeRegistry', false));
require_once $root.'/products/dataform/system/DataFormTransport.php';
$check('Transport include remains idempotent', class_exists('DataFormTransport', false));
$check('Registry still exposes 33 field types', count(DataFormFieldTypeRegistry::allowedTypes())===33, 'count='.count(DataFormFieldTypeRegistry::allowedTypes()));

// Every DataForm HTTP entry point must use require_once for PHP dependencies.
// token_get_all() ignores compatibility text inside comments and inspects executable PHP only.
$plain=[];
foreach (glob($root.'/products/dataform/*.php') ?: [] as $file) {
    $source=(string)file_get_contents($file);
    foreach (token_get_all($source) as $token) {
        if (is_array($token) && $token[0]===T_REQUIRE) {
            $plain[]=basename($file).': line '.(string)$token[2];
        }
    }
}
$check('DataForm entry points contain no executable plain require', $plain===[], implode(' | ',$plain));

$failed=array_values(array_filter($checks,static fn(array $c):bool=>!$c['ok']));
foreach ($checks as $c) {
    echo ($c['ok']?'[PASS] ':'[FAIL] ').$c['name'];
    if ($c['detail']!=='') echo ' -- '.$c['detail'];
    echo PHP_EOL;
}
echo PHP_EOL.'Result: '.(count($checks)-count($failed)).'/'.count($checks).' PASS'.PHP_EOL;
exit($failed===[]?0:1);
