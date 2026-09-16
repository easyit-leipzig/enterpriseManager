<?php
declare(strict_types=1);
namespace EasyIT\Enterprise\Licensing;
final class EnterpriseRuntimeBridge
{
 private static ?DataFormRuntimeClient $runtime=null;private static ?array $session=null;private static ?array $pendingAction=null;
 public static function boot(string $root,string|int $projectId,string|int $dataformId):array{
   $cfgFile=$root.'/config/licensing.php';if(!is_file($cfgFile))throw new \RuntimeException('LICENSE_CONFIG_MISSING');$cfg=require$cfgFile;if(empty($cfg['enabled']))throw new \RuntimeException('DATAFORM_LICENSE_DISABLED');$api=new LicenseApiClient($cfg);self::$runtime=new DataFormRuntimeClient($api);self::$session=self::$runtime->start($projectId,$dataformId);$m=self::$session['manifest']??[];if(!is_array($m)||empty($m['runtime']['id']))throw new \RuntimeException('RUNTIME_MANIFEST_INVALID');return$m;
 }
 public static function authorizePost(array $post):?array{
   if(!self::$runtime||!self::$session)return null;$raw=(string)($post['action']??'');$rid=(string)($post['record']??$post['id']??'');$action=match($raw){'save_record','update'=>$rid!==''&&$rid!=='0'?'dataset.update':'dataset.create','create'=>'dataset.create','delete_record','delete','bulk_delete'=>'dataset.delete',default=>null};if($action===null)return null;$meta=['action','project','dataform','record','id','csrf','csrf_token','selected'];$fields=array_values(array_filter(array_keys($post),fn($k)=>!in_array($k,$meta,true)));$extra=[];if($raw==='bulk_delete'){$ids=array_values(array_unique(array_filter(array_map('intval',(array)($post['selected']??[])),static fn(int $id):bool=>$id>0)));if($ids===[])throw new \RuntimeException('ACTION_RESOURCE_INVALID');$extra['record_ids']=$ids;$rid='';}$decision=self::$runtime->action((string)self::$session['runtime_id'],$action,$rid,$fields,$extra);self::$pendingAction=$decision['decision']??null;return$decision;
 }
 public static function finalize(bool $success,string $code='OK'):void{if(!self::$runtime||!self::$pendingAction||empty(self::$pendingAction['action_id']))return;try{self::$runtime->actionResult((string)self::$pendingAction['action_id'],$success?'completed':'failed',$code);}catch(\Throwable){}self::$pendingAction=null;}
 public static function runtimeId():?string{return self::$session['runtime_id']??null;}
}
