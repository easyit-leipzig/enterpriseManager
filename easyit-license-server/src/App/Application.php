<?php
declare(strict_types=1);
namespace EasyIT\LicenseServer\App;
use EasyIT\LicenseServer\Database\Connection;
use EasyIT\LicenseServer\Http\Http;
use EasyIT\LicenseServer\Http\ApiResponder;
use EasyIT\LicenseServer\ProtectedCore\ActionResolver;
use EasyIT\LicenseServer\ProtectedCore\DataFormCompiler;
use EasyIT\LicenseServer\ProtectedCore\DefinitionValidator;
use EasyIT\LicenseServer\Security\RequestVerifier;
use EasyIT\LicenseServer\Security\ServerSigner;
use EasyIT\LicenseServer\Service\ActivationService;
use EasyIT\LicenseServer\Service\AuditService;
use EasyIT\LicenseServer\Service\LicenseService;
use EasyIT\LicenseServer\Service\RuntimeService;
use EasyIT\LicenseServer\Service\DesignerService;

final class Application
{
 public static function run(array $cfg):never{
  $pdo=Connection::create($cfg['database']);$signer=new ServerSigner($cfg['security']['server_key_id'],$cfg['security']['server_private_key_file'],$cfg['security']['server_public_key_file']);$res=new ApiResponder($signer,$cfg['app']['api_version']);$path=Http::path();$method=Http::method();$headers=Http::headers();$body=Http::body();$req=$headers['x-easyit-request-id']??null;
  try{
   if($method==='GET'&&$path==='/api/v1/health')$res->send(true,['status'=>'ok','api'=>'v1'],null,200,$req);
   $maintenance=(string)($cfg['app']['maintenance_mode']??'off');if($maintenance==='full')throw new \RuntimeException('SERVICE_MAINTENANCE');if($maintenance==='read_only'&&$method==='POST'&&in_array($path,['/api/v1/activation/request','/api/v1/designer/compile','/api/v1/designer/publish'],true))throw new \RuntimeException('SERVICE_MAINTENANCE');
   $data=$body!==''?json_decode($body,true,512,JSON_THROW_ON_ERROR):[];if(!is_array($data))$data=[];
   $audit=new AuditService($pdo);$licenses=new LicenseService($pdo);
   if($method==='POST'&&$path==='/api/v1/activation/request'){
      $svc=new ActivationService($pdo,$licenses,$audit);$payload=$svc->activate($data,(string)($req??''));$payload['server_key_id']=$signer->keyId();$payload['server_public_key']=$signer->publicKeyEncoded();$res->send(true,$payload,null,200,$req);
   }
   $verifier=new RequestVerifier($pdo,(int)$cfg['app']['clock_skew_seconds']);$inst=$verifier->verify($method,$path,$headers,$body);$runtime=new RuntimeService($pdo,$licenses,$audit,new DataFormCompiler(),new ActionResolver(),$signer,$cfg['runtime']);$designer=new DesignerService($pdo,$licenses,$audit,new DefinitionValidator());
   if($method==='POST'&&$path==='/api/v1/runtime/start')$res->send(true,$runtime->start($inst,$data,(string)$req),null,200,$req);
   if($method==='POST'&&$path==='/api/v1/runtime/renew')$res->send(true,$runtime->renew($inst,$data,(string)$req),null,200,$req);
   if($method==='POST'&&$path==='/api/v1/runtime/action')$res->send(true,$runtime->action($inst,$data,(string)$req),null,200,$req);
   if($method==='POST'&&$path==='/api/v1/runtime/action/result')$res->send(true,$runtime->actionResult($inst,$data),null,200,$req);
   if($method==='POST'&&$path==='/api/v1/runtime/end')$res->send(true,$runtime->end($inst,$data),null,200,$req);
   if($method==='POST'&&$path==='/api/v1/designer/validate')$res->send(true,$designer->validate($inst,$data),null,200,$req);
   if($method==='POST'&&$path==='/api/v1/designer/compile')$res->send(true,$designer->compile($inst,$data,(string)$req),null,200,$req);
   if($method==='POST'&&$path==='/api/v1/designer/publish')$res->send(true,$designer->publish($inst,$data,(string)$req),null,200,$req);
   if($method==='POST'&&$path==='/api/v1/designer/revisions')$res->send(true,$designer->revisions($inst,$data),null,200,$req);
   $res->send(false,[],['code'=>'NOT_FOUND','message'=>'Endpoint nicht gefunden.'],404,$req);
  }catch(\JsonException){$res->send(false,[],['code'=>'REQUEST_INVALID','message'=>'Ungültige JSON-Anfrage.'],400,$req);}catch(\Throwable $e){$code=$e->getMessage();$known=preg_match('/^[A-Z0-9_]{3,80}$/',$code)?$code:'SERVER_INTERNAL_ERROR';$status=$known==='SERVICE_MAINTENANCE'?503:(in_array($known,['REQUEST_INVALID','REQUEST_EXPIRED','REQUEST_SIGNATURE_INVALID','NONCE_REUSED'],true)?400:(str_starts_with($known,'LICENSE_')||str_starts_with($known,'INSTALLATION_')||str_starts_with($known,'MODULE_')||str_starts_with($known,'RUNTIME_')||str_starts_with($known,'ACTION_')||str_starts_with($known,'RELATION_')||str_starts_with($known,'DATAFORM_')?403:500));$res->send(false,[],['code'=>$known,'message'=>self::publicMessage($known)],$status,$req);}
 }
 private static function publicMessage(string $code):string{return match($code){'LICENSE_EXPIRED'=>'Die Lizenz ist abgelaufen.','LICENSE_SUSPENDED'=>'Die Lizenz ist gesperrt.','LICENSE_REVOKED'=>'Die Lizenz wurde widerrufen.','MODULE_NOT_LICENSED'=>'Das angeforderte Modul ist nicht lizenziert.','INSTALLATION_LIMIT_REACHED'=>'Die maximale Anzahl aktivierter Installationen ist erreicht.','INSTALLATION_BLOCKED'=>'Diese Installation ist gesperrt.','SERVICE_MAINTENANCE'=>'Der Dienst befindet sich im Wartungsmodus.',default=>'Die Anfrage konnte nicht verarbeitet werden.'};}
}
