<?php
declare(strict_types=1);
require __DIR__.'/DataForm5-Core/bootstrap/autoload.php';
use DataForm5\Core\Container\ServiceContainer;
use DataForm5\Modules\Core\{ModuleManager,ModuleRegistry};
use DataForm5\Modules\Routing\{ModuleRouteRegistry,ModuleRouteDispatcher};
$dir=sys_get_temp_dir().'/easyit_phase_n_'.bin2hex(random_bytes(4));
mkdir($dir.'/demo/src/Http',0777,true);
file_put_contents($dir.'/demo/module.json',json_encode([
 'name'=>'demo','version'=>'1.0.0','entry'=>'Demo\\Module','enabled'=>true,
 'routes'=>[['name'=>'demo.index','methods'=>['GET'],'controller'=>'Demo\\Http\\Controller@index','capability'=>'demo.view','title'=>'Demo']]
]));
file_put_contents($dir.'/demo/bootstrap.php',"<?php\nspl_autoload_register(function(\$c){if(str_starts_with(\$c,'Demo\\\\')){\$f=__DIR__.'/src/'.str_replace('\\\\','/',substr(\$c,5)).'.php';if(is_file(\$f))require \$f;}});\n");
file_put_contents($dir.'/demo/src/Http/Controller.php',"<?php\nnamespace Demo\\Http; final class Controller { public function index(array \$request,array \$user,array \$route): array { return ['title'=>'Demo','content'=>'OK']; } }\n");
$container=new ServiceContainer(); $manager=new ModuleManager($container,new ModuleRegistry()); $manager->discover($dir);
$routes=new ModuleRouteRegistry($manager); if($routes->get('demo.index')===null) exit("PHASE_N_FAIL_ROUTE\n");
function enterprise_can(array $user,string $capability): bool { return in_array($capability,$user['permissions']??[],true); }
$dispatcher=new ModuleRouteDispatcher($container,$routes);
$r=$dispatcher->dispatch('demo.index','GET',[],['permissions'=>['demo.view']]); if(!is_array($r)||($r['content']??'')!=='OK') exit("PHASE_N_FAIL_DISPATCH\n");
try{$dispatcher->dispatch('demo.index','POST',[],['permissions'=>['demo.view']]);exit("PHASE_N_FAIL_METHOD\n");}catch(RuntimeException $e){if($e->getCode()!==405)exit("PHASE_N_FAIL_METHOD_CODE\n");}
try{$dispatcher->dispatch('demo.index','GET',[],['permissions'=>[]]);exit("PHASE_N_FAIL_CAP\n");}catch(RuntimeException $e){if($e->getCode()!==403)exit("PHASE_N_FAIL_CAP_CODE\n");}
$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);foreach($it as $f){$f->isDir()?@rmdir($f->getPathname()):@unlink($f->getPathname());}@rmdir($dir);
echo "PHASE_N_MODULE_ROUTING_OK\n";
