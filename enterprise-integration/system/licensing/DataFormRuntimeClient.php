<?php
declare(strict_types=1);
namespace EasyIT\Enterprise\Licensing;

final class DataFormRuntimeClient
{
    public function __construct(private LicenseApiClient $api) {}

    public function start(string|int $projectId, string|int $dataformId): array
    {
        try {
            $session = $this->api->post('/api/v1/runtime/start', [
                'project_id' => (string)$projectId,
                'dataform_id' => (string)$dataformId,
                'client' => ['product' => 'easyIT-enterprise', 'version' => 'local', 'runtime_protocol' => '1'],
            ]);
            $session['state'] = 'online';
            $this->storeCache((string)$projectId, (string)$dataformId, $session);
            return $session;
        } catch (\RuntimeException $e) {
            if (!str_starts_with($e->getMessage(), 'SERVER_UNAVAILABLE')) throw $e;
            return $this->loadOfflineCache((string)$projectId, (string)$dataformId);
        }
    }

    public function renew(string $runtimeId, string $manifestHash): array
    {
        return $this->api->post('/api/v1/runtime/renew', ['runtime_id' => $runtimeId, 'manifest_hash' => $manifestHash]);
    }

    public function action(string $runtimeId, string $action, string|int|null $recordId, array $fields = [], array $extraResource = []): array
    {
        return $this->api->post('/api/v1/runtime/action', [
            'runtime_id' => $runtimeId,
            'action' => $action,
            'resource' => array_merge(['record_id' => (string)($recordId ?? '')], $extraResource),
            'changes' => ['fields' => array_values($fields)],
        ]);
    }

    public function actionResult(string $actionId, string $status, string $code = 'OK'): array
    {
        return $this->api->post('/api/v1/runtime/action/result', [
            'action_id' => $actionId,
            'status' => $status,
            'result' => ['code' => $code],
        ]);
    }

    public function end(string $runtimeId): array
    {
        return $this->api->post('/api/v1/runtime/end', ['runtime_id' => $runtimeId, 'reason' => 'user_close']);
    }

    private function cacheFile(string $projectId, string $dataformId): string
    {
        $dir = rtrim((string)$this->api->config()['storage_dir'], '/\\') . DIRECTORY_SEPARATOR . 'runtime-cache';
        if (!is_dir($dir)) @mkdir($dir, 0700, true);
        return $dir . DIRECTORY_SEPARATOR . hash('sha256', $projectId . '|' . $dataformId) . '.json';
    }

    private function storeCache(string $projectId, string $dataformId, array $session): void
    {
        if (empty($session['manifest']) || empty($session['manifest_signature']) || empty($session['server_key_id'])) return;
        $data = [
            'project_id' => $projectId,
            'dataform_id' => $dataformId,
            'session' => $session,
            'stored_at' => time(),
        ];
        $file = $this->cacheFile($projectId, $dataformId);
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        @chmod($file, 0600);
    }

    private function loadOfflineCache(string $projectId, string $dataformId): array
    {
        $state = $this->api->state();
        $decision = (string)($state['last_server_decision'] ?? 'ACTIVE');
        if ($decision !== 'ACTIVE') throw new \RuntimeException($decision);

        $now = time();
        $trusted = (int)($state['last_trusted_server_time'] ?? 0);
        if ($trusted > 0 && $now + 300 < $trusted) throw new \RuntimeException('CLOCK_ROLLBACK_DETECTED');

        $file = $this->cacheFile($projectId, $dataformId);
        if (!is_file($file)) throw new \RuntimeException('SERVER_UNAVAILABLE');
        $cached = json_decode((string)file_get_contents($file), true);
        $session = is_array($cached) ? ($cached['session'] ?? null) : null;
        if (!is_array($session) || !is_array($session['manifest'] ?? null)) throw new \RuntimeException('RUNTIME_CACHE_INVALID');
        if (!$this->api->verifyManifest($session['manifest'], (string)($session['manifest_signature'] ?? ''), (string)($session['server_key_id'] ?? ''))) {
            throw new \RuntimeException('RUNTIME_SIGNATURE_INVALID');
        }
        $lease = (int)($session['lease_until'] ?? $session['manifest']['runtime']['lease_until'] ?? 0);
        $grace = (int)($session['grace_until'] ?? $session['manifest']['runtime']['grace_until'] ?? 0);
        if ($now <= $lease) {
            $session['state'] = 'leased';
            return $session;
        }
        if ($now <= $grace) {
            $session['state'] = 'grace';
            $session['read_only'] = true;
            return $session;
        }
        throw new \RuntimeException('GRACE_EXPIRED');
    }
}
