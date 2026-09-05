<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/system/app/bootstrap.php';
require_once __DIR__ . '/system/DataFormRecordStore.php';
require_once __DIR__ . '/system/DataFormFieldTypes.php';

enterprise_require_auth('../../');
$projectId=max(0,(int)($_GET['project']??0));
$dataformId=max(0,(int)($_GET['dataform']??0));
$recordId=max(0,(int)($_GET['record']??0));
$fieldId=max(0,(int)($_GET['field']??0));
$download=(int)($_GET['download']??0)===1;
if ($projectId<1 || $dataformId<1 || $recordId<1 || $fieldId<1) {
    http_response_code(400); exit('Ungültige Media-Anfrage.');
}

try {
    $adminPdo=enterprise_pdo();
    enterprise_upgrade($adminPdo);
    $stmt=$adminPdo->prepare('SELECT * FROM projects WHERE id=? AND product_type=? LIMIT 1');
    $stmt->execute([$projectId,'dataform']);
    $project=$stmt->fetch(PDO::FETCH_ASSOC);
    if (!$project) throw new RuntimeException('Projekt nicht gefunden.');

    $env=enterprise_env(dirname(__DIR__,2).'/DataForm5-Core/.env');
    $pdo=new PDO(
        'mysql:host='.($env['PROJECT_DB_HOST']??'127.0.0.1').';port='.(int)($env['PROJECT_DB_PORT']??3306).';dbname='.$project['database_name'].';charset='.($env['PROJECT_DB_CHARSET']??'utf8mb4'),
        (string)($env['PROJECT_DB_USERNAME']??''),(string)($env['PROJECT_DB_PASSWORD']??''),
        [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]
    );
    $stmt=$pdo->prepare('SELECT id,name,field_type,configuration_json FROM dataform_fields WHERE id=? AND dataform_id=? LIMIT 1');
    $stmt->execute([$fieldId,$dataformId]);
    $field=$stmt->fetch(PDO::FETCH_ASSOC);
    if (!$field || !DataFormFieldTypeRegistry::isMedia((string)$field['field_type'])) throw new RuntimeException('Media-Feld nicht gefunden.');

    $record=DataFormRecordStore::find($pdo,$dataformId,$recordId);
    if ($record===null) throw new RuntimeException('Datensatz nicht gefunden.');
    $raw=$record['data'][(string)$field['name']]??'';
    if (!is_scalar($raw) || trim((string)$raw)==='') throw new RuntimeException('Keine Datei gespeichert.');

    $manager=new DataFormFieldStorageManager(dirname(__DIR__,2));
    $payload=$manager->payload((string)$raw);
    if ($payload===null) throw new RuntimeException('Datei ist nicht verfügbar oder die Prüfsumme ist ungültig.');

    $cfg=[];
    if (is_string($field['configuration_json']??null) && trim((string)$field['configuration_json'])!=='') {
        $decoded=json_decode((string)$field['configuration_json'],true);
        if (is_array($decoded)) $cfg=$decoded;
    }
    $fieldType=(string)$field['field_type'];
    $mime=preg_match('/^[A-Za-z0-9.+-]+\/[A-Za-z0-9.+-]+$/',(string)$payload['mime'])===1?(string)$payload['mime']:'application/octet-stream';
    $inline=!$download && DataFormFieldStorageManager::inlinePreviewAllowed($fieldType,$mime,$cfg);
    $name=preg_replace('/[^A-Za-z0-9._ -]+/u','_',basename((string)$payload['name']))?:'download.bin';
    $asciiName=str_replace(['"',"\r","\n"],'_',$name);

    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: no-referrer');
    header('Cross-Origin-Resource-Policy: same-origin');
    header("Content-Security-Policy: sandbox; default-src 'none'");
    header('Cache-Control: private, no-store, max-age=0, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Content-Type: '.$mime);
    header('Content-Length: '.(int)$payload['size']);
    $disposition=$inline?'inline':'attachment';
    header('Content-Disposition: '.$disposition.'; filename="'.$asciiName.'"; filename*=UTF-8\'\''.rawurlencode($name));
    echo (string)$payload['bytes'];
} catch (Throwable $e) {
    http_response_code(404);
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store');
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Media nicht verfügbar.';
}
