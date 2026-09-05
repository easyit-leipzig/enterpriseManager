<?php
declare(strict_types=1);
use DataForm5\Secrets\Core\{Encrypter,KeyRing,SecretManager};use DataForm5\Secrets\Stores\{EncryptedFileSecretStore,InMemorySecretStore};use DataForm5\Secrets\Contracts\EncrypterInterface;use DataForm5\Secrets\Exceptions\SecretException;
require dirname(__DIR__).'/bootstrap/autoload.php';
$ring=new KeyRing('k2',['k1'=>'old-key-material-that-is-long-enough-123456','k2'=>'new-key-material-that-is-long-enough-654321']);$crypt=new Encrypter($ring);$payload=$crypt->encrypt('top-secret');assert($payload!=='top-secret');assert($crypt->decrypt($payload)==='top-secret');
$bad=false;try{$crypt->decrypt(substr($payload,0,-2).'xx');}catch(\Throwable){$bad=true;}assert($bad);
$memory=new SecretManager(new InMemorySecretStore());$memory->set('db.password','secret');assert($memory->require('db.password')==='secret');$memory->delete('db.password');assert(!$memory->has('db.password'));
$file=sys_get_temp_dir().'/df5-secrets-'.bin2hex(random_bytes(4)).'.json';$store=new EncryptedFileSecretStore($file,$crypt);$store->set('api.token','abc123');assert($store->get('api.token')==='abc123');assert(strpos((string)file_get_contents($file),'abc123')===false);$store->rotate();assert($store->get('api.token')==='abc123');@unlink($file);
$kernel=require dirname(__DIR__).'/bootstrap/app.php';assert($kernel->container()->get(EncrypterInterface::class) instanceof Encrypter);assert($kernel->container()->get(SecretManager::class) instanceof SecretManager);
echo "PASS: Secret, Key and Encryption Layer\n";
