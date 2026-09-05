<?php
declare(strict_types=1);

namespace DataForm5\Scheduler\Providers;

use DataForm5\Core\Config;
use DataForm5\Core\Container\ServiceContainer;
use DataForm5\Core\Contracts\ServiceProviderInterface;
use DataForm5\Core\Kernel;
use DataForm5\Scheduler\Contracts\MutexInterface;
use DataForm5\Scheduler\Core\DatabaseMutex;
use DataForm5\Scheduler\Core\FileMutex;
use DataForm5\Scheduler\Core\ScheduleHistory;
use DataForm5\Scheduler\Core\Scheduler;
use PDO;

final class SchedulerServiceProvider implements ServiceProviderInterface
{
    public function register(ServiceContainer $c): void
    {
        $c->singleton(MutexInterface::class,function(ServiceContainer $c): MutexInterface {
            $cfg=(array)$c->get(Config::class)->get('scheduler',[]);
            $base=$c->get(Kernel::class)->basePath();
            $abs=static fn(string $p): string =>
                str_starts_with($p,'/') || preg_match('/^[A-Za-z]:[\\\\\/]/',$p)
                    ? $p
                    : $base.'/'.ltrim($p,'/\\');

            if(($cfg['mutex_driver']??'file')!=='database'){
                return new FileMutex($abs((string)($cfg['lock_path']??'storage/framework/scheduler/locks')));
            }

            $database=(string)($cfg['db_database']??'');
            if($database===''){
                throw new \RuntimeException('DatabaseMutex benötigt SCHEDULER_DB_DATABASE oder ADMIN_DB_DATABASE.');
            }

            $dsn='mysql:host='.(string)($cfg['db_host']??'127.0.0.1')
                .';port='.(int)($cfg['db_port']??3306)
                .';dbname='.$database.';charset=utf8mb4';

            $pdo=new PDO(
                $dsn,
                (string)($cfg['db_username']??''),
                (string)($cfg['db_password']??''),
                [
                    PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES=>false,
                ]
            );

            return new DatabaseMutex(
                $pdo,
                (string)($cfg['mutex_table']??'enterprise_cluster_locks'),
                (string)($cfg['node_id']??'')
            );
        });

        $c->singleton(ScheduleHistory::class,function(ServiceContainer $c): ScheduleHistory {
            $cfg=(array)$c->get(Config::class)->get('scheduler',[]);
            $base=$c->get(Kernel::class)->basePath();
            $path=(string)($cfg['history_path']??'storage/framework/scheduler/history');
            if(!str_starts_with($path,'/') && !preg_match('/^[A-Za-z]:[\\\\\/]/',$path)){
                $path=$base.'/'.ltrim($path,'/\\');
            }
            return new ScheduleHistory($path);
        });

        $c->singleton(Scheduler::class,fn(ServiceContainer $c): Scheduler =>
            new Scheduler(
                $c,
                $c->get(MutexInterface::class),
                $c->get(ScheduleHistory::class)
            )
        );
    }

    public function boot(ServiceContainer $c): void {}
}
