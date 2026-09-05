<?php
declare(strict_types=1);

require dirname(__DIR__).'/system/app/bootstrap.php';

$name=trim((string)($argv[1]??'diagnostic'));
$ttl=max(1,(int)($argv[2]??30));
try{
    $lock=enterprise_cluster_lock();
    if(!$lock->acquire($name,$ttl)){
        fwrite(STDOUT,"LOCK_BUSY {$name}\n");
        exit(3);
    }
    fwrite(STDOUT,"LOCK_ACQUIRED {$name}\n");
    $lock->release($name);
    fwrite(STDOUT,"LOCK_RELEASED {$name}\n");
}catch(Throwable $e){
    fwrite(STDERR,"ERROR: {$e->getMessage()}\n");
    exit(1);
}
