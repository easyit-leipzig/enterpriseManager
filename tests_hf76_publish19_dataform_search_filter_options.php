<?php
declare(strict_types=1);

$root=__DIR__;
$runtime=file_get_contents($root.'/products/dataform/runtime.php');
$records=file_get_contents($root.'/products/dataform/records.php');
$manager=file_get_contents($root.'/products/dataform/system/DataFormManager.php');
$package=file_get_contents($root.'/products/dataform/system/ProjectPackageManager.php');
$installer=file_get_contents($root.'/installer/schema/project/007_dataform_search_filter_options.php');
$context=file_get_contents($root.'/products/dataform/system/DataFormActionContext.php');
$app=file_get_contents($root.'/system/app/project_runtime/DataFormApp.php');
$foundation=file_get_contents($root.'/products/dataform/foundation.php');

$checks=[];
$check=static function(string $name,bool $ok)use(&$checks):void{$checks[$name]=$ok?'PASS':'FAIL';};

$check('settings: Volltextsuche Ja/Nein',
    str_contains($runtime,'<strong>Volltextsuche</strong>')
    && str_contains($runtime,'name="show_search"')
    && str_contains($runtime,'>Ja</option>')
    && str_contains($runtime,'>Nein</option>'));
$check('settings: Filter Ja/Nein',
    str_contains($runtime,'<strong>Filter</strong>')
    && str_contains($runtime,'name="show_filter"'));
$check('settings persistence',
    str_contains($runtime,'show_search=?,show_filter=?,show_pagination=?')
    && str_contains($runtime,'$showSearch,$showFilter,$showPagination'));
$check('settings select reload',
    str_contains($runtime,'default_per_page, show_search, show_filter, show_pagination'));
$check('schema runtime upgrade',
    str_contains($manager,"'dataforms', 'show_filter'")
    && str_contains($manager,'DEFAULT 1 AFTER show_search'));
$check('schema package bootstrap',
    str_contains($package,'show_filter TINYINT(1) NOT NULL DEFAULT 1')
    && str_contains($package,"'show_filter'=>\"TINYINT(1) NOT NULL DEFAULT 1\""));
$check('installer migration',
    str_contains($installer,"['show_filter']")
    && str_contains($installer,'ADD COLUMN show_filter'));
$check('enterprise runtime switches',
    str_contains($records,'$showSearch=(int)($dataform[\'show_search\']??1)===1')
    && str_contains($records,'$showFilter=(int)($dataform[\'show_filter\']??1)===1'));
$check('fulltext query ignored when disabled',
    str_contains($records,'$q = $showSearch ? trim((string)($_GET[\'q\'] ?? \'\')) : \'\''));
$check('field filters ignored when disabled',
    str_contains($records,'$filters = $showFilter && isset($_GET[\'filter\'])'));
$check('fulltext UI independently gated',
    str_contains($records,'<?php if($showSearch): ?><div class="record-search-main">'));
$check('filter UI independently gated',
    str_contains($records,'<?php if($showFilter): ?><details'));
$check('saved filters independently gated',
    str_contains($records,'if ($showFilter) {')
    && str_contains($records,'if(!$showFilter) throw new RuntimeException'));
$check('exported app honors both switches',
    str_contains($app,'$showFilter=(int)($form[\'show_filter\']??1)===1')
    && str_contains($app,'if($showSearch||$showFilter)')
    && str_contains($app,'$filters=$showFilter&&isset($_GET[\'filter\'])'));
$check('dataformContext exposes properties',
    str_contains($context,"'fulltext_search'")
    && str_contains($context,"'filter'")
    && str_contains($context,"'filter_enabled'"));
$check('version snapshot includes filter',
    str_contains($foundation,'\'show_filter\'=>(int)($dataform[\'show_filter\']??1)'));

$failed=[];
foreach($checks as $name=>$status){
    echo '['.$status.'] '.$name.PHP_EOL;
    if($status!=='PASS')$failed[]=$name;
}
echo count($checks).'/'.count($checks).' checks, '.count($failed).' failures'.PHP_EOL;
exit($failed?1:0);
