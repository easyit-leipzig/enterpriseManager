<?php
declare(strict_types=1);
use DataForm5\Forms\Core\{FormFactory,FormRenderer};
require dirname(__DIR__).'/bootstrap/autoload.php';
$kernel=require dirname(__DIR__).'/bootstrap/app.php';
$factory=$kernel->container()->get(FormFactory::class);
$form=$factory->builder('project')->text('name','Projektname','required|string|min:3')->select('driver',['csv'=>'CSV','mysql'=>'MySQL'],'Datenquelle','required|in:csv,mysql')->checkbox('active','Aktiv')->build();
$bound=$form->bind(['name'=>'Demo','driver'=>'csv','active'=>true]);
assert($bound->values()===['name'=>'Demo','driver'=>'csv','active'=>true]);
$ok=$form->validate(['name'=>'Demo','driver'=>'csv','active'=>true]);assert($ok->passes()&&$ok->data()['name']==='Demo');
$bad=$form->validate(['name'=>'X','driver'=>'invalid','active'=>'x']);assert($bad->fails()&&$bad->errors()->has('name')&&$bad->errors()->has('driver'));
$html=$kernel->container()->get(FormRenderer::class)->render($bound,$bad,'/projects','post','token123');
assert(str_contains($html,'name="_token"')&&str_contains($html,'Projektname')&&str_contains($html,'selected')&&str_contains($html,'checked'));
echo "PASS: Form Engine Layer\n";
