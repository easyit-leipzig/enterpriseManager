<?php
declare(strict_types=1);
use DataForm5\Cache\Contracts\CacheInterface;
use DataForm5\Cache\Core\ArrayCache;
use DataForm5\Cache\Core\CacheManager;
use DataForm5\Cache\Core\FileCache;
$root=dirname(__DIR__); $kernel=require $root.'/bootstrap/app.php';
$manager=$kernel->container()->get(CacheManager::class); $cache=$kernel->container()->get(CacheInterface::class); if($cache!==$manager->store())throw new RuntimeException('Standard-Cache nicht konsistent.');
$cache->clear(); $cache->set('alpha',['ok'=>true],60); if($cache->get('alpha')['ok']!==true||!$cache->has('alpha'))throw new RuntimeException('Datei-Cache Lesen/Schreiben fehlgeschlagen.');
$calls=0; $v=$cache->remember('remember',60,function()use(&$calls){$calls++;return 42;}); $v2=$cache->remember('remember',60,function()use(&$calls){$calls++;return 99;}); if($v!==42||$v2!==42||$calls!==1)throw new RuntimeException('remember() fehlgeschlagen.');
$cache->set('expired','x',0); if($cache->has('expired'))throw new RuntimeException('TTL-Ablauf fehlgeschlagen.');
$array=new ArrayCache(); $array->set('x',1); if($array->get('x')!==1)throw new RuntimeException('Array-Cache fehlgeschlagen.'); $array->delete('x'); if($array->has('x'))throw new RuntimeException('Cache-Löschung fehlgeschlagen.');
if($cache instanceof FileCache)$cache->prune(); $cache->clear(); echo "PASS: Cache Layer\n";
