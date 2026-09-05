<?php
declare(strict_types=1);
use DataForm5\Core\Config;use DataForm5\Core\Support\Path;use DataForm5\Hardening\Core\ProductionGate;use DataForm5\Hardening\Exceptions\ProductionGateException;use DataForm5\Hardening\Middleware\SecurityHeadersMiddleware;use DataForm5\Http\Core\Request;use DataForm5\Http\Core\Response;
require dirname(__DIR__).'/bootstrap/autoload.php';
$base=sys_get_temp_dir().'/df5-hardening-'.bin2hex(random_bytes(4));mkdir($base.'/storage/framework',0775,true);mkdir($base.'/storage/logs',0775,true);
$config=new Config(['app'=>['environment'=>'production','debug'=>false,'url'=>'https://example.test'],'secrets'=>['key'=>'secure-production-key-'.bin2hex(random_bytes(16))]]);$gate=new ProductionGate($config,new Path($base));assert($gate->inspect()['ready']===true);$gate->assertReady();
$bad=new ProductionGate(new Config(['app'=>['environment'=>'development','debug'=>true,'url'=>'http://localhost'],'secrets'=>['key'=>'change-me-development']]),new Path($base));assert($bad->inspect()['ready']===false);$thrown=false;try{$bad->assertReady();}catch(ProductionGateException){$thrown=true;}assert($thrown);
$middleware=new SecurityHeadersMiddleware();$response=$middleware->handle(new Request('GET','/'),fn()=>Response::html('ok'));assert($response->headers()['x-content-type-options']==='nosniff');assert(isset($response->headers()['content-security-policy']));
$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);foreach($it as $item){$item->isDir()?rmdir($item->getPathname()):unlink($item->getPathname());}rmdir($base);echo "PASS: Enterprise Hardening and Production Readiness Layer\n";
