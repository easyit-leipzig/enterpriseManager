<?php
declare(strict_types=1);
use DataForm5\Validation\Core\Validator;
use DataForm5\Validation\Rules\CallbackRule;
$kernel=require dirname(__DIR__).'/bootstrap/app.php';
$v=$kernel->container()->get(Validator::class);
$r=$v->validate(['name'=>'Olaf','email'=>'olaf@example.test','age'=>'18','profile'=>['city'=>'Berlin'],'password'=>'secret12','confirmation'=>'secret12'],[
'name'=>'required|string|min:3|max:30','email'=>'required|email','age'=>'required|integer|between:18,99','profile.city'=>'required|string','password'=>'required|min:8','confirmation'=>'same:password','optional'=>'nullable|string',
]);
if($r->fails()||$r->validated()['profile.city']!=='Berlin')throw new RuntimeException('Gültige Daten wurden abgelehnt.');
$bad=$v->validate(['email'=>'falsch','age'=>12],['name'=>'required','email'=>'email','age'=>'min:18']);
if(!$bad->fails()||!$bad->errors()->has('name')||!$bad->errors()->has('email'))throw new RuntimeException('Fehler wurden nicht erkannt.');
$custom=$v->validate(['code'=>'DF5'],['code'=>[new CallbackRule(fn($a,$value)=>$value==='DF5', ':attribute ist falsch.')]]);
if($custom->fails())throw new RuntimeException('Benutzerdefinierte Regel fehlgeschlagen.');
$v->extend('starts_with_df',fn($a,$value)=>is_string($value)&&str_starts_with($value,'DF'));
if($v->validate(['code'=>'DF5'],['code'=>'starts_with_df'])->fails())throw new RuntimeException('Erweiterte Regel fehlgeschlagen.');
echo "PASS: Validation Layer\n";
