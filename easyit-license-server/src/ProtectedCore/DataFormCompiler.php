<?php
declare(strict_types=1);
namespace EasyIT\LicenseServer\ProtectedCore;

final class DataFormCompiler
{
    public function compile(array $definition,array $binding,int $issued,int $leaseUntil,int $graceUntil,string $runtimeId):array
    {
        $view=$definition['view']??[];$fields=[];
        foreach(($definition['fields']??[]) as $f){if(!is_array($f)||empty($f['name']))continue;$fields[]=['name'=>(string)$f['name'],'type'=>(string)($f['type']??'text'),'readonly'=>(bool)($f['readonly']??false),'required'=>(bool)($f['required']??false)];}
        $caps=array_values(array_unique(array_map('strval',$definition['capabilities']??['dataset.read'])));
        return [
          'manifest_version'=>1,
          'runtime'=>['id'=>$runtimeId,'issued_at'=>$issued,'lease_until'=>$leaseUntil,'grace_until'=>$graceUntil],
          'binding'=>$binding,
          'view'=>['mode'=>(string)($view['mode']??'form'),'page_size'=>(int)($view['page_size']??20),'fulltext_search'=>(bool)($view['fulltext_search']??false),'filter'=>(bool)($view['filter']??false),'addClass'=>(string)($view['addClass']??''),'addJavaScript'=>(string)($view['addJavaScript']??'')],
          'capabilities'=>$caps,
          'fields'=>$fields,
          'events'=>['dataform'=>array_values(array_map('strval',$definition['events']['dataform']??['open','close'])),'dataset'=>array_values(array_map('strval',$definition['events']['dataset']??[]))],
          'relations'=>array_values(array_filter($definition['relations']??[], 'is_array')),
        ];
    }
}
