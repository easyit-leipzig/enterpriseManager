<?php
declare(strict_types=1);
$r=__DIR__;
$runtime=(string)file_get_contents($r.'/products/dataform/runtime.php');
$manager=(string)file_get_contents($r.'/products/dataform/system/DataFormManager.php');
$css=(string)file_get_contents($r.'/products/dataform/assets/workspace.css');
$c=[];$f=function(string $n,bool $ok)use(&$c):void{$c[]=['check'=>$n,'status'=>$ok?'PASS':'FAIL'];};

$f('delete button in DataForm list',str_contains($runtime,'>Löschen</button>')&&str_contains($runtime,'delete_dataform'));
$f('CSRF protected delete',str_contains($runtime,"enterprise_check_csrf((string)(\$_POST['csrf'] ?? ''))"));
$f('server-side exact-name confirmation',str_contains($runtime,'hash_equals((string)$deleteName,$confirmation)'));
$f('double client confirmation',str_contains($runtime,'hf30-dataform-delete-ui')&&str_contains($runtime,'window.confirm')&&str_contains($runtime,'window.prompt'));
$f('manager exists',is_file($r.'/products/dataform/system/DataFormManager.php'));
$f('transactional delete',str_contains($manager,'$pdo->beginTransaction()')&&str_contains($manager,'$pdo->commit()')&&str_contains($manager,'rollBack()'));
$f('relation cleanup both directions',str_contains($manager,'source_dataform_id=? OR target_dataform_id=?'));
$f('cross-form lookup cleanup',str_contains($manager,"field_type='lookup'"));
$f('stale parent default reset',str_contains($manager,"\$cfg['default_mode']='defined'")&&str_contains($manager,"\$cfg['parent_relation_id']=0"));
$f('record cleanup',str_contains($manager,"'dataform_records'"));
$f('workflow cleanup',str_contains($manager,"'workflow_actions'")&&str_contains($manager,"'workflow_transitions'")&&str_contains($manager,"'workflow_states'"));
$f('query/report dependency cleanup',str_contains($manager,"'dataform_queries'")&&str_contains($manager,"'reports'")&&str_contains($manager,"'report_elements'"));
$f('API endpoint dependency cleanup',str_contains($manager,"'api_endpoints'")&&str_contains($manager,"'api_request_log'"));
$f('import dependency cleanup',str_contains($manager,"'dataform_import_runs'")&&str_contains($manager,"'dataform_import_changes'"));
$f('field cleanup',str_contains($manager,"'dataform_fields'"));
$f('DataForm row deleted last',str_contains($manager,"DELETE FROM dataforms WHERE id=?"));
$f('actions CSS',str_contains($css,'.df-dataform-actions'));
$f('HF30 marker',str_contains($runtime,'HF36 DATAFORM RUNTIME ACTIVE'));

$failed=count(array_filter($c,static fn(array $x):bool=>$x['status']==='FAIL'));
echo json_encode(['release'=>'RC1.8','hotfix'=>'HF34','status'=>$failed?'FAIL':'PASS','checks'=>$c,'summary'=>['checks'=>count($c),'failed'=>$failed]],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL;
exit($failed?1:0);
