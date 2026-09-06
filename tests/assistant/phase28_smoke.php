<?php
declare(strict_types=1);

$codeRoot=dirname(__DIR__,2);$manager=require $codeRoot.'/system/assistant/bootstrap.php';

use EasyIT\Assistant\Module\ReleaseTrustStore;
use EasyIT\Assistant\Module\TrustRecoveryService;
use EasyIT\Assistant\Module\TrustFederationService;

function rrmdir28(string $dir):void{if(!is_dir($dir))return;$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);foreach($it as $f){$p=$f->getPathname();$f->isDir()?@rmdir($p):@unlink($p);}@rmdir($dir);}
function ok28(bool $ok,string $m):void{if(!$ok)throw new RuntimeException('FAIL: '.$m);echo "PASS: $m\n";}
function recovery28(ReleaseTrustStore $trust,string $root,$catalog,$library):TrustRecoveryService{return new TrustRecoveryService($trust,$catalog,$library,$root);}

$base=sys_get_temp_dir().'/easyit-phase28-'.bin2hex(random_bytes(4));$pRoot=$base.'/primary';$sRoot=$base.'/secondary';$rRoot=$base.'/recovered';@mkdir($pRoot,0770,true);@mkdir($sRoot,0770,true);@mkdir($rRoot,0770,true);
try{
    $pTrust=new ReleaseTrustStore($pRoot);$pRecovery=recovery28($pTrust,$pRoot,$releaseCatalogService,$moduleLibraryStore);$pFed=new TrustFederationService($pTrust,$pRecovery,$pRoot);
    $p=$pFed->initializeLocal('Primary A','PRIMARY','INIT INSTANCE PRIMARY Primary A');ok28(($p['role']??'')==='PRIMARY','PRIMARY-Instanz initialisiert');
    $active=(string)($pTrust->status()['activeKeyId']??'');ok28($active!=='','PRIMARY besitzt aktiven Signing-Key');$sig=$pTrust->sign('phase28-primary');ok28(($sig['algorithm']??'')==='Ed25519','PRIMARY darf signieren');

    $sTrust=new ReleaseTrustStore($sRoot);$sRecovery=recovery28($sTrust,$sRoot,$releaseCatalogService,$moduleLibraryStore);$sFed=new TrustFederationService($sTrust,$sRecovery,$sRoot);
    $s=$sFed->initializeLocal('Secondary B','SECONDARY','INIT INSTANCE SECONDARY Secondary B');ok28(($s['role']??'')==='SECONDARY','SECONDARY-Instanz initialisiert');
    $blocked=false;try{$sTrust->sign('must-fail');}catch(Throwable $e){$blocked=str_contains($e->getMessage(),'PRIMARY');}ok28($blocked,'SECONDARY darf nicht signieren');

    $public=$pRecovery->exportPublicBundle();$imp=$sRecovery->importPublicBundle((string)$public['path'],true,false,'IMPORT PUBLIC ANCHORS AS RELEASE');ok28(!empty($imp['ok']),'Public Trust Anchor auf SECONDARY importiert');
    $sync1=$pFed->exportSyncBundle();ok28(is_file((string)$sync1['path']),'PRIMARY-Sync-Paket erzeugt');
    $i1=$sFed->inspectSyncBundle((string)$sync1['path']);ok28(!empty($i1['ok'])&&($i1['syncState']??'')==='DIVERGED','Erstsync erkennt abweichenden importierten Trust-Stand als DIVERGED');
    $a1=$sFed->applySyncBundle((string)$sync1['path'],'FORCE TRUST SYNC '.(string)$p['instanceId']);ok28(!empty($a1['ok'])&&($a1['syncState']??'')==='IN_SYNC','Kontrollierter Erstsync bringt SECONDARY exakt in Sync');
    $i1b=$sFed->inspectSyncBundle((string)$sync1['path']);ok28(($i1b['syncState']??'')==='IN_SYNC','Gleicher Sync-Stand wird als IN_SYNC erkannt');

    $old=(string)($pTrust->status()['activeKeyId']??'');$pTrust->rotate('ROTATE '.$old);$sync2=$pFed->exportSyncBundle();$i2=$sFed->inspectSyncBundle((string)$sync2['path']);ok28(($i2['syncState']??'')==='FAST_FORWARD','Direkter Folgestand wird als FAST_FORWARD erkannt');
    $a2=$sFed->applySyncBundle((string)$sync2['path'],'APPLY TRUST SYNC '.(string)$p['instanceId']);ok28(!empty($a2['ok'])&&($a2['syncState']??'')==='IN_SYNC','FAST_FORWARD-Sync erfolgreich angewendet');

    $policyPath=$sRoot.'/storage/assistant/trust/policy.json';$pol=json_decode((string)file_get_contents($policyPath),true);$pol['localDivergenceMarker']='changed';file_put_contents($policyPath,json_encode($pol,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n");$div=$sFed->inspectSyncBundle((string)$sync2['path']);ok28(($div['syncState']??'')==='DIVERGED','Lokale Trust-Abweichung wird als DIVERGED erkannt');
    $sFed->applySyncBundle((string)$sync2['path'],'FORCE TRUST SYNC '.(string)$p['instanceId']);ok28(($sFed->inspectSyncBundle((string)$sync2['path'])['syncState']??'')==='IN_SYNC','Divergenz kann nur mit explizitem FORCE kontrolliert aufgelöst werden');

    $pass='Phase28-Strong-Passphrase!';$dis=$pFed->createDisasterBundle($pass,'CREATE TRUST DISASTER '.(string)$p['instanceId']);ok28(is_file((string)$dis['path']),'Vollständiges Trust-Disaster-Paket erzeugt');
    $rTrust=new ReleaseTrustStore($rRoot);$rRecovery=recovery28($rTrust,$rRoot,$releaseCatalogService,$moduleLibraryStore);$rFed=new TrustFederationService($rTrust,$rRecovery,$rRoot);
    $restore=$rFed->restoreDisasterBundle((string)$dis['path'],$pass,'Recovered C','RESTORE TRUST DISASTER '.(string)$p['instanceId']);ok28(!empty($restore['ok'])&&($restore['newInstance']['role']??'')==='SECONDARY','Disaster-Restore startet absichtlich als SECONDARY');
    $blocked=false;try{$rTrust->sign('still-blocked');}catch(Throwable $e){$blocked=str_contains($e->getMessage(),'PRIMARY');}ok28($blocked,'Wiederhergestellte SECONDARY darf nicht signieren');
    $rid=(string)$restore['newInstance']['instanceId'];$promoted=$rFed->changeRole('PRIMARY','Disaster-Recovery-Failover','PROMOTE '.$rid.' TO PRIMARY');ok28(($promoted['role']??'')==='PRIMARY','Kontrollierte Promotion auf PRIMARY erfolgreich');
    $rsig=$rTrust->sign('recovered-primary');ok28(($rsig['algorithm']??'')==='Ed25519','Promotete Recovery-Instanz darf signieren');

    $fa=$rFed->verifyFederationAudit();ok28(!empty($fa['ok']),'Federation-Audit-Hashkette ist gültig');
    $status=$sFed->status();ok28(($status['peerCount']??0)>=1,'SECONDARY führt Peer-/Sync-Registry');
    echo "PHASE28_SMOKE_PASS\n";
} finally { rrmdir28($base); }
