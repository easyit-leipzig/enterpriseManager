<?php
declare(strict_types=1);

$codeRoot=dirname(__DIR__,2);$manager=require $codeRoot.'/system/assistant/bootstrap.php';

use EasyIT\Assistant\Module\ReleaseTrustStore;
use EasyIT\Assistant\Module\TrustRecoveryService;
use EasyIT\Assistant\Module\TrustFederationService;
use EasyIT\Assistant\Module\TrustFailoverService;

function rrmdir29(string $dir):void{if(!is_dir($dir))return;$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);foreach($it as $f){$p=$f->getPathname();$f->isDir()?@rmdir($p):@unlink($p);}@rmdir($dir);}
function ok29(bool $ok,string $m):void{if(!$ok)throw new RuntimeException('FAIL: '.$m);echo "PASS: $m\n";}
function recovery29(ReleaseTrustStore $trust,string $root,$catalog,$library):TrustRecoveryService{return new TrustRecoveryService($trust,$catalog,$library,$root);}

$base=sys_get_temp_dir().'/easyit-phase29-'.bin2hex(random_bytes(4));$pRoot=$base.'/primary';$aRoot=$base.'/secondary-a';$bRoot=$base.'/secondary-b';foreach([$pRoot,$aRoot,$bRoot] as $r)@mkdir($r,0770,true);
try{
    $pTrust=new ReleaseTrustStore($pRoot);$pRecovery=recovery29($pTrust,$pRoot,$releaseCatalogService,$moduleLibraryStore);$pFed=new TrustFederationService($pTrust,$pRecovery,$pRoot);$p=$pFed->initializeLocal('Primary A','PRIMARY','INIT INSTANCE PRIMARY Primary A');
    $aTrust=new ReleaseTrustStore($aRoot);$aRecovery=recovery29($aTrust,$aRoot,$releaseCatalogService,$moduleLibraryStore);$aFed=new TrustFederationService($aTrust,$aRecovery,$aRoot);$a=$aFed->initializeLocal('Secondary A','SECONDARY','INIT INSTANCE SECONDARY Secondary A');
    $bTrust=new ReleaseTrustStore($bRoot);$bRecovery=recovery29($bTrust,$bRoot,$releaseCatalogService,$moduleLibraryStore);$bFed=new TrustFederationService($bTrust,$bRecovery,$bRoot);$b=$bFed->initializeLocal('Secondary B','SECONDARY','INIT INSTANCE SECONDARY Secondary B');
    ok29(($p['role']??'')==='PRIMARY'&&($a['role']??'')==='SECONDARY'&&($b['role']??'')==='SECONDARY','Drei Phase-28-Instanzen bereit');

    $public=$pRecovery->exportPublicBundle();
    foreach([[$aRecovery,'A'],[$bRecovery,'B']] as [$rec,$label]){$imp=$rec->importPublicBundle((string)$public['path'],true,false,'IMPORT PUBLIC ANCHORS AS RELEASE');ok29(!empty($imp['ok']),'Release-Trust auf Secondary '.$label.' importiert');}
    $pass='Phase29-Strong-Passphrase!';$private=$pRecovery->createPrivateBackup($pass,'BACKUP PRIVATE KEYS');$rest=$aRecovery->restorePrivateBackup((string)$private['path'],$pass,'RESTORE PRIVATE KEYS','Failover-Standby');ok29(!empty($rest['ok']),'Secondary A besitzt wiederhergestellten Release-Signing-Key für Failover');

    $pFail=new TrustFailoverService($pTrust,$pFed,$pRoot);$aFail=new TrustFailoverService($aTrust,$aFed,$aRoot);$bFail=new TrustFailoverService($bTrust,$bFed,$bRoot);
    $pi=$pFail->exportIdentityBundle();$ai=$aFail->exportIdentityBundle();$bi=$bFail->exportIdentityBundle();ok29(!empty($pi['ok'])&&!empty($ai['ok'])&&!empty($bi['ok']),'Separate Ed25519-Failover-Identities erzeugt');
    $clusterArtifact=$pFail->createCluster('cluster-phase29',[(array)$ai['data'],(array)$bi['data']],15,5,1,'CONFIGURE FAILOVER cluster-phase29');$cluster=(array)$clusterArtifact['data'];ok29(($cluster['quorum']??0)===2&&count((array)$cluster['members'])===3,'3er-Cluster besitzt Mehrheitsquorum 2');
    ok29(!empty($aFail->importCluster($cluster)['ok'])&&!empty($bFail->importCluster($cluster)['ok']),'Signierte Clusterkonfiguration auf beide SECONDARY importiert');

    // PRIMARY is fenced immediately after cluster activation until a quorum lease exists.
    $fenced=false;try{$pTrust->sign('before-lease');}catch(Throwable $e){$fenced=str_contains($e->getMessage(),'Lease')||str_contains($e->getMessage(),'Phase 29');}ok29($fenced,'PRIMARY darf nach Phase-29-Aktivierung ohne Quorum-Lease nicht signieren');

    $proposalA=$pFail->createLeaseProposal();$proposal=(array)$proposalA['data'];$ackP=(array)$pFail->acknowledgeLease($proposal)['data'];$ackA=(array)$aFail->acknowledgeLease($proposal)['data'];
    $leaseA=$pFail->activateLease($proposal,[$ackP,$ackA],'ACTIVATE LEASE '.(string)$p['instanceId'].' EPOCH 1');$lease=(array)$leaseA['data'];ok29(!empty($lease['quorumVerified'])&&count((array)$lease['proofs'])===2,'PRIMARY-Lease nur mit Quorum-ACK aktiviert');
    $sig=$pTrust->sign('phase29-leased-primary');ok29(($sig['algorithm']??'')==='Ed25519','PRIMARY mit gültigem Quorum-Lease darf signieren');
    $aFail->observeLease($lease);$bFail->observeLease($lease);
    $hb=(array)$pFail->createHeartbeat()['data'];$aFail->observeHeartbeat($hb);$bFail->observeHeartbeat($hb);ok29(($aFail->status()['heartbeatState']??'')==='HEALTHY','SECONDARY beobachtet PRIMARY-Heartbeat');

    sleep(6);
    $sus=$aFail->status();ok29(($sus['primaryState']??'')==='SUSPECTED'&&!empty($sus['failoverEligible'])===false,'Staler Heartbeat allein erlaubt vor Lease-Ablauf noch keinen Failover');
    $early=false;try{$aFail->createElectionRequest('zu früh');}catch(Throwable $e){$early=str_contains($e->getMessage(),'noch nicht sicher abgelaufen');}ok29($early,'Election vor sicherem Lease-Ablauf blockiert');

    sleep(10); // lease 15 s + grace 1 s from activation
    $eligible=$aFail->status();ok29(!empty($eligible['failoverEligible']),'SECONDARY wird erst nach Lease-Ablauf plus Grace failoverfähig');
    $oldBlocked=false;try{$pTrust->sign('old-primary-after-expiry');}catch(Throwable $e){$oldBlocked=str_contains($e->getMessage(),'abgelaufen');}ok29($oldBlocked,'Alter PRIMARY ist nach Lease-Ablauf kryptographisch vom Release-Signing gefenced');

    $reqA=(array)$aFail->createElectionRequest('PRIMARY lease expired')['data'];$voteA=(array)$aFail->voteElection($reqA)['data'];$voteB=(array)$bFail->voteElection($reqA)['data'];ok29(($voteA['payload']['epoch']??0)===2&&($voteB['payload']['epoch']??0)===2,'Quorum stimmt in neuer Election-Epoche 2 ab');

    $reqB=(array)$bFail->createElectionRequest('competing candidate')['data'];$split=false;try{$aFail->voteElection($reqB);}catch(Throwable $e){$split=str_contains($e->getMessage(),'Split-Brain');}ok29($split,'Peer darf in derselben Epoche nicht für konkurrierenden Kandidaten stimmen');

    $prom=$aFail->promoteWithQuorum($reqA,[$voteA,$voteB],'PROMOTE '.(string)$a['instanceId'].' WITH QUORUM EPOCH 2');ok29(!empty($prom['ok'])&&($prom['instance']['role']??'')==='PRIMARY','SECONDARY A mit Mehrheitsquorum auf PRIMARY promotet');
    $newSig=$aTrust->sign('new-primary-release');ok29(($newSig['algorithm']??'')==='Ed25519','Neuer PRIMARY darf mit Election-Quorum-Lease signieren');

    // Old PRIMARY cannot renew epoch 1 after peers voted epoch 2.
    $oldProposal=(array)$pFail->createLeaseProposal()['data'];$rejectOld=false;try{$bFail->acknowledgeLease($oldProposal);}catch(Throwable $e){$rejectOld=str_contains($e->getMessage(),'höhere Election-Epoche');}ok29($rejectOld,'Peer verweigert Lease-Erneuerung des alten PRIMARY nach höherer Election-Epoche');

    // New PRIMARY renewal still needs quorum.
    $renew=(array)$aFail->createLeaseProposal()['data'];$selfAck=(array)$aFail->acknowledgeLease($renew)['data'];$one=false;try{$aFail->activateLease($renew,[$selfAck],'ACTIVATE LEASE '.(string)$a['instanceId'].' EPOCH 2');}catch(Throwable $e){$one=str_contains($e->getMessage(),'gültige ACKs');}ok29($one,'Lease-Erneuerung mit nur einer Stimme ist blockiert');
    $peerAck=(array)$bFail->acknowledgeLease($renew)['data'];$renewed=$aFail->activateLease($renew,[$selfAck,$peerAck],'ACTIVATE LEASE '.(string)$a['instanceId'].' EPOCH 2');ok29(!empty($renewed['data']['quorumVerified']),'Neuer PRIMARY erneuert Lease nur mit Quorum');

    ok29(!empty($aFail->verifyAudit()['ok'])&&!empty($bFail->verifyAudit()['ok']),'Failover-Audit-Hashketten sind gültig');
    ok29($manager->getRegistry()->has('dataform.trust-failover'),'Phase-29-Assistent zentral registriert');
    echo "PHASE29_SMOKE_PASS\n";
} finally { rrmdir29($base); }
