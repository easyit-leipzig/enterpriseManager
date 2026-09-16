<?php
declare(strict_types=1);
namespace EasyIT\Enterprise\Licensing;

final class LicenseApiClient
{
    private InstallationIdentity $identity;

    public function __construct(private array $cfg)
    {
        $this->identity = new InstallationIdentity((string)$cfg['storage_dir']);
    }

    public function activate(string $licenseKey, string $productVersion = 'unknown'): array
    {
        $id = $this->identity->ensure();
        $body = json_encode([
            'license_key' => $licenseKey,
            'installation_id' => $id['installation_id'],
            'installation_public_key' => $id['public_key'],
            'product' => ['code' => 'easyIT-enterprise', 'version' => $productVersion],
            'environment' => ['hash' => 'sha256:' . hash('sha256', PHP_OS_FAMILY . '|' . PHP_VERSION)],
        ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        $resp = $this->rawPost('/api/v1/activation/request', $body, []);
        $payload = $resp['body']['payload'] ?? [];
        $this->identity->saveState([
            'activated' => true,
            'license' => $payload['license'] ?? null,
            'installation_id' => $id['installation_id'],
            'installation_key_id' => $payload['installation_key_id'] ?? null,
            'installation_generation' => $payload['installation_generation'] ?? null,
            'modules' => $payload['modules'] ?? [],
            'last_successful_contact' => time(),
            'last_trusted_server_time' => (int)($resp['body']['server_time'] ?? time()),
            'last_server_decision' => 'ACTIVE',
        ]);
        return $payload;
    }

    public function post(string $path, array $payload): array
    {
        $id = $this->identity->ensure();
        $state = $this->identity->state();
        if (empty($state['activated'])) {
            throw new \RuntimeException('LICENSE_NOT_ACTIVATED');
        }

        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $requestId = 'req_' . bin2hex(random_bytes(16));
        $timestamp = time();
        $nonce = bin2hex(random_bytes(16));
        $keyId = (string)($state['installation_key_id'] ?? '');
        $canonical = "POST\n" . $path . "\n" . $requestId . "\n" . $id['installation_id'] . "\n" . $timestamp . "\n" . $nonce . "\n" . hash('sha256', $body);
        $signature = base64_encode(sodium_crypto_sign_detached($canonical, $id['private_key']));
        $headers = [
            'X-EasyIT-Request-ID: ' . $requestId,
            'X-EasyIT-Installation: ' . $id['installation_id'],
            'X-EasyIT-Timestamp: ' . $timestamp,
            'X-EasyIT-Nonce: ' . $nonce,
            'X-EasyIT-Key-ID: ' . $keyId,
            'X-EasyIT-Signature: ' . $signature,
        ];

        try {
            $response = $this->rawPost($path, $body, $headers);
        } catch (\RuntimeException $e) {
            // An explicit signed denial must invalidate old local authorization.
            $code = $e->getMessage();
            if (preg_match('/^(LICENSE_|INSTALLATION_|MODULE_)/', $code)) {
                $state['last_server_decision'] = $code;
                $state['last_successful_contact'] = time();
                $this->identity->saveState($state);
            }
            throw $e;
        }

        $state['last_successful_contact'] = time();
        $state['last_trusted_server_time'] = (int)($response['body']['server_time'] ?? time());
        $state['last_server_decision'] = 'ACTIVE';
        $this->identity->saveState($state);
        return $response['body']['payload'] ?? [];
    }

    public function state(): array
    {
        return $this->identity->state();
    }

    public function config(): array
    {
        return $this->cfg;
    }

    public function verifyManifest(array $manifest, string $signatureBase64, string $keyId): bool
    {
        $keys = (array)($this->cfg['trusted_server_keys'] ?? []);
        if ($keyId === '' || $signatureBase64 === '' || empty($keys[$keyId])) return false;
        $pub = base64_decode((string)$keys[$keyId], true);
        $sig = base64_decode($signatureBase64, true);
        if ($pub === false || $sig === false) return false;
        $json = json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        return sodium_crypto_sign_verify_detached($sig, $json, $pub);
    }

    private function rawPost(string $path, string $body, array $extra): array
    {
        $url = rtrim((string)$this->cfg['server_url'], '/') . $path;
        $headers = array_merge(['Content-Type: application/json', 'Accept: application/json'], $extra);
        [$status, $responseHeaders, $raw] = Transport::post(
            $url,
            $body,
            $headers,
            (int)($this->cfg['request_timeout'] ?? 15),
            (bool)($this->cfg['verify_tls'] ?? true)
        );

        $this->verifyServerResponse($raw, $responseHeaders);
        $data = json_decode($raw, true);
        if (!is_array($data)) throw new \RuntimeException('SERVER_RESPONSE_INVALID');
        if (($data['success'] ?? false) !== true) {
            $code = (string)($data['error']['code'] ?? 'SERVER_RESPONSE_INVALID');
            throw new \RuntimeException($code);
        }
        return ['status' => $status, 'headers' => $responseHeaders, 'body' => $data];
    }

    private function verifyServerResponse(string $raw, array $headers): void
    {
        $keyId = (string)($headers['x-easyit-server-key-id'] ?? '');
        $signature64 = (string)($headers['x-easyit-server-signature'] ?? '');
        $keys = (array)($this->cfg['trusted_server_keys'] ?? []);
        if ($keyId === '' || $signature64 === '' || empty($keys[$keyId])) {
            throw new \RuntimeException('SERVER_SIGNATURE_UNTRUSTED');
        }
        $pub = base64_decode((string)$keys[$keyId], true);
        $signature = base64_decode($signature64, true);
        if ($pub === false || $signature === false || !sodium_crypto_sign_verify_detached($signature, $raw, $pub)) {
            throw new \RuntimeException('SERVER_SIGNATURE_INVALID');
        }
    }
}
