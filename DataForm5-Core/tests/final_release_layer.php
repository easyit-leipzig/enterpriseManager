<?php
declare(strict_types=1);
use DataForm5\FinalRelease\Core\FinalReleaseManager;
use DataForm5\FinalRelease\Exceptions\FinalReleaseException;
require dirname(__DIR__).'/bootstrap/autoload.php';
$base=sys_get_temp_dir().'/df5_final_'.bin2hex(random_bytes(4));
mkdir($base.'/storage',0775,true);mkdir($base.'/system');mkdir($base.'/docs');
file_put_contents($base.'/VERSION','1.0.0');file_put_contents($base.'/README.md','ok');
$m=new FinalReleaseManager($base,'1.0.0','build0045',$base.'/storage/report.json',$base.'/storage/manifest.json',['VERSION','README.md'],['system','storage'],['storage/report.json','storage/manifest.json']);
$r=$m->assertReady();assert($r['ready']===true);assert($r['release_policy']['lts']===true);
$written=$m->writeReport();assert(is_file($base.'/storage/report.json'));
$manifest=$m->writeManifest();assert(is_file($base.'/storage/manifest.json'));assert($manifest['file_count']>=2);assert(strlen($manifest['manifest_sha256'])===64);
$bad=new FinalReleaseManager($base,'1.0.0-rc.1','invalid',$base.'/storage/bad.json',$base.'/storage/bad-manifest.json',['missing.txt'],[],[]);
$thrown=false;try{$bad->assertReady();}catch(FinalReleaseException $e){$thrown=true;assert($e->report['failed']>=3);}assert($thrown);
$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);foreach($it as $f){$f->isDir()?rmdir($f->getPathname()):unlink($f->getPathname());}rmdir($base);
echo "PASS: Final Release 1.0.0 and Core Closure Layer\n";
