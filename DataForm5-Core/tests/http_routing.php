<?php
declare(strict_types=1);
use DataForm5\Http\Core\Request; use DataForm5\Http\Core\Response; use DataForm5\Http\Core\Router; use DataForm5\Http\Core\HttpKernel;
$kernel=require __DIR__.'/../bootstrap/app.php'; $router=$kernel->container()->get(Router::class);
$router->get('/health',fn()=>Response::json(['status'=>'ok']))->name('health');
$router->get('/users/{id}',fn(Request $r,string $id)=>['id'=>$id,'attribute'=>$r->attribute('id')])->name('users.show');
$router->post('/echo',fn(Request $r)=>['value'=>$r->input('value')]);
$http=$kernel->container()->get(HttpKernel::class);
$r=$http->handle(new Request('GET','/health')); assert($r->status()===200 && str_contains($r->content(),'ok'));
$r=$http->handle(new Request('GET','/users/42')); assert($r->status()===200 && str_contains($r->content(),'42'));
assert($router->url('users.show',['id'=>7])==='/users/7');
$r=$http->handle(new Request('POST','/echo',[],['value'=>'test'])); assert(str_contains($r->content(),'test'));
$r=$http->handle(new Request('GET','/missing')); assert($r->status()===404);
echo "PASS: HTTP and Routing Layer\n";
