<?php
declare(strict_types=1);
use DataForm5\Core\Container\ServiceContainer;
use DataForm5\Queue\Core\AbstractJob;
use DataForm5\Queue\Core\QueueDispatcher;
use DataForm5\Queue\Core\QueueManager;
use DataForm5\Queue\Core\QueueWorker;
require_once __DIR__.'/../bootstrap/autoload.php';
final class QueueTestJob extends AbstractJob
{
    public function handle(ServiceContainer $container): void
    {
        file_put_contents((string)$this->data['file'],(string)$this->data['value'],FILE_APPEND|LOCK_EX);
    }
}
final class QueueFailingJob extends AbstractJob
{
    public function handle(ServiceContainer $container): void { throw new RuntimeException('Absichtlicher Testfehler'); }
    public function maxAttempts(): int { return 1; }
}
$kernel=require __DIR__.'/../bootstrap/app.php';
$container=$kernel->container();
$manager=$container->get(QueueManager::class);
$dispatcher=$container->get(QueueDispatcher::class);
$worker=$container->get(QueueWorker::class);
$runtime=__DIR__.'/../storage/test-runtime/queue';
if(is_dir($runtime)){foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($runtime,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST) as $item){$item->isDir()?rmdir($item->getPathname()):unlink($item->getPathname());}rmdir($runtime);}mkdir($runtime,0775,true);
$out=$runtime.'/result.txt';
$dispatcher->dispatch(new QueueTestJob(['file'=>$out,'value'=>'S']));
assert(file_get_contents($out)==='S');
$fileQueue=new DataForm5\Queue\Drivers\FileQueue($container,$runtime.'/jobs');
$id=$fileQueue->push(new QueueTestJob(['file'=>$out,'value'=>'F']));
assert(strlen($id)===32); assert($fileQueue->size()===1); assert($worker->runNext($fileQueue)===true); assert(file_get_contents($out)==='SF'); assert($fileQueue->size()===0);
$fileQueue->push(new QueueFailingJob()); assert($worker->runNext($fileQueue)===true); assert(count(glob($runtime.'/jobs/failed/*.json')?:[])===1);
$fileQueue->push(new QueueTestJob(['file'=>$out,'value'=>'D']),1); assert($worker->runNext($fileQueue)===false); sleep(1); assert($worker->runNext($fileQueue)===true); assert(file_get_contents($out)==='SFD');
echo "PASS: Queue and Job Layer\n";
