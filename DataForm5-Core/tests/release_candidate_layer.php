<?php
declare(strict_types=1);
use DataForm5\ReleaseCandidate\Core\ReleaseCandidateManager;
use DataForm5\ReleaseCandidate\Exceptions\ReleaseCandidateException;
require dirname(__DIR__).'/bootstrap/autoload.php';
$base=sys_get_temp_dir().'/df5_rc_'.bin2hex(random_bytes(4));mkdir($base.'/storage',0775,true);mkdir($base.'/system');file_put_contents($base.'/VERSION','1.0.0-rc.1');file_put_contents($base.'/README.md','ok');
$m=new ReleaseCandidateManager($base,'1.0.0-rc.1','build0044',$base.'/storage/report.json',['VERSION','README.md'],['system','storage']);
$r=$m->assertReady();assert($r['ready']===true);assert($r['release_policy']['new_features_allowed']===false);$written=$m->writeReport();assert(is_file($base.'/storage/report.json'));
$bad=new ReleaseCandidateManager($base,'1.0.0','invalid',$base.'/storage/bad.json',['missing.txt'],[]);$thrown=false;try{$bad->assertReady();}catch(ReleaseCandidateException $e){$thrown=true;assert($e->report['failed']>=2);}assert($thrown);
$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);foreach($it as $f){$f->isDir()?rmdir($f->getPathname()):unlink($f->getPathname());}rmdir($base);
echo "PASS: Release Candidate, Stabilization and Core Acceptance Layer\n";
