<?php
declare(strict_types=1);
namespace DataForm5\Queue\Core;
use DataForm5\Core\Container\ServiceContainer;
use DataForm5\Queue\Contracts\QueueInterface;
use DataForm5\Queue\Drivers\FileQueue;
use DataForm5\Queue\Drivers\DatabaseQueue;
use PDO;
use DataForm5\Queue\Drivers\SyncQueue;
use DataForm5\Queue\Exceptions\QueueException;
final class QueueManager
{
    private array $stores=[];
    public function __construct(private readonly ServiceContainer $container, private readonly array $config, private readonly string $basePath) {}
    public function connection(?string $name=null):QueueInterface
    {
        $name ??= (string)($this->config['default']??'sync');
        if(isset($this->stores[$name])) return $this->stores[$name];
        $definition=$this->config['connections'][$name]??null;
        if(!is_array($definition)) throw new QueueException("Queue-Verbindung '{$name}' ist nicht konfiguriert.");
        return $this->stores[$name]=match($definition['driver']??'sync'){
            'sync'=>new SyncQueue($this->container),
            'file'=>new FileQueue($this->container,$this->absolute((string)($definition['path']??'storage/framework/queue'))),
            'database'=>new DatabaseQueue($this->container,$this->pdo($definition),(string)($definition['table']??'enterprise_queue_jobs'),(string)($definition['worker_id']??'')),
            default=>throw new QueueException("Unbekannter Queue-Treiber: ".($definition['driver']??'')),
        };
    }
    private function absolute(string $path):string{return str_starts_with($path,'/')||preg_match('/^[A-Za-z]:[\\\\\/]/',$path)?$path:$this->basePath.'/'.ltrim($path,'/\\');}
    private function pdo(array $definition): PDO
    {
        $dsn=(string)($definition['dsn']??'');
        if($dsn===''){
            $host=(string)($definition['host']??'127.0.0.1');
            $port=(int)($definition['port']??3306);
            $database=(string)($definition['database']??'');
            if($database==='') throw new QueueException('DatabaseQueue benötigt dsn oder database.');
            $charset=(string)($definition['charset']??'utf8mb4');
            $dsn="mysql:host={$host};port={$port};dbname={$database};charset={$charset}";
        }
        return new PDO($dsn,(string)($definition['username']??''),(string)($definition['password']??''),[
            PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES=>false,
        ]);
    }
}
