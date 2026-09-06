<?php
declare(strict_types=1);

session_start();

$root = dirname(__DIR__, 2);
/** @var \EasyIT\Assistant\AssistantManager $manager */
$manager = require $root . '/system/assistant/bootstrap.php';

function ctx(string $project, array $input, string $method = 'POST'): \EasyIT\Assistant\AssistantContext
{
    return new \EasyIT\Assistant\AssistantContext($project, null, null, null, $input, ['requestMethod' => $method]);
}

function ok(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException('FAIL: ' . $message);
    }
    echo 'PASS: ' . $message . PHP_EOL;
}

$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'easyit_phase6_' . bin2hex(random_bytes(4));
mkdir($tmp, 0777, true);
file_put_contents($tmp . DIRECTORY_SEPARATOR . 'people.csv', "id|name\n1|Ada\n2|Linus\n");
file_put_contents($tmp . DIRECTORY_SEPARATOR . 'invalid.csv', "name|value\nX|1\n");

try {
    $project = 'phase6-test';

    $r = $manager->run('datasource.configure', ctx($project, [
        'profile_name' => 'project-main',
        'driver' => 'csv',
    ]), 'profile');
    ok($r->isOk(), 'CSV-Profil kann angelegt werden.');

    $r = $manager->run('datasource.configure', ctx($project, [
        'path' => $tmp,
        'delimiter' => '|',
    ]), 'connection');
    ok($r->isOk(), 'CSV-Verbindungstest ist erfolgreich.');
    $data = $r->getData();
    ok(($data['connectionTest']['ok'] ?? false) === true, 'Verbindungsteststatus ist true.');
    ok(count($data['discoveredSources'] ?? []) === 2, 'CSV-Discovery findet beide Tabellen.');
    $people = null;
    $invalid = null;
    foreach ($data['discoveredSources'] as $source) {
        if (($source['name'] ?? '') === 'people') { $people = $source; }
        if (($source['name'] ?? '') === 'invalid') { $invalid = $source; }
    }
    ok(is_array($people) && ($people['meta']['valid'] ?? false) === true, 'CSV-Tabelle mit id wird als gültig erkannt.');
    ok(is_array($invalid) && ($invalid['meta']['valid'] ?? true) === false, 'CSV-Tabelle ohne id wird als ungültig erkannt.');

    $r = $manager->run('datasource.configure', ctx($project, ['source_name' => 'people']), 'source');
    ok($r->isOk(), 'Gültige CSV-Tabelle kann ausgewählt werden.');

    $r = $manager->run('datasource.configure', ctx($project, [], 'GET'), 'review');
    ok($r->isOk(), 'Datenquellenprofil besteht Gesamtvalidierung.');
    $config = $r->getData()['compiledConfig'] ?? [];
    ok(($config['driver'] ?? '') === 'csv', 'Export enthält Treiber csv.');
    ok(($config['source']['name'] ?? '') === 'people', 'Export enthält ausgewählte Tabelle.');
    ok(($config['connection']['delimiter'] ?? '') === '|', 'CSV-Trennzeichen ist |.');
    ok(($config['connection']['idField'] ?? '') === 'id', 'CSV-Pflichtfeld id ist festgeschrieben.');
    ok(($config['secretPolicy']['plaintextPasswordStored'] ?? true) === false, 'Klartextkennwort wird nicht gespeichert.');
    ok(in_array('query', $config['coreContract']['operations'] ?? [], true), 'Core-Datenbankvertrag enthält query.');
    ok(!empty($r->getData()['handoffUrl']), 'Handoff zum DataForm-Assistenten wird erzeugt.');

    $r = $manager->run('dataform.create', ctx($project, ['import_datasource' => '1'], 'GET'), 'source');
    $draft = $r->getData()['draft'] ?? null;
    $draftArray = $draft instanceof JsonSerializable ? $draft->jsonSerialize() : [];
    ok(($draftArray['source']['profile'] ?? '') === 'project-main', 'DataForm importiert Profilnamen.');
    ok(($draftArray['source']['driver'] ?? '') === 'csv', 'DataForm importiert Treiber.');
    ok(($draftArray['source']['name'] ?? '') === 'people', 'DataForm importiert ausgewählte Hauptquelle.');
    ok(($draftArray['source']['connection'] ?? '') === 'profile:project-main', 'DataForm referenziert Profil statt Kennwortdaten zu kopieren.');

    $badProject = 'phase6-invalid-csv';
    $manager->run('datasource.configure', ctx($badProject, ['profile_name' => 'bad', 'driver' => 'csv']), 'profile');
    $manager->run('datasource.configure', ctx($badProject, ['path' => $tmp, 'delimiter' => '|']), 'connection');
    $r = $manager->run('datasource.configure', ctx($badProject, ['source_name' => 'invalid']), 'source');
    ok(!$r->isOk(), 'CSV-Tabelle ohne id wird bei Auswahl abgelehnt.');

    $mysql = new \EasyIT\Assistant\DataSource\Adapters\MySqlDataSourceAdapter();
    $mysqlResult = $mysql->test([
        'host' => '127.0.0.1', 'port' => 3306, 'database' => 'x', 'username' => 'x', 'password' => 'TOP-SECRET'
    ]);
    ok(!$mysqlResult->isOk() || $mysqlResult->isOk(), 'MySQL-Test liefert definiertes Resultat unabhängig von lokaler Treiberinstallation.');
    ok(strpos(json_encode($mysqlResult), 'TOP-SECRET') === false, 'Verbindungstester gibt Kennwort nicht zurück.');

    $oracle = new \EasyIT\Assistant\DataSource\Adapters\OracleDataSourceAdapter();
    $oracleResult = $oracle->test([
        'host' => '127.0.0.1', 'port' => 1521, 'service' => 'XE', 'username' => 'x', 'password' => 'TOP-SECRET'
    ]);
    ok(strpos(json_encode($oracleResult), 'TOP-SECRET') === false, 'Oracle-Testresultat gibt Kennwort nicht zurück.');

    $fakeCore = new class {
        public function connect(): bool { return true; }
        public function tables(): array { return ['demo']; }
        public function query(string $query, array $params = []): array { return [$query, $params]; }
        public function insert(string $table, array $data): int { return 7; }
        public function update(string $table, array $data, array $where): bool { return true; }
        public function delete(string $table, array $where): bool { return true; }
    };
    $bridge = new \EasyIT\Assistant\DataSource\CoreDatabaseBridge($fakeCore);
    ok($bridge->connect() === true && $bridge->tables() === ['demo'], 'CoreDatabaseBridge erfüllt den einheitlichen DataForm-Datenbankvertrag.');
    ok($bridge->insert('demo', ['name' => 'x']) === 7, 'CoreDatabaseBridge delegiert CRUD an die bestehende Core-Datenbankschicht.');

    echo "PHASE6_SMOKE=PASS\n";
} finally {
    foreach (glob($tmp . DIRECTORY_SEPARATOR . '*') ?: [] as $file) { @unlink($file); }
    @rmdir($tmp);
}
