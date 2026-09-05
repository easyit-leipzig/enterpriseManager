<?php
declare(strict_types=1);
require __DIR__.'/DataForm5-Core/bootstrap/autoload.php';
use DataForm5\Core\Container\ServiceContainer;
use DataForm5\Http\Core\{Request,Response};
use DataForm5\Modules\Core\{ModuleManager,ModuleRegistry};
use DataForm5\Modules\Routing\{ModuleRouteRegistry,ModuleRouteDispatcher};
use DataForm5\Validation\Core\Validator;

$request=new Request('POST','/demo?x=1',['x'=>'1'],['name'=>'Olaf'],['accept'=>'application/json'],[],[],['upload'=>['name'=>'x.txt']],null);
if($request->input('name')!=='Olaf'||$request->query('x')!=='1'||$request->file('upload')['name']!=='x.txt') exit("PHASE_O_FAIL_REQUEST\n");
$v=new Validator(['required'=>':attribute fehlt.']);
$data=$request->validate($v,['name'=>'required|string|min:2']); if(($data['name']??'')!=='Olaf') exit("PHASE_O_FAIL_VALIDATE\n");
$r=Response::page('OK','Demo',201); if(!$r->isPage()||$r->status()!==201||$r->meta('title')!=='Demo') exit("PHASE_O_FAIL_RESPONSE\n");

$dir=sys_get_temp_dir().'/easyit_phase_o_'.bin2hex(random_bytes(4)); mkdir($dir.'/demo/src/Http',0777,true);
file_put_contents($dir.'/demo/module.json',json_encode(['name'=>'demo','version'=>'1.0.0','entry'=>'Demo\\Module','enabled'=>true,'routes'=>[['name'=>'demo.index','methods'=>['GET'],'controller'=>'Demo\\Http\\Controller@index','capability'=>'','title'=>'Demo']]]));
file_put_contents($dir.'/demo/bootstrap.php',"<?php\nspl_autoload_register(function(\$c){if(str_starts_with(\$c,'Demo\\\\')){\$f=__DIR__.'/src/'.str_replace('\\\\','/',substr(\$c,5)).'.php';if(is_file(\$f))require \$f;}});\n");
file_put_contents($dir.'/demo/src/Http/Controller.php',"<?php\nnamespace Demo\\Http; use DataForm5\\Http\\Core\\{Request,Response}; final class Controller { public function index(Request \$request): Response { return Response::json(['method'=>\$request->method(),'route'=>\$request->route()['name']??null]); } }\n");
$c=new ServiceContainer();$m=new ModuleManager($c,new ModuleRegistry());$m->discover($dir);$routes=new ModuleRouteRegistry($m);$d=new ModuleRouteDispatcher($c,$routes);
$out=$d->dispatch('demo.index','GET',new Request('GET','/module',['route'=>'demo.index']),[]); if(!$out instanceof Response||json_decode($out->content(),true)['route']!=='demo.index')exit("PHASE_O_FAIL_DISPATCH\n");
$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);foreach($it as $f){$f->isDir()?@rmdir($f->getPathname()):@unlink($f->getPathname());}@rmdir($dir);
echo "PHASE_O_MODULE_HTTP_OK\n";
