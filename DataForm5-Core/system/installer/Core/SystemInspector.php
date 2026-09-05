<?php
declare(strict_types=1);
namespace DataForm5\Installer\Core;
final class SystemInspector
{
    public function __construct(private readonly string $basePath, private readonly string $minimumPhp = '8.2.0') {}
    public function inspect(): array
    {
        $checks=[];
        $checks[]=new RequirementResult('php.version',version_compare(PHP_VERSION,$this->minimumPhp,'>='),true,'PHP '.PHP_VERSION.'; mindestens '.$this->minimumPhp);
        foreach (['json','openssl'] as $extension) $checks[]=new RequirementResult('extension.'.$extension,extension_loaded($extension),true,'PHP-Erweiterung '.$extension);
        $checks[]=new RequirementResult('extension.mbstring',extension_loaded('mbstring'),false,'Empfohlen für Unicode-Textfunktionen');
        $checks[]=new RequirementResult('extension.zip',extension_loaded('zip'),false,'Optional für ZIP-Pakete und Backups');
        foreach (['storage','storage/framework','storage/logs'] as $path) {
            $absolute=$this->basePath.'/'.$path;
            if (!is_dir($absolute)) @mkdir($absolute,0775,true);
            $checks[]=new RequirementResult('writable.'.$path,is_dir($absolute)&&is_writable($absolute),true,$absolute);
        }
        $failedRequired=array_values(array_filter($checks,fn(RequirementResult $r)=>$r->required&&!$r->passed));
        return ['ready'=>$failedRequired===[],'php'=>PHP_VERSION,'checks'=>array_map(fn(RequirementResult $r)=>$r->toArray(),$checks),'failed_required'=>count($failedRequired)];
    }
}
