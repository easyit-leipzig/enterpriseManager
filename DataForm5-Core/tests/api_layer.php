<?php
declare(strict_types=1);
use DataForm5\Api\Core\ApiResource;
use DataForm5\Api\Core\ApiResponse;
use DataForm5\Api\Core\Paginator;
require_once __DIR__.'/../bootstrap/autoload.php';
final class UserResource extends ApiResource { public function toArray():array{return ['id'=>$this->value('id'),'name'=>$this->value('name')];} }
$kernel=require __DIR__.'/../bootstrap/app.php'; $api=$kernel->container()->get(ApiResponse::class);
$r=UserResource::make(['id'=>1,'name'=>'Ada']); assert($r->toArray()['name']==='Ada');
$c=UserResource::collection([['id'=>1,'name'=>'Ada'],['id'=>2,'name'=>'Grace']])->toArray(); assert(count($c['data'])===2);
$p=Paginator::fromArray(range(1,25),2,10,'/users'); assert($p->items()[0]===11); assert($p->meta()['last_page']===3); assert($p->links()['next']==='/users?page=3');
$res=$api->success($r); assert($res->status()===200); $body=json_decode($res->content(),true,512,JSON_THROW_ON_ERROR); assert($body['success']===true&&$body['data']['id']===1);
$err=$api->error('Ungültig',422,['email'=>['Pflichtfeld']],'VALIDATION_FAILED'); assert($err->status()===422);
$paginated=$api->paginated($p); $pb=json_decode($paginated->content(),true,512,JSON_THROW_ON_ERROR); assert(count($pb['data'])===10&&$pb['meta']['total']===25);
echo "PASS: API and Serialization Layer\n";
