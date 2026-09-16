<?php
declare(strict_types=1);
namespace EasyIT\LicenseServer\Security;

final class ServerSigner
{
    private string $secretKey;
    private string $publicKey;
    public function __construct(private string $keyId, string $secretFile, string $publicFile)
    {
        if(!extension_loaded('sodium')) throw new \RuntimeException('PHP sodium extension is required.');
        if(!is_file($secretFile)||!is_file($publicFile)) throw new \RuntimeException('Server signing key missing.');
        $this->secretKey=KeyCodec::decode((string)file_get_contents($secretFile));
        $this->publicKey=KeyCodec::decode((string)file_get_contents($publicFile));
        if(strlen($this->secretKey)!==SODIUM_CRYPTO_SIGN_SECRETKEYBYTES) throw new \RuntimeException('Invalid server private key.');
        if(strlen($this->publicKey)!==SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) throw new \RuntimeException('Invalid server public key.');
    }
    public function keyId(): string { return $this->keyId; }
    public function publicKeyEncoded(): string { return KeyCodec::encode($this->publicKey); }
    public function sign(string $bytes): string { return base64_encode(sodium_crypto_sign_detached($bytes,$this->secretKey)); }
    public function signArray(array $value): string
    {
        return $this->sign(json_encode($value,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR));
    }
}
