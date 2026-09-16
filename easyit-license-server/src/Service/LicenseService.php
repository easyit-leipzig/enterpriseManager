<?php
declare(strict_types=1);
namespace EasyIT\LicenseServer\Service;
use PDO;
final class LicenseService
{
 public function __construct(private PDO $pdo){}
 public function byActivationKey(string $licenseKey): array{
   $pos=strpos($licenseKey,'.'); if($pos===false) throw new \RuntimeException('LICENSE_INVALID');
   $number=substr($licenseKey,0,$pos); $secret=substr($licenseKey,$pos+1);
   $st=$this->pdo->prepare('SELECT * FROM licenses WHERE license_number=? LIMIT 1');$st->execute([$number]);$lic=$st->fetch();
   if(!$lic||!password_verify($secret,(string)$lic['activation_secret_hash'])) throw new \RuntimeException('LICENSE_INVALID');
   $this->assertUsable($lic); return $lic;
 }
 public function byId(string $id): array{ $st=$this->pdo->prepare('SELECT * FROM licenses WHERE license_id=? LIMIT 1');$st->execute([$id]);$l=$st->fetch();if(!$l)throw new \RuntimeException('LICENSE_INVALID');$this->assertUsable($l);return $l; }
 public function assertUsable(array $lic):void{
   $status=(string)($lic['status']??''); if($status==='expired')throw new \RuntimeException('LICENSE_EXPIRED'); if($status==='suspended')throw new \RuntimeException('LICENSE_SUSPENDED'); if($status==='revoked')throw new \RuntimeException('LICENSE_REVOKED'); if($status!=='active')throw new \RuntimeException('LICENSE_INVALID');
   $now=time(); if(!empty($lic['valid_from'])&&$now<(int)$lic['valid_from'])throw new \RuntimeException('LICENSE_INVALID'); if(!empty($lic['valid_until'])&&$now>(int)$lic['valid_until'])throw new \RuntimeException('LICENSE_EXPIRED');
 }
 public function requireModule(string $licenseId,string $module):void{
   $now=time();$st=$this->pdo->prepare('SELECT * FROM license_modules WHERE license_id=? AND module_code=? AND enabled=1 LIMIT 1');$st->execute([$licenseId,$module]);$m=$st->fetch();
   if(!$m)throw new \RuntimeException('MODULE_NOT_LICENSED'); if(!empty($m['valid_from'])&&$now<(int)$m['valid_from'])throw new \RuntimeException('MODULE_NOT_LICENSED'); if(!empty($m['valid_until'])&&$now>(int)$m['valid_until'])throw new \RuntimeException('MODULE_NOT_LICENSED');
 }
 public function modules(string $licenseId):array{ $st=$this->pdo->prepare('SELECT module_code FROM license_modules WHERE license_id=? AND enabled=1');$st->execute([$licenseId]);return array_map(fn($r)=>(string)$r['module_code'],$st->fetchAll()); }
}
