<?php
declare(strict_types=1);
require __DIR__.'/DataForm5-Core/bootstrap/autoload.php';
use DataForm5\Validation\Core\Validator;
use DataForm5\Forms\Core\{FormFactory,FormRenderer};
$form=(new FormFactory(new Validator()))->fromDefinition('x',['fields'=>['name'=>['type'=>'text','rules'=>'required|min:2'],'msg'=>['type'=>'textarea','rules'=>'required']]]);
$bad=$form->bind(['name'=>'','msg'=>''])->validate(['name'=>'','msg'=>'']); if(!$bad->fails()) exit("PHASE_P_FAIL_VALIDATE\n");
$bound=$form->bind(['name'=>'Olaf','msg'=>'Test']); if($bound->values()['name']!=='Olaf') exit("PHASE_P_FAIL_BIND\n");
$html=(new FormRenderer())->render($bound,$bad,'/x','POST','token123'); if(!str_contains($html,'_token')||!str_contains($html,'textarea')) exit("PHASE_P_FAIL_RENDER\n");
echo "PHASE_P_MODULE_FORMS_OK\n";
